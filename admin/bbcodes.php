<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

($code = $plugins->load('admin_bbcodes_jobs')) ? eval($code) : null;

if ($job == 'smileys_delete') {
	$deleteid = $gpc->get('id', arr_int);
	if (count($deleteid) > 0) {
	   	$delobj = $scache->load('smileys');
	   	$delobj->delete();
	   	$result = $db->query('SELECT * FROM '.$db->pre.'smileys WHERE id IN ('.implode(',', $deleteid).')',__LINE__,__FILE__);
	   	while ($row = $db->fetch_assoc($result)) {
	   		$row['replace'] = str_replace('{folder}', $config['smileypath'], $row['replace']);
	   		if(file_exists($row['replace'])) {
	   			$filesystem->unlink($row['replace']);
	   		}
	   	}
		$db->query('DELETE FROM '.$db->pre.'smileys WHERE id IN ('.implode(',', $deleteid).')',__LINE__,__FILE__);
		$anz = $db->affected_rows();
	}
	else {
		$anz = 'No';
	}
	echo head();
	ok('admin.php?action=bbcodes&job=smileys', $anz.' entries were deleted successfully!');
}
elseif ($job == 'smileys_edit') {
	$editid = $gpc->get('id', arr_int);
	if (count($editid) == 0) {
		viscacha_header('Location: admin.php?action=bbcodes&job=smileys');
		exit;
	}
	$result = $db->query('SELECT * FROM '.$db->pre.'smileys WHERE id IN ('.implode(',', $editid).')',__LINE__,__FILE__);
	echo head();
	?>
<form name="form" method="post" enctype="multipart/form-data" action="admin.php?action=bbcodes&job=smileys_edit2">
 <table class="border">
  <tr>
   <td class="obox">Edit <?php echo count($editid); ?> Smileys</td>
  </tr>
  <tr>
   <td class="ubox" align="center"><input type="submit" name="Submit" value="Send"></td>
  </tr>
 </table><br />
 <?php while($row = $db->fetch_assoc($result)) { ?>
 <input type="hidden" name="id[]" value="<?php echo $row['id']; ?>">
 <table class="border">
  <tr>
   <td class="mbox" width="50%">Code:</td>
   <td class="mbox" width="50%"><input type="text" name="search_<?php echo $row['id']; ?>" size="50" value="<?php echo $row['search']; ?>"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Image:<br><span class="stext">URL or relative path to the image.<br />{folder} is a placeholder for the adress to the smiley directory.</span></td>
   <td class="mbox" width="50%"><input type="text" name="replace_<?php echo $row['id']; ?>" size="50" value="<?php echo $row['replace']; ?>"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Description:<br><span class="stext">Optional</span></td>
   <td class="mbox" width="50%"><input type="text" name="desc_<?php echo $row['id']; ?>" size="50" value="<?php echo $row['desc']; ?>"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Show directly:<br><span class="stext">Indicates whether the smiley is directly placed next to the BB codes or only in the popup menu.</span></td>
   <td class="mbox" width="50%"><input type="checkbox" name="show_<?php echo $row['id']; ?>" value="1"<?php echo iif($row['show'] == 1, ' checked="checked"'); ?>></td>
  </tr>
 </table><br />
 <?php } ?>
 <table class="border">
  <tr>
   <td class="ubox" align="center"><input type="submit" name="Submit" value="Send"></td>
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
		$replace = $gpc->get('replace_'.$i, str);
		$desc = $gpc->get('desc_'.$i, str);
		$show = $gpc->get('show_'.$i, int);
		$db->query("UPDATE {$db->pre}smileys AS s SET s.search = '{$search}', s.replace = '{$replace}', s.desc = '{$desc}', s.show = '{$show}' WHERE s.id = '{$i}' LIMIT 1",__LINE__,__FILE__);
	}
	$delobj = $scache->load('smileys');
	$delobj->delete();
	ok('admin.php?action=bbcodes&job=smileys', count($id).' Smileys wurden editiert.');
}
elseif ($job == 'smileys_import') {
	echo head();
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=bbcodes&job=smileys_import2">
 <table class="border" cellpadding="4" cellspacing="0" border="0">
  <tr><td class="obox" colspan="2">Import Smileypack</td></tr>
  <tr><td class="mbox"><em>Either</em> upload a file:<br /><span class="stext">Allowed file types: .zip - Maximum file size: 2 MB</span></td>
  <td class="mbox"><input type="file" name="upload" size="40" /></td></tr>
  <tr><td class="mbox"><em>or</em> select a file from the server:<br /><span class="stext">Path starting from the Viscacha-root-directory: <?php echo $config['fpath']; ?></span></td>
  <td class="mbox"><input type="text" name="server" size="50" /></td></tr>
  <tr><td class="mbox">Format of imported Smileypack:</td>
  <td class="mbox"><select name="format">
  <option value="viscacha_ini" selected="selected">Viscacha (Standard)</option>
  <option value="phpbb2">phpBB 2</option>
  <option value="none">No format</option>
  </select></td></tr>
  <tr><td class="mbox">Delete all existing smileys before import:</td>
  <td class="mbox"><input type="checkbox" name="truncate" value="1" /></td></tr>
  <tr><td class="mbox">Delete file after import:</td>
  <td class="mbox"><input type="checkbox" name="delete" value="1" checked="checked" /></td></tr>
  <tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" value="Import" /></td></tr>
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
			$inserterrors[] = 'File ('.$file.') does not exist.';
		}
	}
	elseif (file_exists($server)) {
		$ext = get_extension($server);
		if ($ext == 'zip') {
			$file = $server;
		}
		else {
			$inserterrors[] = 'The selected file is no ZIP-file.';
		}
	}
	else {
		$inserterrors[] = 'No valid file selected.';
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
		rmdirr($tempdir);
		error('admin.php?action=bbcodes&job=smileys_import', 'ZIP-archive could not be read or the folder is empty.');
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
				error('admin.php?action=bbcodes&job=smileys_import', 'smileys.ini is missing');
			}
			$package = $myini->read($tempdir.'/smileys.ini');
		break;
	}

	// Delete old smileys
	$codes = array();
	if ($truncate == 1) {
	   	$result = $db->query('SELECT * FROM '.$db->pre.'smileys',__LINE__,__FILE__);
	   	while ($row = $db->fetch_assoc($result)) {
	   		$row['replace'] = str_replace('{folder}', $config['smileypath'], $row['replace']);
	   		if(file_exists($row['replace'])) {
	   			$filesystem->unlink($row['replace']);
	   		}
	   	}
		$db->query('TRUNCATE TABLE '.$db->pre.'smileys',__LINE__,__FILE__);
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
		$sqlinsert[] = '("'.$gpc->save_str($ini['search']).'", "'.$gpc->save_str($ini['replace']).'", "'.$gpc->save_str($ini['desc']).'")';
	}
	$db->query('INSERT INTO '.$db->pre.'smileys (`search`, `replace`, `desc`) VALUES '.implode(', ', $sqlinsert));
	$anz = $db->affected_rows();

	unset($archive);
	if ($del > 0) {
		$filesystem->unlink($file);
	}
	rmdirr($tempdir);

	$delobj = $scache->load('smileys');
	$delobj->delete();

	ok('admin.php?action=bbcodes&job=smileys', $anz.' Smileys successfully imported.');
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
?>
<form name="form" method="post" action="admin.php?action=bbcodes">
 <table class="border">
  <tr>
   <td class="obox" colspan="6"><span style="float: right;"><a class="button" href="admin.php?action=bbcodes&amp;job=smileys_import">Import Smileypack</a></span>Manage Smileys (<?php echo $db->num_rows($result); ?> Smileys)</td>
  </tr>
  <tr class="ubox">
   <td width="5%">Choose<br /><span class="stext"><input type="checkbox" onclick="check_all('id[]');" name="all" value="1" /> All</span></td>
   <td width="10%">Code</td>
   <td width="30%">URL</td>
   <td width="15%">Images</td>
   <td width="5%">Show directly</td>
   <td width="35%">Description</td>
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
    <option value="smileys_edit" selected="selected">Edit</option>
    <option value="smileys_export">Export</option>
   	<option value="smileys_delete">Delete</option>
   </select>&nbsp;&nbsp;&nbsp;&nbsp;
   <input type="submit" value="Go">
   </td>
  </tr>
 </table>
