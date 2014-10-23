$db->query("INSERT INTO {$db->pre}settings_groups (title, name, description) VALUES ('{$ini['info']['title']}', 'module_{$pluginid}', 'Configuration for plugin {$pluginid}')", __LINE__, __FILE__);
$group = $db->insert_id();

$db->query("
INSERT INTO {$db->pre}settings (
	name, title, description, type, optionscode, value, sgroup
) 
VALUES (
	'items', 
	'Number of news', 
	'Number of news shown on the frontpage', 
	'text', 
	'', 
	'5', 
	'{$group}')
", __LINE__, __FILE__);
$db->query("
INSERT INTO {$db->pre}settings (
	name, title, description, type, optionscode, value, sgroup
) 
VALUES (
	'teaserlength', 
	'Shortening news', 
	'Determine onto how many signs the preview of the articles is shortened', 
	'text', 
	'', 
	'300', 
	'{$group}')
", __LINE__, __FILE__);

$c->getdata();
$c->updateconfig(array("module_{$pluginid}", "items"), int, 5);
$c->updateconfig(array("module_{$pluginid}", "teaserlength"), int, 300);
$c->savedata();