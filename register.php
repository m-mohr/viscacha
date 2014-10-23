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

DEFINE('SCRIPTNAME', 'register');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();
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
	$error = array();
	if ($config['botgfxtest'] == 1) {
		include("classes/graphic/class.veriword.php");
		$vword = new VeriWord();
	    if($_POST['letter']) {
	        if ($vword->check_session($_POST['captcha'], $_POST['letter']) == FALSE) {
	        	$error[] = $lang->phrase('veriword_mistake');
	        }
	    }
	    else {
	        $error[] = $lang->phrase('veriword_failed');
	    }
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
	if (strxlen($_POST['email']) > 200) {
		$error[] = $lang->phrase('email_too_long');
	}
	if (double_udata('mail', $_POST['email']) == false) {
		 $error[] = $lang->phrase('email_already_used');
	}
	if (check_mail($_POST['email']) == false) {
		$error[] = $lang->phrase('illegal_mail');
	}
	if ($_POST['pw'] != $_POST['pwx']) {
		$error[] = $lang->phrase('pw_comparison_failed');
	}

	// Custom profile fields
	$upquery = array();
	$query = $db->query("SELECT * FROM {$db->pre}profilefields WHERE editable != '0' AND required = '1' ORDER BY disporder");
	while($profilefield = $db->fetch_assoc($query)) {
		$profilefield['type'] = $gpc->prepare($profilefield['type']);
		$thing = explode("\n", $profilefield['type'], 2);
		$type = $thing[0];
		$field = "fid{$profilefield['fid']}";

		$value = $gpc->get($field, none);

		if((is_string($value) && strlen($value) == 0) || (is_array($value) && count($value) == 0)) {
			$error[] = $lang->phrase('error_missingrequiredfield');
		}
		if($profilefield['maxlength'] > 0 && ((is_string($value) && strlen($value) > $profilefield['maxlength']) || (is_array($value) && count($value) > $profilefield['maxlength']))) {
			$error[] = $lang->phrase('error_customfieldtoolong');
		}
		
		if($type == "multiselect" || $type == "checkbox") {
			if (is_array($value)) {
				$options = implode("\n", $value);
			}
			else {
				$options = '';
			}
		}
		else {
			$options = $value;
		}
		$options = $gpc->save_str($options);
		$upquery[] = "`{$field}` = '{$options}'";
	}

	($code = $plugins->load('register_save_errorhandling')) ? eval($code) : null;

	if (count($error) > 0) {
		// ToDo: Save error data...
		($code = $plugins->load('register_save_errordata')) ? eval($code) : null;
		error($error,"register.php?name={$_POST['name']}&amp;email=".$_POST['email'].SID2URL_x);
	}
	else {
	    $reg = time();
	    $confirmcode = md5($config['cryptkey'].$reg);
	    $pw_md5 = md5($_POST['pwx']);
	    
	    ($code = $plugins->load('register_save_queries')) ? eval($code) : null;
		$db->query("INSERT INTO {$db->pre}user (name, pw, mail, regdate, confirm) VALUES ('{$_POST['name']}', '{$pw_md5}', '{$_POST['email']}', '{$reg}', '{$config['confirm_registration']}')",__LINE__,__FILE__); 
        $redirect = $db->insert_id();

		// Custom profile fields
		if (count($upquery) > 0) {
			$upquery[] = "`ufid` = '{$redirect}'";
			$db->query("INSERT INTO {$db->pre}userfields SET ".implode(', ', $upquery));
		}

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
		$config['register_notification'] = array();
		foreach ($emails as $email) {
			if(check_mail($email)) {
				$config['register_notification'][] = $email;
			}
		}
		if (count($config['register_notification']) > 0) {
			$to = array_combine(array_fill(1, count($config['register_notification']), 'mail'), $config['register_notification']);
			$data = $lang->get_mail('new_member');
			$from = array();
			xmail($to, $from, $data['title'], $data['comment']);
		}
		
        ok($lang->phrase('register_confirm_'.$config['confirm_registration']), "log.php?action=login".SID2URL_x);
	}

}
elseif ($_GET['action'] == 'confirm') {
	
	($code = $plugins->load('register_confirm_start')) ? eval($code) : null;
	
	$result = $db->query("SELECT id, name, regdate, confirm FROM {$db->pre}user WHERE id = '{$_GET['id']}' AND confirm != '01' AND confirm != '11' LIMIT 1",__LINE__,__FILE__);
	if ($db->num_rows($result) != 1) {
		error($lang->phrase('register_code_no_user'), "log.php?action=login".SID2URL_x);
	}
	
	$row = $db->fetch_assoc($result);
	$row['name'] = $gpc->prepare($row['name']);
	$confirmcode = md5($config['cryptkey'].$row['regdate']);
	
	($code = $plugins->load('register_confirm_check')) ? eval($code) : null;
	
	if ($confirmcode == $_GET['fid']) {
		if ($row['confirm'] == '00') {
			$cn = '01';
		}
		else {
			$cn = '11';
		}
		($code = $plugins->load('register_confirm_query')) ? eval($code) : null;
		$result = $db->query("UPDATE {$db->pre}user SET confirm = '{$cn}' WHERE id = '{$_GET['id']}' LIMIT 1",__LINE__,__FILE__);
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

	if ($config['botgfxtest'] == 1) {
		include("classes/graphic/class.veriword.php");
		$vword = new VeriWord();
		$veriid = $vword->set_veriword($config['botgfxtest_text_verification']);
		if ($config['botgfxtest_text_verification'] == 1) {
			$textcode = $vword->output_word($veriid);
		}
	}
	
	$breadcrumb->Add($lang->phrase('register_title'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");

	$customfields = addprofile_customfields();
	$rules = $lang->get_words('rules');

	($code = $plugins->load('register_form_prepared')) ? eval($code) : null;
	echo $tpl->parse("register");
	($code = $plugins->load('register_form_end')) ? eval($code) : null;
}

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();		
?>
