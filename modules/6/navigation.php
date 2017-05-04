$tpl->assignVars(compact("my"));
echo $tpl->parse("modules/{$pluginid}/nav_forum");
echo $tpl->parse("modules/{$pluginid}/nav_pm");
echo $tpl->parse("modules/{$pluginid}/nav_editprofile");