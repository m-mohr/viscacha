<div class="bbody">
<?php
echo "<strong>Starting Update:</strong><br />";

require('data/config.inc.php');
require_once('install/classes/class.phpconfig.php');

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
$hooks = array_map('trim', file('admin/data/hooks.txt'));
removeHook($hooks, 'editprofile_copy_');
removeHook($hooks, 'popup_hlcode_');
removeHook($hooks, 'popup_code_');
insertHookAfter($hooks, 'showtopic_entry_added', 'showtopic_attachments_prepared');
$filesystem->file_put_contents('admin/data/hooks.txt', implode("\r\n", $hooks));
echo "- Hooks updated.<br />";

// Config
$c = new manageconfig();
$c->getdata('data/config.inc.php');
$c->updateconfig('version', str, VISCACHA_VERSION);
$c->updateconfig('doclang', int, $config['langdir']);
$c->updateconfig('error_reporting', str, 'E_ALL');
$c->updateconfig('login_attempts_blocktime', int, 60);
$c->updateconfig('login_attempts_max', int, 5);
$c->updateconfig('login_attempts_time', int, 60);
$c->delete('check_filesystem');
$c->delete('enable_jabber');
$c->delete('jabber_server');
$c->delete('jabber_user');
$c->delete('jabber_pass');
$c->delete('pspell');
$c->delete('smileysperrow');
$c->delete('spellcheck');
$c->delete('spellcheck_ignore');
$c->delete('spellcheck_mode');
$c->delete('vcard_dl');
$c->delete('vcard_dl_guests');
$c->savedata();

$c = new manageconfig();
$c->getdata('admin/data/config.inc.php', 'admconfig');
$c->updateconfig('checked_package_updates', int, 0);
$c->savedata();
echo "- Configuration updated.<br />";

// MySQL & Documents
$pre = $db->prefix();

$result = $db->query("SELECT * FROM {$pre}documents ORDER BY id");
$documents = array();
while($row = $db->fetch_assoc($result)) {
	$documents[$row['id']] = $row;
}

// Start: Update Tables
$file = 'install/package/update/db/db_changes.sql';
//$file = 'package/'.$package.'/db/db_changes.sql';
$sql = file_get_contents($file);
$sql = str_ireplace('{:=DBPREFIX=:}', $pre, $sql);
$db->multi_query($sql);
// End: Update Tables

