<?php
include('data/config.inc.php');
if (!class_exists('filesystem')) {
	require_once('install/classes/class.filesystem.php');
	$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
	$filesystem->set_wd($config['ftp_path'], $config['fpath']);
}

require('install/classes/function.chmod.php');
$chmod = getViscachaCHMODs();
?>
<div class="bbody">
<p>Some directories and files needs special permissions (CHMOD) to be writable und executable.
This permissions will be checked and the result will be shown below.</p>
<p>The following states can appear:<br />
<strong class="hl_true">OK</strong>: The permissions are set correctly.<br />
<strong class="hl_null">Failure*</strong>: The permissions are not correct, but these files are only required for changing a couple of things at the Admin Control Panel. You need not to change them until you edit these files.<br />
<strong class="hl_false">Failure</strong>: The permissions are not correct and you have to set them manually (per FTP). You can not continue this setup until this permissions are set correctly.<br />
</p>
<table class="tables">
<tr>
	<td width="70%"><strong>File / Directory (<?php echo realpath('./'); ?>)</strong></td>
	<td width="10%"><strong>Required</strong></td>
	<td width="10%"><strong>Given</strong></td>
	<td width="10%"><strong>State</strong></td>
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
$failure = false;
foreach ($files as $arr) {
	$path = realpath($arr['path']);
	if (empty($path)) {
		$path = $arr['path'];
	}
	$chmod = get_chmod($path);
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
	<td><?php echo $chmod; ?></td>
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
<a class="submit" href="index.php?package=<?php echo $package;?>&amp;step=<?php echo $step; ?>">Reload page</a>
<?php } ?>
</div>
