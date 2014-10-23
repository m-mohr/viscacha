<?php

class slog {

var $statusdata;
var $ip;
var $sid;
var $cookies;
var $cookiedata;
var $cookielastvisit;
var $bots;
var $bi;
var $gFields;
var $fFields;
var $minFields;
var $maxFields;
var $groups;
var $permissions;
var $querysid;
var $positive;
var $negative;
var $boards;

// Konstruktor
function slog () {
	global $config;

	$this->statusdata = array();
	$this->ip = getip();
	$this->sid = '';
	$this->cookies = FALSE;
	$this->cookiedata = array(0, '');
	$this->cookielastvisit = 0;
	$this->defineGID();
	$this->bots = cache_spiders();
	$this->bi = BotDetection($this->bots, $_SERVER['HTTP_USER_AGENT'], TRUE);
	$this->gFields = array('downloadfiles', 'forum', 'posttopics', 'postreplies', 'addvotes', 'attachments', 'edit', 'voting','admin', 'gmod', 'guest', 'members', 'profile', 'pdf', 'pm', 'wwo', 'search', 'team', 'usepic', 'useabout', 'usesignature', 'docs');
	$this->fFields = array('f_downloadfiles', 'f_forum', 'f_posttopics', 'f_postreplies', 'f_addvotes', 'f_attachments', 'f_edit', 'f_voting');
	$this->minFields = array('flood');
	$this->maxFields = array();
	$this->groups = array();
	$this->permissions = array();
	$this->querysid = TRUE;
	$this->positive = array();
	$this->negative = array();
	$this->boards = array();
}

function SessionDel () {
	global $config;

	if ($config['sessionrefresh'] == 0) {
		return true;
	}
	
	$time = time();
	$handleget = file_get_contents("data/session_del.php");
	$lastrefresh = $time-$handleget;
	if ($lastrefresh > $config['sessionrefresh']) {
		file_put_contents("data/session_del.php",$time);
		return true;
	}
	else {
		return false;
	}
}

/*
* Get the IDs for Member and Guest Group and sets constants
*/
function defineGID() {
	global $db;
	$scache = new scache('groupstandard');
	if ($scache->existsdata() == TRUE) {
	    $data = $scache->importdata();
	}
	else {
	    $cresult = $db->query("SELECT id, guest FROM {$db->pre}groups WHERE core = '1' AND admin != '1' LIMIT 2",__LINE__,__FILE__);
	    $data = array();
	    while ($id = $db->fetch_assoc($cresult)) {
	    	if ($id['guest'] == 1) {
	        	$data['group_guest'] = $id['id'];
	        }
	        else {
	        	$data['group_member'] = $id['id'];
	        }
	    }
	    $scache->exportdata($data);
	}
	if (!defined('GROUP_GUEST')) {
	    DEFINE('GROUP_GUEST', $data['group_guest']);
	}
	if (!defined('GROUP_MEMBER')) {
	    DEFINE('GROUP_MEMBER', $data['group_member']);
	}
}

/*
* Ermittelt die Team-Gruppen-IDs (Admins & G-Mods)
*/
function getTeamID () {
	global $db;
	$scache = new scache('team_ag');
	if ($scache->existsdata() == TRUE) {
	    $data = $scache->importdata();
	}
	else {
	    $cresult = $db->query("SELECT id, gmod, admin FROM {$db->pre}groups WHERE admin = '1' OR gmod = '1'",__LINE__,__FILE__);
	    $data = array('gmod' => array(), 'admin' => array());
	    while ($id = $db->fetch_assoc($cresult)) {
	    	if ($id['admin'] == 1) {
	        	$data['admin'][] = $id['id'];
	        }
	        elseif ($id['gmod'] == 1) {
	        	$data['gmod'][] = $id['id'];
	        }
	    }
	    $scache->exportdata($data);
	}
	return $data;
}

/*
* Ermittelt den öffentlichen Status einer Person und liefert die Gruppentitel per Array zurück
*/
function getStatus($groups, $implode = false) {
	$titles = array();
	if (count($this->statusdata) == 0) {
		$this->getStatusData();
	}
	$groups = explode(',', $groups);
	if (count($groups) == 1) {
		$gid = current($groups);
		if (isset($this->statusdata[$gid])) {
			if (empty($implode)) {
				return array($this->statusdata[$gid]['title']);
			}
			else {
				return $this->statusdata[$gid]['title'];
			}
		}
	}
	else {
		foreach ($groups as $gid) {
			if (!isset($this->statusdata[$gid])) {
				continue;
			}
			if ($this->statusdata[$gid]['admin'] == 1) {
				if (empty($implode)) {
					return array($this->statusdata[$gid]['title']);
				}
				else {
					return $this->statusdata[$gid]['title'];
				}
			}
			if ($this->statusdata[$gid]['core'] != 1) {
				$titles[] = $this->statusdata[$gid]['title'];
			}
		}
	}
	if (count($titles) == 0) {
		if (empty($implode)) {
			return array($this->statusdata[GROUP_MEMBER]['title']);
		}
		else {
			return $this->statusdata[GROUP_MEMBER]['title'];
		}
	}
	else {
		if (empty($implode)) {
			return $titles;
		}
		else {
			return implode($implode, $titles);
		}
	}
}

/*
* - Holt die Daten für getStatus() -
*/
function getStatusData () {
	global $db;
	$scache = new scache('group_status');
	if ($scache->existsdata() == TRUE) {
	    $this->statusdata = $scache->importdata();
	}
	else {
	    $cresult = $db->query("SELECT id, admin, guest, title, core FROM {$db->pre}groups ORDER BY core DESC",__LINE__,__FILE__);
	    while ($row = $db->fetch_assoc($cresult)) {
	        $this->statusdata[$row['id']] = $row;
	    }
	    $scache->exportdata($this->statusdata);
	}
}

function setlang($l1, $l2) {
	global $my;
	if (!$my->vlogin) {
		$my->name = $l1;
	}
	if (date('I', times()) == 1) {
		$my->timezonestr .= $l2;
	}
}

function updatelogged () {
	global $my, $db, $gpc;
	$serialized = serialize($my->mark);
	if (!is_array($my->pwfaccess)) {
		$my->pwfaccess = array();
	}
	if (!is_array($my->settings)) {
		$my->settings = array();
	}
	$serializedpwf = serialize($my->pwfaccess);
	$serializedstg = serialize($my->settings);
	if ($my->id > 0) {
		$sql = "mid = '".$my->id."'";
	}
	else {
		$sql = "sid = '".$my->sid."'";
	}
	$action = $gpc->get('action', str);
	$qid = $gpc->get('id', int);
	
	$db->query ("UPDATE {$db->pre}session SET mark = '".$serialized."', wiw_script = '".SCRIPTNAME."', wiw_action = '".$action."', wiw_id = '".$qid."', active = '".time()."', pwfaccess = '".$serializedpwf."', settings = '".$serializedstg."' WHERE ".$sql." LIMIT 1",__LINE__,__FILE__);

	if ($my->vlogin) {
		// Eigentlich könnten wir uns das Updaten der User-Lastvisit-Spalte sparen, für alle User die Cookies nutzen. Einmal, am Anfang der Session würde dann reichen
		$db->query("UPDATE {$db->pre}user SET lastvisit = '".time()."' WHERE id = '".$my->id."'",__LINE__,__FILE__);
	}

}

function logged () {
	global $config, $db, $phpdoc, $gpc;

	// Alte Sessions (nach bestimmter Zeit, die geprüft wird) löschen
	if ($this->SessionDel() == true) {
	    $sessionsave = $config['sessionsave']*60;
	    $old = time()-$sessionsave;
	    $db->query ("DELETE FROM {$db->pre}session WHERE active <= '".$old."'",__LINE__,__FILE__);
	}
	
	$sessionid = $gpc->get('s', str);
	if (empty($sessionid) || strlen($sessionid) != $config['sid_length']) {
		$sessionid = FALSE;
		$this->querysid = FALSE;
	}

    $vdata = $gpc->save_str(getcookie('vdata'));
    $vlastvisit = $gpc->save_int(getcookie('vlastvisit'));
    $vhash = $gpc->save_str(getcookie('vhash'));
	// Read additional data
	if (!empty($vdata)) {
		$this->cookies = TRUE;
		$this->cookiedata = explode("|", $vdata);
	}
	else {
		$this->cookiedata = array(0,'');
	}
	if (!empty($vlastvisit)) {
		$this->cookies = TRUE;
		$this->cookielastvisit = $vlastvisit;
	}
	else {
		$this->cookielastvisit = 0;
	}
	if (isset($vhash)) {
		$this->cookies = TRUE;
	    if (strlen($vhash) != $config['sid_length']) {
	    	$this->sid = '';
	    }
	    else {
	    	$this->sid = $vhash;
	    }
	}
	elseif($sessionid) {
		$this->sid = $sessionid;
	}
	else {
		$this->sid = '';
	}
	
	if (empty($this->sid)) {
		$result = $db->query('SELECT sid FROM '.$db->pre.'session WHERE ip = "'.$this->ip.'" AND mid = "0" LIMIT 1',__LINE__,__FILE__);
		if ($db->num_rows() == 1) {
			$sidrow = $db->fetch_assoc($result);
			$this->sid = $sidrow['sid'];
			$this->querysid = TRUE;
		}
	}
	
	// Checke nun die Session
	if (empty($this->sid)) {
		if (SCRIPTNAME != 'external') {
			$my = $this->sid_new();
		}
		else {
			$my->vlogin = FALSE;
		}
	}
	else {
		$my = $this->sid_load();
	}
	
	$expire = $config['sessionsave']+1*60;
	makecookie($config['cookie_prefix'].'_vhash', $this->sid, $expire);
	
	if ($gpc->get('action') == "markasread" || !isset($my->mark)) {
		$my->mark = array();
	}
	else {
		$my->mark = unserialize(html_entity_decode($my->mark, ENT_QUOTES));
	}
	if (!is_array($my->mark)) {
		$my->mark = array();
	}
		
	if ($my->vlogin) {
		makecookie($config['cookie_prefix'].'_vdata', $my->id."|".$my->pw);
	}
	else {
		$my->id = 0;
	}

	if (!isset($my->pwfaccess)) {
		$my->pwfaccess = array();
	}
	else {
		$my->pwfaccess = unserialize(html_entity_decode($my->pwfaccess, ENT_QUOTES));
	}
	if (!is_array($my->pwfaccess)) {
		$my->pwfaccess = array();
	}
	
	if (!isset($my->settings)) {
		$my->settings = array();
	}
	else {
		$my->settings = unserialize(html_entity_decode($my->settings, ENT_QUOTES));
	}
	if (!is_array($my->settings)) {
		$my->settings = array();
	}
		
	if (!isset($my->timezone) || $my->timezone == NULL) {
		$my->timezone = $config['timezone'];
	}
	
	$my->timezonestr = '';
	if ($my->timezone <> 0) {
		if ($my->timezone{0} != '+' && $my->timezone > 0) {
			$my->timezonestr = '+'.$my->timezone;
		}
		else {
			$my->timezonestr = $my->timezone;
		}
	}

	$cache = cache_loaddesign();
	$q_tpl = $gpc->get('design', int);
	if (isset($my->template) == false || isset($cache[$my->template]) == false) {
		$my->template = $config['templatedir'];
	}
	if (isset($my->settings['q_tpl']) && isset($cache[$my->settings['q_tpl']])) {
		$my->template = $my->settings['q_tpl'];
	}
	if (isset($cache[$q_tpl])) {
		//if ($gpc->get('admin', int) != 1) {
			$my->settings['q_tpl'] = $q_tpl;
		//}
		$my->template = $q_tpl;
	}
	$my->templateid = $cache[$my->template]['template'];
	$my->imagesid = $cache[$my->template]['images'];
	$my->cssid = $cache[$my->template]['stylesheet'];
	$my->smileyfolder = $cache[$my->template]['smileyfolder'];

	$cache2 = cache_loadlanguage();
	$q_lng = $gpc->get('lang', int);
	if (isset($my->language) == false || isset($cache2[$my->language]) == false) {
		$my->language = $config['langdir'];
	}
	if (isset($my->settings['q_lng']) && isset($cache2[$my->settings['q_lng']]) != false) {
		$my->language = $my->settings['q_lng'];
	}
	if (isset($cache2[$q_lng]) != false) {
		$my->settings['q_lng'] = $q_lng;
		$my->language = $q_lng;
	}

	if (isset($my->lastvisit) && !$my->clv) {
		$my->clv = $my->lastvisit;
	}
	
	if (!isset($my->opt_hidebad)) {
		$my->opt_hidebad = 0;
	}
	if (!isset($my->opt_showsig)) {
		$my->opt_showsig = 1;
	}

	if ($this->bi[1] == 'e') {
		$slog = new slog();
		$my = $slog->logged();
		global $lang;
		$lang->init($my->language);
		$tpl = new tpl();

		ob_start();
		include('data/banned.php');
		$banned = ob_get_contents();
		ob_end_clean();
        echo $tpl->parse("banned");

        $phpdoc->Out();
		$db->close();
		exit();
	}

	$this->sid2url();

	return $my;
}

function sid_load($fromnew=FALSE) {
	global $config, $db, $gpc;
	if ($config['session_checkip']) {
		$short_ip = ext_iptrim ($this->ip, 3);
		$sid_checkip = '(s.sid = "'.$this->sid.'" AND s.ip LIKE "'.$short_ip.'%")';
	}
	else {
		$sid_checkip = 's.sid = "'.$this->sid.'"';
	}

	if (!empty($this->cookiedata[0]) && !empty($this->cookiedata[1])) {
		$sql = 'u.id = "'.$this->cookiedata[0].'" AND u.pw = "'.$this->cookiedata[1].'"';
	}
	elseif ($this->bi[0] != FALSE) {
	    $sql = 's.ip = "'.$this->ip.'" AND s.mid = "0"';
	}
	else {
		$sql = $sid_checkip;
	}

	$result = $db->query('
	SELECT u.*, s.lastvisit as clv, s.ip, s.mark, s.pwfaccess, s.sid, s.settings   
	FROM '.$db->pre.'session AS s LEFT JOIN '.$db->pre.'user as u ON s.mid = u.id 
	WHERE '.$sql.'
	LIMIT 1
	',__LINE__,__FILE__);

	if ($db->num_rows($result) == 1) {
		$my = $gpc->prepare($db->fetch_object($result));
		if ($my->id > 0 && $my->confirm == '11') {
			$my->vlogin = TRUE;
		}
		else {
			$my->vlogin = FALSE;
		}
	}
	else {
		$my = $this->sid_new(TRUE);
	}

	return $my;
}
function sid_new($fromload=FALSE) {
	global $config, $db, $gpc;

	if (!$fromload) {
		$load = $db->query('SELECT mid FROM '.$db->pre.'session WHERE mid = "'.$this->cookiedata[0].'" LIMIT 1',__LINE__,__FILE__);
		if ($db->num_rows($load) == 1) {
			$my = $this->sid_load(TRUE);
			return $my;
		}
	}

	$result = $db->query('SELECT * FROM '.$db->pre.'user WHERE id = "'.$this->cookiedata[0].'" AND pw = "'.$this->cookiedata[1].'" LIMIT 1',__LINE__,__FILE__);
	$my = $gpc->prepare($db->fetch_object($result));
	if ($db->num_rows($result) == 1 && $my->confirm == '11') {
		$id = &$my->id;
		$lastvisit = &$my->lastvisit;
		$my->clv = $my->lastvisit;
		$my->vlogin = TRUE;
		makecookie($config['cookie_prefix'].'_vdata', $my->id."|".$my->pw);
	}
	else {
		$id = 0;
		$lastvisit = $this->cookielastvisit;
		$my->clv = $this->cookielastvisit;
		$my->vlogin = FALSE;
		makecookie($config['cookie_prefix'].'_vdata', "|");
	}
	
	makecookie($config['cookie_prefix'].'_vlastvisit', $lastvisit);
	
	$this->sid = $this->construct_sid();
	$my->sid = &$this->sid;
	$my->mark = serialize(array());
	$my->pwfaccess = serialize(array());
	$my->settings = serialize(array());

	$action = $gpc->get('action', str);
	$qid = $gpc->get('id', int);

	$db->query("INSERT INTO {$db->pre}session 
	(sid, mid, wiw_script, wiw_action, wiw_id, active, ip, remoteaddr, lastvisit, mark, pwfaccess, settings) VALUES
	('$this->sid', '$id','".SCRIPTNAME."','".$action."','".$qid."','".time()."','$this->ip','".$gpc->save_str(htmlspecialchars($_SERVER['HTTP_USER_AGENT']))."','$lastvisit','$my->mark','$my->pwfaccess','$my->settings')",__LINE__,__FILE__);

	if (!$this->cookies && !$this->querysid) {
		$arr = parse_url($_SERVER['REQUEST_URI']);
		if (empty($arr['query'])) {
			$url = $_SERVER['REQUEST_URI'].'?s='.$this->sid;
		}
		else {
			$url = $_SERVER['REQUEST_URI'].'&s='.$this->sid;
		}
		viscacha_header('Location: '.$url);
	}

	return $my;
}
function sid_logout() {
	global $my, $db, $config, $gpc;
	if ($my->id > 0) {
		$sql = "mid = '".$my->id."'";
	}
	else {
		$sql = "sid = '".$my->sid."'";
	}
	
	$action = $gpc->get('action', str);
	$qid = $gpc->get('id', int);
	
	$db->query ("UPDATE {$db->pre}session SET wiw_script = '".SCRIPTNAME."', wiw_action = '".$action."', wiw_id = '".$qid."', active = '".time()."', mid = '0' WHERE ".$sql,__LINE__,__FILE__);
	makecookie($config['cookie_prefix'].'_vdata', '|', -60);
	$db->query("UPDATE {$db->pre}user SET lastvisit = '".time()."' WHERE id = '".$my->id."'",__LINE__,__FILE__);
}
function sid_login() {
	global $my, $config, $db, $gpc;
	$result = $db->query('SELECT u.*, s.mid FROM '.$db->pre.'user AS u LEFT JOIN '.$db->pre.'session AS s ON s.mid = u.id WHERE name="'.$_POST['name'].'" AND pw=MD5("'.$_POST['pw'].'") LIMIT 1',__LINE__,__FILE__);

	$my2 = array();
	$my2['mark'] = $my->mark;
	$my2['sid'] = $my->sid;

	$mytemp = $gpc->prepare($db->fetch_object($result));

	if ($db->num_rows($result) == 1 && $mytemp->confirm == '11') {
	
		$my = &$mytemp;
		
		$my->vlogin = TRUE;
		
		$my->mark = $my2['mark'];
		$my->sid = $my2['sid'];
		$my->p = $this->Permissions();
		
		if (!isset($my->timezone)) {
			$my->timezone = $config['timezone'];
		}
		
		$my->timezonestr = '';
		if ($my->timezone <> 0) {
			if ($my->timezone{0} != '+' && $my->timezone > 0) {
				$my->timezonestr = '+'.$my->timezone;
			}
			else {
				$my->timezonestr = $my->timezone;
			}
		}

		$cache = cache_loaddesign();
		$q_tpl = $gpc->get('design', int);
		if (isset($my->template) == false || isset($cache[$my->template]) == false) {
			$my->template = $config['templatedir'];
		}
		if (isset($my->settings['q_tpl']) && isset($cache2[$my->settings['q_tpl']]) != false) {
			$my->template = $my->settings['q_tpl'];
		}
		if (isset($cache2[$q_tpl]) != false) {
			//if ($gpc->get('admin', int) != 1) {
				$my->settings['q_tpl'] = $q_tpl;
			//}
			$my->template = $q_tpl;
		}
		if (isset($cache[$q_tpl]) != false) {
			$my->template = $q_tpl;
		}
		$my->templateid = $cache[$my->template]['template'];
		$my->imagesid = $cache[$my->template]['images'];
		$my->cssid = $cache[$my->template]['stylesheet'];
		$my->smileyfolder = $cache[$my->template]['smileyfolder'];

		$cache2 = cache_loadlanguage();
		$q_lng = $gpc->get('lang', int);
		if (isset($my->language) == false || isset($cache2[$my->language]) == false) {
			$my->language = $config['langdir'];
		}
		if (isset($my->settings['q_lng']) && isset($cache2[$my->settings['q_lng']]) != false) {
			$my->language = $my->settings['q_lng'];
		}
		if (isset($cache2[$q_lng]) != false) {
			$my->settings['q_lng'] = $q_lng;
			$my->language = $q_lng;
		}
		
		if (!empty($my->mid)) {
			$sqlwhere = "mid = '{$my->id}'";
			$db->query ("DELETE FROM {$db->pre}session WHERE sid = '{$my->sid}' LIMIT 1",__LINE__,__FILE__);
		}
		else {
			$sqlwhere = "sid = '{$my->sid}'";	
		}
		if (!isset($my->settings) || !is_array($my->settings)) {
			$my->settings = array();
		}
		
		$action = $gpc->get('action', str);
		$qid = $gpc->get('id', int);
		
		$db->query ("UPDATE {$db->pre}session SET settings = '".serialize($my->settings)."', mark = '".serialize($my->mark)."', wiw_script = '".SCRIPTNAME."', wiw_action = '".$action."', wiw_id = '".$qid."', active = '".time()."', mid = '$my->id', lastvisit = '$my->lastvisit' WHERE $sqlwhere LIMIT 1",__LINE__,__FILE__);
		makecookie($config['cookie_prefix'].'_vdata', $my->id."|".$my->pw);
		makecookie($config['cookie_prefix'].'_vlastvisit', $my->lastvisit);
		$this->cookiedata[0] = $my->id;
		$this->cookiedata[1] = $my->pw;
		return TRUE;
	}
	else {
		return FALSE;
	}
}

function sid2url() {
	if (!defined('SID2URL')) {
    	if ($this->cookies || $this->bi[0] != FALSE) {
    		DEFINE('SID2URL_JS_x', '');
    		DEFINE('SID2URL_JS_1', '');
    		DEFINE('SID2URL_x', '');
    		DEFINE('SID2URL_1', '');
    		DEFINE('SID2URL', '');
    	}
    	else {
    		DEFINE('SID2URL_JS_x', '&s='.$this->sid);
    		DEFINE('SID2URL_JS_1', '?s='.$this->sid);
    		DEFINE('SID2URL_x', '&amp;s='.$this->sid);
    		DEFINE('SID2URL_1', '?s='.$this->sid);
    		DEFINE('SID2URL', $this->sid);
    	}
	}
}

function mark_read() {
	global $my, $db;
	if ($my->vlogin) {
		$db->query ("UPDATE {$db->pre}session SET lastvisit = '".time()."' WHERE mid = '$my->id'",__LINE__,__FILE__);
	}
	else {
		$db->query ("UPDATE {$db->pre}session SET lastvisit = '".time()."' WHERE sid = '$this->sid'",__LINE__,__FILE__);
	}
	$my->mark = array();
	return $db->affected_rows();
}

/*
* - Konstruiert eine sichere Session-ID -
*/
function construct_sid() {
	global $config;
	if ($config['sid_length'] == 64) {
		srand((double)microtime()*1000000);
		$sid = md5(uniqid(rand())).md5(uniqid($this->ip));
	}
	elseif ($config['sid_length'] == 96) {
		srand((double)microtime()*1000000);
		$sid = md5(uniqid(rand())).md5(uniqid($this->ip)).md5(rand());
	}
	elseif ($config['sid_length'] == 128) {
		srand((double)microtime()*1000000);
		$sid = md5(uniqid(rand())).md5(uniqid($this->ip));
		srand((double)microtime()*2000000);
		$sid .= md5(rand()).md5(uniqid(rand()));
	}
	else {		// Falling back to 32 chars
		srand((double)microtime()*1000000);
		$sid = md5(uniqid(rand()));
	}
	$this->sid = str_shuffle($sid);
	return $this->sid;
}

function Permissions ($board = 0, $groups = null, $member = null) {
	global $db, $my;
	
	if ($groups == null && isset($my->groups)) {
		$groups = $my->groups;
	}
	elseif ($groups != null) {
		$groups = $groups;
	}
	else {
		$groups = '';
	}
	if ($member == null && $groups == null) {
		$member = $my->vlogin;
	}
	$this->groups = explode(',', $groups);
	if (count($this->statusdata) == 0) {
		$this->getStatusData();
	}
	$groups = array();
	foreach ($this->groups as $gid) {
		if (isset($this->statusdata[$gid])) {
			$groups[] = $gid;
		}
	}
	if (empty($groups)) {
		if ($member == true) {
			$groups[] = GROUP_MEMBER;
		}
		elseif ($member == false) {
			$groups[] = GROUP_GUEST;
		}
	}
	$this->groups = $groups;
	$groups = implode(',', $groups);
	
	$keys = array_merge($this->gFields, $this->maxFields, $this->minFields);
	$permissions = array_combine($keys, array_fill(0, count($keys), array()));
	$result = $db->query("SELECT ".implode(', ', $keys)." FROM {$db->pre}groups WHERE id IN ({$groups})");
	if ($db->num_rows() > 1) {
		while ($row = $db->fetch_assoc($result)) {
			foreach ($row as $key => $value) {
				$permissions[$key][] = $value;
			}
		}
		foreach ($permissions as $key => $value) {
			if (in_array($key, $this->minFields)) {
				$permissions[$key] = min($value);
			}
			else {
				$permissions[$key] = max($value);
			}
		}
	}
	else {
		$permissions = $db->fetch_assoc($result);
	}
	// Set positive and negative with global group settings
	$boardid = $this->getBoards();
	if ($permissions['forum'] == 0 && count($boardid) > 0) {
		$this->positive = array();
		$this->negative = array_combine($boardid, $boardid);
	}
	elseif ($permissions['forum'] == 1 && count($boardid) > 0) {
		$this->positive = array_combine($boardid, $boardid);
		$this->negative = array();
	}
	else {
		$this->positive = array();
		$this->negative = array();
	}
	
	$this->permissions = $permissions;
	if ($board > 0) {
		$result = $db->query("SELECT gid, ".implode(', ', $this->fFields)." FROM {$db->pre}fgroups WHERE gid IN ({$groups},0) AND bid = '{$board}'");
		if ($db->num_rows() == 0) {
			return $permissions;
		}
		$fpermission = array();
		while ($row = $db->fetch_assoc($result)) {
			$gid = $row['gid'];
			unset($row['gid']);
			$fpermissions[$gid] = $row;
		}
		$gkeys = array_keys($fpermissions);
		$gkeys = array_intersect($gkeys, $this->groups);
		if (count($gkeys) == 0) {
			$gkeys[] = 0;
		}
		$permissions2 = array_combine($this->fFields, array_fill(0, count($this->fFields), array()));
		foreach ($gkeys as $gid) {
			foreach ($fpermissions[$gid] as $key => $value) {
				if ($value != -1) {
					$permissions2[$key][] = $value;
				}
			}
		}
		foreach ($permissions2 as $key => $value) {
			$key = substr($key, 2, strlen($key));
			if (count($value) > 0) {
				$permissions[$key] = max($value);
			}
		}
		if (isset($this->positive[$board]) && $permissions['forum'] == 0) {
			unset($this->positive[$board]);
			$this->negative[$board] = $board;
		}
		elseif (isset($this->negative[$board]) && $permissions['forum'] == 1) {
			unset($this->negative[$board]);
			$this->positive[$board] = $board;
		}
	}
	return $permissions;
}

function getBoards() {
	if (count($this->boards) == 0) {
		$this->boards = array_keys(cache_cat_bid());
	}
	return $this->boards;
}

/*
* - Ermittelt die Berechtigungen eines Besuchers für alle Foren	-
* - Vorheriger Aufruf von Permissions() erforderlich!			-
*
* Array     =   GlobalPermissions ()
* $array[Board-ID][perm]
*/
function GlobalPermissions() {
	global $db, $my;
	$boardid = $this->getBoards();
	if (count($boardid) > 0) {
		$fpermissions = array_combine($boardid, array_fill(0, count($boardid), array()));
	}
	else {
		$fpermissions = array();
	}
	$groups = implode(',', $this->groups);
	$result = $db->query("SELECT bid, gid,".implode(', ', $this->fFields)." FROM {$db->pre}fgroups WHERE gid IN ({$groups},0)");
	while ($row = $db->fetch_assoc($result)) {
		$gid = $row['gid'];
		$bid = $row['bid'];
		unset($row['gid'], $row['bid']);
		$fpermissions[$bid][$gid] = $row;
	}
	$fFieldValues = array();
	foreach ($this->fFields as $key) {
		$key = substr($key, 2, strlen($key));
		$fFieldValues[$key] = $this->permissions[$key];
	}
	if (count($boardid) > 0) {
		$permissions = array_combine($boardid, array_fill(0, count($boardid), $fFieldValues));
	}
	else {
		$permissions = array();
	}
	foreach ($fpermissions as $bid => $array) {
		$gkeys = array_keys($array);
		$gkeys = array_intersect($gkeys, $this->groups);
		if (count($gkeys) == 0) {
			$gkeys[] = 0;
		}
		$permissions2 = array_combine($this->fFields, array_fill(0, count($this->fFields), array()));
		if (count($array) > 0) {
			foreach ($gkeys as $gid) {
				foreach ($array[$gid] as $key => $value) {
					if ($value != -1) {
						$permissions2[$key][] = $value;
					}
				}
			}
			foreach ($permissions2 as $key => $value) {
				$key = substr($key, 2, strlen($key));
				if (count($value) > 0) {
					$permissions[$bid][$key] = max($value);
				}
			}
			if (isset($this->positive[$bid]) && $permissions[$bid]['forum'] == 0) {
				unset($this->positive[$bid]);
				$this->negative[$bid] = $bid;
			}
			elseif (isset($this->negative[$bid]) && $permissions[$bid]['forum'] == 1) {
				unset($this->negative[$bid]);
				$this->positive[$bid] = $bid;
			}
		}
	}
	return $permissions;
}

/*
* - Ermittelt die zusätzlichen Berechtigungen eines Users (Moderatorenbezogen) -
*
* Array     =   ModPermissions (Board-ID)
* Array     =   [0] Angegebener User ist im Forum Moderator
*               [1] Themenbewertungen setzen
*		    	[2] Themen als News setzen
*		    	[3] Themen als Artikel setzen
*		    	[4] Beiträge löschen
*		    	[5] Beiträge  verschieben/kopieren
*/
function ModPermissions ($bid) {
	global $my, $db;
	if ($my->vlogin && $my->id > 0) {
	    if ($this->permissions['admin'] == 1 || $this->permissions['gmod'] == 1) {
	    	return array(1,1,1,1,1,1);
	    }
	    else {
		    $result = $db->query("SELECT s_rating, s_news, s_article, p_delete, p_mc FROM {$db->pre}moderators WHERE mid = '{$my->id}' AND bid = '{$bid}' AND time > ".time(),__LINE__,__FILE__);
	        if ($db->num_rows() > 0) {
	        	$row = $db->fetch_assoc($result);
	            return array(1, $row['s_rating'], $row['s_news'], $row['s_article'], $row['p_delete'], $row['p_mc']);
	        }
	        else {
	            return array(0,0,0,0,0,0);
	        }
	    }
	}
	else {
		return array(0,0,0,0,0,0);
	}
}

/*
* - Konstruiert den günstigsten SQL-String -
*/
function sqlinboards($spalte,$r_and=NULL) {
	
	if ($this->permissions['forum'] == 1 && count($this->negative) > 1) {
		$ids = implode(',',$this->negative);
		$sql = ' '.$spalte.' NOT IN ('.$ids.') ';
	}
	elseif ($this->permissions['forum'] == 1 && count($this->negative) == 1) {
		$sql = ' '.$spalte.' != '.current($this->negative).' ';
	}
	elseif ($this->permissions['forum'] == 1 && count($this->negative) == 0) {
		$sql = ' 1=1 ';
	}
	elseif ($this->permissions['forum'] == 0 && count($this->positive) > 1) {
		$ids = implode(',',$this->positive);
		$sql = ' '.$spalte.' IN ('.$ids.') ';
	}
	elseif ($this->permissions['forum'] == 0 && count($this->positive) == 1) {
		$sql = ' '.$spalte.' = '.current($this->positive).' ';
	}
	elseif ($this->permissions['forum'] == 0 && count($this->positive) == 0) {
		$sql = ' 1=0 ';
	}
	else {
		die('Hacking Attempt: Groups (Positive/Negative)');
	}
	
	if ($r_and == 1) {
		$sql = $sql.' AND ';
	}
	else {
		$sql = ' AND '.$sql;
	}
	return $sql;
}

}

?>
