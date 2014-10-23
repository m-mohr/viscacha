global $wwo_module;
$wwo = $wwo_module->get();
$tpl->globalvars(compact("wwo"));
$lang->assign('wwo', $wwo);
echo $tpl->parse("modules/{$pluginid}/wwo_nav_box");