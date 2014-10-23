<div class="bbody">
<?php
echo "<strong>Starting Update:</strong><br />";

require('data/config.inc.php');
require_once('install/classes/class.phpconfig.php');

function loadSettingArray($path) {
	include("{$path}/settings.lng.php");
	if (isset($lang['lang_code'])) {
		return $lang;
	}
	else {
		return array('lang_code' => 'en');
	}
}

echo "- Source files loaded<br />";

if (!class_exists('filesystem')) {
	require_once('install/classes/class.filesystem.php');
	$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
	$filesystem->set_wd($config['ftp_path'], $config['fpath']);
}

echo "- FTP class loaded and initialized.<br />";

if (!class_exists('DB')) {
	require_once('install/classes/database/'.$config['dbsystem'].'.inc.php');
	$db = new DB($config['host'], $config['dbuser'], $config['dbpw'], $config['database'], $config['dbprefix']);
	$db->setPersistence($config['pconnect']);
}

echo "- Database class loaded and initialized.<br />";

// Config
$c = new manageconfig();
$c->getdata('data/config.inc.php');
$c->updateconfig('version', str, VISCACHA_VERSION);
$c->savedata();
echo "- Configuration updated.<br />";


// Languages
$ini = array (
  'settings' =>
  array (
    'language_de' =>
    array (
      'compatible_version' => VISCACHA_VERSION,
    ),
    'language' =>
    array (
      'compatible_version' => VISCACHA_VERSION,
    ),
  )
);
updateLanguageFiles($ini);
echo "- Language files updated.<br />";

// Set incompatible packages inactive
setPackagesInactive();
echo "- Incompatible Packages set as 'inactive'.<br />";

// Refresh Cache
$dirs = array('cache/', 'cache/modules/');
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