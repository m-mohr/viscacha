$db->query("INSERT INTO {$db->pre}settings_groups (title, name, description) VALUES ('{$ini['info']['title']}', 'module_{$pluginid}', 'Configuration for plugin {$pluginid}')", __LINE__, __FILE__);
$group = $db->insert_id();

$db->query("
INSERT INTO {$db->pre}settings (
	name, title, description, type, optionscode, value, sgroup
) 
VALUES (
	'text', 
	'Text to show', 
	'You can enter the message here. You can use HTML.', 
	'textarea', 
	'', 
	'', 
	'{$group}')
", __LINE__, __FILE__);
$db->query("
INSERT INTO {$db->pre}settings (
	name, title, description, type, optionscode, value, sgroup
) 
VALUES (
	'title', 
	'Title to show', 
	'You can enter the title here. You can use HTML.', 
	'text', 
	'', 
	'', 
	'{$group}')
", __LINE__, __FILE__);

$c->getdata();
$c->updateconfig(array("module_{$pluginid}", "title"), str, 'Wichtige Nachricht');
$c->updateconfig(array("module_{$pluginid}", "text"), str, 'Willkommen im Forum!');
$c->savedata();