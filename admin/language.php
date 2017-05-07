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
	$result = $db->execute('SELECT * FROM '.$db->pre.'language ORDER BY language');
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="6">
	<span style="float: right;">
	<a class="button" href="admin.php?action=packages&amp;job=browser&amp;type=<?php echo IMPTYPE_LANGUAGE; ?>"><?php echo $lang->phrase('admin_lang_browse_langpacks'); ?></a>
	<a class="button" href="admin.php?action=language&amp;job=import" target="Main"><?php echo $lang->phrase('admin_lang_import_lang'); ?></a>
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
  while ($row = $result->fetch()) {
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
	$publicuse = $db->fetchOne("SELECT publicuse FROM {$db->pre}language WHERE id = '{$id}'");
	if ($publicuse == 1) {
		if ($id == $config['langdir']) {
			die($lang->phrase('admin_lang_cannot_unpublish_until_defined_other_lang'));
		}
		$result = $db->fetchOne("SELECT id FROM {$db->pre}language WHERE publicuse = '1'");
		if ($result) {
			die($lang->phrase('admin_lang_cannot_unpublish_because_no_other_lang'));
		}
	}
	$publicuse = invert($publicuse);
	$db->execute("UPDATE {$db->pre}language SET publicuse = '{$publicuse}' WHERE id = '{$id}' LIMIT 1");
	$scache->load('loadlanguage')->delete();
	die(strval($use));
}
elseif ($job == 'import') {
	echo head();
	$file = $gpc->get('file', path);
	$result = $db->execute('SELECT id, language FROM '.$db->pre.'language ORDER BY language');
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=language&job=import2">
 <table class="border" cellpadding="4" cellspacing="0" border="0">
  <tr><td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_import_lang'); ?></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_lang_either_upload_file'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_allowed_file_types'); ?></span></td>
  <td class="mbox"><input type="file" name="upload" size="40" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_lang_or_select_from_server'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_viscacha_root_path'); ?> <?php echo $config['fpath']; ?></span></td>
  <td class="mbox"><input type="text" name="server" value="<?php echo $file; ?>" size="50" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_lang_overwrite_lang'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_leave_blank_create_new_lang'); ?></span></td>
  <td class="mbox"><select name="overwrite">
	<option value="0"><?php echo $lang->phrase('admin_lang_create_new_lang'); ?></option>
   <?php while ($row = $result->fetch()) { ?>
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

		$my_uploader = new Viscacha\IO\Upload();
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

	$tempdir = 'temp/'.generate_uid().'/';

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
		// We insert some error data and overwrite it later on successful creation
		$langTitle = $db->escape($lang->phrase('admin_lang_new_langpack'));
		$langDetails = $db->escape($lang->phrase('admin_lang_langpack_import_error'));
		$db->execute("INSERT INTO {$db->pre}language (language, detail) VALUES ('{$langTitle}', '{$langDetails}')");
		$inserted = true;
		$overwrite = $db->getInsertId();
	}
	$newdir = "language/{$overwrite}/";

	$filesystem->mover($tempdir, $newdir);
	if (is_dir($tempdir)) {
		$filesystem->rmdirr($tempdir);
	}

	$info = return_array('settings', $overwrite);
	if (isset($info['lang_name'])) {
		$db->execute("UPDATE {$db->pre}language SET language = '{$info['lang_name']}', detail = '{$info['lang_description']}' WHERE id = '{$overwrite}' LIMIT 1");
		unset($archive);
		if ($del == 1) {
			$filesystem->unlink($file);
		}
		$scache->load('loadlanguage')->delete();
		ok('admin.php?action=language&job=manage', $lang->phrase('admin_lang_imported_successfully'));
	}
	else {
		if ($inserted) {
			$db->execute("DELETE FROM {$db->pre}language WHERE id = '{$overwrite}' LIMIT 1");
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

	$result = $db->execute('SELECT language, detail FROM '.$db->pre.'language WHERE id = "'.$id.'" LIMIT 1');
	$row = $result->fetch();

	$file = convert2adress($row['language'].'-v'.str_replace(' ', '', $config['version'])).'.zip';
	$dir = "language/{$id}/";
	$tempdir = "temp/";

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
	$db->execute("INSERT INTO {$db->pre}language (language, detail) VALUES ('{$name}', '{$desc}')");
	$newid = $db->getInsertId();
	$filesystem->mkdir("language/{$newid}/", 0777);
	$filesystem->copyr("language/{$id}/", "language/{$newid}/");
	$scache->load('loadlanguage')->delete();
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

	$result = $db->fetchOne("SELECT id FROM {$db->pre}language WHERE id != '{$id}' AND publicuse = '1' LIMIT 1");
	if (!$result) {
		error('admin.php?action=language&job=manage', $lang->phrase('admin_lang_cannot_delete_last_installed_lang'));
	}

	$publicuse = $db->fetchOne("SELECT publicuse FROM {$db->pre}language WHERE id = '{$id}'");
	if ($publicuse == 1) {
		error('admin.php?action=language&job=manage', $lang->phrase('admin_lang_cannot_unpublish_lang_until_unpublish'));
	}

	$stmt = $db->execute("DELETE FROM {$db->pre}language WHERE id = '{$id}' LIMIT 1");
	if ($stmt->getAffectedRows() == 1) {
		$filesystem->rmdirr("language/{$id}/");
		$scache->load('loadlanguage')->delete();
		ok('admin.php?action=language&job=manage', $lang->phrase('admin_lang_langpack_deleted'));
	}
	else {
		error('admin.php?action=language&job=manage', $lang->phrase('admin_lang_langpack_could_not_deleted'));
	}
}
elseif ($job == 'lang_settings') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->execute("SELECT language, detail, publicuse FROM {$db->pre}language WHERE id = '{$id}' LIMIT 1");
	$data = $gpc->prepare($result->fetch());
	$settings = $gpc->prepare(return_array('settings', $id));
	if (empty($settings['html_read_direction'])) {
		$settings['html_read_direction'] = 'ltr';
	}
	if (empty($settings['lang_code'])) {
		$settings['lang_code'] = 'en';
	}
	if (empty($settings['country_code'])) {
		$settings['country_code'] = '';
	}

	$languages = file2array('admin/data/iso639.txt');
	$country = file2array('admin/data/iso3166.txt');
	?>
<script language="JavaScript">
<!--
function errordefault(box) {
	alert(<?php echo $lang->phrase('admin_lang_cannot_unpublish_until_defined_other_lang'); ?>);
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
   <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_lang_date_and_time'); ?></td>
  </tr>
  <tr>
   <td class="mbox stext" colspan="2"><?php echo $lang->phrase('admin_lang_last_activity_format_info'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_contributions_format'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="datetime_format" value="<?php echo isset($settings['datetime_format']) ?  $settings['datetime_format'] : 'Y-m-d H:i'; ?>" size="20"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_reldatetime_format'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="reldatetime_format" value="<?php echo isset($settings['reldatetime_format']) ? $settings['reldatetime_format'] : '##, H:i'; ?>" size="20"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_regdate_format'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="date_format" value="<?php echo isset($settings['date_format']) ? $settings['date_format'] : 'Y-m-d'; ?>" size="20"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_last_activity_format'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="time_format" value="<?php echo isset($settings['time_format']) ? $settings['time_format'] : 'H:i'; ?>" size="20"></td>
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

	$publicuse = $db->fetchOne("SELECT publicuse FROM {$db->pre}language WHERE id = '{$id}' LIMIT 1");
	if ($publicuse == 1 && $use == 0) {
		if ($id == $config['langdir']) {
			$error .= $lang->phrase('admin_lang_but_cannot_unpublish_until_defined_another_lang');
			$use = 1;
		}
		$result = $db->fetchOne("SELECT id FROM {$db->pre}language WHERE publicuse = '1'");
		if ($result) {
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

	$db->execute("UPDATE {$db->pre}language SET publicuse = '{$use}', language = '{$language}', detail = '{$detail}' WHERE id = '{$id}' LIMIT 1");

	$c = new manageconfig();
	$c->getdata("language/{$id}/settings.lng.php", 'lang');
	$c->updateconfig('html_read_direction', str);
	$c->updateconfig('spellcheck_dict', str, $scd);
	$c->updateconfig('lang_code', str);
	$c->updateconfig('country_code', str);
	$c->updateconfig('thousandssep', str);
	$c->updateconfig('decpoint', str);
	$c->updateconfig('lang_name', str, $language);
	$c->updateconfig('lang_description', str, $detail);
	$c->updateconfig('compatible_version', str);
	$c->updateconfig('datetime_format',str);
	$c->updateconfig('reldatetime_format',str);
	$c->updateconfig('date_format',str);
	$c->updateconfig('time_format',str);
	$c->savedata();

	$scache->load('loadlanguage')->delete();

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
	$lines = preg_split('/[\n\r]+/u', trim($ignore)) ;
	$lines = array_map('trim', $lines);
	$lines = array_map('mb_strtolower', $lines);
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
elseif ($job == 'lang_emailtpl') {
	echo head();
	$id = $gpc->get('id', int);
	$file = $gpc->get('file', path);
	$path = "language/{$id}/mails/{$file}.php";
	if (!file_exists($path)) {
		error('admin.php?action=language&job=lang_edit&id='.$id, $lang->phrase('admin_lang_file_x_does_not_exist'));
	}
	$xml = file_get_contents($path);
	preg_match("~<title>(.+?)</title>.*?<comment>(.+?)</comment>~isu", $xml, $tpl);
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
   <td class="mbox" width="80%"><textarea name="tpl" rows="10" cols="80"><?php echo $gpc->prepare($tpl[2]); ?></textarea></td>
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
	$file = $gpc->get('file', path);
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
	$file = $gpc->get('file', path);
	$lng = return_array($file, $id);
	$pages = 1;
	if (count($lng) > 0) {
		$lng = array_map('viscacha_htmlspecialchars', $lng);
		$lng = array_map('nl2whitespace', $lng);
		ksort($lng);
		$lng = array_chunk($lng, 50, true);
		if (isset($lng[$page-1]) == false) {
			error('admin.php?action=language&job=lang_edit&id='.$id, $lang->phrase('admin_lang_page_not_found'));
		}
		$pages = count($lng);
	}
	$pages_html = "Seiten ({$pages}):";
	// Ersetzen durch Buchstaben (?) -> [A] [B] ...
	for($i=1;$i<=$pages;$i++) {
   		$pages_html .= ' ['.iif($i == $page, "<strong>{$i}</strong>", "<a href='admin.php?action=language&job=lang_array&id={$id}&file={$file}&page={$i}'>{$i}</a>").']';
	}

	?>
<form name="form" method="post" action="admin.php?action=language&job=lang_array2&id=<?php echo $id; ?>&file=<?php echo $file; ?>&page=<?php echo $page; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr>
   <td class="obox" colspan="2">
   <span style="float: right;">
	<a class="button" href="admin.php?action=language&amp;job=phrase_add&amp;file=<?php echo $file; ?>&amp;id=<?php echo $id; ?>"><?php echo $lang->phrase('admin_lang_add_new_phrase'); ?></a>
	<a class="button" href="#" id="menu_acp_switchlang" onmouseover="RegisterMenu('acp_switchlang');"><?php echo $lang->phrase('admin_lang_switch_to_lang'); ?> &#8628;</a>
	<div class="popup" id="popup_acp_switchlang"><ul>
	<?php
	$result = $db->execute('SELECT id, language FROM '.$db->pre.'language ORDER BY language');
	while($row = $result->fetch()) {
		if ($row['id'] == $id) {
			continue;
		}
		echo "<li><a href=\"admin.php?action=language&amp;job=lang_array&amp;file={$file}&amp;id={$row['id']}\">";
		if (file_exists("language/{$row['id']}/{$file}.lng.php")) {
			echo $row['language'];
		}
		else {
			echo "<s>{$row['language']}</s>";
		}
		echo "</a></li>";
	}
	?>
	</ul></div>
   </span>
	<?php echo $lang->phrase('admin_lang_edit_langfile'); ?> &raquo; <?php echo isset($langbase[$file]) ? $langbase[$file] : mb_ucfirst($file); ?>
   </td>
  </tr>
  <tr>
   <td class="mbox stext" colspan="2"><?php echo $lang->phrase('admin_lang_vars_help'); ?></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><?php echo $pages_html; ?></td>
  </tr>
  <?php if (isset($lng[$page-1])) { foreach ($lng[$page-1] as $key => $value) { ?>
  <tr>
   <td class="mbox" width="50%"><img align="absmiddle" name="c" id="img_lang_<?php echo $key; ?>" src="admin/html/images/plus.gif" alt=""> <?php echo $key; ?>
   <div id="part_lang_<?php echo $key; ?>" class="stext">
	<strong><?php echo $lang->phrase('admin_lang_original'); ?></strong> <?php echo $value; ?><br>
	<input type="checkbox" name="delete[]" value="<?php echo $key; ?>"> <?php echo $lang->phrase('admin_lang_delete_phrase'); ?>
   </div>
   </td>
   <td class="mbox" width="50%"><input type="text" name="lang_<?php echo $key; ?>" size="70" value="<?php echo $value ?>" /></td>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="2">
   <?php echo $pages_html; ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_lang_form_save'); ?>" /></td>
  </tr>
  <?php } ?>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'lang_array2') {
	echo head();
	$id = $gpc->get('id', int);
	$file = $gpc->get('file', path);
	$page = $gpc->get('page', int);
	$delete = $gpc->get('delete', arr_str);

	$keys = array_keys($_REQUEST);
	$sent = array();
	foreach ($keys as $key) {
		if (mb_substr($key, 0, 5) == 'lang_') {
			$sent[$key] = mb_substr($key, 5, mb_strlen($key));
		}
	}

	// Update texte from currently chosen language
	$c = new manageconfig();
	$c->getdata("language/{$id}/{$file}.lng.php", 'lang');
	foreach ($sent as $post => $key) {
		$c->updateconfig($key, str, $_REQUEST[$post]);
	}
	$c->savedata();

	// Delete phrases from all languages
	if (count($delete) > 0) {
		$result = $db->execute('SELECT * FROM '.$db->pre.'language ORDER BY language');
		while($row = $result->fetch()) {
			$path = "language/{$row['id']}/{$file}.lng.php";
			if (file_exists($path)) {
				$c->getdata($path, 'lang');
				foreach ($delete as $key) {
					$c->delete($key);
				}
				$c->savedata();
			}
		}
	}

	if ($file == 'javascript') {
		$scache->load('loadlanguage')->delete();
	}

	ok('admin.php?action=language&job=lang_array&id='.$id.'&file='.$file.'&page='.$page);
}
elseif ($job == 'lang_default') {
	echo head();
	$id = $gpc->get('id', int);

	$c = new manageconfig();
	$c->getdata();
	$c->updateconfig('langdir', int, $id);
	$c->savedata();

	$scache->load('loadlanguage')->delete();

	ok('admin.php?action=language&job=manage');
}
elseif ($job == 'lang_edit') {
	echo head();
	$id = $gpc->get('id', int);
	$myini = new INI();

	// Emails
	$mailcategories = array(
		'/^(register_\d\d|admin_confirmed)$/iu' => $lang->phrase('admin_lang_mail_cat_register'),
		'/^digest_\w$/iu' => $lang->phrase('admin_lang_mail_cat_digest'),
		'' => $lang->phrase('admin_lang_mail_cat_others')
	);
	$mailfiles = array();
	$mailpath = "language/{$id}/mails/";
	$result = opendir($mailpath);
	while (($file = readdir($result)) !== false) {
		$info = pathinfo($mailpath.$file);
		if ($info['extension'] == 'php') {
			$name = mb_substr($info['basename'], 0, -(mb_strlen($info['extension']) + ($info['extension'] == '' ? 0 : 1)));
			$mailfiles[$name] = $info;
		}
	}
	closedir($result);
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="4"><?php echo $lang->phrase('admin_lang_edit_langfile_title'); ?></td>
  </tr>
  <tr>
   <td class="ubox" width="20%"><?php echo $lang->phrase('admin_lang_general'); ?></td>
   <td class="ubox" width="30%"><?php echo $lang->phrase('admin_lang_core_phrases'); ?></td>
   <td class="ubox" width="30%"><?php echo $lang->phrase('admin_lang_mails'); ?></td>
  </tr>
  <tr class="mbox inlinelist">
   <td valign="top">
   <ul>
   <li><a href="admin.php?action=language&job=lang_settings&id=<?php echo $id; ?>"><?php echo $lang->phrase('admin_lang_settings'); ?></a></li>
   <li><a href="admin.php?action=language&job=lang_rules&id=<?php echo $id; ?>"><?php echo $lang->phrase('admin_lang_terms_of_behaviour'); ?></a></li>
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
		if (mb_substr($entry, -8, 8) == '.lng.php') {
			$basename = mb_substr($entry, 0, mb_strlen($entry)-8);
			if ($basename != 'settings' && $basename != 'modules') {
				$name = preg_replace("/[^\w\d]/iu", " ", $basename);
				$name = mb_ucfirst($name);
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
	<li><?php echo $lang->phrase('admin_lang_packages_phrases'); ?></li>
	 <ul>
	  <li><a href="admin.php?action=language&job=lang_array&id=<?php echo $id; ?>&file=modules"><?php echo $lang->phrase('admin_lang_plugins'); ?></a></li>
	 </ul>
	<li><?php echo $lang->phrase('admin_lang_admin_control_panel'); ?>
   <ul>
   <?php
	$dir = 'language/'.$id.'/admin/';
	$files = array();
	$d = dir($dir);
	while (FALSE !== ($entry = $d->read())) {
		if (mb_substr($entry, -8, 8) == '.lng.php') {
			$basename = mb_substr($entry, 0, mb_strlen($entry)-8);
			$name = preg_replace("/[^\w\d]/iu", " ", $basename);
			$name = mb_ucfirst($name);
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
	<?php
	foreach ($mailcategories as $pattern => $title) {
		echo "<li>{$title}:<ul>";
		foreach ($mailfiles as $name => $info) {
			if ($info != null && (empty($pattern) || preg_match($pattern, $name))) {
				?>
				<li>
				<a href="admin.php?action=language&job=lang_emailtpl&id=<?php echo $id; ?>&file=<?php echo $name; ?>"><?php echo $name; ?></a>
				<?php echo isset($mailbase[$name]) ? "<br /><span class=\"stext\">{$mailbase[$name]}</span>" : ''; ?>
				</li>
				<?php
				$mailfiles[$name] = null;
			}
		}
		echo "</ul></li>";
	}
	?>
   </ul>
   </td>
  </tr>
 </table>
	<?php
	echo foot();
}
elseif ($job == 'phrase_add') {
	echo head();
	$id = $gpc->get('id', int);
	$file = $gpc->get('file', path);
	?>
<form name="form" method="post" action="admin.php?action=language&amp;job=phrase_add2&amp;file=<?php echo $file; ?>&amp;id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_lang_add_new_phrase'); ?> &raquo; <?php echo $file; ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_varname'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_varname_can_only_contain_letters_etc'); ?></span></td>
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
  $result = $db->execute('SELECT * FROM '.$db->pre.'language ORDER BY language');
  while($row = $result->fetch()) {
    if (file_exists("language/{$row['id']}/{$file}.lng.php")) {
    ?>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_lang_translation'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_lang_optional_html_not_recommended'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="langt[<?php echo $row['id']; ?>]" size="50" /></td>
  </tr>
  <?php } } ?>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_lang_form_save'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'phrase_add2') {
	echo head();
	$source_id = $gpc->get('id', int);
	$file = $gpc->get('file', path);
	$varname = $gpc->get('varname', none);
	$text = $gpc->get('text', none);
	$language = $gpc->get('langt', arr_none);

	$c = new manageconfig();
	foreach ($language as $id => $t) {
		$c->getdata("language/{$id}/{$file}.lng.php", 'lang');
		$c->updateconfig($varname, str, iif(empty($t), $text, $t));
		$c->savedata();
	}

	if ($file == 'javascript') {
		$scache->load('loadlanguage')->delete();
	}

	if (is_id($source_id))
		ok('admin.php?action=language&job=lang_array&id='.$source_id.'&file='.$file);
	else
		ok('admin.php?action=language&job=phrase_add&file='.$file);
}
else {
	sendStatusCode(302, $config['furl'].'/admin.php?action=language&job=manage');
}
?>