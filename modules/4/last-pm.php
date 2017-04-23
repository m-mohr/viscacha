BBProfile($bbcode);

$result = $db->query("
	SELECT p.dir, p.status, p.id, p.topic, p.comment, p.date, u.id AS mid, u.name
	FROM {$db->pre}pm AS p
		LEFT JOIN {$db->pre}user AS u ON u.id = p.pm_from
	WHERE p.pm_to = '{$my->id}' AND p.id = '{$_GET['id']}' AND p.dir != '2'
	ORDER BY p.date ASC
");

if ($db->num_rows($result) != 0) {
	$row = $db->fetch_assoc($result);

	if (empty($row['name'])) {
		$row['name'] = $lang->phrase('fallback_no_username');
	}

	$bbcode->setSmileys(1);
	$row['comment'] = $bbcode->parse($row['comment']);
	
	echo $tpl->parse("modules/{$pluginid}/last-pm");
}