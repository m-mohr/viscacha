<?php
error_reporting(E_ALL);

define('SCRIPTNAME', 'lang2js');
define('VISCACHA_CORE', '1');

include('../data/config.inc.php');
include('../classes/function.phpcore.php');
include('../classes/class.language.php');

header('Content-type: text/javascript');

if (!empty($_REQUEST['id'])) {
	$id = intval(trim($_REQUEST['id']+0));
	$lang = new lang($id);
	$file = !empty($_REQUEST['admin']) ? 'admin/javascript' : 'javascript';
	$lang->javascript($file);
}
else {
	echo 'alert("Could not load language file for javascript without id!");';
}
?>