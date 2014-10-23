<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2007  Matthias Mohr, MaMo Net

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
    		if ($config['asia'] == 0) {
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

	function save_str($var){
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
    		$var = preg_replace('#(script|about|applet|activex|chrome|mocha):#is', "\\1&#058;", $var);
    		$var = $this->secure_null($var);
    		if ($config['asia'] == 1) {
    			//$var = preg_replace('/[^\x26\x09\x0A\x0D\x20-\x7F]/e', '"&#".ord("$0").";"', $var);
    			$var = htmlentities($var, ENT_QUOTES, $config['asia_charset']);
    			$var = str_replace('&amp;#', '&#', $var);
				$var = htmlspecialchars_decode($var);
    		}
			if (is_object($db)) {
    			$var = $db->escape_string($var);
    		}
    		else {
    			$var = addslashes($var);
    		}
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
    			$var[$key] = $this->unescape($var[$key]);
    		}
    	}
    	elseif (is_object($var)) {
    		$ovar = get_object_vars($var);
    		$cnt = count($ovar);
    		$keys = array_keys($ovar);

    		for ($i = 0; $i < $cnt; $i++) {
    			$key = $keys[$i];
    			$var->$key = $this->unescape($ovar[$key]);
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
    		$cnt = count($data);
    		$keys = array_keys($data);

    		for ($i = 0; $i < $cnt; $i++){
    			$key = $keys[$i];
    			$data[$key] = $this->secure_null($data[$key]);
    		}
    	}
		else {
			$data = str_replace("\0", '', $data);
		}
		return $data;
	}

	function stripslashes($array) {
		if(is_array($array)) {
			return array_map(array(&$this, 'stripslashes'), $array);
		}
		else {
			return stripslashes($array);
		}
	}

}


?>
