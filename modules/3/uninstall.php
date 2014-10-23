$db->query("DELETE FROM {$db->pre}textparser WHERE `search` = '[teaser]' AND `type` = 'censor' LIMIT 1",__LINE__,__FILE__);
$delobj = $scache->load('bbcode');
$delobj->delete();