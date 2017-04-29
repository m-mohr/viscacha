$db->execute("INSERT INTO {$db->pre}textparser (`search`,`replace`) VALUES ('[teaser]','')");
$delobj = $scache->load('bbcode');
$delobj->delete();