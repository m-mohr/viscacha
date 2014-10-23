$wwo = array();
$wwo['i']=0; 
$wwo['r']=0; 
$wwo['g']=0; 
$wwo['b']=0;
$wwo['list'] = array();

$result = $db->query("
	SELECT s.mid, s.is_bot, u.name
	FROM {$db->pre}session AS s 
		LEFT JOIN {$db->pre}user AS u ON s.mid = u.id
	ORDER BY u.name
",__LINE__,__FILE__);
$count = $db->num_rows($result);
$sep = $lang->phrase('listspacer');
while ($row = $db->fetch_assoc($result)) {
	$wwo['i']++;
	if ($row['mid'] > 0) {
		$wwo['r']++;
		$row['sep'] = $sep;
		$wwo['list'][] = $row;
	}
	elseif ($row['is_bot'] > 0) {
		$wwo['b']++;
	}
	else {
		$wwo['g']++;
	}
}

if ($wwo['r'] > 0) {
	$wwo['list'][$wwo['r']-1]['sep'] = '';
}

echo $tpl->parse("modules/{$pluginid}/wwo");
