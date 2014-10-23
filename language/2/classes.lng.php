<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }
$lang = array();
$lang['mailer_authenticate'] = 'SMTP Error: Could not authenticate.';
$lang['mailer_connect_host'] = 'SMTP Error: Could not connect to SMTP host.';
$lang['mailer_data_not_accepted'] = 'SMTP Error: Data not accepted.';
$lang['mailer_empty_message'] = 'Message body empty';
$lang['mailer_encoding'] = 'Unknown encoding: ';
$lang['mailer_execute'] = 'Could not execute: ';
$lang['mailer_file_access'] = 'Could not access file: ';
$lang['mailer_file_open'] = 'File Error: Could not open file: ';
$lang['mailer_from_failed'] = 'The following From address failed: ';
$lang['mailer_instantiate'] = 'Could not instantiate mail function.';
$lang['mailer_invalid_address'] = 'Invalid address';
$lang['mailer_mailer_not_supported'] = ' mailer is not supported.';
$lang['mailer_provide_address'] = 'You must provide at least one recipient email address.';
$lang['mailer_recipients_failed'] = 'SMTP Error: The following recipients failed: ';
$lang['mailer_signing'] = 'Signing Error: ';
$lang['mailer_smtp_connect_failed'] = 'SMTP Connect() failed.';
$lang['mailer_smtp_error'] = 'SMTP server error: ';
$lang['mailer_variable_set'] = 'Cannot set or reset variable: ';
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