if($my->vlogin && $my->p['postreplies'] == 1 && $info['status'] == 0 && $last['readonly'] == '0') {
	echo $tpl->parse("modules/{$pluginid}/quick-reply");
}