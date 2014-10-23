<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "filetypes.php") die('Error: Hacking Attempt');

if ($job == 'add') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=filetypes&job=add2">
 <table class="border">
  <tr> 
   <td class="obox" colspan=2>Dateityp hinzufügen</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Endung(en getrennt mit Komma):</font></td>
   <td class="mbox" width="50%"><input type="text" name="extension" size="50" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Relevante Programme:</font><br><font class="stext">Optional: Eine Auswahl relevanter Programme, mit denen man eine solche Datei erstellen, verwalten ... kann.</font></td>
   <td class="mbox" width="50%"><input type="text" name="program" size="50" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Beschreibung:</font><br><font class="stext">HTML ist möglich!</font></td>
   <td class="mbox" width="50%"><textarea name="desctxt" rows="5" cols="50"></textarea></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Icon-Dateiname:</font><br><font class="stext">Optional. Angeben ohne Endung.</font></td>
   <td class="mbox" width="50%"><input type="text" name="icon" size="50" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Mimetype:</font></td>
   <td class="mbox" width="50%"><input type="text" name="mimetype" size="50" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Art des Versands:</font></td>
   <td class="mbox" width="50%">
   <select name="stream">
   <option value="attachment">Attachment (zum Download anbieten)</option>
   <option value="inline">Inline (im Browser öffnen)</option>
   </select>
   </td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'add2') {
	echo head();

	$extension = $gpc->get('extension', str);
	$program = $gpc->get('program', str);
	$desctxt = $db->escape_string($gpc->get('desctxt', none));
	$icon = $gpc->get('icon', str);
	$mimetype = $gpc->get('mimetype', str);
	$stream = $gpc->get('stream', str);
	
    if ($extension{0} == '.') {
        $extension = substr($extension, 1);
    }
	$error = array();
	if (strlen($stream) < 2 && strlen($stream) > 10) {
		$error[] = 'Keine gültige Endung';
	}
	if ($stream != 'inline' && $stream != 'attachment') {
		$error[] = 'Keine gültige Endung';
	}
	if (count($error) > 0) {
		error('admin.php?action=filetypes&job=manage', 'Keine gültige Endung');
	}
	else {
    	if (!empty($mimetype)) {
    	    $mime = ", mimetype";
    	    $mime2 = ", '".$mimetype."'";
    	}
    	else {
    	    $mime = '';
    	    $mime2 = '';
    	}
		$db->query("INSERT INTO {$db->pre}filetypes (extension, program, desctxt, stream, icon".$mime.") VALUES ('".$extension."', '".$program."', '".$desctxt."', '".$stream."', '".$icon."'".$mime2.")",__LINE__,__FILE__);
		ok('admin.php?action=filetypes&job=manage', 'Dateityp wurde geändert');
	}
}
elseif ($job == 'edit') {
	echo head();
	if (!$_GET['id']) {
		error('<li>Es wurde keine gültige ID angegeben</li>','admin.php?action=filetypes&job=manage');
	}
	$result = $db->query('SELECT * FROM '.$db->pre.'filetypes WHERE id = '.$_GET['id']);
	$row = $gpc->prepare($db->fetch_assoc($result));
	?>
<form name="form" method="post" action="admin.php?action=filetypes&job=edit2&id=<?php echo $_GET['id']; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Dateityp ändern</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Endung(en getrennt mit Komma):</font></td>
   <td class="mbox" width="50%"><input type="text" name="extension" size="50" value="<?php echo $row['extension']; ?>"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Relevante Programme:</font><br><font class="stext">Optional: Eine Auswahl relevanter Programme, mit denen man eine solche Datei erstellen, verwalten ... kann.</font></td>
   <td class="mbox" width="50%"><input type="text" name="program" size="50" value="<?php echo $row['program']; ?>"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Beschreibung:</font><br><font class="stext">HTML ist möglich!</font></td>
   <td class="mbox" width="50%"><textarea name="desctxt" rows="5" cols="50"><?php echo $row['desctxt']; ?></textarea></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Icon-Dateiname:</font><br><font class="stext">Optional. Angeben ohne Endung.</font></td>
   <td class="mbox" width="50%"><input type="text" name="icon" size="50" value="<?php echo $row['icon']; ?>"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Mimetype:</font></td>
   <td class="mbox" width="50%"><input type="text" name="mimetype" size="50" value="<?php echo $row['mimetype']; ?>"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Art des Versands:</font></td>
   <td class="mbox" width="50%">
   <select name="stream">
   <option value="inline"<?php echo iif($row['stream'] == 'inline', ' selected="selected"'); ?>>Inline (im Browser öffnen)</option>
   <option value="attachment"<?php echo iif($row['stream'] == 'attachment', ' selected="selected"'); ?>>Attachment (zum Download anbieten)</option>
   </select>
   </td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'edit2') {
	echo head();
	
	$extension = $gpc->get('extension', str);
	$program = $gpc->get('program', str);
	$desctxt = $db->escape_string($gpc->get('desctxt', none));
	$icon = $gpc->get('icon', str);
	$mimetype = $gpc->get('mimetype', str);
	$stream = $gpc->get('stream', str);
	
    if ($extension{0} == '.') {
        $extension = substr($extension, 1);
    }
	$error = array();
	if (strlen($stream) < 2 && strlen($stream) > 10) {
		$error[] = 'Keine gültige Endung';
	}
	if ($stream != 'inline' && $stream != 'attachment') {
		$error[] = 'Keine gültige Endung';
	}
	if (!empty($mimetype)) {
	    $mime = ", mimetype = '".$mimetype."'";
	}
	else {
	    $mime = '';
	}
	if (count($error) > 0) {
		error('admin.php?action=filetypes&job=manage', 'Keine gültige Endung');
	}
	else {
		$db->query("UPDATE ".$db->pre."filetypes SET extension = '".$extension."', program = '".$program."', desctxt = '".$desctxt."', stream = '".$stream."', icon = '".$icon."'".$mime." WHERE id = ".$_GET['id']);
		ok('admin.php?action=filetypes&job=manage', 'Dateityp wurde geändert');
	}
}
elseif ($job == 'manage') {
	echo head();
	$tpl = new tpl();
	$result = $db->query('SELECT * FROM '.$db->pre.'filetypes ORDER BY extension');
	?>
	<form name="form" method="post" action="admin.php?action=filetypes&job=delete">
	 <table class="border">
	  <tr> 
	   <td class="obox" colspan="6">Dateitypen verwalten</td>
	  </tr>
	  <tr> 
	   <td class="ubox" width="5%"><font class="mtext">Löschen</font></td>
	   <td class="ubox" width="5%"><font class="mtext">Icon</font></td>
	   <td class="ubox" width="10%"><font class="mtext">Dateityp</font></td>
	   <td class="ubox" width="25%"><font class="mtext">Relevante Programme</font></td> 
	   <td class="ubox" width="15%"><font class="mtext">Mimetype</font></td>
	   <td class="ubox" width="40%"><font class="mtext">Beschreibung</font></td> 
	  </tr>
	<?php
	while ($row = $gpc->prepare($db->fetch_assoc($result))) {
		if (@strpos($row['desctxt'], ' ', 60) !== FALSE) {
		    $row['desctxt'] = substr($row['desctxt'],0,strpos($row['desctxt'], ' ', 60)).' ...';
		}
		?>
		<tr> 
		   <td class="mbox" width="5%"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>" /></td>
		   <td class="mbox" width="5%"><span class="stext"><img src="<?php echo $tpl->img('filetypes/'.$row['icon']); ?>" alt="" /></span></td>
		   <td class="mbox" width="10%"><span class="mtext"><a href="admin.php?action=filetypes&job=edit&id=<?php echo $row['id']; ?>" title="Editieren"><?php echo $row['extension']; ?></a></span></td>
		   <td class="mbox" width="25%"><span class="stext"><?php echo $row['program']; ?></span></td>
		   <td class="mbox" width="15%"><span class="stext"><?php echo $row['mimetype']; ?></span></td>
		   <td class="mbox" width="40%"><span class="stext"><?php echo $row['desctxt']; ?></span></td> 
		</tr>
	<?php } ?>
	  <tr> 
	   <td class="ubox" width="100%" colspan="6" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'delete') {
	echo head();
	$delete = $gpc->get('delete', arr_int);
	if (count($delete) > 0) {
		$deleteids = array();
		foreach ($delete as $did) {
			$deleteids[] = 'id = '.$did; 
		}
		$db->query('DELETE FROM '.$db->pre.'filetypes WHERE '.implode(' OR ',$deleteids));
		$anz = $db->affected_rows();	
		ok('admin.php?action=filetypes&job=manage', $anz.' Einträge gelöscht');
	}
	else {
		error('admin.php?action=filetypes&job=manage', 'Keine Eingabe gemacht');
	}
}
?>
