<?php
if (defined('VISCACHA_CORE') == false) {
	die('Error: Hacking Attempt');
}

use Viscacha\View\Theme;

// FS: MultiLangAdmin
$lang->group("admin/designs");

($code = $plugins->load('admin_designs_jobs')) ? eval($code) : null;

if ($job == 'design') {
	echo head();
	$themes = Theme::all();
	?>
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		<tr>
			<td class="obox" colspan="6">
				<span class="right">
					<a class="button" href="admin.php?action=packages&amp;job=browser&amp;type=<?php echo IMPTYPE_DESIGN; ?>"><?php echo $lang->phrase('admin_design_browse_design'); ?></a>
					<a class="button" href="admin.php?action=designs&amp;job=design_import"><?php echo $lang->phrase('admin_design_import_design_button'); ?></a>
				</span>
				<?php echo $lang->phrase('admin_design_designs'); ?>
			</td>
		</tr>
		<tr>
			<td class="ubox" width="40%"><?php echo $lang->phrase('admin_design_name'); ?></td>
			<td class="ubox" width="5%"><?php echo $lang->phrase('admin_design_published'); ?></td>
			<td class="ubox" width="40%"><?php echo $lang->phrase('admin_design_action'); ?></td>
		</tr>
		<?php foreach ($themes as $theme) { ?>
		<tr>
			<td class="mbox"><?php echo $theme['meta']['name']; ?><?php echo iif($theme['id'] == $config['theme'], ' (<em>' . $lang->phrase('admin_design_default') . '</em>)'); ?></td>
			<td class="mbox" align="center"><?php echo noki(!$theme['hidden'], ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=designs&job=ajax_publicuse&id=' . $theme['id'] . '\')"'); ?></td>
			<td class="mbox">
				<a class="button" href="admin.php?action=designs&amp;job=design_export&amp;id=<?php echo $theme['id']; ?>"><?php echo $lang->phrase('admin_design_export'); ?></a>
				<a class="button" href="admin.php?action=designs&amp;job=design_delete&amp;id=<?php echo $themes['id']; ?>"><?php echo $lang->phrase('admin_design_delete'); ?></a>
				<?php if (!$theme['hidden'] && $config['theme'] != $theme['id']) { ?>
					<a class="button" href="admin.php?action=designs&amp;job=design_default&amp;id=<?php echo $theme['id']; ?>"><?php echo $lang->phrase('admin_design_set_as_default'); ?></a>
				<?php } ?>
			</td>
		</tr>
		<?php } ?>
	</table>
	<?php
	echo foot();
}
elseif ($job == 'design_default') {
	echo head();

	$public = Theme::all(false);
	$id = $gpc->get('id', str);

	if (isset($public[$id])) {
		include('classes/class.phpconfig.php');
		$c = new manageconfig();
		$c->getdata();
		$c->updateconfig('theme', str, $id);
		$c->savedata();
		ok('admin.php?action=designs&job=design');
	} else {
		error('admin.php?action=designs&job=design', $lang->phrase('admin_design_set_design_as_default_error'));
	}
}
elseif ($job == 'design_delete2') {
	echo head();

	$id = $gpc->get('id', str);
	$themes = Theme::all(false);

	if ($id == $config['theme']) {
		error('admin.php?action=designs&job=design', $lang->phrase('admin_design_you_cant_unpublish_design_until_other_default'));
	}
	
	if (isset($themes[$id]) && file_exists($themes[$id]['path'])) {
		$filesystem->rmdirr($themes[$id]['path']);

		$scache->load('loaddesign')->delete();
	}

	ok('admin.php?action=designs&job=design', $lang->phrase('admin_design_design_deleted_successfully'));
}
elseif ($job == 'design_import') {
	$file = $gpc->get('file', str);
	echo head();
	?>
	<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=designs&job=design_import2">
		<table class="border" cellpadding="4" cellspacing="0" border="0">
			<tr><td class="obox" colspan="2"><?php echo $lang->phrase('admin_design_import_new_design'); ?></td></tr>
			<tr><td class="mbox"><?php echo $lang->phrase('admin_design_either_upload_a_file'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_design_allowed_file_types_and_max_file_size'); ?><?php echo formatFilesize(Sys::getMaxUploadSize()); ?></span></td>
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
	$dir = $gpc->get('dir', none);
	$server = $gpc->get('server', none);
	$del = $gpc->get('delete', int);
	$inserterrors = array();

	if (!empty($_FILES['upload']['name'])) {
		$filesize = Sys::getMaxUploadSize();
		$filetypes = array('zip');
		$dir = realpath('temp') . DIRECTORY_SEPARATOR;

		require("classes/class.upload.php");
		$inserterrors = array();
		$my_uploader = new Viscacha\IO\Upload();
		$my_uploader->max_filesize($filesize);
		$my_uploader->file_types($filetypes);
		$my_uploader->set_path($dir);
		if ($my_uploader->upload('upload')) {
			if ($my_uploader->save_file()) {
				$file = $dir . $my_uploader->fileinfo('filename');
				if (!file_exists($file)) {
					$inserterrors[] = $lang->phrase('admin_design_file_dosent_exist');
				}
			}
		}
		if ($my_uploader->upload_failed()) {
			array_push($inserterrors, $my_uploader->get_error());
		}
	} elseif (file_exists($server)) {
		$ext = get_extension($server);
		if ($ext == 'zip') {
			$file = $server;
		} else {
			$inserterrors[] = $lang->phrase('admin_design_file_isnt_a_zipfile');
		}
	} else {
		$inserterrors[] = $lang->phrase('admin_design_no_valid_file_selected');
	}
	echo head();
	if (count($inserterrors) > 0) {
		error('admin.php?action=designs&job=design_import', $inserterrors);
	}
	$tempdir = 'temp/' . generate_uid() . '/';
	$filesystem->mkdir($tempdir, 0777);
	$archive = new PclZip($file);
	$v_list = $archive->extract($tempdir);
	unset($archive);
	$error = false;
	if ($v_list == 0) {
		$error = $lang->phrase('admin_design_zip_archive_error');
	}
	else if (!file_exists($tempdir . 'theme.ini')) {
		$error = $lang->phrase('admin_design_zip_archive_missing_design_ini');
	}
	else {
		$myini = new INI();
		$ini = $myini->read($tempdir . 'theme.ini');
		if (empty($ini['info']['internal'])) {
			$ini['info']['internal'] = generate_uid();
		}

		if (file_exists('themes/' . $ini['info']['internal'])) {
			$error = $lang->phrase('admin_design_zip_archive_missing_design_ini');
		}
		else {
			$filesystem->mover($tempdir, 'themes/' . $ini['info']['internal']);

			$scache->load('loaddesign')->delete();
		}
	}

	$filesystem->rmdirr($tempdir);
	if ($del > 0) {
		$filesystem->unlink($file);
	}

	if ($error !== false) {
		error('admin.php?action=designs&job=design_import', $error);
	}
	else {
		ok('admin.php?action=designs&job=design', $lang->phrase('admin_design_design_successfully_imported'));
	}
}
elseif ($job == 'design_export') {
	$id = $gpc->get('id', str);

	$source = "themes/{$id}/";
	$target = "temp/{$id}.zip";

	$archive = new PclZip($target);
	$v_list = $archive->add($source, PCLZIP_OPT_REMOVE_PATH, $source);
	unset($archive);
	if ($v_list == 0) {
		echo head();
		$filesystem->unlink($target);
		error('admin.php?action=designs&job=design', $archive->errorInfo(true));
	} else {
		viscacha_header('Content-Type: application/zip');
		viscacha_header('Content-Disposition: attachment; filename="' . basename($target) . '"');
		viscacha_header('Content-Length: ' . filesize($target));
		readfile($target);
		$filesystem->unlink($target);
	}
}
elseif ($job == 'ajax_publicuse') {
	$id = $gpc->get('id', str);

	$public = (mb_substr($id, 0, 1) != '.');
	if ($public && $id == $config['theme']) {
		die($lang->phrase('admin_design_you_cant_unpublish_design_until_other_default'));
	}
	
	$themes = Theme::all();
	if (isset($themes[$id]) && file_exists($themes[$id]['path'])) {
		if($public) {
			$new = ".{$id}";
		}
		else {
			$new = mb_substr($id, 1);
		}

		$filesystem->mover("themes/{$id}", "themes/{$new}");

		$scache->load('loaddesign')->delete();
		
		$public = invert($public);
	}	
	
	die(strval($public));
}
elseif ($job == 'design_delete') {
	$id = $gpc->get('id', str);
	echo head();

	if ($id == $config['theme']) {
		error('admin.php?action=designs&job=design', $lang->phrase('admin_design_you_cant_unpublish_design_until_other_default'));
	}
	
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
	sendStatusCode(302, $config['furl'] . '/admin.php?action=designs&job=design');
}