<?php
include('data/config.inc.php');
if (!class_exists('filesystem')) {
	require_once('install/classes/class.filesystem.php');
	$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
	$filesystem->set_wd($config['ftp_path'], $config['fpath']);
}

$tar_packs = array(
	1 => 'update.classes.tar',
	2 => 'update.misc.tar',
	3 => 'update.rc6.tar'
);
if (empty($_REQUEST['sub']) || !isset($tar_packs[$_REQUEST['sub']])) {
	$sub = 1;
}
else {
	$sub = intval($_REQUEST['sub']);
}
require('install/classes/function.chmod.php');
require('install/classes/class.tar.php');
$tar = new tar(realpath('install/files/'), $tar_packs[$sub]);
$tar->ignore_chmod();
$error = $tar->extract_files('./');

$files = implode("\n", $tar->list_files());
$dirs = array('language' => null, 'templates' => null, 'designs' => null, 'images' => null);
preg_match_all('~^('.implode('|', array_keys($dirs)).')/(\d+)/([^\n]+)$~m', $files, $replicable, PREG_SET_ORDER);
foreach ($replicable as $rep) {
	if ($dirs[$rep[1]] === null) {
		$dirs[$rep[1]] = array();
		$dir = dir($rep[1]);
		while (false !== ($entry = $dir->read())) {
			if (is_id($entry) && is_dir($rep[1].'/'.$entry)) {
				$dirs[$rep[1]][$entry] = $rep[1].'/'.$entry.'/';
			}
		}
		$dir->close();
	}

	$content = file_get_contents($rep[0]);
	foreach ($dirs[$rep[1]] as $path) {
		$target = $path.$rep[3];
		if (file_exists($target)) {
			$filesystem->chmod($target, 0666);
		}
		elseif (!@is_dir(dirname($target))) {
			$filesystem->mkdir(dirname($target), 0777);
		}
		$filesystem->file_put_contents($target, $content);
	}
}
?>
<div class="bfoot">Source file updater - Step <?php echo $sub; ?> of <?php echo count($tar_packs); ?> - Currently extracting: <?php echo $tar_packs[$sub]; ?></div>
<?php if ($error === false) { ?>
<div class="bbody">
	<strong>A critical error occured. Please contact the <a href="http://www.viscacha.org" target="_blank">Viscacha Support Team</a> for assistance!</strong><br />
	Error message: <?php echo $tar->error; ?>
</div>
<?php } else { ?>
<div class="bbody">
<p>
The updater tried to update the Viscacha source files.<br />
<?php if (count($error) > 0) { ?>
<b>The following files could not be updated and must be updated manually before clicking the link below:</b>
<textarea class="codearea"><?php echo implode("\r\n", $error); ?></textarea>
<?php } else { ?>
All files updated succesfully!
<?php } ?>
</p>
</div>
<div class="bfoot center">
<?php if ($sub < count($tar_packs)) { ?>
<a class="submit" href="index.php?package=<?php echo $package;?>&amp;step=<?php echo $step; ?>&amp;sub=<?php echo $sub+1; ?>">Click here to extract the next file...</a>
<?php } elseif ($sub == count($tar_packs)) { ?>
<input type="submit" value="Continue" />
<?php } } ?>
</div>