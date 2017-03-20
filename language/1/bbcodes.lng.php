<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }
$lang = array();
$lang['bb_edit_author'] = 'Nachträgliche Anmerkung des Autors:';
$lang['bb_edit_mod'] = 'Nachträgliche Anmerkung von';
$lang['bb_hidden_content'] = 'Versteckter Inhalt:';
$lang['bb_offtopic'] = 'Off-Topic:';
$lang['bb_quote'] = 'Zitat:';
$lang['bb_quote_by'] = 'Zitat von';
$lang['bb_sourcecode'] = 'Quelltext:';
$lang['bbcodes_align'] = 'Ausrichtung';
$lang['bbcodes_align_center'] = 'Zentriert';
$lang['bbcodes_align_desc'] = 'Der [align] Tag ermöglicht die Ausirchtung von Texten/Absätzen. Als Parameter für die Ausrichtung können folgende Arten benutzt werden: left (Linksbündig, standard), center (Zentriert), right (Rechtsbündig), justify (Blocksatz).';
$lang['bbcodes_align_justify'] = 'Blocksatz';
$lang['bbcodes_align_left'] = 'Linksbündig';
$lang['bbcodes_align_right'] = 'Rechtsbündig';
$lang['bbcodes_align_title'] = 'Schriftausrichtung auswählen';
$lang['bbcodes_bold'] = 'Fettschrift';
$lang['bbcodes_bold_desc'] = 'Mit den [b] Tag können Sie Texte fett darstellen.';
$lang['bbcodes_code'] = 'Quelltext';
$lang['bbcodes_code_desc'] = 'Mit dem [code] Tag kann Quelltext gekennzeichnet werden. Einzeiliger Code fließt im Text mit, ist jedoch besonders gekennzeichnet. Mehrzeiliger Code wird so angezeigt wie eingegeben, jedoch werden Einrückungen beibehalten. Der Code wird mit einer Monospace Schriftart angezeigt.';
$lang['bbcodes_color'] = 'Farbe';
$lang['bbcodes_color_desc'] = 'Mit dem [color] Tag kann Text eingefärbt werden. Die Farbe muss als Hexadezimal Wert eingegeben werden und kann entweder 3 oder 6 Zeichen lang sein. Das in HTML übliche vorangestellte # ist optional.';
$lang['bbcodes_color_title'] = 'Farbe auswählen';
$lang['bbcodes_create_table'] = 'Neue Tabelle erstellen';
$lang['bbcodes_edit'] = 'Nachträgliche Anmerkung / Kennzeichnung der editierten Textstellen';
$lang['bbcodes_edit_desc'] = 'Der [edit] Tag kennzeichnet bearbeitete Textstellen oder später ergänzte Textpassagen. Falls kein Parameter angegeben wird, wird die Passage dem Autor zugeschrieben. Der optionale Parameter ermöglicht die Angabe eines Namens. Dieser Name wird dann als Editor angezeigt.';
$lang['bbcodes_email'] = 'E-Mail-Adresse';
$lang['bbcodes_email_desc'] = 'Mit dem [email] Tag können E-Mails sicher angezeigt werden. Die E-Mail-Adresse wird nicht normal per HTML verlinkt, sondern es wird ein unverlinktes Bild angezeigt, dass die E-Mail-Adresse anzeigt. Dies erschwert zwar die Benutzung der E-Mail-Adresse, schützt jedoch effektiv vor Spam-Bots.';
$lang['bbcodes_example_text'] = 'Text';
$lang['bbcodes_example_text2'] = 'Text 2';
$lang['bbcodes_expand'] = 'Aufklappen';
$lang['bbcodes_header'] = 'Überschrift';
$lang['bbcodes_header_desc'] = 'Der [h] Tag ermöglicht die Strukturierung eines Textes mittels Überschriften. Es gibt 3 Varianten: large (Überschrift 1. Ordnung; sehr groß), middle (Überschrift 2. Ordnung; groß) oder small (Überschrift 3. Ordnung; weniger groß).';
$lang['bbcodes_header_h1'] = 'Überschrift 1';
$lang['bbcodes_header_h2'] = 'Überschrift 2';
$lang['bbcodes_header_h3'] = 'Überschrift 3';
$lang['bbcodes_header_title'] = 'Überschriftengröße auswählen';
$lang['bbcodes_help_example'] = 'Beispiel:';
$lang['bbcodes_help_output'] = 'Ausgabe:';
$lang['bbcodes_help_syntax'] = 'Syntax:';
$lang['bbcodes_hide'] = 'Versteckter Inhalt';
$lang['bbcodes_hide_desc'] = 'Mit dem [hide] Tag können Inhalte des Beitrags versteckt werden. Der Inhalt des Tags wird nur dem Autor, den berechtigten Moderatoren, den globalen Moderatoren und dem Administrator angezeigt.';
$lang['bbcodes_hr'] = 'Horizontale Linie';
$lang['bbcodes_hr_desc'] = 'Der [hr] Tag wird durch eine horizontale Linie ersetzt. Der Tag benötigt kein schließendes Element.';
$lang['bbcodes_img'] = 'Bild';
$lang['bbcodes_img_desc'] = 'Mit dem [img] Tag können Bilder vom Typ jpg, gif und png eingebunden werden. Die Bilder müssen eine korrekte Dateiendung besitzen, ansonsten werden die Bilder lediglich als Link dargestellt. Zu große Bilder werden evtl. verkleinert und können mit einem Klick auf das verkleinerte Bild vergrößert werden.';
$lang['bbcodes_italic'] = 'Kursiv';
$lang['bbcodes_italic_desc'] = 'Mit den [i] Tag können Sie Texte kursiv darstellen.';
$lang['bbcodes_list'] = 'Ungeordnete Liste';
$lang['bbcodes_list_desc'] = 'Mit dem [list] Tag können Sie geordnete und ungeordnete Listen erstellen. Um eine geordnete Liste zu erstellen muss der Tag um einen Parameter erweitert werden. Wird kein Parameter angegeben wird eine ungeordnete Liste angezeigt. Folgene Paramater stehen zur Verfügung: ol oder OL (nummerierte Liste), a oder A (alphabetische Liste mit Klein- oder Großbuchstaben).';
$lang['bbcodes_list_ol'] = 'Geordnete Liste';
$lang['bbcodes_option'] = 'Option';
$lang['bbcodes_ot'] = 'Off-Topic / Vom Thema abweichender Kommentar';
$lang['bbcodes_ot_desc'] = 'Der [ot] Tag kennzeichnet Textstellen, die keine Relevanz zum eigentlichen Thema haben. ';
$lang['bbcodes_param'] = 'Parameter';
$lang['bbcodes_quote'] = 'Zitat';
$lang['bbcodes_quote_desc'] = 'Der [quote] Tag dient der Kennzeichnung von Zitaten. Der Tag kann ich verschiedenen Variationen benutzt werden. Die erste Variante verzichtet auf die Nennung eines Autors/einer Quelle.In der zweiten und dritten Variante kann als Option ein Autor/eine Person genannt werden oder eine Internetadresse angegeben werden, die verlinkt wird.';
$lang['bbcodes_reader'] = 'Umwandlung des Lesernamens';
$lang['bbcodes_reader_desc'] = 'Der [reader] Tag wird durch den Namen des gerade lesenden Benutzers ausgetauscht. Der Tag benötigt kein schließendes Element.';
$lang['bbcodes_size'] = 'Größe';
$lang['bbcodes_size_desc'] = 'Mit dem [size] Tag kann die Schriftgröße variiert werden. Folgende Parameter stehen zur Auswahl: large (große Schrift), small (kleine Schrift) oder extended (Schrift mit erweitertem Zeichenabstand).';
$lang['bbcodes_size_extended'] = 'Erweiterte Schrift';
$lang['bbcodes_size_large'] = 'Große Schrift';
$lang['bbcodes_size_small'] = 'Kleine Schrift';
$lang['bbcodes_size_title'] = 'Schriftgröße auswählen';
$lang['bbcodes_sub'] = 'Tiefgestellt';
$lang['bbcodes_sub_desc'] = 'Der [sub] Tag erlaubt das tiefstellen von bestimmten Zeichen bzw. von bestimmtem Text.';
$lang['bbcodes_sup'] = 'Hochgestellt';
$lang['bbcodes_sup_desc'] = 'Der [sup] Tag erlaubt das hochstellen von bestimmten Zeichen bzw. von bestimmtem Text.';
$lang['bbcodes_table'] = 'Tabelle';
$lang['bbcodes_table_cols'] = 'Spalten';
$lang['bbcodes_table_desc'] = 'Mit dem [table] Tag kann eine Tabelle realisiert werden. In dem [table] Tag werden die Daten eingetragen, wobei jede Zeile einer Tabellenzeile entspricht und einzelne Tabellenspalten werden durch dem [tab] Tag oder ein | getrennt werden. Mehrzeilige Einträge in den einzelnen Zellen sind mit dem [br] Tag möglich, in den Tabellenzellen können BB-Codes benutzt werden. Dem [table] Tag können zwei Optionen mitgegeben werden, getrennt mit Semikolon (;). Wenn die erste Zeile als Überschrift angezeigt werden soll, so muss "head" als Option angegeben werden. Falls die Tabelle eine bestimmte Breite haben soll, so kann die Breite in Prozent mit abschließendem Prozentzeichen (%) angegeben werden. Beide Optionen sind optional verwendbar.';
$lang['bbcodes_table_insert_table'] = 'Tabelle einfügen';
$lang['bbcodes_table_rows'] = 'Zeilen';
$lang['bbcodes_table_show_head'] = 'Erste Zeile als Titelzeile benutzen';
$lang['bbcodes_tt'] = 'Schreibmaschinenschrift';
$lang['bbcodes_tt_desc'] = 'Der [tt] Tag stellt den Text wie mit einer Schreibmaschine geschrieben dar, also mit einer Monospace Schriftart.';
$lang['bbcodes_underline'] = 'Unterstrichen';
$lang['bbcodes_underline_desc'] = 'Mit den [u] Tag können Sie Texte unterstreichen.';
$lang['bbcodes_url'] = 'Internetadresse';
$lang['bbcodes_url_desc'] = 'Mit dem [url] Tag können gültige Internetadressen verlinken. Die erste Variante erfordert lediglich eine URL. Die eingegebene URL wird als Linktitel benutzt, jedoch bei Überlänge gekürzt. Die zweite Variante akzeptiert die URL als Option im [url] Tag. Wo in der ersten Variante die URL stand kann nun ein Linktitel eingegeben werden.';
$lang['bbcode_help_overview'] = 'Übersicht';
$lang['bbcode_help_smileys'] = 'Smileys';
$lang['bbcode_help_smileys_desc'] = 'Folgende Smileys stehen auf dieser Seite zur Verfügung:';
$lang['bbhelp_title'] = 'BB-Code Hilfe';
$lang['more_smileys'] = 'mehr Smileys';
$lang['textarea_check_length'] = 'Überprüfe Textlänge';
$lang['textarea_decrease_size'] = 'Verkleinern';
$lang['textarea_increase_size'] = 'Vergrößern';
?>