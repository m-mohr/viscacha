<?php
global $info, $memberdata, $gpc;
if (!isset($memberdata) || !is_array($memberdata)) {
    $memberdata = cache_memberdata();
}
$tpl->globalvars(compact("row","info","ini"));
$lang->assign("num", $ini['params']['num']);
$lang->assign("tid", $info['id']);
echo $tpl->parse($dir."last");
$result = $db->query('SELECT board, dosmileys, dowords, id, topic, comment, date, name, email FROM '.$db->pre.'replies WHERE topic_id = "'.$info['id'].'" ORDER BY date DESC LIMIT '.$ini['params']['num'],__LINE__,__FILE__);
while ($row = $gpc->prepare($db->fetch_object($result))) {
    
	if (empty($row->email) && isset($memberdata[$row->name])) {
    	$row->name = $memberdata[$row->name];
	}
	$bbcode->setSmileys($row->dosmileys);
	if ($config['wordstatus'] == 0) {
		$row->dowords = 0;
	}
	$bbcode->setReplace($row->dowords);
	if ($info['status'] == 2) {
		$row->comment = $bbcode->ReplaceTextOnce($row->comment, 'moved');
	}
	$row->comment = $bbcode->parse($row->comment);
	
	$row->date = str_date($lang->phrase('dformat1'), times($row->date));
	$tpl->globalvars(compact("row"));
	echo $tpl->parse($dir."last_bit");
}
?>
