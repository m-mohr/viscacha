$result = $db->query("
SELECT t.id, t.board, t.topic, t.last AS date, t.last_name AS name, t.prefix
FROM {$db->pre}topics AS t LEFT JOIN {$db->pre}forums AS f ON t.board = f.id
WHERE f.opt != 'pw' AND f.invisible != '2' AND f.active_topic = '1' AND t.status != '2' ".$slog->sqlinboards('t.board')."
ORDER BY t.last DESC
LIMIT 0,{$config['viscacha_recent_topics']['topicnum']}"
);

$prefix_obj = $scache->load('prefix');
$prefix = $prefix_obj->get();

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
			$row['topic'] = subxstr($row['topic'], 0, 75);
			$row['topic'] .= $lang->phrase('dot_more');
		}
		if (isset($prefix[$row['board']][$row['prefix']]) && $row['prefix'] > 0) {
			$lang->assign('prefix', $prefix[$row['board']][$row['prefix']]['value']);
			$row['prefix'] = $lang->phrase('showtopic_prefix_title');
		}
		else {
			$row['prefix'] = '';
		}
		$lastbox[] = $row;

	}
	$lang->assign('topicnum', $config['viscacha_recent_topics']['topicnum']);
	$tpl->globalvars(compact("lastbox"));
	echo $tpl->parse("modules/{$pluginid}/last");

}