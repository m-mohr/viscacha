<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2007  Matthias Mohr, MaMo Net

	Author: Matthias Mohr
	Publisher: http://www.viscacha.org
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

define('SCRIPTNAME', 'editprofile');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

if ($_GET['action'] != "addabo") {
	$my->p = $slog->Permissions();
}
if (!$my->vlogin) {
	errorLogin($lang->phrase('not_allowed'),'log.php');
}

include_once ("classes/function.profilefields.php");

$breadcrumb->Add($lang->phrase('editprofile_title'), 'editprofile.php'.SID2URL_1);

($code = $plugins->load('editprofile_start')) ? eval($code) : null;

if ($_GET['action'] == "pw2") {

	$error = array();
	if ($_POST['type'] != $_POST['pwx']) {
		$error[] = $lang->phrase('pw_comparison_failed');
	}
	if ($my->pw != md5($_POST['pw'])) {
		$error[] = $lang->phrase('old_pw_incorrect');
	}
	if (strlen($_POST['pwx']) > 200) {
		$error[] = $lang->phrase('pw_too_long');
	}
	if (strxlen($_POST['pwx']) < 3) {
		$error[] = $lang->phrase('pw_too_short');
	}
	($code = $plugins->load('editprofile_pw2_errorhandling')) ? eval($code) : null;
	if (count($error) > 0) {
		error($error,"editprofile.php?action=pw".SID2URL_x);
	}
	else {
		($code = $plugins->load('editprofile_pw2_query')) ? eval($code) : null;
		$db->query("UPDATE {$db->pre}user SET pw = MD5('{$_POST['pwx']}') WHERE id = '{$my->id}' LIMIT 1",__LINE__,__FILE__);
		$slog->sid_logout();
		ok($lang->phrase('editprofile_pw_success'), "log.php".SID2URL_1);
	}

}
elseif ($_GET['action'] == "attachments2" && $config['tpcallow'] == 1) {
	if (count($_POST['delete']) > 0) {
		($code = $plugins->load('editprofile_attachments2_start')) ? eval($code) : null;
		$result = $db->query ("SELECT source FROM {$db->pre}uploads WHERE mid = '$my->id' AND id IN(".implode(',', $_POST['delete']).")",__LINE__,__FILE__);
		while ($row = $db->fetch_assoc($result)) {
			$filesystem->unlink('uploads/topics/'.$row['source']);
		}
		$db->query ("DELETE FROM {$db->pre}uploads WHERE mid = '{$my->id}' AND id IN (".implode(',',$_POST['delete']).")",__LINE__,__FILE__);
		$anz = $db->affected_rows();
		ok($lang->phrase('editprofile_attachments_deleted'), "editprofile.php?action=attachments".SID2URL_x);
	}
	else {
		error($lang->phrase('query_string_error'), "editprofile.php?action=attachments".SID2URL_x);
	}

}
elseif ($_GET['action'] == "attachments" && $config['tpcallow'] == 1) {
	$breadcrumb->Add($lang->phrase('editprofile_attachments'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");

	($code = $plugins->load('editprofile_attachments_query')) ? eval($code) : null;
	$result = $db->query("
	SELECT r.board, r.topic, u.id, u.tid, u.file, u.source, u.hits
	FROM {$db->pre}uploads AS u
		LEFT JOIN {$db->pre}replies AS r ON r.id = u.tid
	WHERE u.mid = '$my->id'
	ORDER BY u.topic_id, u.tid
	",__LINE__,__FILE__);

	$all = array(0,0,0);
	$cache = array();
	while ($row = $db->fetch_assoc($result)) {
		$row['topic'] = $gpc->prepare($row['topic']);
		$uppath = 'uploads/topics/'.$row['source'];
		$fsize = filesize($uppath);
		$all[0]++;
		$all[1] += $fsize;
		$all[2] += $row['hits'];
		$row['hits'] = numbers($row['hits']);
		$row['fsize'] = formatFilesize($fsize);
		($code = $plugins->load('editprofile_attachments_entry_prepared')) ? eval($code) : null;
		$cache[] = $row;
	}
	$all[1] = formatFilesize($all[1]);
	$all[2] = numbers($all[2]);
	($code = $plugins->load('editprofile_attachments_prepared')) ? eval($code) : null;
	echo $tpl->parse("editprofile/attachments");
	($code = $plugins->load('editprofile_attachments_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "abos") {
	$p = $_GET['page']-1;

	$sqlwhere = '';
	if (!empty($_GET['type'])) {
		if ($_GET['type'] == 's') {
			$type = '';
		}
		else {
			$type = $_GET['type'];
		}
		$sqlwhere = " AND type = '{$type}'";
	}

	($code = $plugins->load('editprofile_abos_query')) ? eval($code) : null;
	$result = $db->query("
	SELECT a.id, a.tid, a.type, t.topic, t.prefix, t.last, t.last_name, t.board, t.posts
	FROM {$db->pre}abos AS a
		LEFT JOIN {$db->pre}topics AS t ON a.tid=t.id
		LEFT JOIN {$db->pre}forums AS f ON f.id=t.board
	WHERE a.mid = '{$my->id}' AND f.invisible != '2' {$sqlwhere}
	ORDER BY a.id DESC
	",__LINE__,__FILE__);

	$prefix_obj = $scache->load('prefix');
	$prefix_arr = $prefix_obj->get();
	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();
	$catbid = $scache->load('cat_bid');
	$fc = $catbid->get();

	$cache = array();
	while ($row = $db->fetch_assoc($result)) {
		$info = $fc[$row['board']];
		if ($info['topiczahl'] < 1) {
			$info['topiczahl'] = $config['topiczahl'];
		}

		if (!empty($row['prefix']) && isset($prefix_arr[$row['board']][$row['prefix']])) {
			$prefix = $prefix_arr[$row['board']][$row['prefix']]['value'];
			$row['prefix'] = $lang->phrase('showtopic_prefix_title');
		}
		else {
			$row['prefix'] = '';
		}
		$row['topic'] = $gpc->prepare($row['topic']);
		if ($row['type'] != 'd' && $row['type'] != 'w' && $row['type'] != 'f') {
			$row['type'] = 's';
		}

		if (is_id($row['last_name'])) {
			$row['last_name'] = $memberdata[$row['last_name']];
		}
		if ($slog->isTopicRead($row['tid'], $row['last'])) {
			$row['firstnew'] = 0;
			$row['alt'] = $lang->phrase('forum_icon_old');
			$row['src'] = $tpl->img('dir_open');
	 	}
	  	else {
			$row['firstnew'] = 1;
			$row['alt'] = $lang->phrase('forum_icon_new');
			$row['src'] = $tpl->img('dir_open2');
		}

		$row['last'] = str_date($lang->phrase('dformat1'),times($row['last']));


		if ($row['posts'] > $info['topiczahl']) {
			$row['topic_pages'] = pages($row['posts']+1, $info['topiczahl'], "showtopic.php?id=".$row['id']."&amp;", 0, '_small', false);
		}
		else {
			$row['topic_pages'] = '';
		}

		($code = $plugins->load('editprofile_abos_entry_prepared')) ? eval($code) : null;
		$cache[] = $row;
	}

	$count = count($cache);
	$pages = pages($count, $config['abozahl'], 'editprofile.php?action=abos&amp;type='.$_GET['type'].'&amp;', $_GET['page']);
	$cache = array_chunk($cache, $config['abozahl']);
	if (!isset($cache[$p])) {
		$count = 0;
	}

	$breadcrumb->Add($lang->phrase('editprofile_abos'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");

	($code = $plugins->load('editprofile_abos_prepared')) ? eval($code) : null;
	echo $tpl->parse("editprofile/abos");
	($code = $plugins->load('editprofile_abos_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "abos2") {
	$digest = $gpc->get('digest', arr_str);

	if (count($_POST['delete']) == 0 && count($digest) == 0) {
		error($lang->phrase('no_data_selected'), "editprofile.php?action=abos".SID2URL_x);
	}

	($code = $plugins->load('editprofile_abos2_start')) ? eval($code) : null;

	$anz = 0;
	if (count($_POST['delete']) > 0) {
		$delete = implode(',', $_POST['delete']);
		$db->query ("DELETE FROM `{$db->pre}abos` WHERE `mid` = '{$my->id}' AND `id` IN({$delete})",__LINE__,__FILE__);
		$anz = $db->affected_rows();
	}

	$anz2 = 0;
	if (count($digest) > 0) {
		$update = array('s' => array(),'d' => array(),'w' => array(),'f' => array());
		foreach ($digest as $id => $type) {
			$update[$type][] = $id;
		}
		foreach ($update as $type => $ids) {
			if (count($ids) > 0) {
				$ids = implode(',', $ids);
				$db->query("UPDATE `{$db->pre}abos` SET `type` = '{$type}' WHERE `mid` = '{$my->id}' AND `id` IN ({$id})",__LINE__,__FILE__);
				$anz2 += $db->affected_rows();
			}
		}
	}

	($code = $plugins->load('editprofile_abos2_end')) ? eval($code) : null;

	ok($lang->phrase('x_entries_deleted_x_changed'), "editprofile.php?action=abos".SID2URL_x);

}

elseif ($_GET['action'] == "pw") {
	$breadcrumb->Add($lang->phrase('editprofile_pw'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	($code = $plugins->load('editprofile_pw_start')) ? eval($code) : null;
	echo $tpl->parse("editprofile/pw");
	($code = $plugins->load('editprofile_pw_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "notice2") {

	$notes = array();
	foreach ($_POST['notice'] as $note) {
		if (!empty($note) && strxlen($note) > 2) {
			$notes[] = str_replace('[VSEP]','&#91;VSEP&#93;',$note);
		}
	}

	if (strxlen(implode('',$notes)) > $config['maxnoticelength']) {
		error($lang->phrase('notices_too_long'));
	}
	else {
		$sqlnotes = implode('[VSEP]',$notes);
		($code = $plugins->load('editprofile_notice2_query')) ? eval($code) : null;
		$db->query("UPDATE {$db->pre}user SET notice = '{$sqlnotes}' WHERE id = '{$my->id}' LIMIT 1",__LINE__,__FILE__);
		ok($lang->phrase('text_to_notice_success'), 'editprofile.php?action=notice'.SID2URL_x);
	}

}
elseif ($_GET['action'] == "notice") {
	$breadcrumb->Add($lang->phrase('editprofile_notice'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	if (empty($my->notice)) {
		$notices = array();
	}
	else {
		$notices = explode('[VSEP]',$my->notice);
		if (!is_array($notices)) {
			$notices = array($notices);
		}
	}
	foreach ($notices as $key => $note) {
		$notices[$key] = array(
			'length' => numbers(strxlen($note)),
			'text' => $note,
			'rows' => count_nl($note, 15)+1
		);
	}
	$notes = count($notices);
	$used_chars = numbers(strxlen(str_replace('[VSEP]', '', $my->notice)));
	$chars = numbers($config['maxnoticelength']);

	($code = $plugins->load('editprofile_prepared')) ? eval($code) : null;
	echo $tpl->parse("editprofile/notice");
	($code = $plugins->load('editprofile_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "signature") {
	if (!empty($_POST['Submit'])) {
		$error = array();
		if (strxlen($_POST['signature']) > $config['maxsiglength']) {
			$error[] = $lang->phrase('editprofile_signature_too_long');
		}
		($code = $plugins->load('editprofile_signature2_save')) ? eval($code) : null;
		if (count($error) > 0) {
			error($error, "editprofile.php?action=signature".SID2URL_x);
		}
		else {
			$db->query("UPDATE {$db->pre}user SET signature = '{$_POST['signature']}' WHERE id = '{$my->id}' LIMIT 1",__LINE__,__FILE__);
			ok($lang->phrase('data_success'), "editprofile.php?action=signature".SID2URL_x);
		}
	}
	else {
		$breadcrumb->Add($lang->phrase('editprofile_signature'));
		echo $tpl->parse("header");
		echo $tpl->parse("menu");
		BBProfile($bbcode);
		$chars = numbers($config['maxsiglength']);
		if (empty($_POST['signature'])) {
			$signature = $my->signature;
			$preview = false;
		}
		else {
			$signature = $gpc->unescape($_POST['signature']);
			$preview = true;
			BBProfile($bbcode, 'signature');
			$parsedPreview = $bbcode->parse($signature);
		}
		($code = $plugins->load('editprofile_signature_start')) ? eval($code) : null;
		echo $tpl->parse("editprofile/signature");
		($code = $plugins->load('editprofile_signature_end')) ? eval($code) : null;
	}
}
elseif ($_GET['action'] == "about2") {
	if ($my->p['useabout'] == 0) {
		errorLogin($lang->phrase('not_allowed'), "editprofile.php");
	}
	$error = array();
	if (strxlen($_POST['about']) > $config['maxaboutlength']) {
		$error[] = $lang->phrase('about_too_long');
	}
	($code = $plugins->load('editprofile_about2_start')) ? eval($code) : null;
	if (count($error) > 0 || !empty($_POST['Preview'])) {
		$fid = save_error_data($_POST['about']);
		if (!empty($_POST['Preview'])) {
			$slog->updatelogged();
			$db->close();
			viscacha_header("Location: editprofile.php?action=about&job=preview&fid=".$fid.SID2URL_JS_x);
			exit;
		}
		else {
			error($error, "editprofile.php?action=about&amp;fid=".$fid.SID2URL_x);
		}
	}
	else {
		($code = $plugins->load('editprofile_about2_query')) ? eval($code) : null;
		$db->query("UPDATE {$db->pre}user SET about = '{$_POST['about']}' WHERE id = '{$my->id}'");
		ok($lang->phrase('data_success'), "editprofile.php?action=about".SID2URL_x);
	}

}
elseif ($_GET['action'] == "about") {
	if ($my->p['useabout'] == 0) {
		errorLogin($lang->phrase('not_allowed'), "editprofile.php");
	}
	$breadcrumb->Add($lang->phrase('editprofile_about'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	($code = $plugins->load('editprofile_abos_Start')) ? eval($code) : null;

	BBProfile($bbcode);

	if (strlen($_GET['fid']) == 32) {
		$data = $gpc->prepare(import_error_data($_GET['fid']));
		if ($_GET['job'] == 'preview') {
			$preview = true;
			$data = $gpc->unescape($data);
			$parsedPreview = $bbcode->parse($data);
		}
		else {
			$preview = false;
		}
	}
	else {
		$data = $my->about;
		$preview = false;
	}

	$chars = numbers($config['maxaboutlength']);

	($code = $plugins->load('editprofile_abos_prepared')) ? eval($code) : null;
	echo $tpl->parse("editprofile/about");
	($code = $plugins->load('editprofile_abos_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "pic3") {

	($code = $plugins->load('editprofile_pic3_start')) ? eval($code) : null;
	if ($my->p['usepic'] == 0) {
		errorLogin($lang->phrase('not_allowed'), "editprofile.php");
	}
	removeOldImages('uploads/pics/', $my->id);
	$db->query("UPDATE {$db->pre}user SET pic = '' WHERE id = '{$my->id}' LIMIT 1",__LINE__,__FILE__);
	($code = $plugins->load('editprofile_pic3_end')) ? eval($code) : null;
	ok($lang->phrase('editprofile_pic_success'), "editprofile.php?action=pic".SID2URL_x);

}
elseif ($_GET['action'] == "pic2") {

	$pic = $gpc->get('pic', none);

	if ($my->p['usepic'] == 0) {
		errorLogin($lang->phrase('not_allowed'), "editprofile.php");
	}

	$error = array();
	if (isset($_FILES) && is_array($_FILES['upload']) && !empty($_FILES['upload']['name'])) {
		require("classes/class.upload.php");
		$my_uploader = new uploader();
		$my_uploader->max_filesize($config['avfilesize']);
		$my_uploader->max_image_size($config['avwidth'], $config['avheight']);
		$my_uploader->file_types(explode(',', $config['avfiletypes']));
		$my_uploader->set_path('uploads/pics/');
		$my_uploader->rename_file($my->id);
		if ($my_uploader->upload('upload')) {
			removeOldImages('uploads/pics/', $my->id);
			if ($my_uploader->save_file()) {
				$my->pic = 'uploads/pics/'.$my_uploader->fileinfo('filename');
			}
		}
		if ($my_uploader->upload_failed()) {
			$error[] = $my_uploader->get_error();
		}
	}
	elseif (!empty($pic) && preg_match(URL_REGEXP, $pic)) {
		$my->pic = checkRemotePic($pic, $my->id);
		switch ($my->pic) {
			case REMOTE_INVALID_URL:
				$error[] = $lang->phrase('editprofile_pic_error1');
				$my->pic = '';
			break;
			case REMOTE_CLIENT_ERROR:
				$error[] = $lang->phrase('editprofile_pic_error2');
				$my->pic = '';
			break;
			case REMOTE_FILESIZE_ERROR:
			case REMOTE_IMAGE_HEIGHT_ERROR:
			case REMOTE_IMAGE_WIDTH_ERROR:
			case REMOTE_EXTENSION_ERROR:
				$error[] = $lang->phrase('editprofile_pic_error3')." [ErrNo: {$my->pic}]";
				$my->pic = '';
			break;
			case REMOTE_IMAGE_ERROR:
				$error[] = $lang->phrase('editprofile_pic_error4');
				$my->pic = '';
			break;
		}
	}
	else {
		removeOldImages('uploads/pics/', $my->id);
	}
	if (count($error) == 0 && file_exists($my->pic) == false) {
		$error[] = $lang->phrase('unknown_error');
	}

	if (count($error) > 0) {
		error($error, 'editprofile.php?action=pic');
	}
	else {
		($code = $plugins->load('editprofile_pic2_query')) ? eval($code) : null;
		$db->query("UPDATE {$db->pre}user SET pic = '{$my->pic}' WHERE id = '{$my->id}' LIMIT 1",__LINE__,__FILE__);
		ok($lang->phrase('editprofile_pic_success'), "editprofile.php?action=pic".SID2URL_x);
	}
}
elseif ($_GET['action'] == "pic") {
	if ($my->p['usepic'] == 0) {
		errorLogin($lang->phrase('not_allowed'), "editprofile.php");
	}
	$breadcrumb->Add($lang->phrase('editprofile_pic'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	$filetypes = str_replace(",", $lang->phrase('listspacer'), $config['avfiletypes']);
	$filesize = formatFilesize($config['avfilesize']);

	$size = '';
	if ($config['avwidth'] > 0) {
		$size .= $lang->phrase('editprofile_pic_w1');
	}
	else {
		$size .= $lang->phrase('editprofile_pic_w2');
	}
	if ($config['avheight'] > 0) {
		$size .= $lang->phrase('editprofile_pic_h1');
	}
	else {
		$size .= $lang->phrase('editprofile_pic_h2');
	}

	($code = $plugins->load('editprofile_pic_prepared')) ? eval($code) : null;
	echo $tpl->parse("editprofile/pic");
}
elseif ($_GET['action'] == "profile") {
	$breadcrumb->Add($lang->phrase('editprofile_profile'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");

	($code = $plugins->load('editprofile_profile_start')) ? eval($code) : null;

	$bday = explode('-',$my->birthday);
	if (empty($bday[0]) || $bday[0] <= 1000) {
		$bday[0] = '0000';
	}
	if (empty($bday[1])) {
		$bday[1] = '00';
	}
	if (empty($bday[2])) {
		$bday[2] = '00';
	}
	$my->icq = iif(empty($my->icq), '', $my->icq);
	$year = gmdate('Y');
	$maxy = $year-6;
	$miny = $year-100;

	$customfields = editprofile_customfields(1, $my->id);

	($code = $plugins->load('editprofile_profile_prepared')) ? eval($code) : null;
	echo $tpl->parse("editprofile/profile");
	($code = $plugins->load('editprofile_profile_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "profile2") {

	$_POST['hp'] = trim($_POST['hp']);
	if (strtolower(substr($_POST['hp'], 0, 4)) == 'www.') {
		$_POST['hp'] = "http://{$_POST['hp']}";
	}

	$error = array();
	if (check_mail($_POST['email']) == false) {
		 $error[] = $lang->phrase('illegal_mail');
	}
	if ($my->mail != $_POST['email'] && double_udata('mail', $_POST['email']) == false) {
		 $error[] = $lang->phrase('email_already_used');
	}
	if ($config['changename_allowed'] == 1 && strxlen($_POST['name']) > $config['maxnamelength']) {
		$error[] = $lang->phrase('name_too_long');
	}
	if ($config['changename_allowed'] == 1 && strxlen($_POST['name']) < $config['minnamelength']) {
		$error[] = $lang->phrase('name_too_short');
	}
	if ($config['changename_allowed'] == 1 && strtolower($my->name) != strtolower($_POST['name']) && double_udata('name',$_POST['name']) == false) {
		$error[] = $lang->phrase('username_registered');
	}
	if (strlen($_POST['email']) > 200) {
		$error[] = $lang->phrase('email_too_long');
	}
	if (strlen($_POST['hp']) > 255) {
		$error[] = $lang->phrase('editprofile_homepage_too_long');
	}
	if (!check_hp($_POST['hp'])) {
		$_POST['hp'] = '';
	}
	if (strlen($_POST['location']) > 50) {
		$error[] = $lang->phrase('editprofile_location_too_long');
	}
	if ($_POST['gender'] != 'm' && $_POST['gender'] != 'w' && $_POST['gender'] != '') {
		$error[] = $lang->phrase('editprofile_gender_incorrect');
	}
	if ($_POST['birthday'] > 31) {
		$error[] = $lang->phrase('editprofile_birthday_incorrect');
	}
	if ($_POST['birthmonth'] > 12) {
		$error[] = $lang->phrase('editprofile_birthmonth_incorrect');
	}
	if (($_POST['birthyear'] < gmdate('Y')-120 || $_POST['birthyear'] > gmdate('Y')) && $_POST['birthyear'] != 0 ) {
		$error[] = $lang->phrase('editprofile_birthyear_incorrect');
	}
	if (strlen($_POST['fullname']) > 128) {
		$error[] = $lang->phrase('editprofile_fullname_incorrect');
	}

	$error_custom = editprofile_customsave(1, $my->id);
	$error = array_merge($error, $error_custom);
	($code = $plugins->load('editprofile_profile2_errorhandling')) ? eval($code) : null;

	if (count($error) > 0) {
		($code = $plugins->load('editprofile_profile2_errordata')) ? eval($code) : null;
		error($error, "editprofile.php?action=profile".SID2URL_x);
	}
	else {
		// Now we create the birthday...
		if (empty($_POST['birthmonth']) || empty($_POST['birthday'])) {
			$_POST['birthmonth'] = 0;
			$_POST['birthday'] = 0;
			$_POST['birthyear'] = 0;
		}
		if (empty($_POST['birthyear'])) {
			$_POST['birthyear'] = 1000;
		}
		$_POST['birthmonth'] = leading_zero($_POST['birthmonth']);
		$_POST['birthday'] = leading_zero($_POST['birthday']);
		$_POST['birthyear'] = leading_zero($_POST['birthyear'], 4);
		$bday = $_POST['birthyear'].'-'.$_POST['birthmonth'].'-'.$_POST['birthday'];

		$_POST['icq'] = str_replace('-', '', $_POST['icq']);
		if (!is_id($_POST['icq'])) {
			$_POST['icq'] = 0;
		}

		if ($config['changename_allowed'] == 1) {
			$changename = ", name = '{$_POST['name']}'";
		}
		else {
			$changename = '';
		}

		($code = $plugins->load('editprofile_profile2_query')) ? eval($code) : null;

		$db->query("UPDATE {$db->pre}user SET skype = '{$_POST['skype']}', icq = '{$_POST['icq']}', yahoo = '{$_POST['yahoo']}', aol = '{$_POST['aol']}', msn = '{$_POST['msn']}', jabber = '{$_POST['jabber']}', birthday = '{$bday}', gender = '{$_POST['gender']}', hp = '{$_POST['hp']}', location = '{$_POST['location']}', fullname = '{$_POST['fullname']}', mail = '{$_POST['email']}'{$changename} WHERE id = '{$my->id}' LIMIT 1",__LINE__,__FILE__);
		ok($lang->phrase('data_success'), "editprofile.php?action=profile".SID2URL_x);
	}

}
elseif ($_GET['action'] == "settings") {
	$lang->group("timezones");

	$breadcrumb->Add($lang->phrase('editprofile_settings'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");

	$result = $db->query("SELECT template, language FROM {$db->pre}user WHERE id = '{$my->id}' LIMIT 1");
	$update = $db->fetch_assoc($result);

	$loaddesign_obj = $scache->load('loaddesign');
	$design = $loaddesign_obj->get();
	if (!empty($my->settings['q_tpl']) && isset($design[$my->settings['q_tpl']])) {
		$mydesign = $design[$my->settings['q_tpl']]['name'];
		$my->template = $my->settings['q_tpl'];
	}
	elseif (isset($design[$update['template']])) {
		$mydesign = $design[$update['template']]['name'];
		$my->template = $update['template'];
	}
	else {
		$mydesign = $design[$config['templatedir']]['name'];
		$my->template = $config['templatedir'];
	}

	$loadlanguage_obj = $scache->load('loadlanguage');
	$language = $loadlanguage_obj->get();
	if (!empty($my->settings['q_lng']) && isset($language[$my->settings['q_lng']])) {
		$mylanguage = $language[$my->settings['q_lng']]['language'];
		$my->language = $my->settings['q_lng'];
	}
	elseif (isset($language[$update['language']])) {
		$mylanguage = $language[$update['language']]['language'];
		$my->language = $update['language'];
	}
	else {
		$mylanguage = $language[$config['langdir']]['language'];
		$my->language = $config['langdir'];
	}

	$time = gmdate($lang->phrase('dformat3'), times());

	$customfields = editprofile_customfields(2, $my->id);

	($code = $plugins->load('editprofile_settings_prepared')) ? eval($code) : null;
	echo $tpl->parse("editprofile/settings");
}
elseif ($_GET['action'] == "settings2") {

	$loaddesign_obj = $scache->load('loaddesign');
	$cache = $loaddesign_obj->get();

	$loadlanguage_obj = $scache->load('loadlanguage');
	$cache2 = $loadlanguage_obj->get();

	$error = array();
	if (intval($_POST['location']) < -12 && intval($_POST['location']) > 12) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('timezone');
	}
	if ($_POST['opt_0'] < 0 && $_POST['opt_0'] > 2) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_editor');
	}
	if ($_POST['opt_1'] != 0 && $_POST['opt_1'] != 1) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_emailpn');
	}
	if ($_POST['opt_2'] != 0 && $_POST['opt_2'] != 1) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_bad');
	}
	if ($_POST['opt_3'] < 0 && $_POST['opt_3'] > 2) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_showmail');
	}
	if ($config['hidedesign'] == 0 && $_POST['opt_4'] != 0 && !isset($cache[$_POST['opt_4']])) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_design');
	}
	if ($config['hidelanguage'] == 0 && $_POST['opt_5'] != 0 && !isset($cache2[$_POST['opt_5']])) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_language');
	}
	if ($_POST['opt_7'] != 0 && $_POST['opt_7'] != 1) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_showsig');
	}
	if ($_POST['opt_6'] < 0 && $_POST['opt_6'] > 2) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_newsletter');
	}

	$error_custom = editprofile_customsave(2, $my->id);
	$error = array_merge($error, $error_custom);

	($code = $plugins->load('editprofile_settings2_errorhandling')) ? eval($code) : null;

	if (count($error) > 0) {
		error($error,"editprofile.php?action=settings".SID2URL_x);
	}
	else {
		($code = $plugins->load('editprofile_settings2_query')) ? eval($code) : null;

		if ($config['hidedesign'] == 0 && $_POST['opt_4'] != 0 && isset($my->settings['q_tpl']) && $_POST['opt_4'] != $my->template) {
			unset($my->settings['q_tpl']);
		}
		if ($config['hidelanguage'] == 0 && $_POST['opt_5'] != 0 && isset($my->settings['q_lng']) && $_POST['opt_5'] != $my->language) {
			unset($my->settings['q_lng']);
		}

		$db->query("
		UPDATE {$db->pre}user
		SET
			".
			iif(($config['hidedesign'] == 0 &&  $_POST['opt_4'] > 0), "template = '{$_POST['opt_4']}',").
			iif(($config['hidelanguage'] == 0 && $_POST['opt_5'] > 0), "language = '{$_POST['opt_5']}',")
			."
			timezone = '{$_POST['location']}',
			opt_textarea = '{$_POST['opt_0']}',
			opt_pmnotify = '{$_POST['opt_1']}',
			opt_hidebad = '{$_POST['opt_2']}',
			opt_hidemail = '{$_POST['opt_3']}',
			opt_newsletter = '{$_POST['opt_6']}',
			opt_showsig = '{$_POST['opt_7']}'
		WHERE id = '{$my->id}'
		LIMIT 1
		",__LINE__,__FILE__);
		ok($lang->phrase('data_success'), "editprofile.php?action=settings".SID2URL_x);
	}

}
elseif ($_GET['action'] == "mylast") {
	$breadcrumb->Add($lang->phrase('editprofile_mylast'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");

	$cache = array();

	($code = $plugins->load('editprofile_mylast_query')) ? eval($code) : null;
	$result = $db->query("
	SELECT t.last, t.posts, t.id, t.board, r.topic, r.date, r.name, t.prefix, t.status, r.id AS pid
	FROM {$db->pre}replies AS r
		LEFT JOIN {$db->pre}topics AS t ON t.id = r.topic_id
		LEFT JOIN {$db->pre}forums AS f ON f.id = t.board
	WHERE r.name = '{$my->id}' AND f.invisible != '2'
	GROUP BY r.topic_id
	ORDER BY r.date DESC
	LIMIT 0, {$config['mylastzahl']}
	",__LINE__,__FILE__);
	$anz = $db->num_rows($result);

	$prefix_obj = $scache->load('prefix');
	$prefix_arr = $prefix_obj->get();
	$catbid = $scache->load('cat_bid');
	$fc = $catbid->get();

	while ($row = $db->fetch_assoc($result)) {
		$info = $fc[$row['board']];
		if ($info['topiczahl'] < 1) {
			$info['topiczahl'] = $config['topiczahl'];
		}

		$row['topic'] = $gpc->prepare($row['topic']);
		$row['name'] = $gpc->prepare($row['name']);

		if ($slog->isTopicRead($row['id'], $row['last'])) {
	 		$row['firstnew'] = 0;
			if ($row['status'] == 1 || $row['status'] == 2) {
			   	$row['alt'] = $lang->phrase('forum_icon_closed');
				$row['src'] = $tpl->img('dir_closed');
			}
			else {
			   	$row['alt'] = $lang->phrase('forum_icon_old');
			   	$row['src'] = $tpl->img('dir_open');
	 		}
	 	}
	  	else {
	  		$row['firstnew'] = 1;
			if ($row['status'] == 1 || $row['status'] == 2) {
				$row['alt'] = $lang->phrase('forum_icon_closed');
				$row['src'] = $tpl->img('dir_closed2');
			}
			else {
				$row['alt'] = $lang->phrase('forum_icon_new');
				$row['src'] = $tpl->img('dir_open2');
			}
		}

		if (isset($prefix_arr[$row['board']][$row['prefix']]) && $row['prefix'] > 0) {
			$prefix = $prefix_arr[$row['board']][$row['prefix']]['value'];
			$row['pre'] = $lang->phrase('showtopic_prefix_title');
		}
		else {
			$row['pre'] = '';
		}
		if ($row['posts'] > $info['topiczahl']) {
			$row['topic_pages'] = pages($row['posts']+1, $info['topiczahl'], "showtopic.php?id=".$row['id']."&amp;", 0, '_small', false);
		}
		else {
			$row['topic_pages'] = '';
		}
		$row['posts'] = numbers($row['posts']);
		($code = $plugins->load('editprofile_mylast_entry_prepared')) ? eval($code) : null;
		$cache[] = $row;
	}

	($code = $plugins->load('editprofile_mylast_prepared')) ? eval($code) : null;
	echo $tpl->parse("editprofile/mylast");
	($code = $plugins->load('editprofile_mylast_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "addabo") {
	$result = $db->query('SELECT id, board FROM '.$db->pre.'topics WHERE id = '.$_GET['id'],__LINE__,__FILE__);
	$info = $db->fetch_assoc($result);
	$my->p = $slog->Permissions($info['board']);

	$catbid = $scache->load('cat_bid');
	$fc = $catbid->get();
	$last = $fc[$info['board']];
	forum_opt($last);

	if ($_GET['type'] == 0) {
		$type = '';
	}
	elseif ($_GET['type'] == 1) {
		$type = 'd';
	}
	elseif ($_GET['type'] == 7) {
		$type = 'w';
	}
	elseif ($_GET['type'] == 9) {
		$type = 'f';
	}
	else {
		$error = true;
		($code = $plugins->load('editprofile_addabo_types')) ? eval($code) : null;
		if ($error == true) {
			error($lang->phrase('query_string_error'));
		}
	}

	($code = $plugins->load('editprofile_addabo_prepared')) ? eval($code) : null;
	$result = $db->query('SELECT id, type FROM '.$db->pre.'abos WHERE tid = '.$info['id'].' AND mid = '.$my->id,__LINE__,__FILE__);
	if ($db->num_rows($result) > 0) {
		error($lang->phrase('addabo_error'));
	}
	else {
		$db->query('INSERT INTO '.$db->pre.'abos (tid,mid,type) VALUES ("'.$_GET['id'].'","'.$my->id.'","'.$type.'")',__LINE__,__FILE__);
		ok($lang->phrase('subscribed_successfully'));
	}
}
elseif ($_GET['action'] == "removeabo") {
	($code = $plugins->load('editprofile_removeabo_start')) ? eval($code) : null;
	$result = $db->query('SELECT id, board FROM '.$db->pre.'topics WHERE id = '.$_GET['id'],__LINE__,__FILE__);
	$info = $db->fetch_assoc($result);
	$my->p = $slog->Permissions($info['board']);

	$catbid = $scache->load('cat_bid');
	$fc = $catbid->get();
	$last = $fc[$info['board']];
	forum_opt($last);

	($code = $plugins->load('editprofile_removeabo_prepared')) ? eval($code) : null;
	$db->query("DELETE FROM {$db->pre}abos WHERE tid = '{$_GET['id']}' AND mid = '{$my->id}' LIMIT 1",__LINE__,__FILE__);
	ok($lang->phrase('unsubscribed_successfully'));
}
else {
	$breadcrumb->ResetUrl();
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	($code = $plugins->load('editprofile_index_start')) ? eval($code) : null;
	echo $tpl->parse("editprofile/index");
	($code = $plugins->load('editprofile_index_end')) ? eval($code) : null;
}

($code = $plugins->load('editprofile_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();
?>
