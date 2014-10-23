<?php
error_reporting(E_ALL);
include('../data/config.inc.php');
include('../classes/class.language.php');
header('Content-type: text/javascript');
if (!empty($_REQUEST['id'])) {
	$id = intval(trim($_REQUEST['id']+0));
	$lang = new lang($id);
	$lang->javascript();
}
else {
?>
	alert("Could not load language file for javascript without id!");
<?php
}
?>
