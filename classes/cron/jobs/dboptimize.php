<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

global $db, $config;

if ($config['optimizetables'] == '*') {
	$tables = $db->list_tables();
}
else {
	$tables = explode(',', $config['optimizetables']);
}

foreach ($tables as $table) {
	$table = trim($table);
	if (!empty($table)) {
		$db->query("OPTIMIZE TABLE {$table}",__LINE__,__FILE__);
	}
}

?>