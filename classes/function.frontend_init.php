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

if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "function.frontend_init.php") die('Error: Hacking Attempt');

if (in_array('config', array_keys(array_change_key_case($_REQUEST)))) {
	die('Error: Hacking Attemp (Config variable)');
}

// Gets a file with php-functions
@include_once("classes/function.phpcore.php");

// Thanks to phpBB for this code
if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on') {
	unset($not_used, $input);
	$not_unset = array('_GET', '_POST', '_COOKIE', '_SERVER', '_SESSION', '_ENV', '_FILES', 'config');

	$input = array_merge($_GET, $_POST, $_COOKIE, $_ENV, $_FILES);
	if (isset($_SERVER)) {
		$input = array_merge($input, $_SERVER);
	}
	if (isset($_SESSION) && is_array($_SESSION)) {
		$input = array_merge($input, $_SESSION);
	}

	unset($input['input'], $input['not_unset']);

	while (list($var,) = @each($input)) {
		if (!in_array($var, $not_unset)) {
			unset($$var);
			// Testen
			if (isset($GLOBALS[$var])) {
				unset($GLOBALS[$var]);
			}
		}
	}

	unset($input);
}

$inner = array();
$htmlhead = '';
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
$grabrss_cache = array();
if ($config['avwidth'] == 0) {
	$config['avwidth'] = 2048;
}
if ($config['avheight'] == 0) {
	$config['avheight'] = 2048;
}

require_once("classes/class.filesystem.php");
$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
$filesystem->set_wd($config['ftp_path']);
// Gets a file with php-functions
@include_once("classes/function.chmod.php");
// Permission and Logging Class
require_once ("classes/class.permissions.php");
// A simple caching class for Arrays etc.
include_once ("classes/function.cache.php");
// A class for Templates
require_once ("classes/class.template.php");
// Load Braedcrumb-Module
include_once ("classes/class.breadcrumb.php");
// BB-Code functions
include_once ("classes/class.bbcode.php");
// Global functions
require_once ("classes/function.global.php");
// Load Variables
require_once ("classes/function.gpc.php");
// Load Flood-Check
include_once ("classes/function.flood.php");

if (!file_exists('.htaccess')) {
$htaccess = '';

	if ($config['hterrordocs'] == 1) {
	    $htaccess = "
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
	
	@file_put_contents('.htaccess', $htaccess);
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

$bannedip = file('data/bannedip.php');
$bannedip = array_map('trim', $bannedip);
if (count($bannedip) > 0) {
	foreach ($bannedip as $row) {
		if (strpos(' '.getip(), ' '.trim($row)) !== false) {
			$slog = new slog();
			$my = $slog->logged();
			$lang->init($my->language);
			$tpl = new tpl();
			
			ob_start();
			include('data/banned.php');
			$banned = ob_get_contents();
			ob_end_clean();
            echo $tpl->parse("banned");
            
            $phpdoc->Out();
			$db->close();
		    exit();
		}
	}
}

if ($config['foffline'] && DEFINED('TEMPSHOWLOG') == FALSE && SCRIPTNAME != 'external') {
	$slog = new slog();
	$my = $slog->logged();
	$my->p = $slog->Permissions();

	if ($my->p['admin'] != 1) {
		$lang->init($my->language);
		$tpl = new tpl();
        
        ob_start();
		include('data/offline.php');
		$offline = ob_get_contents();
		ob_end_clean();
        echo $tpl->parse("offline");
        
        $phpdoc->Out();
		$db->close();
	    exit();
	}
	
	unset($slog, $my);
}
?>
