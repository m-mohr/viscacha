$db->query("INSERT INTO {$db->pre}textparser (`search`,`replace`,`type`,`desc`) VALUES ('[teaser]','','censor','')");
$delobj = $scache->load('bbcode');
$delobj->delete();