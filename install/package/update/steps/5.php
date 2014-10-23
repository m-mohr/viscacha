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
  ),
  'admin/frames' => 
  array (
    'language_de' => 
    array (
      'admin_scheduler_log' => 'Protokoll der geplanten Aufgaben',
    ),
  ),
  'classes' => 
  array (
    'language_de' => 
    array (
      'mailer_encoding' => 'Unbekanntes Encoding-Format: ',
      'mailer_execute' => 'Konnte folgenden Befehl nicht ausf&uuml;hren: ',
      'mailer_file_access' => 'Zugriff auf folgende Datei fehlgeschlagen: ',
      'mailer_file_open' => 'Datei Fehler: Konnte folgende Datei nicht &ouml;ffnen: ',
      'mailer_from_failed' => 'Die folgende Absenderadresse ist nicht korrekt: ',
      'mailer_recipients_failed' => 'SMTP Fehler: Die folgenden Empf&auml;nger sind nicht korrekt: ',
      'mailer_signing' => 'Fehler beim Signieren: ',
      'mailer_empty_message' => 'E-Mail Inhalt ist leer.',
      'mailer_invalid_address' => 'E-Mail wird nicht gesendet, die Adresse ist ung&uuml;ltig.',
      'mailer_smtp_connect_failed' => 'Verbindung zu SMTP Server fehlgeschlagen.',
      'mailer_smtp_error' => 'Fehler vom SMTP Server: ',
      'mailer_variable_set' => 'Kann Variable nicht setzen oder zur&uuml;cksetzen: ',
    ),
    'language' => 
    array (
      'mailer_authenticate' => 'SMTP Error: Could not authenticate.',
      'mailer_connect_host' => 'SMTP Error: Could not connect to SMTP host.',
      'mailer_data_not_accepted' => 'SMTP Error: Data not accepted.',
      'mailer_encoding' => 'Unknown encoding: ',
      'mailer_execute' => 'Could not execute: ',
      'mailer_file_access' => 'Could not access file: ',
      'mailer_file_open' => 'File Error: Could not open file: ',
      'mailer_from_failed' => 'The following From address failed: ',
      'mailer_instantiate' => 'Could not instantiate mail function.',
      'mailer_mailer_not_supported' => ' mailer is not supported.',
      'mailer_provide_address' => 'You must provide at least one recipient email address.',
      'mailer_recipients_failed' => 'SMTP Error: The following recipients failed: ',
      'mailer_empty_message' => 'Message body empty',
      'mailer_invalid_address' => 'Invalid address',
      'mailer_smtp_connect_failed' => 'SMTP Connect() failed.',
      'mailer_smtp_error' => 'SMTP server error: ',
      'mailer_variable_set' => 'Cannot set or reset variable: ',
    ),
  ),
  'global' => 
  array (
    'language_de' => 
    array (
      'digest_d' => 'Tägliche E-Mail-Benachrichtigung',
      'digest_none' => 'Keine Benachrichtigung',
      'img_captcha_session_expired_error' => 'Seite aktualisieren',
      'digest_f' => 'Nur Favorit',
    ),
    'language' => 
    array (
      'digest_none' => 'No notification',
      'img_captcha_session_expired_error' => 'Refresh page',
      'digest_f' => 'Favorite only',
    ),
  ),
);
updateLanguageFiles($ini);
echo "- Language files updated.<br />";

$newCss = file_get_contents('temp/standard.css');
$dir = dir('designs');
while (false !== ($entry = $dir->read())) {
	if (is_id($entry)) {
		$path = "{$dir->path}/{$entry}/standard.css";
		$css = file_get_contents($path);
		if (!empty($css)) {
			$css .= $newCss;
			if (!$filesystem->file_put_contents($path, $css)) {
				$css = null;
			}
		}
		if (empty($css)) {
			echo "<br />!!! <strong>Warning:</strong> Updating {$path} failed. Plase add the following CSS code to your main css file in designs/{$entry}:<br /><code>";
			echo htmlentities($newCss);
			echo "</code><br /><br />";
		}
	}
}
echo "- Stylesheets updated.<br />";

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