</form>
<br>
<form name="form" method="post" enctype="multipart/form-data" action="admin.php?action=bbcodes&amp;job=smileys_add">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><span style="float: right;"><a class="button" href="admin.php?action=bbcodes&amp;job=smileys_import">Import Smileypack</a></span>Add Smiley</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Code:</td>
   <td class="mbox" width="50%"><input type="text" name="code" size="50"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Image:<br><span class="stext">URL or relative path to the image. Only when you do not upload an image.<br />{folder} = <?php echo $config['smileypath']; ?> and <?php echo $config['smileyurl']; ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="img" size="50"></td>
  </tr>
   <td class="mbox" width="50%">Upload an image<br><span class="stext">Allowed file types: .gif, .jpg, .jpeg, .png, .jpe, .bmp<br />Maximum file size: 200 KB</span></td>
   <td class="mbox" width="50%"><input type="file" name="upload" size="40" /></td>
  <tr>
   <td class="mbox" width="50%">Description:<br><span class="stext">Optional</span></td>
   <td class="mbox" width="50%"><input type="text" name="desc" size="50"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Show directly:<br><span class="stext">Indicates whether the smiley is directly placed next to the BB codes or only in the popup menu.</span></td>
   <td class="mbox" width="50%"><input type="checkbox" name="show" value="1"></td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Add"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'smileys_ajax_pos') {
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT b.show FROM {$db->pre}smileys AS b WHERE id = '{$id}' LIMIT 1",__LINE__,__FILE__);
	$use = $db->fetch_assoc($result);
	$use = invert($use['show']);
	$db->query("UPDATE {$db->pre}smileys AS b SET b.show = '{$use}' WHERE id = '{$id}' LIMIT 1",__LINE__,__FILE__);
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
		$error[] = 'Code is too short';
	}
	if (empty($has_upload) && empty($img)) {
		$error[] = 'Path of image is too short';
	}
	if (strlen($gpc->get('show', int)) != 1 && $gpc->get('show', int) != 0) {
		$error[] = 'Wrong specification(s)';
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
	$db->query("INSERT INTO {$db->pre}smileys (`search`,`replace`,`desc`,`show`) VALUES ('".$gpc->get('code', str)."','".$img."','".$gpc->get('desc', str)."','".$gpc->get('show', int)."')",__LINE__,__FILE__);

	$delobj = $scache->load('smileys');
	$delobj->delete();

	ok('admin.php?action=bbcodes&job=smileys', 'Smiley successfully added');
}
elseif ($job == 'word') {
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}textparser WHERE type = 'word'",__LINE__,__FILE__);
?>
<form name="form" method="post" action="admin.php?action=bbcodes&job=del&tp=word">
 <table class="border">
  <tr>
   <td class="obox" colspan="4">Manage Glossary</b></td>
  </tr>
  <tr>
   <td class="ubox" width="5%">Delete<br /><span class="stext"><input type="checkbox" onclick="check_all('delete[]');" name="all" value="1" /> All</span></td>
   <td class="ubox" width="15%">Abbreviation</td>
   <td class="ubox" width="30%">Phrase</td>
   <td class="ubox" width="50%">Description</td>
  </tr>
