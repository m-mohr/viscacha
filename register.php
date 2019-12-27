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

define('SCRIPTNAME', 'register');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$my->p = $slog->Permissions();

include_once ("classes/function.profilefields.php");

if ($my->vlogin) {
	error($lang->phrase('already_registered'));
}

($code = $plugins->load('register_start')) ? eval($code) : null;

if ($_GET['action'] == "save") {
	if ($config['disableregistration'] == 1) {
		error($lang->phrase('register_disabled'));
	}
	$fid = $gpc->get('fid', str);
	if (is_hash($fid)) {
		$error_data = import_error_data($fid);
	}
	$error = array();
	$human = empty($error_data['human']) ? false : $error_data['human'];
	if ($config['botgfxtest'] > 0 && $human == false) {
		$captcha = newCAPTCHA();
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
	if (flood_protect() == false) {
		$error[] = $lang->phrase('flood_control');
	}
    if ($config['acceptrules'] == 1 && $_POST['temp'] != 1) {
    	$error[] = $lang->phrase('you_had_to_accept_agb');
    }
	if (double_udata('name',$_POST['name']) == false) {
		$error[] = $lang->phrase('username_registered');
	}
	if (double_udata('mail',$_POST['email']) == false) {
		$error[] = $lang->phrase('email_already_used');
	}
	if (strxlen($_POST['name']) > $config['maxnamelength']) {
		$error[] = $lang->phrase('name_too_long');
	}
	if (strxlen($_POST['name']) < $config['minnamelength']) {
		$error[] = $lang->phrase('name_too_short');
	}
	if (strxlen($_POST['pw']) > $config['maxpwlength']) {
		$error[] = $lang->phrase('pw_too_long');
	}
	if (strxlen($_POST['pw']) < $config['minpwlength']) {
		$error[] = $lang->phrase('pw_too_short');
	}
	if (strlen($_POST['email']) > 200) {
		$error[] = $lang->phrase('email_too_long');
	}
	if (check_mail($_POST['email']) == false) {
		$error[] = $lang->phrase('illegal_mail');
	}
	if ($_POST['pw'] != $_POST['pwx']) {
		$error[] = $lang->phrase('pw_comparison_failed');
	}
	// Custom profile fields
	$custom = addprofile_customprepare();
	$error = array_merge($error, $custom['error']);

	($code = $plugins->load('register_save_errorhandling')) ? eval($code) : null;

	if (count($error) > 0) {
		$data = $custom['data'];
		$data['name'] = $_POST['name'];
		$data['email'] = $_POST['email'];
		$data['temp'] = $_POST['temp'];
		$data['human'] = $human;
		($code = $plugins->load('register_save_errordata')) ? eval($code) : null;
		$fid = save_error_data($data, $fid);
		error($error, 'register.php?fid='.$fid.SID2URL_x);
	}
	else {
		set_flood();
	    $reg = time();
	    $confirmcode = md5($config['cryptkey'].$reg);
	    $pw_md5 = md5($_POST['pwx']);

	    ($code = $plugins->load('register_save_queries')) ? eval($code) : null;
		$db->query("INSERT INTO {$db->pre}user (name, pw, mail, regdate, confirm, groups, signature, about) VALUES ('{$_POST['name']}', '{$pw_md5}', '{$_POST['email']}', '{$reg}', '{$config['confirm_registration']}', '".GROUP_MEMBER."', '', '')");
        $redirect = $db->insert_id();

		// Custom profile fields
		addprofile_customsave($custom['data'], $redirect);

        if ($config['confirm_registration'] != '11') {
			$data = $lang->get_mail('register_'.$config['confirm_registration']);
			$to = array('0' => array('name' => $_POST['name'], 'mail' => $_POST['email']));
			$from = array();
			xmail($to, $from, $data['title'], $data['comment']);
		}

		$com = $scache->load('memberdata');
		$cache = $com->delete();

		($code = $plugins->load('register_save_end')) ? eval($code) : null;

		$emails = preg_split('/[\r\n]+/', $config['register_notification'], -1, PREG_SPLIT_NO_EMPTY);

		if (count($emails) > 0) {
			$to = array();
			foreach ($emails as $email) {
				if(check_mail($email, true)) {
					$to[] = array('mail' => $email);
				}
			}
			if (count($to) > 0) {
				$data = $lang->get_mail('new_member');
				$from = array();
				xmail($to, $from, $data['title'], $data['comment']);
			}
		}

        ok($lang->phrase('register_confirm_'.$config['confirm_registration']), "log.php?action=login".SID2URL_x);
	}
}
elseif ($_GET['action'] == 'resend') {
	($code = $plugins->load('register_resend_start')) ? eval($code) : null;
	$breadcrumb->Add($lang->phrase('register_title'), "register.php".SID2URL_1);
	$breadcrumb->Add($lang->phrase('register_resend_title'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");

	if ($config['botgfxtest'] > 0) {
		$captcha = newCAPTCHA();
	}
	else {
		$captcha = null;
	}
	$name = $gpc->get('name', str);
	($code = $plugins->load('register_resend_form_start')) ? eval($code) : null;
	echo $tpl->parse("register/resend");
	($code = $plugins->load('register_resend_form_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == 'resend2') {
	$breadcrumb->Add($lang->phrase('register_title'), "register.php".SID2URL_1);
	$breadcrumb->Add($lang->phrase('register_resend_title'));

	($code = $plugins->load('register_resend2_start')) ? eval($code) : null;

	$error = array();
	$result = $db->query("SELECT id, name, mail, regdate, confirm FROM {$db->pre}user WHERE name = '{$_POST['name']}' AND (confirm = '10' OR confirm = '00') LIMIT 1");
	if ($db->num_rows($result) != 1) {
		$error[] = $lang->phrase('register_resend_no_user');
	}
	if (flood_protect() == false) {
		$error[] = $lang->phrase('flood_control');
	}
	if ($config['botgfxtest'] > 0) {
		$captcha = newCAPTCHA();
		$status = $captcha->check();
		if ($status == CAPTCHA_FAILURE) {
			$error[] = $lang->phrase('veriword_failed');
		}
		elseif ($status == CAPTCHA_MISTAKE) {
			$error[] = $lang->phrase('veriword_mistake');
		}
	}
	if (count($error) > 0) {
		error($error, "register.php?action=resend&amp;name=".$_POST['name'].SID2URL_x);
	}
	else {
		set_flood();
		$row = $db->fetch_assoc($result);
		$confirmcode = md5($config['cryptkey'].$row['regdate']);

		($code = $plugins->load('register_resend2_check')) ? eval($code) : null;

		$lang->assign('redirect', $row['id']);
		$data = $lang->get_mail('register_'.$row['confirm']);
		$to = array('0' => array('name' => $row['name'], 'mail' => $row['mail']));
		$from = array();
		xmail($to, $from, $data['title'], $data['comment']);

		($code = $plugins->load('register_resend2_end')) ? eval($code) : null;

		ok($lang->phrase('register_resend_success'), "log.php?action=login".SID2URL_x);
	}
}
elseif ($_GET['action'] == 'confirm') {

	($code = $plugins->load('register_confirm_start')) ? eval($code) : null;

	$result = $db->query("SELECT id, name, regdate, confirm FROM {$db->pre}user WHERE id = '{$_GET['id']}' AND confirm != '01' AND confirm != '11' LIMIT 1");
	if ($db->num_rows($result) != 1) {
		error($lang->phrase('register_code_no_user'), "log.php?action=login".SID2URL_x);
	}

	$row = $db->fetch_assoc($result);
	$row['name'] = $gpc->prepare($row['name']);
	$confirmcode = md5($config['cryptkey'].$row['regdate']);

	($code = $plugins->load('register_confirm_check')) ? eval($code) : null;

	if ($confirmcode == $gpc->get('fid')) {
		if ($row['confirm'] == '00') {
			$cn = '01';
		}
		else {
			$cn = '11';
		}
		($code = $plugins->load('register_confirm_query')) ? eval($code) : null;
		$result = $db->query("UPDATE {$db->pre}user SET confirm = '{$cn}' WHERE id = '{$_GET['id']}' LIMIT 1");
		ok($lang->phrase('register_code_validated'), "log.php?action=login".SID2URL_x);
	}
	else {
		error($lang->phrase('register_code_not_valid'), "log.php?action=login".SID2URL_x);
	}

}
else {
	($code = $plugins->load('register_form_start')) ? eval($code) : null;

	if ($config['disableregistration'] == 1) {
		error($lang->phrase('register_disabled'));
	}

	$fid = $gpc->get('fid', str);
	if (is_hash($fid)) {
		$data = $gpc->unescape(import_error_data($fid));
		$customfields = addprofile_customfields($data);
	}
	else {
		$data = array(
			'name' => '',
			'email' => '',
			'temp' => '',
			'human' => false
		);
		$customfields = addprofile_customfields();
	}

	if ($config['botgfxtest'] > 0 && $data['human'] == false) {
		$captcha = newCAPTCHA();
	}
	else {
		$captcha = null;
	}

	$breadcrumb->Add($lang->phrase('register_title'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");

	$rules = $lang->get_words('rules');

	$_GET['email'] = $gpc->prepare($_GET['email']);

	($code = $plugins->load('register_form_prepared')) ? eval($code) : null;
	echo $tpl->parse("register/register");
	($code = $plugins->load('register_form_end')) ? eval($code) : null;
}

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();
?>