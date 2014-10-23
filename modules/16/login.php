if (!$my->vlogin) {
	$request_uri = htmlspecialchars($_SERVER['REQUEST_URI']);
    echo $tpl->parse("modules/{$pluginid}/login");
}