<?php while ($row = $db->fetch_assoc($result)) { ?>
  <tr>
   <td class="mbox" width="5%"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
   <td class="mbox" width="15%"><?php echo $row['search']; ?></td>
   <td class="mbox" width="30%"><?php echo $row['replace']; ?></td>
   <td class="mbox" width="50%"><?php echo $row['desc']; ?></td>
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" width="100%" colspan="4" align="center"><input type="submit" name="Submit" value="Delete"></td>
  </tr>
 </table>
</form>
<br>
<form name="form" method="post" action="admin.php?action=bbcodes&job=add&tp=word">
 <table class="border">
  <tr>
   <td class="obox" colspan="2">Add Word</b></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Abbreviation:</td>
   <td class="mbox" width="50%"><input type="text" name="temp1" size="50"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Phrase:</td>
   <td class="mbox" width="50%"><input type="text" name="temp2" size="50"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Description:</td>
   <td class="mbox" width="50%"><textarea name="temp3" cols="50" rows="3"></textarea></td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Add"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'censor') {
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}textparser WHERE type = 'censor'",__LINE__,__FILE__);
?>
<form name="form" method="post" action="admin.php?action=bbcodes&job=del&tp=censor">
 <table class="border">
  <tr>
   <td class="obox" colspan="3">Manage Censorship</b></td>
  </tr>
  <tr>
   <td class="ubox" width="10%">Delete<br /><span class="stext"><input type="checkbox" onclick="check_all('delete[]');" name="all" value="1" /> All</span></td>
   <td class="ubox" width="45%">Word</td>
   <td class="ubox" width="45%">Censored Word</td>
  </tr>
<?php while ($row = $db->fetch_assoc($result)) { ?>
  <tr>
   <td class="mbox" width="10%"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
   <td class="mbox" width="45%"><?php echo $row['search']; ?></td>
   <td class="mbox" width="45%"><?php echo $row['replace']; ?></td>
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" width="100%" colspan=3 align="center"><input type="submit" name="Submit" value="Delete"></td>
  </tr>
 </table>
</form>
<br>
<form name="form" method="post" action="admin.php?action=bbcodes&job=add&tp=censor">
 <table class="border">
  <tr>
   <td class="obox" colspan=2>Add Word</b></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Word:</td>
   <td class="mbox" width="50%"><input type="text" name="temp1" size="50"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Censored Word:</td>
   <td class="mbox" width="50%"><input type="text" name="temp2" size="50"></td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Add"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'replace') {
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}textparser WHERE type = 'replace'",__LINE__,__FILE__);
?>
<form name="form" method="post" action="admin.php?action=bbcodes&job=del&tp=replace">
 <table class="border">
  <tr>
   <td class="obox" colspan="3">Manage Vocabulary</b></td>
  </tr>
  <tr>
   <td class="ubox" width="10%">Delete<br /><span class="stext"><input type="checkbox" onclick="check_all('delete[]');" name="all" value="1" /> All</span></td>
   <td class="ubox" width="45%">Word</td>
   <td class="ubox" width="45%">Replacement</td>
  </tr>
