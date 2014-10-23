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

if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

if (in_array('config', array_keys(array_change_key_case($_REQUEST)))) {
	die('Error: Hacking Attemp (Config variable)');
}

// Gets a file with php-functions
@include_once("classes/function.phpcore.php");

if (empty($config['cryptkey']) || empty($config['database']) || empty($config['dbsystem'])) {
	trigger_error('Viscacha is currently not installed. How to install Viscacha is described in the file "_docs/readme.txt"!', E_USER_ERROR);
}
if (empty($config['dbpw']) || empty($config['dbuser'])) {
	trigger_error('You have specified database authentification data that is not safe. Please change your database user and the database password!', E_USER_ERROR);
}

// Debugging / Error Handling things
require_once("classes/function.errorhandler.php");

// A class for Languages
require_once("classes/class.language.php");
$lang = new lang();

// Filesystem
require_once("classes/class.filesystem.php");
$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
$filesystem->set_wd($config['ftp_path'], $config['fpath']);

// Database functions
require_once('classes/database/'.$config['dbsystem'].'.inc.php');
$db = new DB($config['host'], $config['dbuser'], $config['dbpw'], $config['database'], $config['dbprefix']);
$db->setPersistence($config['pconnect']);

// Variables
require_once ("classes/function.gpc.php");

/* 	Handling of _GET, _POST, _REQUEST, _COOKIE, _SERVER, _ENV
 	_ENV, _SERVER: Won't be checked, but null-byte is deleted
 	_COOKIE: You can check them in the script ( getcookie() ), won't be checked
 	_REQUEST: Won't be checked - array has the original values (but without magic_quotes)
 	_POST, _GET with keys specified in http_vars are checked and save
*/
$http_vars = array(
	'action' => str,
	'job' => str,
	'search' => str,
	'name' => str,
	'email' => db_esc,
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
	'pic' => db_esc,
	'question' => str,
	'type' => str,
	'gender' => str,
	'aol' => db_esc,
	'msn' => db_esc,
	'skype' => db_esc,
	'yahoo' => db_esc,
	'jabber' => db_esc,
	'fid' => str,
	'file' => str,
	'groups' => str,
	'captcha' => str,
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

$http_all = array_merge(
	array_keys($http_vars),
	is_array($_POST) ? array_keys($_POST) : array(),
	is_array($_GET) ? array_keys($_GET) : array()
);
$http_all = array_unique($http_all);


foreach ($http_all as $key) {
	if (isset($http_vars[$key])) {
		$type = $http_vars[$key];
	}
	else {
		$type = none;
	}
	$_POST[$key] = $_GET[$key] = $gpc->get($key, $type);
}

$_GET['page'] = $_POST['page'] = $_GET['page'] < 1 ? 1 : $_GET['page'];

unset($http_vars, $http_all);

$inner = array();
$htmlhead = '';
$htmlonload = '';
if (defined('SCRIPTNAME')) {
    $htmlbody = ' id="css_'.SCRIPTNAME.'"';
}
if ($config['nocache'] == 1) {
	$htmlhead .= '
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="-1" />
	<meta http-equiv="Cache-Control" content="no-cache" />
	';
}

if ($config['avwidth'] == 0) {
	$config['avwidth'] = 2048;
}
if ($config['avheight'] == 0) {
	$config['avheight'] = 2048;
}

// Permission and Logging Class
require_once ("classes/class.permissions.php");
// A class for Templates
require_once ("classes/class.template.php");
// Load Braedcrumb-Module
include_once ("classes/class.breadcrumb.php");
// Global functions
require_once ("classes/function.global.php");
// Load Flood-Check
include_once ("classes/function.flood.php");

if (!file_exists('.htaccess')) {
$htaccess = '';

	if ($config['hterrordocs'] == 1) {
	    $htaccess .= "
	    ErrorDocument 400	{$config['furl']}/misc.php?action=error&id=400
	    ErrorDocument 401	{$config['furl']}/misc.php?action=error&id=401
	    ErrorDocument 403	{$config['furl']}/misc.php?action=error&id=403
	    ErrorDocument 404	{$config['furl']}/misc.php?action=error&id=404
	    ErrorDocument 500	{$config['furl']}/misc.php?action=error&id=500
	    ";
	}

	if ($config['correctsubdomains'] == 1) {
	    $url = parse_url($config['furl']);
	    $host = str_ireplace('www.', '', $url['host']);
	    $htaccess .= "
	    RewriteEngine On
	    RewriteCond %{HTTP_HOST} ^www\.".preg_quote($host)."$ [NC]
	    RewriteRule ^(.*)$ http://".$host."/$1 [R=301,L]
	    ";
	}

	$filesystem->file_put_contents('.htaccess', $htaccess);
}

$breadcrumb = new breadcrumb();
$breadcrumb->Add($config['fname'], 'index.php');

$phpdoc = new OutputDoc($config['gzip']);
$phpdoc->Start($config['gzcompression']);
if ($config['gzip'] == 1 && $phpdoc->Encoding()) {
	$gzbenchval = 'On - Compression Rate: '.$config['gzcompression'];
}
else {
	$gzbenchval = 'Off';
}

($code = $plugins->load('frontend_init')) ? eval($code) : null;

// Global and important functions (not for cron and external)
if (defined('TEMPNOFUNCINIT') == false || ($config['foffline'] && defined('TEMPSHOWLOG') == false)) {
	$zeitmessung1 = t1();
	$slog = new slog();
	$my = $slog->logged();
	$lang->init($my->language);
	$tpl = new tpl();
	$slog->checkBan();
}

if ($config['foffline'] && defined('TEMPSHOWLOG') == false) {
	$my->p = $slog->Permissions();

	if ($my->p['admin'] != 1) {
		$offline = file_get_contents('data/offline.php');
        ($code = $plugins->load('frontend_init_offline')) ? eval($code) : null;
		echo $tpl->parse("offline");

        $phpdoc->Out();
		$db->close();
	    exit();
	}
}
?>
