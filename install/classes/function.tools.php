<?php
function getFUrl() {
	// HTTP_HOST is having the correct browser url in most cases...
	$server_name = (!empty($_SERVER['HTTP_HOST'])) ? strtolower($_SERVER['HTTP_HOST']) : ((!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME'));
	$https = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://');

	$source = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
	if (!$source) {
		$source = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
	}
	// Replace backslashes and doubled slashes (could happen on some proxy setups)
	$source = str_replace(array('\\', '//', '/install'), '/', $source);
	$source = trim(trim(dirname($source)), '/');

	$furl = rtrim($https.$server_name.'/'.$source, '/');
	return $furl;
}
function makeOneLine($str) {
	return str_replace(array("\r\n","\n","\r","\t","\0"), ' ', $str);
}
function getFilePath($package, $step) {
	$package2 = explode('_', $package, 2);
	if (!empty($package2[1]) && !file_exists('install/package/'.$package.'/steps/'.$step.'.php')) {
		return 'install/package/'.$package2[0].'/steps/'.$step.'.php';
	}
	return 'install/package/'.$package.'/steps/'.$step.'.php';
}
function return_array($group, $id) {
	$file = "language/{$id}/{$group}.lng.php";
	if (file_exists($file)) {
		include($file);
	}
	if (!isset($lang) || !is_array($lang)) {
		$lang = array();
	}
	return $lang;
}
function updateLanguageFiles($ini) {
	$c = new manageconfig();
	$codes = array();
	$keys = array('language', 'language_de');
	$codes = getLangCodesByKeys($keys);
	$langcodes = getLangCodes();
	foreach ($langcodes as $code => $lid) {
		$ldat = explode('_', $code);
		if (isset($codes[$ldat[0]])) {
			$count = count($codes[$ldat[0]]);
			if (in_array('', $codes[$ldat[0]])) {
				$count--;
			}
		}
		else {
			$count = -1;
		}
		if (isset($codes[$ldat[0]]) && !empty($ldat[1]) && in_array($ldat[1], $codes[$ldat[0]])) { // Nehme Original
			$src = 'language_'.$code;
		}
		elseif(isset($codes[$ldat[0]]) && in_array('', $codes[$ldat[0]])) { // Nehme gleichen Langcode, aber ohne Countrycode
			$src = 'language_'.$ldat[0];
		}
		elseif(isset($codes[$ldat[0]]) && $count > 0) { // Nehme gleichen Langcode, aber falchen Countrycode
			$src = 'language_'.$ldat[0].'_'.reset($codes[$ldat[0]]);
		}
		else { // Nehme Standard
			$src = 'language';
		}
		foreach($ini as $file => $data){
			if (!isset($data[$src])) {
				continue;
			}
			$c->getdata("language/{$lid}/{$file}.lng.php", 'lang');
			foreach ($data[$src] as $varname => $text) {
				if ($text === null) {
					$c->delete($varname);
				}
				else {
					$c->updateconfig($varname, str, $text);
				}
			}
			$c->savedata();
		}
	}
}
function getLangCodes() {
	global $db;
	$l = array();
	$result = $db->query('SELECT id FROM '.$db->pre.'language ORDER BY language');
	while($row = $db->fetch_assoc($result)) {
		$settings = return_array('settings', $row['id']);
		if (!isset($l[$settings['spellcheck_dict']])) {
			$l[$settings['spellcheck_dict']] = array();
		}
		$l[$settings['spellcheck_dict']] = $row['id'];
	}
	return $l;
}
function getLangCodesByKeys($keys) {
	$codes = array();
	foreach ($keys as $entry) {
		if (preg_match('~language_(\w{2})_?(\w{0,2})~i', $entry, $code)) {
			if (!isset($codes[$code[1]])) {
				$codes[$code[1]] = array();
			}
			if (isset($code[2])) {
				$codes[$code[1]][] = $code[2];
			}
			else {
				if (!in_array('', $codes[$code[1]])) {
					$codes[$code[1]][] = '';
				}
			}
		}
	}
	return $codes;
}
function setPackagesInactive() {
	global $db;
	require_once('install/classes/class.ini.php');
	$myini = new INI();
	$result = $db->query("SELECT id, internal FROM {$db->pre}packages");
	$data = array();
	$disable = array();
	$dependencies = array();
	$assoc = array();
	while ($row = $db->fetch_assoc($result)) {
		$ini = $myini->read("modules/{$row['id']}/package.ini");

		if (!isset($ini['dependency']) || !is_array($ini['dependency'])) {
			$ini['dependency'] = array();
		}

		$min_compatible = ((!empty($ini['min_version']) && version_compare(VISCACHA_VERSION, $ini['min_version'], '>=')) || empty($ini['min_version']));
		$max_compatible = ((!empty($ini['max_version']) && version_compare(VISCACHA_VERSION, $ini['max_version'], '<=')) || empty($ini['max_version']));

		$data[$row['id']] = array(
			'min_version' => !empty($ini['min_version']) ? $ini['min_version'] : '',
			'max_version' => !empty($ini['max_version']) ? $ini['max_version'] : '',
			'id' => $row['id'],
			'internal' => $row['internal'],
			'dependency' => (isset($ini['dependency']) && is_array($ini['dependency'])) ? $ini['dependency'] : array(),
			'compatible' => ($min_compatible && $max_compatible)
		);

		if ($data[$row['id']]['compatible'] == false) {
			$disable[$row['id']] = $row['internal'];
		}
		$dependencies = array_merge($dependencies, $ini['dependency']);
		if (isset($assoc[$row['internal']])) {
			$assoc[$row['internal']][] = $row['id'];
		}
		else {
			$assoc[$row['internal']] = array($row['id']);
		}
	}

	$n = 0;
	while (count($dependencies) > 0) {
		reset($dependencies);
		$value = current($dependencies);
		$key = key($dependencies);
		if (isset($assoc[$value])) {
			foreach ($assoc[$value] as $id) {
				if (isset($data[$id]['dependency']) && is_array($data[$id]['dependency'])) {
					foreach ($data[$id]['dependency'] as $int) {
						if (!in_array($int, $disable) && !in_array($int, $dependencies)) {
							$dependencies[] = $int;
						}
					}
				}
				if (!isset($disable[$id])) {
					$disable[$id] = $value;
				}
			}
		}
		unset($dependencies[$key]);

		$n++;
		if ($n > 10000) {
			trigger_error("setPackagesInactive(): Your database is inconsistent - Please ask the Viscacha support for help.", E_USER_ERROR); // Break loop, Database seems to be inconsistent (or thousands of packages are installed)
		}
	}
	if (count($disable) > 0) {
		$in = implode(',', array_keys($disable));
		$db->query("UPDATE {$db->pre}packages SET active = '0' WHERE id IN ({$in})");
	}
}
function removeHook(&$array, $value) {
	foreach($array as $key => $val) {
		if(strpos($val, $value) !== false) {
			unset($array[$key]);
		}
	}
}
function insertHookAfter(&$array, $value, $after) {
	$values = array();
	if (is_array($value)) {
		foreach ($value as $val) {
			$values[] = "-{$val}";
		}
	}
	else {
		$values[] = "-{$value}";
	}

    if (is_array($array)) {
		$offset = 0;
		foreach($array as $key => $val) {
			if(strpos($val, $after) !== false) {
				break;
			}
    		$offset++;
		}

        $array  = array_values($array);
        $offset = intval($offset);
        if ($offset < 0 || $offset >= count($array)) {
            array_push($array, $value);
        }
        elseif ($offset == 0) {
            array_unshift($array, $value);
        }
        else {
            $temp  = array_slice($array, 0, $offset);
            $array = array_slice($array, $offset);
            $array = array_merge($temp, $values, $array);
        }
    }
    else {
    	trigger_error('Empty hook array given', E_USER_NOTICE);
    }
}

define('GPC_HTML', 1);
define('GPC_DB', 2);
define('GPC_ALNUM', 3); // A-Z, a-z, 0-9, -, _

function GPC_escape($var, $type = GPC_HTML){
	global $config, $lang, $db;
	if (is_numeric($var) || empty($var)) {
		// Do nothing to save time
	}
	elseif (is_array($var)) {
		foreach ($var as $key => $value) {
			$var[$key] = GPC_escape($value);
		}
	}
	elseif (is_string($var)){
		$var = str_replace("\0", '', $var);
		if ($type == GPC_HTML) {
			$var = preg_replace('#(script|about|applet|activex|chrome|mocha):#is', "\\1&#058;", $var);
			$var = str_replace("\0", '', $var);
			if (version_compare(PHP_VERSION, '5.2.3', '>=')) {
				$var = htmlentities($var, ENT_QUOTES, 'ISO-8859-1', false);
			}
			else {
				$var = htmlentities($var, ENT_QUOTES, 'ISO-8859-1');
				$var = str_replace('&amp;#', '&#', $var);
			}
		}
		if ($type == GPC_DB && isset($db) && is_object($db)) {
			$var = $db->escape_string($var);
		}
		elseif($type != GPC_ALNUM) {
			$var = addslashes($var);
		}
		if ($type == GPC_ALNUM) {
			$var = preg_replace("~[^a-z0-9_\-]+~i", '', $var);
		}
	}
	return $var;
}

function GPC_unescape($var){
	if (is_numeric($var) || empty($var)) {
		// Do nothing to save time
	}
	elseif (is_array($var)) {
		foreach ($var as $key => $value) {
			$var[$key] = GPC_unescape($value);
		}
	}
	elseif (is_string($var)){
		$var = stripslashes(trim($var));
	}
	return $var;
}

// Variables
@set_magic_quotes_runtime(0);
@ini_set('magic_quotes_gpc',0);
// Start - Thanks to phpBB for this code
if (isset($_POST['GLOBALS']) || isset($_FILES['GLOBALS']) || isset($_GET['GLOBALS']) || isset($_COOKIE['GLOBALS'])) {
	die("Hacking attempt (Globals)");
}
if (isset($_SESSION) && !is_array($_SESSION)) {
	die("Hacking attempt (Session Variable)");
}
if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on') {
	unset($not_used, $input);
	$not_unset = array('_GET', '_POST', '_COOKIE', '_SERVER', '_SESSION', '_ENV', '_FILES');

	$input = array_merge($_GET, $_POST, $_COOKIE, $_ENV, $_FILES);
	if (isset($_SERVER) && is_array($_SERVER)) {
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
// End

if (get_magic_quotes_gpc() == 1) {
	$_REQUEST = GPC_unescape($_REQUEST);
}
?>