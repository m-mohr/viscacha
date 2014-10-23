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

	BBProfile($bbcode);

	$sql_select = iif($config['pm_user_status'] == 1, ", IF (s.mid > 0, 1, 0) AS online");
	$sql_join = iif($config['pm_user_status'] == 1, "LEFT JOIN {$db->pre}session AS s ON s.mid = u.id");	
	($code = $plugins->load('pm_show_query')) ? eval($code) : null;
	$result = $db->query("
	SELECT 
		   p.dir, p.status, p.id, p.topic, p.comment, p.date, p.pm_from as mid, 
		   u.id as mid, u.name, u.mail, u.regdate, u.fullname, u.hp, u.signature, u.location, u.gender, u.birthday, u.pic, u.lastvisit, u.icq, u.yahoo, u.aol, u.msn, u.jabber, u.skype, u.groups, 
		   f.* {$sql_select}
	FROM {$db->pre}pm AS p 
		LEFT JOIN {$db->pre}user AS u ON p.pm_from = u.id 
		LEFT JOIN {$db->pre}userfields AS f ON u.id = f.ufid 
		{$sql_join}
	WHERE p.pm_to = '{$my->id}' AND p.id = '{$_GET['id']}' 
	ORDER BY p.date ASC
	",__LINE__,__FILE__);
	if ($db->num_rows() != 1) {
		error($lang->phrase('query_string_error'), 'pm.php'.SID2URL_1);
	}
	
	$row = $gpc->prepare($db->fetch_assoc($result));
	
	if ($row['status'] == '0') {
		$db->query("UPDATE {$db->pre}pm SET status = '1' WHERE id = '{$row['id']}'",__LINE__,__FILE__);
	}

	if (empty($row['name'])) {
		$row['regdate'] = '-';
		$row['groups'] = 'guest';
		
		$memberdata_obj = $scache->load('memberdata');
		$memberdata = $memberdata_obj->get();

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
	if ($config['pm_user_status'] == 1) {
		$row['lang_online'] = $lang->phrase('profile_'.iif($row['online'] == 1, 'online', 'offline'));
	}

	if ($my->opt_showsig == 1) {
		BBProfile($bbcode, 'signature');
		$row['signature'] = $bbcode->parse($row['signature']);
	}

	($code = $plugins->load('pm_show_prepared')) ? eval($code) : null;

	$breadcrumb->Add(get_pmdir($row['dir']), 'pm.php?action=browse&amp;id='.$row['dir'].SID2URL_x);
	$breadcrumb->Add($lang->phrase('pm_show'));
	
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	echo $tpl->parse("pm/menu");
	echo $tpl->parse("pm/show");
	($code = $plugins->load('pm_show_end')) ? eval($code) : null;
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
		($code = $plugins->load('pm_massmanage_move')) ? eval($code) : null;
		echo $tpl->parse("pm/move");
	}
	else {
		($code = $plugins->load('pm_massmanage_delete')) ? eval($code) : null;
		echo $tpl->parse("pm/delete");
	}
}
elseif ($_GET['action'] == "massdelete") {
	$breadcrumb->Add($lang->phrase('pm_massmanage'));
	$deleteids = explode(',', $_GET['data']);
	$deleteids = $gpc->save_int($deleteids);
	if (count($deleteids) > 0) {
		($code = $plugins->load('pm_massdelete_query')) ? eval($code) : null;
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
		($code = $plugins->load('pm_massmove_query')) ? eval($code) : null;
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
	($code = $plugins->load('pm_delete2_query')) ? eval($code) : null;
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
		$user = $db->fetch_num($result);
		if ($user[0] > 0) {
			$_POST['name'] = $user[0];
		}
		else {
			$error[] = $lang->phrase('pm_toname_notfound');
		}
	}

	($code = $plugins->load('pm_save_errorhandling')) ? eval($code) : null;

	BBProfile($bbcode);
	$_POST['topic'] = $bbcode->parseTitle($_POST['topic']);

	if (count($error) > 0 || !empty($_POST['Preview'])) {
		$data = array(
			'topic' => $_POST['topic'],
			'comment' => $_POST['comment'],
			'name' => $_POST['name'],
			'outgoing' => $_POST['temp']
		);
		($code = $plugins->load('pm_save_errordata')) ? eval($code) : null;
		$fid = save_error_data($data);
		if (!empty($_POST['Preview'])) {
			$slog->updatelogged();
			$db->close();
			viscacha_header("Location: pm.php?action=preview&fid=".$fid.SID2URL_JS_x);
			exit;
		}
		else {
			error($error,"pm.php?action=new&amp;fid=".$fid.SID2URL_x);
		}
	}
	else {
		set_flood();
		$date = time();	
		
		($code = $plugins->load('pm_save_queries')) ? eval($code) : null;
		$db->query("
		INSERT INTO {$db->pre}pm (topic,pm_from,pm_to,comment,date,dir) 
		VALUES ('{$_POST['topic']}','{$my->id}','{$_POST['name']}','{$_POST['comment']}','{$date}','1')
		",__LINE__,__FILE__); 
		
		if ($_POST['temp'] == 1) {
			$db->query("
			INSERT INTO {$db->pre}pm (topic,pm_from,pm_to,comment,date,dir,status) 
			VALUES ('{$_POST['topic']}','{$_POST['name']}','{$my->id}','{$_POST['comment']}','{$date}','2','1')
			",__LINE__,__FILE__); 
		}
		
		$result = $db->query('SELECT name, mail, opt_pmnotify FROM '.$db->pre.'user WHERE id = '.$_POST['name'],__LINE__,__FILE__);
		$row = $gpc->prepare($db->fetch_assoc($result));
		if ($row['opt_pmnotify'] == 1) {
			$maildata = $lang->get_mail('newpm');
			$to = array('0' => array('name' => $row['name'], 'mail' => $row['mail']));
			$from = array();
			xmail($to, $from, $maildata['title'], $maildata['comment']);
		}
		
		($code = $plugins->load('pm_save_end')) ? eval($code) : null;
		ok($lang->phrase('newpm_success'),"pm.php".SID2URL_1);
	}
}
elseif ($_GET['action'] == "new" || $_GET['action'] == "preview" || $_GET['action'] == "quote" || $_GET['action'] == 'reply') {
	$breadcrumb->Add($lang->phrase('pm_new_title'));
	echo $tpl->parse("header");
	
	BBProfile($bbcode);
	
	($code = $plugins->load('pm_compose_start')) ? eval($code) : null;
	
	if (strlen($_GET['fid']) == 32) {
		$data = $gpc->prepare(import_error_data($_GET['fid']));
		if ($_GET['action'] == 'preview') {
			$bbcode->setSmileys(1);
			$bbcode->setReplace($config['wordstatus']);
	   		$data['formatted_comment'] = $bbcode->parse($data['comment']);
		}
	}
	elseif ($_GET['action'] == 'quote' || $_GET['action'] == 'reply') {
		$result = $db->query('SELECT topic, comment FROM '.$db->pre.'pm WHERE id="'.$_GET['id'].'" LIMIT 1',__LINE__,__FILE__);
		$info = $gpc->prepare($db->fetch_assoc($result));
		$data = array(
			'name' => $_GET['name'],
			'topic' => $lang->phrase('reply_prefix').$info['topic'],
			'outgoing' => 1
		);
		if ($_GET['action'] == 'quote') {
			$info['comment'] = str_replace("[br]", "\n", $info['comment']);
			$info['comment'] = preg_replace('/\[hide\](.+?)\[\/hide\]/is', '', $info['comment']);
			$data['comment'] = "[quote]".$info['comment']."[/quote]";
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
	($code = $plugins->load('pm_compose_data')) ? eval($code) : null;

	if (is_id($data['name'])) {
		$memberdata_obj = $scache->load('memberdata');
		$memberdata = $memberdata_obj->get();
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
	($code = $plugins->load('pm_compose_prepared')) ? eval($code) : null;
	echo $tpl->parse("pm/new");
	($code = $plugins->load('pm_compose_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "browse") {

	$dir_name = get_pmdir($_GET['id']);
	if (!$dir_name) {
		error($lang->phrase('query_string_error'), 'pm.php'.SID2URL_1);
	}
	$breadcrumb->Add($dir_name);
	echo $tpl->parse("header");
	echo $tpl->parse("menu");

	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();

	($code = $plugins->load('pm_browse_start')) ? eval($code) : null;

	
	$result = $db->query("
	SELECT COUNT(*) 
	FROM {$db->pre}pm 
	WHERE pm_to = '{$my->id}' AND dir = '{$_GET['id']}'
	",__LINE__,__FILE__);
	$count = $db->fetch_num($result);
	
	$temp = pages($count[0], $config['pmzahl'], 'pm.php?action=browse&amp;id='.$_GET['id'].'&amp;', $_GET['page']);
	$start = $_GET['page']*$config['pmzahl'];
	$start = $start-$config['pmzahl'];

	$inner['index_bit'] = '';
	
	($code = $plugins->load('pm_browse_query')) ? eval($code) : null;
	$result = $db->query("
	SELECT id, pm_from, topic, date, status, pm_to 
	FROM {$db->pre}pm 
	WHERE pm_to = '{$my->id}' AND dir = '{$_GET['id']}' 
	ORDER BY date DESC 
	LIMIT $start, {$config['pmzahl']}
	",__LINE__,__FILE__);
	
	echo $tpl->parse("pm/menu");
	
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
		($code = $plugins->load('pm_browse_entry_prepared')) ? eval($code) : null;
		$inner['index_bit'] .= $tpl->parse("pm/browse_bit");
	}
	echo $tpl->parse("pm/browse");
	($code = $plugins->load('pm_browse_end')) ? eval($code) : null;
}
else {
	$breadcrumb->resetUrl();
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	echo $tpl->parse("pm/menu");

	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();

	$time = time()-60*60*24*7;
	$timestamp = $time > $my->clv ? $my->clv : $time;
	
	($code = $plugins->load('pm_index_start')) ? eval($code) : null;

	$result = $db->query("
	SELECT id, pm_from, topic, date, status, pm_to 
	FROM {$db->pre}pm 
	WHERE pm_to = '{$my->id}' AND (date > {$timestamp} OR  status = '0') AND dir != '2' 
	ORDER BY date DESC
	",__LINE__,__FILE__);
	
	$count = $db->num_rows($result);
	$inner['index_bit'] = '';
	$inner['index_bit_old'] = '';
	$ib = 0;
	$ibo = 0;
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
		($code = $plugins->load('pm_index_entry_prepared')) ? eval($code) : null;
		if ($row['date'] >= $my->clv || $row['status'] == '0') {
			$ib++;
			$inner['index_bit'] .= $tpl->parse("pm/index_bit");
		}
		else {
			$ibo++;
			$inner['index_bit_old'] .= $tpl->parse("pm/index_bit");
		}
	}
	
	($code = $plugins->load('pm_index_prepared')) ? eval($code) : null;
	echo $tpl->parse("pm/index");
	($code = $plugins->load('pm_index_end')) ? eval($code) : null;
}

($code = $plugins->load('pm_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();		
?>
