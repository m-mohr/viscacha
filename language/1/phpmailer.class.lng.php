<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "phpmailer.class.lng.php") die('Error: Hacking Attempt');
$lang = array();
$lang['mailer_authenticate'] = 'SMTP Fehler: Authentifizierung fehlgeschlagen.';
$lang['mailer_connect_host'] = 'SMTP Fehler: Konnte keine Verbindung zum SMTP-Host herstellen.';
$lang['mailer_data_not_accepted'] = 'SMTP Fehler: Daten werden nicht akzeptiert.';
$lang['mailer_encoding'] = 'Unbekanntes Encoding-Format:';
$lang['mailer_execute'] = 'Konnte folgenden Befehl nicht ausf&uuml;hren:';
$lang['mailer_file_access'] = 'Zugriff auf folgende Datei fehlgeschlagen:';
$lang['mailer_file_open'] = 'Datei Fehler: Konnte Datei nicht &ouml;ffnen:';
$lang['mailer_from_failed'] = 'Die folgende Absenderadresse ist nicht korrekt:';
$lang['mailer_instantiate'] = 'Mail Funktion konnte nicht initialisiert werden.';
$lang['mailer_mailer_not_supported'] = '-Mailer wird nicht unterst&uuml;tzt.';
$lang['mailer_provide_address'] = 'Bitte geben Sie mindestens eine Empf&auml;nger E-Mail-Adresse an.';
$lang['mailer_recipients_failed'] = 'SMTP Fehler: Die folgenden Empf&auml;nger sind nicht korrekt:';
?>