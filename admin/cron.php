<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "start.php") die('Error: Hacking Attempt');

if ($_GET['job'] == 'upload') {
	echo head();
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=explorer&job=upload&cfg=cron">
<table class="border" cellpadding="3" cellspacing="0" border="0">
<tr><td class="obox">Cron Job File Upload</td></tr>
<tr><td class="mbox">
Um eine Datei anzufügen, klicken Sie auf die "Durchsuchen"-Schaltfläche und wählen Sie eine Datei aus.
Klicken Sie dann auf "Senden", um den Vorgang abzuschließen.<br /><br />
Erlaubte Dateitypen: .php<br />
Maximale Dateigröße: 100 KB<br /><br />
<strong>Datei hochladen:</strong>
<br /><input type="file" name="upload_0" size="40" />
</td></tr>
<tr><td class="ubox" align="center"><input accesskey="s" type="submit" value="Upload" /></td></tr>
</table>
</form>
	<?php
	echo foot();
}
elseif ($_GET['job'] == 'add') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=cron&job=add2<?php echo SID2URL_x; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan=2>Add a new task</td>
  </tr>
  <tr> 
   <td class="mbox" colspan=2><span class='stext'>
<?php if ($config['pccron'] == 1) { ?>
<b>Status: Cron Jobs werden simuliert</b> [<a href="admin.php?action=settings&job=cron<?php echo SID2URL_x; ?>">Ändern</a>]<br>
   Da normale "Cron Jobs", die die Aufgaben normalerweise ausführen, oft nicht zur Verfügung stehen, wurde in dieses Programm eine Simulation von Cron Jobs eingebaut. Dieses System arbeitet folgendermaßen: 
   Bei jedem Seiten-Aufruf wird überprüft, ob in der Zeit seit dem letzten Cron Job einer fällig geworden wäre. 
   Bei Bedarf wird der fällige Job ausgeführt.
<?php } else { ?>
<b>Status: Original Cron Jobs aktiv</b> [<a href="admin.php?action=settings&job=cron<?php echo SID2URL_x; ?>">Ändern</a>]<br>
Es werden normale "Cron Jobs" eingesetzt. Sie müssen, wenn nicht schon geschehen, für jeden Eintrag hier, einen eigenen Cron Job (vom System oder einem außenstehenden Dienst) starten!
<?php } ?>
   </span></td>
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Datei:</font><br>
<span class="stext">
Datei im Verzeichnis "<a target="_blank" href="admin.php?action=explorer&path=<?php echo urlencode('./classes/cron/jobs'); ?>">classes/cron/jobs</a>".<br />
<strong><a href="admin.php?action=cron&job=upload" target="_blank">Neue Datei hochladen</a></strong></span></td>
   <td class="mbox" width="50%"><input type="text" name="temp1" size="50"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Titel / Beschreibung:</font></td>
   <td class="mbox" width="50%"><input type="text" name="temp2" size="50"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Minute:</font><br><font class="stext">* = Each minute</font></td>
   <td class="mbox" width="50%">
	<select size="1" name="minute">
	<option value="-1">*</option>
	<?php 
	for ($i=0; $i<=59; $i++) {
		echo "<option value=\"$i\">$i</option>\n";
	}
	?>
	</select>
   </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Hour:</font><br><font class="stext">* = Each hour</font></td>
   <td class="mbox" width="50%">
	<select size="1" name="hour">
	<option value="-1">*</option>
	<?php 
	for ($i=0; $i<=23; $i++) {
		echo "<option value=\"$i\">$i</option>\n";
	}
	?>
	</select>
   </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Day:</font><br><font class="stext">* = Each day</font></td>
   <td class="mbox" width="50%">
	<select size="1" name="day">
	<option value="-1">*</option>
	<?php 
	for ($i=1; $i<=31; $i++) {
		echo "<option value=\"$i\">$i</option>\n";
	}
	?>
	</select>
   </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Weekday:</font><br><font class="stext">* = Each day of the week</font></td>
   <td class="mbox" width="50%">
	<select size="1" name="weekday">
	<option value="-1">*</option>
	<option value="0">Sonntag</option>
	<option value="1">Montag</option>
	<option value="2">Dienstag</option>
	<option value="3">Mittwoch</option>
	<option value="4">Donnerstag</option>
	<option value="5">Freitag</option>
	<option value="6">Samstag</option>
	</select>
   </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Month:</font><br><font class="stext">* = Each month</font></td>
   <td class="mbox" width="50%">
	<select size="1" name="month">
	<option value="-1">*</option>
	<?php 
	for ($i=1; $i<=12; $i++) {
		echo "<option value=\"$i\">$i</option>\n";
	}
	?>
	</select>
   </td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Add"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($_GET['job'] == 'add2') {
	echo head();
	
	$temp1 = $gpc->get('temp1', str);
	$minute = $gpc->get('minute', int);
	$hour = $gpc->get('hour', int);
	$day = $gpc->get('day', int);
	$month = $gpc->get('month', int);
	$weekday = $gpc->get('weekday', int);
	$temp2 = $gpc->get('temp2', none);
	
	if (!file_exists('classes/cron/jobs/'.basename($temp1))) {
		error('admin.php?action=cron&job=add', 'The specified file does not exist.');
	}
	
	if ($minute < 0) {
		$minute = '*';
	}
	if ($hour < 0) {
		$hour = '*';
	}
	if ($day < 1) {
		$day = '*';
	}
	if ($month < 1) {
		$month = '*';
	}
	if ($weekday < 0 || $weekday > 6) {
		$weekday = '*';
	}
	$line = $minute."\t".$hour."\t".$day."\t".$month."\t".$weekday."\t".$temp1."\t#".$temp2;
	$cronjobs = file('data/cron/crontab.inc.php');
	$cronjobs = array_map("rtrim", $cronjobs);
	foreach ($cronjobs as $cron) {
		if ($cron == $line) {
			error('admin.php?action=cron&job=manage', 'This entry already exists.');
		}
	}
	$cronjobs[] = $line;
	$filesystem->file_put_contents('data/cron/crontab.inc.php',implode("\n",$cronjobs));
	ok('admin.php?action=cron&job=add', 'Cron job added to crontab-file.');
}
elseif ($_GET['job'] == 'manage') {
	echo head();
	$cronjobs = file('data/cron/crontab.inc.php');
	?>
	<form name="form" method="post" action="admin.php?action=cron&job=delete<?php echo SID2URL_x; ?>">
	 <table class="border">
	  <tr> 
	   <td class="obox" colspan="7">Aufgaben verwalten</td>
	  </tr>
	  <tr> 
	   <td class="ubox" width="5%"><font class="mtext">Löschen</font></td>
	   <td class="ubox" width="55%"><font class="mtext">Datei</font></td>
	   <td class="ubox" width="8%"><font class="mtext">Minute</font></td>
	   <td class="ubox" width="8%"><font class="mtext">Stunde</font></td>
	   <td class="ubox" width="8%"><font class="mtext">Tag</font></td>
	   <td class="ubox" width="8%"><font class="mtext">Monat</font></td>
	   <td class="ubox" width="8%"><font class="mtext">Wochentag</font></td>
	  </tr>
	<?php
	foreach ($cronjobs as $job) {
		$job = rtrim($job);
		$row = explode("\t",$job);
		if (isset($row[6]) && strlen($row[6]) > 2) {
			$row[6] = substr($row[6],1);
		}
		else {
			$row[6] = '';
		}
		?>
		<tr> 
		   <td class="mbox" width="5%"><input type="checkbox" name="delete[]" value="<?php echo md5($job); ?>"></td>
		   <td class="mbox" width="55%"><font class="mtext"><?php echo $row[5]; ?></font><br><font class="stext"><?php echo $row[6]; ?></font></td>
		   <td class="mbox" width="8%"><font class="mtext"><?php echo $row[0]; ?></font></td>
		   <td class="mbox" width="8%"><font class="mtext"><?php echo $row[1]; ?></font></td> 
		   <td class="mbox" width="8%"><font class="mtext"><?php echo $row[2]; ?></font></td> 
		   <td class="mbox" width="8%"><font class="mtext"><?php echo $row[3]; ?></font></td> 
		   <td class="mbox" width="8%"><font class="mtext"><?php echo $row[4]; ?></font></td> 
		</tr>
	<?php } ?>
	  <tr> 
	   <td class="ubox" width="100%" colspan=7 align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($_GET['job'] == 'delete') {
	echo head();
	$delete = $gpc->get('delete', arr_str);
	if (count($delete) > 0) {
		$cronjobs = file('data/cron/crontab.inc.php');
		$jobs = array();
		foreach ($cronjobs as $job) {
			$job = rtrim($job);
			$md5 = md5($job);
			$jobs[$md5] = $job;
		}
		foreach ($delete as $did) {
			if (isset($jobs[$did])) {
				unset($jobs[$did]);
			}
		}
		$filesystem->file_put_contents('data/cron/crontab.inc.php',implode("\n",$jobs));
		$anz = count($cronjobs) - count($jobs);
		ok('admin.php?action=cron&job=manage'.SID2URL_x, $anz.' cron jobs deleted');
	}
	else {
		error('admin.php?action=cron&job=manage'.SID2URL_x);
	}
}
?>
