<?php
global $scache, $db, $lang, $config;

$lastdate = mktime(0, 0); // midnight today
$lastdate -= 7 * 24 * 60 * 60; // last week midnight

$result = $db->query("
SELECT t.id, t.board, t.topic, t.last_name, u.mail, u.name, u.language, l.name AS last_name
FROM {$db->pre}abos AS a
	LEFT JOIN {$db->pre}user AS u ON u.id = a.mid
	LEFT JOIN {$db->pre}topics AS t ON t.id = a.tid
	LEFT JOIN {$db->pre}user AS l ON l.id = t.last_name
WHERE a.type = 'w' AND t.last > '{$lastdate}' AND t.last_name != u.id
");

$lang_dir = $lang->getdir(true);

while ($row = $db->fetch_assoc($result)) {
	$lang->setdir($row['language']);
	$lang->assign('row', $row);
	$data = $lang->get_mail('digest_w');
	$to = array('0' => array('name' => $row['name'], 'mail' => $row['mail']));
	$from = array();
	xmail($to, $from, $data['title'], $data['comment']);

}

$lang->setdir($lang_dir);

?>