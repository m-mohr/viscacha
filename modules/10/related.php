<?php
global $info;
global $gpc;
$ignorewords = $lang->get_words();
	
$searchdata = array();
	
$word_seperator = "0-9\\.,;:!\\?\\-\\|\n\r\s\"'\\[\\]\\{\\}\\(\\)\\/\\\\";
$searchtopic = preg_split('/['.$word_seperator.']+?/', strtolower($info['topic']), -1, PREG_SPLIT_NO_EMPTY);
$ignorewords = array_map("strtolower", $ignorewords);

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
$rows = array();
if (count($sqltopic) > 0) {
	$matchsql = implode(' ', $sqltopic);
	
	$slog->GlobalPermissions();
	
	$result = $db->query("
	SELECT id, board, topic, MATCH (topic) AGAINST ('$matchsql') AS af
	FROM {$db->pre}topics
	WHERE ".$slog->sqlinboards('board', 1)." id != '{$_GET['id']}' AND status != '2' AND MATCH (topic) AGAINST ('$matchsql') > 0.1
	ORDER BY af DESC LIMIT ".$ini['params']['num'],__LINE__,__FILE__);
	
	if ($db->num_rows($result) > 0) {
		while ($line = $db->fetch_assoc($result)) {
			$line['topic'] = $gpc->prepare($line['topic']);
			$rows[] = $line;
		}
	}
}
$tpl->globalvars(compact("rows"));
echo $tpl->parse($dir."related");
?>
