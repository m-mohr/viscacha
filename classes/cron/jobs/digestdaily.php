<?php

$i = 0;

$lastdate = mktime(0, 0); // midnight today
$lastdate -= 24 * 60 * 60; // yesterday midnight

$memberdata = cache_memberdata();

$sql = '
SELECT t.id, t.board, t.topic, t.last_name, t.name as gname, u.mail, u.name
FROM '.$db->pre.'abos AS a 
	LEFT JOIN '.$db->pre.'user AS u ON u.id = a.mid 
	LEFT JOIN '.$db->pre.'topics AS t ON t.id = a.tid 
WHERE a.type = "d" AND 
	t.last > ".$lastdate." AND 
	t.last_name != u.id AND 
	u.lastvisit < t.last
';
	
$result = $db->query($sql,__LINE__,__FILE__);

while ($row = $db->fetch_assoc($result)) {
	if (isset($memberdata[$row['gname']])) {
		$row['name'] = $memberdata[$row['gname']];
	}
	if (isset($memberdata[$row['last_name']])) {
		$row['last_name'] = $memberdata[$row['last_name']];
	}
		
	$data = $lang->get_mail('digest_d');
	$to = array('0' => array('name' => $row['name'], 'mail' => $row['mail']));
	$from = array();
	xmail($to, $from, $data['title'], $data['comment']);

}

?>
