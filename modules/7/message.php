$boxtext = $config['module_'.$pluginid]['text'];
$boxtitle = $config['module_'.$pluginid]['title'];
echo $tpl->parse("modules/{$pluginid}/message");