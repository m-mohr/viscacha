<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// TR: MultiLangAdmin
$lang->group("admin/language");

include('classes/class.phpconfig.php');
require('admin/lib/function.language.php');

$langbase = array(
	'global' => $lang->phrase('admin_lang_global_phrases'),
	'bbcodes' => $lang->phrase('admin_lang_bbcode_phrases'),
	'modules' => $lang->phrase('admin_lang_plugin_phrases'),
	'javascript' => $lang->phrase('admin_lang_js_phrases'),
	'wwo' => $lang->phrase('admin_lang_wwo_phrases'),
	'classes' => $lang->phrase('admin_lang_classes_phrases'),
	'custom' => $lang->phrase('admin_lang_custom_phrases')
);

$mailbase = array(
	'admin_confirmed' => $lang->phrase('admin_lang_note_member_activated_by_admin'),
	'digest_d' => $lang->phrase('admin_lang_notification_new_posts_daily'),
	'digest_s' => $lang->phrase('admin_lang_notification_new_posts_immediate'),
	'digest_w' => $lang->phrase('admin_lang_notification_new_posts_weekly'),
	'mass_topic_moved' => $lang->phrase('admin_lang_notification_mass_topic_moved'),
	'newpm' => $lang->phrase('admin_lang_notification_new_pm'),
	'new_member' => $lang->phrase('admin_lang_notification_new_members'),
	'new_topic' => $lang->phrase('admin_lang_notification_new_topics'),
	'new_reply' => $lang->phrase('admin_lang_notification_new_replies'),
	'pwremind' => $lang->phrase('admin_lang_confirm_new_pw'),
	'pwremind2' => $lang->phrase('admin_lang_mail_contains_new_pw'),
	'register_00' => $lang->phrase('admin_lang_confirm_reg_mail_by_admin'),
	'register_01' => $lang->phrase('admin_lang_confirm_reg_by_admin'),
	'register_10' => $lang->phrase('admin_lang_confirm_reg_mail'),
	'report_post' => $lang->phrase('admin_lang_notification_reported_post'),
	'topic_moved' => $lang->phrase('admin_lang_notification_topic_moved')
);

($code = $plugins->load('admin_language_jobs')) ? eval($code) : null;

