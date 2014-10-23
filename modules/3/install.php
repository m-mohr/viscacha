$db->query("INSERT INTO {$db->pre}textparser (`search`,`replace`,`type`,`desc`) VALUES ('[teaser]','','censor','')",__LINE__,__FILE__);
$delobj = $scache->load('bbcode');
$delobj->delete();