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

if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "function.gpc.php") die('Error: Hacking Attempt');

/* Handling of _GET, _POST, _REQUEST, _COOKIE, _SERVER, _ENV
 _ENV, _SERVER: Won't be checked
 _COOKIE: You can check them in the script, won't be checked
 _REQUEST: Won't be checked - array has the original values (with magic_quotes if enabled)
 _POST, _GET: Are checked and save (after this file)
*/

include('classes/class.gpc.php');

$gpc = new GPC();

$http_vars = array(
'action' => str,
'job' => str,
'search' => str,
'reply' => str,
'name' => str,
'email' => str,
'topic' => str,
'comment' => str,
'error' => str,
'pw' => str,
'pwx' => str,
'order' => str,
'sort' => str,
'letter' => str,
'fullname' => str,
'about' => str,
'location' => str,
'signature' => str,
'hp' => str,
'icq' => str,
'pic' => str,
'question' => str,
'type' => str,
'gender' => str,
'aol' => str,
'msn' => str,
'yahoo' => str,
'jabber' => str,
'fid' => str,
'file' => str,
'groups' => str,
'board' => int,
'topic_id' => int,
'id' => int,
'page' => int,
'temp' => int,
'temp2' => int,
'dosmileys' => int,
'dowords' => int,
'birthday' => int,
'birthmonth' => int,
'birthyear' => int,
'opt_0' => int,
'opt_1' => int,
'opt_2' => int,
'opt_3' => int,
'opt_4' => int,
'opt_5' => int,
'opt_6' => int,
'opt_7' => int,
'notice' => arr_str,
'boards' => arr_int,
'delete' => arr_int
);

$http_all = array_merge(array_keys($http_vars), array_keys($_POST), array_keys($_GET));
$http_all = array_unique($http_all);

$http_std = array(
int => 0,
arr_int => array(),
arr_str => array(),
str => '',
none => null
);


foreach ($http_all as $key) {
	if (isset($http_vars[$key])) {
		$type = $http_vars[$key];
	}
	else {
		$type = str;
	}
	if (isset($_POST[$key])) {
        if ($type == int || $type == arr_int) {
            $_POST[$key] = $gpc->save_int($_POST[$key]);
        }
        else {
            $_POST[$key] = $gpc->save_str($_POST[$key]);
        }
	}
	else {
		$_POST[$key] = $http_std[$type];
	}
	if (isset($_GET[$key])) {
        if ($type == int || $type == arr_int) {
            $_GET[$key] = $gpc->save_int($_GET[$key]);
        }
        else {
            $_GET[$key] = $gpc->save_str($_GET[$key]);
        }
	}
	else {
		$_GET[$key] = $http_std[$type];
	}
}

$_GET['page'] = !isset($_GET['page']) || $_GET['page'] < 1 ? 1 : $_GET['page'];
$_POST['page'] = !isset($_POST['page']) || $_POST['page'] < 1 ? 1 : $_POST['page'];
$_REQUEST['page'] = !isset($_REQUEST['page']) || $_REQUEST['page'] < 1 ? 1 : $_REQUEST['page'];

unset($http_vars, $http_all, $http_std);

?>
