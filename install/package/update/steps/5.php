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
if (!class_exists('DB')) {
	require_once('install/classes/database/'.$config['dbsystem'].'.inc.php');
	$db = new DB($config['host'], $config['dbuser'], $config['dbpw'], $config['database'], $config['dbprefix']);
	$db->setPersistence($config['pconnect']);
}

echo "- FTP class loaded, Database connection started.<br />";

// Hooks
$hooks = file_get_contents('admin/data/hooks.txt');
$add_com = array('components.php');
$add_acom = array('admin/packages_admin.php');
$result = $db->query("SELECT internal FROM {$db->pre}packages");
while ($row = $db->fetch_assoc($result)) {
	$internal = preg_quote($row['internal'], "~");
	if (!preg_match("~^-component_{$internal}$~im", $hooks)) {
		$add_com[] = "-component_{$row['internal']}";
	}
	if (!preg_match("~^-admin_component_{$internal}$~im", $hooks)) {
		$add_acom[] = "-admin_component_{$row['internal']}";
	}
}
if (count($add_com) > 1) {
	$hooks = preg_replace("~^components.php$~im", implode("\r\n", $add_com), $hooks);
}
if (count($add_acom) > 1) {
	$hooks = preg_replace("~^admin/packages_admin.php$~im", implode("\r\n", $add_acom), $hooks);
}
$filesystem->file_put_contents('admin/data/hooks.txt', $hooks);
echo "- Hooks updated.<br />";

// Config
$c = new manageconfig();
$c->getdata('data/config.inc.php');
$c->updateconfig('version', str, VISCACHA_VERSION);
$c->updateconfig('local_mode', int, 0);
$c->updateconfig('multiple_instant_notifications', int, 0);
$c->savedata();

$c = new manageconfig();
$c->getdata('admin/data/config.inc.php', 'admconfig');
$c->updateconfig('checked_package_updates', int, 0);
$c->savedata();
echo "- Configuration updated.<br />";

