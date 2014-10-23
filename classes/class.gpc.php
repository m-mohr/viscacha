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

class GPC {

    function GPC() {
		if (!defined('str')) {
			define('str', 2);
		}
		if (!defined('int')) {
			define('int', 1);
		}
		if (!defined('arr_str')) {
			define('arr_str', 5);
		}
		if (!defined('arr_int')) {
			define('arr_int', 4);
		}
		if (!defined('none')) {
			define('none', 0);
		}
		if (!defined('arr_none')) {
			define('arr_none', 3);
		}
    }
    
    function get($index, $type = none, $standard = NULL) {
        if (isset($_REQUEST[$index])) {
            if ($type == str || $type == arr_str) {
				if ($type == str) {
					$_REQUEST[$index] = trim($_REQUEST[$index]);
				}
                $var = $this->save_str($_REQUEST[$index]);
            }
            elseif ($type == int || $type == arr_int) {
                $var = $this->save_int($_REQUEST[$index]);
            }
            else {
                $var = $_REQUEST[$index];
            }
        }
        else {
        	if ($standard == NULL) {
            	if ($type == str) {
            	    $var = '';
            	}
            	elseif ($type == int) {
            	    $var = 0;
            	}
            	elseif ($type == arr_int || $type == arr_str || $type == arr_none) {
            	    $var = array();
            	}
            	else {
            	    $var = NULL;
            	}
            }
            else {
            	$var = $standard;
            }
        }

        return $var;
    }

    function prepare($var) {
    	global $config;
    	if (is_array($var)) {
    		$cnt = count($var);
    		$keys = array_keys($var);
    		
    		for ($i = 0; $i < $cnt; $i++) {
    			$key = $keys[$i];
    			$var[$key] = $this->prepare($var[$key]);
    		}
    	}
    	elseif (is_object($var)) {
    		$ovar = get_object_vars($var);
    		$cnt = count($ovar);
    		$keys = array_keys($ovar);
    		
    		for ($i = 0; $i < $cnt; $i++) {
    			$key = $keys[$i];
    			$var->$key = $this->prepare($ovar[$key]);
    		}
    	}
    	elseif (is_string($var)) {
    		if ($config['asia'] != 1) {
    			$var = htmlspecialchars($var, ENT_QUOTES);
    		}
    		else {
        		$var = str_replace('"', '&quot;', $var);
        		$var = str_replace("'", '&#039;', $var);
        		$var = str_replace('>', '&gt;', $var);
        		$var = str_replace('<', '&lt;', $var);		
    		}
    	}
    	return $var;
    }

	function save_str($var, $html = false){
    	global $db, $config;
    	if (is_array($var)) {
    		$cnt = count($var);
    		$keys = array_keys($var);
    		
    		for ($i = 0; $i < $cnt; $i++){
    			$key = $keys[$i];
    			$var[$key] = $this->save_str($var[$key]);
    		}
    	}
    	elseif (is_string($var)){	
    		$var = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1&#058;", $var);
    
    		if ($config['asia'] == 1) {
    			$var = htmlentities($var, ENT_NOQUOTES, $config['asia_charset']);
    		}
    
			if ($html) {
				$var = $this->prepare($var);
			}
    		$var = $db->escape_string($this->secure_null($var));
    	}
    	return $var;
    }
    
    function save_int($var){
    	global $db, $config;
    	if (is_array($var)) {
    		$cnt = count($var);
    		$keys = array_keys($var);
    		
    		for ($i = 0; $i < $cnt; $i++){
    			$key = $keys[$i];
    			$var[$key] = $this->save_int($var[$key]);
    		}
    	}
    	else {
    		$var = intval(trim($var));	
    	}
    	
    	return $var;
    }
    
    function unescape($var) {
    	if (is_array($var)) {
    		$cnt = count($var);
    		$keys = array_keys($var);
    		
    		for ($i = 0; $i < $cnt; $i++) {
    			$key = $keys[$i];
    			$var[$key] = $this->unescape_r($var[$key]);
    		}
    	}
    	elseif (is_object($var)) {
    		$ovar = get_object_vars($var);
    		$cnt = count($ovar);
    		$keys = array_keys($ovar);
    		
    		for ($i = 0; $i < $cnt; $i++) {
    			$key = $keys[$i];
    			$var->$key = $this->unescape_r($ovar[$key]);
    		}
    	}
    	elseif (is_string($var)) {
    	    $var = str_replace('\\n', "\n", $var);
    	    $var = str_replace('\\\\', '\\', $var);
    	    $var = str_replace("\\'", "'", $var);
    	    $var = str_replace('\\"', '"', $var);
    	    $var = str_replace('\\r', "\r", $var);
    	}
    	return $var;
    }
    
	function secure_null($data) {
		if (is_array($data)) {
			$data = $this->secure_null($data);
		}
		else {
			return str_replace("\0", '', $data);
		}
	}

}

if (version_compare(PHP_VERSION, '4.1.0', '<')) {
	$_GET = &$HTTP_GET_VARS;
	$_POST = &$HTTP_POST_VARS;
	$_COOKIE = &$HTTP_COOKIE_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
	$_ENV = &$HTTP_ENV_VARS;
	$_FILES = &$HTTP_POST_FILES;
	if (isset($HTTP_SESSION_VARS) && is_array($HTTP_SESSION_VARS)) {
		$_SESSION = &$HTTP_SESSION_VARS;
	}
	$_REQUEST = array_merge($_GET, $_POST, $_COOKIE);
}

function array_stripslashes($array) {
	if(is_array($array)) {
		return array_map('stripslashes', $array); 
	}
	else {
		return stripslashes($array);
	}
}

if (get_magic_quotes_gpc() == 1) {
	while(list($key,$value)=each($_GET)) $_GET[$key]=array_stripslashes($value);
	while(list($key,$value)=each($_POST)) $_POST[$key]=array_stripslashes($value);
	while(list($key,$value)=each($_REQUEST)) $_REQUEST[$key]=array_stripslashes($value);
}

// Thanks to phpBB for this 6 lines
if (isset($_POST['GLOBALS']) || isset($_FILES['GLOBALS']) || isset($_GET['GLOBALS']) || isset($_COOKIE['GLOBALS'])) {
	die("Hacking attempt (Globals)");
}
if (isset($_SESSION) && !is_array($_SESSION)) {
	die("Hacking attempt (Session Variable)");
}

$http_svars = array(
	'PHP_SELF',
	'HTTP_USER_AGENT',
	'SERVER_SOFTWARE',
	'REMOTE_ADDR',
	'SCRIPT_NAME',
	'SERVER_PORT',
	'SERVER_NAME',
	'HTTP_REFERER',
	'HTTP_X_FORWARDED_FOR',
	'HTTP_CLIENT_IP',
	'REQUEST_URI',
	'HTTP_ACCEPT_ENCODING',
	'DOCUMENT_ROOT'
);
if (version_compare(PHP_VERSION, '4.2.1', '<=')) {
	if (!is_array($_SERVER)) {
		$_SERVER = array();
	}
	foreach ($http_svars as $http_var) {
		$_SERVER[$http_var] = getenv(str_replace("\0", '', $http_var));
	}
}
$empty_svars = array_diff ($http_svars, array_keys($_SERVER));
foreach ($empty_svars as $key) {
	$_SERVER[$key] = '';
}

if (empty($_SERVER['DOCUMENT_ROOT'])) {
	$_SERVER['DOCUMENT_ROOT'] = getDocumentRoot();
	if (empty($_SERVER['DOCUMENT_ROOT'])) {
		$_SERVER['DOCUMENT_ROOT'] = $config['fpath'];
	}
}
?>
