<?php
global $memberdata, $gpc, $slog, $lang, $db, $tpl;
if (!isset($memberdata) || !is_array($memberdata)) {
    $memberdata = cache_memberdata();
}

$wwo = array();
$wwo['i']=0; 
$wwo['r']=0; 
$wwo['g']=0; 
$wwo['b']=0;
$wwo['list'] = array();

$result = $db->query('SELECT mid, remoteaddr FROM '.$db->pre.'session',__LINE__,__FILE__);
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
	elseif (BotDetection($slog->bots, $row['remoteaddr']) != false) {
		$wwo['b']++;
	}
	else {
		$wwo['g']++;
	}
}

if ($wwo['r'] > 0) {
	$wwo['list'][$wwo['r']-1]['sep'] = '';
}

$tpl->globalvars(compact("wwo"));
$lang->assign('wwo', $wwo);
echo $tpl->parse($dir."wwo");
?>
