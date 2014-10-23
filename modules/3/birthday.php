<?php
global $gpc;

$stime = times();
$today = $stime - gmmktime (0, 0, 0, gmdate('m',$stime), gmdate('d',$stime), gmdate('Y',$stime), date('I',$stime)) - 60;

$scache = new scache('birthday_module');
if ($scache->existsdata($today) == TRUE) {
    $data = $scache->importdata();
}
else {
    $result = $db->query("SELECT id, name, birthday FROM {$db->pre}user WHERE RIGHT( birthday, 5 ) = '".gmdate('m-d',times())."' ORDER BY name",__LINE__,__FILE__);
    $data = array();
    while ($e = $db->fetch_assoc($result)) {
    	$e['name'] = $gpc->prepare($e['name']);
    	$e['birthday'] = explode('-',$e['birthday']);
    	$e['age'] = getAge($e['birthday']);
        $data[] = $e;
    }
    $scache->exportdata($data);
}

if (count($data) > 0) {
	$tpl->globalvars(compact("data"));
	echo $tpl->parse($dir."birthday_box");
}
?>
