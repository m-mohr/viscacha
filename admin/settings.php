<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "settings.php") die('Error: Hacking Attempt');

// Loading Config-Data
include('classes/class.phpconfig.php');
$c = new manageconfig();

if ($job == 'ftp') {
	$config = $gpc->prepare($config);
	
	$path = '--';
	if (isset($_SERVER['DOCUMENT_ROOT'])) {
		$path = str_replace(realpath($_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR, '', realpath('../'));
	}
	
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=ftp2">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	 <tr> 
	  <td class="obox" colspan="2"><b>FTP Settings</b></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">FTP-Server:<br /><span class="stext">You can leave it empty for disabling FTP.</span></td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_server" size="50" value="<?php echo $config['ftp_server']; ?>"></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">FTP-Port:</td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_port" value="21" size="4" value="<?php echo $config['ftp_port']; ?>"></td> 
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">FTP-Startpfad:<br /><span class="stext">Pfad von dem aus das FTP-Programm arbeiten soll. Dieser Pfad soltle ausgehen vom normalen Start-FTP-Pfad relativ zu ihrem Viscacha-Verzeichnis zeigen. Wenn der FTP-Account direkt auf das Viscacha-Verzeichnis zeigt, reicht ein "/" unter *nix-Systemen. Vom Script ermittelter Pfad: <code><?php echo $path; ?></code></span></td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_path" value="<?php echo $config['ftp_path']; ?>" size="50"></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">FTP-Account-Benutzername</span></td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_user" value="<?php echo $config['ftp_user']; ?>" size="50"></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">FTP-Account-Passwort:</td>
	  <td class="mbox" width="50%"><input type="password" name="ftp_pw" value="<?php echo $config['ftp_pw']; ?>" size="50"></td> 
	 </tr>
	 </tr>
	  <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	 </tr>
	</table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'ftp2') {
	echo head();

	$c->getdata();
	$c->updateconfig('ftp_server', str);
	$c->updateconfig('ftp_user', str);
	$c->updateconfig('ftp_pw', str);
	$c->updateconfig('ftp_path', str);
	$c->updateconfig('ftp_port', int);
	$c->savedata();

	ok('admin.php?action=settings&job=ftp');
}
elseif ($job == 'posts') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=posts2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Beiträge &amp; Themen</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Anzahl Beiträge pro Themenseite:</td>
	   <td class="mbox" width="50%"><input type="text" name="topiczahl" value="<?php echo $config['topiczahl']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Time Limit on Editing of Posts:<br /><span class="stext">Time limit (in minutes) to impose on editing of messages. After this time limit only moderators will be able to edit the message. 0 = Disabled.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="edit_edit_time" value="<?php echo $config['edit_edit_time']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Time Limit on Deleting of Posts:<br /><span class="stext">Time limit (in minutes) to impose on deleting of messages. After this time limit only moderators will be able to delete the message. 0 = Disabled.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="edit_delete_time" value="<?php echo $config['edit_delete_time']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximale Anzahl Beiträge die per Multiquote in einem Beitrag zitiert werden können:<br /><span class="stext">Falls mehr Beitr&auml;ge im Zwischenspeicher zum &quot;Multiquote&quot; sind, werden nur die ersten X in das Textfeld eingefügt.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="maxmultiquote" value="<?php echo $config['maxmultiquote']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Minimale Titell&auml;nge:</td>
	   <td class="mbox" width="50%"><input type="text" name="mintitlelength" value="<?php echo $config['mintitlelength']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximale Titell&auml;nge:</td>
	   <td class="mbox" width="50%"><input type="text" name="maxtitlelength" value="<?php echo $config['maxtitlelength']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Minimale Beitragsl&auml;nge:</td>
	   <td class="mbox" width="50%"><input type="text" name="minpostlength" value="<?php echo $config['minpostlength']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximale Beitragsl&auml;nge:</td>
	   <td class="mbox" width="50%"><input type="text" name="maxpostlength" value="<?php echo $config['maxpostlength']; ?>" size="8"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Zu große Bilder automatisch verkleinern:</font><br /><span class="stext">Bilder, die per [img]-BB-Code einge&uuml;gt werden und für das Design zu groß sind, können automatisch durch Javascript verkleinert werden. Bei einem Klick auf das Bild kann es in Originalgr&ouml;&szlig;e dargestellt werden.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="resizebigimg" value="1"<?php echo iif($config['resizebigimg'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximalbreite f&uuml;r zu gro&szlig;e Bilder:<br /><span class="stext">Bilderbreite in Pixeln. Nur relevant, wenn die obige Option aktiv ist.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="resizebigimgwidth" value="<?php echo $config['resizebigimgwidth']; ?>" size="6"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">PDF-Ausgabe von Dokumenten aktivieren:</font><br /><span class="stext">Unabh&auml;ngig von den Einstellungen in der Gruppen- bzw. Rechteverwaltung.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pdfdownload" value="1"<?php echo iif($config['pdfdownload'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">PDF-Ausgabe komprimieren:</font><br /><span class="stext">Wenn die Ausgabe komprimiert wird, kann die Datei schneller heruntergeladen werden, jedoch wird mehr Performance zur Erstellung in Anspruch genommen.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pdfcompress" value="1"<?php echo iif($config['pdfcompress'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'posts2') {
	echo head();

	$c->getdata();
	$c->updateconfig('pdfdownload', int);
	$c->updateconfig('pdfcompress', int);
	$c->updateconfig('resizebigimg', int);
	$c->updateconfig('resizebigimgwidth', int);
	$c->updateconfig('maxpostlength', int);
	$c->updateconfig('minpostlength', int);
	$c->updateconfig('maxtitlelength', int);
	$c->updateconfig('mintitlelength', int);
	$c->updateconfig('maxmultiquote', int);
	$c->updateconfig('edit_delete_time', int);
	$c->updateconfig('edit_edit_time', int);
	$c->updateconfig('topiczahl', int);
	$c->savedata();

	ok('admin.php?action=settings&job=posts');
}
elseif ($job == 'profile') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=profile2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Profil (editieren &amp; ansehen) </b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximall&auml;nge f&uuml;r Benutzernamen:</td>
	   <td class="mbox" width="50%"><input type="text" name="maxnamelength" value="<?php echo $config['maxnamelength']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Mindestl&auml;nge f&uuml;r Benutzernamen:</td>
	   <td class="mbox" width="50%"><input type="text" name="minnamelength" value="<?php echo $config['minnamelength']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximall&auml;nge f&uuml;r Passw&ouml;rter:</td>
	   <td class="mbox" width="50%"><input type="text" name="maxpwlength" value="<?php echo $config['maxpwlength']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Mindestl&auml;nge f&uuml;r Passw&ouml;rter:</td>
	   <td class="mbox" width="50%"><input type="text" name="minpwlength" value="<?php echo $config['minpwlength']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximall&auml;nge f&uuml;r &quot;persönliche Seiten&quot;:</td>
	   <td class="mbox" width="50%"><input type="text" name="maxaboutlength" value="<?php echo $config['maxaboutlength']; ?>" size="8"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximaler Speicher f&uuml;r die Notizen pro User:<br /><span class="stext">in Bytes</span></td>
	   <td class="mbox" width="50%"><input type="text" name="maxnoticelength" value="<?php echo $config['maxnoticelength']; ?>" size="8"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximall&auml;nge für den &quot;Grund der Editierung&quot;:</td>
	   <td class="mbox" width="50%"><input type="text" name="maxeditlength" value="<?php echo $config['maxeditlength']; ?>" size="6"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Anzahl Themen die in der eigenen Themen-Ansicht angezeigt werden:<br /><span class="stext">siehe: &quot;<a href="editprofile.php?action=mylast" target="_blank">Meine letzten Beiträge</a>&quot;</span></td>
	   <td class="mbox" width="50%"><input type="text" name="mylastzahl" value="<?php echo $config['mylastzahl']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Online-Status im Profil anzeigen:</font><br /><span class="stext">Zeigt im Profil an, ob der Benutzer im Forum gerade aktiv ist.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="osi_profile" value="1"<?php echo iif($config['osi_profile'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">vCard-Download erlauben:</font><br /><span class="stext">Eine vCard ist eine „elektronische Visitenkarte“, die ein Benutzer mit einem Mausklick direkt in das Adressbuch seines E-Mail-Programms übernehmen kann.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="vcard_dl" value="1"<?php echo iif($config['vcard_dl'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">vCard-Download auch Gästen erlauben:</font><br /><span class="stext">Die obige Option auch für Gäste aktivieren.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="vcard_dl_guests" value="1"<?php echo iif($config['vcard_dl_guests'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Bernutzernamen-Änderung erlauben:</font><br /><span class="stext">Wenn diese Option aktiviert ist, können die Benutzer Ihre Namen ändern.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="changename_allowed" value="1"<?php echo iif($config['changename_allowed'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Anzahl der geschriebenen Beiträge im Profil anzeigen:</font><br /><span class="stext">Im Profil können die Anzahl der geschriebenen Beiträge des Benutzers angezeigt werden. Diese Option kann bei gr&ouml;&szlig;eren Foren zu Performance-Problemen führen!</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="showpostcounter" value="1"<?php echo iif($config['showpostcounter'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'profile2') {
	echo head();

	$c->getdata();
	$c->updateconfig('osi_profile', int);
	$c->updateconfig('changename_allowed', int);
	$c->updateconfig('vcard_dl_guests', int);
	$c->updateconfig('vcard_dl', int);
	$c->updateconfig('showpostcounter', int);
	$c->updateconfig('maxnamelength', int);
	$c->updateconfig('minnamelength', int);
	$c->updateconfig('minpwlength', int);
	$c->updateconfig('maxpwlength', int);
	$c->updateconfig('maxaboutlength', int);
	$c->updateconfig('maxnoticelength', int);
	$c->savedata();

	ok('admin.php?action=settings&job=profile');
}
elseif ($job == 'signature') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=signature2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Signaturen</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximale Signaturl&auml;nge:</td>
	   <td class="mbox" width="50%"><input type="text" name="maxsiglength" value="<?php echo $config['maxsiglength']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">[img]-BB-Code (Bilder) erlauben:</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sig_bbimg" value="1"<?php echo iif($config['sig_bbimg'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">[code]-BB-Code (Quelltext) erlauben:</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sig_bbcode" value="1"<?php echo iif($config['sig_bbcode'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">[list]-BB-Code (Listen) erlauben:</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sig_bblist" value="1"<?php echo iif($config['sig_bblist'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">[edit]-BB-Code (Nachträgliche Anmerkung) erlauben:</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sig_bbedit" value="1"<?php echo iif($config['sig_bbedit'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">[ot]-BB-Code (Off-Topic) erlauben:</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sig_bbot" value="1"<?php echo iif($config['sig_bbot'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">[h]-BB-Code (Überschriften) erlauben:</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="" value="1"<?php echo iif($config['sig_bbh'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'signature2') {
	echo head();

	$c->getdata();
	$c->updateconfig('maxsiglength', int);
	$c->updateconfig('sig_bbimg', int);
	$c->updateconfig('sig_bbcode', int);
	$c->updateconfig('sig_bblist', int);
	$c->updateconfig('sig_bbedit', int);
	$c->updateconfig('sig_bbot', int);
	$c->updateconfig('sig_bbh', int);
	$c->savedata();

	ok('admin.php?action=settings&job=signature');
}
elseif ($job == 'search') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=search2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Suche</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Mindestlänge der Suchbegriffe:<br /><span class="stext">Diese Einstellung erlaubt es, kurze Wörter zu ignorieren. Wörter mit weniger als der hier angegebenen Anzahl an Buchstaben werden ignoriert.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="searchminlength" value="<?php echo $config['searchminlength']; ?>" size="3"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximale Anzahl Suchergebnisse:<br /><span class="stext">Nach dem Erreichen der Maximalzahl, wird aufgehört zu suchen um den Server zu entlasten.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="maxsearchresults" value="<?php echo $config['maxsearchresults']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Floodsperre bei der Suche aktivieren:</font><br /><span class="stext">Mit Flood ist im Internet ein Kommando gemeint, das sehr schnell wiederholt wird, um damit im Extremfall normales Arbeiten zu verhindern oder den so angegriffenen Rechner lahm zu legen.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="floodsearch" value="1"<?php echo iif($config['floodsearch'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'search2') {
	echo head();

	$c->getdata();
	$c->updateconfig('floodsearch', int);
	$c->updateconfig('maxsearchresults', int);
	$c->updateconfig('searchminlength', int);
	$c->savedata();

	ok('admin.php?action=settings&job=search');
}
elseif ($job == 'server') {
	$config = $gpc->prepare($config);
	
	$gdv = 'GD not found!';
	if (function_exists('gd_info')) {
	    $gd = @gd_info();
	}
	if (!empty($gd['GD Version'])) {
		$gdv = $gd['GD Version'];
	}
	else {
    	ob_start();
    	phpinfo();
    	$info = ob_get_contents();
    	ob_end_clean();
    	foreach(explode("\n", $info) as $line) {
     		if(strpos($line, "GD Version")!==false) {
        		$gdv = trim(str_replace("GD Version", "", strip_tags($line)));
        	}
    	}
	}
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=server2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>PHP, Webserver und Dateisystem</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">GD Version:<br /><span class="stext">Version of <a href="http://www.boutell.com/gd/" target="_blank">GD</a> installed on your server. You can find the version by searching for 'GD' on your <a href="admin.php?action=misc&job=phpinfo" target="Main">phpinfo()</a> output. Detected GD Version: <?php echo $gdv; ?></span></td>
	   <td class="mbox" width="50%"><select name="gdversion">
	   <option value="1"<?php echo iif($config['gdversion'] == 1, ' selected="selected"'); ?>>1.x</option>
	   <option value="2"<?php echo iif($config['gdversion'] == 2, ' selected="selected"'); ?>>2.x</option>
	   </select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">PHP-Fehlermeldungen:<br /><span class="stext">Gibt an, welche PHP-Fehlermeldungen angezeigt werden. Mehr Informationen: <a href="http://www.php.net/manual/ref.errorfunc.php#errorfunc.constants" target="_blank">Error Handling: Konstanten</a> und <a href="http://www.php.net/error-reporting" target="_blank">error_reporting()</a>.</span></td>
	   <td class="mbox" width="50%"><select name="error_reporting">
	   <option value="-1"<?php echo iif($config['error_reporting'] == -1, ' selected="selected"'); ?>>PHP-Standard</option>
	   <option value="1"<?php echo iif($config['error_reporting'] == 1, ' selected="selected"'); ?>>E_ERROR: Fatale Laufzeit-Fehler</option>
	   <option value="2"<?php echo iif($config['error_reporting'] == 2, ' selected="selected"'); ?>>E_WARNING: Warnungen zur Laufzeit des Skripts. </option>
	   <option value="4"<?php echo iif($config['error_reporting'] == 4, ' selected="selected"'); ?>>E_PARSE: Parser-Fehler während der Übersetzung. </option>
	   <option value="8"<?php echo iif($config['error_reporting'] == 8, ' selected="selected"'); ?>>E_NOTICE: Benachrichtigungen während der Laufzeit.</option>
	   <option value="2047"<?php echo iif($config['error_reporting'] == 2047, ' selected="selected"'); ?>>E_ALL: Alle Fehler und Warnungen (Ausnahme: E_STRICT).</option>
	   <?php if (version_compare(PHP_VERSION, '5.0.0', '>=')) { ?>
	   <option value="2048"<?php echo iif($config['error_reporting'] == 2048, ' selected="selected"'); ?>>E_STRICT: Benachrichtigungen des Laufzeitsystems (ab PHP5).</option>
	   <?php } ?>
	   </select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Dateisystem auf korrekt gesetzte CHMODs prüfen:</font><br /><span class="stext">Wenn diese Option aktiviert ist, wird bei jedem Aufruf geprüft, ob die CHMODs der Order und Dateien richtig gesetzt sind. Diese Option sollte deaktiviert sein, wenn nicht unmittelbar zuvor Änderungen am Dateisystem gemacht wurden, z.B. nach der Installation oder Updates.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="check_filesystem" value="1"<?php echo iif($config['check_filesystem'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">.htaccess: Leite alle Subdomains auf die Domain um:<br /><span class="stext">http://www.mamo-net.de wird zu http://mamo-net.de. Es werden jedoch wahrscheinlich auch alle Subdomains umgeleitet!</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="correctsubdomains" value="1"<?php echo iif($config['hterrordocs'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">.htaccess: Error-Dokumente benutzen:</font><br /><span class="stext">Bei Server-Fehlern (400, 401, 403, 404, 500) werden eigene Fehlerseiten angezeigt. Beispiel: <a href="misc.php?action=error&id=404" target="_blank">Error 404</a></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="hterrordocs" value="1"<?php echo iif($config['hterrordocs'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'server2') {
	echo head();

	$c->getdata();
	$c->updateconfig('gdversion', int);
	$c->updateconfig('error_reporting', int);
	$c->updateconfig('correctsubdomains', int);
	$c->updateconfig('hterrordocs', int);
	$c->updateconfig('check_filesystem', int);
	$c->savedata();

	$filesystem->unlink('.htaccess');

	ok('admin.php?action=settings&job=server');
}
elseif ($job == 'session') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=session2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Sessionsystem</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Länge der Session-IDs:<br /><span class="stext"></span></td>
	   <td class="mbox" width="50%"><select name="sid_length">
	   <option value="32"<?php echo iif($config['sid_length'] == '32', ' selected="selected"'); ?>>32 Zeichen</option>
	   <option value="64"<?php echo iif($config['sid_length'] == '64', ' selected="selected"'); ?>>64 Zeichen</option>
	   <option value="96"<?php echo iif($config['sid_length'] == '96', ' selected="selected"'); ?>>96 Zeichen</option>
	   <option value="128"<?php echo iif($config['sid_length'] == '128', ' selected="selected"'); ?>>128 Zeichen</option>
	   </select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Zeit zwischen der Überprüfung auf inaktive User in der Session-Tabelle:<br /><span class="stext">in Sekunden</span></td>
	   <td class="mbox" width="50%"><input type="text" name="sessionrefresh" value="<?php echo $config['sessionrefresh']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Zeit nach der User als inaktiv gelten:<br /><span class="stext">in Minuten</span></td>
	   <td class="mbox" width="50%"><input type="text" name="sessionsave" value="<?php echo $config['sessionsave']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Floodsperre aktivieren:</font><br /><span class="stext">Mit Flood ist im Internet ein Kommando gemeint, das sehr schnell wiederholt wird, um damit im Extremfall normales Arbeiten zu verhindern oder den so angegriffenen Rechner lahm zu legen.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="enableflood" value="1"<?php echo iif($config['enableflood'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Bei Session-ID Nutzung auf IP-Prüfen:</font><br /><span class="stext">Übernahme erschweren</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="session_checkip" value="1"<?php echo iif($config['session_checkip'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'session2') {
	echo head();

	$c->getdata();
	$c->updateconfig('sid_length', int);
	$c->updateconfig('sessionrefresh', int);
	$c->updateconfig('sessionsave', int);
	$c->updateconfig('enableflood', int);
	$c->updateconfig('session_checkip', int);
	$c->savedata();

	ok('admin.php?action=settings&job=session');
}
elseif ($job == 'boardcat') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=boardcat2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Foren &amp; Kategorien</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Anzahl Themen pro Forenseite:<br /><span class="stext">Anzahl der Themen die pro Seite der Themenübersicht angezeigt werden.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="forumzahl" value="<?php echo $config['forumzahl']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Unterforen in der Forenübersicht anzeigen:</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="showsubfs" value="1"<?php echo iif($config['showsubfs'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Forenstatistiken bei Änderungen synchronisieren:</font><br /><span class="stext">Bei größeren Foren nicht empfohlen! Wenn diese Option aktiviert ist, wird bei jeder Änderung der Beitragsanzahl, Themenanzahl etc. diese Anzahl mit den Datenbeständen abgeglichen, ansonsten wird es manuell angepasst.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="updateboardstats" value="1"<?php echo iif($config['updateboardstats'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'boardcat2') {
	echo head();

	$c->getdata();
	$c->updateconfig('forumzahl', int);
	$c->updateconfig('showsubfs', int);
	$c->updateconfig('updateboardstats', int);
	$c->savedata();

	ok('admin.php?action=settings&job=boardcat');
}
elseif ($job == 'user') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=user2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Mitglieder- &amp; Teamliste</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Mitglieder pro Seite:<br /><span class="stext">Anzahl der Mitglieder die pro Seite der Mitgliederliste angezeigt werden.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="mlistenzahl" value="<?php echo $config['mlistenzahl']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Teamliste - Zeitraum der Berechtigung als Moderator anzeigen:</font><br /><span class="stext">In der Teamliste bei Moderatoren anzeigen, bis wann der Benutzer die Berechtigung hat, als Moderator zu fungieren.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="team_mod_dateuntil" value="1"<?php echo iif($config['team_mod_dateuntil'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'user2') {
	echo head();

	$c->getdata();
	$c->updateconfig('mlistenzahl', int);
	$c->updateconfig('team_mod_dateuntil', int);
	$c->savedata();

	ok('admin.php?action=settings&job=user');
}
elseif ($job == 'cmsp') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=cmsp2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>CMS &amp; Portal</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Welche Datei soll als Startseite benutzt werden:</td>
	   <td class="mbox" width="50%"><select name="indexpage">
	   <option value="forum"<?php echo iif($config['indexpage'] == 'forum', ' selected="selected"'); ?>>Forenübersicht</option>
	   <option value="portal"<?php echo iif($config['indexpage'] == 'portal', ' selected="selected"'); ?>>Portal</option>
	   </select></td> 
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'cmsp2') {
	echo head();

	$c->getdata();
	$c->updateconfig('indexpage', str);
	$c->savedata();

	ok('admin.php?action=settings&job=cmsp');
}
elseif ($job == 'pm') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=pm2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Administration</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Anzahl private Nachrichten pro Seite:</td>
	   <td class="mbox" width="50%"><input type="text" name="pmzahl" value="<?php echo $config['pmzahl']; ?>" size="4"></td> 
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'pm2') {
	echo head();

	$c->getdata();
	$c->updateconfig('pmzahl', int);
	$c->savedata();

	ok('admin.php?action=settings&job=pm');
}
elseif ($job == 'email') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=email2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>E-Mails</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Versandart:<br /><span class="stext"></span></td>
	   <td class="mbox" width="50%"><select name="type">
	   <option value="0"<?php echo iif($config['smtp'] != 1 && $config['sendmail'] != 1, ' selected="selected"'); ?>>PHP interne Mail-Funktion</option>
	   <option value="1"<?php echo iif($config['sendmail'] == 1, ' selected="selected"'); ?>>Sendmail-Versand</option>
	   <option value="2"<?php echo iif($config['smtp'] == 1, ' selected="selected"'); ?>>SMTP-Versand</option>
	   </select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Sendmail - Host:<br /><span class="stext">Nur wenn Sendmail aktiviert ist.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="sendmail_host" value="<?php echo $config['sendmail_host']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">SMTP - Host:</font><br /><span class="stext">Nur wenn SMTP aktiviert ist.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="smtp_host" value="<?php echo $config['smtp_host']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">SMTP - Authentifizierung:</font><br /><span class="stext">Nur wenn SMTP aktiviert ist.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="smtp_auth" value="1"<?php echo iif($config['smtp_auth'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">SMTP - Username:<br /><span class="stext">Nur wenn SMTP und Authentifizierung aktiviert ist.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="smtp_username" value="<?php echo $config['smtp_username']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">SMTP - Passwort:<br /><span class="stext">Nur wenn SMTP und Authentifizierung aktiviert ist.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="smtp_password" value="<?php echo $config['smtp_password']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">"Wegwerf"-Emailadressen verbieten:</font><br /><span class="stext">Die Domains der Adressen können Sie <a href="admin.php?action=misc&job=sessionmails">hier</a> editieren.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sessionmails" value="1"<?php echo iif($config['sessionmails'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'email2') {
	echo head();

	$versand = $gpc->get('type', int);

	$c->getdata();
	if ($versand == 2) {
		$c->updateconfig('smtp', int, 1);
		$c->updateconfig('sendmail', int, 0);
	}
	elseif ($versand == 1) {
		$c->updateconfig('smtp', int, 0);
		$c->updateconfig('sendmail', int, 1);
	}
	else {
		$c->updateconfig('smtp', int, 0);
		$c->updateconfig('sendmail', int, 0);
	}
	$c->updateconfig('sendmail_host', str);
	$c->updateconfig('smtp_host', str);
	$c->updateconfig('smtp_auth', int);
	$c->updateconfig('smtp_username', str);
	$c->updateconfig('smtp_password', str);
	$c->savedata();

	ok('admin.php?action=settings&job=email');
}
elseif ($job == 'lang') {
	$config = $gpc->prepare($config);
	echo head();
	
	$charsets = array(
	'ISO-8859-1' => 'Westeuropäisch, Latin-1',
	'ISO-8859-15' => 'Westeuropäisch, Latin-9.',
	'UTF-8' => 'ASCII-kompatibles Multi-Byte 8-Bit Unicode.',
	'cp866' => 'DOS-spezifischer Kyrillischer Zeichensatz. (Ab PHP version 4.3.2)',
	'cp1251' => 'Windows-spezifischer Kyrillischer Zeichensatz. (Ab PHP version 4.3.2)',
	'cp1252' => 'Windows spezifischer Zeichensatz für westeuropäische Sprachen.',
	'KOI8-R' => 'Russisch. (Ab PHP version 4.3.2)',
	'BIG5' => 'Traditionelles Chinesisch, hauptsächlich in Taiwan verwendet.',
	'GB2312' => 'Vereinfachtes Chinesisch, nationaler Standard-Zeichensatz.',
	'BIG5-HKSCS' => 'traditionelles Chinesisch mit Hongkong-spezifischen Erweiterungen',
	'Shift_JIS' => 'Japanisch',
	'EUC-JP' => 'Japanisch'
	);
	
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=lang2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Internationalisierung &amp; Sprachen </b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Zeichensätze aktivieren:</font><br /><span class="stext">Unterstützung für z.B. asiatische Sprachen aktivieren. Sollte nur aktiviert werden, wenn Probleme auftauchen.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="asia" value="1"<?php echo iif($config['asia'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="mbox" width="50%">Zeichensatz in den die eingehenden Daten konvertiert werden sollen:<br /><span class="stext">Hinweis zu ISO-8895-15: Ist der gleiche Zeichensatz wie ISO-8895-1, jedoch ergänzt um das Euro-Zeichen sowie französische und finnische Buchstaben.</span></td>
	   <td class="mbox" width="50%"><select name="asia_charset">
	   <?php foreach ($charsets as $key => $opt) { ?>
	   <option value="<?php echo $key; ?>"<?php echo iif($config['asia_charset'] == $key, ' selected="selected"'); ?>><?php echo $key.': '.$opt; ?></option>
	   <?php } ?>
	   </select>
	   </td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'lang2') {
	echo head();

	$c->getdata();
	$c->updateconfig('asia',int);
	$c->updateconfig('asia_charset',str);
	$c->savedata();

	ok('admin.php?action=settings&job=lang');
}
elseif ($job == 'register') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=register2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Registrierung</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Benutzer freischalten:<br /><span class="stext"></span></td>
	   <td class="mbox" width="50%"><select name="confirm_registration">
	   <option value="11"<?php echo iif($config['confirm_registration'] == '11', ' selected="selected"'); ?>>User sind sofort freigeschaltet</option>
	   <option value="10"<?php echo iif($config['confirm_registration'] == '10', ' selected="selected"'); ?>>Nur Freischaltung per E-Mail</option>
	   <option value="01"<?php echo iif($config['confirm_registration'] == '01', ' selected="selected"'); ?>>Nur Freischaltung durch den Administrator</option>
	   <option value="00"<?php echo iif($config['confirm_registration'] == '00', ' selected="selected"'); ?>>Freischaltung per E-Mail und durch den Administrator</option>
	   </select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">User muss Regeln bei der Registrierung akzeptieren:</font><br /><span class="stext">Die Verhaltensbedingungen <!-- Ersetzen durch Link zu ACP -->(<a href="misc.php?action=rules" target="_blank">siehe hier</a>) müssen gelesen und akzeptiert werden, wenn aktiviert.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="acceptrules" value="1"<?php echo iif($config['acceptrules'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Spam-Bot-Schutz aktivieren:</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="botgfxtest" value="1"<?php echo iif($config['botgfxtest'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Filter auf Spam-Bot-Schutz-Bild anwenden:</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="botgfxtest_filter" value="1"<?php echo iif($config['botgfxtest_filter'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Spam-Bot-Schutz: Anstatt CAPTCHA-Image einen Text-Code anzeigen:</font><br /><span class="stext">Beispiele siehe unten.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="register_text_verification" value="1"<?php echo iif($config['register_text_verification'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table> 
	</form><br />
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="4"><b>Beispiele für CAPTCHA-Images unt Text-Codes</b></td>
	  </tr>
	  <tr> 
	   <td class="ubox" width="50%" colspan="2">CAPTCHA-Image</td>
	   <td class="ubox" width="50%" colspan="2">Text-Code</td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="25%" align="center"><img src="admin/html/images/captcha.jpg" border="0" /></td>
	   <td class="mbox" width="25%" align="center"><img src="admin/html/images/captcha2.jpg" border="0" /></td>
	   <td class="mbox" width="25%"><div class="center" style="padding: 2px; font-size: 7px; line-height:7px; font-family: Courier New, monospace">&nbsp;########&nbsp;&nbsp;&nbsp;######&nbsp;&nbsp;&nbsp;&nbsp;########&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;########&nbsp;&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;<br>&nbsp;########&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;######&nbsp;&nbsp;&nbsp;&nbsp;#####&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;########&nbsp;&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;######&nbsp;&nbsp;&nbsp;&nbsp;########&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;########&nbsp;&nbsp;</div></td>
	   <td class="mbox" width="25%"><div class="center" style="padding: 2px; font-size: 7px; line-height:7px; font-family: Courier New, monospace">&nbsp;&nbsp;######&nbsp;&nbsp;&nbsp;######&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;######&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;###&nbsp;&nbsp;&nbsp;&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;###&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;##&nbsp;&nbsp;&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;####&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;##&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;####&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;#########&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;###&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;<br>&nbsp;&nbsp;######&nbsp;&nbsp;&nbsp;######&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;######&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;</div></td>
	  </tr>
	 </table>
	<?php
	echo foot();
}
elseif ($job == 'register2') {
	echo head();

	$c->getdata();
	$c->updateconfig('confirm_registration',str);
	$c->updateconfig('acceptrules',int);
	$c->updateconfig('botgfxtest',int);
	$c->updateconfig('botgfxtest_filter', int);
	$c->updateconfig('register_text_verification',int);
	$c->savedata();

	ok('admin.php?action=settings&job=register');
}
elseif ($job == 'spellcheck') {
	$config = $gpc->prepare($config);
	$ext = get_loaded_extensions();
	if (in_array("pspell", $ext)) {
		$ps = "<span style='color: green;'>vorhanden</span>";
	}
	else {
		$ps = "<span style='color: red;'>nicht vorhanden</span>";
	}
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=spellcheck2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Spellcheck</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Enable Spellchecker:<br /><span class="stext">Weitere Einstellungen finden Sie unter "<a href="admin.php?action=misc&job=spellcheck">Spellchecking</a>".</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="spellcheck" value="1"<?php echo iif($config['spellcheck'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Spellcheck-System:<br /><span class="stext">Es wird empfohlen Pspell zu benutzen, da sowohl MySQL als auch Textdateien den Server extrem beanspruchen können. Pspell ist auf Ihrem System <?php echo $ps; ?>.</span></td>
	   <td class="mbox" width="50%"><select name="pspell">
	   <option value="pspell"<?php echo iif($config['pspell'] == 'pspell', ' selected="selected"'); ?>>PSpell/Aspell (Empfohlen)</option>
	   <option value="mysql"<?php echo iif($config['pspell'] == 'mysql', ' selected="selected"'); ?>>MySQL/PHP</option>
	   <option value="php"<?php echo iif($config['pspell'] == 'php', ' selected="selected"'); ?>>Textdateien/PHP</option>
	   </select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Ignoriert Wörter mit weniger Buchstaben als:<br /><span class="stext">Diese Einstellung erlaubt es, kurze Wörter zu überspringen. Wörter mit weniger als der hier angegebenen Anzahl an Buchstaben werden übersprungen.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="spellcheck_ignore" value="<?php echo $config['spellcheck_ignore']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Modus der gelieferten Vorschläge:</td>
	   <td class="mbox" width="50%"><select name="spellcheck_mode">
	   <option value="0"<?php echo iif($config['spellcheck_mode'] == 0, ' selected="selected"'); ?>>Schneller Modus (geringste Anzahl Vorschläge)</option>
	   <option value="1"<?php echo iif($config['spellcheck_mode'] == 1, ' selected="selected"'); ?>>Normaler Modus (mehr Vorschläge)</option>
	   <option value="2"<?php echo iif($config['spellcheck_mode'] == 2, ' selected="selected"'); ?>>Langsamer Modus (viele Vorschläge)</option>
	   </select></td> 
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'spellcheck2') {
	echo head();

	$c->getdata();
	$c->updateconfig('spellcheck',int);
	$c->updateconfig('spellcheck_ignore',int);
	$c->updateconfig('spellcheck_mode',int);
	$c->updateconfig('pspell',str);
	$c->savedata();

	ok('admin.php?action=settings&job=spellcheck');
}
elseif ($job == 'jabber') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=jabber2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Jabber</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Enable Jabber-Support:</font><br /><span class="stext">Aktiviert den Versand von Nachrichten mittels Jabber. Das Profil-Feld ist davon <em>nicht</em> betroffen.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="enable_jabber" value="1"<?php echo iif($config['enable_jabber'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Jabber-Server (und Port):<br /><span class="stext">Jabber-Server ohne Protokoll angeben. In üblichen Jabber-Adresse ist dieser Eintrag gleich dem Text nach dem @. Der Port kann mit : getrent angehängt werden. Beispiel für die Adresse username@domain.com und dem Port 5222: "domain.com:5222".</span></td>
	   <td class="mbox" width="50%"><input type="text" name="jabber_server" value="<?php echo $config['jabber_server']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Jabber-Benutzername:<br /><span class="stext">Benutezrname eines Jabber-Accounts zum Versand von Jabber-Nachrichten. In üblichen Jabber-Adresse ist dieser Eintrag gleich dem Text vor dem @. Beispiel für die Adresse username@domain.com: "username".</span></td>
	   <td class="mbox" width="50%"><input type="text" name="jabber_user" value="<?php echo $config['jabber_user']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Jabber-Passwort:<br /><span class="stext">Passwort zum oben angegebenen Jabber-Account.</span></td>
	   <td class="mbox" width="50%"><input type="password" name="jabber_pass" value="<?php echo $config['jabber_pass']; ?>" size="50"></td> 
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'jabber2') {
	echo head();

	$c->getdata();
	$c->updateconfig('enable_jabber',int);
	$c->updateconfig('jabber_server',str);
	$c->updateconfig('jabber_user',str);
	$c->updateconfig('jabber_pass',str);
	$c->savedata();

	ok('admin.php?action=settings&job=jabber');
}
elseif ($job == 'db') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=db2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>Datenbank</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Datenbank-System:</td>
	   <td class="mbox" width="50%"><select name="dbsystem"><option value="mysql"<?php echo iif($config['dbsystem'] == 'mysql', ' selected="selected"'); ?>>MySQL</option></select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Datenbank-Host:<br><font class="stext">häufig "localhost"</font></td>
	   <td class="mbox" width="50%"><input type="text" name="host" value="<?php echo $config['host']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Datenbank-Nutzer:</td>
	   <td class="mbox" width="50%"><input type="text" name="dbuser" value="<?php echo $config['dbuser']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Datenbank-Passwort:</td>
	   <td class="mbox" width="50%"><input type="password" name="dbpw" value="<?php echo $config['dbpw']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Datenbank:<br><font class="stext">Datenbank in der die tabellen des Forums gespeichert sind</font></td>
	   <td class="mbox" width="50%"><input type="text" name="database" value="<?php echo $config['database']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Tabellenprefix:<br><font class="stext">Prefix für die Tabellen dieser Viscacha-Installation.<br>Achtung: Tabellen werden nicht automatisch umbenannt!</font></td>
	   <td class="mbox" width="50%"><input type="text" name="dbprefix" value="<?php echo $config['dbprefix']; ?>" size="10"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Wichtige Tabellen:</font><br><font class="stext">Diese Tabellen werden automatisch per Cron Job optimiert! Tabellen mit "," getrennt angeben und ohne Tabellenprefix.</font></td>
	   <td class="mbox" width="50%"><input type="text" name="optimizetables" value="<?php echo $config['optimizetables']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Persistente Verbindung:</font><br><font class="stext">SQL-Verbindungen, die nach Ende des Skriptes nicht geschlossen werden. Wenn eine Verbindung angefordert wird, wird geprüft ob bereits eine Verbindung existiert.<br>Quelle: <a href="http://www.php.net/manual/features.persistent-connections.php" target="_blank">php.net - Persistente Datenbankverbindungen</a></font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pconnect" value="1"<?php echo iif($config['pconnect'],' checked'); ?>></td> 
	  </tr>
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'db2') {
	echo head();

	$c->getdata();
	$c->updateconfig('host',str);
	$c->updateconfig('dbuser',str);
	$c->updateconfig('dbpw',str);
	$c->updateconfig('database',str);
	$c->updateconfig('pconnect',int);
	$c->updateconfig('dbprefix',str);
	$c->updateconfig('dbsystem',str);
	$c->updateconfig('optimizetables',str);
	$c->savedata();

	ok('admin.php?action=settings&job=db');
}
elseif ($job == 'attupload') {
	$config = $gpc->prepare($config);
	echo head();
	
	$array = explode('|',$config['tpcfiletypes']);
	$array2 = array();
	foreach ($array as $row) {
		if (strpos($row, '.') == 0) {
			$array2[] = substr($row,1);
		}
		else {
			$array2[] = $row;
		}
	}
	$config['tpcfiletypes'] = implode(',',$array2);
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=attupload2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>Beitragsupload</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Beitragsupload aktivieren:</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="tpcallow" value="1"<?php echo iif($config['tpcallow'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Erlaubte Dateiformate für Beitragsuploads:</font><br><font class="stext">Einträge getrennt mit Komma (,).</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcfiletypes" value="<?php echo $config['tpcfiletypes']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximale Dateigröße für Beitragsuploads in Bytes:</font><br><font class="stext">1 KB = 1024 Byte</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcfilesize" value="<?php echo $config['tpcfilesize']; ?>" size="10"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximale Breite in Pixel für Bilder beim Beitragsupload:</font><br><font class="stext">Keine Angabe = beliebige Breite</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcwidth" value="<?php echo $config['tpcwidth']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximale Höhe in Pixel für Bilder beim Beitragsupload:</font><br><font class="stext">Keine Angabe = beliebige Höhe</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcheight" value="<?php echo $config['tpcheight']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Breite in Pixel für verkleinerte Bilder:</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcthumbwidth" value="<?php echo $config['tpcthumbwidth']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Höhe in Pixel für verkleinerte Bilder:</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcthumbheight" value="<?php echo $config['tpcthumbheight']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximale Anzahl Beitragsuploads pro Beitrag:</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcmaxuploads" value="<?php echo $config['tpcmaxuploads']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Downloadgeschwindigkeit begrenzen:</font><br><font class="stext">Hier könnt Ihr die Geschwindigkeit beim Download von Beitragsuploads drosseln! Es ist eine <b>maximale Geschwindigkeit in KB</b> anzugeben. 0 = keine Begrenzung</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcdownloadspeed" value="<?php echo $config['tpcdownloadspeed']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'attupload2') {
	echo head();

	$c->getdata();
	
	// Beitragsupload
	$array = explode(',',$gpc->get('tpcfiletypes'));
	$array2 = array();
	foreach ($array as $row) {
		$array2[] = '.'.$row;
	}
	$ft = implode('|',$array2);

	$c->updateconfig('tpcallow',int);
	$c->updateconfig('tpcdownloadspeed',int);
	$c->updateconfig('tpcmaxuploads',int);
	$c->updateconfig('tpcheight',int);
	$c->updateconfig('tpcwidth',int);
	$c->updateconfig('tpcfilesize',int);
	$c->updateconfig('tpcfiletypes',str, $ft);
	$c->updateconfig('tpcthumbwidth',int);
	$c->updateconfig('tpcthumbheight',int);
	
	$c->savedata();

	ok('admin.php?action=settings&job=attupload');
}
elseif ($job == 'avupload') {
	$config = $gpc->prepare($config);
	echo head();
	
	$array = explode('|',$config['avfiletypes']);
	$array2 = array();
	foreach ($array as $row) {
		if (strpos($row, '.') == 0) {
			$array2[] = substr($row,1);
		}
		else {
			$array2[] = $row;
		}
	}
	$ft = implode(',',$array2);
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=avupload2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>Profilbilder &amp; Avatare</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Erlaubte Dateiformate für Profilbilder:</font><br><font class="stext">Einträge getrennt mit Komma (,).</font></td>
	   <td class="mbox" width="50%"><input type="text" name="avfiletypes" value="<?php echo $ft; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximale Dateigröße für Profilbilder in Bytes:</font><br><font class="stext">1 KB = 1024 Byte</font></td>
	   <td class="mbox" width="50%"><input type="text" name="avfilesize" value="<?php echo $config['avfilesize']; ?>" size="10"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximale Breite in Pixel für Profilbilder:</font><br><font class="stext">Keine Angabe = beliebige Breite</font></td>
	   <td class="mbox" width="50%"><input type="text" name="avwidth" value="<?php echo $config['avwidth']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximale Höhe in Pixel für Profilbilder:</font><br><font class="stext">Keine Angabe = beliebige Höhe</font></td>
	   <td class="mbox" width="50%"><input type="text" name="avheight" value="<?php echo $config['avheight']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'avupload2') {
	echo head();

	$c->getdata();
	$array = explode(',',$gpc->get('avfiletypes', none));
	$array2 = array();
	foreach ($array as $row) {
		$array2[] = '.'.$row;
	}
	$ft = implode('|',$array2);
	
	$c->updateconfig('avfiletypes',str, $ft);
	$c->updateconfig('avfilesize',int);
	$c->updateconfig('avwidth',int);
	$c->updateconfig('avheight',int);
	
	$c->savedata();

	ok('admin.php?action=settings&job=avupload');
}

elseif ($job == 'cron') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=cron2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>Zeitliche-Aufgaben-Einstellungen</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Scheduled Tasks in der Seite einbinden:<br><span class="stext">Wenn diese Option aktiviert ist, wird bei jedem Seitenaufruf geprüft ob Aufgaben zu erledigen sind. Sie können aus Performance-Gründen dieses jedoch auslagern, in dem Sie einen Cron-Job (Dienst) die Datei (<a href="cron.php" target="_blank">cron.php</a>) aufrufen lassen.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pccron" value="1"<?php echo iif($config['pccron'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximale Anzahl Aufgaben, die pro Aufruf ausgeführt werden:<br><font class="stext">Bei größeren Boards empfiehlt sich ein geringer Wert (1-2), bei kleineren Boards kann der Wert höher (3-5) gestellt werden. 0 = Alle ausführen!</font></td>
	   <td class="mbox" width="50%"><input type="text" name="pccron_maxjobs" value="<?php echo $config['pccron_maxjobs']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Aufgaben-Log-Datei nutzen:<br><font class="stext">Log-Datei kann <a href="admin.php?action=slog&job=l_cron" target="_blank">hier</a> eingesehen werden.</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pccron_uselog" value="1"<?php echo iif($config['pccron_uselog'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Scheduled Tasks-Report per E-Mail versenden:</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pccron_sendlog" value="1"<?php echo iif($config['pccron_sendlog'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Emailadresse für den Scheduled Tasks-Report:</td>
	   <td class="mbox" width="50%"><input type="text" name="pccron_sendlog_email" value="<?php echo $config['pccron_sendlog_email']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'cron2') {
	echo head();

	$c->getdata();
	$c->updateconfig('pccron',int);
	$c->updateconfig('pccron_maxjobs',int);
	$c->updateconfig('pccron_uselog',int);
	$c->updateconfig('pccron_sendlog',int);
	$c->updateconfig('pccron_sendlog_email',str);
	$c->savedata();

	ok('admin.php?action=settings&job=cron');
}
elseif ($job == 'general') {
	echo head();
	
	if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['PHP_SELF'])) {
		$furl = "http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	}
	else {
		$furl = "Konnte URL nicht analysieren";
	}
	
	$config = $gpc->prepare($config);
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=general2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>Allgemeine Foreneinstellungen</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Name der Seite:<br><font class="stext">Wird u.a. in Mails und Emails verwendet und sollte 64 Zeichen nicht überschreiten</font></td>
	   <td class="mbox" width="50%"><input type="text" name="fname" value="<?php echo $config['fname']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Kurze Beschreibung der Seite:<br><font class="stext">HTML ist möglich.</font></td>
	   <td class="mbox" width="50%"><input type="text" name="fdesc" value="<?php echo $config['fdesc']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">URL des Seite:<br><font class="stext">Url zum Ordner in dem die Dateien liegen (ohne / am Ende).<br>Vom Script ermittelte URL: <?php echo $furl; ?></font></td>
	   <td class="mbox" width="50%"><input type="text" name="furl" value="<?php echo $config['furl']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Pfad zum Forum:</font><br><font class="stext">Pfad zum Ordner in dem die Dateien liegen (ohne / am Ende).<br>Vom Script ermittelter Pfad: <?php echo str_replace('\\', '/', realpath('./')); ?></font></font></td>
	   <td class="mbox" width="50%"><input type="text" name="fpath" value="<?php echo $config['fpath']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Emailadresse des Forums:</font><br><font class="stext">Wird bei allen ausgehenden Emails verwendet.</font></td>
	   <td class="mbox" width="50%"><input type="text" name="forenmail" value="<?php echo $config['forenmail']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Benchmarkergebnisse und Debuginformationen ausgeben:</font><br><font class="stext">Nur in der Entwicklung und zum Debuggen notwendig. Sollte generell deaktiviert sein.</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="benchmarkresult" value="1"<?php echo iif($config['benchmarkresult'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'general2') {
	echo head();

	$c->getdata();
	$c->updateconfig('fname',str);
	$c->updateconfig('fdesc',str);
	$c->updateconfig('furl',str);
	$c->updateconfig('fpath',str);
	$c->updateconfig('forenmail',str);
	$c->updateconfig('benchmarkresult',int);
	$c->savedata();

	ok('admin.php?action=settings&job=general');
}
elseif ($job == 'sitestatus') {
	$obox = file_get_contents('data/offline.php');
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=sitestatus2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2">Viscacha ein- und ausschalten</td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Seite ausschalten:<br><font class="stext">Wenn Ihre Seite ausgeschaltet ist, bekommen Ihre Besucher die unten veränderbare Nachricht angezeigt, Administratoren können die Seite jedoch ganz normal nutzen!</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="foffline" value="1"<?php echo iif($config['foffline'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Offlinenachricht:<br><font class="stext">Dieser Text wird angezeigt, wenn die Seite ausgeschaltet ist.<br>HTML und PHP sind möglich!</font></td>
	   <td class="mbox" width="50%"><textarea class="texteditor" name="template" rows="5" cols="60"><?php echo $obox; ?></textarea></td> 
	  </tr>
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'sitestatus2') {
	echo head();

	$c->getdata();
	$c->updateconfig('foffline',int);
	$filesystem->file_put_contents('data/offline.php',$gpc->get('template', none));
	$c->savedata();

	ok('admin.php?action=settings&job=sitestatus');
}
elseif ($job == 'ajax_sitestatus') {
	$new = invert($config['foffline']);
	$c->getdata();
	$c->updateconfig('foffline', int, $new);
	$c->savedata();
	die(strval($new));
}
elseif ($job == 'datetime') {
	echo head();
	$config = $gpc->prepare($config);
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=datetime2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>Datum und Zeit</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Zeitzone des Forums:<br><font class="stext">Standard Zeitzone für das Forum ausgehen von der Zeitzone GMT!</font></td>
	   <td class="mbox" width="50%"><select name="timezone"> 
					<option selected value="<?php echo $config['timezone']; ?>">Zeitzone beibehalten (GMT <?php echo $config['timezone']; ?>)</option>
					<option value="-12">(GMT -12:00) Eniwetok, Kwajalein</option>
					<option value="-11">(GMT -11:00) Midway-Inseln, Samoa</option>
					<option value="-10">(GMT -10:00) Hawaii</option>
					<option value="-9">(GMT -09:00) Alaska</option>
					<option value="-8">(GMT -08:00) Tijuana, Lod Angeles, Seattle, Vancouver</option>
					<option value="-7">(GMT -07:00) Arizona, Denver, Salt Lake City, Calgary</option>
					<option value="-6">(GMT -06:00) Mexiko-Stadt, Saskatchewan, Zentralamerika</option>
					<option value="-5">(GMT -05:00)  Bogot&aacute;, Lima, Quito, Indiana (Ost), New York, Toronto</option>
					<option value="-4">(GMT -04:00) Caracas, La Paz, Montreal, Quebec, Santiago</option>
					<option value="-3.5">(GMT -03:30) Neufundland</option>
					<option value="-3">(GMT -03:00) Brasilia, Buenos Aires, Georgetown, Gr&ouml;nland</option>
					<option value="-2">(GMT -02:00) Mittelatlantik</option>
					<option value="-1">(GMT -01:00) Azoren, Kapverdische Inseln</option>
					<option value="0">(GMT) Casablance, Monrovia, Dublin, Edinburgh, Lissabon, London</option>
					<option value="+1">(GMT +01:00) Amsterdam, Berlin, Bern, Rom, Stockholm, Wien, Paris</option>
					<option value="+2">(GMT +02:00) Athen, Istanbul, Minsk, Kairo, Jerusalem</option>
					<option value="+3">(GMT +03:00) Bagdad, Moskau, Nairobi</option>
					<option value="+3.5">(GMT +03:30) Teheran</option>
					<option value="+4">(GMT +04:00) Muskat, Tiflis</option>
					<option value="+4.5">(GMT +04:30) Kabul</option>
					<option value="+5">(GMT +05:00) Islamabad</option>
					<option value="+5.5">(GMT +05:30) Kalkutta, Neu-Delhi</option>
					<option value="+5.75">(GMT +05:45) Katmandu</option>
					<option value="+6">(GMT +06:00) Almaty, Nowosibirsk, Dhaka</option>
					<option value="+6.5">(GMT +06:30) Rangun</option>
					<option value="+7">(GMT +07:00) Bangkok, Hanoi, Jakarta</option>
					<option value="+8">(GMT +08:00) Ulan Bator, Singapur, Peking, Hongkong</option>
					<option value="+9">(GMT +09:00) Irkutsk, Osaka, Sapporo, Tokyo, Seoul</option>
					<option value="+9.5">(GMT +09:30) Adelaide, Darwin</option>
					<option value="+10">(GMT +10:00) Brisbane, Canberra, Melbourne, Sydney, Wladiwostok</option>
					<option value="+11">(GMT +11:00) Salomonen, Neukaledonien</option>
					<option value="+12">(GMT +12:00) Auckland, Wellington, Fidschi, Kamtschatka</option>
				</select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">"Heute" und "Gestern" benutzen:</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="new_dformat4" value="1"<?php echo iif($config['new_dformat4'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'datetime2') {
	echo head();

	$c->getdata();
	$c->updateconfig('new_dformat4',int);
	$c->updateconfig('timezone',str);
	$c->savedata();

	ok('admin.php?action=settings&job=fgeneral');
}
elseif ($job == 'http') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=http2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2">Headers, Cookies &amp; GZIP</td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">GZip-Komprimierung aktivieren:<br><font class="stext">Die Seiten können mittels GZip zur schnelleren Übertragung komprimiert werden.</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="gzip" value="1"<?php echo iif($config['gzip'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">GZip-Komprimierungsstufe:<br><font class="stext">Wert muss zwischen 0 (keine) und 9 (maximal) liegen. Es wird ein Wert um 2-3 empfohlen!</font></td>
	   <td class="mbox" width="50%"><select size="1" name="gzcompression">
	   <?php 
	   	for($i=0;$i<10;$i++) {
	   		if ($i == $config['gzcompression']) {
	   			echo "<option value=\"$i\" selected>$i</option>";
	   		}
			else {
	   			echo "<option value=\"$i\">$i</option>";
			}
		}
    	?>
  		</select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Zwischenspeichern (Cachen) von Seiten im Browser verhindern:</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="nocache" value="1"<?php echo iif($config['nocache'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Prefix f&uuml;r Cookies:<br><font class="stext">Nur Buchstaben von a-z und _ benutzen!</font></td>
	   <td class="mbox" width="50%"><input type="text" size="10" name="cookie_prefix" value="<?php echo $config['cookie_prefix']; ?>"></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'http2') {
	echo head();

	$c->getdata();
	$c->updateconfig('gzip',int);
	$c->updateconfig('gzcompression',int);
	$c->updateconfig('nocache',int);
	$c->updateconfig('cookie_prefix',str);
	$c->savedata();

	ok('admin.php?action=settings&job=http');
}
elseif ($job == 'textprocessing') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=textprocessing2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>BB-Codes &amp; Textverarbeitung</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="70%">Texte zensieren:<br>
	   <font class="stext">Die zu zensierenden Wörter können <a href="admin.php?action=bbcodes&job=censor">hier</a> festgelegt werden!<br>
	   Die erweiterte Zensur Hiermit können Sie die Zensur treffsicherer machen findet auch Wörter dessen Buchstaben durch etwaige Zeichen getrennt sind.</font></td>
	   <td class="mbox" width="30%">
	   <input type="radio" name="censorstatus" value="0"<?php echo iif($config['censorstatus'] == 0,' checked'); ?>> Nicht zensieren<br>
	   <input type="radio" name="censorstatus" value="1"<?php echo iif($config['censorstatus'] == 1,' checked'); ?>> Normale Zensur<br>
	   <input type="radio" name="censorstatus" value="2"<?php echo iif($config['censorstatus'] == 2,' checked'); ?>> Erweiterte Zensur
	   </td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="70%">Glossareinträge markieren und Erklärung anzeigen:<br><font class="stext">Hiermit können Sie die <a href="admin.php?action=bbcodes&job=word">Glossareinträge</a> markieren und die passende Erklärung anzeigen.</font></td>
	   <td class="mbox" width="30%"><input type="checkbox" name="dictstatus" value="1"<?php echo iif($config['dictstatus'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="70%">Vokabeln ersetzen:<br><font class="stext">Sie können die <a href="admin.php?action=bbcodes&job=replace">Vokabeln</a> automatisch ersetzen lassen (jedoch in jedem Beitrag explizit wählbar).</font></td>
	   <td class="mbox" width="30%"><input type="checkbox" name="wordstatus" value="1"<?php echo iif($config['wordstatus'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="70%">Viele Zeilenumbruch kürzen:<br><font class="stext">Mehr als 3 Zeilenumbrüche könenn automatisch gekürzt werden.</font></td>
	   <td class="mbox" width="30%"><input type="checkbox" name="reduce_nl" value="1"<?php echo iif($config['reduce_nl'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="70%">Interpunktion verbessern:<br><font class="stext">Sie können mehr als 2 Fragezeichen, 2 Ausrufezeichen oder mehr als 4 Punkte automatisch kürzen lassen.</font></td>
	   <td class="mbox" width="30%"><input type="checkbox" name="reduce_endchars" value="1"<?php echo iif($config['reduce_endchars'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="70%">Durchgehende Großschreibung korrigieren:<br><font class="stext">Wenn der komplette Titel großgeschrieben ist, wird nur der erste Buchstabe eines Wortes so belassen.<br />Beispiel: "BRAUCHE HILFE!" wird zu "Brauche Hilfe!".</font></td>
	   <td class="mbox" width="30%"><input type="checkbox" name="topicuppercase" value="1"<?php echo iif($config['topicuppercase'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="70%">Wordwrap: Zu lange Wörter trennen:<br><font class="stext">Sie können zu lange Wörter, die das Design zerstören, automatisch nach einer bestimmten Anzahl an Zeichen trennen lassen.</font></td>
	   <td class="mbox" width="30%"><input type="checkbox" name="wordwrap" value="1"<?php echo iif($config['wordwrap'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="70%">Wordwrap: Anzahl der Zeichen nach denen getrennt wird:</font></td>
	   <td class="mbox" width="30%"><input type="text" name="maxwordlength" value="<?php echo $config['maxwordlength']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="70%">Wordwrap: Zeichen oder HTML-Tag mit dem zu lange Wörter getrennt werden<br><font class="stext">Zum Beispiel ein Zeilenumbruch mittels &lt;br /&gt; oder ein Strich (-).</font></td>
	   <td class="mbox" width="30%"><input type="text" name="maxwordlengthchar" value="<?php echo $config['maxwordlengthchar']; ?>" size="8"></td> 
	  </tr>
  	  <tr> 
	   <td class="mbox" width="70%">URL-Wordwrap: Zu lange URLs automatisch kürzen:<br><font class="stext">Zu lange URLs können automatisch gekürzt werden, ohne dass der Link beschädigt wird.</font></td>
	   <td class="mbox" width="30%"><input type="checkbox" name="reduce_url" value="1"<?php echo iif($config['reduce_url'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="70%">URL-Wordwrap: Anzahl der Zeichen nach denen getrennt wird:</font></td>
	   <td class="mbox" width="30%"><input type="text" name="maxurllength" value="<?php echo $config['maxurllength']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="70%">URL-Wordwrap: Zeichen o.ä. das den Start- und Endteil der zu langen URL trennt:</font></td>
	   <td class="mbox" width="30%"><input type="text" name="maxurltrenner" value="<?php echo $config['maxurltrenner']; ?>" size="8"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="70%">Dezimalstellen nach dem Komma:</td>
	   <td class="mbox" width="30%"><input type="text" name="decimals" value="<?php echo $config['decimals']; ?>" size="8"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="70%">Anzahl Smileys die beim posten in einer Reihe angezeigt werden:</font></td>
	   <td class="mbox" width="30%"><input type="text" name="smileysperrow" value="<?php echo $config['smileysperrow']; ?>" size="8"></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'textprocessing2') {
	echo head();

	$c->getdata();
	$c->updateconfig('censorstatus',int);
	$c->updateconfig('decimals',int);
	$c->updateconfig('dictstatus',int);
	$c->updateconfig('wordstatus',int);
	$c->updateconfig('reduce_nl',int);
	$c->updateconfig('reduce_endchars',int);
	$c->updateconfig('wordwrap',int);
	$c->updateconfig('maxwordlength',int);
	$c->updateconfig('maxwordlengthchar',str);
	$c->updateconfig('reduce_url',int);
	$c->updateconfig('maxurllength',int);
	$c->updateconfig('maxurltrenner',str);
	$c->updateconfig('smileysperrow',int);
	$c->updateconfig('topicuppercase',int);
	$c->savedata();

	ok('admin.php?action=settings&job=textprocessing');
}
elseif ($job == 'syndication') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=syndication2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>Content Syndication (Javascript, RSS, ...)</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Newsfeed der Forenbeiträge aktivieren:<br /><span class="stext">Die Newsfeed-Formate können <a href="admin.php?action=misc&amp;job=feedcreator">hier</a> verwaltet werden.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="syndication" value="1"<?php echo iif($config['syndication'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximale Anzahl der Zeichen der Texte:</td>
	   <td class="mbox" width="50%"><input type="text" name="rsschars" value="<?php echo $config['rsschars']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Zeit in Minuten die die Newsfeeds gecached werden:</td>
	   <td class="mbox" width="50%"><input type="text" name="rssttl" value="<?php echo $config['rssttl']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Icon der Newsfeeds:<br /><span class="stext">Größe: 16x16 Pixel; Format: gif, jp(e)g</span></td>
	   <td class="mbox" width="50%"><input type="text" name="syndication_klipfolio_icon" value="<?php echo $config['syndication_klipfolio_icon']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Banner des Klipfolio-Newsfeeds:<br /><span class="stext">Größe: 234x60 Pixel; Format: gif, jp(e)g</span></td>
	   <td class="mbox" width="50%"><input type="text" name="syndication_klipfolio_banner" value="<?php echo $config['syndication_klipfolio_banner']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'syndication2') {
	echo head();

	$c->getdata();
	$c->updateconfig('syndication',int);
	$c->updateconfig('syndication_klipfolio_banner',str);
	$c->updateconfig('syndication_klipfolio_icon',str);
	$c->updateconfig('rssttl',int);
	$c->updateconfig('rsschars',int);
	$c->savedata();

	ok('admin.php?action=settings&job=syndication');
}
elseif ($job == 'version') {
	echo head();
	$comp = @get_remote('http://version.viscacha.org/compare/?version='.base64_encode($config['version']));
	$version = @get_remote('http://version.viscacha.org/version');
	$news = @get_remote('http://version.viscacha.org/news');
	if ($comp == -1) {
		$res = "Ihr Viscacha ist <strong>nicht aktuell</strong>. Die aktuelle Version ist {$version}!";
	}
	elseif ($comp == 1) {
		$res = "Ihr Viscach ist eine, noch nicht freigegebene, Testversion.";
	}
	elseif ($comp == 0) {
		$res = "Ihr Viscacha ist aktuell!";
	}
	else {
		$res = "Fehler bei der Datensynchronisation!";
	}
	if (!$news) {
		$news = 'Konnte keine Verbindung zum Server aufbauen.';
	}
	if (!$version) {
		$version = 'KoKeine Verbindung';
	}
	if (!$comp) {
		$comp = 'Konnte keine Verbindung zum Server aufbauen.';
	}
	?>
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="4">Version Check</td>
	  </tr>
	  <tr> 
	   <td class="mmbox" width="25%">Ihre Version:</td>
	   <td class="mbox" width="25%"><?php echo $config['version']; ?></td>
	   <td class="mmbox" width="25%">Aktuelle Version:</td> 
	   <td class="mbox" width="25%"><?php echo $version; ?></td>
	  </tr>
	  <tr> 
	   <td class="mbox" colspan="4"><?php echo $res; ?></td>
	  </tr>
	 </table><br />
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox">Latest Announcement</td> 
	  </tr>
	  <tr> 
	   <td class="mbox"><?php echo $news; ?></td>
	  </tr>
	 </table>
	<?php
	echo foot();
}
elseif ($job == 'custom') {
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}settings ORDER BY name");
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=custom2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="3"><b>Custom Settings</b></td>
	  </tr>
	<?php
	if ($db->num_rows() > 0) {
		while ($row = $db->fetch_assoc($result)) {
			call_user_func('custom_'.$row['type'], $row);
		}
	}
	else {
	?>
	  <tr> 
	   <td class="mbox" colspan="3" align="center">No custom settings added. You can add a new setting <a href="admin.php?action=settings&job=new">here</a>.</td>
	  </tr>
	<?php
	}
	?>
	  <tr> 
	   <td class="ubox" colspan="3" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 	
	<?php
	echo foot();
}
elseif ($job == 'custom2') {
	echo head();

	$c->getdata();
	
	$result = $db->query("SELECT * FROM {$db->pre}settings ORDER BY name");
	while ($row = $db->fetch_assoc($result)) {
		$c->updateconfig($row['name'], str);
	}
	
	$c->savedata();

	ok('admin.php?action=settings&job=custom');
}
elseif ($job == 'delete') {
	$name = $gpc->get('name', str);
	$db->query("DELETE FROM {$db->pre}settings WHERE name = '{$name}' LIMIT 1");
	$upd = $db->affected_rows();
	if ($upd == 1) {
		$c->getdata();
		$c->delete($name);
		$c->savedata();
		ok('admin.php?action=settings&job=custom','Custom Setting deleted!');
	}
	else {
		error('admin.php?action=settings&job=custom','Custom setting not available or belongs to core settings.');
	}
}
elseif ($job == 'new') {
	echo head()
	?>
<form action="admin.php?action=settings&job=new2" method="post">
<table border="0" align="center" class="border">
<tr>
<td class="obox" colspan="2">Add Setting</td>
</tr>
<tr>
<td class="mbox" width="40%">Setting Title</td>
<td class="mbox" width="60%"><input type="text" name="title" value="" size="40"></td>
</tr>
<tr>
<td class="mbox" width="40%">Description</td>
<td class="mbox" width="60%"><textarea name="description" rows="4" cols="50"></textarea></td>
</tr>
<tr>
<td class="mbox" width="40%">Setting Name<br /><span class="stext">This will be the name of the setting as used in scripts and templates. If the name is "<code>value</code>", the variable is <code>$config['value']</code></span></td>
<td class="mbox" width="60%"><input type="text" name="name" value="" size="40"></td>
</tr>
<tr>
<td class="mbox" width="40%">Setting Type</td>
<td class="mbox" width="60%">
<select name="type">
<option value="select">Select</option>
<option value="checkbox">Checkbox</option>
<option value="text">Text (one line)</option>
<option selected="selected" value="textarea">Textarea</option>
</select>
</td>
</tr>
<tr>
<td class="mbox" width="40%">Setting Type Values<br />
<span class="stext">
Only for Select-Fields.<br />
<strong>Format:</strong> (each entry in a new line)<br />
<code>value=title</code><br />
<code>value</code> is a value which can only contain letters, numbers and underscores.<br />
<code>title</code> is a one line value shown in the select box.<br />
</span></td>
<td class="mbox" width="60%"><textarea name="typevalue" rows="6" cols="50"></textarea></td>
</tr>
<tr>
<td class="mbox" width="40%">(Standard-)Value<br /><span class="stext">Can be changed later. If it is a select-box then this value has to be one of the <code>value</code>'s. Can not be changed if it is a checkbox.</span></td>
<td class="mbox" width="60%"><input type="text" name="value" value="" size="40"></td>
</tr>
<tr><td class="ubox" colspan="2" align="center"><input type="submit" value="Add Setting"></td></tr>
</table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'new2') {
	echo head();
	$title = $gpc->get('title', str);
	$desc = $gpc->get('description', str);
	$name = $gpc->get('name', str);
	$type = $gpc->get('type', str);
	$typevalue = $gpc->get('typevalue', none);
	$value = $gpc->get('value', str);
	
	if (isset($config[$name]) || strlen($name) < 3 || strlen($name) > 120) {
		error('admin.php?action=settings&job=custom','Name already exists.');
	}
	if ($type != 'checkbox' && $type != 'text' && $type != 'textarea' && $type != 'select') {
		error('admin.php?action=settings&job=custom','Invalid type.');
	}
	if ($type == 'select') {
		$typevalue = str_replace("\r\n", "\n", trim($typevalue));
		$typevalue = str_replace("\r", "\n", $typevalue);
		$arr_value = prepare_custom($typevalue);
		$typevalue = $gpc->save_str($typevalue);
		if (empty($arr_value[$value])) {
			error('admin.php?action=settings&job=custom','Value is not given in Setting Type Values.');
		}
	}
	else {
		$typevalue = '';
	}
	
	$db->query("
INSERT INTO {$db->pre}settings (name, title, description, type, optionscode, value) 
VALUES ('{$name}', '{$title}', '{$desc}', '{$type}', '{$typevalue}', '{$value}')
");
	
	$c->getdata();
	$c->updateconfig($name, str, $value);
	$c->savedata();
	
	ok('admin.php?action=settings&job=custom', 'Setting inserted!');
}
else {
	echo head();
	?>
<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
 <tr> 
  <td class="obox" colspan="2"><b>Foreneinstellungen</b></td>
 </tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=sitestatus">Viscacha On- oder Offline schalten</a>
 </td><td>
  <span class="stext">Hier können Sie zu Wartungs- oder Updatearbeiten das System für Nicht-Administratoren temporär deaktivieren.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=general">Grundeinstellungen</a>
 </td><td>
  <span class="stext">Grundlegenden Einstellungen wie Adressen und Namen ändern.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=db">Datenbank</a>
 </td><td>
  <span class="stext">Datenbankkonfiguration: Host, User, Passwort, Datenbank, System etc.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=ftp">FTP</a>
 </td><td>
  <span class="stext">FTP-Zugangsdaten: Host, User, Passwort für den Zugriff auf FTP, BackUps und Dateioperationen.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=datetime">Datum- und Zeit</a>
 </td><td>
  <span class="stext">Format von Datums- und Zeitausgaben, Zeitzone und ähnliches.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=cron">Scheduled Tasks</a>
 </td><td>
  <span class="stext">Einstellungen die "<a href="admin.php?action=cron&job=manage">Scheduled Tasks</a>" betreffend.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=avupload">Profilbilder &amp; Avatare</a>
 </td><td>
  <span class="stext">Die Bilder der Benutzer können beschränkt werden.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=attupload">Beitragsanhänge</a>
 </td><td>
  <span class="stext">Beschränkungen und Einstellungen bezüglich der beitragsanhänge.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=textprocessing">BB-Codes &amp; Textverarbeitung</a>
 </td><td>
  <span class="stext">Der BB-Code-Parser kann konfiguriert werden (Wordwrap, Interpunktion, Zensur ...)</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=syndication">Syndication</a>
 </td><td>
  <span class="stext">Generelle Einstellungen die <a href="admin.php?action=misc&job=feedcreator">selbsterstellten Newsfeeds</a> betreffend (Javascript, RSS, Atom, Klipfolio).</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=jabber">Jabber</a>
 </td><td>
  <span class="stext">Konfiguration des Jabber-Accounts und des Jabber-Verdands.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=spellcheck">Spellcheck</a>
 </td><td>
  <span class="stext">Einstellungen die <a href="admin.php?action=misc&job=spellcheck">Rechtschreibprüfung</a> betreffend.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=register">Registrierung</a>
 </td><td>
  <span class="stext">Registrierungsablauf, <a href="admin.php?action=misc&job=captcha">CAPTCHA-Verifikationa</a> und Forenregeln.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=email">E-Mails</a>
 </td><td>
  <span class="stext">E-Mail-Versand (PHP, SMTP, Sendmail).</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=lang">Internationalisierung &amp; Sprachen</a>
 </td><td>
  <span class="stext">Internationalisierung (<a href="admin.php?action=language&job=manage">Sprachen</a> und Zeichensätze)</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=profile">Profil (editieren &amp; ansehen)</a>
 </td><td>
  <span class="stext">Einstellungen die Profile betreffend. Mindestlängen, vCards etc.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=signature">Signaturen</a>
 </td><td>
  <span class="stext">Die Signaturen betreffende Einstellungen, wie BB-Code Beschränkungen.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=posts">Beiträge &amp; Themen</a>
 </td><td>
  <span class="stext">Mindestlängen und Maximallängen, PDF-Ausgabe, Editieren von Beiträgen und andere Einstellungen die Beiträge und Themen betreffend.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=search">Suche</a>
 </td><td>
  <span class="stext">Suchergebnisse und Sucheinstellungen.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=server">PHP, Webserver und Dateisystem</a>
 </td><td>
  <span class="stext">Einstellungen rund um die Websever-Installation (.htaccess), PHP und die Dateien auf dem Server.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=http">Headers, Cookies &amp; GZIP</a>
 </td><td>
  <span class="stext">Cookies, Seitenkomprimierung und HTTP-Header.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=session">Sessionsystem</a>
 </td><td>
  <span class="stext">Floodsperre und Sitzungen (Sessions) im Forum.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=boardcat">Foren &amp; Kategorien</a>
 </td><td>
  <span class="stext">Foren, Unterforen, Statistiken und Kategorien.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=user">Mitglieder- &amp; Teamliste</a>
 </td><td>
  <span class="stext">Einstellungen die Mitgliederübersicht und der Teamliste betreffend.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=pm">Private Nachrichten</a>
 </td><td>
  <span class="stext">Konfiguration die privaten Nachrichten betreffend.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=cmsp">CMS &amp; Portal</a>
 </td><td>
  <span class="stext">Portal, Startseite und Seitenverwaltung.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=custom">Custom Settings</a>
 </td><td>
  <span class="stext">Eigene, von Ihnen hinzugefügte, Einstellungen.</span>
 </td></tr>
</table>
	<?php
	echo foot();
}

function custom_select($arr) {
	global $config;
	$val = prepare_custom($arr['optionscode']);
?>
<tr> 
 <td class="mbox" width="45%"><?php echo $arr['title']; ?><br /><span class="stext"><?php echo $arr['description']; ?></span></td>
 <td class="mbox" width="45%">
 <select name="<?php echo $arr['name']; ?>">
 <?php foreach ($val as $key => $value) { ?>
  <option value="<?php echo $key; ?>"<?php echo iif($config[$arr['name']] == $key, ' selected="selected"'); ?>><?php echo $value; ?></option>
 <?php } ?>
 </select>
 </td>
 <td class="mbox" width="10%"><a href="admin.php?action=settings&job=delete&name=<?php echo $arr['name']; ?>">Delete Setting</a></td>
</tr>
<?php
}
function custom_checkbox($arr) {
	global $config;
?>
<tr> 
 <td class="mbox" width="45%"><?php echo $arr['title']; ?><br /><span class="stext"><?php echo $arr['description']; ?></span></td>
 <td class="mbox" width="45%"><input type="checkbox" name="<?php echo $arr['name']; ?>" value="<?php echo $config[$arr['name']]; ?>"<?php echo iif($config[$arr['name']],' checked="checked"'); ?> /></td>
 <td class="mbox" width="10%"><a href="admin.php?action=settings&job=delete&name=<?php echo $arr['name']; ?>">Delete Setting</a></td>
</tr>
<?php
}
function custom_text($arr) {
	global $config;
?>
<tr> 
 <td class="mbox" width="45%"><?php echo $arr['title']; ?><br /><span class="stext"><?php echo $arr['description']; ?></span></td>
 <td class="mbox" width="45%"><input type="text" name="<?php echo $arr['name']; ?>" value="<?php echo $config[$arr['name']]; ?>" /></td>
 <td class="mbox" width="10%"><a href="admin.php?action=settings&job=delete&name=<?php echo $arr['name']; ?>">Delete Setting</a></td>
</tr>
<?php
}
function custom_textarea($arr) {
	global $config;
?>
<tr> 
 <td class="mbox" width="45%"><?php echo $arr['title']; ?><br /><span class="stext"><?php echo $arr['description']; ?></span></td>
 <td class="mbox" width="45%"><textarea cols="50" rows="4" name="<?php echo $arr['name']; ?>"><?php echo $config[$arr['name']]; ?></textarea></td>
 <td class="mbox" width="10%"><a href="admin.php?action=settings&job=delete&name=<?php echo $arr['name']; ?>">Delete Setting</a></td>
</tr>
<?php
}
function prepare_custom($str) {
	$str = trim($str);
	$explode = explode("\n", $str);
	$arr = array();
	foreach ($explode as $val) {
		$dat = explode('=', $val);
		if (count($dat) > 2) {
			$k = array_shift($dat);
			$dat = implode('=', $dat);
			$arr[$k] = $dat;
		}
		elseif (count($dat) == 2) {
			$arr[$dat[0]] = $dat[1];
		}
		else {
			error();
		}
	}
	return $arr;
}
?>
