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

// Hooks (ToDo: this sould be done in one step!)
$hooks = array_map('trim', file('admin/data/hooks.txt'));
removeHook($hooks, 'components_');
$hooks[] = '';
$hooks[] = 'admin/packages_admin.php';
$filesystem->file_put_contents('admin/data/hooks.txt', implode("\r\n", $hooks));

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
$c->updateconfig('spider_logvisits', int, 2);
$c->updateconfig('local_mode', int, 0);
$c->updateconfig('multiple_instant_notifications', int, 0);
$c->delete('always_send_js');
$c->savedata();

$c = new manageconfig();
$c->getdata('admin/data/config.inc.php', 'admconfig');
$c->updateconfig('checked_package_updates', int, 0);
$c->savedata();
echo "- Configuration updated.<br />";

// Old files
$filesystem->unlink('templates/lang2js.php');
$filesystem->unlink('classes/feedcreator/mbox.inc.php');
$feeds = file_get_contents('data/feedcreator.inc.php');
$feeds = preg_replace('~[\r\n]+MBOX\|mbox\.inc\.php\|MBox\|\d\|\d~i', '', $feeds);
$filesystem->file_put_contents('data/feedcreator.inc.php', $feeds);
$dir = dir('language');
while (false !== ($entry = $dir->read())) {
	$path = "{$dir->path}/{$entry}";
	if (is_dir($path) && is_id($entry)) {
		$filesystem->rmdirr("{$path}/modules/");
	}
}
$filesystem->file_put_contents('data/errlog_php.inc.php', '');
$filesystem->file_put_contents("data/errlog_{$db->system}.inc.php", '');
echo "- Old files deleted.<br />";

