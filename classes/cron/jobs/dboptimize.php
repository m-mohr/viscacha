<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

global $db, $config;

// Added with 0.8 RC5: Kill old flood elements (only old login attempts)
$limit = time() - $config['login_attempts_time']*60;
$db->query("DELETE FROM {$db->pre}flood WHERE type = 'log' AND time <= '{$limit}'");

if ($config['optimizetables'] == '*') {
	$tables = $db->list_tables();
}
else {
	$tables = explode(',', $config['optimizetables']);
}

foreach ($tables as $table) {
	$table = trim($table);
	if (!empty($table)) {
		$db->query("OPTIMIZE TABLE {$table}");
	}
}
?>