<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "members.php") die('Error: Hacking Attempt');

if ($job == 'newsletter') {
	echo head();
?>
<form name="form" method="post" action="admin.php?action=members&job=newsletter2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2"><b><span style="float: right;">[<a href="admin.php?action=members&job=newsletter_archive">Newsletter-Archiv</a>]</span>Newsletter verschicken</b></td>
  </tr>
  <tr> 
	<td class="mbox" width="50%">Empfänger:</td>
	<td class="mbox" width="50%"><select size="1" name="int1"><option value="1">Alle</option><option value="2" selected>Nur Mitglieder</option><option value="3">Nur Gäste</option></select></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Titel:</td>
   <td class="mbox" width="50%"><input type="text" name="temp1" size="60"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Text:</td>
   <td class="mbox" width="50%"><textarea name="temp2" rows="8" cols="60"></textarea></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Anzahl Emails die pro Staffel verschickt werden:</td>
   <td class="mbox" width="50%"><input type="text" name="int2" size="10" value="100"></td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
  </tr>
 </table>
</form>
<?php
	echo foot();
}
elseif ($job == 'newsletter2') {
	
	$int1 = $gpc->get('int1', int);
	
	if ($int1 == 1) {
		$emails = array();
		$result = $db->query('SELECT mail FROM '.$db->pre.'user');
		while ($row = $db->fetch_array($result)) {
			$emails[] = $row[0];
		}
		$result = $db->query('SELECT email FROM '.$db->pre.'replies WHERE email != ""');
		while ($row = $db->fetch_array($result)) {
			$emails[] = $row[0];
		}
	}
	elseif ($int1 == 2) {
		$emails = array();
		$result = $db->query('SELECT mail FROM '.$db->pre.'user');
		while ($row = $db->fetch_array($result)) {
			$emails[] = $row[0];
		}
	}
	elseif ($int1 == 3) {
		$emails = array();
		$result = $db->query('SELECT email FROM '.$db->pre.'replies WHERE email != ""');
		while ($row = $db->fetch_array($result)) {
			$emails[] = $row[0];
		}
	}
	$emails = array_unique($emails);
	$anz = count($emails);
	if ($anz == 0) {
		echo head();
		error('admin.php?action=members&job=newsletter', 'Keine E-Mail-Adressen gefunden!');
	}
	$int2 = $gpc->get('int2', int, 100);
	$steps = ceil($anz/$int2);
	
	$db->query('INSERT INTO '.$db->pre.'newsletter (receiver, title, content, time) VALUES ("'.$int1.'","'.$gpc->get('temp1', str).'","'.$gpc->get('temp2', str).'","'.time().'")');
	$lid = $db->affected_rows();
	
	$scache = new scache('newsletter_session');
	$scache->exportdata($emails);
	$htmlhead .= '<meta http-equiv="refresh" content="2; url=admin.php?action=members&job=newsletter3&id='.$lid.'&int2='.$int2.'&page=1">';
	echo head();
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox"><b>Schritt 1 von <?php echo $steps+1; ?></b></td>
  </tr>
  <tr> 
   <td class="mbox">Die Daten wurden gespeichert<br>Die Emails werden nun in Staffeln versandt.</td>
  </tr>
 </table>	
<?php
	echo foot();
}
elseif ($job == 'newsletter3') {
	$scache = new scache('newsletter_session');
	$emails = $scache->importdata();
	
	$int2 = $gpc->get('int2', int, 100);
	$page = $gpc->get('page', int, 1);

	$anz = count($emails);
	$steps = ceil($anz/$int2);
	
	$result = $db->query('SELECT * FROM '.$db->pre.'newsletter WHERE id = '.$gpc->get('id', int));
	$row = $db->fetch_assoc($result);
	
	$split = array_chunk($emails, $int2);
	$minus = $page-1;
	$plus = $page+1;
	
	$i = 0;
	if (!isset($split[$minus]) || !is_array($split[$minus])) {
		echo head();
		error('admin.php?action=members&job=newsletter', 'Keine E-Mail-Adressen zu dieser Staffel gefunden!');
	}
	foreach ($split[$minus] as $mail) {
		$i++;
		$comment = $row['content'];
		$to = array('0' => array('mail' => $mail));
		$topic = $row['title'];
		$from = array();
		xmail($to, $from, $topic, $comment);
	}
	
	$ready = $minus*$int2+$i;
	
	if ($page == $steps) {
		$htmlhead .= '<meta http-equiv="refresh" content="2; url=admin.php?action=members&job=newsletter">';
		$scache->deletedata();
		echo head();
?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox"><b>Staffel <?php echo $page+1; ?> von <?php echo $steps+1; ?>...</b></td>
  </tr>
  <tr> 
   <td class="mbox">E-Mail Staffel <?php echo $page; ?> versandt.<br>Insgesamt <?php echo $ready; ?> E-Mails gesandt!<br><br>Alle Emails erfolgreich versandt! <a href="admin.php?action=members&job=newsletter">Leite zur Verwaltung zurück.</a></td>
  </tr>
 </table>	
<?php
	}
	else {
		$htmlhead .= '<meta http-equiv="refresh" content="5; url=admin.php?action=members&job=newsletter3&id='.$gpc->get('id', int).'&int2='.$gpc->get('int2', int).'&page='.$plus.'">';
		echo head();
?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox"><b>Staffel <?php echo $page+1; ?> von <?php echo $steps+1; ?>...</b></td>
  </tr>
  <tr> 
   <td class="mbox">E-Mail Staffel <?php echo $page; ?> versandt.<br>Insgesamt <?php echo $ready; ?> E-Mails gesandt!</td>
  </tr>
 </table>	
<?php
	}
	echo foot();
}
elseif ($job == 'newsletter_archive') {
	$result = $db->query('SELECT id, title, receiver, time FROM '.$db->pre.'newsletter ORDER BY time');
	echo head();
	$receiver = array('1' => 'Alle','2' => 'Nur Mitglieder','3' => 'Nur Gäste');
?>
<form name="form" method="post" action="admin.php?action=members&job=newsletter_delete">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan=4><b>Newsletter-Archiv</b></td>
  </tr>
  <tr> 
   <td class="ubox">Del</td>
   <td class="ubox">Betreff</td>
   <td class="ubox">Gesendet</td> 
   <td class="ubox">An</td> 
  </tr>
<?php while ($row = $db->fetch_assoc($result)) { ?>
  <tr>
   <td class="mbox"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td> 
   <td class="mbox"><a href="admin.php?action=members&job=newsletter_view&id=<?php echo $row['id']; ?>"><?php echo $row['title']; ?></a></td>
   <td class="mbox"><?php echo date('d.m.Y, H:i', $row['time']); ?></td>
   <td class="mbox"><?php echo $receiver[$row['receiver']]; ?></td>
  </tr>
<?php } ?>
  <tr> 
   <td class="ubox" colspan=4 align="center"><input type="submit" name="Submit" value="Löschen"></td> 
  </tr>
 </table>
</form> 
<?php
	echo foot();
}
elseif ($job == 'newsletter_view') {
	$result = $db->query('SELECT * FROM '.$db->pre.'newsletter WHERE id = '.$gpc->get('id', int));
	$row = $db->fetch_assoc($result);
	echo head();
	$receiver = array('1' => 'Alle','2' => 'Nur Mitglieder','3' => 'Nur Gäste');
?>
<form name="form" method="post" action="admin.php?action=members&job=newsletter_delete">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan=2><b>Newsletter-Archiv: Detail-Ansicht</b></td>
  </tr>
  <tr>
   <td class="mbox">Titel:</td> 
   <td class="mbox"><?php echo $row['title']; ?></td>
  </tr>
  <tr>
   <td class="mbox">Gesendet am:</td> 
   <td class="mbox"><?php echo date('d.m.Y, H:i', $row['time']); ?></td>
  </tr>
  <tr>
   <td class="mbox">Empfänger:</td> 
   <td class="mbox"><?php echo $receiver[$row['receiver']]; ?></td>
  </tr>
  <tr> 
   <td class="ubox" colspan=2>Newsletter-Text</td>
  </tr>
  <tr>
   <td class="mbox" colspan=2><pre><?php echo $row['content']; ?></pre></td>
  </tr>
  <tr> 
   <td class="ubox" colspan=2 align="center"><input type="hidden" name="delete[]" value="<?php echo $row['id']; ?>"><input type="submit" name="Submit" value="Löschen"></td> 
  </tr>
 </table> 
</form>
<?php
	echo foot();
}
elseif ($job == 'newsletter_delete') {
	echo head();
	$del = $gpc->get('delete', arr_int);
	if (count($del) > 0) {
		$deleteids = array();
		foreach ($del as $did) {
			$deleteids[] = 'id = '.$did; 
		}
		$db->query('DELETE FROM '.$db->pre.'newsletter WHERE '.implode(' OR ',$deleteids));
		$anz = $db->affected_rows();
		ok('admin.php?action=members&job=newsletter_archive', $anz.' Newsletter wurden gelöscht!');
	}
	else {
		error('admin.php?action=members&job=newsletter_archive', 'Keine Eingabe gemacht');
	}
	
}
elseif ($job == 'merge') {
	echo head();
	?>
<form name="form2" method="post" action="admin.php?action=members&job=merge2">
<table class="border">
<tr><td class="obox" colspan="2">Merge Users</td></tr>
<tr><td class="ubox" colspan="2">
Hier können Sie 2 Benutzeraccounts zu einem zusammenführen. 
Das "Basis-Mitglied" bleibt bestehen und die Daten werden als Standard benutzt. 
Die Beiträge, PNs etc. vom "überflüssigen Mitglied" werden auf das "Basis-Mitglied überschrieben. 
Beim "Basis-Mitglied" fehlende Daten werden vom "überflüssigen Mitglied" übernommen. 
Das "überflüssige Mitglied" wird danach gelöscht.
</td></tr>
<tr>
<td class="mbox">Basis-Mitglied:</td>
<td class="mbox">
	<input type="text" name="name1" id="name1" onkeyup="ajax_searchmember(this, 'sugg1');" size="40" /><br />
	<span class="stext">Suggestions: <span id="sugg1"></span></span>
</td>
</tr>
<td class="mbox">Überflüssiges Mitglied:</td>
<td class="mbox">
	<input type="text" name="name2" id="name2" onkeyup="ajax_searchmember(this, 'sugg2');" size="40" /><br />
	<span class="stext">Suggestions: <span id="sugg2"></span></span>
</td>
</tr>
<tr> 
<td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Löschen"></td> 
</tr>
</table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'merge2') {
	echo head();
	$base = $gpc->get('name1', str);
	$old = $gpc->get('name2', str);

	// Step 1: Getting data
	$result = $db->query('SELECT * FROM '.$db->pre.'user WHERE name = "'.$base.'" LIMIT 1');
	$result2 = $db->query('SELECT * FROM '.$db->pre.'user WHERE name = "'.$old.'" LIMIT 1');
	if ($db->num_rows($result) != 1 || $db->num_rows($result2) != 1) {
		error('admin.php?action=members&job=merge', 'Mindestens einer der angegebenen Benutzernamen wurde nicht gefunden.');
	}
	$base = $db->fetch_assoc($result);
	$old = $db->fetch_assoc($result2);

	// Step 2: Update abos
	$db->query("UPDATE {$db->pre}abos SET mid = '".$base['id']."' WHERE mid = '".$old['id']."'");
	// Step 3: Update favorites
	$db->query("UPDATE {$db->pre}fav SET mid = '".$base['id']."' WHERE mid = '".$old['id']."'");
	// Step 4: Update mods
	$db->query("UPDATE {$db->pre}moderators SET mid = '".$base['id']."' WHERE mid = '".$old['id']."'");
	// Step 5: Update pms
	$db->query("UPDATE {$db->pre}pm SET pm_to = '".$base['id']."' WHERE pm_to = '".$old['id']."'");
	$db->query("UPDATE {$db->pre}pm SET pm_from = '".$base['id']."' WHERE pm_from = '".$old['id']."'");
	// Step 6: Update posts
	$db->query("UPDATE {$db->pre}replies SET name = '".$base['id']."' WHERE name = '".$old['id']."' AND email = ''");
	// Step 7:Update topics
	$db->query("UPDATE {$db->pre}topics SET name = '".$base['id']."' WHERE name = '".$old['id']."'");
	$db->query("UPDATE {$db->pre}topics SET last_name = '".$base['id']."' WHERE last_name = '".$old['id']."'");
	// Step 8: Update uploads
	$db->query("UPDATE {$db->pre}uploads SET mid = '".$base['id']."' WHERE mid = '".$old['id']."'");
	// Step 9: Delete pic
	removeOldImages('uploads/pics/', $old['id']);
	// Step 10: Update votes
	$db->query("UPDATE {$db->pre}votes SET mid = '".$base['id']."' WHERE mid = '".$old['id']."'");
	// Setp 11: Update User data
	$newdata = array();
	$base = $gpc->save_str($base);
	$old = $gpc->save_str($old);
	if ($base['regdate'] > $old['regdate']) {
		$newdata[] = "regdate = '{$old['regdate']}'";
	}
	if (empty($base['fullname']) && !empty($old['fullname'])) {
		$newdata[] ="fullname = '{$old['fullname']}'";
	}
	if (empty($base['hp']) && !empty($old['hp'])) {
		$newdata[] ="hp = '{$old['hp']}'";
	}
	if (empty($base['signature']) && !empty($old['signature'])) {
		$newdata[] ="signature = '{$old['signature']}'";
	}
	if (empty($base['about']) && !empty($old['about'])) {
		$newdata[] ="about = '{$old['about']}'";
	}
	if (!empty($old['notice'])) {
		if (empty($base['notice'])) {
			$notice = $old['notice'];
		}
		else {
			$notice = $base['notice'].'[VSEP]'.$old['notice'];
		}
		$newdata[] ="notice = '{$notice}'";
	}
	if (empty($base['location']) && !empty($old['location'])) {
		$newdata[] ="location = '{$old['location']}'";
	}
	if (empty($base['pic']) && !empty($old['pic'])) {
		$newdata[] ="pic = '{$old['pic']}'";
	}
	if (empty($base['yahoo']) && !empty($old['yahoo'])) {
		$newdata[] ="yahoo = '{$old['yahoo']}'";
	}
	if (empty($base['msn']) && !empty($old['msn'])) {
		$newdata[] ="msn = '{$old['msn']}'";
	}
	if (empty($base['jabber']) && !empty($old['jabber'])) {
		$newdata[] ="jabber = '{$old['jabber']}'";
	}
	if (empty($base['aol']) && !empty($old['aol'])) {
		$newdata[] ="aol = '{$old['aol']}'";
	}
	if (empty($base['icq']) && !empty($old['icq'])) {
		$newdata[] ="icq = '{$old['icq']}'";
	}
	if ($base['birthday'] == '0000-00-00' && $old['birthday'] != '0000-00-00') {
		$newdata[] ="birthday = '{$old['birthday']}'";
	}
	if (empty($base['timezone']) && !empty($old['timezone'])) {
		$newdata[] ="timezone = '{$old['timezone']}'";
	}
	$g1 = explode(',', $base['groups']);
	natsort($g1);
	$base['groups'] = implode(',', $g1);
	$g2 = explode(',', $old['groups']);
	$g = array_merge($g1, $g2);
	$g = array_unique($g);
	natsort($g);
	$groups = implode(',', $g);
	if ($groups != $base['groups']) {
		$newdata[] ="groups = '{$groups}'";
	}
	if (count($newdata) > 0) {
		$db->query("UPDATE {$db->pre}user SET ".implode(', ', $newdata)." WHERE id = '".$base['id']."' LIMIT 1");
	}
	// Step 12: Delete old user
	$db->query("DELETE FROM {$db->pre}user WHERE id = '".$old['id']."'");
	
	ok('admin.php?action=members&job=manage', "{$old['name']}'s data is converted to {$base['name']}'s Account.");
}
elseif ($job == 'manage') {
	echo head();
	$sort = $gpc->get('sort', str);
	$order = $gpc->get('order', int);
	$letter = $gpc->get('letter', str);
	$page = $gpc->get('page', int, 1);
	
	$count = $db->fetch_array($db->query('SELECT COUNT(*) FROM '.$db->pre.'user'));
	$temp = pages($count[0], "admin.php?action=members&job=manage&sort=".$sort."&amp;letter=".$letter."&amp;order=".$order."&amp;", 25);

    if ($order == '1') $order = 'desc';
	else $order = 'asc';
			
	if ($sort == 'regdate') $sort = 'regdate';
	elseif ($sort == 'location') $sort = 'location';
	elseif ($sort == 'gender') $sort = 'gender';
	elseif ($sort == 'lastvisit') $sort = 'lastvisit';
	else $sort = 'name';

	$start = $page*25;
	$start = $start-25;
	
	$change = array('m' => 'male', 'w' => 'female', '' => '-');

	$result = $db->query('SELECT * FROM '.$db->pre.'user ORDER BY '.$sort.' '.$order.' LIMIT '.$start.',25');
	?>
	<form name="form" action="admin.php?action=members&job=delete" method="post">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		<tr> 
		  <td class="obox" colspan="8"><span style="float: right;">[<a href="admin.php?action=members&job=merge">Merge Users</a>]</span>List of Members &amp; User Manager</td>
		</tr>
		<tr> 
		  <td class="ubox" colspan="8"><span style="float: right;"><?php echo $temp; ?></span><?php echo $count[0]; ?> Members</td>
		</tr>
		<tr>
		  <td class="obox">Del</td>
		  <td class="obox">Name
		  <a href="admin.php?action=members&job=manage&letter=<?php echo $letter; ?>&amp;page=<?php echo $page; ?>"><img src="admin/html/images/asc.gif" border=0 alt="Ascending"></a>
		  <a href="admin.php?action=members&job=manage&order=1&amp;page=<?php echo $page; ?>&amp;letter=<?php echo $letter; ?>"><img src="admin/html/images/desc.gif" border=0 alt="Descending"></a></td>
		  <td class="obox">Email</td>
		  <td class="obox">Gender
		  <a href="admin.php?action=members&job=manage&sort=gender&amp;letter=<?php echo $letter; ?>&amp;page=<?php echo $page; ?>"><img src="admin/html/images/asc.gif" border=0 alt="Ascending"></a>
		  <a href="admin.php?action=members&job=manage&sort=gender&amp;letter=<?php echo $letter; ?>&amp;order=1&amp;page=<?php echo $page; ?>"><img src="admin/html/images/desc.gif" border=0 alt="Descending"></a></td>
		  <td class="obox">Residence
		  <a href="admin.php?action=members&job=manage&sort=location&amp;letter=<?php echo $letter; ?>&amp;page=<?php echo $page; ?>"><img src="admin/html/images/asc.gif" border=0 alt="Ascending"></a>
		  <a href="admin.php?action=members&job=manage&sort=location&amp;letter=<?php echo $letter; ?>&amp;order=1&amp;page=<?php echo $page; ?>"><img src="admin/html/images/desc.gif" border=0 alt="Descending"></a></td>
		  <td class="obox">Last Visit
		  <a href="admin.php?action=members&job=manage&sort=lastvisit&amp;letter=<?php echo $letter; ?>&amp;page=<?php echo $page; ?>"><img src="admin/html/images/asc.gif" border=0 alt="Ascending"></a>
		  <a href="admin.php?action=members&job=manage&sort=lastvisit&amp;letter=<?php echo $letter; ?>&amp;order=1&amp;page=<?php echo $page; ?>"><img src="admin/html/images/desc.gif" border=0 alt="Descending"></a></td>
		  <td class="obox">Registered on
		  <a href="admin.php?action=members&job=manage&sort=regdate&amp;letter=<?php echo $letter; ?>&amp;page=<?php echo $page; ?>"><img src="admin/html/images/asc.gif" border=0 alt="Ascending"></a>
		  <a href="admin.php?action=members&job=manage&sort=regdate&amp;letter=<?php echo $letter; ?>&amp;order=1&amp;page=<?php echo $page; ?>"><img src="admin/html/images/desc.gif" border=0 alt="Descending"></a></td>
		</tr>
	<?php
	while ($row = $gpc->prepare($db->fetch_object($result))) { 
		$row->regdate = gmdate('d.m.Y', times($row->regdate));
		if ($row->lastvisit == 0) {
			$row->lastvisit = 'Never';
		} else {
			$row->lastvisit = gmdate('d.m.Y H:i', times($row->lastvisit));
		}
		?>
	    <tr>
	      <td class="mbox"><input type="checkbox" name="delete[]" value="<?php echo $row->id; ?>"></td> 
		  <td class="mbox"><a title="Edit" href="admin.php?action=members&job=edit&id=<?php echo $row->id; ?>"><?php echo $row->name; ?></a><?php echo iif($row->fullname,"<br><i>".$row->fullname."</i>"); ?></td> 
		  <td class="mbox" align="center"><a href="mailto:<?php echo $row->mail; ?>">Email</a></td> 
		  <td class="mbox"><?php echo $change[$row->gender]; ?></td>
		  <td class="mbox"><?php echo iif($row->location,$row->location,'-'); ?></td>
		  <td class="mbox"><?php echo $row->lastvisit; ?></td>
		  <td class="mbox"><?php echo $row->regdate; ?></td>
		</tr>
		<?php
	} 
	?>
		<tr> 
		  <td class="ubox" colspan="8"><span style="float: right;"><?php echo $temp; ?></span><input type="submit" name="submit" value="Delete"></td>
		</tr>
	</table>
	</form>
	<?php
    echo foot();
}
elseif ($job == 'edit') {
	// About
	$id = $gpc->get('id', int);

	$result = $db->query('SELECT * FROM '.$db->pre.'user WHERE id = '.$id);
	if ($db->num_rows() != 1) {
		error('admin.php?action=members&job=manage', 'Keine gültige ID angegeben.');
	}
	$user = $gpc->prepare($db->fetch_assoc($result));
	
	$chars = $config['maxaboutlength'];
	
	if (empty($user['template'])) {
	    $user['template'] = $config['templatedir'];
	}
	if (empty($user['language'])) {
	    $user['language'] = $config['langdir'];
	}		
	
	// Settings
	$design = cache_loaddesign();
	$mydesign = $design[$user['template']]['name'];
	$language = cache_loadlanguage();
	$mylanguage = $language[$user['language']]['language'];
	// Profile
    $bday = explode('-',$user['birthday']);
    $year = gmdate('Y');
    $maxy = $year-6;
    $miny = $year-100;
    $result = $db->query("SELECT id, title, name, core FROM {$db->pre}groups ORDER BY admin DESC , guest ASC , core ASC");
    $random = md5(microtime());

	echo head();
?>
<form name="form_<?php echo $random; ?>" method="post" action="admin.php?action=members&job=edit2&amp;id=<?php echo $id; ?>&amp;random=<?php echo $random; ?>">
<table class="border">
<tr><td class="obox" colspan="2">Mitglied bearbeiten</td></tr>
<tr><td class="mbox">Benutzername:</td><td class="mbox">
<input type="text" name="name_<?php echo $random; ?>" size="40" value="<?php echo $user['name']; ?>" />
</td></tr>
<tr><td class="mbox">Neues Passwort:</td><td class="mbox">
<input type="password" name="pw_<?php echo $random; ?>" size="40" value="" />
</td></tr>
<tr><td class="mbox" valign="top">Gruppenzugehörigkeit:<br />
<span class="stext">Mehrere Gruppen möglich. Eingabe der IDs mit Kommas!</span>
</td><td class="mbox">
<input type="text" name="groups" id="groups" size="40" value="<?php echo $user['groups']; ?>" />
<br />
<table class="border">
<tr>
<td class="ubox">ID</td>
<td class="ubox">Interner Gruppenname></td>
<td class="ubox">Öffentlicher Gruppentitel</td>
</tr>
<?php while ($row = $gpc->prepare($db->fetch_assoc($result))) { ?>
<tr>
<td class="mbox"><?php echo $row['id']; ?></td>
<td class="mbox"><?php echo $row['name']; ?></td>
<td class="mbox"><?php echo $row['title']; ?></td>
</tr>
<?php } ?>
</table>
</td></tr>
<tr><td class="mbox">Bürgerlicher Name:</td><td class="mbox">
<input type="text" name="fullname" id="fullname" size="40" value="<?php echo $user['fullname']; ?>" />
</td></tr>
<tr><td class="mbox">Emailadresse:</td><td class="mbox"> 
<input type="text" name="email" id="email" size="40" value="<?php echo $user['mail']; ?>" />
</td></tr>
<tr><td class="mbox">Wohnort:</td><td class="mbox"> 
<input type="text" name="location" id="location" size="40" value="<?php echo $user['location']; ?>" />
</td></tr>
<tr><td class="mbox">Geschlecht:</td><td class="mbox"> 
<select size="1" name="gender">
	<option value="">Keine Angabe</option>
	<option<?php echo iif($user['gender'] == 'm',' selected="selected"'); ?> value="m">Männlich</option>
	<option<?php echo iif($user['gender'] == 'w',' selected="selected"'); ?> value="w">Weiblich</option>
</select>
</td></tr>
<tr><td class="mbox">Geburtstag:</td><td class="mbox">
  <select size="1" name="birthday">
  <option value="00">--</option>
	<?php
	for ($i=1;$i<=31;$i++) {
		echo "<option value='".leading_zero($i)."'".iif($bday[2] == $i, ' selected="selected"').">".$i."</option>\n";
	}
	?>
  </select>. 
  <select size="1" name="birthmonth">
  <option value="00">--</option>
	<?php
	for ($i=1;$i<=12;$i++) {
		echo "<option value='".leading_zero($i)."'".iif($bday[1] == $i, ' selected="selected"').">".$i."</option>\n";
	}
	?>
  </select> 
  <select size="1" name="birthyear">
  <option value="0000">----</option>
	<?php
	for ($i=$maxy;$i>=$miny;$i--) {
		echo "<option value='".$i."'".iif($bday[0] == $i, ' selected="selected"').">".$i."</option>\n";
	}
	?>
  </select>
</td></tr>
<tr><td class="mbox">Homepage:</td><td class="mbox"> 
<input type="text" name="hp" id="hp" size="40" value="<?php echo $user['hp']; ?>" />
</td></tr>
<tr><td class="mbox">ICQ:</td><td class="mbox"> 
<input type="text" name="icq" id="icq" size="40" value="<?php echo iif(!empty($user['icq']), $user['icq']); ?>" />
</td></tr>
<tr><td class="mbox">AOL- & Netscape-Messenger:</td><td class="mbox"> 
<input type="text" name="aol" id="aol" size="40" value="<?php echo $user['aol']; ?>" />
</td></tr>
<tr><td class="mbox">Yahoo-Messenger:</td><td class="mbox"> 
<input type="text" name="yahoo" id="yahoo" size="40" value="<?php echo $user['yahoo']; ?>" />
</td></tr>
<tr><td class="mbox">MSN- & Windows-Messenger</td><td class="mbox"> 
<input type="text" name="msn" id="msn" size="40" value="<?php echo $user['msn']; ?>" />
</td></tr>
<tr><td class="mbox">Jabber:</td><td class="mbox"> 
<input type="text" name="jabber" id="jabber" size="40" value="<?php echo $user['jabber']; ?>" />
</td></tr>
<tr><td class="ubox" align="center" colspan="2"><input accesskey="s" type="submit" name="Submit1" value="Submit" /></td></tr>
</table>

<br class="minibr" />
<table class="border">
<tr><td class="obox">Signatur</td></tr>
<tr><td class="mbox" align="center"><textarea name="signature" rows="4" cols="110"><?php echo $user['signature']; ?></textarea></td></tr>
<tr><td class="ubox" align="center"><input accesskey="s" type="submit" name="Submit1" value="Submit" /></td></tr>
</table>
<br class="minibr" />

<table class="border">
<tr><td class="obox" colspan="2">Profilbild ändern</td></tr>
<tr>
<td class="mbox">Neues Profilbild per URL hinzufügen:</td>
<td class="mbox"><input type="text" name="pic" id="pic" size="70" value="<?php echo $user['pic']; ?>" /></td>
</tr>
<tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" name="Submit1" value="Submit" /></td></tr>
</table>
<br class="minibr" />

<table class="border">
<tr><td class="obox" colspan="2">Optionen ändern</td></tr>
<tr><td class="mbox">Zeitzone:</td><td class="mbox">
<select id="temp" name="temp"> 
	<option selected="selected" value="<?php echo $user['timezone']; ?>">Zeitzone beibehalten (GMT <?php echo $user['timezone']; ?>)</option>
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
</select>		
</td></tr>
<tr><td class="mbox">Beitragseditor:</td><td class="mbox">
<select id="opt_0" name="opt_0">
	<option<?php echo iif($user['opt_textarea'] == 0,' selected="selected"'); ?> value="0">Einfacher Editor</option>
	<option<?php echo iif($user['opt_textarea'] == 1,' selected="selected"'); ?> value="1">Erweiterter Editor</option>
</select>
</td></tr>
<tr><td class="mbox">Email bei neuer PN senden?</td><td class="mbox">
<input id="opt_1" type="checkbox" name="opt_1" <?php echo iif($user['opt_pmnotify'] == 1,' checked="checked"'); ?> value="1" />
</td></tr>
<tr><td class="mbox">Schlecht bewertete Themen automatisch ausblenden?</td><td class="mbox">
<input id="opt_2" type="checkbox" name="opt_2" <?php echo iif($user['opt_hidebad'] == 1,' checked="checked"'); ?> value="1" />
</td></tr>
<tr><td class="mbox">Wie soll Mitgliedern Ihre Email angezeigt werden?</td><td class="mbox">
<select id="opt_3" name="opt_3">
	<option<?php echo iif($user['opt_hidemail'] == 0,' selected="selected"'); ?> value="0">E-Mail verschlüsselt zeigen + Formular</option>
	<option<?php echo iif($user['opt_hidemail'] == 1,' selected="selected"'); ?> value="1">E-Mail nicht zeigen + Kein Formular</option>
	<option<?php echo iif($user['opt_hidemail'] == 2,' selected="selected"'); ?> value="2">E-Mail nicht zeigen + Formular</option>
</select>
</td></tr>
<tr><td class="mbox">Welches Design wollen Sie nutzen?</td><td class="mbox">
<select id="opt_4" name="opt_4">
	<option selected="selected" value="<?php echo $user['template']; ?>">Design beibehalten</option>
	<?php foreach ($design as $row) { ?>
	<option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
	<?php } ?>
</select>
</td></tr>
<tr><td class="mbox">Welche Sprache wollen Sie nutzen?</td><td class="mbox">
<select id="opt_5" name="opt_5">
	<option selected="selected" value="<?php echo $user['language']; ?>">Sprache beibehalten</option>
	<?php foreach ($language as $row) { ?>
	<option value="<?php echo $row['id']; ?>"><?php echo $row['language']; ?></option>
	<?php } ?>
</select>
</td></tr>
<tr><td class="ubox" colspan="2"><input accesskey="s" type="submit" name="Submit1" value="Submit" /></td></tr>
</table>
<br class="minibr" />

<table class="border">
<tr><td class="obox">Persönliche Seite ändern</td></tr>
<tr><td class="mbox" align="center"><textarea name="comment" id="comment" rows="15" cols="110"><?php echo $user['about']; ?></textarea></td></tr>
<tr><td class="ubox" align="center"><input accesskey="s" type="submit" name="Submit1" value="Submit" /></td></tr>
</table>
</form>
<?php
	echo foot();
}
elseif ($job == 'edit2') {
	echo head();
	$cache = cache_loaddesign();
	$cache2 = cache_loadlanguage();
	
	$keys_int = array('id', 'birthday', 'birthmonth', 'birthyear', 'opt_0', 'opt_1', 'opt_2', 'opt_3', 'opt_4', 'opt_5');
	$keys_str = array('groups', 'fullname', 'email', 'location', 'icq', 'gender', 'hp', 'aol', 'yahoo', 'msn', 'jabber', 'signature', 'pic', 'temp', 'comment');
	foreach ($keys_int as $val) {
		$query[$val] = $gpc->get($val, int);
	}
	foreach ($keys_str as $val) {
		$query[$val] = $gpc->get($val, str);
	}

	$result = $db->query('SELECT * FROM '.$db->pre.'user WHERE id = '.$query['id']);
	if ($db->num_rows() != 1) {
		error('admin.php?action=members&job=manage', 'Keine gültige ID angegeben.');
	}
	$user = $gpc->prepare($db->fetch_assoc($result));

	$random = $gpc->get('random', none);
	$name = $gpc->get('name_'.$random, str);
	if (empty($name)) {
		$query['name'] = $user['name'];
	}
	else {
		$query['name'] = $name;
	}
	$name = $gpc->get('pw_'.$random, str);
	if (empty($name)) {
		$query['pw'] = $user['pw'];
	}
	else {
		$query['pw'] = $name;
	}
	
	$error = array();
	if (strxlen($query['comment']) > $config['maxaboutlength']) {
		$error[] = $lang->phrase('about_too_long');
	}
	if (check_mail($query['email']) == FALSE) {
		 $error[] = $lang->phrase('illegal_mail');
	}
	if (strxlen($query['name']) > $config['maxnamelength']) {
		$error[] = $lang->phrase('name_too_long');
	}
	if (strxlen($query['name']) < $config['minnamelength']) {
		$error[] = $lang->phrase('name_too_short');
	}
	if (strxlen($query['email']) > 200) {
		$error[] = $lang->phrase('email_too_long');
	}
	if (strxlen($query['signature']) > $config['maxsiglength']) {
		$error[] = $lang->phrase('editprofile_signature_too_long');
	}
	if (strxlen($query['hp']) > 254) {
		$error[] = $lang->phrase('editprofile_homepage_too_long');
	}
	if (!check_hp($query['hp'])) {
		$query['hp'] = '';
	}
	if (strxlen($query['location']) > 50) {
		$error[] = $lang->phrase('editprofile_location_too_short');
	}
	if ($query['gender'] != 'm' && $query['gender'] != 'w' && $query['gender'] != '') {
		$error[] = $lang->phrase('editprofile_gender_incorrect');
	}
	if ($query['birthday'] > 31) {
		$error[] = $lang->phrase('editprofile_birthday_incorrect');
	}
	if ($query['birthmonth'] > 12) {
		$error[] = $lang->phrase('editprofile_birthmonth_incorrect');
	}
	if (($query['birthyear'] < gmdate('Y')-120 || $query['birthyear'] > gmdate('Y')) && $query['birthyear'] != 0 ) {
		$error[] = $lang->phrase('editprofile_birthyear_incorrect');
	}
	if (strxlen($query['fullname']) > 128) {
		$error[] = $lang->phrase('editprofile_fullname_incorrect');
	}
	if (intval($query['temp']) < -12 && intval($query['temp']) > 12) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('timezone');
	}
	if ($query['opt_0'] < 0 && $query['opt_0'] > 2) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_editor');
	}
	if ($query['opt_1'] != 0 && $query['opt_1'] != 1) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_emailpn');
	}
	if ($query['opt_2'] != 0 && $query['opt_2'] != 1) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_bad');
	}
	if ($query['opt_3'] < 0 && $query['opt_3'] > 2) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_showmail');
	}
	if (!isset($cache[$query['opt_4']])) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_design');
	}
	if (!isset($cache2[$query['opt_5']])) {
		$error[] = $lang->phrase('editprofile_settings_error').$lang->phrase('editprofile_language');
	}
	if (!empty($query['pic']) && preg_match('/^(http:\/\/|www.)([\wäöüÄÖÜ@\-_\.]+)\:?([0-9]*)\/(.*)$/', $query['pic'], $url_ary)) {
		$query['pic'] = checkRemotePic($query['pic'], $url_ary, $query['id']);
	}
	elseif (empty($query['pic']) || !file_exists($query['pic'])) {
		$query['pic'] = '';
	}
	
	if (count($error) > 0) {	
		error('admin.php?action=members&job=edit&id='.$query['id'], $error);
	}
	else {
	    // Now we create the birthday... 
	    if (!$query['birthmonth'] && !$query['birthday'] && !$query['birthyear']) {
	    	$bday = '0000-00-00';
	    }
	    else {
	        $query['birthmonth'] = leading_zero($query['birthmonth']);
	        $query['birthday'] = leading_zero($query['birthday']);
	        $query['birthyear'] = leading_zero($query['birthyear'],4);
	        $bday = $query['birthyear'].'-'.$query['birthmonth'].'-'.$query['birthday'];
	    }
	    $query['icq'] = str_replace('-', '', $query['icq']);
    	if (!is_id($query['icq'])) {
    		$query['icq'] = 0;
	    }

		$pw = $gpc->get('pw', none);
		if (!empty($pw) && strlen($pw) >= $config['minpwlength']) {
			$md5 = md5($pw);
			$update_sql = ", pw = '{$md5}' ";
		}
		else {
			$update_sql = ' ';
		}

		$db->query("UPDATE {$db->pre}user SET groups = '".$query['groups']."', timezone = '".$query['temp']."', opt_textarea = '".$query['opt_0']."', opt_pmnotify = '".$query['opt_1']."', opt_hidebad = '".$query['opt_2']."', opt_hidemail = '".$query['opt_3']."', template = '".$query['opt_4']."', language = '".$query['opt_5']."', pic = '".$query['pic']."', about = '".$query['comment']."', icq = '".$query['icq']."', yahoo = '".$query['yahoo']."', aol = '".$query['aol']."', msn = '".$query['msn']."', jabber = '".$query['jabber']."', birthday = '".$bday."', gender = '".$query['gender']."', hp = '".$query['hp']."', signature = '".$query['signature']."', location = '".$query['location']."', fullname = '".$query['fullname']."', mail = '".$query['email']."', name = '".$query['name']."'".$update_sql." WHERE id = '".$user['id']."' LIMIT 1",__LINE__,__FILE__); 
		ok("admin.php?action=members&job=manage", 'Daten erfolgreich gespeichert!');
	}
}
elseif ($job == 'delete') {
	echo head();
	$delete = $gpc->get('delete', arr_int);
	if (in_array($my->id, $delete)) {
		$mykey = array_search($my->id, $delete);
		unset($delete[$mykey]);
	}
	if (count($delete) > 0) {
		$did = implode(',', $delete);
		$result = $db->query('SELECT * FROM '.$db->pre.'user WHERE id IN ('.$did.')');
		$olduserdata = file_get_contents('data/deleteduser.php');
		while ($user = $gpc->prepare($db->fetch_assoc($result))) {
			// Step 1: Write Data to File with old Usernames
			$olduserdata .= "\n".$user['id']."\t".$user['name'];
			$olduserdata = trim($olduserdata);
			// Step 2: Delete all pms
			$db->query("DELETE FROM {$db->pre}pm WHERE pm_to IN ({$did})");
			// Step 3: Search all old posts by an user, and update to guests post
			$db->query("UPDATE {$db->pre}replies SET name = '".$user['name']."', email = '".$user['mail']."' WHERE name = '".$user['id']."' AND email = ''");
			// Step 4: Search all old topics by an user, and update to guests post
			$db->query("UPDATE {$db->pre}topics SET name = '".$user['name']."' WHERE name = '".$user['id']."'");
			$db->query("UPDATE {$db->pre}topics SET last_name = '".$user['name']."' WHERE last_name = '".$user['id']."'");
			// Step 5: Delete pic
			removeOldImages('uploads/pics/', $user['id']);
		}
		$filesystem->file_put_contents('data/deleteduser.php', $olduserdata);
		// Step 6: Delete all abos
		$db->query("DELETE FROM {$db->pre}abos WHERE mid IN ({$did})");
		// Step 7: Delete all favorites
		$db->query("DELETE FROM {$db->pre}fav WHERE mid IN ({$did})");
		// Step 8: Delete as mod
		$db->query("DELETE FROM {$db->pre}moderators WHERE mid IN ({$did})");
		$delete = $gpc->get('delete', arr_int);
		// Step 9: Set uploads from member to guests-group
		$db->query("UPDATE {$db->pre}uploads SET mid = '0' WHERE mid IN ({$did})");
		// Step 10: Delete user himself
		$db->query("DELETE FROM {$db->pre}user WHERE id IN ({$did})");
		ok('javascript:history.back(-1);', $db->affected_rows().' members deleted');
	}
	else {
		error('javascript:history.back(-1);', 'Keine gültige Angabe gemacht.');
	}

}
elseif ($job == 'emaillist') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=members&job=emaillist2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2"><b>Email-Liste erstellen</b></td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Trennzeichen:<br><span class="stext">Trennzeichen zwischen den Emailadressen. Keine Angabe = Kommagetrennt</span></td>
   <td class="mbox" width="50%"><textarea name="template" cols="10" rows="2"></textarea></td> 
  </tr>
  <tr> 
	<td class="mbox" width="50%">Empfänger:</td>
	<td class="mbox" width="50%"><select size="1" name="int1"><option value="1">Alle</option><option value="2" selected>Nur Mitglieder</option><option value="3">Nur Gäste</option></select></td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Submit"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'emaillist2') {
	echo head();
	$int1 = $gpc->get('int1', int);
	if ($int1 == 1) {
		$emails = array();
		$result = $db->query('SELECT mail FROM '.$db->pre.'user');
		while ($row = $db->fetch_array($result)) {
			$emails[] = $row[0];
		}
		$result = $db->query('SELECT email FROM '.$db->pre.'replies WHERE email != ""');
		while ($row = $db->fetch_array($result)) {
			$emails[] = $row[0];
		}
	}
	elseif ($int1 == 2) {
		$emails = array();
		$result = $db->query('SELECT mail FROM '.$db->pre.'user');
		while ($row = $db->fetch_array($result)) {
			$emails[] = $row[0];
		}
	}
	elseif ($int1 == 3) {
		$emails = array();
		$result = $db->query('SELECT email FROM '.$db->pre.'replies WHERE email != ""');
		while ($row = $db->fetch_array($result)) {
			$emails[] = $row[0];
		}
	}
	$emails = array_unique($emails);
	$template = $gpc->get('template', none);
	if (empty($template)) {
		$template = ',';
	}
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox"><b>Email-Liste erstellen</b></td>
  </tr>
  <tr> 
   <td class="mbox"><textarea class="fullwidth" cols="125" rows="25"><?php echo implode($template, $emails); ?></textarea></td>
  </tr>
 </table>	
	<?php
	echo foot();
}
elseif ($job == 'banned') {
	echo head();
	$content = file_get_contents('data/banned.php');
	$b = file_get_contents('data/bannedip.php');
	?>
<form name="form" method="post" action="admin.php?action=members&job=banned2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan=2><b>Seite für gebannte IP-Adressen</b></td>
  </tr>
  <tr> 
   <td class="mbox" width="30%">Inhalt d. Seite:<br><span class="stext">HTML und PHP sind möglich!</span></td>
   <td class="mbox" width="70%"><textarea name="template" rows="10" cols="90"><?php echo $content; ?></textarea></td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
  </tr>
 </table>
</form><br>
<form name="form" method="post" action="admin.php?action=members&job=banned3">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2"><b>IP-Adressen verwalten</b></td>
  </tr>
  <tr>
   <td class="mbox" width="30%">
   IP-Adresse:<br />
   <span class="stext">Pro Zeile eine Emailadresse.<br />Um einen IP-Adressenbereich anzugeben, geben sie nur die erstezn Zeichen des Bereichs an (Bsp: "127.0." findet z.Bsp: "127.0.0.1")</span>
   </td>
   <td class="mbox" width="70%"><textarea name="ips" rows="10" cols="90"><?php echo $b; ?></textarea></td> 
  </tr>
  <tr> 
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'banned2') {
	echo head();
	$filesystem->file_put_contents('data/banned.php', $gpc->get('template', none));
	ok('admin.php?action=members&job=banned', 'Seite wurde gespeichert');
}
elseif ($job == 'banned3') {
	echo head();
	$bannedip = file('data/bannedip.php');
	$bannedip = array_map('trim', $bannedip);
	$file = $gpc->get('ips', none);
	$file = trim($file);
	$filesystem->file_put_contents('data/bannedip.php',$file);
	ok('admin.php?action=members&job=banned', 'IP-Adressen wurden gespeichert.');
}
elseif ($job == 'search') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=members&job=search2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2">Mitglieder suchen</td>
  </tr>
  <tr>
	<td class="mbox" width="50%" colspan="2"><b>Hilfe:</b> Sie können "%" und "_" als Platzhalter in den Suchbegriff einfügen. Ein "_" steht für ein einziges Zeichen, ein "%" steht für beliebig viele Zeichen.</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Einmalige Identifikationsnummer (ID):</td>
   <td class="mbox" width="50%">
      <input type="text" name="id" size="12">
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Nickname:</td>
   <td class="mbox" width="50%">
      <input type="text" name="name" size="50">
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Emailadresse:</td>
   <td class="mbox" width="50%">
      <input type="text" name="mail" size="50">
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Datum d. Registrierung:</td>
   <td class="mbox" width="50%">
      <input type="text" name="regdate2[1]" size="3">. <input type="text" name="regdate2[2]" size="3">. <input type="text" name="regdate2[3]" size="5"> (DD. MM. YYYY)
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Vollständiger Name:</td>
   <td class="mbox" width="50%">
      <input type="text" name="fullname" size="50">
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Homepage:</td>
   <td class="mbox" width="50%">
      <input type="text" name="hp" size="50">
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Signatur:</td>
   <td class="mbox" width="50%">
      <textarea rows="4" cols="50" name="signature"></textarea>
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Wohnort:</td>
   <td class="mbox" width="50%">
      <input type="text" name="location" size="50">
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Geschlecht:</td>
   <td class="mbox" width="50%">
      <select name="gender"><option value="NULL">- Angabe egal -</option><option value="''">Keine Angabe</option><option value="m">Männlich</option><option value="w">Weiblich</option></select>
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Geburtstag:<br>Keine Angabe = NULL</td>
   <td class="mbox" width="50%">
      <input type="text" name="birthday2[1]" size="3">. <input type="text" name="birthday2[2]" size="3">. <input type="text" name="birthday2[3]" size="5"> (DD. MM. YYYY)
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Letzter Besuch:</td>
   <td class="mbox" width="50%">
      <input type="text" name="lastvisit2[1]" size="3">. <input type="text" name="lastvisit2[2]" size="3">. <input type="text" name="lastvisit2[3]" size="5"> (DD. MM. YYYY)
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">ICQ-Nummer:<br>Keine Angabe = NULL</td>
   <td class="mbox" width="50%">
      <input type="text" name="icq" size="12">
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Yahoo-ID:</td>
   <td class="mbox" width="50%">
      <input type="text" name="yahoo" size="50">
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">AOL-Name:</td>
   <td class="mbox" width="50%">
      <input type="text" name="aol" size="50">
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">MSN-Adresse:</td>
   <td class="mbox" width="50%">
      <input type="text" name="msn" size="50">
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Jabber-Adresse:</td>
   <td class="mbox" width="50%">
      <input type="text" name="jabber" size="50">
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Zeitzone:<br>Keine Angabe = NULL</td>
   <td class="mbox" width="50%">
      <input type="text" name="timezone" size="50">
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Gruppen-ID:</td>
   <td class="mbox" width="50%">
      <input type="text" name="groups" size="50">
      </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Genauigkeit:</td>
   <td class="mbox" width="50%">
   <input type="radio" name="int1" value="0"> ODER (irgendeine der Eingaben)<br>
   <input type="radio" name="int1" value="1" checked> UND (alle Eingaben)
   </td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan="2" align="center"><input type="hidden" name="regdate" value=""><input type="hidden" name="lastvisit" value=""><input type="submit" name="Submit" value="Abschicken"></td> 
  </tr>
 </table>
