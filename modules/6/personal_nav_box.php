$request_uri = getRequestURI();
if (!$my->vlogin) {
	$request_uri = viscacha_htmlspecialchars($request_uri);
	$tpl->assignVars(compact("request_uri"));
	echo $tpl->parse("modules/{$pluginid}/nav_guest");
}
else {
	$result = $db->query("SELECT COUNT(*) FROM {$db->pre}pm AS p WHERE pm_to = '{$my->id}' AND status = '0'");
	$newpms = $db->fetch_num($result);
	$my->pms = $newpms[0];
	$request_uri = rawurlencode($request_uri);
	$tpl->assignVars(compact("request_uri", "my"));
	echo $tpl->parse("modules/{$pluginid}/nav_member");
}