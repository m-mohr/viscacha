$db->execute("INSERT INTO {$db->pre}textparser (`search`,`replace`) VALUES ('[teaser]','')");
$scache->load('bbcode')->delete();