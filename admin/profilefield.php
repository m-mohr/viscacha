<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// MM: MultiLangAdmin
$lang->group("admin/profilefield");

$editable = array(
	'0' => $lang->phrase('admin_editable_hidden'),
	'1' => $lang->phrase('admin_editable_change_user_data'),
	'2' => $lang->phrase('admin_editable_change_settings')
);

$viewable = array(
	'0' => $lang->phrase('admin_viewable_hidden'),
	'1' => $lang->phrase('admin_viewable_personal_information'),
	'2' => $lang->phrase('admin_viewable_forum_information'),
	'3' => $lang->phrase('admin_viewable_contact_information')
);

($code = $plugins->load('admin_profilefield_jobs')) ? eval($code) : null;

if($job == "add2") {
	$type = $gpc->get('type', none);
	$options = $gpc->get('options', none);
	if($type != "text" && $type != "textarea") {
		$thing = "$type\n$options";
	}
	else {
		$thing = $type;
	}
	$insert = array(
		"name" => $gpc->get('name', str),
		"description" => $gpc->get('description', str),
		"disporder" => $gpc->get('disporder', int),
		"type" => $gpc->save_str($thing),
		"length" => $gpc->get('length', int),
		"maxlength" => $gpc->get('maxlength', int),
		"required" => $gpc->get('required', int),
		"editable" => $gpc->get('editable', int),
		"viewable" => $gpc->get('viewable', int)
	);
	$db->query('INSERT INTO '.$db->pre.'profilefields SET '.array2sqlsetlist($insert));
	$fid = $db->insert_id();
	$db->query("ALTER TABLE ".$db->pre."userfields ADD fid{$fid} TEXT NOT NULL");
	$db->query("OPTIMIZE TABLE ".$db->pre."userfields");
	echo head();
	ok("admin.php?action=profilefield&job=add", $lang->phrase('admin_profilefield_successfully_added'));
}
elseif($job == "delete2") {
	$fid = $gpc->get('fid', int);
	$db->query("DELETE FROM ".$db->pre."profilefields WHERE fid = '{$fid}' LIMIt 1");
	$db->query("ALTER TABLE ".$db->pre."userfields DROP fid{$fid}");
	$db->query("OPTIMIZE TABLE ".$db->pre."userfields");
	echo head();
	ok("admin.php?action=profilefield&job=manage", $lang->phrase('admin_profilefield_successfully_deleted'));
}
elseif($job == "edit2") {
	$fid = $gpc->get('fid', int);
	$type = $gpc->get('type', none);
	$options = $gpc->get('options', none);
	if($type != "text" && $type != "textarea") {
		$thing = "$type\n$options";
	}
	else {
		$thing = $type;
	}
	$update = array(
		"name" => $gpc->get('name', str),
		"description" => $gpc->get('description', str),
		"disporder" => $gpc->get('disporder', int),
		"type" => $gpc->save_str($thing),
		"length" => $gpc->get('length', int),
		"maxlength" => $gpc->get('maxlength', int),
		"required" => $gpc->get('required', int),
		"editable" => $gpc->get('editable', int),
		"viewable" => $gpc->get('viewable', int)
	);
	$db->query('UPDATE '.$db->pre.'profilefields SET '.array2sqlsetlist($update).' WHERE fid="'.$fid.'" LIMIT 1');
	echo head();
	ok("admin.php?action=profilefield&job=manage", $lang->phrase('admin_profilefield_successfully_updated'));
}
elseif($job == "add") {
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=profilefield&job=add2">
	<table class="border">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_add_new_custom_profilefield'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_name'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="name" size="50" value="" /></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_description'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_profilefield_description_info'); ?></span></td>
	   <td class="mbox" width="50%"><textarea name="description" rows="3" cols="80"></textarea></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_maximum_length'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_profilefield_maximum_length_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="maxlength" size="50" value="" /></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_field_length'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_profilefield_field_length_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="length" size="50" value="" /></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_display_order'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="disporder" size="10" value="" /></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_field_type'); ?></td>
	   <td class="mbox" width="50%">
		  <select name="type">
			<option value="text"><?php echo $lang->phrase('admin_field_type_textbox'); ?></option>
			<option value="textarea"><?php echo $lang->phrase('admin_field_type_textarea'); ?></option>
			<option value="select"><?php echo $lang->phrase('admin_field_type_select'); ?></option>
			<option value="multiselect"><?php echo $lang->phrase('admin_field_type_select_multiple'); ?></option>
			<option value="radio"><?php echo $lang->phrase('admin_field_type_radio'); ?></option>
			<option value="checkbox"><?php echo $lang->phrase('admin_field_type_checkbox'); ?></option>
		  </select>
		</td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_selectable_options'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_profilefield_selectable_options_info'); ?><span></td>
	   <td class="mbox" width="50%"><textarea name="options" rows="5" cols="50"></textarea></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_required'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_profilefield_required_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="required" value="1" /></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_editable_pos'); ?></td>
	   <td class="mbox" width="50%">
		  <select name="editable">
		  	<?php foreach ($editable as $id => $title) { ?>
			<option value="<?php echo $id; ?>"><?php echo $title; ?></option>
			<?php } ?>
		  </select>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_visible_pos'); ?></td>
	   <td class="mbox" width="50%">
		  <select name="viewable">
		  	<?php foreach ($viewable as $id => $title) { ?>
			<option value="<?php echo $id; ?>"><?php echo $title; ?></option>
			<?php } ?>
		  </select>
	   </td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_save_form'); ?>"></td>
	  </tr>
	</table>
	<?php
	echo foot();
}
elseif($job == "delete") {
	$fid = $gpc->get('fid', int);
	$query = $db->query("SELECT * FROM ".$db->pre."profilefields WHERE fid='{$fid}'");
	$profilefield = $db->fetch_assoc($query);
	echo head();
	?>
	<table class="border" border="0" cellspacing="0" cellpadding="4">
	<tr><td class="obox"><?php echo $lang->phrase('admin_confirm_delete_head'); ?></td></tr>
	<tr><td class="mbox">
	<p align="center"><?php echo $lang->phrase('admin_confirm_delete_text'); ?></p>
	<p align="center">
	<a href="admin.php?action=profilefield&job=delete2&fid=<?php echo $fid; ?>"><img border="0" alt="<?php echo $lang->phrase('admin_yes'); ?>" src="admin/html/images/yes.gif"> <?php echo $lang->phrase('admin_yes'); ?></a>
	&nbsp&nbsp;&nbsp;&nbsp&nbsp;&nbsp;<a href="javascript: history.back(-1);"><img border="0" alt="<?php echo $lang->phrase('admin_no'); ?>" src="admin/html/images/no.gif"> <?php echo $lang->phrase('admin_no'); ?></a>
	</p>
	</td></tr>
	</table>
	<?php
	echo foot();
}
elseif($job == "edit") {
	$fid = $gpc->get('fid', int);
	$query = $db->query("SELECT * FROM ".$db->pre."profilefields WHERE fid = '{$fid}' LIMIT 1");
	$profilefield = $gpc->prepare($db->fetch_assoc($query));

	$type = explode("\n", $profilefield['type'], 2);
	if (!isset($type[1])) {
		$type[1] = '';
	}

	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=profilefield&job=edit2&fid=<?php echo $profilefield['fid']; ?>">
	<table class="border">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_edit_custom_profilefield'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_name'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="name" size="50" value="<?php echo $profilefield['name']; ?>" /></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_description'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_profilefield_description_info'); ?></span></td>
	   <td class="mbox" width="50%"><textarea name="description" rows="3" cols="80"><?php echo $profilefield['description']; ?></textarea></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_maximum_length'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_profilefield_maximum_length_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="maxlength" size="50" value="<?php echo $profilefield['maxlength']; ?>" /></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_field_length'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_profilefield_field_length_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="length" size="50" value="<?php echo $profilefield['length']; ?>" /></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_display_order'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="disporder" size="10" value="<?php echo $profilefield['disporder']; ?>" /></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_field_type'); ?></td>
	   <td class="mbox" width="50%">
		  <select name="type">
			<option value="text"<?php echo iif($type[0] == 'text', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_field_type_textbox'); ?></option>
			<option value="textarea"<?php echo iif($type[0] == 'textarea', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_field_type_textarea'); ?></option>
			<option value="select"<?php echo iif($type[0] == 'select', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_field_type_select'); ?></option>
			<option value="multiselect"<?php echo iif($type[0] == 'multiselect', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_field_type_select_multiple'); ?></option>
			<option value="radio"<?php echo iif($type[0] == 'radio', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_field_type_radio'); ?></option>
			<option value="checkbox"<?php echo iif($type[0] == 'checkbox', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_field_type_checkbox'); ?></option>
		  </select>
		</td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_selectable_options'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_profilefield_selectable_options_info'); ?><span></td>
	   <td class="mbox" width="50%"><textarea name="options" rows="5" cols="50"><?php echo $type[1]; ?></textarea></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_required'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_profilefield_required_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="required" value="1"<?php echo iif($profilefield['required'] == 1, ' checked="checked"'); ?> /></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_editable_pos'); ?></td>
	   <td class="mbox" width="50%">
		  <select name="editable">
		  	<?php foreach ($editable as $id => $title) { ?>
			<option<?php echo iif($id == $profilefield['editable'], ' selected="selected"'); ?> value="<?php echo $id; ?>"><?php echo $title; ?></option>
			<?php } ?>
		  </select>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_profilefield_visible_pos'); ?></td>
	   <td class="mbox" width="50%">
		  <select name="viewable">
		  	<?php foreach ($viewable as $id => $title) { ?>
			<option<?php echo iif($id == $profilefield['viewable'], ' selected="selected"'); ?> value="<?php echo $id; ?>"><?php echo $title; ?></option>
			<?php } ?>
		  </select>
	   </td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_save_form'); ?>"></td>
	  </tr>
	</table>
	<?php
	echo foot();
}
elseif ($job == "manage") {
	echo head();
	?>
	<table class="border">
	  <tr>
	   <td class="obox" colspan="6">
	    <span style="float: right;"><a class="button" href="admin.php?action=profilefield&amp;job=add"><?php echo $lang->phrase('admin_add_new_profilefield'); ?></a></span>
		<?php echo $lang->phrase('admin_profilefield_manager'); ?>
		</td>
	  </tr>
	  <tr class="ubox">
		<td><?php echo $lang->phrase('admin_head_name'); ?></td>
		<td><?php echo $lang->phrase('admin_head_id'); ?></td>
		<td><?php echo $lang->phrase('admin_head_required'); ?></td>
		<td><?php echo $lang->phrase('admin_head_editable'); ?></td>
		<td><?php echo $lang->phrase('admin_head_visible'); ?></td>
		<td><?php echo $lang->phrase('admin_head_action'); ?></td>
	  </tr>
	<?php
	$query = $db->query("SELECT * FROM ".$db->pre."profilefields ORDER BY disporder");
	while($profilefield = $db->fetch_assoc($query)) {
		$profilefield['required'] = ($profilefield['required'] == 1) ? $lang->phrase('admin_yes') : $lang->phrase('admin_no');
		$profilefield['editable'] = (isset($editable[$profilefield['editable']])) ? $editable[$profilefield['editable']] : $lang->phrase('admin_editable_fallback');
		$profilefield['viewable'] = (isset($viewable[$profilefield['viewable']])) ? $viewable[$profilefield['viewable']] : $lang->phrase('admin_viewable_fallback');
		?>
		<form name="form" method="get" action="admin.php">
		<input type="hidden" value="profilefield" name="action" />
		<input type="hidden" value="<?php echo $profilefield['fid']; ?>" name="fid" />
		<tr class="mbox">
		<td><?php echo $profilefield['name']; ?></td>
		<td align="center"><?php echo $profilefield['fid']; ?></td>
		<td align="center"><?php echo $profilefield['required']; ?></td>
		<td align="center"><?php echo $profilefield['editable']; ?></td>
		<td align="center"><?php echo $profilefield['viewable']; ?></td>
		<td align="right">
			<select name="job">
				<option value="edit"><?php echo $lang->phrase('admin_action_edit'); ?></option>
				<option value="delete"><?php echo $lang->phrase('admin_action_delete'); ?></option>
			</select>&nbsp;<input type="submit" value="<?php echo $lang->phrase('admin_go_form'); ?>">
		</td>
		</tr>
		</form>
		<?php
	}
	echo "</table>";
	echo foot();
}
?>
