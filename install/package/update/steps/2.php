<?php
include('../data/config.inc.php');
if (!class_exists('filesystem')) {
	require_once('../classes/class.filesystem.php');
	$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
	$filesystem->set_wd($config['ftp_path']);
}
?>
<div class="bbody">
<?php 
define('CHEX', 777);
define('CHWR', 666);
require('lib/function.chmod.php');
$chmod = array(
array('path' => 'install', 'chmod' => CHEX, 'recursive' => false, 'req' => true),
array('path' => 'data', 'chmod' => CHEX, 'recursive' => false, 'req' => true),
array('path' => 'data/cron', 'chmod' => CHEX, 'recursive' => false, 'req' => true),
array('path' => 'feeds', 'chmod' => CHEX, 'recursive' => false, 'req' => true),
array('path' => 'docs', 'chmod' => CHEX, 'recursive' => false, 'req' => false),
array('path' => 'classes/cron/jobs', 'chmod' => CHEX, 'recursive' => false, 'req' => false),
array('path' => 'classes/feedcreator', 'chmod' => CHEX, 'recursive' => false, 'req' => false),
array('path' => 'classes/fonts', 'chmod' => CHEX, 'recursive' => false, 'req' => false),
array('path' => 'classes/geshi', 'chmod' => CHEX, 'recursive' => false, 'req' => false),
array('path' => 'classes/graphic/noises', 'chmod' => CHEX, 'recursive' => false, 'req' => false),
array('path' => 'admin/backup', 'chmod' => CHEX, 'recursive' => false, 'req' => false),
array('path' => 'admin/data', 'chmod' => CHEX, 'recursive' => false, 'req' => false),
array('path' => 'designs', 'chmod' => CHEX, 'recursive' => true, 'req' => false),
array('path' => 'images', 'chmod' => CHEX, 'recursive' => true, 'req' => false),
array('path' => 'components', 'chmod' => CHEX, 'recursive' => true, 'req' => false),
array('path' => 'language', 'chmod' => CHEX, 'recursive' => true, 'req' => false),
array('path' => 'cache', 'chmod' => CHEX, 'recursive' => true, 'req' => true),
array('path' => 'temp', 'chmod' => CHEX, 'recursive' => true, 'req' => true),
array('path' => 'uploads', 'chmod' => CHEX, 'recursive' => true, 'req' => true),
array('path' => 'admin/data', 'chmod' => CHWR, 'recursive' => true, 'req' => false),
array('path' => '.htaccess', 'chmod' => CHWR, 'recursive' => false, 'req' => false),
array('path' => 'data', 'chmod' => CHWR, 'recursive' => true, 'req' => true),
array('path' => 'feeds', 'chmod' => CHWR, 'recursive' => true, 'req' => false),
array('path' => 'language', 'chmod' => CHWR, 'recursive' => true, 'req' => false)
);
$path = 'docs';
$dh = opendir('../'.$path);
while ($file = readdir($dh)) {
	if($file != '.' && $file != '..') {
		$fullpath = $path.'/'.$file;
		if(is_file('../'.$fullpath)) {
			$chmod[] = array('path' => $fullpath, 'chmod' => CHWR, 'recursive' => false, 'req' => false);
		}
	}
}
closedir($dh);
$path = 'templates';
$dh = opendir('../'.$path);
while ($file = readdir($dh)) {
	if($file != '.' && $file != '..') {
		$fullpath = $path.'/'.$file;
		if(is_dir('../'.$fullpath) && intval($file) == $file) {
			$chmod[] = array('path' => $fullpath, 'chmod' => CHEX, 'recursive' => false, 'req' => false);
		}
	}
}
closedir($dh);

?>
<p>Some directories and files needs special permissions (CHMOD) to be writable und executable. 
This permissions will be checked and the result will be shown below.</p>
<p>The following states can appear:<br />
<strong class="hl_true">OK</strong>: The permissions are set correctly.<br />
<strong class="hl_null">Failure*</strong>: The permissions are not correct, but these files are only required for changing a couple of things at the Admin Control Panel. You need not to change them until you edit these files.<br />
<strong class="hl_false">Failure</strong>: The permissions are not correct and you have to set them manually (per FTP). You can not continue this setup until this permissions<br />
</p>
<table class="tables">
<tr>
	<td width="70%"><strong>File / Directory (<?php echo realpath('../'); ?>)</strong></td>
	<td width="10%"><strong>Required</strong></td>
	<td width="10%"><strong>Given</strong></td>
	<td width="10%"><strong>State</strong></td>
</tr>
<?php
$files = array();
foreach ($chmod as $dat) {
	$path = '../'.$dat['path'];
	if ($dat['recursive']) {
		$filenames = array();
		if ($dat['chmod'] == CHEX) {
			$filenames = set_chmod_r($path, 0777, CHMOD_DIR);
		}
		elseif ($dat['chmod'] == CHWR) {
			$filenames = set_chmod_r($path, 0666, CHMOD_FILE);
		}
		foreach ($filenames as $f) {
			$f = str_replace('../', '', $f);
			$files[] = array('path' => $f, 'chmod' => $dat['chmod'], 'recursive' => false, 'req' => $dat['req']);
		}
	}
	else {
		if ($dat['chmod'] == CHEX) {
			set_chmod($path, 0777, CHMOD_DIR);
		}
		elseif ($dat['chmod'] == CHWR) {
			set_chmod($path, 0666, CHMOD_FILE);
		}
		$files[] = $dat;
	}
}
@clearstatcache();
sort($files);
$failure = false;
foreach ($files as $arr) {
	$filesys_path = '../'.$arr['path'];
	$path = realpath($filesys_path);
	if (empty($path)) {
		$path = $arr['path'];
	}
	$chmod = get_chmod($filesys_path);
	if (check_chmod($arr['chmod'], $chmod)) {
		$status = '<strong class="hl_true">OK</strong>';
		$int_status = true;
	}
	elseif ($arr['req'] == false) {
		$status = '<strong class="hl_null">Failure*</strong>';
		$int_status = null;
	}
	else {
		$status = '<strong class="hl_false">Failure</strong>';
		$int_status = false;
		$failure = true;
	}
	if ($arr['req'] == true || $int_status != true) {
?>
<tr>
	<td><?php echo $arr['path']; ?></td>
	<td><?php echo $arr['chmod']; ?></td>
	<td><?php echo substr($chmod, 1, 3); ?></td>
	<td><?php echo $status; ?></td>
</tr>
<?php
	}
}
?>
</table>
</div>
<div class="bfoot center">
<?php if (!$failure) { ?>
<input type="submit" value="Continue" />
<?php } else { ?>
<a class="submit" href="index.php?step=<?php echo $step; ?>">Reload page</a>
<?php } ?>
</div>
