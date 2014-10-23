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


if ($my->vlogin) {
	error($lang->phrase('already_registered'));
}

if ($_GET['action'] == "save") {
	$error = array();
	if ($config['botgfxtest'] == 1) {
		include("classes/graphic/class.veriword.php");
		$vword = new VeriWord();
	    if($_POST['letter']) {
	        if ($vword->check_session($_POST['fid'], $_POST['letter']) == FALSE) {
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
	if (check_mail($_POST['email']) == FALSE) {
		$error[] = $lang->phrase('illegal_mail');
	}
	if ($_POST['pw'] != $_POST['pwx']) {
		$error[] = $lang->phrase('pw_comparison_failed');
	}
	
	if (count($error) > 0) {
		error($error,"register.php?name=".$_POST['name']."&amp;email=".$_POST['email'].SID2URL_x);
	}
	else {
	    $reg = time();
	    $_POST['pwx'] = md5($_POST['pwx']);
		$db->query("INSERT INTO {$db->pre}user (name, pw, mail, regdate, confirm) VALUES ('{$_POST['name']}', '{$_POST['pwx']}', '{$_POST['email']}', '{$reg}', '{$config['confirm_registration']}')",__LINE__,__FILE__); 
        $redirect = $db->insert_id();
        $confirmcode = md5($config['cryptkey'].$reg);
        
        if ($config['confirm_registration'] != '11') {
			$data = $lang->get_mail('register_'.$config['confirm_registration']);
			$to = array('0' => array('name' => $_POST['name'], 'mail' => $_POST['email']));
			$from = array();
			xmail($to, $from, $data['title'], $data['comment']);
		}
		$scache = new scache('memberdata');
		if ($scache->existsdata() == TRUE) {
			$cache = $scache->deletedata();
		}
        ok($lang->phrase('register_confirm_'.$config['confirm_registration']), "log.php?action=login".SID2URL_x);
	}

}
elseif ($_GET['action'] == 'confirm') {
	
	$result = $db->query("SELECT id, name, regdate, confirm FROM {$db->pre}user WHERE id = '{$_GET['id']}' AND confirm != '01' AND confirm != '11' LIMIT 1",__LINE__,__FILE__);
	$row = $db->fetch_assoc($result);
	$row['name'] = $gpc->prepare($row['name']);
	
	if ($db->num_rows($result) != 1) {
		error($lang->phrase('register_code_no_user'), "log.php?action=login".SID2URL_x);
	}
	
	$confirmcode = md5($config['cryptkey'].$row['regdate']);
	if ($confirmcode == $_GET['fid']) {
		if ($row['confirm'] == '00') {
			$cn = '01';
		}
		else {
			$cn = '11';
		}
		$result = $db->query("UPDATE {$db->pre}user SET confirm = '{$cn}' WHERE id = '{$_GET['id']}' LIMIT 1",__LINE__,__FILE__);
		ok($lang->phrase('register_code_validated'), "log.php?action=login".SID2URL_x);
	}
	else {
		error($lang->phrase('register_code_not_valid'), "log.php?action=login".SID2URL_x);
	}
	
}
elseif ($_GET['action'] == 'veriword') {
	include("classes/graphic/class.veriword.php");
	$vword = new VeriWord();
	if (isset($_GET['width'])) {
	    $_GET['width'] = $gpc->save_int($_GET['width']);
	}
	else {
	    $_GET['width'] = 150;
	}
	if (isset($_GET['height'])) {
	    $_GET['height'] = $gpc->save_int($_GET['height']);
	}
	else {
	    $_GET['height'] = 33;
	}
	$vword->set_filter($config['botgfxtest_filter']);
	$vword->set_size($_GET['width'],$_GET['height']);
	$vword->output_image($_GET['fid']);
	exit;
}
else {
	include("classes/graphic/class.veriword.php");
	$vword = new VeriWord();
	$veriid = $vword->set_veriword($config['register_text_verification']);
	if ($config['register_text_verification'] == 1) {
		$code = $vword->output_word($veriid);
	}
	$breadcrumb->Add($lang->phrase('register_title'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	$mymodules->load('register_top');
	$rules = $lang->get_words('rules');
	echo $tpl->parse("register");
	$mymodules->load('register_bottom');
}

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();		
?>
