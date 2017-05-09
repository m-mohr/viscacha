$teaserlength = $config['viscacha_news_boxes']['teaserlength'];
$cutat = "[".$config['viscacha_news_boxes']['cutat']."]";
$board = intval($config['viscacha_news_boxes']['board']);
$limit = intval($config['viscacha_news_boxes']['items']);

$result = $db->execute("
SELECT r.dosmileys, t.posts, t.prefix, t.status, t.sticky, t.id, t.board, f.name as forumname, t.topic, r.comment, r.date, u.name, u.deleted_at
FROM {$db->pre}topics AS t
	LEFT JOIN {$db->pre}replies AS r ON t.id = r.topic_id
	LEFT JOIN {$db->pre}user AS u ON r.name = u.id
	LEFT JOIN {$db->pre}forums AS f ON t.board = f.id
WHERE t.board = '{$board}' AND t.status != '2' ".$slog->sqlinboards('t.board')." AND r.tstart = '1'
ORDER BY r.date DESC
LIMIT 0,{$limit}"
);
BBProfile($bbcode);
while ($row = $result->fetch()) {
	$row['pre'] = '';
	if ($row['prefix'] > 0) {
		$prefix_obj = $scache->load('prefix');
		$prefix_arr = $prefix_obj->get($row['board']);
		if (isset($prefix_arr[$row['prefix']])) {
			$row['pre'] = '[' . $prefix_arr[$row['prefix']]['value'] . ']';
		}
	}

	$row['date'] = str_date(times($row['date']));

	$row['read_more'] = false;
	$pos = \Str::contains($row['comment'], $cutat, false);
	if ($pos !== false) {
		$row['comment'] = \Str::substr($row['comment'], 0, $pos);
		$row['comment'] = rtrim($row['comment'], "\r\n").$lang->phrase('dot_more');
		$row['read_more'] = true;
	}
	else {
		// IntelliCut - Start
		$stack = array();
		if (\Str::length($row['comment']) > $teaserlength) {
			$culance = $teaserlength*0.1;
			$teaserlength -= $culance;
			$maxlength = $teaserlength+(2*$culance);
			if (preg_match("/[\.!\?]+[\s\r\n]+/u", $row['comment'], $matches, PREG_OFFSET_CAPTURE, $teaserlength)) {
				$pos = $matches[0][1];
				if ($maxlength > $pos) {
					$row['comment'] = \Str::substr($row['comment'], 0, $pos+2);
					$row['comment'] = rtrim($row['comment'], "\r\n").$lang->phrase('dot_more');
					$row['read_more'] = true;
				}
			}
			if ($row['read_more'] == false) {
				$pos = $teaserlength+$culance;
				if (($offset = \Str::indexOf($row['comment'], ' ', true, $pos)) !== false) {
					$newpos = $pos+$offset+1;
					if ($maxlength > $newpos) {
						$pos = $newpos;
					}
				}
				$row['comment'] = \Str::substr($row['comment'], 0, $pos).$lang->phrase('dot_more');
				$row['read_more'] = true;
			}
			$token = preg_split('/(\[[^\/\r\n\[\]]+?\]|\[\/[^\/\s\r\n\[\]]+?\])/u', $row['comment'], -1, PREG_SPLIT_DELIM_CAPTURE);
			foreach ($token as $t) {
				if (\Str::substr($t, 0, 1) == '[' && preg_match('/(\[([^\/\r\n\[\]]+?)\]|\[\/([^\/\s\r\n\[\]]+?)\])/u', $t, $match)) {
					if (isset($match[3])) {
						$top = array_shift($stack);
					}
					else {
						array_unshift($stack, $match[2]);
					}
				}
			}
			$bbcodes =	array(	'hide', 'code', 'list', 'note', 'url', 'img', 'color', 'align', 'email', 'h', 'size',
								'quote', 'edit', 'ot', 'b', 'i', 'u', 'sub', 'sup', 'tt', 'table');
			$custom = $bbcode->getCustomBB();
			foreach ($custom as $re) {
				$bbcodes[] = \Str::lower($re['tag']);
			}
			while(($top = array_shift($stack)) != null) {
				$top = preg_replace("/(\w+?)(=[^\/\r\n\[\]]+)?/iu", "\\1", $top);
				$top = \Str::lower($top);
				if (in_array($top, $bbcodes) == true) {
					$row['comment'] = "{$row['comment']}[/{$top}]";
				}
			}
		}
		// IntelliCut - End
	}
	$bbcode->setSmileys($row['dosmileys']);
	$row['comment'] = $bbcode->parse($row['comment']);

	$row['posts'] = numbers($row['posts']);
	echo $tpl->parse("modules/{$pluginid}/news");
}