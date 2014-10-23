<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

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
	function lang($js = false, $level = E_USER_ERROR) {
		$this->js = $js;
		$this->file = '';
		$this->vars = array();
		$this->benchmark = array('all' => 0, 'ok' => 0, 'error' => 0);
		$this->lngarray = array();
		$this->cache = array();
		$this->assign = array();
		if ($this->js > 0) {
			$dir = $this->js;
		}
		else {
			global $config;
			$dir = $config['langdir'];
		}
		if (!$this->setdir($dir)) {
			if ($this->js > 0) {
				die('alert("Language-Directory not found!");');
			}
			else {
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

		@ini_set('default_charset', '');
		if (!headers_sent()) {
			viscacha_header('Content-type: text/html; charset='.$this->phrase('charset'));
		}

		global $slog;
		if (isset($slog) && is_object($slog) && method_exists($slog, 'setlang')) {
			$slog->setlang($this->phrase('fallback_no_username'), $this->phrase('timezone_summer'));
		}
		global $config, $breadcrumb;
		if (isset($breadcrumb)) {
			$isforum = array('addreply','attachments','edit','forum','manageforum','managetopic','misc','newtopic','pdf','search','showforum','showtopic');
			if ($config['indexpage'] != 'forum' && in_array(SCRIPTNAME, $isforum)) {
				$breadcrumb->Add($this->phrase('forumname'), iif(SCRIPTNAME != 'forum', 'forum.php'));
			}
		}
	}

	function initAdmin($dir = null) {
		if ($dir != null) {
			$this->setdir($dir);
		}
		$this->group('settings');
		$this->group('admin/global');
		$this->group('modules');
		$this->group('custom');

		@ini_set('default_charset', '');
		if (!headers_sent()) {
			viscacha_header('Content-type: text/html; charset='.$this->phrase('charset'));
		}
	}

	function javascript() {
		$file = $this->dir.DIRECTORY_SEPARATOR.'javascript.lng.php';
		require($file);
		echo 'var lng = new Array();'."\n";
		foreach ($lang as $k => $l) {
			$l = str_replace("'", "\\'", $l);
			echo "lng['$k'] = '$l';\n";
		}
	}

	function return_array($group = '') {
		if (!empty($group)) {
			$file = $this->dir.DIRECTORY_SEPARATOR.$group.'.lng.php';
			@require($file);
			return $lang;
		}
		else {
			return $this->lngarray;
		}
	}

	function get_mail($file,$ext='php') {
		$this->benchmark['all']++;
		$this->file = $this->dir.DIRECTORY_SEPARATOR.'mails/'.$file.'.'.$ext;
		if (file_exists($this->file) == false) {
		    $this->benchmark['error']++;
			return FALSE;
		}
        $this->benchmark['ok']++;
        $content = file_get_contents($this->file);
        preg_match("|<title>(.+?)</title>.*?<comment>(.+?)</comment>|is", $content, $matches);
		$matches[1] = $this->parse_pvar($matches[1]);
		$matches[2] = $this->parse_pvar($matches[2]);
        return array('title' => $matches[1], 'comment' => $matches[2]);
	}
	function get_text($file,$ext='php') {
		$this->benchmark['all']++;
		$this->file = $this->dir.DIRECTORY_SEPARATOR.'texts/'.$file.'.'.$ext;
		if (file_exists($this->file) == false) {
		    $this->benchmark['error']++;
			return FALSE;
		}
        $this->benchmark['ok']++;
        $content = file_get_contents($this->file);
		$content = $this->parse_pvar($content);
        return $content;
	}
	function get_words($file = 'search') {
		$this->file = $this->dir.DIRECTORY_SEPARATOR.'words'.DIRECTORY_SEPARATOR.$file.'.inc.php';
		if (file_exists($this->file) == false) {
			return array();
		}
        $arr = file($this->file);
		$arr = array_map('trim', $arr);
        return $arr;
	}

	function parse_pvar($content) {
		return preg_replace('#\{(\$|\%|\@)(.+?)\}#ie', "\$this->parse_variable('\\2','\\1')", $content);
	}

	function phrase($phrase) {
		if (isset($this->lngarray[$phrase])) {
			$pphrase = $this->lngarray[$phrase];
			if (strpos($pphrase, '{') !== false) {
        		$pphrase = $this->parse_pvar($pphrase);
			}
			return $pphrase;
		}
		else {
			return $phrase;
		}
	}

	function assign($key, $val) {
		$this->assign[$key] = $val;
	}

	function parse_variable($key, $type) {
		if ($type == '%') {
			$keys = explode('->',$key);
			if (isset($this->assign[$keys[0]]->$keys[1])) {
				$var = $this->assign[$keys[0]]->$keys[1];
				return $var;
			}
			elseif(isset($GLOBALS[$keys[0]]->$keys[1])) {
				return $GLOBALS[$keys[0]]->$keys[1];
			}
		}
		elseif ($type == '@') {
			$keys = explode('->',$key);
			if (isset($keys[2])) {
				if (isset($this->assign[$keys[0]][$keys[1]][$keys[2]])) {
					$var = $this->assign[$keys[0]][$keys[1]][$keys[2]];
					return $var;
				}
				elseif(isset($GLOBALS[$keys[0]][$keys[1][$keys[2]]])) {
					return $GLOBALS[$keys[0]][$keys[1]][$keys[2]];
				}
			}
			else {
				if (isset($this->assign[$keys[0]][$keys[1]])) {
					$var = $this->assign[$keys[0]][$keys[1]];
					return $var;
				}
				elseif(isset($GLOBALS[$keys[0]][$keys[1]])) {
					return $GLOBALS[$keys[0]][$keys[1]];
				}
			}
		}
		else {
			if (isset($this->assign[$key])) {
				$var = $this->assign[$key];
				return $var;
			}
			elseif(isset($GLOBALS[$key])) {
				return $GLOBALS[$key];
			}
		}
		return '';
	}

	function group($group) {
		$file = $this->dir.DIRECTORY_SEPARATOR.$group.'.lng.php';
		if (file_exists($file) && !isset($this->cache[$file])) {
			@include($file);
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


	function setdir($dirId) {
		global $config;
		if ($dirId < 1) {
			$dirId = $config['langdir'];
		}

		$dir = "language/{$dirId}";
		if (@is_dir($dir) == false) {
			$dir = "{$config['fpath']}/language/{$dirId}";
			if (@is_dir($dir) == false) {
				$dir = extract_dir(dirname(__FILE__));
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
