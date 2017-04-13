if (!$my->vlogin) {
	$request_uri = viscacha_htmlspecialchars(getRequestURI());
	$tpl->assignVars(compact("request_uri"));
	echo $tpl->parse("modules/{$pluginid}/forum_login");
}