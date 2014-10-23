$db->query("INSERT INTO {$db->pre}settings_groups (title, name, description) VALUES ('{$ini['info']['title']}', 'module_{$pluginid}', 'Configuration for plugin {$pluginid}')", __LINE__, __FILE__);
$group = $db->insert_id();

$db->query("
INSERT INTO {$db->pre}settings (
	name, title, description, type, optionscode, value, sgroup
) 
VALUES (
	'repliesnum', 
	'Number of replies', 
	'Maximum number of newest replies shown after the form.', 
	'text', 
	'', 
	'5', 
	'{$group}')
", __LINE__, __FILE__);

$c->getdata();
$c->updateconfig(array("module_{$pluginid}", "repliesnum"), int, 5);
$c->savedata();