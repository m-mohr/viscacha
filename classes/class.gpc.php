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
		if (!defined('db_esc')) {
			define('db_esc', 6);
		}
		if (!defined('html_enc')) {
			define('html_enc', 7);
		}
		$this->prepare_original = array('"', "'", '<', '>');
		$this->prepare_entity = array('&quot;', '&#039;', '&lt;', '&gt;');
		$this->php523 = version_compare(PHP_VERSION, '5.2.3', '>=');
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
			elseif ($type == db_esc) {
				global $db;
				$var = $this->secure_null($_REQUEST[$index]);
				$var = $db->escape_string($var);
			}
			elseif ($type == html_enc) {
				if ($type == str) {
					$_REQUEST[$index] = trim($_REQUEST[$index]);
				}
				$var = $this->save_str($_REQUEST[$index], false);
			}
			else {
				$var = $this->secure_null($_REQUEST[$index]);
			}
		}
		else {
			if ($standard === null) {
				if ($type == str || $type == db_esc || $type == html_enc) {
					$var = '';
				}
				elseif ($type == int) {
					$var = 0;
				}
				elseif ($type == arr_int || $type == arr_str || $type == arr_none) {
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

	function save_str($var, $db_esc = true){
		if (is_numeric($var) || empty($var)) {
			// Do nothing to save time
		}
		elseif (is_array($var)) {
			$cnt = count($var);
			$keys = array_keys($var);

			for ($i = 0; $i < $cnt; $i++){
				$key = $keys[$i];
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
			if ($db_esc == true && is_object($db)) {
				$var = $db->escape_string($var);
			}
			elseif ($db_esc == true) {
				$var = addslashes($var);
			}
		}
		return $var;
	}

	function save_int($var){
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
	    static $trans_tbl;

	    // replace numeric entities
	    $string = preg_replace('~&#x([0-9a-f]+);~ei', 'code2utf(hexdec("\\1"))', $string);
	    $string = preg_replace('~&#0*([0-9]+);~e', 'code2utf(\\1)', $string);

	    // replace literal entities
	    if (!isset($trans_tbl)) {
	        $trans_tbl = array();

	        foreach (get_html_translation_table(HTML_ENTITIES, $mode) as $val=>$key) {
	            $trans_tbl[$key] = utf8_encode($val);
	        }
	    }

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
				$var = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $var); // ToDo: Convert to correct charset
				$var = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $var);
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