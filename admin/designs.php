<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// FS: MultiLangAdmin
$lang->group("admin/designs");

$all = array();
foreach(array('templates', 'images', 'designs') as $dir) {
	$all[$dir] = array();
	$path = "./{$dir}/";
	$d = dir($path);
	while (false !== ($entry = $d->read())) {
		if (preg_match('/^\d{1,}$/', $entry) && is_dir($path.$entry)) {
			$all[$dir][] = $entry;
		}
	}
	$d->close();
}

($code = $plugins->load('admin_designs_jobs')) ? eval($code) : null;

if ($job == 'design') {
	echo head();
	$result = $db->query('SELECT * FROM '.$db->pre.'designs ORDER BY name');
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="6"><span class="right"><a class="button" href="admin.php?action=packages&amp;job=browser&amp;type=<?php echo IMPTYPE_DESIGN; ?>"><?php echo $lang->phrase('admin_design_browse_design'); ?></a> <a class="button" href="admin.php?action=designs&amp;job=design_import"><?php echo $lang->phrase('admin_design_import_design_button'); ?></a> <a class="button" href="admin.php?action=designs&amp;job=design_add"><?php echo $lang->phrase('admin_design_add_new_design'); ?></a></span><?php echo $lang->phrase('admin_design_designs'); ?></td>
  </tr>
  <tr>
   <td class="ubox" width="40%"><?php echo $lang->phrase('admin_design_name'); ?></td>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_design_templates'); ?></td>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_design_stylesheets'); ?></td>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_design_images'); ?></td>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_design_published'); ?></td>
   <td class="ubox" width="40%"><?php echo $lang->phrase('admin_design_action'); ?></td>
  </tr>
  <?php while ($row = $db->fetch_assoc($result)) { ?>
  <tr>
   <td class="mbox"><?php echo $row['name']; ?><?php echo iif($row['id'] == $config['templatedir'], ' (<em>'.$lang->phrase('admin_design_default').'</em>)'); ?></td>
   <td class="mbox" align="right"><a href="admin.php?action=explorer&amp;path=<?php echo urlencode('./templates/'.$row['template'].'/'); ?>"><?php echo $row['template']; ?></a></td>
   <td class="mbox" align="right"><a href="admin.php?action=explorer&amp;path=<?php echo urlencode('./designs/'.$row['stylesheet'].'/'); ?>"><?php echo $row['stylesheet']; ?></a></td>
   <td class="mbox" align="right"><a href="admin.php?action=explorer&amp;path=<?php echo urlencode('./images/'.$row['images'].'/'); ?>"><?php echo $row['images']; ?></a></td>
   <td class="mbox" align="center"><?php echo noki($row['publicuse'], ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=designs&job=ajax_publicuse&id='.$row['id'].'\')"'); ?></td>
   <td class="mbox">
   <a class="button" href="admin.php?action=designs&amp;job=design_edit&amp;id=<?php echo $row['id']; ?>"><?php echo $lang->phrase('admin_design_edit'); ?></a>
   <a class="button" href="admin.php?action=designs&amp;job=design_export&amp;id=<?php echo $row['id']; ?>"><?php echo $lang->phrase('admin_design_export'); ?></a>
   <a class="button" href="admin.php?action=designs&amp;job=design_delete&amp;id=<?php echo $row['id']; ?>"><?php echo $lang->phrase('admin_design_delete'); ?></a>
   <?php if ($row['publicuse'] == 1 && $config['templatedir'] != $row['id']) { ?>
   <a class="button" href="admin.php?action=designs&amp;job=design_default&amp;id=<?php echo $row['id']; ?>"><?php echo $lang->phrase('admin_design_set_as_default'); ?></a>
   <?php } ?>
   <a class="button" href="forum.php?design=<?php echo $row['id']; ?>&amp;admin=<?php echo $config['cryptkey'].SID2URL_x; ?>" target="_blank"><?php echo $lang->phrase('admin_design_view'); ?></a>
   </td>
  </tr>
  <?php } ?>
 </table>
 <br class="minibr" />
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox center">
   <a class="button" href="admin.php?action=explorer&path=<?php echo urlencode('./templates/'); ?>"><?php echo $lang->phrase('admin_design_template_manager'); ?></a>
   <a class="button" href="admin.php?action=explorer&path=<?php echo urlencode('./designs/'); ?>"><?php echo $lang->phrase('admin_design_stylesheet_manager'); ?></a>
   <a class="button" href="admin.php?action=explorer&path=<?php echo urlencode('./images/'); ?>"><?php echo $lang->phrase('admin_design_image_manager'); ?></a>
   </td>
  </tr>
 </table>
	<?php
	echo foot();
}
elseif ($job == 'design_default') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT publicuse FROM {$db->pre}designs WHERE id = '{$id}' LIMIT 1");
	$info = $db->fetch_assoc($result);
	if ($info['publicuse'] == 1) {
		include('classes/class.phpconfig.php');
		$c = new manageconfig();
		$c->getdata();
		$c->updateconfig('templatedir', int, $id);
		$c->savedata();
		$delobj = $scache->load('loaddesign');
		$delobj->delete();
		ok('admin.php?action=designs&job=design');
	}
	else {
		error('admin.php?action=designs&job=design', $lang->phrase('admin_design_set_design_as_default_error'));
	}
}
elseif ($job == 'design_edit') {
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}designs WHERE id = '{$id}' LIMIT 1");
	$info = $db->fetch_assoc($result);

	echo head();
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=designs&job=design_edit2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="6"><?php echo $lang->phrase('admin_design_edit_design'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_name_for_this_design'); ?></td>
   <td class="mbox" width="60%"><input type="text" name="name" size="60" value="<?php echo $gpc->prepare($info['name']); ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_directory_for_templates'); ?></td>
   <td class="mbox" width="60%">
   <?php foreach ($all['templates'] as $dir) { ?>
   <input<?php echo iif($info['template'] == $dir, ' checked="checked"'); ?> type="radio" name="template" value="<?php echo $dir; ?>" /> <?php echo $dir; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_directory_for_stylesheets'); ?></td>
   <td class="mbox" width="60%">
   <?php foreach ($all['designs'] as $dir) { ?>
   <input<?php echo iif($info['stylesheet'] == $dir, ' checked="checked"'); ?> type="radio" name="stylesheet" value="<?php echo $dir; ?>" /> <?php echo $dir; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_directory_for_images'); ?></td>
   <td class="mbox" width="60%">
   <?php foreach ($all['images'] as $dir) { ?>
   <input<?php echo iif($info['images'] == $dir, ' checked="checked"'); ?> type="radio" name="images" value="<?php echo $dir; ?>" /> <?php echo $dir; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_form_published'); ?></td>
   <td class="mbox" width="60%"><input type="checkbox" name="publicuse" value="1"<?php echo iif($info['publicuse'] == '1', ' checked="checked"'); ?> /></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_design_form_save'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'design_edit2') {
	echo head();

	$id = $gpc->get('id', int);
	$template = $gpc->get('template', int);
	$stylesheet = $gpc->get('stylesheet', int);
	$images = $gpc->get('images', int);
	$use = $gpc->get('publicuse', int);
	$name = $gpc->get('name', str);
	$error = '';

	$result = $db->query("SELECT publicuse FROM {$db->pre}designs WHERE id = '{$id}' LIMIT 1");
	$puse = $db->fetch_assoc($result);
	if ($puse['publicuse'] == 1 && $use == 0) {
		if ($id == $config['templatedir']) {
			$error .= $lang->phrase('admin_design_design_unpublish_default_design_error');
			$use = 1;
		}
		$result = $db->query("SELECT * FROM {$db->pre}designs WHERE publicuse = '1'");
		if ($db->num_rows($result) == 1) {
			$error .= $lang->phrase('admin_design_design_unpublish_no_other_design_published_error');
			$use = 1;
		}
	}
	$delobj = $scache->load('loaddesign');
	$delobj->delete();
	$db->query("UPDATE {$db->pre}designs SET template = '{$template}', stylesheet = '{$stylesheet}', images = '{$images}', publicuse = '{$use}', name = '{$name}' WHERE id = '{$id}' LIMIT 1");

	ok('admin.php?action=designs&job=design&id='.$id, $lang->phrase('admin_design_changes_were_successfully_saved'));
}
elseif ($job == 'design_delete2') {
	$id = $gpc->get('id', int);

	$result = $db->query("SELECT id FROM {$db->pre}designs WHERE id != '{$id}' AND publicuse = '1' LIMIT 1");
	if ($db->num_rows($result) != 1) {
		error('admin.php?action=designs&job=design', $lang->phrase('admin_design_you_cant_delete_the_last_design'));
	}

	if ($id == $config['templatedir']) {
		echo head();
		error('admin.php?action=designs&job=design', $lang->phrase('admin_design_you_cant_unpublish_design_until_other_default'));
	}

	$db->query("DELETE FROM {$db->pre}designs WHERE id = '{$id}' LIMIT 1");
	$delobj = $scache->load('loaddesign');
	$delobj->delete();

// Do NOT removes data. That "feature" is terrible on account of loosing data!

	echo head();
	ok('admin.php?action=designs&job=design', $lang->phrase('admin_design_design_deleted_successfully'));
}
elseif ($job == 'design_add') {
	echo head();
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=designs&job=design_add2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="6"><?php echo $lang->phrase('admin_design_add_a_new_design'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_name_for_this_design'); ?></td>
   <td class="mbox" width="60%"><input type="text" name="name" size="60" /></td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_directory_for_templates'); ?></td>
   <td class="mbox" width="60%">
   <input type="radio" name="template" value="0" checked="checked" /> <?php echo $lang->phrase('admin_design_create_new_template_directory'); ?>
   <?php foreach ($all['templates'] as $dir) { ?>
   <br /><input type="radio" name="template" value="<?php echo $dir; ?>" /> <?php echo $dir; ?>
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_directory_for_stylesheets'); ?></td>
   <td class="mbox" width="60%">
   <input type="radio" name="stylesheet" value="0" checked="checked" /> <?php echo $lang->phrase('admin_design_copy_standard_css'); ?>
   <?php foreach ($all['designs'] as $dir) { ?>
   <br /><input type="radio" name="stylesheet" value="<?php echo $dir; ?>" /> <?php echo $dir; ?>
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_directory_for_images'); ?></td>
   <td class="mbox" width="60%">
   <input type="radio" name="images" value="0" checked="checked" /> <?php echo $lang->phrase('admin_design_create_new_images_directory'); ?>
   <?php foreach ($all['images'] as $dir) { ?>
   <br /><input type="radio" name="images" value="<?php echo $dir; ?>" /> <?php echo $dir; ?>
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_form_published'); ?></td>
   <td class="mbox" width="60%"><input type="checkbox" name="publicuse" value="1" /></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_design_save'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
// Changed behaviour in 0.8 RC6: Templates and images are copied also
elseif ($job == 'design_add2') {
	echo head();

	$template = $gpc->get('template', int);
	$stylesheet = $gpc->get('stylesheet', int);
	$images = $gpc->get('images', int);
	$use = $gpc->get('publicuse', int);
	$name = $gpc->get('name', str);

	if (empty($name)) {
		$name = 'Design '.$id;
	}

	$result = $db->query("SELECT template, images, stylesheet FROM {$db->pre}designs WHERE id = '{$config['templatedir']}' LIMIT 1");
	$info = $db->fetch_assoc($result);

	if ($template == 0) {
		$template = 1;
		if (count($all['templates']) > 0) {
			$template = max($all['templates']) + 1;
		}
		$filesystem->mkdir("templates/{$template}/", 0777);
		$filesystem->copyr("templates/{$info['template']}/", "templates/{$template}/");
	}
	if ($stylesheet == 0) {
		$stylesheet = 1;
		if (count($all['designs']) > 0) {
			$stylesheet = max($all['designs']) + 1;
		}
		$filesystem->mkdir("designs/{$stylesheet}/", 0777);
		$filesystem->copyr("designs/{$info['stylesheet']}/", "designs/{$stylesheet}/");
	}
	if ($images == 0) {
		$images = 1;
		if (count($all['images']) > 0) {
			$images = max($all['images']) + 1;
		}
		$filesystem->mkdir("images/{$images}/", 0777);
		$filesystem->copyr("images/{$info['images']}/", "images/{$images}/");
	}

	$delobj = $scache->load('loaddesign');
	$delobj->delete();
	$db->query("INSERT INTO {$db->pre}designs SET template = '{$template}', stylesheet = '{$stylesheet}', images = '{$images}', publicuse = '{$use}', name = '{$name}'");

	ok('admin.php?action=designs&job=design', $lang->phrase('admin_design_design_successfully_added'));
}
elseif ($job == 'design_import') {
	$file = $gpc->get('file', str);
	echo head();
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=designs&job=design_import2">
 <table class="border" cellpadding="4" cellspacing="0" border="0">
  <tr><td class="obox" colspan="2"><?php echo $lang->phrase('admin_design_import_new_design'); ?></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_design_either_upload_a_file'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_design_allowed_file_types_and_max_file_size'); ?><?php echo formatFilesize(ini_maxupload()); ?></span></td>
  <td class="mbox"><input type="file" name="upload" size="40" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_design_or_select_file_from_server'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_design_path_starting_from_root'); ?></span></td>
  <td class="mbox"><input type="text" name="server" size="50" value="<?php echo $file; ?>" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_design_delete_file_after_import'); ?></td>
  <td class="mbox"><input type="checkbox" name="delete" value="1" checked="checked" /></td></tr>
  <tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" value="<?php echo $lang->phrase('admin_design_form_send'); ?>" /></td></tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'design_import2') {

	$dir = $gpc->get('dir', int);
	$server = $gpc->get('server', none);
	$del = $gpc->get('delete', int);
	$inserterrors = array();

	if (!empty($_FILES['upload']['name'])) {
		$filesize = ini_maxupload();
		$filetypes = array('zip');
		$dir = realpath('temp').DIRECTORY_SEPARATOR;

		require("classes/class.upload.php");
		$inserterrors = array();
		$my_uploader = new uploader();
		$my_uploader->max_filesize($filesize);
		$my_uploader->file_types($filetypes);
		$my_uploader->set_path($dir);
		if ($my_uploader->upload('upload')) {
			if ($my_uploader->save_file()) {
				$file = $dir.$my_uploader->fileinfo('filename');
				if (!file_exists($file)) {
					$inserterrors[] = $lang->phrase('admin_design_file_dosent_exist');
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
			$inserterrors[] = $lang->phrase('admin_design_file_isnt_a_zipfile');
		}
	}
	else {
		$inserterrors[] = $lang->phrase('admin_design_no_valid_file_selected');
	}
	echo head();
	if (count($inserterrors) > 0) {
		error('admin.php?action=designs&job=design_import', $inserterrors);
	}
	$tempdir = 'temp/'.md5(microtime()).'/';
	$filesystem->mkdir($tempdir, 0777);
	require_once('classes/class.zip.php');
	$archive = new PclZip($file);
	$failure = $archive->extract($tempdir);
	if ($failure < 1) {
		$filesystem->rmdirr($tempdir);
		unset($archive);
		if ($del > 0) {
			$filesystem->unlink($file);
		}
		error('admin.php?action=designs&job=design_import', $lang->phrase('admin_design_zip_archive_error'));
	}
	else {
		if (!file_exists($tempdir.'design.ini')) {
			error('admin.php?action=designs&job=design_import', $lang->phrase('admin_design_zip_archive_missing_design_ini'));
		}
		$myini = new INI();
		$ini = $myini->read($tempdir.'design.ini');

		$result = $db->query("SELECT * FROM `{$db->pre}designs` WHERE id = '{$config['templatedir']}'");
		$row = $db->fetch_assoc($result);

		if (!empty($ini['template'])) {
			$tplid = 1;
			if (count($all['templates']) > 0) {
				$tplid = max($all['templates']) + 1;
			}
			$filesystem->mover($tempdir.'templates', 'templates/'.$tplid);
		}
		else {
			$tplid = $row['template'];
		}

		if (!empty($ini['stylesheet'])) {
			$cssid = 1;
			if (count($all['designs']) > 0) {
				$cssid = max($all['designs']) + 1;
			}
			$filesystem->mover($tempdir.'designs', 'designs/'.$cssid);
		}
		else {
			$cssid = $row['stylesheet'];
		}

		if (!empty($ini['images'])) {
			$imgid = 1;
			if (count($all['images']) > 0) {
				$imgid = max($all['images']) + 1;
			}
			$filesystem->mover($tempdir.'images', 'images/'.$imgid);
		}
		else {
			$imgid = $row['images'];
		}

		$db->query("INSERT INTO `{$db->pre}designs` (`template` , `stylesheet` , `images` , `name`) VALUES ('{$tplid}', '{$cssid}', '{$imgid}', '{$ini['name']}')");

		unset($archive);
		if ($del > 0) {
			$filesystem->unlink($file);
		}
		$filesystem->rmdirr($tempdir);
	}
	$delobj = $scache->load('loaddesign');
	$delobj->delete();
	ok('admin.php?action=designs&job=design', $lang->phrase('admin_design_design_successfully_imported'));

}
elseif ($job == 'design_export') {
	$id = $gpc->get('id', int);
	echo head();
	?>
<form name="form2" method="post" action="admin.php?action=designs&job=design_export2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="6"><?php echo $lang->phrase('admin_design_export_design'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_export_templates'); ?></td>
   <td class="mbox" width="60%"><input type="checkbox" name="tpl" value="1" checked="checked" /></td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_export_css'); ?></td>
   <td class="mbox" width="60%"><input type="checkbox" name="css" value="1" checked="checked" /></td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_export_images'); ?></td>
   <td class="mbox" width="60%"><input type="checkbox" name="img" value="1" checked="checked" /></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_design_export'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'design_export2') {
	$id = $gpc->get('id', int);
	$tpl = $gpc->get('tpl', int);
	$img = $gpc->get('img', int);
	$css = $gpc->get('css', int);

	$columns = array('id', 'name');
	if ($tpl == 1) {
		$columns[] = 'template';
	}
	if ($img == 1) {
		$columns[] = 'images';
	}
	if ($css == 1) {
		$columns[] = 'stylesheet';
	}

	$result = $db->query("SELECT ".implode(',', $columns)." FROM {$db->pre}designs WHERE id = '{$id}' LIMIT 1");
	$info = $db->fetch_assoc($result);

	$file = convert2adress($info['name']).'.zip';
	$dirs = array();
	if ($tpl == 1) {
		$dirs[] = array('dir' => "templates/{$info['template']}/", 'func' => 'export_template_list');
	}
	if ($img == 1) {
		$dirs[] = array('dir' => "images/{$info['images']}/", 'func' => '');
	}
	if ($css == 1) {
		$dirs[] = array('dir' => "designs/{$info['stylesheet']}/", 'func' => '');
	}
	$tempdir = "temp/";
	$error = false;
	$settings = $tempdir.'design.ini';
	$myini = new INI();
	$myini->write($settings, $info);

	require_once('classes/class.zip.php');
	$archive = new PclZip($tempdir.$file);
	$v_list = $archive->create($settings, PCLZIP_OPT_REMOVE_PATH, $tempdir);
	if ($v_list == 0) {
		$error = true;
	}
	else {
		foreach ($dirs as $dir) {
			$archive = new PclZip($tempdir.$file);
			if (!empty($dir['func']) && viscacha_function_exists($dir['func'])) {
				$list = $dir['func']($dir['dir']);
				$v_list = $archive->add($list, PCLZIP_OPT_REMOVE_PATH, $dir['dir'], PCLZIP_OPT_ADD_PATH, extract_dir($dir['dir'], false));
			}
			else {
				$v_list = $archive->add($dir['dir'], PCLZIP_OPT_REMOVE_PATH, $dir['dir'], PCLZIP_OPT_ADD_PATH, extract_dir($dir['dir'], false));
			}
			if ($v_list == 0) {
				$error = true;
				break;
			}
		}
	}
	if ($error) {
		echo head();
		$error = $archive->errorInfo(true);
		unset($archive);
		$filesystem->unlink($tempdir.$file);
		$filesystem->unlink($settings);
		error('admin.php?action=designs&job=design_export&id='.$id, $error);
	}
	else {
		viscacha_header('Content-Type: application/zip');
		viscacha_header('Content-Disposition: attachment; filename="'.$file.'"');
		viscacha_header('Content-Length: '.filesize($tempdir.$file));
		readfile($tempdir.$file);
		unset($archive);
		$filesystem->unlink($tempdir.$file);
		$filesystem->unlink($settings);
	}
}
elseif ($job == 'ajax_publicuse') {
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT publicuse FROM {$db->pre}designs WHERE id = '{$id}' LIMIT 1");
	$use = $db->fetch_assoc($result);
	if ($use['publicuse'] == 1) {
		if ($id == $config['templatedir']) {
			die($lang->phrase('admin_design_you_cant_unpublish_design_until_other_default'));
		}
		$result = $db->query("SELECT * FROM {$db->pre}designs WHERE publicuse = '1'");
		if ($db->num_rows($result) == 1) {
			die($lang->phrase('admin_design_you_cant_unpublish_design_until_another_published'));
		}
	}
	$use = invert($use['publicuse']);
	$db->query("UPDATE {$db->pre}designs SET publicuse = '{$use}' WHERE id = '{$id}' LIMIT 1");
	$delobj = $scache->load('loaddesign');
	$delobj->delete();
	die(strval($use));
}
elseif ($job == 'design_delete') {
	$id = $gpc->get('id', int);
	echo head();
	?>
	<table class="border" border="0" cellspacing="0" cellpadding="4">
	<tr><td class="obox"><?php echo $lang->phrase('admin_design_delete_the_data'); ?></td></tr>
	<tr><td class="mbox">
	<p align="center"><?php echo $lang->phrase('admin_design_really_delete_this_data'); ?></p>
	<p align="center">
	<a href="admin.php?action=designs&amp;job=design_delete2&amp;id=<?php echo $id; ?>"><img border="0" alt="<?php echo $lang->phrase('admin_design_yes'); ?>" src="admin/html/images/yes.gif"> <?php echo $lang->phrase('admin_design_yes'); ?></a>
	&nbsp&nbsp;&nbsp;&nbsp&nbsp;&nbsp;
	<a href="javascript: history.back(-1);"><img border="0" alt="<?php echo $lang->phrase('admin_design_no'); ?>" src="admin/html/images/no.gif"> <?php echo $lang->phrase('admin_design_no'); ?></a>
	</p>
	</td></tr>
	</table>
	<?php
	echo foot();
}
else {
	sendStatusCode(302, $config['furl'].'/admin.php?action=designs&job=design');
}
?>