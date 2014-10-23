<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// MB: MultiLangAdmin
$lang->group("admin/bbcodes");

($code = $plugins->load('admin_bbcodes_jobs')) ? eval($code) : null;

if ($job == 'smileys_delete') {
	$deleteid = $gpc->get('id', arr_int);
	if (count($deleteid) > 0) {
	   	$delobj = $scache->load('smileys');
	   	$delobj->delete();
	   	$result = $db->query('SELECT * FROM '.$db->pre.'smileys WHERE id IN ('.implode(',', $deleteid).')');
	   	while ($row = $db->fetch_assoc($result)) {
	   		$row['replace'] = str_replace('{folder}', $config['smileypath'], $row['replace']);
	   		if(file_exists($row['replace'])) {
	   			$filesystem->unlink($row['replace']);
	   		}
	   	}
		$db->query('DELETE FROM '.$db->pre.'smileys WHERE id IN ('.implode(',', $deleteid).')');
		$anz = $db->affected_rows();
	}
	else {
		$anz = $lang->phrase('admin_bbc_no');
	}
	echo head();
	ok('admin.php?action=bbcodes&job=smileys', $lang->phrase('admin_bbc_entries_deleted'));
}
elseif ($job == 'smileys_edit') {
	$editid = $gpc->get('id', arr_int);
	if (count($editid) == 0) {
		sendStatusCode(307, $config['furl'].'/admin.php?action=bbcodes&job=smileys');
		exit;
	}
	$result = $db->query('SELECT * FROM '.$db->pre.'smileys WHERE id IN ('.implode(',', $editid).')');
	echo head();
	$num_smileys = count($editid);
	?>
<form name="form" method="post" enctype="multipart/form-data" action="admin.php?action=bbcodes&job=smileys_edit2">
 <table class="border">
  <tr>
   <td class="obox"><?php echo $lang->phrase('admin_bbc_edit_smileys'); ?></td>
  </tr>
  <tr>
   <td class="ubox" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_bbc_send'); ?>"></td>
  </tr>
 </table><br />
 <?php while($row = $db->fetch_assoc($result)) { ?>
 <input type="hidden" name="id[]" value="<?php echo $row['id']; ?>">
 <table class="border">
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_bbc_code'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="search_<?php echo $row['id']; ?>" size="50" value="<?php echo $row['search']; ?>"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_bbc_image'); ?><br><span class="stext"><?php echo $lang->phrase('admin_bbc_image_desc'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="replace_<?php echo $row['id']; ?>" size="50" value="<?php echo $row['replace']; ?>"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_bbc_description'); ?><br><span class="stext"><?php echo $lang->phrase('admin_bbc_optional'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="desc_<?php echo $row['id']; ?>" size="50" value="<?php echo $row['desc']; ?>"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_bbc_show_directly'); ?><br><span class="stext"><?php echo $lang->phrase('admin_bbc_show_directly_desc'); ?></span></td>
   <td class="mbox" width="50%"><input type="checkbox" name="show_<?php echo $row['id']; ?>" value="1"<?php echo iif($row['show'] == 1, ' checked="checked"'); ?>></td>
  </tr>
 </table><br />
 <?php } ?>
 <table class="border">
  <tr>
   <td class="ubox" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_bbc_send'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
}
elseif ($job == 'smileys_edit2') {
	echo head();
	$id = $gpc->get('id', arr_int);
	foreach ($id as $i) {
		$search = $gpc->get('search_'.$i, str);
		$replace = $gpc->get('replace_'.$i, db_esc);
		$desc = $gpc->get('desc_'.$i, db_esc);
		$show = $gpc->get('show_'.$i, int);
		$db->query("UPDATE {$db->pre}smileys AS s SET s.search = '{$search}', s.replace = '{$replace}', s.desc = '{$desc}', s.show = '{$show}' WHERE s.id = '{$i}' LIMIT 1");
	}
	$delobj = $scache->load('smileys');
	$delobj->delete();
	ok('admin.php?action=bbcodes&job=smileys', count($id).$lang->phrase('admin_bbc_smileys_edited'));
}
elseif ($job == 'smileys_import') {
	echo head();
	$file = $gpc->get('file', str);
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=bbcodes&job=smileys_import2">
 <table class="border" cellpadding="4" cellspacing="0" border="0">
  <tr><td class="obox" colspan="2"><?php echo $lang->phrase('admin_bbc_import_smileypack'); ?></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_bbc_either_upload'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_bbc_allowed_file_types'); ?></span></td>
  <td class="mbox"><input type="file" name="upload" size="40" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_bbc_or_select'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_bbc_path_from_vc_root'); ?> <?php echo $config['fpath']; ?></span></td>
  <td class="mbox"><input type="text" name="server" value="<?php echo $file; ?>" size="50" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_bbc_smileypack_format'); ?></td>
  <td class="mbox"><select name="format">
  <option value="viscacha_ini" selected="selected"><?php echo $lang->phrase('admin_bbc_vc_format'); ?></option>
  <option value="phpbb2"><?php echo $lang->phrase('admin_bbc_phpbb2_format'); ?></option>
  <option value="none"><?php echo $lang->phrase('admin_bbc_no_format'); ?></option>
  </select></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_bbc_delete_before_import'); ?></td>
  <td class="mbox"><input type="checkbox" name="truncate" value="1" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_bbc_delete_file_after_import'); ?></td>
  <td class="mbox"><input type="checkbox" name="delete" value="1" checked="checked" /></td></tr>
  <tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" value="<?php echo $lang->phrase('admin_bbc_import'); ?>" /></td></tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'smileys_import2') {
	$server = $gpc->get('server', none);
	$del = $gpc->get('delete', int);
	$format = $gpc->get('format', none);
	$truncate = $gpc->get('truncate', int);
	$inserterrors = array();

	if (!empty($_FILES['upload']['name'])) {
		$filesize = 2048*1024;
		$filetypes = array('zip');
		$dir = realpath('temp').DIRECTORY_SEPARATOR;

		$insertuploads = array();
		require("classes/class.upload.php");

		$my_uploader = new uploader();
		$my_uploader->max_filesize($filesize);
		$my_uploader->file_types($filetypes);
		$my_uploader->set_path($dir);
		if ($my_uploader->upload('upload')) {
			$my_uploader->save_file();
			if ($my_uploader->upload_failed()) {
				array_push($inserterrors,$my_uploader->get_error());
			}
		}
		else {
			array_push($inserterrors,$my_uploader->get_error());
		}
		$file = $dir.DIRECTORY_SEPARATOR.$my_uploader->fileinfo('filename');
		if (!file_exists($file)) {
			$inserterrors[] = $lang->phrase('admin_bbc_file_not_existing');
		}
	}
	elseif (file_exists($server)) {
		$ext = get_extension($server);
		if ($ext == 'zip') {
			$file = $server;
		}
		else {
			$inserterrors[] = $lang->phrase('admin_bbc_no_zip_file');
		}
	}
	else {
		$inserterrors[] = $lang->phrase('admin_bbc_no_valid_file');
	}
	echo head();
	if (count($inserterrors) > 0) {
		error('admin.php?action=bbcodes&job=smileys_import', $inserterrors);
	}

	$tempdir = 'temp/'.md5(microtime()).'/';

	// Exract
	require_once('classes/class.zip.php');
	$archive = new PclZip($file);
	$failure = $archive->extract($tempdir);
	if ($failure < 1) {
		unset($archive);
		if ($del > 0) {
			$filesystem->unlink($file);
		}
		$filesystem->rmdirr($tempdir);
		error('admin.php?action=bbcodes&job=smileys_import', $lang->phrase('admin_bbc_zip_invalid'));
	}

	// Parse format
	switch ($format) {
		case 'phpbb2':
			$package = array();
			$d = dir($tempdir);
			while (false !== ($entry = $d->read())) {
				if (get_extension($entry) == 'pak') {
					$lines = file($tempdir.$entry);
					break;
				}
			}
			$d->close();
			$lines = array_map('trim', $lines);
			foreach($lines as $line) {
				$new_pack = array();
				list($new_pack['replace'], $new_pack['desc'], $new_pack['search']) = explode('=+:', $line, 3);
				$new_pack['replace'] = '{folder}/'.$new_pack['replace'];
				$package[] = $new_pack;
			}
		break;
		case 'none':
			$package = array();
			$d = dir($tempdir);
			$i = 0;
			while (false !== ($entry = $d->read())) {
				if (@getimagesize($tempdir.$entry) != false) {
					$i++;
					$package[] = array(
						'search' => ':smiley'.$i.':',
						'replace' => '{folder}/'.$entry,
						'desc' => ''
					);
				}
			}
			$d->close();
		break;
		default: // viscacha_ini
			if (!file_exists($tempdir.'/smileys.ini')) {
				error('admin.php?action=bbcodes&job=smileys_import', $lang->phrase('admin_bbc_smileys_ini_missing'));
			}
			$myini = new INI();
			$package = $myini->read($tempdir.'/smileys.ini');
		break;
	}

	// Delete old smileys
	$codes = array();
	if ($truncate == 1) {
	   	$result = $db->query('SELECT * FROM '.$db->pre.'smileys');
	   	while ($row = $db->fetch_assoc($result)) {
	   		$row['replace'] = str_replace('{folder}', $config['smileypath'], $row['replace']);
	   		if(file_exists($row['replace'])) {
	   			$filesystem->unlink($row['replace']);
	   		}
	   	}
		$db->query('TRUNCATE TABLE '.$db->pre.'smileys');
	}
	else {
		// Get existing smiley codes from database
		$result = $db->query('SELECT search FROM '.$db->pre.'smileys');
		while ($row = $db->fetch_assoc($result)) {
			$codes[] = strtolower($row['search']);
		}
	}

	// Copy files and prepare for inserting smileys
	$sqlinsert = array();
	foreach ($package as $ini) {
		if (strpos($ini['replace'], '{folder}') !== false) {
			$ini['replace_temp'] = str_replace('{folder}', $tempdir, $ini['replace']);
			$ini['replace_new'] = str_replace('{folder}', $config['smileypath'], $ini['replace']);
			$n = 0;
			while(file_exists($ini['replace_new'])) {
				$ext = get_extension($ini['replace_new'], true);
				$length = strlen($ext);
				$base = substr($ini['replace_new'], $length*(-1), $length);
				$n++;
				$base .= '_'.$n;
				$ini['replace_new'] = $base.$ext;
			}
			$n = 0;
			while(in_array($ini['search'], $codes)) {
				$n++;
				$ini['search'] = ':smiley'.$n.':';
			}
			$codes[] = $ini['search'];
			$filesystem->copy($ini['replace_temp'], $ini['replace_new']);
		}
		$sqlinsert[] = '("'.$gpc->save_str($ini['search']).'", "'.$db->escape_string($ini['replace']).'", "'.$db->escape_string($ini['desc']).'")';
	}
	$db->query('INSERT INTO '.$db->pre.'smileys (`search`, `replace`, `desc`) VALUES '.implode(', ', $sqlinsert));
	$anz = $db->affected_rows();

	unset($archive);
	if ($del > 0) {
		$filesystem->unlink($file);
	}
	$filesystem->rmdirr($tempdir);

	$delobj = $scache->load('smileys');
	$delobj->delete();

	ok('admin.php?action=bbcodes&job=smileys', $lang->phrase('admin_bbc_successfully_imported'));
}
elseif ($job == 'smileys_export') {
	$smileys = $gpc->get('id', arr_int);

	if (count($smileys) > 0) {
		$sqlwhere = " WHERE id IN (".implode(',', $smileys).") ";
	}
	else {
		$sqlwhere = "";
	}

	$file = 'smileys_'.gmdate('Ymd', times()).'.zip';
	$tempdir = "temp/";
	$smilieconfig = $config['smileypath'].'/smileys.ini';

	$result = $db->query('SELECT `id`, `search`, `replace`, `desc` FROM `'.$db->pre.'smileys` '.$sqlwhere);
	$files = array();
	$filedata = array();
	while ($row = $db->fetch_assoc($result)) {
		$filepath = str_replace('{folder}', $config['smileypath'], $row['replace']);
		$filedata[$row['id']] = array(
			'search' => $row['search'],
			'replace' => $row['replace'],
			'desc' => $row['desc'],
		);
		if (!preg_match('~http(s)?:\/\/~i', $filepath)) {
			$files[] = $filepath;
		}
	}

	$myini = new INI();
	$myini->write($smilieconfig, $filedata);
	$files[] = $smilieconfig;
	$files = array_unique($files);

	require_once('classes/class.zip.php');
	$archive = new PclZip($tempdir.$file);
	// Have to parse $dir with PclZipUtilTranslateWinPath to have equal paths at $files-Array and $dir (PclZip-Bug?)
	$v_list = $archive->create($files, PCLZIP_OPT_REMOVE_PATH, PclZipUtilTranslateWinPath($config['smileypath']));
	if ($v_list == 0) {
		echo head();
		unset($archive);
		$filesystem->unlink($smilieconfig);
		error('admin.php?action=bbcodes&job=smileys', $archive->errorInfo(true));
	}
	else {
		viscacha_header('Content-Type: application/zip');
		viscacha_header('Content-Disposition: attachment; filename="'.$file.'"');
		viscacha_header('Content-Length: '.filesize($tempdir.$file));
		readfile($tempdir.$file);
		unset($archive);
		$filesystem->unlink($smilieconfig);
		$filesystem->unlink($tempdir.$file);
	}
}
elseif ($job == 'smileys') {
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}smileys AS s ORDER BY s.show DESC");
	$res_num_rows = $db->num_rows($result);
