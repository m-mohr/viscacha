<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// FS: MultiLangAdmin
$lang->group("admin/designs");

require_once("admin/lib/class.servernavigator.php");
$snav = new ServerNavigator();

function export_template_list ($path, $d = 0) {
   	if (substr($path , strlen($path)-1) != '/') {
		$path .= '/';
	}
   	$dirlist = array ();
   	if ($handle = opendir($path)) {
       	while (false !== ($file = readdir($handle))) {
           	if ($file != '.' && $file != '..') {
               	$file = $path . $file ;
               	if (!is_dir($file)) {
					$extension = get_extension($path);
					if (stripos($extension, 'bak') === false) {
						$dirlist[] = $file;;
					}
				}
               	elseif ($d >= 0) {
                   	$result = export_template_list ( $file . '/' , $d + 1 );
                   	$dirlist = array_merge ( $dirlist , $result );
               	}
       		}
       	}
       	closedir($handle);
   	}
   	if ($d == 0) {
		natcasesort($dirlist);
	}
   	return ($dirlist);
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
   <td class="mbox" align="right"><?php echo $row['template']; ?></td>
   <td class="mbox" align="right"><?php echo $row['stylesheet']; ?></td>
   <td class="mbox" align="right"><?php echo $row['images']; ?></td>
   <td class="mbox" align="center"><?php echo noki($row['publicuse'], ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=designs&job=ajax_publicuse&id='.$row['id'].'\')"'); ?></td>
   <td class="mbox">
   <a class="button" href="admin.php?action=designs&amp;job=design_edit&amp;id=<?php echo $row['id']; ?>"><?php echo $lang->phrase('admin_design_edit'); ?></a>
   <a class="button" href="admin.php?action=designs&amp;job=design_export&amp;id=<?php echo $row['id']; ?>"><?php echo $lang->phrase('admin_design_export'); ?></a>
   <a class="button" href="admin.php?action=designs&amp;job=confirm_delete&amp;type=design&amp;id=<?php echo $row['id']; ?>"><?php echo $lang->phrase('admin_design_delete'); ?></a>
   <?php if ($row['publicuse'] == 1 && $config['templatedir'] != $row['id']) { ?>
   <a class="button" href="admin.php?action=designs&amp;job=design_default&amp;id=<?php echo $row['id']; ?>"><?php echo $lang->phrase('admin_design_set_as_default'); ?></a>
   <?php } ?>
   <a class="button" href="forum.php?design=<?php echo $row['id']; ?>&amp;admin=<?php echo $config['cryptkey'].SID2URL_x; ?>" target="_blank"><?php echo $lang->phrase('admin_design_view'); ?></a>
   </td>
  </tr>
  <?php } ?>
 </table>
 <?php if ($my->settings['admin_interface'] == 0) { ?>
 <br class="minibr" />
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox center">
   <a class="button" href="admin.php?action=designs&job=templates"><?php echo $lang->phrase('admin_design_template_manager'); ?></a>
   <a class="button" href="admin.php?action=designs&job=css"><?php echo $lang->phrase('admin_design_stylesheet_manager'); ?></a>
   <a class="button" href="admin.php?action=designs&job=images"><?php echo $lang->phrase('admin_design_image_manager'); ?></a>
   </td>
  </tr>
 </table>
 <?php } ?>
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

	$dir = "templates/";
	$templates = array();
	$d = dir($dir);
	while (false !== ($entry = $d->read())) {
		if ($entry != '..' && $entry != '.' && preg_match('/^\d{1,}$/', $entry) && is_dir($dir.$entry)) {
			$templates[] = $entry;
		}
	}
	$d->close();

	$dir = "images/";
	$images = array();
	$d = dir($dir);
	while (false !== ($entry = $d->read())) {
		if ($entry != '..' && $entry != '.' && preg_match('/^\d{1,}$/', $entry) && is_dir($dir.$entry)) {
			$images[] = $entry;
		}
	}
	$d->close();

	$dir = "designs/";
	$stylesheet = array();
	$d = dir($dir);
	while (false !== ($entry = $d->read())) {
		if ($entry != '..' && $entry != '.' && preg_match('/^\d{1,}$/', $entry) && is_dir($dir.$entry)) {
			$stylesheet[] = $entry;
		}
	}
	$d->close();

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
   <?php foreach ($templates as $dir) { ?>
   <input<?php echo iif($info['template'] == $dir, ' checked="checked"'); ?> type="radio" name="template" value="<?php echo $dir; ?>" /> <a href="admin.php?action=designs&job=templates_browse&id=<?php echo $dir; ?>" target="_blank"><?php echo $dir; ?></a><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_directory_for_stylesheets'); ?></td>
   <td class="mbox" width="60%">
   <?php foreach ($stylesheet as $dir) { ?>
   <input<?php echo iif($info['stylesheet'] == $dir, ' checked="checked"'); ?> type="radio" name="stylesheet" value="<?php echo $dir; ?>" /> <a href="admin.php?action=explorer&path=<?php echo urlencode('./designs/'.$dir.'/'); ?>" target="_blank"><?php echo $dir; ?></a><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_directory_for_images'); ?></td>
   <td class="mbox" width="60%">
   <?php foreach ($images as $dir) { ?>
   <input<?php echo iif($info['images'] == $dir, ' checked="checked"'); ?> type="radio" name="images" value="<?php echo $dir; ?>" /> <a href="admin.php?action=explorer&path=<?php echo urlencode('./images/'.$dir.'/'); ?>" target="_blank"><?php echo $dir; ?></a><br />
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
elseif ($job == 'design_delete') {
	$id = $gpc->get('id', int);

	$result = $db->query("SELECT id FROM {$db->pre}designs WHERE id != '{$id}' AND publicuse = '1' LIMIT 1");
	if ($db->num_rows($result) != 1) {
		error('admin.php?action=designs&job=design', $lang->phrase('admin_design_you_cant_delete_the_last_design'));
	}

	$result = $db->query("SELECT publicuse FROM {$db->pre}designs WHERE id = '{$id}' LIMIT 1");
	$info = $db->fetch_assoc($result);

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
	$id = $gpc->get('id', int);

	$dir = "templates/";
	$templates = array();
	$d = dir($dir);
	while (false !== ($entry = $d->read())) {
		if ($entry != '..' && $entry != '.' && preg_match('/^\d{1,}$/', $entry) && is_dir($dir.$entry)) {
			$templates[] = $entry;
		}
	}
	$d->close();

	$dir = "images/";
	$images = array();
	$d = dir($dir);
	while (false !== ($entry = $d->read())) {
		if ($entry != '..' && $entry != '.' && preg_match('/^\d{1,}$/', $entry) && is_dir($dir.$entry)) {
			$images[] = $entry;
		}
	}
	$d->close();

	$dir = "designs/";
	$stylesheet = array();
	$d = dir($dir);
	while (false !== ($entry = $d->read())) {
		if ($entry != '..' && $entry != '.' && preg_match('/^\d{1,}$/', $entry) && is_dir($dir.$entry)) {
			$stylesheet[] = $entry;
		}
	}
	$d->close();

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
   <input type="radio" name="template" value="0" /><?php echo $lang->phrase('admin_design_create_new_template_directory'); ?>
   <?php foreach ($templates as $dir) { ?>
   <br /><input type="radio" name="template" value="<?php echo $dir; ?>" /> <a href="admin.php?action=designs&job=templates_browse&id=<?php echo $dir; ?>" target="_blank"><?php echo $dir; ?></a>
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_directory_for_stylesheets'); ?></td>
   <td class="mbox" width="60%">
   <input type="radio" name="stylesheet" value="0" /><?php echo $lang->phrase('admin_design_copy_standard_css'); ?>
   <?php foreach ($stylesheet as $dir) { ?>
   <br /><input type="radio" name="stylesheet" value="<?php echo $dir; ?>" /> <a href="admin.php?action=explorer&path=<?php echo urlencode('./designs/'.$dir.'/'); ?>" target="_blank"><?php echo $dir; ?></a>
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_design_directory_for_images'); ?></td>
   <td class="mbox" width="60%">
   <input type="radio" name="images" value="0" /><?php echo $lang->phrase('admin_design_create_new_images_directory'); ?>
   <?php foreach ($images as $dir) { ?>
   <br /><input type="radio" name="images" value="<?php echo $dir; ?>" /> <a href="admin.php?action=explorer&path=<?php echo urlencode('./images/'.$dir.'/'); ?>" target="_blank"><?php echo $dir; ?></a>
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

	if ($template == 0) {
		$template = 1;
		while(is_dir('templates/'.$template)) {
			$template++;
			if ($template > 10000) {
				error('admin.php?action=designs&job=design_add', $lang->phrase('admin_design_buffer_overflow_templates'));
			}
		}
		$filesystem->mkdir("templates/{$template}/", 0777);
	}
	if ($stylesheet == 0) {
		$stylesheet = 1;
		while(is_dir('designs/'.$stylesheet)) {
			$stylesheet++;
			if ($stylesheet > 10000) {
				error('admin.php?action=designs&job=design_add', $lang->phrase('admin_design_buffer_overflow_css'));
			}
		}
		$result = $db->query("SELECT stylesheet FROM {$db->pre}designs WHERE id = '{$config['templatedir']}' LIMIT 1");
		$info = $db->fetch_assoc($result);
		$filesystem->mkdir("designs/{$stylesheet}/", 0777);
		copyr("designs/{$info['stylesheet']}/", "designs/{$stylesheet}/");
	}
	if ($images == 0) {
		$images = 1;
		while(is_dir('images/'.$images)) {
			$images++;
			if ($images > 10000) {
				error('admin.php?action=designs&job=design_add', $lang->phrase('admin_design_buffer_overflow_images'));
			}
		}
		$filesystem->mkdir("images/{$images}/", 0777);
	}

	$delobj = $scache->load('loaddesign');
	$delobj->delete();
	$db->query("INSERT INTO {$db->pre}designs SET template = '{$template}', stylesheet = '{$stylesheet}', images = '{$images}', publicuse = '{$use}', name = '{$name}'", __LINE__, __FILE__);

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
		rmdirr($tempdir);
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

		$result = $db->query("SELECT * FROM `{$db->pre}designs` WHERE id = '{$config['templatedir']}' LIMIT 1");
		$row = $db->fetch_assoc($result);

		if (!empty($ini['template'])) {
			$tplid = 1;
			while(is_dir('templates/'.$tplid)) {
				$tplid++;
				if ($tplid > 10000) {
					error('admin.php?action=designs&job=design_import', $lang->phrase('admin_design_buffer_overflow_templates'));
				}
			}
			$tpldir = 'templates/'.$tplid;
		}
		else {
			$tplid = $row['template'];
		}
		if (!empty($ini['stylesheet'])) {
			$cssid = 1;
			while(is_dir('designs/'.$cssid)) {
				$cssid++;
				if ($cssid > 10000) {
					error('admin.php?action=designs&job=design_import', $lang->phrase('admin_design_buffer_overflow_css'));
				}
			}
			$cssdir = 'designs/'.$cssid;
		}
		else {
			$tplid = $row['stylesheet'];
		}
		if (!empty($ini['images'])) {
			$imgid = 1;
			while(is_dir('images/'.$imgid)) {
				$imgid++;
				if ($imgid > 10000) {
					error('admin.php?action=designs&job=design_import', $lang->phrase('admin_design_buffer_overflow_images'));
				}
			}
			$imgdir = 'images/'.$imgid;
		}
		else {
			$tplid = $row['images'];
		}

		if (!empty($ini['template'])) {
			mover($tempdir.'templates', $tpldir);
		}
		if (!empty($ini['stylesheet'])) {
			mover($tempdir.'designs', $cssdir);
		}
		if (!empty($ini['images'])) {
			mover($tempdir.'images', $imgdir);
		}

		$db->query("INSERT INTO `{$db->pre}designs` (`template` , `stylesheet` , `images` , `name`) VALUES ('{$tplid}', '{$cssid}', '{$imgid}', '{$ini['name']}')");

		unset($archive);
		if ($del > 0) {
			$filesystem->unlink($file);
		}
		rmdirr($tempdir);
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
		unset($archive);
		$filesystem->unlink($tempdir.$file);
		$filesystem->unlink($settings);
		error('admin.php?action=designs&job=export&id='.$id, $archive->errorInfo(true));
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
elseif ($job == 'templates') {
	echo head();
	$dir = "templates/";
	$d = dir($dir);
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="3"><span style="float: right;"><a class="button" href="admin.php?action=designs&amp;job=templates_add"><?php echo $lang->phrase('admin_design_add_new_templates'); ?></a></span><?php echo $lang->phrase('admin_design_template_manager'); ?></td>
  </tr>
  <tr>
   <td class="ubox" width="40%"><?php echo $lang->phrase('admin_design_directory'); ?></td>
   <td class="ubox" width="10%"><?php echo $lang->phrase('admin_design_files'); ?></td>
   <td class="ubox" width="40%"><?php echo $lang->phrase('admin_design_action'); ?></td>
  </tr>
  <?php
	while (false !== ($entry = $d->read())) {
		if ($entry != '..' && $entry != '.' && preg_match('/^\d{1,}$/', $entry) && is_dir($dir.$entry)) {
			$files = count_dir($dir.$entry);
  ?>
  <tr>
   <td class="mbox"><?php echo $entry; ?></td>
   <td class="mbox" align="right"><?php echo $files; ?></td>
   <td class="mbox">
   <a class="button" href="admin.php?action=designs&amp;job=templates_browse&amp;id=<?php echo $entry; ?>"><?php echo $lang->phrase('admin_design_browse'); ?></a>
   <a class="button" href="admin.php?action=designs&amp;job=templates_export&amp;id=<?php echo $entry; ?>"><?php echo $lang->phrase('admin_design_export'); ?></a>
   <a class="button" href="admin.php?action=designs&amp;job=confirm_delete&amp;type=templates&amp;id=<?php echo $entry; ?>"><?php echo $lang->phrase('admin_design_delete'); ?></a>
   </td>
  </tr>
  <?php } } ?>
 </table>
	<?php
	$d->close();
	echo foot();
}
elseif ($job == 'templates_add') {
	echo head();
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=designs&job=templates_add2">
 <table class="border" cellpadding="4" cellspacing="0" border="0">
  <tr><td class="obox" colspan="2"><?php echo $lang->phrase('admin_design_import_new_templates'); ?></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_design_either_upload_a_file'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_design_allowed_file_types_and_max_file_size'); ?><?php echo formatFilesize(ini_maxupload()); ?></span></td>
  <td class="mbox"><input type="file" name="upload" size="40" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_design_or_select_file_from_server'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_design_path_starting_from_root'); ?></span></td>
  <td class="mbox"><input type="text" name="server" size="50" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_design_delete_file_after_import'); ?></td>
  <td class="mbox"><input type="checkbox" name="delete" value="1" checked="checked" /></td></tr>
  <tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" value="<?php echo $lang->phrase('admin_design_form_send'); ?>" /></td></tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'templates_add2') {

	$dir = $gpc->get('dir', int);
	$server = $gpc->get('server', none);
	$del = $gpc->get('delete', int);
	$inserterrors = array();

	if (!empty($_FILES['upload']['name'])) {
		$filesize = ini_maxupload();
		$filetypes = array('zip');
		$dir = realpath('temp').DIRECTORY_SEPARATOR;

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
					$inserterrors[] = $lang->phrase('admin_design_file_dosent_exist');
				}
			}
		}
		if ($my_uploader->upload_failed()) {
			array_push($inserterrors,$my_uploader->get_error());
		}
	}
	elseif (file_exists($server)) {
		$ext = get_extension($serve);
		if ($ext == 'zip') {
			$file = $server;
		}
		else {
			$inserterrors[] = $lang->phrase('admin_design_zip_file_corrupt');
		}
	}
	else {
		$inserterrors[] = $lang->phrase('admin_design_no_valid_file_selected');
	}
	echo head();
	if (count($inserterrors) > 0) {
		error('admin.php?action=designs&job=templates_add', $inserterrors);
	}

	$n = 1;
	while(is_dir('templates/'.$n)) {
		$n++;
		if ($n > 10000) {
			error('admin.php?action=designs&job=templates_add', $lang->phrase('admin_design_buffer_overflow'));
		}
	}

	$tempdir = 'templates/'.$n;

	require_once('classes/class.zip.php');
	$archive = new PclZip($file);
	$failure = $archive->extract($tempdir);
	if ($failure < 1) {
		rmdirr($tempdir);
		unset($archive);
		if ($del > 0) {
			$filesystem->unlink($file);
		}
		error('admin.php?action=designs&job=templates_add', $lang->phrase('admin_design_zip_archive_error'));
	}

	unset($archive);
	if ($del > 0) {
		$filesystem->unlink($file);
	}
	ok('admin.php?action=designs&job=templates', $lang->phrase('admin_design_templates_successfully_imported'));

}
elseif ($job == 'templates_export') {
	$id = $gpc->get('id', int);

	$file = 'templates'.$id.'.zip';
	$dir = "templates/{$id}/";
	$tempdir = "temp/";

	$list = export_template_list($dir);

	require_once('classes/class.zip.php');
	$archive = new PclZip($tempdir.$file);
	$v_list = $archive->create($list, PCLZIP_OPT_REMOVE_PATH, $dir);
	if ($v_list == 0) {
		echo head();
		unset($archive);
		$filesystem->unlink($tempdir.$file);
		error('admin.php?action=designs&job=templates', $archive->errorInfo(true));
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
elseif ($job == 'templates_delete') {
	$id = $gpc->get('id', int);
	echo head();
	$dir = 'templates/'.$id;
	rmdirr($dir);
	@clearstatcache();
	if (file_exists($dir) || is_dir($dir)) {
		error('admin.php?action=designs&job=templates', $lang->phrase('admin_design_directory_couldnt_be_deleted'));
	}
	else {
		ok('admin.php?action=designs&job=templates', $lang->phrase('admin_design_directory_successfully_deleted'));
	}
}
elseif ($job == 'templates_file_edit') {
	echo head();
	$id = $gpc->get('id', int);
	$readonly = $gpc->get('readonly', int);
	$sub = rawurldecode($gpc->get('dir', none));
	$path = 'templates/' . $id . iif(!empty($sub), "/{$sub}");
	$file = rawurldecode($gpc->get('file', none));
	if ((!file_exists($path.'/'.$file) || empty($file)) && !$readonly) {
		$result = $db->query('SELECT template FROM '.$db->pre.'designs WHERE id = "'.$config['templatedir'].'" LIMIT 1');
		$design = $db->fetch_assoc($result);
		$path = 'templates/' . $design['template'] . iif(!empty($sub), "/{$sub}");
		if (!file_exists($path.'/'.$file) || empty($file)) {
			error('admin.php?action=designs&job=templates_browse&id='.$id, $lang->phrase('admin_design_file_not_found'));
		}
	}
	$content = file_get_contents($path.'/'.$file);
	if (!$readonly) {
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=designs&job=templates_file_edit2&id=<?php echo $id; ?>&dir=<?php echo rawurlencode( iif(!empty($sub), $sub.'/', '')); ?>&file=<?php echo $file; ?>">
<?php } ?>
 <table class="border" cellpadding="4" cellspacing="0" border="0">
  <tr><td class="obox"><?php echo iif($readonly, $lang->phrase('admin_design_view_a_template'), $lang->phrase('admin_design_edit_a_template')).' &raquo; '.$file; ?></td></tr>
  <tr>
   <td class="mbox">
   <strong><?php echo $lang->phrase('admin_design_form_template'); ?></strong><br />
   <textarea cols="120" rows="20" name="template" class="texteditor"><?php echo htmlspecialchars($content, ENT_NOQUOTES); ?></textarea>
   </td>
  </tr>
  <?php if (!$readonly) { ?>
  <tr>
   <td class="mbox">
   <strong><?php echo $lang->phrase('admin_design_make_backup'); ?></strong><br />
   <input type="checkbox" name="backup" value="1" />&nbsp;<?php echo $lang->phrase('admin_design_yes'); ?>
   </td>
  </tr>
  <tr><td class="ubox" align="center"><input accesskey="s" type="submit" value="<?php echo $lang->phrase('admin_design_edit'); ?>" /></td></tr>
  <?php } ?>
 </table>
 <?php if (!$readonly) { ?>
</form>
	<?php
	}
	echo foot();
}
elseif ($job == 'templates_file_delete') {
	echo head();
	$id = $gpc->get('id', int);
	$sub = rawurldecode($gpc->get('dir', none));
	$path = 'templates/' . $id . iif(!empty($sub), "/{$sub}");
	$file = rawurldecode($gpc->get('file', none));
	$filesystem->unlink($path.'/'.$file);
	$extension = get_extension($file);
	if (stripos($extension, 'bak') !== false) {
		$file = basename($file, $extension);
	}
	ok("admin.php?action=designs&job=templates_file_history&id={$id}&dir=" . rawurlencode( iif(!empty($sub), $sub.'/')) . "&file=".$file);
}
elseif ($job == 'templates_file_edit2') {
	echo head();
	$id = $gpc->get('id', int);
	$sub = rawurldecode($gpc->get('dir', none));
	$path = 'templates/' . $id . iif(!empty($sub), "/{$sub}");
	$file = rawurldecode($gpc->get('file', none));
	$backup = $gpc->get('backup', int);
	$content = $gpc->get('template', none);
	if ($backup == 1) {
		$ext = 0;
		if (!file_exists($path.'/'.$file)) {
			$result = $db->query('SELECT template FROM '.$db->pre.'designs WHERE id = "'.$config['templatedir'].'" LIMIT 1');
			$design = $db->fetch_assoc($result);
			$newpath = 'templates/' . $design['template'] . iif(!empty($sub), "/{$sub}");
		}
		else {
			$newpath = $path;
		}
		$bcontent = file_get_contents($newpath.'/'.$file);
		while(file_exists($path.'/'.$file.'.bak'.$ext)) {
			$ext++;
		}
		$filesystem->file_put_contents($path.'/'.$file.'.bak'.$ext, $bcontent);
	}
	$filesystem->file_put_contents($path.'/'.$file, $content);
	ok("admin.php?action=designs&job=templates_browse&id={$id}&dir=" . rawurlencode( iif(!empty($sub), $sub.'/')));
}
elseif ($job == 'templates_file_revert') {
	echo head();
	$id = $gpc->get('id', int);
	$sub = rawurldecode($gpc->get('dir', none));
	$path = 'templates/' . $id . iif(!empty($sub), "/{$sub}");
	$file = rawurldecode($gpc->get('file', none));
	$default = $gpc->get('default', int);
	if ($default == 1) {
		$filesystem->unlink($path.'/'.$file);
		$basename = $file;
	}
	else {
		$basename = basename ($path.'/'.$file, get_extension($path.'/'.$file, true));
		$content = file_get_contents($path.'/'.$file);
		$filesystem->file_put_contents($path.'/'.$basename, $content);
		$filesystem->unlink($path.'/'.$file);
	}

	ok("admin.php?action=designs&job=templates_file_history&id={$id}&dir=" . rawurlencode( iif(!empty($sub), $sub.'/')) . "&file=".$basename);
}
elseif ($job == 'templates_file_history') {
	echo head();
	$id = $gpc->get('id', int);

	$sub = rawurldecode($gpc->get('dir', none));
	$path = 'templates/' . $id . iif(!empty($sub), "/{$sub}");
	$file = rawurldecode($gpc->get('file', none));

	$result = $db->query('SELECT template FROM '.$db->pre.'designs WHERE id = "'.$config['templatedir'].'" LIMIT 1');
	$design = $db->fetch_assoc($result);
	$defpath = 'templates/' . $design['template'] . iif(!empty($sub), "/{$sub}");

	if ($id == $design['template']) {
		$default = true;
	}
	else {
		$default = false;
	}

	$history = array();
   	if ($dh = opendir($path)) {
	   	while (($hfile = readdir($dh)) !== false) {
	   		$data = pathinfo($path.'/'.$hfile);
	   		$ext = get_extension($path.'/'.$hfile, true);
	   		$basename = basename ($path.'/'.$hfile, $ext);
	   		if ($basename == $file && stripos($ext, 'bak') !== false) {
		   		$history[] = $hfile;
	   		}
	   	}
	   	closedir($dh);
	   	rsort($history);
   	}

	$revert = false;
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=designs&job=templates_file_compare">
 <table class="border" cellpadding="4" cellspacing="0" border="0">
  <tr><td class="obox" colspan="7"><?php echo $lang->phrase('admin_design_edit_a_template'); ?> <?php echo $file; ?></td></tr>
  <tr>
   <td class="ubox" width="25%"><?php echo $lang->phrase('admin_design_type'); ?></td>
   <td class="ubox" width="25%"><?php echo $lang->phrase('admin_design_last_modified'); ?></td>
   <td class="ubox" width="40%" colspan="3"><?php echo $lang->phrase('admin_design_action'); ?></td>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_design_old'); ?></td>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_design_new'); ?></td>
  </tr>
  <?php if (!$default && file_exists($path.'/'.$file)) { $revert = true; ?>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_design_current_version'); ?></td>
   <td class="mbox"><?php echo gmdate('d.m.Y H:i', times(filemtime($path.'/'.$file))); ?></td>
   <td class="mbox">&nbsp;</td>
   <td class="mbox"><a class="button" href="admin.php?action=designs&job=templates_file_edit&id=<?php echo $id; ?>&dir=<?php echo rawurlencode( iif(!empty($sub), $sub.'/')); ?>&file=<?php echo rawurldecode($file); ?>"><?php echo $lang->phrase('admin_design_edit'); ?></a></td>
   <td class="mbox"><a class="button" href="admin.php?action=designs&job=templates_file_delete&id=<?php echo $id; ?>&dir=<?php echo rawurlencode( iif(!empty($sub), $sub.'/')); ?>&file=<?php echo rawurldecode($file); ?>"><?php echo $lang->phrase('admin_design_delete'); ?></a></td>
   <td class="mbox"><input type="radio" name="old" value="<?php echo urldecode($path.'/'.$file); ?>" /></td>
   <td class="mbox"><input type="radio" name="new" checked="checked" value="<?php echo urldecode($path.'/'.$file); ?>" /></td>
  </tr>
  <?php
  }
  $i = 0;
  foreach ($history as $hfile) {
	$i++;
	$revert = true;
  ?>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_design_historical'); ?> <?php echo $i; ?></td>
   <td class="mbox"><?php echo gmdate('d.m.Y H:i', times(filemtime($path.'/'.$hfile))); ?></td>
   <td class="mbox"><a class="button" href="admin.php?action=designs&job=templates_file_revert&id=<?php echo $id; ?>&dir=<?php echo rawurlencode( iif(!empty($sub), $sub.'/')); ?>&file=<?php echo rawurldecode($hfile); ?>"><?php echo $lang->phrase('admin_design_revert'); ?></a></td>
   <td class="mbox"><a class="button" href="admin.php?action=designs&job=templates_file_edit&id=<?php echo $id; ?>&dir=<?php echo rawurlencode( iif(!empty($sub), $sub.'/')); ?>&file=<?php echo rawurldecode($hfile); ?>&readonly=1"><?php echo $lang->phrase('admin_design_view'); ?></a></td>
   <td class="mbox"><a class="button" href="admin.php?action=designs&job=templates_file_delete&id=<?php echo $id; ?>&dir=<?php echo rawurlencode( iif(!empty($sub), $sub.'/')); ?>&file=<?php echo rawurldecode($hfile); ?>"><?php echo $lang->phrase('admin_design_delete'); ?></a></td>
   <td class="mbox"><input type="radio" name="old" value="<?php echo urldecode($path.'/'.$hfile); ?>" /></td>
   <td class="mbox"><input type="radio" name="new" value="<?php echo urldecode($path.'/'.$hfile); ?>" /></td>
  </tr>
  <?php } ?>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_design_current_default'); ?></td>
   <td class="mbox"><?php echo gmdate('d.m.Y H:i', times(filemtime($defpath.'/'.$file))); ?></td>
   <td class="mbox">
   <?php echo iif($revert, '<a class="button" href="admin.php?action=designs&job=templates_file_revert&id='.$id.'&dir='.rawurlencode( iif(!empty($sub), $sub.'/')).'&file='.rawurldecode($file).'&default=1">'.$lang->phrase('admin_design_revert').'</a>', '&nbsp;'); ?></td>
   <td class="mbox"><a class="button" href="admin.php?action=designs&job=templates_file_edit&id=<?php echo $design['template']; ?>&dir=<?php echo rawurlencode( iif(!empty($sub), $sub.'/')); ?>&file=<?php echo rawurldecode($file); ?>"><?php echo $lang->phrase('admin_design_edit'); ?></a></td>
   <td class="mbox">&nbsp;</td>
   <td class="mbox"><input type="radio" name="old" checked="checked" value="<?php echo urldecode($defpath.'/'.$file); ?>" /></td>
   <td class="mbox"><input type="radio" name="new" value="<?php echo urldecode($defpath.'/'.$file); ?>" /></td>
  </tr>
  <tr><td class="ubox" colspan="7" align="center"><input accesskey="s" type="submit" value="<?php echo $lang->phrase('admin_design_compare_versions'); ?>" /></td></tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'templates_file_compare') {
	echo head();
	include('classes/diff/class.diff.php');
	include('classes/diff/function.diff.php');
	$old = $gpc->get('old', none);
	$new = $gpc->get('new', none);
	if (empty($old) || empty($new)) {
		error('javascript:history.back(-1);',$lang->phrase('admin_design_choose_an_old_and_new_version'));
	}
	$origText = file_get_contents($old);
	$finalText = file_get_contents($new);
	$diff = makeDiff(trim($origText), trim($finalText));
	?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%" class="border">
	 <tr>
	  <td width="50%" class="obox"><?php echo $lang->phrase('admin_design_old'); ?></td>
	  <td width="50%" class="obox"><?php echo $lang->phrase('admin_design_new'); ?></td>
	 </tr>
	<?php
	foreach ($diff['final'] as $idx => $undef) {
	 	if ($diff['orig'][$idx]['type'] == 'edit' && $diff['final'][$idx]['type'] == 'edit') {
	 		$tab = calcdiffer($diff['orig'][$idx]['line'], $diff['final'][$idx]['line']);
			$edit_o = '';
			$edit_f = '';
			foreach($tab as $k) {
	  			if ($k[0] == '+') {
	  				$edit_o .= '<span style="color:red">'.$k[1].'</span>';
	  			}
	  			elseif ($k[0] == '-') {
	  				$edit_f .= '<span style="color:green">'.$k[1].'</span>';
	  			}
	  			else {
	  				$edit_o .= $k[1];
	  				$edit_f .= $k[1];
	  			}
	  		}
	  	}
	  ?>
	 <tr>
	  <td class="mbox" style="font-family:monospace">
	  <?php if ($diff['orig'][$idx]['type'] == 'edit') { echo $edit_o; } elseif ($diff['orig'][$idx]['type'] == 'add') { ?>
	  <span style="color:green"><?php echo $diff['orig'][$idx]['line']; ?></span>
	  <?php } elseif ($diff['orig'][$idx]['type'] == 'subs') { ?>
	  <span style="color:red"><?php echo $diff['orig'][$idx]['line']; ?></span>
	  <?php } else { echo $diff['orig'][$idx]['line']; } ?>
	  </td>
	  <td class="mbox" style="font-family:monospace">
	  <?php if ($diff['final'][$idx]['type'] == 'edit') { echo $edit_f; } elseif ($diff['final'][$idx]['type'] == 'add') { ?>
	  <span style="color:green"><?php echo $diff['final'][$idx]['line']; ?></span>
	  <?php } elseif ($diff['final'][$idx]['type'] == 'subs') { ?>
	  <span style="color:red"><?php echo $diff['final'][$idx]['line']; ?></span>
	  <?php } else { echo $diff['final'][$idx]['line']; } ?>
	  </td>
	 </tr>
	 <?php } ?>
	</table>
	<?php
	echo foot();
}
elseif ($job == 'templates_browse') {
	echo head();
	$id = $gpc->get('id', int);
	$sub = rawurldecode($gpc->get('dir', none));
	$path = 'templates/' . $id . iif(!empty($sub), "/{$sub}");

	$result = $db->query('SELECT template FROM '.$db->pre.'designs WHERE id = "'.$config['templatedir'].'" LIMIT 1');
	$design = $db->fetch_assoc($result);
	if ($id != $design['template']) {
		$opath = 'templates/' . $design['template'] . iif(!empty($sub), "/{$sub}");
		$dirs = recur_dir($opath);
	}
	else {
		$dirs = recur_dir($path);
	}
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2">Templates</td>
  </tr>
  <tr>
   <td class="ubox" width="50%">File</td>
   <td class="ubox" width="50%">Action</td>
  </tr>
	<?php
	foreach ($dirs as $dir) {
		if ($dir['dir']) {
			if (isset($dir['content']) && is_array($dir['content']) && count($dir['content']) > 0) {
				$empty = false;
			}
			else {
				$empty = true;
			}
			if (file_exists($path.'/'.$dir['name'])) {
				$color = 'green';
			}
			else {
				$color = 'black';
			}
			?>
		  <tr>
		   <td class="mmbox" colspan="2">
		   <a style="color: <?php echo $color; ?>" href="admin.php?action=designs&job=templates_browse&id=<?php echo $id; ?>&dir=<?php echo rawurlencode( iif(!empty($sub), $sub.'/') . $dir['name'] ); ?>">
		   <?php echo $dir['name'].iif($empty, ' (Empty)', ' ('.count($dir['content']).')'); ?>
		   </a></td>
		  </tr>
			<?php
		}
		else {
			if (file_exists($path.'/'.$dir['name'])) {
				$color = 'green';
				$custom = true;
			}
			else {
				$color = 'black';
				$custom = false;
			}
			$extension = get_extension($dir['name']);
			if (stripos($extension, 'bak') !== false || stripos($extension, 'htaccess') !== false) {
				continue;
			}
		?>
	  <tr>
	   <td class="mbox" width="50%" style="color: <?php echo $color; ?>"><?php echo $dir['name']; ?></td>
	   <td class="mbox" width="50%">
	   <a class="button" href="admin.php?action=designs&job=templates_file_edit&id=<?php echo $id; ?>&dir=<?php echo rawurlencode( iif(!empty($sub), $sub.'/')); ?>&file=<?php echo rawurldecode($dir['name']); ?>"><?php echo $lang->phrase('admin_design_edit'); ?></a>
	   <a class="button" href="admin.php?action=designs&job=templates_file_history&id=<?php echo $id; ?>&dir=<?php echo rawurlencode( iif(!empty($sub), $sub.'/')); ?>&file=<?php echo rawurldecode($dir['name']); ?>"><?php echo $lang->phrase('admin_design_view_history'); ?></a>
	   </td>
	  </tr>
	  <?php
		}
	}
	?>
	</table><br class="minibr" />
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox"><?php echo $lang->phrase('admin_design_color'); ?></td>
  </tr>
  <tr>
   <td class="mbox" style="color: black;"><?php echo $lang->phrase('admin_design_template_is_unchanged'); ?></td>
  </tr>
  <tr>
   <td class="mbox" style="color: green;"><?php echo $lang->phrase('admin_design_template_is_customized'); ?></td>
  </tr>
 </table>
	<?php
	echo foot();
}
elseif ($job == 'css') {
	echo head();
	$dir = "designs/";
	$d = dir($dir);
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="3"><span style="float: right;"><a class="button" href="admin.php?action=designs&amp;job=css_add"><?php echo $lang->phrase('admin_design_add_new_css'); ?></a></span><?php echo $lang->phrase('admin_design_stylesheet_manager'); ?></td>
  </tr>
  <tr>
   <td class="ubox" width="40%"><?php echo $lang->phrase('admin_design_directory'); ?></td>
   <td class="ubox" width="10%"><?php echo $lang->phrase('admin_design_files'); ?></td>
   <td class="ubox" width="40%"><?php echo $lang->phrase('admin_design_action'); ?></td>
  </tr>
  <?php
	while (false !== ($entry = $d->read())) {
		if ($entry != '..' && $entry != '.' && preg_match('/^\d{1,}$/', $entry) && is_dir($dir.$entry)) {
			$files = count_dir($dir.$entry);
  ?>
  <tr>
   <td class="mbox"><?php echo $entry; ?></td>
   <td class="mbox" align="right"><?php echo $files; ?></td>
   <td class="mbox">
   <a class="button" href="admin.php?action=explorer&amp;path=<?php echo urlencode('designs/'.$entry.'/'); ?>"><?php echo $lang->phrase('admin_design_browse'); ?></a>
   <a class="button" href="admin.php?action=designs&amp;job=css_export&amp;id=<?php echo $entry; ?>"><?php echo $lang->phrase('admin_design_export'); ?></a>
   <a class="button" href="admin.php?action=designs&amp;job=confirm_delete&amp;type=css&amp;id=<?php echo $entry; ?>"><?php echo $lang->phrase('admin_design_delete'); ?></a>
   </td>
  </tr>
  <?php } } ?>
 </table>
	<?php
	$d->close();
	echo foot();
}
/*
elseif ($job == 'css_browse') {

	echo head();
	$id = $gpc->get('id', int);
	$path = 'designs/'.$id;
	if (!is_dir($path)) {
		error('admin.php?action=designs&job=css', $lang->phrase('admin_design_css_directory_dosent_exist'));
	}
	$dirs = recur_dir($path);
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="3"><?php echo $lang->phrase('admin_design_images'); ?></td>
  </tr>
  <tr>
   <td class="ubox" width="40%"><?php echo $lang->phrase('admin_design_file'); ?></td>
   <td class="ubox" width="10%"><?php echo $lang->phrase('admin_design_size'); ?></td>
   <td class="ubox" width="40%"><?php echo $lang->phrase('admin_design_action'); ?></td>
  </tr>
	<?php showthis_css($dirs); ?>
 </table>
	<?php
	echo foot();
}
*/
elseif ($job == 'css_delete') {
	$id = $gpc->get('id', int);
	echo head();
	$dir = 'designs/'.$id;
	rmdirr($dir);
	@clearstatcache();
	if (file_exists($dir) || is_dir($dir)) {
		error('admin.php?action=designs&job=css', $lang->phrase('admin_design_directory_couldnt_be_deleted'));
	}
	else {
		ok('admin.php?action=designs&job=css', $lang->phrase('admin_design_directory_successfully_deleted'));
	}
}
elseif ($job == 'css_add') {
	echo head();
	$result = $db->query('SELECT stylesheet FROM '.$db->pre.'designs GROUP BY stylesheet');
	while ($row = $db->fetch_assoc($result)) {
		$existing[] = $row['stylesheet'];
	}
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=designs&job=css_add2">
 <table class="border" cellpadding="4" cellspacing="0" border="0">
  <tr><td class="obox" colspan="2"><?php echo $lang->phrase('admin_design_import_new_stylesheets'); ?></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_design_either_upload_a_file'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_design_allowed_file_types_and_max_file_size'); ?><?php echo formatFilesize(ini_maxupload()); ?></span></td>
  <td class="mbox"><input type="file" name="upload" size="40" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_design_or_select_file_from_server'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_design_path_starting_from_root'); ?></span></td>
  <td class="mbox"><input type="text" name="server" size="50" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_design_delete_file_after_import'); ?></td>
  <td class="mbox"><input type="checkbox" name="delete" value="1" checked="checked" /></td></tr>
  <tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" value="<?php echo $lang->phrase('admin_design_form_send'); ?>" /></td></tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'css_add2') {

	$dir = $gpc->get('dir', int);
	$server = $gpc->get('server', none);
	$del = $gpc->get('delete', int);
	$inserterrors = array();

	if (!empty($_FILES['upload']['name'])) {
		$filesize = ini_maxupload();
		$filetypes = array('zip');
		$dir = realpath('temp').DIRECTORY_SEPARATOR;

		$insertuploads = array();
		require("classes/class.upload.php");

		$my_uploader = new uploader();
		$my_uploader->max_filesize($filesize);
		$my_uploader->file_types($filetypes);
		$my_uploader->set_path($dir);
		if ($my_uploader->upload('upload', $filetypes)) {
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
		error('admin.php?action=designs&job=css_add', $inserterrors);
	}

	$n = 1;
	while(is_dir('designs/'.$n)) {
		$n++;
		if ($n > 10000) {
			error('admin.php?action=designs&job=css_add', $lang->phrase('admin_design_buffer_overflow'));
		}
	}

	$tempdir = 'designs/'.$n;

	require_once('classes/class.zip.php');
	$archive = new PclZip($file);
	$failure = $archive->extract($tempdir);
	if ($failure < 1) {
		unset($archive);
		if ($del > 0) {
			$filesystem->unlink($file);
		}
		rmdirr($tempdir);
		error('admin.php?action=designs&job=css_add', $lang->phrase('admin_design_zip_archive_error'));
	}

	unset($archive);
	if ($del > 0) {
		$filesystem->unlink($file);
	}
	ok('admin.php?action=designs&job=css', $lang->phrase('admin_design_css_successfully_imported'));

}
elseif ($job == 'css_export') {
	$id = $gpc->get('id', int);

	$file = 'stylesheet'.$id.'.zip';
	$dir = "designs/{$id}/";
	$tempdir = "temp/";

	require_once('classes/class.zip.php');
	$archive = new PclZip($tempdir.$file);
	$v_list = $archive->create($dir, PCLZIP_OPT_REMOVE_PATH, $dir);
	if ($v_list == 0) {
		echo head();
		error('admin.php?action=designs&job=css', $archive->errorInfo(true));
	}
	else {
		viscacha_header('Content-Type: application/zip');
		viscacha_header('Content-Disposition: attachment; filename="'.$file.'"');
		viscacha_header('Content-Length: '.filesize($tempdir.$file));
		readfile($tempdir.$file);
		$filesystem->unlink($tempdir.$file);
	}
}
elseif ($job == 'confirm_delete') {
	$id = $gpc->get('id', int);
	$type = $gpc->get('type', str);
	if ($type == 'images') {
		$title = '';
	}
	echo head();
	?>
	<table class="border" border="0" cellspacing="0" cellpadding="4">
	<tr><td class="obox"><?php echo $lang->phrase('admin_design_delete_the_data'); ?></td></tr>
	<tr><td class="mbox">
	<p align="center"><?php echo $lang->phrase('admin_design_really_delete_this_data'); ?></p>
	<p align="center">
	<a href="admin.php?action=designs&job=<?php echo $type; ?>_delete&id=<?php echo $id; ?>"><img border="0" alt="<?php echo $lang->phrase('admin_design_yes'); ?>" src="admin/html/images/yes.gif"> <?php echo $lang->phrase('admin_design_yes'); ?></a>
	&nbsp&nbsp;&nbsp;&nbsp&nbsp;&nbsp;
	<a href="javascript: history.back(-1);"><img border="0" alt="<?php echo $lang->phrase('admin_design_no'); ?>" src="admin/html/images/no.gif"> <?php echo $lang->phrase('admin_design_no'); ?></a>
	</p>
	</td></tr>
	</table>
	<?php
	echo foot();
}
elseif ($job == 'images') {
	echo head();
	$dir = "images/";
	$d = dir($dir);
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="3"><span style="float: right;"><a class="button" href="admin.php?action=designs&amp;job=images_add"><?php echo $lang->phrase('admin_design_add_new_images'); ?></a></span><?php echo $lang->phrase('admin_design_image_manager'); ?></td>
  </tr>
  <tr>
   <td class="ubox" width="40%"><?php echo $lang->phrase('admin_design_directory'); ?></td>
   <td class="ubox" width="10%"><?php echo $lang->phrase('admin_design_files'); ?></td>
   <td class="ubox" width="40%"><?php echo $lang->phrase('admin_design_action'); ?></td>
  </tr>
  <?php
	while (false !== ($entry = $d->read())) {
		if ($entry != '..' && $entry != '.' && preg_match('/^\d{1,}$/', $entry) && is_dir($dir.$entry)) {
			$files = count_dir($dir.$entry);
  ?>
  <tr>
   <td class="mbox"><?php echo $entry; ?></td>
   <td class="mbox" align="right"><?php echo $files; ?></td>
   <td class="mbox">
   <a class="button" href="admin.php?action=explorer&path=<?php echo urlencode('./images/'.$entry.'/'); ?>"><?php echo $lang->phrase('admin_design_browse'); ?></a>
   <a class="button" href="admin.php?action=designs&amp;job=images_export&amp;id=<?php echo $entry; ?>"><?php echo $lang->phrase('admin_design_export'); ?></a>
   <a class="button" href="admin.php?action=designs&amp;job=confirm_delete&amp;type=images&amp;id=<?php echo $entry; ?>"><?php echo $lang->phrase('admin_design_delete'); ?></a>
   </td>
  </tr>
  <?php } } ?>
 </table>
	<?php
	$d->close();
	echo foot();
}
elseif ($job == 'images_delete') {
	$id = $gpc->get('id', int);
	echo head();
	$dir = 'images/'.$id;
	rmdirr($dir);
	@clearstatcache();
	if (file_exists($dir) || is_dir($dir)) {
		error('admin.php?action=designs&job=images', $lang->phrase('admin_design_directory_couldnt_be_deleted'));
	}
	else {
		ok('admin.php?action=designs&job=images', $lang->phrase('admin_design_directory_successfully_deleted'));
	}
}
elseif ($job == 'images_add') {
	echo head();
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=designs&job=images_add2">
 <table class="border" cellpadding="4" cellspacing="0" border="0">
  <tr><td class="obox" colspan="2"><?php echo $lang->phrase('admin_design_import_new_images'); ?></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_design_either_upload_a_file'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_design_allowed_file_types_and_max_file_size'); ?><?php echo formatFilesize(ini_maxupload()); ?></span></td>
  <td class="mbox"><input type="file" name="upload" size="40" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_design_or_select_file_from_server'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_design_path_starting_from_root'); ?></span></td>
  <td class="mbox"><input type="text" name="server" size="50" /></td></tr>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_design_delete_file_after_import'); ?></td>
  <td class="mbox"><input type="checkbox" name="delete" value="1" checked="checked" /></td></tr>
  <tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" value="<?php echo $lang->phrase('admin_design_form_send'); ?>" /></td></tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'images_add2') {

	$dir = $gpc->get('dir', int);
	$server = $gpc->get('server', none);
	$del = $gpc->get('delete', int);
	$inserterrors = array();

	if (!empty($_FILES['upload']['name'])) {
		$filesize = ini_maxupload();
		$filetypes = array('zip');
		$dir = realpath('temp').DIRECTORY_SEPARATOR;

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
		error('admin.php?action=designs&job=images_add', $inserterrors);
	}

	$n = 1;
	while(is_dir('images/'.$n)) {
		$n++;
		if ($n > 10000) {
			error('admin.php?action=designs&job=images_add', $lang->phrase('admin_design_buffer_overflow'));
		}
	}

	$tempdir = 'images/'.$n;

	require_once('classes/class.zip.php');
	$archive = new PclZip($file);
	$failure = $archive->extract($tempdir);
	if ($failure < 1) {
		unset($archive);
		if ($del > 0) {
			$filesystem->unlink($file);
		}
		rmdirr($tempdir);
		error('admin.php?action=designs&job=images_add', $lang->phrase('admin_design_zip_archive_error'));
	}

	unset($archive);
	if ($del > 0) {
		$filesystem->unlink($file);
	}
	ok('admin.php?action=designs&job=images', $lang->phrase('admin_design_images_successfully_imported'));

}
elseif ($job == 'images_export') {
	$id = $gpc->get('id', int);

	$file = 'images'.$id.'.zip';
	$dir = "images/{$id}/";
	$tempdir = "temp/";

	require_once('classes/class.zip.php');
	$archive = new PclZip($tempdir.$file);
	$v_list = $archive->create($dir, PCLZIP_OPT_REMOVE_PATH, $dir);
	if ($v_list == 0) {
		echo head();
		unset($archive);
		if ($del > 0) {
			$filesystem->unlink($tempdir.$file);
		}
		error('admin.php?action=designs&job=images', $archive->errorInfo(true));
	}
	else {
		viscacha_header('Content-Type: application/zip');
		viscacha_header('Content-Disposition: attachment; filename="'.$file.'"');
		viscacha_header('Content-Length: '.filesize($tempdir.$file));
		readfile($tempdir.$file);
		unset($archive);
		if ($del > 0) {
			$filesystem->unlink($tempdir.$file);
		}
		$filesystem->unlink($tempdir.$file);
	}
}
else {
	viscacha_header('Location: admin.php?action=designs&job=design&interface=1');
}
?>
