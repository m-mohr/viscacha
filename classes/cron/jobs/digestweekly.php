<?php
global $scache, $db, $lang, $config;

$i = 0;

$lastdate = mktime(0, 0); // midnight today
$lastdate -= 7 * 24 * 60 * 60; // last week midnight

$memberdata_obj = $scache->load('memberdata');
$memberdata = $memberdata_obj->get();

$sql = '
SELECT t.id, t.board, t.topic, t.last_name, t.name as gname, u.mail, u.name, u.language
FROM '.$db->pre.'abos AS a 
	LEFT JOIN '.$db->pre.'user AS u ON u.id = a.mid 
	LEFT JOIN '.$db->pre.'topics AS t ON t.id = a.tid 
WHERE a.type = "w" AND 
	t.last > ".$lastdate." AND 
	t.last_name != u.id AND 
	u.lastvisit < t.last
';
$result = $db->query($sql,__LINE__,__FILE__);

$lang_dir = $lang->getdir(true);

while ($row = $db->fetch_assoc($result)) {
	if (isset($memberdata[$row['gname']])) {
		$row['name'] = $memberdata[$row['gname']];
	}
	if (isset($memberdata[$row['last_name']])) {
		$row['last_name'] = $memberdata[$row['last_name']];
	}

	$lang->setdir($row['language']);
	$data = $lang->get_mail('digest_w');
	$to = array('0' => array('name' => $row['name'], 'mail' => $row['mail']));
	$from = array();
	xmail($to, $from, $data['title'], $data['comment']);

}

$lang->setdir($lang_dir);

?>
