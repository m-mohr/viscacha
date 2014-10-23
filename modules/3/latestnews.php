$anznews = $config['module_'.$pluginid]['items'];
$teaserlength = $config['module_'.$pluginid]['teaserlength'];

$result = $db->query("
SELECT r.dowords, r.dosmileys, t.posts, t.prefix, t.status, t.sticky, t.id, t.board, t.topic, r.comment, r.date, r.guest, IF(r.guest = '0', u.name, r.name) AS name 
FROM {$db->pre}topics AS t LEFT JOIN {$db->pre}replies AS r ON t.id = r.topic_id LEFT JOIN {$db->pre}user AS u ON r.name=u.id 
WHERE t.mark = 'n' AND (t.status = '0' OR t.status = '1') ".$slog->sqlinboards('r.board')." AND r.tstart = '1' 
ORDER BY r.date DESC
LIMIT 0,{$anznews}"
,__LINE__,__FILE__);
BBProfile($bbcode);
while ($row = $gpc->prepare($db->fetch_assoc($result))) {
	$row['pre'] = '';
	if ($row['prefix'] > 0) {
		$prefix_obj = $scache->load('prefix');
		$prefix_arr = $prefix_obj->get($row['board']);
		if (isset($prefix_arr[$row['prefix']])) {
			$lang->assign('prefix', $prefix_arr[$row['prefix']]['value']);
			$row['pre'] = $lang->phrase('showtopic_prefix_title');
		}
	}
	
	// Verbessern: Es wird mitten in BB-Codes angeschnitten
	$row['date'] = str_date($lang->phrase('dformat1'), times($row['date']));
	if (strlen($row['comment']) > $teaserlength && strpos($row['comment'], '. ', $teaserlength) !== false) {
		$row['comment'] = substr($row['comment'],0,strpos($row['comment'], '. ', $teaserlength)).'.';
	}
	else {
		$complete = 1;
	}

	$bbcode->setSmileys($row['dosmileys']);
	if ($config['wordstatus'] == 0) {
		$row['dowords'] = 0;
	}
	$bbcode->setReplace($row['dowords']);
	$row['comment'] = $bbcode->parse($row['comment']);

	$row['posts'] = numbers($row['posts']);
	echo $tpl->parse("modules/{$pluginid}/news");
}
