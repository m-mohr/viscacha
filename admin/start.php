<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "start.php") die('Error: Hacking Attempt');

if ($job == 'save_notes') {
	$location = $gpc->get('location', str, 'admin.php?action=index');
	$filesystem->file_put_contents('admin/data/notes.php', $gpc->get('notes', none));
	header('Location: '.$location);
}
else {
	echo head();
	$notes = file_get_contents('admin/data/notes.php');
	$tasks = array();
	// Count the inactive members
	$result = $db->query('SELECT COUNT(*) as activate FROM '.$db->pre.'user WHERE confirm = "00" OR confirm = "01"');
	$user = $db->fetch_assoc($result);
	if ($user['activate'] > 0) {
		$tasks[] = '<li><a href="admin.php?action=members&job=activate">'.$user['activate'].' Users to Moderate/Unlock</a></li>';
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
					$diff = filemtime($dir.$file);
				}
				else {
					$diff = mktime($mktime[4], $mktime[5], $mktime[6], $mktime[2], $mktime[1], $mktime[3]);
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
			$x1 = 'No backup found.';
		}
		else {
			$x1 = 'Your last backup is '.ceil($last).' days old.';
		}
		$tasks[] = '<li>'.$x1.' <a href="admin.php?action=db&job=backup">It is recommended to create a new backup of your database!</a></li>';
	}
	
	$frontpage_content = '';
	$webserver = get_webserver();
	($code = $plugins->load('admin_start_tasks')) ? eval($code) : null;
	
	?>
	 <table class="border">
	  <tr> 
	   <td class="obox">
		<span class="right">[<a href="admin.php?action=logout<?php echo SID2URL_x; ?>" target="_top">Sign off</a>]</span>
		Welcome to the Viscacha Admin Control Panel, <?php echo $my->name; ?>!
	   </td>
	  </tr>
	  <tr> 
		<td class="mbox"><strong>Upcoming Tasks:</strong>
		<ul>
		<?php if (count($tasks) == 0) { ?>
		<li>No upcoming tasks available!</li>
		<?php
		} 
		else {
			echo implode("\n", $tasks);
		}
		?>
		</ul>
		</td>
	  </tr>
	 </table>
	<br />
	 <table class="border">
	  <tr> 
	   <td class="obox" align="center" colspan="4">Program Statistics</td>
	  </tr>
	  <tr> 
		<td class="mmbox" width="25%">Viscacha Version:</td>
		<td class="mbox"  width="25%"><a href="admin.php?action=settings&job=version"><?php echo $config['version']; ?></a></td>
		<td class="mmbox" width="25%">Website Offline:</td>
		<td class="mbox"  width="25%"><?php echo noki($config['foffline'], ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=settings&job=ajax_sitestatus\')"'); ?></td>
	  </tr>
	  <tr> 
		<td class="mmbox" width="25%">PHP Version:</td>
		<td class="mbox"  width="25%"><?php echo PHP_VERSION; ?></td>
		<td class="mmbox" width="25%">MySQL Version:</td>
		<td class="mbox"  width="25%"><?php echo $db->version(); ?></td>
	  </tr>
	  <tr> 
		<td class="mmbox" width="25%">Web Server:</td>
		<td class="mbox"  width="25%"><?php echo $webserver; ?></td>
		<td class="mmbox" width="25%">Server Load:</td>
		<td class="mbox"  width="25%"><?php echo serverload(); ?></td>
	  </tr>
	 </table>
	<br />
	<?php echo $frontpage_content; ?>
	<form action="admin.php?action=index&job=save_notes" method="post">
	 <table class="border">
	  <tr> 
	   <td class="obox" align="center">Administrator Notes</td>
	  </tr>
	  <tr> 
		<td class="mbox" align="center"><textarea name="notes" rows="6" cols="120"><?php echo $notes; ?></textarea></td>
	  </tr>
	  <tr> 
	   <td class="ubox" align="center"><input type="submit" value="Save"></td>
	  </tr>
	 </table>
	</form>
	<br />
	 <table class="border">
	  <tr> 
	   <td class="obox" align="center" colspan="2">Useful Links</td>
	  </tr>
	  <tr>
	  	<td class="mbox">PHP Function Lookup</td>
		<td class="mbox">
		<form action="http://www.php.net/manual-lookup.php" method="get">
		<input type="text" name="function" size="30" />&nbsp;
		<input type="submit" value="Find" />
		</form>
		</td>
	  </tr>
	  <tr>
	  	<td class="mbox">MySQL Language Lookup</td>
		<td class="mbox">
		<form action="http://www.mysql.com/search/" method="get">
		<input type="text" name="q" size="30" />&nbsp;
		<input type="submit" value="Find" />
		<input type="hidden" name="doc" value="1" />
		<input type="hidden" name="m" value="o" />
		</form>
		</td>
	  </tr>
	  <tr>
	  	<td class="mbox">Useful Links</td>
		<td class="mbox">
	<form>
	<select onchange="if (this.options[this.selectedIndex].value != '') { window.open(this.options[this.selectedIndex].value); } return false;">
		<option value="">-- Useful Links --</option>
		<optgroup label="PHP">
		<option value="http://www.php.net/">Home Page (PHP.net)</option>
		<option value="http://www.php.net/manual/">Reference Manual</option>
		<option value="http://www.php.net/downloads.php">Download Latest Version</option>
		</optgroup>
		<optgroup label="MySQL">
		<option value="http://www.mysql.com/">Home Page (MySQL.com)</option>
		<option value="http://www.mysql.com/documentation/">Reference Manual</option>
		<option value="http://www.mysql.com/downloads/">Download Latest Version</option>
		</optgroup>
		<optgroup label="Viscacha">
		<option value="http://www.viscacha.org/">Home Page (viscacha.org)</option>
		<option value="http://docs.viscacha.org/">Reference Manual</option>
		<option value="http://files.viscacha.org/">Download Latest Version</option>
		<option value="http://bugs.viscacha.org/">Bugtracker &amp; ToDo</option>
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
