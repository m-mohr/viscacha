<?php
ob_start();
include($dir."data.php");
$data = ob_get_contents();
ob_end_clean();
$tpl->globalvars(compact("data","row"));
echo $tpl->parse($dir."message");
?>