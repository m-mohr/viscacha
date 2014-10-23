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

DEFINE('SCRIPTNAME', 'managemembers');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();
$my->p = $slog->Permissions();

$breadcrumb->Add($lang->phrase('teamcp'));

echo $tpl->parse("header");

if ($my->vlogin && $my->p['admin'] == 1) { 
	
	$result = $db->query('SELECT * FROM '.$db->pre.'user WHERE id = '.$_GET['id']);
	if ($db->num_rows() != 1) {
		error($lang->phrase('no_id_given'), 'members.php'.SID2URL_1);
	}
	$user = $gpc->prepare($db->fetch_assoc($result));
	
	if ($_GET['action'] == 'delete') {
		if ($my->id == $user['id']) {
			error($lang->phrase('member_delete_yourself_error'));
		}
		echo $tpl->parse("menu");
		echo $tpl->parse("admin/members/delete");
	}
	elseif ($_GET['action'] == 'delete2') {
		if ($my->id == $user['id']) {
			error($lang->phrase('member_delete_yourself_error'));
		}
		// Step 1: Write Data to File with old Usernames
		$olduserdata = file_get_contents('data/deleteduser.php');
		$olduserdata .= "\n".$user['id']."\t".$user['name'];
		$olduserdata = trim($olduserdata);
		file_put_contents('data/deleteduser.php', $olduserdata);
		// Step 2: Delete all abos
		$db->query("DELETE FROM {$db->pre}abos WHERE mid = '".$user['id']."'");
		// Step 3: Delete all favorites
		$db->query("DELETE FROM {$db->pre}fav WHERE mid = '".$user['id']."'");
		// Step 4: Delete as mod
		$db->query("DELETE FROM {$db->pre}moderators WHERE mid = '".$user['id']."'");
		// Step 5: Delete all pms
		$db->query("DELETE FROM {$db->pre}pm WHERE pm_to = '".$user['id']."'");
		// Step 6: Search all old posts by an user, and update to guests post
		$db->query("UPDATE {$db->pre}replies SET name = '".$user['name']."', email = '".$user['mail']."' WHERE name = '".$user['id']."' AND email = ''");
		// Step 7: Search all old topics by an user, and update to guests post
		$db->query("UPDATE {$db->pre}topics SET name = '".$user['name']."' WHERE name = '".$user['id']."'");
		$db->query("UPDATE {$db->pre}topics SET last_name = '".$user['name']."' WHERE last_name = '".$user['id']."'");
		// Step 8: Set uploads from member to guests-group
		$db->query("UPDATE {$db->pre}uploads SET mid = '0' WHERE mid = '".$user['id']."'");
		// Step 9: Delete pic
		removeOldImages('uploads/pics/', $user['id']);
		// Step 10: Delete user himself
		$db->query("DELETE FROM {$db->pre}user WHERE id = '".$user['id']."'");

		ok($lang->phrase('member_deleted'),'members.php'.SID2URL_1);
	}
	elseif ($_GET['action'] == 'edit') {
		// About
		$chars = numbers($config['maxaboutlength']);
		$bbcode = initBBCodes();
		$inner['bbhtml'] = $bbcode->getbbhtml();
		$inner['smileys'] = $bbcode->getsmileyhtml($config['smileysperrow']);
		
		if (empty($user['template'])) {
		    $user['template'] = $config['templatedir'];
		}
		if (empty($user['language'])) {
		    $user['language'] = $config['langdir'];
		}		
		
		// Settings
		$design = cache_loaddesign();
		$mydesign = $design[$user['template']]['name'];
		$language = cache_loadlanguage();
		$mylanguage = $language[$user['language']]['language'];
		// Profile
	    $bday = explode('-',$user['birthday']);
	    $year = gmdate('Y');
	    $maxy = $year-6;
	    $miny = $year-100;
	    $result = $db->query("SELECT id, title, name, core FROM {$db->pre}groups ORDER BY admin DESC , guest ASC , core ASC");
		
		echo $tpl->parse("menu");
		echo $tpl->parse("admin/members/edit");
	}
	elseif ($_GET['action'] == 'edit2') {
	
		$cache = cache_loaddesign();
		$cache2 = cache_loadlanguage();
	
	    $error = array();
		if (strxlen($_POST['comment']) > $config['maxaboutlength']) {
			$error[] = $lang->phrase('about_too_long');
		}
		if (check_mail($_POST['email']) == FALSE) {
			 $error[] = $lang->phrase('illegal_mail');
		}
		if (strxlen($_POST['name']) > $config['maxnamelength']) {
			$error[] = $lang->phrase('name_too_long');
		}
		if (strxlen($_POST['name']) < $config['minnamelength']) {
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
		if (intval($_POST['temp']) < -12 && intval($_POST['temp']) > 12) {
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
	
		if (count($error) > 0) {	
			error($error);
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

            $pw = $gpc->get('pw', none);
            if (!empty($pw) && strlen($pw) >= $config['minpwlength']) {
                $md5 = md5($pw);
				$update_sql = ", pw = '{$md5}' ";
            }
            else {
                $update_sql = ' ';
            }
            
			if (!empty($_POST['pic']) && preg_match('/^(http:\/\/|www.)([\wäöüÄÖÜ@\-_\.]+)\:?([0-9]*)\/(.*)$/', $_POST['pic'], $url_ary)) {
				$_POST['pic'] = checkRemotePic($_POST['pic'], $url_ary, $_GET['id'], "managemembers.php?action=edit&id=".$_GET['id'].SID2URL_x);
			}
			elseif (empty($_POST['pic']) || !file_exists($_POST['pic'])) {
				$_POST['pic'] = '';
			}

			$db->query("UPDATE {$db->pre}user SET groups = '".$_POST['groups']."', timezone = '".$_POST['temp']."', opt_textarea = '".$_POST['opt_0']."', opt_pmnotify = '".$_POST['opt_1']."', opt_hidebad = '".$_POST['opt_2']."', opt_hidemail = '".$_POST['opt_3']."', template = '".$_POST['opt_4']."', language = '".$_POST['opt_5']."', pic = '".$_POST['pic']."', about = '".$_POST['comment']."', icq = '".$_POST['icq']."', yahoo = '".$_POST['yahoo']."', aol = '".$_POST['aol']."', msn = '".$_POST['msn']."', jabber = '".$_POST['jabber']."', birthday = '".$bday."', gender = '".$_POST['gender']."', hp = '".$_POST['hp']."', signature = '".$_POST['signature']."', location = '".$_POST['location']."', fullname = '".$_POST['fullname']."', mail = '".$_POST['email']."', name = '".$_POST['name']."'".$update_sql." WHERE id = '".$user['id']."' LIMIT 1",__LINE__,__FILE__); 
			ok($lang->phrase('data_success'), "profile.php?id=".$_GET['id']);
		}
	}
}
else {
    errorLogin($lang->phrase('not_allowed'));
}

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();	
?>
