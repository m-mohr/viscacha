<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }
$lang = array();
$lang['admin_an_error_occured'] = 'Es ist ein Fehler aufgetreten:';
$lang['admin_an_unexpected_error_occured'] = 'Ein unerwarteter Fehler ist aufgetreten';
$lang['admin_back'] = 'zurück';
$lang['admin_benchmark_generation_time'] = 'Generiert in {$benchmark} Sekunden';
$lang['admin_benchmark_queries'] = 'Datenbankabfragen: {$queries}';
$lang['admin_choose_valid_location_option'] = 'Bitte wählen Sie eine gültige Option.';
$lang['admin_click_here_to_change_setting'] = 'Klicken Sie hier um die Einstellung zu ändern!';
$lang['admin_confirmation'] = 'Bestätigung:';
$lang['admin_continue'] = 'weiter';
$lang['admin_could_not_prepare_custom_settings'] = 'Konnte die benutzerdefinierten Einstellungen leider nicht aufbereiten';
$lang['admin_days_friday'] = 'Freitag';
$lang['admin_days_monday'] = 'Montag';
$lang['admin_days_name'] = 'Tage';
$lang['admin_days_saturday'] = 'Samstag';
$lang['admin_days_sunday'] = 'Sonntag';
$lang['admin_days_thursday'] = 'Donnerstag';
$lang['admin_days_tuesday'] = 'Dienstag';
$lang['admin_days_wednesday'] = 'Mittwoch';
$lang['admin_delete_settings'] = 'Delete Setting';
$lang['admin_error_message'] = 'Fehlermeldung:';
$lang['admin_form_login'] = 'Anmelden';
$lang['admin_form_upload'] = 'Hochladen';
$lang['admin_gll_addvotes'] = 'Umfragen können an Themen angehängt werden.';
$lang['admin_gll_admin'] = 'Der Benutzer ist Administrator und damit der Benutzer mit den meisten Rechten und vollem Zugang zum administrativen Verwaltungszentrum';
$lang['admin_gll_attachments'] = 'Dem Benutzer ist es erlaubt Datei-Anhänge in Beiträge einzufügen.';
$lang['admin_gll_downloadfiles'] = 'Der Benutzer kann Anhänge herunterladen und ansehen.';
$lang['admin_gll_edit'] = 'Der Benutzer kann eigene Beiträge ändern und löschen.';
$lang['admin_gll_forum'] = 'Der Benutzer darf die Foren und Themenlisten ansehen.';
$lang['admin_gll_gmod'] = 'Der Benutzer ist in allen Foren Moderator und kann alle Optionen auf Themen anwenden.';
$lang['admin_gll_guest'] = 'Die Benutzer in dieser Gruppe sind (nicht registrierte) Gäste.';
$lang['admin_gll_members'] = 'Der Benutzer kann die Mitgliederliste einsehen.';
$lang['admin_gll_pm'] = 'Der Benutzer kann das private Nachrichtensystem (PN) benutzen. Er kann Nachrichten schicken, empfengen und eigene Nachrichten archivieren.';
$lang['admin_gll_postreplies'] = 'Auf Themen kann der Benutzer Antworten schreiben.';
$lang['admin_gll_posttopics'] = 'Neue Themen dürfen vom Benutzer gestartet werden.';
$lang['admin_gll_profile'] = 'Der Benutzer kann die Benutzerprofile ansehen (und damit die Daten benutzen).';
$lang['admin_gll_search'] = 'Der Benutzer kann die Suche benutzen und Suchergebnisse einsehen.';
$lang['admin_gll_team'] = 'Einsicht in die Teamliste mit Administratoren und (globalen) Moderatoren.';
$lang['admin_gll_useabout'] = 'Der Benutzer kann sich eine eigene persönliche Seite im Profil erstellen.';
$lang['admin_gll_usepix'] = 'Der Benutzer darf ein eigenes Benutzerbild (häufig Avatar genannt) hochladen oder per URL übertragen.';
$lang['admin_gll_usesignature'] = 'Der Benutzer kann sich eine eigene Signatur erstellen.';
$lang['admin_gll_voting'] = 'Der Benutzer kann an Umfragen teilnehmen und seine Stimme abgeben.';
$lang['admin_gll_wwo'] = 'Der Benzutzer kann die Wer-ist-online-Liste mit den Aufenthaltsorten der Benutzer ansehen.';
$lang['admin_gls_addvotes'] = 'Kann Umfrage starten';
$lang['admin_gls_admin'] = 'Ist Administrator';
$lang['admin_gls_attachments'] = 'Kann Anhänge beifügen';
$lang['admin_gls_downloadfiles'] = 'Kann Anhänge runterladen';
$lang['admin_gls_edit'] = 'Kann eigene Beiträge ändern';
$lang['admin_gls_forum'] = 'Kann Foren ansehen';
$lang['admin_gls_gmod'] = 'Ist globaler Moderator';
$lang['admin_gls_guest'] = 'Ist Gast';
$lang['admin_gls_members'] = 'Kann Mitgliederliste ansehen';
$lang['admin_gls_pm'] = 'Kann das PN-System nutzen';
$lang['admin_gls_postreplies'] = 'Kann Antworten schreiben';
$lang['admin_gls_posttopics'] = 'Kann ein neues Thema starten';
$lang['admin_gls_profile'] = 'Kann Profile ansehen';
$lang['admin_gls_search'] = 'Kann die Suche benutzen';
$lang['admin_gls_team'] = 'Kann die Teamliste ansehen';
$lang['admin_gls_useabout'] = 'Kann eigene persönliche Seite erstellen';
$lang['admin_gls_usepic'] = 'Kann eigenen Avatar benutzen';
$lang['admin_gls_usesignature'] = 'Kann eigene Signatur benutzen';
$lang['admin_gls_voting'] = 'Kann abstimmen';
$lang['admin_gls_wwo'] = 'Kann Wer-ist-online ansehen';
$lang['admin_gzip_not_loaded'] = 'GZIP-Erweiterung nicht geladen';
$lang['admin_hours_name'] = 'Stunden';
$lang['admin_incorrect_username_or_password_entered'] = 'Sie haben einen falschen Benutzernamen oder ein falsches Passwort eingegeben!';
$lang['admin_lang_vars_help'] = 'Das Sprachsystem unterstützt die Benutzung von Variablen in Phrasen und Texten. Alle Variablen müssen mit { und } umgeben sein. Die aus dem PHP-Programm vorhandenen Variablen können wie folgt genutzt werden:<br />Normale Variablen vom Typ <code>$var</code> werden demnach <code>{&#36;var}</code>,<br />der Zugriff auf ein Array <code>$var[\'key\']</code> wird zu <code>{&#64;var->key}</code> und<br />der Zugriff auf ein Objekt-Attribut <code>$var->key</code> wird zu <code>{&#37;var->key}</code>.';
$lang['admin_login_password'] = 'Passwort:';
$lang['admin_login_title'] = 'Anmelden';
$lang['admin_login_username'] = 'Benutzername:';
$lang['admin_minutes_name'] = 'Minuten';
$lang['admin_months_april'] = 'April';
$lang['admin_months_august'] = 'August';
$lang['admin_months_december'] = 'Dezember';
$lang['admin_months_february'] = 'Februar';
$lang['admin_months_january'] = 'Januar';
$lang['admin_months_july'] = 'Juli';
$lang['admin_months_june'] = 'Juni';
$lang['admin_months_march'] = 'März';
$lang['admin_months_may'] = 'Mai';
$lang['admin_months_name'] = 'Monate';
$lang['admin_months_november'] = 'November';
$lang['admin_months_october'] = 'Oktober';
$lang['admin_months_september'] = 'September';
$lang['admin_no'] = 'Nein';
$lang['admin_not_allowed_to_view_this_page'] = 'Sie haben nicht die Erlaubnis diese Seite anzusehen.';
$lang['admin_pages'] = 'Seiten ({$anz}):';
$lang['admin_pb_type1_name'] = 'Pakete';
$lang['admin_pb_type1_name2'] = 'Paket';
$lang['admin_pb_type2_name'] = 'Designs';
$lang['admin_pb_type2_name2'] = 'Design';
$lang['admin_pb_type3_name'] = 'Smiley-Packs';
$lang['admin_pb_type3_name2'] = 'Smiley-Pack';
$lang['admin_pb_type4_name'] = 'Sprachpakete';
$lang['admin_pb_type4_name2'] = 'Sprachpaket';
$lang['admin_pb_type5_name'] = 'BB-Codes';
$lang['admin_pb_type5_name2'] = 'BB-Code';
$lang['admin_requested_page_doesnot_exist'] = 'Die von Ihnen angeforderte Seite existiert leider nicht.';
$lang['admin_seconds_name'] = 'Sekunde';
$lang['admin_server_unknown'] = 'Unbekannt';
$lang['admin_settings_successfully_saved'] = 'Einstellungen wurden erfolgreich gespeichert.';
$lang['admin_successfully_logged_in'] = 'Sie wurden erfolgreich angemeldet.';
$lang['admin_successfully_logged_off'] = 'Sie wurden erfolgreich abgemeldet.';
$lang['admin_yes'] = 'Ja';
$lang['gmt'] = 'GMT';
?>