foreach ($documents as $doc) {
	if (!empty($doc['content'])) {
		$content = $doc['content'];
	}
	elseif (!empty($doc['file'])) {
		if ($doc['type'] == 1) { // Frame Page!
			$content = $doc['file'];
		}
		else {
			$base = realpath($doc['file']);
			if (!empty($base) && file_exists($base)) {
				$content = file_get_contents($base);
			}
		}
	}
	if (empty($content)) {
		$content = '';
	}
	$db->query("
		INSERT INTO `v_documents` (
			`id` , `author` , `date` , `update` , `type` , `groups` , `icomment`
		) VALUES (
			{$doc['id']} , '{$doc['author']}', '{$doc['date']}', '{$doc['update']}', '{$doc['type']}', '{$doc['groups']}', ''
		)
	");
	$content = $db->escape_string($content);
	$doc['title'] = $db->escape_string($doc['title']);
	$doc['author'] = $db->escape_string($doc['author']);
	$db->query("
		INSERT INTO `v_documents_content` ( `did` , `lid` , `title` , `content` , `active` )
		VALUES (
		'{$doc['id']}', '{$config['langdir']}', '{$doc['title']}', '{$content}', '{$doc['active']}'
		)
	");

}

echo "- Database tables updated and documents converted.<br />";

// Update crontab file
$cron = file_get_contents('data/cron/crontab.inc.php');
if (strpos($cron, 'exportBoardStats.php') === false) {
	$cron = trim($cron);
	$cron .= "\r\n0\t5\t*\t*\t*\texportBoardStats.php\t#Daily: Export forum statistics to an ini-file (optional)";
	$filesystem->file_put_contents('data/cron/crontab.inc.php', $cron);
	echo "- Crontab updated.<br />";
}

// Old files
$filesystem->unlink('admin/html/menu.js');
$filesystem->unlink('admin/html/editor.js');
$filesystem->unlink('classes/function.jabber.php');
$filesystem->unlink('classes/class.vCard.inc.php');
$filesystem->unlink('classes/class.jabber.php');
$filesystem->unlink('classes/geshi/asp.php');
$filesystem->unlink('classes/geshi/c.php');
$filesystem->unlink('classes/geshi/dos.php');
$filesystem->unlink('classes/geshi/eiffel.php');
$filesystem->unlink('classes/geshi/fortran.php');
$filesystem->unlink('classes/geshi/oobas.php');
$filesystem->unlink('classes/geshi/pascal.php');
$filesystem->unlink('classes/geshi/qbasic.php');
$filesystem->unlink('classes/geshi/rails.php');
$filesystem->unlink('classes/geshi/reg.php');
$filesystem->unlink('classes/geshi/visualfoxpro.php');
$filesystem->unlink('classes/geshi/winbatch.php');
$filesystem->unlink('classes/mail/extended.phpmailer.php');
$filesystem->unlink('data/g_flood.php');
$filesystem->unlink('data/m_flood.php');
$filesystem->unlink('templates/editor/images/bar.gif');
$filesystem->unlink('templates/editor/images/blackdot.gif');
$filesystem->unlink('templates/editor/images/centre.gif');
$filesystem->unlink('templates/editor/images/code.gif');
$filesystem->unlink('templates/editor/images/copy.gif');
$filesystem->unlink('templates/editor/images/cut.gif');
$filesystem->unlink('templates/editor/images/design.gif');
$filesystem->unlink('templates/editor/images/hyperlink.gif');
$filesystem->unlink('templates/editor/images/image.gif');
$filesystem->unlink('templates/editor/images/indent.gif');
$filesystem->unlink('templates/editor/images/insert_table.gif');
$filesystem->unlink('templates/editor/images/justifyfull.gif');
$filesystem->unlink('templates/editor/images/left_just.gif');
$filesystem->unlink('templates/editor/images/list.gif');
$filesystem->unlink('templates/editor/images/numbered_list.gif');
$filesystem->unlink('templates/editor/images/outdent.gif');
$filesystem->unlink('templates/editor/images/paste.gif');
$filesystem->unlink('templates/editor/images/pastetext.gif');
$filesystem->unlink('templates/editor/images/pasteword.gif');
$filesystem->unlink('templates/editor/images/preview.gif');
$filesystem->unlink('templates/editor/images/redo.gif');
$filesystem->unlink('templates/editor/images/replace.gif');
$filesystem->unlink('templates/editor/images/right_just.gif');
$filesystem->unlink('templates/editor/images/selectall.gif');
$filesystem->unlink('templates/editor/images/special_char.gif');
$filesystem->unlink('templates/editor/images/spellcheck.gif');
$filesystem->unlink('templates/editor/images/textcolor.gif');
$filesystem->unlink('templates/editor/images/undo.gif');
$filesystem->unlink('templates/editor/images/unformat.gif');
$filesystem->unlink('templates/editor/images/word_count.gif');
$filesystem->unlink('templates/editor/blank.htm');
$filesystem->unlink('templates/editor/html2xhtml.js');
$filesystem->unlink('templates/editor/insert_char.htm');
$filesystem->unlink('templates/editor/insert_img.htm');
$filesystem->unlink('templates/editor/insert_link.htm');
$filesystem->unlink('templates/editor/insert_table.htm');
$filesystem->unlink('templates/editor/palette.htm');
$filesystem->unlink('templates/editor/paste_text.htm');
$filesystem->unlink('templates/editor/paste_word.htm');
$filesystem->unlink('templates/editor/replace.htm');
$filesystem->unlink('templates/editor/richtext.js');
$filesystem->unlink('templates/editor/rte.css');
$filesystem->unlink('templates/controlWindow.js');
$filesystem->unlink('templates/editor.js');
$filesystem->unlink('templates/menu.js');
$filesystem->unlink('templates/spellChecker.js');
$filesystem->unlink('templates/wordWindow.js');
$filesystem->rmdirr('templates/editor/lang/');
$filesystem->rmdirr('classes/spellchecker/');
$filesystem->rmdirr('docs/');
$dir = dir('images');
while (false !== ($entry = $dir->read())) {
	$path = "{$dir->path}/{$entry}";
	if (is_dir($path) && is_id($entry)) {
		$filesystem->rmdirr("{$path}/bbcodes/");
		$filesystem->unlink("{$path}/copy.gif");
	}
}
$dir = dir('language');
while (false !== ($entry = $dir->read())) {
	$path = "{$dir->path}/{$entry}";
	if (is_dir($path) && is_id($entry)) {
		$filesystem->unlink("{$path}/texts/notice.php");
	}
}
$dir = dir('templates');
while (false !== ($entry = $dir->read())) {
	$path = "{$dir->path}/{$entry}";
	if (is_dir($path) && is_id($entry)) {
		$filesystem->unlink("{$path}/main/pages_current.html");
		$filesystem->unlink("{$path}/main/pages_current_small.html");
		$filesystem->unlink("{$path}/main/smileys.html");
		$filesystem->unlink("{$path}/members/index_letter.html");
		$filesystem->unlink("{$path}/popup/code.html");
		$filesystem->unlink("{$path}/popup/hlcode.html");
		$filesystem->rmdirr("{$path}/spellcheck/");
	}
}
echo "- Old files deleted.<br />";

$filesystem->mkdir('uploads/images/', 0777);
$filesystem->file_put_contents('uploads/images/index.htm', '', true);
echo "- Updated filesystem.<br />";

// Languages
$ini = array (
  'admin/bbcodes' =>
  array (
    'language_de' =>
    array (
      'admin_bbc_button_image_desc' => 'Optional - Wenn Sie diesen BB-Code als klickbaren Button angezeigt haben möchten, so geben Sie hier die Adresse zu einem 20 x 20 Pixel großen Bild an. Dieser Button wird dann zum Einfügen des BB-Codes benutzt. Sie können entweder eine absolute URL (http://...) benutzen oder eine relative URL, ausgehend vom Verzeichnis {@config->furl}/templates/editor/images/',
    ),
    'language' =>
    array (
      'admin_bbc_button_image_desc' => 'Optional - If you would like this bbcode to appear as a clickable button on the message editor toolbar, enter the adress of an image 20 x 20 pixels in size that will act as the button to insert this bbcode. You can use either an absolute URL (http://...) or a relative URL, basing on the directory {@config->furl}/templates/editor/images/.',
    ),
  ),
  'admin/cms' =>
  array (
    'language_de' =>
    array (
      'admin_cms_big_font' => NULL,
      'admin_cms_center' => NULL,
      'admin_cms_doc_file' => NULL,
      'admin_cms_doc_id' => 'Hinweis',
      'admin_cms_extended_font' => NULL,
      'admin_cms_heading_1' => NULL,
      'admin_cms_heading_2' => NULL,
      'admin_cms_heading_3' => NULL,
      'admin_cms_head_alignment' => NULL,
      'admin_cms_head_choose_alignment' => NULL,
      'admin_cms_head_choose_color' => NULL,
      'admin_cms_head_choose_heading' => NULL,
      'admin_cms_head_choose_size' => NULL,
      'admin_cms_head_color' => NULL,
      'admin_cms_head_heading' => NULL,
      'admin_cms_head_help' => NULL,
      'admin_cms_head_size' => NULL,
      'admin_cms_head_smileys' => NULL,
      'admin_cms_if_path_is_given' => NULL,
      'admin_cms_invalid_id_given' => 'Ungültige ID angegeben',
      'admin_cms_justify' => NULL,
      'admin_cms_left' => NULL,
      'admin_cms_more_smileys' => NULL,
      'admin_cms_nav_title_text' => 'Um Phrasen aus der benutzerdefinierten Sprachdatei für diesen Eintrag zu benutzen, ist der folgende Code vorgesehen: <code>lang->key</code>. <code>key</code> ist der Schlüssel/interne Name der Phrase die Sie benutzen möchten. Um die benutzerdefinierten Phrasen zu verwalten, klicken Sie bitte <a href="admin.php?action=language&amp;job=phrase_file&amp;file=Y3VzdG9tLmxuZy5waHA%3D" target="_blank">hier</a>. Die Titel der Dokumente können für die Verlinkung ebenfalls verwendet werden. Dazu einfach den Code <code>doc->ID</code> verwenden. <code>ID</code> ist dabei die ID des Dokuments, das Sie verlinken möchten. Dieser Code wird bei Auswahl automatisch über die Seite "Existierende Dokumente" erzeugt.',
      'admin_cms_right' => NULL,
      'admin_cms_small_font' => NULL,
      'admin_cms_tag_boldface' => NULL,
      'admin_cms_tag_definition' => NULL,
      'admin_cms_tag_definition_please_enter_definition' => NULL,
      'admin_cms_tag_definition_please_enter_word' => NULL,
      'admin_cms_tag_edited_passage' => NULL,
      'admin_cms_tag_email' => NULL,
      'admin_cms_tag_horizontal_ruler' => NULL,
      'admin_cms_tag_image' => NULL,
      'admin_cms_tag_italic' => NULL,
      'admin_cms_tag_off_topic' => NULL,
      'admin_cms_tag_ordered_list' => NULL,
      'admin_cms_tag_quote' => NULL,
      'admin_cms_tag_source_code' => NULL,
      'admin_cms_tag_subscript' => NULL,
      'admin_cms_tag_superscript' => NULL,
      'admin_cms_tag_typewriter' => NULL,
      'admin_cms_tag_underline' => NULL,
      'admin_cms_tag_unordered_list' => NULL,
      'admin_cms_tag_url' => NULL,
      'admin_cms_tag_url_please_provide_text' => NULL,
      'admin_cms_tag_url_please_provide_url' => NULL,
      'admin_cms_doc_av_languages' => 'Sprachen',
      'admin_cms_doc_checkboxes_help' => '<strong>Hinweis:</strong> Bei jeder Sprache die gespeichert werden soll, muss die Checkbox vor dem Sprachennamen mit einem Häkchen versehen sein, ansonsten werden die Daten nicht gespeichert und gehen dann unwiderruflich verloren!',
      'admin_cms_doc_click_for_adding_lang' => 'Klicken Sie hier, um für diese Sprache Daten zum Dokument hinzuzufügen.',
      'admin_cms_doc_global_settings' => 'Globale Einstellungen für das Dokument',
      'admin_cms_doc_internal_note' => 'Interner Hinweis/Kommentar:',
      'admin_cms_edit_doc' => 'Ein Dokument ändern',
      'admin_cms_file_does_not_exist' => 'Die Datei wurde leider nicht gefunden!',
      'admin_cms_news_max_age' => 'Zeit nach der auf Aktualisierungen geprüft wird',
      'admin_cms_news_max_age_info' => 'Zeit in Minuten, nach der der Cache geelert wird.',
      'bbcode_help' => 'Hilfe',
    ),
    'language' =>
    array (
      'admin_cms_big_font' => NULL,
      'admin_cms_center' => NULL,
      'admin_cms_doc_file' => NULL,
      'admin_cms_doc_id' => 'Note',
      'admin_cms_extended_font' => NULL,
      'admin_cms_heading_1' => NULL,
      'admin_cms_heading_2' => NULL,
      'admin_cms_heading_3' => NULL,
      'admin_cms_head_alignment' => NULL,
      'admin_cms_head_choose_alignment' => NULL,
      'admin_cms_head_choose_color' => NULL,
      'admin_cms_head_choose_heading' => NULL,
      'admin_cms_head_choose_size' => NULL,
      'admin_cms_head_color' => NULL,
      'admin_cms_head_heading' => NULL,
      'admin_cms_head_help' => NULL,
      'admin_cms_head_size' => NULL,
      'admin_cms_head_smileys' => NULL,
      'admin_cms_if_path_is_given' => NULL,
      'admin_cms_justify' => NULL,
      'admin_cms_left' => NULL,
      'admin_cms_more_smileys' => NULL,
      'admin_cms_nav_title_text' => 'To use phrases from the custom language file for this entry simply use the follwoing code: <code>lang->key</code>. <code>key</code> is the key of the phrase you want to use. To manage the phrases just <a href="admin.php?action=language&amp;job=phrase_file&amp;file=Y3VzdG9tLmxuZy5waHA%3D" target="_blank">edit the custom language file</a>. The titles of the documents can also be used for the link text. Therefor you can use the code <code>doc->ID</code>, whereat <code>ID</code> is the ID of the linked document. This code will be created automatically while choosing one of the documents on the page "Existing Documents".',
      'admin_cms_right' => NULL,
      'admin_cms_small_font' => NULL,
      'admin_cms_tag_boldface' => NULL,
      'admin_cms_tag_definition' => NULL,
      'admin_cms_tag_definition_please_enter_definition' => NULL,
      'admin_cms_tag_definition_please_enter_word' => NULL,
      'admin_cms_tag_edited_passage' => NULL,
      'admin_cms_tag_email' => NULL,
      'admin_cms_tag_horizontal_ruler' => NULL,
      'admin_cms_tag_image' => NULL,
      'admin_cms_tag_italic' => NULL,
      'admin_cms_tag_off_topic' => NULL,
      'admin_cms_tag_ordered_list' => NULL,
      'admin_cms_tag_quote' => NULL,
      'admin_cms_tag_source_code' => NULL,
      'admin_cms_tag_subscript' => NULL,
      'admin_cms_tag_superscript' => NULL,
      'admin_cms_tag_typewriter' => NULL,
      'admin_cms_tag_underline' => NULL,
      'admin_cms_tag_unordered_list' => NULL,
      'admin_cms_tag_url' => NULL,
      'admin_cms_tag_url_please_provide_text' => NULL,
      'admin_cms_tag_url_please_provide_url' => NULL,
      'admin_cms_doc_av_languages' => 'Languages',
      'admin_cms_doc_checkboxes_help' => '<strong>Notice:</strong> Each language that is supposed to be saved, the check box before the language name must be checked, otherwise the data won\'t be saved and is lost irrevocably!',
      'admin_cms_doc_click_for_adding_lang' => 'Click here to add data for this language to the document.',
      'admin_cms_doc_global_settings' => 'Global settings for the document',
      'admin_cms_doc_internal_note' => 'Internal note/comment:',
      'admin_cms_edit_doc' => 'Edit a Document',
      'admin_cms_file_does_not_exist' => 'The file does not exist!',
      'admin_cms_news_max_age' => 'Time after which the program checks for updates',
      'admin_cms_news_max_age_info' => 'Time in minutes after that the cache will be cleared.',
      'bbcode_help' => 'Hilfe',
    ),
  ),
  'admin/db' =>
  array (
    'language_de' =>
    array (
      'admin_db_file_no_comments' => 'In der Datei wurden keine Backup-Informationen gefunden.',
      'admin_db_unknown_file_format' => 'Unbekanntes Dateiformat angegeben.',
    ),
    'language' =>
    array (
      'admin_db_file_no_comments' => 'The file does not contain backup information.',
      'admin_db_unknown_file_format' => 'Unknown file format.',
    ),
  ),
  'admin/explorer' =>
  array (
    'language_de' =>
    array (
      'admin_explorer_archive_is_not_supported' => 'Dieses Archiv wird derzeit nicht unterstützt.',
      'admin_explorer_file_is_not_supported2' => NULL,
      'admin_explorer_check_chmod' => 'Alle CHMOD prüfen',
      'admin_explorer_chmod_file_dir' => 'Datei oder Verzeichnis',
      'admin_explorer_chmod_info1' => 'Einige Verzeichnisse und Dateien benötigen spezielle Zugriffsrechte (CHMODs) um beschreibbar und ausführbar zu sein. Diese Rechte werden hier geprüft (und ggf. geändert) und das Resultat wird weiter unten angezeigt.',
      'admin_explorer_chmod_info2' => 'Die folgenen Status können auftreten:',
      'admin_explorer_chmod_state' => 'Status',
      'admin_explorer_chmod_status_failure' => 'Fehler',
      'admin_explorer_chmod_status_failure_info' => 'Die Rechte sind nicht korrekt gesetzt und müssen manuell (per FTP) korrigiert werden. Sie können die grundlegenden Funktionen von Viscacha nicht nutzen bis die Rechte korrekt gesetzt sind.',
      'admin_explorer_chmod_status_failure_x' => 'Fehler*',
      'admin_explorer_chmod_status_failure_x_info' => 'Die Rechte sind nicht korrekt gesetzt, aber diese Dateien sind nur für Arbeiten im Administrationsbereich relevant, daher müssen die Rechte erst dann vorher korrekt gesetzt werden wenn Sie diese Dateien bearbeiten wollen.',
      'admin_explorer_chmod_status_ok' => 'OK',
      'admin_explorer_chmod_status_ok_info' => 'Die Rechte sind korrekt gesetzt.',
      'admin_explorer_current_chmod' => 'Derzeitiger CHMOD',
      'admin_explorer_required_chmod' => 'Benötigter CHMOD',
    ),
    'language' =>
    array (
      'admin_explorer_archive_is_not_supported' => 'The archive is currently not supported.',
      'admin_explorer_file_is_not_supported2' => NULL,
      'admin_explorer_check_chmod' => 'Check all CHMOD',
      'admin_explorer_chmod_file_dir' => 'File or Directory',
      'admin_explorer_chmod_info1' => 'Some directories and files needs special permissions (CHMODs) to be writable und executable. This permissions will be checked (and changed) and the result will be shown below.',
      'admin_explorer_chmod_info2' => 'The following states can appear:',
      'admin_explorer_chmod_state' => 'State',
      'admin_explorer_chmod_status_failure' => 'Failure',
      'admin_explorer_chmod_status_failure_info' => 'The permissions are not correct and you have to set them manually (per FTP). You can not run the base functionality of Viscacha until this permissions are set correctly.',
      'admin_explorer_chmod_status_failure_x' => 'Failure*',
      'admin_explorer_chmod_status_failure_x_info' => 'The permissions are not correct, but these files are only required for changing a couple of things at the Admin Control Panel. You only need to change them before you edit these files.',
      'admin_explorer_chmod_status_ok' => 'OK',
      'admin_explorer_chmod_status_ok_info' => 'The permissions are set correctly.',
      'admin_explorer_current_chmod' => 'Current CHMOD',
      'admin_explorer_required_chmod' => 'Required CHMOD',
    ),
  ),
  'admin/forums' =>
  array (
    'language_de' =>
    array (
      'admin_forum_bbcode_html' => 'BB-Code ist erlaubt, HTML ist nicht erlaubt!',
    ),
    'language' =>
    array (
      'admin_forum_bbcode_html' => 'BB-Code is allowed; HTML is not allowed!',
    ),
  ),
  'admin/frames' =>
  array (
    'language_de' =>
    array (
      'admin_spellcheck_manager' => NULL,
    ),
    'language' =>
    array (
      'admin_spellcheck_manager' => NULL,
    ),
  ),
  'admin/javascript' =>
  array (
    'language_de' =>
    array (
      'bbcodes_note_prompt1' => 'Bitte Erklärung für ein Wort eingeben',
      'bbcodes_note_prompt2' => 'Bitte das zu erklärende Wort eingeben',
      'bbcodes_url_prompt1' => 'Bitte geben Sie die URL (mit http://) an',
      'bbcodes_url_prompt2' => 'Bitte geben Sie den Linktext an',
      'confirmNotUsed' => 'Wollen Sie die eingegebenen Daten der ausgewählten Sprache beim Speichern wirklich nicht übernehmen?',
      'js_listpompt1' => 'Bitte geben Sie den ',
      'js_listpompt2' => '. Listenpunkt an.\\n"Abbrechen" klicken zum Beenden.',
    ),
    'language' =>
    array (
      'bbcodes_note_prompt1' => 'Please enter the definition of the word',
      'bbcodes_note_prompt2' => 'Please enter the word to be defined',
      'bbcodes_url_prompt1' => 'Please provide URL (with http://)',
      'bbcodes_url_prompt2' => 'Please provide text for the link',
      'confirmNotUsed' => 'Do you really want to discard the data specified for this language?',
      'js_listpompt1' => 'please provide the ',
      'js_listpompt2' => '. listpoint on.\\nclick "cancel" to quit.',
    ),
  ),
  'admin/language' =>
  array (
    'language_de' =>
    array (
      'admin_lang_ignored_search_keys_desc' => 'Hier sind die Wörter aufgelistet, die während einer Suchanfrage ignoriert werden sollen, um einerseits die Suchergebnisse gering zu halten und andererseits die Suchgeschwindigkeit zu verbessern.<br /><br />Nur ein Wort pro Zeile. Bitte tippen Sie die Wörter komplett in Kleinbuchstaben. Sonderzeichen sollten in zwei Formen auftauchen. Beispiele:<br />&Auml; = ae und &auml;,<br />&szlig; = ss und &szlig;,<br />&eacute; = e und &eacute;,<br />&Ccedil; = c und &ccedil;',
      'admin_lang_used_as_original' => 'Sprache die als Original verwendet werden soll:',
      'admin_lang_used_as_original_info' => 'Ggeben Sie das Verzeichnis/die Sprache an, aus der die Phrase/der Ausdruck kopiert werden soll.',
    ),
    'language' =>
    array (
      'admin_lang_ignored_search_keys_desc' => '<br /><br />Only one word in each line. Please type the words completly in lower case letters. Special symbols should occur in two forms. Examples: <br />&Auml; = ae and &auml;,<br />&szlig; = ss and &szlig;,<br />&eacute; = e and &eacute;,<br />&Ccedil; = c and &ccedil;',
      'admin_lang_used_as_original' => 'Language which should be used as original:',
      'admin_lang_used_as_original_info' => 'Specify the directory/language wherefrom the phrase should be copied.',
    ),
  ),
  'admin/members' =>
  array (
    'language_de' =>
    array (
      'admin_member_merge_help' => 'Hier können Sie zewi Benutzeraccounts in einem zusammenführen. Das "Basis-Mitglied" belibt bestehen und die Daten des Accounts bleiben als Standard-Werte bestehen. Die Beiträge, privaten Nachrichten etc. werden vom "Überflüssigen Mitglied" zum "Basis-Mitglied" übernommen. Wenn bei dem "Basis-Mitglied" Daten fehlen, werden diese vom "Überflüssigen Mitglied" übernommen. Anschließend wird das "Überflüssigen Mitglied" gelöscht.',
      'admin_member_nl_mail_manager' => 'Newsletter und E-Mail-Verwaltung',
      'admin_member_send_nl' => 'Newsletter verfassen',
      'admin_member_mail_manager_instructions' => 'Hier können Sie die E-Mail-Adressen von Mitgliedern exportieren oder an diese E-Mail-Adressen einen Newsletter schicken. Geben Sie unten bei der Mitgliedersuche die Parameter ein, nach denen die Mitglieder gesucht werden sollen. Auf der darauf folgenden Seite kann man die Mitglieder dann, anhand der Einstellungen zum E-Mail-Empfang in den Profileinstellungen, noch einmal eingrenzen und abschließend auswählen, ob man an die gefundenen Mitglieder einen Newsletter verschicken will oder ob lediglich deren E-Mail-Adressen exportiert werden sollen. Danach ist es dann möglich in der jeweiligen Oberfläche die Export-Optionen anzugeben bzw. einen Newsletter zu verfassen.',
      'admin_member_name_connected_to_id' => 'Benutzername verknüpft an gelöschte ID',
      'admin_member_name_not_connected' => 'Nicht verknüpft',
      'admin_member_reserve_names_add' => 'Benutzernamen reservieren',
      'admin_member_reserve_names_title' => 'Reservierte Benutzernamen',
      'admin_member_selected_reserved_names_deleted' => 'Die Reservierung der markierten Benutzernamen wurde aufgehoben.',
      'admin_member_username_successfully_reserved' => 'Benutzername wurde erfolgreich reserviert.',
    ),
    'language' =>
    array (
      'admin_member_send_nl' => 'Compose Newsletter',
      'admin_member_mail_manager_instructions' => 'On this page you can export members\' e-mail addresses or send them a newsletter. Please feed your parameters you want to use to narrow down the results into the member search below. On the following page you are able to narrow down the results again according to the profile settings of the e-mail reception and you can choose whether you want to send out a newsletter to the members found or just export their e-mail adresses.  Once done searching it is possible compose a newsletter or to specify the options for the export of the e-mail adresses in their particular interfaces.',
      'admin_member_name_connected_to_id' => 'User Name connected to deleted ID',
      'admin_member_name_not_connected' => 'Not connected',
      'admin_member_reserve_names_add' => 'Reserve User Name',
      'admin_member_reserve_names_title' => 'Reserved User Names',
      'admin_member_selected_reserved_names_deleted' => 'The reservation of the selected user names has been deleted.',
      'admin_member_username_successfully_reserved' => 'User name has been reserved successfully.',
    ),
  ),
  'admin/misc' =>
  array (
    'language_de' =>
    array (
      'admin_misc_spellcheck_add_to_list' => NULL,
      'admin_misc_spellcheck_add_to_list_info' => NULL,
      'admin_misc_spellcheck_disabled' => NULL,
    ),
    'language' =>
    array (
      'admin_misc_spellcheck_add_to_list' => NULL,
      'admin_misc_spellcheck_add_to_list_info' => NULL,
      'admin_misc_spellcheck_disabled' => NULL,
    ),
  ),
  'admin/packages' =>
  array (
    'language_de' =>
    array (
      'admin_packages_head_delete_plugin' => 'Plugin löschen',
    ),
    'language' =>
    array (
      'admin_packages_head_delete_plugin' => 'Delete plugin',
    ),
  ),
  'admin/profilefield' =>
  array (
    'language_de' =>
    array (
      'admin_editable_change_settings' => '"Optionen ändern"',
      'admin_editable_change_user_data' => '"Daten ändern"',
    ),
  ),
  'admin/settings' =>
  array (
    'language_de' =>
    array (
      'admin_allow_vcard_dl' => NULL,
      'admin_allow_vcard_dl_guest' => NULL,
      'admin_allow_vcard_dl_guest_info' => NULL,
      'admin_allow_vcard_dl_info' => NULL,
      'admin_disable_registration_info' => 'Aktivieren Sie diese Option, wenn Sie die Registration neuer Mitglieder (temporär) verbieten möchten. Jeder der versucht sich zu registrieren bekommt die Meldung angezeigt, dass derzeit keine Registrierungen angenommen werden.',
      'admin_enable_jabber_support' => NULL,
      'admin_enable_jabber_support_info' => NULL,
      'admin_enable_spellchecker' => NULL,
      'admin_enable_spellchecker_info' => NULL,
      'admin_e_parse' => NULL,
      'admin_jabber_edit' => NULL,
      'admin_jabber_password' => NULL,
      'admin_jabber_password_info' => NULL,
      'admin_jabber_server' => NULL,
      'admin_jabber_server_info' => NULL,
      'admin_jabber_username' => NULL,
      'admin_jabber_username_info' => NULL,
      'admin_number_of_smileys' => NULL,
      'admin_number_of_smileys_info' => NULL,
      'admin_php_standard' => 'Keine PHP-Fehlermeldungen anzeigen',
      'admin_profil_avatar_edit' => 'Profilbilder &amp; Avatare',
      'admin_pspell_available' => NULL,
      'admin_pspell_not_available' => NULL,
      'admin_select_spell_check' => NULL,
      'admin_setting_jabber' => NULL,
      'admin_setting_jabber_info' => NULL,
      'admin_setting_profile_edit_info' => 'Profileinstellungen, Feldlängen und mehr.',
      'admin_setting_spell_check' => NULL,
      'admin_setting_spell_check_info' => NULL,
      'admin_spellcheck_edit' => NULL,
      'admin_spellcheck_mysql_php' => NULL,
      'admin_spellcheck_pspell_aspell' => NULL,
      'admin_spellcheck_system' => NULL,
      'admin_spellcheck_system_info' => NULL,
      'admin_spellcheck_textfile_php' => NULL,
      'admin_switch_cms_portal' => 'Welche Datei/Seite soll als Startseite der Homepage benutzt werden:',
      'admin_test_filesystem_chmods' => NULL,
      'admin_test_filesystem_chmods_info' => NULL,
      'admin_doclang_desc' => 'Die Dokumente, die in der vom Mitglied gewählten Sprache nicht zur Verfügung stehen, werden in der hier angegebenen Sprache als Ersatz angezeigt. Hinweis: Wenn von dieser Sprache ebenfalls kein Dokument existiert, so wird das Dokument in der Standard-Foren-Sprache verwendet. Wenn diese Sprache ebenfalls nicht zur Verfügung steht, dann wird ein beliebiges ausgewählt.',
      'admin_doclang_title' => 'Standard Rückfall-Sprache für Dokumente:',
      'admin_login_attempts' => 'Anmeldeversuche beschränken',
      'admin_login_attempts_blocktime' => 'Zeit, die ein Benutzer aus dem Forum ausgeschlossen wird',
      'admin_login_attempts_blocktime_info' => 'Zeit in Minuten, die ein Benutzer vom Forum ausgesperrt wird, nach dem die oben angegebene Anzahl an Fehlversuchen sich einzuloggen erreicht wird. Die Sperrungen werden in der Übersicht der gesperrten IPs (auch später noch) angezeigt.',
      'admin_login_attempts_max' => 'Maximale Anzahl der Anmeldeversuche',
      'admin_login_attempts_max_info' => 'Geben Sie die Anzahl Versuche an, die ein Benutzer ein falsches Passwort angeben kann. Wird dieses Limit überschritten, wird der Benutzer temporär gebannt. Setzen Sie dies auf 0, um das Feature zu deaktivieren.',
      'admin_login_attempts_time' => 'Zeit bevor die falsch eingegebenen Passwörter zurückgesetzt werden',
      'admin_login_attempts_time_info' => 'Zeit in Minuten nach denen die falsch eingegebenen Passwörter gelöscht werden. Bitte beachten Sie, dass sobald sich der Nutzer mit korrekten Daten angemeldet hat, die vorherigen vergeblichen Anmeldeversuche gelöscht werden.',
    ),
    'language' =>
    array (
      'admin_allow_vcard_dl' => NULL,
      'admin_allow_vcard_dl_guest' => NULL,
      'admin_allow_vcard_dl_guest_info' => NULL,
      'admin_allow_vcard_dl_info' => NULL,
      'admin_enable_jabber_support' => NULL,
      'admin_enable_jabber_support_info' => NULL,
      'admin_enable_spellchecker' => NULL,
      'admin_enable_spellchecker_info' => NULL,
      'admin_e_parse' => NULL,
      'admin_jabber_edit' => NULL,
      'admin_jabber_password' => NULL,
      'admin_jabber_password_info' => NULL,
      'admin_jabber_server' => NULL,
      'admin_jabber_server_info' => NULL,
      'admin_jabber_username' => NULL,
      'admin_jabber_username_info' => NULL,
      'admin_number_of_smileys' => NULL,
      'admin_number_of_smileys_info' => NULL,
      'admin_php_standard' => 'Do not show PHP error messages',
      'admin_pspell_available' => NULL,
      'admin_pspell_not_available' => NULL,
      'admin_select_spell_check' => NULL,
      'admin_setting_jabber' => NULL,
      'admin_setting_jabber_info' => NULL,
      'admin_setting_profile_edit_info' => 'Profile settings, fields lengths and more.',
      'admin_setting_spell_check' => NULL,
      'admin_setting_spell_check_info' => NULL,
      'admin_spellcheck_edit' => NULL,
      'admin_spellcheck_mysql_php' => NULL,
      'admin_spellcheck_pspell_aspell' => NULL,
      'admin_spellcheck_system' => NULL,
      'admin_spellcheck_system_info' => NULL,
      'admin_spellcheck_textfile_php' => NULL,
      'admin_test_filesystem_chmods' => NULL,
      'admin_test_filesystem_chmods_info' => NULL,
      'admin_doclang_desc' => 'This setting specifies the fallback language that will be shown if the document is not available in the language chosen by the member. Notice<A[Notice|Tip]>: If there is also no document in the language specified here, the standard forum language is used. If this language is also not available, an arbitrary will be chosen.',
      'admin_doclang_title' => 'Standard fallback language for documents:',
      'admin_login_attempts' => 'Limit login attempts',
      'admin_login_attempts_blocktime' => 'Time a user will be locked out of the forums',
      'admin_login_attempts_blocktime_info' => 'Enter in minutes the time that a user will not be able to access the forums after entering the above amount of wrong passwords. The bans will be shown on the banned ip overview.',
      'admin_login_attempts_max' => 'Maximum number of login attempts',
      'admin_login_attempts_max_info' => 'Insert the number of times a user can submit a wrong password when logging in, before they are unable to access the board temporarily. Set this to 0 to disable the feature completely.',
      'admin_login_attempts_time' => 'Time before number of wrong passwords is reset',
      'admin_login_attempts_time_info' => 'Enter in minutes the time before any wrong passwords submitted by a user is reset to 0. Please note also that once a user has successfully logged in, the data is deleted for that person.',
    ),
  ),
  'admin/spider' =>
  array (
    'language_de' =>
    array (
      'admin_spider_no_pending_bots' => 'Es sind derzeit leider keine neu erkannten Spider vorhanden.',
      'admin_spider_bots' => 'Bots',
    ),
    'language' =>
    array (
      'admin_spider_bots' => 'Bots',
    ),
  ),
  'bbcodes' =>
  array (
    'language_de' =>
    array (
      'bbcodes_help' => NULL,
      'bbcodes_note_prompt1' => NULL,
      'bbcodes_note_prompt2' => NULL,
      'bbcodes_url_prompt1' => NULL,
      'bbcodes_url_promtp2' => NULL,
      'bbcodes_code_short' => 'Code',
      'bbcodes_create_table' => 'Neue Tabelle erstellen',
      'bbcodes_table_cols' => 'Spalten',
      'bbcodes_table_insert_table' => 'Tabelle einfügen',
      'bbcodes_table_rows' => 'Zeilen',
      'bbcodes_table_show_head' => 'Erste Zeile als Titelzeile benutzen',
      'geshi_bbcode_nohighlighting' => 'Kein Highlighting',
      'more_smileys' => 'mehr Smileys',
      'textarea_check_length' => 'Überprüfe Textlänge',
      'textarea_decrease_size' => 'Verkleinern',
      'textarea_increase_size' => 'Vergrößern',
    ),
    'language' =>
    array (
      'bbcodes_help' => NULL,
      'bbcodes_note_prompt1' => NULL,
      'bbcodes_note_prompt2' => NULL,
      'bbcodes_url_prompt1' => NULL,
      'bbcodes_url_promtp2' => NULL,
      'bbcodes_code_short' => 'Code',
      'bbcodes_create_table' => 'Create new table',
      'bbcodes_table_cols' => 'Columns',
      'bbcodes_table_insert_table' => 'Insert Table',
      'bbcodes_table_rows' => 'Rows',
      'bbcodes_table_show_head' => 'Use first row as header',
      'geshi_bbcode_nohighlighting' => 'No Syntax Highlighting',
      'more_smileys' => 'more Smilies',
      'textarea_check_length' => 'Check length',
      'textarea_decrease_size' => 'Decrease Size',
      'textarea_increase_size' => 'Increase Size',
    ),
  ),
  'global' =>
  array (
    'language_de' =>
    array (
      'bb_ext_sourcecode' => NULL,
      'geshi_bbcode_desc' => NULL,
      'geshi_bbcode_nohighlighting' => NULL,
      'geshi_bbcode_title' => NULL,
      'geshi_hlcode_options' => NULL,
      'geshi_hlcode_title' => '{$lang_name}-Quelltext:',
      'im_yahoo_2' => NULL,
      'log_wrong_data' => 'Sie haben falsche Benutzerdaten angegeben oder Sie sind noch nicht freigeschaltet. {$can_try}<br />Benutzen Sie die <a href="log.php?action=pwremind">Passwort vergessen</a>-Funktion wenn Sie Ihr Passwort nicht mehr wissen. Falls Sie keine Freischalt-E-Mail bekommen haben, klicken Sie <a href="register.php?action=resend">hier</a>.',
      'more_smileys' => NULL,
      'pages_sep' => '...',
      'pm_index_old' => 'Gelesene Nachrichten dieser Woche',
      'post_copy' => NULL,
      'post_copy_desc' => NULL,
      'profile_vcard' => NULL,
      'spellcheck' => NULL,
      'spellcheck_changeto' => NULL,
      'spellcheck_close' => NULL,
      'spellcheck_disabled' => NULL,
      'spellcheck_ignore' => NULL,
      'spellcheck_ignore_all' => NULL,
      'spellcheck_in_progress' => NULL,
      'spellcheck_notfound' => NULL,
      'spellcheck_options' => NULL,
      'spellcheck_replace' => NULL,
      'spellcheck_replace_all' => NULL,
      'spellcheck_undo' => NULL,
      'textarea_check_length' => NULL,
      'textarea_decrease_size' => NULL,
      'textarea_increase_size' => NULL,
      'upload_intro1' => 'Um an diesen Beitrag eine Datei anzufügen, klicken Sie auf die "Durchsuchen" Schaltfläche und wählen Sie eine Datei aus. Klicken Sie dann auf "Senden", um den Vorgang abzuschließen.<br /><br />Erlaubte Dateitypen: {$filetypes}<br />Maximale Dateigröße: {$filesize}',
      'vcard_note' => NULL,
      'bbcode_help' => 'Hilfe',
      'doc_wrong_language_shown' => 'Leider ist kein Dokument in der von Ihnen gewählten Sprache vorhanden, daher wird das Dokument in einer anderen Sprache angezeigt!',
      'general_notice_title' => 'Hinweis!',
      'link_rel_atom' => 'Atom Newsfeed',
      'link_rel_opml' => 'OPML Newsfeed',
      'link_rel_print' => 'Druckversion',
      'link_rel_rss' => 'RSS Newsfeed',
      'login_attempts_banned' => 'Ihre IP-Adresse ({$ip}) wurde temporär gebannt, da Sie zu viele Anmeldeversuche mit falschen Benutzerdaten durchgeführt haben.',
      'log_wrong_data_block' => 'Sie wurden temporär gebannt, da Sie die maximale Anzahl an Anmeldeversuchen in einer bestimmten Zeit überschritten haben. Mehr Informationen erhalten Sie auf der nächsten Seite...',
      'log_x_attempts' => 'Sie haben noch {$attempts} Versuche sich anzumelden!',
      'pages_sep2' => ', ',
      'upload_intro1b' => 'Maxmimale Bildabmessungen: {@config->tpcwidth} x {@config->tpcheight} Pixel',
    ),
    'language' =>
    array (
      'bb_ext_sourcecode' => NULL,
      'geshi_bbcode_desc' => NULL,
      'geshi_bbcode_nohighlighting' => NULL,
      'geshi_bbcode_title' => NULL,
      'geshi_hlcode_options' => NULL,
      'geshi_hlcode_title' => 'Source code ({$lang_name}):',
      'im_yahoo_2' => NULL,
      'log_wrong_data' => 'You entered invalid login data or your account has not yet been activated. {$can_try}<br />Did you <a href="log.php?action=pwremind">forget your password</a>? If you have not received a validation e-mail click <a href="register.php?action=resend">here</a>.',
      'more_smileys' => NULL,
      'pages_sep' => '...',
      'pm_index_old' => 'Read private messages of this week',
      'post_copy' => NULL,
      'post_copy_desc' => NULL,
      'profile_vcard' => NULL,
      'spellcheck' => NULL,
      'spellcheck_changeto' => NULL,
      'spellcheck_close' => NULL,
      'spellcheck_disabled' => NULL,
      'spellcheck_ignore' => NULL,
      'spellcheck_ignore_all' => NULL,
      'spellcheck_in_progress' => NULL,
      'spellcheck_notfound' => NULL,
      'spellcheck_options' => NULL,
      'spellcheck_replace' => NULL,
      'spellcheck_replace_all' => NULL,
      'spellcheck_undo' => NULL,
      'textarea_check_length' => NULL,
      'textarea_decrease_size' => NULL,
      'textarea_increase_size' => NULL,
      'upload_intro1' => 'To attach a file to this post, click the file upload button, select a file and press "submit" to start the upload.<br /><br />Allowed filetypes: {$filetypes}<br />max filesize: {$filesize}',
      'vcard_note' => NULL,
      'bbcode_help' => 'Help',
      'doc_wrong_language_shown' => 'Unfortunately the document you requested is not available in the language you have chosen. Therefore the document will be displayed in another language!',
      'general_notice_title' => 'Notice!',
      'link_rel_atom' => 'Atom Newsfeed',
      'link_rel_opml' => 'OPML Newsfeed',
      'link_rel_print' => 'Print version',
      'link_rel_rss' => 'RSS Newsfeed',
      'login_attempts_banned' => 'Your IP-Adress ({$ip}) has been banned temporarily because you have reached the maximum number of failed login attempts.',
      'log_wrong_data_block' => 'You have been blocked temporarily, because you reached the maximum login attempts allowed in a certain time range. More information you can find on the follwoing page...',
      'log_x_attempts' => 'You have {$attempts} login attempts left!',
      'pages_sep2' => ', ',
      'upload_intro1b' => 'Max. image size: {@config->tpcwidth} x {@config->tpcheight} px',
    ),
  ),
  'javascript' =>
  array (
    'language_de' =>
    array (
      'js_no_changed' => NULL,
      'js_no_found' => NULL,
      'js_no_sug' => NULL,
      'js_one_changed' => NULL,
      'js_sc_complete' => NULL,
      'js_submitted' => NULL,
      'js_x_changed' => NULL,
      'bbcodes_note_prompt1' => 'Bitte Erklärung für ein Wort eingeben',
      'bbcodes_note_prompt2' => 'Bitte das zu erklärende Wort eingeben',
      'bbcodes_url_prompt1' => 'Bitte geben Sie die URL (mit http://) an',
      'bbcodes_url_prompt2' => 'Bitte geben Sie den Linktext an',
      'js_page_jumpto' => 'Geben Sie die Seite ein, zu der Sie springen möchten:',
    ),
    'language' =>
    array (
      'js_no_changed' => NULL,
      'js_no_found' => NULL,
      'js_no_sug' => NULL,
      'js_one_changed' => NULL,
      'js_sc_complete' => NULL,
      'js_submitted' => NULL,
      'js_x_changed' => NULL,
      'bbcodes_note_prompt1' => 'Please enter the definition of the word',
      'bbcodes_note_prompt2' => 'Please enter the word to be defined',
      'bbcodes_url_prompt1' => 'Please provide URL (with http://)',
      'bbcodes_url_prompt2' => 'Please provide text for the link',
      'js_page_jumpto' => 'Enter the page number you wish to go to:',
    ),
  ),
  'modules' =>
  array (
    'language_de' =>
    array (
      'last_posts_info_reply' => 'Dieses Thema enthält mehr als {$num} Beiträge. Klicken Sie <a href=\'showtopic.php?id={@info->id}\' target=\'_blank\'>hier</a>, um das ganze Thema zu lesen.',
    ),
    'language' =>
    array (
      'last_posts_info_reply' => 'This topic contains more than {$num} posts. Click <a href="showtopic.php?id={@info->id}" target="_blank">here</a>, to view the complete topic.',
    ),
  ),
  'settings' =>
  array (
    'language_de' =>
    array (
      'compatible_version' => '0.8 RC5',
    ),
    'language' =>
    array (
      'compatible_version' => '0.8 RC5',
    ),
  ),
  'wwo' =>
  array (
    'language_de' =>
    array (
      'wwo_fallback' => NULL,
      'wwo_misc_board_rules' => 'liest die Forenregln eines Forums',
      'wwo_pdf' => 'Betrachtet die PDF-Version eines Themas: <a href="pdf.php?id={$id}">{$title}</a>',
      'wwo_popup_hlcode' => NULL,
      'wwo_profile' => 'Betrachtet ein Profil',
      'wwo_profile_send' => 'Verschickt über das Profil eine Nachricht',
      'wwo_showtopic' => 'Liest ein Thema: <a href="showtopic.php?id={$id}">{$title}</a>',
      'wwo_spellcheck' => NULL,
      'wwo_addreply_fallback' => 'Schreibt eine Antwort zu einem Thema',
      'wwo_docs_fallback' => 'Betrachtet eine Seite',
      'wwo_pdf_fallback' => 'Betrachtet die PDF-Version eines Themas',
      'wwo_popup_showpost_fallback' => 'Betrachtet einen einzelnen Beitrag',
      'wwo_print_fallback' => 'Druckt ein Thema',
      'wwo_showforum_fallback' => 'Betrachtet ein Forum',
      'wwo_showtopic_fallback' => 'Liest ein Thema',
    ),
    'language' =>
    array (
      'wwo_addreply' => 'is writing a reply to the topic <a href="showtopic.php?id={$id}">{$title}</a>',
      'wwo_fallback' => NULL,
      'wwo_misc_board_rules' => 'is reading the rules of a forum',
      'wwo_pdf' => 'is viewing the PDF file of the following topic: <a href="pdf.php?id={$id}">{$title}</a>',
      'wwo_popup_hlcode' => NULL,
      'wwo_popup_showpost' => 'is reading the following post: <a href="popup.php?action=showpost&id={$id}" target="showpost" onclick="showpost(this)">{$title}</a>',
      'wwo_print' => 'is printing the following topic: <a href="print.php?id={$id}">{$title}</a>',
      'wwo_profile' => 'is viewing a profile',
      'wwo_profile_send' => 'is sending a message to a member',
      'wwo_showforum' => 'is viewing a board',
      'wwo_showtopic' => 'is reading the following topic: <a href="showtopic.php?id={$id}">{$title}</a>',
      'wwo_spellcheck' => NULL,
      'wwo_addreply_fallback' => 'is writing a reply to a topic',
      'wwo_docs_fallback' => 'is viewing the page',
      'wwo_pdf_fallback' => 'is viewing the PDF file of a topic',
      'wwo_popup_showpost_fallback' => 'is reading a post',
      'wwo_print_fallback' => 'is printing a topic',
      'wwo_showtopic_fallback' => 'is reading a topic',
    ),
  ),
);
updateLanguageFiles($ini);
echo "- Language files updated.<br />";

// Set incompatible packages inactive
$db->query("UPDATE {$db->pre}packages SET active = '0' WHERE internal = 'viscacha_quick_reply'");
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