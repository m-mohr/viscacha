$db->query("INSERT INTO {$db->pre}settings_groups (title, name, description) VALUES ('{$ini['info']['title']}', 'module_{$pluginid}', 'Configuration for plugin {$pluginid}')", __LINE__, __FILE__);
$group = $db->insert_id();

$db->query("
INSERT INTO {$db->pre}settings (
	name, title, description, type, optionscode, value, sgroup
) 
VALUES (
	'feed', 
	'ID of Newsfeed', 
	'ID of Newsfeed to show. Look up ID here: <a href=\"admin.php?action=cms&job=feed\" target=\"_blank\">Newsfeed Syndication</a>', 
	'text', 
	'', 
	'1', 
	'{$group}')
", __LINE__, __FILE__);
$db->query("
INSERT INTO {$db->pre}settings (
	name, title, description, type, optionscode, value, sgroup
) 
VALUES (
	'title', 
	'Title for Newsfeed', 
	'', 
	'text', 
	'', 
	'Ticker', 
	'{$group}')
", __LINE__, __FILE__);
	
$c->getdata();
$c->updateconfig(array("module_{$pluginid}", "feed"), int, 1);
$c->updateconfig(array("module_{$pluginid}", "title"), str, 'Ticker');
$c->savedata();