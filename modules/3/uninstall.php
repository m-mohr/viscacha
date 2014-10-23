$db->query("DELETE FROM {$db->pre}textparser WHERE `search` = '[teaser]' AND `type` = 'censor' LIMIT 1");
$delobj = $scache->load('bbcode');
$delobj->delete();