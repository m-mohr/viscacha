<div class="bbody">
<?php
echo "<strong>Starting Update:</strong><br />";

require('../data/config.inc.php');
require_once('lib/function.variables.php');
require_once('../classes/class.phpconfig.php');

function return_array($group, $id) {
	$file = "../language/{$id}/{$group}.lng.php";
	if (file_exists($file)) {
		include($file);
	}
	if (!isset($lang) || !is_array($lang)) {
		$lang = array();
	}
	return $lang;
}

function getLangCodes() {
	global $db;
	$l = array();
	$result = $db->query('SELECT id FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
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
	require_once('../classes/class.ini.php');
	$myini = new INI();
	$result = $db->query("SELECT id, internal FROM {$db->pre}packages");
	$data = array();
	$disable = array();
	$dependencies = array();
	$assoc = array();
	while ($row = $db->fetch_assoc($result)) {
		$ini = $myini->read("../modules/{$row['id']}/package.ini");

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

echo "- Source files loaded<br />";

if (!class_exists('filesystem')) {
	require_once('../classes/class.filesystem.php');
	$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
	$filesystem->set_wd($config['ftp_path']);
}
if (!class_exists('DB')) {
	require_once('../classes/database/'.$config['dbsystem'].'.inc.php');
	$db = new DB($config['host'], $config['dbuser'], $config['dbpw'], $config['database'], $config['dbprefix']);
	$db->setPersistence($config['pconnect']);
	$db->errlogfile = '../'.$db->errlogfile;
}

echo "- FTP class loaded, Database connection started.<br />";

// Config/Hooks
$c = new manageconfig();
$c->getdata('../data/config.inc.php');
$c->updateconfig('version', str, VISCACHA_VERSION);
$c->delete('asia');
$c->savedata();

$c = new manageconfig();
$c->getdata('../admin/data/config.inc.php', 'admconfig');
$c->updateconfig('default_language', int, 0);
$c->updateconfig('checked_package_updates', int, 0);
$c->savedata();

$hooks = file_get_contents('../admin/data/hooks.txt');
if (strpos($hooks, "-update") === false) {
	$hooks = str_replace("-uninstall", "-uninstall\r\n-update_init\r\n-update_finish", $hooks);
	$filesystem->file_put_contents('../admin/data/hooks.txt', $hooks);
}
echo "- Configuration and Hooks updated.<br />";

// Old files
$filesystem->unlink('../classes/class.imageconverter.php');
echo "- Old files deleted.<br />";

// Stylesheets
$dir = dir('../designs/');
while (false !== ($entry = $dir->read())) {
	$path = $dir->path.DIRECTORY_SEPARATOR.$entry.DIRECTORY_SEPARATOR;
	if (is_dir($path) && is_numeric($entry) && $entry > 0) {
   		if (file_exists($path.'standard.css')) {
   			$file = file_get_contents($path.'standard.css');
			$file .= "\r\n.tooltip {\r\n	left: -1000px;\r\n	top: -1000px;\r\n	visibility: hidden;\r\n	position: absolute;\r\n	max-width: 300px;\r\n	max-height: 300px;\r\n	overflow: auto;\r\n	border: 1px solid #336699;\r\n	background-color: #ffffff;\r\n	font-size: 8pt;\r\n}\r\n";
			$file .= "\r\n.tooltip_header {\r\n	display: block;\r\n	background-color: #E1E8EF;\r\n	color: #24486C;\r\n	padding: 3px;\r\n	border-bottom: 1px solid #839FBC;\r\n}\r\n";
			$file .= "\r\n.tooltip_body {\r\n	padding: 3px;\r\n}\r\n";
   			$filesystem->file_put_contents($path.'standard.css', $file);

   		}
   		if ($path.'ie.css') {
   			$file = file_get_contents($path.'ie.css');
   			$file .= "\r\n* html .tooltip {\r\n	width: 300px;\r\n}\r\n";
   			$filesystem->file_put_contents($path.'ie.css', $file);
   		}
	}
}
$dir->close();
echo "- Stylesheets updated.<br />";

// MySQL
$file = 'package/'.$package.'/db/db_changes.sql';
$sql = file_get_contents($file);
$sql = str_ireplace('{:=DBPREFIX=:}', $db->prefix(), $sql);
$db->multi_query($sql);
echo "- Database tables updated.<br />";

// Languages
$ini = array(
	'settings' => array(
		'language' => array(
			'compatible_version' => VISCACHA_VERSION
		),
		'language_de' => array(
			'compatible_version' => VISCACHA_VERSION
		)
	),
	'global' => array(
		'language' => array(
			'upload_error_default' => null,
			'upload_error_fileexists' => null,
			'upload_error_maxfilesize' => null,
			'upload_error_maximagesize' => null,
			'upload_error_noaccess' => null,
			'upload_error_noupload' => null,
			'upload_error_wrongfiletype' => null,
			'ats_select9' => null,
			'ats_choose' => 'No Status',
			'ats_choose_standard_a' => 'Use default setting (Article)',
			'ats_choose_standard_n' => 'Use default setting (News)',
			'profile_never' => 'Never'
		),
		'language_de' => array(
			'upload_error_default' => null,
			'upload_error_fileexists' => null,
			'upload_error_maxfilesize' => null,
			'upload_error_maximagesize' => null,
			'upload_error_noaccess' => null,
			'upload_error_noupload' => null,
			'upload_error_wrongfiletype' => null,
			'ats_select9' => null,
			'ats_choose' => 'Kein Status',
			'ats_choose_standard_a' => 'Standardeinstellung nutzen (Artikel)',
			'ats_choose_standard_n' => 'Standardeinstellung nutzen (News)',
			'editprofile_about_longdesc' => 'Hier können Sie sich eine persönliche "Forenseite" erstellen.<br /><br />Sie können BB-Codes und maximal <em>{$chars}</em> Zeichen für die Seite nutzen.',
			'profile_about' => 'Persönliche Seite',
			'profile_never' => 'Nie'
		)
	)
);

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
		$c->getdata("../language/{$lid}/{$file}.lng.php", 'lang');
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

echo "- Language files updated.<br />";

// Set incompatible packages inactive
setPackagesInactive();
echo "- Incompatible Packages set as 'inactive'.<br />";

// Refresh Cache
$dirs = array('../cache/', '../cache/modules/');
foreach ($dirs as $dir) {
	if ($dh = @opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if (strpos($file, '.php') !== false) {
				$filesystem->unlink($dir.$file);
			}
	    }
		closedir($dh);
	}
}
echo "- Cache cleared.<br />";
echo "<br /><strong>Finished Update!</strong>";
?>
</div>
<div class="bfoot center"><input type="submit" value="Continue" /></div>