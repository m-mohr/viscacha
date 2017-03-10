$db->query("DELETE FROM {$db->pre}textparser WHERE `search` = '[teaser]' LIMIT 1");
$delobj = $scache->load('bbcode');
$delobj->delete();