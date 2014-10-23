<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "forums.php") die('Error: Hacking Attempt');

function ForumSubs ($tree, $cat, $board, $char = '+', $level = 0) {
	foreach ($tree as $cid => $boards) {
		$cdata = $cat[$cid];
		?>
		<tr> 
			<td class="mmbox" width="50%"><?php echo str_repeat($char, $level).' <b>'.$cdata['name']; ?></b></td>
			<td class="mmbox" width="10%"><?php echo $cdata['c_order']; ?>&nbsp;&nbsp;
				<a href="admin.php?action=forums&job=move&temp1=c_<?php echo $cdata['id']; ?>&int1=-1"><img src="admin/html/images/asc.gif" border="0" alt="Up"></a>&nbsp;
				<a href="admin.php?action=forums&job=move&temp1=c_<?php echo $cdata['id']; ?>&int1=1"><img src="admin/html/images/desc.gif" border="0" alt="Down"></a>
			</td>
			<td class="mmbox" width="30%">
			  <form name="act" action="admin.php?action=locate" method="post">
			  	<select size="1" name="url" onchange="locate(this.value)">
			  	<option value="" selected="selected">Please choose</option>
				 <optgroup label="General">
				  <option value="admin.php?action=forums&job=cat_edit&id=<?php echo $cdata['id']; ?>">Edit Category</option>
				  <option value="admin.php?action=forums&job=cat_delete&id=<?php echo $cdata['id']; ?>">Delete Category</option>
				 </optgroup>
				</select>
				<input type="submit" value="Go">
			  </form>
			</td> 
		</tr>
		<?php
		foreach ($boards as $bid => $sub) {
			$bdata = $board[$bid];
			?>
			  <tr> 
			    <td class="mbox"><?php echo str_repeat($char, $level+1).' '.$bdata['name']; ?></td>
				<td class="mbox" width="10%" align="right"><?php echo $bdata['c_order']; ?>&nbsp;&nbsp;
				<a href="admin.php?action=forums&job=move&temp1=f_<?php echo $bdata['id']; ?>&int1=-1"><img src="admin/html/images/asc.gif" border="0" alt="Up"></a>&nbsp;
				<a href="admin.php?action=forums&job=move&temp1=f_<?php echo $bdata['id']; ?>&int1=1"><img src="admin/html/images/desc.gif" border="0" alt="Down"></a>
				</td>
			   <td class="mbox" width="30%">
				<form name="act" action="admin.php?action=locate" method="post">
			  		<select size="1" name="url" onchange="locate(this.value)">
			  		<option value="" selected="selected">Please choose</option>
					 <optgroup label="General">
					  <option value="admin.php?action=forums&job=edit&id=<?php echo $bdata['id']; ?>">Edit Forum</option>
					  <option value="admin.php?action=forums&job=delete&id=<?php echo $bdata['id']; ?>">Delete Forum</option>
					 </optgroup>
					 <?php if ($bdata['opt'] != 're') { ?>
					 <optgroup label="Permissions">
					  <option value="admin.php?action=forums&job=rights&id=<?php echo $bdata['id']; ?>">Manage Usergroups</option>
					  <option value="admin.php?action=forums&job=add_rights&id=<?php echo $bdata['id']; ?>">Add Usergroup</option>
					 </optgroup>
					 <optgroup label="Prefix">
					  <option value="admin.php?action=forums&job=prefix&id=<?php echo $bdata['id']; ?>">Manage</option>
					 </optgroup>
					 <optgroup label="Statistics">
					  <option value="admin.php?action=forums&job=updatestats&id=<?php echo $bdata['id']; ?>">Recount</option>
					 </optgroup>
					 <optgroup label="Moderators">
					  <option value="admin.php?action=forums&job=add_mod&id=<?php echo $bdata['id']; ?>">Add</option>
					  <option value="admin.php?action=forums&job=mods&int1=<?php echo $bdata['id']; ?>">Delete</option>
					 </optgroup>
					 <?php } ?>
					</select>
					<input type="submit" value="Go" />
				</form>
			   </td> 
			  </tr>	
	    	<?php
	    	ForumSubs($sub, $cat, $board, $char, $level+2);
	    }
	}
}
if ($job == 'ajax_changemodperm') {
	$mid = $gpc->get('mid', int);
	$bid = $gpc->get('bid', int);
	$key = $gpc->get('key', str);
	if(!is_id($mid) || !is_id($bid) || empty($key)) {
		die('The ids or the key is not valid!');
	}
	$result = $db->query("SELECT {$key} FROM {$db->pre}moderators WHERE bid = '{$bid}' AND mid = '{$mid}' LIMIT 1");
	$perm = $db->fetch_assoc($result);
	if ($db->num_rows($result) == 0) {
		die('Not found!');
	}
	$perm = invert($perm[$key]);
	$db->query("UPDATE {$db->pre}moderators SET {$key} = '{$perm}' WHERE bid = '{$bid}' AND mid = '{$mid}' LIMIT 1");
	die(strval($perm));
}
elseif ($job == 'mods') {
	echo head();
	if ($gpc->get('temp1', str) == 'member') {
		$order = 'u.name, c.name';
	}
	else {
		$order = 'c.name, u.name';
	}
	if ($gpc->get('int1', int) > 0) {
		$where = 'WHERE m.bid = '.$gpc->get('int1', int);
	}
	else {
		$where = '';
	}
	$result = $db->query("SELECT m.*, u.name as user, c.name as cat FROM {$db->pre}moderators AS m LEFT JOIN {$db->pre}user AS u ON u.id = m.mid LEFT JOIN {$db->pre}cat AS c ON c.id = m.bid $where ORDER BY ".$order);
	?>
<form name="form" method="post" action="admin.php?action=forums&job=mods2">
 <table class="border">
  <tr> 
   <td class="obox" colspan="9"><span style="float: right;">Sort by [<a href="admin.php?action=forums&job=mods&temp1=member">Name</a>] [<a href="admin.php?action=forums&job=mods&temp1=board">Forums</a>]</span>Moderator Manager</td>
  </tr>
  <tr class="ubox">
    <td width="5%" rowspan="2">Delete</td>
    <td width="30%" rowspan="2">Name</td>
    <td width="30%" rowspan="2">Forum</td>
    <td width="20%" rowspan="2">Period</td>
    <td width="21%" colspan="3">Status</td>
    <td width="14%" colspan="2">Topics</td>
  </tr>
  <tr class="ubox">
    <td width="7%">Rating</td>
    <td width="7%">Articles</td>
    <td width="7%">News</td>
    <td width="7%">move</td>
    <td width="7%">delete</td>
  </tr>
<?php 
	while ($row = $db->fetch_assoc($result)) {
	if ($row['time'] > -1) {
		$row['time'] = 'until '.gmdate('M d, Y',times($row['time']));
	}
	else {
	    $row['time'] = '<em>No restriction!</em>';
	}
    $p1 = ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=forums&job=ajax_changemodperm&mid='.$row['mid'].'&bid='.$row['bid'].'&key=';
    $p2 = '\')"';
?>
  <tr> 
   <td class="mbox" width="5%" align="center"><input type="checkbox" value="<?php echo $row['mid'].'_'.$row['bid']; ?>" name="delete[]"></td>
   <td class="mbox" width="30%"><?php echo $row['user']; ?></td>
   <td class="mbox" width="30%"><?php echo $row['cat']; ?></td>
   <td class="mbox" width="20%"><?php echo $row['time']; ?></td>
   <td class="mbox" width="7%" align="center"><?php echo noki($row['s_rating'], $p1.'s_rating'.$p2); ?></td>
   <td class="mbox" width="7%" align="center"><?php echo noki($row['s_article'], $p1.'s_article'.$p2); ?></td>
   <td class="mbox" width="7%" align="center"><?php echo noki($row['s_news'], $p1.'s_news'.$p2); ?></td>
   <td class="mbox" width="7%" align="center"><?php echo noki($row['p_mc'], $p1.'p_mc'.$p2); ?></td>
   <td class="mbox" width="7%" align="center"><?php echo noki($row['p_delete'], $p1.'p_delete'.$p2); ?></td>
  </tr>
<?php } ?>
  <tr> 
   <td class="ubox" width="100%" colspan="9" align="center"><input type="submit" name="Submit" value="Delete"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'mods2') {
	echo head();
	if (count($gpc->get('delete', none)) > 0) {
		$deleteids = array();
		foreach ($gpc->get('delete', none) as $did) {
			list($mid, $bid) = explode('_',$did);
			$mid = $gpc->save_int($mid);
			$bid = $gpc->save_int($bid);
			$deleteids[] = ' (mid = '.$mid.' AND bid = '.$bid.') '; 
		}
		$db->query("DELETE FROM {$db->pre}moderators WHERE ".implode(' OR ',$deleteids));
		$anz = $db->affected_rows();
		$delobj = $scache->load('index-moderators');
		$delobj->delete();
		ok('admin.php?action=forums&job=mods', $anz.' entries deleted!');
	}
	else {
		error('admin.php?action=forums&job=mods', 'Invalid data sent!');
	}
}
elseif ($job == 'delete') {
	echo head();
	?>
	<table class="border">
	<tr><td class="obox">Delete Forum</td></tr>
	<tr><td class="mbox">
	    <p align="center">Do you really want to delete this forum?</p>
	    <p align="center">
	        <a href="admin.php?action=forums&amp;job=delete2&amp;id=<?php echo $_GET['id']; ?>">
	        	<img alt="Yes" border="0" src="admin/html/images/yes.gif" /> Yes
	        </a>
	        &nbsp;&nbsp;&nbsp;&nbsp;
	        <a href="javascript: history.back(-1);"><img border="0" alt="No" src="admin/html/images/no.gif" /> No</a>
	    </p>
	</td></tr>
	</table>
	<?php
	echo foot();
}
elseif ($job == 'delete2') {
	echo head();
	$id = array();
	$result = $db->query("SELECT id FROM {$db->pre}topics WHERE board = '{$_GET['id']}'");
		if ($db->num_rows($result) > 0) {
		while ($row = $db->fetch_assoc($result)) {
			$id[] = $row['id'];
		}
		$ids = implode(',', $id);
	
		$db->query ("DELETE FROM {$db->pre}replies WHERE board = '{$_GET['id']}'",__LINE__,__FILE__);
		$uresult = $db->query ("SELECT file FROM {$db->pre}uploads WHERE topic_id IN({$ids})",__LINE__,__FILE__);
		while ($urow = $db->fetch_num($uresult)) {
		    $filesystem->unlink('uploads/topics/'.$urow[0]);
		    if (file_exists('uploads/topics/thumbnails/'.$urow[0])) {
		    	$filesystem->unlink('uploads/topics/thumbnails/'.$urow[0]);
		    }
		}
		$db->query ("DELETE FROM {$db->pre}uploads WHERE topic_id IN({$ids})",__LINE__,__FILE__);
		$db->query ("DELETE FROM {$db->pre}postratings WHERE tid IN({$ids})",__LINE__,__FILE__);
		$db->query ("DELETE FROM {$db->pre}abos WHERE tid IN({$ids})",__LINE__,__FILE__);
		$db->query ("DELETE FROM {$db->pre}topics WHERE board = '{$_GET['id']}'",__LINE__,__FILE__);
		$votes = $db->query("SELECT id FROM {$db->pre}vote WHERE tid IN({$ids})",__LINE__,__FILE__);
		$voteaids = array();
		while ($row = $db->fetch_num($votes)) {
			$voteaids[] = $row[0];
		}
		if (count($voteaids) > 0) {
			$db->query ("DELETE FROM {$db->pre}votes WHERE id IN(".implode(',', $voteaids).")",__LINE__,__FILE__);
		}
		$db->query ("DELETE FROM {$db->pre}vote WHERE tid IN({$ids})",__LINE__,__FILE__);
	}
	$db->query("DELETE FROM {$db->pre}fgroups WHERE bid = '{$_GET['id']}'");
	$db->query("DELETE FROM {$db->pre}moderators WHERE bid = '{$_GET['id']}'");
	$db->query("DELETE FROM {$db->pre}prefix WHERE bid = '{$_GET['id']}'");
	$db->query("DELETE FROM {$db->pre}cat WHERE id = '{$_GET['id']}' LIMIT 1");
	
	$delobj = $scache->load('cat_bid');
	$delobj->delete();
	$delobj = $scache->load('forumtree');
	$delobj->delete();
	$delobj = $scache->load('parent_forums');
	$delobj->delete();
	
	ok('admin.php?action=forums&job=manage', 'Board was successfully deleted!');
}
elseif ($job == 'edit') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query('SELECT * FROM '.$db->pre.'cat WHERE id = '.$id);
	if ($db->num_rows() == 0) {
		error('admin.php?action=forums&job=manage', 'Invalid ID given');
	}
	$row = $db->fetch_assoc($result);
	?>
