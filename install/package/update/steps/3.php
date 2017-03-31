<?php
include('data/config.inc.php');
if (!class_exists('filesystem')) {
	require_once('install/classes/class.filesystem.php');
	$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
	$filesystem->set_wd($config['ftp_path'], $config['fpath']);
}

$zip_packs = array(
	1 => 'update.zip'
);
if (empty($_REQUEST['sub']) || !isset($zip_packs[$_REQUEST['sub']])) {
	$sub = 1;
}
else {
	$sub = intval($_REQUEST['sub']);
}
require('install/classes/function.chmod.php');
$zip = new PclZip('install/files/' . $zip_packs[$sub]);
$error = 0;
$files = array();
$zcontent = $zip->extract('./', PCLZIP_OPT_REPLACE_NEWER);
if (is_array($zcontent)) {
	$error = array();
	foreach($zcontent as $zc) {
		switch($zc['status']) {
			case 'ok':
				$files[] = $zc['filename'];
				break;
			case 'filtered':
				break;
			default:
				$error[] = $zc['filename'];
		}
	}
}
$files = implode("\n", $files);
$dirs = array('language' => null, 'themes' => null);
preg_match_all('~^('.implode('|', array_keys($dirs)).')/(\d+)/([^\n]+)$~mu', $files, $replicable, PREG_SET_ORDER);
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

	$isDir = is_dir($rep[0]);
	if (!$isDir) {
		$content = file_get_contents($rep[0]);
	}
	foreach ($dirs[$rep[1]] as $path) {
		$target = $path.$rep[3];
		if ($isDir) {
			if (!@is_dir($target)) {
				$filesystem->mkdir($target, 0777);
			}
		}
		else {
			if (file_exists($target)) {
				$filesystem->chmod($target, 0666);
			}
			elseif (!@is_dir(dirname($target))) {
				$filesystem->mkdir(dirname($target), 0777);
			}
			$filesystem->file_put_contents($target, $content);
		}
	}
}
?>
<div class="bfoot">Source file updater - Step <?php echo $sub; ?> of <?php echo count($zip_packs); ?> - Currently extracting: <?php echo $zip_packs[$sub]; ?></div>
<?php if ($error === 0) { ?>
<div class="bbody">
	<strong>A critical error occured. Please contact the <a href="http://www.viscacha.org" target="_blank">Viscacha Support Team</a> for assistance!</strong><br />
	Error message: <?php echo $zip->errorInfo(); ?>
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
<?php if ($sub < count($zip_packs)) { ?>
<a class="submit" href="index.php?package=<?php echo $package;?>&amp;step=<?php echo $step; ?>&amp;sub=<?php echo $sub+1; ?>">Click here to extract the next file...</a>
<?php } elseif ($sub == count($zip_packs)) { ?>
<input type="submit" value="Continue" />
<?php } } ?>
</div>