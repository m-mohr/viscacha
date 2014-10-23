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

DEFINE('SCRIPTNAME', 'editprofile');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();
if ($_GET['action'] != "addabo" && $_GET['action'] != "addfav" && $_GET['action'] != "copy") {
	$my->p = $slog->Permissions();
}
if (!$my->vlogin) {
    errorLogin($lang->phrase('not_allowed'),'log.php');
}

$breadcrumb->Add($lang->phrase('editprofile_title'), 'editprofile.php'.SID2URL_1);

if ($_GET['action'] == "pw2") {

    $error = array();
	if ($_POST['type'] != $_POST['pwx']) {
		$error[] = $lang->phrase('pw_comparison_failed');
	}
	if ($my->pw != md5($_POST['pw'])) {
		$error[] = $lang->phrase('old_pw_incorrect');
	}
	if (strxlen($_POST['pwx']) > 200) {
		$error[] = $lang->phrase('pw_too_long');
	}
	if (strxlen($_POST['pwx']) < 3) {
		$error[] = $lang->phrase('pw_too_short');
	}
	if (count($error) > 0) {
		error($error,"editprofile.php?action=pw".SID2URL_x);
	}
	else {
	    $db->query("UPDATE {$db->pre}user SET pw = MD5('{$_POST['pwx']}') WHERE id = '$my->id' LIMIT 1",__LINE__,__FILE__);
	    ok($lang->phrase('editprofile_pw_success'), "log.php".SID2URL_1);
	}

}
elseif ($_GET['action'] == "abos2") {
	if (count($_POST['delete']) < 1) {
		error($lang->phrase('no_data_selected'), "editprofile.php?action=abos".SID2URL_x);
	}

	$db->query ("DELETE FROM {$db->pre}abos WHERE mid = '$my->id' AND id IN(".implode(',',$_POST['delete']).")",__LINE__,__FILE__);
	$anz = $db->affected_rows();
	ok($lang->phrase('x_entries_deleted'), "editprofile.php?action=abos".SID2URL_x);

}

