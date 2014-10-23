<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// MM/PK: MultiLangAdmin
$lang->group("admin/start");

($code = $plugins->load('admin_start_jobs')) ? eval($code) : null;

if ($job == 'save_notes') {
	$location = $gpc->get('location', str, 'admin.php?action=index');
	$filesystem->file_put_contents('admin/data/notes.php', $gpc->get('notes', none));
	header('Location: '.$location);
}
elseif (empty($job) || $job == 'start') {
	echo head();
	$notes = file_get_contents('admin/data/notes.php');
	$tasks = array();

	// Install-folder
	if (is_dir('./install/')) {
		$tasks[] = '<span style="color: red;">'.$lang->phrase('admin_task_remove_installdir1').' <strong><a href="admin.php?action=explorer&amp;job=delete_install">'.$lang->phrase('admin_task_remove_installdir2').'</a></strong></span>';
	}

	// Checked for Package Updated after Viscacha Update?
	if ($admconfig['checked_package_updates'] != 1) {
		$tasks[] = '<span style="color: red;">'.$lang->phrase('admin_checked_package_updates').' <strong><a href="admin.php?action=packages&amp;job=package_updates">'.$lang->phrase('admin_checked_package_updates_link').'</a></strong></span>';
	}

	// Offline-check
	if ($config['foffline'] == 1) {
		$tasks[] = '<span style="color: red;">'.$lang->phrase('admin_task_currently_offline1').' <a href="admin.php?action=settings&amp;job=sitestatus">'.$lang->phrase('admin_task_currently_offline2').'</a></span>';
	}

	// Count the inactive members
	$result = $db->query('SELECT COUNT(*) as activate FROM '.$db->pre.'user WHERE confirm = "00" OR confirm = "01"');
	$user = $db->fetch_assoc($result);
	if ($user['activate'] > 0) {
		$tasks[] = '<a href="admin.php?action=members&job=activate">'.$lang->phrase('admin_task_moderate_members').'</a>';
	}
	// Check for recent beackups
	$dir = "./admin/backup/";
	$handle = opendir($dir);
	$highest = 0;
	while ($file = readdir($handle)) {
		if ($file != "." && $file != ".." && !is_dir($dir.$file)) {
			$nfo = pathinfo($dir.$file);
			if ($nfo['extension'] == 'zip' || $nfo['extension'] == 'sql') {
				$date = str_replace('.zip', '', $nfo['basename']);
				$date = str_replace('.sql', '', $date);
				$valid = preg_match('/(\d{1,2})_(\d{1,2})_(\d{2,4})-(\d{1,2})_(\d{1,2})_(\d{1,2})/', $date, $mktime);
				if ($valid == 0) {
					$diff = times(filemtime($dir.$file));
				}
				else {
					$diff = times(gmmktime($mktime[4], $mktime[5], $mktime[6], $mktime[2], $mktime[1], $mktime[3]));
				}
				if ($diff > $highest) {
					$highest = $diff;
				}
			}
		}
	}
	$days = 7;
	$besttime = time()-$days*24*60*60;
	if ($highest < $besttime) {
		$last = (time()-$highest)/(24*60*60);
		if ($highest == 0) {
			$x1 = $lang->phrase('admin_task_no_backup_found');
		}
		else {
			$last = ceil($last);
			$x1 = $lang->phrase('admin_task_backup_too_old');
		}
		$tasks[] = $x1.' <a href="admin.php?action=db&job=backup">'.$lang->phrase('admin_task_backup_recommended').'</a>';
	}
	// Viscacha Version Check
	$cache = $scache->load('version_check');
	$age = time();
	if ($cache->exists()) {
		$age = $cache->age();
	}
	if ($age > 14*24*60*60) {
		$vcurl = 'admin.php?action=settings&amp;job=version';
		$tasks[] = $lang->phrase('admin_task_version_check');
	}

	$frontpage_content = '';
	$webserver = get_webserver();
	($code = $plugins->load('admin_start_tasks')) ? eval($code) : null;

	?>
	 <table class="border">
	  <tr>
	   <td class="obox">
		<span class="right"><a class="button" href="admin.php?action=logout<?php echo SID2URL_x; ?>" target="_top"><?php echo $lang->phrase('admin_sign_off'); ?></a></span>
		<?php echo $lang->phrase('admin_welcome_admin'); ?>
	   </td>
	  </tr>
	  <tr>
		<td class="mbox"><strong><?php echo $lang->phrase('admin_upcoming_tasks'); ?></strong>
		<ul>
		<?php if (count($tasks) == 0) { ?>
			<li><?php echo $lang->phrase('admin_no_tasks'); ?></li>
		<?php } else { foreach ($tasks as $task) { echo "<li>{$task}</li>"; } } ?>
		</ul>
		</td>
	  </tr>
	 </table>
	<br />
	 <table class="border">
	  <tr>
	   <td class="obox" align="center" colspan="4"><?php echo $lang->phrase('admin_program_stats'); ?></td>
	  </tr>
	  <tr>
		<td class="mmbox" width="25%"><?php echo $lang->phrase('admin_viscacha_version'); ?></td>
		<td class="mbox"  width="25%"><a href="admin.php?action=settings&job=version"><?php echo $config['version']; ?></a></td>
		<td class="mmbox" width="25%"><?php echo $lang->phrase('admin_website_offline');?></td>
		<td class="mbox"  width="25%"><?php echo noki($config['foffline'], ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=settings&job=ajax_sitestatus\')"'); ?></td>
	  </tr>
	  <tr>
		<td class="mmbox" width="25%"><?php echo $lang->phrase('admin_php_version'); ?></td>
		<td class="mbox"  width="25%"><?php echo PHP_VERSION; ?></td>
		<td class="mmbox" width="25%"><?php echo $lang->phrase('admin_database_version'); ?></td>
		<td class="mbox"  width="25%"><?php echo $db->version(); ?></td>
	  </tr>
	  <tr>
		<td class="mmbox" width="25%"><?php echo $lang->phrase('admin_webserver'); ?></td>
		<td class="mbox"  width="25%"><?php echo $webserver; ?></td>
		<td class="mmbox" width="25%"><?php echo $lang->phrase('admin_server_load'); ?></td>
		<td class="mbox"  width="25%"><?php echo serverload(); ?></td>
	  </tr>
	 </table>
	<br />
	<?php echo $frontpage_content; ?>
	<form action="admin.php?action=index&job=save_notes" method="post">
	 <table class="border">
	  <tr>
	   <td class="obox" align="center"><?php echo $lang->phrase('admin_notes'); ?></td>
	  </tr>
	  <tr>
		<td class="mbox" align="center"><textarea name="notes" rows="6" cols="120"><?php echo $notes; ?></textarea></td>
	  </tr>
	  <tr>
	   <td class="ubox" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_button_save'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<br />
	 <table class="border">
	  <tr>
	   <td class="obox" align="center" colspan="2"><?php echo $lang->phrase('admin_useful_links'); ?></td>
	  </tr>
	  <tr>
	  	<td class="mbox"><?php echo $lang->phrase('admin_php_lookup'); ?></td>
		<td class="mbox">
		<form action="http://www.php.net/manual-lookup.php" method="get">
		<input type="text" name="function" size="30" />&nbsp;
		<input type="submit" value="<?php echo $lang->phrase('admin_button_find'); ?>" />
		</form>
		</td>
	  </tr>
	  <tr>
	  	<td class="mbox"><?php echo $lang->phrase('admin_mysql_lookup'); ?></td>
		<td class="mbox">
		<form action="http://www.mysql.com/search/" method="get">
		<input type="text" name="q" size="30" />&nbsp;
		<input type="submit" value="<?php echo $lang->phrase('admin_button_find'); ?>" />
		<input type="hidden" name="doc" value="1" />
		<input type="hidden" name="m" value="o" />
		</form>
		</td>
	  </tr>
	  <tr>
	  	<td class="mbox"><?php echo $lang->phrase('admin_useful_links'); ?></td>
		<td class="mbox">
	<form>
	<select onchange="if (this.options[this.selectedIndex].value != '') { window.open(this.options[this.selectedIndex].value); } return false;">
		<option value=""><?php echo $lang->phrase('admin_useful_links'); ?></option>
		<optgroup label="PHP">
		<option value="http://www.php.net/"><?php echo $lang->phrase('admin_documentation_homepage'); ?> (PHP.net)</option>
		<option value="http://www.php.net/manual/"><?php echo $lang->phrase('admin_reference_manual'); ?></option>
		<option value="http://www.php.net/downloads.php"><?php echo $lang->phrase('admin_download_latest_version'); ?></option>
		</optgroup>
		<optgroup label="MySQL">
		<option value="http://www.mysql.com/"><?php echo $lang->phrase('admin_documentation_homepage'); ?> (MySQL.com)</option>
		<option value="http://www.mysql.com/documentation/"><?php echo $lang->phrase('admin_reference_manual'); ?></option>
		<option value="http://www.mysql.com/downloads/"><?php echo $lang->phrase('admin_download_latest_version'); ?></option>
		</optgroup>
		<optgroup label="Viscacha">
		<option value="http://www.viscacha.org/"><?php echo $lang->phrase('admin_documentation_homepage'); ?> (viscacha.org)</option>
		<option value="http://docs.viscacha.org/"><?php echo $lang->phrase('admin_reference_manual'); ?></option>
		<option value="http://files.viscacha.org/"><?php echo $lang->phrase('admin_download_latest_version'); ?></option>
		<option value="http://bugs.viscacha.org/"><?php echo $lang->phrase('admin_bugtracker_todo'); ?></option>
		</optgroup>
	</select>
	</form>
		</td>
	  </tr>
	 </table>
	<?php
	echo foot();
}
?>
