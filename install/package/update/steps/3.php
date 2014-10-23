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

echo "- Source files loaded<br />";

if (!class_exists('filesystem')) {
	require_once('../classes/class.filesystem.php');
	$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
	$filesystem->set_wd($config['ftp_path']);
}
if (!class_exists('DB')) {
	require_once('../classes/database/'.$config['dbsystem'].'.inc.php');
	$db = new DB($config['host'], $config['dbuser'], $config['dbpw'], $config['database'], $config['pconnect'], true, $config['dbprefix']);
	$db->pre = $db->prefix();
	$db->errlogfile = '../'.$db->errlogfile;
}

echo "- FTP class loaded, Database connection started.<br />";

// Config
$c = new manageconfig();
$c->getdata('../data/config.inc.php');
$c->updateconfig('version', str, VISCACHA_VERSION);
$c->updateconfig('syndication_insert_email', int, 0);
$c->savedata();
echo "- Configuration updated.<br />";

// MySQL
$db->query("ALTER TABLE `{$db->pre}spider` CHANGE `last_visit` `last_visit` text NOT NULL default ''", __LINE__, __FILE__);
echo "- Database table updated.<br />";

// templates
$dir = "../templates/";
$tplids = array();
$d = dir($dir);
while (false !== ($entry = $d->read())) {
	if (is_dir($dir.$entry) && preg_match('/^\d{1,}$/', $entry) && $entry != '.' && $entry != '..') {
		$tplids[] = $entry;
	}
}
$d->close();
foreach ($tplids as $id) {
	$tpldir = $dir.$id;
	$filesystem->chmod($tpldir.'/header.html', 0666);
	$header = file_get_contents($tpldir.'/header.html');
	$header = str_ireplace("<!--[if lt IE 7]>", "<!--[if IE]>", $header);
	$filesystem->file_put_contents($tpldir.'/header.html', $header);
}
echo "- Templates updated.<br />";

$ini = array(
	'global' => array(
		'language' => array(
			'post_settings' => 'Options:',
			'pm_not_found' => 'Private Message not found.'
		),
		'language_de' => array(
			'register_resend_desc' => 'Sollten Sie sich bereits registriert - aber den Bestätigungslink der Registrierungs-E-Mail noch nicht angeklickt haben, konnte Ihre Registrierung nicht vollständig abgeschlossen werden. Sie haben hier die Möglichkeit, Ihnen diese E-Mail erneut zuschicken zu lassen, ohne den Registrierungsvorgang wiederholen zu müssen. Dazu tragen Sie lediglich den bereits von Ihnen beantragten Benutzernamen ein - und die E-Mail wird Ihnen erneut an Ihre bereits bei der Registrierung angegebene E-Mail-Adresse übersandt.',
			'pm_not_found' => 'Die PN wurde nicht gefunden.'
		)
	)
);

$c = new manageconfig();
$codes = array();
$keys = array('language', 'language_de');
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
			$c->updateconfig($varname, str, $text);
		}
		$c->savedata();
	}
}

echo "- Language files updated.<br />";

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