BBProfile($bbcode);

$result = $db->query("SELECT dir, status, id, topic, comment, date, pm_from AS mid FROM {$db->pre}pm WHERE pm_to = '{$my->id}' AND id = '{$_GET['id']}' AND dir != '2' ORDER BY date ASC");

if ($db->num_rows($result) != 0) {
	$row = $gpc->prepare($db->fetch_assoc($result));
	
	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();

	if (isset($memberdata[$row['mid']])) {
		$row['name'] = $memberdata[$row['mid']];
	}
	else {
		$row['name'] = $lang->phrase('fallback_no_username');
	}

	$bbcode->setSmileys(1);
	$bbcode->setReplace($config['wordstatus']);
	$bbcode->setAuthor($row['mid']);
	$row['comment'] = $bbcode->parse($row['comment']);
	$row['date'] = str_date($lang->phrase('dformat1'), times($row['date']));
	
	echo $tpl->parse("modules/{$pluginid}/last-pm");
}