elseif ($_GET['action'] == "fav2") {
	if (count($_POST['delete']) < 1) {
		error($lang->phrase('no_data_selected'), "editprofile.php?action=fav".SID2URL_x);
	}

	$db->query ("DELETE FROM {$db->pre}fav WHERE mid = '$my->id' AND id IN(".implode(',',$_POST['delete']).")",__LINE__,__FILE__);
	$anz = $db->affected_rows();
	ok($lang->phrase('x_entries_deleted'), "editprofile.php?action=fav".SID2URL_x);

}
elseif ($_GET['action'] == "attachments2" && $config['tpcallow'] == 1) {
	if (count($_POST['delete']) > 0) {
		$result = $db->query ("SELECT file FROM {$db->pre}uploads WHERE mid = '$my->id' AND id IN(".implode(',', $_POST['delete']).")",__LINE__,__FILE__);
	    while ($row = $db->fetch_assoc($result)) {
	        @unlink('uploads/topics/'.$row['file']);
	    }
		$db->query ("DELETE FROM {$db->pre}uploads WHERE mid = '$my->id' AND id IN (".implode(',',$_POST['delete']).")",__LINE__,__FILE__);
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

    $result = $db->query("SELECT r.board, r.topic, u.id, u.tid, u.file, u.hits FROM {$db->pre}uploads AS u LEFT JOIN {$db->pre}replies AS r ON r.id = u.tid WHERE u.mid = '$my->id' ORDER BY u.topic_id, u.tid",__LINE__,__FILE__);

    $all = array(0,0,0);
    $cache = array();
    while ($row = $db->fetch_assoc($result)) {
    	$row['topic'] = $gpc->prepare($row['topic']);
        $row['file'] = trim($row['file']);
        $uppath = 'uploads/topics/'.$row['file'];
        $fsize = filesize($uppath);
        $all[0]++;
        $all[1] += $fsize;
        $all[2] += $row['hits'];
        $row['hits'] = numbers($row['hits']);
        $row['fsize'] = formatFilesize($fsize);
        $cache[] = $row;
    }
    $all[1] = formatFilesize($all[1]);
    $all[2] = numbers($all[2]);
    echo $tpl->parse("editprofile/attachments");
}
elseif ($_GET['action'] == "abos") {
    $breadcrumb->Add($lang->phrase('editprofile_abos'));
	echo $tpl->parse("header");
    echo $tpl->parse("menu");

    $result = $db->query("SELECT a.id, a.tid, t.topic, a.type, t.prefix FROM {$db->pre}abos AS a LEFT JOIN {$db->pre}topics AS t ON a.tid=t.id WHERE a.mid = '{$my->id}' ORDER BY a.id DESC",__LINE__,__FILE__);

	$prefix = cache_prefix();

    $cache = array();
    while ($row = $db->fetch_assoc($result)) {
    	if (!empty($row['prefix']) && isset($prefix[$row['board']][$row['prefix']])) {
    		$row['prefix'] = '['.$prefix[$row['board']][$row['prefix']].']';
    	}
    	else {
    		$row['prefix'] = '';
    	}
    	$row['topic'] = $gpc->prepare($row['topic']);
	    if ($row['type'] == 'd') {
	    	$row['period'] = $lang->phrase('editprofile_digest_d');
	    }
	    elseif ($row['type'] == 'w') {
	    	$row['period'] = $lang->phrase('editprofile_digest_w');
	    }
	    else {
	    	$row['period'] = $lang->phrase('editprofile_digest_s');
	    }

	    $cache[] = $row;
	}

    echo $tpl->parse("editprofile/abos");

}
elseif ($_GET['action'] == "fav") {
    $breadcrumb->Add($lang->phrase('editprofile_fav'));
	echo $tpl->parse("header");
    echo $tpl->parse("menu");

	$prefix = cache_prefix();
	$memberdata = cache_memberdata();
    $result = $db->query("SELECT f.id, f.tid, t.topic, t.last, t.last_name, t.board, t.prefix FROM {$db->pre}fav AS f LEFT JOIN {$db->pre}topics AS t ON f.tid=t.id WHERE f.mid = '{$my->id}' ORDER BY f.id DESC",__LINE__,__FILE__);
    $cache = array();

    while ($row = $gpc->prepare($db->fetch_assoc($result))) {
		$showprefix = '';
		if (isset($prefix[$row['board']][$row['prefix']]) && $row['prefix'] > 0) {
			$showprefix = $prefix[$row['board']][$row['prefix']];
		}

		if (is_id($row['last_name'])) {
			$row['last_name'] = $memberdata[$row['last_name']];
		}
		if ((isset($my->mark['t'][$row['tid']]) && $my->mark['t'][$row['tid']] > $row['last']) || $row['last'] < $my->clv) {
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
    	$cache[] = $row;
    }
    echo $tpl->parse("editprofile/fav");
}

elseif ($_GET['action'] == "pw") {
	$breadcrumb->Add($lang->phrase('editprofile_pw'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	echo $tpl->parse("editprofile/pw");
	$mymodules->load('editprofile_pw_bottom');
}
elseif ($_GET['action'] == "notice2") {

	$notes = array();
	foreach ($_POST['notice'] as $note) {
		if (!empty($note) && strlen($note) > 2) {
			$notes[] = str_replace('[VSEP]','&#91;VSEP&#93;',$note);
		}
	}

	if (strxlen(implode('',$notes)) > $config['maxnoticelength']) {
		error($lang->phrase('notices_too_long'));
	}
	else {
		$db->query("UPDATE {$db->pre}user SET notice = '".implode('[VSEP]',$notes)."' WHERE id = '".$my->id."' LIMIT 1",__LINE__,__FILE__);
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
	$mymodules->load('editprofile_notice_top');
	echo $tpl->parse("editprofile/notice");
}
elseif ($_GET['action'] == "about2") {
    if ($my->p['useabout'] == 0) {
    	errorLogin($lang->phrase('not_allowed'), "editprofile.php");
    }
	if (strxlen($_POST['comment']) > $config['maxaboutlength']) {
		$fid = save_error_data($_POST['comment']);
		error($lang->phrase('about_too_long'), "editprofile.php?action=about&amp;fid=".$fid.SID2URL_x);
	}
	else {
	    $db->query("UPDATE {$db->pre}user SET about = '".$_POST['comment']."' WHERE id = '".$my->id."'");
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
	if (strlen($_GET['fid']) == 32) {
		$data = $gpc->prepare(import_error_data($_GET['fid']));
	}
	else {
		$data = $my->about;
	}
	$chars = numbers($config['maxaboutlength']);
	$bbcode = initBBCodes();
	$inner['bbhtml'] = $bbcode->getbbhtml();
	$inner['smileys'] = $bbcode->getsmileyhtml($config['smileysperrow']);
	$mymodules->load('editprofile_about_top');
	echo $tpl->parse("editprofile/about");
}
elseif ($_GET['action'] == "pic2") {

    $pic = $gpc->get('pic', none);
    if ($my->p['usepic'] == 0) {
		errorLogin($lang->phrase('not_allowed'), "editprofile.php");
	}
	elseif (isset($_FILES) && is_array($_FILES['upload']) && !empty($_FILES['upload']['name'])) {
		require("classes/class.upload.php");
		$my_uploader = new uploader();
		$my_uploader->max_filesize($config['avfilesize']);
		$my_uploader->max_image_size($config['avwidth'], $config['avheight']);
		if ($my_uploader->upload('upload', explode('|', $config['avfiletypes']))) {
			$my_uploader->save_file('uploads/pics/', '2');
		}
		if ($my_uploader->return_error()) {
			error($my_uploader->return_error(),'editprofile.php?action=pic');
		}
		else {
			if (file_exists($my->pic)) {
				@unlink($my->pic);
			}
			$ext = $my_uploader->rename_file('uploads/pics/', $my_uploader->file['name'], $my->id);
		}
		$my->pic = 'uploads/pics/'.$my->id.$ext;
	}
	elseif (!empty($pic) && preg_match('/^(http:\/\/|www.)([\wäöüÄÖÜ@\-_\.]+)\:?([0-9]*)\/(.*)$/', $pic, $url_ary)) {
		$my->pic = checkRemotePic($pic, $url_ary, $my->id);
	}
	else {
		removeOldImages('uploads/pics/', $my->id);
	}
	$db->query("UPDATE {$db->pre}user SET pic = '{$my->pic}' WHERE id = '{$my->id}' LIMIT 1",__LINE__,__FILE__);
	ok($lang->phrase('editprofile_pic_success'), "editprofile.php?action=pic".SID2URL_x);
}
elseif ($_GET['action'] == "pic") {
    if ($my->p['usepic'] == 0) {
    	errorLogin($lang->phrase('not_allowed'), "editprofile.php");
    }
    $breadcrumb->Add($lang->phrase('editprofile_pic'));
	echo $tpl->parse("header");
    echo $tpl->parse("menu");
    $filetypes = str_replace("|", ", ", $config['avfiletypes']);
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

	$mymodules->load('editprofile_pic_top');
    echo $tpl->parse("editprofile/pic");
}
elseif ($_GET['action'] == "profile2") {

	$_POST['hp'] = trim($_POST['hp']);
	if (strtolower(substr($_POST['hp'], 0, 4)) == 'www.') {
		$_POST['hp'] = "http://".$_POST['hp'];
	}
    $error = array();
	if (check_mail($_POST['email']) == FALSE) {
		 $error[] = $lang->phrase('illegal_mail');
	}
	if (strxlen($_POST['name']) > $config['maxnamelength'] && $config['changename_allowed'] == 1) {
		$error[] = $lang->phrase('name_too_long');
	}
	if (strxlen($_POST['name']) < $config['minnamelength'] && $config['changename_allowed'] == 1) {
		$error[] = $lang->phrase('name_too_short');
	}
	if (strxlen($_POST['email']) > 200) {
		$error[] = $lang->phrase('email_too_long');
	}
	if (strxlen($_POST['signature']) > $config['maxsiglength']) {
		$error[] = $lang->phrase('editprofile_signature_too_long');
	}
	if (strxlen($_POST['hp']) > 254) {
		$error[] = $lang->phrase('editprofile_homepage_too_long');
	}
	if (!check_hp($_POST['hp'])) {
		$_POST['hp'] = '';
	}
	if (strxlen($_POST['location']) > 50) {
		$error[] = $lang->phrase('editprofile_location_too_short');
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
	if (strxlen($_POST['fullname']) > 128) {
		$error[] = $lang->phrase('editprofile_fullname_incorrect');
	}

	if (count($error) > 0) {
		error($error, "editprofile.php?action=profile".SID2URL_x);
	}
	else {
	    // Now we create the birthday...
	    if (!$_POST['birthmonth'] && !$_POST['birthday'] && !$_POST['birthyear']) {
	    	$bday = '0000-00-00';
	    }
	    else {
	        $_POST['birthmonth'] = leading_zero($_POST['birthmonth']);
	        $_POST['birthday'] = leading_zero($_POST['birthday']);
	        $_POST['birthyear'] = leading_zero($_POST['birthyear'],4);
	        $bday = $_POST['birthyear'].'-'.$_POST['birthmonth'].'-'.$_POST['birthday'];
	    }
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

		$db->query("UPDATE {$db->pre}user SET icq = '{$_POST['icq']}', yahoo = '{$_POST['yahoo']}', aol = '{$_POST['aol']}', msn = '{$_POST['msn']}', jabber = '{$_POST['jabber']}', birthday = '{$bday}', gender = '{$_POST['gender']}', hp = '{$_POST['hp']}', signature = '{$_POST['signature']}', location = '{$_POST['location']}', fullname = '{$_POST['fullname']}', mail = '{$_POST['email']}'{$changename} WHERE id = '{$my->id}' LIMIT 1",__LINE__,__FILE__);
		ok($lang->phrase('data_success'), "editprofile.php?action=profile".SID2URL_x);
	}

}
elseif ($_GET['action'] == "settings") {
	$breadcrumb->Add($lang->phrase('editprofile_settings'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	$design = cache_loaddesign();
	$mydesign = $design[$my->template]['name'];
	$language = cache_loadlanguage();
	$mylanguage = $language[$my->language]['language'];
	$mymodules->load('editprofile_settings_top');
    echo $tpl->parse("editprofile/settings");
}
elseif ($_GET['action'] == "settings2") {

	$cache = cache_loaddesign();
	$cache2 = cache_loadlanguage();

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
	if (!isset($cache[$_POST['opt_4']])) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_design');
	}
	if (!isset($cache2[$_POST['opt_5']])) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_language');
	}
	if ($_POST['opt_7'] != 0 && $_POST['opt_7'] != 1) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_showsig');
	}
	if ($_POST['opt_6'] < 0 && $_POST['opt_6'] > 2) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_newsletter');
	}
	if (count($error) > 0) {
		error($error,"editprofile.php?action=settings".SID2URL_x);
	}
	else {
		$db->query("UPDATE {$db->pre}user SET timezone = '".$_POST['location']."', opt_textarea = '".$_POST['opt_0']."', opt_pmnotify = '".$_POST['opt_1']."', opt_hidebad = '".$_POST['opt_2']."', opt_hidemail = '".$_POST['opt_3']."', template = '".$_POST['opt_4']."', language = '".$_POST['opt_5']."', opt_newsletter = '".$_POST['opt_6']."', opt_showsig = '".$_POST['opt_7']."' WHERE id = $my->id LIMIT 1",__LINE__,__FILE__);
		ok($lang->phrase('data_success'), "editprofile.php?action=settings".SID2URL_x);
	}

}
elseif ($_GET['action'] == "profile") {
    $breadcrumb->Add($lang->phrase('editprofile_profile'));
	echo $tpl->parse("header");
    echo $tpl->parse("menu");

    $bday = explode('-',$my->birthday);
    if (empty($bday[0])) {
    	$bday[0] = '0000';
    }
    if (empty($bday[1])) {
    	$bday[1] = '00';
    }
    if (empty($bday[2])) {
    	$bday[2] = '00';
    }
    $year = gmdate('Y');
    $maxy = $year-6;
    $miny = $year-100;
    $mymodules->load('editprofile_profile_top');
    echo $tpl->parse("editprofile/profile");
}
elseif ($_GET['action'] == "mylast") {
    $breadcrumb->Add($lang->phrase('editprofile_mylast'));
	echo $tpl->parse("header");
    echo $tpl->parse("menu");

	$cache = array();
    $result = $db->query("SELECT t.last, t.posts, t.id, t.board, r.topic, r.date, r.name, t.prefix, r.id AS pid FROM {$db->pre}replies AS r LEFT JOIN {$db->pre}topics AS t ON t.id = r.topic_id WHERE r.name = '$my->id' GROUP BY r.topic_id ORDER BY r.date DESC LIMIT 0, ".$config['mylastzahl'],__LINE__,__FILE__);
    $anz = $db->num_rows($result);
    $prefix = cache_prefix();

    while ($row = $db->fetch_assoc($result)) {
    	$row['topic'] = $gpc->prepare($row['topic']);
    	$row['name'] = $gpc->prepare($row['name']);
		if ((isset($my->mark['t'][$row['id']]) && $my->mark['t'][$row['id']] > $row['last']) || $row['last'] < $my->clv) {
			$row['firstnew'] = 0;
			$row['alt'] = $lang->phrase('forum_icon_old');
			$row['src'] = $tpl->img('dir_open');
	 	}
	  	else {
			$row['firstnew'] = 1;
			$row['alt'] = $lang->phrase('forum_icon_new');
			$row['src'] = $tpl->img('dir_open2');
		}
		if (isset($prefix[$row['board']][$row['prefix']]) && $row['prefix'] > 0) {
			$row['pre'] = '['.$prefix[$row['board']][$row['prefix']].']';
		}
		else {
			$row['pre'] = '';
		}
		$row['posts'] = numbers($row['posts']);
		$cache[] = $row;
    }
    $mymodules->load('editprofile_mylast_top');
    echo $tpl->parse("editprofile/mylast");
    $mymodules->load('editprofile_mylast_bottom');
}
elseif ($_GET['action'] == "addfav") {
	$result = $db->query('SELECT id, board FROM '.$db->pre.'topics WHERE id = '.$_GET['id'],__LINE__,__FILE__);
	$info = $db->fetch_assoc($result);
	$my->p = $slog->Permissions($info['board']);

	$fc = cache_cat_bid();
	$last = $fc[$info['board']];
	forum_opt($last['opt'], $last['optvalue'], $last['id']);

	$result = $db->query('SELECT id FROM '.$db->pre.'fav WHERE tid = '.$info['id'].' AND mid = '.$my->id,__LINE__,__FILE__);
	if ($db->num_rows($result) > 0) {
		error($lang->phrase('addfav_error'));
	}
	else {
		$db->query('INSERT INTO '.$db->pre.'fav (tid,mid) VALUES ("'.$_GET['id'].'","'.$my->id.'")',__LINE__,__FILE__);
		ok($lang->phrase('data_success'));
	}
}
elseif ($_GET['action'] == "addabo") {
	$result = $db->query('SELECT id, board FROM '.$db->pre.'topics WHERE id = '.$_GET['id'],__LINE__,__FILE__);
	$info = $db->fetch_assoc($result);
	$my->p = $slog->Permissions($info['board']);

	$fc = cache_cat_bid();
	$last = $fc[$info['board']];
	forum_opt($last['opt'], $last['optvalue'], $last['id']);

	if ($_GET['temp'] == 0) {
		$type = '';
	}
	elseif ($_GET['temp'] == 1) {
		$type = 'd';
	}
	elseif ($_GET['temp'] == 7) {
		$type = 'w';
	}
	else {
		error($lang->phrase('query_string_error'));
	}

	$result = $db->query('SELECT id, type FROM '.$db->pre.'abos WHERE tid = '.$info['id'].' AND mid = '.$my->id,__LINE__,__FILE__);
	if ($db->num_rows($result) > 0) {
		error($lang->phrase('addabo_error'));
	}
	else {
		$db->query('INSERT INTO '.$db->pre.'abos (tid,mid,type) VALUES ("'.$_GET['id'].'","'.$my->id.'","'.$type.'")',__LINE__,__FILE__);
		ok($lang->phrase('data_success'));
	}
}
elseif ($_GET['action'] == "copy") {

	$result = $db->query("SELECT board, id, topic_id, topic, comment, date, name, email, dosmileys FROM {$db->pre}replies WHERE id = '{$_GET['id']}'",__LINE__,__FILE__);
    $row = $gpc->prepare($db->fetch_assoc($result));
	$error = array();
	if ($db->num_rows($result) < 1) {
		$error[] = $lang->phrase('query_string_error');
	}
	$my->p = $slog->Permissions($row['board']);
	if ($my->p['forum'] == 0) {
		$error[] = $lang->phrase('not_allowed');
	}
	if (count($error) > 0) {
		errorLogin($error,'forum.php'.SID2URL_1);
	}

    $result = $db->query("SELECT status, prefix FROM {$db->pre}topics WHERE id = {$row['topic_id']} LIMIT 1");
	$topic = $db->fetch_assoc($result);

	$fc = cache_cat_bid();
	$last = $fc[$row['board']];
	forum_opt($last['opt'], $last['optvalue'], $last['id']);

	$memberdata = cache_memberdata();

    if (empty($row['email']) && isset($memberdata[$row['name']])) {
    	$row['name'] = $memberdata[$row['name']];
    }
    $row['date'] = gmdate($lang->phrase('dformat1'), times($row['date']));

	$bbcode = initBBCodes();
	$bbcode->setSmileys($row['dosmileys']);
	$bbcode->setReplace($config['wordstatus']);
	if ($topic['status'] == 2) {
		$row['comment'] = $bbcode->ReplaceTextOnce($row['comment'], 'moved');
	}
    $text = $bbcode->parse($row['comment'],'plain');

	if (!empty($my->notice)) {
		$notes = explode('[VSEP]', $my->notice);
		if (!is_array($notes)) {
			$notes = array($notes);
		}
	}
	else {
		$notes = array();
	}

	$setnotice = $lang->get_text('notice');
	$notes[] = str_replace('[VSEP]','&#91;VSEP&#93;',$setnotice);
	if (strxlen(implode('',$notes)) > $config['maxnoticelength']) {
		error($lang->phrase('notices_too_long'));
	}

    $db->query("UPDATE {$db->pre}user SET notice = '".implode('[VSEP]',$notes)."' WHERE id = '".$my->id."'",__LINE__,__FILE__);
    ok($lang->phrase('text_to_notice_success'));

}
else {
	$breadcrumb->ResetUrl();
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	echo $tpl->parse("editprofile/index");
    $mymodules->load('editprofile_index_bottom');
}

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();
?>
