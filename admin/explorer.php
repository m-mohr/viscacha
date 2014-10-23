<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

$uploadfields = 5;
require_once("classes/function.chmod.php");
require_once("admin/lib/class.servernavigator.php");
$ServerNavigator = new ServerNavigator();

($code = $plugins->load('admin_explorer_jobs')) ? eval($code) : null;

if ($job == 'delete_install') {
	echo head();
	$path = './install/';
	if (is_dir($path) && $filesystem->rmdirr($path)) {
		$filesystem->unlink('./locked.txt');
		$name = '"./install/"';
		ok('admin.php?action=index', $lang->phrase('admin_explorer_x_successfully_deleted'));
	}
	else {
		error('admin.php?action=index');
	}
}
if ($job == 'upload') {

	$cfg = $gpc->get('cfg', str);
	$path = $gpc->get('path', none);

	if ($cfg == 'cron') {
		$ups = 1;
		$filesize = 100; // 100KB
		$filetypes = 'php';
		$dir = realpath('./classes/cron/jobs/');
		$url = 'javascript:history.back();';
	}
	elseif ($cfg == 'codefiles') {
		$ups = 1;
		$filesize = 200; // 200KB
		$filetypes = 'php';
		$dir = realpath('./classes/geshi/');
		$url = 'admin.php?action=bbcodes&job=codefiles';
	}
	elseif ($cfg == 'dbrestore') {
		$ups = 1;
		$filesize = ini_maxupload();
		$filetypes = 'sql|zip';
		$dir = realpath('./admin/backup/');
		$url = 'admin.php?action=db&job=restore';
	}
	elseif ($cfg == 'captcha_fonts') {
		$ups = 1;
		$filesize = 500; // 500KB
		$filetypes = 'ttf';
		$dir = realpath('./classes/fonts/');
		$url = 'admin.php?action=misc&job=captcha_fonts';
	}
	elseif ($cfg == 'captcha_noises') {
		$ups = 1;
		$filesize = 200; // 200KB
		$filetypes = 'jpg';
		$dir = realpath('./classes/graphic/noises/');
		$url = 'admin.php?action=misc&job=captcha_noises';
		$imgwidth = 300;
		$imgheight = 80;
	}
	else {
		$ups = $uploadfields;
		$filesize = ini_maxupload();
		$filetypes = '';
		$path = $gpc->get('path');
		$dir = realpath($path);
		$url = 'admin.php?action=explorer&path='.urlencode($ServerNavigator->realPath($path));
	}
	$filesize *= 1024;
	$filetypes = explode('|', $filetypes);
	foreach ($filetypes as $key => $value) {
		if (empty($value)) {
			unset($filetypes[$key]);
		}
	}

	$insertuploads = array();
	$inserterrors = array();
	require("classes/class.upload.php");

    $success = 0;
	for ($i = 0; $i < $ups; $i++) {
	    if (empty($_FILES['upload_'.$i]['name'])) {
	    	continue;
	    }

	    $my_uploader = new uploader();
		$my_uploader->max_filesize($filesize);
		$my_uploader->file_types($filetypes);
		$my_uploader->set_path($dir.DIRECTORY_SEPARATOR);
		if (isset($imgwidth) && isset($imgheight)) {
			$my_uploader->max_image_size($imgwidth, $imgheight);
		}
		if ($my_uploader->upload('upload_'.$i)) {
			$my_uploader->save_file();
		}
		if ($my_uploader->upload_failed()) {
			array_push($inserterrors,$my_uploader->get_error());
		}
    	$file = $dir.DIRECTORY_SEPARATOR.$my_uploader->fileinfo('filename');
    	if (!file_exists($file)) {
    	    $inserterrors[] = $lang->phrase('admin_explorer_file_does_not_exist');
    	}
    	else {
    	    $success++;
    	}
	}
	echo head();
	if ($success == 0) {
	    $inserterrors[] = $lang->phrase('admin_explorer_no_file_successfully_uploaded');
	}

	if (count($inserterrors) > 0) {
		error($url, $inserterrors);
	}
	else {
		if ($cfg == 'captcha_fonts') {
			$n = 1;
			while(file_exists($dir.DIRECTORY_SEPARATOR.'captcha_'.$n.'.ttf')) {
				$n++;
			}
			$filesystem->rename($dir.DIRECTORY_SEPARATOR.$my_uploader->fileinfo('filename'), $dir.DIRECTORY_SEPARATOR.'captcha_'.$n.'.ttf');
		}
		elseif ($cfg == 'captcha_noises') {
			$n = 1;
			while(file_exists($dir.DIRECTORY_SEPARATOR.'noise_'.$n.'.jpg')) {
				$n++;
			}
			$filesystem->rename($dir.DIRECTORY_SEPARATOR.$my_uploader->fileinfo('filename'), $dir.DIRECTORY_SEPARATOR.'noise_'.$n.'.jpg');
		}
		ok($url, $lang->phrase('admin_explorer_upload_ready'));
	}
}
elseif ($job == 'newdir') {
	$path = urldecode($gpc->get('path', none));
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=explorer&job=newdir2">
<input type="hidden" name="path" value="<?php echo $path; ?>">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_explorer_create_a_new_directory'); ?></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_explorer_name_for_directory'); ?></td>
   <td class="mbox"><input type="text" name="name" size="30"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_explorer_chmod_label'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_explorer_chmod_label_desc'); ?></span></td>
   <td class="mbox"><select name="chmod">
   <option value="777">777</option>
   <option value="755" selected="selected">755</option>
   <option value="700">700</option>
   <option value="666">666</option>
   <option value="644">644</option>
   <option value="600">600</option>
   <option value="400">400</option>
   </select></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_explorer_form_create'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == "newdir2") {
	$chmod = $gpc->get('chmod', int);
	$name = $gpc->get('name', str, 'New Directory');
	$path = urldecode($gpc->get('path', none));
	$new = $path.$name.'/';
	echo head();
	if ($filesystem->mkdir($new, chmod_str2oct($chmod))) {
		ok('admin.php?action=explorer&path='.urlencode($new));
	}
	else {
		error('admin.php?action=explorer&path='.urlencode($path));
	}
}
elseif ($job == "chmod") {
	$path = $gpc->get('path', none);
	$chmod = get_chmod($path);
	echo head(' onload="octalchange()"');
	?>
<form name="form" method="post" action="admin.php?action=explorer&job=chmod2">
<input type="hidden" name="path" value="<?php echo $path; ?>">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_explorer_set_chmod_title'); ?></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_explorer_set_new_chmod'); ?></td>
   <td class="mbox"><input type="text" name="chmod" id="chmod" value="<?php echo $chmod; ?>" size="4" onKeyUp="octalchange()"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_explorer_chmod_helper'); ?>
   <noscript><br /><span class="stext"><?php echo $lang->phrase('admin_explorer_helper_requires_js'); ?></span></noscript>
   </td>
   <td class="mbox">
	<table class="inlinetable">
	<tr>
	<th>&nbsp;</th>
	<th><?php echo $lang->phrase('admin_explorer_chmod_owner'); ?></th>
	<th><?php echo $lang->phrase('admin_explorer_chmod_group'); ?></th>
	<th><?php echo $lang->phrase('admin_explorer_chmod_other'); ?></th>
	</tr>
	<tr>
	<th><?php echo $lang->phrase('admin_explorer_chmod_read'); ?></th>
	<td align="center"><input type="checkbox" id="owner4" value="4" onclick="calc_chmod()"></td>
	<td align="center"><input type="checkbox" id="group4" value="4" onclick="calc_chmod()"></td>
	<td align="center"><input type="checkbox" id="other4" value="4" onclick="calc_chmod()"></td>
	</tr>
	<tr>
	<th><?php echo $lang->phrase('admin_explorer_chmod_write'); ?></th>
	<td align="center"><input type="checkbox" id="owner2" value="2" onclick="calc_chmod()"></td>
	<td align="center"><input type="checkbox" id="group2" value="2" onclick="calc_chmod()"></td>
	<td align="center"><input type="checkbox" id="other2" value="2" onclick="calc_chmod()"></td>
	</tr>
	<tr>
	<th><?php echo $lang->phrase('admin_explorer_chmod_execute'); ?></th>
	<td align="center"><input type="checkbox" id="owner1" value="1" onclick="calc_chmod()"></td>
	<td align="center"><input type="checkbox" id="group1" value="1" onclick="calc_chmod()"></td>
	<td align="center"><input type="checkbox" id="other1" value="1" onclick="calc_chmod()"></td>
	</tr>
	</table>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_explorer_form_set'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == "chmod2") {
	echo head();
	$path = $gpc->get('path', none);
	$chmod = $gpc->get('chmod', int);
	$repath = urlencode(extract_dir($path, false));
	if ($filesystem->chmod($path, chmod_str2oct($chmod))) {
		ok('admin.php?action=explorer&path='.$repath);
	}
	else {
		error('admin.php?action=explorer&path='.$repath);
	}
}
elseif ($job == "rename") {
	$path = urldecode($gpc->get('path', none));
	$type = $gpc->get('type', str);
	$name = iif($type == 'dir', $lang->phrase('admin_explorer_switch_dir'), $lang->phrase('admin_explorer_switch_file'));
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=explorer&job=rename2">
<input type="hidden" name="path" value="<?php echo $path; ?>">
<input type="hidden" name="type" value="<?php echo $type; ?>">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_explorer_rename_a_x'); ?></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_explorer_new_name_of_x'); ?><?php echo iif($type != 'dir', '<br /><span class="stext">'.$lang->phrase('admin_explorer_append_the_extension').'</span>'); ?></td>
   <td class="mbox"><input type="text" name="name" size="30"></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_explorer_form_rename'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == "rename2") {
	echo head();
	$type = $gpc->get('type', str);
	$source = urldecode($gpc->get('path', none));
	$newname = $gpc->get('name', str);
	if (empty($newname)) {
		error('admin.php?action=explorer&job=rename&path='.urlencode($source), $lang->phrase('admin_explorer_no_new_name_specified'));
	}

	$repath = urlencode(extract_dir($source, false));
	if ($type == 'dir') {
		$dest = extract_dir($source).$newname;
		if ($filesystem->rename($source, $dest)) {
			ok('admin.php?action=explorer&path='.$repath);
		}
		else {
			error('admin.php?action=explorer&path='.$repath);
		}
	}
	else {
		$oldDir = getcwd();
		chdir(extract_dir($source));
		if (@rename(basename($source), $newname)) {
			chdir($oldDir);
			ok('admin.php?action=explorer&path='.$repath);
		}
		else {
			chdir($oldDir);
			error('admin.php?action=explorer&path='.$repath);
		}
	}
}
elseif ($job == "delete") {
	$path = urldecode($gpc->get('path', none));
	$type = $gpc->get('type', str);
	$name = iif($type == 'dir', $lang->phrase('admin_explorer_switch_dir'), $lang->phrase('admin_explorer_switch_file'));
	echo head();
	if (!file_exists($path)) {
		$name = ucfirst($name);
		error('admin.php?action=explorer&path='.urlencode(extract_dir($path, false)), $lang->phrase('admin_explorer_x_does_not_exist'));
	}
	?>
<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	<tr><td class="obox"><?php echo $lang->phrase('admin_explorer_delete_x'); ?></td></tr>
	<tr><td class="mbox">
	<p align="center"><?php echo $lang->phrase('admin_explorer_confirm_delete'); ?></p>
	<p align="center">
	<a href="admin.php?action=explorer&job=delete2&type=<?php echo $type; ?>&path=<?php echo urlencode($path); ?>"><img border="0" alt="" src="admin/html/images/yes.gif"> <?php echo $lang->phrase('admin_explorer_yes'); ?></a>
	&nbsp&nbsp;&nbsp;&nbsp&nbsp;&nbsp;
	<a href="javascript: history.back(-1);"><img border="0" alt="" src="admin/html/images/no.gif"> <?php echo $lang->phrase('admin_explorer_no'); ?></a>
	</p>
	</td></tr>
</table>
	<?php
	echo foot();
}
elseif ($job == "delete2") {
	$path = urldecode($gpc->get('path', none));
	$type = $gpc->get('type', str);
	$name = iif($type == 'dir', $lang->phrase('admin_explorer_switch_dir'), $lang->phrase('admin_explorer_switch_file'));
	echo head();

	$repath = urlencode(extract_dir($path, false));
	if (@$filesystem->rmdirr($path)) {
		$name = ucfirst($name);
		ok('admin.php?action=explorer&path='.$repath, $lang->phrase('admin_explorer_x_successfully_deleted'));
	}
	else {
		error('admin.php?action=explorer&path='.$repath);
	}
}
elseif ($job == "edit") {
	echo head();
	$file = urldecode($gpc->get('path', none));

	set_chmod($file, 0666, CHMOD_FILE);
	@clearstatcache();
	$given = get_chmod($file);
	if (!$ServerNavigator->checkEdit($file) || !check_chmod(CHMOD_WR, $given)) {
		error('admin.php?action=explorer&path='.urlencode(extract_dir($file, false)), $lang->phrase('admin_explorer_file_is_not_editable'));
	}
	$content = file_get_contents($file);
	?>
<form name="form" method="post" action="admin.php?action=explorer&job=edit2&path=<?php echo urlencode($file); ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_explorer_edit_a_file'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="15%"><?php echo $lang->phrase('admin_explorer_edit_content'); ?></td>
   <td class="mbox" width="85%"><textarea name="content" rows="20" cols="110" class="texteditor"><?php echo htmlspecialchars($content); ?></textarea></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_explorer_form_save'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == "edit2") {
	echo head();
	$file = urldecode($gpc->get('path', none));
	if (!$ServerNavigator->checkEdit($file)) {
		error('admin.php?action=explorer&path='.urlencode(extract_dir($file, false)), $lang->phrase('admin_explorer_file_is_not_editable'));
	}
	$content = $gpc->get('content', none);
	$filesystem->file_put_contents($file, $content);
	ok('admin.php?action=explorer&path='.urlencode(extract_dir($file, false)), $lang->phrase('admin_explorer_file_successfully_saved'));
}
elseif ($job == "extract") {
	echo head();
	$file = urldecode($gpc->get('path', none));
	if (!$ServerNavigator->checkExtract($file)) {
		error('admin.php?action=explorer&path='.urlencode(extract_dir($file, false)), $lang->phrase('admin_explorer_file_format_is_not_supported'));
	}
	$newdir = realpath(extract_dir($file, false));
	$filename = basename($file, get_extension($file, true));
	$newdir .= DIRECTORY_SEPARATOR.$filename;
	?>
<form name="form" method="post" action="admin.php?action=explorer&job=extract2">
<input type="hidden" name="path" value="<?php echo $file; ?>">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_explorer_extract_an_compressed_archive'); ?> (<?php echo implode(', ', $ServerNavigator->extract); ?>)</td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_explorer_extract_to'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_explorer_extract_to_info'); ?></span></td>
   <td class="mbox"><input type="text" name="to" size="60" value="<?php echo $newdir; ?>"></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_explorer_form_extract'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == "extract2") {
	echo head();
	$file = $gpc->get('path', none);
	$dir = $gpc->get('to', none);

	set_chmod($dir, 0777, CHMOD_EX);
	$redirect = 'admin.php?action=explorer&path='.urlencode(extract_dir($file, false));
	if (!preg_match('#\.(tar\.gz|tar|gz|zip)$#is', $file, $ext)) {
		error($redirect, $lang->phrase('admin_explorer_archive_is_not_supported'));
	}
	if (isset($ext[1])) {
		$extension = $ext[1];
		if ($extension == 'zip') {
			include('classes/class.zip.php');
			$archive = new PclZip($file);
			if ($archive->extract(PCLZIP_OPT_PATH, $dir) == 0) {
				error($redirect, $archive->errorInfo(true));
			}
		}
		elseif ($extension == 'tar.gz') {
			gzAbortNotLoaded();
			$temp = gzTempfile($file);
			$temp = realpath($temp);
			include('classes/class.tar.php');
			$tar = new tar(dirname($temp), basename($temp));
			$tar->extract_files(realpath($dir));
			$filesystem->unlink($temp);
			if (!empty($tar->error)) {
				error($redirect, $tar->error);
			}
		}
		elseif ($extension == 'tar') {
			$file = realpath($file);
			include('classes/class.tar.php');
			$tar = new tar(dirname($file), basename($file));
			$tar->extract_files($dir);
			if (!empty($tar->error)) {
				error($redirect, $tar->error);
			}
		}
		elseif ($extension == 'gz') {
			gzAbortNotLoaded();
			$new = $dir.DIRECTORY_SEPARATOR.basename($file);
			$temp = gzTempfile($file, $new);
		}
	}
	ok($redirect);
}
elseif ($job == 'all_chmod') {
	echo head();
	$chmod = getViscachaCHMODs();
	?>
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	<tr>
		<td class="obox" colspan="4"><?php echo $lang->phrase('admin_explorer_check_chmod'); ?></td>
	</tr>
	<tr>
  		<td class="mbox" colspan="4">
			<?php echo $lang->phrase('admin_explorer_chmod_info1'); ?><br /><br />
			<?php echo $lang->phrase('admin_explorer_chmod_info2'); ?><br />
			<strong style="color: #008000;"><?php echo $lang->phrase('admin_explorer_chmod_status_ok'); ?></strong>: <?php echo $lang->phrase('admin_explorer_chmod_status_ok_info'); ?><br />
			<strong style="color: #ffaa00;"><?php echo $lang->phrase('admin_explorer_chmod_status_failure_x'); ?></strong>: <?php echo $lang->phrase('admin_explorer_chmod_status_failure_x_info'); ?><br />
			<strong style="color: #ff0000;"><?php echo $lang->phrase('admin_explorer_chmod_status_failure'); ?></strong>: <?php echo $lang->phrase('admin_explorer_chmod_status_failure_info'); ?>
  		</td>
	</tr>
	<tr class="ubox">
		<td width="60%"><strong><?php echo $lang->phrase('admin_explorer_chmod_file_dir'); ?></strong></td>
		<td width="15%"><strong><?php echo $lang->phrase('admin_explorer_required_chmod'); ?></strong></td>
		<td width="15%"><strong><?php echo $lang->phrase('admin_explorer_current_chmod'); ?></strong></td>
		<td width="10%"><strong><?php echo $lang->phrase('admin_explorer_chmod_state'); ?></strong></td>
	</tr>
	<?php
	$files = array();
	foreach ($chmod as $dat) {
		if ($dat['recursive']) {
			$filenames = array();
			if ($dat['chmod'] == CHMOD_EX) {
				$filenames = set_chmod_r($dat['path'], 0777, CHMOD_DIR);
			}
			elseif ($dat['chmod'] == CHMOD_WR) {
				$filenames = set_chmod_r($dat['path'], 0666, CHMOD_FILE);
			}
			foreach ($filenames as $f) {
				$files[] = array('path' => $f, 'chmod' => $dat['chmod'], 'recursive' => false, 'req' => $dat['req']);
			}
		}
		else {
			if ($dat['chmod'] == CHMOD_EX) {
				set_chmod($dat['path'], 0777, CHMOD_DIR);
			}
			elseif ($dat['chmod'] == CHMOD_WR) {
				set_chmod($dat['path'], 0666, CHMOD_FILE);
			}
			$files[] = $dat;
		}
	}
	@clearstatcache();
	sort($files);
	foreach ($files as $arr) {
		$chmod = get_chmod($arr['path']);
		if (check_chmod($arr['chmod'], $chmod)) {
			$status = '<strong style="color: #008000;">'.$lang->phrase('admin_explorer_chmod_status_ok').'</strong>';
		}
		elseif ($arr['req'] == false) {
			$status = '<strong style="color: #ffaa00;">'.$lang->phrase('admin_explorer_chmod_status_failure_x').'</strong>';
		}
		else {
			$status = '<strong style="color: #ff0000;">'.$lang->phrase('admin_explorer_chmod_status_failure').'</strong>';
		}
	?>
	<tr class="mbox">
		<td><?php echo $arr['path']; ?></td>
		<td><?php echo $arr['chmod']; ?></td>
		<td><?php echo $chmod; ?></td>
		<td><?php echo $status; ?></td>
	</tr>
	<?php
	}
	?>
	</table>
	<?php
	echo foot();
}
else {
	$ServerNavigator->useImageIcons(true);
	$ServerNavigator->showSubfoldersSize(true);
	echo head();
	$ServerNavigator->show();
	echo '<br />';
	$ServerNavigator->uploadForm($uploadfields);
	echo foot();
}

?>
