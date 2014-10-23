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
removeHook($hooks, 'pdf');
$filesystem->file_put_contents('admin/data/hooks.txt', implode("\r\n", $hooks));
echo "- Hooks updated.<br />";

// Config
$c = new manageconfig();
$c->getdata('data/config.inc.php');
$c->updateconfig('version', str, VISCACHA_VERSION);
$c->updateconfig('fname', str, htmlentities($config['fname'], ENT_QUOTES));
$c->updateconfig('fdesc', str, htmlentities($config['fdesc'], ENT_QUOTES));
$c->updateconfig('always_send_js', int, 1);
$c->updateconfig('vote_change', int, 0);
$c->updateconfig('botgfxtest_width', int, 150);
$c->updateconfig('botgfxtest_height', int, 40);
$c->updateconfig('botgfxtest_recaptcha_private', str, '');
$c->updateconfig('botgfxtest_recaptcha_public', str, '');
$c->delete('pdfcompress');
$c->delete('pdfdownload');
$c->delete('allow_http_auth');
$c->delete('botgfxtest_text_verification');
$c->delete('botgfxtest_posts_width');
$c->delete('botgfxtest_posts_height');
$c->savedata();

$c = new manageconfig();
$c->getdata('admin/data/config.inc.php', 'admconfig');
$c->updateconfig('checked_package_updates', int, 0);
$c->savedata();
echo "- Configuration updated.<br />";