<form name="form" method="post" action="admin.php?action=forums&job=editforum2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2">Edit a forum</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Title:</td>
   <td class="mbox" width="50%"><input type="text" name="name" size="50" value="<?php echo $gpc->prepare($row['name']); ?>"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Description:</font><br /><font class="stext">Optional. HTML is allowed.</td>
   <td class="mbox" width="50%"><textarea name="desc" rows="4" cols="50"><?php echo $row['desc']; ?></textarea></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Parent Forum/Category:</font></td>
   <td class="mbox" width="50%">
   <select name="parent">
   <option value="NULL">Do not change</option>
   <?php
	$forumtree = $scache->load('forumtree');
	$tree = $forumtree->get();
	$categories_obj = $scache->load('categories');
	$categories = $categories_obj->get();
	$catbid = $scache->load('cat_bid');
	$boards = $catbid->get();
	AdminSelectForum($tree, $categories, $boards);
   ?>
   </select>
   </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Forum Link:<br /><span class="stext">Entering a URL here will cause anyone clicking the forum link to be redirected to that URL.</span></td>
   <td class="mbox" width="50%"><input type="text" value="<?php echo iif($row['opt'] == 're', $row['optvalue']); ?>" name="link" size="50" id="dis1" onmouseover="disable(this)" onmouseut="disable(this)"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Forum Password:<br /><span class="stext">Subforums are protected with this password, too!</span></td>
   <td class="mbox" width="50%"><input type="text" value="<?php echo iif($row['opt'] == 'pw', $row['optvalue']); ?>" name="text" size="50" id="dis2" onmouseover="disable(this)" onmouseut="disable(this)"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Number of Posts per Page:<br /><span class="stext">0 = Use default value (<?php echo $config['topiczahl']; ?>)</span></td>
   <td class="mbox" width="50%"><input type="text" name="topiczahl" size="5" value="<?php echo $row['topiczahl']; ?>" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Number of Topics per Forumpage:<br /><span class="stext">0 = Use default value (<?php echo $config['forumzahl']; ?>)</span></td>
   <td class="mbox" width="50%"><input type="text" name="forumzahl" size="5" value="<?php echo $row['forumzahl']; ?>" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Hide forum from users without authorization:<br /><span class="stext">Forum will not appear if it is locked and option is checked. This only affects forums without password.</span></td>
   <td class="mbox" width="50%"><input type="checkbox" name="invisible" value="1"<?php echo iif($row['invisible'] == 1, ' checked="checked"'); ?> /></td> 
  </tr>
  <tr> 
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'editforum2') {
	echo head();

	$id = $gpc->get('id', int);
	$name = $gpc->get('name', str);
	$desc = $gpc->get('desc', str);
	$pw = $gpc->get('pw', str);
	$link = $gpc->get('link', str);
	$parent = $gpc->get('parent', str);
	$invisible = $gpc->get('invisible', int);
	$topiczahl = $gpc->get('topiczahl', int);
	$forumzahl = $gpc->get('forumzahl', int);

	if (!$id) {
		error('admin.php?action=forums&job=edit&id='.$id, 'EInvalid ID given');
	}

	$option = '';
	if ($parent != 'NULL') {
		if (preg_match("/c_\d{1,}/", $parent) == 1) {
			$cid = str_replace("c_", "", $parent);
			$array = $db->fetch_num($db->query("SELECT bid FROM {$db->pre}cat WHERE cid = $cid LIMIT 1"));
			$bid = $array[0];
		}
		elseif (preg_match("/f_\d{1,}/", $parent) == 1) {
			$bid = str_replace("f_", "", $parent);
			$array = $db->fetch_num($db->query("SELECT cid FROM {$db->pre}cat WHERE bid = $bid LIMIT 1"));
			if ($array[0] < 1) {
				$array2 = $db->fetch_num($db->query("SELECT name, c.desc FROM {$db->pre}cat AS c WHERE id = $bid LIMIT 1"));
				$db->query("INSERT INTO {$db->pre}categories (name, desctxt, c_order) VALUES ('{$array2[0]}', '{$array2[1]}', 0)");
				$cid = $db->insert_id();
			}
			else {
				$cid = $array[0];
			}
		}
		else {
			error('admin.php?action=forums&job=edit&id='.$id, 'Could not retrieve forum or categorie!');
		}
		$option .= ", bid = '$bid', cid = '$cid'";
	}
	
	if (strlen($name) < 2) {
		error('admin.php?action=forums&job=edit&id='.$id, 'Name is too short (< 2 chars)');
	}
	if (strlen($name) > 200) {
		error('admin.php?action=forums&job=edit&id='.$id, 'Name is too long (> 200 chars)');
	}
	if (strlen($link) > 0) {
		$opt = 're';
		$optvalue = $link;
	}
	elseif (strlen($pw) > 0) {
		$opt = 'pw';
		$optvalue = $pw;
		$invisible = 0;
	}
	else {
		$opt = '';
		$optvalue = '';	
	}
	$db->query("UPDATE {$db->pre}cat SET name = '{$name}', `desc` = '{$desc}', forumzahl = '{$forumzahl}', topiczahl = '{$topiczahl}', invisible = '{$invisible}', opt = '{$opt}', optvalue = '{$optvalue}' {$option} WHERE id = '{$id}' LIMIT 1");
	
	$delobj = $scache->load('categories');
	$delobj->delete();
	$delobj = $scache->load('cat_bid');
	$delobj->delete();
	$delobj = $scache->load('forumtree');
	$delobj->delete();
	$delobj = $scache->load('parent_forums');
	$delobj->delete();
	
	ok('admin.php?action=forums&job=manage','Forum wurde editiert!');
}
elseif ($job == 'add_mod') {
	echo head();
    $id = $gpc->get('id', str);
	if (!is_id($id)) {
		error('admin.php?action=forums&job=manage', 'Forum or Category not found on account of an invalid ID');
	}
	?>
<form name="form" method="post" action="admin.php?action=forums&job=add_mod2">
<input type="hidden" name="id" value="<?php echo $id; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Add Moderator</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Username:</td>
   <td class="mbox" width="50%"><input type="text" name="temp1" size="50" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Period:<br />
   <span class="stext">Entering a date here will cause that the moderator has the specified permissions only until the entered date. The moderator will loose his permissions at the specified date at 0 o'clock! This is optional!</span></td>
   <td class="mbox" width="50%">Day: <input type="text" name="day" size="4" />&nbsp;&nbsp;&nbsp;&nbsp;Month: <input type="text" name="month" size="4" />&nbsp;&nbsp;&nbsp;&nbsp;Year: <input type="text" name="weekday" size="6" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Status: Is allowed to...</td>
   <td class="mbox" width="50%">
   <input type="checkbox" name="int1" value="1" checked="checked" /> set Ratings (Good, Bad)<br />
   <input type="checkbox" name="int2" value="1" /> specify a topic as news<br />
   <input type="checkbox" name="int3" value="1" /> specify a topic as article
   </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Manage Posts: Is allowed to...</td>
   <td class="mbox" width="50%">
   <input type="checkbox" name="int4" value="1" checked="checked" /> delete posts and topics<br />
   <input type="checkbox" name="int5" value="1" checked="checked" /> move posts and topics
   </td> 
  </tr>
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Add"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'add_mod2') {
	echo head();
	
    $id = $gpc->get('id', str);
    $temp1 = $gpc->get('temp1', str);
    $month = $gpc->get('month', int);
    $day = $gpc->get('day', int);
    $weekday = $gpc->get('weekday', int);
	if (!is_id($id)) {
		error('admin.php?action=forums&job=manage', 'Forum or Category was not found on account of an invalid ID');
	}
	$uid = $db->fetch_num($db->query('SELECT id FROM '.$db->pre.'user WHERE name = "'.$temp1.'" LIMIT 1'));
	if ($uid[0] < 1) {
		error('admin.php?action=forums&job=add_mod&id='.$id, 'Member not found!');
	}
	if ($month > 0 && $day > 0 && $weekday > 0) {
		$timestamp = mktime(0, 0, 0, $month, $day, $weekday, -1);
	}
	else {
		$timestamp = 'NULL';
	}
	
	$db->query('INSERT INTO '.$db->pre.'moderators (mid, bid, s_rating, s_news, s_article, p_delete, p_mc, time) VALUES ('.$uid[0].', '.$id.', "'.$gpc->get('int1', int).'", "'.$gpc->get('int2', int).'", "'.$gpc->get('int3', int).'", "'.$gpc->get('int4', int).'", "'.$gpc->get('int5', int).'", '.$timestamp.')');
	if ($db->affected_rows() == 1) {
		$delobj = $scache->load('index-moderators');
		$delobj->delete();
		ok('admin.php?action=forums&job=add_mod&id='.$id, 'Moderator successfully added!');
	}
	else {
		error('admin.php?action=forums&job=add_mod&id='.$id);
	}
}
elseif ($job == 'addforum') {
	echo head();
	
	$forumtree = $scache->load('forumtree');
	$tree = $forumtree->get();
	$categories_obj = $scache->load('categories');
	$categories = $categories_obj->get();
	$catbid = $scache->load('cat_bid');
	$boards = $catbid->get();
	
	?>
<form name="form" method="post" action="admin.php?action=forums&job=addforum2">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Add a new forum</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Title:</td>
   <td class="mbox" width="50%"><input type="text" name="name" size="50" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Description:<br /><span class="stext">Optional. HTML is allowed.<7span></td>
   <td class="mbox" width="50%"><textarea name="desc" rows="4" cols="50"></textarea></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Parent Forum/Category:</font></td>
   <td class="mbox" width="50%">
	<select name="parent" size="1">
	 <?php AdminSelectForum($tree, $categories, $boards); ?>
	</select>
   </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Forum Link :<br /><span class="stext">Entering a URL here will cause anyone clicking the forum link to be redirected to that URL.</span></td>
   <td class="mbox" width="50%"><input type="text" name="link" size="50" id="dis1" onchange="disable(this)" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Forum Password:<br /><span class="stext">Subforums are protected with this password, too!</span></td>
   <td class="mbox" width="50%"><input type="text" name="pw" size="50" id="dis2" onchange="disable(this)" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Number of Posts per Page:<br /><span class="stext">0 = Use default value (<?php echo $config['topiczahl']; ?>)</span></td>
   <td class="mbox" width="50%"><input type="text" name="topiczahl" size="5" value="0" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Number of Topics per Forumpage:<br /><span class="stext">0 = Use default value (<?php echo $config['forumzahl']; ?>)</span></td>
   <td class="mbox" width="50%"><input type="text" name="forumzahl" size="5" value="0" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Hide forum from users without authorization:<br /><span class="stext">Forum will not appear if it is locked and option is checked. This only affects forums without password.</span></td>
   <td class="mbox" width="50%"><input type="checkbox" name="invisible" value="1" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Copy permissions from:<br /><span class="stext">The forum will have the same permissions as the one you select here. If no forum is selected the default settings are used. <em>Caution: This is experimental! Use with care and report bugs, please.</em></span></td>
   <td class="mbox" width="50%">
	<select name="copypermissions">
   		<option value="0">Default</option>
   		<?php AdminSelectForum($tree, $categories, $boards); ?>
   	</select>
   </td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Sort in:</font></td>
   <td class="mbox" width="50%"><select name="sort">
   <option value="0">before existing forums</option>
   <option value="1" selected="selected">after existing forums</option>
   </select></td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Submit" /></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'addforum2') {
	echo head();
	
	$name = $gpc->get('name', str);
	$desc = $gpc->get('desc', str);
	$sort = $gpc->get('sort', int);
	$pw = $gpc->get('pw', str);
	$link = $gpc->get('link', str);
	$parent = $gpc->get('parent', str);
	$invisible = $gpc->get('invisible', int);
	$topiczahl = $gpc->get('topiczahl', int);
	$forumzahl = $gpc->get('forumzahl', int);
	$perm = $gpc->get('copypermissions', str);
	
	if (preg_match("/c_\d{1,}/", $parent) == 1) {
		$cid = str_replace("c_", "", $parent);
		$array = $db->fetch_num($db->query("SELECT bid FROM {$db->pre}cat WHERE cid = '{$cid}' LIMIT 1"));
		$bid = $array[0];
	}
	elseif (preg_match("/f_\d{1,}/", $parent) == 1) {
		$bid = str_replace("f_", "", $parent);
		$array = $db->fetch_num($db->query("SELECT cid FROM {$db->pre}cat WHERE bid = '{$bid}' LIMIT 1"));
		if ($array[0] < 1) {
			$array2 = $db->fetch_num($db->query("SELECT name, c.desc FROM {$db->pre}cat AS c WHERE id = $bid LIMIT 1"));
			$db->query("INSERT INTO {$db->pre}categories (name, desctxt, c_order) VALUES ('{$array2[0]}', '{$array2[1]}', 0)");
			$cid = $db->insert_id();
		}
		else {
			$cid = $array[0];
		}
	}
	else {
		error('admin.php?action=forums&job=addforum','Could not retrieve forum or category!');
	}
	
	if ($sort == 1) {
		$sortx = $db->fetch_num($db->query("SELECT MAX(c_order) FROM {$db->pre}cat WHERE cid = {$cid} LIMIT 1"));
		$sort = $sortx[0]+1;
	}
	elseif ($sort == 0) {
		$sortx = $db->fetch_num($db->query("SELECT MIN(c_order) FROM {$db->pre}cat WHERE cid = {$cid} LIMIT 1"));
		$sort = $sortx[0]-1;
	}
	else {
		$sort = 0;
	}
	if (strlen($name) < 2) {
		error('admin.php?action=forums&job=addforum', 'Name is too short (< 2 chars)');
	}
	if (strlen($name) > 200) {
		error('admin.php?action=forums&job=addforum', 'Name is too long (> 200 chars)');
	}
	if (strlen($link) > 0) {
		$opt = 're';
		$optvalue = $link;
	}
	elseif (strlen($pw) > 0) {
		$opt = 'pw';
		$optvalue = $pw;
		$invisible = 0;
	}
	else {
		$opt = '';
		$optvalue = '';	
	}
	
	$db->query("
	INSERT INTO {$db->pre}cat (name, `desc`, bid, cid, c_order, opt, optvalue, forumzahl, topiczahl, invisible)
	VALUES ('{$name}', '{$desc}', '{$bid}', '{$cid}', '{$sort}', '{$opt}', '{$optvalue}','{$forumzahl}','{$topiczahl}','{$invisible}')
	", __LINE__, __FILE__);

	if (preg_match("/f_\d{1,}/", $perm) == 1) {
		$newid = $db->insert_id();
		$fid = str_replace("f_", "", $perm);
		$result = $db->query("SELECT * FROM {$db->pre}fgroups WHERE bid = '{$fid}'");
		while($row = $db->fetch_assoc($result)) {
			unset($row['bid'], $row['fid']);
			$keys = array_keys($row);
			sort($keys, SORT_STRING);
			ksort($row, SORT_STRING);
			$row_str = implode("','", $row);
			$keys_str = implode(',', $keys);
			$db->query("INSERT INTO {$db->pre}fgroups (".$keys_str.", bid) VALUES ('".$row_str."', '{$newid}')");
		}
	}

	$delobj = $scache->load('cat_bid');
	$delobj->delete();
	$delobj = $scache->load('forumtree');
	$delobj->delete();
	$delobj = $scache->load('categories');
	$delobj->delete();
	$delobj = $scache->load('parent_forums');
	$delobj->delete();
	
	ok('admin.php?action=forums&job=addforum', 'Forum successfully added!');
}
elseif ($job == 'addcat') {
	echo head();
	$forumtree = $scache->load('forumtree');
	$cat = $forumtree->get();
	$categories_obj = $scache->load('categories');
	$categories = $categories_obj->get();
	?>
<form name="form" method="post" action="admin.php?action=forums&job=addcat2">
 <table class="border">
  <tr> 
   <td class="obox" colspan=2>Add Category</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Name:</td>
   <td class="mbox" width="50%"><input type="text" name="temp1" size="50" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Short Description:<br /><span class="stext">Optional. HTML is allowed!</span></td>
   <td class="mbox" width="50%"><textarea name="temp2" rows="2" cols="50"></textarea></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Sort in after:</td>
   <td class="mbox" width="50%"><select name="sort">
   <?php
   $catid = array_keys($cat);
   foreach ($catid as $id) {
   	$row = $categories[$id];
   ?>
	<option value="<?php echo $row['c_order']; ?>"><?php echo $row['name']; ?></option>
	<?php } ?>
   </select></td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Add"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'addcat2') {
	echo head();
	
	$sort = $gpc->get('sort', int);
	
	$boardname = $gpc->get('temp1', str);
	if (strlen($boardname) < 2) {
		error('admin.php?action=forums&job=addcat', 'Name ist zu kurz ( < 2 chars)');
	}
	if (strlen($boardname) > 200) {
		error('admin.php?action=forums&job=addcat', 'Name is too long( > 200 chars)');
	}
	
	$db->query("INSERT INTO ".$db->pre."categories (name, desctxt, c_order) VALUES ('{$boardname}', '".$gpc->get('temp2', str)."', '$sort')");

	$delobj = $scache->load('categories');
	$delobj->delete();
	$delobj = $scache->load('forumtree');
	$delobj->delete();

	ok('admin.php?action=forums&job=addcat', 'Category sucessfully created!');
}
elseif ($job == 'manage') {
	send_nocache_header();
	echo head();
	?>
	<table class="border">
	<tr><td class="obox" colspan="3">Manage Forums &amp; Categories</td></tr>
	<tr> 
	<td class="ubox" width="50%"><b>Title</b></td>
	<td class="ubox" width="20%"><b>Ordering</b></td> 
	<td class="ubox" width="30%"><b>Action</b></td> 
	</tr>
	<?php
	$forumtree = $scache->load('forumtree');
	$tree = $forumtree->get();
	$categories_obj = $scache->load('categories');
	$categories = $categories_obj->get();
	$catbid = $scache->load('cat_bid');
	$boards = $catbid->get();
	ForumSubs($tree, $categories, $boards);
	?>
	</table>
	<?php
	echo foot();
}
elseif ($job == 'updatestats') {
	echo head();
	UpdateBoardStats($gpc->get('id', int));
	ok('admin.php?action=forums&job=manage', 'Statistics successfully recounted!');
}
elseif ($job == 'move') {
    $id = $gpc->get('temp1', str);
	if (!is_id($id)) {
		error('admin.php?action=forums&job=manage', 'Forum or Category was not found on account of an invalid ID.');
	}
	list($type, $gid) = explode('_',$id);
	$gid = $gpc->save_int($gid);
	$move = $gpc->get('int1', int);
	
	if ($move == -1 && $type == 'c') {
		$db->query('UPDATE '.$db->pre.'categories SET c_order = c_order-1 WHERE id = '.$gid);
	}
	elseif ($move == 1 && $type == 'c') {
		$db->query('UPDATE '.$db->pre.'categories SET c_order = c_order+1 WHERE id = '.$gid);
	}
	elseif ($move == -1 && $type == 'f') {
		$db->query('UPDATE '.$db->pre.'cat SET c_order = c_order-1 WHERE id = '.$gid);
	}
	elseif ($move == 1 && $type == 'f') {
		$db->query('UPDATE '.$db->pre.'cat SET c_order = c_order+1 WHERE id = '.$gid);
	}
	else {
		error('admin.php?action=forums&job=manage','Invalid data sent!');
	}

	$delobj = $scache->load('forumtree');
	$delobj->delete();
	if ($type == 'c') {
		$delobj = $scache->load('categories');
		$delobj->delete();
	}
	else{
		$delobj = $scache->load('cat_bid');
		$delobj->delete();
	}
	
	viscacha_header('Location: admin.php?action=forums&job=manage');
}
elseif ($job == 'rights') {
	echo head();
	$id = $gpc->get('id', int);
	if ($id == 0) {
		error('admin.pgp?action=forums&job=manage', 'Forum not found');
	}
	$result = $db->query("SELECT f.*, g.name, g.title FROM {$db->pre}fgroups AS f LEFT JOIN {$db->pre}groups AS g ON g.id = f.gid WHERE f.bid = ".$id." ORDER BY f.gid");
	$cache = array();
	?>
<form name="form" method="post" action="admin.php?action=forums&job=delete_rights&id=<?php echo $id; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="10"><span style="float: right;">[<a href="admin.php?action=forums&job=add_rights&id=<?php echo $id; ?>">Add Usergroup</a>]</span>Forum Permission Manager</td>
  </tr>
  <tr>
  	<td class="ubox" valign="bottom"><b>Delete</b></td>
    <td class="ubox" valign="bottom"><b>Name / Public Title</b></td>
   	<td class="ubox" valign="bottom"><?php txt2img('Download Attachements'); ?></td>
   	<td class="ubox" valign="bottom"><?php txt2img('View Forum'); ?></td>
   	<td class="ubox" valign="bottom"><?php txt2img('Start a new Topic'); ?></td>
   	<td class="ubox" valign="bottom"><?php txt2img('Write a reply'); ?></td>
   	<td class="ubox" valign="bottom"><?php txt2img('Start a Poll'); ?></td>
   	<td class="ubox" valign="bottom"><?php txt2img('Add Attachements'); ?></td>
   	<td class="ubox" valign="bottom"><?php txt2img('Edit own Posts'); ?></td>
   	<td class="ubox" valign="bottom"><?php txt2img('Can vote'); ?></td>
  </tr>
  <?php while ($row = $db->fetch_assoc($result)) { ?>
  <tr>
  	<td class="mbox">
	<input type="checkbox" name="delete[]" value="<?php echo $row['fid']; ?>"></td>
    <td class="mbox">
    <?php
    if ($row['gid'] > 0) {
    	echo $row['name'].' / '.$row['title'];
    } else {
    	echo '<i>Valid for all groups except the groups shown below!</i>';
    }
    $p1 = ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=forums&job=ajax_changeperm&id='.$row['fid'].'&key=';
    $p2 = '\')"';
    ?>
    </td>
   	<td class="mbox"><?php echo noki($row['f_downloadfiles'], $p1.'f_downloadfiles'.$p2); ?></td>
   	<td class="mbox"><?php echo noki($row['f_forum'], $p1.'f_forum'.$p2); ?></td>
   	<td class="mbox"><?php echo noki($row['f_posttopics'], $p1.'f_posttopics'.$p2); ?></td>
   	<td class="mbox"><?php echo noki($row['f_postreplies'], $p1.'f_postreplies'.$p2); ?></td>
   	<td class="mbox"><?php echo noki($row['f_addvotes'], $p1.'f_addvotes'.$p2); ?></td>
   	<td class="mbox"><?php echo noki($row['f_attachments'], $p1.'f_attachments'.$p2); ?></td>
   	<td class="mbox"><?php echo noki($row['f_edit'], $p1.'f_edit'.$p2); ?></td>
   	<td class="mbox"><?php echo noki($row['f_voting'], $p1.'f_voting'.$p2); ?></td>
  </tr>
  <?php } ?>
  <tr> 
   <td class="ubox" width="100%" colspan="10" align="center"><input type="submit" name="Submit" value="Delete"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'ajax_changeperm') {
	$id = $gpc->get('id', int);
	$key = $gpc->get('key', str);
	if(!is_id($id) || empty($key)) {
		die('The id or the key is not valid!');
	}
	$result = $db->query("SELECT f.{$key} FROM {$db->pre}fgroups AS f WHERE f.fid = '{$id}' LIMIT 1");
	$perm = $db->fetch_assoc($result);
	if ($db->num_rows($result) == 0) {
		die('Not found!');
	}
	$perm = invert($perm[$key]);
	$db->query("UPDATE {$db->pre}fgroups AS f SET f.{$key} = '{$perm}' WHERE f.fid = '{$id}' LIMIT 1");
	die(strval($perm));
}
elseif ($job == 'add_rights') {
	echo head();
	$id = $gpc->get('id', int);
	if ($id == 0) {
		error('admin.pgp?action=forums&job=manage', 'Forum not found');
	}
	$result = $db->query("SELECT id, name FROM {$db->pre}groups ORDER BY admin DESC , guest ASC , core ASC");
	$result2 = $db->query("SELECT gid FROM {$db->pre}fgroups WHERE bid = ".$id);
	$cache = array();
	$cache2 = array();
	while ($row = $db->fetch_num($result2)) {
		$cache2[] = $row[0];
	}
	while ($row = $db->fetch_assoc($result)) {
		if (in_array($row['id'],$cache2) == FALSE) {
			$cache[] = $row;
		}
	}
	?>
<form name="form" method="post" action="admin.php?action=forums&job=add_rights2&id=<?php echo $id; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Add a new Usergroup - Settings and Permissions</td>
  </tr>
  <tr> 
   <td class="ubox" colspan="2">Settings:</td>
  </tr>
  <tr>
      <td class="mbox">Use for group(s):<br /><span class="stext">Choose the usergroup (or all groups) which will be affected by the below specified permissions.</span></td>
      <td class="mbox">
      <select name="int1">
      <option value="0">All Groups</option>
      <?php
      foreach($cache as $row) {
      	echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
      }
      ?>
      </select>
      </td>
  </tr>
  <tr> 
   <td class="ubox" colspan="2">Permissions:</td>
  </tr>
  <?php foreach ($glk_forums as $key) { ?>
  <tr>
   <td class="mbox" width="50%"><?php echo $gls[$key]; ?><br /><span class="stext"><?php echo $gll[$key]; ?></span></td>
   <td class="mbox" width="50%"><input type="checkbox" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="1" /></td>
  </tr>
  <?php } ?>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'add_rights2') {
	echo head();

	$id = $gpc->get('id', int);
	$int1 = $gpc->get('int1', int);

	$db->query('SELECT * FROM '.$db->pre.'fgroups WHERE bid = "'.$id.'" AND gid = "'.$int1.'"');
	if ($db->num_rows() > 0) {
		error('admin.php?action=forums&job=rights&id='.$id, 'Für die angegebene Gruppe besteht schon ein Eintrag!');
	}

	// ToDo: Gäste-Limitierungen (kein voten und editieren) beachten!

	$db->query('INSERT INTO '.$db->pre.'fgroups (bid,gid,f_downloadfiles,f_forum,f_posttopics,f_postreplies,f_addvotes,f_attachments,f_edit,f_voting) VALUES ("'.$id.'","'.$int1.'","'.$gpc->get('downloadfiles', int).'","'.$gpc->get('forum', int).'","'.$gpc->get('posttopics', int).'","'.$gpc->get('postreplies', int).'","'.$gpc->get('addvotes', int).'","'.$gpc->get('attachments', int).'","'.$gpc->get('edit', int).'","'.$gpc->get('voting', int).'")');
	if ($db->affected_rows() == 1) {
		ok('admin.php?action=forums&job=rights&id='.$id, 'Data successfully inserted!');
	}
	else {
		error('admin.php?action=forums&job=add_rights&id='.$id, 'There was an error while inserting data!');
	}
}
elseif ($job == 'delete_rights') {
	echo head();
	$id = $gpc->get('id', int);
	if (!is_id($id)) {
		error('admin.pgp?action=forums&job=manage', 'Forum not found');
	}
	$did = $gpc->get('delete', arr_int);
	if (count($did) > 0) {
		$db->query('DELETE FROM '.$db->pre.'fgroups WHERE fid IN('.implode(',',$did).') AND bid = "'.$id.'"');
		$anz = $db->affected_rows();	
		ok('admin.php?action=forums&job=rights&id='.$id, $anz.' entries deleted!');
	}
	else {
		error('admin.php?action=forums&job=rights&id='.$id, 'You have not chosen which entry shall be deleted!');
	}
}
elseif ($job == 'cat_edit') {
	echo head();
	$id = $gpc->get('id', int);
	if (!is_id($id)) {
		error('admin.pgp?action=forums&job=manage', 'Forum not found');
	}
	$result = $db->query('SELECT name, desctxt FROM '.$db->pre.'categories WHERE id = '.$id);
	$row = $db->fetch_assoc($result);

	?>
<form name="form" method="post" action="admin.php?action=forums&amp;job=cat_edit2&amp;id=<?php echo $id; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Edit Category</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Name:</td>
   <td class="mbox" width="50%"><input type="text" name="boardname" size="50" value="<?php echo $row['name']; ?>" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Short Description:<br /><span class="stext">Optional. BB-Codes and HTML is not allowed!</span></td>
   <td class="mbox" width="50%"><textarea name="description" rows="2" cols="50"><?php echo $row['desctxt']; ?></textarea></td> 
  </tr>
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Edit" /></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'cat_edit2') {
	echo head();
	
	$boardname = $gpc->get('boardname', str);
	$description = $gpc->get('description', str);
	$id = $gpc->get('id', int);
	
	if (strlen($boardname) < 2) {
		error('Name ist zu kurz ( < 2 chars)');
	}
	if (strlen($boardname) > 200) {
		error('Name is too long( > 200 chars)');
	}
	
	$db->query("UPDATE {$db->pre}categories SET name = '{$boardname}', desctxt = '{$description}' WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);

	$delobj = $scache->load('categories');
	$delobj->delete();
	$delobj = $scache->load('forumtree');
	$delobj->delete();
	
	ok('admin.php?action=forums&job=manage', 'Category successfully edited!');
}
elseif ($job == 'cat_delete') {
	echo head();
	$id = $gpc->get('id', int);
	
	$result = $db->query('SELECT id FROM '.$db->pre.'cat WHERE cid = '.$id);
	if ($db->num_rows() > 0) {
		error('admin.php?action=forums&job=manage', 'Until you can delete this category, you have to delete all forums this category contains.');
	}
	
	$db->query("DELETE FROM {$db->pre}categories WHERE id = '{$id}' LIMIT 1");

	$delobj = $scache->load('categories');
	$delobj->delete();
	$delobj = $scache->load('forumtree');
	$delobj->delete();

	ok('admin.php?action=forums&job=manage', 'Category successfully deleted!');
}
elseif ($job == 'prefix') {
	$id = $gpc->get('id', int);
	$result = $db->query('SELECT * FROM '.$db->pre.'prefix WHERE bid = "'.$id.'" ORDER BY value');
	echo head();
?>
<form name="form" method="post" action="admin.php?action=forums&job=delete_prefix&id=<?php echo $id; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Manage Prefix</td>
  </tr>
  <tr> 
   <td class="ubox" width="10%">Delete</td>
   <td class="ubox" width="90%">Value</td> 
  </tr>
  <?php while($prefix = $db->fetch_assoc($result)) { ?>
  <tr> 
   <td class="mbox" width="10%"><input type="checkbox" name="delete[]" value="<?php echo $prefix['id']; ?>"></td>
   <td class="mbox" width="90%"><?php echo $prefix['value']; ?></td> 
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Delete"></td> 
  </tr>
 </table>
</form><br />
<form name="form" method="post" action="admin.php?action=forums&job=add_prefix&id=<?php echo $id; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Add Prefix</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Value:</td>
   <td class="mbox" width="50%"><input type="text" name="name" size="50" /></td> 
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Add"></td> 
  </tr>
 </table>
</form> 
<?php
	echo foot();
}
elseif ($job == 'delete_prefix') {
	echo head();
	$id = $gpc->get('id', int);
	$did = $gpc->get('delete', arr_int);
	$did = implode(',', $did);
	$delobj = $scache->load('prefix');
	$delobj->delete();
	$db->query('DELETE FROM '.$db->pre.'prefix WHERE id IN('.$did.') AND bid = "'.$id.'"');
	$i = $db->affected_rows();
	ok('admin.php?action=forums&job=prefix&id='.$id, $i.' values deleted!');
}
elseif ($job == 'add_prefix') {
	echo head();
	$id = $gpc->get('id', int);
	$val = $gpc->get('name', str);
	$delobj = $scache->load('prefix');
	$delobj->delete();
	$result = $db->query('SELECT id FROM '.$db->pre.'prefix WHERE bid= "'.$id.'" AND value = "'.$val.'" LIMIT 1');
	if ($db->num_rows() > 0) {
		error('admin.php?action=forums&job=prefix&id='.$id, 'This value already exists!');
	}
	else {
		$db->query('INSERT INTO '.$db->pre.'prefix (bid, value) VALUES ("'.$id.'", "'.$val.'")');
		ok('admin.php?action=forums&job=prefix&id='.$id, 'Value successfully added!');
	}
}
?>