// Languages
$ini = array (
  'admin/forums' =>
  array (
    'language_de' =>
    array (
      'admin_forum_until' => 'bis ',
    ),
    'language' =>
    array (
      'admin_forum_until' => 'until ',
    ),
  ),
  'admin/global' =>
  array (
    'language_de' =>
    array (
      'gmt' => 'GMT',
    ),
    'language' =>
    array (
      'gmt' => 'GMT',
    ),
  ),
  'admin/language' =>
  array (
    'language_de' =>
    array (
      'admin_lang_imported_successfully' => 'Sprachpaket wurde erfolgreich importiert.',
    ),
    'language' =>
    array (
      'admin_lang_imported_successfully' => 'Languagepack imported successfully.',
    ),
  ),
  'admin/members' =>
  array (
    'language_de' =>
    array (
      'admin_member_keep_time_zone' => NULL,
    ),
    'language' =>
    array (
      'admin_member_keep_time_zone' => NULL,
    ),
  ),
  'admin/misc' =>
  array (
    'language_de' =>
    array (
      'admin_misc_license_not_forun' => NULL,
      'admin_misc_license_not_found' => 'Lizenztext wurde leider nicht gefunden.',
      'admin_misc_mysql_version' => 'Datenbank-Version:',
    ),
    'language' =>
    array (
      'admin_misc_license_not_forun' => NULL,
      'admin_misc_license_not_found' => 'License not found.',
      'admin_misc_mysql_version' => 'Database version:',
    ),
  ),
  'admin/packages' =>
  array (
    'language_de' =>
    array (
      'admin_packages_plugins_template_manage_templates_for_package' => ' Template-Verwaltung des Pakets ',
    ),
    'language' =>
    array (
      'admin_packages_plugins_template_manage_templates_for_package' => 'Template Management for Package ',
    ),
  ),
  'admin/settings' =>
  array (
    'language_de' =>
    array (
      'admin_htaccess_error_doc_info' => 'Bei einem Server-Fehler (400, 403, 404, 500) wird die benutzerdefinierte Fehlerseite angezeigt. Beispiel: <a href="misc.php?action=error&id=404" target="_blank">Fehler 404</a>',
      'admin_test_ftp_connection' => 'Daten speichern und FTP-Verbindung ggf. testen',
      'admin_timezone_maintain' => NULL,
      'admin_wordwrap_character_html_tag_long_words' => 'Wordwrap: Text (HTML erlaubt) der für die Trennung von zu langen Wörtern genutzt wird:',
      'admin_ftp_php_extension_error' => 'Viscacha benötigt mindestens fsockopen, die Sockets-Erweiterung oder die FTP-Erweiterung für die FTP-Funktionalität. Bitte aktiviere eines dieser Features oder deaktiviere FTP.',
      'admin_multiple_instant_notifications' => 'Pro Antwort eine E-Mail-Benachrichtigungen schicken:',
      'admin_multiple_instant_notifications_info' => 'Bei der sofortigen E-Mail-Benachrichtigung (Abonnements) von Themen wird, wenn diese Option aktiviert ist, pro Antwort eine Benachrichtigung geschickt. Andernfalls wird nur bei der ersten Antwort seit dem letzten Besuch eine Benachrichtigung verschickt.',
      'admin_topics_subscriptions' => 'Themen & Beiträge » Abonnements',
    ),
    'language' =>
    array (
      'admin_htaccess_error_doc_info' => 'On Server-Errors (400, 403, 404, 500) the custom Error-sites will be shown. Example: <a href="misc.php?action=error&id=404" target="_blank">Error 404</a>',
      'admin_test_ftp_connection' => 'Save data and if so test FTP connection',
      'admin_timezone_maintain' => NULL,
      'admin_wordwrap_character_html_tag_long_words' => 'Wordwrap: Text (HTML allowed) which will be used for separation of too long words:',
      'admin_ftp_php_extension_error' => 'Viscacha needs at least fsockopen, sockets extension or ftp extension to work! Please enable one of this features or disable ftp.',
      'admin_multiple_instant_notifications' => 'Send one e-mail subscriptions per reply:',
      'admin_multiple_instant_notifications_info' => 'For instant e-mail notifications (subscriptions) of replies you can receive one notification per reply, if this option is turned on. In the other case, you only get one notification for the first reply since your last visit.',
      'admin_topics_subscriptions' => 'Topics & Posts » Subscriptions',
    ),
  ),
  'admin/slog' =>
  array (
    'language_de' =>
    array (
      'admin_slog_day' => 'Tag',
      'admin_slog_month' => 'Monat',
      'admin_slog_week' => 'Woche',
    ),
    'language' =>
    array (
      'admin_slog_day' => 'Day',
      'admin_slog_month' => 'Month',
      'admin_slog_week' => 'Week',
    ),
  ),
  'bbcodes' =>
  array (
    'language_de' =>
    array (
      'bb_edit_author' => 'Nachträgliche Anmerkung des Autors:',
      'bb_edit_mod' => 'Nachträgliche Anmerkung von',
      'bb_hidden_content' => 'Versteckter Inhalt:',
      'bb_offtopic' => 'Off-Topic:',
      'bb_quote' => 'Zitat:',
      'bb_quote_by' => 'Zitat von',
      'bb_sourcecode' => 'Quelltext:',
      'geshi_hlcode_title' => '{$lang_name}-Quelltext:',
      'geshi_hlcode_txtdownload' => 'Download',
    ),
    'language' =>
    array (
      'bb_edit_author' => 'Additional note by the author:',
      'bb_edit_mod' => 'Additional note by',
      'bb_hidden_content' => 'Hidden Content:',
      'bb_offtopic' => 'Off Topic:',
      'bb_quote' => 'Quote:',
      'bb_quote_by' => 'Quote by',
      'bb_sourcecode' => 'Source Code:',
      'geshi_hlcode_title' => 'Source code ({$lang_name}):',
      'geshi_hlcode_txtdownload' => 'Download',
    ),
  ),
  'global' =>
  array (
    'language_de' =>
    array (
      'bb_edit_author' => NULL,
      'bb_edit_mod' => NULL,
      'bb_hidden_content' => NULL,
      'bb_offtopic' => NULL,
      'bb_quote' => NULL,
      'bb_quote_by' => NULL,
      'bb_sourcecode' => NULL,
      'geshi_hlcode_title' => NULL,
      'geshi_hlcode_txtdownload' => NULL,
      'htaccess_errdesc_401' => NULL,
      'htaccess_error_401' => NULL,
      'timestamps_gmt_diff' => 'Alle Zeitangaben in {%my->timezone_str}.',
      'timezone_current' => NULL,
      'timezone_desc' => 'Aktuelle Uhrzeit: {%my->current_time}.',
      'timezone_summer' => NULL,
      'gmt' => 'GMT',
      'showtopic_options_fav_remove' => 'Aus den Favoriten entfernen',
      'vote_reply_too_long' => 'Die Antwortmöglichkeit {$i} ist zu lang.',
    ),
    'language' =>
    array (
      'bb_edit_author' => NULL,
      'bb_edit_mod' => NULL,
      'bb_hidden_content' => NULL,
      'bb_offtopic' => NULL,
      'bb_quote' => NULL,
      'bb_quote_by' => NULL,
      'bb_sourcecode' => NULL,
      'geshi_hlcode_title' => NULL,
      'geshi_hlcode_txtdownload' => NULL,
      'htaccess_errdesc_401' => NULL,
      'htaccess_error_401' => NULL,
      'timestamps_gmt_diff' => 'All times are {%my->timezone_str}.',
      'timezone_current' => NULL,
      'timezone_desc' => 'Current time: {%my->current_time}.',
      'timezone_summer' => NULL,
      'gmt' => 'GMT',
      'showtopic_options_fav_remove' => 'Remove from favorites',
      'vote_reply_too_long' => 'Option {$i} of your vote is too long.',
    ),
  ),
  'settings' =>
  array (
    'language_de' =>
    array (
      'compatible_version' => '0.8',
    ),
    'language' =>
    array (
      'compatible_version' => '0.8',
    ),
  ),
);
updateLanguageFiles($ini);

