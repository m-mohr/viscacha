$memberdata_obj = $scache->load('memberdata');
$memberdata = $memberdata_obj->get();

$wwo = array();
$wwo['i']=0; 
$wwo['r']=0; 
$wwo['g']=0; 
$wwo['b']=0;
$wwo['list'] = array();

$result = $db->query('SELECT mid, remoteaddr, is_bot FROM '.$db->pre.'session',__LINE__,__FILE__);
$count = $db->num_rows($result);
$sep = $lang->phrase('listspacer');
while ($row = $db->fetch_assoc($result)) {
	$wwo['i']++;
	$row['remoteaddr'] = $gpc->prepare($row['remoteaddr']);
	if ($row['mid'] > 0 && isset($memberdata[$row['mid']])) {
		$wwo['r']++;
		$row['name'] = $memberdata[$row['mid']];
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
