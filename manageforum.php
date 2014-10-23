<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2009  The Viscacha Project

	Author: Matthias Mohr (et al.)
	Publisher: The Viscacha Project, http://www.viscacha.org
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

define('SCRIPTNAME', 'manageforum');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$board = $gpc->get('id', int);

$catbid = $scache->load('cat_bid');
$fc = $catbid->get();

if (empty($board) || !isset($fc[$board]) || empty($_GET['action'])) {
	error($lang->phrase('query_string_error'));
}
$info = $fc[$board];
if ($info['forumzahl'] < 1) {
	$info['forumzahl'] = $config['forumzahl'];
}

$my->p = $slog->Permissions($info['id']);
$my->mp = $slog->ModPermissions($info['id']);

forum_opt($info);

$breadcrumb->Add($lang->phrase('teamcp'));

($code = $plugins->load('manageforum_start')) ? eval($code) : null;

if (!$my->vlogin || $my->mp[0] == 0) {
	errorLogin($lang->phrase('not_allowed'));
}

if ($_GET['action'] == "index") {
	if ($_GET['type'] == 'open') {
		$marksql = ' AND status = "1" ';
	}
	elseif ($_GET['type'] == 'close') {
		$marksql = ' AND status = "0" ';
	}
	else { // 'close' or 'move'
		$marksql = '';
	}

	($code = $plugins->load('manageforum_filter_query')) ? eval($code) : null;

	if (!empty($marksql)) {
		$result = $db->query("SELECT COUNT(*) FROM {$db->pre}topics WHERE board = '$board' {$marksql}");
		$vlasttopics = $db->fetch_num($result);
		$info['topics'] = $vlasttopics[0];
	}

	$pages = pages($info['topics'], $info['forumzahl'], 'manageforum.php?action=index&amp;id='.$board.'&amp;type='.$_GET['type'].'&amp;', $_GET['page']);
	$inner['index_bit'] = '';
	if ($info['topics'] > 0) {
		$start = $_GET['page']*$info['forumzahl'];
		$start = $start-$info['forumzahl'];

		($code = $plugins->load('manageforum_query')) ? eval($code) : null;
		$result = $db->query("
		SELECT prefix, vquestion, posts, mark, id, board, topic, date, status, last, last_name, sticky, name
		FROM {$db->pre}topics
		WHERE board = '{$board}' {$marksql}
		ORDER BY sticky DESC, last DESC LIMIT {$start}, {$info['forumzahl']}
		");

		$memberdata_obj = $scache->load('memberdata');
		$memberdata = $memberdata_obj->get();

		$prefix_obj = $scache->load('prefix');
		$prefix_arr = $prefix_obj->get($board);

		while ($row = $gpc->prepare($db->fetch_object($result))) {
			$pref = '';
			$showprefix = false;
			if (isset($prefix_arr[$row->prefix]) && $row->prefix > 0) {
				$prefix = $prefix_arr[$row->prefix]['value'];
				$showprefix = true;
			}
			else {
				$prefix = '';
			}

			if(is_id($row->name) && isset($memberdata[$row->name])) {
				$row->mid = $row->name;
				$row->name = $memberdata[$row->name];
			}
			else {
				$row->mid = FALSE;
			}

			if (is_id($row->last_name) && isset($memberdata[$row->last_name])) {
				$row->last_name = $memberdata[$row->last_name];
			}

			$rstart = str_date($lang->phrase('dformat1'),times($row->date));
			$rlast = str_date($lang->phrase('dformat1'),times($row->last));

			if ($row->status == '2') {
				$pref .= $lang->phrase('forum_moved');
			}
			else {
				if ($row->mark === null && !empty($info['auto_status'])) {
					$row->mark = $info['auto_status'];
				}
				if ($row->mark == 'n') {
					$pref .= $lang->phrase('forum_mark_n');
				}
				elseif ($row->mark == 'a') {
					$pref .= $lang->phrase('forum_mark_a');
				}
				elseif ($row->mark == 'b') {
					$pref .= $lang->phrase('forum_mark_b');
				}
				elseif ($row->mark == 'g') {
					$pref .= $lang->phrase('forum_mark_g');
				}
				if ($row->sticky == '1') {
					$pref .= $lang->phrase('forum_announcement');
				}
			}

			($code = $plugins->load('manageforum_entry_prepared')) ? eval($code) : null;
			$inner['index_bit'] .= $tpl->parse("admin/forum/index_bit");
		}
	}
	else {
		($code = $plugins->load('manageforum_empty')) ? eval($code) : null;
		$inner['index_bit'] .= $tpl->parse("admin/forum/index_bit_empty");
	}

	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	($code = $plugins->load('manageforum_index_prepared')) ? eval($code) : null;
	echo $tpl->parse("admin/forum/index");
	($code = $plugins->load('manageforum_index_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "close") {
	if (count($_POST['delete']) == 0) {
		$slog->updatelogged();
		$db->close();
		if (empty($_GET['action'])) {
			$url = 'showforum.php?id='.$board.SID2URL_JS_x;
		}
		else {
			$url = 'manageforum.php?action=index&id='.$board.'&type='.$_GET['action'].SID2URL_JS_x;
		}
		sendStatusCode(307, $config['furl'].'/'.$url);
		exit;
	}
	$db->query("UPDATE {$db->pre}topics SET status = '1' WHERE board = '{$board}' AND id IN(".implode(',', $_POST['delete']).")");
	if ($db->affected_rows() > 0) {
		ok($lang->phrase('admin_topicstatus_changed'),'showforum.php?id='.$board.SID2URL_x);
	}
	else {
		error($lang->phrase('admin_failed'),'showforum.php?id='.$board.SID2URL_x);
	}
}
elseif ($_GET['action'] == "open") {
	if (count($_POST['delete']) == 0) {
		$slog->updatelogged();
		$db->close();
		if (empty($_GET['action'])) {
			$url = 'showforum.php?id='.$board.SID2URL_JS_x;
		}
		else {
			$url = 'manageforum.php?action=index&id='.$board.'&type='.$_GET['action'].SID2URL_JS_x;
		}
		sendStatusCode(307, $config['furl'].'/'.$url);
		exit;
	}
	$db->query("UPDATE {$db->pre}topics SET status = '0' WHERE board = '{$board}' AND id IN(".implode(',', $_POST['delete']).")");
	if ($db->affected_rows() > 0) {
		ok($lang->phrase('admin_topicstatus_changed'),'showforum.php?id='.$board.SID2URL_x);
	}
	else {
		error($lang->phrase('admin_failed'),'showforum.php?id='.$board.SID2URL_x);
	}
}
elseif ($_GET['action'] == "move") {
	if (count($_POST['delete']) == 0) {
		if (empty($_GET['action'])) {
			$url = 'showforum.php?id='.$board.SID2URL_JS_x;
		}
		else {
			$url = 'manageforum.php?action=index&id='.$board.'&type='.$_GET['action'].SID2URL_JS_x;
		}
		sendStatusCode(307, $config['furl'].'/'.$url);
	}
	$my->pb = $slog->GlobalPermissions();
	if ($my->mp[0] == 1 && $my->mp[5] == 0) {
		errorLogin($lang->phrase('not_allowed'), 'showforum.php?id='.$board.SID2URL_x);
	}
	$forums = BoardSubs();
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	echo $tpl->parse("admin/forum/move");
}
elseif ($_GET['action'] == "move2") {
	if ($my->mp[0] == 1 && $my->mp[5] == 0) {
		errorLogin($lang->phrase('not_allowed'), 'manageforum.php?action=index&amp;id='.$board.'&amp;type='.$_GET['action'].SID2URL_x);
	}
	$anz = 0;
	foreach ($_POST['delete'] as $id) {
		$result = $db->query("
		SELECT r.date, r.topic, r.name, r.guest, r.email, u.name AS uname, u.mail AS uemail
		FROM {$db->pre}replies AS r
			LEFT JOIN {$db->pre}user AS u ON u.id = r.name AND r.guest = '0'
		WHERE topic_id = '{$id}' AND tstart = '1'
		");
		$old = $db->fetch_assoc($result);
		$db->query("UPDATE {$db->pre}topics SET board = '{$_POST['opt_0']}' WHERE id = '{$id}' LIMIT 1");
		$anz += $db->affected_rows();
		$db->query("UPDATE {$db->pre}replies SET board = '{$_POST['opt_0']}' WHERE topic_id = '{$id}'");
		$anz += $db->affected_rows();

		if ($_POST['temp'] == 1) {
			// Prefix wird nicht übernommen!
			$db->query("INSERT INTO {$db->pre}topics SET status = '2', topic = '".$gpc->save_str($old['topic'])."', board='{$board}', name = '".$gpc->save_str($old['name'])."', date = '{$old['date']}', last_name = '".$gpc->save_str($old['name'])."', last = '{$old['date']}', vquestion = ''");
			$tid = $db->insert_id();
			$db->query("INSERT INTO {$db->pre}replies SET tstart = '1', topic_id = '{$tid}', comment = '{$id}', topic = '".$gpc->save_str($old['topic'])."', board='{$board}', name = '".$gpc->save_str($old['name'])."', email = '{$old['email']}', date = '{$old['date']}', guest = '{$old['guest']}', edit = '', report = ''");
		}
		if ($_POST['temp2'] == 1) {
			if ($old['guest'] == 0) {
				$old['email'] = $old['uemail'];
				$old['name'] = $old['uname'];
			}
			$data = $lang->get_mail('mass_topic_moved');
			$to = array('0' => array('name' => $old['name'], 'mail' => $old['email']));
			$from = array();
			xmail($to, $from, $data['title'], $data['comment']);
		}
	}
	if ($config['updateboardstats'] == 1) {
		UpdateBoardStats($board);
		UpdateBoardStats($_POST['opt_0']);
	}
	else {
		UpdateBoardLastStats($board);
		UpdateBoardLastStats($_POST['opt_0']);
	}

	ok($lang->phrase('x_entries_moved'),'showforum.php?id='.$board.SID2URL_x);
}
elseif ($_GET['action'] == "delete") {
	if ($my->mp[0] == 1 && $my->mp[4] == 0) {
		errorLogin($lang->phrase('not_allowed'),'manageforum.php?action=index&amp;id='.$board.'&amp;type='.$_GET['action'].SID2URL_x);
	}
	if (count($_POST['delete']) == 0) {
		$slog->updatelogged();
		$db->close();
		if (empty($_GET['action'])) {
			$url = 'showforum.php?id='.$board.SID2URL_JS_x;
		}
		else {
			$url = 'manageforum.php?action=index&id='.$board.'&type='.$_GET['action'].SID2URL_JS_x;
		}
		sendStatusCode(307, $config['furl'].'/'.$url);
		exit;
	}
	$ids = implode(',', $_POST['delete']);
	if ($config['updatepostcounter'] == 1 && $info['count_posts'] == 1) {
		$result = $db->query("SELECT COUNT(*) AS posts, name FROM {$db->pre}replies WHERE guest = '0' AND topic_id IN({$ids}) GROUP BY name");
		while ($row = $db->fetch_assoc($result)) {
			$db->query("UPDATE {$db->pre}user SET posts = posts-{$row['posts']} WHERE id = '{$row['name']}'");
		}
	}
	$db->query ("DELETE FROM {$db->pre}replies WHERE topic_id IN({$ids})");
	$anz = $db->affected_rows();
	$uresult = $db->query ("SELECT id, source FROM {$db->pre}uploads WHERE topic_id IN({$ids})");
	while ($urow = $db->fetch_assoc($uresult)) {
		$filesystem->unlink('uploads/topics/'.$urow['source']);
		$thumb = 'uploads/topics/thumbnails/'.$urow['id'].get_extension($urow['source'], true);
		if (file_exists($thumb)) {
			$filesystem->unlink($thumb);
		}
	}
	$db->query ("DELETE FROM {$db->pre}uploads WHERE topic_id IN({$ids})");
	$anz += $db->affected_rows();
	$db->query ("DELETE FROM {$db->pre}postratings WHERE tid IN({$ids})");
	$anz += $db->affected_rows();
	$db->query ("DELETE FROM {$db->pre}abos WHERE tid IN({$ids})");
	$anz += $db->affected_rows();
	$db->query ("DELETE FROM {$db->pre}topics WHERE id IN({$ids})");
	$anz += $db->affected_rows();
	$votes = $db->query("SELECT id FROM {$db->pre}vote WHERE tid IN({$ids})");
	$voteaids = array();
	while ($row = $db->fetch_num($votes)) {
		$voteaids[] = $row[0];
	}
	if (count($voteaids) > 0) {
		$db->query ("DELETE FROM {$db->pre}votes WHERE id IN (".implode(',', $voteaids).")");
		$anz += $db->affected_rows();
	}
	$db->query ("DELETE FROM {$db->pre}vote WHERE tid IN({$ids})");
	$anz += $db->affected_rows();
	($code = $plugins->load('manageforum_delete_end')) ? eval($code) : null;

	if ($config['updateboardstats'] == 1) {
		UpdateBoardStats($board);
	}
	else {
		UpdateBoardLastStats($board);
	}

	ok($lang->phrase('x_entries_deleted'),"showforum.php?id=".$board.SID2URL_x);
}
elseif ($_GET['action'] == "stat") {
	UpdateBoardStats($info['id']);
	ok($lang->phrase('data_success'),'showforum.php?id='.$board.SID2URL_x);
}

($code = $plugins->load('manageforum_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();
?>