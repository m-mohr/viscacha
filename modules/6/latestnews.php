<?php
global $slog;
global $gpc;

$result = $db->query("
SELECT r.dowords, r.dosmileys, t.posts, t.prefix, t.status, t.sticky, t.id, t.board, t.topic, r.comment, r.date, IF(r.email = '', u.name, r.name) AS name 
FROM {$db->pre}topics AS t LEFT JOIN {$db->pre}replies AS r ON t.id = r.topic_id LEFT JOIN {$db->pre}user AS u ON r.name=u.id 
WHERE t.mark = 'n' AND t.status != 2 ".$slog->sqlinboards('r.board')." AND r.tstart = '1' 
ORDER BY r.date DESC
LIMIT ".$ini['params']['num']
,__LINE__,__FILE__);
	
while ($row = $gpc->prepare($db->fetch_assoc($result))) {
	$row['pre'] = '';
	if ($row['prefix'] > 0) {
		$prefix = cache_prefix($row['board']);
		if (isset($prefix[$row['prefix']])) {
			$row['pre'] = $prefix[$row['prefix']];
			$lang->assign('pre', $row['pre']);
			$row['pre'] = $lang->phrase('showtopic_prefix_title');
		}
	}
	
	$bbcode->setSmileys($row['dosmileys']);
	if ($config['wordstatus'] == 0) {
		$row['dowords'] = 0;
	}
	$bbcode->setReplace($row['dowords']);
	$row['comment'] = $bbcode->parse($row['comment']);
	
	$row['date'] = str_date($lang->phrase('dformat1'), times($row['date']));
	if (strlen($row['comment']) > $ini['params']['len'] && strpos($row['comment'], '. ', $ini['params']['len']) !== FALSE) {
		$row['comment'] = substr($row['comment'],0,strpos($row['comment'], '. ', $ini['params']['len'])).'.';
	}
	else {
		$complete = 1;
	}
	$row['posts'] = numbers($row['posts']);
	$tpl->globalvars(compact("row","complete"));
	$lang->assign('posts',$row['posts']);
	echo $tpl->parse($dir."news");
}
?>
