$relatednum = $config['module_'.$pluginid]['relatednum'];

$ignorewords = $lang->get_words();
$ignorewords = array_map("strtolower", $ignorewords);

$word_seperator = "0-9\\.,;:!\\?\\-\\|\n\r\s\"'\\[\\]\\{\\}\\(\\)\\/\\\\";
$searchtopic = preg_split('/['.$word_seperator.']+?/', strtolower($info['topic']), -1, PREG_SPLIT_NO_EMPTY);

$sqltopic = array();
foreach ($searchtopic as $val) {
	if (strlen($val) > 3) {
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
	
	$result = $db->query("
	SELECT id, board, topic, MATCH (topic) AGAINST ('{$matchsql}') AS af
	FROM {$db->pre}topics
	WHERE {$boardsql} id != '{$_GET['id']}' AND status != '2' AND MATCH (topic) AGAINST ('$matchsql') > 0.5
	ORDER BY af DESC 
	LIMIT {$relatednum}"
	,__LINE__,__FILE__);
	
	if ($db->num_rows($result) > 0) {
		while ($line = $db->fetch_assoc($result)) {
			$line['topic'] = $gpc->prepare($line['topic']);
			$rows[] = $line;
		}
	}
}

echo $tpl->parse("modules/{$pluginid}/related");