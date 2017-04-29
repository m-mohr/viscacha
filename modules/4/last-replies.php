$num = $config['viscacha_addreply_last_replies']['repliesnum'];

$result = $db->execute('
SELECT r.dosmileys, r.id, r.comment, r.date, u.id AS uid, u.name, u.deleted_at
FROM '.$db->pre.'replies AS r
	LEFT JOIN '.$db->pre.'user AS u ON u.id = r.name
WHERE r.topic_id = "'.$info['id'].'"
ORDER BY r.date DESC
LIMIT '.$num
);

BBProfile($bbcode);
$data = array();
while ($row = $result->fetchObject()) {
	$bbcode->setSmileys($row->dosmileys);
	if ($info['status'] == 2) {
		$row->comment = $bbcode->ReplaceTextOnce($row->comment, 'moved');
	}
	$row->comment = $bbcode->parse($row->comment);

	$data[] = $row;
}

echo $tpl->parse("modules/{$pluginid}/last_replies");