?>
<form name="form" method="post" action="admin.php?action=bbcodes">
 <table class="border">
  <tr>
   <td class="obox" colspan="6">
   	<span class="right">
   		<a class="button" href="admin.php?action=packages&amp;job=browser&amp;type=<?php echo IMPTYPE_SMILEYPACK; ?>"><?php echo $lang->phrase('admin_bbc_browse_smileypacks'); ?></a>
   		<a class="button" href="admin.php?action=bbcodes&amp;job=smileys_import"><?php echo $lang->phrase('admin_bbc_import_smileypack'); ?></a>
   	</span>
   	<?php echo $lang->phrase('admin_bbc_manage_smileys'); ?>
   </td>
  </tr>
  <tr class="ubox">
   <td width="5%"><?php echo $lang->phrase('admin_bbc_choose_all'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="id[]" /> <?php echo $lang->phrase('admin_bbc_choose_all2'); ?></span></td>
   <td width="10%"><?php echo $lang->phrase('admin_bbc_code'); ?></td>
   <td width="30%"><?php echo $lang->phrase('admin_bbc_url'); ?></td>
   <td width="15%"><?php echo $lang->phrase('admin_bbc_images'); ?></td>
   <td width="5%"><?php echo $lang->phrase('admin_bbc_show_directly'); ?></td>
   <td width="35%"><?php echo $lang->phrase('admin_bbc_description'); ?></td>
  </tr>
<?php
	while ($row = $db->fetch_assoc($result)) {
		$src = str_replace('{folder}', $config['smileyurl'], $row['replace']);
?>
  <tr class="mbox">
   <td width="5%"><input type="checkbox" name="id[]" value="<?php echo $row['id']; ?>"></td>
   <td width="10%"><?php echo $row['search']; ?></td>
   <td width="30%"><?php echo $row['replace']; ?></td>
   <td align="center" width="15%"><img src="<?php echo $src; ?>" alt="<?php echo $row['search']; ?>" border="0" />&nbsp;</td>
   <td width="5%" align="center"><?php echo noki($row['show'], ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=bbcodes&job=smileys_ajax_pos&id='.$row['id'].'\')"'); ?></td>
   <td width="35%"><?php echo $row['desc']; ?></td>
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" colspan="6" align="center">
   Selected Smileys: <select name="job">
	<option value="smileys_edit" selected="selected"><?php echo $lang->phrase('admin_bbc_edit'); ?></option>
	<option value="smileys_export"><?php echo $lang->phrase('admin_bbc_export'); ?></option>
   	<option value="smileys_delete"><?php echo $lang->phrase('admin_bbc_delete'); ?></option>
   </select>&nbsp;&nbsp;&nbsp;&nbsp;
   <input type="submit" value="<?php echo $lang->phrase('admin_bbc_go'); ?>">
   </td>
  </tr>
 </table>
</form>
<br>
<form name="form" method="post" enctype="multipart/form-data" action="admin.php?action=bbcodes&amp;job=smileys_add">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><span style="float: right;"><a class="button" href="admin.php?action=bbcodes&amp;job=smileys_import"><?php echo $lang->phrase('admin_bbc_import_smileypack'); ?></a></span><?php echo $lang->phrase('admin_bbc_add_smiley'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_bbc_code'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="code" size="50"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_bbc_image'); ?><br><span class="stext"><?php echo $lang->phrase('admin_bbc_image_no_upload_desc'); ?><br />{folder} = <?php echo $config['smileypath'].$lang->phrase('admin_bbc_and').$config['smileyurl']; ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="img" size="50"></td>
  </tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_bbc_upload_image'); ?><br><span class="stext"><?php echo $lang->phrase('admin_bbc_image_upload_desc'); ?></span></td>
   <td class="mbox" width="50%"><input type="file" name="upload" size="40" /></td>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_bbc_description'); ?><br><span class="stext"><?php echo $lang->phrase('admin_bbc_optional'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="desc" size="50"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_bbc_show_directly'); ?><br><span class="stext"><?php echo $lang->phrase('admin_bbc_show_directly_desc'); ?></span></td>
   <td class="mbox" width="50%"><input type="checkbox" name="show" value="1"></td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_bbc_add'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'smileys_ajax_pos') {
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT b.show FROM {$db->pre}smileys AS b WHERE id = '{$id}' LIMIT 1");
	$use = $db->fetch_assoc($result);
	$use = invert($use['show']);
	$db->query("UPDATE {$db->pre}smileys AS b SET b.show = '{$use}' WHERE id = '{$id}' LIMIT 1");
	$delobj = $scache->load('smileys');
	$delobj->delete();
	echo strval($use);
	exit;
}
elseif ($job == 'smileys_add') {
	echo head();
	$error = array();

	$path = 'temp/';
	$dir = realpath($path);

	$insertuploads = array();
	$inserterrors = array();
	require("classes/class.upload.php");

	$img = $gpc->get('img', str);

	$has_upload = false;

	if (!empty($_FILES['upload']['name'])) {
		$my_uploader = new uploader();
		$my_uploader->max_filesize(200*1024);
		$my_uploader->file_types(array('gif', 'jpg', 'png', 'bmp', 'jpeg', 'jpe'));
		$my_uploader->set_path($dir);
		if ($my_uploader->upload('upload')) {
			if ($my_uploader->save_file()) {
				$has_upload = $gpc->save_str($my_uploader->fileinfo('filename'));
			}
		}
		if ($my_uploader->upload_failed()) {
			$error[] = $my_uploader->get_error();
		}
	}

	if (strlen($gpc->get('code', str)) < 2) {
		$error[] = $lang->phrase('admin_bbc_code_too_short');
	}
	if (empty($has_upload) && empty($img)) {
		$error[] = $lang->phrase('admin_bbc_path_too_short');
	}
	if (strlen($gpc->get('show', int)) != 1 && $gpc->get('show', int) != 0) {
		$error[] = $lang->phrase('admin_bbc_wrong_spec');
	}
	if (count($error) > 0) {
		error('admin.php?action=bbcodes&job=smileys', $error);
	}
	if ($has_upload) {
		$filesystem->copy($path.$has_upload, $config['smileypath'].'/'.$has_upload);
		$img = '{folder}/'.$has_upload;
	}
	else {
		if (stripos(realpath($img), realpath($config['fpath'])) !== false) {
			$img = str_replace(realpath($config['fpath']), '{folder}', realpath($img));
		}
		else {
			$img = str_replace($config['fpath'], '{folder}', $img);
		}
	}
	$db->query("INSERT INTO {$db->pre}smileys (`search`,`replace`,`desc`,`show`) VALUES ('".$gpc->get('code', str)."','".$img."','".$gpc->get('desc', str)."','".$gpc->get('show', int)."')");

	$delobj = $scache->load('smileys');
	$delobj->delete();

	ok('admin.php?action=bbcodes&job=smileys', $lang->phrase('admin_bbc_successfully_added'));
}
elseif ($job == 'word') {
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}textparser WHERE type = 'word'");
?>
<form name="form" method="post" action="admin.php?action=bbcodes&job=del&tp=word">
 <table class="border">
  <tr>
   <td class="obox" colspan="4"><?php echo $lang->phrase('admin_bbc_manage_glossary'); ?></b></td>
  </tr>
  <tr>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_bbc_delete_all'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /> <?php echo $lang->phrase('admin_bbc_delete_all2'); ?></span></td>
   <td class="ubox" width="15%"><?php echo $lang->phrase('admin_bbc_abbr'); ?></td>
   <td class="ubox" width="30%"><?php echo $lang->phrase('admin_bbc_phrase'); ?></td>
   <td class="ubox" width="50%"><?php echo $lang->phrase('admin_bbc_description'); ?></td>
  </tr>
