<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// PK: MultiLangAdmin
$lang->group("admin/filetypes");

($code = $plugins->load('admin_filetypes_jobs')) ? eval($code) : null;

if ($job == 'add') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=filetypes&job=add2">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_add_filetype_head'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_extensions'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="extension" size="50" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_relevant_programs'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_relevant_programs2'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="program" size="50" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_description'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_description2'); ?></span></td>
   <td class="mbox" width="50%"><textarea name="desctxt" rows="5" cols="50"></textarea></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_icon'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_icon2'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="icon" size="50" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_mimetype'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="mimetype" size="50" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_delivery_type'); ?></td>
   <td class="mbox" width="50%">
   <select name="stream">
   <option value="attachment"><?php echo $lang->phrase('admin_attachment'); ?></option>
   <option value="inline"><?php echo $lang->phrase('admin_inline'); ?></option>
   </select>
   </td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_button_send'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'add2') {
	echo head();

	$extension = $gpc->get('extension', str);
	$program = $gpc->get('program', str);
	$desctxt = $db->escape_string($gpc->get('desctxt', none));
	$icon = $gpc->get('icon', str);
	$mimetype = $gpc->get('mimetype', str);
	$stream = $gpc->get('stream', str);

    if ($extension{0} == '.') {
        $extension = substr($extension, 1);
    }
	$error = array();
	if (strlen($extension) < 1 && strlen($extension) > 10) {
		$error[] = $lang->phrase('admin_err_no_valid_extension');
	}
	if ($stream != 'inline' && $stream != 'attachment') {
		$error[] = $lang->phrase('admin_err_no_valid_delivery_type');
	}
	if (count($error) > 0) {
		error('admin.php?action=filetypes&job=manage', $error);
	}
	else {
    	if (!empty($mimetype)) {
    	    $mime = ", mimetype";
    	    $mime2 = ", '".$mimetype."'";
    	}
    	else {
    	    $mime = '';
    	    $mime2 = '';
    	}
		$db->query("INSERT INTO {$db->pre}filetypes (extension, program, desctxt, stream, icon{$mime}) VALUES ('{$extension}', '{$program}', '{$desctxt}', '{$stream}', '{$icon}'{$mime2})",__LINE__,__FILE__);
		ok('admin.php?action=filetypes&job=manage', $lang->phrase('admin_filetype_added'));
	}
}
elseif ($job == 'edit') {
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}filetypes WHERE id = '{$_GET['id']}' LIMIT 1");
	if ($db->num_rows($result) != 1) {
		error('admin.php?action=filetypes&job=manage', $lang->phrase('admin_id_not_found'));
	}
	$row = $gpc->prepare($db->fetch_assoc($result));
	?>
