$request_uri = '';
if (check_hp($_SERVER['HTTP_REFERER'])) {
	$url = parse_url($_SERVER['HTTP_REFERER']);
	if (strpos($config['furl'], $url['host']) !== FALSE) {
		$request_uri = htmlspecialchars($_SERVER['HTTP_REFERER']);
	}
}

if (!$my->vlogin) {
	$tpl->globalvars(compact("request_uri"));
	echo $tpl->parse("modules/{$pluginid}/login_guest");
} 
else {
	$result = $db->query("SELECT COUNT(*) FROM {$db->pre}pm AS p WHERE pm_to = '{$my->id}' AND status = '0'",__LINE__,__FILE__);
	$newpms = $db->fetch_num($result);
	$my->pms = $newpms[0];
	$tpl->globalvars(compact("request_uri", "my"));
	echo $tpl->parse("modules/{$pluginid}/login_member");
}