<?php while ($row = $db->fetch_assoc($result)) { ?>
  <tr>
   <td class="mbox" width="10%"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
   <td class="mbox" width="45%"><?php echo $row['search']; ?></td>
   <td class="mbox" width="45%"><?php echo $row['replace']; ?></td>
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" width="100%" colspan=3 align="center"><input type="submit" name="Submit" value="Delete"></td>
  </tr>
 </table>
</form>
<br>
<form name="form" method="post" action="admin.php?action=bbcodes&job=add&tp=replace">
<input name="tp" value="replace" type="hidden">
 <table class="border">
  <tr>
   <td class="obox" colspan=2>Add Word</b></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Word:</td>
   <td class="mbox" width="50%"><input type="text" name="temp1" size="50"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Replacement:</td>
   <td class="mbox" width="50%"><input type="text" name="temp2" size="50"></td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Add"></td>
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
		$error[] = "No valid type given";
	}
	if (strlen($gpc->get('temp1', str)) < 2) {
		$error[] = "Word is too short";
	}
	if (strlen($gpc->get('temp2', str)) < 2) {
		$error[] = "Replacement/Censored Word/Phrase is too short";
	}
	if (strlen($gpc->get('temp3', str)) < 2 && $type == 'word') {
		$error[] = "Description is too short";
	}
	if (count($error) > 0) {
		error('admin.php?action=bbcodes&job='.$type, $error);
	}

	$db->query("INSERT INTO {$db->pre}textparser (`search`,`replace`,`type`,`desc`) VALUES ('".$gpc->get('temp1', str)."','".$gpc->get('temp2', str)."','{$type}','".$gpc->get('temp3', str)."')",__LINE__,__FILE__);

	$delobj = $scache->load('bbcode');
	$delobj->delete();

	ok('admin.php?action=bbcodes&job='.$type, 'Data successfully added!');
}
elseif ($job == 'del') {
	echo head();
	$delete = $gpc->get('delete', arr_int);
	$type = $gpc->get('tp', str);
	if (count($delete) == 0) {
		error('admin.php?action=bbcodes&job='.$type, 'You did not enter a valid selection.');
	}
	$db->query('DELETE FROM '.$db->pre.'textparser WHERE id IN ('.implode(',',$delete).')',__LINE__,__FILE__);
	$anz = $db->affected_rows();
	$delobj = $scache->load('bbcode');
	$delobj->delete();
	ok('admin.php?action=bbcodes&job='.$type, $anz.' entries were deleted successfully!');
}
elseif ($job == 'codefiles') {
	echo head();
	include_once('classes/class.geshi.php');
	$clang = array();
	$d = dir("classes/geshi");
	while (false !== ($entry = $d->read())) {
		if (get_extension($entry) == 'php' && !is_dir("classes/geshi/".$entry)) {
			include_once("classes/geshi/".$entry);
			$short = str_replace('.php','',$entry);
			$clang[$short]['file'] = $entry;
			$clang[$short]['name'] = $language_data['LANG_NAME'];
		}
	}
	$d->close();
	asort($clang);
?>
<form name="form" method="post" action="admin.php?action=bbcodes&job=del_codefiles">
 <table class="border">
  <tr>
   <td class="obox" colspan="3">Syntax Highlighting Manager (<?php echo count($clang); ?> Languages)</b></td>
  </tr>
  <tr>
   <td class="ubox" width="10%">Delete<br /><span class="stext"><input type="checkbox" onclick="check_all('delete[]');" name="all" value="1" /> All</span></td>
   <td class="ubox" width="45%">Language</td>
   <td class="ubox" width="45%">File</td>
  </tr>
<?php foreach ($clang as $row) { ?>
  <tr>
   <td class="mbox" width="10%"><input type="checkbox" name="delete[]" value="<?php echo $row['file']; ?>"></td>
   <td class="mbox" width="45%"><?php echo $row['name']; ?></td>
   <td class="mbox" width="45%"><?php echo $row['file']; ?></td>
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" width="100%" colspan="3" align="center"><input type="submit" name="Submit" value="Delete"></td>
  </tr>
 </table>
</form>
<br>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=explorer&job=upload&cfg=codefiles">
<table class="border">
<tr><td class="obox">Add Syntax Highlighting Files</td></tr>
<tr><td class="ubox">GeSHi is used a syntax highlighter. You can use all compatible language files you can get from <a href="http://qbnz.com/highlighter/" target="_blank">http://qbnz.com/highlighter/</a>. Just download GeSHi and upload the language files you want. To create your own language files just read the GeSHi documentation on how to create them.</td></tr>
<tr><td class="mbox">
To attach a file, click on the &quot;browse&quot;-button and select a file.
Then click on &quot;upload&quot; in order to complete the procedure.<br /><br />
Allowed file types: .php<br />
Maximum file size: 200 KB<br /><br />
<strong>Upload file:</strong>
<br /><input type="file" name="upload_0" size="40" />
</td></tr>
<tr><td class="ubox" align="center"><input accesskey="s" type="submit" value="Upload" /></td></tr>
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
    ok('admin.php?action=bbcodes&job=codefiles', 'Files successfully deleted!');
}
elseif ($job == 'custombb_export') {
	$id = $gpc->get('id', int);

	$result = $db->query("
	SELECT bbcodetag, bbcodereplacement, bbcodeexample, bbcodeexplanation, twoparams, title, buttonimage
	FROM {$db->pre}bbcode
	WHERE id = '{$id}'
	LIMIT 1
	", __LINE__, __FILE__);
	$data = $db->fetch_assoc($result);
	$data['button'] = null;

	if (!empty($data['buttonimage']) && (file_exists($data['buttonimage']) || preg_match('/^(http:\/\/|www.)([\wäöüÄÖÜ@\-_\.]+)\:?([0-9]*)\/(.*)$/', $data['buttonimage'])) ) {
		if (preg_match('/^(http:\/\/|www.)([\wäöüÄÖÜ@\-_\.]+)\:?([0-9]*)\/(.*)$/', $data['buttonimage'])) {
			$button = get_remote($data['buttonimage']);
		}
		else {
			$button = file_get_contents($data['buttonimage']);
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
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=bbcodes&job=custombb_import2">
 <table class="border" cellpadding="4" cellspacing="0" border="0">
  <tr><td class="obox" colspan="2">Import a new Design</td></tr>
  <tr><td class="mbox"><em>Either</em> upload a file:<br /><span class="stext">Allowed file types: .bbc - Maximum file size: <?php echo formatFilesize(1024*250); ?></span></td>
  <td class="mbox"><input type="file" name="upload" size="40" /></td></tr>
  <tr><td class="mbox"><em>or</em> select a file from the server:<br /><span class="stext">Path starting from the Viscacha-root-directory: <?php echo $config['fpath']; ?></span></td>
  <td class="mbox"><input type="text" name="server" size="50" /></td></tr>
  <tr><td class="mbox">Delete file after import:</td>
  <td class="mbox"><input type="checkbox" name="delete" value="1" checked="checked" /></td></tr>
  <tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" value="Import" /></td></tr>
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
		$filetypes = array('bbc');
		$dir = 'temp/';

		$insertuploads = array();
		require("classes/class.upload.php");

		$my_uploader = new uploader();
		$my_uploader->max_filesize(1024*250);
		$my_uploader->file_types($filetypes);
		$my_uploader->set_path($dir);
		if ($my_uploader->upload('upload')) {
			if ($my_uploader->save_file()) {
				$file = $dir.$my_uploader->fileinfo('filename');
				if (!file_exists($file)) {
					$inserterrors[] = 'File ('.$file.') does not exist.';
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
			$inserterrors[] = 'The selected file is no BBC-file.';
		}
	}
	else {
		$inserterrors[] = 'No valid file selected.';
	}
	echo head();
	if (count($inserterrors) > 0) {
		error('admin.php?action=bbcodes&job=custombb_import', $inserterrors);
	}

	$content = file_get_contents($file);
	extract(unserialize($content));

	if (empty($bbcodetag) || empty($bbcodereplacement) || empty($bbcodeexample)) {
		error('admin.php?action=bbcodes&job=custombb_import', 'File not valid!');
	}

	$result = $db->query("SELECT * FROM {$db->pre}bbcode WHERE bbcodetag = '{$bbcodetag}' AND twoparams = '{$twoparams}'", __LINE__, __FILE__);
	if ($db->num_rows($result) > 0) {
		error('admin.php?action=bbcodes&job=custombb_import', 'There is already a BB-Code named &quot;'.$bbcodetag.'&quot;. You may not create duplicate names.');
	}

	if (empty($button)) {
		$buttonimage = '';
	}
	else {
		$name = basename($buttonimage);
		$buttonimage = "images/{$name}";
		if (!file_exists($buttonimage)) {
			$filesystem->file_put_contents($buttonimage, base64_decode($button));
		}
	}

	$db->query("
	INSERT INTO {$db->pre}bbcode (bbcodetag, bbcodereplacement, bbcodeexample, bbcodeexplanation, twoparams, title, buttonimage)
	VALUES ('{$bbcodetag}','{$bbcodereplacement}','{$bbcodeexample}','{$bbcodeexplanation}','{$twoparams}','{$title}','{$buttonimage}')
	", __LINE__, __FILE__);

	if ($del > 0) {
		$filesystem->unlink($file);
	}

	$delobj = $scache->load('custombb');
	$delobj->delete();

	ok('admin.php?action=bbcodes&job=custombb', 'BB-Code ('.$title.') successfully imported!');
}
elseif ($job == 'custombb_add') {
	echo head();
	?>
	<form action="admin.php?action=bbcodes&job=custombb_add2" name="form2" method="post">
	<table align="center" class="border">
	<tr>
		<td class="obox" align="center" colspan="2"><b>Add new BB Code</b></td>
	</tr>
	<tr>
		<td class="mbox" width="50%">Title</td>
		<td class="mbox" width="50%"><input type="text" name="title" value="" size="60" /></td>
	</tr>
	<tr>
		<td class="mbox">Tag<br />
		<span class="stext">This is the text for the BB code, which goes inside the square brackets.</span></td>
		<td class="mbox"><input type="text" name="bbcodetag" value="" size="60" /></td>
	</tr>
	<tr>
		<td class="mbox">Replacement<br />
		<span class="stext">This is the HTML code for the BB code replacement. Make sure that you include '{param}' (without the quotes) to insert the text between the opening and closing BB code tags, and '{option}' for the parameter within the BB code tag. You can only use {option} if 'Use Option' is set to yes.</span></td>
		<td class="mbox"><textarea name="bbcodereplacement" rows="6" cols="60" wrap="virtual"></textarea></td>
	</tr>
	<tr>
		<td class="mbox">Example<br />
		<span class="stext">This is a sample piece of BB code to use as an example for this particular BB code.</span></td>
		<td class="mbox"><input type="text" name="bbcodeexample" value="" size="60" /></td>
	</tr>
	<tr>
		<td class="mbox">Description<br />
		<span class="stext">This is a piece of text to describe the BB code tag. This can include HTML tags if you wish.</span></td>
		<td class="mbox"><textarea name="bbcodeexplanation" rows="8" cols="60" wrap="virtual"></textarea></td>
	</tr>
	<tr>
		<td class="mbox">Use {option}<br />
		<span class="stext">Setting this option to yes will allow you to create a [tag=option][/tag] style tag, rather than just a [tag][/tag] style tag.</span></td>
		<td class="mbox">
			<input type="radio" name="twoparams" value="1" />Yes<br />
			<input type="radio" name="twoparams" value="0" checked="checked" />No
		</td>
	</tr>
	<tr>
		<td class="mbox">Button Image<br />
		<span class="stext">Optional - If you would like this bbcode to appear as a clickable button on the message editor toolbar, enter the URL of an image 21 x 20 pixels in size that will act as the button to insert this bbcode.</span>
		</td>
		<td class="mbox"><input type="text" name="buttonimage" value="" size="60" /></td>
	</tr>
	<tr><td class="ubox" colspan="2" align="center"><input type="submit" value="Save" /></td></tr>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'custombb_add2') {
	$vars = array(
		'title'				=> str,
		'bbcodetag'			=> str,
		'bbcodereplacement' => str,
		'bbcodeexample'		=> str,
		'bbcodeexplanation' => str,
		'twoparams'			=> int,
		'buttonimage'		=> str
	);
	$query = array();
	foreach ($vars as $key => $type) {
		$query[$key] = $gpc->get($key, $type);
	}

	echo head();

	if (!$query['bbcodetag'] OR !$query['bbcodereplacement'] OR !$query['bbcodeexample']) {
		error('admin.php?action=bbcodes&job=custombb_add', 'Please complete all required fields');
	}

	$result = $db->query("SELECT * FROM {$db->pre}bbcode WHERE bbcodetag = '{$query['bbcodetag']}' AND twoparams = '{$query['twoparams']}'", __LINE__, __FILE__);
	if ($db->num_rows($result) > 0) {
		error('admin.php?action=bbcodes&job=custombb_add', 'There is already a BB Code named &quot;'.$query['bbcodetag'].'&quot;. You may not create duplicate names.');
	}

	$db->query("
	INSERT INTO {$db->pre}bbcode (bbcodetag, bbcodereplacement, bbcodeexample, bbcodeexplanation, twoparams, title, buttonimage)
	VALUES ('{$query['bbcodetag']}','{$query['bbcodereplacement']}','{$query['bbcodeexample']}','{$query['bbcodeexplanation']}','{$query['twoparams']}','{$query['title']}','{$query['buttonimage']}')
	", __LINE__, __FILE__);

	$delobj = $scache->load('custombb');
	$delobj->delete();

	ok('admin.php?action=bbcodes&job=custombb');
}
elseif ($job == 'custombb_edit') {
	echo head();
	$id = $gpc->get('id', int);

	$result = $db->query("SELECT * FROM {$db->pre}bbcode WHERE id = ".$id, __LINE__, __FILE__);
	$bbcode = $gpc->prepare($db->fetch_assoc($result));

	?>
	<form action="admin.php?action=bbcodes&job=custombb_edit2&amp;id=<?php echo $bbcode['id']; ?>" name="form2" method="post">
	<table align="center" class="border">
	<tr>
		<td class="obox" align="center" colspan="2"><b>Edit a BB Code</b></td>
	</tr>
	<tr>
		<td class="mbox" width="50%">Title</td>
		<td class="mbox" width="50%"><input type="text" name="title" value="<?php echo $bbcode['title']; ?>" size="60" /></td>
	</tr>
	<tr>
		<td class="mbox">Tag<br />
		<span class="stext">This is the text for the BB code, which goes inside the square brackets.</span></td>
		<td class="mbox">
		 <input type="text" name="bbcodetag" value="<?php echo $bbcode['bbcodetag']; ?>" size="60" />
		 <input type="hidden" name="bbcodetag_old" value="<?php echo $bbcode['bbcodetag']; ?>" />
		</td>
	</tr>
	<tr>
		<td class="mbox">Replacement<br />
		<span class="stext">This is the HTML code for the BB code replacement. Make sure that you include '{param}' (without the quotes) to insert the text between the opening and closing BB code tags, and '{option}' for the parameter within the BB code tag. You can only use {option} if 'Use Option' is set to yes.</span></td>
		<td class="mbox"><textarea name="bbcodereplacement" rows="6" cols="60" wrap="virtual"><?php echo $bbcode['bbcodereplacement']; ?></textarea></td>
	</tr>
	<tr>
		<td class="mbox">Example<br />
		<span class="stext">This is a sample piece of BB code to use as an example for this particular BB code.</span></td>
		<td class="mbox"><input type="text" name="bbcodeexample" value="<?php echo $bbcode['bbcodeexample']; ?>" size="60" /></td>
	</tr>
	<tr>
		<td class="mbox">Description<br />
		<span class="stext">This is a piece of text to describe the BB code tag. This can include HTML tags if you wish.</span></td>
		<td class="mbox"><textarea name="bbcodeexplanation" rows="8" cols="60" wrap="virtual"><?php echo $bbcode['bbcodeexplanation']; ?></textarea></td>
	</tr>
	<tr>
		<td class="mbox">Use {option}<br />
		<span class="stext">Setting this option to yes will allow you to create a [tag=option][/tag] style tag, rather than just a [tag][/tag] style tag.</span></td>
		<td class="mbox">
			<input type="radio" name="twoparams" value="1"<?php echo iif($bbcode['twoparams'], ' checked="checked"'); ?> />Yes<br />
			<input type="radio" name="twoparams" value="0"<?php echo iif(!$bbcode['twoparams'], ' checked="checked"'); ?> />No
		</td>
	</tr>
	<tr>
		<td class="mbox">Button Image<br />
		<span class="stext">Optional - If you would like this bbcode to appear as a clickable button on the message editor toolbar, enter the URL of an image 21 x 20 pixels in size that will act as the button to insert this bbcode.</span>
		</td>
		<td class="mbox"><input type="text" name="buttonimage" value="<?php echo $bbcode['buttonimage']; ?>" size="60" /></td>
	</tr>
	<tr><td class="ubox" colspan="2" align="center"><input type="submit" value="Save" /></td></tr>
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
		'bbcodereplacement' => str,
		'bbcodeexample'		=> str,
		'bbcodeexplanation' => str,
		'twoparams'			=> int,
		'buttonimage'		=> str
	);
	$query = array();
	foreach ($vars as $key => $type) {
		$query[$key] = $gpc->get($key, $type);
	}

	echo head();

	if (!$query['bbcodetag'] OR !$query['bbcodereplacement'] OR !$query['bbcodeexample']) {
		error('admin.php?action=bbcodes&job=custombb_add', 'Please complete all required fields');
	}

	if (strtolower($query['bbcodetag']) != strtolower($query['bbcodetag_old'])) {
		$result = $db->query("SELECT * FROM {$db->pre}bbcode WHERE bbcodetag = '{$query['bbcodetag']}' AND twoparams = '{$query['twoparams']}' AND ", __LINE__, __FILE__);
		if ($db->num_rows($result) > 0) {
			error('admin.php?action=bbcodes&job=custombb_add', 'There is already a BB Code named &quot;'.$query['bbcodetag'].'&quot;. You may not create duplicate names.');
		}
	}

	$db->query("UPDATE {$db->pre}bbcode SET title = '{$query['title']}',bbcodetag = '{$query['bbcodetag']}',bbcodereplacement = '{$query['bbcodereplacement']}',bbcodeexample = '{$query['bbcodeexample']}',bbcodeexplanation = '{$query['bbcodeexplanation']}',twoparams = '{$query['twoparams']}',buttonimage = '{$query['buttonimage']}' WHERE id = '{$query['id']}'", __LINE__, __FILE__);

	$delobj = $scache->load('custombb');
	$delobj->delete();

	ok('admin.php?action=bbcodes&job=custombb');
}
elseif ($job == 'custombb_delete') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT buttonimage FROM {$db->pre}bbcode WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	$image = $db->fetch_assoc($result);
	?>
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	<tr><td class="obox">Delete Custom BB Code</td></tr>
	<tr><td class="mbox">
	<p align="center">Do you really want to delete this custom BB code?</p>
	<p align="center">
	<?php if (@file_exists($image['buttonimage']) && !preg_match('/^(http:\/\/|www.)([\wäöüÄÖÜ@\-_\.]+)\:?([0-9]*)\/(.*)$/', $image['buttonimage'])) { ?>
	<a href="admin.php?action=bbcodes&amp;job=custombb_delete2&amp;id=<?php echo $id; ?>&amp;img=1"><img border="0" align="absmiddle" alt="" src="admin/html/images/yes.gif"> Yes, inclusive image</a><br />
	<a href="admin.php?action=bbcodes&amp;job=custombb_delete2&amp;id=<?php echo $id; ?>"><img border="0" align="absmiddle" alt="" src="admin/html/images/yes.gif"> Yes, but not the image</a><br />
	<?php } else { ?>
	<a href="admin.php?action=bbcodes&amp;job=custombb_delete2&amp;id=<?php echo $id; ?>"><img border="0" align="absmiddle" alt="" src="admin/html/images/yes.gif"> Yes</a><br />
	<?php } ?>
	<br /><a href="admin.php?action=bbcodes&amp;job=custombb"><img border="0" align="absmiddle" alt="" src="admin/html/images/no.gif"> No</a>
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
		$result = $db->query("SELECT buttonimage FROM {$db->pre}bbcode WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
		$image = $db->fetch_assoc($result);
		if (@file_exists($image['buttonimage']) && !preg_match('/^(http:\/\/|www.)([\wäöüÄÖÜ@\-_\.]+)\:?([0-9]*)\/(.*)$/', $image['buttonimage'])) {
			$filesystem->unlink($image['buttonimage']);
		}
	}
	$db->query("DELETE FROM {$db->pre}bbcode WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	$delobj = $scache->load('custombb');
	$delobj->delete();
	ok('admin.php?action=bbcodes&job=custombb', 'Custom BB Code successfully deleted');
}
elseif ($job == 'custombb_test') {
	echo head();
	// reader-Tag not recognized
	$file = 'admin/data/bbcode_test.php';
	$test = $gpc->get('test', none);
	$parsed_test = null;
	if (!empty($test)) {
		file_put_contents($file, $test);
		$lang = new lang(false, E_USER_WARNING);
		$lang->init();
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
  	<tr><td class="obox">Parsing Results</td></tr>
  	<tr><td class="ubox">
  		<strong>Benchmark:</strong><br />
  		Smileys: <?php echo $smileys_time; ?> seconds<br />
  		BB-Codes: <?php echo $bbcode_time; ?> seconds<br />
  	</td></tr>
  	<tr><td class="mbox"><?php echo $parsed_test; ?></td></tr>
</table>
<br /><?php } ?>
<form action="admin.php?action=bbcodes&job=custombb_test" name="form2" method="post">
	<table align="center" class="border">
  		<tr><td class="obox">Test your custom BB Codes</td></tr>
		<tr><td class="mbox" align="center"><textarea name="test" rows="10" cols="120"><?php echo $test; ?></textarea></td></tr>
		<tr><td class="ubox" align="center"><input type="submit" value="Test" /></td></tr>
	</table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'custombb') {
	$result = $db->query("SELECT * FROM {$db->pre}bbcode", __LINE__, __FILE__);
	echo head();
	?>
	<table align="center" class="border">
	<tr>
		<td class="obox" colspan="4"><span style="float: right;">
		<a class="button" href="admin.php?action=bbcodes&job=custombb_add">Add new BB Code</a>
		<a class="button" href="admin.php?action=bbcodes&job=custombb_import">Import BB Code</a>
		<a class="button" href="admin.php?action=bbcodes&job=custombb_test">Test BB Codes</a>
		</span>Custom BB Code Manager</td>
	</tr>
	<tr>
		<td class="ubox" width="30%">Title</td>
		<td class="ubox" width="35%">BB Code</td>
		<td class="ubox" width="10%">Button Image</td>
		<td class="ubox" width="25%">Action</td>
	</tr>
	<?php
	while ($bbcode = $db->fetch_assoc($result)) {
		if (!empty($bbcode['buttonimage'])) {
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
			<a class="button" href="admin.php?action=bbcodes&job=custombb_edit&id=<?php echo $bbcode['id']; ?>">Edit</a>
			<a class="button" href="admin.php?action=bbcodes&job=custombb_export&id=<?php echo $bbcode['id']; ?>">Export</a>
			<a class="button" href="admin.php?action=bbcodes&job=custombb_delete&id=<?php echo $bbcode['id']; ?>">Delete</a>
			</td>
		</tr>
		<?
	}
	?>
	</table>
	<?php
}
?>
