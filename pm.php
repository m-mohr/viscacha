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

error_reporting(E_ALL);

define('SCRIPTNAME', 'pm');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$my->p = $slog->Permissions();

if ($my->p['pm'] == 0 || !$my->vlogin) {
	errorLogin();
}

Breadcrumb::universal()->add($lang->phrase('editprofile_pm'), 'pm.php'.SID2URL_x);

if ($_GET['action'] == 'show') {

	BBProfile($bbcode);

	($code = $plugins->load('pm_show_query')) ? eval($code) : null;
	$result = $db->query("
	SELECT
		   p.dir, p.status, p.id, p.topic, p.comment, p.date, p.pm_from as mid,
		   p.pm_from as mid, u.name, u.mail, u.regdate, u.fullname, u.hp, u.signature, u.location, u.gender, u.birthday, u.pic, u.lastvisit, u.groups, u.posts, u.deleted_at,
		   f.*, IF (s.mid > 0, 1, 0) AS online
	FROM {$db->pre}pm AS p
		LEFT JOIN {$db->pre}user AS u ON p.pm_from = u.id
		LEFT JOIN {$db->pre}userfields AS f ON u.id = f.ufid
		LEFT JOIN {$db->pre}session AS s ON s.mid = u.id
	WHERE p.pm_to = '{$my->id}' AND p.id = '{$_GET['id']}'
	ORDER BY p.date ASC
	");
	if ($db->num_rows($result) != 1) {
		error($lang->phrase('query_string_error'), 'pm.php'.SID2URL_1);
	}

	$row = $slog->cleanUserData($db->fetch_assoc($result));

	if ($row['status'] == '0') {
		$db->query("UPDATE {$db->pre}pm SET status = '1' WHERE id = '{$row['id']}'");
	}

	$bbcode->setSmileys(1);
	$bbcode->setAuthor($row['mid']);
	$row['comment'] = $bbcode->parse($row['comment']);
	$row['read'] = iif($row['status'] == 1,'old','new');
	$row['level'] = $slog->getStatus($row['groups'], ', ', $row['deleted_at'] !== null);
	if ($row['dir'] == 2) {
		$row['fullname'] = $my->fullname;
	}
	if ($my->opt_showsig == 1) {
		BBProfile($bbcode, 'signature');
		if ($row['dir'] == 2) {
			$row['signature'] = $bbcode->parse($my->signature);
		}
		else {
			$row['signature'] = $bbcode->parse($row['signature']);
		}
	}

	if ((!empty($row['fullname']) && $config['fullname_posts'] == 1) || (!empty($row['signature']) && $my->opt_showsig == 1)) {
		$bottom = true;
	}
	else {
		$bottom = false;
	}

	($code = $plugins->load('pm_show_prepared')) ? eval($code) : null;

	Breadcrumb::universal()->add(get_pmdir($row['dir']), 'pm.php?action=browse&amp;id='.$row['dir'].SID2URL_x);
	Breadcrumb::universal()->add($lang->phrase('pm_show'));

	echo $tpl->parse("header");
	echo $tpl->parse("pm/show");
	($code = $plugins->load('pm_show_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "massmanage") {
	Breadcrumb::universal()->add($lang->phrase('pm_massmanage'));
	echo $tpl->parse("header");
	$data = implode(',', $_POST['delete']);
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
	Breadcrumb::universal()->add($lang->phrase('pm_massmanage'));
	$deleteids = explode(',', $_GET['data']);
	$deleteids = $gpc->save_int($deleteids);
	if (count($deleteids) > 0) {
		($code = $plugins->load('pm_massdelete_query')) ? eval($code) : null;
		$ids = implode(',', $deleteids);
		$db->query("DELETE FROM {$db->pre}pm WHERE pm_to = '{$my->id}' AND id IN ({$ids})");
		$anz = $db->affected_rows();
		ok($lang->phrase('x_entries_deleted'), 'pm.php'.SID2URL_1);
	}
	else {
		error($lang->phrase('query_string_error'));
	}
}
elseif ($_GET['action'] == "massmove") {
	Breadcrumb::universal()->add($lang->phrase('pm_massmanage'));
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
		$ids = implode(',', $deleteids);
		$db->query("UPDATE {$db->pre}pm SET dir = '{$verz}' WHERE pm_to = '{$my->id}' AND dir != '2' AND id IN ({$ids})");
		$anz = $db->affected_rows();
		ok($lang->phrase('x_entries_moved'), 'pm.php?action=browse&amp;id='.$_GET['id'].SID2URL_x);
	}
	else {
		error($lang->phrase('query_string_error'));
	}
}
elseif ($_GET['action'] == "delete") {
	$result = $db->query ("SELECT id FROM {$db->pre}pm WHERE id = '{$_GET['id']}' AND pm_to = '{$my->id}' LIMIT 1");
	if ($db->num_rows($result) != 1) {
		error($lang->phrase('pm_not_found'));
	}
	$info = $db->fetch_assoc($result);
	Breadcrumb::universal()->add($lang->phrase('pm_manage'));
	echo $tpl->parse("header");
	$data = $info['id'];
	echo $tpl->parse("pm/delete");
}
elseif ($_GET['action'] == "delete2") {
	Breadcrumb::universal()->add($lang->phrase('pm_manage'));
	if (empty($_GET['id'])) {
		error($lang->phrase('query_string_error'));
	}
	($code = $plugins->load('pm_delete2_query')) ? eval($code) : null;
	$db->query ("DELETE FROM {$db->pre}pm WHERE id = '{$_GET['id']}' AND pm_to = '{$my->id}'");
	$anz = $db->affected_rows();
	ok($lang->phrase('x_entries_deleted'),'pm.php'.SID2URL_1);
}
elseif ($_GET['action'] == "save") {
	$error = array();
	if (flood_protect() == FALSE) {
		$error[] = $lang->phrase('flood_control');
	}
	if (mb_strlen($_POST['comment']) > $config['maxpostlength']) {
		$error[] = $lang->phrase('comment_too_long');
	}
	if (mb_strlen($_POST['comment']) < $config['minpostlength']) {
		$error[] = $lang->phrase('comment_too_short');
	}
	if (mb_strlen($_POST['topic']) > $config['maxtitlelength']) {
		$error[] = $lang->phrase('title_too_long');
	}
	if (mb_strlen($_POST['topic']) < $config['mintitlelength']) {
		$error[] = $lang->phrase('title_too_short');
	}

	$name_id = 0;
	if (mb_strlen($_POST['name']) > 0) {
		$result = $db->query('SELECT id FROM '.$db->pre.'user WHERE deleted_at IS NULL AND name = "'.$_POST['name'].'" LIMIT 1');
		$user = $db->fetch_num($result);
		if (!empty($user[0])) {
			$name_id = $user[0];
		}
	}
	if (!is_id($name_id) || empty($_POST['name'])) {
		$error[] = $lang->phrase('pm_toname_notfound');
	}

	($code = $plugins->load('pm_save_errorhandling')) ? eval($code) : null;

	BBProfile($bbcode);
	$_POST['topic'] = $bbcode->parseTitle($_POST['topic']);

	if (count($error) > 0 || !empty($_POST['Preview'])) {
		$data = array(
			'topic' => $_POST['topic'],
			'comment' => $_POST['comment'],
			'name' => $_POST['name'],
			'name_id' => $name_id,
			'outgoing' => $_POST['temp']
		);
		($code = $plugins->load('pm_save_errordata')) ? eval($code) : null;
		$fid = save_error_data($data);
		if (!empty($_POST['Preview'])) {
			$slog->updatelogged();
			$db->close();
			sendStatusCode(302, $config['furl'].'/pm.php?action=preview&fid='.$fid.SID2URL_JS_x);
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
		VALUES ('{$_POST['topic']}','{$my->id}','{$name_id}','{$_POST['comment']}','{$date}','1')
		");

		if ($_POST['temp'] == 1) {
			$db->query("
			INSERT INTO {$db->pre}pm (topic,pm_from,pm_to,comment,date,dir,status)
			VALUES ('{$_POST['topic']}','{$name_id}','{$my->id}','{$_POST['comment']}','{$date}','2','1')
			");
		}

		$lang_dir = $lang->getdir(true);
		$result = $db->query("SELECT name, mail, opt_pmnotify, language FROM {$db->pre}user WHERE deleted_at IS NULL AND id = '{$name_id}'");
		$row = $slog->cleanUserData($db->fetch_assoc($result));
		if ($row['opt_pmnotify'] == 1) {
			$lang->setdir($row['language']);
			$maildata = $lang->get_mail('newpm');
			$to = array('0' => array('name' => $row['name'], 'mail' => $row['mail']));
			$from = array();
			xmail($to, $from, $maildata['title'], $maildata['comment']);
		}
		$lang->setdir($lang_dir);

		($code = $plugins->load('pm_save_end')) ? eval($code) : null;
		ok($lang->phrase('newpm_success'),"pm.php".SID2URL_1);
	}
}
elseif ($_GET['action'] == "new" || $_GET['action'] == "preview" || $_GET['action'] == "quote" || $_GET['action'] == 'reply') {
	Breadcrumb::universal()->add($lang->phrase('pm_new_title'));
	echo $tpl->parse("header");

	BBProfile($bbcode);

	($code = $plugins->load('pm_compose_start')) ? eval($code) : null;

	$fid = $gpc->get('fid', str);
	if (is_hash($fid)) {
		$data = $gpc->unescape(import_error_data($fid));
		if ($_GET['action'] == 'preview') {
			$bbcode->setSmileys(1);
	   		$data['formatted_comment'] = $bbcode->parse($data['comment']);
		}
	}
	elseif ($_GET['action'] == 'quote' || $_GET['action'] == 'reply') {
		$result = $db->query("
			SELECT p.topic, p.comment, u.name, p.pm_from AS uid
			FROM {$db->pre}pm AS p
				LEFT JOIN {$db->pre}user AS u ON u.id = p.pm_from
			WHERE p.id = '{$_GET['id']}' AND p.dir != '2' AND p.pm_to = '{$my->id}'
			LIMIT 1
		");
		if ($db->num_rows($result) != 1) {
			error($lang->phrase('pm_not_found'), 'pm.php'.SID2URL_1);
		}
		$info = $gpc->prepare($db->fetch_assoc($result));
		$data = array(
			'name' => $info['name'],
			'name_id' => $info['uid'],
			'topic' => $lang->phrase('reply_prefix').$info['topic'],
			'outgoing' => 1
		);
		if ($_GET['action'] == 'quote') {
			$info['comment'] = str_replace('[br]', "\n", $info['comment']);
			$info['comment'] = preg_replace('/\[hide\](.+?)\[\/hide\]/isu', '', $info['comment']);
			$data['comment'] = "[quote={$info['name']}]{$info['comment']}[/quote]";
		}
		else {
			$data['comment'] = '';
		}

	}
	else {
		$data = array(
			'name' => $_GET['name'],
			'name_id' => 0,
			'comment' => '',
			'topic' => '',
			'outgoing' => 1
		);
	}
	($code = $plugins->load('pm_compose_prepared')) ? eval($code) : null;
	echo $tpl->parse("pm/new");
	($code = $plugins->load('pm_compose_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "browse") {

	$dir_name = get_pmdir($_GET['id']);
	if (!$dir_name) {
		error($lang->phrase('query_string_error'), 'pm.php'.SID2URL_1);
	}
	Breadcrumb::universal()->add($dir_name);

	($code = $plugins->load('pm_browse_start')) ? eval($code) : null;

	$result = $db->query("
	SELECT COUNT(*)
	FROM {$db->pre}pm
	WHERE pm_to = '{$my->id}' AND dir = '{$_GET['id']}'
	");
	$count = $db->fetch_num($result);

	$temp = pages($count[0], $config['pmzahl'], 'pm.php?action=browse&amp;id='.$_GET['id'].'&amp;', $_GET['page']);
	$start = ($_GET['page'] - 1) * $config['pmzahl'];

	$inner['index_bit'] = '';

	($code = $plugins->load('pm_browse_query')) ? eval($code) : null;
	$result = $db->query("
	SELECT p.id, p.topic, p.date, p.status, p.pm_to, u.id AS pm_from, u.name
	FROM {$db->pre}pm AS p
		LEFT JOIN {$db->pre}user AS u ON u.id = p.pm_from
	WHERE p.pm_to = '{$my->id}' AND p.dir = '{$_GET['id']}'
	ORDER BY p.date DESC
	LIMIT {$start}, {$config['pmzahl']}
	");

	echo $tpl->parse("header");

	while ($row = $db->fetch_assoc($result)) {
		$row['topic'] = $gpc->prepare($row['topic']);
		if ($row['status'] == 0) {
			$row['alt'] = $lang->phrase('pm_newicon');
			$row['src'] = $tpl->img('dir_open2');
		}
		else {
			$row['alt'] = $lang->phrase('pm_oldicon');
			$row['src'] = $tpl->img('dir_open');
		}
		if (empty($row['name'])) {
			$row['name'] = $lang->phrase('fallback_no_username');
		}
		($code = $plugins->load('pm_browse_entry_prepared')) ? eval($code) : null;
		$inner['index_bit'] .= $tpl->parse("pm/browse_bit");
	}
	echo $tpl->parse("pm/browse");
	($code = $plugins->load('pm_browse_end')) ? eval($code) : null;
}
else {
	Breadcrumb::universal()->resetUrl();
	echo $tpl->parse("header");

	$time = time()-60*60*24*7;
	$timestamp = $time > $my->clv ? $my->clv : $time;

	($code = $plugins->load('pm_index_start')) ? eval($code) : null;

	$result = $db->query("
	SELECT p.id, p.topic, p.date, p.status, p.pm_to, u.id AS pm_from, u.name
	FROM {$db->pre}pm AS p
		LEFT JOIN {$db->pre}user AS u ON u.id = p.pm_from
	WHERE p.pm_to = '{$my->id}' AND (p.date > {$timestamp} OR  p.status = '0') AND p.dir != '2'
	ORDER BY p.date DESC
	");

	$count = $db->num_rows($result);
	$inner['index_bit'] = '';
	$inner['index_bit_old'] = '';
	$ib = 0;
	$ibo = 0;
	while ($row = $db->fetch_assoc($result)) {
		if ($row['status'] == 0) {
			$row['alt'] = $lang->phrase('pm_newicon');
			$row['src'] = $tpl->img('dir_open2');
		}
		else {
			$row['alt'] = $lang->phrase('pm_oldicon');
			$row['src'] = $tpl->img('dir_open');
		}
		if (empty($row['name'])) {
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
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();