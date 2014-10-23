<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "cron.php") die('Error: Hacking Attempt');

($code = $plugins->load('admin_cron_jobs')) ? eval($code) : null;

if ($job == 'add') {
	echo head();
	$dir = "classes/cron/jobs/";
	$files = array();
	$d = dir($dir);
	while (false !== ($entry = $d->read())) {
		if ($entry != '..' && $entry != '.' && get_extension(strtolower($entry)) == 'php') {
			$files[] = $entry;
		}
	}
	$d->close();
	?>
<form name="form" method="post" action="admin.php?action=cron&amp;job=add2<?php echo SID2URL_x; ?>" enctype="multipart/form-data">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Add a new task</td>
  </tr>
  <tr> 
   <td class="mbox" colspan="2">
	<b>Status: Simulated Cron Jobs <?php echo iif ($config['pccron'] == 1, 'enabled', 'disabled'); ?></b>
   </td>
  </tr>
  <tr> 
   <td class="ubox" colspan="2">Specify a file and enter a title</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Title / Description:</td>
   <td class="mbox" width="50%"><input type="text" name="title" size="50" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><em>Either</em> enter a filename:<br /><span class="stext">Specify a file in the directory "classes/cron/jobs/".</span>
   </td>
   <td class="mbox" width="50%">
	<select name="filename">
	 <option value="">-- Please choose a file --</option>
	 <?php foreach ($files as $file) { ?>
	 <option value="<?php echo $file; ?>"><?php echo $file; ?></option>
	 <?php } ?>
	</select>
   </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%"><em>or</em> upload a file:<br /><span class"stext">Allowed file types: .php<br />Maximum file size: 100 KB</span>
   </td>
   <td class="mbox" width="50%"><input type="file" name="upload" size="50" /></td> 
  </tr>
  <tr> 
   <td class="ubox" colspan="2">Time to execute</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Minute:</td>
   <td class="mbox" width="50%">
	<select size="1" name="minute">
	<option value="-1">Every Minute (*)</option>
	<option value="-5">Every Five Minutes (*/5)</option>
	<option value="-10">Every Ten Minutes (*/10)</option>
	<option value="-15">Every Fifteen Minutes (*/15)</option>
	<option value="-30">Every Thirty Minutes (*/30)</option>
	<?php 
	for ($i=0; $i<60; $i++) {
		echo "<option value=\"{$i}\">{$i}</option>\n";
	}
	?>
	</select>
   </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Hour:</td>
   <td class="mbox" width="50%">
	<select size="1" name="hour">
	<option value="-1">Every Hour (*)</option>
	<option value="-2">Every Two Hours (*/2)</option>
	<option value="-3">Every Three Hours (*/3)</option>
	<option value="-4">Every Four Hours (*/4)</option>
	<option value="-6">Every Six Hours (*/6)</option>
	<option value="-12">Every Twelwe Hours (*/12)</option>
	<?php 
	for ($i=0; $i<24; $i++) {
		echo "<option value=\"{$i}\">{$i}</option>\n";
	}
	?>
	</select>
   </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Day:</td>
   <td class="mbox" width="50%">
	<select size="1" name="day">
	<option value="-1">Every Day (*)</option>
	<option value="-2">Every Two Days (*/2)</option>
	<option value="-14">Every Fourteen Days (*/14)</option>
	<?php 
	for ($i=1; $i<=31; $i++) {
		echo "<option value=\"{$i}\">{$i}</option>\n";
	}
	?>
	</select>
   </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Weekday:</td>
   <td class="mbox" width="50%">
	<select size="1" name="weekday">
	<option value="-1">Every Weekday (*)</option>
	<?php
	foreach ($days as $id => $name) {
		echo "<option value=\"{$id}\">{$name}</option>\n";
	}
	?>
	</select>
   </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Month:</td>
   <td class="mbox" width="50%">
	<select size="1" name="month">
	<option value="-1">Every Month (*)</option>
	<?php 
	for ($i=1; $i<=12; $i++) {
		echo "<option value=\"{$i}\">".$months[$i-1]."</option>\n";
	}
	?>
	</select>
   </td> 
  </tr>
  <tr> 
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Add" /></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'add2') {
	echo head();
	
	$title = $gpc->get('title', none);
	$filename = $gpc->get('filename', str);
	$minute = $gpc->get('minute', int);
	$hour = $gpc->get('hour', int);
	$day = $gpc->get('day', int);
	$month = $gpc->get('month', int);
	$weekday = $gpc->get('weekday', int);

	$inserterrors = array();
	if ((empty($filename) || !file_exists('classes/cron/jobs/'.$filename)) && empty($_FILES['upload']['name'])) {
		$inserterrors[] = 'No file specified. Either upload a file or specify a file in the select-box.';
	}
	if (empty($title)) {
		$inserterrors[] = 'You have not specified a title.';
	}

	if (count($inserterrors) == 0 && !empty($_FILES['upload']['name'])) {
		require("classes/class.upload.php");
		 
		$dir = realpath('./classes/cron/jobs/');
		$my_uploader = new uploader();
		$my_uploader->max_filesize(100*1024);
		$my_uploader->file_types(array('php'));
		$my_uploader->set_path($dir.DIRECTORY_SEPARATOR);
		if ($my_uploader->upload('upload')) {
			$my_uploader->save_file();
		}
		if ($my_uploader->upload_failed()) {
			array_push($inserterrors, $my_uploader->get_error());
		}
		else {
			$filename = $my_uploader->fileinfo('filename');
		}
		if (empty($filename) || !file_exists('classes/cron/jobs/'.$filename)) {
			$inserterrors[] = 'File could not be uploaded.';
		}
	}
	
	if (count($inserterrors) > 0) {
		error('admin.php?action=cron&job=add', $inserterrors);
	}
	else {
		if ($minute > -60 && $minute < -1) {
			$minute *= -1;
			$minute = "*/{$minute}";
		}
		elseif ($minute >= 0 && $minute < 60) {}
		else {
			$minute = '*';
		}
		if ($hour > -24 && $hour < -1) {
			$hour *= -1;
			$hour = "*/{$hour}";
		}
		elseif ($hour >= 0 && $hour < 24) {}
		else {
			$hour = '*';
		}
		if ($day >= -31 && $day < -1) {
			$day *= -1;
			$day = "*/{$day}";
		}
		elseif ($day > 0 && $day <= 31) {}
		else {
			$day = '*';
		}
		if ($month < 1 || $month > 12) {
			$month = '*';
		}
		if ($weekday < 0 || $weekday > 6) {
			$weekday = '*';
		}
		
		$line = "{$minute}\t{$hour}\t{$day}\t{$month}\t{$weekday}\t{$filename}\t#{$title}";
		$cronjobs = file('data/cron/crontab.inc.php');
		$cronjobs = array_map("rtrim", $cronjobs);
		foreach ($cronjobs as $cron) {
			if ($cron == $line) {
				error('admin.php?action=cron&job=manage', 'This entry already exists.');
			}
		}
		$cronjobs[] = $line;
		$filesystem->file_put_contents('data/cron/crontab.inc.php', implode("\n",$cronjobs));
		ok('admin.php?action=cron&job=manage', 'Cron Job successfully added');
	}
}
elseif ($job == 'manage') {
	echo head();
	$cronjobs = file('data/cron/crontab.inc.php');
	?>
	<form name="form" method="post" action="admin.php?action=cron&job=delete<?php echo SID2URL_x; ?>">
	 <table class="border">
	  <tr> 
	   <td class="obox" colspan="7">
	    <span style="float: right;">
		<a class="button" href="admin.php?action=cron&job=add">Add new Task</a>
		<a class="button" href="admin.php?action=slog&job=l_cron">Tasks Log File</a>
		</span>
	   	Manage Tasks
	   </td>
	  </tr>
	  <tr> 
	   <td class="mbox" colspan="7">
		<?php if ($config['pccron'] == 1) { ?>
		<b>Status: Simulated Cron Jobs enabled</b>&nbsp;&nbsp;&nbsp;<a class="button" href="admin.php?action=settings&amp;job=cron<?php echo SID2URL_x; ?>">Change</a><br>
		Because Cron Jobs are often not availible, Viscacha can simulate Cron Jobs. This works as follows: On every page call, it will be checked if there should have been a Cron Job executed. If the time limit of a Cron Job is exceeded, it will be executed in the background.
		<?php } else { ?>
		<b>Status: Simulated Cron Jobs disabled</b>&nbsp;&nbsp;&nbsp;<a class="button" href="admin.php?action=settings&amp;job=cron<?php echo SID2URL_x; ?>">Change</a><br>
		Cron Jobs are not simuleted by Viscacha. You have to set up a Cron Job that starts the installed Cron Jobs automatically.
		<?php } ?>
	   </td>
	  </tr>
	  <tr> 
	   <td class="ubox" width="5%">Delete</td>
	   <td class="ubox" width="55%">File</td>
	   <td class="ubox" width="8%">Minute(s)</td>
	   <td class="ubox" width="8%">Hour(s)</td>
	   <td class="ubox" width="8%">Day(s)</td>
	   <td class="ubox" width="8%">Month</td>
	   <td class="ubox" width="8%">Weekday</td>
	  </tr>
	<?php
	foreach ($cronjobs as $job) {
		$job = rtrim($job);
		$row = explode("\t", $job, 7);
		for($i = 0; $i <= 4; $i++) {
			if ($row[$i] == '*') {
				$row[$i] = 'Every';
			}
			elseif (substr($row[$i], 0, 2) == '*/') {
				$row[$i] = 'Every '.substr($row[$i], 2);
			}
			else {
				$row[$i] = intval($row[$i]);
			}
		}
		if (strlen($row[6]) > 0 && substr($row[6], 0, 1) == '#') {
			$row[6] = substr($row[6], 1);
		}
		?>
		<tr> 
		   <td class="mbox" width="5%"><input type="checkbox" name="delete[]" value="<?php echo md5($job); ?>"></td>
		   <td class="mbox" width="55%"><?php echo $row[5]; ?><br /><span class="stext"><?php echo $row[6]; ?></span></td>
		   <td class="mbox" width="8%"><?php echo $row[0]; ?></td>
		   <td class="mbox" width="8%"><?php echo $row[1]; ?></td> 
		   <td class="mbox" width="8%"><?php echo $row[2]; ?></td> 
		   <td class="mbox" width="8%"><?php echo $row[3]; ?></td> 
		   <td class="mbox" width="8%"><?php echo $row[4]; ?></td> 
		</tr>
	<?php } ?>
	  <tr> 
	   <td class="ubox" width="100%" colspan="7" align="center"><input type="submit" name="Submit" value="Send" /></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'delete') {
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
		$filesystem->file_put_contents('data/cron/crontab.inc.php', implode("\n",$jobs));
		$anz = count($cronjobs) - count($jobs);
		ok('admin.php?action=cron&job=manage'.SID2URL_x, $anz.' cron jobs deleted.');
	}
	else {
		error('admin.php?action=cron&job=manage'.SID2URL_x);
	}
}
?>
