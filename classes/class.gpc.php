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

if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

class GPC {

	var $prepare_original;
	var $prepare_entity;

	function GPC() {
		if (!defined('str')) {
			define('str', 2);
		}
		if (!defined('int')) {
			define('int', 1);
		}
		if (!defined('arr_str')) {
			define('arr_str', 5); // keys => int, values => str
		}
		if (!defined('arr_int')) {
			define('arr_int', 4); // keys and values => int
		}
		if (!defined('arr_str_int')) {
			define('arr_str_int', 9); // keys => str, values => int
		}
		if (!defined('arr_str_str')) {
			define('arr_str_str', 9); // keys and values => str
		}
		if (!defined('none')) {
			define('none', 0);
		}
		if (!defined('arr_none')) {
			define('arr_none', 3);
		}
		if (!defined('db_esc')) {
			define('db_esc', 6);
		}
		if (!defined('html_enc')) {
			define('html_enc', 7);
		}
		if (!defined('path')) {
			define('path', 8);
		}
		$this->prepare_original = array('"', "'", '<', '>');
		$this->prepare_entity = array('&quot;', '&#039;', '&lt;', '&gt;');
		$this->php523 = version_compare(PHP_VERSION, '5.2.3', '>=');
	}

	function get($index, $type = none, $standard = NULL) {
		if (isset($_REQUEST[$index])) {
			$value = $_REQUEST[$index];
			if (is_array($value) && $type != arr_str && $type != arr_int && $type != arr_none && $type != arr_str_int && $type != arr_str_str) {
				$value = null;
			}
			if ($type == str || $type == arr_str || $type == arr_str_str) {
				if ($type == str) {
					$value = trim($value);
				}
				$var = $this->save_str($value, true, ($type != arr_str_str));
				if (($type == arr_str || $type == arr_str_str) && !is_array($var)) {
					$var = array($var);
				}
			}
			else if ($type == path) {
				$var = convert2path(trim($value));
			}
			elseif ($type == int || $type == arr_int || $type == arr_str_int) {
				if ($type == int && ($value === '' || $value === null)) {
					if ($standard === null) {
						$var = 0;
					}
					else {
						$var = $standard;
					}
				}
				else {
					$var = $this->save_int($value, ($type != arr_str_int));
					if (($type == arr_int || $type == arr_str_int) && !is_array($var)) {
						$var = array($var);
					}
				}
			}
			elseif ($type == db_esc) {
				global $db;
				$var = $this->secure_null($value);
				$var = $db->escape_string($var);
			}
			elseif ($type == html_enc) {
				$var = $this->save_str($value, false);
			}
			else {
				$var = $this->secure_null($value);
				if ($type == arr_none && !is_array($var)) {
					$var = array($var);
				}
			}
		}
		else {
			if ($standard === null) {
				if ($type == str || $type == db_esc || $type == html_enc || $type == path) {
					$var = '';
				}
				elseif ($type == int) {
					$var = 0;
				}
				elseif ($type == arr_int || $type == arr_str || $type == arr_none || $type == arr_str_int || $type == arr_str_str) {
					$var = array();
				}
				else {
					$var = null;
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
		if (is_numeric($var) || empty($var)) {
			// Do nothing to save time
		}
		elseif (is_array($var)) {
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
			$var = str_replace($this->prepare_original, $this->prepare_entity, $var);
		}
		return $var;
	}

	function save_str($var, $db_esc = true, $numerated_array = false){
		if (is_numeric($var) || empty($var)) {
			// Do nothing to save time
		}
		elseif (is_array($var)) {
			$cnt = count($var);
			$keys = array_keys($var);
			for ($i = 0; $i < $cnt; $i++){
				if ($numerated_array) {
					$key = $this->save_int($keys[$i]);
				}
				else {
					$key = $this->save_str($keys[$i]);
				}
				if (!isset($var[$key]) || $key != $keys[$i]) {
					trigger_error('Error: Hacking Attempt (GPC::save_str)', E_USER_ERROR);
				}
				$var[$key] = $this->save_str($var[$key], $db_esc);
			}
		}
		elseif (is_string($var)){
			global $db, $lang;
			$var = preg_replace('#(script|about|applet|activex|chrome|mocha):#is', "\\1&#058;", $var);
			$var = $this->secure_null($var);
			if ($this->php523) {
				$var = htmlentities($var, ENT_QUOTES, $lang->charset(), false);
			}
			else {
				$var = htmlentities($var, ENT_QUOTES, $lang->charset());
				$var = str_replace('&amp;#', '&#', $var);
			}
			$var = preg_replace("~\\\\(\r|\n)~", "&#92;\\1", $var); // NL Hack
			if ($db_esc == true && is_object($db)) {
				$var = $db->escape_string($var);
			}
			elseif ($db_esc == true) {
				$var = addslashes($var);
			}
		}
		return $var;
	}

	function save_int($var, $numerated_array = false){
		if (is_array($var)) {
			$cnt = count($var);
			$keys = array_keys($var);
			for ($i = 0; $i < $cnt; $i++){
				if ($numerated_array) {
					$key = $this->save_int($keys[$i]);
				}
				else {
					$key = $this->save_str($keys[$i]);
				}
				if (!isset($var[$key]) || $key != $keys[$i]) {
					trigger_error('Error: Hacking Attempt (GPC::save_int)', E_USER_ERROR);
				}
				$var[$key] = $this->save_int($var[$key]);
			}
		}
		else {
			$var = intval(trim($var));
		}
		return $var;
	}

	function unescape($var) {
		if (is_numeric($var) || empty($var)) {
			// Do nothing to save time
		}
		elseif (is_array($var)) {
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
			global $db;
			if (is_object($db)) {
				$var = $db->unescape_string($var);
			}
			else {
				$var = stripslashes($var);
			}
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
		if (is_numeric($array) || empty($array)) {
			return $array;
		}
		elseif(is_array($array)) {
			return array_map(array(&$this, 'stripslashes'), $array); // Durchsucht nur 1-dimensionale Arrays
		}
		else {
			return stripslashes($array);
		}
	}

	// from php.net
	// ToDo: Remove in 0.8 RC5
	function html_entity_decode($string, $mode = ENT_COMPAT) {
	    // replace literal entities
	    static $trans_tbl;
	    if (!isset($trans_tbl)) {
	        $trans_tbl = array();

	        foreach (get_html_translation_table(HTML_ENTITIES, $mode) as $val=>$key) {
	            $trans_tbl[$key] = utf8_encode($val);
	        }
	    }
		static $cb1;
		if (!isset($cb1)) {
			$cb1 = create_function('$m', 'return code2utf(hexdec($m[1]));');
		}
		static $cb2;
		if (!isset($cb2)) {
			$cb2 = create_function('$m', 'return code2utf($m[1]);');
		}

	    // replace numeric entities
	    $string = preg_replace_callback('~&#x([0-9a-f]+);~i', $cb1, $string);
	    $string = preg_replace_callback('~&#0*([0-9]+);~', $cb2, $string);

	    return strtr($string, $trans_tbl);
	}

	function plain_str($var, $utf = true) {
		if (is_numeric($var) || empty($var)) {
			// Do nothing to save time
		}
		elseif (is_array($var)) {
			$cnt = count($var);
			$keys = array_keys($var);

			for ($i = 0; $i < $cnt; $i++){
				$key = $keys[$i];
				$var[$key] = $this->plain_str($var[$key], $utf);
			}
		}
		elseif (is_string($var)){
			if ($utf == true) {
				$var = $this->html_entity_decode($var, ENT_QUOTES); // Todo: Make PHP5 only: html_entity_decode($var, ENT_QUOTES, 'UTF-8');
			}
			else {
				global $lang;
				
				static $cb1;
				if (!isset($cb1)) {
					$cb1 = create_function('$m', 'return chr(hexdec($m[1]));');
				}
				static $cb2;
				if (!isset($cb2)) {
					$cb2 = create_function('$m', 'return chr($m[1]);');
				}
				
				$var = preg_replace_callback('~&#x([0-9a-f]+);~i', $cb1, $var); // ToDo: Convert to correct charset
				$var = preg_replace_callback('~&#([0-9]+);~', $cb2, $var);
				$var = html_entity_decode($var, ENT_QUOTES, $lang->charset());
			}
		}
		return $var;
	}

}

// Returns the utf string corresponding to the unicode value (from php.net, courtesy - romans@void.lv)
// ToDo: Remove in 0.8 RC5
function code2utf($num) {
    if ($num < 128) return chr($num);
    if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
    if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    return '';
}
?>