<?php while ($row = $db->fetch_assoc($result)) { ?>
  <tr>
   <td class="mbox" width="5%"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
   <td class="mbox" width="15%"><a href="admin.php?action=bbcodes&amp;job=edit&amp;tp=word&amp;id=<?php echo $row['id']; ?>"><?php echo $row['search']; ?></a></td>
   <td class="mbox" width="30%"><?php echo $row['replace']; ?></td>
   <td class="mbox" width="50%"><?php echo $row['desc']; ?></td>
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" width="100%" colspan="4" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_bbc_delete'); ?>"></td>
  </tr>
 </table>
</form>
<br>
<form name="form" method="post" action="admin.php?action=bbcodes&job=add&tp=word">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_bbc_add_word'); ?></b></td>
  </tr>
  <tr>
   <td class="mbox" width="30%"><?php echo $lang->phrase('admin_bbc_abbr'); ?>:<br /><span class="stext"><?php echo $lang->phrase('admin_bbc_max_200_chars'); ?></span></td>
   <td class="mbox" width="70%"><input type="text" name="temp1" size="70"></td>
  </tr>
  <tr>
   <td class="mbox" width="30%"><?php echo $lang->phrase('admin_bbc_phrase'); ?>:<br /><span class="stext"><?php echo $lang->phrase('admin_bbc_max_255_chars'); ?></span></td>
   <td class="mbox" width="70%"><input type="text" name="temp2" size="70"></td>
  </tr>
  <tr>
   <td class="mbox" width="30%"><?php echo $lang->phrase('admin_bbc_description'); ?>:</td>
   <td class="mbox" width="70%"><textarea name="temp3" cols="70" rows="3"></textarea></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_bbc_add'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'censor') {
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}textparser WHERE type = 'censor'");
?>
<form name="form" method="post" action="admin.php?action=bbcodes&job=del&tp=censor">
 <table class="border">
  <tr>
   <td class="obox" colspan="3"><?php echo $lang->phrase('admin_bbc_manage_censorship'); ?></b></td>
  </tr>
  <tr>
   <td class="ubox" width="10%"><?php echo $lang->phrase('admin_bbc_delete_all'); ?></td>
   <td class="ubox" width="45%"><?php echo $lang->phrase('admin_bbc_word'); ?></td>
   <td class="ubox" width="45%"><?php echo $lang->phrase('admin_bbc_censored_word'); ?></td>
  </tr>
