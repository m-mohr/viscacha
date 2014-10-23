if (!$my->vlogin) {
	$request_uri = htmlspecialchars(getRefererURL());
	$tpl->globalvars(compact("request_uri"));
    echo $tpl->parse("modules/{$pluginid}/login");
}
