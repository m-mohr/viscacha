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

$fields = array_map('strtolower', $db->list_fields($db->pre.'forums'));
if (!in_array('post_order', $fields)) {
	$db->query("ALTER TABLE `{$db->pre}forums` ADD `post_order` enum('-1','0','1') NOT NULL DEFAULT '-1'");
}
echo "- Database structure updated.<br />";

// Config
$c = new manageconfig();
$c->getdata('data/config.inc.php');
$c->updateconfig('version', str, VISCACHA_VERSION);
$c->updateconfig('post_order', int, 0);
$c->savedata();
echo "- Configuration updated.<br />";


// Languages
$ini = array (
  'admin/cms' =>
  array (
    'language_de' =>
    array (
      'admin_cms_nav_title_text' => 'Um Phrasen aus der benutzerdefinierten Sprachdatei für diesen Eintrag zu benutzen, ist der folgende Code vorgesehen: <code>lang->key</code>. Dabei ist <code>key</code> der Schlüssel (interne Name) der jeweiligen zu nutzenden Phrase. <a href="admin.php?action=language&amp;job=phrase_file&amp;file=Y3VzdG9tLmxuZy5waHA%3D" target="_blank">Öffne die Verwaltung für benutzerdefinierte Phrasen.</a><br />Die Titel der Dokumente können für die Verlinkung ebenfalls verwendet werden. Dazu einfach den Code <code>doc->ID</code> verwenden. <code>ID</code> ist dabei die ID des Dokuments, das Sie verlinken möchten. Dieser Code wird automatisch bei Auswahl über die Seite "Existierende Dokumente" erzeugt.',
    ),
    'language' =>
    array (
      'admin_cms_nav_title_text' => 'To use phrases from the custom language file for this entry simply use the follwoing code: <code>lang->key</code>. Therefor <code>key</code> is the internal name of the phrase you want to use. <a href="admin.php?action=language&amp;job=phrase_file&amp;file=Y3VzdG9tLmxuZy5waHA%3D" target="_blank">Open the custom language file manager.</a><br />The titles of the documents can also be used for the link text. Therefor you can use the code <code>doc->ID</code>, whereat <code>ID</code> is the ID of the linked document. This code will be set automatically after choosing one of the documents on the page "Existing Documents".',
    ),
  ),
  'admin/db' =>
  array (
    'language_de' =>
    array (
      'admin_db_entries_per_call' => 'Zu speichernde Datensätze pro Anfrage:',
    ),
    'language' =>
    array (
      'admin_db_entries_per_call' => 'Number of data sets saved per call:',
    ),
  ),
  'admin/forums' =>
  array (
    'language_de' =>
    array (
      'admin_forum_really_delete_data?' => NULL,
      'admin_forum_po_default' => 'Standardeinstellung',
      'admin_forum_po_desc' => 'Standardeinstellung des Forums: ',
      'admin_forum_po_new' => 'Neue Beiträge zuerst',
      'admin_forum_po_old' => 'Alte Beiträge zuerst',
      'admin_forum_po_title' => 'Beitragssortierung in Themen:',
      'admin_forum_really_delete_data' => 'Möchten Sie das Forum "{@forum->name}" mit allen enthaltenen Daten wirklich löschen?',
    ),
    'language' =>
    array (
      'admin_forum_really_delete_data?' => NULL,
      'admin_forum_po_default' => 'Default setting',
      'admin_forum_po_desc' => 'Forum default (global): ',
      'admin_forum_po_new' => 'New posts first',
      'admin_forum_po_old' => 'Old posts first',
      'admin_forum_po_title' => 'Post order for topics:',
      'admin_forum_really_delete_data' => 'Do you really want to delete the forum "{@forum->name}" with all data?',
    ),
  ),
  'admin/javascript' =>
  array (
    'language_de' =>
    array (
      'ajax2' => 'Keine (Der Benutzername ist zu kurz!)',
      'ajax3' => 'Keine',
    ),
  ),
  'admin/members' =>
  array (
    'language_de' =>
    array (
      'admin_member_nl_message_description' => 'In the message, you may use <code>{$user.id}</code>, <code>{$user.name}</code> or <code>{$user.mail}</code>.',
    ),
    'language' =>
    array (
      'admin_member_nl_message_description' => 'In the message, you may use <code>{$user.id}</code>, <code>{$user.name}</code> or <code>{$user.mail}</code>.',
    ),
  ),
  'admin/settings' =>
  array (
    'language_de' =>
    array (
      'admin_post_order_new' => 'Neue Beiträge zuerst',
      'admin_post_order_old' => 'Alte Beiträge zuerst',
      'admin_post_order_title' => 'Beitragssortierung in Themen:',
    ),
    'language' =>
    array (
      'admin_post_order_new' => 'New posts first',
      'admin_post_order_old' => 'Old posts first',
      'admin_post_order_title' => 'Post order for topics:',
    ),
  ),
  'global' =>
  array (
    'language_de' =>
    array (
      'why_register_desc' => 'Um sich einzuloggen, müssen Sie registriert sein. Sich zu registrieren kostet nur ein paar Sekunden, gibt dir aber erweiterte Möglichkeiten das Forum zu benutzen. Der Administrator kann auch zusätzliche Rechte an registrierte Benutzern vergeben. Bevor Sie sich einloggen stellen Sie bitte sicher, dass Sie mit unseren Nutzungsbedingungen vertraut sind und die Richtlinien befolgen. Stellen Sie bitte sicher, dass Sie die Foren-Regeln gelesen haben, bevor Sie das Forum benutzen.',
    ),
  ),
  'settings' =>
  array (
    'language_de' =>
    array (
      'compatible_version' => '0.8.1',
    ),
    'language' =>
    array (
      'compatible_version' => '0.8.1',
    ),
  )
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
		$filesystem->file_put_contents(
			"{$path}/mails/report_post.php",
			file_get_contents('install/package/update/language/'.$lng_settings['lang_code'].'/report_post.php')
		);
	}
}

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