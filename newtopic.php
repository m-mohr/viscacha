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

define('SCRIPTNAME', 'newtopic');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$board = $gpc->get('id', int);
$fid = $gpc->get('fid', str);

$my->p = $slog->Permissions($board);
$my->mp = $slog->ModPermissions($board);

$catbid = $scache->load('cat_bid');
$fc = $catbid->get();
if (empty($board) || !isset($fc[$board])) {
	error($lang->phrase('query_string_error'));
}
$last = $fc[$board];
forum_opt($last, 'posttopics');

if ($config['tpcallow'] == 1 && $my->p['attachments'] == 1) {
	$p_upload = 1;
}
else {
	$p_upload = 0;
}

$topforums = get_headboards($fc, $last, true);
Breadcrumb::universal()->add($last['name'], "showforum.php?id=".$last['id'].SID2URL_x);
Breadcrumb::universal()->add($lang->phrase('newtopic_title'));

($code = $plugins->load('newtopic_start')) ? eval($code) : null;

if ($_GET['action'] == "startvote") {

	$result = $db->query("SELECT id, vquestion, name, board FROM {$db->pre}topics WHERE id = '{$_GET['topic_id']}' LIMIT 1");
	$info = $db->fetch_assoc($result);

	$temp = $gpc->get('temp', int, 2);
	if ($temp < 2) {
		$temp = 2;
	}
	if ($temp > 50) {
		$temp = 50;
	}

	($code = $plugins->load('newtopic_startvote_start')) ? eval($code) : null;

	$error = array();
	if ($my->p['addvotes'] == 0 || !empty($info['vquestion']) || ($info['name'] != $my->id && $my->mp[0] == 0)) {
		$error[] = $lang->phrase('not_allowed');
	}
	if ($db->num_rows($result) != 1) {
		$error[] = $lang->phrase('query_string_error');
	}
	if (count($error) > 0) {
		errorLogin($error,"showforum.php?id=".$info['board'].SID2URL_x);
	}

	if (is_hash($fid)) {
		$data = $gpc->unescape(import_error_data($fid));
		for ($i = 1; $i <= $temp; $i++) {
			if (!isset($data[$i])) {
				$data[$i] = '';
			}
		}
	}
	else {
		$data = array_fill(1, $temp, '');
		$data['question'] = '';
	}

	Breadcrumb::universal()->add($lang->phrase('add_vote_to_thread'));

	($code = $plugins->load('newtopic_startvote_prepared')) ? eval($code) : null;
	echo $tpl->parse("newtopic/startvote");
	($code = $plugins->load('newtopic_startvote_end')) ? eval($code) : null;

}
elseif ($_GET['action'] == "savevote") {

	$temp = $gpc->get('temp', int);
	$topic_id = $gpc->get('topic_id', int);
	$notices = $gpc->get('notice', arr_str);

	if (!empty($_POST['Update'])) {
		$notices['question'] = $_POST['question'];
		$fid = save_error_data($notices, $fid);
		$slog->updatelogged();
		sendStatusCode(302, $config['furl']."/newtopic.php?action=startvote&id={$board}&topic_id={$topic_id}&temp={$temp}&fid=".$fid.SID2URL_x);
	}

	if ($my->p['addvotes'] == 0 || !empty($info['vquestion'])) {
		errorLogin($lang->phrase('not_allowed'),"showforum.php?id=".$info['board'].SID2URL_x);
	}

	$result = $db->query("SELECT id, vquestion, board FROM {$db->pre}topics WHERE id = '{$topic_id}' LIMIT 1");
	$info = $db->fetch_assoc($result);

	$error = array();
	if ($db->num_rows($result) != 1) {
		$error[] = $lang->phrase('query_string_error');
	}
	if (mb_strlen($_POST['question']) > $config['maxtitlelength']) {
		$error[] = $lang->phrase('question_too_long');
	}
	if (mb_strlen($_POST['question']) < $config['mintitlelength']) {
		$error[] = $lang->phrase('question_too_short');
	}
	$i = 1;
	foreach ($notices as $id => $uval) {
		$uval = trim($uval);
		if (strlen($uval) >= 255) {
			$error[] = $lang->phrase('vote_reply_too_long');
		}
		if (empty($uval)) {
			unset($notices[$id]);
		}
		else {
			$notices[$id] = $uval;
		}
		$i++;
	}
	if (count_filled($notices) < 2) {
		$error[] = $lang->phrase('min_replies_vote');
	}
	if (count_filled($notices) > 50) {
		$error[] = $lang->phrase('max_replies_vote');
	}

	($code = $plugins->load('newtopic_savevote_errorhandling')) ? eval($code) : null;

	if (count($error) > 0) {
		$notices['question'] = $_POST['question'];
		($code = $plugins->load('newtopic_savevote_errordata')) ? eval($code) : null;
		$fid = save_error_data($notices, $fid);
		error($error,"newtopic.php?action=startvote&amp;id={$info['board']}&topic_id={$topic_id}&amp;temp={$temp}&amp;fid=".$fid.SID2URL_x);
	}
	else {
		$sqlwhere = array();
		foreach ($notices as $uval) {
			$sqlwhere[] = "({$topic_id}, '{$uval}')";
		}
		$sqlwhere = implode(", ",$sqlwhere);

		($code = $plugins->load('newtopic_savevote_queries')) ? eval($code) : null;

		$db->query("UPDATE {$db->pre}topics SET vquestion = '{$_POST['question']}' WHERE id = '{$info['id']}'");
		$db->query("INSERT INTO {$db->pre}vote (tid, answer) VALUES {$sqlwhere}");
		$inserted = $db->affected_rows();
		if ($inserted > 1) {
			ok($lang->phrase('data_success'),"showtopic.php?id={$topic_id}".SID2URL_x);
		}
		else {
			$db->query("UPDATE {$db->pre}topics SET vquestion = '' WHERE id = '{$topic_id}'");
			error($lang->phrase('add_vote_failed'),"showtopic.php?id={$topic_id}".SID2URL_x);
		}
	}
}
elseif ($_GET['action'] == "save") {
	$digest = $gpc->get('digest', int);

	BBProfile($bbcode);
	$_POST['topic'] = $bbcode->parseTitle($_POST['topic']);

	$error = array();
	if (is_hash($fid)) {
		$error_data = import_error_data($fid);
	}
	if (flood_protect(FLOOD_TYPE_POSTING) == false) {
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

	$prefix_obj = $scache->load('prefix');
	$prefix_arr = $prefix_obj->get($board);

	if (!isset($prefix_arr[$_POST['opt_0']]) && $last['prefix'] == 1) {
		$error[] = $lang->phrase('prefix_not_optional');
	}

	($code = $plugins->load('newtopic_save_errorhandling')) ? eval($code) : null;

	if (count($error) > 0 || !empty($_POST['Preview'])) {
		$data = array(
			'topic' => $_POST['topic'],
			'comment' => $_POST['comment'],
			'prefix' => $_POST['opt_0'],
			'dosmileys' => $_POST['dosmileys'],
			'vote' => $_POST['opt_2'],
			'replies' => $_POST['temp'],
			'digest' => $digest
		);

		($code = $plugins->load('newtopic_save_errordata')) ? eval($code) : null;
		$fid = save_error_data($data, $fid);
		if (!empty($_POST['Preview'])) {
			$slog->updatelogged();
			sendStatusCode(302, $config['furl']."/newtopic.php?action=preview&id={$board}&fid=".$fid.SID2URL_JS_x);
		}
		else {
			error($error,"newtopic.php?id={$board}&amp;fid=".$fid.SID2URL_x);
		}
	}
	else {
		set_flood(FLOOD_TYPE_POSTING);

		$date = time();

		($code = $plugins->load('newtopic_save_savedata')) ? eval($code) : null;

		$db->query("
		INSERT INTO {$db->pre}topics (board,topic,name,date,last,last_name,prefix,vquestion)
		VALUES ('{$board}','{$_POST['topic']}','{$my->id}','{$date}','{$date}','{$my->id}','{$_POST['opt_0']}','')
		");
		$tredirect = $db->insert_id();

		$db->query("
		INSERT INTO {$db->pre}replies (topic,topic_id,name,comment,dosmileys,date,tstart,ip,edit,report)
		VALUES ('{$_POST['topic']}','{$tredirect}','{$my->id}','{$_POST['comment']}','{$_POST['dosmileys']}','{$date}','1','{$my->ip}','','')
		");
		$rredirect = $db->insert_id();

		$db->query("UPDATE {$db->pre}uploads SET topic_id = '{$tredirect}', tid = '{$rredirect}' WHERE mid = '{$my->id}' AND topic_id = '0' AND tid = '0'");

		// Insert notifications
		if ($digest != 0) {
			switch ($digest) {
				case 2:  $type = 'd'; break;
				case 3:  $type = 'w'; break;
				default: $type = '';  break;
			}
			$db->query("INSERT INTO {$db->pre}abos (mid, tid, type) VALUES ('{$my->id}', '{$tredirect}', '{$type}')");
		}

		if ($gpc->get('close', int) == 1 && $my->mp[0] == 1) {
			$db->query("UPDATE {$db->pre}topics SET status = '1' WHERE id = '{$tredirect}'");
		}
		if ($gpc->get('pin', int) == 1 && $my->mp[0] == 1) {
			$db->query("UPDATE {$db->pre}topics SET sticky = '1' WHERE id = '{$tredirect}'");
		}

		if ($config['updatepostcounter'] == 1 && $last['count_posts'] == 1) {
			$db->query ("UPDATE {$db->pre}user SET posts = posts+1 WHERE id = '{$my->id}'");
		}

		$db->query ("UPDATE {$db->pre}forums SET topics = topics+1, last_topic = '{$tredirect}' WHERE id = '{$board}'");
		$catobj = $scache->load('cat_bid');
		$catobj->delete();

		if (count($last['topic_notification']) > 0) {
			$to = array();
			foreach ($last['topic_notification'] as $mail) {
				$to[] = array('mail' => $mail);
			}
			$lang_dir = $lang->getdir(true);
			$lang->setdir($config['langdir']);
			$data = $lang->get_mail('new_topic');
			$lang->setdir($lang_dir);
			$from = array();
			xmail($to, $from, $data['title'], $data['comment']);
		}

		// Set topic read
		$slog->setTopicRead($tredirect, $topforums);

		($code = $plugins->load('newtopic_save_end')) ? eval($code) : null;

		if ($_POST['opt_2'] == '1') {
			ok($lang->phrase('new_thread_vote_success'),"newtopic.php?action=startvote&amp;id={$board}&amp;topic_id={$tredirect}&amp;temp={$_POST['temp']}");
		}
		else {
			ok($lang->phrase('data_success'),"showtopic.php?id={$tredirect}".SID2URL_x);
		}
	}

}
else {
	BBProfile($bbcode);

	$prefix_obj = $scache->load('prefix');
	$prefix_arr = $prefix_obj->get($board);

	$standard_data = array(
		'prefix' => 0,
		'vote' => '',
		'replies' => '',
		'comment' => '',
		'dosmileys' => 1,
		'topic' => '',
		'digest' => 0
	);

	if (is_hash($fid)) {
		$data = $gpc->unescape(import_error_data($fid));
		$info = array($data['topic']);
		if ($_GET['action'] == 'preview') {
			$bbcode->setSmileys($data['dosmileys']);
			$data['formatted_comment'] = $bbcode->parse($data['comment']);
			$prefix = '';
			if (isset($prefix_arr[$data['prefix']])) {
				$prefix = $prefix_arr[$data['prefix']]['value'];
			}
		}
		foreach ($standard_data as $key => $value) {
			if (!isset($data[$key])) {
				$data[$key] = $value;
			}
		}
	}
	else {
		$data = $standard_data;
		$_GET['action'] = $_POST['action'] = '';
	}

	if (count($prefix_arr) > 0) {
		array_columnsort($prefix_arr, "value");
		if ($last['prefix'] == 0) {
			$prefix_arr_standard = $prefix_arr;
			array_columnsort($prefix_arr_standard, "standard");
			$standard = end($prefix_arr_standard);
			if ($standard['standard'] == 1) {
				$sel = key($prefix_arr_standard);
			}
			else {
				$sel = 0;
			}
			unset($prefix_arr_standard, $standard);
			$prefix_arr = $prefix_obj->addEmptyPrefix($prefix_arr);
		}
		else {
			$sel = -1;
		}
		if ($data['prefix'] > 0) {
			$sel = $data['prefix'];
		}
	}

	($code = $plugins->load('newtopic_form_prepared')) ? eval($code) : null;

	echo $tpl->parse("newtopic/index");

	($code = $plugins->load('newtopic_form_end')) ? eval($code) : null;
}

($code = $plugins->load('newtopic_end')) ? eval($code) : null;

$slog->updatelogged();
$phpdoc->Out();