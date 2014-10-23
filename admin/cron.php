<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "cron.php") die('Error: Hacking Attempt');

if ($_GET['job'] == 'upload') {
	echo head();
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=explorer&job=upload&cfg=cron">
<table class="border" cellpadding="3" cellspacing="0" border="0">
<tr><td class="obox">Cron Job File Upload</td></tr>
<tr><td class="mbox">
To attach a file, click on the &quot;browse&quot;-button and select a file.
Then click on &quot;upload&quot; in order to complete the procedure.
Allowed file types: .php
Maximum file size: 100 KB<br /><br />
<strong>Upload file:</strong>
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
   <td class="obox" colspan="2">Add a new task</td>
  </tr>
  <tr> 
   <td class="mbox" colspan="2"><span class="stext">
<?php if ($config['pccron'] == 1) { ?>
<b>Status: Cron Jobs are simulated</b> [<a href="admin.php?action=settings&job=cron<?php echo SID2URL_x; ?>">change</a>]<br>
Because original Cron Jobs are often not availible, Viscacha can simulate Cron Jobs. This works as follows:<br />
On every page call it will be checked if there should have been a Cron Job, the due will be done if necessary.
<?php } else { ?>
<b>Status: Original Cron Jobs activated</b> [<a href="admin.php?action=settings&job=cron<?php echo SID2URL_x; ?>">change</a>]<br>
Normal Cron Jobs are used. You have to start an Cron Job for every entry (of the system or an external service), if not already done.
<?php } ?>
   </span></td>
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">File:</font><br>
<span class="stext">
File in directory "<a href="admin.php?action=explorer&path=<?php echo urlencode('./classes/cron/jobs'); ?>">classes/cron/jobs</a>".<br />
<strong><a href="admin.php?action=cron&job=upload" target="_blank">Upload new file</a></strong></span></td>
   <td class="mbox" width="50%"><input type="text" name="temp1" size="50"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><font class="mtext">Title / description:</font></td>
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
	<option value="0">Sunday</option>
	<option value="1">Monday</option>
	<option value="2">Tuesday</option>
	<option value="3">Wednesday</option>
	<option value="4">Thursday</option>
	<option value="5">Friday</option>
	<option value="6">Saturday</option>
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
	   <td class="obox" colspan="7">Manage Tasks</td>
	  </tr>
	  <tr> 
	   <td class="ubox" width="5%"><font class="mtext">Delete</font></td>
	   <td class="ubox" width="55%"><font class="mtext">File</font></td>
	   <td class="ubox" width="8%"><font class="mtext">Minute</font></td>
	   <td class="ubox" width="8%"><font class="mtext">Hour</font></td>
	   <td class="ubox" width="8%"><font class="mtext">Day</font></td>
	   <td class="ubox" width="8%"><font class="mtext">Month</font></td>
	   <td class="ubox" width="8%"><font class="mtext">Weekday</font></td>
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
	   <td class="ubox" width="100%" colspan=7 align="center"><input type="submit" name="Submit" value="Send"></td> 
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
