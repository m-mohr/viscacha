<?php
$scache = new scache('grabrss');
if ($scache->existsdata() == TRUE) {
    $grabrss_cache = $scache->importdata();
}
else {
    $cresult = $db->query("SELECT id, file, title, entries, max_age FROM {$db->pre}grab",__LINE__,__FILE__);
    $grabrss_cache = array();
    while ($row = $db->fetch_assoc($cresult)) {
		$row['title'] = htmlentities($row['title'], ENT_QUOTES);
        $grabrss_cache[$row['id']] = $row;
    }
    $scache->exportdata($grabrss_cache);
}
?>