<?php while ($row = $db->fetch_assoc($result)) { ?>
  <tr>
   <td class="mbox" width="10%"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
   <td class="mbox" width="45%"><a href="admin.php?action=bbcodes&amp;job=edit&amp;tp=censor&amp;id=<?php echo $row['id']; ?>"><?php echo $row['search']; ?></a></td>
   <td class="mbox" width="45%"><?php echo $row['replace']; ?></td>
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" width="100%" colspan=3 align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_bbc_delete'); ?>"></td>
  </tr>
 </table>
</form>
<br>
<form name="form" method="post" action="admin.php?action=bbcodes&job=add&tp=censor">
 <table class="border">
  <tr>
   <td class="obox" colspan=2><?php echo $lang->phrase('admin_bbc_add_word'); ?></b></td>
  </tr>
  <tr>
   <td class="mbox" width="30%"><?php echo $lang->phrase('admin_bbc_word'); ?>:<br /><span class="stext"><?php echo $lang->phrase('admin_bbc_max_200_chars'); ?></span></td>
   <td class="mbox" width="70%"><input type="text" name="temp1" size="70"></td>
  </tr>
  <tr>
   <td class="mbox" width="30%"><?php echo $lang->phrase('admin_bbc_censored_word'); ?>:<br /><span class="stext"><?php echo $lang->phrase('admin_bbc_max_255_chars'); ?></span></td>
   <td class="mbox" width="70%"><input type="text" name="temp2" size="70"></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_bbc_add'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'replace') {
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}textparser WHERE type = 'replace'");
?>
<form name="form" method="post" action="admin.php?action=bbcodes&job=del&tp=replace">
 <table class="border">
  <tr>
   <td class="obox" colspan="3"><?php echo $lang->phrase('admin_bbc_manage_vocab'); ?></b></td>
  </tr>
  <tr>
   <td class="ubox" width="10%"><?php echo $lang->phrase('admin_bbc_delete_all'); ?></td>
   <td class="ubox" width="45%"><?php echo $lang->phrase('admin_bbc_word'); ?></td>
   <td class="ubox" width="45%"><?php echo $lang->phrase('admin_bbc_replacement'); ?></td>
  </tr>
<?php while ($row = $db->fetch_assoc($result)) { ?>
  <tr>
   <td class="mbox" width="10%"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
   <td class="mbox" width="45%"><a href="admin.php?action=bbcodes&amp;job=edit&amp;tp=replace&amp;id=<?php echo $row['id']; ?>"><?php echo $row['search']; ?></a></td>
   <td class="mbox" width="45%"><?php echo $row['replace']; ?></td>
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" width="100%" colspan=3 align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_bbc_delete'); ?>"></td>
  </tr>
 </table>
</form>
<br>
<form name="form" method="post" action="admin.php?action=bbcodes&job=add&tp=replace">
<input name="tp" value="replace" type="hidden">
 <table class="border">
  <tr>
   <td class="obox" colspan=2><?php echo $lang->phrase('admin_bbc_add_word'); ?></b></td>
  </tr>
  <tr>
   <td class="mbox" width="30%"><?php echo $lang->phrase('admin_bbc_word'); ?>:<br /><span class="stext"><?php echo $lang->phrase('admin_bbc_max_200_chars'); ?></span></td>
   <td class="mbox" width="70%"><input type="text" name="temp1" size="70"></td>
  </tr>
  <tr>
   <td class="mbox" width="30%"><?php echo $lang->phrase('admin_bbc_replacement'); ?>:<br /><span class="stext"><?php echo $lang->phrase('admin_bbc_max_255_chars'); ?></span></td>
   <td class="mbox" width="70%"><input type="text" name="temp2" size="70"></td>
  </tr>
  <tr>
   <td class="ubox" colspan=2 align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_bbc_add'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'add') {
	echo head();
	$type = $gpc->get('tp', str);

	$error = array();
	if ($type != 'word' && $type != 'censor' && $type != 'replace') {
		$error[] = $lang->phrase('admin_bbc_no_valid_type');
	}
	if (strxlen($gpc->get('temp1', str)) < 2) {
		$error[] = $lang->phrase('admin_bbc_word_too_short');
	}
	if (strxlen($gpc->get('temp2', str)) < 2) {
		$error[] = $lang->phrase('admin_bbc_something_else_too_short');
	}
	if (strxlen($gpc->get('temp3', str)) < 2 && $type == 'word') {
		$error[] = $lang->phrase('admin_bbc_desc_too_short');
	}
	if (count($error) > 0) {
		error('admin.php?action=bbcodes&job='.$type, $error);
	}

	$db->query("INSERT INTO {$db->pre}textparser (`search`,`replace`,`type`,`desc`) VALUES ('".$gpc->get('temp1', str)."','".$gpc->get('temp2', db_esc)."','{$type}','".$gpc->get('temp3', db_esc)."')");

	$delobj = $scache->load('bbcode');
	$delobj->delete();

	ok('admin.php?action=bbcodes&job='.$type, $lang->phrase('admin_bbc_data_successfully_added'));
}
elseif ($job == 'edit') {
	echo head();
	$type = $gpc->get('tp', str);
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}textparser WHERE id = '{$id}' AND type = '{$type}'");
	if ($db->num_rows($result) == 0) {
		error('admin.php?action=bbcodes&job='.$type, $lang->phrase('admin_bbc_no_valid_selection'));
	}
	$row = $db->fetch_assoc($result);
	if ($row['type'] == 'replace') {
		$label1 = $lang->phrase('admin_bbc_word');
		$label2 = $lang->phrase('admin_bbc_replacement');
	}
	elseif ($row['type'] == 'censor') {
		$label1 = $lang->phrase('admin_bbc_word');
		$label2 = $lang->phrase('admin_bbc_censored_word');
	}
	else {
		$label1 = $lang->phrase('admin_bbc_abbr');
		$label2 = $lang->phrase('admin_bbc_phrase');
	}
	?>
