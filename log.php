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
DEFINE('TEMPSHOWLOG', 1);

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();
$my->p = $slog->Permissions();

if ($_GET['action'] == "login2") {

    $loc = strip_tags($gpc->get('location', none, 'index.php'.SID2URL_1));
    $file = basename($loc);
    if ($file = 'log.php') {
    	$loc = 'index.php'.SID2URL_1;
    }

    if ($my->vlogin) {
        viscacha_header($loc);
    }

    if (!$slog->sid_login()) {
		error($lang->phrase('log_wrong_data'), "log.php?action=login&amp;location=".urlencode($loc).SID2URL_x);
    }
    else {
        ok($lang->phrase('log_msglogin'), $loc);
    }
    
}
elseif ($_GET['action'] == "logout") {

    if (!$my->vlogin) {
    	viscacha_header('Location: log.php');
    }
    else {
        $slog->sid_logout();
        $loc = strip_tags($gpc->get('location', none, 'index.php'.SID2URL_1));
	    $file = basename($loc);
	    if ($file = 'log.php') {
	    	$loc = 'index.php'.SID2URL_1;
	    }
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
	echo $tpl->parse("log/pwremind");
	$slog->updatelogged();
}
elseif ($_GET['action'] == "pwremind2") {
	if (flood_protect() == FALSE) {
		error($lang->phrase('flood_control'),'log.php?action=login'.SID2URL_x);
	}
	set_flood();

    $result=$db->query('SELECT id FROM '.$db->pre.'user WHERE name="'.$_POST['name'].'" AND mail="'.$_POST['email'].'" LIMIT 1',__LINE__,__FILE__);
    $user=$db->fetch_array($result);
    if ($db->num_rows($result) != 1) {
		error($lang->phrase('log_pwremind_failed'), "log.php?action=pwremind".SID2URL_x);
    }
	else {
		$pw = random_word();
		
		$data = $lang->get_mail('pwremind');
		$to = array('0' => array('name' => $_POST['name'], 'mail' => $_POST['email']));
		$from = array();
		xmail($to, $from, $data['title'], $data['comment']);

		$db->query("UPDATE {$db->pre}user SET pw = MD5('".$pw."') WHERE id = '".$user['id']."' LIMIT 1",__LINE__,__FILE__);
		ok($lang->phrase('log_pwremind_success'), "log.php?action=login".SID2URL_x);
	}
	$slog->updatelogged();
}
else {
	if ($my->vlogin) {
    	error($lang->phrase('log_already_logged'));
    }
    $breadcrumb->Add($lang->phrase('log_title'));
    echo $tpl->parse("header");
    echo $tpl->parse("menu");
    
    $loc = htmlspecialchars($gpc->get('location', none));
    if (empty($loc)) {
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $url = parse_url($_SERVER['HTTP_REFERER']);
            if (strpos($config['furl'], $url['host']) !== FALSE) {
                $loc = htmlspecialchars($_SERVER['HTTP_REFERER']);
            }
        }
    }
    
	echo $tpl->parse("log/login");
	$slog->updatelogged();
}

$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();	
?>
