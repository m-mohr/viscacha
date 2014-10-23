<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// FS: MultiLangAdmin
$lang->group("admin/groups");

($code = $plugins->load('admin_groups_jobs')) ? eval($code) : null;

if ($job == 'manage') {
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}groups ORDER BY admin DESC , guest ASC , core ASC");
	$cache = array();
	$delete = 0;
	while ($row = $db->fetch_assoc($result)) {
		if ($row['core'] == 0) {
			$delete = 1;
		}
		$cache[] = $row;
	}
	$colspan = count($glk)+$delete;
	?>
<form name="form" method="post" action="admin.php?action=groups&job=delete">
 <table class="border">
  <tr>
   <td class="obox" colspan="<?php echo $colspan+4; ?>">
	<span style="float: right;"><a class="button" href="admin.php?action=groups&job=add"><?php echo $lang->phrase('admin_groups_add_new_usergroup'); ?></a></span>
	<?php echo $lang->phrase('admin_groups_usergroup_manager'); ?>
  </td>
  </tr>
  <tr class="ubox">
  	<?php if ($delete == 1) { ?><td valign="bottom"><b><?php echo $lang->phrase('admin_groups_delete'); ?></b></td><?php } ?>
  	<td valign="bottom"><b><?php echo $lang->phrase('admin_groups_edit'); ?></b></td>
	<td valign="bottom"><b><?php echo $lang->phrase('admin_groups_name'); ?><br /><?php echo $lang->phrase('admin_groups_public_title_head'); ?></b></td>
	<td valign="bottom"><b><?php echo $lang->phrase('admin_groups_id'); ?></b></td>
	<?php foreach ($gls as $txt) { ?>
   	<td valign="bottom"><img src="images.php?action=textimage&amp;text=<?php echo rawurlencode($txt); ?>&amp;angle=90&amp;bg=<?php echo $txt2img_bg; ?>&amp;fg=<?php echo $txt2img_fg; ?>" border="0"></td>
	<?php } ?>
   	<td valign="bottom"><img src="images.php?action=textimage&amp;text=<?php echo rawurlencode($lang->phrase('admin_groups_floodcheck_img')); ?>&amp;angle=90&amp;bg=<?php echo $txt2img_bg; ?>&amp;fg=<?php echo $txt2img_fg; ?>" border="0"></td>
  </tr>
  <?php
  foreach ($cache as $row) {
  	$guest = ($row['guest'] == 1 && $row['core'] == 1);
  ?>
  <tr class="mbox">
  <?php if ($delete == 1) { ?>
  	<td>
  	<?php if ($row['core'] == 0) { ?>
  		<input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>">
  	<?php } else { ?>
  		<font class="stext"><?php echo $lang->phrase('admin_groups_core'); ?></font>
  	<?php } ?>
  	</td>
  <?php } ?>
	<td><input type="radio" name="edit" value="<?php echo $row['id']; ?>"></td>
	<td nowrap="nowrap"><?php echo $row['name']; ?><br /><?php echo $row['title']; ?></td>
	<td><?php echo $row['id']; ?></td>
	<?php
	foreach ($glk as $txt) {
	   	$clickable = !($guest && in_array($txt, $guest_limitation));
	   	if ($txt == 'guest') {
	   		$clickable = false;
	   	}
	   	$js = iif ($clickable,
	   			' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=groups&job=ajax_changeperm&id='.$row['id'].'&key='.$txt.'\')"',
	   			' style="-moz-opacity: 0.4; opacity: 0.4; filter:Alpha(opacity=40, finishopacity=0);"'
	   		  );
	   	echo '<td align="center"'.iif(!$clickable, ' class="mmbox"').'>';
	   	echo noki($row[$txt], $js);
	   	echo '</td>';
	}
	?>
   	<td><?php echo $row['flood']; ?></td>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="<?php echo $colspan+4; ?>" align="center">
   <?php if ($delete == 1) { ?><input type="submit" name="submit_delete" value="<?php echo $lang->phrase('admin_groups_form_delete'); ?>"><?php } ?>
   <input type="submit" name="submit_edit" value="<?php echo $lang->phrase('admin_groups_form_edit'); ?>">
   </td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'ajax_changeperm') {
	$id = $gpc->get('id', int);
	$key = $gpc->get('key', str);
	if(!is_id($id) || !isset($gls[$key])) {
		die($lang->phrase('admin_groups_id_or_key_invalid'));
	}
	$result = $db->query("SELECT g.{$key}, g.core FROM {$db->pre}groups AS g WHERE id = '{$id}' LIMIT 1");
	$perm = $db->fetch_assoc($result);
	if (($key == 'admin' || $key == 'guest') && $perm['core'] == 1) {
		die($lang->phrase('not_allowed'));
	}
	$perm = invert($perm[$key]);
	$db->query("UPDATE {$db->pre}groups AS g SET g.{$key} = '{$perm}' WHERE id = '{$id}' LIMIT 1");
	$delobj = $scache->load('groups');
	$delobj->delete();
	die(strval($perm));
}
elseif ($job == 'add') {
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}groups ORDER BY admin DESC , guest ASC , core ASC");
	$cache = array();
	while ($row = $db->fetch_assoc($result)) {
		$cache[] = $row;
	}
	?>
