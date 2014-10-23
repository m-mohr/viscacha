<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// FS: MultiLangAdmin
$lang->group("admin/spider");

($code = $plugins->load('admin_spider_jobs')) ? eval($code) : null;

// This bases on a phpBB Mod
// I can not find any information about the author. Please tell me if you are the author!
$bot_errors = array();
$submit = ((isset($_POST['submit'])) ? true : false);
$action = $gpc->get('job', str, 'manage');
$id = $gpc->get('id', int);
$mark = $gpc->get('mark', arr_int);

echo head();

if ($job == 'ignore_pending' || $job == 'add_pending') {

	$pending_number = $gpc->get('pending', int);
	$pending_data = $gpc->get('data', str);

	$result = $db->query("SELECT pending_{$pending_data} FROM {$db->pre}spider WHERE id = '{$id}'", __LINE__, __FILE__);

	$row = $db->fetch_assoc($result);

	$pending_array = explode('|', $row['pending_'.$pending_data]);

	if ($action == 'add_pending') {
		$new_data = $pending_array[($pending_number-1)*2];
	}

	array_splice($pending_array, ($pending_number-1)*2, 2);
	$pending = implode("|", array_empty_trim($pending_array));

	$result = $db->query("UPDATE {$db->pre}spider SET pending_{$pending_data} = '{$pending}' WHERE id = '{$id}'", __LINE__, __FILE__);

	if ($action == "add_pending") {

		$result = $db->query("SELECT bot_{$pending_data} FROM {$db->pre}spider WHERE id = '{$id}'", __LINE__, __FILE__);
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
					for ($loop2 = 0; $loop2 < $count2; $loop2++) {
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

		$db->query("UPDATE {$db->pre}spider SET bot_{$pending_data} = '{$pending}' WHERE id = '{$id}'", __LINE__, __FILE__);

		$delobj = $scache->load('spiders');
		$delobj->delete();
	}

	$jobname = iif($job == 'add_pending', "added", "ignored");
	ok("admin.php?action=spider&job=pending", $lang->phrase('admin_spider_bot_information_sucessfully_x'));
	echo foot();
}
elseif ($job == 'ignore_all_pending' || $job == 'add_all_pending') {

	$result = $db->query("SELECT pending_ip, pending_agent, bot_ip, user_agent FROM {$db->pre}spider WHERE id = '{$id}'", __LINE__, __FILE__);
	$row = $db->fetch_assoc($result);

	$pending_ip_array = explode('|', $row['pending_ip']);
	foreach ($pending_ip_array as $key => $value) {
		$pending_ip_array[$key] = str_replace("|", "&#124;", $value);
	}
	$pending_agent_array = explode('|', $row['pending_agent']);
	foreach ($pending_agent_array as $key => $value) {
		$pending_agent_array[$key] = str_replace("|", "&#124;", $value);
	}

	$result = $db->query("UPDATE {$db->pre}spider SET pending_ip = '', pending_agent = '' WHERE id = '{$id}'", __LINE__, __FILE__);

	if ($action == "add_all_pending") {

		$new_ip_data = array_chunk($pending_ip_array, 2);
		$new_agent_data = array_chunk($pending_agent_array, 2);

		$bot_ip_array = explode('|', $row['bot_ip']);
		$bot_agent_array = explode('|', $row['user_agent']);

		if (count($new_ip_data) > 0) {
			foreach ($new_ip_data as $new_key => $new_value) {
				$added = false;
				foreach ($bot_ip_array as $key => $value) {
					$ip_found = false;
					for ( $limit = 9; $limit <= 15; $limit++ ) {
						if (strcmp( substr($value,0,$limit) , substr($new_value[0],0,$limit) ) != 0) {
							if ($ip_found == true) {
								$bot_ip_array[$key] = substr($new_value[0],0,($limit-1));
								$added = true;
							}
						}
						else {
							$ip_found = true;
						}
					}
				}
				if ($added == false) {
					$bot_ip_array[] = $new_value[0];
				}
			}
		}
		if (count($new_agent_data) > 0) {
			foreach ($new_agent_data as $new_key => $new_value) {
				$added = false;
				foreach ($bot_agent_array as $key => $value) {

					$smaller_string = ( ( strlen($value) > strlen($new_value[0]) ) ? $new_value[0] : $value);
					$larger_string = ( ( strlen($value) < strlen($new_value[0]) ) ? $new_value[0] : $value);
					if (strlen($smaller_string) <= 6) {
						continue;
					}

					for ( $limit = strlen($smaller_string); $limit > 6; $limit-- ) {
						$count = (strlen($smaller_string)-$limit)+1;
						for ($loop2 = 0; $loop2 < $count; $loop2++) {
							if (strstr($larger_string, substr($smaller_string, $loop2, $limit))) {
								$bot_agent_array[$key] = $smaller_string;
								$added = true;
							}
						}
					}
				}
				if ($added == false) {
					$bot_agent_array[] = $new_value[0];
				}
			}
		}

		$bot_ip = implode("|", array_empty_trim($bot_ip_array));
		$bot_agent = implode("|", array_empty_trim($bot_agent_array));

		$db->query("UPDATE {$db->pre}spider SET user_agent = '{$bot_agent}', bot_ip = '{$bot_ip}' WHERE id = '{$id}'", __LINE__, __FILE__);

		$delobj = $scache->load('spiders');
		$delobj->delete();
	}

	ok("admin.php?action=spider&job=pending", $lang->phrase('admin_spider_bot_information_sucessfully_x'));
	echo foot();
}
elseif ($job == 'delete') {
	$mark = array_empty_trim($mark);
	if ($id > 0 || count($mark) > 0) {
		$id = ($id > 0) ? " = {$id}" : ' IN (' . implode(', ', $mark) . ')';
		$db->query("DELETE FROM {$db->pre}spider WHERE id {$id}", __LINE__, __FILE__);
		if ($db->affected_rows() == 0) {
			error("admin.php?action=spider&job=manage", $lang->phrase('admin_spider_no_entries_deleted'));
		}
		else {
			$delobj = $scache->load('spiders');
			$delobj->delete();
			ok("admin.php?action=spider&job=manage", $lang->phrase('admin_spider_bot_data_successfully_deleted'));
		}
	}
	else {
		error("admin.php?action=spider&job=manage", $lang->phrase('admin_spider_no_data_chosen'));
	}
}
elseif ($job == 'reset') {
	$mark = array_empty_trim($mark);
	if ($id > 0 || count($mark) > 0) {
		$id = ($id > 0) ? " = {$id}" : ' IN (' . implode(', ', $mark) . ')';
		$db->query("UPDATE {$db->pre}spider SET last_visit = '', bot_visits = '0' WHERE id {$id}", __LINE__, __FILE__);
		if ($db->affected_rows() == 0) {
			error("admin.php?action=spider&job=manage", $lang->phrase('admin_spider_no_entries_reset'));
		}
		else {
			ok("admin.php?action=spider&job=manage", $lang->phrase('admin_spider_bot_data_successfully_reset'));
		}
	}
	else {
		error("admin.php?action=spider&job=manage", $lang->phrase('admin_spider_no_data_chosen'));
	}
}
elseif ($job == 'add2' || $job == 'edit2') {
	$bot_ip = $gpc->get('bot_ip', str);
	$bot_ip = trim($bot_ip);
	$bot_agent = $gpc->get('bot_agent', str);
	if ( empty($bot_ip) && empty($bot_agent) ) {
		$bot_errors[] = $lang->phrase('admin_spider_invalid_user_agent_or_ip');
	}
	$type = $gpc->get('type', str);
	$bot_name = $gpc->get('bot_name', str);
	if ( empty($bot_name) ) {
		$bot_errors[] = $lang->phrase('admin_spider_missing_bot_name');
	}

	if (count($bot_errors) > 0) {
		error("admin.php?action=spider&job=".$job.iif($id > 0, '&id='.$id), $bot_errors);
	}
	else {
		if ($job == 'add2') {
			$db->query("INSERT INTO {$db->pre}spider (name, user_agent, bot_ip, type) VALUES ('{$bot_name}', '{$bot_agent}', '{$bot_ip}', '{$type}')", __LINE__, __FILE__);
		}
		else {
			$bot_visits = $gpc->get('visits', int);
			$reset_lastvisit = $gpc->get('reset_lastvisit', int);
			if ($reset_lastvisit == 1) {
				$result = $db->query("SELECT last_visit FROM {$db->pre}spider WHERE id = '{$id}'", __LINE__, __FILE__);
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
			$db->query("UPDATE {$db->pre}spider SET name='{$bot_name}', user_agent='{$bot_agent}', bot_ip='{$bot_ip}', bot_visits='{$bot_visits}'".iif($reset_lastvisit > 0, ", last_visit = '{$last}'")." WHERE id = '{$id}'", __LINE__, __FILE__);
		}
		if ($db->affected_rows() <> 1) {
			error("admin.php?action=spider&job=".iif($job == 'edit2', 'edit', 'add').iif($id > 0, '&id='.$id), "No data changed.");
		}
		else {
			$delobj = $scache->load('spiders');
			$delobj->delete();
			ok("admin.php?action=spider&job=manage", $lang->phrase('admin_spider_bot_settings_successfuly_changed'));
		}
	}
}
elseif ($job == 'add' || $job == 'edit') {
	if ($job == 'edit') {
		$result = $db->query("SELECT name, user_agent, bot_ip, type, bot_visits FROM {$db->pre}spider WHERE id = '{$id}'", __LINE__, __FILE__);
		if ($db->num_rows($result) == 1) {
			$row = $db->fetch_assoc($result);
		}
		else {
			error("admin.php?action=spider&job=manage", $lang->phrase('admin_spider_couldnt_obtain_bot_data'));
		}
	}
	else {
		$row = array('name' => '', 'user_agent' => '', 'bot_ip' => '', 'type' => '');
	}
	?>
	<form action="admin.php?action=spider&amp;job=<?php echo $job; ?>2<?php echo iif($id > 0, '&amp;id='.$id); ?>" method="post">
	<table border="0" align="center" class="border">
		<tr>
			<td class="obox" colspan="2"><?php echo iif($action == $lang->phrase('admin_spider_add_lowercase'), $lang->phrase('admin_spider_add'), '<mla=edit>Edit</edit>'); ?> Bots</th>
		</tr>
		<tr>
			<td class="ubox" colspan="2"><?php echo $lang->phrase('admin_spider_add_edit_description'); ?></th>
		</tr>
		<tr>
			<td class="mbox" width="40%"><?php echo $lang->phrase('admin_spider_name'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_spider_name_description'); ?></span></td>
			<td class="mbox"><input type="text" name="bot_name" size="50"<?php echo iif($job == 'edit', ' value="'.$row['name'].'"'); ?> /></td>
		</tr>
		<tr>
			<td class="mbox" width="40%"><?php echo $lang->phrase('admin_spider_user_agent'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_spider_user_agent_description'); ?></span></td>
			<td class="mbox"><input type="text" name="bot_agent" size="50"<?php echo iif($job == 'edit', ' value="'.$row['user_agent'].'"'); ?> /></td>
		</tr>
		<tr>
			<td class="mbox" width="40%"><?php echo $lang->phrase('admin_spider_ip_adress'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_spider_ip_adress_description'); ?></span></td>
			<td class="mbox"><input type="text" name="bot_ip" size="50"<?php echo iif($job == 'edit', ' value="'.$row['bot_ip'].'"'); ?> /></td>
		</tr>
		<tr>
			<td class="mbox" width="40%"><?php echo $lang->phrase('admin_spider_type'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_spider_type_description'); ?></span></td>
			<td class="mbox">
				<input type="radio" name="type" value="b"<?php echo iif(($job == 'edit' && $row['type'] == 'b'), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_spider_search_engine'); ?><br />
   				<input type="radio" name="type" value="e"<?php echo iif(($job == 'edit' && $row['type'] == 'e'), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_spider_mail_collector_or_spam_bot'); ?><br />
   				<input type="radio" name="type" value="v"<?php echo iif(($job == 'edit' && $row['type'] == 'v'), ' checked="checked"'); ?> /> <?php $lang->phrase('admin_spider_validator'); ?>
   			</td>
		</tr>
		<?php if ($job == 'edit') { ?>
		<tr>
			<td class="mbox" width="40%"><?php echo $lang->phrase('admin_spider_visits'); ?></td>
			<td class="mbox"><input type="text" name="visits" size="10" value="<?php echo $row['bot_visits']; ?>" /></td>
		</tr>
		<tr>
			<td class="mbox" width="40%"><?php echo $lang->phrase('admin_spider_reset_last_visits'); ?><br /><span class="stext"></span></td>
			<td class="mbox">
			<input type="radio" name="reset_lastvisit" value="0" checked="checked" /><?php echo $lang->phrase('admin_spider_keep_last_visits'); ?><br />
			<input type="radio" name="reset_lastvisit" value="1" /><?php echo $lang->phrase('admin_spider_last_visits_will_be_deleted_with_exception'); ?><br />
			<input type="radio" name="reset_lastvisit" value="2" /><?php echo $lang->phrase('admin_spider_last_visits_will_be_deleted_completely'); ?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td class="ubox" colspan="2" align="center"><input type="submit" name="submit" value="<?php echo $lang->phrase('admin_spider_submit'); ?>" /></td>
		</tr>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'pending') {
	$result = $db->query("SELECT id, name, pending_agent, pending_ip, type FROM {$db->pre}spider", __LINE__, __FILE__);
	$pending_bots = 0;
	?>
	<table border="0" align="center" class="border">
	<tr><td class="obox"><?php echo $lang->phrase('admin_spider_pending_bots'); ?></td></tr>
	<tr><td class="mbox">
	<?php if ($config['spider_pendinglist'] == 0) { ?>
	<p><strong><?php echo $lang->phrase('admin_spider_pending_function_currently_disabled'); ?></strong></p>
	<?php } ?>
	<p><?php echo $lang->phrase('admin_spider_pending_bots_description'); ?></p>
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
			<tr><td class="obox" colspan="3">
				<span style="float: right;">
					<a class="button" href="admin.php?action=spider&amp;id=<?php echo $row['id']; ?>&amp;job=ignore_all_pending"><?php echo $lang->phrase('admin_spider_ignore_all'); ?></a>
					<a class="button" href="admin.php?action=spider&amp;id=<?php echo $row['id']; ?>&amp;job=add_all_pending"><?php echo $lang->phrase('admin_spider_add_all'); ?></a>
				</span>
				<?php echo $row['name']; ?>
			</td></tr>
			<tr class="ubox">
				<td nowrap="nowrap"><?php echo $lang->phrase('admin_spider_ip_adress_title'); ?></td>
				<td nowrap="nowrap"><?php echo $lang->phrase('admin_spider_user_agent_title'); ?></td>
				<td nowrap="nowrap"><?php echo $lang->phrase('admin_spider_actions_title'); ?></td>
			</tr>
			<?php
		}

		if (count($pending_agent_array) > 0) {
			for ($loop = 0; $loop < count($pending_agent_array); $loop+=2) {
			?>
			<tr>
				<td class="mbox" width="15%" align="center" nowrap="nowrap"><?php echo $pending_agent_array[$loop+1]; ?></td>
				<td class="mbox" width="25%" align="center" nowrap="nowrap"><b><?php echo $pending_agent_array[$loop]; ?></b></td>
				<td class="mbox" width="20%" align="center">
					<a class="button" href="admin.php?action=spider&amp;id=<?php echo $row['id']; ?>&amp;pending=<?php echo ($loop/2)+1; ?>&amp;data=agent&amp;job=ignore_pending"><?php echo $lang->phrase('admin_spider_ignore'); ?></a>
					<a class="button" href="admin.php?action=spider&amp;id=<?php echo $row['id']; ?>&amp;pending=<?php echo ($loop/2)+1; ?>&amp;data=agent&amp;job=add_pending"><?php echo $lang->phrase('admin_spider_add'); ?></a>
				</td>
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
				<td class="mbox" width="20%" align="center"><a class="button" href="admin.php?action=spider&id=<?php echo $row['id']; ?>&pending=<?php echo ($loop/2)+1; ?>&data=ip&job=ignore_pending">Ignore</a>&nbsp;<a class="button" href="admin.php?action=spider&id=<?php echo $row['id']; ?>&pending=<?php echo ($loop/2)+1; ?>&data=ip&job=add_pending"><?php echo $lang->phrase('admin_spider_add'); ?></a></td>
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
		<p align="center"><b><?php echo $lang->phrase('admin_spider_no_pending_bots'); ?></b></p>
	<?php } ?>
	</td></tr>
	</table>
	<?php
	echo foot();
}
elseif (empty($job) || $job == 'manage') {
	?>
	<table border="0" align="center" class="border">
	<tr><td class="obox">
	<span style="float: right;">
	<a class="button" href="admin.php?action=spider&amp;job=add" target="Main"><?php echo $lang->phrase('admin_spider_add_new_robot'); ?></a>
	<a class="button" href="admin.php?action=spider&amp;job=pending" target="Main"><?php echo $lang->phrase('admin_spider_pending_robots'); ?></a>
	</span>
	<?php echo $lang->phrase('admin_spider_manage_bots'); ?>
	</td></tr>
	<tr><td class="mbox">
	<p><?php echo $lang->phrase('admin_spider_bots_description'); ?></p>
	<?php if ($config['spider_logvisits'] == 0) { ?>
	<p><em><?php echo $lang->phrase('admin_spider_bots_note'); ?></em></p>
	<?php } ?>
	<form action="admin.php?action=spider" method="post">
	<table border="0" align="center" class="border">
	<?php
	$result = $db->query("SELECT id, name, last_visit, bot_visits, type, user_agent FROM {$db->pre}spider ORDER BY type, name", __LINE__, __FILE__);
	if ($db->num_rows($result) > 0) {
		$category = '';
		while ($row = $db->fetch_assoc($result)) {

			if ($row['type'] != $category) {
				$category = $row['type'];
				if ($row['type'] == 'v') {
					$row['type'] = $lang->phrase('admin_spider_validator');
				}
				elseif ($row['type'] == 'e') {
					$row['type'] = $lang->phrase('admin_spider_mail_collector_or_spam_bot');
				}
				else {
					$row['type'] = $lang->phrase('admin_spider_search_engine');
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
					<td class="ubox" nowrap="nowrap"><?php echo $lang->phrase('admin_spider_name_title'); ?></td>
					<td class="ubox" nowrap="nowrap"><?php echo $lang->phrase('admin_spider_user_agents_count_title'); ?></td>
					<td class="ubox" nowrap="nowrap"><?php echo $lang->phrase('admin_spider_visits_title'); ?></td>
					<td class="ubox" nowrap="nowrap"><?php echo $lang->phrase('admin_spider_last_visit_title'); ?></td>
					<td class="ubox" nowrap="nowrap"><?php echo $lang->phrase('admin_spider_actions_title'); ?></td>
					<td class="ubox" nowrap="nowrap"><?php echo $lang->phrase('admin_spider_mark_title'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all('mark[]');" name="all" value="1" /> <?php echo $lang->phrase('admin_spider_all_title'); ?></span></td>
				</tr>
				<?php
			}

			$useragents = explode('|', $row['user_agent']);
			if (empty($useragents[0])) {
				$useragent = $lang->phrase('admin_spider_not_specified');
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
				$last_visit = $lang->phrase('admin_spider_last_visit_never');
			}
			else {
				$last_visit = "<select>";
				$last_visits = array_reverse($last_visits);
				foreach ($last_visits as $visit) {
					$last_visit .= "<option>".gmdate("d.m.Y @ H:i:s", times($visit))."</option>";
				}
				$last_visit .= "</select>";
			}

			?>
			<tr>
				<td class="mbox" width="25%"><?php echo $row['name']; ?></td>
				<td class="mbox" width="20%" nowrap="nowrap"><?php echo $useragent; ?></td>
				<td class="mbox" width="10%" align="center" nowrap="nowrap"><?php echo $row['bot_visits']; ?></td>
				<td class="mbox" width="15%" align="center"><?php echo $last_visit; ?></td>
				<td class="mbox" width="25%" align="center">
					<a class="button" href="admin.php?action=spider&amp;id=<?php echo $row['id']; ?>&amp;job=edit"><?php echo $lang->phrase('admin_spider_edit'); ?></a>&nbsp;
					<a class="button" href="admin.php?action=spider&amp;id=<?php echo $row['id']; ?>&amp;job=reset"><?php echo $lang->phrase('admin_spider_reset'); ?></a>&nbsp;
					<a class="button" href="admin.php?action=spider&amp;id=<?php echo $row['id']; ?>&amp;job=delete"><?php echo $lang->phrase('admin_spider_delete'); ?></a>
				</td>
				<td class="mbox" width="5%" align="center"><input type="checkbox" name="mark[]" value="<?php echo $row['id']; ?>" /></td>
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
				<option value="reset"><?php echo $lang->phrase('admin_spider_select_reset'); ?></option>
				<option value="delete"><?php echo $lang->phrase('admin_spider_select_delete'); ?></option>
				</select><?php echo $lang->phrase('admin_spider_selected_entries'); ?> <input type="submit" name="submit" value="<?php echo $lang->phrase('admin_spider_form_go'); ?>" /></td>
			</tr>
		<?php
	}
	else {
		?>
		<tr>
			<td class="mbox" align="center" colspan="6"><b><?php echo $lang->phrase('admin_spider_no_bots_in_database'); ?></b></td>
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