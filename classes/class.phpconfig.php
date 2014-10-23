<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "class.phpconfig.php") die('Error: Hacking Attempt');

DEFINE('STRING','string');
DEFINE('INT','int');

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
		require($this->file);
		if (!isset($$varname)) {
			$$varname = array();
		}
		$this->data = $$varname;
		$this->opt = array();
	}

	function createfile($file, $varname) {
		global $filesystem;
		$top = '<?php'."\n".'if (isset($_SERVER[\'PHP_SELF\']) && basename($_SERVER[\'PHP_SELF\']) == "'.basename($file).'") die(\'Error: Hacking Attempt\');'."\n";
		$top .= '$'.$varname.' = array();'."\n".'?>';
	
		$this->data = $filesystem->file_put_contents($file, $top);
	
	}
	
	function savedata($add = false) {
		global $filesystem;
		$top = '<?php'."\n".'if (isset($_SERVER[\'PHP_SELF\']) && basename($_SERVER[\'PHP_SELF\']) == "'.basename($this->file).'") die(\'Error: Hacking Attempt\');'."\n";
		if ($add == false) {
			$top .= '$'.$this->varname.' = array('."\n";
		}

		$cfg = array();
		while (list($key, $val) = each($this->data)) {
			if ((isset($this->opt[$key]) && $this->opt[$key] == int) || is_int($val)) {
				$val = intval($val+0);
			}
			elseif (isset($this->opt[$key]) && $this->opt[$key] == null) {
				// Fall through
			}
			else {
				$val = str_replace("'", "\\'", $val);
				$val = "'{$val}'";
			}
			if ($add == false) {
				$cfg[] = "'".$key."' => ".$val;
			}
			else {
				$cfg[] = '$'.$this->varname."['".$key."'] = ".$val.";";
			
			}
		}
		natcasesort($cfg);		

		if ($add == false) {
			$newdata = implode(",\n", $cfg);
			$bottom = "\n".');'."\n".'?>';
		}
		else {
			$newdata = implode("\n", $cfg);
			$bottom = "\n".'?>';
		}
	
		$this->data = $filesystem->file_put_contents($this->file,$top.$newdata.$bottom);
	
	}

	function updateconfig($key, $type = str, $val = null) {
		$key = trim($key);		
		if ($val == null) {
			global $gpc;
			if (isset($gpc)) {
				$val = $gpc->get($key, $type);
			}
			else {
		        if (isset($_REQUEST[$key])) {
		            if ($type == str) {
		                $val = trim($_REQUEST[$key]);
		            }
		            elseif ($type == int) {
		                $val = trim($_REQUEST[$key])+0;
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
		$this->opt[$key] = $type;
		$this->data[$key] = $val;
		
		
	}

	function delete($key) {
		$key = trim($key);
		unset($this->opt[$key], $this->data[$key]);
	}

}

?>
