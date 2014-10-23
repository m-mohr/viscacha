<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "profilefield.php") die('Error: Hacking Attempt');

$editable = array(
	'0' => 'Hidden',
	'1' => 'Change User Data',
	'2' => 'Change Settings'
);

$viewable = array(
	'0' => 'Hidden',
	'1' => 'Personal information',
	'2' => 'Forum information',
	'3' => 'Contact information'
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
	ok("admin.php?action=profilefield&job=add", "The profile field has successfully been added.");
}
elseif($job == "delete2") {
	$fid = $gpc->get('fid', int);
	$db->query("DELETE FROM ".$db->pre."profilefields WHERE fid = '{$fid}' LIMIt 1");
	$db->query("ALTER TABLE ".$db->pre."userfields DROP fid{$fid}");
	$db->query("OPTIMIZE TABLE ".$db->pre."userfields");
	echo head();
	ok("admin.php?action=profilefield&job=manage", 'The profile field has successfully been deleted.');
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
	ok("admin.php?action=profilefield&job=manage", "The profile field has successfully been updated.");
}
elseif($job == "add") {
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=profilefield&job=add2">
	<table class="border">
	  <tr> 
	   <td class="obox" colspan="2">Add new Custom Profile Field</td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Name:</td>
	   <td class="mbox" width="50%"><input type="text" name="name" size="50" value="" /></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Beschreibung:<br /><span class="stext">Please enter a small description for this field, you should explain if the field is required or hidden.</span></td>
	   <td class="mbox" width="50%"><textarea name="description" rows="3" cols="80"></textarea></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximum Length:<br /><span class="stext">This only applies to textboxes/areas.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="maxlength" size="50" value="" /></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Field Length:<br /><span class="stext">The length of the field, this only applies to single and multiple option select boxes.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="length" size="50" value="" /></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Display Order:</td>
	   <td class="mbox" width="50%"><input type="text" name="disporder" size="10" value="" /></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Field Type:</td>
	   <td class="mbox" width="50%">
		  <select name="type">
			<option value="text">Textbox</option>
			<option value="textarea">Textarea</option>
			<option value="select">Select Box</option>
			<option value="multiselect">Multiple Option Selection Box</option>
			<option value="radio">Radio Buttons</option>
			<option value="checkbox">Check Boxes</option>
		  </select>
		</td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Selectable Options:<br /><span class="stext">Please enter each option on its own line. The internal key (first) and the shown label (second) has to be separated with an "=". This only applies to the selectboxes, checkboxes, and radio box setting types.<span></td>
	   <td class="mbox" width="50%"><textarea name="options" rows="5" cols="50"></textarea></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Required?<br /><span class="stext">Require the field to be filled in during registration or profile editing?<br />Does not apply if 'hidden' is selected below.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="required" value="1" /></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Where shall it be editable?</td>
	   <td class="mbox" width="50%">
		  <select name="editable">
		  	<?php foreach ($editable as $id => $title) { ?>
			<option value="<?php echo $id; ?>"><?php echo $title; ?></option>
			<?php } ?>
		  </select>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Where shall it be visible?</td>
	   <td class="mbox" width="50%">
		  <select name="viewable">
		  	<?php foreach ($viewable as $id => $title) { ?>
			<option value="<?php echo $id; ?>"><?php echo $title; ?></option>
			<?php } ?>
		  </select>
	   </td> 
	  </tr>
	  <tr> 
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Save"></td> 
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
	<tr><td class="obox">Löschen bestätigen</td></tr>
	<tr><td class="mbox">
	<p align="center">Are you sure you want to delete the selected custom profile field?</p>
	<p align="center">
	<a href="admin.php?action=profilefield&job=delete2&fid=<?php echo $fid; ?>"><img border="0" align="middle" alt="Yes" src="admin/html/images/yes.gif"> Yes</a>
	&nbsp&nbsp;&nbsp;&nbsp&nbsp;&nbsp;<a href="javascript: history.back(-1);"><img border="0" align="middle" alt="No" src="admin/html/images/no.gif"> No</a>
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
	   <td class="obox" colspan="2">Edit Custom Profile Field &raquo; <?php echo $profilefield['name']; ?></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Name:</td>
	   <td class="mbox" width="50%"><input type="text" name="name" size="50" value="<?php echo $profilefield['name']; ?>" /></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Beschreibung:<br /><span class="stext">Please enter a small description for this field, you should explain if the field is required or hidden.</span></td>
	   <td class="mbox" width="50%"><textarea name="description" rows="3" cols="80"><?php echo $profilefield['description']; ?></textarea></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximum Length:<br /><span class="stext">This only applies to textboxes/areas.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="maxlength" size="50" value="<?php echo $profilefield['maxlength']; ?>" /></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Field Length:<br /><span class="stext">The length of the field, this only applies to single and multiple option select boxes.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="length" size="50" value="<?php echo $profilefield['length']; ?>" /></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Display Order:</td>
	   <td class="mbox" width="50%"><input type="text" name="disporder" size="10" value="<?php echo $profilefield['disporder']; ?>" /></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Field Type:</td>
	   <td class="mbox" width="50%">
		  <select name="type">
			<option value="text"<?php echo iif($type[0] == 'text', ' selected="selected"'); ?>>Textbox</option>
			<option value="textarea"<?php echo iif($type[0] == 'textarea', ' selected="selected"'); ?>>Textarea</option>
			<option value="select"<?php echo iif($type[0] == 'select', ' selected="selected"'); ?>>Select Box</option>
			<option value="multiselect"<?php echo iif($type[0] == 'multiselect', ' selected="selected"'); ?>>Multiple Option Selection Box</option>
			<option value="radio"<?php echo iif($type[0] == 'radio', ' selected="selected"'); ?>>Radio Buttons</option>
			<option value="checkbox"<?php echo iif($type[0] == 'checkbox', ' selected="selected"'); ?>>Check Boxes</option>
		  </select>
		</td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Selectable Options:<br /><span class="stext">Please enter each option on its own line. The internal key (first) and the shown label (second) has to be separated with an "=". This only applies to the selectboxes, checkboxes, and radio box setting types.<span></td>
	   <td class="mbox" width="50%"><textarea name="options" rows="5" cols="50"><?php echo $type[1]; ?></textarea></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Required?<br /><span class="stext">Require the field to be filled in during registration or profile editing?<br />Does not apply if 'hidden' is selected below.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="required" value="1"<?php echo iif($profilefield['required'] == 1, ' checked="checked"'); ?> /></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Where shall it be editable?</td>
	   <td class="mbox" width="50%">
		  <select name="editable">
		  	<?php foreach ($editable as $id => $title) { ?>
			<option<?php echo iif($id == $profilefield['editable'], ' selected="selected"'); ?> value="<?php echo $id; ?>"><?php echo $title; ?></option>
			<?php } ?>
		  </select>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Where shall it be visible?</td>
	   <td class="mbox" width="50%">
		  <select name="viewable">
		  	<?php foreach ($viewable as $id => $title) { ?>
			<option<?php echo iif($id == $profilefield['viewable'], ' selected="selected"'); ?> value="<?php echo $id; ?>"><?php echo $title; ?></option>
			<?php } ?>
		  </select>
	   </td> 
	  </tr>
	  <tr> 
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Save"></td> 
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
	    <span style="float: right;"><a class="button" href="admin.php?action=profilefield&amp;job=add">Add new Profile Field</a></span>
		Custom Profile Field Manager
		</td>
	  </tr>
	  <tr class="ubox">
		<td>Name</td>
		<td>ID</td>
		<td>Required</td>
		<td>Editable</td>
		<td>Visible</td>
		<td>Action</td>
	  </tr>
	<?php
	$query = $db->query("SELECT * FROM ".$db->pre."profilefields ORDER BY disporder");
	while($profilefield = $db->fetch_assoc($query)) {
		$profilefield['required'] = ($profilefield['required'] == 1) ? 'Yes' : 'No';
		$profilefield['editable'] = (isset($editable[$profilefield['editable']])) ? $editable[$profilefield['editable']] : '-';
		$profilefield['viewable'] = (isset($viewable[$profilefield['viewable']])) ? $viewable[$profilefield['viewable']] : '-';
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
				<option value="edit">Edit</option>
				<option value="delete">Delete</option>
			</select>&nbsp;<input type="submit" value="Go">
		</td>
		</tr>
		</form>
		<?php
	}
	echo "</table>";
	echo foot();
}
?>
