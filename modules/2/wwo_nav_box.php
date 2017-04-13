global $wwo_module, $lang, $tpl;
$wwo = $wwo_module->get();
$tpl->assignVars(compact("wwo"));
$lang->assign('wwo', $wwo);
echo $tpl->parse("modules/{$pluginid}/wwo_nav_box");