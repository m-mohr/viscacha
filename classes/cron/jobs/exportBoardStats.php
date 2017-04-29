<?php
global $db, $config;

$result = $db->query("SELECT COUNT(*) FROM {$db->pre}topics");
$topics = $db->fetch_one($result);

$result = $db->query("SELECT COUNT(*) FROM {$db->pre}replies");
$posts = $db->fetch_one($result);

$result = $db->query("SELECT COUNT(*) FROM {$db->pre}user WHERE deleted_at IS NULL AND confirm = '11'");
$members = $db->fetch_one($result);

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

if (!file_exists('feeds/')) {
	mkdir('feeds/');
}
$myini = new INI();
$myini->write('feeds/board_data.ini', $data);