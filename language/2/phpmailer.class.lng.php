<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "phpmailer.class.lng.php") die('Error: Hacking Attempt');
$lang = array(
'mailer_authenticate' => 'SMTP error: Authentification failed, check username and password.',
'mailer_connect_host' => 'SMTP error: Could not connect to SMTP host, please try again later.',
'mailer_data_not_accepted' => 'SMTP error: Data not accepted.',
'mailer_encoding' => 'Unknown encoding:',
'mailer_execute' => 'Could not execute this command:',
'mailer_file_access' => 'Acces on this file failed:',
'mailer_file_open' => 'File error: Could not open file:',
'mailer_from_failed' => 'Sender address is not correct:',
'mailer_instantiate' => 'Could not initialise mail function.',
'mailer_mailer_not_supported' => '-mailer is not supported.',
'mailer_provide_address' => 'Please enter at least one e-mail address.',
'mailer_recipients_failed' => 'SMTP error: These addresses are not correct:'
);
?>
