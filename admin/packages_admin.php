<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

$lang->group("modules");

$cid = $gpc->get('cid', int);
$result = $db->execute("
	SELECT p.id AS cid, c.id, p.internal
	FROM {$db->pre}packages AS p
		LEFT JOIN {$db->pre}plugins AS c ON c.module = p.id
	WHERE c.position = CONCAT('admin_component_', p.internal)
");

if ($result->getResultCount() == 0) {
	echo head();
	error('admin.php?action=cms&job=package',$lang->phrase('admin_requested_page_doesnot_exist'));
}
else {
	$cache = $result->fetch();
	DEFINE('PACKAGE_ID', $cache['cid']);
	DEFINE('PACKAGE_INTERNAL', $cache['internal']);
	DEFINE('PACKAGE_DIR', 'modules/'.PACKAGE_ID.'/');
	DEFINE('PLUGIN_ID', $cache['id']);
	unset($cache);

	($code = $plugins->load('admin_component_'.PACKAGE_INTERNAL)) ? eval($code) : null;
}
?>