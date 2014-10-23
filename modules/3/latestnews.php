$anznews = $config['module_'.$pluginid]['items'];
$teaserlength = $config['module_'.$pluginid]['teaserlength'];

$intelliCut = version_compare(PHP_VERSION, "4.3.3", ">=");

$result = $db->query("
SELECT r.dowords, r.dosmileys, t.posts, t.prefix, t.status, t.sticky, t.id, t.board, f.name as forumname, t.topic, r.comment, r.date, r.guest, IF(r.guest = '0', u.name, r.name) AS name
FROM {$db->pre}topics AS t
	LEFT JOIN {$db->pre}replies AS r ON t.id = r.topic_id
	LEFT JOIN {$db->pre}user AS u ON r.name = u.id
	LEFT JOIN {$db->pre}forums AS f ON t.board = f.id
WHERE (t.mark = 'n' OR (f.auto_status = 'n' AND t.mark = '')) AND t.status != '2' ".$slog->sqlinboards('r.board')." AND r.tstart = '1'
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

	$row['date'] = str_date($lang->phrase('dformat1'), times($row['date']));

	// IntelliCut - Start
	$row['read_more'] = false;
	$stack = array();
	if (strlen($row['comment']) > $teaserlength) {
		$culance = $teaserlength*0.1;
		$teaserlength -= $culance;
		$maxlength = $teaserlength+(2*$culance);
		if ($intelliCut && preg_match("/[\.!\?]+[\s\r\n]+/", $row['comment'], $matches, PREG_OFFSET_CAPTURE, $teaserlength)) {
			$pos = $matches[0][1];
			if ($maxlength > $pos) {
				$row['comment'] = substr($row['comment'], 0, $pos+2);
				$row['comment'] = rtrim($row['comment'], "\r\n").$lang->phrase('dot_more');
				$row['read_more'] = true;
			}
		}
		if ($row['read_more'] == false) {
			$pos = $teaserlength+$culance;
			if (($offset = strpos($row['comment'], ' ', $pos)) !== false) {
				$newpos = $pos+$offset+1;
				if ($maxlength > $newpos) {
					$pos = $newpos;
				}
			}
			$row['comment'] = substr($row['comment'], 0, $pos).$lang->phrase('dot_more');
			$row['read_more'] = true;
		}
		$token = preg_split('/(\[[^\/\r\n\[\]]+?\]|\[\/[^\/\s\r\n\[\]]+?\])/', $row['comment'], -1, PREG_SPLIT_DELIM_CAPTURE);
		foreach ($token as $t) {
			if (substr($t, 0, 1) == '[' && preg_match('/(\[([^\/\r\n\[\]]+?)\]|\[\/([^\/\s\r\n\[\]]+?)\])/', $t, $match)) {
				if (isset($match[3])) {
					$top = array_shift($stack);
				}
				else {
					array_unshift($stack, $match[2]);
				}
			}
		}
		while(($top = array_shift($stack)) != null) {
			$top = preg_replace("/(\w+?)(=[^\/\r\n\[\]]+)?/i", "\\1", $top);
			if ($top == '*' || $top == 'reader') { // Listenelemnte nicht schließen
				continue;
			}
			$row['comment'] = "{$row['comment']}[/{$top}]";
		}
	}
	// IntelliCut - End

	$bbcode->setSmileys($row['dosmileys']);
	if ($config['wordstatus'] == 0) {
		$row['dowords'] = 0;
	}
	$bbcode->setReplace($row['dowords']);
	$row['comment'] = $bbcode->parse($row['comment']);

	$row['posts'] = numbers($row['posts']);
	echo $tpl->parse("modules/{$pluginid}/news");
}
