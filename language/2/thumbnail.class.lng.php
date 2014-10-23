<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "thumbnail.class.lng.php") die('Error: Hacking Attempt');
$lang = array();
$lang['tne_badtype'] = 'Invalid imagetype.';
$lang['tne_gd1error'] = 'Could not create GD1-thumbnail.';
$lang['tne_giferror'] = 'Could not create gif-thumbnail.';
$lang['tne_imageerror'] = 'Could not create image thumbnail.';
$lang['tne_jpgerror'] = 'Could not create jpeg-thumbnail.';
$lang['tne_pngerror'] = 'Could not create png-thumbnail.';
$lang['tne_truecolorerror'] = 'Could not create true color thumbnail.';
?>