<form name="form" method="post" action="admin.php?action=bbcodes&amp;job=edit2&amp;tp=<?php echo $type; ?>&amp;id=<?php echo $id; ?>">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_bbc_edit_word'); ?></b></td>
  </tr>
  <tr>
   <td class="mbox" width="30%"><?php echo $label1; ?>:<br /><span class="stext"><?php echo $lang->phrase('admin_bbc_max_200_chars'); ?></span></td>
   <td class="mbox" width="70%"><input type="text" name="temp1" size="70" value="<?php echo $gpc->prepare($row['search']); ?>"></td>
  </tr>
  <tr>
   <td class="mbox" width="30%"><?php echo $label2; ?>:<br /><span class="stext"><?php echo $lang->phrase('admin_bbc_max_255_chars'); ?></span></td>
   <td class="mbox" width="70%"><input type="text" name="temp2" size="70" value="<?php echo $gpc->prepare($row['replace']); ?>"></td>
  </tr>
  <?php if ($row['type'] == 'word') { ?>
  <tr>
   <td class="mbox" width="30%"><?php echo $lang->phrase('admin_bbc_description'); ?>:</td>
   <td class="mbox" width="70%"><textarea name="temp3" cols="70" rows="5"><?php echo $row['desc']; ?></textarea></td>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_bbc_edit'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'edit2') {
	echo head();
	$type = $gpc->get('tp', str);
	$id = $gpc->get('id', int);

	$error = array();
	if ($type != 'word' && $type != 'censor' && $type != 'replace') {
		error('admin.php?action=index', $lang->phrase('admin_bbc_no_valid_type'));
	}
	if (strxlen($gpc->get('temp1', str)) < 2) {
		$error[] = $lang->phrase('admin_bbc_word_too_short');
	}
	if (strxlen($gpc->get('temp1', str)) > 200) {
		$error[] = $lang->phrase('admin_bbc_word_too_long');
	}
	if (strlen($gpc->get('temp2', none)) > 255) {
		$error[] = $lang->phrase('admin_bbc_something_else_too_long');
	}
	if (strlen($gpc->get('temp2', none)) < 2) {
		$error[] = $lang->phrase('admin_bbc_something_else_too_short');
	}
	if (strlen($gpc->get('temp3', none)) < 2 && $type == 'word') {
		$error[] = $lang->phrase('admin_bbc_desc_too_short');
	}
	if (count($error) > 0) {
		error('admin.php?action=bbcodes&job=edit&tp='.$type.'&id='.$id, $error);
	}

	$db->query("UPDATE {$db->pre}textparser SET `search` = '".$gpc->get('temp1', str)."', `replace` = '".$gpc->get('temp2', db_esc)."', `desc` = '".$gpc->get('temp3', db_esc)."' WHERE id = '{$id}' AND type = '{$type}'");

	$delobj = $scache->load('bbcode');
	$delobj->delete();

	ok('admin.php?action=bbcodes&job='.$type, $lang->phrase('admin_bbc_data_successfully_edited'));
}
elseif ($job == 'del') {
	echo head();
	$delete = $gpc->get('delete', arr_int);
	$type = $gpc->get('tp', str);
	if (count($delete) == 0) {
		error('admin.php?action=bbcodes&job='.$type, $lang->phrase('admin_bbc_no_valid_selection'));
	}
	$db->query('DELETE FROM '.$db->pre.'textparser WHERE id IN ('.implode(',',$delete).')');
	$anz = $db->affected_rows();
	$delobj = $scache->load('bbcode');
	$delobj->delete();
	ok('admin.php?action=bbcodes&job='.$type, $lang->phrase('admin_bbc_entries_successfully_deleted'));
}
elseif ($job == 'codefiles') {
	echo head();
	include_once('classes/class.geshi.php');
	$clang = array();
	$d = dir("classes/geshi");
	while (false !== ($entry = $d->read())) {
		if (get_extension($entry) == 'php' && !is_dir("classes/geshi/".$entry)) {
			include_once("classes/geshi/".$entry);
			if (!isset($language_data['NO_INDEX'])) {
				$short = str_replace('.php','',$entry);
				$clang[$short]['file'] = $entry;
				$clang[$short]['name'] = $language_data['LANG_NAME'];
			}
		}
	}
	$d->close();
	asort($clang);
	$num_langs = count($clang);
?>
<form name="form" method="post" action="admin.php?action=bbcodes&job=del_codefiles">
 <table class="border">
  <tr>
   <td class="obox" colspan="3"><?php echo $lang->phrase('admin_bbc_syntax_highlighting_manager'); ?></b></td>
  </tr>
  <tr>
   <td class="ubox" width="10%"><?php echo $lang->phrase('admin_bbc_delete_all'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /> <?php echo $lang->phrase('admin_bbc_delete_all2'); ?></span></td>
   <td class="ubox" width="45%"><?php echo $lang->phrase('admin_bbc_language'); ?></td>
   <td class="ubox" width="45%"><?php echo $lang->phrase('admin_bbc_file'); ?></td>
  </tr>
<?php foreach ($clang as $row) { ?>
  <tr>
   <td class="mbox" width="10%"><input type="checkbox" name="delete[]" value="<?php echo $row['file']; ?>"></td>
   <td class="mbox" width="45%"><?php echo $row['name']; ?></td>
   <td class="mbox" width="45%"><?php echo $row['file']; ?></td>
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" width="100%" colspan="3" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_bbc_delete'); ?>"></td>
  </tr>
 </table>
</form>
<br>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=explorer&job=upload&cfg=codefiles">
<table class="border">
<tr><td class="obox"><?php echo $lang->phrase('admin_bbc_add_highlighting_files'); ?></td></tr>
<tr><td class="ubox"><?php echo $lang->phrase('admin_bbc_geshi_desc'); ?></td></tr>
<tr><td class="mbox">
<?php echo $lang->phrase('admin_bbc_upload_info'); ?><br /><br />
<?php echo $lang->phrase('admin_bbc_upload_info2'); ?><br /><br />
<strong><?php echo $lang->phrase('admin_bbc_upload_file'); ?></strong>
<br /><input type="file" name="upload_0" size="40" />
</td></tr>
<tr><td class="ubox" align="center"><input accesskey="s" type="submit" value="<?php echo $lang->phrase('admin_bbc_upload'); ?>" /></td></tr>
</table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'del_codefiles') {
	echo head();
	$d = $gpc->get('delete', arr_str);
	foreach ($d as $file) {
		$file = 'classes/geshi/'.$file;
		if (file_exists($file)) {
			$filesystem->unlink($file);
		}
	}
	$delobj = $scache->load('syntaxhighlight');
	$delobj->delete();
	ok('admin.php?action=bbcodes&job=codefiles', $lang->phrase('admin_bbc_files_successfully_deleted'));
}
elseif ($job == 'custombb_export') {
	$id = $gpc->get('id', int);

	$result = $db->query("
	SELECT bbcodetag, bbcodereplacement, bbcodeexample, bbcodeexplanation, twoparams, title, buttonimage
	FROM {$db->pre}bbcode
	WHERE id = '{$id}'
	LIMIT 1
	");
	$data = $db->fetch_assoc($result);
	$data['button'] = null;

	if (!empty($data['buttonimage']) && (preg_match('~^'.URL_REGEXP.'$~i', $data['buttonimage']) || file_exists(CBBC_BUTTONDIR.$data['buttonimage'])) ) {
		if (preg_match('~^'.URL_REGEXP.'$~i', $data['buttonimage'])) {
			$button = get_remote($data['buttonimage']);
		}
		else {
			$button = file_get_contents(CBBC_BUTTONDIR.$data['buttonimage']);
		}
		if ($button == REMOTE_CLIENT_ERROR || $button == REMOTE_INVALID_URL) {
			$data['buttonimage'] = '';
		}
		else {
			$ext = get_extension($data['buttonimage']);
			if (!in_array($ext, $imagetype_extension)) {
				$data['buttonimage'] = '';
			}
			else {
				$data['button'] = base64_encode($button);
			}
		}
	}
	else {
		$data['buttonimage'] = '';
	}

	$content = serialize($data);

	viscacha_header('Content-Type: text/plain');
	viscacha_header('Content-Length: '.strlen($content));
	viscacha_header('Content-Disposition: attachment; filename="'.$data['bbcodetag'].'.bbc"');

	print($content);
}
elseif ($job == 'custombb_import') {
	echo head();
	$file = $gpc->get('file', str);
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=bbcodes&job=custombb_import2">
 <table class="border" cellpadding="4" cellspacing="0" border="0">
  <tr><td class="obox" colspan="2"><?php echo $lang->phrase('admin_bbc_import_design'); ?></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_bbc_either_upload'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_bbc_design_allowed_file_types'); ?> <?php echo formatFilesize(1024*250); ?></span></td>
  <td class="mbox"><input type="file" name="upload" size="40" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_bbc_or_select'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_bbc_path_from_vc_root'); ?> <?php echo $config['fpath']; ?></span></td>
  <td class="mbox"><input type="text" name="server" value="<?php echo $file; ?>" size="50" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_bbc_delete_file_after_import'); ?></td>
  <td class="mbox"><input type="checkbox" name="delete" value="1" checked="checked" /></td></tr>
  <tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" value="<?php echo $lang->phrase('admin_bbc_import'); ?>" /></td></tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'custombb_import2') {

	$dir = $gpc->get('dir', int);
	$server = $gpc->get('server', none);
	$del = $gpc->get('delete', int);
	$inserterrors = array();

	if (!empty($_FILES['upload']['name'])) {
		$filesize = ini_maxupload();
		$dir = 'temp/';

		$insertuploads = array();
		require("classes/class.upload.php");

		$my_uploader = new uploader();
		$my_uploader->max_filesize(1024*250);
		$my_uploader->file_types(array('bbc'));
		$my_uploader->set_path($dir);
		if ($my_uploader->upload('upload')) {
			if ($my_uploader->save_file()) {
				$file = $dir.$my_uploader->fileinfo('filename');
				if (!file_exists($file)) {
					$inserterrors[] = $lang->phrase('admin_bbc_file_not_existing');
				}
			}
		}
		if ($my_uploader->upload_failed()) {
			array_push($inserterrors,$my_uploader->get_error());
		}
	}
	elseif (file_exists($server)) {
		$ext = get_extension($server);
		if ($ext == 'bbc') {
			$file = $server;
		}
		else {
			$inserterrors[] = $lang->phrase('admin_bbc_no_bbc_file');
		}
	}
	else {
		$inserterrors[] = $lang->phrase('admin_bbc_no_valid_file');
	}
	echo head();
	if (count($inserterrors) > 0) {
		error('admin.php?action=bbcodes&job=custombb_import', $inserterrors);
	}

	$content = file_get_contents($file);
	$bb = unserialize($content);

	if (empty($bb['bbcodetag']) || empty($bb['bbcodereplacement']) || empty($bb['bbcodeexample'])) {
		error('admin.php?action=bbcodes&job=custombb_import', $lang->phrase('admin_bbc_bbc_corrupt'));
	}

	$bb = array_map(array($db, 'escape_string'), $bb);

	$result = $db->query("SELECT * FROM {$db->pre}bbcode WHERE bbcodetag = '{$bb['bbcodetag']}' AND twoparams = '{$bb['twoparams']}'");
	if ($db->num_rows($result) > 0) {
		$bbcodetag = $bb['bbcodetag'];
		error('admin.php?action=bbcodes&job=custombb_import', $lang->phrase('admin_bbc_bbcode_already_exists'));
	}

	if (empty($bb['button'])) {
		$bb['buttonimage'] = '';
	}
	else {
		$bb['buttonimage'] = basename($bb['buttonimage']);
		if (!file_exists(CBBC_BUTTONDIR.$bb['buttonimage'])) {
			$filesystem->file_put_contents(CBBC_BUTTONDIR.$bb['buttonimage'], base64_decode($bb['button']));
		}
	}

	$db->query("
	INSERT INTO {$db->pre}bbcode (bbcodetag, bbcodereplacement, bbcodeexample, bbcodeexplanation, twoparams, title, buttonimage)
	VALUES ('{$bb['bbcodetag']}','{$bb['bbcodereplacement']}','{$bb['bbcodeexample']}','{$bb['bbcodeexplanation']}','{$bb['twoparams']}','{$bb['title']}','{$bb['buttonimage']}')
	");

	if ($del > 0) {
		$filesystem->unlink($file);
	}

	$delobj = $scache->load('custombb');
	$delobj->delete();

	ok('admin.php?action=bbcodes&job=custombb', $lang->phrase('admin_bbc_bbc_successfully_imported'));
}
elseif ($job == 'custombb_add') {
	echo head();
	?>
	<form action="admin.php?action=bbcodes&job=custombb_add2" name="form2" method="post">
	<table align="center" class="border">
	<tr>
		<td class="obox" align="center" colspan="2"><b><?php echo $lang->phrase('admin_bbc_bbc_add'); ?></b></td>
	</tr>
	<tr>
		<td class="mbox" width="50%"><?php echo $lang->phrase('admin_bbc_title'); ?></td>
		<td class="mbox" width="50%"><input type="text" name="title" value="" size="60" /></td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_bbc_tag'); ?><br />
		<span class="stext"><?php echo $lang->phrase('admin_bbc_tag_desc'); ?></span></td>
		<td class="mbox"><input type="text" name="bbcodetag" value="" size="60" /></td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_bbc_replacement'); ?><br />
		<span class="stext"><?php echo $lang->phrase('admin_bbc_replacement_desc'); ?></span></td>
		<td class="mbox"><textarea name="bbcodereplacement" rows="6" cols="60" wrap="virtual"></textarea></td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_bbc_example'); ?><br />
		<span class="stext"><?php echo $lang->phrase('admin_bbc_example_desc'); ?></span></td>
		<td class="mbox"><input type="text" name="bbcodeexample" value="" size="60" /></td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_bbc_description'); ?><br />
		<span class="stext"><?php echo $lang->phrase('admin_bbc_description_desc'); ?></span></td>
		<td class="mbox"><textarea name="bbcodeexplanation" rows="8" cols="60" wrap="virtual"></textarea></td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_bbc_use_option'); ?><br />
		<span class="stext"><?php echo $lang->phrase('admin_bbc_use_option_desc'); ?></span></td>
		<td class="mbox">
			<input type="radio" name="twoparams" value="1" /> <?php echo $lang->phrase('admin_bbc_yes'); ?><br />
			<input type="radio" name="twoparams" value="0" checked="checked" /> <?php echo $lang->phrase('admin_bbc_no'); ?>
		</td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_bbc_button_image'); ?><br />
		<span class="stext"><?php echo $lang->phrase('admin_bbc_button_image_desc'); ?></span>
		</td>
		<td class="mbox"><input type="text" name="buttonimage" value="" size="60" /></td>
	</tr>
	<tr><td class="ubox" colspan="2" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_bbc_save'); ?>" /></td></tr>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'custombb_add2') {
	$vars = array(
		'title'				=> str,
		'bbcodetag'			=> str,
		'bbcodereplacement' => db_esc,
		'bbcodeexample'		=> str,
		'bbcodeexplanation' => db_esc,
		'twoparams'			=> int,
		'buttonimage'		=> db_esc
	);
	$query = array();
	foreach ($vars as $key => $type) {
		$query[$key] = $gpc->get($key, $type);
	}

	echo head();

	if (!$query['bbcodetag'] OR !$query['bbcodereplacement'] OR !$query['bbcodeexample']) {
		error('admin.php?action=bbcodes&job=custombb_add', $lang->phrase('admin_bbc_please_complete'));
	}

	$result = $db->query("SELECT * FROM {$db->pre}bbcode WHERE bbcodetag = '{$query['bbcodetag']}' AND twoparams = '{$query['twoparams']}'");
	if ($db->num_rows($result) > 0) {
		$bbcodetag = $query['bbcodetag'];
		error('admin.php?action=bbcodes&job=custombb_add', $lang->phrase('admin_bbc_bbcode_already_exists'));
	}

	$db->query("
	INSERT INTO {$db->pre}bbcode (bbcodetag, bbcodereplacement, bbcodeexample, bbcodeexplanation, twoparams, title, buttonimage)
	VALUES ('{$query['bbcodetag']}','{$query['bbcodereplacement']}','{$query['bbcodeexample']}','{$query['bbcodeexplanation']}','{$query['twoparams']}','{$query['title']}','{$query['buttonimage']}')
	");

	$delobj = $scache->load('custombb');
	$delobj->delete();

	ok('admin.php?action=bbcodes&job=custombb');
}
elseif ($job == 'custombb_edit') {
	echo head();
	$id = $gpc->get('id', int);

	$result = $db->query("SELECT * FROM {$db->pre}bbcode WHERE id = ".$id);
	$bbcode = $gpc->prepare($db->fetch_assoc($result));

	?>
	<form action="admin.php?action=bbcodes&job=custombb_edit2&amp;id=<?php echo $bbcode['id']; ?>" name="form2" method="post">
	<table align="center" class="border">
	<tr>
		<td class="obox" align="center" colspan="2"><b><?php echo $lang->phrase('admin_bbc_edit_bbcode'); ?></b></td>
	</tr>
	<tr>
		<td class="mbox" width="50%"><?php echo $lang->phrase('admin_bbc_title'); ?></td>
		<td class="mbox" width="50%"><input type="text" name="title" value="<?php echo $bbcode['title']; ?>" size="60" /></td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_bbc_tag'); ?><br />
		<span class="stext"><?php echo $lang->phrase('admin_bbc_tag_desc'); ?></span></td>
		<td class="mbox">
		 <input type="text" name="bbcodetag" value="<?php echo $bbcode['bbcodetag']; ?>" size="60" />
		 <input type="hidden" name="bbcodetag_old" value="<?php echo $bbcode['bbcodetag']; ?>" />
		</td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_bbc_replacement'); ?><br />
		<span class="stext"><?php echo $lang->phrase('admin_bbc_replacement_desc'); ?></span></td>
		<td class="mbox"><textarea name="bbcodereplacement" rows="6" cols="60" wrap="virtual"><?php echo $bbcode['bbcodereplacement']; ?></textarea></td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_bbc_example'); ?><br />
		<span class="stext"><?php echo $lang->phrase('admin_bbc_example_desc'); ?></span></td>
		<td class="mbox"><input type="text" name="bbcodeexample" value="<?php echo $bbcode['bbcodeexample']; ?>" size="60" /></td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_bbc_description'); ?><br />
		<span class="stext"><?php echo $lang->phrase('admin_bbc_description_desc'); ?></span></td>
		<td class="mbox"><textarea name="bbcodeexplanation" rows="8" cols="60" wrap="virtual"><?php echo $bbcode['bbcodeexplanation']; ?></textarea></td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_bbc_use_option'); ?><br />
		<span class="stext"><?php echo $lang->phrase('admin_bbc_use_option_desc'); ?></span></td>
		<td class="mbox">
			<input type="radio" name="twoparams" value="1"<?php echo iif($bbcode['twoparams'], ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_bbc_yes'); ?><br />
			<input type="radio" name="twoparams" value="0"<?php echo iif(!$bbcode['twoparams'], ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_bbc_no'); ?>
		</td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_bbc_button_image'); ?><br />
		<span class="stext"><?php echo $lang->phrase('admin_bbc_button_image_desc'); ?></span>
		</td>
		<td class="mbox"><input type="text" name="buttonimage" value="<?php echo $bbcode['buttonimage']; ?>" size="60" /></td>
	</tr>
	<tr><td class="ubox" colspan="2" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_bbc_save'); ?>" /></td></tr>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'custombb_edit2') {
	$vars = array(
		'id'				=> int,
		'title'				=> str,
		'bbcodetag'			=> str,
		'bbcodetag_old'		=> str,
		'bbcodereplacement' => db_esc,
		'bbcodeexample'		=> str,
		'bbcodeexplanation' => db_esc,
		'twoparams'			=> int,
		'buttonimage'		=> db_esc
	);
	$query = array();
	foreach ($vars as $key => $type) {
		$query[$key] = $gpc->get($key, $type);
	}

	echo head();

	if (!$query['bbcodetag'] OR !$query['bbcodereplacement'] OR !$query['bbcodeexample']) {
		error('admin.php?action=bbcodes&job=custombb_add', $lang->phrase('admin_bbc_please_complete'));
	}

	if (strtolower($query['bbcodetag']) != strtolower($query['bbcodetag_old'])) {
		$result = $db->query("SELECT * FROM {$db->pre}bbcode WHERE bbcodetag = '{$query['bbcodetag']}' AND twoparams = '{$query['twoparams']}' AND ");
		if ($db->num_rows($result) > 0) {
			$bbcodetag = $query['bbcodetag'];
			error('admin.php?action=bbcodes&job=custombb_add', $lang->phrase('admin_bbc_bbcode_already_exists'));
		}
	}

	$db->query("UPDATE {$db->pre}bbcode SET title = '{$query['title']}',bbcodetag = '{$query['bbcodetag']}',bbcodereplacement = '{$query['bbcodereplacement']}',bbcodeexample = '{$query['bbcodeexample']}',bbcodeexplanation = '{$query['bbcodeexplanation']}',twoparams = '{$query['twoparams']}',buttonimage = '{$query['buttonimage']}' WHERE id = '{$query['id']}'");

	$delobj = $scache->load('custombb');
	$delobj->delete();

	ok('admin.php?action=bbcodes&job=custombb');
}
elseif ($job == 'custombb_delete') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT buttonimage FROM {$db->pre}bbcode WHERE id = '{$id}' LIMIT 1");
	$image = $db->fetch_assoc($result);
	?>
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	<tr><td class="obox"><?php echo $lang->phrase('admin_bbc_delete_custom_bbc'); ?></td></tr>
	<tr><td class="mbox">
	<p align="center"><?php echo $lang->phrase('admin_bbc_delete_bbc_question'); ?></p>
	<p align="center">
	<?php if (!preg_match('~^'.URL_REGEXP.'$~i', $image['buttonimage']) && @file_exists(CBBC_BUTTONDIR.$image['buttonimage'])) { ?>
	<a href="admin.php?action=bbcodes&amp;job=custombb_delete2&amp;id=<?php echo $id; ?>&amp;img=1"><img border="0" align="absmiddle" alt="" src="admin/html/images/yes.gif"> <?php echo $lang->phrase('admin_bbc_including_image'); ?></a><br />
	<a href="admin.php?action=bbcodes&amp;job=custombb_delete2&amp;id=<?php echo $id; ?>"><img border="0" align="absmiddle" alt="" src="admin/html/images/yes.gif"> <?php echo $lang->phrase('admin_bbc_without_image'); ?></a><br />
	<?php } else { ?>
	<a href="admin.php?action=bbcodes&amp;job=custombb_delete2&amp;id=<?php echo $id; ?>"><img border="0" align="absmiddle" alt="" src="admin/html/images/yes.gif"> <?php echo $lang->phrase('admin_bbc_yes'); ?></a><br />
	<?php } ?>
	<br /><a href="admin.php?action=bbcodes&amp;job=custombb"><img border="0" align="absmiddle" alt="" src="admin/html/images/no.gif"> <?php echo $lang->phrase('admin_bbc_no'); ?></a>
	</p>
	</td></tr>
	</table>
	<?php
	echo foot();
}
elseif ($job == 'custombb_delete2'){
	echo head();
	$id = $gpc->get('id', int);
	$img = $gpc->get('img', int);
	if ($img == 1) {
		$result = $db->query("SELECT buttonimage FROM {$db->pre}bbcode WHERE id = '{$id}' LIMIT 1");
		$image = $db->fetch_assoc($result);
		if (!preg_match('~^'.URL_REGEXP.'$~i', $image['buttonimage']) && @file_exists(CBBC_BUTTONDIR.$image['buttonimage'])) {
			$filesystem->unlink(CBBC_BUTTONDIR.$image['buttonimage']);
		}
	}
	$db->query("DELETE FROM {$db->pre}bbcode WHERE id = '{$id}' LIMIT 1");
	$delobj = $scache->load('custombb');
	$delobj->delete();
	ok('admin.php?action=bbcodes&job=custombb', $lang->phrase('admin_bbc_bbc_successfully_deleted'));
}
elseif ($job == 'custombb_test') {
	echo head();
	// reader-Tag not recognized
	$file = 'admin/data/bbcode_test.php';
	$test = $gpc->get('test', none);
	$parsed_test = null;
	if (!empty($test)) {
		file_put_contents($file, $test);
		BBProfile($bbcode);
		$bbcode->setSmileys(1);
		$bbcode->setReplace(0);
		$bbcode->setAuthor($my->id);
		$parsed_test = $bbcode->parse($test);
		$smileys_time = round($bbcode->getBenchmark('smileys'), 3);
		$bbcode_time = round($bbcode->getBenchmark(), 3);
	}
	else {
		$test = file_get_contents($file);
	}
	if (!empty($parsed_test)) {
?>
<table align="center" class="border">
  	<tr><td class="obox"><?php echo $lang->phrase('admin_bbc_parsing_results'); ?></td></tr>
  	<tr><td class="ubox">
  		<strong><?php echo $lang->phrase('admin_bbc_benchmark'); ?></strong><br />
  	 	<?php echo $lang->phrase('admin_bbc_smileys'); ?> <?php echo $smileys_time; ?> <?php echo $lang->phrase('admin_bbc_seconds'); ?><br />
  		<?php echo $lang->phrase('admin_bbc_bbcs'); ?> <?php echo $bbcode_time; ?> <?php echo $lang->phrase('admin_bbc_seconds'); ?><br />
  	</td></tr>
  	<tr><td class="mbox"><?php echo $parsed_test; ?></td></tr>
</table>
<br /><?php } ?>
<form action="admin.php?action=bbcodes&job=custombb_test" name="form2" method="post">
	<table align="center" class="border">
  		<tr><td class="obox"><?php echo $lang->phrase('admin_bbc_test_custom_bbc'); ?></td></tr>
		<tr><td class="mbox" align="center"><textarea name="test" rows="10" cols="120"><?php echo $test; ?></textarea></td></tr>
		<tr><td class="ubox" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_bbc_test'); ?>" /></td></tr>
	</table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'custombb') {
	$result = $db->query("SELECT * FROM {$db->pre}bbcode");
	echo head();
	?>
	<table align="center" class="border">
	<tr>
		<td class="obox" colspan="4"><span class="right">
		<a class="button" href="admin.php?action=bbcodes&job=custombb_add"><?php echo $lang->phrase('admin_bbc_bbc_add'); ?></a>
		<a class="button" href="admin.php?action=packages&amp;job=browser&amp;type=<?php echo IMPTYPE_BBCODE; ?>"><?php echo $lang->phrase('admin_bbc_browse_bbc'); ?></a>
		<a class="button" href="admin.php?action=bbcodes&job=custombb_import"><?php echo $lang->phrase('admin_bbc_bbc_import'); ?></a>
		<a class="button" href="admin.php?action=bbcodes&job=custombb_test"><?php echo $lang->phrase('admin_bbc_test_bbc'); ?></a>
		</span><?php echo $lang->phrase('admin_bbc_custom_bbc_manager'); ?></td>
	</tr>
	<tr>
		<td class="ubox" width="30%"><?php echo $lang->phrase('admin_bbc_title'); ?></td>
		<td class="ubox" width="35%"><?php echo $lang->phrase('admin_bbc_bbc'); ?></td>
		<td class="ubox" width="10%"><?php echo $lang->phrase('admin_bbc_button_image'); ?></td>
		<td class="ubox" width="25%"><?php echo $lang->phrase('admin_bbc_action'); ?></td>
	</tr>
	<?php
	while ($bbcode = $db->fetch_assoc($result)) {
		if (!empty($bbcode['buttonimage'])) {
			if (!preg_match('~^'.URL_REGEXP.'$~i', $bbcode['buttonimage'])) {
				$bbcode['buttonimage'] = CBBC_BUTTONDIR.$bbcode['buttonimage'];
			}
			$src = "<img style=\"background-color: buttonface; border:solid 1px highlight;\" src=\"{$bbcode['buttonimage']}\" alt=\"\" />";
		}
		else {
			$src = '-';
		}
		?>
		<tr>
			<td class="mbox"><?php echo $bbcode['title']; ?></td>
			<td class="mbox"><code>[<?php echo $bbcode['bbcodetag'].iif($bbcode['twoparams'], '={option}'); ?>]{param}[/<?php echo $bbcode['bbcodetag']; ?>]</code></td>
			<td class="mbox" align="center"><?php echo $src; ?></td>
			<td class="mbox">
			<a class="button" href="admin.php?action=bbcodes&job=custombb_edit&id=<?php echo $bbcode['id']; ?>"><?php echo $lang->phrase('admin_bbc_edit'); ?></a>
			<a class="button" href="admin.php?action=bbcodes&job=custombb_export&id=<?php echo $bbcode['id']; ?>"><?php echo $lang->phrase('admin_bbc_export'); ?></a>
			<a class="button" href="admin.php?action=bbcodes&job=custombb_delete&id=<?php echo $bbcode['id']; ?>"><?php echo $lang->phrase('admin_bbc_delete'); ?></a>
			</td>
		</tr>
	<?php } ?>
	</table>
	<?php
}
?>