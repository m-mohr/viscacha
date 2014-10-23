<?php
if ($_GET['action'] == 'word') {	
   	if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'],'application/vnd-ms.word')) {
   		viscacha_header('Content-disposition: inline; filename='.$_GET['id'].'-'.$_GET['page'].'.doc');
   		viscacha_header('Content-Type: application/vnd-ms.word');
   	}
   	elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'],'application/msword')) {
   		viscacha_header('Content-disposition: inline; filename='.$_GET['id'].'-'.$_GET['page'].'.doc');
   		viscacha_header('Content-Type: application/msword');
   	}
	elseif (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')) {
   		viscacha_header('Content-disposition: attachment; filename='.$_GET['id'].'-'.$_GET['page'].'.doc');
		viscacha_header('Content-Type: application/force-download');
	}
	else {
   		viscacha_header('Content-disposition: attachment; filename='.$_GET['id'].'-'.$_GET['page'].'.doc');
		viscacha_header('Content-Type: application/octet-stream');
	}
}
?>
