<?php
/*
	Viscacha - An advanced bulletin board solution to manage your content easily
	Copyright (C) 2004-2017, Lutana
	http://www.viscacha.org

	Authors: Matthias Mohr et al.
	Start Date: May 22, 2004

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Load flood check (essential for this class)
include_once("classes/function.flood.php");

// TODO: Dieser Code sollte nicht auf $my basieren, da sonst bei der Abfrage von fremden Rechten
// eine gefährliche Vermischung stattfindet.

class slog {

var $statusdata;
var $ip;
var $user_agent;
var $sid;
var $cookies;
var $cookiedata;
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
var $sidload;
var $change_mid;

/**
 * Constructor for this class.
 *
 * This class manages the user-permissions, login and logout.
 * This function does some initial work.
 */
function __construct() {
	$this->statusdata = array();
	$this->ip = getip();
	$this->user_agent = iif(isset($_SERVER['HTTP_USER_AGENT']), $_SERVER['HTTP_USER_AGENT'], getenv('HTTP_USER_AGENT'));
	$this->sid = '';
	$this->cookies = false;
	$this->cookiedata = array(0, '');
	$this->defineGID();
	$this->groups = array();
	$this->permissions = array();
	$this->querysid = true;
	$this->positive = array();
	$this->negative = array();
	$this->boards = array();
	$this->sidload = false;
	$this->change_mid = null;
	$data = unserialize(file_get_contents('data/group_fields.php'));
	foreach ($data as $key => $values) {
		$this->{$key} = $values;
	}
}

/**
 * Checks if it is time to delete the old user sessions (and returns true in this case).
 *
 * @return boolean
 */
function SessionDel () {
	global $config, $filesystem;

	if ($config['sessionrefresh'] == 0) {
		return true;
	}

	$time = time();
	$handleget = file_get_contents("data/session_del.php");
	$lastrefresh = $time-$handleget;
	if ($lastrefresh > $config['sessionrefresh']) {
		$filesystem->file_put_contents("data/session_del.php",$time);
		return true;
	}
	else {
		return false;
	}
}

/**
 * Gets the IDs for the member and the guest group and sets constants.
 *
 * The ID for the members is set to the constant 'GROUP_GUEST'.
 * The ID for the guests is set to the constant 'GROUP_MEMBER'.
 */
function defineGID() {
	global $db, $scache;

	$groups_obj = $scache->load('groups');
	$data = $groups_obj->standard();

	if (!defined('GROUP_GUEST')) {
		DEFINE('GROUP_GUEST', $data['group_guest']);
	}
	if (!defined('GROUP_MEMBER')) {
		DEFINE('GROUP_MEMBER', $data['group_member']);
	}
}

/**
 * Returns the ip of the calling user.
 *
 * The ip is determined while constructing this class by function getip().
 *
 * @return string Ip-Adress
 */
function getIP() {
	return $this->ip;
}

/**
 * Determines the public status of a member and returns the titles.
 *
 * If you specify the second parameter and it is empty you get an array.
 * If the second parameter is not empty, the titles will be separated by this parameter.
 *
 * @param String Komma-separated list containing the group-ids
 * @param String Separator for the titles or empty string
 * @return mixed An array with the titles or a string (depends on second parameter)
 */
