$db->query("INSERT INTO {$db->pre}settings_groups (title, name, description) VALUES ('{$ini['info']['title']}', 'module_{$pluginid}', 'Configuration for plugin {$pluginid}')", __LINE__, __FILE__);
$group = $db->insert_id();

$db->query("
INSERT INTO {$db->pre}settings (
	name, title, description, type, optionscode, value, sgroup
) 
VALUES (
	'topicnum', 
	'Topics to show', 
	'Number of new topics which are supposed to be listed maximally.', 
	'text', 
	'', 
	'10', 
	'{$group}')
", __LINE__, __FILE__);

$c->getdata();
$c->updateconfig(array("module_{$pluginid}", "topicnum"), int, 10);
$c->savedata();