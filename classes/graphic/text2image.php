<?php
require('class.text2image.php');
if (!empty($_REQUEST['text'])) {
	$img = new text2image();
	if (!isset($_REQUEST['angle'])) {
		$_REQUEST['angle'] = 0;
	}
	if (!isset($_REQUEST['size'])) {
		$_REQUEST['size'] = 10;
	}
	if (!empty($_REQUEST['file']) && @file_exists('../fonts/'.$_REQUEST['file'].'.ttf')) {
		$file = $_REQUEST['file'];
	}
	else {
		$file = 'trebuchet';
	}
	$img->prepare($_REQUEST['text'], $_REQUEST['angle'], $_REQUEST['size'], '../fonts/'.$file.'.ttf');
	if (isset($_REQUEST['enc'])) {
		$img->base64();
	}
	$img->build(4);
	$img->output();
}
?>
