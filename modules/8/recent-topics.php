$result = $db->query("
SELECT t.id, t.board, t.topic, t.last AS date, t.prefix, l.id AS luid, l.name AS luname
FROM {$db->pre}topics AS t
	LEFT JOIN {$db->pre}forums AS f ON t.board = f.id
	LEFT JOIN {$db->pre}user AS l ON l.id = t.last_name
WHERE f.opt != 'pw' AND f.invisible != '2' AND f.active_topic = '1' AND t.status != '2' ".$slog->sqlinboards('t.board')."
ORDER BY t.last DESC
LIMIT 0,{$config['viscacha_recent_topics']['topicnum']}"
);

$prefix_obj = $scache->load('prefix');
$prefix = $prefix_obj->get();

if ($db->num_rows($result) > 0) {

	$lastbox = array();
	while ($row = $db->fetch_assoc($result)) {
		if (isset($prefix[$row['board']][$row['prefix']]) && $row['prefix'] > 0) {
			$row['prefix'] = '[' . $prefix[$row['board']][$row['prefix']]['value'] . ']';
		}
		else {
			$row['prefix'] = '';
		}

		if (mb_strlen($row['topic']) >= 75) {
			$row['topic_full'] = $row['prefix'].$row['topic'];
			$row['topic'] = mb_substr($row['topic'], 0, 75);
			$row['topic'] .= $lang->phrase('dot_more');
		}
		else {
			$row['topic_full'] = '';
		}

		$lastbox[] = $row;

	}
	$lang->assign('topicnum', $config['viscacha_recent_topics']['topicnum']);
	$tpl->assignVars(compact("lastbox"));
	echo $tpl->parse("modules/{$pluginid}/last");

}