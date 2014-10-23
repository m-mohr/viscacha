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

define('SCRIPTNAME', 'addreply');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

($code = $plugins->load('addreply_topic_query')) ? eval($code) : null;

$id = $gpc->get('id', int);
$fid = $gpc->get('fid', str);

$result = $db->query("SELECT id, prefix, topic, board, posts, status FROM {$db->pre}topics WHERE id = '{$id}'");

$info = $db->fetch_assoc($result);
if ($db->num_rows($result) == 0) {
	error($lang->phrase('query_string_error'));
}
$my->p = $slog->Permissions($info['board']);

$info['topic'] = $gpc->prepare($info['topic']);

$cat_bid_obj = $scache->load('cat_bid');
$fc = $cat_bid_obj->get();
$last = $fc[$info['board']];
forum_opt($last, 'postreplies');

$prefix = '';
if ($info['prefix'] > 0) {
	$prefix_obj = $scache->load('prefix');
	$prefix_arr = $prefix_obj->get($info['board']);
	if (isset($prefix_arr[$info['prefix']])) {
		$prefix = $prefix_arr[$info['prefix']]['value'];
		$prefix = $lang->phrase('showtopic_prefix_title');
	}
}

$topforums = get_headboards($fc, $last, true);
$breadcrumb->Add($last['name'], "showforum.php?id=".$last['id'].SID2URL_x);
$breadcrumb->Add($prefix.$info['topic'], 'showtopic.php?id='.$id.SID2URL_x);
$breadcrumb->Add($lang->phrase('addreply_title'));

if ($info['status'] != 0) {
	error($lang->phrase('topic_closed'));
}

$p_upload = 0;
if ($config['tpcallow'] == 1 && $my->p['attachments'] == 1) {
	$p_upload = 1;
}

$standard_data = array(
			'name' => '',
			'email' => '',
			'guest' => iif($my->vlogin, 0, 1),
			'comment' => '',
			'dosmileys' => 1,
			'dowords' => 1,
			'digest' => -1,
			'topic' => $lang->phrase('reply_prefix').$info['topic'],
			'human' => false,
			'id' => $id
		);

($code = $plugins->load('addreply_start')) ? eval($code) : null;

