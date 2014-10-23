<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "filetypes.php") die('Error: Hacking Attempt');

if ($job == 'add') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=filetypes&job=add2">
 <table class="border">
  <tr> 
   <td class="obox" colspan=2>Add filetype:</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Extension(s) (separated by comma):</font></td>
   <td class="mbox" width="50%"><input type="text" name="extension" size="50" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Relevant programs:</font><br><font class="stext">Optional: A selection of relevant programs which work with this filetype.</font></td>
   <td class="mbox" width="50%"><input type="text" name="program" size="50" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Description:</font><br><font class="stext">HTML is allowed!</font></td>
   <td class="mbox" width="50%"><textarea name="desctxt" rows="5" cols="50"></textarea></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Icon-filename:</font><br><font class="stext">Optional. Indicate without extension.</font></td>
   <td class="mbox" width="50%"><input type="text" name="icon" size="50" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Mimetype:</font></td>
   <td class="mbox" width="50%"><input type="text" name="mimetype" size="50" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Delivery type:</font></td>
   <td class="mbox" width="50%">
   <select name="stream">
   <option value="attachment">Attachment (offer for download)</option>
   <option value="inline">Inline (open in browser)</option>
   </select>
   </td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Send"></td> 
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
		$error[] = 'No valid extension';
	}
	if ($stream != 'inline' && $stream != 'attachment') {
		$error[] = 'No valid delivery type';
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
		ok('admin.php?action=filetypes&job=manage', 'Filetype has been changed');
	}
}
elseif ($job == 'edit') {
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}filetypes WHERE id = '{$_GET['id']}' LIMIT 1");
	if ($db->num_rows($result) != 1) {
		error('No valid ID indicated','admin.php?action=filetypes&job=manage');
	}
	$row = $gpc->prepare($db->fetch_assoc($result));
	?>
<form name="form" method="post" action="admin.php?action=filetypes&job=edit2&id=<?php echo $_GET['id']; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Change filetype:</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Extension(s) (separated by comma):</font></td>
   <td class="mbox" width="50%"><input type="text" name="extension" size="50" value="<?php echo $row['extension']; ?>"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Relevant programs:</font><br><font class="stext">Optional: A Selection of relevant Programs which work with this filetype.</font></td>
   <td class="mbox" width="50%"><input type="text" name="program" size="50" value="<?php echo htmlspecialchars($row['program']); ?>"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Description:</font><br><font class="stext">HTML is activated!</font></td>
   <td class="mbox" width="50%"><textarea name="desctxt" rows="5" cols="50"><?php echo htmlspecialchars($row['desctxt']); ?></textarea></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Icon-filename:</font><br><font class="stext">Optional. Indicate without extension.</font></td>
   <td class="mbox" width="50%"><input type="text" name="icon" size="50" value="<?php echo $row['icon']; ?>"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Mimetype:</font></td>
   <td class="mbox" width="50%"><input type="text" name="mimetype" size="50" value="<?php echo $row['mimetype']; ?>"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Delivery type:</font></td>
   <td class="mbox" width="50%">
   <select name="stream">
   <option value="inline"<?php echo iif($row['stream'] == 'inline', ' selected="selected"'); ?>>Inline (open in browser)</option>
   <option value="attachment"<?php echo iif($row['stream'] == 'attachment', ' selected="selected"'); ?>>Attachment (offer for download)</option>
   </select>
   </td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Send"></td> 
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
		$error[] = 'No valid extension';
	}
	if ($stream != 'inline' && $stream != 'attachment') {
		$error[] = 'No valid delivery type';
	}
	if (!empty($mimetype)) {
	    $mime = ", mimetype = '".$mimetype."'";
	}
	else {
	    $mime = '';
	}
	if (count($error) > 0) {
		error('admin.php?action=filetypes&job=manage', 'No valid extension');
	}
	else {
		$db->query("UPDATE {$db->pre}filetypes SET extension = '{$extension}', program = '{$program}', desctxt = '{$desctxt}', stream = '{$stream}', icon = '{$icon}'{$mime} WHERE id = '{$_GET['id']}'");
		ok('admin.php?action=filetypes&job=manage', 'Filetype has been changed');
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
	   <td class="obox" colspan="7">Manage Filetypes</td>
	  </tr>
	  <tr> 
	   <td class="ubox" width="2%">Delete</td>
	   <td class="ubox" width="5%">Icon</td>
	   <td class="ubox" width="22%">Filetype</td>
	   <td class="ubox" width="3%" title="Filetype is allowed for Attachments">Attachm.</td>
	   <td class="ubox" width="3%" title="Filetype is allowed for Avatars/Personal Pics">Pics</td>
	   <td class="ubox" width="35%">Relevant programs</td> 
	   <td class="ubox" width="20%">Mimetype</td>
	  </tr>
	<?php
	$attachments = explode('|', $config['tpcfiletypes']);
	foreach ($attachments as $key => $value) {
		$attachments[$key] = substr($value, 1);
	}
	$pics = explode('|', $config['avfiletypes']);
	foreach ($pics as $key => $value) {
		$pics[$key] = substr($value, 1);
	}
	while ($row = $gpc->prepare($db->fetch_assoc($result))) {
		$extensions = explode(',', $row['extension']);
		$extension_count = count($extensions);
		$adiff = count(array_intersect($extensions, $attachments));
		$pdiff = count(array_intersect($extensions, $pics));
		if ($adiff == $extension_count) {
			$adiff = '<span style="color: #006600;">Yes</span>';
		}
		elseif ($adiff == 0) {
			$adiff = '<span style="color: #990000;">No</span>';
		}
		else {
			$adiff = '<span style="color: #b8860b;">Partially</span>';
		}
		if ($pdiff == $extension_count) {
			$pdiff = '<span style="color: #006600;">Yes</span>';
		}
		elseif ($pdiff == 0) {
			$pdiff = '<span style="color: #990000;">No</span>';
		}
		else {
			$pdiff = '<span style="color: #b8860b;">Partially</span>';
		}
		?>
		<tr> 
		   <td class="mbox"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>" /></td>
		   <td class="mbox"><img src="<?php echo $tpl->img('filetypes/'.$row['icon']); ?>" alt="" /></td>
		   <td class="mbox"><a href="admin.php?action=filetypes&job=edit&id=<?php echo $row['id']; ?>" title="Edit"><?php echo implode(', ', $extensions); ?></a></td>
		   <td class="mbox"><?php echo $adiff; ?></td>
		   <td class="mbox"><?php echo $pdiff; ?></td>
		   <td class="mbox"><?php echo $row['program']; ?></td>
		   <td class="mbox" ><?php echo $row['mimetype']; ?></td>
		</tr>
	<?php } ?>
	  <tr> 
	   <td class="ubox" width="100%" colspan="7" align="center"><input type="submit" name="Submit" value="Delete"></td> 
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
		$deleteids = array();
		foreach ($delete as $did) {
			$deleteids[] = 'id = '.$did; 
		}
		$db->query('DELETE FROM '.$db->pre.'filetypes WHERE '.implode(' OR ',$deleteids));
		$anz = $db->affected_rows();	
		ok('admin.php?action=filetypes&job=manage', $anz.'Entries deleted');
	}
	else {
		error('admin.php?action=filetypes&job=manage', 'No Input!');
	}
}
?>
