<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

class manageconfig {

	var $file;
	var $data;
	var $opt;
	var $varname;

	function manageconfig() {

		if (!defined('str')) {
			define('str', 2);
		}
		if (!defined('int')) {
			define('int', 1);
		}

	}

	function getdata($file='data/config.inc.php', $varname = 'config') {
		$this->data = array();
		$this->file = $file;
		$this->varname = $varname;
		if (file_exists($this->file)) {
			require($this->file);
		}
		else {
			$this->createfile($this->file, $varname);
		}
		if (!isset($$varname)) {
			$$varname = array();
		}
		$this->data = $$varname;
		$this->opt = array();
	}

	function createfile($file, $varname) {
		global $filesystem;
		$top = '<?php'."\nif (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }\n";
		$top .= '$'.$varname.' = array();'."\n".'?>';

		$filesystem->file_put_contents($file, $top);

	}

	function _escapeNewline($nl) {
		$nl = str_replace("\r\n", '\\r\\n', $nl[1]);
		$nl = str_replace("\n", '\\n', $nl);
		$nl = str_replace("\r", '\\r', $nl);
		$nl = str_replace("\t", '\\t', $nl);
		$str = "'.\"";
		$str .= $nl;
		$str .= "\".'";
		return $str;
	}

	function _prepareString($val2) {
		$val2 = str_replace("\0", "", $val2);
		$val2 = str_replace('\\', '\\\\', $val2);
		$val2 = str_replace("'", "\\'", $val2);
		$val2 = preg_replace_callback("/((\r\n|\n|\r|\t)+)/s", array(&$this, '_escapeNewline'), $val2);
		$val2 = "'{$val2}'";
		return $val2;
	}

	function savedata() {
		global $filesystem;
		$top = '<?php'."\nif (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }\n";
		$top .= '$'.$this->varname.' = array();'."\n";

		$cfg = array();
		while (list($key, $val) = each($this->data)) {
			if (is_array($val)) {
				foreach ($val as $key2 => $val2) {
					if ((isset($this->opt[$key][$key2]) && $this->opt[$key][$key2] == int) || is_int($val2)) {
						$val2 = intval($val2);
					}
					else {
						$val2 = $this->_prepareString($val2);
					}
					$cfg[] = '$'.$this->varname."['{$key}']['{$key2}'] = {$val2};";
				}
			}
			else {
				if ((isset($this->opt[$key]) && $this->opt[$key] == int) || is_int($val)) {
					$val = intval($val);
				}
				else {
					$val = $this->_prepareString($val);
				}
				$cfg[] = '$'.$this->varname."['{$key}'] = {$val};";
			}
		}
		natcasesort($cfg);

		$newdata = implode("\n", $cfg);
		$bottom = "\n".'?>';

		$filesystem->file_put_contents($this->file,$top.$newdata.$bottom);
	}

	function updateconfig($key, $type = str, $val = null) {
		if (is_array($key)) {
			$key = array_map('trim', $key);
			$group = $key[0];
			$key = $key[1];
		}
		else {
			$key = trim($key);
		}
		if ($val == null) {
			global $gpc;
			if (isset($gpc)) {
				$val = $gpc->get($key, none);
			}
			else {
		        if (isset($_REQUEST[$key])) {
		            if ($type == int) {
		                $val = intval(trim($_REQUEST[$key]));
		            }
		            else {
		                $val = $_REQUEST[$key];
		            }
		        }
		        else {
		            if ($type == str) {
		                $val = '';
		            }
		            elseif ($type == int) {
		                $val = 0;
		            }
		        }
			}
		}

		if (isset($group)) {
			$this->opt[$group][$key] = $type;
			$this->data[$group][$key] = $val;
		}
		else {
			$this->opt[$key] = $type;
			$this->data[$key] = $val;
		}

	}

	function delete($key) {
		if (is_array($key)) {
			$key = array_map('trim', $key);
			unset($this->opt[$key[0]][$key[1]], $this->data[$key[0]][$key[1]]);
		}
		else {
			$key = trim($key);
			unset($this->opt[$key], $this->data[$key]);
		}
	}

}

?>
