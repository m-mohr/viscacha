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

DEFINE('SCRIPTNAME', 'log');

include("data/config.inc.php");
include("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();
$my->p = $slog->Permissions();

($code = $plugins->load('log_start')) ? eval($code) : null;

if ($_GET['action'] == "login2") {
	$remember = $gpc->get('remember', int, 1);
	$loc = getRedirectURL();
	if ($my->vlogin) {
		$slog->updatelogged();
		$db->close();
		viscacha_header("Location: {$loc}");
		exit;
	}

	if ($remember == 1) {
		$remember = true;
	}
	else {
		$remember = false;
	}
	
	($code = $plugins->load('log_login2')) ? eval($code) : null;
	
	$log_status = $slog->sid_login($remember);
	if (!$log_status) {
		error($lang->phrase('log_wrong_data'), "log.php?action=login&amp;redirect=".rawurlencode($loc).SID2URL_x);
	}
	else {
		ok($lang->phrase('log_msglogin'), $loc);
	}
}
elseif ($_GET['action'] == "logout") {

	if (!$my->vlogin) {
		$slog->updatelogged();
		$db->close();
		viscacha_header('Location: log.php');
		exit;
	}
	else {
		$loc = getRedirectURL();
		($code = $plugins->load('log_logout')) ? eval($code) : null;
		$slog->sid_logout();

		ok($lang->phrase('log_msglogout'), $loc);
	}

}
elseif ($_GET['action'] == "pwremind") {
	if ($my->vlogin) {
		error($lang->phrase('log_already_logged'));
	}
	$breadcrumb->Add($lang->phrase('log_pwremind_title'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	($code = $plugins->load('log_pwremind_form_start')) ? eval($code) : null;
	echo $tpl->parse("log/pwremind");
	($code = $plugins->load('log_pwremind_form_end')) ? eval($code) : null;
	$slog->updatelogged();
}
elseif ($_GET['action'] == "pwremind2") {
	if (flood_protect() == false) {
		error($lang->phrase('flood_control'),'log.php?action=login'.SID2URL_x);
	}
	set_flood();
	
	($code = $plugins->load('log_pwremind2_start')) ? eval($code) : null;

	$result = $db->query("SELECT id, name, mail, pw FROM {$db->pre}user WHERE mail = '{$_POST['email']}' LIMIT 1",__LINE__,__FILE__);
	
	$user = $db->fetch_assoc($result);
	if ($db->num_rows($result) != 1) {
		error($lang->phrase('log_pwremind_failed'), "log.php?action=pwremind".SID2URL_x);
	}
	else {

		$confirmcode = md5($config['cryptkey'].$user['pw']);

		($code = $plugins->load('log_pwremind2_prepare')) ? eval($code) : null;
		
		$data = $lang->get_mail('pwremind');
		$to = array('0' => array('name' => $user['name'], 'mail' => $user['mail']));
		$from = array();
		xmail($to, $from, $data['title'], $data['comment']);

		($code = $plugins->load('log_pwremind2_end')) ? eval($code) : null;
		
		ok($lang->phrase('log_pwremind_success'), "log.php?action=login".SID2URL_x);
	}
	$slog->updatelogged();
}
elseif ($_GET['action'] == "pwremind3") {
	if (flood_protect() == false) {
		error($lang->phrase('flood_control'),'log.php?action=login'.SID2URL_x);
	}
	set_flood();

	($code = $plugins->load('log_pwremind3_start')) ? eval($code) : null;

	$result = $db->query("SELECT id, pw, mail, name FROM {$db->pre}user WHERE id = '{$_GET['id']}' LIMIT 1",__LINE__,__FILE__);
	$user = $db->fetch_assoc($result);
	
	$confirmcode = md5($config['cryptkey'].$user['pw']);
	if ($confirmcode == $_GET['fid']) {
	
		$pw = random_word();
		$md5 = md5($pw);
		$db->query("UPDATE {$db->pre}user SET pw = '{$md5}' WHERE id = '{$user['id']}' LIMIT 1",__LINE__,__FILE__);

		$data = $lang->get_mail('pwremind2');
		$to = array('0' => array('name' => $user['name'], 'mail' => $user['mail']));
		$from = array();
		xmail($to, $from, $data['title'], $data['comment']);
		
		($code = $plugins->load('log_pwremind3_success')) ? eval($code) : null;
		ok($lang->phrase('log_pwremind_changed'), "log.php?action=login".SID2URL_x);
	}
	else {
		($code = $plugins->load('log_pwremind3_failed')) ? eval($code) : null;
		error($lang->phrase('log_pwremind_wrong_code'), "log.php?action=pwremind".SID2URL_x);
	}
}
else {
	$loc = getRedirectURL(false);
	if (empty($loc)) {
		$loc = getRefererURL();
	}
	$loc = htmlspecialchars($loc);
	if ($my->vlogin) {
		error($lang->phrase('log_already_logged'), $loc);
	}

	$breadcrumb->Add($lang->phrase('log_title'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");

	($code = $plugins->load('log_login_form_start')) ? eval($code) : null;
	echo $tpl->parse("log/login");
	($code = $plugins->load('log_login_form_end')) ? eval($code) : null;
	$slog->updatelogged();
}

($code = $plugins->load('log_end')) ? eval($code) : null;

$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();	
?>
