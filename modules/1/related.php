$ignorewords = $lang->get_words();
$ignorewords = array_map("mb_strtolower", $ignorewords);

$searchtopic = Str::splitWords(mb_strtolower($info['topic']));

$sqltopic = array();
foreach ($searchtopic as $val) {
	if (mb_strlen($val) > 3) {
		if (in_array($val, $ignorewords)) {
			continue;
		}
		$sqltopic[] = $val;
	}
}
$sqltopic = array_unique($sqltopic);

$searchdata = array();
$rows = array();

if (count($sqltopic) > 0) {
	$matchsql = implode(' ', $sqltopic);

	$slog->GlobalPermissions();
	$boardsql = $slog->sqlinboards('board', 1);

	$result = $db->execute("
	SELECT id, board, topic, MATCH (topic) AGAINST ('{$matchsql}') AS af
	FROM {$db->pre}topics
	WHERE {$boardsql} id != '{$_GET['id']}' AND status != '2' AND MATCH (topic) AGAINST ('$matchsql') > 0.5
	ORDER BY af DESC
	LIMIT {$config['viscacha_related_topics']['relatednum']}"
	);

	while ($line = $result->fetch()) {
		$rows[] = $line;
	}
}
if ((count($rows) > 0 && $config['viscacha_related_topics']['hide_empty'] == 1) || $config['viscacha_related_topics']['hide_empty'] != 1) {
	echo $tpl->parse("modules/{$pluginid}/related");
}