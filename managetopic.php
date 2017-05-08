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

define('SCRIPTNAME', 'managetopic');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$action = $gpc->get('action', none);

$info = $db->fetch('SELECT board, id, last_name, prefix, topic FROM '.$db->pre.'topics WHERE id = "'.$_GET['id'].'"');
if (!$info) {
	error($lang->phrase('query_string_error'));
}

$my->p = $slog->Permissions($info['board']);
$my->mp = $slog->ModPermissions($info['board']);

// preparing data for breadcrumb
$catbid = $scache->load('cat_bid');
$fc = $catbid->get();
$last = $fc[$info['board']];
$topforums = get_headboards($fc, $last, true);

$prefix = '';
if ($info['prefix'] > 0) {
	$prefix_obj = $scache->load('prefix');
	$prefix_arr = $prefix_obj->get($info['board']);
	if (isset($prefix[$info['prefix']])) {
		$prefix = '[' . $prefix_arr[$info['prefix']]['value'] . ']';
	}
}

Breadcrumb::universal()->add($last['name'], "showforum.php?id=".$last['id'].SID2URL_x);
Breadcrumb::universal()->add($prefix.$info['topic'], "showtopic.php?id=".$info['id'].SID2URL_x);
Breadcrumb::universal()->add($lang->phrase('teamcp'));

forum_opt($last);

if (!$my->vlogin || $my->mp[0] != 1) {
	errorLogin($lang->phrase('not_allowed'), 'showtopic.php?id='.$info['id'].SID2URL_x);
}

($code = $plugins->load('managetopic_start')) ? eval($code) : null;

