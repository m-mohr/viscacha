<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "spider.php") die('Error: Hacking Attempt');

if ($_GET['job'] == 'add') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=spider&job=add2">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Add a new Robot</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">User Agent:</td>
   <td class="mbox" width="50%"><input type="text" name="agent" size="50"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Public name:</td>
   <td class="mbox" width="50%"><input type="text" name="name" size="50"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Type:</td>
   <td class="mbox" width="50%">
   <input type="radio" name="type" value="b"> Search engine<br />
   <input type="radio" name="type" value="e"> Mail-Collector<br />
   <input type="radio" name="type" value="v"> Validator
   </td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Add"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($_GET['job'] == 'add2') {
	echo head();

    $name = $gpc->get('name', str);
    $agent = $gpc->get('agent', str);
    $type = $gpc->get('type', str);

	$error = array();
	if (strlen($agent) < 4) {
		$error[] = 'User agent is not valid';
	}
	if (strlen($name) < 4) {
		$error[] = 'Name is not valid';
	}
	if ($type != 'b' && $type != 'e' && $type != 'v') {
		$error[] = 'Type is not valid';
	}
	$result = $db->query('SELECT id FROM '.$db->pre.'spider WHERE user_agent = "'.$agent.'"');
	if ($db->num_rows($result) > 0) {
		$error[] = 'This entry already exists!';
	}	
	if (count($error) > 0) {
		error('admin.php?action=spider&job=add', $error);
	}
	else {
		$db->query("INSERT INTO {$db->pre}spider (user_agent, name, type) VALUES ('".$agent."', '".$name."', '".$type."')");
		if ($db->affected_rows() == 1) {
			$scache = new scache('spiders');
			$scache->deletedata();
			ok('admin.php?action=spider&job=add');
		}
		else {
			error('admin.php?action=spider&job=add', 'Could not create entry!');
		}
	}
}
elseif ($_GET['job'] == 'manage') {
	echo head();
	$result = $db->query('SELECT * FROM '.$db->pre.'spider ORDER BY type, name');
	?>
	<form name="form" method="post" action="admin.php?action=spider&job=delete">
	 <table class="border">
	  <tr> 
	   <td class="obox" colspan="5">Crawler &amp; Robots Manager</td>
	  </tr>
	  <tr> 
	   <td class="ubox" width="5%">Delete</td>
	   <td class="ubox" width="34%">Name</td>
	   <td class="ubox" width="45%">User Agent</td> 
	   <td class="ubox" width="16%">Type</td> 
	  </tr>
	<?php
	while ($row = $gpc->prepare($db->fetch_assoc($result))) {
		if ($row['type'] == 'v') {
			$row['type'] = 'Validator';
		}
		elseif ($row['type'] == 'e') {
			$row['type'] = 'Mail-Collector';
		}
		else {
			$row['type'] = 'Search engine';
		}
		?>
		<tr> 
		   <td class="mbox" width="5%"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
		   <td class="mbox" width="34%"><a href="admin.php?action=spider&job=edit&id=<?php echo $row['id']; ?>"><?php echo $row['name']; ?></a></td>
		   <td class="mbox" width="40%"><?php echo $row['user_agent']; ?></td> 
		   <td class="mbox" width="16%"><?php echo $row['type']; ?></td> 
		</tr>
	<?php } ?>
	  <tr> 
	   <td class="ubox" colspan="5" align="center"><input type="submit" value="Delete"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($_GET['job'] == 'edit') {
    echo head();
    $edit = $gpc->get('id', int);
    
    $result = $db->query('SELECT * FROM '.$db->pre.'spider WHERE id = '.$edit.' LIMIT 1');
	if ($db->num_rows($result) == 0) {
		error('admin.php?action=spider&job=manage', 'No entry selected!');
	}
	$data = $gpc->prepare($db->fetch_assoc($result));
    ?>
<form name="form" method="post" action="admin.php?action=spider&job=edit2&id=<?php echo $edit; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Edit</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">User agent:</td>
   <td class="mbox" width="50%"><input type="text" name="agent" size="50" value="<?php echo $data['user_agent']; ?>"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Public name:</td>
   <td class="mbox" width="50%"><input type="text" name="name" size="50" value="<?php echo $data['name']; ?>"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Type:</td>
   <td class="mbox" width="50%">
   <input type="radio" name="type" value="b"<?php echo iif($data['type'] != 'e' && $data['type'] != 'v', ' checked'); ?>> Search engine<br />
   <input type="radio" name="type" value="e"<?php echo iif($data['type'] == 'e', ' checked'); ?>> Mail-Collector<br />
   <input type="radio" name="type" value="v"<?php echo iif($data['type'] == 'v', ' checked'); ?>> Validator
   </td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan="2" align="center">
   <input type="submit" name="Submit" value="Change">
   </td> 
  </tr>
 </table>
</form> 
    <?php
    echo foot();
}
elseif ($_GET['job'] == 'delete') {
    echo head();
    $anz = 0;
    $delete = $gpc->get('delete', arr_int);
    if (count($delete) > 0) {
        $ids = implode(',', $delete);
		$db->query("DELETE FROM {$db->pre}spider WHERE id IN({$ids})");
		$anz = $db->affected_rows();
		$scache = new scache('spiders');
		$scache->deletedata();
		ok('admin.php?action=spider&job=manage', $anz.' entries deleted!');
    }
    else {
        error('admin.php?action=spider&job=manage');
    }
}
elseif ($_GET['job'] == 'edit2') {
	echo head();

    $id = $gpc->get('id', int);
    $name = $gpc->get('name', str);
    $agent = $gpc->get('agent', str);
    $type = $gpc->get('type', str);

	$error = array();
	if (strlen($agent) < 4) {
		$error[] = 'User agent is not valid';
	}
	if (strlen($name) < 4) {
		$error[] = 'Name is not valid';
	}
	if ($type != 'b' && $type != 'e' && $type != 'v') {
		$error[] = 'Type is not valid';
	}
	if (count($error) > 0) {
		error('admin.php?action=spider&job=edit&submit_edit=1&id='.$id, $error);
	}
	else {
		$db->query("UPDATE {$db->pre}spider SET user_agent = '".$agent."', name = '".$name."', type = '".$type."' WHERE id = '".$id."' LIMIT 1");
		if ($db->affected_rows() == 1) {
			$scache = new scache('spiders');
			$scache->deletedata();
			ok('admin.php?action=spider&job=manage');
		}
		else {
			error('admin.php?action=spider&job=manage', 'Could not edit entry!');
		}
	}
}
?>
