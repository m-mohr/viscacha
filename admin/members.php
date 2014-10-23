<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// TR: MultiLangAdmin
$lang->group("admin/members");
$lang->group("timezones");

($code = $plugins->load('admin_members_jobs')) ? eval($code) : null;

if ($job == 'reserve') {
	$olduserdata = file('data/deleteduser.php');
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=members&job=reserve_delete">
<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="3"><?php echo $lang->phrase('admin_member_reserve_names_title'); ?></td>
  </tr>
  <tr class="ubox">
   <td width="10%" class="center"><?php echo $lang->phrase('admin_member_delete'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /> <?php echo $lang->phrase('admin_member_all'); ?></span></td>
   <td width="50%"><?php echo $lang->phrase('admin_member_user_name'); ?></td>
   <td width="40%"><?php echo $lang->phrase('admin_member_name_connected_to_id'); ?></td>
  </tr>
	<?php
	foreach ($olduserdata as $name) {
		$name = trim($name);
		if (empty($name)) {
			continue;
		}
		list($id, $username) = explode("\t", $name);
		?>
  <tr class="mbox">
   <td class="center"><input type="checkbox" name="delete[]" value="<?php echo $username; ?>" /></td>
   <td><?php echo $username; ?></td>
   <td><?php echo iif(is_id($id), $id, '<em>'.$lang->phrase('admin_member_name_not_connected').'</em>'); ?></td>
  </tr>
	<?php } ?>
  <tr>
   <td class="ubox center" colspan="3"><input type="submit" value="<?php echo $lang->phrase('admin_member_submit'); ?>" /></td>
  </tr>
</table>
</form>
<br />
<form name="form" method="post" action="admin.php?action=members&job=reserve_add">
<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="3"><?php echo $lang->phrase('admin_member_reserve_names_add'); ?></td>
  </tr>
  <tr class="mbox">
   <td><?php echo $lang->phrase('admin_member_user_name'); ?>:</td>
   <td><input type="text" name="name" size="60" /></td>
  </tr>
  <tr>
   <td class="ubox center" colspan="2"><input type="submit" value="<?php echo $lang->phrase('admin_member_submit'); ?>" /></td>
  </tr>
</table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'reserve_add') {
	$name = $gpc->get('name', str);

	$error = array();
	if (double_udata('name', $name) == false) {
		$error[] = $lang->phrase('admin_member_username_already_in_use');
	}
	if (strxlen($name) > $config['maxnamelength']) {
		$error[] = $lang->phrase('admin_member_name_too_long');
	}
	if (strxlen($name) < $config['minnamelength']) {
		$error[] = $lang->phrase('admin_member_name_too_short');
	}
	if (count($error) > 0) {
		echo head();
		error('admin.php?action=members&job=reserve', $error);
	}
	else {
		$olduserdata = file_get_contents('data/deleteduser.php');
		$olduserdata = trim($olduserdata);
		$olduserdata .= "\n0\t".$name;
		$filesystem->file_put_contents('data/deleteduser.php', $olduserdata);

		echo head();
		ok('admin.php?action=members&job=reserve', $lang->phrase('admin_member_username_successfully_reserved'));
	}
}
elseif ($job == 'reserve_delete') {
	$del = $gpc->get('delete', arr_none);
	$olduserdata = file('data/deleteduser.php');
	$rows = array();
	foreach ($olduserdata as $name) {
		$name = trim($name);
		if (empty($name)) {
			continue;
		}
		list($id, $username) = explode("\t", $name);
		$save = true;
		foreach ($del as $username2) {
			if (strtolower($username2) == strtolower($username)) {
				$save = false;
			}
		}
		if ($save == true) {
			$rows[] = $name;
		}
	}
	$filesystem->file_put_contents('data/deleteduser.php', implode("\n", $rows));

	echo head();
	ok('admin.php?action=members&job=reserve', $lang->phrase('admin_member_selected_reserved_names_deleted'));
}
elseif ($job == 'emailsearch') {
	echo head();

	$loadlanguage_obj = $scache->load('loadlanguage');
	$language = $loadlanguage_obj->get();

	$result = $db->query("SELECT id, title, name FROM {$db->pre}groups WHERE guest = '0' ORDER BY admin DESC, guest ASC, core ASC");
	?>
<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="3">
   	<span style="float: right;"><a class="button" href="admin.php?action=members&job=newsletter_archive"><?php echo $lang->phrase('admin_member_nl_archive'); ?></a></span>
	<?php echo $lang->phrase('admin_member_nl_mail_manager'); ?>
   </td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_mail_manager_instructions'); ?></td>
  </tr>
</table>
<br class="minibr" />
<form name="form" method="post" action="admin.php?action=members&job=emailsearch2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="3"><?php echo $lang->phrase('admin_member_search_for_members'); ?></td>
  </tr>
  <tr>
	<td class="mbox" width="50%" colspan="3">
	<b><?php echo $lang->phrase('admin_member_help'); ?></b>
	<ul>
	<li><?php echo $lang->phrase('admin_member_wildcard_description'); ?></li>
	<li>
	<?php echo $lang->phrase('admin_member_means_equal'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<?php echo $lang->phrase('admin_member_means_less'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<?php echo $lang->phrase('admin_member_means_greater'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<?php echo $lang->phrase('admin_member_means_not_equal'); ?>
	</li>
	</ul>
	</td>
  </tr>
  <tr>
   <td class="mbox" colspan="2" width="50%"><?php echo $lang->phrase('admin_member_exactness'); ?></td>
   <td class="mbox" colspan="1" width="50%">
   <input type="radio" name="type" value="0"> <b><?php echo $lang->phrase('admin_member_at_least_one_match'); ?></b> (<?php echo $lang->phrase('admin_member_at_least_one_match_desc'); ?>)<br>
   <input type="radio" name="type" value="1" checked="checked"> <b><?php echo $lang->phrase('admin_member_whole_match'); ?></b> (<?php echo $lang->phrase('admin_member_whole_match'); ?>)
   </td>
  </tr>
  <tr>
   <td class="ubox" width="35%">&nbsp;</td>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_member_relational_operator'); ?></td>
   <td class="ubox" width="60%">&nbsp;</td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_id'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[id]">
	  <option value="-1">&lt;</option>
	  <option value="0" selected="selected">=</option>
	  <option value="1">&gt;</option>
	</select></td>
   <td class="mbox"><input type="text" name="id" size="12"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_mail_address'); ?></td>
   <td class="mbox" align="center">=</td>
   <td class="mbox"><input type="text" name="mail" size="50"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_registration_date'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[regdate]">
	  <option value="-1">&lt;</option>
	  <option value="0" selected="selected">=</option>
	  <option value="1">&gt;</option>
	</select></td>
   <td class="mbox"><input type="text" name="regdate[1]" size="3">. <input type="text" name="regdate[2]" size="3">. <input type="text" name="regdate[3]" size="5"> (<?php echo $lang->phrase('admin_member_date_numeric'); ?>)</td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_posts'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[posts]">
	  <option value="-1">&lt;</option>
	  <option value="0" selected="selected">=</option>
	  <option value="1">&gt;</option>
	</select></td>
   <td class="mbox"><input type="text" name="posts" size="10"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_gender'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[gender]">
	  <option value="0" selected="selected">=</option>
	  <option value="2">!=</option>
	</select></td>
   <td class="mbox"><select name="gender" size="1">
   <option selected="selected" value=""><?php echo $lang->phrase('admin_member_whatever'); ?></option>
   <option value="x"><?php echo $lang->phrase('admin_member_not_specified'); ?></option>
   <option value="m"><?php echo $lang->phrase('admin_member_male'); ?></option>
   <option value="w"><?php echo $lang->phrase('admin_member_female'); ?></option>
   </select></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_birthday'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[birthday]">
	  <option value="-1">&lt;</option>
	  <option value="0" selected="selected">=</option>
	  <option value="1">&gt;</option>
	</select></td>
   <td class="mbox"><input type="text" name="birthday[1]" size="3">. <input type="text" name="birthday[2]" size="3">. <input type="text" name="birthday[3]" size="5"> (<?php echo $lang->phrase('admin_member_date_numeric'); ?>)</td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_last_visit'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[lastvisit]">
	  <option value="-1">&lt;</option>
	  <option value="0" selected="selected">=</option>
	  <option value="1">&gt;</option>
	</select></td>
   <td class="mbox"><input type="text" name="lastvisit[1]" size="3">. <input type="text" name="lastvisit[2]" size="3">. <input type="text" name="lastvisit[3]" size="5"> (<?php echo $lang->phrase('admin_member_date_numeric'); ?>)</td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_groups'); ?></td>
   <td class="mbox" align="center">=</td>
   <td class="mbox">
	<select size="3" name="groups[]" multiple="multiple">
	  <option selected="selected" value=""><?php echo $lang->phrase('admin_member_whatever'); ?></option>
	  <?php while ($row = $gpc->prepare($db->fetch_assoc($result))) { ?>
		<option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
	  <?php } ?>
	</select>
	<select name="groups_op" size="2" style="margin: 0.5em 0 0.5em 0;">
	  <option value="0" selected="selected" title="<?php echo $lang->phrase('admin_member_at_least_one_match_desc'); ?>"><?php echo $lang->phrase('admin_member_at_least_one_match'); ?></option>
   	  <option value="1" title="<?php echo $lang->phrase('admin_member_whole_match_desc'); ?>"><?php echo $lang->phrase('admin_member_whole_match'); ?></option>
	</select>
	</td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_lang'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[language]">
	  <option value="0" selected="selected">=</option>
	  <option value="2">!=</option>
	</select></td>
   <td class="mbox"><select name="language">
	<option selected="selected" value=""><?php echo $lang->phrase('admin_member_whatever'); ?></option>
	<?php foreach ($language as $row) { ?>
	<option value="<?php echo $row['id']; ?>"><?php echo $row['language']; ?></option>
	<?php } ?>
</select></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_status'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[confirm]">
	  <option value="0" selected="selected">=</option>
	  <option value="2">!=</option>
	</select></td>
   <td class="mbox"><select size="1" name="confirm">
	  <option selected="selected" value=""><?php echo $lang->phrase('admin_member_whatever'); ?></option>
	  <option value="11"><?php echo $lang->phrase('admin_member_activated'); ?></option>
	  <option value="10"><?php echo $lang->phrase('admin_member_must_activate_via_mail'); ?></option>
	  <option value="01"><?php echo $lang->phrase('admin_member_must_activate_by_admin'); ?></option>
	  <option value="00"><?php echo $lang->phrase('admin_member_has_not_been_activated'); ?></option>
	</select></td>
  </tr>
  <tr>
   <td class="ubox" align="center" colspan="4"><input type="submit" value="<?php echo $lang->phrase('admin_member_search'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'emailsearch2') {
	echo head();

	define('DONT_CARE', md5(microtime()));
	$fields = 	array(
		'id' => int,
		'mail' => str,
		'regdate' => arr_int,
		'posts' => int,
		'gender' => str,
		'birthday' => arr_none,
		'lastvisit' => arr_int,
		'groups' => arr_int,
		'language' => int,
		'confirm' => none
	);

	$loadlanguage_obj = $scache->load('loadlanguage');
	$language = $loadlanguage_obj->get();

	$type = $gpc->get('type', int);
	$sep = ($type == 0) ? ' OR ' : ' AND ';

	$compare = $gpc->get('compare', arr_int);
	foreach ($compare as $key => $cmp) {
		if ($cmp == -1) {
			$compare[$key] = '<';
		}
		elseif ($cmp == 1) {
			$compare[$key] = '>';
		}
		elseif ($cmp == 2) {
			$compare[$key] = '!=';
		}
		else {
			$compare[$key] = '=';
		}
	}
	$sqlwhere = array();
	$input = array();
	foreach ($fields as $key => $data) {
		$value = $gpc->get($key, $data[1], DONT_CARE);
		if ($key == 'regdate' || $key == 'lastvisit') {
			if (is_array($value) && array_sum($value) != 0) { // for php version >= 5.1.0
				$input[$key] =  @mktime(0, 0, 0, intval($value[2]), intval($value[1]), intval($value[3]));
				if ($input[$key] == -1 || $input[$key] == false) { // -1 for php version < 5.1.0, false for php version >= 5.1.0
					$input[$key] = DONT_CARE;
				}
			}
			else {
				$input[$key] = DONT_CARE;
			}
		}
		elseif ($key == 'groups') {
			if (array_empty($value) !== false) {
				$input[$key] = DONT_CARE;
			}
			else {
				$input[$key] = $value;
			}
		}
		elseif ($key == 'birthday') {
			$value[1] = intval(trim($value[1]));
			if ($value[1] < 1 || $value[1] > 31) {
				$value[1] = '%';
			}
			$value[2] = intval(trim($value[2]));
			if ($value[2] < 1 || $value[2] > 12) {
				$value[2] = '%';
			}
			if (strlen($value[3]) == 2) {
				if ($value[3] > 40) {
					$value[3] += 1900;
				}
				else {
					$value[3] += 2000;
				}
			}
			else {
				$value[3] = intval(trim($value[3]));
			}
			if ($value[3] < 1900 || $value[3] > 2100) {
				$value[3] = '%';
			}
			if ($value[1] == '%' && $value[2] == '%' && $value[3] == '%') {
				$input[$key] = DONT_CARE;
			}
			else {
				$input[$key] = $value[3].'-'.$value[2].'-'.$value[1];
			}
		}
		elseif ($key == 'gender') {
			if (empty($value)) {
				$input[$key] = DONT_CARE;
			}
			elseif ($value == 'x') {
				$input[$key] = '';
			}
			else {
				$input[$key] = $value;
			}
		}
		else {
			if (empty($value)) {
				$input[$key] = DONT_CARE;
			}
			else {
				$input[$key] = $value;
			}
		}

		if (!isset($compare[$key])) {
			$compare[$key] = '=';
		}

		if ($input[$key] != DONT_CARE) {
			if ($key == 'groups') {
				$gcmp = $gpc->get('groups_op', int);
				$gsep = ($gcmp == 0) ? ' OR ' : ' AND ';
				$groupwhere = array();
				foreach ($input[$key] as $gid) {
					$groupwhere[] = " FIND_IN_SET('{$gid}', {$key}) ";
				}
				$groupwhere = implode($gsep, $groupwhere);
				$sqlwhere[] = " ({$groupwhere}) ";
			}
			else {
				if (strpos($input[$key], '%') !== false || strpos($input[$key], '_') !== false) {
					if ($compare[$key] == '=') {
						$compare[$key] = 'LIKE';
					}
					elseif ($compare[$key] == '!=') {
						$compare[$key] = 'NOT LIKE';
					}
				}
				$sqlwhere[] = " `{$key}` {$compare[$key]} '{$input[$key]}' ";
			}
		}
	}

	if (count($sqlwhere) > 0) {
		$query = 'SELECT id FROM '.$db->pre.'user WHERE '.implode($sep, $sqlwhere);
		$result = $db->query($query);
		$users = array();
		while ($row = $db->fetch_assoc($result)) {
			$users[] = $row['id'];
		}
		$count = $db->num_rows($result);
	}
	else {
		$count = 0;
	}
	?>
	<form name="form" action="admin.php?action=members" method="post">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		<tr>
		  <td class="obox" colspan="2"><?php echo $lang->phrase('admin_member_nl_mail_manager'); ?></td>
		</tr>
		<?php if ($count == 0) { ?>
		<tr>
		  <td class="mbox" colspan="2"><?php echo $lang->phrase('admin_member_no_results'); ?></td>
		</tr>
		<?php } else { ?>
		<tr>
		  <td class="mbox" width="50%"><?php echo $lang->phrase('admin_member_filter_recipients'); ?></td>
		  <td class="mbox" width="50%">
			<select name="filter">
			  <option value="0"><?php echo $lang->phrase('admin_member_include_important_admin_mails'); ?></option>
			  <option value="1"><?php echo $lang->phrase('admin_member_include_all_admin_mails'); ?></option>
			  <option value="2"><?php echo $lang->phrase('admin_member_include_all'); ?></option>
			</select>
		  </td>
		</tr>
		<tr>
		  <td class="ubox" colspan="2" align="center">
			<input type="hidden" name="users" value="<?php echo implode(',', $users); ?>" />
		  	<select name="job">
		  		<option value="emaillist"><?php echo $lang->phrase('admin_member_export_mail_addresses'); ?></option>
		  		<option value="newsletter"><?php echo $lang->phrase('admin_member_send_nl'); ?></option>
		  	</select> <input type="submit" name="submit" value="<?php echo $lang->phrase('admin_member_go'); ?>">
		  </td>
		</tr>
		<?php } ?>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'emaillist') {
	echo head();
	$delete = $gpc->get('delete', arr_int);
	$users = $gpc->get('users', none);
	$filter = $gpc->get('filter', int);

	$filter = '';
	if ($filter == 1) {
		$filter = " opt_newsletter = '1' AND ";
	}
	else if ($filter == 0) {
		$filter = " (opt_newsletter = '2' OR opt_newsletter = '1') AND ";
	}

	if (empty($users) == false) {
		$delete = array_merge($delete, explode(',', $users));
	}
	$ids = array();
	foreach ($delete as $id) {
		if (is_id($id)) {
			$ids[] = $id;
		}
	}

	$result = $db->query("SELECT DISTINCT mail FROM {$db->pre}user WHERE {$filter} id IN (".implode(',', $ids).")");
	$emails = array();
	while ($row = $db->fetch_assoc($result)) {
		$emails[] = $row['mail'];
	}

	$template = $gpc->get('template', none);
	if (empty($template)) {
		$template = ',';
	}
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox"><?php echo $lang->phrase('admin_member_export_mail_addresses'); ?></td>
  </tr>
  <tr>
   <td class="mbox"><textarea class="fullwidth" cols="125" rows="25"><?php echo implode($template, $emails); ?></textarea></td>
  </tr>
 </table>
<br />
<form name="form" method="post" action="admin.php?action=members&amp;job=emaillist">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_member_change_settings'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_member_seperator'); ?><br><span class="stext"><?php echo $lang->phrase('admin_member_sperator_mail_addresses'); ?></span></td>
   <td class="mbox" width="50%"><textarea name="template" cols="10" rows="2"></textarea></td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan="2" align="center">
	<input type="hidden" name="users" value="<?php echo implode(',', $ids); ?>">
   	<input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_member_submit'); ?>">
   </td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'newsletter') {
	$delete = $gpc->get('delete', arr_int);
	$users = $gpc->get('users', none);
	$filter = $gpc->get('filter', int);

	$filter = '';
	if ($filter == 1) {
		$filter = " opt_newsletter = '1' AND ";
	}
	else if ($filter == 0) {
		$filter = " (opt_newsletter = '2' OR opt_newsletter = '1') AND ";
	}

	if (empty($users) == false) {
		$delete = array_merge($delete, explode(',', $users));
	}
	$ids = array();
	foreach ($delete as $id) {
		if (is_id($id)) {
			$ids[] = $id;
		}
	}

	echo head();
	if (count($ids) == 0) {
		error('admin.php?action=members&job=emailsearch', 'No Members found.');
	}

	$result = $db->query("SELECT id FROM {$db->pre}user WHERE {$filter} id IN (".implode(',', $ids).") GROUP BY mail");
	$ids = array();
	while ($row = $db->fetch_assoc($result)) {
		$ids[] = $row['id'];
	}
?>
<form name="form" method="post" action="admin.php?action=members&amp;job=newsletter2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_member_compose_nl'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_member_recipients'); ?></td>
   <td class="mbox" width="60%"><?php echo count($ids); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_member_from'); ?></td>
   <td class="mbox" width="60%">
   <?php echo $lang->phrase('admin_member_from_mail'); ?> <input type="text" name="from_mail" size="60" value="<?php echo $config['forenmail']; ?>"><br />
   <?php echo $lang->phrase('admin_member_from_name'); ?> <input type="text" name="from_name" size="60" value="<?php echo $config['fname']; ?>">
   </td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_member_subject'); ?></td>
   <td class="mbox" width="60%"><input type="text" name="title" size="70"></td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_member_format'); ?></td>
   <td class="mbox" width="60%">
   	<input type="radio" name="type" value="h"> <?php echo $lang->phrase('admin_member_xhtml'); ?>
   	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   	<input type="radio" name="type" value="p" checked="checked"> <?php echo $lang->phrase('admin_member_plain_text'); ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="40%">
	<?php echo $lang->phrase('admin_member_message'); ?><br />
   	<span class="stext">
	<?php echo $lang->phrase('admin_member_nl_message_description'); ?>
   	</span>
   </td>
   <td class="mbox" width="60%"><textarea name="message" rows="10" cols="70"></textarea></td>
  </tr>
  <tr>
   <td class="mbox" width="40%">
	<?php echo $lang->phrase('admin_member_mail_send_at_once'); ?><br />
   	<span class="stext"><?php echo $lang->phrase('admin_member_batch_sent_mails'); ?></span>
   </td>
   <td class="mbox" width="60%"><input type="text" name="batch" size="10" value="20"></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center">
	<input type="hidden" name="users" value="<?php echo implode(',', $ids); ?>">
   	<input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_member_submit'); ?>">
   </td>
  </tr>
 </table>
</form>
<?php
	echo foot();
}
elseif ($job == 'newsletter2') {

	$data = array(
		'users' => $gpc->get('users', none),
		'from_mail' => $gpc->get('from_mail', none, $config['forenmail']),
		'from_name' => $gpc->get('from_name', none, $gpc->plain_str($config['fname'])),
		'title' => $gpc->get('title', none),
		'message' => $gpc->get('message', none),
		'batch' => $gpc->get('batch', int, 20),
		'type' => $gpc->get('type', none, 'p')
	);

	if (!check_mail($data['from_mail'])) {
		$data['from_mail'] = $config['forenmail'];
	}

	$dbd = array_map(array($db, 'escape_string'), $data);

	$db->query("INSERT INTO {$db->pre}newsletter (receiver, sender, title, content, time, type) VALUES ('{$dbd['users']}','{$dbd['from_name']} <{$dbd['from_mail']}>','{$dbd['title']}','{$dbd['message']}','".time()."', '{$dbd['type']}')");
	$nid = $db->insert_id();

	$data['chunks'] = array_chunk(explode(',', $data['users']), $data['batch']);

	$cache = new CacheItem('newsletter_'.$nid);
	$cache->set($data);
	$cache->export();

	echo head();
	ok('admin.php?action=members&job=newsletter3&id='.$nid, $lang->phrase('admin_member_mail_will_be_sent'));
}
elseif ($job == 'newsletter3') {
	$page = $gpc->get('page', int, 1);
	$id = $gpc->get('id', int);

	$cache = new CacheItem('newsletter_'.$id);
	$cache->import();
	$data = $cache->get();

	require_once("classes/mail/class.phpmailer.php");
	$mail = new PHPMailer();
	$mail->From = $data['from_mail'];
	$mail->FromName = $data['from_name'];
	$mail->Subject = $data['title'];
	if ($config['smtp'] == 1) {
		$mail->Mailer = "smtp";
		$mail->IsSMTP();
		$mail->Host = $config['smtp_host'];
		if ($config['smtp_auth'] == 1) {
			$mail->SMTPAuth = true;
			$mail->Username = $config['smtp_username'];
			$mail->Password = $config['smtp_password'];
		}
	}
	elseif ($config['sendmail'] == 1) {
		$mail->IsSendmail();
		$mail->Mailer   = "sendmail";
		$mail->Sendmail = $config['sendmail_host'];
	}
	else {
		$mail->IsMail();
	}
	$ids = implode(',', $data['chunks'][$page-1]);
	$result = $db->query("SELECT id, name, mail FROM {$db->pre}user WHERE id IN ($ids)");
	while ($row = $db->fetch_assoc($result)) {
		$message = str_replace('{$user.id}', $row['id'], $data['message']);
		$message = str_replace('{$user.name}', $row['name'], $message);
		$message = str_replace('{$user.mail}', $row['mail'], $message);
		if ($data['type'] == 'h') {
			$mail->IsHTML(true);
			$mail->Body = $message;
			// No alternative Body atm
			// $plain = preg_replace('~<br(\s*/)?\>(\r\n|\r|\n){0,1}~i', "\r\n", $message);
			// $mail->AltBody = strip_tags($plain);
		}
		else {
			$mail->Body = $message;
		}
		$mail->AddAddress($row['mail']);
		$mail->Send();
		$mail->ClearAddresses();
	}

	$steps = count($data['chunks']);
	$page2 = $page+1;
	$all = count(explode(',', $data['users']));
	$sec = 3;
	$left = (($steps-$page)*$sec)/60;
	if ($left == 0) {
		$left = $lang->phrase('admin_member_ask_finished');
	}
	else if ($left < 60) {
		 $left = ceil($left*60).$lang->phrase('admin_member_seconds');
	}
	else if ($left > 60) {
		 $left = round($left/60, 1).$lang->phrase('admin_member_hours');
	}
	else {
		$left = ceil($left).$lang->phrase('admin_member_minutes');
	}

	if ($steps > $page) {
		$percent = (100/$all)*(($page)*$data['batch']);
		$url = 'admin.php?action=members&amp;job=newsletter3&amp;id='.$id.'&amp;page='.$page2;
	}
	else {
		$percent = 100;
		$url = 'admin.php?action=members&amp;job=newsletter_view&amp;id='.$id;
		$cache->delete();
	}

	$htmlhead .= '<meta http-equiv="refresh" content="'.$sec.'; url='.$url.'">';
	echo head();
	?>
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox"><?php echo $lang->phrase('admin_member_sending_nl'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox">
	   	<div style="width: 600px; border: 1px solid black; background-color: white;"><div style="width: <?php echo ceil($percent)*6; ?>px; background-color: steelblue;">&nbsp;</div></div>
	   <?php echo $lang->phrase('admin_member_progress').round($percent, 1); ?>%<br />
	   <?php echo $lang->phrase('admin_member_time_left'); ?><?php echo $left; ?>
	   </td>
	  </tr>
	  <tr>
	  	<td class="ubox" align="center"><a href="<?php echo $url; ?>"><?php echo $lang->phrase('admin_member_click_if_no_redirection'); ?></a></td>
	  </tr>
	 </table>
	<?php
	echo foot();
}
elseif ($job == 'newsletter_archive') {
	$result = $db->query('SELECT * FROM '.$db->pre.'newsletter ORDER BY time');
	echo head();
?>
<form name="form" method="post" action="admin.php?action=members&amp;job=newsletter_delete">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="4"><?php echo $lang->phrase('admin_member_nl_archive'); ?></td>
  </tr>
  <tr>
   <td class="ubox"><?php echo $lang->phrase('admin_member_delete'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /> <?php echo $lang->phrase('admin_member_all'); ?></span></td>
   <td class="ubox"><?php echo $lang->phrase('admin_member_nl_subject'); ?></td>
   <td class="ubox"><?php echo $lang->phrase('admin_member_date_time'); ?></td>
   <td class="ubox"><?php echo $lang->phrase('admin_member_nl_recipients'); ?></td>
  </tr>
<?php while ($row = $db->fetch_assoc($result)) { ?>
  <tr>
   <td class="mbox"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
   <td class="mbox"><a href="admin.php?action=members&amp;job=newsletter_view&id=<?php echo $row['id']; ?>"><?php echo $row['title']; ?></a></td>
   <td class="mbox"><?php echo gmdate('d.m.Y, H:i', times($row['time'])); ?></td>
   <td class="mbox"><?php echo count(explode(',', $row['receiver'])); ?></td>
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" colspan="4" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_member_delete'); ?>"></td>
  </tr>
 </table>
</form>
<?php
	echo foot();
}
elseif ($job == 'newsletter_view') {
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}newsletter WHERE id = '{$id}' LIMIT 1");
	$row = $gpc->prepare($db->fetch_assoc($result));
	echo head();
?>
<form name="form" method="post" action="admin.php?action=members&job=newsletter_delete">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_member_newsletter'); ?> <?php echo $row['id']; ?></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_title'); ?></td>
   <td class="mbox"><?php echo $row['title']; ?></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_from'); ?></td>
   <td class="mbox"><?php echo $row['sender']; ?></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_recipients'); ?></td>
   <td class="mbox"><?php echo count(explode(',', $row['receiver'])); ?></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_time_sent'); ?></td>
   <td class="mbox"><?php echo gmdate('d.m.Y, H:i', times($row['time'])); ?></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_format'); ?></td>
   <td class="mbox"><?php echo iif($row['type'] == 'h', '(x)HTML', 'Plain text'); ?></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_member_text'); ?></td>
  </tr>
  <tr>
   <td class="mbox" colspan="2">
   <?php if ($row['type'] == 'h') { ?>
   <iframe src="admin.php?action=members&amp;job=newsletter_html&amp;id=<?php echo $row['id']; ?>" width="700" height="500"></iframe>
   <?php } else { ?>
   <pre><?php echo $row['content']; ?></pre>
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="hidden" name="delete[]" value="<?php echo $row['id']; ?>"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_member_delete'); ?>"></td>
  </tr>
 </table>
</form>
<?php
	echo foot();
}
elseif ($job == 'newsletter_html') {
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}newsletter WHERE id = '{$id}' LIMIT 1");
	$row = $db->fetch_assoc($result);
	echo $row['content'];
}
elseif ($job == 'newsletter_delete') {
	echo head();
	$del = $gpc->get('delete', arr_int);
	if (count($del) > 0) {
		$ids = implode(',', $del);
		$db->query("DELETE FROM {$db->pre}newsletter WHERE id IN ($ids)");
		$anz = $db->affected_rows();
		ok('admin.php?action=members&job=newsletter_archive', $lang->phrase('admin_member_nl_been_deleted'));
	}
	else {
		error('admin.php?action=members&job=newsletter_archive', $lang->phrase('admin_member_no_nl_deleted'));
	}

}
elseif ($job == 'merge') {
	echo head();
	?>
<form name="form2" method="post" action="admin.php?action=members&job=merge2">
<table class="border">
<tr><td class="obox" colspan="2"><?php echo $lang->phrase('admin_member_merge_users'); ?></td></tr>
<tr><td class="ubox" colspan="2"><?php echo $lang->phrase('admin_member_merge_help'); ?></td></tr>
<tr>
<td class="mbox"><?php echo $lang->phrase('admin_member_basemember'); ?></td>
<td class="mbox">
	<input type="text" name="name1" id="name1" onkeyup="ajax_searchmember(this, 'sugg1');" size="40" /><br />
	<span class="stext"><?php echo $lang->phrase('admin_member_suggestions'); ?> <span id="sugg1"></span></span>
</td>
</tr>
<td class="mbox"><?php echo $lang->phrase('admin_member_needlessmember'); ?></td>
<td class="mbox">
	<input type="text" name="name2" id="name2" onkeyup="ajax_searchmember(this, 'sugg2');" size="40" /><br />
	<span class="stext"><?php echo $lang->phrase('admin_member_suggestions'); ?> <span id="sugg2"></span></span>
</td>
</tr>
<tr>
<td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_member_submit'); ?>"></td>
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
		error('admin.php?action=members&job=merge', 'At least one of the selected users could not be found.');
	}
	$base = $db->fetch_assoc($result);
	$old = $db->fetch_assoc($result2);

	// Step 2: Update abos
	$db->query("UPDATE {$db->pre}abos SET mid = '{$base['id']}' WHERE mid = '{$old['id']}'"); // Multiple entries with different type are possible!
	// Step 3: Delete exactly the same abos
	$result = $db->query("SELECT id FROM {$db->pre}abos WHERE mid = '1' GROUP BY tid, type HAVING COUNT(*) > 1");
	if ($db->num_rows($result) > 0) {
		$ids = array();
		while ($row = $db->fetch_assoc($result)) {
			$ids[] = $row['id'];
		}
		$ids = implode(',', $ids);
		$db->query("DELETE FROM {$db->pre}abos WHERE id IN ({$ids})");
	}
	// Step 4: Update mods (keep the settings from base member)
	$result = $db->query("SELECT bid FROM {$db->pre}moderators WHERE mid = '{$base['id']}'");
	while ($row = $db->fetch_assoc($result)) {
		// Delete settings from old member when there is data for the base member
		$db->query("DELETE FROM {$db->pre}moderators WHERE mid = '{$old['id']}' AND bid = '{$row['bid']}'");
	}
	// All the other mod data move to new account
	$db->query("UPDATE {$db->pre}moderators SET mid = '{$base['id']}' WHERE mid = '{$old['id']}'");
	// Step 5: Update pms
	$db->query("UPDATE {$db->pre}pm SET pm_to = '{$base['id']}' WHERE pm_to = '{$old['id']}'");
	$db->query("UPDATE {$db->pre}pm SET pm_from = '{$base['id']}' WHERE pm_from = '{$old['id']}'");
	// Step 6: Update posts
	$db->query("UPDATE {$db->pre}replies SET name = '{$base['id']}' WHERE name = '{$old['id']}' AND guest = '0'");
	// Step 7: Update topics
	$db->query("UPDATE {$db->pre}topics SET name = '{$base['id']}' WHERE name = '{$old['id']}'");
	$db->query("UPDATE {$db->pre}topics SET last_name = '{$base['id']}' WHERE last_name = '{$old['id']}'");
	// Step 8: Update uploads
	$db->query("UPDATE {$db->pre}uploads SET mid = '{$base['id']}' WHERE mid = '{$old['id']}'");
	// Step 9: Delete pic
	removeOldImages('uploads/pics/', $old['id']);
	// Step 10: Update votes (@TODO Optimze this)
	// Get topics the base member has voted for
	$result = $db->query("
		SELECT p.tid, v.id
		FROM {$db->pre}votes AS v
			LEFT JOIN {$db->pre}vote AS p ON p.id = v.aid
		WHERE v.mid = '{$base['id']}'
	");
	$ids_base = array();
	while ($row = $db->fetch_assoc($result)) {
		$ids_base[] = $row['tid'];
	}
	// Get topics the old member has voted for
	$result = $db->query("
		SELECT p.tid, v.id
		FROM {$db->pre}votes AS v
			LEFT JOIN {$db->pre}vote AS p ON p.id = v.aid
		WHERE v.mid = '{$old['id']}'
	");
	$ids_old = array();
	while ($row = $db->fetch_assoc($result)) {
		$ids_old[$row['id']] = $row['tid'];
	}
	// Get the topics where both users have voted, keep the vote id from the old user
	$delete = array_intersect($ids_old, $ids_base);
	// Delete multiple votes if existant
	if (count($delete) > 0) {
		$delete = implode(',', array_keys($delete));
		$db->query("DELETE FROM {$db->pre}votes WHERE id IN ({$delete})");
	}
	// Update all votes that hasn't been double
	$db->query("UPDATE {$db->pre}votes SET mid = '{$base['id']}' WHERE mid = '{$old['id']}'");

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
	if (empty($base['skype']) && !empty($old['skype'])) {
		$newdata[] ="skype = '{$old['skype']}'";
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
	if (($base['birthday'] == '0000-00-00' || $base['birthday'] == '1000-00-00') && $old['birthday'] != '0000-00-00' && $old['birthday'] != '1000-00-00') {
		$newdata[] ="birthday = '{$old['birthday']}'";
	}
	if ((!isset($base['timezone']) || $base['timezone'] === null) && !empty($old['timezone'])) {
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
		$groups = saveCommaSeparated($groups);
		$newdata[] ="groups = '{$groups}'";
	}
	if (count($newdata) > 0) {
		$db->query("UPDATE {$db->pre}user SET ".implode(', ', $newdata)." WHERE id = '".$base['id']."' LIMIT 1");
	}
	// Step 12: Delete old user
	$db->query("DELETE FROM {$db->pre}user WHERE id = '".$old['id']."'");

	// Step 13: Recount User Post Count
	UpdateMemberStats($base['id']);

	$cache = $scache->load('memberdata');
	$cache = $cache->delete();

	ok('admin.php?action=members&job=manage', "{$old['name']}'s data is converted to {$base['name']}'s Account.");
}
elseif ($job == 'manage') {
	send_nocache_header();
	echo head();
	$sort = $gpc->get('sort', str);
	$order = $gpc->get('order', int);
	$letter = $gpc->get('letter', str);
	$page = $gpc->get('page', int, 1);

	$count = $db->fetch_num($db->query('SELECT COUNT(*) FROM '.$db->pre.'user'));
	$temp = pages($count[0], "admin.php?action=members&job=manage&sort=".$sort."&amp;letter=".$letter."&amp;order=".$order."&amp;", 25);

	if ($order == '1') $order = 'desc';
	else $order = 'asc';

	if ($sort == 'regdate') $sort = 'regdate';
	elseif ($sort == 'location') $sort = 'location';
	elseif ($sort == 'posts') $sort = 'posts';
	elseif ($sort == 'lastvisit') $sort = 'lastvisit';
	else $sort = 'name';

	$start = $page*25;
	$start = $start-25;

	$result = $db->query('SELECT * FROM '.$db->pre.'user ORDER BY '.$sort.' '.$order.' LIMIT '.$start.',25');
	?>
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	 <tr>
	  <td class="obox button_multiline">
	   <a class="button" href="admin.php?action=members&amp;job=register"><?php echo $lang->phrase('admin_member_add_new_member'); ?></a>
	   <?php if ($my->settings['admin_interface'] == 0) { ?>
	   <a class="button" href="admin.php?action=members&amp;job=search"><?php echo $lang->phrase('admin_member_search_members'); ?></a>
	   <a class="button" href="admin.php?action=members&amp;job=inactive"><?php echo $lang->phrase('admin_member_inactive_members'); ?></a>
	   <?php } ?>
	   <a class="button" href="admin.php?action=members&amp;job=reserve"><?php echo $lang->phrase('admin_member_reserve_names_title'); ?></a>
	   <a class="button" href="admin.php?action=members&amp;job=memberrating"><?php echo $lang->phrase('admin_member_memberratings'); ?></a>
	   <a class="button" href="admin.php?action=members&amp;job=merge"><?php echo $lang->phrase('admin_member_merge_users'); ?></a>
	   <a class="button" href="admin.php?action=members&amp;job=recount"><?php echo $lang->phrase('admin_member_recount_post_counts'); ?></a>
	  </td>
	 </tr>
	</table>
	<br class="minibr" />
	<form name="form" action="admin.php?action=members&job=delete" method="post">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		<tr>
		  <td class="obox" colspan="8"><?php echo $lang->phrase('admin_member_member_list'); ?></td>
		</tr>
		<tr>
		  <td class="ubox" colspan="8"><span style="float: right;"><?php echo $temp; ?></span><?php echo $count[0]; ?> <?php echo $lang->phrase('admin_member_members'); ?></td>
		</tr>
		<tr>
		  <td class="obox"><?php echo $lang->phrase('admin_member_delete'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /> <?php echo $lang->phrase('admin_member_all'); ?></span></td>
		  <td class="obox"><?php echo $lang->phrase('admin_member_username'); ?>
		  <a href="admin.php?action=members&amp;job=manage&amp;letter=<?php echo $letter; ?>&amp;page=<?php echo $page; ?>"><img src="admin/html/images/asc.gif" border="0" alt="<?php echo $lang->phrase('admin_member_asc'); ?>"></a>
		  <a href="admin.php?action=members&amp;job=manage&amp;order=1&amp;page=<?php echo $page; ?>&amp;letter=<?php echo $letter; ?>"><img src="admin/html/images/desc.gif" border="0" alt="<?php echo $lang->phrase('admin_member_desc'); ?>"></a></td>
		  <td class="obox"><?php echo $lang->phrase('admin_member_mail'); ?></td>
		  <td class="obox"><?php echo $lang->phrase('admin_member_posts'); ?>
		  <a href="admin.php?action=members&amp;job=manage&amp;sort=posts&amp;letter=<?php echo $letter; ?>&amp;page=<?php echo $page; ?>"><img src="admin/html/images/asc.gif" border=0 alt="<?php echo $lang->phrase('admin_member_asc'); ?>"></a>
		  <a href="admin.php?action=members&amp;job=manage&amp;sort=posts&amp;letter=<?php echo $letter; ?>&amp;order=1&amp;page=<?php echo $page; ?>"><img src="admin/html/images/desc.gif" border=0 alt="<?php echo $lang->phrase('admin_member_desc'); ?>"></a></td>
		  <td class="obox"><?php echo $lang->phrase('admin_member_residence'); ?>
		  <a href="admin.php?action=members&amp;job=manage&samp;ort=location&amp;letter=<?php echo $letter; ?>&amp;page=<?php echo $page; ?>"><img src="admin/html/images/asc.gif" border=0 alt="<?php echo $lang->phrase('admin_member_asc'); ?>"></a>
		  <a href="admin.php?action=members&amp;job=manage&amp;sort=location&amp;letter=<?php echo $letter; ?>&amp;order=1&amp;page=<?php echo $page; ?>"><img src="admin/html/images/desc.gif" border=0 alt="<?php echo $lang->phrase('admin_member_desc'); ?>"></a></td>
		  <td class="obox"><?php echo $lang->phrase('admin_member_last_visit'); ?>
		  <a href="admin.php?action=members&amp;job=manage&amp;sort=lastvisit&amp;letter=<?php echo $letter; ?>&amp;page=<?php echo $page; ?>"><img src="admin/html/images/asc.gif" border=0 alt="<?php echo $lang->phrase('admin_member_asc'); ?>"></a>
		  <a href="admin.php?action=members&amp;job=manage&amp;sort=lastvisit&amp;letter=<?php echo $letter; ?>&amp;order=1&amp;page=<?php echo $page; ?>"><img src="admin/html/images/desc.gif" border=0 alt="<?php echo $lang->phrase('admin_member_desc'); ?>"></a></td>
		  <td class="obox"><?php echo $lang->phrase('admin_member_reg_date'); ?>
		  <a href="admin.php?action=members&amp;job=manage&amp;sort=regdate&amp;letter=<?php echo $letter; ?>&amp;page=<?php echo $page; ?>"><img src="admin/html/images/asc.gif" border=0 alt="<?php echo $lang->phrase('admin_member_asc'); ?>"></a>
		  <a href="admin.php?action=members&amp;job=manage&amp;sort=regdate&amp;letter=<?php echo $letter; ?>&amp;order=1&amp;page=<?php echo $page; ?>"><img src="admin/html/images/desc.gif" border=0 alt="<?php echo $lang->phrase('admin_member_desc'); ?>"></a></td>
		</tr>
	<?php
	while ($row = $slog->cleanUserData($db->fetch_object($result))) {
		$row->regdate = gmdate('d.m.Y', times($row->regdate));
		if ($row->lastvisit == 0) {
			$row->lastvisit = 'Never';
		}
		else {
			$row->lastvisit = gmdate('d.m.Y H:i', times($row->lastvisit));
		}
		?>
		<tr>
		  <td class="mbox"><input type="checkbox" name="delete[]" value="<?php echo $row->id; ?>"></td>
		  <td class="mbox"><a title="<?php echo $lang->phrase('admin_member_edit'); ?>" href="admin.php?action=members&job=edit&id=<?php echo $row->id; ?>"><?php echo $row->name; ?></a><?php echo iif($row->fullname,"<br><i>".$row->fullname."</i>"); ?></td>
		  <td class="mbox" align="center"><a href="mailto:<?php echo $row->mail; ?>"><?php echo $lang->phrase('admin_member_mail'); ?></a></td>
		  <td class="mbox"><a title="<?php echo $lang->phrase('admin_member_recount'); ?>" href="admin.php?action=members&amp;job=recount&amp;id=<?php echo $row->id; ?>"><?php echo $row->posts; ?></a></td>
		  <td class="mbox"><?php echo iif($row->location,$row->location,'-'); ?></td>
		  <td class="mbox"><?php echo $row->lastvisit; ?></td>
		  <td class="mbox"><?php echo $row->regdate; ?></td>
		</tr>
		<?php
	}
	?>
		<tr>
		  <td class="ubox" colspan="8"><span style="float: right;"><?php echo $temp; ?></span><input type="submit" name="submit" value="<?php echo $lang->phrase('admin_member_delete'); ?>"></td>
		</tr>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'memberrating') {
	echo head();
	$page = $gpc->get('page', int, 1);

	$count = $db->fetch_num($db->query('SELECT COUNT(*) FROM '.$db->pre.'postratings WHERE aid != "0" GROUP BY aid'));
	$temp = pages($count[0], "admin.php?action=members&job=memberrating&amp;", 25);

	$start = $page*25;
	$start = $start-25;

	$change = array('m' => 'male', 'w' => 'female', '' => '-');

	$result = $db->query('
	SELECT u.*, avg(p.rating) AS ravg, count(*) AS rcount
	FROM '.$db->pre.'postratings AS p
		LEFT JOIN '.$db->pre.'user AS u ON p.aid = u.id
	WHERE aid != "0"
	GROUP BY aid
	ORDER BY ravg DESC
	LIMIT '.$start.',25
	');
	?>
	<form name="form" action="admin.php?action=members&job=delete" method="post">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		<tr>
		  <td class="obox" colspan="6"><?php echo $lang->phrase('admin_member_memberrating'); ?></td>
		</tr>
		<tr>
		  <td class="ubox" colspan="6"><span style="float: right;"><?php echo $temp; ?></span><?php echo $count[0]; ?> <?php echo $lang->phrase('admin_member_rated_members'); ?></td>
		</tr>
		<tr>
		  <td class="obox"><?php echo $lang->phrase('admin_member_delete'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /> <?php echo $lang->phrase('admin_member_all'); ?></span></td>
		  <td class="obox"><?php echo $lang->phrase('admin_member_username'); ?></td>
		  <td class="obox"><?php echo $lang->phrase('admin_member_rating_amount'); ?></td>
		  <td class="obox"><?php echo $lang->phrase('admin_member_mail'); ?></td>
		  <td class="obox"><?php echo $lang->phrase('admin_member_last_visit'); ?></td>
		  <td class="obox"><?php echo $lang->phrase('admin_member_reg_date'); ?></td>
		</tr>
	<?php
	while ($row = $slog->cleanUserData($db->fetch_object($result))) {
		$row->regdate = gmdate('d.m.Y', times($row->regdate));
		if ($row->lastvisit == 0) {
			$row->lastvisit = $lang->phrase('admin_member_never');
		}
		else {
			$row->lastvisit = gmdate('d.m.Y H:i', times($row->lastvisit));
		}
		$percent = round((($row->ravg*50)+50));
		?>
		<tr>
		  <td class="mbox"><input type="checkbox" name="delete[]" value="<?php echo $row->id; ?>"></td>
		  <td class="mbox"><a title="<?php echo $lang->phrase('admin_member_edit'); ?>" href="admin.php?action=members&job=edit&id=<?php echo $row->id; ?>"><?php echo $row->name; ?></a><?php echo iif($row->fullname,"<br><i>".$row->fullname."</i>"); ?></td>
		  <td class="mbox"><img src="images.php?action=memberrating&id=<?php echo $row->id; ?>" alt="<?php echo $percent; ?>%" title="<?php echo $percent; ?>%"  /> <?php echo $percent; ?>% (<?php echo $row->rcount; ?>)</td>
		  <td class="mbox" align="center"><a href="mailto:<?php echo $row->mail; ?>"><?php echo $lang->phrase('admin_member_mail'); ?></a></td>
		  <td class="mbox"><?php echo $row->lastvisit; ?></td>
		  <td class="mbox"><?php echo $row->regdate; ?></td>
		</tr>
		<?php
	}
	?>
		<tr>
		  <td class="ubox" colspan="6"><span style="float: right;"><?php echo $temp; ?></span><input type="submit" name="submit" value="<?php echo $lang->phrase('admin_member_delete'); ?>"></td>
		</tr>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'recount') {
	echo head();
	$id = $gpc->get('id', int);
	if (is_id($id)) {
		$result = $db->query("SELECT id, posts FROM {$db->pre}user WHERE id = '{$id}'");
		if ($db->num_rows($result) != 1) {
			error('admin.php?action=members&job=manage', $lang->phrase('admin_member_user_not_found'));
		}
		else {
			$user = $db->fetch_assoc($result);
			$posts = UpdateMemberStats($id);
			$diff = $posts - $user['posts'];
			ok('admin.php?action=members&job=manage', $lang->phrase('admin_member_posts_recounted'));
		}
	}
	else {
		$confirm = $gpc->get('confirm', int);
		if ($confirm > 0) {

			$cat_bid_obj = $scache->load('cat_bid');
			$boards = $cat_bid_obj->get();
			$id = array();
			foreach ($boards as $board) {
				if ($board['count_posts'] == 0) {
					$id[] = $board['id'];
				}
			}

			$result = $db->query("
				SELECT COUNT(*) AS new, u.posts, u.id
				FROM {$db->pre}replies AS r
					LEFT JOIN {$db->pre}user AS u ON u.id = r.name
				WHERE r.guest = '0'". iif(count($id) > 0, " AND r.board NOT IN (".implode(',', $id).")") ."
				GROUP BY u.id
			");


			$i = 0;
			while ($row = $db->fetch_assoc($result)) {
				if ($row['new'] != $row['posts']) {
					$i++;
					$db->query("UPDATE {$db->pre}user SET posts = '{$row['new']}' WHERE id = '{$row['id']}'");
				}
			}

			ok("admin.php?action=members&job=manage", $lang->phrase('admin_member_posts_for_recounted'));
		}
		else {
			echo head();
			?>
			<table class="border">
			<tr><td class="obox"><?php echo $lang->phrase('admin_member_recount_post_counts_title'); ?></td></tr>
			<tr><td class="mbox">
				<p align="center"><?php echo $lang->phrase('admin_member_recount_proceed'); ?></p>
				<p align="center">
					<a href="admin.php?action=members&amp;job=recount&amp;confirm=1"><img alt="<?php echo $lang->phrase('admin_member_yes'); ?>" border="0" src="admin/html/images/yes.gif" /> <?php echo $lang->phrase('admin_member_yes'); ?></a>
					&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="javascript: history.back(-1);"><img border="0" alt="<?php echo $lang->phrase('admin_member_no'); ?>" src="admin/html/images/no.gif" /> <?php echo $lang->phrase('admin_member_no'); ?></a>
				</p>
			</td></tr>
			</table>
			<?php
			echo foot();
			?>


			<?php
		}
	}
}
elseif ($job == 'register') {
	include_once ("classes/function.profilefields.php");
	$customfields = addprofile_customfields();

	echo head();
	?>
	<form name="form2" method="post" action="admin.php?action=members&amp;job=register2">
	<table class="border">
	<tr><td class="obox" colspan="2"><?php echo $lang->phrase('admin_member_add_a_new_member'); ?></td></tr>
	<tr>
		<td class="mbox">
		<?php echo $lang->phrase('admin_member_cmp_name'); ?><br />
			<span class="stext"><?php echo $lang->phrase('admin_member_min_max_name'); ?></span>
		</td>
		<td class="mbox">
			<input type="text" name="name" size="40" />
		</td>
	</tr><tr>
		<td class="mbox">
		<?php echo $lang->phrase('admin_member_password'); ?><br />
			<span class="stext"><?php echo $lang->phrase('admin_member_min_max_pw'); ?></span>
			</td>
		<td class="mbox">
			<input type="password" name="pw" size="40" />
		</td>
	</tr><tr>
		<td class="mbox">
		<?php echo $lang->phrase('admin_member_confirm_password'); ?>
		</td>
		<td class="mbox">
			<input type="password" name="pwx" size="40" />
		</td>
	</tr><tr>
		<td class="mbox">
		<?php echo $lang->phrase('admin_member_mail_address'); ?>
			<?php if ($config['sessionmails'] == 1) { ?>
			  <br /><span class="stext"><?php echo $lang->phrase('admin_member_disposable_mail_not_allowed'); ?></span>
			<?php } ?>
		</td>
		<td class="mbox">
			<input type="text" name="email" size="40" />
		</td>
	</tr>
	<?php foreach ($customfields as $row) { ?>
	<tr>
	  <td class="mbox">
	  	<?php echo $row['name']; ?>
	  	<?php if(!empty($row['description'])) { ?>
	  	  <br /><span class="stext"><?php echo $row['description']; ?></span>
	  	<?php } ?>
	  </td>
	  <td class="mbox">
	  	<?php echo $row['input']; ?>
	  </td>
	</tr>
	<?php } ?>
	<tr>
		<td class="ubox" align="center" colspan="2">
			<input accesskey="s" type="submit" name="Submit1" value="<?php echo $lang->phrase('admin_member_submit'); ?>" />
		</td>
	</tr>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'register2') {
	include_once ("classes/function.profilefields.php");

	$name = $gpc->get('name', str);
	$email = $gpc->get('email', db_esc);
	$pw = $gpc->get('pw', str);
	$pwx = $gpc->get('pwx', str);

	$error = array();
	if (double_udata('name', $name) == false) {
		$error[] = $lang->phrase('admin_member_username_already_in_use');
	}
	if (double_udata('mail', $email) == false) {
		$error[] = $lang->phrase('admin_member_mail_already_in_use');
	}
	if (strxlen($name) > $config['maxnamelength']) {
		$error[] = $lang->phrase('admin_member_name_too_long');
	}
	if (strxlen($name) < $config['minnamelength']) {
		$error[] = $lang->phrase('admin_member_name_too_short');
	}
	if (strxlen($pw) > $config['maxpwlength']) {
		$error[] = $lang->phrase('admin_member_password_too_long');
	}
	if (strxlen($pw) < $config['minpwlength']) {
		$error[] = $lang->phrase('admin_member_password_too_short');
	}
	if (strlen($email) > 200) {
		$error[] = $lang->phrase('admin_member_mail_too_long');
	}
	if (check_mail($email) == false) {
		$error[] = $lang->phrase('admin_member_mail_not_valid');
	}
	if ($pw != $pwx) {
		$error[] = $lang->phrase('admin_member_passwords_different');
	}

	// Custom profile fields
	$custom = addprofile_customprepare('admin_member_no_value_for_required_field', 'admin_member_to_many_chars_for_required_fields');
	$error = array_merge($error, $custom['error']);

	if (count($error) > 0) {
		echo head();
		error("admin.php?action=members&job=register", $error);
	}
	else {
		$reg = time();
		$pw_md5 = md5($pwx);

		$db->query("INSERT INTO {$db->pre}user (name, pw, mail, regdate, confirm, groups, signature, about, notice) VALUES ('{$name}', '{$pw_md5}', '{$email}', '{$reg}', '11', '".GROUP_MEMBER."', '', '', '')");
		$redirect = $db->insert_id();

		addprofile_customsave($custom['data'], $redirect);

		$com = $scache->load('memberdata');
		$cache = $com->delete();

		echo head();
		ok("admin.php?action=members&job=edit&id=".$redirect, $lang->phrase('admin_member_member_added'));
	}
}
elseif ($job == 'edit') {
	include_once ("classes/function.profilefields.php");

	echo head();

	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}user WHERE id = '{$id}'");
	if ($db->num_rows($result) != 1) {
		error('admin.php?action=members&job=manage', $lang->phrase('admin_member_no_id'));
	}
	$user = $gpc->prepare($db->fetch_assoc($result));

	$chars = $config['maxaboutlength'];

	$loadlanguage_obj = $scache->load('loadlanguage');
	$language = $loadlanguage_obj->get();
	if (!isset($language[$user['language']]['language'])) {
		$user['language'] = $config['langdir'];
	}
	$mylanguage = $language[$user['language']]['language'];

	$loaddesign_obj = $scache->load('loaddesign');
	$design = $loaddesign_obj->get();
	if (!isset($design[$user['template']]['name'])) {
		$user['template'] = $config['templatedir'];
	}
	$mydesign = $design[$user['template']]['name'];

	// Profile
	$bday = explode('-',$user['birthday']);
	$year = gmdate('Y');
	$maxy = $year-6;
	$miny = $year-100;
	$result = $db->query("SELECT id, title, name, core FROM {$db->pre}groups WHERE guest = '0' ORDER BY admin DESC , guest ASC , core ASC");
	$random = md5(microtime());

	$customfields = admin_customfields($user['id']);
?>
<form name="form_<?php echo $random; ?>" method="post" action="admin.php?action=members&job=edit2&amp;id=<?php echo $id; ?>&amp;random=<?php echo $random; ?>">
<table class="border">
<tr><td class="obox" colspan="2"><?php echo $lang->phrase('admin_member_edit_member'); ?></td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_nickname'); ?></td><td class="mbox">
<input type="text" name="name_<?php echo $random; ?>" size="40" value="<?php echo $user['name']; ?>" />
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_new_password'); ?></td><td class="mbox">
<input type="password" name="pw_<?php echo $random; ?>" size="40" value="" />
</td></tr>
<tr><td class="mbox" valign="top"><?php echo $lang->phrase('admin_member_group_s'); ?><br />
<span class="stext"><?php echo $lang->phrase('admin_member_multiple_groups_possible'); ?></span>
</td><td class="mbox">
<input type="text" name="groups" id="groups" size="40" value="<?php echo $user['groups']; ?>" />
<br />
<table class="inlinetable">
<tr>
<th>ID</th>
<th><?php echo $lang->phrase('admin_member_internal_group_name'); ?></th>
<th><?php echo $lang->phrase('admin_member_public_group_title'); ?></th>
</tr>
<?php while ($row = $gpc->prepare($db->fetch_assoc($result))) { ?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['title']; ?></td>
</tr>
<?php } ?>
</table>
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_cmp_civil_name'); ?></td><td class="mbox">
<input type="text" name="fullname" id="fullname" size="40" value="<?php echo $user['fullname']; ?>" />
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_mail_address'); ?></td><td class="mbox">
<input type="text" name="email" id="email" size="40" value="<?php echo $user['mail']; ?>" />
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_lcmp_ocation'); ?></td><td class="mbox">
<input type="text" name="location" id="location" size="40" value="<?php echo $user['location']; ?>" />
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_cmp_gender'); ?></td><td class="mbox">
<select size="1" name="gender">
	<option value=""><?php echo $lang->phrase('admin_member_not_specified'); ?></option>
	<option<?php echo iif($user['gender'] == 'm',' selected="selected"'); ?> value="m"><?php echo $lang->phrase('admin_member_male'); ?></option>
	<option<?php echo iif($user['gender'] == 'w',' selected="selected"'); ?> value="w"><?php echo $lang->phrase('admin_member_female'); ?></option>
</select>
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_cmp_birthday'); ?></td><td class="mbox">
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
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_cmp_homepage'); ?></td><td class="mbox">
<input type="text" name="hp" id="hp" size="40" value="<?php echo $user['hp']; ?>" />
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_cmp_icq'); ?></td><td class="mbox">
<input type="text" name="icq" id="icq" size="40" value="<?php echo iif(!empty($user['icq']), $user['icq']); ?>" />
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_cmp_aol'); ?></td><td class="mbox">
<input type="text" name="aol" id="aol" size="40" value="<?php echo $user['aol']; ?>" />
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_cmp_yim'); ?></td><td class="mbox">
<input type="text" name="yahoo" id="yahoo" size="40" value="<?php echo $user['yahoo']; ?>" />
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_cmp_msn'); ?></td><td class="mbox">
<input type="text" name="msn" id="msn" size="40" value="<?php echo $user['msn']; ?>" />
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_cmp_jabber'); ?></td><td class="mbox">
<input type="text" name="jabber" id="jabber" size="40" value="<?php echo $user['jabber']; ?>" />
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_cmp_skype'); ?></td><td class="mbox">
<input type="text" name="skype" id="skype" size="40" value="<?php echo $user['skype']; ?>" />
</td></tr>
<?php foreach ($customfields['1'] as $row1) { ?>
<tr><td class="mbox"><?php echo $row1['name'] . iif(!empty($row1['description']), '<br /><span class="stext">'.$row1['description'].'</span>'); ?></td>
<td class="mbox"> <?php echo $row1['input']; ?></td></tr>
<?php } ?>
<tr><td class="ubox" align="center" colspan="2"><input accesskey="s" type="submit" name="Submit1" value="<?php echo $lang->phrase('admin_member_submit'); ?>" /></td></tr>
</table>

<br class="minibr" />
<table class="border">
<tr><td class="obox"><?php echo $lang->phrase('admin_member_signature'); ?></td></tr>
<tr><td class="mbox" align="center"><textarea name="signature" rows="4" cols="110"><?php echo $user['signature']; ?></textarea></td></tr>
<tr><td class="ubox" align="center"><input accesskey="s" type="submit" name="Submit1" value="<?php echo $lang->phrase('admin_member_submit'); ?>" /></td></tr>
</table>
<br class="minibr" />

<table class="border">
<tr><td class="obox" colspan="2"><?php echo $lang->phrase('admin_member_change_avatar'); ?></td></tr>
<tr>
<td class="mbox"><?php echo $lang->phrase('admin_member_add_url_avatar'); ?></td>
<td class="mbox"><input type="text" name="pic" id="pic" size="70" value="<?php echo $user['pic']; ?>" /></td>
</tr>
<tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" name="Submit1" value="<?php echo $lang->phrase('admin_member_submit'); ?>" /></td></tr>
</table>
<br class="minibr" />

<table class="border">
<tr><td class="obox" colspan="2"><?php echo $lang->phrase('admin_member_edit_options'); ?></td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_cmp_time_zone'); ?></td><td class="mbox">
<select id="temp" name="temp">
	<option value="-12"<?php selectTZ($user['timezone'], -12); ?>><?php echo $lang->phrase('timezone_n12'); ?></option>
	<option value="-11"<?php selectTZ($user['timezone'], -11); ?>><?php echo $lang->phrase('timezone_n11'); ?></option>
	<option value="-10"<?php selectTZ($user['timezone'], -10); ?>><?php echo $lang->phrase('timezone_n10'); ?></option>
	<option value="-9"<?php selectTZ($user['timezone'], -9); ?>><?php echo $lang->phrase('timezone_n9'); ?></option>
	<option value="-8"<?php selectTZ($user['timezone'], -8); ?>><?php echo $lang->phrase('timezone_n8'); ?></option>
	<option value="-7"<?php selectTZ($user['timezone'], -7); ?>><?php echo $lang->phrase('timezone_n7'); ?></option>
	<option value="-6"<?php selectTZ($user['timezone'], -6); ?>><?php echo $lang->phrase('timezone_n6'); ?></option>
	<option value="-5"<?php selectTZ($user['timezone'], -5); ?>><?php echo $lang->phrase('timezone_n5'); ?></option>
	<option value="-4"<?php selectTZ($user['timezone'], -4); ?>><?php echo $lang->phrase('timezone_n4'); ?></option>
	<option value="-3.5"<?php selectTZ($user['timezone'], -3.5); ?>><?php echo $lang->phrase('timezone_n35'); ?></option>
	<option value="-3"<?php selectTZ($user['timezone'], -3); ?>><?php echo $lang->phrase('timezone_n3'); ?></option>
	<option value="-2"<?php selectTZ($user['timezone'], -2); ?>><?php echo $lang->phrase('timezone_n2'); ?></option>
	<option value="-1"<?php selectTZ($user['timezone'], -1); ?>><?php echo $lang->phrase('timezone_n1'); ?></option>
	<option value="0"<?php selectTZ($user['timezone'], 0); ?>><?php echo $lang->phrase('timezone_0'); ?></option>
	<option value="+1"<?php selectTZ($user['timezone'], 1); ?>><?php echo $lang->phrase('timezone_p1'); ?></option>
	<option value="+2"<?php selectTZ($user['timezone'], 2); ?>><?php echo $lang->phrase('timezone_p2'); ?></option>
	<option value="+3"<?php selectTZ($user['timezone'], 3); ?>><?php echo $lang->phrase('timezone_p3'); ?></option>
	<option value="+3.5"<?php selectTZ($user['timezone'], 3.5); ?>><?php echo $lang->phrase('timezone_p35'); ?></option>
	<option value="+4"<?php selectTZ($user['timezone'], 4); ?>><?php echo $lang->phrase('timezone_p4'); ?></option>
	<option value="+4.5"<?php selectTZ($user['timezone'], 4.5); ?>><?php echo $lang->phrase('timezone_p45'); ?></option>
	<option value="+5"<?php selectTZ($user['timezone'], 5); ?>><?php echo $lang->phrase('timezone_p5'); ?></option>
	<option value="+5.5"<?php selectTZ($user['timezone'], 5.5); ?>><?php echo $lang->phrase('timezone_p55'); ?></option>
	<option value="+5.75"<?php selectTZ($user['timezone'], 5.75); ?>><?php echo $lang->phrase('timezone_p575'); ?></option>
	<option value="+6"<?php selectTZ($user['timezone'], 6); ?>><?php echo $lang->phrase('timezone_p6'); ?></option>
	<option value="+6.5"<?php selectTZ($user['timezone'], 6.5); ?>><?php echo $lang->phrase('timezone_p65'); ?></option>
	<option value="+7"<?php selectTZ($user['timezone'], 7); ?>><?php echo $lang->phrase('timezone_p7'); ?></option>
	<option value="+8"<?php selectTZ($user['timezone'], 8); ?>><?php echo $lang->phrase('timezone_p8'); ?></option>
	<option value="+9"<?php selectTZ($user['timezone'], 9); ?>><?php echo $lang->phrase('timezone_p9'); ?></option>
	<option value="+9.5"<?php selectTZ($user['timezone'], 9.5); ?>><?php echo $lang->phrase('timezone_p95'); ?></option>
	<option value="+10"<?php selectTZ($user['timezone'], 10); ?>><?php echo $lang->phrase('timezone_p10'); ?></option>
	<option value="+11"<?php selectTZ($user['timezone'], 11); ?>><?php echo $lang->phrase('timezone_p11'); ?></option>
	<option value="+12"<?php selectTZ($user['timezone'], 12); ?>><?php echo $lang->phrase('timezone_p12'); ?></option>
</select>
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_contribution_editor'); ?></td><td class="mbox">
<select id="opt_0" name="opt_0">
	<option<?php echo iif($user['opt_textarea'] == 0,' selected="selected"'); ?> value="0"><?php echo $lang->phrase('admin_member_simple_editor'); ?></option>
	<option<?php echo iif($user['opt_textarea'] == 1,' selected="selected"'); ?> value="1"><?php echo $lang->phrase('admin_member_advanced_editor'); ?></option>
</select>
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_sending_mail_receiving_pn'); ?></td><td class="mbox">
<input id="opt_1" type="checkbox" name="opt_1" <?php echo iif($user['opt_pmnotify'] == 1,' checked="checked"'); ?> value="1" />
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_hide_bad_rated_topics'); ?></td><td class="mbox">
<input id="opt_2" type="checkbox" name="opt_2" <?php echo iif($user['opt_hidebad'] == 1,' checked="checked"'); ?> value="1" />
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_how_should_mail_be_shown'); ?></td><td class="mbox">
<select id="opt_3" name="opt_3">
	<option<?php echo iif($user['opt_hidemail'] == 0,' selected="selected"'); ?> value="0"><?php echo $lang->phrase('admin_member_show_mail_provide_form'); ?></option>
	<option<?php echo iif($user['opt_hidemail'] == 1,' selected="selected"'); ?> value="1"><?php echo $lang->phrase('admin_member_not_show_mail_no_form'); ?></option>
	<option<?php echo iif($user['opt_hidemail'] == 2,' selected="selected"'); ?> value="2"><?php echo $lang->phrase('admin_member_not_show_mail_show_form'); ?></option>
</select>
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_which_design'); ?></td><td class="mbox">
<select id="opt_4" name="opt_4">
	<option selected="selected" value="<?php echo $user['template']; ?>"><?php echo $lang->phrase('admin_member_keep_design'); ?></option>
	<?php foreach ($design as $row) { ?>
	<option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
	<?php } ?>
</select>
</td></tr>
<tr><td class="mbox"><?php echo $lang->phrase('admin_member_which_lang'); ?></td><td class="mbox">
<select id="opt_5" name="opt_5">
	<option selected="selected" value="<?php echo $user['language']; ?>"><?php echo $lang->phrase('admin_member_keep_lang'); ?></option>
	<?php foreach ($language as $row) { ?>
	<option value="<?php echo $row['id']; ?>"><?php echo $row['language']; ?></option>
	<?php } ?>
</select>
</td></tr>
<?php foreach ($customfields['2'] as $row1) { ?>
<tr><td class="mbox"><?php echo $row1['name'] . iif(!empty($row1['description']), '<br /><span class="stext">'.$row1['description'].'</span>'); ?></td>
<td class="mbox"> <?php echo $row1['input']; ?></td></tr>
<?php } ?>
<?php foreach ($customfields['0'] as $row1) { ?>
<tr><td class="mbox"><?php echo $row1['name'] . iif(!empty($row1['description']), '<br /><span class="stext">'.$row1['description'].'</span>'); ?></td>
<td class="mbox"> <?php echo $row1['input']; ?></td></tr>
<?php } ?>
<tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" name="Submit1" value="<?php echo $lang->phrase('admin_member_submit'); ?>" /></td></tr>
</table>
<br class="minibr" />

<table class="border">
<tr><td class="obox"><?php echo $lang->phrase('admin_member_change_personal_site'); ?></td></tr>
<tr><td class="mbox" align="center"><textarea name="comment" id="comment" rows="15" cols="110"><?php echo $user['about']; ?></textarea></td></tr>
<tr><td class="ubox" align="center"><input accesskey="s" type="submit" name="Submit1" value="<?php echo $lang->phrase('admin_member_submit'); ?>" /></td></tr>
</table>
</form>
<?php
	echo foot();
}
elseif ($job == 'edit2') {
	include_once ("classes/function.profilefields.php");

	echo head();
	$loaddesign_obj = $scache->load('loaddesign');
	$cache = $loaddesign_obj->get();

	$loadlanguage_obj = $scache->load('loadlanguage');
	$cache2 = $loadlanguage_obj->get();

	$keys_int = array('id', 'birthday', 'birthmonth', 'birthyear', 'opt_0', 'opt_1', 'opt_2', 'opt_3', 'opt_4', 'opt_5');
	$keys_str = array('groups', 'fullname', 'email', 'location', 'icq', 'gender', 'hp', 'aol', 'yahoo', 'msn', 'jabber', 'signature', 'pic', 'temp', 'comment', 'skype');
	foreach ($keys_int as $val) {
		$query[$val] = $gpc->get($val, int);
	}
	foreach ($keys_str as $val) {
		$query[$val] = $gpc->get($val, str);
	}

	$result = $db->query('SELECT * FROM '.$db->pre.'user WHERE id = '.$query['id']);
	if ($db->num_rows($result) != 1) {
		error('admin.php?action=members&job=manage', $lang->phrase('admin_member_no_id'));
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
	$query['pw'] = $gpc->get('pw_'.$random, str);

	$query['hp'] = trim($query['hp']);
	if (strtolower(substr($query['hp'], 0, 4)) == 'www.') {
		$query['hp'] = "http://{$query['hp']}";
	}

	$error = array();
	if (strxlen($query['comment']) > $config['maxaboutlength']) {
		$error[] = $lang->phrase('admin_member_about_too_many_chars');
	}
	if (check_mail($query['email']) == false) {
		 $error[] = $lang->phrase('admin_member_no_valid_mail');
	}
	if (strxlen($query['name']) > $config['maxnamelength']) {
		$error[] = $lang->phrase('admin_member_name_too_many_chars');
	}
	if (strxlen($query['name']) < $config['minnamelength']) {
		$error[] = $lang->phrase('admin_member_too_less_chars');
	}
	if (strlen($query['email']) > 200) {
		$error[] = $lang->phrase('admin_member_email_too_many_chars');
	}
	if ($user['mail'] != $_POST['email'] && double_udata('mail', $_POST['email']) == false) {
		 $error[] = $lang->phrase('email_already_used');
	}
	if (strxlen($query['signature']) > $config['maxsiglength']) {
		$error[] = $lang->phrase('admin_member_signature_too_many_chars');
	}
	if (strlen($query['hp']) > 255) {
		$error[] = $lang->phrase('admin_member_hp_too_many_chars');
	}
	if (!check_hp($query['hp'])) {
		$query['hp'] = '';
	}
	if (strlen($query['location']) > 50) {
		$error[] = $lang->phrase('admin_member_location_too_many_chars');
	}
	if ($query['gender'] != 'm' && $query['gender'] != 'w' && $query['gender'] != '') {
		$error[] = $lang->phrase('admin_member_gender_not_valid');
	}
	if ($query['birthday'] > 31) {
		$error[] = $lang->phrase('admin_member_day_not_valid');
	}
	if ($query['birthmonth'] > 12) {
		$error[] = $lang->phrase('admin_member_month_not_valid');
	}
	if (($query['birthyear'] < gmdate('Y')-120 || $query['birthyear'] > gmdate('Y')) && $query['birthyear'] != 0 ) {
		$error[] = $lang->phrase('admin_member_year_not_valid');
	}
	if (strlen($query['fullname']) > 128) {
		$error[] = $lang->phrase('admin_member_fullname_too_many_chars');
	}
	if (intval($query['temp']) < -12 && intval($query['temp']) > 12) {
		$error[] = $lang->phrase('admin_member_time_zone_not_valid');
	}
	if (!isset($cache[$query['opt_4']])) {
		$error[] = $lang->phrase('admin_member_design=not_valid');
	}
	if (!isset($cache2[$query['opt_5']])) {
		$error[] = $lang->phrase('admin_member_lang_not_valid');
	}
	if (!empty($query['pic']) && preg_match('~^'.URL_REGEXP.'$~i', $query['pic'])) {
		$query['pic'] = checkRemotePic($query['pic'], $query['id']);
		switch ($query['pic']) {
			case REMOTE_INVALID_URL:
				$error[] = $lang->phrase('admin_member_ava_url_not_valid');
				$query['pic'] = '';
			break;
			case REMOTE_CLIENT_ERROR:
				$error[] = $lang->phrase('admin_member_ava_not_from_server');
				$query['pic'] = '';
			break;
			case REMOTE_FILESIZE_ERROR:
				$error[] = $lang->phrase('admin_member_ava_filesize_exceeded');
				$query['pic'] = '';
			break;
			case REMOTE_IMAGE_HEIGHT_ERROR:
				$error[] = $lang->phrase('admin_member_ava_height_too_high');
				$query['pic'] = '';
			break;
			case REMOTE_IMAGE_WIDTH_ERROR:
				$error[] = $lang->phrase('admin_member_ava_width_too_high');
				$query['pic'] = '';
			break;
			case REMOTE_EXTENSION_ERROR:
				$error[] = $lang->phrase('admin_member_ava_file_type_not_valid');
				$query['pic'] = '';
			break;
			case REMOTE_IMAGE_ERROR:
				$error[] = $lang->phrase('admin_member_ava_not_parsed');
				$query['pic'] = '';
			break;
		}
	}
	elseif (empty($query['pic']) || !file_exists($query['pic'])) {
		$query['pic'] = '';
	}

	if (count($error) > 0) {
		error('admin.php?action=members&job=edit&id='.$query['id'], $error);
	}
	else {
		// Now we create the birthday...
		if (empty($query['birthmonth']) || empty($query['birthday'])) {
			$query['birthmonth'] = 0;
			$query['birthday'] = 0;
			$query['birthyear'] = 0;
		}
		if (empty($_POST['birthyear'])) {
			$query['birthyear'] = 1000;
		}
		$query['birthmonth'] = leading_zero($query['birthmonth']);
		$query['birthday'] = leading_zero($query['birthday']);
		$query['birthyear'] = leading_zero($query['birthyear'], 4);
		$bday = $query['birthyear'].'-'.$query['birthmonth'].'-'.$query['birthday'];

		$query['icq'] = str_replace('-', '', $query['icq']);
		if (!is_id($query['icq'])) {
			$query['icq'] = 0;
		}

		if (!empty($query['pw']) && strlen($query['pw']) >= $config['minpwlength']) {
			$md5 = md5($query['pw']);
			$update_sql = ", pw = '{$md5}' ";
		}
		else {
			$update_sql = ' ';
		}

		admin_customsave($query['id']);

		$db->query("UPDATE {$db->pre}user SET groups = '".saveCommaSeparated($query['groups'])."', timezone = '{$query['temp']}', opt_textarea = '{$query['opt_0']}', opt_pmnotify = '{$query['opt_1']}', opt_hidebad = '{$query['opt_2']}', opt_hidemail = '{$query['opt_3']}', template = '{$query['opt_4']}', language = '{$query['opt_5']}', pic = '{$query['pic']}', about = '{$query['comment']}', icq = '{$query['icq']}', yahoo = '{$query['yahoo']}', aol = '{$query['aol']}', msn = '{$query['msn']}', jabber = '{$query['jabber']}', birthday = '{$bday}', gender = '{$query['gender']}', hp = '{$query['hp']}', signature = '{$query['signature']}', location = '{$query['location']}', fullname = '{$query['fullname']}', skype = '{$query['skype']}', mail = '{$query['email']}', name = '{$query['name']}' {$update_sql} WHERE id = '{$user['id']}'");

		$cache = $scache->load('memberdata');
		$cache = $cache->delete();

		ok("admin.php?action=members&job=manage", $lang->phrase('admin_member_data_saved'));
	}
}
elseif ($job == 'delete') {
	echo head();
	$delete = $gpc->get('delete', arr_int);
	$mykey = array_search($my->id, $delete);
	if ($mykey !== false) {
		unset($delete[$mykey]);
	}
	if (count($delete) > 0) {
		$did = implode(',', $delete);
		$result = $db->query('SELECT * FROM '.$db->pre.'user WHERE id IN ('.$did.')');
		$olduserdata = file_get_contents('data/deleteduser.php');
		while ($user = $gpc->prepare($db->fetch_assoc($result))) {
			// Step 1: Write Data to File with old Usernames
			$olduserdata .= "\n{$user['id']}\t{$user['name']}";
			$olduserdata = trim($olduserdata);
			// Step 2: Delete all pms
			$db->query("DELETE FROM {$db->pre}pm WHERE pm_to IN ({$did})");
			// Step 3: Search all old posts by an user, and update to guests post
			$db->query("UPDATE {$db->pre}replies SET name = '{$user['name']}', email = '{$user['mail']}', guest = '1' WHERE name = '{$user['id']}' AND guest = '0'");
			// Step 4: Search all old topics by an user, and update to guests post
			$db->query("UPDATE {$db->pre}topics SET name = '{$user['name']}' WHERE name = '{$user['id']}'");
			$db->query("UPDATE {$db->pre}topics SET last_name = '{$user['name']}' WHERE last_name = '{$user['id']}'");
			// Step 5: Delete pic
			removeOldImages('uploads/pics/', $user['id']);
		}
		$filesystem->file_put_contents('data/deleteduser.php', $olduserdata);
		// Step 6: Delete all abos
		$db->query("DELETE FROM {$db->pre}abos WHERE mid IN ({$did})");
		// Step 8: Delete as mod
		$db->query("DELETE FROM {$db->pre}moderators WHERE mid IN ({$did})");
		$delete = $gpc->get('delete', arr_int);
		// Step 9: Set uploads from member to guests-group
		$db->query("UPDATE {$db->pre}uploads SET mid = '0' WHERE mid IN ({$did})");
		// Step 10: Set post ratings from member to guests-group I
		$db->query("UPDATE {$db->pre}postratings SET mid = '0' WHERE mid IN ({$did})");
		// Step 11: Set post ratings from member to guests-group II
		$db->query("UPDATE {$db->pre}postratings SET aid = '0' WHERE aid IN ({$did})");
		// Step 12: Delete user himself
		$db->query("DELETE FROM {$db->pre}user WHERE id IN ({$did})");
		$anz = $db->affected_rows();
		// Step 13: Delete user's custom profile fields
		$db->query("DELETE FROM {$db->pre}userfields WHERE ufid IN ({$did})");

		$cache = $scache->load('memberdata');
		$cache = $cache->delete();

		ok('javascript:history.back(-1);', $anz.' members deleted');
	}
	else {
		error('javascript:history.back(-1);', $lang->phrase('admin_member_no_specification'));
	}

}
elseif ($job == 'banned') {
	echo head();
	$bannedip = file('data/bannedip.php');
	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();
	?>
<form name="form" method="post" action="admin.php?action=members&amp;job=ban_delete">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="7">
	<span class="right"><a class="button" href="admin.php?action=members&amp;job=ban_add"><?php echo $lang->phrase('admin_member_ban_user'); ?></a></span>
   <?php echo $lang->phrase('admin_member_banned_members_ip'); ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" width="2%"><?php echo $lang->phrase('admin_member_del'); ?></td>
   <td class="ubox" width="17%"><?php echo $lang->phrase('admin_member_user_ip'); ?></td>
   <td class="ubox" width="17%"><?php echo $lang->phrase('admin_member_banned_by'); ?></td>
   <td class="ubox" width="12%"><?php echo $lang->phrase('admin_member_banned_on'); ?></td>
   <td class="ubox" width="12%"><?php echo $lang->phrase('admin_member_ban_get_lifted'); ?></td>
   <td class="ubox" width="12%"><?php echo $lang->phrase('admin_member_time_remain'); ?></td>
   <td class="ubox" width="28%"><?php echo $lang->phrase('admin_member_reason'); ?></td>
  </tr>
  <?php
  foreach ($bannedip as $row) {
  	$row = explode("\t", rtrim($row, "\r\n"), 6);
  	if ($row[0] == 'ip') {
  		$data = '<span class="right stext">IP</span><a href="admin.php?action=members&amp;job=ips&amp;ipaddress='.$row[1].'">'.$row[1].'</a>';
  	}
  	elseif ($row[0] == 'user') {
  		if (isset($memberdata[$row[1]]) == true) {
  			$data = '<a href="admin.php?action=members&amp;job=edit&amp;id='.$row[1].'">'.$memberdata[$row[1]].'</a>';
  		}
  		else {
  			$data = '<em>N/A</em>';
  		}

  	}
  	else {
  		continue;
  	}

	if (isset($memberdata[$row[3]]) == true) {
		$row[3] = '<a href="admin.php?action=members&amp;job=edit&amp;id='.$row[3].'">'.$memberdata[$row[3]].'</a>';
	}
	else {
		$row[3] = '<em>N/A</em>';
	}

  	$sec = $row[2] - time();
  	if ($row[2] == 0) {
  		$diff = '-';
  	}
  	elseif ($sec >= 60) {
	  	$days = floor($sec/(60*60*24));
	  	$sec = $sec - $days*60*60*24;
	  	$hours = floor($sec/(60*60));
	  	$sec = $sec - $hours*60*60;
	  	$mins = floor($sec/60);
	  	$diff = "{$days}d {$hours}h {$mins}m";
  	}
  	else {
  		$diff = "<em>".$lang->phrase('admin_member_expired')."</em>";
  	}

  	$row[2] = intval($row[2]);
  	if ($row[2] > 0) {
  		$row[2] = gmdate('d.m.Y H:i', times($row[2]));
  	}
  	else {
  		$row[2] = $lang->phrase('admin_member_never');
  	}

  	$crea = gmdate('d.m.Y H:i', times($row[4]));
  	?>
  <tr>
   <td class="mbox"><input type="checkbox" name="delete[]" value="<?php echo $row[0]; ?>#<?php echo $row[1]; ?>#<?php echo $row[4]; ?>" /></td>
   <td class="mbox"><?php echo $data; ?></td>
   <td class="mbox"><?php echo $row[3]; ?></td>
   <td class="mbox"><?php echo $crea; ?></td>
   <td class="mbox"><?php echo $row[2]; ?></td>
   <td class="mbox"><?php echo $diff; ?></td>
   <td class="mbox"><?php echo htmlspecialchars($row[5]); ?></td>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="7" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_member_lift_bans'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'ban_add') {
	echo head();
	$b = file_get_contents('data/bannedip.php');
	?>
<form name="form" method="post" action="admin.php?action=members&amp;job=ban_add2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_member_ban_user_or_ip'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_member_user_or_ip'); ?></td>
   <td class="mbox" width="60%">
   	<input type="text" name="data" size="60" /><br />
	<?php echo $lang->phrase('admin_member_data_above'); ?>
	<input type="radio" name="type" value="user" checked="checked" /> <?php echo $lang->phrase('admin_member_user_name'); ?>&nbsp;&nbsp;&nbsp;&nbsp;
   	<input type="radio" name="type" value="ip" /> <?php echo $lang->phrase('admin_member_ip'); ?>
   	</td>
  </tr>
  <tr>
   <td class="mbox" width="40%">
   <?php echo $lang->phrase('admin_member_lift_ban'); ?><br />
   	<span class="stext"><?php echo $lang->phrase('admin_member_ban_duration'); ?></span>
   </td>
   <td class="mbox" width="60%">
   	<select name="until">
	<option value="0" selected="selected"><?php echo $lang->phrase('admin_member_permanent_ban'); ?></option>
	<option value="D_1"><?php echo $lang->phrase('admin_member_1day'); ?></option>
	<option value="D_2"><?php echo $lang->phrase('admin_member_2days'); ?></option>
	<option value="D_3"><?php echo $lang->phrase('admin_member_3days'); ?></option>
	<option value="D_4"><?php echo $lang->phrase('admin_member_4days'); ?></option>
	<option value="D_5"><?php echo $lang->phrase('admin_member_5days'); ?></option>
	<option value="D_6"><?php echo $lang->phrase('admin_member_6days'); ?></option>
	<option value="D_7"><?php echo $lang->phrase('admin_member_7week'); ?></option>
	<option value="D_14"><?php echo $lang->phrase('admin_member_weeks'); ?></option>
	<option value="D_21"><?php echo $lang->phrase('admin_member_3weeks'); ?></option>
	<option value="M_1"><?php echo $lang->phrase('admin_member_1month'); ?></option>
	<option value="M_2"><?php echo $lang->phrase('admin_member_2month'); ?></option>
	<option value="M_3"><?php echo $lang->phrase('admin_member_3month'); ?></option>
	<option value="M_4"><?php echo $lang->phrase('admin_member_4month'); ?></option>
	<option value="M_5"><?php echo $lang->phrase('admin_member_5month'); ?></option>
	<option value="M_6"><?php echo $lang->phrase('admin_member_6month'); ?></option>
	<option value="M_12"><?php echo $lang->phrase('admin_member_1year'); ?></option>
	<option value="M_24"><?php echo $lang->phrase('admin_member_2year'); ?></option>
   	</select>
   	</td>
  </tr>
  <tr>
   <td class="mbox" width="40%"><?php echo $lang->phrase('admin_member_reason_to_show'); ?></td>
   <td class="mbox" width="60%"><input type="text" name="reason" size="60" /></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_member_add'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'ban_add2') {
	echo head();

	$data = $gpc->get('data', none);
	$type = strtolower($gpc->get('type', none));
	$until = $gpc->get('until', none);
	$reason = $gpc->get('reason', none);

	$error = array();
	if ($type == 'ip') {
		if (!preg_match("/[0-9]{1,3}\.[0-9]{1,3}(\.[0-9]{0,3})?(\.[0-9]{0,3})?/", $data)) {
			$error[] = $lang->phrase('admin_member_ip_not_correct');
		}
	}
	elseif ($type == 'user') {
		$data = $gpc->save_str($data);
		$result = $db->query("SELECT id FROM {$db->pre}user WHERE name = '{$data}' LIMIT 1");
		if ($db->num_rows($result) == 0) {
			$error[] = $lang->phrase('admin_member_no_user_found');
		}
		else {
			$user = $db->fetch_assoc($result);
			if ($user['id'] == $my->id) {
				$error[] = $lang->phrase('admin_member_cannot_ban_yourself');
			}
			else {
				$data = $user['id'];
			}
		}
	}
	else {
		$error[] = $lang->phrase('admin_member_data_type_not_correct');
	}
	if (!(is_numeric($until) && intval($until) === 0)) { // WTF? $until != 0 won't work?!
		$until = explode('_', $until);
		$until[0] = strtoupper($until[0]);
		if (($until[0] != 'D' && $until[0] != 'M') || !isset($until[1])) {
			$error[] = $lang->phrase('admin_member_time_not_valid');
		}
	}

	$banned = file('data/bannedip.php');
	$file = array();
	foreach ($banned as $line) {
		$row = rtrim($line, "\r\n");
		$file[] = $row;
		$row = explode("\t", $row, 6);
		// Check if there is a ban that is currently(!) active
		// If there are expired bans, don't print an error
		if ($row[0] == $type && strcasecmp($row[1], $data) == 0 && $row[2] > time()) {
			$error[] = $lang->phrase('admin_member_user_or_ip_already_banned');
		}
	}

	if (count($error) > 0) {
		error('admin.php?action=members&job=ban_add', $error);
	}

	if ($until[0] == 'D') {
		$until = strtotime('+'.$until[1].' day'.iif($until[1] > 0, 's'));
	}
	elseif ($until[0] == 'M') {
		$until = strtotime('+'.$until[1].' month'.iif($until[1] > 0, 's'));
	}

	$new = array(
		$type,
		$data,
		$until,
		$my->id,
		time(),
		str_replace(array("\r", "\n", "\t"), ' ', $reason)
	);
	$file[] = implode("\t", $new);

	$filesystem->file_put_contents('data/bannedip.php', implode("\n", $file) );

	ok('admin.php?action=members&job=banned', iif($type == 'ip', $lang->phrase('admin_member_banned_ip_addr'), $lang->phrase('admin_member_banned_user')).$lang->phrase('admin_member_has_been_banned'));
}
elseif ($job == 'ban_delete') {
	echo head();
	$delete = $gpc->get('delete', arr_none);
	if (array_empty($delete) == true) {
		error('admin.php?action=members&job=banned', $lang->phrase('admin_member_nothing_selected'));
	}
	$banned = file('data/bannedip.php');
	$file = array();
	foreach ($banned as $line) {
		$add = true;
		$row = explode("\t", rtrim($line, "\r\n"), 6);
		foreach ($delete as $del) {
			$del = explode("#", $del, 3);
			if ($del[0] == $row[0] && $del[1] == $row[1] && $del[2] == $row[4]) {
				$add = false;
			}
		}
		if ($add == true && empty($line) == false) {
			$file[] = $line;
		}
	}
	$filesystem->file_put_contents('data/bannedip.php', implode("\n", $file) );
	ok('admin.php?action=members&job=banned', $lang->phrase('admin_member_ips_saved'));
}
elseif ($job == 'inactive') {
	echo head();
	$year =  time()-60*60*24*365;
	$two_month =  time()-60*60*24*30*2;
	?>
<form name="form" method="post" action="admin.php?action=members&amp;job=inactive2">
 <table class="border">
  <tr>
   <td class="obox" colspan="3">
	<span class="right">
	  <a class="button" href="admin.php?action=members&amp;job=search"><?php echo $lang->phrase('admin_member_search_members'); ?></a>
	</span>
   <?php echo $lang->phrase('admin_member_inactive_members'); ?></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_posts'); ?></td>
   <td class="mbox" align="center">&lt;=</td>
   <td class="mbox"><input type="text" name="posts" size="3" value="0" /></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_date_of_registry'); ?></td>
   <td class="mbox" align="center">&lt;</td>
   <td class="mbox">
   <input type="text" name="regdate[1]" size="3" value="" />.
   <input type="text" name="regdate[2]" size="3" value="" />.
   <input type="text" name="regdate[3]" size="5" value="" /> (<?php echo $lang->phrase('admin_member_date_numeric'); ?>)
   </td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_last_visit'); ?></td>
   <td class="mbox" align="center">&lt;</td>
   <td class="mbox">
   <input type="text" name="lastvisit[1]" size="3" value="<?php echo gmdate('d', times($two_month)); ?>" />.
   <input type="text" name="lastvisit[2]" size="3" value="<?php echo gmdate('m', times($two_month)); ?>" />.
   <input type="text" name="lastvisit[3]" size="5" value="<?php echo gmdate('Y', times($two_month)); ?>" /> (<?php echo $lang->phrase('admin_member_date_numeric'); ?>)
   </td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_status'); ?></td>
   <td class="mbox" align="center">=</td>
   <td class="mbox"><select size="1" name="confirm">
	  <option selected="selected" value=""><?php echo $lang->phrase('admin_member_whatever'); ?></option>
	  <option value="11"><?php echo $lang->phrase('admin_member_activated'); ?></option>
	  <option value="10"><?php echo $lang->phrase('admin_member_must_activate_via_mail'); ?></option>
	  <option value="01"><?php echo $lang->phrase('admin_member_must_be_activated_by_admin'); ?></option>
	  <option value="00"><?php echo $lang->phrase('admin_member_has_not_been_activated'); ?></option>
	</select></td>
  </tr>
  <tr>
   <td class="ubox" align="center" colspan="4"><input type="submit" value="<?php echo $lang->phrase('admin_member_submit'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'inactive2') {
	echo head();

	define('DONT_CARE', md5(microtime()));

	$fields = 	array(
		'name' => array($lang->phrase('admin_member_user_name'), str, null),
		'mail' => array($lang->phrase('admin_member_email'), str, null),
		'posts' => array($lang->phrase('admin_member_posts'), int, '<='),
		'regdate' => array($lang->phrase('admin_member_registration'), arr_int, '<'),
		'lastvisit' => array($lang->phrase('admin_member_last_visit'), arr_int, '<'),
		'confirm' => array($lang->phrase('admin_member_status'), none, '=')
	);
	$keys = array_keys($fields);


	$sqlwhere = array();
	$input = array();
	foreach ($fields as $key => $data) {
		$value = $gpc->get($key, $data[1], DONT_CARE);
		if ($key == 'regdate' || $key == 'lastvisit') {
			if (is_array($value) && array_sum($value) != 0) { // for php version >= 5.1.0
				$input[$key] =  @mktime(0, 0, 0, intval($value[2]), intval($value[1]), intval($value[3]));
				if ($input[$key] == -1 || $input[$key] == false) { // -1 for php version < 5.1.0, false for php version >= 5.1.0
					$input[$key] = DONT_CARE;
				}
			}
			else {
				$input[$key] = DONT_CARE;
			}
		}
		else {
			if (!isset($_REQUEST[$key])) {
				$_REQUEST[$key] = null;
			}
			if (empty($_REQUEST[$key]) && $_REQUEST[$key] != '0') {
				$input[$key] = DONT_CARE;
			}
			else {
				$input[$key] = $value;
			}
		}

		if ($input[$key] != DONT_CARE) {
			$sqlwhere[] = " `{$key}` {$fields[$key][2]} '{$input[$key]}' ";
		}
	}

	if (count($sqlwhere) > 0) {
		$query = 'SELECT id, '.implode(',', $keys).' FROM '.$db->pre.'user WHERE '.implode(' AND ', $sqlwhere).' ORDER BY name';
		$result = $db->query($query);
		$count = $db->num_rows($result);
	}
	else {
		$count = 0;
	}
	?>
	<form name="form" action="admin.php?action=members" method="post">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		<tr>
		  <td class="obox" colspan="9">
		<span style="float: right;">
		  <a class="button" href="admin.php?action=members&amp;job=search"><?php echo $lang->phrase('admin_member_search_members'); ?></a>
		</span>
		  <?php echo $lang->phrase('admin_member_search_inactive'); ?>
		  </td>
		</tr>
		<?php if ($count == 0) { ?>
		<tr>
		  <td class="mbox" colspan="9"><?php echo $lang->phrase('admin_member_no_inactive_found'); ?></td>
		</tr>
		<?php } else { ?>
			<tr>
			  <td class="ubox" colspan="9"><?php echo $count; ?> <?php echo $lang->phrase('admin_member_inactive_found'); ?></td>
			</tr>
			<tr>
			  <td class="obox center"><?php echo $lang->phrase('admin_member_select'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /> <?php echo $lang->phrase('admin_member_all'); ?></span></td>
			  <td class="obox center"><?php echo $lang->phrase('admin_member_edit'); ?></td>
			  <?php foreach ($keys as $key) { ?>
			  <td class="obox"><?php echo $fields[$key][0]; ?></td>
			  <?php } ?>
			</tr>
			<?php
			while ($row = $gpc->prepare($db->fetch_assoc($result))) {
				if (empty($row['lastvisit'])) {
					$row['lastvisit'] = $lang->phrase('admin_member_never');
				}
				else {
					$row['lastvisit'] = gmdate('d.m.Y H:i', times($row['lastvisit']));
				}
				if (isset($row['regdate'])) {
					$row['regdate'] = gmdate('d.m.Y', times($row['regdate']));
				}
				if (isset($row['confirm'])) {
				  	if ($row['confirm'] == "11") { $row['confirm'] = $lang->phrase('admin_member_activated'); }
				  	elseif ($row['confirm'] == "10") { $row['confirm'] = $lang->phrase('admin_member_must_activate_via_mail'); }
				  	elseif ($row['confirm'] == "01") { $row['confirm'] = $lang->phrase('admin_member_must_be_activated_by_admin'); }
				  	elseif ($row['confirm'] == "00") { $row['confirm'] = $lang->phrase('admin_member_has_not_been_activated'); }
				}
			?>
			<tr>
			  <td class="mbox center"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
			  <td class="mbox center"><a class="button" href="admin.php?action=members&amp;job=edit&amp;id=<?php echo $row['id']; ?>"><?php echo $lang->phrase('admin_member_edit'); ?></a></td>
			  <?php foreach ($keys as $key) { ?>
			  <td class="mbox"><?php echo $row[$key]; ?></td>
			  <?php } ?>
			</tr>
			<?php } ?>
			<tr>
			  <td class="ubox" colspan="9">
			  	<select name="job">
			  		<option value="delete"><?php echo $lang->phrase('admin_member_delete'); ?></option>
			  		<option value="emaillist"><?php echo $lang->phrase('admin_member_export_mail_addresses'); ?></option>
			  		<option value="newsletter"><?php echo $lang->phrase('admin_member_send_nl'); ?></option>
			  	</select> <input type="submit" name="submit" value="<?php echo $lang->phrase('admin_member_go'); ?>">
			  </td>
			</tr>
		<?php } ?>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'search') {
	echo head();

	$loaddesign_obj = $scache->load('loaddesign');
	$design = $loaddesign_obj->get();

	$loadlanguage_obj = $scache->load('loadlanguage');
	$language = $loadlanguage_obj->get();

	$result = $db->query("SELECT id, title, name FROM {$db->pre}groups WHERE guest = '0' ORDER BY admin DESC , guest ASC , core ASC");
	?>
<form name="form" method="post" action="admin.php?action=members&job=search2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="4">
	<span style="float: right;">
	  <a class="button" href="admin.php?action=members&amp;job=inactive"><?php echo $lang->phrase('admin_member_inactive_members'); ?></a>
	</span>
   <?php echo $lang->phrase('admin_member_search_for_members'); ?></td>
  </tr>
  <tr>
	<td class="mbox" width="50%" colspan="4">
	<b><?php echo $lang->phrase('admin_member_help'); ?></b>
	<ul>
	<li><?php echo $lang->phrase('admin_member_wildcard_description'); ?></li>
	<li>
	<?php echo $lang->phrase('admin_member_means_equal'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<?php echo $lang->phrase('admin_member_means_less'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<?php echo $lang->phrase('admin_member_means_greater'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<?php echo $lang->phrase('admin_member_means_not_equal'); ?>
	</li>
	</ul>
	</td>
  </tr>
  <tr>
   <td class="mbox" colspan="2"><?php echo $lang->phrase('admin_member_exactness'); ?></td>
   <td class="mbox" colspan="2">
   <input type="radio" name="type" value="0"> <b><?php echo $lang->phrase('admin_member_at_least_one_match'); ?></b> (<?php echo $lang->phrase('admin_member_at_least_one_match_desc'); ?>)<br>
   <input type="radio" name="type" value="1" checked="checked"> <b><?php echo $lang->phrase('admin_member_whole_match'); ?></b> (<?php echo $lang->phrase('admin_member_whole_match_desc'); ?>)
   </td>
  </tr>
  <tr>
   <td class="ubox" width="30%">&nbsp;</td>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_member_relational_operator'); ?></td>
   <td class="ubox" width="60%">&nbsp;</td>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_member_show'); ?></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_id'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[id]">
	  <option value="-1">&lt;</option>
	  <option value="0" selected="selected">=</option>
	  <option value="1">&gt;</option>
	</select></td>
   <td class="mbox"><input type="text" name="id" size="12"></td>
   <td class="mbox"><input type="checkbox" name="show[id]" value="1" checked></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_nickname'); ?></td>
   <td class="mbox" align="center">=</td>
   <td class="mbox"><input type="text" name="name" size="50"></td>
   <td class="mbox"><input type="checkbox" name="show[name]" value="1" checked></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_mail_address'); ?></td>
   <td class="mbox" align="center">=</td>
   <td class="mbox"><input type="text" name="mail" size="50"></td>
   <td class="mbox"><input type="checkbox" name="show[mail]" value="1" checked></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_date_of_registry'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[regdate]">
	  <option value="-1">&lt;</option>
	  <option value="0" selected="selected">=</option>
	  <option value="1">&gt;</option>
	</select></td>
   <td class="mbox"><input type="text" name="regdate[1]" size="3">. <input type="text" name="regdate[2]" size="3">. <input type="text" name="regdate[3]" size="5"> (<?php echo $lang->phrase('admin_member_date_numeric'); ?>)</td>
   <td class="mbox"><input type="checkbox" name="show[regdate]" value="1" checked></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_posts'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[posts]">
	  <option value="-1">&lt;</option>
	  <option value="0" selected="selected">=</option>
	  <option value="1">&gt;</option>
	</select></td>
   <td class="mbox"><input type="text" name="posts" size="10"></td>
   <td class="mbox"><input type="checkbox" name="show[posts]" value="1"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_civil_name'); ?></td>
   <td class="mbox" align="center">=</td>
   <td class="mbox"><input type="text" name="fullname" size="50"></td>
   <td class="mbox"><input type="checkbox" name="show[fullname]" value="1" checked></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_homepage'); ?></td>
   <td class="mbox" align="center">=</td>
   <td class="mbox"><input type="text" name="hp" size="50"></td>
   <td class="mbox"><input type="checkbox" name="show[hp]" value="1"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_residence'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[location]">
	  <option value="0" selected="selected">=</option>
	  <option value="2">!=</option>
	</select></td>
   <td class="mbox"><input type="text" name="location" size="50"></td>
   <td class="mbox"><input type="checkbox" name="show[location]" value="1"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_gender'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[gender]">
	  <option value="0" selected="selected">=</option>
	  <option value="2">!=</option>
	</select></td>
   <td class="mbox"><select name="gender" size="1">
   <option selected="selected" value=""><?php echo $lang->phrase('admin_member_whatever'); ?></option>
   <option value="x"><?php echo $lang->phrase('admin_member_not_specified'); ?></option>
   <option value="m"><?php echo $lang->phrase('admin_member_male'); ?></option>
   <option value="w"><?php echo $lang->phrase('admin_member_female'); ?></option>
   </select></td>
   <td class="mbox"><input type="checkbox" name="show[gender]" value="1"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_birthday'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[birthday]">
	  <option value="-1">&lt;</option>
	  <option value="0" selected="selected">=</option>
	  <option value="1">&gt;</option>
	</select></td>
   <td class="mbox"><input type="text" name="birthday[1]" size="3">. <input type="text" name="birthday[2]" size="3">. <input type="text" name="birthday[3]" size="5"> (<?php echo $lang->phrase('admin_member_date_numeric'); ?>)</td>
   <td class="mbox"><input type="checkbox" name="show[birthday]" value="1"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_last_visit'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[lastvisit]">
	  <option value="-1">&lt;</option>
	  <option value="0" selected="selected">=</option>
	  <option value="1">&gt;</option>
	</select></td>
   <td class="mbox"><input type="text" name="lastvisit[1]" size="3">. <input type="text" name="lastvisit[2]" size="3">. <input type="text" name="lastvisit[3]" size="5"> (<?php echo $lang->phrase('admin_member_date_numeric'); ?>)</td>
   <td class="mbox"><input type="checkbox" name="show[lastvisit]" value="1" checked></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_icq_uin'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[icq]">
	  <option value="-1">&lt;</option>
	  <option value="0" selected="selected">=</option>
	  <option value="1">&gt;</option>
	</select></td>
   <td class="mbox"><input type="text" name="icq" size="12"></td>
   <td class="mbox"><input type="checkbox" name="show[icq]" value="1"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_yahoo_id'); ?></td>
   <td class="mbox" align="center">=</td>
   <td class="mbox"><input type="text" name="yahoo" size="50"></td>
   <td class="mbox"><input type="checkbox" name="show[yahoo]" value="1"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_aol_name'); ?></td>
   <td class="mbox" align="center">=</td>
   <td class="mbox"><input type="text" name="aol" size="50"></td>
   <td class="mbox"><input type="checkbox" name="show[aol]" value="1"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_msn_address'); ?></td>
   <td class="mbox" align="center">=</td>
   <td class="mbox"><input type="text" name="msn" size="50"></td>
   <td class="mbox"><input type="checkbox" name="show[msn]" value="1"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_jabber_address'); ?></td>
   <td class="mbox" align="center">=</td>
   <td class="mbox"><input type="text" name="jabber" size="50"></td>
   <td class="mbox"><input type="checkbox" name="show[jabber]" value="1"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_skype_name'); ?></td>
   <td class="mbox" align="center">=</td>
   <td class="mbox"><input type="text" name="skype" size="50"></td>
   <td class="mbox"><input type="checkbox" name="show[skype]" value="1"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_time_zone'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[timezone]">
	  <option value="0" selected="selected">=</option>
	  <option value="2">!=</option>
	</select></td>
   <td class="mbox"><select name="timezone">
	<option selected="selected" value=""><?php echo $lang->phrase('admin_member_whatever'); ?></option>
	<option value="-12"><?php echo $lang->phrase('timezone_n12'); ?></option>
	<option value="-11"><?php echo $lang->phrase('timezone_n11'); ?></option>
	<option value="-10"><?php echo $lang->phrase('timezone_n10'); ?></option>
	<option value="-9"><?php echo $lang->phrase('timezone_n9'); ?></option>
	<option value="-8"><?php echo $lang->phrase('timezone_n8'); ?></option>
	<option value="-7"><?php echo $lang->phrase('timezone_n7'); ?></option>
	<option value="-6"><?php echo $lang->phrase('timezone_n6'); ?></option>
	<option value="-5"><?php echo $lang->phrase('timezone_n5'); ?></option>
	<option value="-4"><?php echo $lang->phrase('timezone_n4'); ?></option>
	<option value="-3.5"><?php echo $lang->phrase('timezone_n35'); ?></option>
	<option value="-3"><?php echo $lang->phrase('timezone_n3'); ?></option>
	<option value="-2"><?php echo $lang->phrase('timezone_n2'); ?></option>
	<option value="-1"><?php echo $lang->phrase('timezone_n1'); ?></option>
	<option value="0"><?php echo $lang->phrase('timezone_0'); ?></option>
	<option value="+1"><?php echo $lang->phrase('timezone_p1'); ?></option>
	<option value="+2"><?php echo $lang->phrase('timezone_p2'); ?></option>
	<option value="+3"><?php echo $lang->phrase('timezone_p3'); ?></option>
	<option value="+3.5"><?php echo $lang->phrase('timezone_p35'); ?></option>
	<option value="+4"><?php echo $lang->phrase('timezone_p4'); ?></option>
	<option value="+4.5"><?php echo $lang->phrase('timezone_p45'); ?></option>
	<option value="+5"><?php echo $lang->phrase('timezone_p5'); ?></option>
	<option value="+5.5"><?php echo $lang->phrase('timezone_p55'); ?></option>
	<option value="+5.75"><?php echo $lang->phrase('timezone_p575'); ?></option>
	<option value="+6"><?php echo $lang->phrase('timezone_p6'); ?></option>
	<option value="+6.5"><?php echo $lang->phrase('timezone_p65'); ?></option>
	<option value="+7"><?php echo $lang->phrase('timezone_p7'); ?></option>
	<option value="+8"><?php echo $lang->phrase('timezone_p8'); ?></option>
	<option value="+9"><?php echo $lang->phrase('timezone_p9'); ?></option>
	<option value="+9.5"><?php echo $lang->phrase('timezone_p95'); ?></option>
	<option value="+10"><?php echo $lang->phrase('timezone_p10'); ?></option>
	<option value="+11"><?php echo $lang->phrase('timezone_p11'); ?></option>
	<option value="+12"><?php echo $lang->phrase('timezone_p12'); ?></option>
</select>	</td>
   <td class="mbox"><input type="checkbox" name="show[timezone]" value="1"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_groups'); ?></td>
   <td class="mbox" align="center">=</td>
   <td class="mbox">
	<select size="3" name="groups[]" multiple="multiple">
	  <option selected="selected" value=""><?php echo $lang->phrase('admin_member_whatever'); ?></option>
	  <?php while ($row = $gpc->prepare($db->fetch_assoc($result))) { ?>
		<option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
	  <?php } ?>
	</select>
	<select name="groups_op" size="2" style="margin: 0.5em 0 0.5em 0;">
	  <option value="0" selected="selected" title="<?php echo $lang->phrase('admin_member_at_least_one_match_desc'); ?>"><?php echo $lang->phrase('admin_member_at_least_one_match'); ?></option>
   	  <option value="1" title="<?php echo $lang->phrase('admin_member_whole_match_desc'); ?>"><?php echo $lang->phrase('admin_member_whole_match'); ?></option>
	</select>
	</td>
   <td class="mbox"><input type="checkbox" name="show[groups]" value="1"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_design'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[template]">
	  <option value="0" selected="selected">=</option>
	  <option value="2">!=</option>
	</select></td>
   <td class="mbox"><select name="template">
	<option selected="selected" value=""><?php echo $lang->phrase('admin_member_whatever'); ?></option>
	<?php foreach ($design as $row) { ?>
	<option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
	<?php } ?>
</select></td>
   <td class="mbox"><input type="checkbox" name="show[template]" value="1"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_lang'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[language]">
	  <option value="0" selected="selected">=</option>
	  <option value="2">!=</option>
	</select></td>
   <td class="mbox"><select name="language">
	<option selected="selected" value=""><?php echo $lang->phrase('admin_member_whatever'); ?></option>
	<?php foreach ($language as $row) { ?>
	<option value="<?php echo $row['id']; ?>"><?php echo $row['language']; ?></option>
	<?php } ?>
</select></td>
   <td class="mbox"><input type="checkbox" name="show[language]" value="1"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_member_cmp_status'); ?></td>
   <td class="mbox" align="center"><select size="1" name="compare[confirm]">
	  <option value="0" selected="selected">=</option>
	  <option value="2">!=</option>
	</select></td>
   <td class="mbox"><select size="1" name="confirm">
	  <option selected="selected" value=""><?php echo $lang->phrase('admin_member_whatever'); ?></option>
	  <option value="11"><?php echo $lang->phrase('admin_member_activated'); ?></option>
	  <option value="10"><?php echo $lang->phrase('admin_member_must_activate_via_mail'); ?></option>
	  <option value="01"><?php echo $lang->phrase('admin_member_must_activate_by_admin'); ?></option>
	  <option value="00"><?php echo $lang->phrase('admin_member_has_not_been_activated'); ?></option>
	</select></td>
   <td class="mbox"><input type="checkbox" name="show[confirm]" value="1"></td>
  </tr>
  <tr>
   <td class="ubox" align="center" colspan="4"><input type="submit" value="<?php echo $lang->phrase('admin_member_submit'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'search2') {
	echo head();

	define('DONT_CARE', md5(microtime()));
	$fields = 	array(
		'id' => array('ID', int),
		'name' => array($lang->phrase('admin_member_user_name'), str),
		'mail' => array($lang->phrase('admin_member_email'), str),
		'regdate' => array($lang->phrase('admin_member_registration'), arr_int),
		'posts' => array($lang->phrase('admin_member_posts'), int),
		'fullname' => array($lang->phrase('admin_member_civil_name'), str),
		'hp' => array($lang->phrase('admin_member_homepage'), str),
		'location' => array($lang->phrase('admin_member_residence'), str),
		'gender' => array($lang->phrase('admin_member_gender'), str),
		'birthday' => array($lang->phrase('admin_member_birthday'), arr_none),
		'lastvisit' => array($lang->phrase('admin_member_last_visit'), arr_int),
		'icq' => array($lang->phrase('admin_member_icq'), int),
		'yahoo' => array($lang->phrase('admin_member_yahoo'), str),
		'aol' => array($lang->phrase('admin_member_aol'), str),
		'msn' => array($lang->phrase('admin_member_msn'), str),
		'skype' => array($lang->phrase('admin_member_skype'), str),
		'jabber' => array($lang->phrase('admin_member_jabber'), str),
		'timezone' => array($lang->phrase('admin_member_time_zone'), db_esc),
		'groups' => array($lang->phrase('admin_member_groups'), arr_int),
		'template' => array($lang->phrase('admin_member_design'), int),
		'language' => array($lang->phrase('admin_member_lang'), int),
		'confirm' => array($lang->phrase('admin_member_status'), none)
	);
	$change = array('m' => $lang->phrase('admin_member_male'), 'w' => $lang->phrase('admin_member_female'), '' => '-');

	$loaddesign_obj = $scache->load('loaddesign');
	$design = $loaddesign_obj->get();

	$loadlanguage_obj = $scache->load('loadlanguage');
	$language = $loadlanguage_obj->get();

	$type = $gpc->get('type', int);
	$sep = ($type == 0) ? ' OR ' : ' AND ';

	$compare = $gpc->get('compare', arr_int);
	foreach ($compare as $key => $cmp) {
		if ($cmp == -1) {
			$compare[$key] = '<';
		}
		elseif ($cmp == 1) {
			$compare[$key] = '>';
		}
		elseif ($cmp == 2) {
			$compare[$key] = '!=';
		}
		else {
			$compare[$key] = '=';
		}
	}
	$show = $gpc->get('show', arr_none);
	$show = array_keys($show);
	$show = array_intersect($show, array_keys($fields));
	$sqlkeys = array_unique(array_intersect(array_merge($show, array('id', 'name')), array_keys($fields)));
	$sqlwhere = array();
	$input = array();
	foreach ($fields as $key => $data) {
		$value = $gpc->get($key, $data[1], DONT_CARE);
		if ($key == 'regdate' || $key == 'lastvisit') {
			if (is_array($value) && array_sum($value) != 0) { // for php version >= 5.1.0
				$input[$key] =  @mktime(0, 0, 0, intval($value[2]), intval($value[1]), intval($value[3]));
				if ($input[$key] == -1 || $input[$key] == false) { // -1 for php version < 5.1.0, false for php version >= 5.1.0
					$input[$key] = DONT_CARE;
				}
			}
			else {
				$input[$key] = DONT_CARE;
			}
		}
		elseif ($key == 'groups') {
			if (array_empty($value) !== false) {
				$input[$key] = DONT_CARE;
			}
			else {
				$input[$key] = $value;
			}
		}
		elseif ($key == 'birthday') {
			$value[1] = intval(trim($value[1]));
			if ($value[1] < 1 || $value[1] > 31) {
				$value[1] = '%';
			}
			$value[2] = intval(trim($value[2]));
			if ($value[2] < 1 || $value[2] > 12) {
				$value[2] = '%';
			}
			if (strlen($value[3]) == 2) {
				if ($value[3] > 40) {
					$value[3] += 1900;
				}
				else {
					$value[3] += 2000;
				}
			}
			else {
				$value[3] = intval(trim($value[3]));
			}
			if ($value[3] < 1900 || $value[3] > 2100) {
				$value[3] = '%';
			}
			if ($value[1] == '%' && $value[2] == '%' && $value[3] == '%') {
				$input[$key] = DONT_CARE;
			}
			else {
				$input[$key] = $value[3].'-'.$value[2].'-'.$value[1];
			}
		}
		elseif ($key == 'gender') {
			if (empty($value)) {
				$input[$key] = DONT_CARE;
			}
			elseif ($value == 'x') {
				$input[$key] = '';
			}
			else {
				$input[$key] = $value;
			}
		}
		else {
			if (empty($value)) {
				$input[$key] = DONT_CARE;
			}
			else {
				$input[$key] = $value;
			}
		}

		if (!isset($compare[$key])) {
			$compare[$key] = '=';
		}

		if ($input[$key] != DONT_CARE) {
			if ($key == 'groups') {
				$gcmp = $gpc->get('groups_op', int);
				$gsep = ($gcmp == 0) ? ' OR ' : ' AND ';
				$groupwhere = array();
				foreach ($input[$key] as $gid) {
					$groupwhere[] = " FIND_IN_SET('{$gid}', {$key}) ";
				}
				$groupwhere = implode($gsep, $groupwhere);
				$sqlwhere[] = " ({$groupwhere}) ";
			}
			else {
				if (strpos($input[$key], '%') !== false || strpos($input[$key], '_') !== false) {
					if ($compare[$key] == '=') {
						$compare[$key] = 'LIKE';
					}
					elseif ($compare[$key] == '!=') {
						$compare[$key] = 'NOT LIKE';
					}
				}
				$sqlwhere[] = " `{$key}` {$compare[$key]} '{$input[$key]}' ";
			}
		}
	}

	$colspan = count($show) + 1;

	if (count($sqlwhere) > 0) {
		$query = 'SELECT '.implode(',',$sqlkeys).' FROM '.$db->pre.'user WHERE '.implode($sep, $sqlwhere).' ORDER BY name';
		$result = $db->query($query);
		$count = $db->num_rows($result);
	}
	else {
		$count = 0;
	}
	?>
	<form name="form" action="admin.php?action=members" method="post">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		<tr>
		  <td class="obox" colspan="<?php echo $colspan; ?>"><b><?php echo $lang->phrase('admin_member_search_for_members'); ?></b></td>
		</tr>
		<?php if ($count == 0) { ?>
		<tr>
		  <td class="mbox" colspan="<?php echo $colspan; ?>"><?php echo $lang->phrase('admin_member_no_member_found'); ?></td>
		</tr>
		<?php } else { ?>
			<tr>
			  <td class="ubox" colspan="<?php echo $colspan; ?>"><?php echo $count; ?> <?php echo $lang->phrase('admin_member_members_found'); ?></td>
			</tr>
			<tr>
			  <td class="obox"><?php echo $lang->phrase('admin_member_select'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /> <?php echo $lang->phrase('admin_member_all'); ?></span></td>
			  <?php foreach ($show as $key) { ?>
			  <td class="obox"><?php echo $fields[$key][0]; ?></td>
			  <?php } ?>
			</tr>
			<?php
			while ($row = $gpc->prepare($db->fetch_assoc($result))) {
				if (isset($row['lastvisit'])) {
					$row['lastvisit'] = gmdate('d.m.Y H:i', times($row['lastvisit']));
				}
				if (isset($row['regdate'])) {
					$row['regdate'] = gmdate('d.m.Y', times($row['regdate']));
				}
				if (empty($row['icq'])) {
					$row['icq'] = '-';
				}
				if (!isset($row['timezone']) || $row['timezone'] === '') {
					$row['timezone'] = $config['timezone'];
				}
				$row['timezone'] = (int) str_replace('+', '', $row['timezone']);
				if (isset($row['gender'])) {
					$row['gender'] = $change[$row['gender']];
				}
				if (!isset($row['birthday']) || intval($row['birthday']) == 0) {
					$row['birthday'] = '-';
				}
				else {
					$bd = explode('-', $row['birthday']);
					$bd = array_reverse($bd);
					if ($bd[2] <= 1000) {
						$bd[2] = 0;
						$row['birthday'] = "{$bd[0]}.{$bd[1]}.";
					}
					else {
						$row['birthday'] = implode('.', $bd);
					}
				}
				if (isset($row['template']) && isset($design[$row['template']])) {
					$row['template'] = $design[$row['template']]['name'];
				}
				if (isset($row['language']) && isset($language[$row['language']])) {
					$row['language'] = $language[$row['language']]['language'];
				}
				if (isset($row['confirm'])) {
				  	if ($row['confirm'] == "11") { $row['confirm'] = $lang->phrase('admin_member_activated'); }
				  	elseif ($row['confirm'] == "10") { $row['confirm'] = $lang->phrase('admin_member_must_activate_via_mail'); }
				  	elseif ($row['confirm'] == "01") { $row['confirm'] = $lang->phrase('admin_member_must_be_activated_by_admin'); }
				  	elseif ($row['confirm'] == "00") { $row['confirm'] = $lang->phrase('admin_member_not_activated'); }
				}
			?>
			<tr>
			  <td class="mbox"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
			  <?php foreach ($show as $key) { ?>
			  <td class="mbox"><a href="admin.php?action=members&job=edit&id=<?php echo $row['id']; ?>"><?php echo $row[$key]; ?></a></td>
			  <?php } ?>
			</tr>
			<?php } ?>
			<tr>
			  <td class="ubox" colspan="<?php echo $colspan; ?>">
			  	<select name="job">
			  		<option value="delete"><?php echo $lang->phrase('admin_member_delete'); ?></option>
			  		<option value="emaillist"><?php echo $lang->phrase('admin_member_export_mail_addresses'); ?></option>
			  		<option value="newsletter"><?php echo $lang->phrase('admin_member_send_nl'); ?></option>
			  	</select> <input type="submit" name="submit" value="<?php echo $lang->phrase('admin_member_go'); ?>">
			  </td>
			</tr>
		<?php } ?>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'disallow') {
	echo head();
	$delete = $gpc->get('delete', arr_int);
	if (count($delete) > 0) {
		$did = implode(',', $delete);
		$db->query("DELETE FROM {$db->pre}user WHERE id IN ({$did}) AND confirm != '11'");
		$anz = $db->affected_rows();
		$db->query("DELETE FROM {$db->pre}userfields WHERE ufid IN ({$did})");

		$cache = $scache->load('memberdata');
		$cache = $cache->delete();

		ok('admin.php?action=members&job=activate', $lang->phrase('admin_member_members_deleted'));
	}
	else {
		error('admin.php?action=members&job=activate', $lang->phrase('admin_member_no_valid_input'));
	}
}
elseif ($job == 'activate') {
	echo head();

	$result = $db->query('SELECT * FROM '.$db->pre.'user WHERE confirm != "11" ORDER BY regdate DESC');
	?>
	<form name="form" action="admin.php?action=members&job=disallow" method="post">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		<tr>
		  <td class="obox" colspan="4"><?php echo $lang->phrase('admin_member_moderate'); ?> &amp; <?php echo $lang->phrase('admin_member_unlock_members'); ?></td>
		</tr>
		<tr>
		  <td class="ubox" width="30%"><?php echo $lang->phrase('admin_member_username'); ?></td>
		  <td class="ubox" width="10%"><?php echo $lang->phrase('admin_member_mail'); ?></td>
		  <td class="ubox" width="15%"><?php echo $lang->phrase('admin_member_registered'); ?></td>
		  <td class="ubox" width="45%"><?php echo $lang->phrase('admin_member_status'); ?> (<input type="checkbox" onchange="check_all(this)" value="delete[]" /> <?php echo $lang->phrase('admin_member_all'); ?>`)</td>
		</tr>
	<?php
	while ($row = $gpc->prepare($db->fetch_object($result))) {
		$row->regdate = gmdate('d.m.Y', times($row->regdate));
		if ($row->lastvisit == 0) {
			$row->lastvisit = $lang->phrase('admin_member_never');
		}
		else {
			$row->lastvisit = gmdate('d.m.Y', times($row->lastvisit));
		}
		?>
		<tr>
		  <td class="mbox"><a title="<?php echo $lang->phrase('admin_member_edit'); ?>" href="admin.php?action=members&job=edit&id=<?php echo $row->id; ?>"><?php echo $row->name; ?></a></td>
		  <td class="mbox" align="center"><a href="mailto:<?php echo $row->mail; ?>"><?php echo $lang->phrase('admin_member_mail'); ?></a></td>
		  <td class="mbox"><?php echo $row->regdate; ?></td>
		  <td class="mbox"><ul>
		  <?php if ($row->confirm == '00' || $row->confirm == '01') { ?>
		  <li><strong><a href="admin.php?action=members&job=confirm&id=<?php echo $row->id; ?>"><?php echo $lang->phrase('admin_member_confirm_user'); ?></a></strong></li>
		  <?php } if ($row->confirm == '00' || $row->confirm == '10') { ?>
		  <li><?php echo $lang->phrase('admin_member_must_activate_via_mail'); ?> [<a href="admin.php?action=members&job=confirm2&id=<?php echo $row->id; ?>"><?php echo $lang->phrase('admin_member_activate_user_completely'); ?></a>]</li>
		  <?php } ?>
		  <li><?php echo $lang->phrase('admin_member_delete_user'); ?> <input type="checkbox" name="delete[]" value="<?php echo $row->id; ?>"></li>
		  </ul></td>
		</tr>
		<?php
	}
	?>
		<tr>
		  <td class="ubox" colspan="4" align="center"><input type="submit" name="submit" value="<?php echo $lang->phrase('admin_member_delete_selected_user'); ?>"></td>
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

	ok('admin.php?action=members&job=activate', $lang->phrase('admin_member_member_confirmed'));
}
elseif ($job == 'confirm2') {
	echo head();

	$id = $gpc->get('id', int);
	$result = $db->query('SELECT id, name, mail FROM '.$db->pre.'user WHERE id = "'.$id.'" LIMIT 1');
	$row = $db->fetch_assoc($result);

	$db->query("UPDATE {$db->pre}user SET confirm = '11' WHERE id = '{$id}' LIMIT 1");

	$content = $lang->get_mail('admin_confirmed');
	xmail(array('0' => array('mail' => $row['mail'])), array(), $content['title'], $content['comment']);

	ok('admin.php?action=members&job=activate', $lang->phrase('admin_member_member_activated_completely'));
}
elseif ($job == 'ips') {
	$username = $gpc->get('username', str);
	$ipaddress = $gpc->get('ipaddress', str);
	$userid = $gpc->get('id', int);

	echo head();
	if (!empty($username)) {
		$result = $db->query("SELECT id, name FROM {$db->pre}user WHERE name = '{$username}' LIMIT 1");
		$userinfo = $db->fetch_assoc($result);
		$userid = $userinfo['id'];
		if (!is_id($userid)) {
			error('admin.php?action=members&job=ip', $lang->phrase('admin_member_invalid_user'));
		}
	}

	if (!empty($ipaddress) || $userid > 0) {
		if (!empty($ipaddress)) {
			if (check_ip($ipaddress)) {
				$hostname = @gethostbyaddr($ipaddress);
			}
			if (empty($hostname) || $hostname == $ipaddress) {
				$hostname = $lang->phrase('admin_member_could_not_resolve_hostname');
			}
			$users = $db->query("SELECT DISTINCT u.id, u.name, r.ip FROM {$db->pre}replies AS r, {$db->pre}user AS u  WHERE u.id = r.name AND r.ip LIKE '{$ipaddress}%' AND r.ip != '' ORDER BY u.name");
			?>
			<table align="center" class="border">
			<tr>
				<td class="obox"><?php echo $lang->phrase('admin_member_ip_search'); ?></td>
			</tr>
			<tr>
				<td class="ubox">
				<a href="http://ripe.net/fcgi-bin/whois?searchtext=<?php echo $ipaddress; ?>" target="_blank" title="<?php echo $lang->phrase('admin_member_visit_ripe'); ?>"><?php echo $ipaddress; ?></a>: <b><?php echo htmlspecialchars($hostname); ?></b>
				</td>
			</tr>
			<tr>
				<td class="mbox">
				<ul>
				<?php while ($user = $db->fetch_assoc($users)) { ?>
					<li style="padding: 3px;">
					<a href="admin.php?action=members&amp;job=edit&amp;id=<?php echo $user['id']; ?>"><b><?php echo $user['name']; ?></b></a> &nbsp;&nbsp;&nbsp;
					<a href="admin.php?action=members&amp;job=iphost&amp;ip=<?php echo $user['ip']; ?>" title="<?php echo $lang->phrase('admin_member_resolve_address'); ?>"><?php echo $user['ip']; ?></a> &nbsp;&nbsp;&nbsp;
					<a class="button" href="admin.php?action=members&amp;job=ips&amp;id=<?php echo $user['id']; ?>&amp;username=<?php echo urlencode($user['name']); ?>"><?php echo $lang->phrase('admin_member_view_other_ips'); ?></a>
					</li>
					<?php
				}
				if ($db->num_rows($users) == 0) {
					?>
					<li><?php echo $lang->phrase('admin_member_no_matches'); ?></li>
					<?php
				}
				?>
				</ul>
				</td>
			</tr>
			</table>
			<br />
		<?php } if ($userid > 0) { ?>
			<table align="center" class="border">
			<tr>
				<td class="obox"><?php echo $lang->phrase('admin_member_search_ips_for_user'); ?> &quot;<?php echo $userinfo['name']; ?>&quot;</td>
			</tr>
			<tr>
				<td class="mbox">
				<ul>
				<?php
				$ips = $db->query("SELECT DISTINCT ip FROM {$db->pre}replies WHERE name = '{$userid}' AND ip != '{$ipaddress}' AND ip != '' ORDER BY ip");
				while ($ip = $db->fetch_assoc($ips)) {
					?>
					<li style="padding: 3px;">
					<a href="admin.php?action=members&job=iphost&amp;ip=<?php echo $ip['ip']; ?>" title="<?php echo $lang->phrase('admin_member_resolve_address'); ?>"><?php echo $ip['ip']; ?></a> &nbsp;&nbsp;&nbsp;
					<a class="button" href="admin.php?action=members&amp;job=ips&amp;ipaddress=<?php echo $ip['ip']; ?>"><?php echo $lang->phrase('admin_member_find_more_with_this_ip'); ?></a>
					</li>
					<?php
				}
				if ($db->num_rows($ips) == 0) {
					?>
					<li><?php echo $lang->phrase('admin_member_no_matches'); ?></li>
					<?php
				}
				?>
				</ul>
				</td>
			</tr>
			</table>
			<br />
			<?php
		}
	}
	?>
	<form action="admin.php?action=members&amp;job=ips" method="post">
	<table align="center" class="border">
	<tr>
		<td class="obox" colspan="2"><?php echo $lang->phrase('admin_member_search_ips'); ?></td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_member_find_by_ip'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_member_enter_partial_ip_address'); ?></span></td>
		<td class="mbox"><input type="text" name="ipaddress" value="<?php echo $ipaddress; ?>" size="35" /></td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_member_find_ip_user_match'); ?></td>
		<td class="mbox"><input type="text" name="username" value="<?php echo $username; ?>" size="35" /></td>
	</tr>
	<tr>
		<td class="ubox" colspan="2" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_member_find'); ?>" /></td>
	</tr>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'iphost') {
	$ip = $gpc->get('ip', str);
	if (check_ip($ip)) {
		$resolvedip = @gethostbyaddr($ip);
	}
	if (empty($resolvedip) || $resolvedip == $ip) {
		$host = '<i>'.$lang->phrase('admin_member_iphost_na').'</i>';
	}
	else {
		$host = htmlspecialchars($resolvedip);
	}
	echo head();
	?>
	<table align="center" class="border">
	<tr>
		<td class="obox" colspan="2"><?php echo $lang->phrase('admin_member_resolve_ip'); ?></td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_member_ip_address'); ?></td>
		<td class="mbox"><a href="http://ripe.net/fcgi-bin/whois?searchtext=<?php echo $ip; ?>" target="_blank"><?php echo $ip; ?></a></td>
	</tr>
	<tr>
		<td class="mbox"><?php echo $lang->phrase('admin_member_host_name'); ?></td>
		<td class="mbox"><?php echo $host; ?></td>
	</tr>
	</table>
	<?php
	echo foot();
}
?>