if ($job == 'manage') {
	echo head();
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language');
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="6">
    <span style="float: right;">
    <a class="button" href="admin.php?action=packages&amp;job=browser&amp;type=<?php echo IMPTYPE_LANGUAGE; ?>"><?php echo $lang->phrase('admin_lang_browse_langpacks'); ?></a>
	<a class="button" href="admin.php?action=language&amp;job=import" target="Main"><?php echo $lang->phrase('admin_lang_import_lang'); ?></a>
	<a class="button" href="admin.php?action=language&amp;job=phrase" target="Main"><?php echo $lang->phrase('admin_lang_phrase_manager'); ?></a>
	</span>
	<?php echo $lang->phrase('admin_lang_lang_files'); ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" width="18%"><?php echo $lang->phrase('admin_lang_lang'); ?></td>
   <td class="ubox" width="6%"><?php echo $lang->phrase('admin_lang_code'); ?></td>
   <td class="ubox" width="37%"><?php echo $lang->phrase('admin_lang_description'); ?></td>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_lang_published'); ?></td>
   <td class="ubox" width="34%"><?php echo $lang->phrase('admin_lang_action'); ?></td>
  </tr>
  <?php
  while ($row = $db->fetch_assoc($result)) {
  	$settings = $gpc->prepare(return_array('settings', $row['id']));
  ?>
  <tr>
   <td class="mbox"><?php echo $row['language'].iif($config['langdir'] == $row['id'], '<br /><span class="stext">'.$lang->phrase('admin_lang_default').'</span>'); ?></td>
   <td class="mbox"><?php echo $settings['lang_code'].iif(!empty($settings['country_code']), '_'.$settings['country_code']); ?></td>
   <td class="mbox stext"><?php echo $row['detail']; ?></td>
   <td class="mbox" align="center"><?php echo noki($row['publicuse'], ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=language&job=ajax_publicuse&id='.$row['id'].'\')"'); ?></td>
   <td class="mbox">
   <a class="button" href="admin.php?action=language&amp;job=lang_edit&amp;id=<?php echo $row['id']; ?>"><?php echo $lang->phrase('admin_lang_edit'); ?></a>
   <a class="button" href="admin.php?action=language&amp;job=lang_copy&amp;id=<?php echo $row['id']; ?>" title="<?php echo $lang->phrase('admin_lang_copy_langpack_translate_later'); ?>"><?php echo $lang->phrase('admin_lang_copy'); ?></a>
   <a class="button" href="admin.php?action=language&amp;job=export&amp;id=<?php echo $row['id']; ?>">Export</a>
   <a class="button" href="admin.php?action=language&amp;job=lang_delete&amp;id=<?php echo $row['id']; ?>"><?php echo $lang->phrase('admin_lang_delete'); ?></a>
   <?php if ($row['publicuse'] == 1 && $config['langdir'] != $row['id']) { ?>
   <a class="button" href="admin.php?action=language&amp;job=lang_default&amp;id=<?php echo $row['id']; ?>"><?php echo $lang->phrase('admin_lang_set_default'); ?></a>
   <?php } ?>
   <a class="button" href="forum.php?language=<?php echo $row['id']; ?>" target="_blank"><?php echo $lang->phrase('admin_lang_view'); ?></a>
   </td>
  </tr>
  <?php } ?>
 </table>
	<?php
	echo foot();
}
elseif ($job == 'ajax_publicuse') {
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT publicuse FROM {$db->pre}language WHERE id = '{$id}' LIMIT 1");
	$use = $db->fetch_assoc($result);
	if ($use['publicuse'] == 1) {
		if ($id == $config['langdir']) {
			die($lang->phrase('admin_lang_cannot_unpublish_until_defined_other_lang'));
		}
		$result = $db->query("SELECT * FROM {$db->pre}language WHERE publicuse = '1'");
		if ($db->num_rows($result) == 1) {
			die($lang->phrase('admin_lang_cannot_unpublish_because_no_other_lang'));
		}
	}
	$use = invert($use['publicuse']);
	$db->query("UPDATE {$db->pre}language SET publicuse = '{$use}' WHERE id = '{$id}' LIMIT 1");
	$delobj = $scache->load('loadlanguage');
	$delobj->delete();
	die(strval($use));
}
elseif ($job == 'import') {
	echo head();
	$file = $gpc->get('file', str);
	$result = $db->query('SELECT id, language FROM '.$db->pre.'language ORDER BY language');
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=language&job=import2">
 <table class="border" cellpadding="4" cellspacing="0" border="0">
  <tr><td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_import_langpack'); ?></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_lang_either_upload_file'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_allowed_file_types'); ?></span></td>
  <td class="mbox"><input type="file" name="upload" size="40" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_lang_or_select_from_server'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_viscacha_root_path'); ?> <?php echo $config['fpath']; ?></span></td>
  <td class="mbox"><input type="text" name="server" value="<?php echo $file; ?>" size="50" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_lang_overwrite_lang'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_leave_blank_create_new_lang'); ?></span></td>
  <td class="mbox"><select name="overwrite">
    <option value="0"><?php echo $lang->phrase('admin_lang_create_new_lang'); ?></option>
   <?php while ($row = $db->fetch_assoc($result)) { ?>
    <option value="<?php echo $row['id']; ?>"><?php echo $row['language']; ?></option>
   <?php } ?>
  </select></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_lang_delete_file_after_import'); ?></td>
  <td class="mbox"><input type="checkbox" name="delete" value="1" checked="checked" /></td></tr>
  <tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" value="<?php echo $lang->phrase('admin_lang_send'); ?>" /></td></tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'import2') {

	$overwrite = $gpc->get('overwrite', int);
	$server = $gpc->get('server', none);
	$del = $gpc->get('delete', int);
	$inserterrors = array();

	if (!empty($_FILES['upload']['name'])) {
		$filesize = 1024*1024;
		$filetypes = array('zip');
		$dir = realpath('temp/').DIRECTORY_SEPARATOR;

		$insertuploads = array();
		require("classes/class.upload.php");

		$my_uploader = new uploader();
		$my_uploader->max_filesize($filesize);
		$my_uploader->file_types($filetypes);
		$my_uploader->set_path($dir);
		if ($my_uploader->upload('upload')) {
			if ($my_uploader->save_file()) {
				$file = $dir.$my_uploader->fileinfo('filename');
				if (!file_exists($file)) {
					$inserterrors[] = $lang->phrase('admin_lang_file_not_exist');
				}
			}
		}
		if ($my_uploader->upload_failed()) {
			array_push($inserterrors,$my_uploader->get_error());
		}

	}
	elseif (file_exists($server)) {
		$ext = get_extension($server);
		if ($ext == 'zip') {
			$file = $server;
		}
		else {
			$inserterrors[] = $lang->phrase('admin_lang_file_not_zip');
		}
	}
	else {
		$inserterrors[] = $lang->phrase('admin_lang_no_valid_file');
	}
	echo head();
	if (count($inserterrors) > 0) {
		error('admin.php?action=language&job=import', $inserterrors);
	}

	$tempdir = 'temp/'.md5(microtime()).'/';

	require_once('classes/class.zip.php');
	$archive = new PclZip($file);
	$failure = $archive->extract($tempdir);
	if ($failure < 1) {
		unset($archive);
		if ($del == 1) {
			$filesystem->unlink($file);
		}
		$filesystem->rmdirr($tempdir);
		error('admin.php?action=language&job=import', $lang->phrase('admin_lang_zip_not_readable_or_empty'));
	}

	$inserted = false;
	if ($overwrite == 0) {
		$db->query("INSERT INTO {$db->pre}language (language, detail) VALUES ($lang->phrase('admin_lang_new_langpack'), $lang->phrase('admin_lang_langpack_import_error'))");
		$inserted = true;
		$overwrite = $db->insert_id();
	}
	$newdir = "language/{$overwrite}/";

	$filesystem->mover($tempdir, $newdir);
	if (is_dir($tempdir)) {
		$filesystem->rmdirr($tempdir);
	}

	$info = return_array('settings', $overwrite);
	if (isset($info['lang_name'])) {
		$db->query("UPDATE {$db->pre}language SET language = '{$info['lang_name']}', detail = '{$info['lang_description']}' WHERE id = '{$overwrite}' LIMIT 1");
		unset($archive);
		if ($del == 1) {
			$filesystem->unlink($file);
		}
		$delobj = $scache->load('loadlanguage');
		$delobj->delete();
		ok('admin.php?action=language&job=manage', 'Languagepack import successful.');
	}
	else {
		if ($inserted) {
			$db->query("DELETE FROM {$db->pre}language WHERE id = '{$overwrite}' LIMIT 1");
		}
		unset($archive);
		if ($del == 1) {
			$filesystem->unlink($file);
		}
		error('admin.php?action=language&job=import', $lang->phrase('admin_lang_could_not_import_langpack'));
	}
}
elseif ($job == 'export') {
	$id = $gpc->get('id', int);

	$result = $db->query('SELECT language, detail FROM '.$db->pre.'language WHERE id = "'.$id.'" LIMIT 1');
	$row = $db->fetch_assoc($result);

	$file = convert2adress($row['language'].'-v'.str_replace(' ', '', $config['version'])).'.zip';
	$dir = "language/{$id}/";
	$tempdir = "temp/";

	require_once('classes/class.zip.php');
	$archive = new PclZip($tempdir.$file);
	$v_list = $archive->create($dir, PCLZIP_OPT_REMOVE_PATH, $dir, PCLZIP_OPT_COMMENT, "{$row['language']}\n\n{$row['detail']}\n\nVersion: {$config['version']}");
	if ($v_list == 0) {
		echo head();
		unset($archive);
		if ($del > 0) {
			$filesystem->unlink($tempdir.$file);
		}
		error('admin.php?action=language&job=manage', $archive->errorInfo(true));
	}
	else {
		viscacha_header('Content-Type: application/zip');
		viscacha_header('Content-Disposition: attachment; filename="'.$file.'"');
		viscacha_header('Content-Length: '.filesize($tempdir.$file));
		readfile($tempdir.$file);
		unset($archive);
		$filesystem->unlink($tempdir.$file);
	}
}
elseif ($job == 'lang_copy') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=language&job=lang_copy2&id=<?php echo $gpc->get('id', int); ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="6"><?php echo $lang->phrase('admin_lang_copy_lang_file'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_lang_name_new_langpack'); ?></td>
   <td class="mbox" width="60%"><input type="text" name="name" size="60" /></td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_lang_description_for_langpack'); ?></td>
   <td class="mbox" width="60%"><textarea name="desc" rows="3" cols="70"></textarea></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_lang_copy'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'lang_copy2') {
	echo head();
	$id = $gpc->get('id', int);
	$name = $gpc->get('name', str);
	$desc = $gpc->get('desc', str);
	$db->query("INSERT INTO {$db->pre}language (language, detail) VALUES ('{$name}', '{$desc}')");
	$newid = $db->insert_id();
	$filesystem->mkdir("language/{$newid}/", 0777);
	$filesystem->copyr("language/{$id}/", "language/{$newid}/");
	$delobj = $scache->load('loadlanguage');
	$delobj->delete();
	ok('admin.php?action=language&job=manage', $lang->phrase('admin_lang_langpack_copied'));
}
elseif ($job == 'lang_delete') {
	echo head();
	$id = $gpc->get('id', int);
	?>
	<table class="border" border="0" cellspacing="0" cellpadding="4">
	<tr><td class="obox"><?php echo $lang->phrase('admin_lang_delete_langpack'); ?></td></tr>
	<tr><td class="mbox">
	<p align="center"><?php echo $lang->phrase('admin_lang_really_delete_langpack'); ?></p>
	<p align="center">
	<a href="admin.php?action=language&job=lang_delete2&id=<?php echo $id; ?>"><img border="0" alt="" src="admin/html/images/yes.gif"> <?php echo $lang->phrase('admin_lang_yes'); ?></a>
	&nbsp&nbsp;&nbsp;&nbsp&nbsp;&nbsp;
	<a href="javascript: history.back(-1);"><img border="0" alt="" src="admin/html/images/no.gif"> <?php echo $lang->phrase('admin_lang_no'); ?></a>
	</p>
	</td></tr>
	</table>
	<?php
	echo foot();
}
elseif ($job == 'lang_delete2') {
	echo head();
	$id = $gpc->get('id', int);

	$result = $db->query("SELECT id FROM {$db->pre}language WHERE id != '{$id}' AND publicuse = '1' LIMIT 1");
	if ($db->num_rows($result) != 1) {
		error('admin.php?action=language&job=manage', $lang->phrase('admin_lang_cannot_delete_last_installed_lang'));
	}

	$result = $db->query("SELECT publicuse FROM {$db->pre}language WHERE id = '{$id}' LIMIT 1");
	$info = $db->fetch_assoc($result);

	if ($info['publicuse'] == 1) {
		error('admin.php?action=language&job=manage', $lang->phrase('admin_lang_cannot_unpublish_lang_until_unpublish'));
	}

	$db->query("DELETE FROM {$db->pre}language WHERE id = '{$id}' LIMIT 1");

	if ($db->affected_rows() == 1) {
		$filesystem->rmdirr("language/{$id}/");
		$delobj = $scache->load('loadlanguage');
		$delobj->delete();
		ok('admin.php?action=language&job=manage', $lang->phrase('admin_lang_langpack_deleted'));
	}
	else {
		error('admin.php?action=language&job=manage', $lang->phrase('admin_lang_langpack_could_not_deleted'));
	}
}
elseif ($job == 'lang_settings') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT language, detail, publicuse FROM {$db->pre}language WHERE id = '{$id}' LIMIT 1");
	$data = $gpc->prepare($db->fetch_assoc($result));
	$settings = $gpc->prepare(return_array('settings', $id));
	if (empty($settings['html_read_direction'])) {
		$settings['html_read_direction'] = 'ltr';
	}
	if (empty($settings['rss_language'])) {
		$settings['rss_language'] = 'en';
	}
	if (empty($settings['lang_code'])) {
		$settings['lang_code'] = 'en';
	}
	if (empty($settings['country_code'])) {
		$settings['country_code'] = '';
	}

	$charsets = array();
	$charsets['ISO-8859-1'] = $lang->phrase('admin_charset_iso88591');
	$charsets['ISO-8859-15'] = $lang->phrase('admin_charset_iso889515');
//	$charsets['UTF-8'] = $lang->phrase('admin_charset_utf8');
	$charsets['cp1252'] = $lang->phrase('admin_charset_cp1252');
	if (version_compare(PHP_VERSION, '4.3.2', '>=')) {
		$charsets['cp866'] = $lang->phrase('admin_charset_cp866');
		$charsets['cp1251'] = $lang->phrase('admin_charset_cp1251');
		$charsets['KOI8-R'] = $lang->phrase('admin_charset_koi8r');
	}
	$charsets['BIG5'] = $lang->phrase('admin_charset_big5');
	$charsets['GB2312'] = $lang->phrase('admin_charset_gb2312');
	$charsets['BIG5-HKSCS'] = $lang->phrase('admin_charset_big5hkscs');
	$charsets['Shift_JIS'] = $lang->phrase('admin_charset_shiftjis');
	$charsets['EUC-JP'] = $lang->phrase('admin_charset_eucjp');
	$settings['charset'] = isset($settings['charset']) ? $settings['charset'] : $config['asia_charset'];

	$rss = file2array('admin/data/rss.txt');
	$languages = file2array('admin/data/iso639.txt');
	$country = file2array('admin/data/iso3166.txt');
	?>
