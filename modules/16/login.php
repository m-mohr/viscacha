if (!$my->vlogin) {
	$request_uri = '';
	if (check_hp($_SERVER['HTTP_REFERER'])) {
		$url = parse_url($_SERVER['HTTP_REFERER']);
		if (strpos($config['furl'], $url['host']) !== FALSE) {
			$request_uri = htmlspecialchars($_SERVER['HTTP_REFERER']);
		}
	}
	$tpl->globalvars(compact("request_uri"));
    echo $tpl->parse("modules/{$pluginid}/login");
}
