<?php
require('class.text2image.php');
if (!empty($_REQUEST['text'])) {
	$img = new text2image();
	if (!isset($_REQUEST['angle'])) {
		$_REQUEST['angle'] = 0;
	}
	$img->prepare($_REQUEST['text'], $_REQUEST['angle'], 10, '../fonts/trebuchet.ttf');
	if (isset($_REQUEST['enc'])) {
		$img->base64();
	}
	$img->build(4);
	$img->output();
}
?>
