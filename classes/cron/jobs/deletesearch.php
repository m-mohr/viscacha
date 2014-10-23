<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

global $filesystem;

$dir = 'cache/search/';
$d = dir($dir);
while (false !== ($entry = $d->read())) {
	if (!is_dir($dir.$entry) && file_exists($dir.$entry) && fileatime($dir.$entry) < time()-60*60*24*7) {
		$filesystem->unlink($dir.$entry);
	}
}
$d->close();

?>