if ($_GET['action'] == "save") {
	$digest = $gpc->get('digest', int);
	$error = array();
	if (is_hash($fid)) {
		$error_data = import_error_data($fid);
	}
	$human = empty($error_data['human']) ? false : $error_data['human'];
	if (!$my->vlogin) {
		if ($config['botgfxtest_posts'] > 0 && $human == false) {
			$captcha = newCAPTCHA('posts');
			$status = $captcha->check();
			if ($status == CAPTCHA_FAILURE) {
				$error[] = $lang->phrase('veriword_failed');
			}
			elseif ($status == CAPTCHA_MISTAKE) {
				$error[] = $lang->phrase('veriword_mistake');
			}
			else {
				$human = true;
			}
		}
		if (!check_mail($_POST['email']) && ($config['guest_email_optional'] == 0 || !empty($_POST['email']))) {
			$error[] = $lang->phrase('illegal_mail');
		}
		if (double_udata('name',$_POST['name']) == false) {
			$error[] = $lang->phrase('username_registered');
		}
		if (is_id($_POST['name'])) {
			$error[] = $lang->phrase('username_registered');
		}
		if (strxlen($_POST['name']) > $config['maxnamelength']) {
			$error[] = $lang->phrase('name_too_long');
		}
		if (strxlen($_POST['name']) < $config['minnamelength']) {
			$error[] = $lang->phrase('name_too_short');
		}
		if (strlen($_POST['email']) > 200) {
			$error[] = $lang->phrase('email_too_long');
		}
		$pname = $_POST['name'];
		$pnameid = $_POST['name'];
		$pid = 0;
	}
	else {
		$pname = $my->name;
		$pnameid = $my->id;
		$pid = $my->id;
	}
	if (flood_protect(FLOOD_TYPE_POSTING) == false) {
		$error[] = $lang->phrase('flood_control');
	}
	if (strxlen($_POST['comment']) > $config['maxpostlength']) {
		$error[] = $lang->phrase('comment_too_long');
	}
	if (strxlen($_POST['comment']) < $config['minpostlength']) {
		$error[] = $lang->phrase('comment_too_short');
	}
	// Add some chars for reply title prefix
	$maxlength = $config['maxtitlelength'] + strxlen($lang->phrase('reply_prefix'));
	if (strxlen($_POST['topic']) > $maxlength) {
		$error[] = $lang->phrase('title_too_long');
	}
	if (strxlen($_POST['topic']) < $config['mintitlelength']) {
		$error[] = $lang->phrase('title_too_short');
	}
	($code = $plugins->load('addreply_save_errorhandling')) ? eval($code) : null;

	BBProfile($bbcode);
	$_POST['topic'] = $bbcode->parseTitle($_POST['topic']);

	if (count($error) > 0 || !empty($_POST['Preview'])) {
		$data = array(
			'topic' => $_POST['topic'],
			'comment' => $_POST['comment'],
			'dosmileys' => $_POST['dosmileys'],
			'dowords' => $_POST['dowords'],
			'id' => $id,
			'digest' => $digest,
			'guest' => 0,
			'human' => $human,
			'name' => null,
			'email' => null
		);
		if (!$my->vlogin) {
			if ($config['guest_email_optional'] == 0 && empty($_POST['email'])) {
				$data['email'] = '';
			}
			else {
				$data['email'] = $_POST['email'];
			}
			$data['guest'] = 1;
			$data['name'] = $_POST['name'];
		}
		($code = $plugins->load('addreply_save_errordata')) ? eval($code) : null;
		$fid = save_error_data($data, $fid);
		if (!empty($_POST['Preview'])) {
			$slog->updatelogged();
			$db->close();
			sendStatusCode(307, $config['furl']."/addreply.php?action=preview&id={$id}&fid=".$fid.SID2URL_JS_x);
			exit;
		}
		else {
			error($error,"addreply.php?id={$id}&amp;fid=".$fid.SID2URL_x);
		}
	}
	else {
		set_flood(FLOOD_TYPE_POSTING);

		if ($my->vlogin) {
			$guest = 0;
		}
		else {
			$guest = 1;
		}

		$date = time();

		($code = $plugins->load('addreply_save_queries')) ? eval($code) : null;

		$db->query("
		UPDATE {$db->pre}topics
		SET last_name = '{$pnameid}', last = '{$date}', posts = posts+1
		WHERE id = '{$id}'
		");

		$db->query("
		INSERT INTO {$db->pre}replies (board,topic,topic_id,name,comment,dosmileys,dowords,email,date,ip,guest,edit,report)
		VALUES ('{$info['board']}','{$_POST['topic']}','{$id}','{$pnameid}','{$_POST['comment']}','{$_POST['dosmileys']}','{$_POST['dowords']}','{$_POST['email']}','{$date}','{$my->ip}','{$guest}','','')
		");
		$redirect = $db->insert_id();

		// Set uploads to correct reply
		$db->query("UPDATE {$db->pre}uploads SET tid = '{$redirect}' WHERE mid = '{$pid}' AND topic_id = '{$id}' AND tid = '0'");

		// Update, insert, delete notifications
		if ($my->vlogin) {
			$result = $db->query("SELECT id, type FROM {$db->pre}abos WHERE mid = '{$my->id}' AND tid = '{$id}'");
			switch ($digest) {
				case 1:  $type = '';  break;
				case 2:  $type = 'd'; break;
				case 3:  $type = 'w'; break;
				default: $type = null; break;
			}

			if ($db->num_rows($result) > 0) {
				$row = $db->fetch_assoc($result);
				if ($type === null) { // Lösche Abo
					$db->query("DELETE FROM {$db->pre}abos WHERE id = '{$row['id']}'");
				}
				elseif ($row['type'] != $type) { // Aktualisiere Abo, wenn veränderter Typ
					$db->query("UPDATE {$db->pre}abos SET type = '{$type}' WHERE id = '{$row['id']}'");
				}
			}
			else {
				if ($type !== null) { // Füge Abo hinzu
					$db->query("INSERT INTO {$db->pre}abos (mid, tid, type) VALUES ('{$my->id}', '{$id}', '{$type}')");
				}
			}
		}

		if ($config['updatepostcounter'] == 1 && $last['count_posts'] == 1) {
			$db->query ("UPDATE {$db->pre}user SET posts = posts+1 WHERE id = '{$my->id}'");
		}

		$db->query ("UPDATE {$db->pre}forums SET replies = replies+1, last_topic = '{$id}' WHERE id = '{$info['board']}'");

		$lang_dir = $lang->getdir(true);
		// ToDo: Send only one notification on more than one answer
		if ($config['multiple_instant_notifications'] == 1) {
			$notification_query = "
				SELECT t.id, t.topic, u.name, u.mail, u.language
				FROM {$db->pre}abos AS a
					LEFT JOIN {$db->pre}user AS u ON u.id = a.mid
					LEFT JOIN {$db->pre}topics AS t ON t.id = a.tid
				WHERE a.type = '' AND a.tid = '{$id}' AND a.mid != '{$my->id}'
			";
		}
		else {
			$notification_query = "
				SELECT t.id, t.topic, u.name, u.mail, u.language, COUNT(*) AS num
				FROM {$db->pre}abos AS a
					LEFT JOIN {$db->pre}user AS u ON u.id = a.mid
					LEFT JOIN {$db->pre}topics AS t ON t.id = a.tid
					INNER JOIN {$db->pre}replies AS r ON r.topic_id = t.id AND r.date >= u.lastvisit
				WHERE a.type = '' AND a.tid = '{$id}' AND a.mid != '{$my->id}'
				GROUP BY t.id HAVING num = 1
			";
		}
		$result = $db->query($notification_query);
		while ($row = $db->fetch_assoc($result)) {
			$lang->setdir($row['language']);
			$data = $lang->get_mail('digest_s');
			$to = array('0' => array('name' => $row['name'], 'mail' => $row['mail']));
			$from = array();
			xmail($to, $from, $data['title'], $data['comment']);
		}
		$lang->setdir($config['langdir']);
		if (count($last['reply_notification']) > 0) {
			$to = array();
			foreach ($last['reply_notification'] as $mail) {
				$to[] = array('mail' => $mail);
			}
			$data = $lang->get_mail('new_reply');
			$from = array();
			xmail($to, $from, $data['title'], $data['comment']);
		}
		$lang->setdir($lang_dir);

		$close = $gpc->get('close', int);
		if ($close == 1 && $my->vlogin) {
			$my->mp = $slog->ModPermissions($info['board']);
			if ($my->mp[0] == 1) {
				$db->query("UPDATE {$db->pre}topics SET status = '1' WHERE id = '{$info['id']}'");
			}
		}

		// Set topic read
		$slog->setTopicRead($info['id'], $topforums);

		($code = $plugins->load('addreply_save_end')) ? eval($code) : null;

		ok($lang->phrase('data_success'),"showtopic.php?id={$id}&amp;action=last".SID2URL_x);
	}
}
else {

	$qids = array();
	$my->mp = $slog->ModPermissions($info['board']);
	BBProfile($bbcode);

	($code = $plugins->load('addreply_form_start')) ? eval($code) : null;

	if (is_hash($fid)) {
		$data = $gpc->unescape(import_error_data($fid));
		if ($id != $data['id']) {
			error($lang->phrase('query_string_error'), 'showforum.php?id='.$info['board'].SID2URL_x);
		}
		$info['topic'] = $data['topic'];
		if ($_GET['action'] == 'preview') {
			$bbcode->setSmileys($data['dosmileys']);
			if ($config['wordstatus'] == 0) {
				$data['dowords'] = 0;
			}
			$bbcode->setReplace($data['dowords']);
			$data['formatted_comment'] = $bbcode->parse($data['comment']);
		}
		foreach ($standard_data as $key => $value) {
			if (!isset($data[$key])) {
				$data[$key] = $value;
			}
		}
	}
	else {
		$data = $standard_data;

		$memberdata_obj = $scache->load('memberdata');
		$memberdata = $memberdata_obj->get();

		// Multiquote
		$qids = $gpc->get('qid', arr_int);
		$pids = getcookie('vquote');
		if(!empty($pids) && preg_match("/^[0-9,]+$/", $pids)) {
			$qids = array_merge($qids, explode(',', $pids));
			$qids = array_unique($qids);
			makecookie($config['cookie_prefix'].'_vquote', '', 0);
		}

		if (count($qids) > 0) {

			$result = $db->query('
			SELECT name, comment, guest
			FROM '.$db->pre.'replies
			WHERE id IN('.implode(',',$qids).')
			LIMIT '.$config['maxmultiquote']
			);

			while($row = $gpc->prepare($db->fetch_assoc($result))) {
				if ($row['guest'] == 0) {
					if (isset($memberdata[$row['name']])) {
						$row['name'] = $memberdata[$row['name']];
					}
					else {
						$row['name'] = '';
					}
				}
				($code = $plugins->load('addreply_form_quotes')) ? eval($code) : null;
				$row['comment'] = preg_replace('/\[hide\](.+?)\[\/hide\]/is', '', $row['comment']);
				$row['comment'] = $bbcode->censor(trim($row['comment']));
				$data['comment'] .= "\r\n[quote".iif(!empty($row['name']), "=".$row['name'])."]";
				$data['comment'] .= $row['comment'];
				$data['comment'] .= "[/quote]\r\n";
			}
		}
	}

	if ($config['botgfxtest_posts'] > 0 && $data['human'] == false) {
		$captcha = newCAPTCHA('posts');
	}
	else {
		$captcha = null;
	}

	if ($my->vlogin && $data['digest'] == -1) {
		$result = $db->query("SELECT type FROM {$db->pre}abos WHERE mid = '{$my->id}' AND tid = '{$id}'");
		if ($db->num_rows($result) > 0) {
			$temp = $db->fetch_assoc($result);
			switch ($temp['type']) {
				case '':	$data['digest'] = 1; break;
				case 'd':	$data['digest'] = 2; break;
				case 'w':	$data['digest'] = 3; break;
				default:	$data['digest'] = 0; // Favoriten = Keine Benachrichtigung
			}
		}
		else {
			$data['digest'] = 0;
		}
	}

	echo $tpl->parse("header");
	echo $tpl->parse("menu");

	($code = $plugins->load('addreply_form_prepared')) ? eval($code) : null;

	echo $tpl->parse("addreply");

	($code = $plugins->load('addreply_form_end')) ? eval($code) : null;

}

($code = $plugins->load('addreply_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();
?>