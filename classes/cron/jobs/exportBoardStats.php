<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

global $db, $config;

$result = $db->query("SELECT COUNT(*) FROM {$db->pre}topics");
list($topics) = $db->fetch_num($result);

$result = $db->query("SELECT COUNT(*) FROM {$db->pre}replies");
list($posts) = $db->fetch_num($result);

$result = $db->query("SELECT COUNT(*) FROM {$db->pre}user WHERE confirm = '11'");
list($members) = $db->fetch_num($result);

include("language/{$config['langdir']}/settings.lng.php");
$lngc = $lang['lang_code'];
if (!empty($lang['country_code'])) {
	$lngc .= '_'.$lang['country_code'];
}

$data = array(
	'settings' => array(
		'url' => $config['furl'],
		'name' => $config['fname'],
		'description' => $config['fdesc'],
		'version' => $config['version'],
		'language' => $lngc
	),
	'statistics' => array(
		'topics' => intval($topics),
		'posts' => intval($posts),
		'members' => intval($members)
	)
);

$myini = new INI();
$myini->write('feeds/board_data.ini', $data);

?>