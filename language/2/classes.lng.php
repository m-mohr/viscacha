<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }
$lang = array();
$lang['mailer_authenticate'] = 'SMTP error: Authentification failed, check username and password.';
$lang['mailer_connect_host'] = 'SMTP error: Could not connect to SMTP host, please try again later.';
$lang['mailer_data_not_accepted'] = 'SMTP error: Data not accepted.';
$lang['mailer_encoding'] = 'Unknown encoding:';
$lang['mailer_execute'] = 'Could not execute this command:';
$lang['mailer_file_access'] = 'Access on this file failed:';
$lang['mailer_file_open'] = 'File error: Could not open file:';
$lang['mailer_from_failed'] = 'Sender address is not correct:';
$lang['mailer_instantiate'] = 'Could not initialise mail function.';
$lang['mailer_mailer_not_supported'] = '-mailer is not supported.';
$lang['mailer_provide_address'] = 'Please enter at least one e-mail address.';
$lang['mailer_recipients_failed'] = 'SMTP error: These addresses are not correct:';
$lang['tne_badtype'] = 'Invalid imagetype.';
$lang['tne_gd1error'] = 'Could not create GD1-thumbnail.';
$lang['tne_giferror'] = 'Could not create gif-thumbnail.';
$lang['tne_imageerror'] = 'Could not create image thumbnail.';
$lang['tne_jpgerror'] = 'Could not create jpeg-thumbnail.';
$lang['tne_pngerror'] = 'Could not create png-thumbnail.';
$lang['tne_truecolorerror'] = 'Could not create true color thumbnail.';
$lang['upload_error_default'] = 'An unknown error occured while uploading.';
$lang['upload_error_fileexists'] = 'File already exists.';
$lang['upload_error_maxfilesize'] = 'Max. filesize reached. The max allowable filesize is {$mfs}.';
$lang['upload_error_maximagesize'] = 'Max. imagesize reached. The max allowable image dimensions are {$miw} x {$mih}.';
$lang['upload_error_noaccess'] = 'Access denied. The system could not copy the file.';
$lang['upload_error_noupload'] = 'No file has been uploaded';
$lang['upload_error_wrongfiletype'] = 'Only {$aft} files are allowed to be uploaded.';
?>
