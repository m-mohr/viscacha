########################################
# Installation Viscacha 0.8 Beta 1     #
########################################

== Präambel ==

Dies ist das erste öffentliche Release des Viscacha (0.8). Einige Features 
fehlen noch, aber ich arbeite daran die Features für Version 0.9 fertigzu-
stellen. Fehler und Verbesserungsvorschläge können jederzeit im Bugtracker
(s.u.) hinterlassen weden. Ich werde diese Fehler dann prüfen und ggf. ausbessern.

Diese Version ist weiterhin lediglich zum Testen gedacht und sollte nicht 
im produktiven Einsatz benutzt werden, denn es können Daten beim Update auf Version
0.9 verloren gehen.
Es handelt sich hierbei um die Version 0.8, nicht 1.0. Ich spreche dies explizit
an, da ich von 0.8 zu 1.0 noch viele grundlegende Dinge neuschreiben möchte, was
eine Kompatibilität der Versionen evtl. beeinträchtigt und teilweise Daten ver-
loren gehen können oder von Hand nachgetragen werden müssen. Weiterhin werden 
Module und Komponenten wohl überarbeitet werden müssen.

Einige Funktionen im Admin Control Panel sind nocht nicht funktionsfähig und
vieles muss noch in die englische Sprache übersetzt werden. Wer Lust hat, mir
beim Übersetzen des Administrationscenters zu helfen, meldet sich bitte bei mir.

== Installation ==

Einfach den Ordner "install/" im Viscacha-Verzeichnis 
aufrufen und den Anweisungen folgen. Anschließend sollte euch eine "frische"
Viscacha-Installation zur Verfügung stehen.

== Systemvorraussetzungen ==
Nähere Informationen zu den Systemvorraussetzungen erhalten Sie in der Datei
requirements.txt. Wenn Sie das Viscacha testen, bitte ich Sie darum, mir Be-
scheid zu geben unter welcher Serverkonfiguration das Viscacha lief und welche
Fehler aufgetreten sind.

Folgende Angaben sind von Interesse:
- Betriebssystem (des Servers)
- Serversoftware und Version
- Mailversand-Art (SMTP, Sendmail, PHP-Intern)
- MySQL-Version
- PHP-Version
- Status der Extensions: mysql, pcre, gd, zlib, xml, pspell, iconv, mbstring, mhash
- Folgende Einstellungen in der php.ini: 
  - safe_mode
  - magic_quotes_gpc
  - register_globals
  - register_long_arrays
  - sql.safe_mode

== Kontakt ==

E-Mail: webmaster@mamo-net.de
ICQ: 104458187
AIM: mamonede8
YIM: mamonede
Jabber: MaMo@jabber.ccc.de
MSN: ma_mo_web@hotmail.com

Bugtracker: http://bugs.viscacha.org

== Komponenten und PlugIns ==
PlugIns sind in dieser Version noch nicht möglich (abgesehen von den schon vorhandenen).
Komponenten sind keine mitgeliefert. Diese werden erst in einer späteren Version mit-
geliefert.