// Languages
$ini = array (
  'admin/bbcodes' =>
  array (
    'language_de' =>
    array (
      'admin_bbc_replacement_desc' => 'Dies ist der HTML-Code für die BB-Code-Ersetzung. Stellen Sie sicher, dass sie \'{param}\' (ohne Anführungszeichen) verwenden um den Text, der zwischen dem öffnenden und dem schließenden BB-Code-Tag steht, einzufügen. Wenn Sie dem BB-Code einen Parameter mitgeben (muss vorher aktiviert werden), so fügen Sie auch \'{option}\' (ohne Anführungszeichen) an der gewünschten Stelle ein. Zur Absicherung können Sie den Platzhaltern einen Typ zuweisen. Dafür hängen Sie hinter "param" bzw. "option" ein Doppelpunkt und danach ergänzen Sie den Typ. Folgende Typen sind möglich: hexcolor, int, float, hex, simpletext, url, email, alnum, alpha. Beispiel: {param:hexcolor}',
    ),
    'language' =>
    array (
      'admin_bbc_replacement_desc' => 'This is the HTML code for the BB code replacement. Make sure that you include \'{param}\' (without the quotes) to insert the text between the opening and closing BB code tags, and \'{option}\' for the parameter within the BB code tag. You can only use {option} if \'Use Option\' is set to yes. For a better security you can specify a type for each placeholder. To specify the placeholder simply add a colon and the type. The following types can be used: hexcolor, int, float, hex, simpletext, url, email, alnum, alpha. Example: {param:hexcolor}',
    ),
  ),
  'admin/cms' =>
  array (
    'language_de' =>
    array (
      'admin_cms_nav_package' => 'Paket:',
      'admin_wysiwyg_target' => 'Ziel-Fenster:',
    ),
    'language' =>
    array (
      'admin_cms_nav_package' => 'Package:',
      'admin_wysiwyg_target' => 'Target Window:',
    ),
  ),
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
  'admin/frames' =>
  array (
    'language_de' =>
    array (
      'admin_component_manager' => NULL,
      'admin_sqlerror_log' => 'Protokoll der Systemfehler',
    ),
    'language' =>
    array (
      'admin_component_manager' => NULL,
      'admin_sqlerror_log' => 'System Error Log',
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
      'admin_lang_component' => NULL,
      'admin_lang_components' => NULL,
      'admin_lang_component_without_langfile' => NULL,
      'admin_lang_edit_langfile_package_id' => NULL,
      'admin_lang_files' => NULL,
      'admin_lang_packages_phrases' => 'Pakete',
      'admin_lang_imported_successfully' => 'Sprachpaket wurde erfolgreich importiert.',
    ),
    'language' =>
    array (
      'admin_lang_component' => NULL,
      'admin_lang_components' => NULL,
      'admin_lang_component_without_langfile' => NULL,
      'admin_lang_edit_langfile_package_id' => NULL,
      'admin_lang_files' => NULL,
      'admin_lang_packages_phrases' => 'Packages',
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
      'admin_misc_dictionary' => NULL,
      'admin_misc_license_not_forun' => NULL,
      'admin_misc_save' => NULL,
      'admin_misc_license_not_found' => 'Lizenztext wurde leider nicht gefunden.',
      'admin_misc_mysql_version' => 'Datenbank-Version:',
    ),
    'language' =>
    array (
      'admin_misc_dictionary' => NULL,
      'admin_misc_license_not_forun' => NULL,
      'admin_misc_save' => NULL,
      'admin_misc_license_not_found' => 'License not found.',
      'admin_misc_mysql_version' => 'Database version:',
    ),
  ),
  'admin/packages' =>
  array (
    'language_de' =>
    array (
      'admin_packages_component_is_active' => NULL,
      'admin_packages_component_is_active_but_package_is_not_active' => NULL,
      'admin_packages_component_is_not_active' => NULL,
      'admin_packages_component_manager' => NULL,
      'admin_packages_com_activate' => NULL,
      'admin_packages_com_component_is_required' => NULL,
      'admin_packages_com_deactivate' => NULL,
      'admin_packages_com_delete_do_you_really_want_to_delete_this_component' => NULL,
      'admin_packages_com_delete_head_delete_component' => NULL,
      'admin_packages_com_th_component' => NULL,
      'admin_packages_com_th_package' => NULL,
      'admin_packages_conf_add_a_new_group_for_settings' => 'Gruppe für Einstellungen hinzufügen',
      'admin_packages_err_no_package_with_this_id' => NULL,
      'admin_packages_err_section_not_found' => NULL,
      'admin_packages_err_specified_component_is_required' => NULL,
      'admin_packages_err_specified_component_not_found' => NULL,
      'admin_packages_err_this_package_is_required_you_cannot_change_the_status' => NULL,
      'admin_packages_info_component' => NULL,
      'admin_packages_info_for_this_package_is_no_component_specified' => NULL,
      'admin_packages_info_required' => NULL,
      'admin_packages_ok_component_successfully_removed' => NULL,
      'admin_packages_plugins_add_file_for_code_text' => NULL,
      'admin_packages_plugins_delete_head_delete_package' => NULL,
      'admin_packages_plugins_edit_add_edit_phrases' => NULL,
      'admin_packages_plugins_edit_file_for_code_text' => NULL,
      'admin_packages_plugins_template_file_for_code_text' => NULL,
      'admin_packages_plugins_template_manage_templates_for_package' => ' Template-Verwaltung des Pakets ',
    ),
    'language' =>
    array (
      'admin_packages_component_is_active' => NULL,
      'admin_packages_component_is_active_but_package_is_not_active' => NULL,
      'admin_packages_component_is_not_active' => NULL,
      'admin_packages_component_manager' => NULL,
      'admin_packages_com_activate' => NULL,
      'admin_packages_com_component_is_required' => NULL,
      'admin_packages_com_deactivate' => NULL,
      'admin_packages_com_delete_do_you_really_want_to_delete_this_component' => NULL,
      'admin_packages_com_delete_head_delete_component' => NULL,
      'admin_packages_com_th_component' => NULL,
      'admin_packages_com_th_package' => NULL,
      'admin_packages_err_no_package_with_this_id' => NULL,
      'admin_packages_err_section_not_found' => NULL,
      'admin_packages_err_specified_component_is_required' => NULL,
      'admin_packages_err_specified_component_not_found' => NULL,
      'admin_packages_err_this_package_is_required_you_cannot_change_the_status' => NULL,
      'admin_packages_info_component' => NULL,
      'admin_packages_info_for_this_package_is_no_component_specified' => NULL,
      'admin_packages_ok_component_successfully_removed' => NULL,
      'admin_packages_plugins_add_file_for_code_text' => NULL,
      'admin_packages_plugins_delete_head_delete_package' => NULL,
      'admin_packages_plugins_edit_add_edit_phrases' => NULL,
      'admin_packages_plugins_edit_file_for_code_text' => NULL,
      'admin_packages_plugins_template_file_for_code_text' => NULL,
      'admin_packages_plugins_template_manage_templates_for_package' => 'Template Management for Package ',
    ),
  ),
  'admin/settings' =>
  array (
    'language_de' =>
    array (
      'admin_htaccess_error_doc_info' => 'Bei einem Server-Fehler (400, 403, 404, 500) wird die benutzerdefinierte Fehlerseite angezeigt. Beispiel: <a href="misc.php?action=error&id=404" target="_blank">Fehler 404</a>',
      'admin_ignor_words_less_chackters' => NULL,
      'admin_ignor_words_less_chackters_info' => NULL,
      'admin_mode_suggestions' => NULL,
      'admin_save_php_errors' => 'Speichere Fehler (PHP und MySQL) in Protokolldatei:',
      'admin_select_setting_group' => NULL,
      'admin_select_slq_erroe_log' => NULL,
      'admin_show_text_captcha' => NULL,
      'admin_show_text_captcha_info' => NULL,
      'admin_suggestions_fast_mode' => NULL,
      'admin_suggestions_normal_mode' => NULL,
      'admin_suggestions_slow_mode' => NULL,
      'admin_test_ftp_connection' => 'Daten speichern und FTP-Verbindung ggf. testen',
      'admin_timezone_maintain' => NULL,
      'admin_wordwrap_character_html_tag_long_words' => 'Wordwrap: Text (HTML erlaubt) der für die Trennung von zu langen Wörtern genutzt wird:',
      'admin_ftp_php_extension_error' => 'Viscacha benötigt mindestens fsockopen, die Sockets-Erweiterung oder die FTP-Erweiterung für die FTP-Funktionalität. Bitte aktiviere eines dieser Features oder deaktiviere FTP.',
      'admin_logvisits_count_logging' => 'Nur Anzahl der Besuche protokollieren',
      'admin_logvisits_full_logging' => 'Zeit und Anzahl der Besuche protokollieren',
      'admin_logvisits_no_logging' => 'Keine Protokollierung',
      'admin_multiple_instant_notifications' => 'Pro Antwort eine E-Mail-Benachrichtigungen schicken:',
      'admin_multiple_instant_notifications_info' => 'Bei der sofortigen E-Mail-Benachrichtigung (Abonnements) von Themen wird, wenn diese Option aktiviert ist, pro Antwort eine Benachrichtigung geschickt. Andernfalls wird nur bei der ersten Antwort seit dem letzten Besuch eine Benachrichtigung verschickt.',
      'admin_select_sys_error_log' => 'Protokoll der Systemfehler',
      'admin_topics_subscriptions' => 'Themen & Beiträge » Abonnements',
    ),
    'language' =>
    array (
      'admin_htaccess_error_doc_info' => 'On Server-Errors (400, 403, 404, 500) the custom Error-sites will be shown. Example: <a href="misc.php?action=error&id=404" target="_blank">Error 404</a>',
      'admin_ignor_words_less_chackters' => NULL,
      'admin_ignor_words_less_chackters_info' => NULL,
      'admin_mode_suggestions' => NULL,
      'admin_select_setting_group' => NULL,
      'admin_select_slq_erroe_log' => NULL,
      'admin_show_text_captcha' => NULL,
      'admin_show_text_captcha_info' => NULL,
      'admin_suggestions_fast_mode' => NULL,
      'admin_suggestions_normal_mode' => NULL,
      'admin_suggestions_slow_mode' => NULL,
      'admin_test_ftp_connection' => 'Save data and if so test FTP connection',
      'admin_timezone_maintain' => NULL,
      'admin_wordwrap_character_html_tag_long_words' => 'Wordwrap: Text (HTML allowed) which will be used for separation of too long words:',
      'admin_ftp_php_extension_error' => 'Viscacha needs at least fsockopen, sockets extension or ftp extension to work! Please enable one of this features or disable ftp.',
      'admin_logvisits_count_logging' => 'Log only number of visits',
      'admin_logvisits_full_logging' => 'Log time and number of visits',
      'admin_logvisits_no_logging' => 'No logging',
      'admin_multiple_instant_notifications' => 'Send one e-mail subscriptions per reply:',
      'admin_multiple_instant_notifications_info' => 'For instant e-mail notifications (subscriptions) of replies you can receive one notification per reply, if this option is turned on. In the other case, you only get one notification for the first reply since your last visit.',
      'admin_select_sys_error_log' => 'System Error Log',
      'admin_topics_subscriptions' => 'Topics & Posts » Subscriptions',
    ),
  ),
  'admin/slog' =>
  array (
    'language_de' =>
    array (
      'admin_slog_sql_error_logfile' => 'Protokoll der Systemfehler',
      'admin_slog_backtrace' => 'Laufzeitinformationen',
      'admin_slog_day' => 'Tag',
      'admin_slog_error_num' => 'Nr.',
      'admin_slog_month' => 'Monat',
      'admin_slog_week' => 'Woche',
    ),
    'language' =>
    array (
      'admin_slog_sql_error_logfile' => 'System Error Logfile',
      'admin_slog_backtrace' => 'Runtime information',
      'admin_slog_day' => 'Day',
      'admin_slog_error_num' => 'No.',
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
      'box_newtopic' => NULL,
      'editprofile_signature_longdesc' => NULL,
      'editprofile_standard' => NULL,
      'forum_options_search_reset' => NULL,
      'geshi_hlcode_title' => NULL,
      'geshi_hlcode_txtdownload' => NULL,
      'htaccess_errdesc_401' => NULL,
      'htaccess_error_401' => NULL,
      'im_msgtitle' => NULL,
      'index_headline' => NULL,
      'no_board_given' => NULL,
      'pm_index_dir' => NULL,
      'post_sent' => NULL,
      'print_title_page' => NULL,
      'section_closed' => NULL,
      'section_not_available' => NULL,
      'thumb_error' => 'Konnte Miniaturansicht nicht erstellen',
      'timestamps_gmt_diff' => 'Alle Zeitangaben in {%my->timezone_str}.',
      'timezone_current' => NULL,
      'timezone_desc' => 'Aktuelle Uhrzeit: {%my->current_time}.',
      'timezone_summer' => NULL,
      'x_article' => NULL,
      'benchmark_bbc_smileys' => 'BB-Codes + Smileys:',
      'benchmark_failed' => 'fehlerhaft',
      'benchmark_gzip' => 'GZIP:',
      'benchmark_load_time' => 'Generierungszeit:',
      'benchmark_queries' => 'DB-Abfragen:',
      'benchmark_queries_time' => 'Zeit für DB-Abfragen:',
      'benchmark_sec' => 'Sek.',
      'benchmark_smileys' => 'Nur Smileys:',
      'benchmark_templates' => 'Templates:',
      'benchmark_templates_time' => 'Zeit für Templates:',
      'gmt' => 'GMT',
      'img_captcha_session_expired_error' => 'Sitzung beendet<br>Aktualisiere die Seite',
      'page_gzip_off' => 'Aus',
      'page_gzip_on' => 'An<br />Komprimierungsrate: ',
      'post_info_postcount' => 'Beiträge: ',
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
      'box_newtopic' => NULL,
      'editprofile_signature_longdesc' => NULL,
      'editprofile_standard' => NULL,
      'forum_options_search_reset' => NULL,
      'geshi_hlcode_title' => NULL,
      'geshi_hlcode_txtdownload' => NULL,
      'htaccess_errdesc_401' => NULL,
      'htaccess_error_401' => NULL,
      'im_msgtitle' => NULL,
      'index_headline' => NULL,
      'no_board_given' => NULL,
      'pm_index_dir' => NULL,
      'post_sent' => NULL,
      'print_title_page' => NULL,
      'register_veriword' => 'Please enter the chars in the image. This should help to avoid spam.',
      'section_closed' => NULL,
      'section_not_available' => NULL,
      'timestamps_gmt_diff' => 'All times are {%my->timezone_str}.',
      'timezone_current' => NULL,
      'timezone_desc' => 'Current time: {%my->current_time}.',
      'timezone_summer' => NULL,
      'x_article' => NULL,
      'benchmark_bbc_smileys' => 'BB-Codes + Smileys:',
      'benchmark_failed' => 'failed',
      'benchmark_gzip' => 'GZIP:',
      'benchmark_load_time' => 'Load Time:',
      'benchmark_queries' => 'Queries:',
      'benchmark_queries_time' => 'Time for Queries:',
      'benchmark_sec' => 'sec.',
      'benchmark_smileys' => 'Only Smileys:',
      'benchmark_templates' => 'Templates:',
      'benchmark_templates_time' => 'Templates Time:',
      'gmt' => 'GMT',
      'img_captcha_session_expired_error' => 'Session expired<br>Refresh the Page',
      'page_gzip_off' => 'Off',
      'page_gzip_on' => 'On<br />Compression Rate: ',
      'post_info_postcount' => 'Posts: ',
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
  'wwo' =>
  array (
    'language' =>
    array (
      'wwo_showforum' => 'is viewing the following board: <a href="showforum.php?id={$id}">{$title}</a>',
      'wwo_showforum_fallback' => 'is viewing a board',
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
			$css .= "\r\ntt {\r\n\tfont-family: 'Courier New', monospace;\r\n}";
			$filesystem->file_put_contents("{$path}/standard.css", $css);
		}
	}
}
echo "- Stylesheets updated.<br />";

// Set incompatible packages inactive
$db->query("UPDATE {$db->pre}packages SET active = '0' WHERE internal = 'viscacha_quick_reply'");
$result = $db->query("SELECT package FROM {$db->pre}component");
while ($row = $db->fetch_assoc($result)) {
	$db->query("UPDATE {$db->pre}packages SET active = '0' WHERE id = '{$row['package']}'");
}
setPackagesInactive();
echo "- Incompatible Packages set as 'inactive'.<br />";

// MySQL
$file = 'install/package/'.$package.'/db/db_changes.sql';
$sql = file_get_contents($file);
$sql = str_ireplace('{:=DBPREFIX=:}', $db->prefix(), $sql);
$db->multi_query($sql);
echo "- Database tables updated.<br />";

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