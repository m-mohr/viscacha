<?php
if (!$my->vlogin) {
    $request_uri = htmlspecialchars($_SERVER['REQUEST_URI']);
    $tpl->globalvars(compact("request_uri"));
	echo $tpl->parse($dir."login_guest");
} 
else {
	$result = $db->query("SELECT COUNT(*) FROM {$db->pre}pm AS p WHERE pm_to = '{$my->id}' AND status = '0'",__LINE__,__FILE__);
	$newpms = $db->fetch_array($result);
	$my->pms = $newpms[0];
	$request_uri = htmlspecialchars($_SERVER['REQUEST_URI']);
	$tpl->globalvars(compact("request_uri"));
	echo $tpl->parse($dir."login_member");
}
?>
