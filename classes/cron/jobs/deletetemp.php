<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

global $filesystem;

function is_subdir($dir) {
	if (is_dir($dir) && !preg_match("~\.{1,2}$~", $dir)) {
		return true;
	}
	else {
		return false;
	}
}

function unlink_dir($dir) {
	global $filesystem;
	$dir = $dir."/";
	$d = dir($dir);
	while (false !== ($entry = $d->read())) {
		if (is_subdir($dir.$entry)) {
			unlink_dir($dir.$entry);
		}
		if ($entry != '.htaccess' && $entry != 'index.htm' && file_exists($dir.$entry) && is_file($dir.$entry) && filemtime($dir.$entry) < time()-60*60) {
			$filesystem->unlink($dir.$entry);
		}
	}
	$d->close();
}

unlink_dir('temp');

?>
