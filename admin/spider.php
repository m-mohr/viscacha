<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "spider.php") die('Error: Hacking Attempt');

// This version bases on a phpBB Mod

$bot_errors = array();

$submit = ((isset($_POST['submit'])) ? true : false);
$action = $gpc->get('job', str, 'manage');
$id = $gpc->get('id', int);
$mark = $gpc->get('mark', arr_int);

echo head();

if ($job == 'ignore_pending' || $job == 'add_pending') {

	$pending_number = $gpc->get('pending', int); 
	$pending_data = $gpc->get('data', str); 

	$result = $db->query("SELECT pending_{$pending_data} FROM {$db->pre}spider WHERE id = ".$id);

	$row = $db->fetch_assoc($result);

	$pending_array = explode('|', $row['pending_'.$pending_data]);

	if ($action == 'add_pending') {
		$new_data = $pending_array[($pending_number-1)*2];
	}

	array_splice($pending_array, ($pending_number-1)*2, 2);
	$pending = implode("|", array_empty_trim($pending_array));

	$result = $db->query("UPDATE {$db->pre}spider SET pending_{$pending_data} = '{$pending}' WHERE id = ".$id);

	if ($action == "add_pending") {
	
		$result = $db->query("SELECT bot_{$pending_data} FROM {$db->pre}spider WHERE id = ".$id);
		$row = $db->fetch_assoc($result);

		$pending_array = explode('|', $row['bot_' . $pending_data]);

		$new_data = str_replace("|", "&#124;", $new_data);

		$pending_added = false;

		// are we dealing with an ip or user agent?
		if ($pending_data == "ip") {
			// loop through ip's
			$count = count($pending_array);
			for ( $loop = 0; $loop < $count; $loop++) {
				$ip_found = false;

				for ( $limit = 9; $limit <= 15; $limit++ ) {
					if (strcmp(substr($pending_array[$loop],0,$limit) , substr($new_data, 0, $limit))!=0) {
						if ($ip_found == true) {
							$pending_array[$loop] = substr($pending_array[$loop],0,($limit-1));
							$pending_added = true;
						}
					}
					else {
						$ip_found = true;
					}
				}
			}
		}
		else {
			// loop through user agent's
			$count = count($pending_array);
			for ( $loop = 0; $loop < $count; $loop++) {
				// which user agent string is shorter?
				$smaller_string = ( ( strlen($pending_array[$loop]) > strlen($new_data) ) ? $new_data : $pending_array[$loop]);
				$larger_string = ( ( strlen($pending_array[$loop]) < strlen($new_data) ) ? $new_data : $pending_array[$loop]);

				// shortest user agent string too short?
				if (strlen($smaller_string) <= 6) {
					continue;
				}

				for ( $limit = strlen($smaller_string); $limit > 6; $limit-- ) {
					$count2 = (strlen($smaller_string)-$limit)+1;
					for ($loop2 = 0; $loop2 < count2; $loop2++) {
						if (strstr($larger_string, substr($smaller_string, $loop2, $limit))) {
							$pending_array[$loop] = $smaller_string;
							$pending_added = true;
						}
					}
				}
			}
		}

		// insert new data into array
		if (!$pending_added) {
			$pending_array[] = $new_data;
		}

		$pending = implode("|", array_empty_trim($pending_array));

		$db->query("UPDATE {$db->pre}spider SET bot_{$pending_data} = '$pending' WHERE id = " . $id);
		
		$delobj = $scache->load('spiders');
		$delobj->delete();
	}
	
	ok("admin.php?action=spider&job=pending", "Bot information successfully ".iif($job == 'add_pending', "added", "ignored").".");
	echo foot();
}
elseif ($job == 'delete') {
	$mark = array_empty_trim($mark);
	if ($id > 0 || count($mark) > 0) {
		$id = ($id > 0) ? " = {$id}" : ' IN (' . implode(', ', $mark) . ')';
		$db->query("DELETE FROM {$db->pre}spider WHERE id {$id}");
		if ($db->affected_rows() == 0) {
			error("admin.php?action=spider&job=manage", "No entries deleted.");
		}
		else {
			$delobj = $scache->load('spiders');
			$delobj->delete();
			ok("admin.php?action=spider&job=manage", "Bot data successfully deleted.");
		}
	}
	else {
		error("admin.php?action=spider&job=manage", "No data chosen.");
	}
}
elseif ($job == 'add2' || $job == 'edit2') {
	$bot_ip = $gpc->get('bot_ip', str);
	$bot_ip = trim($bot_ip);
	$bot_agent = $gpc->get('bot_agent', str);
	if ( empty($bot_ip) && empty($bot_agent) ) {
		$bot_errors[] = "You have not supplied a vaild user agent or ip.";
	}
	$type = $gpc->get('type', str);
	$bot_name = $gpc->get('bot_name', str);
	if ( empty($bot_name) ) {
		$bot_errors[] = "You have not supplied a bot name.";
	}

	if (count($bot_errors) > 0) {
		error("admin.php?action=spider&job=".$job.iif($id > 0, '&id='.$id), $bot_errors);
	}
	else {
		if ($job == 'add2') {
			$db->query("INSERT INTO {$db->pre}spider (name, user_agent, bot_ip, type) VALUES ('{$bot_name}', '{$bot_agent}', '{$bot_ip}', '{$type}')");
		}
		else {
			$bot_visits = $gpc->get('visits', int);
			$reset_lastvisit = $gpc->get('reset_lastvisit', int);
			if ($reset_lastvisit == 1) {
				$result = $db->query("SELECT last_visit FROM {$db->pre}spider WHERE id = '{$id}'");
				$lvdat = $db->fetch_assoc($result);
				$lastvisits = explode('|', $lvdat['last_visit']);
				$lastvisits = array_empty_trim($lastvisits);
				if (count($lastvisits) > 0) {
					$last = max($lastvisits);
				}
				else {
					$last = '';
				}
			}
			else {
				$last = '';
			}
			$db->query("UPDATE {$db->pre}spider SET name='{$bot_name}', user_agent='{$bot_agent}', bot_ip='{$bot_ip}', bot_visits='{$bot_visits}'".iif($reset_lastvisit > 0, ", last_visit = '{$last}'")." WHERE id = '{$id}'");
		}
		if ($db->affected_rows() <> 1) {
			error("admin.php?action=spider&job=".iif($job == 'edit2', 'edit', 'add').iif($id > 0, '&id='.$id), "No data changed.");
		}
		else {
			$delobj = $scache->load('spiders');
			$delobj->delete();
			ok("admin.php?action=spider&job=manage", "Bot settings successfully changed.");
		}
	}
}
elseif ($job == 'add' || $job == 'edit') {
	if ($job == 'edit') {
		$result = $db->query("SELECT name, user_agent, bot_ip, type, bot_visits FROM {$db->pre}spider WHERE id = '{$id}'");
		if ($db->num_rows($result) == 1) {
			$row = $db->fetch_assoc($result);
		}
		else {
			error("admin.php?action=spider&job=manage", "Couldn't obtain bot data.");
		}
	}
	else {
		$row = array('name' => '', 'user_agent' => '', 'bot_ip' => '', 'type' => '');
	}
	?>
	<form action="admin.php?action=spider&amp;job=<?php echo $job; ?>2<?php echo iif($id > 0, '&amp;id='.$id); ?>" method="post">
	<table border="0" align="center" class="border">
		<tr>
			<td class="obox" colspan="2"><?php echo iif($action == 'add', 'Add', 'Edit'); ?> Bots</th>
		</tr>
		<tr>
			<td class="ubox" colspan="2">Here you can either add or modify an existing bot entry. You are able to supply either a matching user agent or a range of ip's to use.</th>
		</tr>
		<tr>
			<td class="mbox" width="40%">Name:<br /><span class="stext">This name is shown on the "who is online"-page.</span></td>
			<td class="mbox"><input type="text" name="bot_name" size="50"<?php echo iif($job == 'edit', ' value="'.$row['name'].'"'); ?> /></td>
		</tr>
		<tr>
			<td class="mbox" width="40%">User Agent:<br /><span class="stext">A matching user agent. Partial matches are allowed. Seperate agents with a single '|'.</span></td>
			<td class="mbox"><input type="text" name="bot_agent" size="50"<?php echo iif($job == 'edit', ' value="'.$row['user_agent'].'"'); ?> /></td>
		</tr>
		<tr>
			<td class="mbox" width="40%">IP Adress:<br /><span class="stext">Partial matches are allowed. Seperate IP addresses with a single '|'.</span></td>
			<td class="mbox"><input type="text" name="bot_ip" size="50"<?php echo iif($job == 'edit', ' value="'.$row['bot_ip'].'"'); ?> /></td>
		</tr>
		<tr>
			<td class="mbox" width="40%">Type:<br /><span class="stext">Mail collectors will be banned.</span></td>
			<td class="mbox">
				<input type="radio" name="type" value="b"<?php echo iif(($job == 'edit' && $row['type'] == 'b'), ' checked="checked"'); ?> /> Search engine<br />
   				<input type="radio" name="type" value="e"<?php echo iif(($job == 'edit' && $row['type'] == 'e'), ' checked="checked"'); ?> /> Mail-Collector<br />
   				<input type="radio" name="type" value="v"<?php echo iif(($job == 'edit' && $row['type'] == 'v'), ' checked="checked"'); ?> /> Validator
   			</td>
		</tr>
		<?php if ($job == 'edit') { ?>
		<tr>
			<td class="mbox" width="40%">Visits:</td>
			<td class="mbox"><input type="text" name="visits" size="10" value="<?php echo $row['bot_visits']; ?>" /></td>
		</tr>
		<tr>
			<td class="mbox" width="40%">Reset Last Visits:<br /><span class="stext"></span></td>
			<td class="mbox">
			<input type="radio" name="reset_lastvisit" value="0" checked="checked" />Keep the "Last Vists". Don't change them!<br />
			<input type="radio" name="reset_lastvisit" value="1" />The "Last Visits" will be deleted except for the really last visit.<br />
			<input type="radio" name="reset_lastvisit" value="2" />The "Last Visits" will be completely deleted.
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td class="ubox" colspan="2" align="center"><input type="submit" name="submit" value="Submit" /></td>
		</tr>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'pending') {
	$result = $db->query("SELECT id, name, pending_agent, pending_ip, type FROM {$db->pre}spider");
	$pending_bots = 0;
	?>
	<table border="0" align="center" class="border">
	<tr><td class="obox">Pending Bots</td></tr>
	<tr><td class="mbox">
	<?php if ($config['spider_pendinglist'] == 0) { ?>
	<p><strong>This function is currently disabled. You can turn it on in your <a href="admin.php?action=settings&amp;job=spiders">Viscacha Settings</a>!</strong></p>
	<?php } ?>
	<p>Listed below are users that matched some but not all of your bot criteria. In other words the user only matched either the user agent or ip. The mismatched data is the highlighted next to the bot name. You can choose to either add this info which will then appear as part of that bots criteria or ignore it.</p>
	<?php
	while ($row = $db->fetch_assoc($result)) {
		$pending_agent_array = array();
		$pending_ip_array = array();
		
		if ( !empty( $row['pending_agent'] ) ) {
			$pending_agent_array = explode('|', $row['pending_agent']);
			if (count($pending_agent_array) > 0) {
				$pending_bots++;
			}
		}
		if ( !empty( $row['pending_ip'] ) ) {
			$pending_ip_array = explode('|', $row['pending_ip']);
			if (count($pending_ip_array) > 0 && count($pending_agent_array) == 0) {
				$pending_bots++;
			}
		}
		natsort($pending_agent_array);
		natsort($pending_ip_array);
		
		if (count($pending_agent_array) > 0 || count($pending_ip_array) > 0) {
			?>
			<table border="0" align="center" class="border">
			<tr><td class="obox" colspan="3"><?php echo $row['name']; ?></td></tr>
			<tr class="ubox">
				<td nowrap="nowrap">IP Adress</td>
				<td nowrap="nowrap">User Agent</td>
				<td nowrap="nowrap">Actions</td>
			</tr>
			<?php
		}
		
		if (count($pending_agent_array) > 0) {
			for ($loop = 0; $loop < count($pending_agent_array); $loop+=2) {
			?>
			<tr>
				<td class="mbox" width="15%" align="center" nowrap="nowrap"><?php echo $pending_agent_array[$loop]; ?></td>
				<td class="mbox" width="25%" align="center" nowrap="nowrap"><b><?php echo $pending_agent_array[$loop+1]; ?></b></td>
				<td class="mbox" width="20%" align="center">[<a href="admin.php?action=spider&id=<?php echo $row['id']; ?>&pending=<?php echo ($loop/2)+1; ?>&data=agent&job=ignore_pending">Ignore</a>&nbsp;[<a href="admin.php?action=spider&id=<?php echo $row['id']; ?>&pending=<?php echo ($loop/2)+1; ?>&data=agent&job=add_pending">Add</a>]</td>
			</tr>
			<?php
			}
		}
	
		if (count($pending_ip_array) > 0) {
			for ($loop = 0; $loop < count($pending_ip_array); $loop+=2) {
			?>
			<tr>
				<td class="mbox" width="15%" align="center" nowrap="nowrap"><b><?php echo $pending_ip_array[$loop]; ?></b></td>
				<td class="mbox" width="25%" align="center" nowrap="nowrap"><?php echo $pending_ip_array[$loop+1]; ?></td>
				<td class="mbox" width="20%" align="center">[<a href="admin.php?action=spider&id=<?php echo $row['id']; ?>&pending=<?php echo ($loop/2)+1; ?>&data=ip&job=ignore_pending">Ignore</a>]&nbsp;[<a href="admin.php?action=spider&id=<?php echo $row['id']; ?>&pending=<?php echo ($loop/2)+1; ?>&data=ip&job=add_pending">Add</a>]</td>
			</tr>
			<?php
			}
		}
		
		if (count($pending_agent_array) > 0 || count($pending_ip_array) > 0) {
			?>
			</table><br class="minibr" />
			<?php
		}
	}
	if ($pending_bots == 0 && $config['spider_pendinglist'] == 1) {
	?>
		<p align="center"><b>Sorry there are currently no pending bots in the database</b></p>
	<?php } ?>
	</td></tr>
	</table>
	<?php
	echo foot();
}
else {
	?>
	<table border="0" align="center" class="border">
	<tr><td class="obox">Manage Bots</td></tr>
	<tr><td class="mbox">
	<p>Bots (also known as crawlers or spiders) are automated agents most commonly used to index information on the internet. Very few of these bots support sessions and can therefore fail to index your site correctly. Here you can define the assigning of session ids to these bots to solve this problem.</p>
	<?php if ($config['spider_logvisits'] == 0) { ?>
	<p><em>The logging of visits and last visits is currently disabled, but old data may be shown. You can turn it on in your <a href="admin.php?action=settings&amp;job=spiders">Viscacha Settings</a>!</em></p>
	<?php } ?>
	<form action="admin.php?action=spider" method="post">
	<table border="0" align="center" class="border">
	<?php
	$result = $db->query("SELECT id, name, last_visit, bot_visits, type, user_agent FROM {$db->pre}spider ORDER BY type, name");
	if ($db->num_rows($result) > 0) {
		$category = '';
		while ($row = $db->fetch_assoc($result)) {
			
			if ($row['type'] != $category) {
				$category = $row['type'];
				if ($row['type'] == 'v') {
					$row['type'] = 'Validator';
				}
				elseif ($row['type'] == 'e') {
					$row['type'] = 'Mail-Collector';
				}
				else {
					$row['type'] = 'Search engine';
				}
				if (!empty($category)) {
				?>
					</table>
					<br class="minibr" />
				<?php } ?>
				<table border="0" align="center" class="border">
				<tr>
					<td class="obox" colspan="6"><?php echo $row['type']; ?></td>
				</tr>
				<tr>
					<td class="ubox" nowrap="nowrap">Name</td>
					<td class="ubox" nowrap="nowrap">User Agents (Count)</td>
					<td class="ubox" nowrap="nowrap">Visits</td>
					<td class="ubox" nowrap="nowrap">Last Visit</td>
					<td class="ubox" nowrap="nowrap">Actions</td>
					<td class="ubox" nowrap="nowrap">Mark</td>
				</tr>
				<?php
			}

			$useragents = explode('|', $row['user_agent']);
			if (empty($useragents[0])) {
				$useragent = 'Not specified!';
			}
			else {
				$useragent = "<select style=\"width: 90%;\">";
				foreach ($useragents as $ua) {
					$useragent .= "<option>".htmlspecialchars($ua)."</option>";
				}
				$useragent .= "</select> (".count($useragents).")";
			}
			
			$last_visits = explode('|', $row['last_visit']);
			if (empty($last_visits[0])) {
				$last_visit = 'Never';
			}
			else {
				$last_visit = "<select>";
				$last_visits = array_reverse($last_visits);
				foreach ($last_visits as $visit) {
					$last_visit .= "<option>" . date("d.m.Y @ H:i:s", $visit) . "</option>";
				}
				$last_visit .= "</select>";
			}
			
			?>
			<tr>
				<td class="mbox" width="25%"><?php echo $row['name']; ?></td>
				<td class="mbox" width="20%" nowrap="nowrap"><?php echo $useragent; ?></td>
				<td class="mbox" width="10%" align="center" nowrap="nowrap"><?php echo $row['bot_visits']; ?></td>
				<td class="mbox" width="15%" align="center"><?php echo $last_visit; ?></td>
				<td class="mbox" width="3%" align="center">[<a href="admin.php?action=spider&id=<?php echo $row['id']; ?>&job=edit">Edit</a>]&nbsp;[<a href="admin.php?action=spider&id=<?php echo $row['id']; ?>&job=delete">Delete</a>]</td>
				<td class="mbox" width="3%" align="center"><input type="checkbox" name="mark[]" value="<?php echo $row['id']; ?>" /></td>	
			</tr>
			<?php
		}
		?>
				</table>
			</td>
			</tr>
			<tr class="ubox">
				<td align="right">
				<select name="job">
				<!-- <option value="edit">Edit</option> -->
				<option value="delete">Delete</option>
				</select> selected entries! <input type="submit" name="submit" value="Go" /></td>
			</tr>
		<?php
	}
	else {
		?>
		<tr>
			<td class="mbox" align="center" colspan="6"><p align="center"><b>Sorry there are currently no bots in the database!</b></p></td>
		</tr>
		<?php
	}
	?>
	</table>
	</form>
	<?php
	echo foot();
}
?>
