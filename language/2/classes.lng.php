<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }
$lang = array();
$lang['tne_badtype'] = 'Invalid imagetype.';
$lang['tne_giferror'] = 'Could not create gif-thumbnail.';
$lang['tne_imageerror'] = 'Could not create image thumbnail.';
$lang['tne_jpgerror'] = 'Could not create jpeg-thumbnail.';
$lang['tne_pngerror'] = 'Could not create png-thumbnail.';
$lang['upload_error_default'] = 'An unknown error occured while uploading.';
$lang['upload_error_fileexists'] = 'File already exists.';
$lang['upload_error_maxfilesize'] = 'Max. filesize reached. The max allowable filesize is {$mfs}.';
$lang['upload_error_maximagesize'] = 'Max. imagesize reached. The max allowable image dimensions are {$miw} x {$mih}.';
$lang['upload_error_noaccess'] = 'Access denied. The system could not copy the file.';
$lang['upload_error_noupload'] = 'No file has been uploaded';
$lang['upload_error_wrongfiletype'] = 'Only {$aft} files are allowed to be uploaded.';
?>