$dir = dir('language');
while (false !== ($entry = $dir->read())) {
	$path = "{$dir->path}/{$entry}";
	if (is_dir($path) && is_id($entry)) {
		$lng_settings = loadSettingArray($path);
		if ($lng_settings['lang_code'] != 'de') {
			$lng_settings['lang_code'] = 'en';
		}
		$filesystem->file_put_contents(
			"{$path}/mails/digest_d.php",
			file_get_contents('install/package/update/language/'.$lng_settings['lang_code'].'/digest_d.php')
		);
		$filesystem->file_put_contents(
			"{$path}/mails/digest_w.php",
			file_get_contents('install/package/update/language/'.$lng_settings['lang_code'].'/digest_w.php')
		);
		$filesystem->file_put_contents(
			"{$path}/mails/digest_s.php",
			file_get_contents('install/package/update/language/'.$lng_settings['lang_code'].'/digest_s.php')
		);
	}
}

echo "- Language files updated.<br />";

// Stylesheets
$dir = dir('designs');
while (false !== ($entry = $dir->read())) {
	$path = "{$dir->path}/{$entry}";
	if (is_dir($path) && is_id($entry)) {
		if (file_exists("{$path}/standard.css")) {
			$css = file_get_contents("{$path}/standard.css");
			$css = preg_replace("~\.popup\s+\{~i", ".popup {\r\n\toverflow: hidden;", $css);
			$filesystem->file_put_contents("{$path}/standard.css", $css);
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