if ($action == "delete") {
	if ($my->mp[1] == 0) {
		errorLogin($lang->phrase('not_allowed'), 'showtopic.php?id='.$info['id'].SID2URL_x);
	}
	echo $tpl->parse("header");
	echo $tpl->parse("admin/topic/delete");
	echo $tpl->parse("footer");
}
elseif ($action == "delete2") {
	if ($my->mp[1] == 0) {
		errorLogin($lang->phrase('not_allowed'), 'showtopic.php?id='.$info['id'].SID2URL_x);
	}
	if ($config['updatepostcounter'] == 1 && $last['count_posts'] == 1) {
		$result = $db->execute("SELECT COUNT(*) AS posts, name FROM {$db->pre}replies WHERE topic_id = '{$info['id']}' GROUP BY name");
		while ($row = $result->fetch()) {
			$db->execute("UPDATE {$db->pre}user SET posts = posts-{$row['posts']} WHERE id = '{$row['name']}'");
		}
	}
	$stmt = $db->execute ("DELETE FROM {$db->pre}replies WHERE topic_id = '{$info['id']}'");
	$anz = $stmt->getAffectedRows();
	$uresult = $db->execute ("SELECT id, source FROM {$db->pre}uploads WHERE topic_id = '{$info['id']}'");
	while ($urow = $uresult->fetch()) {
		$filesystem->unlink('uploads/topics/'.$urow['source']);
		$thumb = 'uploads/topics/thumbnails/'.$urow['id'].get_extension($urow['source'], true);
		if (file_exists($thumb)) {
			$filesystem->unlink($thumb);
		}
	}
	$stmt = $db->execute ("DELETE FROM {$db->pre}uploads WHERE topic_id = '{$info['id']}'");
	$anz += $stmt->getAffectedRows();
	$stmt = $db->execute ("DELETE FROM {$db->pre}abos WHERE tid = '{$info['id']}'");
	$anz += $stmt->getAffectedRows();
	$stmt = $db->execute ("DELETE FROM {$db->pre}topics WHERE id = '{$info['id']}'");
	$anz += $stmt->getAffectedRows();
	$votes = $db->execute("SELECT id FROM {$db->pre}vote WHERE tid = '{$info['id']}'");
	$voteaids = array();
	while ($row = $votes->fetch()) {
		$voteaids[] = $row['id'];
	}
	if (count($voteaids) > 0) {
		$stmt = $db->execute ("DELETE FROM {$db->pre}votes WHERE id IN (".implode(',', $voteaids).")");
		$anz += $stmt->getAffectedRows();
	}
	$stmt = $db->execute ("DELETE FROM {$db->pre}vote WHERE tid = '{$info['id']}'");
	$anz += $stmt->getAffectedRows();

	($code = $plugins->load('managetopic_delete2_end')) ? eval($code) : null;

	if ($config['updateboardstats'] == 1) {
		UpdateBoardStats($info['board']);
	}
	else {
		UpdateBoardLastStats($info['board']);
	}
	ok($lang->phrase('x_entries_deleted'),"showforum.php?id=".$info['board'].SID2URL_x);
}
elseif ($action == "move") {
	$my->pb = $slog->GlobalPermissions();
	if ($my->mp[2] == 0) {
		errorLogin($lang->phrase('not_allowed'), 'showtopic.php?id='.$info['id'].SID2URL_x);
	}
	$forums = BoardSubs();
	echo $tpl->parse("admin/topic/move");
}
elseif ($action == "move2") {
	if ($my->mp[2] == 0) {
		errorLogin($lang->phrase('not_allowed'), 'showtopic.php?id='.$info['id'].SID2URL_x);
	}

	$result = $db->execute("
		SELECT r.date, r.name, u.name, u.mail, u.deleted_at
		FROM {$db->pre}replies AS r
			LEFT JOIN {$db->pre}user AS u ON u.id = r.name
		WHERE topic_id = '{$info['id']}' AND tstart = '1'
	");
	$old = $result->fetch();

	$board = $gpc->get('board', int);

	$stmt = $db->execute("UPDATE {$db->pre}topics SET board = '{$board}' WHERE id = '{$info['id']}' LIMIT 1");
	$anz = $stmt->getAffectedRows();

	// TODO: Prefix und Editierungen werden nicht übernommen
	if ($_POST['temp'] == 1) {
		$db->execute("INSERT INTO {$db->pre}topics SET status = '2', topic = '".$gpc->save_str($old['topic'])."', board='{$info['board']}', name = '".$gpc->save_int($old['name'])."', date = '{$old['date']}', last_name = '".$gpc->save_int($info['last_name'])."', prefix = '{$info['prefix']}', last = '{$old['date']}', vquestion = ''");
		$tid = $db->getInsertId();
		$db->execute("INSERT INTO {$db->pre}replies SET tstart = '1', topic_id = '{$tid}', comment = '{$info['id']}', name = '".$gpc->save_int($old['name'])."', date = '{$old['date']}', edit = '', report = ''");
	}
	if ($_POST['temp2'] == 1) {
		$data = $lang->get_mail('topic_moved');
		$to = array('0' => array('name' => $old['name'], 'mail' => $old['mail']));
		$from = array();
		xmail($to, $from, $data['title'], $data['comment']);
	}

	if ($config['updateboardstats'] == 1) {
		UpdateBoardStats($info['board']);
		UpdateBoardStats($board);
	}
	else {
		UpdateBoardLastStats($info['board']);
		UpdateBoardLastStats($board);
	}

	ok($lang->phrase('x_entries_moved'),'showtopic.php?id='.$info['id']);
}
elseif ($action == "reports") {
	$data = $db->fetch("SELECT id, report, topic_id, tstart FROM {$db->pre}replies WHERE id = '{$_GET['topic_id']}'");
	if (!$data) {
		error($lang->phrase('query_string_error'), 'showtopic.php?id='.$info['id'].SID2URL_x);
	}
	if (empty($data['report'])) {
		error($lang->phrase('admin_report_not_found'), "showtopic.php?action=jumpto&topic_id={$data['id']}".SID2URL_x);
	}

	echo $tpl->parse("admin/topic/reports");
}
elseif ($action == "reports2") {
	if ($_POST['temp'] == 1) {
		$db->execute("UPDATE {$db->pre}replies SET report = '' WHERE id = '{$_GET['topic_id']}' LIMIT 1");
		ok($lang->phrase('admin_report_reset_success'), "showtopic.php?action=jumpto&topic_id={$_GET['topic_id']}".SID2URL_x);
	}
	else {
		error($lang->phrase('admin_failed'), 'managetopic.php?action=reports&id='.$info['id'].'&topic_id='.$_GET['topic_id'].SID2URL_x);
	}
}
elseif ($action == "pin") {
	$stmt = $db->execute("UPDATE {$db->pre}topics SET sticky = '1' WHERE id = '".$info['id']."'");
	if ($stmt->getAffectedRows() == 1) {
		ok($lang->phrase('admin_topicstatus_changed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	}
	else {
		error($lang->phrase('admin_failed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	}
}
elseif ($action == "unpin") {
	$stmt = $db->execute("UPDATE {$db->pre}topics SET sticky = '0' WHERE id = '".$info['id']."'");
	if ($stmt->getAffectedRows() == 1) {
		ok($lang->phrase('admin_topicstatus_changed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	}
	else {
		error($lang->phrase('admin_failed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	}
}
elseif ($action == "close") {
	$stmt = $db->execute("UPDATE {$db->pre}topics SET status = '1' WHERE id = '".$info['id']."'");
	if ($stmt->getAffectedRows() == 1) {
		ok($lang->phrase('admin_topicstatus_changed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	}
	else {
		error($lang->phrase('admin_failed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	}
}
elseif ($action == "open") {
	$stmt = $db->execute("UPDATE {$db->pre}topics SET status = '0' WHERE id = '".$info['id']."'");
	if ($stmt->getAffectedRows() == 1) {
		ok($lang->phrase('admin_topicstatus_changed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	}
	else {
		error($lang->phrase('admin_failed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	}
}
elseif ($action == "stat") {
	UpdateTopicStats($info['id']);
	ok($lang->phrase('data_success'),'showtopic.php?id='.$info['id'].SID2URL_x);
}
elseif ($action == "vote_export") {
	$PG = new Viscacha\Graphic\Charts();

	$skin = $gpc->get('skin', int, 1);
	$modus = $gpc->get('modus', int, 1);

	echo $tpl->parse("admin/topic/vote_export");
}
elseif ($action == "vote_edit") {
	$error = array();

	$result = $db->execute('SELECT id, topic, posts, sticky, status, last, board, vquestion, prefix FROM '.$db->pre.'topics WHERE id = '.$_GET['id'].' LIMIT 1');
	$info = $result->fetch();

	$fid = $gpc->get('fid', str);
	if (is_hash($fid)) {
		$data = $gpc->unescape(import_error_data($fid));
		$data[0] = $data['answer'][0];
		unset($data['answer'][0]);
		$result = $db->execute("SELECT id, answer FROM {$db->pre}vote WHERE tid = '{$info['id']}' ORDER BY id");
		while ($row = $result->fetch()) {
			$data['original'][$row['id']] = $row['answer'];
		}
	}
	else {
		$data = $data['answer'] = array();
		$data['question'] = $info['vquestion'];
		$result = $db->execute("SELECT id, answer FROM {$db->pre}vote WHERE tid = '{$info['id']}' ORDER BY id");
		while ($row = $result->fetch()) {
			$data['answer'][$row['id']] = $row['answer'];
			$data['original'][$row['id']] = $row['answer'];
		}
		$data[0] = '';
	}

	$i = 0;
	echo $tpl->parse("admin/topic/vote_edit");
}
elseif ($action == "vote_edit2") {
	$error = array();
	if (mb_strlen($_POST['question']) > $config['maxtitlelength']) {
		$error[] = $lang->phrase('question_too_long');
	}
	if (mb_strlen($_POST['question']) < $config['mintitlelength']) {
		$error[] = $lang->phrase('question_too_short');
	}
	$notices = $gpc->get('notice', arr_str);
	foreach ($notices as $id => $uval) {
		$uval = trim($uval);
		if (strlen($uval) >= 255) {
			$error[] = $lang->phrase('vote_reply_too_long');
		}
		$notices[$id] = $uval;
	}
	if (count_filled($notices) < 2) {
		$error[] = $lang->phrase('min_replies_vote');
	}
	if (count_filled($notices) > 50) {
		$error[] = $lang->phrase('max_replies_vote');
	}
	if (count($error) > 0) {
		$data = array(
			'question' => $_POST['question'],
			'answer' => $notices
		);
		$fid = save_error_data($data);
		error($error,'managetopic.php?action=vote_edit&amp;id='.$_GET['id'].'&amp;fid='.$fid.SID2URL_x);
	}
	else {
		$db->execute("UPDATE {$db->pre}topics SET vquestion = '{$_POST['question']}' WHERE id = '{$_GET['id']}' LIMIT 1");
		$result = $db->execute("SELECT id, answer FROM {$db->pre}vote WHERE tid = '{$info['id']}' ORDER BY id");
		while($row = $result->fetch()) {
			if ($notices[$row['id']] != $row['answer']) {
				if (mb_strlen($notices[$row['id']]) > 0) {
					$db->execute("UPDATE {$db->pre}vote SET answer = '{$notices[$row['id']]}' WHERE id = '{$row['id']}'");
				}
				else {
					$db->execute("DELETE FROM {$db->pre}vote WHERE id = '{$row['id']}'");
					$db->execute("DELETE FROM {$db->pre}votes WHERE aid = '{$row['id']}'");
				}
			}
		}
		if (strlen($notices[0]) > 0) {
			$db->execute("INSERT INTO {$db->pre}vote (tid, answer) VALUES ('{$_GET['id']}','{$notices[0]}')");
		}
		ok($lang->phrase('data_success'),"showtopic.php?id={$_GET['id']}");
	}
}
elseif ($action == "vote_delete") {
	if ($my->mp[1] == 0) {
		errorLogin($lang->phrase('not_allowed'), 'showtopic.php?id='.$info['id'].SID2URL_x);
	}
	echo $tpl->parse("header");
	echo $tpl->parse("admin/topic/vote_delete");
	echo $tpl->parse("footer");
}
elseif ($action == "vote_delete2") {
	if ($my->mp[1] == 0) {
		errorLogin($lang->phrase('not_allowed'), 'showtopic.php?id='.$info['id'].SID2URL_x);
	}
	$anz = 0;
	$voteaids = $db->fetchList("SELECT id FROM {$db->pre}vote WHERE tid = '{$info['id']}'");
	if (count($voteaids) > 0) {
		$stmt = $db->execute ("DELETE FROM {$db->pre}votes WHERE id IN (".implode(',', $voteaids).")");
		$anz += $stmt->getAffectedRows();
	}
	$stmt = $db->execute("DELETE FROM {$db->pre}vote WHERE tid = '{$info['id']}'");
	$anz += $stmt->getAffectedRows();
	$db->execute("UPDATE {$db->pre}topics SET vquestion = '' WHERE id = '{$info['id']}'");

	ok($lang->phrase('x_entries_deleted'),"showforum.php?id=".$info['board'].SID2URL_x);
}
elseif ($action == "pdelete") {
	if ($my->mp[1] == 0) {
		errorLogin($lang->phrase('not_allowed'), 'showtopic.php?id='.$info['id'].SID2URL_x);
	}
	$ids = $gpc->get('ids', arr_int);
	if (count($ids) == 0) {
		error($lang->phrase('no_data_selected'));
	}

	$iid = implode(',', $ids);

	if ($config['updatepostcounter'] == 1 && $last['count_posts'] == 1) {
		$result = $db->execute("SELECT COUNT(*) AS posts, name FROM {$db->pre}replies WHERE id IN ({$iid}) GROUP BY name");
		while ($row = $result->fetch()) {
			$db->execute("UPDATE {$db->pre}user SET posts = posts-{$row['posts']} WHERE id = '{$row['name']}' AND deleted_at IS NULL");
		}
	}

	$stmt = $db->execute ("DELETE FROM {$db->pre}replies WHERE id IN ({$iid})");
	$anz = $stmt->getAffectedRows();
	$uresult = $db->execute ("SELECT id, source FROM {$db->pre}uploads WHERE tid IN ({$iid})");
	while ($urow = $uresult->fetch()) {
		$filesystem->unlink('uploads/topics/'.$urow['source']);
		$thumb = 'uploads/topics/thumbnails/'.$urow['id'].get_extension($urow['source'], true);
		if (file_exists($thumb)) {
			$filesystem->unlink($thumb);
		}
	}
	$db->execute ("DELETE FROM {$db->pre}uploads WHERE tid IN ({$iid})");

	$result = $db->fetch("SELECT id FROM {$db->pre}replies WHERE topic_id = '{$info['id']}'");
	if (!$result) {
		$db->execute ("DELETE FROM {$db->pre}abos WHERE tid = '{$info['id']}'");
		$db->execute ("DELETE FROM {$db->pre}topics WHERE id = '{$info['id']}'");
		$votes = $db->execute("SELECT id FROM {$db->pre}vote WHERE tid = '{$info['id']}'");
		$voteaids = array();
		while ($row = $votes->fetch()) {
			$voteaids[] = $row['id'];
		}
		if (count($voteaids) > 0) {
			$db->execute ("DELETE FROM {$db->pre}votes WHERE id IN (".implode(',', $voteaids).")");
		}
		$db->execute ("DELETE FROM {$db->pre}vote WHERE tid = '{$info['id']}'");
		$redirect = "showforum.php?id=".$info['board'].SID2URL_x;
	}
	else {
		UpdateTopicStats($info['id']);
		$redirect = "showtopic.php?id=".$info['id'].SID2URL_x;
	}

	($code = $plugins->load('managetopic_pdelete_end')) ? eval($code) : null;

	if ($config['updateboardstats'] == 1) {
		UpdateBoardStats($info['board']);
	}
	else {
		UpdateBoardLastStats($info['board']);
	}

	ok($lang->phrase('x_entries_deleted'),$redirect);
}

elseif ($action == "pmerge") {
	$ids = $gpc->get('ids', arr_int);
	if (count($ids) < 2) {
		error($lang->phrase('no_data_selected'));
	}
	$iid = implode(',', $ids);

	$result = $db->execute("SELECT r.*, u.name, u.id AS uid, u.mail, u.deleted_at FROM {$db->pre}replies AS r LEFT JOIN {$db->pre}user AS u ON r.name = u.id WHERE r.id IN ({$iid}) ORDER BY date ASC");
	$author = array();
	$comment = array();
	$posts = array();
	while ($row = $result->fetch()) {
		$posts[$row['id']] = $row;
		$author[$row['id']] = $row['name'];
		$comment[$row['id']] = $row['comment'];
	}
	$author = array_unique($author);
	$comment = array_unique($comment);

	BBProfile($bbcode);

	($code = $plugins->load('managetopic_pmerge_prepared')) ? eval($code) : null;

	echo $tpl->parse("admin/topic/post_merge");
}
elseif ($action == "pmerge2") {
	$ids = $gpc->get('ids', arr_int);
	$iids = implode(',', $ids);
	$author = $gpc->get('author', int);
	$error = array();
	if (count($ids) < 2) {
		$error[] = $lang->phrase('no_data_selected');
	}
	if (empty($author)) {
		$error[] = $lang->phrase('name_too_short');
	}

	if (count($error) > 0) {
		error($error);
	}
	else {
		$cache = array();
		$base = array('date' => time());
		$result = $db->execute("SELECT r.*, u.name, u.deleted_at FROM {$db->pre}replies AS r LEFT JOIN {$db->pre}user AS u ON r.name = u.id WHERE r.id IN ({$iids})");
		while ($row = $result->fetch()) {
			$cache[$row['id']] = $row;
			if ($row['date'] < $base['date']) {
				$base = $row;
			}
		}

		$old = array();
		foreach ($ids as $id) {
			if ($id != $base['id']) {
				$old[] = $id;
			}
		}
		$iold = implode(',', $old);

		$name = $cache[$author]['name'];
		$ip = $cache[$author]['ip'];

		$rev = array();
		foreach ($cache as $row) {
			$row['edit'] = explode("\n", $row['edit']);
			foreach ($row['edit'] as $row2) {
				$x = trim($row2);
				if (empty($x)) {
					continue;
				}
				$row2 = explode("\t", $row2);
				$rev[] = array(
					'name' => $row2[0],
					'date' => $row2[1],
					'reason' => (isset($row2[2]) ? $row2[2] : ''),
					'ip' => (isset($row2[3]) ? $row2[3] : '')
				);
			}
		}
		foreach ($old as $id) {
			$row = $cache[$id];
			$rev[] = array(
				'name' => $row['name'],
				'date' => $row['date'],
				'reason' => $lang->phrase('admin_merge_edit_add'),
				'ip' => $row['ip']
			);
		}
		$rev[] = array(
			'name' => $my->name,
			'date' => time(),
			'reason' => $lang->phrase('admin_merge_edit_reason'),
			'ip' => $my->ip
		);

		usort($rev, "cmp_edit_date");

		$edit = '';
		foreach ($rev as $row) {
			$edit .= "{$row['name']}\t{$row['date']}\t{$row['reason']}\t{$row['ip']}\n";
		}
		$edit = trim($edit, "\n");

		$db->execute ("UPDATE {$db->pre}uploads SET tid = '{$base['id']}' WHERE tid IN ({$iold})");
		$db->execute ("UPDATE {$db->pre}vote SET tid = '{$base['id']}' WHERE tid IN ({$iold})");

		$db->execute ("UPDATE {$db->pre}replies SET name = '{$name}', comment = '{$_POST['comment']}', dosmileys = '{$_POST['dosmileys']}', ip = '{$ip}', edit = '{$edit}' WHERE id = '{$base['id']}'");
		$db->execute ("DELETE FROM {$db->pre}replies WHERE id IN ({$iold})");

		($code = $plugins->load('managetopic_pmerge_end')) ? eval($code) : null;

		UpdateTopicStats($info['id']);
		if ($config['updateboardstats'] == 1) {
			UpdateBoardStats($info['board']);
		}
		else {
			UpdateBoardLastStats($info['board']);
		}

		$anz = count($ids);
		ok($lang->phrase('x_entries_merged'),"showtopic.php?action=jumpto&topic_id=".$base['id'].SID2URL_x);
	}
}

($code = $plugins->load('managetopic_end')) ? eval($code) : null;

$slog->updatelogged();
$response->send();