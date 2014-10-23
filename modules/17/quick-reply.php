if ($my->vlogin) { 
	$inner['smileys'] = $bbcode->getsmileyhtml($config['smileysperrow']);
	$inner['bbhtml'] = $bbcode->getbbhtml();
	
	echo $tpl->parse("modules/{$pluginid}/quick-reply");
}