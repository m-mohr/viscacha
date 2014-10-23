$topicnum = $config['module_'.$pluginid]['topicnum'];

$result = $db->query("
SELECT t.id, t.board, t.topic, t.last AS date, t.last_name AS name
FROM {$db->pre}topics AS t LEFT JOIN {$db->pre}cat AS c ON t.board = c.id 
WHERE c.opt != 'pw' AND t.status != '2' ".$slog->sqlinboards('t.board')."
ORDER BY t.last DESC 
LIMIT 0,{$topicnum}"
,__LINE__,__FILE__);

if ($db->num_rows($result) > 0) {

	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();
	
	$lastbox = array();
	while ($row = $gpc->prepare($db->fetch_assoc($result))) {
		if (is_id($row['name']) && isset($memberdata[$row['name']])) {
			$row['name'] = $memberdata[$row['name']];
		}
		$row['date'] = str_date($lang->phrase('dformat1'),times($row['date']));
		if (strxlen($row['topic']) >= 75) {
			$row['topic'] = substr($row['topic'], 0, 75);
			$row['topic'] .= $lang->phrase('dot_more');
		}
		$lastbox[] = $row;
		
	}
	echo $tpl->parse("modules/{$pluginid}/last");

}