// Languages
$ini = array (
  'admin/cms' =>
  array (
    'language_de' =>
    array (
      'admin_wysiwyg_alignment' => 'Ausrichtung:',
      'admin_wysiwyg_alignment_bottom' => 'Unten',
      'admin_wysiwyg_alignment_center' => 'Zentriert',
      'admin_wysiwyg_alignment_left' => 'Links',
      'admin_wysiwyg_alignment_middle' => 'Mitte',
      'admin_wysiwyg_alignment_not_set' => 'Nicht gesetzt',
      'admin_wysiwyg_alignment_right' => 'Rechts',
      'admin_wysiwyg_alignment_top' => 'Oben',
      'admin_wysiwyg_alt_text' => 'Alternativtext:',
      'admin_wysiwyg_bgcolor' => 'Hintergrundfarbe',
      'admin_wysiwyg_border_collapse' => 'Rahmen vereinigen:',
      'admin_wysiwyg_border_color' => 'Rahmenfarbe',
      'admin_wysiwyg_border_style' => 'Rahmenstil:',
      'admin_wysiwyg_border_width' => 'Rahmenstärke:',
      'admin_wysiwyg_choose' => 'Auswählen',
      'admin_wysiwyg_color' => 'Farbe:',
      'admin_wysiwyg_color_preview' => 'Vorschau der Farbe',
      'admin_wysiwyg_custom_target' => 'Kein Ziel / Benutzerdefiniert',
      'admin_wysiwyg_file' => 'Datei:',
      'admin_wysiwyg_folder' => 'Verzeichnis:',
      'admin_wysiwyg_folder_restrictions' => 'Das Verzeichnis darf nur Buchstaben, Zahlen, Unterstriche und Bindestriche enthalten.',
      'admin_wysiwyg_form_cancel' => 'Abbrechen',
      'admin_wysiwyg_form_submit' => 'Einfügen',
      'admin_wysiwyg_form_upload' => 'Hochladen',
      'admin_wysiwyg_height' => 'Höhe:',
      'admin_wysiwyg_hey_code' => 'Hex-Code:',
      'admin_wysiwyg_hspace' => 'Horizontaler Zwischenraum:',
      'admin_wysiwyg_image_url' => 'Bild-Adresse:',
      'admin_wysiwyg_insert_hr' => 'Horizontale Linie einfügen',
      'admin_wysiwyg_insert_img' => 'Bild einfügen',
      'admin_wysiwyg_insert_link' => 'Link einfügen',
      'admin_wysiwyg_layout' => 'Layout',
      'admin_wysiwyg_max_filesize' => 'Maximale Dateigröße: {$filesize}',
      'admin_wysiwyg_name' => 'Titel:',
      'admin_wysiwyg_no_files_found' => 'Es wurden leider keine Dateien gefunden.',
      'admin_wysiwyg_no_shade' => 'Kein Schatten:',
      'admin_wysiwyg_padding' => 'Innenabstand:',
      'admin_wysiwyg_predefined_colors' => 'Vordefinierte Farben',
      'admin_wysiwyg_preview' => 'Vorschau',
      'admin_wysiwyg_prev_dir' => 'Vorheriges Verzeichnis',
      'admin_wysiwyg_select_color' => 'Farbe wählen',
      'admin_wysiwyg_select_img' => 'Bild auswählen',
      'admin_wysiwyg_table_cols' => 'Spalten:',
      'admin_wysiwyg_table_properties' => 'Tabellen-Einstellungen',
      'admin_wysiwyg_table_rows' => 'Zeilen:',
      'admin_wysiwyg_table_width' => 'Breite:',
      'admin_wysiwyg_upload_x' => 'Bild hochladen',
      'admin_wysiwyg_url' => 'Adresse:',
      'admin_wysiwyg_vspace' => 'Vertikaler Zwischenraum:',
      'admin_wysiwyg_width' => 'Breite:',
      'admin_wysiwyg_width_full' => 'Voll',
    ),
    'language' =>
    array (
      'admin_wysiwyg_alignment' => 'Alignment:',
      'admin_wysiwyg_alignment_bottom' => 'Bottom',
      'admin_wysiwyg_alignment_center' => 'Center',
      'admin_wysiwyg_alignment_left' => 'Left',
      'admin_wysiwyg_alignment_middle' => 'Middle',
      'admin_wysiwyg_alignment_not_set' => 'Not set',
      'admin_wysiwyg_alignment_right' => 'Right',
      'admin_wysiwyg_alignment_top' => 'Top',
      'admin_wysiwyg_alt_text' => 'Alternate Text:',
      'admin_wysiwyg_bgcolor' => 'Background-Color:',
      'admin_wysiwyg_border_collapse' => 'Border-Collapse:',
      'admin_wysiwyg_border_color' => 'Border-Color:',
      'admin_wysiwyg_border_style' => 'Border-Style:',
      'admin_wysiwyg_border_width' => 'Border-Width:',
      'admin_wysiwyg_choose' => 'Choose',
      'admin_wysiwyg_color' => 'Color:',
      'admin_wysiwyg_color_preview' => 'Preview of the color',
      'admin_wysiwyg_custom_target' => 'No target / Custom',
      'admin_wysiwyg_file' => 'File:',
      'admin_wysiwyg_folder' => 'Folder:',
      'admin_wysiwyg_folder_restrictions' => 'The folder should only contain letters, numbers, underscores or hyphen.',
      'admin_wysiwyg_form_cancel' => 'Cancel',
      'admin_wysiwyg_form_submit' => 'Insert',
      'admin_wysiwyg_form_upload' => 'Upload',
      'admin_wysiwyg_height' => 'Height:',
      'admin_wysiwyg_hey_code' => 'Hex-Code:',
      'admin_wysiwyg_hspace' => 'Horizontal Space:',
      'admin_wysiwyg_image_url' => 'Image URL:',
      'admin_wysiwyg_insert_hr' => 'Insert Horizontal Ruler',
      'admin_wysiwyg_insert_img' => 'Insert Image',
      'admin_wysiwyg_insert_link' => 'Insert Hyperlink',
      'admin_wysiwyg_layout' => 'Layout',
      'admin_wysiwyg_max_filesize' => 'Max Filesize: {$filesize}',
      'admin_wysiwyg_name' => 'Name:',
      'admin_wysiwyg_no_files_found' => 'Sorry, no files found.',
      'admin_wysiwyg_no_shade' => 'No Shade:',
      'admin_wysiwyg_padding' => 'Padding:',
      'admin_wysiwyg_predefined_colors' => 'Predefined colors',
      'admin_wysiwyg_preview' => 'Preview',
      'admin_wysiwyg_prev_dir' => 'Previous Directory',
      'admin_wysiwyg_select_color' => 'Select Color',
      'admin_wysiwyg_select_img' => 'Select Image',
      'admin_wysiwyg_table_cols' => 'Columns:',
      'admin_wysiwyg_table_properties' => 'Table Properties',
      'admin_wysiwyg_table_rows' => 'Rows:',
      'admin_wysiwyg_table_width' => 'Width:',
      'admin_wysiwyg_upload_x' => 'Upload image',
      'admin_wysiwyg_url' => 'URL:',
      'admin_wysiwyg_vspace' => 'Vertical Space:',
      'admin_wysiwyg_width' => 'Width:',
      'admin_wysiwyg_width_full' => 'Full',
    ),
  ),
  'admin/db' =>
  array (
    'language_de' =>
    array (
      'admin_db_backup_options_invalid' => 'Die Wahl der Optionen war leider nicht korrekt. Sie müssen entweder die Struktur und/oder die Daten exportieren.',
    ),
    'language' =>
    array (
      'admin_db_backup_options_invalid' => 'The chosen options are not correct. You need to export the structure and/or the data.',
    ),
  ),
  'admin/designs' =>
  array (
    'language_de' =>
    array (
      'admin_design_copy_standard_css' => 'Erstelle ein neues Stylesheet-Verzeichnis und benutze die Stylesheets des Standard-Designs als Grundlage.',
      'admin_design_create_new_images_directory' => 'Erstelle ein neues Bilder-Verzeichnis und benutze die Bilder des Standard-Designs als Grundlage.',
      'admin_design_create_new_template_directory' => 'Erstelle ein neues Template-Verzeichnis und benutze die Templates des Standard-Designs als Grundlage.',
    ),
    'language' =>
    array (
      'admin_design_copy_standard_css' => 'Create a new directory for stylesheets and use the stylesheets from the standard design as base',
      'admin_design_create_new_images_directory' => 'Create a new directory for images and use the images from the standard design as base.',
      'admin_design_create_new_template_directory' => 'Create a new directory for templates and use the templates from the standard design as base.',
    ),
  ),
  'admin/global' =>
  array (
    'language_de' =>
    array (
      'admin_gll_docs' => NULL,
      'admin_gll_pdf' => NULL,
      'admin_gls_docs' => NULL,
      'admin_gls_pdf' => NULL,
    ),
    'language' =>
    array (
      'admin_gll_docs' => NULL,
      'admin_gll_pdf' => NULL,
      'admin_gls_docs' => NULL,
      'admin_gls_pdf' => NULL,
    ),
  ),
  'admin/javascript' =>
  array (
    'language_de' =>
    array (
      'wysiwyg_backcolor' => 'Hintergrundfarbe',
      'wysiwyg_bold' => 'Fett',
      'wysiwyg_center' => 'Zentriert ausrichten',
      'wysiwyg_clean_word' => 'HTML-Code von MS Word säubern?',
      'wysiwyg_copy' => 'Kopieren',
      'wysiwyg_cut' => 'Ausscheiden',
      'wysiwyg_error_text_mode' => 'Sie sind im Text-Modus. Dieses Feature ist deswegen zur Zeit nicht verfügbar.',
      'wysiwyg_font_face' => 'Schriftart',
      'wysiwyg_font_size' => 'Schriftgröße',
      'wysiwyg_forecolor' => 'Vordergrundfarbe',
      'wysiwyg_headings' => 'Überschrift',
      'wysiwyg_hr' => 'Horizontale Linie',
      'wysiwyg_image' => 'Bild',
      'wysiwyg_indent' => 'Einrücken',
      'wysiwyg_italic' => 'Kursiv',
      'wysiwyg_justify' => 'Blocksatz',
      'wysiwyg_left' => 'Linksbündig ausrichten',
      'wysiwyg_link' => 'Link',
      'wysiwyg_maximize' => 'Editor maximieren',
      'wysiwyg_not_compatible' => 'Der WYSIWYG-Editor wird von Ihrem Browser leider nicht (ausreichend) unterstützt.',
      'wysiwyg_ordered_list' => 'Geordnete Liste',
      'wysiwyg_outdent' => 'Ausrücken',
      'wysiwyg_paste' => 'Einfügen',
      'wysiwyg_redo' => 'Wiederherstellen',
      'wysiwyg_remove_formatting' => 'Formatierung entfernen',
      'wysiwyg_right' => 'Rechtsbündig ausrichten',
      'wysiwyg_strikethrough' => 'Durchgestrichen',
      'wysiwyg_strip_word' => 'Word HTML entfernen',
      'wysiwyg_subscript' => 'Tiefgestellt',
      'wysiwyg_superscript' => 'Hochgestellt',
      'wysiwyg_table' => 'Tabelle',
      'wysiwyg_underline' => 'Unterstrichen',
      'wysiwyg_undo' => 'Rückgängig',
      'wysiwyg_unordered_list' => 'Ungeordnete Liste',
      'wysiwyg_view_source' => 'Quelltext ansehen',
      'wysiwyg_view_text' => 'Design ansehen',
    ),
    'language' =>
    array (
      'wysiwyg_backcolor' => 'Back Color',
      'wysiwyg_bold' => 'Bold',
      'wysiwyg_center' => 'Justify Center',
      'wysiwyg_clean_word' => 'Clean HTML inserted by MS Word?',
      'wysiwyg_copy' => 'Copy',
      'wysiwyg_cut' => 'Cut',
      'wysiwyg_error_text_mode' => 'You are in TEXT Mode. This feature has been disabled.',
      'wysiwyg_font_face' => 'Font face',
      'wysiwyg_font_size' => 'Font Size',
      'wysiwyg_forecolor' => 'Fore Color',
      'wysiwyg_headings' => 'Headings',
      'wysiwyg_hr' => 'Horizontal Ruler',
      'wysiwyg_image' => 'Image',
      'wysiwyg_indent' => 'Indent',
      'wysiwyg_italic' => 'Italic',
      'wysiwyg_justify' => 'Justify',
      'wysiwyg_left' => 'Justify Left',
      'wysiwyg_link' => 'Link',
      'wysiwyg_maximize' => 'Maximize the editor',
      'wysiwyg_not_compatible' => 'The WYSIWYG-Editor is not (completely) supported by your browser.',
      'wysiwyg_ordered_list' => 'Ordered List',
      'wysiwyg_outdent' => 'Outdent',
      'wysiwyg_paste' => 'Paste',
      'wysiwyg_redo' => 'Redo',
      'wysiwyg_remove_formatting' => 'Remove Formatting',
      'wysiwyg_right' => 'Justify Right',
      'wysiwyg_strikethrough' => 'Strikethrough',
      'wysiwyg_strip_word' => 'Strip Word HTML',
      'wysiwyg_subscript' => 'Subscript',
      'wysiwyg_superscript' => 'Superscript',
      'wysiwyg_table' => 'Table',
      'wysiwyg_underline' => 'Underline',
      'wysiwyg_undo' => 'Undo',
      'wysiwyg_unordered_list' => 'Unordered List',
      'wysiwyg_view_source' => 'View Source',
      'wysiwyg_view_text' => 'View Design',
    ),
  ),
  'admin/members' =>
  array (
    'language_de' =>
    array (
      'admin_member_activate_by_admin' => NULL,
      'admin_member_activate_via_mail' => NULL,
      'admin_member_at_least_one_match' => 'oder',
      'admin_member_not_activated' => NULL,
      'admin_member_whole_match' => 'und',
      'admin_member_at_least_one_match_desc' => 'Nur eine der Angaben muss passen, um zu einem Treffer zu führen',
      'admin_member_whole_match_desc' => 'Alle Angaben müssen passen, um zu einem Treffer zu führen',
    ),
    'language' =>
    array (
      'admin_member_activate_by_admin' => NULL,
      'admin_member_activate_via_mail' => NULL,
      'admin_member_at_least_one_match' => 'or',
      'admin_member_not_activated' => NULL,
      'admin_member_whole_match' => 'and',
      'admin_member_at_least_one_match_desc' => 'at least one of the input have to lead to a match',
      'admin_member_whole_match_desc' => 'the whole input have to lead to a match',
    ),
  ),
  'admin/settings' =>
  array (
    'language_de' =>
    array (
      'admin_activate_logging_missing_ip' => 'Aktiviert die Protokollierung von IPs und User-Agents:',
      'admin_activate_pdf_topics' => NULL,
      'admin_activate_pdf_topics_info' => NULL,
      'admin_activate_spambot_at_guests' => 'Spam-Bot-Schutz bei Gastbeiträgen',
      'admin_activate_spambot_registration' => 'Spam-Bot-Schutz bei der Registration:',
      'admin_compress_pdf' => NULL,
      'admin_compress_pdf_info' => NULL,
      'admin_dyeing_letters_captcha' => 'Eingefärbte Buchstaben:',
      'admin_examples_captcha' => NULL,
      'admin_examples_captchaimg_textcodes' => NULL,
      'admin_examples_textcodes' => NULL,
      'admin_e_all' => 'Alle Fehler, Warnungen und Hinweise',
      'admin_e_error' => 'Nur schwerewiegende Fehler',
      'admin_e_notice' => NULL,
      'admin_e_strict' => NULL,
      'admin_e_warning' => NULL,
      'admin_file_typ_captcha' => 'Dateityp:',
      'admin_ftp_directory_does_not_exist' => 'Verzeichnis "{$ftp_path}" existiert leider nicht!',
      'admin_image_height_captcha' => 'Standard Bildhöhe:',
      'admin_image_width_captcha' => 'Standard Bildbreite:',
      'admin_php_standard' => 'Standardeinstellung von PHP nutzen',
      'admin_pic_quality_captcha' => 'Qualität der Bilder:',
      'admin_save_php_errors_info' => 'Diese Option sollte nur zu Debugging-Zwecken aktiviert werden.',
      'admin_setting_posts_topics_info' => 'Minimale und Maximale Längen, Beitragsänderungen und andere Einstellungen zu Beiträgen.',
      'admin_spambot_posting' => NULL,
      'admin_spambot_registration' => NULL,
      'admin_topics_posts_pdf' => NULL,
      'admin_wave_filter_captcha' => 'Wende den "Wellen"-Filter auf das Spamschutz-Bild an:',
      'admin_captcha_type0' => 'Nicht aktiviert',
      'admin_captcha_type1' => 'Standard (VeriWord)',
      'admin_captcha_type2' => 'reCaptcha',
      'admin_enable_change_vote' => 'Erlaubt sich bei einer Umfrage umzuentscheiden',
      'admin_enable_change_vote_info' => 'Diese Option ermöglicht es Mitgliedern, sich, nach ihrer Stimmabgabe, bei einer Umfrage nochmal umzuentscheiden.',
      'admin_e_none' => 'Keine Fehlermeldungen ausgeben',
      'admin_recaptcha_private_key' => 'Interner Schlüssel:',
      'admin_recaptcha_private_key_info' => '\'Private Key\', der Ihnen von {$re_link} zur Verfügung gestellt wurde.',
      'admin_recaptcha_public_key' => 'Öffentlicher Schlüssel:',
      'admin_recaptcha_public_key_info' => '\'Public Key\', der Ihnen von {$re_link} zur Verfügung gestellt wurde.',
      'admin_spambot_recaptcha' => 'reCaptcha-Einstellungen',
      'admin_spambot_recaptcha_info' => 'reCaptcha ist ein Online-Service zur Spam-Abwehr. Sie brauchen einen persönlichen Schlüssel um diesen Service in Anspruch zu nehmen (siehe unten). Ein Bild mit zwei Wörtern wird den Nutzern angezeigt. Diese Überprüfung unterstützt Audio und erlaubt blinden Benutzern sich ebenfalls zu registrieren.',
      'admin_spambot_veriword' => 'VeriWord-Einstellungen',
      'admin_spambot_veriword_info' => 'VeriWord ist der Standard-Spam-Schutz von Viscacha. Ein Bild, bestehend aus mehreren Zeichen in variierenden Schriften/Farben, wird dem Nutzer angezeigt. Das Verhalten und Aussehen des Bildes wird von diversen Optionen bestimmt, die unten angepasst werden können.',
    ),
    'language' =>
    array (
      'admin_activate_pdf_topics' => NULL,
      'admin_activate_pdf_topics_info' => NULL,
      'admin_activate_spambot_at_guests' => 'Spam-Bot-Protection at Posting of guests',
      'admin_activate_spambot_registration' => 'Spam-Bot-Protection at Registration:',
      'admin_compress_pdf' => NULL,
      'admin_compress_pdf_info' => NULL,
      'admin_dyeing_letters_captcha' => 'Dyeing letters:',
      'admin_examples_captcha' => NULL,
      'admin_examples_captchaimg_textcodes' => NULL,
      'admin_examples_textcodes' => NULL,
      'admin_e_all' => 'All errors, warnings and notices',
      'admin_e_error' => 'Only fatal error messages',
      'admin_e_notice' => NULL,
      'admin_e_strict' => NULL,
      'admin_e_warning' => NULL,
      'admin_file_typ_captcha' => 'File type:',
      'admin_ftp_directory_does_not_exist' => 'Directory "{$ftp_path}" does not exist!',
      'admin_image_height_captcha' => 'Standard image height:',
      'admin_image_width_captcha' => 'Standard image width:',
      'admin_php_standard' => 'Standardeinstellung von PHP nutzen',
      'admin_pic_quality_captcha' => 'Quality of the picture:',
      'admin_save_php_errors_info' => 'This option should be activated only for debugging purposes!',
      'admin_setting_posts_topics_info' => 'Minimum lengths and maximum lengths, editing and other settings on posts and topics.',
      'admin_spambot_posting' => NULL,
      'admin_spambot_registration' => NULL,
      'admin_topics_posts_pdf' => NULL,
      'admin_wave_filter_captcha' => 'Use "wave"-filter on Spam-Bot-Protection-Picture:',
      'admin_captcha_type0' => 'Not active',
      'admin_captcha_type1' => 'Standard (VeriWord)',
      'admin_captcha_type2' => 'reCaptcha',
      'admin_enable_change_vote' => 'Allow to change one\'s mind of a survey',
      'admin_enable_change_vote_info' => 'This option allows members to change their vote in surveys again.',
      'admin_e_none' => 'Keine Fehlermeldungen ausgeben',
      'admin_recaptcha_private_key' => 'Private Key:',
      'admin_recaptcha_private_key_info' => 'Private key provided to you by {$re_link}.',
      'admin_recaptcha_public_key' => 'Public Key:',
      'admin_recaptcha_public_key_info' => 'Public key provided to you by {$re_link}.',
      'admin_spambot_recaptcha' => 'reCaptcha Settings',
      'admin_spambot_recaptcha_info' => 'reCaptcha is an online service to protect against spam. You\'ll need to get your personal keys to use this service (see below). An image containing two words will be shown to the user. This verification supports audio, allowing blind users to register.',
      'admin_spambot_veriword' => 'VeriWord Settings',
      'admin_spambot_veriword_info' => 'VeriWord is the default spam protection of Viscacha. An image consisting of letters in varying fonts/colors will be shown to the user. The appearance of this image is dictated by several options that you may control below.',
    ),
  ),
  'classes' =>
  array (
    'language_de' =>
    array (
      'mailer_signing' => 'Signierungsfehler: ',
    ),
    'language' =>
    array (
      'mailer_signing' => 'Signing Error: ',
    ),
  ),
  'global' =>
  array (
    'language_de' =>
    array (
      'pdf_attachments' => NULL,
      'pdf_attachments_filesize' => NULL,
      'pdf_footer' => NULL,
      'pdf_postinfo' => NULL,
      'pdf_vote' => NULL,
      'pdf_vote_result' => NULL,
      'pdf_vote_voters' => NULL,
      'register_veriword' => 'Bitte geben Sie zum Spamschutz die Zeichenfolge aus dem Bild ein.',
      'showtopic_options_pdf' => NULL,
      'error_no_forum_permissions' => 'Sie haben leider keine Berechtigung die versteckten Foren anzusehen. Bitte melden Sie sich mit den nötigen Rechten an!',
      'vote_change_option' => 'Votum ändern',
      'vote_go_form' => 'Votum abgeben',
    ),
    'language' =>
    array (
      'pdf_attachments' => NULL,
      'pdf_attachments_filesize' => NULL,
      'pdf_footer' => NULL,
      'pdf_postinfo' => NULL,
      'pdf_vote' => NULL,
      'pdf_vote_result' => NULL,
      'pdf_vote_voters' => NULL,
      'register_veriword' => 'Please enter the chard in the image. This is to avoid spam.',
      'showtopic_options_pdf' => NULL,
      'error_no_forum_permissions' => 'Sorry, you haven\'t got the permission to view the hidden forums. Please log in with the necessary permissions!',
      'vote_change_option' => 'Change vote',
      'vote_go_form' => 'Cast your vote',
    ),
  ),
  'modules' =>
  array (
    'language_de' =>
    array (
      'ps_nav_package_overview' => NULL,
      'ps_nav_title' => NULL,
    ),
    'language' =>
    array (
      'ps_nav_package_overview' => NULL,
      'ps_nav_title' => NULL,
    ),
  ),
  'settings' =>
  array (
    'language_de' =>
    array (
      'compatible_version' => '0.8 RC6',
    ),
    'language' =>
    array (
      'compatible_version' => '0.8 RC6',
    ),
  ),
  'wwo' =>
  array (
    'language_de' =>
    array (
      'wwo_pdf' => NULL,
      'wwo_pdf_fallback' => NULL,
    ),
    'language' =>
    array (
      'wwo_pdf' => NULL,
      'wwo_pdf_fallback' => NULL,
    ),
  ),
);
updateLanguageFiles($ini);
echo "- Language files updated.<br />";

// MySQL
$file = 'install/package/update/db/db_changes.sql';
//$file = 'package/'.$package.'/db/db_changes.sql';
$sql = file_get_contents($file);
$sql = str_ireplace('{:=DBPREFIX=:}', $db->prefix(), $sql);
$db->multi_query($sql);
echo "- Database tables updated.<br />";

// Old files
$filesystem->unlink('admin/html/images/captcha.jpg');
$filesystem->unlink('admin/html/images/captcha2.jpg');
$filesystem->unlink("pdf.php");
$filesystem->unlink('templates/editor/wysiwyg-color.js');
$filesystem->rmdirr("classes/fpdf/");
$filesystem->rmdirr("temp/pdfimages");
$filesystem->rmdirr("templates/editor/popups");
$dir = dir('images');
while (false !== ($entry = $dir->read())) {
	$path = "{$dir->path}/{$entry}";
	if (is_dir($path) && is_id($entry)) {
		$filesystem->unlink("{$path}/pdf.gif");
	}
}
echo "- Old files deleted.<br />";

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