function getStatus($groups, $implode = '', $deleted = false) {
	global $scache, $lang;
	if ($deleted) {
		return $lang->phrase('fallback_no_username_group');
	}
	$titles = array();
	if (count($this->statusdata) == 0) {
		$group_status = $scache->load('groups');
		$this->statusdata = $group_status->status();
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
			if ($this->statusdata[$gid]['core'] != 1 || $this->statusdata[$gid]['admin'] == 1) {
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

/**
 * This is a quick and dirty helper function to set some data.
 *
 * This function sets some language-data (name for guests and the timezone) and sets $my->ip.
 */
function setlang() {
	global $my, $lang;
	if (!$my->vlogin) {
		$my->name = $lang->phrase('fallback_no_username');
	}
	$my->timezone_str = $this->getTimezone();
	$my->ip = $this->ip;
}

/**
 * Returns the timezone for the current user (GMT +/-??:?? or just GMT).
 */
function getTimezone($base = null) {
	global $my, $lang;

	$tz = $lang->phrase('gmt');

	if ($base === null || $base === '') {
		$base = $my->timezone;
	}

	if ($base != 0) {
		preg_match('~^(\+|-)?(\d{1,2})\.?(\d{0,2})?$~u', $base, $parts);
		$parts[2] = intval($parts[2]);
		$parts[3] = intval($parts[3]);
	}
	else {
		$parts = array(
			1 => '',
			2 => 0,
			3 => 0
		);
	}

	$summer = (date('I', times()) == 1);
	if ($summer && $parts[1] == '-') {
		$parts[2] = $parts[2] - 1;
	}
	else if ($summer) {
		$parts[2] = $parts[2] + 1;
	}

	if ($parts[2] != 0) {
		if (empty($parts[1])) {
			$parts[1] = '+';
		}

		$parts[2] = leading_zero($parts[2]);

		$parts[3] = $parts[3]/100*60;
		$parts[3] = leading_zero($parts[3]);

		$tz .= ' '.$parts[1].$parts[2].':'.$parts[3];
	}

	return $tz;
}

/**
 * Function that updates the user and session data after the script finished.
 */
function updatelogged () {
	global $my, $db, $gpc, $plugins;
	if (!isset($my->pwfaccess) || !is_array($my->pwfaccess)) {
		$my->pwfaccess = array();
	}
	if (!isset($my->settings) || !is_array($my->settings)) {
		$my->settings = array();
	}
	if (!isset($my->mark) || !is_array($my->mark)) {
		$my->mark = array();
	}
	$serialized = $db->escape_string(serialize($my->mark));
	$serializedpwf = $db->escape_string(serialize($my->pwfaccess));
	$serializedstg = $db->escape_string(serialize($my->settings));
	if ($my->id > 0 && !is_id($this->change_mid)) {
		$sqlwhere = "mid = '{$my->id}'";
	}
	else {
		$sqlwhere = "sid = '{$this->sid}'";
	}
	$action = $gpc->get('action', str);
	$qid = $gpc->get('id', int);

	$sqlset = '';
	if (is_id($this->change_mid)) {
		$sqlset = ", mid = '{$this->change_mid}'";
	}

	($code = $plugins->load('permissions_updatelogged_query')) ? eval($code) : null;
	
	$db->query ("
	UPDATE {$db->pre}session
	SET mark = '{$serialized}', wiw_script = '".SCRIPTNAME."', wiw_action = '{$action}', wiw_id = '{$qid}', active = '".time()."',
		pwfaccess = '{$serializedpwf}', settings = '{$serializedstg}', lastvisit = '{$my->clv}' {$sqlset}
	WHERE {$sqlwhere}
	LIMIT 1
	");

	if ($my->vlogin) {
		// Eigentlich könnten wir uns das Updaten der User-Lastvisit-Spalte sparen, für alle User die Cookies nutzen. Einmal, am Anfang der Session würde dann reichen
		$db->query("UPDATE {$db->pre}user SET lastvisit = '".time()."'  WHERE id = '{$my->id}'");
	}

}

/**
 * Deletes old sessions (after a specific time, set in the admin control panel).
 *
 * @return boolean
 */
function deleteOldSessions () {
	global $config, $db;
	if ($this->SessionDel() == true) {
		$sessionsave = $config['sessionsave']*60;
		$old = time()-$sessionsave;
		$db->query ("DELETE FROM {$db->pre}session WHERE active <= '{$old}'");
		return true;
	}
	else {
		return false;
	}
}

/**
 * This script gets and prepares userdata, checks login data, sets cookies and manages sessions.
 *
 * @return object Data of the user who is calling this script
 */
function logged () {
	global $config, $db, $gpc, $scache, $plugins, $tpl;

	$this->deleteOldSessions();

	$sessionid = $gpc->get('s', str);
	if (empty($sessionid) || strlen($sessionid) != 32) {
		$sessionid = false;
		$this->querysid = false;
	}

	$vdata = $gpc->save_str(getcookie('vdata'));
	$vhash = $gpc->save_str(getcookie('vhash'));
	if (!empty($vdata)) {
		$this->cookies = true;
		$this->cookiedata = explode("|", $vdata);
	}
	else {
		$this->cookiedata = array(0,'');
	}
	if (isset($vhash)) {
		$this->cookies = true;
		if (strlen($vhash) != 32) {
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

	if (empty($this->sid) && array_empty($this->cookiedata)) {
		$result = $db->query('SELECT sid FROM '.$db->pre.'session WHERE ip = "'.$this->ip.'" AND mid = "0" LIMIT 1');
		if ($db->num_rows($result) == 1) {
			$sidrow = $db->fetch_assoc($result);
			$this->sid = $sidrow['sid'];
			$this->querysid = true;
		}
	}

	// Checke nun die Session
	if (empty($this->sid)) {
		$my = $this->sid_new();
	}
	else {
		$my = $this->sid_load();
	}

	$expire = ($config['sessionsave']+1)*60;
	makecookie($config['cookie_prefix'].'_vhash', $this->sid, $expire);

	if (!$my->vlogin) {
		$my->id = 0;
	}

	if ($gpc->get('action') == "markasread" || !isset($my->mark)) {
		$my->mark = array();
	}
	else {
		$my->mark = unserialize($my->mark);
		if (!is_array($my->mark)) {
			$my->mark = array();
		}
	}

	if (empty($my->pwfaccess) || !isset($my->mark)) {
		$my->pwfaccess = array();
	}
	else {
		$my->pwfaccess = unserialize($my->pwfaccess);
		if (!is_array($my->mark)) {
			$my->pwfaccess = array();
		}
	}

	if (empty($my->settings) || !isset($my->settings)) {
		$my->settings = array();
	}
	else {
		$my->settings = unserialize($my->settings);
		if (!is_array($my->mark)) {
			$my->settings = array();
		}
	}

	if (!isset($my->timezone) || $my->timezone === null || $my->timezone === '') {
		$my->timezone = $config['timezone'];
	}
	
	if (!isset($my->theme)) {
		$my->theme = null;
	}

	$loadlanguage_obj = $scache->load('loadlanguage');
	$cache2 = $loadlanguage_obj->get();

	$q_lng = $gpc->get('language', int);
	if (isset($my->language) == false || isset($cache2[$my->language]) == false) {
		$my->language = $config['langdir'];
	}
	if (isset($my->settings['q_lng']) && isset($cache2[$my->settings['q_lng']])) {
		$my->language = $my->settings['q_lng'];
	}
	if (isset($cache2[$q_lng])) {
		$my->settings['q_lng'] = $q_lng;
		$my->language = $q_lng;
	}

	if (empty($my->clv)) {
		$my->clv = 0;
	}

	if (!isset($my->opt_showsig)) {
		$my->opt_showsig = 1;
	}

	makecookie($config['cookie_prefix'].'_vlastvisit', time());

	($code = $plugins->load('permissions_logged_end')) ? eval($code) : null;

	$this->sid2url($my);

	return $my;
}

/**
 * Checks whether a user has to be banned, and if so, calls $this->banisch().
 */
function checkBan() {
	global $my;
	// Try to ban other banned people or do nothing
	if (file_exists('data/bannedip.php')) {
		$bannedip = file('data/bannedip.php');
		$bannedip = array_map('trim', $bannedip);
	}
	else {
		$bannedip = array();
		$filesystem->file_put_contents('data/bannedip.php', '');
	}
	$ban = false;
	foreach ($bannedip as $row) {
		$row = explode("\t", $row, 6);
		if ($row[0] == 'ip') {
			$row[2] = intval($row[2]);
			if (mb_strpos(' '.$this->ip, ' '.trim($row[1])) !== false && ($row[2] > time() || $row[2] == 0)) {
				$ban = true;
				break;
			}
		}
		elseif ($row[0] == 'user') {
			$row[2] = intval($row[2]);
			if ($my->id == $row[1] && ($row[2] > time() || $row[2] == 0)) {
				$ban = true;
				break;
			}
		}
		else {
			continue;
		}
	}
	if ($ban == true) {
		$reason = null;
		if (!empty($row[5])) {
			$reason = $row[5];
		}
		$until = null;
		if ($row[2] != 0) {
			$until = $row[2];
		}
		return compact("reason", "until");
	}
	return false;
}

/**
 * Loads an existing session.
 *
 * @return object Data of the user who is calling this script
 */
function sid_load() {
	global $config, $db, $gpc;
	if ($config['session_checkip'] > 0) {
		$short_ip = ext_iptrim($this->ip, $config['session_checkip']);
		if ($config['session_checkip'] != 4) {
			$sqliplike = "LIKE '{$short_ip}%'";
		}
		else {
			$sqliplike = "= '{$short_ip}'";
		}
		$sid_checkip = "(s.sid = '{$this->sid}' AND s.ip {$sqliplike})";
	}
	else {
		$sid_checkip = "s.sid = '{$this->sid}'";
	}

	if (!array_empty($this->cookiedata) && count($this->cookiedata) == 2) {
		$sql = 'u.id = "'.$this->cookiedata[0].'" AND u.pw = "'.$this->cookiedata[1].'"';
	}
	else {
		$sql = $sid_checkip;
	}

	$result = $db->query('
	SELECT u.*, f.*, s.lastvisit as clv, s.ip, s.mark, s.pwfaccess, s.sid, s.settings
	FROM '.$db->pre.'session AS s
		LEFT JOIN '.$db->pre.'user as u ON s.mid = u.id
		LEFT JOIN '.$db->pre.'userfields as f ON f.ufid = u.id
	WHERE u.deleted_at IS NULL AND '.$sql.'
	LIMIT 1
	');

	if ($db->num_rows($result) == 1) {
		$my = $this->cleanUserData($db->fetch_object($result));
		if ($my->id > 0 && $my->confirm == '11') {
			$my->vlogin = TRUE;
		}
		else {
			$my->vlogin = FALSE;
		}
	}
	else {
		$this->sidload = true;
		$my = $this->sid_new();
	}

	return $my;
}

/**
 * Creates a new session.
 *
 * If cookies are disabled the page will be reloaded with session id added to query string.
 *
 * @param boolean Set to true if this function is called from sid_load()
 * @return object Data of the user who is calling this script
 */
function sid_new() {
	global $config, $db, $gpc;

	if (!$this->sidload && !array_empty($this->cookiedata)) {
		$load = $db->query('SELECT mid FROM '.$db->pre.'session WHERE mid = "'.$this->cookiedata[0].'" LIMIT 1');
		if ($db->num_rows($load) == 1) {
			$this->sidload = true;
			$my = $this->sid_load();
			return $my;
		}
	}

	if (!array_empty($this->cookiedata) && count($this->cookiedata) == 2) {
		$result = $db->query('
			SELECT u.*, f.*
			FROM '.$db->pre.'user AS u LEFT JOIN '.$db->pre.'userfields as f ON f.ufid = u.id
			WHERE u.deleted_at IS NULL AND u.id = "'.$this->cookiedata[0].'" AND u.pw = "'.$this->cookiedata[1].'"
			LIMIT 1');
		$my = $this->cleanUserData($db->fetch_object($result));
		$nodata = ($db->num_rows($result) == 1) ? false : true;
		if ($nodata == true) { // Loginversuch mit falschen Daten => Versuch protokollieren!
			makecookie($config['cookie_prefix'].'_vdata', '|', 0);
			set_failed_login();
		}
	}
	else {
		$nodata = true;
	}
	
	if (!isset($my) || !is_object($my)) {
		$my = new stdClass();
	}

	if ($nodata == false && $my->confirm == '11') {
		$id = &$my->id;
		$lastvisit = $my->lastvisit;
		$my->clv = $my->lastvisit;
		$my->vlogin = true;
		makecookie($config['cookie_prefix'].'_vdata', $my->id."|".$my->pw);
	}
	else {
		$id = 0;
		$lastvisit = $gpc->save_int(getcookie('vlastvisit'));
		$my->clv = $lastvisit;
		$my->vlogin = false;
		makecookie($config['cookie_prefix'].'_vdata', "|", -60);
	}

	$this->sid = $this->construct_sid();
	$my->sid = &$this->sid;
	$my->mark = serialize(array());
	$my->pwfaccess = serialize(array());
	$my->settings = serialize(array());

	$action = $gpc->get('action', str);
	$qid = $gpc->get('id', int);

	$db->query("INSERT INTO {$db->pre}session
	(sid, mid, wiw_script, wiw_action, wiw_id, active, ip, user_agent, lastvisit, mark, pwfaccess, settings) VALUES
	('{$this->sid}', '{$id}','".SCRIPTNAME."','{$action}','{$qid}','".time()."','{$this->ip}','".$gpc->save_str($this->user_agent)."','{$lastvisit}','".$db->escape_string($my->mark)."','".$db->escape_string($my->pwfaccess)."','".$db->escape_string($my->settings)."')");

	return $my;
}

/**
 * Logs the user out.
 */
function sid_logout() {
	global $my, $db, $config, $gpc;

	$action = $gpc->get('action', str);
	$qid = $gpc->get('id', int);
	$time = time();

	$db->query ("
	UPDATE {$db->pre}session
	SET wiw_script = '".SCRIPTNAME."', wiw_action = '{$action}', wiw_id = '{$qid}', active = '{$time}', mid = '0', pwfaccess = ''
	WHERE ".iif($my->id > 0, "mid = '{$my->id}'", "sid = '{$this->sid}'")."
	LIMIT 1
	");
	$db->query("UPDATE {$db->pre}user SET lastvisit = '{$time}' WHERE id = '{$my->id}'");

	makecookie($config['cookie_prefix'].'_vdata', '|', 0);
}

/**
 * Logs the user in.
 *
 * @param boolean Remember the user's data (with cookies).
 * @return boolean Returns 'true' on success, 'false' on failure.
 */
function sid_login($remember = true) {
	global $my, $config, $db, $gpc, $scache;
	$username = $gpc->get('name', str);
	$pw = $gpc->get('pw', none);

	$result = $db->query("
	SELECT u.*, f.*, u.lastvisit as clv, s.ip, s.mark, s.pwfaccess, s.sid, s.settings
	FROM {$db->pre}user AS u
		LEFT JOIN {$db->pre}session AS s ON (u.id = s.mid OR s.sid = '{$this->sid}')
		LEFT JOIN {$db->pre}userfields as f ON f.ufid = u.id
	WHERE u.name = '{$username}' AND u.deleted_at IS NULL
	");
	$mytemp = $db->fetch_object($result);
	if (is_object($mytemp) && check_pw($pw, $mytemp->pw) && $mytemp->confirm == '11') {
		$mytemp = $this->cleanUserData($mytemp);

		$mytemp->mark = $my->mark;
		$mytemp->pwfaccess = $my->pwfaccess;
		$mytemp->settings = $my->settings;
		$my = $mytemp;
		unset($mytemp);
		$my->vlogin = true;
		$my->p = $this->Permissions();

		if (!isset($my->timezone) || $my->timezone === null || $my->timezone === '') {
			$my->timezone = $config['timezone'];
		}
	
		if (!isset($my->theme)) {
			$my->theme = null;
		}

		$loadlanguage_obj = $scache->load('loadlanguage');
		$cache2 = $loadlanguage_obj->get();

		$q_lng = $gpc->get('language', int);
		if (isset($my->language) == false || isset($cache2[$my->language]) == false) {
			$my->language = $config['langdir'];
		}
		if (isset($my->settings['q_lng']) && isset($cache2[$my->settings['q_lng']])) {
			$my->language = $my->settings['q_lng'];
		}
		if (isset($cache2[$q_lng])) {
			$my->settings['q_lng'] = $q_lng;
			$my->language = $q_lng;
		}
		if (!isset($my->settings) || !is_array($my->settings)) {
			$my->settings = array();
		}

		$this->setlang();

		$action = $gpc->get('action', str);
		$qid = $gpc->get('id', int);

		$this->change_mid = $my->id;
		if ($remember == true) {
			$expire = 31536000;
		}
		else {
			$expire = 900;
		}
		makecookie($config['cookie_prefix'].'_vdata', $my->id.'|'.$my->pw, $expire);
		$this->cookiedata[0] = $my->id;
		$this->cookiedata[1] = $my->pw; // TODO: Password nicht im Cookie speichern!!!
		return true;
	}
	else {
		return false;
	}
}

/**
 * Defines constants with some variations of session ids.
 *
 * If cookies are enabled the constants are empty. If cookies are disabled
 * 'SID2URL' contains the plain session id,
 * 'SID2URL_1' contains '?s=' and the session id,
 * 'SID2URL_x' contains '&amp;amp;s=' and the session id,
 * 'SID2URL_JS_1' contains '?s=' and the plain session id and
 * 'SID2URL_JS_x' contains '&amp;s=' and the plain session id.
 */
function sid2url($my = null) {
	if ($my == null) {
		$my = new stdClass();
	}
	if (!defined('SID2URL')) {
		if ($this->cookies) {
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

function cleanUserData($data) {
	global $gpc;
	$trust = array(
		'id', 'pw', 'regdate', 'posts', 'gender', 'birthday', 'lastvisit', 'language',
		'opt_pmnotify', 'opt_hidemail', 'opt_newsletter', 'opt_showsig', 'theme', 'confirm', // from user-table
		'ufid', // from userfields-table
		'mid', 'active', 'wiw_id', 'last_visit', 'mark', 'pwfaccess', 'settings' // from session-table
	);
	if (is_object($data)) {
		foreach ($data as $key => $value) {
			if (in_array($key, $trust) == false) {
				$data->{$key} = $gpc->prepare($value);
			}
		}
	}
	else if (is_array($data)) {
		foreach ($data as $key => $value) {
			if (in_array($key, $trust) == false) {
				$data[$key] = $gpc->prepare($value);
			}
		}
	}
	else if ($data != null) {
		trigger_error('Data passed to cleanUserData has not been not secured! Wrong data type specified.', E_USER_WARNING);
	} // else: $data == null
	return $data;
}

/**
 * Creates a 32 chars long Session-ID.
 *
 * @return String Session-ID
 */
function construct_sid() {
	$this->sid = generate_uid();
	return $this->sid;
}

/**
 * Returns an array with board-ids the user has permissions for.
 *
 * @return array Board-IDs
 */
function getBoards() {
	if (count($this->boards) == 0) {
		global $scache;
		$catbid = $scache->load('cat_bid');
		$this->boards = array_keys( $catbid->get() );
	}
	return $this->boards;
}

/**
 * Gets the permissions of a member.
 * This has to be optimized!
 *
 * @param string Komma-separated list with group-ids
 * @param boolean Default to member permissions when no valid group is specified
 * @return array Permissions
 */
function StrangerPermissions ($groups, $defaultToMemberPerms = true) {
	global $db, $scache;

	$group_cache = $scache->load('groups');
	if (count($this->statusdata) == 0) {
		$this->statusdata = $group_cache->status();
	}

	$groups = array_intersect(explode(',', $groups), array_keys($this->statusdata));
	if (count($groups) == 0) {
		$groups[] = ($defaultToMemberPerms || $my->vlogin) ? GROUP_MEMBER : GROUP_GUEST;
	}

	$keys = array_merge($this->gFields, $this->maxFields, $this->minFields);
	$permissions = array_combine($keys, array_fill(0, count($keys), array()));
	$groupdb = $group_cache->groups();
	$groups = array_intersect(array_keys($groupdb), $groups);
	if (count($groups) > 1) {
		foreach ($groups as $gid) {
			foreach ($groupdb[$gid] as $key => $value) {
				$permissions[$key][] = $value;
			}
		}
		foreach ($permissions as $key => $value) { // $value is an array!!
			if (in_array($key, $this->minFields)) {
				$permissions[$key] = (int) @min($value);
			}
			else {
				$permissions[$key] = (int) @max($value); // Do the max more elegant
			}
		}
	}
	else {
		$gid = current($groups);
		$permissions = $groupdb[$gid];
	}

	return $permissions;
}

/**
 * Gets the permissions of a member in a specified board.
 * This has to be optimized!
 *
 * @param integer Board-ID or 0 for all boards
 * @param string Komma-separated list with group-ids
 * @param boolean Default to member permissions when no valid group is specified
 * @return array Permissions
 */
function Permissions ($board = 0, $groups = null, $defaultToMemberPerms = null) {
	global $db, $my, $scache;

	if ($groups == null && isset($my->groups)) {
		$groups = $my->groups;
	}

	$group_cache = $scache->load('groups');
	if (count($this->statusdata) == 0) {
		$this->statusdata = $group_cache->status();
	}

	$groups = array_intersect(explode(',', $groups), array_keys($this->statusdata));
	if (count($groups) == 0) {
		$groups[] = ($defaultToMemberPerms || $my->vlogin) ? GROUP_MEMBER : GROUP_GUEST;
	}

	$keys = array_merge($this->gFields, $this->maxFields, $this->minFields);
	$permissions = array_combine($keys, array_fill(0, count($keys), array()));
	$groupdb = $group_cache->groups();
	$this->groups = array_intersect(array_keys($groupdb), $groups);
	if (count($this->groups) > 1) {
		foreach ($this->groups as $gid) {
			$row = $groupdb[$gid];
			foreach ($row as $key => $value) {
				$permissions[$key][] = $value;
			}
		}
		foreach ($permissions as $key => $value) { // $value is an array!!
			if (in_array($key, $this->minFields)) {
				$permissions[$key] = (int) @min($value);
			}
			else {
				$permissions[$key] = (int) @max($value); // Do the max more elegant
			}
		}
	}
	else {
		$gid = current($this->groups);
		$permissions = $groupdb[$gid];
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

		$parent_forums = $scache->load('parent_forums');
		$parent = $parent_forums->get();
		if (isset($parent[$board]) && is_array($parent[$board])) {
			$boards = $parent[$board];
		}
		else {
			$boards = array();
		}

		if (count($boards) == 0) {
			return $permissions;
		}

		$groups[] = 0;
		$fgroups_cache = $scache->load('fgroups');
		$fgroups = $fgroups_cache->getBoard($groups, iif(count($boards) == 1, $board, $boards));
		if (count($fgroups) == 0) {
			return $permissions;
		}

		$permissions2 = array();
		$fpermissions = array_combine($boards, array_fill(0, count($boards), array()));
		foreach ($fgroups as $bid => $trow) {
			foreach ($trow as $gid => $row) {
				$fpermissions[$bid][$gid] = $row;
			}
		}

		$gkeys = array();
		foreach ($boards as $bid) {
			$gkeys = array_merge($gkeys, array_intersect(array_keys($fpermissions[$bid]), $this->groups));
		}
		if (count($gkeys) == 0) {
			$gkeys[] = 0;
		}

		foreach ($boards as $bid) {
			$permissions2[$bid] = array_combine($this->fFields, array_fill(0, count($this->fFields), -1));
			foreach ($gkeys as $gid) {
				if (isset($fpermissions[$bid][$gid])) {
					foreach ($fpermissions[$bid][$gid] as $key => $value) {
						if ($value == -1 || $value > $permissions2[$bid][$key]) {
							$permissions2[$bid][$key] = $value;
						}
					}
				}
			}
		}

		$permissions3 = array();
		foreach ($this->fFields as $key) {
			$orig_key = mb_substr($key, 2, mb_strlen($key));
			foreach ($permissions2 as $bid => $arr) {
				if (isset($permissions2[$bid][$key]) && $permissions2[$bid][$key] != -1 && !isset($permissions3[$orig_key])) {
					$permissions3[$orig_key] = $arr[$key];
				}
			}
			if (isset($permissions3[$orig_key])) {
				$permissions[$orig_key] = $permissions3[$orig_key];
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

	$this->pwboards();

	return $permissions;
}

/**
 * Determines the permissions of an user for all forums.
 *
 * Returns an array ($array[Board-ID][Permission-Key]) with permissions. It is required to call $this->Permissions() before!
 * This has to be extremely optimized!
 *
 * @return array Permissions
 */
function GlobalPermissions() {
	global $db, $my, $scache;
	$parent_forums = $scache->load('parent_forums');
	$parent = $parent_forums->get();
	$boardid = array_keys($parent);

	$groups = $this->groups;
	$groups[] = 0;
	$fgroups_cache = $scache->load('fgroups');
	$fgroups = $fgroups_cache->getGlobal($groups);

	if (count($fgroups) == 0) {
		if (count($boardid) == 0) {
			return array();
		}
		return array_combine($boardid, array_fill(0, count($boardid), $this->permissions));
	}

	if (count($parent) > 0) {
		$fpermissions = array_combine($boardid, array_fill(0, count($parent), array()));
	}
	else {
		$fpermissions = array();
	}

	foreach ($fgroups as $gid => $trow) {
		foreach ($trow as $bid => $row) {
			$fpermissions[$bid][$gid] = $row;
		}
	}

	$fFieldValues = array();
	foreach ($this->fFields as $key) {
		$key = mb_substr($key, 2, mb_strlen($key));
		$fFieldValues[$key] = $this->permissions[$key];
	}
	if (count($boardid) > 0) {
		$permissions = array_combine($boardid, array_fill(0, count($boardid), $fFieldValues));
	}
	else {
		$permissions = array();
	}

	foreach ($parent as $board => $boards) {

		$gkeys = array();
		foreach ($boards as $bid) {
			$gkeys = array_merge($gkeys, array_intersect(array_keys($fpermissions[$bid]), $this->groups));
		}
		if (count($gkeys) == 0) {
			$gkeys[] = 0;
		}

		$permissions2 = array();
		foreach ($boards as $bid) {
			$permissions2[$bid] = array_combine($this->fFields, array_fill(0, count($this->fFields), -1));
			foreach ($gkeys as $gid) {
				if (isset($fpermissions[$bid][$gid])) {
					foreach ($fpermissions[$bid][$gid] as $key => $value) {
						if ($value == -1 || $value > $permissions2[$bid][$key]) {
							$permissions2[$bid][$key] = $value;
						}
					}
				}
			}
		}

		$permissions3 = array();
		foreach ($this->fFields as $key) {
			$orig_key = mb_substr($key, 2, mb_strlen($key));
			foreach ($permissions2 as $bid => $arr) {
				if (isset($permissions2[$bid][$key]) && $permissions2[$bid][$key] != -1 && !isset($permissions3[$orig_key])) {
					$permissions3[$orig_key] = $arr[$key];
				}
			}
			if (isset($permissions3[$orig_key])) {
				$permissions[$board][$orig_key] = $permissions3[$orig_key];
			}
		}

		if (isset($this->positive[$board]) && $permissions[$board]['forum'] == 0) {
			unset($this->positive[$board]);
			$this->negative[$board] = $board;
		}
		elseif (isset($this->negative[$board]) && $permissions[$board]['forum'] == 1) {
			unset($this->negative[$board]);
			$this->positive[$board] = $board;
		}

	}

	return $permissions;
}

/**
 * Determines the permissions of a moderator.
 *
 * Returns an array with the following keys and the values:
 * [0] user is moderator in this forum,
 * [1] delete posts,
 * [2] move/copy topics
 *
 * @param integer Board-ID
 * @return array Permissions
 */
function ModPermissions ($bid) {
	global $my, $db;
	if ($my->vlogin && $my->id > 0) {
		if ($this->permissions['admin'] == 1 || $this->permissions['gmod'] == 1) {
			return array(1,1,1);
		}
		else {
			$result = $db->query("SELECT p_delete, p_mc FROM {$db->pre}moderators WHERE mid = '{$my->id}' AND bid = '{$bid}'");
			if ($db->num_rows($result) > 0) {
				$row = $db->fetch_assoc($result);
				return array(1, $row['p_delete'], $row['p_mc']);
			}
			else {
				return array(0,0,0);
			}
		}
	}
	else {
		return array(0,0,0);
	}
}

function pwboards() {
	global $scache, $my;
	$catbid = $scache->load('cat_bid');
	$forums = $catbid->get();
	foreach ($forums as $bid => $data) {
		if ($data['opt'] == 'pw') {
			if (isset($my->pwfaccess[$bid]) && $my->pwfaccess[$bid] == $data['optvalue']) {
				$this->positive[$bid] = $bid;
			}
			else {
				$this->negative[$bid] = $bid;
			}
		}
	}
}

/**
 * Constructs the best sql query.
 *
 * If the second parameter is 0 then the 'AND' is added before the query.
 * If the second parameter is 1 then the 'AND' is added after the query.
 *
 * @param string field name
 * @param integer
 * @param array board ids or null for all boards
 * @return string part of sql query
 */
function sqlinboards($spalte, $r_and = 0, $boards = null) {
	$negative = $this->negative;
	$positive = $this->positive;
	if ($boards != null) {
		// Positive
		$positive = array_intersect($positive, $boards);
		// Negative
		$all = $this->getBoards();
		$temp = array_diff($all, $boards);
		$negative = array_merge($negative, $temp);
	}

	if ($this->permissions['forum'] == 1 && count($negative) > 1) {
		$ids = implode(',', $negative);
		$sql = " {$spalte} NOT IN ({$ids}) ";
	}
	elseif ($this->permissions['forum'] == 1 && count($negative) == 1) {
		$nid = current($negative);
		$sql = " {$spalte} != {$nid} ";
	}
	elseif ($this->permissions['forum'] == 1 && count($negative) == 0) {
		$sql = ' 1=1 ';
	}
	elseif ($this->permissions['forum'] == 0 && count($positive) > 1) {
		$ids = implode(',', $positive);
		$sql = " {$spalte} IN ({$ids}) ";
	}
	elseif ($this->permissions['forum'] == 0 && count($positive) == 1) {
		$pid = current($positive);
		$sql = " {$spalte} = {$pid} ";
	}
	elseif ($this->permissions['forum'] == 0 && count($positive) == 0) {
		$sql = ' 1=0 ';
	}
	else {
		trigger_error('Hacking Attempt: Groups (Positive/Negative)', E_USER_ERROR);
	}

	if ($r_and == 1) {
		$sql = $sql.' AND ';
	}
	else {
		$sql = ' AND '.$sql;
	}
	return $sql;
}

function setTopicRead($tid, $parents) {
	global $my, $db;
	$my->mark['t'][$tid] = time();

	// Erstelle ein Array mit schon gelesenen Beiträgen
	$inkeys = implode(',', array_keys($my->mark['t']));
	foreach ($parents as $tf) {
		$result = $db->query("SELECT COUNT(*) FROM {$db->pre}topics WHERE board = '{$tf}' AND last >= '{$my->clv}' AND id NOT IN({$inkeys})");
		$row = $db->fetch_num($result);
		if ($row[0] == 0) {
			$my->mark['f'][$tf] = time();
		}
	}
}

function setForumRead($fid) {
	global $db, $my;
	$result = $db->query("SELECT id FROM {$db->pre}topics WHERE board = '{$fid}' AND last >= '{$my->clv}'");
	while ($row = $db->fetch_assoc($result)) {
		$my->mark['t'][$row['id']] = time();
	}
	$my->mark['f'][$fid] = time();
}

/**
 * Sets all posts read (sets lastvisit time to now).
 *
 * Returns 'true' on success and 'false' on failure.
 *
 * @return boolean
 */
function setAllRead() {
	global $my, $db;
	if ($my->vlogin) {
		$db->query ("UPDATE {$db->pre}session SET lastvisit = '".time()."' WHERE mid = '$my->id'");
	}
	else {
		$db->query ("UPDATE {$db->pre}session SET lastvisit = '".time()."' WHERE sid = '$this->sid'");
	}
	// Todo: Save some queries!
	// This queries can be saved normally, because it will be saved with updatelogged (in ok/error funcs) later!
	$my->mark = array();
	$my->clv = time();
	if ($db->affected_rows() > 0) {
		return true;
	}
	else {
		return false;
	}
}

function isForumRead($fid, $last_change) {
	global $my;
	return ((isset($my->mark['f'][$fid]) && $my->mark['f'][$fid] >= $last_change) || $last_change <= $my->clv);
}

function isTopicRead($tid, $last_change) {
	global $my;
	return ((isset($my->mark['t'][$tid]) && $my->mark['t'][$tid] >= $last_change) || $last_change <= $my->clv);
}

}
?>