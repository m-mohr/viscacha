<?php
error_reporting(E_ALL);

define('SCRIPTNAME', 'lang2js');
define('VISCACHA_CORE', '1');

include('../data/config.inc.php');

function extract_dir($source, $realpath = true) {
	if ($realpath) {
		$source = realpath($source);
	}
	else {
		$source = rtrim($source, '/\\');
	}
	$pos = strrpos($source, '/');
	if ($pos === false) {
		$pos = strrpos($source, '\\');
	}
	if ($pos > 0) {
		$dest = substr($source, 0, $pos+1);
	}
	else {
		$dest = '';
	}
	return $dest;
}

include('../classes/class.language.php');

header('Content-type: text/javascript');

if (!empty($_REQUEST['id'])) {
	$id = intval(trim($_REQUEST['id']+0));
	$lang = new lang($id);
	$lang->javascript();
}
else {
	echo 'alert("Could not load language file for javascript without id!");';
}
?>