</form>
	<?php
    echo foot();
}
elseif ($job == 'search2') {
	echo head();
	
	$fields = 	array(
	'id' => 'ID',
	'name' => 'Nickname',
	'mail' => 'Email',
	'regdate' => 'Registration',
	'fullname' => 'Vollständiger Name',
	'hp' => 'Homepage',
	'signature' => 'Signatur',
	'location' => 'Wohnort',
	'gender' => 'Geschlecht',
	'birthday' => 'Geburtstag',
	'lastvisit' => 'Letzter Besuch',
	'icq' => 'ICQ',
	'yahoo' => 'Yahoo',
	'aol' => 'AOL',
	'msn' => 'MSN',
	'jabber' => 'Jabber',
	'timezone' => 'Zeitzone',
	'groups' => 'Gruppen-ID'
	);

	// Verbessern
	if ($_POST['regdate2'][1] > 0 && $_POST['regdate2'][2] > 0 && $_POST['regdate2'][3] > 0) {
		$_POST['regdate'] = mktime(0, 0, 0, $_POST['regdate2'][2], $_POST['regdate2'][1], $_POST['regdate2'][3]);
	}
	if ($_POST['birthday2'][1] > 0 || $_POST['birthday2'][2] > 0 || $_POST['birthday2'][3] > 1900) {
		if (strlen($_POST['birthday2'][1]) < 1) {
			$_POST['birthday2'][1] = '__';
		}
		if (strlen($_POST['birthday2'][2]) < 1) {
			$_POST['birthday2'][2] = '__';
		}
		if (strlen($_POST['birthday2'][3]) < 1) {
			$_POST['birthday2'][3] = '__';
		}
		$_POST['birthday'] = $_POST['birthday2'][3].'-'.$_POST['birthday2'][2].'-'.$_POST['birthday2'][1];
	}
	
	$fields_key = array_keys($fields);
	$fields_val = array_values($fields);

	$searchfor_keys = array();
	foreach ($fields_key as $key) {
		if ((strlen($gpc->get($key, none)) > 0 && $gpc->get($key, none) != 'NULL')) {
			$searchfor_keys[] = $key;
		}
	}

	$searchfor_keys = array_unique($searchfor_keys);
	
	if ($gpc->get('int1', int) == 0) {
		$delimiter = ' OR ';
	}
	else {
		$delimiter = ' AND ';
	}
	
	$sqlwhere = array();
	foreach ($searchfor_keys as $intkey => $key) {
		if (strlen($gpc->get($key, none)) > 0) {
			if ($_POST[$key] == "''") {
				$sqlwhere[] = $key." = ''";
			}
			elseif ($_POST[$key] != 'NULL') {
				$sqlwhere[] = $key.' LIKE "'.$gpc->get($key, none).'"';
			}
			else {
				$sqlwhere[] = $key.' = NULL';
			}
		}
	}

	$searchfor_keys2 = $searchfor_keys;

	$searchfor_keys2[] = 'id';
	$searchfor_keys2[] = 'name';

	$colspan = count($searchfor_keys2);
	
	$anz = count($sqlwhere);

	$result = $db->query('SELECT '.implode(',',$searchfor_keys2).' FROM '.$db->pre.'user WHERE '.iif($anz > 0, implode($delimiter,$sqlwhere), '1=0').' ORDER BY name');
	$count = $db->num_rows($result);
	?>
	<form name="form" action="admin.php?action=members&job=delete" method="post">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		<tr> 
		  <td class="obox" colspan="<?php echo $colspan; ?>"><b>Mitgliede suchen</b></td>
		</tr>
		<tr> 
		  <td class="ubox" colspan="<?php echo $colspan; ?>"><?php echo $count; ?> gefundene Mitglieder</td>
		</tr>
		<tr>
		  <td class="obox">DEL</td>
		  <td class="obox">Name</td>
		  <?php foreach ($searchfor_keys as $key) { ?>
		  <td class="obox"><?php echo $fields[$key]; ?></td>
		  <?php } ?>
		</tr>
	<?php while ($row = $gpc->prepare($db->fetch_assoc($result))) { ?>
	    <tr>
	      <td class="mbox"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td> 
		  <td class="mbox"><a title="Editieren" href="admin.php?action=members&job=edit&id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
		  <?php foreach ($searchfor_keys as $key) { ?>
		  <td class="mbox"><?php echo $row[$key]; ?></td>
		  <?php } ?>
		</tr>
	<?php } ?>
		<tr> 
		  <td class="ubox" colspan="<?php echo $colspan; ?>"><input type="submit" name="submit" value="Löschen"></td>
		</tr>
	</table>
	</form>
	<?php
    echo foot();
}
elseif ($job == 'activate') {
	echo head();

	$result = $db->query('SELECT * FROM '.$db->pre.'user WHERE confirm != "11" ORDER BY regdate DESC');
	?>
	<form name="form" action="admin.php?action=members&job=delete" method="post">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		<tr> 
		  <td class="obox" colspan="4">Moderate &amp; Unlock Members</td>
		</tr>
		<tr>
		  <td class="ubox" width="30%">Name</td>
		  <td class="ubox" width="10%">Email</td>
		  <td class="ubox" width="15%">Registered</td>
		  <td class="ubox" width="45%">Status</td>
		</tr>
	<?php
	while ($row = $gpc->prepare($db->fetch_object($result))) { 
		$row->regdate = gmdate('d.m.Y', times($row->regdate));
		if ($row->lastvisit == 0) {
			$row->lastvisit = 'Never';
		} else {
			$row->lastvisit = gmdate('d.m.Y', times($row->lastvisit));
		}
		?>
	    <tr>
		  <td class="mbox"><a title="Edit" href="admin.php?action=members&job=edit&id=<?php echo $row->id; ?>"><?php echo $row->name; ?></a></td> 
		  <td class="mbox" align="center"><a href="mailto:<?php echo $row->mail; ?>">Email</a></td> 
		  <td class="mbox"><?php echo $row->regdate; ?></td>
		  <td class="mbox"><ul>
		  <?php if ($row->confirm == '00' || $row->confirm == '01') { ?>
		  <li><strong><a href="admin.php?action=members&job=confirm&id=<?php echo $row->id; ?>">User freischalten</a></strong></li>
		  <?php } if ($row->confirm == '00' || $row->confirm == '10') { ?>
		  <li>User muss sich noch per E-Mail freischalten</li>
		  <?php } ?>
		  <li>User löschen: <input type="checkbox" name="delete[]" value="<?php echo $row->id; ?>"></li>
		  </ul></td>
		</tr>
		<?php
	} 
	?>
		<tr> 
		  <td class="ubox" colspan="4" align="center"><input type="submit" name="submit" value="Delete selected User"></td>
		</tr>
	</table>
	</form>
	<?php
    echo foot();
}
elseif ($job == 'confirm') {
	echo head();

	$id = $gpc->get('id', int);
	$result = $db->query('SELECT id, name, confirm, mail FROM '.$db->pre.'user WHERE id = "'.$id.'" LIMIT 1');
	$row = $db->fetch_assoc($result);
	
	if ($row['confirm'] == '00') {
		$confirm = '10';
	}
	else {
		$confirm = '11';
	}
	
	$db->query('UPDATE '.$db->pre.'user SET confirm = "'.$confirm.'" WHERE id = "'.$row['id'].'" LIMIT 1');
	
	// Send Mail
	$content = $lang->get_mail('admin_confirmed');
	xmail(array('0' => array('mail' => $row['mail'])), array(), $content['title'], $content['comment']);
	
	ok('admin.php?action=members&job=activate', 'Mitglied wurde freigeschaltet!');
}
?>
