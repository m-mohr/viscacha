<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

$uploadfields = 5;
require_once("admin/lib/class.servernavigator.php");
include_once('classes/class.template.php');
$tpl = new tpl();
$ServerNavigator = new ServerNavigator();

($code = $plugins->load('admin_explorer_jobs')) ? eval($code) : null;

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
    	    $inserterrors[] = 'File ('.$file.') does not exist.';
    	}
    	else {
    	    $success++;
    	}
	}
	echo head();
	if ($success == 0) {
	    $inserterrors[] = 'No file successfully uploaded!';
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
		ok($url, 'Upload ready!');
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
   <td class="obox" colspan="2">Create new directory</td>
  </tr>
  <tr> 
   <td class="mbox">Name for the new directory:</td>
   <td class="mbox"><input type="text" name="name" size="30"></td>
  </tr>
  <tr> 
   <td class="mbox">CHMOD:<br /><span class="stext">If you are not sure, use CHMOD 755. You can change it later.</span></td>
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
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Create"></td> 
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
   <td class="obox" colspan="2">Set CHMOD</td>
  </tr>
  <tr> 
   <td class="mbox">New CHMOD:</td>
   <td class="mbox"><input type="text" name="chmod" id="chmod" value="<?php echo $chmod; ?>" size="4" onKeyUp="octalchange()"></td>
  </tr>
  <tr> 
   <td class="mbox">CHMOD-Helper:
   <noscript><br /><span class="stext">You can not use this Helper, until JavaScript is enabled.</span></noscript>
   </td>
   <td class="mbox">
	<table class="inlinetable">
	<tr>
	<th>&nbsp;</th>
	<th>Owner</th>
	<th>Group</th>
	<th>Other</th>
	</tr>
	<tr>
	<th>Read</th>
	<td align="center"><input type="checkbox" id="owner4" value="4" onclick="calc_chmod()"></td>
	<td align="center"><input type="checkbox" id="group4" value="4" onclick="calc_chmod()"></td>
	<td align="center"><input type="checkbox" id="other4" value="4" onclick="calc_chmod()"></td>
	</tr>
	<tr>
	<th>Write</th>
	<td align="center"><input type="checkbox" id="owner2" value="2" onclick="calc_chmod()"></td>
	<td align="center"><input type="checkbox" id="group2" value="2" onclick="calc_chmod()"></td>
	<td align="center"><input type="checkbox" id="other2" value="2" onclick="calc_chmod()"></td>
	</tr>
	<tr>
	<th>Execute</th>
	<td align="center"><input type="checkbox" id="owner1" value="1" onclick="calc_chmod()"></td>
	<td align="center"><input type="checkbox" id="group1" value="1" onclick="calc_chmod()"></td>
	<td align="center"><input type="checkbox" id="other1" value="1" onclick="calc_chmod()"></td>
	</tr>
	</table>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="&nbsp;Set&nbsp;"></td> 
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
	$name = iif($type == 'dir', 'directory', 'file');
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=explorer&job=rename2">
<input type="hidden" name="path" value="<?php echo $path; ?>">
<input type="hidden" name="type" value="<?php echo $type; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Rename a <?php echo $name; ?></td>
  </tr>
  <tr> 
   <td class="mbox">New name of <?php echo $name; ?>:<?php echo iif($type != 'dir', '<br /><span class="stext">Append the extension!</span>'); ?></td>
   <td class="mbox"><input type="text" name="name" size="30"></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Rename"></td> 
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
		error('admin.php?action=explorer&job=rename&path='.urlencode($source), 'No new name specified!');
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
	$name = iif($type == 'dir', 'directory', 'file');
	echo head();
	if (!file_exists($path)) {
		error('admin.php?action=explorer&path='.urlencode(extract_dir($path, false)), ucfirst($name).' does not exist.');
	}
	?>
<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	<tr><td class="obox">Delete <?php echo $name; ?></td></tr>
	<tr><td class="mbox">
	<p align="center">Do you really want to delete this <?php echo $name; ?>?</p>
	<p align="center">
	<a href="admin.php?action=explorer&job=delete2&type=<?php echo $type; ?>&path=<?php echo urlencode($path); ?>"><img border="0" align="middle" alt="" src="admin/html/images/yes.gif"> Yes</a>
	&nbsp&nbsp;&nbsp;&nbsp&nbsp;&nbsp;
	<a href="javascript: history.back(-1);"><img border="0" align="middle" alt="" src="admin/html/images/no.gif"> No</a>
	</p>
	</td></tr>
</table>
	<?php
	echo foot();
}
elseif ($job == "delete2") {
	$path = urldecode($gpc->get('path', none));
	$type = $gpc->get('type', str);
	$name = iif($type == 'dir', 'directory', 'file');
	echo head();
	
	$repath = urlencode(extract_dir($path, false));
	if (@rmdirr($path)) {
		ok('admin.php?action=explorer&path='.$repath, ucfirst($name).' successfully deleted!');
	}
	else {
		error('admin.php?action=explorer&path='.$repath);
	}
}
elseif ($job == "edit") {
	echo head();
	$file = urldecode($gpc->get('path', none));
	check_writable($file);
	if (!$ServerNavigator->checkEdit($file)) {
		error('admin.php?action=explorer&path='.urlencode(extract_dir($file, false)), 'File is not editable.');
	}
	$content = file_get_contents($file);
	?>
<form name="form" method="post" action="admin.php?action=explorer&job=edit2&path=<?php echo urlencode($file); ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2"><b>Edit a File</b></td>
  </tr>
  <tr>
   <td class="mbox" width="15%">Content:</td> 
   <td class="mbox" width="85%"><textarea name="content" rows="20" cols="110" class="texteditor"><?php echo htmlspecialchars($content); ?></textarea></td> 
  </tr>
  <tr> 
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Save" /></td> 
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
		error('admin.php?action=explorer&path='.urlencode(extract_dir($file, false)), 'File is not editable.');
	}
	$content = $gpc->get('content', none);
	$filesystem->file_put_contents($file, $content);
	ok('admin.php?action=explorer&path='.urlencode(extract_dir($file, false)), 'File successfully saved.');
}
elseif ($job == "extract") {
	echo head();
	$file = urldecode($gpc->get('path', none));
	if (!$ServerNavigator->checkExtract($file)) {
		error('admin.php?action=explorer&path='.urlencode(extract_dir($file, false)), 'File is not an supported archive.');
	}
	$newdir = realpath(extract_dir($file, false));
	$filename = basename($file, get_extension($file, true));
	$newdir .= DIRECTORY_SEPARATOR.$filename;
	?>
<form name="form" method="post" action="admin.php?action=explorer&job=extract2">
<input type="hidden" name="path" value="<?php echo $path; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Extract an compressed archive (<?php echo implode(', ', $ServerNavigator->extract); ?>)</td>
  </tr>
  <tr> 
   <td class="mbox">Extract to:<br /><span class="stext">The directory must not exist. Please specify the <strong>absolute path</strong>!</span></td>
   <td class="mbox"><input type="text" name="to" size="60" value="<?php echo $newdir; ?>"></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Extract"></td> 
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
	check_executable($dir);
	$redirect = 'admin.php?action=explorer&path='.urlencode(extract_dir($file, false));
	if (!preg_match('#\.(tar\.gz|tar|gz|zip)$#is', $file, $ext)) {
		error($redirect, 'The archive is currently not supported. (Failed at position: preg_match)');
	}
	unset($extension);
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
			$tar = new tar();
			$tar->new_tar(viscacha_dirname($temp), basename($temp));
			$tar->extract_files(realpath($dir));
			$err = $tar->error;
			$filesystem->unlink($temp);
			if (!empty($err)) {
				error($redirect, $err);
			}
		}
		elseif ($extension == 'tar') {
			include('classes/class.tar.php');
			$tar = new tar();
			$file = realpath($file);
			$tar->new_tar(viscacha_dirname($file), basename($file));
			$tar->extract_files($dir);
		}
		elseif ($extension == 'gz') {
			gzAbortNotLoaded();
			$new = $dir.DIRECTORY_SEPARATOR.basename($file);
			$temp = gzTempfile($file, $new);
		}
	}
	if (!isset($extension)) {
		error($redirect, 'File is not an supported archive. (Failed at position: setting extension)');
	}
	else {
		ok($redirect);
	}
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
