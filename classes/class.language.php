<?php
/*
	Viscacha - An advanced bulletin board solution to manage your content easily
	Copyright (C) 2004-2017, Lutana
	http://www.viscacha.org

	Authors: Matthias Mohr et al.
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

class lang {

	var $dir;
	var $dirid;
	var $file;
	var $assign;
	var $benchmark;
	var $lngarray;
	var $cache;
	var $js;

	// ToDo: Alternatives Verzeichnis für den Fall, dass eine ID übergeben wurde, die nichtmehr aktiv ist...
	function __construct($js = false, $level = E_USER_ERROR) {
		$this->file = '';
		$this->vars = array();
		$this->benchmark = array('all' => 0, 'ok' => 0, 'error' => 0);
		$this->lngarray = array();
		$this->cache = array();
		$this->assign = array();
		$this->js = $js;

		if ($this->js > 0) {
			$dir = $this->js;
			if (!$this->setdir($dir)) {
				die('alert("Language-Directory not found!");');
			}
		}
		else {
			global $config;
			$dir = $config['langdir'];
			if (!$this->setdir($dir)) {
				trigger_error('Language-Directory not found!', $level);
			}
		}
	}

	function init($dir = null) {
		if ($dir != null) {
			$this->setdir($dir);
		}
		$this->group('settings');
		$this->group('global');
		$this->group('modules');
		$this->group('custom');

		@ini_set('default_charset', 'utf-8');
		mb_internal_encoding('UTF-8');
		if (!headers_sent()) {
			viscacha_header('Content-type: text/html; charset=utf-8');
		}

		global $slog;
		if (isset($slog) && is_object($slog) && method_exists($slog, 'setlang')) {
			$slog->setlang();
		}
	}

	function initAdmin($dir = null) {
		global $admconfig, $my;
		if (!empty($my->settings['default_language'])) {
			$dir = $my->settings['default_language'];
		}
		elseif (is_id($admconfig['default_language'])) {
			$dir = $admconfig['default_language'];
		}
		if ($dir != null) {
			$this->setdir($dir);
		}
		$this->group('settings');
		$this->group('admin/global');
		$this->group('modules');
		$this->group('custom');

		@ini_set('default_charset', 'utf-8');
		mb_internal_encoding('UTF-8');
		if (!headers_sent()) {
			viscacha_header('Content-type: text/html; charset=utf-8');
		}
	}

	function javascript($file = 'javascript') {
		require($this->get_path($file));
		if (isset($lang) && is_array($lang)) {
			$str = 'var lng = new Array();'."\n";
			foreach ($lang as $k => $l) {
				$l = str_replace("'", "\\'", $l);
				$str .= "lng['{$k}'] = '{$l}';\n";
			}
			return $str;
		}
		else {
			return false;
		}
	}

	function return_array($group = '') {
		if (!empty($group)) {
			require($this->get_path($group));
			if (isset($lang) && is_array($lang)) {
				return $lang;
			}
		}
		trigger_error('Array from language file can\'t be returned.', E_USER_NOTICE);
		return false;
	}

	function get_mail($file) {
		global $gpc;
		$this->benchmark['all']++;
		$this->file = $this->get_path(array('mails', $file), 'php');
		if (file_exists($this->file) == false) {
		    $this->benchmark['error']++;
			return false;
		}
        $this->benchmark['ok']++;
        $content = file_get_contents($this->file);
        preg_match("~<title>(.+?)</title>.*?<comment>(.+?)</comment>~isu", $content, $matches);
		$matches[1] = $this->parse_pvar($matches[1]);
		$matches[2] = $this->parse_pvar($matches[2]);
        return array(
        	'title' => $gpc->plain_str($matches[1]),
        	'comment' => $gpc->plain_str($matches[2])
        );
	}

	function get_words($file = 'search') {
		$this->file = $this->get_path(array('words', $file), 'inc.php');
		if (file_exists($this->file) == false) {
			return array();
		}
        $arr = file($this->file);
		$arr = array_map('trim', $arr);
        return $arr;
	}

	function group($group) {
		$this->file = $this->get_path($group);
		if (file_exists($this->file) && !isset($this->cache[$this->file])) {
			@include($this->file);
			if (isset($lang) && is_array($lang)) {
				$this->lngarray += $lang;
				$this->cache[$group] = true;
			}
			else {
				echo "<!-- Could not parse language-file {$file} -->";
			}
		}
		else {
			echo "<!-- Could not load language-file {$file} -->";
		}
	}
	
	function exists($phrase) {
		return isset($this->lngarray[$phrase]);
	}

	function phrase($phrase, $vars = array()) {
		$this->massAssign($vars);
		if ($this->exists($phrase)) {
			$pphrase = $this->lngarray[$phrase];
			if (\Str::contains($pphrase, '{')) {
        		$pphrase = $this->parse_pvar($pphrase);
			}
			return $pphrase;
		}
		else {
			return $phrase;
		}
	}

	function massAssign(array $vars) {
		foreach($vars as $key => $value) {
			$this->assign($key, $value);
		}
	}

	function assign($key, $val) {
		$this->assign[$key] = $val;
	}

	function parse_pvar($content) {
		$content = preg_replace_callback('~\{(\$|\%|\@)(.+?)\}~iu', array($this, 'parse_variable'), $content);
		return preg_replace('~\{\\\(\$|\%|\@)(.+?)\}~iu', '{\1\2}', $content);
	}

	function parse_variable($params) {
		list(, $type, $key) = $params;
		$keys = explode('->',$key);
		if ($type == '%') { // Object property / method
			if (isset($this->assign[$keys[0]])) {
				$var = $this->assign[$keys[0]];
			}
			elseif(isset($GLOBALS[$keys[0]])) {
				$var = $GLOBALS[$keys[0]];
			}

			if (count($keys) == 2 && isset($var) && is_object($var)) {
				$methodKeys = explode('(', $keys[1], 2);
				if (count($methodKeys) > 1 && \Str::substr($methodKeys[1], -1) == ')' && method_exists($var, $methodKeys[0])) { // Object method
					$args = array();
					$arg = \Str::substr($methodKeys[1], 0, -1);
					if (!empty($arg)) {
						$args = array($arg);
					}
					return call_user_func_array(array($var, $methodKeys[0]), $args);
				}
				elseif (count($methodKeys) == 1 && isset($var->{$methodKeys[0]})) { // Object property
					return $var->{$methodKeys[0]};
				}
			}

		}
		elseif ($type == '@') { // Array
			if (count($keys) == 3) { // Two dimensional
				if (isset($this->assign[$keys[0]][$keys[1]][$keys[2]])) {
					$var = $this->assign[$keys[0]][$keys[1]][$keys[2]];
					return $var;
				}
				elseif(isset($GLOBALS[$keys[0]][$keys[1][$keys[2]]])) {
					return $GLOBALS[$keys[0]][$keys[1]][$keys[2]];
				}
			}
			else { // One dimensional
				if (isset($this->assign[$keys[0]][$keys[1]])) {
					$var = $this->assign[$keys[0]][$keys[1]];
					return $var;
				}
				elseif(isset($GLOBALS[$keys[0]][$keys[1]])) {
					return $GLOBALS[$keys[0]][$keys[1]];
				}
			}
		}
		elseif ($type == '$' && count($keys) == 1) { // (Scalar) variable
			if (isset($this->assign[$keys[0]])) {
				return $this->assign[$keys[0]];
			}
			elseif(isset($GLOBALS[$keys[0]])) {
				return $GLOBALS[$keys[0]];
			}
		}
		return "{{$type}{$key}}"; // Not found. Don't change anything!
	}

	function setdir($dirId) {
		global $config;
		if ($dirId < 1) {
			$dirId = $config['langdir'];
		}

		$dir = "language/{$dirId}";
		if (@is_dir($dir) == false) {
			$dir = "{$config['fpath']}/language/{$dirId}";
			if (@is_dir($dir) == false) {
				$dir = extract_dir(__DIR__);
				$dir = "{$dir}/language/{$dirId}";
			}
		}

		$dir = realpath($dir);

		if (file_exists($dir)) {
			$this->dirid = $dirId;
			$this->dir = $dir;
			return true;
		}
		else {
			return false;
		}
	}

	function getdir($id = false) {
		if ($id == true) {
			return $this->dirid;
		}
		else {
			return $this->dir;
		}
	}

	function get_path($name, $ext = 'lng.php') {
		if (is_array($name)) {
			$name = implode(DIRECTORY_SEPARATOR, $name);
		}
		$this->file = $this->dir.DIRECTORY_SEPARATOR.$name.'.'.$ext;
		return $this->dir.DIRECTORY_SEPARATOR.$name.'.'.$ext;
	}

	function group_is_loaded($group) {
		if (isset($this->cache[$group]) && $this->cache[$group] == true) {
			return true;
		}
		else {
			return false;
		}
	}

}
?>