$db->execute("DELETE FROM {$db->pre}textparser WHERE `search` = '[teaser]' LIMIT 1");
$scache->load('bbcode')->delete();