<script language="JavaScript">
<!--
function errordefault(box) {
	alert($lang->phrase('admin_lang_cannot_unpublish_until_defined_other_lang'));
	box.checked = true;
	return false;
}
-->
</script>
<form name="form" method="post" action="admin.php?action=language&job=lang_settings2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_edit_lang_file_settings'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_langpack_name'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="language" size="50" value="<?php echo $data['language']; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_langpack_description'); ?></td>
   <td class="mbox" width="50%"><textarea name="desc" rows="3" cols="60"><?php echo $data['detail']; ?></textarea></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_compatible_with'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_your_current_viscacha'); ?> <?php echo $config['version']; ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="compatible_version" size="20" value="<?php echo isset($settings['compatible_version']) ? $settings['compatible_version'] : $config['version']; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_langpack_public_usable'); ?></td>
   <td class="mbox" width="50%"><input<?php echo iif($config['langdir'] == $id, ' onclick="errordefault(this)"'); ?> type="checkbox" name="use" value="1"<?php echo iif($data['publicuse'] == 1, ' checked="checked"'); ?> /></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_lang_number_formatting'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_thousand_seperator'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="thousandssep" size="2" value="<?php echo isset($settings['thousandssep']) ? $settings['thousandssep'] : ','; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_decimal_seperator'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="decpoint" size="2" value="<?php echo isset($settings['decpoint']) ? $settings['decpoint'] : '.'; ?>" /></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_lang_country_lang_settings'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_langcode'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_specify_lang_for_pack'); ?></span></td>
   <td class="mbox" width="50%">
   <select name="lang_code">
   <?php foreach ($languages as $key => $val) { ?>
   <option value="<?php echo $key; ?>"<?php echo iif($settings['lang_code'] == $key, ' selected="selected"'); ?>><?php echo $val; ?></option>
   <?php } ?>
   </select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_country'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_country_code'); ?></span></td>
   <td class="mbox" width="50%">
   <select name="country_code">
   <option value=""<?php echo iif($settings['country_code'] == '', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_lang_no_specific_country'); ?></option>
   <?php foreach ($country as $key => $val) { ?>
   <option value="<?php echo $key; ?>"<?php echo iif($settings['country_code'] == $key, ' selected="selected"'); ?>><?php echo $val; ?></option>
   <?php } ?>
   </select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_writing_direction'); ?></td>
   <td class="mbox" width="50%">
   <select name="html_read_direction">
   <option value="ltr"<?php echo iif($settings['html_read_direction'] == 'ltr', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_lang_ltr'); ?></option>
   <option value="rtl"<?php echo iif($settings['html_read_direction'] == 'rtl', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_lang_rtl'); ?></option>
   </select>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_rss_lang'); ?></td>
   <td class="mbox" width="50%">
   <select name="rss_language">
   <?php foreach ($rss as $key => $val) { ?>
   <option value="<?php echo $key; ?>"<?php echo iif($settings['rss_language'] == $key, ' selected="selected"'); ?>><?php echo $val; ?></option>
   <?php } ?>
   </select>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_charset'); ?><br /><span class="stest"><?php echo $lang->phrase('admin_character_set_incomming_data_converted_info'); ?></span></td>
   <td class="mbox" width="50%">
	<select name="charset">
	   <?php foreach ($charsets as $key => $opt) { ?>
	   <option value="<?php echo $key; ?>"<?php echo iif($settings['charset'] == $key, ' selected="selected"'); ?>><?php echo $key.': '.$opt; ?></option>
	   <?php } ?>
	</select>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_lang_date_and_time'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_contributions_format'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_contributions_format_info'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="dformat1" value="<?php echo isset($settings['dformat1']) ?  $settings['dformat1'] : 'd.m.Y, H:i'; ?>" size="20"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_regdate_format'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_last_activity_format_info'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="dformat2" value="<?php echo isset($settings['dformat2']) ? $settings['dformat2'] : 'd.m.Y, H:i'; ?>" size="20"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_last_activity_format'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_last_activity_format_info'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="dformat3" value="<?php echo isset($settings['dformat3']) ? $settings['dformat3'] : 'H:i'; ?>" size="20"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_today_yesterday_format'); ?><br><span class="stext"><?php echo $lang->phrase('admin_lang_today_yesterday_format_info'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="dformat4" value="<?php echo isset($settings['dformat4']) ? $settings['dformat4'] : 'H:i'; ?>" size="20"></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_lang_form_save'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'lang_settings2') {
	echo head();
	$id = $gpc->get('id', int);
	$use = $gpc->get('use', int);
	$detail = $gpc->get('desc', str);
	$language = $gpc->get('language', str);
	$error = '';

	$result = $db->query("SELECT publicuse FROM {$db->pre}language WHERE id = '{$id}' LIMIT 1");
	$puse = $db->fetch_assoc($result);
	if ($puse['publicuse'] == 1 && $use == 0) {
		if ($id == $config['langdir']) {
			$error .= $lang->phrase('admin_lang_but_cannot_unpublish_until_defined_another_lang');
			$use = 1;
		}
		$result = $db->query("SELECT * FROM {$db->pre}language WHERE publicuse = '1'");
		if ($db->num_rows($result) == 1) {
			$error .= $lang->phrase('admin_lang_but_cannot_unpublish_because_no_other_lang_published');
			$use = 1;
		}
	}

	$lc = $gpc->get('lang_code', none);
	$cc = $gpc->get('country_code', none);
	if (!empty($cc)) {
		$scd = $lc.'_'.$cc;
	}
	else {
		$scd = $lc;
	}

	$db->query("UPDATE {$db->pre}language SET publicuse = '{$use}', language = '{$language}', detail = '{$detail}' WHERE id = '{$id}' LIMIT 1");

	$c = new manageconfig();
	$c->getdata("language/{$id}/settings.lng.php", 'lang');
	$c->updateconfig('html_read_direction', str);
	$c->updateconfig('rss_language', str);
	$c->updateconfig('spellcheck_dict', str, $scd);
	$c->updateconfig('lang_code', str);
	$c->updateconfig('country_code', str);
	$c->updateconfig('thousandssep', str);
	$c->updateconfig('decpoint', str);
	$c->updateconfig('lang_name', str, $language);
	$c->updateconfig('lang_description', str, $detail);
	$c->updateconfig('compatible_version', str);
	$c->updateconfig('dformat1',str);
	$c->updateconfig('dformat2',str);
	$c->updateconfig('dformat3',str);
	$c->updateconfig('dformat4',str);
	$c->updateconfig('charset',str);
	$c->savedata();

	if ($config['langdir'] == $id) {
		$c = new manageconfig();
		$c->getdata();
		$c->updateconfig('asia_charset', str, $gpc->get('charset', str));
		$c->savedata();
	}

	$delobj = $scache->load('loadlanguage');
	$delobj->delete();

	ok('admin.php?action=language&job=lang_edit&id='.$id, $lang->phrase('admin_lang_changes_successful').$error.'.');
}
elseif ($job == 'lang_ignore') {
	echo head();
	$id = $gpc->get('id', int);
	$file = "language/{$id}/words/search.inc.php";
	if (!file_exists($file)) {
		$ignore = '';
	}
	else {
		$ignore = file_get_contents($file);
	}
	?>
<form name="form" method="post" action="admin.php?action=language&job=lang_ignore2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_edit_lang_file_ignored_search_keys'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="40%" valign="top"><?php echo $lang->phrase('admin_lang_ignored_search_keys_desc'); ?></td>
   <td class="mbox" width="60%" align="center">
   <textarea name="ignore" rows="25" cols="50"><?php echo $ignore; ?></textarea>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_lang_form_save'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'lang_ignore2') {
	echo head();

	$id = $gpc->get('id', int);
	$ignore = $gpc->get('ignore', none);
	$lines = preg_split('`[\n\r]+`', trim($ignore)) ;
	$lines = array_map('trim', $lines);
	$lines = array_map('strtolower', $lines);
	$lines = array_unique($lines);
	sort($lines);
	if (!is_dir("language/{$id}/words/")) {
		$filesystem->mkdir("language/{$id}/words/", 0777);
	}
	$filesystem->file_put_contents("language/{$id}/words/search.inc.php", implode("\n", $lines));

	ok('admin.php?action=language&job=lang_edit&id='.$id);
}
elseif ($job == 'lang_rules') {
	echo head();
	$id = $gpc->get('id', int);
	$rules = $gpc->get('rules', arr_str);
	$delete = $gpc->get('delete', arr_int);
	$c = $gpc->get('c', int);
	$file = "language/{$id}/words/rules.inc.php";
	if (!file_exists($file)) {
		$rules = array();
	}
	else {
		$rules = file($file);
	}
	$i = 1;
	?>
<form name="form" method="post" action="admin.php?action=language&job=lang_rules2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr>
   <td class="obox"><?php echo $lang->phrase('admin_lang_edit_lang_file_behavior'); ?></td>
  </tr>
  <tr>
   <td class="ubox"><?php echo $lang->phrase('admin_lang_existing_rules'); ?></td>
  </tr>
  <tr>
   <td class="mbox">
   <ol>
   <?php foreach ($rules as $rule) { ?>
    <li><input type="text" name="rules[<?php echo $i; ?>]" size="110" value="<?php echo $gpc->prepare($rule); ?>" />&nbsp;&nbsp;<input type="checkbox" name="delete[<?php echo $i; ?>]" value="1"> <?php echo $lang->phrase('admin_lang_delete'); ?></li>
   <?php $i++; } if (count($rules) == 0) { ?>
   	<li><em><?php echo $lang->phrase('admin_lang_no_rule_created'); ?></em></li>
   <?php } ?>
   </ol>
   </td>
  </tr>
  <tr>
   <td class="ubox" align="center"><?php echo $lang->phrase('admin_lang_add_new_rules'); ?></td>
  </tr>
  <tr>
   <td class="mbox">
  <?php if ($c > 0) { ?>
   <ol start="<?php echo $i; ?>">
   <?php for($ii=1;$ii<=$c;$ii++) { ?>
   <li><input type="text" name="rules[<?php echo $ii+$i; ?>]" size="110" value="" /></li>
   <?php } ?>
   </ol>
  <?php } else { ?>
   <?php echo $lang->phrase('admin_lang_add_x'); ?> <input type="text" name="c" size="3" value="0" /> <?php echo $lang->phrase('admin_lang_new_rules_after_saving'); ?>
  <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_lang_form_save'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'lang_rules2') {
	echo head();

	$id = $gpc->get('id', int);
	$rules = $gpc->get('rules', arr_str);
	$delete = $gpc->get('delete', arr_int);
	$c = $gpc->get('c', int);
	$newrules = array();
	foreach ($rules as $rid => $rule) {
		if (!isset($delete[$rid]) && !empty($rule)) {
			$newrules[$rid] = $rule;
		}
	}
	ksort($newrules);
	if (!is_dir("language/{$id}/words/")) {
		$filesystem->mkdir("language/{$id}/words/", 0777);
	}
	$filesystem->file_put_contents("language/{$id}/words/rules.inc.php", implode("\n", $newrules));

	if ($c > 0) {
		ok('admin.php?action=language&job=lang_rules&c='.$c.'&id='.$id, $lang->phrase('admin_lang_settings_saved_can_add_new_rules'));
	}
	else {
		ok('admin.php?action=language&job=lang_edit&id='.$id);
	}
}
elseif ($job == 'lang_txttpl') {
	echo head();
	$id = $gpc->get('id', int);
	$file = $gpc->get('file', str);
	$path = "language/{$id}/texts/{$file}.php";
	if (!file_exists($path)) {
		error('admin.php?action=language&job=lang_edit&id='.$id, $lang->phrase('admin_lang_file_x_does_not_exist'));
	}
	$tpl = file_get_contents($path);
	?>
<form name="form" method="post" action="admin.php?action=language&job=lang_txttpl2&id=<?php echo $id; ?>&file=<?php echo $file; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr>
   <td class="obox"><?php echo $lang->phrase('admin_lang_edit_langfile_text_templates'); ?></td>
  </tr>
  <tr>
   <td class="ubox"><?php echo $lang->phrase('admin_lang_vars_help'); ?></td>
  </tr>
  <tr>
   <td class="mbox" align="center">
   <textarea name="tpl" rows="15" cols="120"><?php echo $tpl; ?></textarea>
   </td>
  </tr>
  <tr>
   <td class="ubox" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_lang_form_save'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'lang_txttpl2') {
	echo head();

	$id = $gpc->get('id', int);
	$file = $gpc->get('file', str);
	if (!is_dir("language/{$id}/texts/")) {
		$filesystem->mkdir("language/{$id}/texts/", 0777);
	}
	$path = "language/{$id}/texts/{$file}.php";
	if (!file_exists($path)) {
		error('admin.php?action=language&job=lang_edit&id='.$id, $lang->phrase('admin_lang_file_x_does_not_exist'));
	}
	$tpl = $gpc->get('tpl', none);
	$filesystem->file_put_contents($path, $tpl);

	ok('admin.php?action=language&job=lang_edit&id='.$id);
}
elseif ($job == 'lang_emailtpl') {
	echo head();
	$id = $gpc->get('id', int);
	$file = $gpc->get('file', str);
	$path = "language/{$id}/mails/{$file}.php";
	if (!file_exists($path)) {
		error('admin.php?action=language&job=lang_edit&id='.$id, $lang->phrase('admin_lang_file_x_does_not_exist'));
	}
	$xml = file_get_contents($path);
    preg_match("|<title>(.+?)</title>.*?<comment>(.+?)</comment>|is", $xml, $tpl);
	?>
<form name="form" method="post" action="admin.php?action=language&job=lang_emailtpl2&id=<?php echo $id; ?>&file=<?php echo $file; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_edit_langfile_mail_texts'); ?><?php echo $file; ?></td>
  </tr>
  <tr>
   <td class="ubox" width="20%"><?php echo $lang->phrase('admin_lang_help'); ?></td>
   <td class="ubox" width="80%"><?php echo $lang->phrase('admin_lang_vars_help'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="20%"><?php echo $lang->phrase('admin_lang_subject'); ?></td>
   <td class="mbox" width="80%"><input type="text" name="title" value="<?php echo $gpc->prepare($tpl[1]); ?>" size="80"></td>
  </tr>
  <tr>
   <td class="mbox" width="20%"><?php echo $lang->phrase('admin_lang_message'); ?></td>
   <td class="mbox" width="80%"><textarea name="tpl" rows="10" cols="80"><?php echo $tpl[2]; ?></textarea></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_lang_form_save'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'lang_emailtpl2') {
	echo head();

	$id = $gpc->get('id', int);
	$file = $gpc->get('file', str);
	if (!is_dir("language/{$id}/mails/")) {
		$filesystem->mkdir("language/{$id}/mails/", 0777);
	}
	$path = "language/{$id}/mails/{$file}.php";
	if (!file_exists($path)) {
		error('admin.php?action=language&job=lang_edit&id='.$id, $lang->phrase('admin_lang_file_x_does_not_exist'));
	}
	$tpl = $gpc->get('tpl', none);
	$title = $gpc->get('title', none);

	$xml = "<mail>\n\t<title>{$title}</title>\n\t<comment>{$tpl}</comment>\n</mail>";

	$filesystem->file_put_contents($path, $xml);

	ok('admin.php?action=language&job=lang_edit&id='.$id);
}
elseif ($job == 'lang_array') {
	echo head(' onload="initTranslateDetails()"');
	$id = $gpc->get('id', int);
	$page = $gpc->get('page', int, 1);
	$file = $gpc->get('file', str);
	$lng = return_array($file, $id);
	$lng = array_map('htmlspecialchars', $lng);
	$lng = array_map('nl2whitespace', $lng);
	ksort($lng);
	$lng = array_chunk($lng, 50, true);
	if (isset($lng[$page-1]) == false) {
		error('admin.php?action=language&job=lang_edit&id='.$id, $lang->phrase('admin_lang_page_not_found'));
	}
	$pages = count($lng);
	$pages_html = "Seiten ({$pages}):";
	// Ersetzen durch Buchstaben (?) -> [A] [B] ...
	for($i=1;$i<=$pages;$i++) {
   		$pages_html .= ' ['.iif($i == $page, "<strong>{$i}</strong>", "<a href='admin.php?action=language&job=lang_array&id={$id}&file={$file}&page={$i}'>{$i}</a>").']';
	}

	?>
<form name="form" method="post" action="admin.php?action=language&job=lang_array2&id=<?php echo $id; ?>&file=<?php echo $file; ?>&page=<?php echo $page; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_edit_langfile'); ?> &raquo; <?php echo isset($langbase[$file]) ? $langbase[$file] : ucfirst($file); ?></td>
  </tr>
  <tr>
   <td class="mbox stext" colspan="2"><?php echo $lang->phrase('admin_lang_vars_help'); ?></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><?php echo $pages_html; ?></td>
  </tr>
  <?php
  foreach ($lng[$page-1] as $key => $value) {
  	$word = explode('_', $key);
  	$word = array_map('ucfirst', $word);
  	$word = implode(' ', $word);
  ?>
  <tr>
   <td class="mbox" width="50%"><img name="c" id="img_lang_<?php echo $key; ?>" src="admin/html/images/plus.gif" alt=""> <?php echo $word; ?>
   <div id="part_lang_<?php echo $key; ?>" class="stext">
   <strong><?php echo $lang->phrase('admin_lang_variable'); ?></strong> <code>$lang->phrase('<?php echo $key; ?>')</code><br />
   <strong><?php echo $lang->phrase('admin_lang_original'); ?></strong> <?php echo $value; ?>
   </div>
   </td>
   <td class="mbox" width="50%"><input type="text" name="lang_<?php echo $key; ?>" size="70" value="<?php echo $value ?>" /></td>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="2" align="right">
   <?php echo $pages_html; ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_lang_form_save'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'lang_array2') {
	echo head();
	$id = $gpc->get('id', int);
	$file = $gpc->get('file', str);
	$page = $gpc->get('page', int);

	$keys = array_keys($_REQUEST);
	$sent = array();
	foreach ($keys as $key) {
		if (substr($key, 0, 5) == 'lang_') {
			$sent[$key] = substr($key, 5, strlen($key));
		}
	}

	$c = new manageconfig();
	$c->getdata("language/{$id}/{$file}.lng.php", 'lang');
	foreach ($sent as $post => $key) {
		$c->updateconfig($key, str, $_REQUEST[$post]);
	}
	$c->savedata();

	ok('admin.php?action=language&job=lang_array&id='.$id.'&file='.$file.'&page='.$page);
}
elseif ($job == 'lang_com') {
	echo head(' onload="initTranslateDetails()"');
	$id = $gpc->get('id', int);
	$cid = $gpc->get('cid', int);
	$file = $gpc->get('file', str);
	$files = array();

	$dir = "language/{$id}/modules/{$cid}/";
	if (is_dir($dir)) {
	   if ($dh = opendir($dir)) {
	       while (($fileh = readdir($dh)) !== false) {
				$ext = substr($fileh, -8, 8);
				if ($ext == '.lng.php') {
					$files[] = substr($fileh, 0, strlen($fileh)-8);
				}
	       }
	       closedir($dh);
		}
	}
	if (count($files) == 0) {
		error('admin.php?action=language&job=lang_edit&id='.$id, $lang->phrase('admin_lang_component_without_langfile'));
	}
	if (count($files) > 0 && empty($file)) {
		$file = current($files);
	}

	$lng = return_array('modules/'.$cid.'/'.$file, $id);
	$lng = array_map('htmlspecialchars', $lng);
	$lng = array_map('nl2whitespace', $lng);
	ksort($lng);
	sort($files);
	$pages_html = $lang->phrase('admin_lang_files');
	foreach ($files as $page) {
	  		$pages_html .= ' ['.iif($file == $page, "<strong>{$page}</strong>", "<a href='admin.php?action=language&job=lang_com&id={$id}&file={$page}&cid={$cid}'>{$page}</a>").']';
	}

	?>
	<form name="form" method="post" action="admin.php?action=language&job=lang_com2&id=<?php echo $id; ?>&file=<?php echo $file; ?>&cid=<?php echo $cid; ?>">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_edit_langfile_package_id'); ?> &raquo; <?php echo ucfirst($file); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox stext" colspan="2"><?php echo $lang->phrase('admin_lang_vars_help'); ?></td>
	  </tr>
	  <?php if (count($files) > 1) { ?>
	  <tr>
	   <td class="ubox" colspan="2">
	   <?php echo $pages_html; ?>
	   </td>
	  </tr>
	  <?php
	  }
	  foreach ($lng as $key => $value) {
	  	$word = explode('_', $key);
	  	$word = array_map('ucfirst', $word);
	  	$word = implode(' ', $word);
	  ?>
	  <tr>
	   <td class="mbox" width="50%"><img name="c" id="img_lang_<?php echo $key; ?>" src="admin/html/images/plus.gif" alt=""> <?php echo $word; ?>
	   <div id="part_lang_<?php echo $key; ?>" class="stext">
	   <strong><?php echo $lang->phrase('admin_lang_variable'); ?></strong> <code>$lang->phrase('<?php echo $key; ?>')</code><br />
	   <strong><?php echo $lang->phrase('admin_lang_original'); ?></strong> <?php echo $value; ?>
	   </div>
	   </td>
	   <td class="mbox" width="50%"><input type="text" name="lang_<?php echo $key; ?>" size="70" value="<?php echo $value ?>" /></td>
	  </tr>
	  <?php } if (count($files) > 1) { ?>
	  <tr>
	   <td class="ubox" colspan="2">
	   <?php echo $pages_html; ?>
	   </td>
	  </tr>
	  <?php } ?>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_lang_form_save'); ?>" /></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'lang_com2') {
	echo head();
	$id = $gpc->get('id', int);
	$file = $gpc->get('file', str);
	$cid = $gpc->get('cid', int);

	$keys = array_keys($_REQUEST);
	$sent = array();
	foreach ($keys as $key) {
		if (substr($key, 0, 5) == 'lang_') {
			$sent[$key] = substr($key, 5, strlen($key));
		}
	}

	$c = new manageconfig();
	$c->getdata("language/{$id}/modules/{$cid}/{$file}.lng.php", 'lang');
	foreach ($sent as $post => $key) {
		$c->updateconfig($key, str, $gpc->prepare($_REQUEST[$post]));
	}
	$c->savedata();

	ok('admin.php?action=language&job=lang_com&id='.$id.'&file='.$file.'&cid='.$cid);
}
elseif ($job == 'lang_default') {
	echo head();
	$id = $gpc->get('id', int);

	$c = new manageconfig();
	$c->getdata();
	$c->updateconfig('langdir', int, $id);
	$data = return_array('settings', $id);
	$c->updateconfig('asia_charset', str, $data['charset']);
	$c->savedata();

	$delobj = $scache->load('loadlanguage');
	$delobj->delete();

	ok('admin.php?action=language&job=manage');
}
elseif ($job == 'lang_edit') {
	echo head();
	$id = $gpc->get('id', int);
	$myini = new INI();
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="4"><?php echo $lang->phrase('admin_lang_edit_langfile_title'); ?></td>
  </tr>
  <tr>
   <td class="ubox" width="20%"><?php echo $lang->phrase('admin_lang_general'); ?></td>
   <td class="ubox" width="30%"><?php echo $lang->phrase('admin_lang_core_phrases'); ?></td>
   <td class="ubox" width="20%"><?php echo $lang->phrase('admin_lang_packages_phrases'); ?></td>
   <td class="ubox" width="30%"><?php echo $lang->phrase('admin_lang_mails'); ?></td>
  </tr>
  <tr class="mbox inlinelist">
   <td valign="top">
   <ul>
   <li><a href="admin.php?action=language&job=lang_settings&id=<?php echo $id; ?>"><?php echo $lang->phrase('admin_lang_settings'); ?></a></li>
   <li><a href="admin.php?action=language&job=lang_rules&id=<?php echo $id; ?>"><?php echo $lang->phrase('admin_lang_terms_of_behaviour'); ?></a></li>
   <li><strong><?php echo $lang->phrase('admin_lang_text_templates'); ?></strong>
	  <ul>
	   	<li><a href="admin.php?action=language&job=lang_txttpl&id=<?php echo $id; ?>&file=moved"><?php echo $lang->phrase('admin_lang_topic_moved'); ?></a></li>
	   	<li><a href="admin.php?action=language&job=lang_txttpl&id=<?php echo $id; ?>&file=notice"><?php echo $lang->phrase('admin_lang_copied_posts'); ?></a></li>
	  </ul>
   </li>
   <li><a href="admin.php?action=language&job=lang_ignore&id=<?php echo $id; ?>"><?php echo $lang->phrase('admin_lang_ignored_search_keys'); ?></a></li>
   </ul>
   </td>
   <td valign="top">
   <ul>
   <?php
	$dir = 'language/'.$id.'/';
	$files = array();
	$d = dir($dir);
	while (FALSE !== ($entry = $d->read())) {
		if (substr($entry, -8, 8) == '.lng.php') {
			$basename = substr($entry, 0, strlen($entry)-8);
			if ($basename != 'settings' && $basename != 'modules') {
				$name = preg_replace("/[^\w\d]/i", " ", $basename);
				$name = ucfirst($name);
			?>
			<li>
				<a href="admin.php?action=language&job=lang_array&id=<?php echo $id; ?>&file=<?php echo $basename; ?>"><?php echo $name; ?></a>
				<?php echo isset($langbase[$basename]) ? "<br /><span class=\"stext\">{$langbase[$basename]}</span>" : ''; ?>
			</li>
			<?php
			}
	   	}
   	}
	$d->close();
	?>
	<li><strong><?php echo $lang->phrase('admin_lang_admin_control_panel'); ?></strong>
   <ul>
   <?php
	$dir = 'language/'.$id.'/admin/';
	$files = array();
	$d = dir($dir);
	while (FALSE !== ($entry = $d->read())) {
		if (substr($entry, -8, 8) == '.lng.php') {
			$basename = substr($entry, 0, strlen($entry)-8);
			$name = preg_replace("/[^\w\d]/i", " ", $basename);
			$name = ucfirst($name);
			?>
			<li>
				<a href="admin.php?action=language&job=lang_array&id=<?php echo $id; ?>&file=admin%2F<?php echo $basename; ?>"><?php echo $name; ?></a>
			</li>
			<?php
	   	}
   	}
	$d->close();
	?>
   </ul>
	</li>
   </ul>
   </td>
   <td valign="top">
   <ul>
   <li><a href="admin.php?action=language&job=lang_array&id=<?php echo $id; ?>&file=modules"><?php echo $lang->phrase('admin_lang_plugins'); ?></a></li>
   <li><strong><?php echo $lang->phrase('admin_lang_components'); ?></strong>
	   <ul>
	   <?php
		$result = $db->query("
			SELECT c.id, c.package, p.title
			FROM {$db->pre}component AS c
				LEFT JOIN {$db->pre}packages AS p ON c.package = p.id
			ORDER BY p.title
		");
		while ($row = $db->fetch_assoc($result)) {
			$c = $myini->read('modules/'.$row['package'].'/component.ini');
		?>
	   	<li>
	   		<a href="admin.php?action=language&job=lang_com&id=<?php echo $id; ?>&cid=<?php echo $row['package']; ?>"><?php echo $c['info']['title']; ?></a><br />
	   		<span class="stext"><?php echo $lang->phrase('admin_lang_package'); ?> <?php echo $row['title']; ?></span>
	   	</li>
	   <?php } ?>
	   </ul>
   </li>
   </ul>
   </td>
   <td valign="top">
   <ul>
    <?php
	$path = "language/{$id}/mails/";
    $i = 0;
	$result = opendir($path);
	while (($file = readdir($result)) !== false) {
		$info = pathinfo($path.$file);
		if ($info['extension'] == 'php') {
			$n = substr($info['basename'], 0, -(strlen($info['extension']) + ($info['extension'] == '' ? 0 : 1)));
			$i++;
		?>
	   	<li>
	   	<a href="admin.php?action=language&job=lang_emailtpl&id=<?php echo $id; ?>&file=<?php echo $n; ?>"><?php echo $n; ?></a>
	   	<?php echo isset($mailbase[$n]) ? "<br /><span class=\"stext\">{$mailbase[$n]}</span>" : ''; ?>
	   	</li>
	    <?php
	    }
    }
    closedir($result);
    ?>
   </ul>
   </td>
  </tr>
 </table>
	<?php
	echo foot();
}
elseif ($job == 'phrase') {
	echo head();
	$cache = array();
	$diff = array();
	$complete = array();
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language');
	while($row = $db->fetch_assoc($result)) {
		$cache[$row['id']] = $row;
		$diff[$row['id']] = dir_array('language/'.$row['id'], 'language/'.$row['id']);
		$complete = array_merge($complete, array_diff($diff[$row['id']], $complete) );
	}
	usort($complete, 'sort_dirlist');
	$width = floor(75/count($cache));
	?>
<form name="form" method="post" action="admin.php?action=language&job=phrase_delete">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="<?php echo count($cache)+1; ?>">
   <span style="float: right;"><a class="button" href="admin.php?action=language&job=phrase_add_lngfile"><?php echo $lang->phrase('admin_lang_add_new_langfile'); ?></a> <a class="button" href="admin.php?action=language&job=phrase_add_mailfile"><?php echo $lang->phrase('admin_lang_add_new_mail_file'); ?></a></span>
   <?php echo $lang->phrase('admin_lang_phrase_manager'); ?></td>
  </tr>
  <tr>
   <td class="mmbox" width="25%">&nbsp;</td>
   <?php foreach ($cache as $row) { ?>
   <td class="mmbox" align="center" width="<?php echo $width; ?>%"><?php echo $row['language']; ?> <a class="button" href="admin.php?action=language&job=lang_edit&id=<?php echo $row['id']; ?>"><?php echo $lang->phrase('admin_lang_edit'); ?></a></td>
   <?php } ?>
  </tr>
  <?php foreach ($complete as $file) { ?>
  <tr>
   <td class="mmbox" nowrap="nowrap">
   <input type="checkbox" name="delete[]" value="<?php echo urlencode(base64_encode($file)); ?>">
   <?php if (substr($file, -8, 8) == '.lng.php') { ?>
   <a href="admin.php?action=language&job=phrase_file&file=<?php echo urlencode(base64_encode($file)); ?>"><?php echo $file; ?></a>
   <?php } else { echo $file; } ?>
   </td>
   <?php
   foreach ($cache as $row) {
   	$status = in_array($file, $diff[$row['id']]);
   ?>
   <td class="mbox" align="center"><?php echo noki($status).iif(!$status, ' <a class="button" href="admin.php?action=language&job=phrase_copy&file='.urlencode(base64_encode($file)).'&id='.$row['id'].'">'.$lang->phrase('admin_lang_add').'</a>'); ?></td>
   <?php } ?>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" align="center" colspan="<?php echo count($cache)+1; ?>"><input type="submit" value="<?php echo $lang->phrase('admin_lang_delete_selected_items'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'phrase_file_find') {
	$file = $gpc->get('file', none);
	echo head();
?>
<form name="form" method="get" action="admin.php">
<input type="hidden" name="action" value="language" />
<input type="hidden" name="job" value="phrase_file" />
<input type="hidden" name="show" value="search" />
<input type="hidden" name="file" value="<?php echo $file; ?>" />
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_phrase_amanger_find_phrase'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_lang_keyword'); ?></td>
   <td class="mbox" width="60%"><input type="text" name="key" size="40" /></td>
  <tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_lang_search_in'); ?><br /><?php echo $lang->phrase('admin_lang_search_in_info'); ?></td>
   <td class="mbox" width="60%">
   	<input type="checkbox" name="keys" value="1" checked="checked" /> <?php echo $lang->phrase('admin_lang_keys'); ?><br />
   	<input type="checkbox" name="values" value="1" checked="checked" /> <?php echo $lang->phrase('admin_lang_values'); ?>
   </td>
  <tr>
   <td class="ubox" align="center" colspan="2"><input type="submit" value="<?php echo $lang->phrase('admin_lang_find'); ?>"></td>
  </tr>
 </table>
</form>
<?php
	echo foot();
}
elseif ($job == 'phrase_file') {
	echo head();
	$file = $gpc->get('file', none);
	$encfile = base64_decode($file);
	$group = substr($encfile, 0, strlen($encfile)-8);
	$page = $gpc->get('page', int, 1);
	$show = $gpc->get('show', str);
	$cache = array();
	$diff = array();
	$complete = array();
	$lang_data = array();
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language');
	while($row = $db->fetch_assoc($result)) {
		$cache[$row['id']] = $row;
		$lang_data[$row['id']] = return_array($group, $row['id']);
		$diff[$row['id']] = array_keys($lang_data[$row['id']]);
		$complete = array_merge($complete, array_diff($diff[$row['id']], $complete) );
	}
	$search = $gpc->get('key', none);
	$keys = $gpc->get('keys', int);
	$values = $gpc->get('values', int);
	if ($show == 'diff') {
		$same = call_user_func_array('array_intersect', $diff);
		$complete = array_diff($complete, $same);
	}
	elseif ($show == 'search') {
		if (strlen($search) < 3) {
			error('admin.php?action=language&job=phrase_file_find', $lang->phrase('admin_lang_keyword_too_short'));
		}
		$ids = array_keys($cache);
		foreach ($complete as $index => $key) {
			$found = false;
			if ($keys == 1 && stristr($key, $search) !== false) {
				$found = true;
			}
			if ($values == 1 && $found == false) {
				foreach ($ids as $id) {
					if (isset($lang_data[$id][$key]) && stristr($lang_data[$id][$key], $search) !== false) {
						$found = true;
					}
				}
			}
			if ($found == false) {
				unset($complete[$index]);
			}
		}
	}
	sort($complete);
	$width = floor(75/count($cache));
	if ($show == 'diff' || $show == 'search') {
		$perpage = 100;
	}
	else {
		$perpage = 50;
	}
	$data = array_chunk($complete, $perpage);
	if (!isset($data[$page-1])) {
		$page = 1;
	}
	$pages = $anz = count($data);
	$pages_html = $lang->phrase('admin_pages');
	// ToDo: Ersetzen durch Buchstaben (?) -> [A] [B] ...
	for($i=1;$i<=$pages;$i++) {
   		$pages_html .= ' ['.iif($i == $page, "<strong>{$i}</strong>", "<a href='admin.php?action=language&amp;job=phrase_file&amp;file={$file}&amp;page={$i}&amp;show={$show}&amp;key={$search}&amp;keys={$keys}&amp;values={$values}'>{$i}</a>").']';
	}
	?>
<form name="form" method="post" action="admin.php?action=language&job=phrase_file_delete&file=<?php echo $file; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="<?php echo count($cache)+1; ?>">
   <span style="float: right;">
   <?php if ($show == 'diff') { ?>
    <a class="button" href="admin.php?action=language&job=phrase_file&file=<?php echo $file; ?>"><?php echo $lang->phrase('admin_lang_show_all_phrases'); ?></a>
   <?php } else { ?>
    <a class="button" href="admin.php?action=language&job=phrase_file&file=<?php echo $file; ?>&show=diff"><?php echo $lang->phrase('admin_lang_show_only_differences'); ?></a>
   <?php } ?>
    <a class="button" href="admin.php?action=language&job=phrase_file_find&file=<?php echo $file; ?>"><?php echo $lang->phrase('admin_lang_find_phrases'); ?></a>
    <a class="button" href="admin.php?action=language&job=phrase_add&file=<?php echo $file; ?>"><?php echo $lang->phrase('admin_lang_add_new_phrase'); ?></a>
   </span>
   <?php echo $lang->phrase('admin_lang_phrase_manager'); ?> &raquo; <?php echo $encfile; ?></td>
  </tr>
  <?php if (count($cache) < 2 && $show == 'diff') { ?>
  <tr>
   <td class="mbox" colspan="<?php echo count($cache)+1; ?>"><?php echo $lang->phrase('admin_lang_not_enough_lang_found_to_compare'); ?></td>
  </tr>
  <?php } elseif (!isset($data[$page-1]) || count($data[$page-1]) == 0) { ?>
  <tr>
   <td class="mbox" colspan="<?php echo count($cache)+1; ?>"><?php echo $lang->phrase('admin_lang_no_phrases_saved_yet'); ?> <a class="button" href="admin.php?action=language&job=phrase_add&file=<?php echo $file; ?>"><?php echo $lang->phrase('admin_lang_add_new_phrase'); ?></a></td>
  </tr>
  <?php } else { ?>
  <tr>
   <td class="ubox" colspan="<?php echo count($cache)+1; ?>"><?php echo $pages_html; ?></td>
  </tr>
  <tr>
   <td class="mmbox" width="25%">&nbsp;</td>
   <?php foreach ($cache as $row) { ?>
   <td class="mmbox" align="center" width="<?php echo $width; ?>%"><?php echo $row['language']; ?></td>
   <?php } ?>
  </tr>
  <?php foreach ($data[$page-1] as $phrase) { ?>
  <tr>
   <td class="mmbox" nowrap="nowrap"><input type="checkbox" name="delete[]" value="<?php echo $phrase; ?>">&nbsp;<a class="button" href="admin.php?action=language&job=phrase_file_edit&file=<?php echo $file; ?>&phrase=<?php echo $phrase; ?>"><?php echo $lang->phrase('admin_lang_edit'); ?></a>&nbsp;<?php echo $phrase; ?></td>
   <?php
   foreach ($cache as $row) {
   	$status = in_array($phrase, $diff[$row['id']]);
   ?>
   <td class="mbox" align="center"><?php echo noki($status).iif(!$status, ' <a class="button" href="admin.php?action=language&job=phrase_file_copy&file='.$file.'&id='.$row['id'].'&phrase='.$phrase.'">'.$lang->phrase('admin_lang_add').'</a>'); ?></td>
   <?php } ?>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="<?php echo count($cache)+1; ?>"><?php echo $pages_html; ?></td>
  </tr>
  <tr>
   <td class="ubox" align="center" colspan="<?php echo count($cache)+1; ?>"><input type="submit" value="<?php echo $lang->phrase('admin_lang_delete_selected_phrases'); ?>"></td>
  </tr>
  <?php } ?>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'phrase_copy') {
	$language = $gpc->get('id', int);
	$file = $gpc->get('file', none);
	$encfile = base64_decode($file);
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language');
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=language&amp;job=phrase_copy2&amp;file=<?php echo $file; ?>&amp;id=<?php echo $language; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_phrase_manager'); ?> &raquo; <?php echo $encfile; ?> &raquo; <?php echo $lang->phrase('admin_lang_copy_file'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_template_directory'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_lang_directory_from_where_file_should_be_copied'); ?></span></td>
   <td class="mbox" width="50%"><select name="dir">
	<?php
	while($row = $db->fetch_assoc($result)) {
		if (file_exists('language/'.$row['id'].'/'.$encfile)) {
	?>
   	<option value="<?php echo $row['id']; ?>"><?php echo $row['language']; ?> (ID: <?php echo $row['id']; ?>)</option>
	<?php } } ?>
   </select></td>
  </tr>
  <tr>
   <td class="ubox" align="center" colspan="2"><input type="submit" value="<?php echo $lang->phrase('admin_lang_copy_file'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'phrase_copy2') {
	$dest = $gpc->get('id', int);
	$file = base64_decode($gpc->get('file', none));
	$source = $gpc->get('dir', int);
	echo head();
	createParentDir($file, 'language/'.$dest);
	$dest = 'language/'.$dest.'/'.$file;
	$source = 'language/'.$source.'/'.$file;
	if (file_exists($dest)) {
		error('admin.php?action=language&job=phrase', $lang->phrase('admin_lang_file_already_exists_not_overwritten'));
	}
	if (!file_exists($source)) {
		error('admin.php?action=language&job=phrase', $lang->phrase('admin_lang_file_does_not_exist'));
	}
	$filesystem->copy($source, $dest);
	if (file_exists($dest)) {
		ok('admin.php?action=language&job=phrase', $lang->phrase('admin_lang_file_successfully_copied'));
	}
	else {
		error('admin.php?action=language&job=phrase', $lang->phrase('admin_lang_file_could_not_be_copied'));
	}
}
elseif ($job == 'phrase_file_edit') {
	echo head();

	$phrase = $gpc->get('phrase', none);
	$file = $gpc->get('file', none);
	$encfile = base64_decode($file);
	$basefile = substr($encfile, 0, strlen($encfile)-8);

	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language');
	$cache = array();
	while($row = $db->fetch_assoc($result)) {
	  	$phrases = return_array($basefile, $row['id']);
	  	if (!isset($phrases[$phrase])) {
	  		$row['phrase'] = '';
	  	}
	  	else {
	  		$row['phrase'] = $phrases[$phrase];
	  	}
	  	unset($phrases);
	  	$cache[$row['id']] = $row;
	}
	?>
<form name="form" method="post" action="admin.php?action=language&job=phrase_file_edit2&file=<?php echo $file; ?>">
 <table class="border" border="0" cellspacing="0" cellpediting="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_phrase_manager_edit_new_to_package'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_varname'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_lang_varname_can_only_contain_letters_etc'); ?></span></td>
   <td class="mbox" width="50%"><input type="hidden" name="varname" size="50" value="<?php echo $phrase; ?>" /><code><?php echo $phrase; ?></code></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_text'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_lang_default_used_lang'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="text" size="50" value="<?php echo htmlspecialchars(nl2whitespace($cache[$config['langdir']]['phrase'])); ?>" /></td>
  </tr>
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_translations'); ?></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><ul>
	<li><?php echo $lang->phrase('admin_lang_when_editing_a_custom_phrase'); ?></li>
	<li><?php echo $lang->phrase('admin_lang_if_translation_box_left_blank'); ?></li>
   </ul></td>
  </tr>
  <?php foreach ($cache as $row) { ?>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_translation'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_optional_html_not_recommended'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="langt[<?php echo $row['id']; ?>]" size="50" value="<?php echo htmlspecialchars(nl2whitespace($row['phrase'])); ?>" /></td>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_lang_form_save'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'phrase_file_edit2') {
	echo head();

	$file = $gpc->get('file', none);
	$encfile = base64_decode($file);
	$varname = $gpc->get('varname', none);
	$text = $gpc->get('text', none);
	$language = $gpc->get('langt', none);

	$c = new manageconfig();
	foreach ($language as $id => $t) {
		if (empty($t)) {
			$t = $text;
		}
		$c->getdata("language/{$id}/{$encfile}", 'lang');
		$c->updateconfig($varname, str, $t);
		$c->savedata();
	}

	ok('admin.php?action=language&job=phrase_file&file='.$file);
}
elseif ($job == 'phrase_file_copy') {
	$language = $gpc->get('id', int);
	$file = $gpc->get('file', none);
	$encfile = base64_decode($file);
	$phrase = $gpc->get('phrase', str);
	$result = $db->query("SELECT * FROM {$db->pre}language WHERE id != '{$language}' ORDER BY language");
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=language&job=phrase_file_copy2&phrase=<?php echo $phrase; ?>&file=<?php echo $file; ?>&id=<?php echo $language; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_phrase_manager'); ?> &raquo; <?php echo $encfile; ?> &raquo; <?php echo $lang->phrase('admin_lang_copy_phrase'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_used_as_original'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_lang_used_as_original_info'); ?></span></td>
   <td class="mbox" width="50%"><select name="dir">
	<?php
	$basefile = substr($encfile, 0, strlen($encfile)-8);
	while($row = $db->fetch_assoc($result)) {
		if (file_exists('language/'.$row['id'].'/'.$encfile)) {
			$langarr = return_array($basefile, $row['id']);
			if (isset($langarr[$phrase])) {
	?>
   	<option value="<?php echo $row['id']; ?>"><?php echo $row['language']; ?> (ID: <?php echo $row['id']; ?>)</option>
	<?php } } } ?>
   </select></td>
  </tr>
  <tr>
   <td class="ubox" align="center" colspan="2"><input type="submit" value="<?php echo $lang->phrase('admin_lang_copy_phrase'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'phrase_file_copy2') {
	echo head();
	$dest = $gpc->get('id', int);
	$source = $gpc->get('dir', int);
	$file = $gpc->get('file', none);
	$encfile = base64_decode($file);
	$phrase = $gpc->get('phrase', str);
	$destpath = 'language/'.$dest.'/'.$encfile;
	$c = new manageconfig();
	if (!file_exists($destpath)) {
		createParentDir($encfile, 'language/'.$dest);
		$c->createfile($destpath, 'lang');
	}
	$encfile = substr($encfile, 0, strlen($encfile)-8);
	$langarr = return_array($encfile, $source);
	if (!isset($langarr[$phrase])) {
		error('admin.php?action=language&job=phrase_file&file='.$file, $lang->phrase('admin_lang_phrase_not_found'));
	}
	$c->getdata($destpath, 'lang');
	$c->updateconfig($phrase, str, $langarr[$phrase]);
	$c->savedata();
	ok('admin.php?action=language&job=phrase_file&file='.$file, $lang->phrase('admin_lang_phrase_copied'));
}
elseif ($job == 'phrase_delete') {
	echo head();
	$delete = $gpc->get('delete', arr_none);
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language');
	while($row = $db->fetch_assoc($result)) {
		foreach ($delete as $base) {
			$base = base64_decode($base);
			$path = "language/{$row['id']}/{$base}";
			if (file_exists($path)) {
				$filesystem->unlink($path);
			}
		}
	}
	ok('admin.php?action=language&job=phrase', $lang->phrase('admin_lang_deleted_selected_files'));
}
elseif ($job == 'phrase_file_delete') {
	echo head();
	$delete = $gpc->get('delete', arr_str);
	$file = $gpc->get('file', none);
	$encfile = base64_decode($file);
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language');
	$c = new manageconfig();
	while($row = $db->fetch_assoc($result)) {
		$path = "language/{$row['id']}/{$encfile}";
		if (file_exists($path)) {
			$c->getdata($path, 'lang');
			foreach ($delete as $phrase) {
				$c->delete($phrase);
			}
			$c->savedata();
		}
	}
	ok('admin.php?action=language&job=phrase_file&file='.$file, $lang->phrase('admin_lang_selected_phrases_deleted'));
}
elseif ($job == 'phrase_add_lngfile') {
	echo head();
	$myini = new INI();
	?>
<form name="form" method="post" action="admin.php?action=language&job=phrase_add_lngfile2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_phrase_manager_add_new_langfile'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_filename'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_lang_filename_can_only_contain_letters_etc'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="file" size="50" />.lng.php</td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_directory'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_lang_directory_where_file_is_saved'); ?></span></td>
   <td class="mbox" width="50%"><select name="dir">
    <option value="<?php echo base64_decode(''); ?>">language/ID/ <?php echo $lang->phrase('admin_lang_main_dir_langfiles'); ?></option>
   <?php
   $result = $db->query("SELECT * FROM {$db->pre}component ORDER BY active DESC");
   while ($row = $db->fetch_assoc($result)) {
	$cfg = $myini->read('modules/'.$row['package'].'/component.ini');
	$row = array_merge($row, $cfg);
   ?>
    <option value="<?php echo base64_decode('modules/'.$row['packages'].'/'); ?>">language/ID/modules/<?php echo $row['packages']; ?> (<?php echo $lang->phrase('admin_lang_component').$row['info']['title']; ?>)</option>
   <?php } ?>
   </select></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_lang_create'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'phrase_add_lngfile2') {
	$dir = base64_decode($gpc->get('dir', none));
	$file = $gpc->get('file', none);
	$c = new manageconfig();
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language');
	while($row = $db->fetch_assoc($result)) {
		$c->createfile("language/{$row['id']}/{$dir}{$file}.lng.php", 'lang');
	}
	echo head();
	ok('admin.php?action=language&job=phrase_file&file='.urlencode(base64_encode("{$dir}{$file}.lng.php")), $lang->phrase('admin_lang_langfile_created'));
}
elseif ($job == 'phrase_add_mailfile') {
	echo head();
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language');
	?>
<form name="form" method="post" action="admin.php?action=language&job=phrase_add_mailfile2">
 <table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_add_new_mail_file'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="30%"><?php echo $lang->phrase('admin_lang_filename'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_lang_filename_can_only_contain_letters_etc'); ?></span></td>
   <td class="mbox" width="70%"><input type="text" name="file" size="80">.php</td>
  </tr>
  <tr>
   <td class="mmbox" width="30%"><?php echo $lang->phrase('admin_lang_help'); ?></td>
   <td class="mmbox stext" width="70%"><?php echo $lang->phrase('admin_lang_vars_help'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="30%"><?php echo $lang->phrase('admin_lang_title'); ?></td>
   <td class="mbox" width="70%"><input type="text" name="title" size="80" value="<?php echo $lang->phrase('admin_lang_your_title'); ?>"></td>
  </tr>
  <tr>
   <td class="mbox" width="30%"><?php echo $lang->phrase('admin_lang_text'); ?></td>
   <td class="mbox" width="70%"><textarea name="tpl" rows="8" cols="80"><?php echo $lang->phrase('admin_lang_mailfile_text'); ?></textarea></td>
  </tr>
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_translations'); ?></td>
  </tr>
  <tr>
   <td class="mmbox" colspan="2">
   <ul>
	<li><?php echo $lang->phrase('admin_lang_mailfile_help1'); ?></li>
	<li><?php echo $lang->phrase('admin_lang_mailfile_help2'); ?></li>
   </ul>
   </td>
  </tr>
  <?php while($row = $db->fetch_assoc($result)) { ?>
  <tr>
   <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_lang_translation'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="30%"><?php echo $lang->phrase('admin_lang_title'); ?></td>
   <td class="mbox" width="70%"><input type="text" name="titles[<?php echo $row['id']; ?>]" size="80"></td>
  </tr>
  <tr>
   <td class="mbox" width="30%"><?php echo $lang->phrase('admin_lang_text'); ?></td>
   <td class="mbox" width="70%"><textarea name="texts[<?php echo $row['id']; ?>]" rows="5" cols="80"></textarea></td>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_lang_create'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'phrase_add_mailfile2') {
	echo head();

	$file = $gpc->get('file', str);
	$tpl = $gpc->get('tpl', none);
	$title = $gpc->get('title', none);
	$titles = $gpc->get('titles', none);
	$texts = $gpc->get('texts', none);

	foreach ($titles as $id => $tit) {
		if (!empty($texts[$id])) {
			$tex = $texts[$id];
		}
		else {
			$tex = $tpl;
		}
		if (empty($tit)) {
			$tit = $title;
		}
		$xml = "<mail>\n\t<title>{$tit}</title>\n\t<comment>{$tex}</comment>\n</mail>";
		$filesystem->file_put_contents("language/{$id}/mails/{$file}.php", $xml);
	}

	ok('admin.php?action=language&job=phrase');
}
elseif ($job == 'phrase_add') {
	echo head();
	$file = base64_decode($gpc->get('file', none));
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language');
	?>
<form name="form" method="post" action="admin.php?action=language&job=phrase_add2&file=<?php echo $gpc->get('file', none); ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_phrase_manager'); ?> &raquo; <?php echo $file; ?> &raquo; <?php echo $lang->phrase('admin_lang_add_new_phrase'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_varname'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_lang_varname_can_only_contain_letters_etc'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="varname" size="50" value="" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_text'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="text" size="50" /></td>
  </tr>
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_translations'); ?></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><ul>
	<li><?php echo $lang->phrase('admin_lang_when_inserting_a_custom_phrase'); ?></li>
	<li><?php echo $lang->phrase('admin_lang_if_translation_box_left_blank'); ?></li>
   </ul></td>
  </tr>
  <?php
  while($row = $db->fetch_assoc($result)) {
  if (file_exists('language/'.$row['id'].'/'.$file)) {
  ?>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_translation'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_optional_html_not_recommended'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="langt[<?php echo $row['id']; ?>]" size="50" /></td>
  </tr>
  <?php } } ?>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_lang_form_save'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'phrase_add2') {
	echo head();
	$varname = $gpc->get('varname', none);
	$text = $gpc->get('text', none);
	$file = base64_decode($gpc->get('file', none));
	$language = $gpc->get('langt', none);

	$c = new manageconfig();
	foreach ($language as $id => $t) {
		if (empty($t)) {
			$t = $text;
		}
		$c->getdata("language/{$id}/{$file}", 'lang');
		$c->updateconfig($varname, str, $t);
		$c->savedata();
	}

	ok('admin.php?action=language&job=phrase_file&file='.urlencode(base64_encode($file)));
}
else {
	viscacha_header('Location: admin.php?action=language&job=manage');
}
?>