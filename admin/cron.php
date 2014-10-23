<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// PK: MultiLangAdmin
$lang->group("admin/cron");

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
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_add_a_new_task'); ?></td>
  </tr>
  <tr>
   <td class="mbox" colspan="2">
	<b><?php echo $lang->phrase('admin_cron_status'); ?> <?php echo iif ($config['pccron'] == 1, $lang->phrase('admin_status_enabled'), $lang->phrase('admin_status_disabled')); ?></b>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_specify_a_file'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_title_description'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="title" size="50" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_enter_a_filename'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_enter_a_filename2'); ?></span></td>
   <td class="mbox" width="50%">
	<select name="filename">
	 <option value=""><?php echo $lang->phrase('admin_option_choose_a_file'); ?></option>
	 <?php foreach ($files as $file) { ?>
	 <option value="<?php echo $file; ?>"><?php echo $file; ?></option>
	 <?php } ?>
	</select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_upload_a_file'); ?><br /><span class"stext"><?php echo $lang->phrase('admin_upload_a_file2'); ?></span></td>
   <td class="mbox" width="50%"><input type="file" name="upload" size="50" /></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_time_to_execute'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_minute'); ?></td>
   <td class="mbox" width="50%">
	<select size="1" name="minute">
	<option value="-1"><?php echo $lang->phrase('admin_every_minute'); ?></option>
	<option value="-5"><?php echo $lang->phrase('admin_every_5_minutes'); ?></option>
	<option value="-10"><?php echo $lang->phrase('admin_every_10_minutes'); ?></option>
	<option value="-15"><?php echo $lang->phrase('admin_every_15_minutes'); ?></option>
	<option value="-30"><?php echo $lang->phrase('admin_every_30_minutes'); ?></option>
	<?php
	for ($i=0; $i<60; $i++) {
		echo "<option value=\"{$i}\">{$i}</option>\n";
	}
	?>
	</select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_hour'); ?></td>
   <td class="mbox" width="50%">
	<select size="1" name="hour">
	<option value="-1"><?php echo $lang->phrase('admin_every_hour'); ?></option>
	<option value="-2"><?php echo $lang->phrase('admin_every_2_hours'); ?></option>
	<option value="-3"><?php echo $lang->phrase('admin_every_3_hours'); ?></option>
	<option value="-4"><?php echo $lang->phrase('admin_every_4_hours'); ?></option>
	<option value="-6"><?php echo $lang->phrase('admin_every_6_hours'); ?></option>
	<option value="-12"><?php echo $lang->phrase('admin_every_12_hours'); ?></option>
	<?php
	for ($i=0; $i<24; $i++) {
		echo "<option value=\"{$i}\">{$i}</option>\n";
	}
	?>
	</select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_day'); ?></td>
   <td class="mbox" width="50%">
	<select size="1" name="day">
	<option value="-1"><?php echo $lang->phrase('admin_every_day'); ?></option>
	<option value="-2"><?php echo $lang->phrase('admin_every_2_days'); ?></option>
	<option value="-14"><?php echo $lang->phrase('admin_every_14_days'); ?></option>
	<?php
	for ($i=1; $i<=31; $i++) {
		echo "<option value=\"{$i}\">{$i}</option>\n";
	}
	?>
	</select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_weekday'); ?></td>
   <td class="mbox" width="50%">
	<select size="1" name="weekday">
	<option value="-1"><?php echo $lang->phrase('admin_every_weekday'); ?></option>
	<?php
	foreach ($days as $id => $name) {
		echo "<option value=\"{$id}\">{$name}</option>\n";
	}
	?>
	</select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_month'); ?></td>
   <td class="mbox" width="50%">
	<select size="1" name="month">
	<option value="-1"><?php echo $lang->phrase('admin_every_month'); ?></option>
	<?php
	for ($i=1; $i<=12; $i++) {
		echo "<option value=\"{$i}\">".$months[$i-1]."</option>\n";
	}
	?>
	</select>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_button_add'); ?>" /></td>
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
		$inserterrors[] = $lang->phrase('admin_err_no_file_specified');
	}
	if (empty($title)) {
		$inserterrors[] = $lang->phrase('admin_err_no_title_specified');
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
			$inserterrors[] = $lang->phrase('admin_err_upload_failed');
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
				error('admin.php?action=cron&job=manage', $lang->phrase('admin_err_entry_already_exists'));
			}
		}
		$cronjobs[] = $line;
		$filesystem->file_put_contents('data/cron/crontab.inc.php', implode("\n",$cronjobs));
		ok('admin.php?action=cron&job=manage', $lang->phrase('admin_job_successfully_added'));
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
		<a class="button" href="admin.php?action=cron&job=add"><?php echo $lang->phrase('admin_add_new_task'); ?></a>
		<a class="button" href="admin.php?action=slog&job=l_cron"><?php echo $lang->phrase('admin_tasks_logfile'); ?></a>
		</span>
	   	<?php echo $lang->phrase('admin_manage_tasks'); ?>
	   </td>
	  </tr>
	  <tr>
	   <td class="mbox" colspan="7">
	   <b>
		<?php echo $lang->phrase('admin_cron_status').iif ($config['pccron'] == 1, $lang->phrase('admin_status_enabled'), $lang->phrase('admin_status_disabled')); ?>
		</b>
		&nbsp;&nbsp;&nbsp;<a class="button" href="admin.php?action=settings&amp;job=cron"><?php echo $lang->phrase('admin_cron_change'); ?></a><br />
		<?php
			echo iif($config['pccron'] == 1, $lang->phrase('admin_status_enabled_info'), $lang->phrase('admin_status_disabled_info'));
		?>
	   </td>
	  </tr>
	  <tr>
	   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_th_delete'); ?></td>
	   <td class="ubox" width="55%"><?php echo $lang->phrase('admin_th_file'); ?></td>
	   <td class="ubox" width="8%"><?php echo $lang->phrase('admin_th_minutes'); ?></td>
	   <td class="ubox" width="8%"><?php echo $lang->phrase('admin_th_hours'); ?></td>
	   <td class="ubox" width="8%"><?php echo $lang->phrase('admin_th_days'); ?></td>
	   <td class="ubox" width="8%"><?php echo $lang->phrase('admin_th_month'); ?></td>
	   <td class="ubox" width="8%"><?php echo $lang->phrase('admin_th_weekday'); ?></td>
	  </tr>
	<?php
	foreach ($cronjobs as $job) {
		$job = rtrim($job);
		$row = explode("\t", $job, 7);
		for($i = 0; $i <= 4; $i++) {
			if ($row[$i] == '*') {
				$row[$i] = $lang->phrase('admin_every');
			}
			elseif (substr($row[$i], 0, 2) == '*/') {
				$what = substr($row[$i], 2);
				$row[$i] = $lang->phrase('admin_every_x');
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
	   <td class="ubox" width="100%" colspan="7" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_button_send'); ?>" /></td>
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
		ok('admin.php?action=cron&job=manage', $lang->phrase('admin_cron_jobs_deleted'));
	}
	else {
		error('admin.php?action=cron&job=manage');
	}
}
?>
