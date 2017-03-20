if (!$my->vlogin) {
	$request_uri = viscacha_htmlspecialchars(getRequestURI());
	$tpl->globalvars(compact("request_uri"));
	echo $tpl->parse("modules/{$pluginid}/forum_login");
}