<form name="form" method="post" action="admin.php?action=groups&job=add2">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_groups_add_a_new_usergroup_settings_and_permissions'); ?></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_groups_copy_permissions_of_another_group'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_groups_group'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_groups_group_permissions_warning'); ?></span></td>
   <td class="mbox" width="50%">
   <select name="copy">
   <option value="0"><?php echo $lang->phrase('admin_groups_set_data_by_hand'); ?></option>
   <?php foreach ($cache as $row) { ?>
   <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
   <?php } ?>
   </select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_groups_also_copy_forum_rights'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_groups_copy_forum_rights_description'); ?></span></td>
   <td class="mbox" width="50%"><input type="checkbox" name="copyf" value="1" /></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_groups_set_settings_of_this_group_manually'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_groups_insert_values_from_another_group'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_groups_insert_values_from_another_group_description'); ?></span></td>
   <td class="mbox" width="50%">
<script language="JavaScript" type="text/javascript">
<!--
var v = new Array();
<?php foreach ($cache as $row) { ?>
v['<?php echo $row['id']; ?>'] = new Array();
v['<?php echo $row['id']; ?>']['flood'] = <?php echo $row['flood']; ?>;
<?php foreach ($glk as $key) { if ($key != 'guest') { ?>
v['<?php echo $row['id']; ?>']['<?php echo $key; ?>'] = <?php echo $row[$key]; ?>;
<?php } } } ?>
function setGroupBoxes(sel) {
	id = sel.value;
	f = FetchElement('flood');
	if (id == 0) { f.value = 20; }
	else { f.value = v[id]['flood']; }
<?php foreach ($glk as $key) { if ($key != 'guest') { ?>
	f = FetchElement('<?php echo $key; ?>');
   	if (id == 0) { f.checked = 0; }
   	else { f.checked = v[id]['<?php echo $key; ?>']; }
<?php } } ?>
}
-->
</script>
   <select name="template" onchange="setGroupBoxes(this);">
   <option value="0"><?php echo $lang->phrase('admin_groups_set_no_checkbox'); ?></option>
   <?php foreach ($cache as $row) { ?>
   <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
   <?php } ?>
   </select>
   </td>
  </tr>
  <?php
  foreach ($glk as $key) {
  if ($key != 'guest') {
  ?>
  <tr>
   <td class="mbox" width="50%"><?php echo $gls[$key]; ?><br /><span class="stext"><?php echo $gll[$key]; ?></span></td>
   <td class="mbox" width="50%"><input type="checkbox" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="1" /></td>
  </tr>
  <?php } } ?>
  <tr>
	<td class="mbox" width="50%"><?php echo $lang->phrase('admin_groups_floodcheck_in_sec'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_groups_floodcheck_description'); ?></span></td>
   	<td class="mbox" width="50%"><input type="text" name="flood" id="flood" size="3" value="20"></td>
  </tr>
 </table>
 <br />
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_groups_add_a_new_usergroup_settings'); ?></td>
  </tr>
  <tr>
	  <td class="mbox" width="50%"><?php echo $lang->phrase('admin_groups_internal_name_for_the_group'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_groups_internal_name_for_the_group_description'); ?></span></td>
	  <td class="mbox" width="50%"><input type="text" name="name" size="35"></td>
  </tr><tr>
	  <td class="mbox" width="50%"><?php echo $lang->phrase('admin_groups_public_title'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_groups_public_title_for_users_in_the_forum'); ?></span></td>
	  <td class="mbox" width="50%"><input type="text" name="title" size="35"></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="hidden" name="guest" value="0"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_groups_add'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'add2') {
	echo head();
	$copy = $gpc->get('copy', int);

	$sql_values = '';
	if ($copy == 0) {
		foreach ($glk as $key) {
			$sql_values .= '"'.$gpc->get($key, int).'",';
		}
	}
	else {
		$result = $db->query("SELECT ".implode(', ', $glk)." FROM {$db->pre}groups WHERE id = '{$copy}' LIMIT 1");
		$row = $db->fetch_assoc($result);
		foreach ($glk as $key) {
			$sql_values .= '"'.$row[$key].'",';
		}
	}

	$db->query('INSERT INTO '.$db->pre.'groups ('.implode(',', $glk).',flood,title,name) VALUES ('.$sql_values.'"'.$gpc->get('flood', int).'","'.$gpc->get('title', str).'","'.$gpc->get('name', str).'")');
	$gid = $db->insert_id();

	$copyf = $gpc->get('copyf', int);
	if ($copy > 0 && $copyf == 1) {
		$fields = array('f_downloadfiles', 'f_forum', 'f_posttopics', 'f_postreplies', 'f_addvotes', 'f_attachments', 'f_edit', 'f_voting');
		$result = $db->query("SELECT * FROM {$db->pre}fgroups WHERE gid = '{$gid}'");
		$fgnum = $db->num_rows($result);
		$fgnum2 = 0;
		while ($row = $db->fetch_assoc($result)) {
			$sql_fvalues = '';
			foreach ($glk as $key) {
				$sql_fvalues .= '"'.$gpc->get($key, int).'",';
			}
			$db->query("INSERT INTO {$db->pre}fgroups (gid,".implode(',', $fields).",bid) VALUES ('{$gid}',{$sql_fvalues},'{$row['bid']}')");
			$fgnum2 += $db->affected_rows();
		}
	}

	$delobj = $scache->load('groups');
	$delobj->delete();
	$delobj = $scache->load('fgroups');
	$delobj->delete();
	if ($gid > 0) {
		if (isset($fgnum) && isset($fgnum2) && $fgnum != $fgnum2) {
			ok('admin.php?action=groups&job=manage', $lang->phrase('admin_groups_group_add_successful_with_permission_copy_error'));
		}
		else {
			ok('admin.php?action=groups&job=manage', $lang->phrase('admin_groups_group_add_successful'));
		}
	}
	else {
		error('admin.php?action=groups&job=add', $lang->phrase('admin_groups_the_group_couldnt_be_added'));
	}
}
elseif ($job == 'delete') {
	$del = $gpc->get('delete', arr_int);
	$edit = $gpc->get('edit', int);
	if (isset($_POST['submit_delete']) && count($del) > 0) {
		$db->query("DELETE FROM {$db->pre}groups WHERE id IN (".implode(',',$del).")");
		$anz = $db->affected_rows();
		$db->query("DELETE FROM {$db->pre}fgroups WHERE gid IN (".implode(',',$del).")");
		$delobj = $scache->load('groups');
		$delobj->delete();
		$delobj = $scache->load('fgroups');
		$delobj->delete();
		echo head();
		ok('admin.php?action=groups&job=manage', $lang->phrase('admin_groups_x_groups_deleted'));
	}
	elseif (isset($_POST['submit_edit']) && $edit > 0) {
		sendStatusCode(307, $config['furl'].'/admin.php?action=groups&job=edit&id='.$edit);
	}
	else {
		sendStatusCode(307, $config['furl'].'/admin.php?action=groups&job=manage');
	}
}
elseif ($job == 'edit') {
	$id = $gpc->get('id', int);
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}groups WHERE id = '{$id}' LIMIT 1");
	if ($db->num_rows($result) != 1) {
		error('admin.php?action=groups&job=manage', $lang->phrase('admin_groups_no_valid_id_given'));
	}
	$data = $db->fetch_assoc($result);
	?>
<form name="form" method="post" action="admin.php?action=groups&amp;job=edit2&amp;id=<?php echo $id; ?>">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_groups_edit_an_usergroup_settings_and_permissions'); ?></td>
  </tr>
  <?php
  foreach ($glk as $key) {
  	$result = array_search($key, $guest_limitation);
  	$editable = !(($data['guest'] == 1 && $data['core'] == 1) && $result !== false);
  	if ($key != 'guest' && $editable) {
  ?>
  <tr>
   <td class="mbox" width="50%"><?php echo $gls[$key]; ?><br /><span class="stext"><?php echo $gll[$key]; ?></span></td>
   <td class="mbox" width="50%"><input type="checkbox" name="<?php echo $key; ?>" value="1"<?php echo iif($data[$key] == 1, ' checked="checked"'); ?>/></td>
  </tr>
  <?php } } ?>
  <tr>
	<td class="mbox" width="50%"><?php echo $lang->phrase('admin_groups_floodcheck_in_sec'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_groups_floodcheck_description'); ?></span></td>
   	<td class="mbox" width="50%"><input type="text" name="flood" size="3" value="<?php echo $data['flood']; ?>"></td>
  </tr>
 </table>
 <br />
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_groups_edit_an_usergroup_settings'); ?></td>
  </tr>
  <tr>
	  <td class="mbox" width="50%"><?php echo $lang->phrase('admin_groups_internal_name_for_the_group'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_groups_internal_name_for_the_group_description'); ?></span></td>
	  <td class="mbox" width="50%"><input type="text" name="name" size="35" value="<?php echo $data['name']; ?>"></td>
  </tr><tr>
	  <td class="mbox" width="50%"><?php echo $lang->phrase('admin_groups_public_title'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_groups_public_title_description'); ?></span></td>
	  <td class="mbox" width="50%"><input type="text" name="title" size="35" value="<?php echo $data['title']; ?>"></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_groups_edit'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'edit2') {
	echo head();

	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}groups WHERE id = {$id} LIMIT 1");
	if ($db->num_rows($result) != 1) {
		error('admin.php?action=groups&job=manage', $lang->phrase('admin_groups_no_valid_id_given'));
	}
	$data = $db->fetch_assoc($result); // FIXME

	$sql_values = '';
	foreach ($glk as $key) {
	  	$result = array_search($key, $guest_limitation);
	  	$editable = !(($data['guest'] == 1 && $data['core'] == 1) && $result !== false);
		if ($key != 'guest' && $editable) {
			$sql_values .= $key.' = "'.$gpc->get($key, int).'", ';
		}
	}

	$db->query('UPDATE '.$db->pre.'groups SET '.$sql_values.'flood = "'.$gpc->get('flood', int).'", title = "'.$gpc->get('title', str).'", name = "'.$gpc->get('name', str).'" WHERE id = "'.$id.'" LIMIT 1');

	$delobj = $scache->load('groups');
	$delobj->delete();

	if ($db->affected_rows()) {
		ok('admin.php?action=groups&job=manage');
	}
	else {
		error('admin.php?action=groups&job=add', $lang->phrase('admin_groups_the_group_couldnt_be_updated'));
	}
}
?>