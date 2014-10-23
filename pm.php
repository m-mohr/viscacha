<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2006  Matthias Mohr, MaMo Net
	
	Author: Matthias Mohr
	Publisher: http://www.mamo-net.de
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

error_reporting(E_ALL);

DEFINE('SCRIPTNAME', 'pm');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();
$my->p = $slog->Permissions();

if ($my->p['pm'] == 0 || !$my->vlogin) {
	errorLogin();
}
    
$breadcrumb->Add($lang->phrase('editprofile_pm'), 'pm.php'.SID2URL_x);

if ($_GET['action'] == 'show') {

	$bbcode = initBBCodes();
	
	$result = $db->query("
	SELECT p.dir, p.status, p.id, p.topic, p.comment, p.date, u.fullname, u.groups, u.hp, u.pic, u.mail, u.regdate, u.location, u.name, p.pm_from as mid 
	FROM {$db->pre}pm AS p LEFT JOIN {$db->pre}user AS u ON p.pm_from=u.id 
	WHERE p.pm_to = '".$my->id."' AND p.id = '{$_GET['id']}' 
	ORDER BY p.date ASC",__LINE__,__FILE__);
	if ($db->num_rows() != 1) {
		error($lang->phrase('query_string_error'), 'pm.php'.SID2URL_1);
	}
	
	$row = $gpc->prepare($db->fetch_assoc($result));
	
	$db->query("UPDATE {$db->pre}pm SET status = '1' WHERE id = ".$_GET['id'],__LINE__,__FILE__);

	if (empty($row['name'])) {
		$row['regdate'] = '-';
		$row['groups'] = 'guest';
		$memberdata = cache_memberdata();
		if (isset($memberdata[$row['mid']])) {
			$row['name'] = $memberdata[$row['mid']];
		}
		else {
			$row['name'] = $lang->phrase('fallback_no_username');
		}
		$row['location'] = '-';
	}
	else {
		$row['regdate'] = gmdate($lang->phrase('dformat2'), times($row['regdate']));
	}
	$bbcode->setSmileys(1);
	$bbcode->setReplace($config['wordstatus']);
	$bbcode->setAuthor($row['mid']);
    $row['comment'] = $bbcode->parse($row['comment']);
	$row['date'] = str_date($lang->phrase('dformat1'), times($row['date']));
	$row['read'] = iif($row['status'] == 1,'old','new');
	$row['level'] = $slog->getStatus($row['groups'], ', ');
	
	$breadcrumb->Add(get_pmdir($row['dir']), 'pm.php?action=browse&amp;id='.$row['dir'].SID2URL_x);
	$breadcrumb->Add($lang->phrase('pm_show'));
	
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	echo $tpl->parse("pm/menu");
	$mymodules->load('pm_show_top');
	echo $tpl->parse("pm/show");
	$mymodules->load('pm_show_bottom');
}
elseif ($_GET['action'] == "massmanage") {
	$breadcrumb->Add($lang->phrase('pm_massmanage'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	echo $tpl->parse("pm/menu");
	$querystr = implode(',', $_POST['delete']);
	if (!empty($_POST['move2'])) {
		if ($_POST['id'] == 3) {
			$verz = get_pmdir(1);
		}
		else {
			$verz = get_pmdir(3);
		}
		echo $tpl->parse("pm/move");
	}
	else {
		echo $tpl->parse("pm/delete");
	}
}
elseif ($_GET['action'] == "massdelete") {
	$breadcrumb->Add($lang->phrase('pm_massmanage'));
	$deleteids = explode(',', $_GET['data']);
	$deleteids = $gpc->save_int($deleteids);
	if (count($deleteids) > 0) {
		$db->query('DELETE FROM '.$db->pre.'pm WHERE pm_to = "'.$my->id.'" AND id IN ('.implode(',',$deleteids).')',__LINE__,__FILE__);
		$anz = $db->affected_rows();		
		ok($lang->phrase('x_entries_deleted'), 'pm.php'.SID2URL_1);
	}
	else {
		error($lang->phrase('query_string_error'));
	}
}
elseif ($_GET['action'] == "massmove") {
	$breadcrumb->Add($lang->phrase('pm_massmanage'));
	$deleteids = explode(',', $_GET['data']);
	$deleteids = $gpc->save_int($deleteids);
	if ($_GET['id'] == 3) {
		$verz = 1;
	}
	else {
		$verz = 3;
	}
	if (count($deleteids) > 0) {
		$db->query('UPDATE '.$db->pre.'pm SET dir = "'.$verz.'" WHERE pm_to = "'.$my->id.'" AND id IN ('.implode(',',$deleteids).')',__LINE__,__FILE__);
		$anz = $db->affected_rows();		
		ok($lang->phrase('x_entries_moved'), 'pm.php?action=browse&amp;id='.$_GET['id'].SID2URL_1);
	}
	else {
		error($lang->phrase('query_string_error'));
	}
}
elseif ($_GET['action'] == "delete") {
    if (empty($_GET['id'])) {
    	error($lang->phrase('query_string_error'));
    }
	$breadcrumb->Add($lang->phrase('pm_manage'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	echo $tpl->parse("pm/menu");
	echo $tpl->parse("pm/delete");
}
elseif ($_GET['action'] == "delete2") {
	$breadcrumb->Add($lang->phrase('pm_manage'));
    if (empty($_GET['id'])) {
    	error($lang->phrase('query_string_error'));
    }
    $db->query ("DELETE FROM {$db->pre}pm WHERE id = '".$_GET['id']."' AND pm_to = '$my->id'",__LINE__,__FILE__);
    $anz = $db->affected_rows();
    ok($lang->phrase('x_entries_deleted'),'pm.php'.SID2URL_1);
}
elseif ($_GET['action'] == "save") {
    $error = array();
	if (flood_protect() == FALSE) {
    	$error[] = $lang->phrase('flood_control');
    }
	if (strxlen($_POST['comment']) > $config['maxpostlength']) {
    	$error[] = $lang->phrase('comment_too_long');
    }
	if (strxlen($_POST['comment']) < $config['minpostlength']) {
    	$error[] = $lang->phrase('comment_too_short');
    }
	if (strxlen($_POST['topic']) > $config['maxtitlelength']) {
    	$error[] = $lang->phrase('title_too_long');
    }
	if (strxlen($_POST['topic']) < $config['mintitlelength']) {
    	$error[] = $lang->phrase('title_too_short');
    }

	if (!is_id($_POST['name'])) {
		$result = $db->query('SELECT id FROM '.$db->pre.'user WHERE name="'.$_POST['name'].'" LIMIT 1',__LINE__,__FILE__);
		$user = $db->fetch_array($result);
		if ($user[0] > 0) {
			$_POST['name'] = $user[0];
		}
		else {
			$error[] = $lang->phrase('pm_toname_notfound');
		}
	}

	$bbcode = initBBCodes();
	$_POST['topic'] = $bbcode->parseTitle($_POST['topic']);

	if (count($error) > 0 || !empty($_POST['Preview2'])) {
		$data = array(
			'topic' => $_POST['topic'],
			'comment' => $_POST['comment'],
			'name' => $_POST['name'],
			'outgoing' => $_POST['temp']
		);
		$fid = save_error_data($data);
		if (!empty($_POST['Preview2'])) {
			viscacha_header("Location: pm.php?action=preview&fid=".$fid.SID2URL_JS_x);
		}
		else {
			error($error,"pm.php?action=new&amp;fid=".$fid.SID2URL_x);
		}
	}
	else {
		set_flood();
		$date = time();	
		
		$db->query("INSERT INTO {$db->pre}pm (topic,pm_from,pm_to,comment,date,dir) VALUES ('{$_POST['topic']}','{$my->id}','{$_POST['name']}','{$_POST['comment']}','{$date}','1')",__LINE__,__FILE__); 
		if ($_POST['temp'] == 1) {
			$db->query("INSERT INTO {$db->pre}pm (topic,pm_from,pm_to,comment,date,dir,status) VALUES ('{$_POST['topic']}','{$_POST['name']}','{$my->id}','{$_POST['comment']}','{$date}','2','1')",__LINE__,__FILE__); 
		}
		
		$result = $db->query('SELECT name, mail, opt_pmnotify FROM '.$db->pre.'user WHERE id = '.$_POST['name'],__LINE__,__FILE__);
		$row = $gpc->prepare($db->fetch_assoc($result));
		if ($row['opt_pmnotify'] == 1) {
			$maildata = $lang->get_mail('newpm');
			$to = array('0' => array('name' => $row['name'], 'mail' => $row['mail']));
			$from = array();
			xmail($to, $from, $maildata['title'], $maildata['comment']);
		}
		
		ok($lang->phrase('newpm_success'),"pm.php".SID2URL_1);
	}
}
elseif ($_GET['action'] == "new" || $_GET['action'] == "preview" || $_GET['action'] == "quote" || $_GET['action'] == 'reply') {
	$breadcrumb->Add($lang->phrase('pm_new_title'));
	echo $tpl->parse("header");
	
    $bbcode = initBBCodes();
	if (strlen($_GET['fid']) == 32) {
		$data = $gpc->prepare(import_error_data($_GET['fid']));
		if ($_GET['action'] == 'preview') {
			$bbcode->setSmileys(1);
			$bbcode->setReplace($config['wordstatus']);
       		$data['formatted_comment'] = $bbcode->parse($data['comment']);
		}
	}
	elseif ($_GET['action'] == 'quote' || $_GET['action'] == 'reply') {
		$result=$db->query('SELECT topic, comment FROM '.$db->pre.'pm WHERE id="'.$_GET['id'].'" LIMIT 1',__LINE__,__FILE__);
		$info = $gpc->prepare($db->fetch_assoc($result));
		$data = array(
			'name' => $_GET['name'],
			'topic' => $lang->phrase('reply_prefix').$info['topic'],
			'outgoing' => 1
		);
		if ($_GET['action'] == 'quote') {
			$info['comment'] = str_replace("[br]", "\n", $info['comment']);
			$info['comment'] = preg_replace('/\[hide\](.+?)\[\/hide\]/is', '', $info['comment']);
			$data['comment'] = "[QUOTE]".$info['comment']."[/QUOTE]";
		}
		else {
			$data['comment'] = '';
		}

	}
	else {
		$data = array(
			'name' => $_GET['name'],
			'comment' => '',
			'topic' => '',
			'outgoing' => 1
		);
		if (is_id($_GET['id'])) {
			$data['name'] = $_GET['id'];
		}
	}

	if (is_id($data['name'])) {
		$memberdata = cache_memberdata();
		if (isset($memberdata[$data['name']])) {
			$showname = $memberdata[$data['name']];
		}
		else {
			$data['name'] = '';
		}
	}

    echo $tpl->parse("menu");
	$inner['smileys'] = $bbcode->getsmileyhtml($config['smileysperrow']);
	$inner['bbhtml'] = $bbcode->getbbhtml();
	echo $tpl->parse("pm/menu");
	$mymodules->load('pm_new_top');
	echo $tpl->parse("pm/new");
    $mymodules->load('pm_new_bottom');
}
elseif ($_GET['action'] == "browse") {

	$dir_name = get_pmdir($_GET['id']);
	if (!$dir_name) {
		error($lang->phrase('query_string_error'), 'pm.php'.SID2URL_1);
	}
	$breadcrumb->Add($dir_name);
	echo $tpl->parse("header");
	echo $tpl->parse("menu");

	$memberdata = cache_memberdata();

    $count = $db->fetch_array($db->query("SELECT COUNT(*) FROM {$db->pre}pm WHERE pm_to = '".$my->id."' AND dir = '".$_GET['id']."'",__LINE__,__FILE__));
    $temp = pages($count[0], 'pmzahl', 'pm.php?action=browse&amp;id='.$_GET['id'].'&amp;');
    $start = $_GET['page']*$config['pmzahl'];
    $start = $start-$config['pmzahl'];

    $inner['index_bit'] = '';
	$result = $db->query("SELECT id, pm_from, topic, date, status, pm_to FROM {$db->pre}pm WHERE pm_to = '".$my->id."' AND dir = '".$_GET['id']."' ORDER BY date DESC LIMIT $start, ".$config['pmzahl'],__LINE__,__FILE__);
	
	echo $tpl->parse("pm/menu");
    $mymodules->load('pm_browse_top');
	
	
	while ($row = $db->fetch_assoc($result)) {
		$row['topic'] = $gpc->prepare($row['topic']);
		$row['date'] = str_date($lang->phrase('dformat1'), times($row['date']));
		if ($row['status'] == 0) {
	    	$row['alt'] = $lang->phrase('pm_newicon');
	    	$row['src'] = $tpl->img('dir_open2');
		}
		else {
	    	$row['alt'] = $lang->phrase('pm_oldicon');
	    	$row['src'] = $tpl->img('dir_open');
		}
		if (isset($memberdata[$row['pm_from']])) {
			$row['name'] = $memberdata[$row['pm_from']];
		}
		else {
			$row['name'] = $lang->phrase('fallback_no_username');
		}
		$mymodules->load('pm_browse_top');
		$inner['index_bit'] .= $tpl->parse("pm/browse_bit");
	}
	echo $tpl->parse("pm/browse");
	$mymodules->load('pm_browse_bottom');
}
else {
	$breadcrumb->resetUrl();
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	echo $tpl->parse("pm/menu");

	$memberdata = cache_memberdata();

	$time = time()-60*60*24*7;
	$timestamp = $time > $my->clv ? $my->clv : $time;

	$result = $db->query("SELECT id, pm_from, topic, date, status, pm_to FROM {$db->pre}pm WHERE pm_to = '".$my->id."' AND (date > {$timestamp} OR  status = '0') AND dir != '2' ORDER BY date DESC",__LINE__,__FILE__);
	$count = $db->num_rows($result);
	$inner['index_bit'] = '';
	$inner['index_bit_old'] = '';
	$ib = 0;
	$ibo = 0;
	$mymodules->load('pm_index_top');
	while ($row = $db->fetch_assoc($result)) {
		$row['topic'] = $gpc->prepare($row['topic']);
		$row['date_str'] = str_date($lang->phrase('dformat1'), times($row['date']));
		if ($row['status'] == 0) {
	    	$row['alt'] = $lang->phrase('pm_newicon');
	    	$row['src'] = $tpl->img('dir_open2');
		}
		else {
	    	$row['alt'] = $lang->phrase('pm_oldicon');
	    	$row['src'] = $tpl->img('dir_open');
		}
		if (isset($memberdata[$row['pm_from']])) {
			$row['name'] = $memberdata[$row['pm_from']];
		}
		else {
			$row['name'] = $lang->phrase('fallback_no_username');
		}
		$mymodules->load('pm_index_bit');
		if ($row['date'] >= $my->clv || $row['status'] == '0') {
			$ib++;
			$inner['index_bit'] .= $tpl->parse("pm/index_bit");
		}
		else {
			$ibo++;
			$inner['index_bit_old'] .= $tpl->parse("pm/index_bit");
		}
	}
	
	echo $tpl->parse("pm/index");
	$mymodules->load('pm_index_bottom');
}

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();		
?>
