if($my->vlogin && $my->p['postreplies'] == 1 && $info['status'] == 0 && $last['readonly'] == '0') {
	$inner['smileys'] = $bbcode->getsmileyhtml($config['smileysperrow']);
	$inner['bbhtml'] = $bbcode->getbbhtml();
	
	echo $tpl->parse("modules/{$pluginid}/quick-reply");
}