<form name="form" method="post" action="admin.php?action=filetypes&job=edit2&id=<?php echo $_GET['id']; ?>">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_change_filetype'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_extensions'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="extension" size="50" value="<?php echo $row['extension']; ?>"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_relevant_programs'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_relevant_programs2'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="program" size="50" value="<?php echo htmlspecialchars($row['program']); ?>"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_description'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_description2'); ?></span></td>
   <td class="mbox" width="50%"><textarea name="desctxt" rows="5" cols="50"><?php echo htmlspecialchars($row['desctxt']); ?></textarea></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_icon'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_icon2'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="icon" size="50" value="<?php echo $row['icon']; ?>"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_mimetype'); ?></font></td>
   <td class="mbox" width="50%"><input type="text" name="mimetype" size="50" value="<?php echo $row['mimetype']; ?>"></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_delivery_type'); ?></font></td>
   <td class="mbox" width="50%">
   <select name="stream">
   <option value="inline"<?php echo iif($row['stream'] == 'inline', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_inline'); ?></option>
   <option value="attachment"<?php echo iif($row['stream'] == 'attachment', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_attachment'); ?></option>
   </select>
   </td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_button_send'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'edit2') {
	echo head();

	$extension = $gpc->get('extension', str);
	$program = $gpc->get('program', str);
	$desctxt = $db->escape_string($gpc->get('desctxt', none));
	$icon = $gpc->get('icon', str);
	$mimetype = $gpc->get('mimetype', str);
	$stream = $gpc->get('stream', str);

    if ($extension{0} == '.') {
        $extension = substr($extension, 1);
    }
	$error = array();
	if (strlen($extension) < 1 && strlen($extension) > 10) {
		$error[] = $lang->phrase('admin_err_no_valid_extension');
	}
	if ($stream != 'inline' && $stream != 'attachment') {
		$error[] = $lang->phrase('admin_err_no_valid_delivery_type');
	}
	if (!empty($mimetype)) {
	    $mime = ", mimetype = '".$mimetype."'";
	}
	else {
	    $mime = '';
	}
	if (count($error) > 0) {
		error('admin.php?action=filetypes&job=manage', $lang->phrase('admin_err_no_valid_extension'));
	}
	else {
		$db->query("UPDATE {$db->pre}filetypes SET extension = '{$extension}', program = '{$program}', desctxt = '{$desctxt}', stream = '{$stream}', icon = '{$icon}'{$mime} WHERE id = '{$_GET['id']}'");
		ok('admin.php?action=filetypes&job=manage', $lang->phrase('admin_filetype_changed'));
	}
}
elseif ($job == 'manage') {
	echo head();
	$tpl = new tpl();
	$result = $db->query('SELECT * FROM '.$db->pre.'filetypes ORDER BY extension');
	?>
	<form name="form" method="post" action="admin.php?action=filetypes&job=delete">
	 <table class="border">
	  <tr>
	   <td class="obox" colspan="7">
		<span style="float: right;"><a class="button" href="admin.php?action=filetypes&amp;job=add"><?php echo $lang->phrase('admin_button_add_new_filetype'); ?></a></span>
		<?php echo $lang->phrase('admin_manage_filetypes'); ?>
	   </td>
	  </tr>
	  <tr>
	   <td class="ubox" width="2%"><?php echo $lang->phrase('admin_th_delete'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all('delete[]');" name="all" value="1" /> <?php echo $lang->phrase('admin_th_delete_all'); ?></span></td>
	   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_th_icon'); ?></td>
	   <td class="ubox" width="22%"><?php echo $lang->phrase('admin_th_filetype'); ?></td>
	   <td class="ubox" width="3%" title="<?php echo $lang->phrase('admin_th_attach_title'); ?>"><?php echo $lang->phrase('admin_th_attach'); ?></td>
	   <td class="ubox" width="3%" title="<?php echo $lang->phrase('admin_th_pics_title'); ?>"><?php echo $lang->phrase('admin_th_pics'); ?></td>
	   <td class="ubox" width="35%"><?php echo $lang->phrase('admin_th_relevant_programs'); ?></td>
	   <td class="ubox" width="20%"><?php echo $lang->phrase('admin_th_mimetype'); ?></td>
	  </tr>
	<?php
	$attachments = explode(',', $config['tpcfiletypes']);
	$pics = explode(',', $config['avfiletypes']);
	while ($row = $gpc->prepare($db->fetch_assoc($result))) {
		$extensions = explode(',', $row['extension']);
		$extension_count = count($extensions);
		$adiff = count(array_intersect($extensions, $attachments));
		$pdiff = count(array_intersect($extensions, $pics));
		if ($adiff == $extension_count) {
			$adiff = '<span style="color: #006600;">'.$lang->phrase('admin_attpic_yes').'</span>';
		}
		elseif ($adiff == 0) {
			$adiff = '<span style="color: #990000;">'.$lang->phrase('admin_attpic_no').'</span>';
		}
		else {
			$adiff = '<span style="color: #b8860b;">'.$lang->phrase('admin_attpic_partially').'</span>';
		}
		if ($pdiff == $extension_count) {
			$pdiff = '<span style="color: #006600;">'.$lang->phrase('admin_attpic_yes').'</span>';
		}
		elseif ($pdiff == 0) {
			$pdiff = '<span style="color: #990000;">'.$lang->phrase('admin_attpic_no').'</span>';
		}
		else {
			$pdiff = '<span style="color: #b8860b;">'.$lang->phrase('admin_attpic_partially').'</span>';
		}
		?>
		<tr>
		   <td class="mbox"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>" /></td>
		   <td class="mbox"><img src="<?php echo $tpl->img('filetypes/'.$row['icon']); ?>" alt="" /></td>
		   <td class="mbox"><a href="admin.php?action=filetypes&job=edit&id=<?php echo $row['id']; ?>" title="<?php echo $lang->phrase('admin_filetype_edit'); ?>"><?php echo implode(', ', $extensions); ?></a></td>
		   <td class="mbox"><?php echo $adiff; ?></td>
		   <td class="mbox"><?php echo $pdiff; ?></td>
		   <td class="mbox"><?php echo $row['program']; ?></td>
		   <td class="mbox" ><?php echo $row['mimetype']; ?></td>
		</tr>
	<?php } ?>
	  <tr>
	   <td class="ubox" width="100%" colspan="7" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_button_delete'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'delete') {
	echo head();
	$delete = $gpc->get('delete', arr_int);
	if (count($delete) > 0) {
		$db->query('DELETE FROM '.$db->pre.'filetypes WHERE id IN ('.implode(',', $delete).')');
		$anz = $db->affected_rows();
		ok('admin.php?action=filetypes&job=manage', $lang->phrase('admin_filetypes_deleted'));
	}
	else {
		error('admin.php?action=filetypes&job=manage');
	}
}
?>
