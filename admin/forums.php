<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "forums.php") die('Error: Hacking Attempt');

function ForumSubs ($tree, $cat, $board, $char = '+', $level = 0) {
	foreach ($tree as $cid => $boards) {
		$cdata = $cat[$cid];
		?>
		<tr> 
			<td class="mmbox" width="50%"><?php echo str_repeat($char, $level).' <b>'.$cdata['name']; ?></b></td>
			<td class="mmbox" width="10%"><?php echo $cdata['position']; ?>&nbsp;&nbsp;
				<a href="admin.php?action=forums&job=cat_move&id=<?php echo $cdata['id']; ?>&move=-1"><img src="admin/html/images/asc.gif" border="0" alt="Up"></a>&nbsp;
				<a href="admin.php?action=forums&job=cat_move&id=<?php echo $cdata['id']; ?>&move=1"><img src="admin/html/images/desc.gif" border="0" alt="Down"></a>
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
				<td class="mbox" width="10%" align="right"><?php echo $bdata['position']; ?>&nbsp;&nbsp;
				<a href="admin.php?action=forums&job=forum_move&id=<?php echo $bdata['id']; ?>&move=-1"><img src="admin/html/images/asc.gif" border="0" alt="Up"></a>&nbsp;
				<a href="admin.php?action=forums&job=forum_move&id=<?php echo $bdata['id']; ?>&move=1"><img src="admin/html/images/desc.gif" border="0" alt="Down"></a>
				</td>
			   <td class="mbox" width="30%">
				<form name="act" action="admin.php?action=locate" method="post">
			  		<select size="1" name="url" onchange="locate(this.value)">
			  		<option value="" selected="selected">Please choose</option>
					 <optgroup label="General">
					  <option value="admin.php?action=forums&job=forum_edit&id=<?php echo $bdata['id']; ?>">Edit Forum</option>
					  <option value="admin.php?action=forums&job=forum_delete&id=<?php echo $bdata['id']; ?>">Delete Forum</option>
					 </optgroup>
					 <?php if ($bdata['opt'] != 're') { ?>
					 <optgroup label="Permissions">
					  <option value="admin.php?action=forums&job=rights&id=<?php echo $bdata['id']; ?>">Manage Usergroups</option>
					  <option value="admin.php?action=forums&job=rights_add&id=<?php echo $bdata['id']; ?>">Add Usergroup</option>
					 </optgroup>
					 <optgroup label="Prefixes">
					  <option value="admin.php?action=forums&job=prefix&id=<?php echo $bdata['id']; ?>">Manage</option>
					 </optgroup>
					 <optgroup label="Statistics">
					  <option value="admin.php?action=forums&job=forum_recount&id=<?php echo $bdata['id']; ?>">Recount</option>
					 </optgroup>
					 <optgroup label="Moderators">
					  <option value="admin.php?action=forums&job=mods&id=<?php echo $bdata['id']; ?>">Manage</option>
					  <option value="admin.php?action=forums&job=mods_add&id=<?php echo $bdata['id']; ?>">Add</option>
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
if ($job == 'mods_ajax_changeperm') {
	$mid = $gpc->get('mid', int);
	$bid = $gpc->get('bid', int);
	$key = $gpc->get('key', str);
	if(!is_id($mid) || !is_id($bid) || empty($key)) {
		die('The ids or the key is not valid!');
	}
	$result = $db->query("SELECT {$key} FROM {$db->pre}moderators WHERE bid = '{$bid}' AND mid = '{$mid}' LIMIT 1", __LINE__, __FILE__);
	$perm = $db->fetch_assoc($result);
	if ($db->num_rows($result) == 0) {
		die('Not found!');
	}
	$perm = invert($perm[$key]);
	$db->query("UPDATE {$db->pre}moderators SET {$key} = '{$perm}' WHERE bid = '{$bid}' AND mid = '{$mid}' LIMIT 1", __LINE__, __FILE__);
	die(strval($perm));
}
elseif ($job == 'mods') {
	echo head();
	$orderby = $gpc->get('order', str);
	$bid = $gpc->get('id', int);
	
	$colspan = iif($bid > 0, '8', '9');

	$result = $db->query("
	SELECT m.*, u.name as user, c.name as cat, c.id AS cat_id
	FROM {$db->pre}moderators AS m 
		LEFT JOIN {$db->pre}user AS u ON u.id = m.mid 
		LEFT JOIN {$db->pre}forums AS c ON c.id = m.bid 
	".iif($bid > 0, "WHERE m.bid = '{$bid}'")." 
	ORDER BY ".iif($orderby == 'member' || $bid > 0, "u.name, c.name", "c.name, u.name")
	, __LINE__, __FILE__);
	?>
<form name="form" method="post" action="admin.php?action=forums&job=mods_delete<?php echo iif($bid > 0, '&id='.$bid); ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="<?php echo $colspan; ?>"><span style="float: right;">[<a href="admin.php?action=forums&amp;job=mods_add&amp;id=<?php echo $bid; ?>">Add Moderator</a>]</span>Moderator Manager</td>
  </tr>
  <tr class="ubox">
    <td width="5%" rowspan="2">Delete</td>
    <td width="30%" rowspan="2">
    	<?php if ($bid == 0) { ?>
    	<a<?php echo iif($orderby == 'member', ' style="font-weight: bold;"'); ?> href="admin.php?action=forums&job=mods&order=member">
    		Name
    	</a>
    	<?php } else { ?>
    		Name
    	<?php } ?>
    </td>
    <?php if ($bid == 0) { ?>
    <td width="30%" rowspan="2">
    	<a<?php echo iif($orderby != 'member', ' style="font-weight: bold;"'); ?> href="admin.php?action=forums&job=mods&order=board">
    		Forum
    	</a>
    </td>
    <?php } ?>
    <td width="20%" rowspan="2">Period</td>
    <td width="21%" colspan="3" align="center">Status</td>
    <td width="14%" colspan="2" align="center">Topics</td>
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
    $p1 = ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=forums&job=mods_ajax_changeperm&mid='.$row['mid'].'&bid='.$row['bid'].'&key=';
    $p2 = '\')"';
?>
  <tr> 
   <td class="mbox" width="5%" align="center"><input type="checkbox" value="<?php echo $row['mid'].'_'.$row['bid']; ?>" name="delete[]"></td>
   <td class="mbox" width="30%"><?php echo $row['user']; ?></td>
   <?php if ($bid == 0) { ?>
   <td class="mbox" width="30%"><a href="admin.php?action=forums&job=mods&id=<?php echo $row['cat_id']; ?>"><?php echo $row['cat']; ?></a></td>
   <?php } ?>
   <td class="mbox" width="20%"><?php echo $row['time']; ?></td>
   <td class="mbox" width="7%" align="center"><?php echo noki($row['s_rating'], $p1.'s_rating'.$p2); ?></td>
   <td class="mbox" width="7%" align="center"><?php echo noki($row['s_article'], $p1.'s_article'.$p2); ?></td>
   <td class="mbox" width="7%" align="center"><?php echo noki($row['s_news'], $p1.'s_news'.$p2); ?></td>
   <td class="mbox" width="7%" align="center"><?php echo noki($row['p_mc'], $p1.'p_mc'.$p2); ?></td>
   <td class="mbox" width="7%" align="center"><?php echo noki($row['p_delete'], $p1.'p_delete'.$p2); ?></td>
  </tr>
<?php } ?>
  <tr> 
   <td class="ubox" width="100%" colspan="<?php echo $colspan; ?>" align="center"><input type="submit" name="Submit" value="Delete"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'mods_delete') {
	echo head();
	$id = $gpc->get('id', int);
	if (count($gpc->get('delete', none)) > 0) {
		$deleteids = array();
		foreach ($gpc->get('delete', none) as $did) {
			list($mid, $bid) = explode('_',$did);
			$mid = $gpc->save_int($mid);
			$bid = $gpc->save_int($bid);
			$deleteids[] = " (mid = '{$mid}' AND bid = '{$bid}') "; 
		}
		$db->query("DELETE FROM {$db->pre}moderators WHERE ".implode(' OR ',$deleteids), __LINE__, __FILE__);
		$anz = $db->affected_rows();
		$delobj = $scache->load('index-moderators');
		$delobj->delete();
		ok('admin.php?action=forums&job=mods'.iif($id > 0, '&id='.$id), $anz.' entries deleted!');
	}
	else {
		error('admin.php?action=forums&job=mods'.iif($id > 0, '&id='.$id), 'Invalid data sent!');
	}
}
elseif ($job == 'mods_add') {
	echo head();
    $id = $gpc->get('id', int);
	?>
<form name="form" method="post" action="admin.php?action=forums&amp;job=mods_add2">
<?php echo iif(is_id($id), '<input type="hidden" name="id" value="'.$id.'" /><input type="hidden" name="bid" value="'.$id.'" />'); ?>
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Add Moderator</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Forum:</td>
   <td class="mbox" width="50%">
   <?php
	$catbid = $scache->load('cat_bid');
	$boards = $catbid->get();
   	if (!isset($boards[$id]['name'])) {
   		echo SelectBoardStructure('id', ADMIN_SELECT_FORUMS);
    }
   	else {
		echo $boards[$id]['name'];
   	}
   ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Username:</td>
   <td class="mbox" width="50%">
   	<input type="text" name="name" id="name" size="50" onkeyup="ajax_searchmember(this, 'sugg');" /><br />
   	<span class="stext">Suggestions: <span id="sugg">-</span></span>
   </td> 
  </tr>
  <tr>
   <td class="mbox" width="50%">Period:<br />
   <span class="stext">Entering a date here will cause that the moderator has the specified permissions only until the entered date. The moderator will loose his permissions at the specified date at 0 o'clock! This is optional!</span></td>
   <td class="mbox" width="50%">Day: <input type="text" name="day" size="4" />&nbsp;&nbsp;&nbsp;&nbsp;Month: <input type="text" name="month" size="4" />&nbsp;&nbsp;&nbsp;&nbsp;Year: <input type="text" name="weekday" size="6" /></td> 
  </tr>
  <tr>
   <td class="mbox" width="50%">Status: Is allowed to...</td>
   <td class="mbox" width="50%">
   <input type="checkbox" name="ratings" value="1" checked="checked" /> set Ratings (Good, Bad)<br />
   <input type="checkbox" name="news" value="1" /> specify a topic as news<br />
   <input type="checkbox" name="article" value="1" /> specify a topic as article
   </td> 
  </tr>
  <tr>
   <td class="mbox" width="50%">Manage Posts: Is allowed to...</td>
   <td class="mbox" width="50%">
   <input type="checkbox" name="delete" value="1" checked="checked" /> delete posts and topics<br />
   <input type="checkbox" name="move" value="1" checked="checked" /> move posts and topics
   </td> 
  </tr>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Add"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'mods_add2') {
	echo head();
	
    $id = $gpc->get('id', int);
    $bid = $gpc->get('bid', int);
    $temp1 = $gpc->get('name', str);
    $month = $gpc->get('month', int);
    $day = $gpc->get('day', int);
    $weekday = $gpc->get('weekday', int);
	if (!is_id($id)) {
		error('admin.php?action=forums&job=manage', 'Forum or Category was not found on account of an invalid ID.');
	}
	$uid = $db->fetch_num($db->query('SELECT id FROM '.$db->pre.'user WHERE name = "'.$temp1.'" LIMIT 1', __LINE__, __FILE__));
	if ($uid[0] < 1) {
		error('admin.php?action=forums&job=mods_add'.iif($bid > 0, '&id='.$id), 'Member not found!');
	}
	if ($month > 0 && $day > 0 && $weekday > 0) {
		$timestamp = "'".mktime(0, 0, 0, $month, $day, $weekday, -1)."'";
	}
	else {
		$timestamp = 'NULL';
	}
	
	$news = $gpc->get('news', int);
	$article = $gpc->get('article', int);
	$rating = $gpc->get('rating', int);
	$move = $gpc->get('move', int);
	$delete = $gpc->get('delete', int);
	
	$db->query("
	INSERT INTO {$db->pre}moderators (mid, bid, s_rating, s_news, s_article, p_delete, p_mc, time) 
	VALUES ('{$uid[0]}', '{$id}', '{$rating}', '{$news}', '{$article}', '{$delete}', '{$move}', {$timestamp})
	", __LINE__, __FILE__);
	
	if ($db->affected_rows() == 1) {
		$delobj = $scache->load('index-moderators');
		$delobj->delete();
		ok('admin.php?action=forums&job=mods'.iif($bid > 0, '&id='.$id), 'Moderator successfully added!');
	}
	else {
		error('admin.php?action=forums&job=mods'.iif($bid > 0, '&id='.$id), 'Could not insert data into database.');
	}
}
elseif ($job == 'manage') {
	send_nocache_header();
	echo head();
	?>
<table class="border">
  <tr><td class="obox" colspan="3">
  <span style="float: right;">[<a href="admin.php?action=forums&job=cat_add">Add new Category</a>] [<a href="admin.php?action=forums&job=forum_add">Add new Forum</a>]</span>
  Manage Forums &amp; Categories
  </td></tr>
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
elseif ($job == 'forum_delete') {
	echo head();
	$id = $gpc->get('id', int);
	?>
	<table class="border">
	<tr><td class="obox">Delete Forum</td></tr>
	<tr><td class="mbox">
	    <p align="center">Do you really want to delete this forum with all data?</p>
	    <p align="center">
	        <a href="admin.php?action=forums&amp;job=forum_delete2&amp;id=<?php echo $id; ?>">
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
elseif ($job == 'forum_delete2') {
	echo head();
	$id = array();
	$result = $db->query("SELECT id FROM {$db->pre}topics WHERE board = '{$_GET['id']}'", __LINE__, __FILE__);
		if ($db->num_rows($result) > 0) {
		while ($row = $db->fetch_assoc($result)) {
			$id[] = $row['id'];
		}
		$ids = implode(',', $id);
	
		$db->query ("DELETE FROM {$db->pre}replies WHERE board = '{$_GET['id']}'",__LINE__,__FILE__);
		$uresult = $db->query ("SELECT id, source FROM {$db->pre}uploads WHERE topic_id IN({$ids})",__LINE__,__FILE__);
		while ($urow = $db->fetch_assoc($uresult)) {
			$filesystem->unlink('uploads/topics/'.$urow['source']);
			$thumb = 'uploads/topics/thumbnails/'.$urow['id'].get_extension($urow['source'], true);
			if (file_exists($thumb)) {
				$filesystem->unlink($thumb);
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
	$db->query("DELETE FROM {$db->pre}fgroups WHERE bid = '{$_GET['id']}'", __LINE__, __FILE__);
	$db->query("DELETE FROM {$db->pre}moderators WHERE bid = '{$_GET['id']}'", __LINE__, __FILE__);
	$db->query("DELETE FROM {$db->pre}prefix WHERE bid = '{$_GET['id']}'", __LINE__, __FILE__);
	$db->query("DELETE FROM {$db->pre}forums WHERE id = '{$_GET['id']}' LIMIT 1", __LINE__, __FILE__);
	
	$delobj = $scache->load('cat_bid');
	$delobj->delete();
	$delobj = $scache->load('forumtree');
	$delobj->delete();
	$delobj = $scache->load('parent_forums');
	$delobj->delete();
	
	ok('admin.php?action=forums&job=manage', 'Forum successfully deleted!');
}
elseif ($job == 'forum_edit') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}forums WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows($result) == 0) {
		error('admin.php?action=forums&job=manage', 'Invalid ID given');
	}
	$row = $db->fetch_assoc($result);
	?>
<form name="form" method="post" action="admin.php?action=forums&amp;job=forum_edit2&amp;id=<?php echo $id; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">
   <span style="float: right;">
   [<a href="admin.php?action=forums&amp;job=prefix&amp;id=<?php echo $id; ?>">Manage Prefixes</a>] 
   [<a href="admin.php?action=forums&amp;job=mods&amp;id=<?php echo $id; ?>">Manage Moderators</a>] 
   [<a href="admin.php?action=forums&amp;job=rights&amp;id=<?php echo $id; ?>">Manage Permissions</a>] 
   </span>
   Edit a forum
   </td>
  </tr>
  <tr> 
   <td class="mbox" width="45%">Title:</td>
   <td class="mbox" width="55"><input type="text" name="name" size="70" value="<?php echo $row['name']; ?>" /></td> 
  </tr>
  <tr>
   <td class="mbox">Description:<br />
   <span class="stext">
   You can optionally type in a short description for this category.<br />
   HTML is allowed; BB-Code is not allowed!</span></td>
   <td class="mbox"><textarea name="description" rows="3" cols="70"><?php echo $row['description']; ?></textarea></td> 
  </tr>
  <tr>
   <td class="mbox">Parent Category:</td>
   <td class="mbox">
   	<select name="parent" size="1">
   	 <option value="0"<?php echo iif($row['parent'] == '0', ' selected="selected"'); ?>>No one</option>
   	 <?php echo SelectBoardStructure('parent', ADMIN_SELECT_CATEGORIES, $row['parent'], true); ?>
   	</select>
   </td> 
  </tr>
  <tr> 
   <td class="mbox">Forum Link :<br /><span class="stext">Entering a URL here will cause anyone clicking the forum link to be redirected to that URL. You can not specifiy a link if this forum has a password.</span></td>
   <td class="mbox"><input type="text" name="link" size="70" value="<?php echo iif($row['opt'] == 're', $row['optvalue']); ?>" /></td> 
  </tr>
  <tr><td class="ubox" colspan="2">Override global Settings</td></tr>
  <tr> 
   <td class="mbox">Number of Posts per Page:<br /><span class="stext">0 = Use default value (<?php echo $config['topiczahl']; ?>)</span></td>
   <td class="mbox"><input type="text" name="topiczahl" size="5" value="<?php echo $row['topiczahl']; ?>" /></td> 
  </tr>
  <tr> 
   <td class="mbox">Number of Topics per Forumpage:<br /><span class="stext">0 = Use default value (<?php echo $config['forumzahl']; ?>)</span></td>
   <td class="mbox"><input type="text" name="forumzahl" size="5" value="<?php echo $row['forumzahl']; ?>" /></td> 
  </tr>
  <tr><td class="ubox" colspan="2">Moderation Options</td></tr>
  <tr>
   <td class="mbox">Automatically set topic status to:<br /><span class="stext">Selecting &quot;Article&quot; or &quot;News&quot; will cause that topics in this forum will automatically be marked as &quot;Article&quot; or &quot;News&quot;.</span></td>
   <td class="mbox">
    <select name="auto_status" size="1">
     <option value=""<?php echo iif($row['auto_status'] == '', ' selected="selected"'); ?>>Do not set status</option>
     <option value="a"<?php echo iif($row['auto_status'] == 'a', ' selected="selected"'); ?>>Article</option>
     <option value="n"<?php echo iif($row['auto_status'] == 'n', ' selected="selected"'); ?>>News</option>
    </select>
   </td>
  </tr>
  <tr>
   <td class="mbox">Email addresses to notify when there is a new topic:<br />
   <span class="stext">Separate each address with a Newline/Carriage Return => Each address in an own row.</span></td>
   <td class="mbox"><textarea name="topic_notification" rows="2" cols="70"><?php echo $row['topic_notification']; ?></textarea></td> 
  </tr>
  <tr>
   <td class="mbox">Email addresses to notify when there is a new reply:<br />
   <span class="stext">Separate each address with a Newline/Carriage Return => Each address in an own row.</span></td>
   <td class="mbox"><textarea name="reply_notification" rows="2" cols="70"><?php echo $row['reply_notification']; ?></textarea></td> 
  </tr>
  <tr><td class="ubox" colspan="2">Access Options</td></tr>
  <tr> 
   <td class="mbox">Forum Password:<br /><span class="stext">Subforums are protected with this password, too! You can not specifiy a password if this forum is a link.</span></td>
   <td class="mbox"><input type="text" name="pw" size="40" value="<?php echo iif($row['opt'] == 'pw', $row['optvalue']); ?>" /></td> 
  </tr>
  <tr>
   <td class="mbox" rowspan="3">Visibility:</td>
   <td class="mbox">
    <input type="radio" name="invisible" value="0"<?php echo iif($row['invisible'] == '0', ' checked="checked"'); ?> checked="checked" /> Show Forum to everyone<br />
    <span class="stext">Forum will appear on the forum index, but it is locked for users without permission.</span>
   </td></tr><tr><td class="mbox">
    <input type="radio" name="invisible" value="1"<?php echo iif($row['invisible'] == '1', ' checked="checked"'); ?> /> Hide forum from users without authorization<br />
    <span class="stext">Forum will not appear if it is locked and option is checked. This only affects forums without password.</span>
   </td></tr><tr><td class="mbox">
    <input type="radio" name="invisible" value="2"<?php echo iif($row['invisible'] == '2', ' checked="checked"'); ?> /> Hide forum completely<br />
    <span class="stext">Forum will not appear and nobody can access it.</span>
   </td> 
  </tr>
  <tr> 
   <td class="mbox">Forum is read only:<br /><span class="stext">Check this to prevent any new posts being made in this forum.</span></td>
   <td class="mbox"><input type="checkbox" name="readonly" value="1"<?php echo iif($row['readonly'] == '1', ' checked="checked"'); ?> /></td> 
  </tr>
  <tr> 
   <td class="mbox">Show topics in active topic list:<br /><span class="stext">If checked, the topics in this forum will be shown in the active topic lists.</span></td>
   <td class="mbox"><input type="checkbox" name="active_topic" value="1"<?php echo iif($row['active_topic'] == '1', ' checked="checked""'); ?> /></td> 
  </tr>
  <tr><td class="ubox" colspan="2">Forum Rules (Announcement)</td></tr>
  <tr>
   <td class="mbox">Display Method:</td>
   <td class="mbox">
    <select name="message_active" size="1">
     <option value="0"<?php echo iif($row['message_active'] == '0', ' selected="selected"'); ?>>Don't display rules</option>
     <option value="1"<?php echo iif($row['message_active'] == '1', ' selected="selected"'); ?>>Display rules inline</option>
     <option value="2"<?php echo iif($row['message_active'] == '2', ' selected="selected"'); ?>>Display a link to the rules</option>
    </select>
   </td>
  </tr>
  <tr>
   <td class="mbox">Rules Title:</td>
   <td class="mbox"><input type="text" name="message_title" size="70" value="<?php echo $row['message_title']; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox">Rules:<br /><span class="stext">HTML is allowed; BB-Code is not allowed!</span></td>
   <td class="mbox"><textarea name="message_text" rows="4" cols="70"><?php echo $row['message_text']; ?></textarea></td>
  </tr>
  <tr> 
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit" /></td> 
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'forum_edit2') {
	echo head();
	
	$id = $gpc->get('id', int);
	$name = $gpc->get('name', str);
	$description = $gpc->get('description', str);
	$parent = $gpc->get('parent', int);
	$opt_re = $gpc->get('link', str);
	$topiczahl = $gpc->get('topiczahl', int);
	$forumzahl = $gpc->get('forumzahl', int);
	$auto_status = $gpc->get('auto_status', none);
	$topic_notification = $gpc->get('topic_notification', none);
	$reply_notification = $gpc->get('reply_notification', none);
	$opt_pw = $gpc->get('pw', str);
	$invisible = $gpc->get('invisible', int);
	$readonly = $gpc->get('readonly', int);
	$active_topic = $gpc->get('active_topic', int);
	$message_active = $gpc->get('message_active', int);
	$message_title = $gpc->get('message_title', str);
	$message_text = $gpc->get('message_text', str);

	$error = array();
	$result = $db->query("SELECT * FROM {$db->pre}forums WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows($result) == 0) {
		$error[] = 'Invalid ID given';
	}
	$data = $db->fetch_assoc($result);
	if (strlen($name) < 2) {
		$error[] = 'Name is too short (Minimum: 2 characters)';
	}
	if (strlen($name) > 200) {
		$error[] = 'Name is too long (Maximum: 200 characters)';
	}
	if ($message_active > 0 && strlen($message_title) < 2) {
		$error[] = 'Title for Forum Rules is too short (Minimum: 2 characters)';
	}
	if ($message_active > 0 && strlen($message_title) > 200) {
		$error[] = 'Title for Forum Rules is too long (Maximum: 200 characters)';
	}
	if (strlen($opt_re) > 255) {
		$error[] = 'Link is too long (Maximum: 255 characters)';
	}
	$result = $db->query("SELECT id FROM {$db->pre}categories WHERE id = '{$parent}' LIMIT 1");
	if ($db->num_rows($result) != 1) {
		$error[] = 'No valid parent category choosen.';
	}
	if (count($error) > 0) {
		error('admin.php?action=forums&job=forum_edit&id='.$id, $error);
	}
	else {
		if ($message_active < 0 && $message_active > 2) {
			$message_active = 0;
		}
		if ($invisible < 0 && $invisible > 2) {
			$invisible = 0;
		}
		if ($readonly != 0 && $readonly != 1) {
			$readonly = 0;
		}
		if ($active_topic != 0 && $active_topic != 1) {
			$active_topic = 0;
		}
		if ($auto_status != 'n' && $auto_status != 'a') {
			$auto_status = '';
		}
		if ($topiczahl < 0) {
			$topiczahl * -1;
		}
		if ($forumzahl < 0) {
			$forumzahl * -1;
		}
		
		$emails = preg_split('/[\r\n]+/', $reply_notification, -1, PREG_SPLIT_NO_EMPTY);
		$reply_notification = array();
		foreach ($emails as $email) {
			if(check_mail($email)) {
				$reply_notification[] = $email;
			}
		}
		$reply_notification = implode("\n", $reply_notification);
		$emails = preg_split('/[\r\n]+/', $topic_notification, -1, PREG_SPLIT_NO_EMPTY);
		$topic_notification = array();
		foreach ($emails as $email) {
			if(check_mail($email)) {
				$topic_notification[] = $email;
			}
		}
		$topic_notification = implode("\n", $topic_notification);
		
		if (strlen($opt_re) > 0) {
			$opt = 're';
			$optvalue = $opt_re;
			if ($invisible == 1) {
				$invisible = 0;
			}
		}
		elseif (strlen($opt_pw) > 0) {
			$opt = 'pw';
			$optvalue = $opt_pw;
			if ($invisible == 1) {
				$invisible = 0;
			}
		}
		else {
			$opt = '';
			$optvalue = '';	
		}
		
		$db->query("
		UPDATE {$db->pre}forums SET 
		  `name` = '{$name}',
		  `description` = '{$description}',
		  `parent` = '{$parent}',
		  `opt` = '{$opt}',
		  `optvalue` = '{$optvalue}',
		  `forumzahl` = '{$forumzahl}',
		  `topiczahl` = '{$topiczahl}',
		  `invisible` = '{$invisible}',
		  `readonly` = '{$readonly}',
		  `auto_status` = '{$auto_status}',
		  `reply_notification` = '{$reply_notification}',
		  `topic_notification` = '{$topic_notification}',
		  `active_topic` = '{$active_topic}',
		  `message_active` = '{$message_active}',
		  `message_title` = '{$message_title}',
		  `message_text` = '{$message_text}' 
		WHERE id = '{$id}' 
		LIMIT 1;
		", __LINE__, __FILE__);
		
		$delobj = $scache->load('cat_bid');
		$delobj->delete();
		$delobj = $scache->load('forumtree');
		$delobj->delete();
		$delobj = $scache->load('parent_forums');
		$delobj->delete();
		
		ok('admin.php?action=forums&job=manage', 'Forum successfully added!');
	}
}
elseif ($job == 'forum_add') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=forums&job=forum_add2">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Add a new forum</td>
  </tr>
  <tr> 
   <td class="mbox" width="45%">Title:</td>
   <td class="mbox" width="55"><input type="text" name="name" size="70" /></td> 
  </tr>
  <tr>
   <td class="mbox">Description:<br />
   <span class="stext">
   You can optionally type in a short description for this category.<br />
   HTML is allowed; BB-Code is not allowed!</span></td>
   <td class="mbox"><textarea name="description" rows="3" cols="70"></textarea></td> 
  </tr>
  <tr>
   <td class="mbox">Position:</td>
   <td class="mbox">
    <select name="sort_where">
     <option value="-1">Before</option>
     <option value="1" selected="selected">After</option>
    </select>&nbsp;<?php echo SelectBoardStructure('sort', ADMIN_SELECT_FORUMS); ?>
   </td>
  </tr>
  <tr>
   <td class="mbox">Parent Category:</td>
   <td class="mbox">
   	<select name="parent" size="1">
   	 <?php echo SelectBoardStructure('parent', ADMIN_SELECT_CATEGORIES, null, true); ?>
   	</select>
   </td> 
  </tr>
  <tr> 
   <td class="mbox">Forum Link :<br /><span class="stext">Entering a URL here will cause anyone clicking the forum link to be redirected to that URL. You can not specifiy a link if this forum has a password.</span></td>
   <td class="mbox"><input type="text" name="link" size="70" id="dis1" onchange="disable(this)" /></td> 
  </tr>
  <tr><td class="ubox" colspan="2">Override global Settings</td></tr>
  <tr> 
   <td class="mbox">Number of Posts per Page:<br /><span class="stext">0 = Use default value (<?php echo $config['topiczahl']; ?>)</span></td>
   <td class="mbox"><input type="text" name="topiczahl" size="5" value="0" /></td> 
  </tr>
  <tr> 
   <td class="mbox">Number of Topics per Forumpage:<br /><span class="stext">0 = Use default value (<?php echo $config['forumzahl']; ?>)</span></td>
   <td class="mbox"><input type="text" name="forumzahl" size="5" value="0" /></td> 
  </tr>
  <tr><td class="ubox" colspan="2">Moderation Options</td></tr>
  <tr>
   <td class="mbox">Automatically set topic status to:<br /><span class="stext">Selecting &quot;Article&quot; or &quot;News&quot; will cause that topics in this forum will automatically be marked as &quot;Article&quot; or &quot;News&quot;.</span></td>
   <td class="mbox">
    <select name="auto_status" size="1">
     <option value="" selected="selected">Do not set status</option>
     <option value="a">Article</option>
     <option value="n">News</option>
    </select>
   </td>
  </tr>
  <tr>
   <td class="mbox">Email addresses to notify when there is a new topic:<br />
   <span class="stext">Separate each address with a Newline/Carriage Return => Each address in an own row.</span></td>
   <td class="mbox"><textarea name="topic_notification" rows="2" cols="70"></textarea></td> 
  </tr>
  <tr>
   <td class="mbox">Email addresses to notify when there is a new reply:<br />
   <span class="stext">Separate each address with a Newline/Carriage Return => Each address in an own row.</span></td>
   <td class="mbox"><textarea name="reply_notification" rows="2" cols="70"></textarea></td> 
  </tr>
  <tr><td class="ubox" colspan="2">Access Options</td></tr>
  <tr> 
   <td class="mbox">Forum Password:<br /><span class="stext">Subforums are protected with this password, too! You can not specifiy a password if this forum is a link.</span></td>
   <td class="mbox"><input type="text" name="pw" size="40" id="dis2" onchange="disable(this)" /></td> 
  </tr>
  <tr>
   <td class="mbox" rowspan="3">Visibility:</td>
   <td class="mbox">
    <input type="radio" name="invisible" value="0" checked="checked" /> Show Forum to everyone<br />
    <span class="stext">Forum will appear on the forum index, but it is locked for users without permission.</span>
   </td></tr><tr><td class="mbox">
    <input type="radio" name="invisible" value="1" /> Hide forum from users without authorization<br />
    <span class="stext">Forum will not appear if it is locked and option is checked. This only affects forums without password.</span>
   </td></tr><tr><td class="mbox">
    <input type="radio" name="invisible" value="2" /> Hide forum completely<br />
    <span class="stext">Forum will not appear and nobody can access it.</span>
   </td> 
  </tr>
  <tr> 
   <td class="mbox">Forum is read only:<br /><span class="stext">Check this to prevent any new posts being made in this forum.</span></td>
   <td class="mbox"><input type="checkbox" name="readonly" value="1" /></td> 
  </tr>
  <tr> 
   <td class="mbox">Show topics in active topic list:<br /><span class="stext">If checked, the topics in this forum will be shown in the active topic lists.</span></td>
   <td class="mbox"><input type="checkbox" name="active_topic" value="1" checked="checked" /></td> 
  </tr>
  <tr> 
   <td class="mbox">Copy permissions from:<br /><span class="stext">The forum will have the same permissions as the one you select here. If no forum is selected the default settings are used. <em>Caution: This is experimental! Use with care and report bugs, please.</em></span></td>
   <td class="mbox">
	<select name="copypermissions" size="1">
   	 <option value="0" selected="selected">Default</option>
   	 <?php echo SelectBoardStructure('copypermissions', ADMIN_SELECT_FORUMS, null, true); ?>
   	</select>
   </td>
  </tr>
  <tr><td class="ubox" colspan="2">Forum Rules (Announcement)</td></tr>
  <tr>
   <td class="mbox">Display Method:</td>
   <td class="mbox">
    <select name="message_active" size="1">
     <option value="0" selected="selected">Don't display rules</option>
     <option value="1">Display rules inline</option>
     <option value="2">Display a link to the rules</option>
    </select>
   </td>
  </tr>
  <tr>
   <td class="mbox">Rules Title:</td>
   <td class="mbox"><input type="text" name="message_title" size="70" /></td>
  </tr>
  <tr>
   <td class="mbox">Rules:<br /><span class="stext">HTML is allowed; BB-Code is not allowed!</span></td>
   <td class="mbox"><textarea name="message_text" rows="4" cols="70"></textarea></td>
  </tr>
  <tr> 
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit" /></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'forum_add2') {
	echo head();
	
	$name = $gpc->get('name', str);
	$description = $gpc->get('description', str);
	$sortx = $gpc->get('sort_where', int);
	$sort = $gpc->get('sort', int);
	$parent = $gpc->get('parent', int);
	$opt_re = $gpc->get('link', str);
	$topiczahl = $gpc->get('topiczahl', int);
	$forumzahl = $gpc->get('forumzahl', int);
	$auto_status = $gpc->get('auto_status', none);
	$topic_notification = $gpc->get('topic_notification', none);
	$reply_notification = $gpc->get('reply_notification', none);
	$opt_pw = $gpc->get('pw', str);
	$invisible = $gpc->get('invisible', int);
	$readonly = $gpc->get('readonly', int);
	$active_topic = $gpc->get('active_topic', int);
	$perm = $gpc->get('copypermissions', int);
	$message_active = $gpc->get('message_active', int);
	$message_title = $gpc->get('message_title', str);
	$message_text = $gpc->get('message_text', str);

	$error = array();
	if (strlen($name) < 2) {
		$error[] = 'Name is too short (Minimum: 2 characters)';
	}
	if (strlen($name) > 200) {
		$error[] = 'Name is too long (Maximum: 200 characters)';
	}
	if ($message_active > 0 && strlen($message_title) < 2) {
		$error[] = 'Title for Forum Rules is too short (Minimum: 2 characters)';
	}
	if ($message_active > 0 && strlen($message_title) > 200) {
		$error[] = 'Title for Forum Rules is too long (Maximum: 200 characters)';
	}
	if (strlen($opt_re) > 255) {
		$error[] = 'Link is too long (Maximum: 255 characters)';
	}
	$result = $db->query("SELECT id FROM {$db->pre}categories WHERE id = '{$parent}' LIMIT 1");
	if ($db->num_rows($result) != 1) {
		$error[] = 'No valid parent category choosen.';
	}
	if (count($error) > 0) {
		error('admin.php?action=forums&job=forum_add', $error);
	}
	else {
		if ($message_active < 0 && $message_active > 2) {
			$message_active = 0;
		}
		if ($invisible < 0 && $invisible > 2) {
			$invisible = 0;
		}
		if ($readonly != 0 && $readonly != 1) {
			$readonly = 0;
		}
		if ($active_topic != 0 && $active_topic != 1) {
			$active_topic = 0;
		}
		if ($auto_status != 'n' && $auto_status != 'a') {
			$auto_status = '';
		}
		if ($topiczahl < 0) {
			$topiczahl * -1;
		}
		if ($forumzahl < 0) {
			$forumzahl * -1;
		}
		
		$emails = preg_split('/[\r\n]+/', $reply_notification, -1, PREG_SPLIT_NO_EMPTY);
		$reply_notification = array();
		foreach ($emails as $email) {
			if(check_mail($email)) {
				$reply_notification[] = $email;
			}
		}
		$reply_notification = implode("\n", $reply_notification);
		$emails = preg_split('/[\r\n]+/', $topic_notification, -1, PREG_SPLIT_NO_EMPTY);
		$topic_notification = array();
		foreach ($emails as $email) {
			if(check_mail($email)) {
				$topic_notification[] = $email;
			}
		}
		$topic_notification = implode("\n", $topic_notification);
		
		$position = null;
		$positions = array();
		$result = $db->query("SELECT id, position FROM {$db->pre}forums WHERE parent = '{$parent}' ORDER BY position");
		while ($pos = $db->fetch_assoc($result)) {
			if ($pos['id'] == $sort) {
				$position = $pos['position']+$sortx;
			}
			else {
				$positions[$pos['id']] = $pos['position'];
			}
		}
		if ($position == null) {
			if (count($positions) > 0) {
				$position = iif($sortx == 1, max($positions), min($positions));
			}
			else {
				$position = 0;
			}
		}
		else {
			$id = array_search($position, $positions);
			$move = array();
			while (is_id($id)) {
				$move[$id] = $positions[$id]+$sortx;
				$id = array_search($move[$id], $positions);
			}
			if (count($move) > 0) {
				$op = iif($sortx == 1, '+', '-');
				$idlist = implode(',', array_keys($move));
				$db->query("UPDATE {$db->pre}forums SET position = position {$op} 1 WHERE id IN({$idlist})");
			}
		}
		
		if (strlen($opt_re) > 0) {
			$opt = 're';
			$optvalue = $opt_re;
			if ($invisible == 1) {
				$invisible = 0;
			}
		}
		elseif (strlen($opt_pw) > 0) {
			$opt = 'pw';
			$optvalue = $opt_pw;
			if ($invisible == 1) {
				$invisible = 0;
			}
		}
		else {
			$opt = '';
			$optvalue = '';	
		}
		
		$db->query("
		INSERT INTO {$db->pre}forums (
		  `name`,`description`,`parent`,`position`,`opt`,`optvalue`,`forumzahl`,`topiczahl`,`invisible`,`readonly`,
		  `auto_status`,`reply_notification`,`topic_notification`,`active_topic`,`message_active`,`message_title`,`message_text`
		)
		VALUES (
		  '{$name}','{$description}','{$parent}','{$position}','{$opt}','{$optvalue}','{$forumzahl}','{$topiczahl}','{$invisible}','{$readonly}',
		  '{$auto_status}','{$reply_notification}','{$topic_notification}','{$active_topic}','{$message_active}','{$message_title}','{$message_text}'
		);
		", __LINE__, __FILE__);
		$newid = $db->insert_id();
	
		if ($perm > 0) {
			$columns = implode(', ', array_keys($glk_forums));
			$result = $db->query("SELECT {$columns} FROM {$db->pre}fgroups WHERE bid = '{$perm}'", __LINE__, __FILE__);
			while($row = $db->fetch_assoc($result)) {
				ksort($glk_forums, SORT_STRING);
				ksort($row, SORT_STRING);
				$row_str = implode("', '", $row);
				$db->query("INSERT INTO {$db->pre}fgroups ({$columns}, bid) VALUES ('{$row_str}', '{$newid}')", __LINE__, __FILE__);
			}
		}
		
		$delobj = $scache->load('cat_bid');
		$delobj->delete();
		$delobj = $scache->load('forumtree');
		$delobj->delete();
		$delobj = $scache->load('parent_forums');
		$delobj->delete();
		
		ok('admin.php?action=forums&job=manage', 'Forum successfully added!');
	}
}
elseif ($job == 'forum_recount') {
	echo head();
	$id = $gpc->get('id', int);
	if (!is_id($id)) {
		echo head();
		error('admin.php?action=forums&job=manage', 'Forum or Category was not found on account of an invalid ID.');
	}
	UpdateBoardStats($id);
	ok('admin.php?action=forums&job=manage', 'Statistics successfully recounted!');
}
elseif ($job == 'cat_move' || $job == 'forum_move') {
    $id = $gpc->get('id', int);
    $move = $gpc->get('move', int);
    
	if (!is_id($id)) {
		echo head();
		error('admin.php?action=forums&job=manage', 'Forum or Category was not found on account of an invalid ID.');
	}

	$table = iif($job == 'cat_move', "{$db->pre}categories", "{$db->pre}forums");
	$cache = iif($job == 'cat_move', "categories", "cat_bid");
	$op = iif($move == 1, "+", "-");

	$db->query("UPDATE {$table} SET position = position{$op}1 WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);

	$delobj = $scache->load('forumtree');
	$delobj->delete();
	$delobj = $scache->load($cache);
	$delobj->delete();
	
	viscacha_header('Location: admin.php?action=forums&job=manage');
}
elseif ($job == 'rights') {
	echo head();
	$id = $gpc->get('id', int);
	if ($id == 0) {
		error('admin.pgp?action=forums&job=manage', 'Forum not found');
	}
	$result = $db->query("SELECT f.*, g.name, g.title, g.guest, g.core FROM {$db->pre}fgroups AS f LEFT JOIN {$db->pre}groups AS g ON g.id = f.gid WHERE f.bid = '{$id}' ORDER BY f.gid", __LINE__, __FILE__);
	$cache = array();
	$colspan = count($glk_forums)+2;
	?>
<form name="form" method="post" action="admin.php?action=forums&job=rights_delete&id=<?php echo $id; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="<?php echo $colspan; ?>"><span style="float: right;">[<a href="admin.php?action=forums&job=rights_add&id=<?php echo $id; ?>">Add Usergroup</a>]</span>Forum Permission Manager</td>
  </tr>
  <tr>
  	<td class="ubox" valign="bottom"><b>Delete</b></td>
    <td class="ubox" valign="bottom"><b>Name / Public Title</b></td>
    <?php foreach ($glk_forums as $key) { ?>
   	<td class="ubox" valign="bottom" align="center"><?php txt2img($gls[$key]); ?></td>
	<?php } ?>
  </tr>
  <?php
  while ($row = $db->fetch_assoc($result)) {
  	$guest = ($row['guest'] == 1 && $row['core'] == 1);
  ?>
  <tr class="mbox">
  	<td><input type="checkbox" name="delete[]" value="<?php echo $row['fid']; ?>"></td>
    <td>
    <?php
    if ($row['gid'] > 0) {
    	echo $row['name'].' / '.$row['title'];
    }
    else {
    	echo '<i>Valid for all groups except the groups shown below!</i>';
    }

    ?>
    </td>
    <?php
    foreach ($glk_forums as $key) {
    	$clickable = !($guest && in_array($key, $guest_limitation));
	   	$js = iif ($clickable, 
	   			' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=forums&job=rights_ajax_changeperm&id='.$row['fid'].'&key='.$key.'\')"',
	   			' style="-moz-opacity: 0.4; opacity: 0.4; filter:Alpha(opacity=40, finishopacity=0);"'
	   		  );
    ?>
   	<td align="center"<?php echo iif(!$clickable, ' class="mmbox"'); ?>><?php echo noki($row["f_{$key}"], $js); ?></td>
   	<?php } ?>
  </tr>
  <?php } ?>
  <tr> 
   <td class="ubox" width="100%" colspan="<?php echo $colspan; ?>" align="center"><input type="submit" name="Submit" value="Delete"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'rights_ajax_changeperm') {
	$id = $gpc->get('id', int);
	$key = $gpc->get('key', str);
	if(!is_id($id) || !in_array($key, $glk_forums)) {
		die('The id or the key is not valid!');
	}
	$result = $db->query("SELECT `f_{$key}` AS `{$key}`, gid FROM {$db->pre}fgroups WHERE fid = '{$id}' LIMIT 1", __LINE__, __FILE__);
	$perm = $db->fetch_assoc($result);
	if ($db->num_rows($result) == 0) {
		die('Not found!');
	}
	if (in_array($key, $guest_limitation)) {
		$result = $db->query("SELECT id FROM {$db->pre}groups WHERE guest = '1' LIMIT 1");
		$row = $db->fetch_assoc($result);
		if ($perm['gid'] == $row['id']) {
			die('Guests can not vote or edit posts!');
		}
	}
	$perm = invert($perm[$key]);
	$db->query("UPDATE {$db->pre}fgroups SET `f_{$key}` = '{$perm}' WHERE fid = '{$id}' LIMIT 1", __LINE__, __FILE__);
	die(strval($perm));
}
elseif ($job == 'rights_add') {
	echo head();
	$id = $gpc->get('id', int);
	if ($id == 0) {
		error('admin.pgp?action=forums&job=manage', 'Forum not found');
	}
	$result = $db->query("SELECT id, name FROM {$db->pre}groups ORDER BY admin DESC , guest ASC , core ASC", __LINE__, __FILE__);
	$result2 = $db->query("SELECT gid FROM {$db->pre}fgroups WHERE bid = '{$id}'", __LINE__, __FILE__);
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
<form name="form" method="post" action="admin.php?action=forums&job=rights_add2&id=<?php echo $id; ?>">
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
      <select name="group">
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
   <td class="mbox" width="50%"><?php echo $gls[$key]; ?><br /><span class="stext"><?php echo $gll[$key].iif(in_array($key, $guest_limitation), ' (Guests are not allowed to do this!)'); ?></span></td>
   <td class="mbox" width="50%"><input type="checkbox" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="1" /></td>
  </tr>
  <?php } ?>
  <tr> 
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Add" /></td> 
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'rights_add2') {
	echo head();

	$id = $gpc->get('id', int);
	$group = $gpc->get('group', int);

	$db->query("SELECT * FROM {$db->pre}fgroups WHERE bid = '{$id}' AND gid = '{$group}'", __LINE__, __FILE__);
	if ($db->num_rows() > 0) {
		error('admin.php?action=forums&job=rights&id='.$id, 'Für die angegebene Gruppe besteht schon ein Eintrag!');
	}
	
	$result = $db->query("SELECT id FROM {$db->pre}groups WHERE guest = '1' LIMIT 1");
	$row = $db->fetch_assoc($result);
	if ($group == $row['id']) {
		$edit = 0;
		$voting = 0;
	}
	else {
		$edit = $gpc->get('edit', int);
		$voting = $gpc->get('voting', int);
	}
	$downloadfiles = $gpc->get('downloadfiles', int);
	$forum = $gpc->get('forum', int);
	$posttopics = $gpc->get('posttopics', int);
	$postreplies = $gpc->get('postreplies', int);
	$addvotes = $gpc->get('addvotes', int);
	$attachments = $gpc->get('attachments', int);

	$db->query("
	INSERT INTO {$db->pre}fgroups (bid,gid,f_downloadfiles,f_forum,f_posttopics,f_postreplies,f_addvotes,f_attachments,f_edit,f_voting) 
	VALUES ('{$id}','{$group}','{$downloadfiles}','{$forum}','{$posttopics}','{$postreplies}','{$addvotes}','{$attachments}','{$edit}','{$voting}')
	", __LINE__, __FILE__);
	if ($db->affected_rows() == 1) {
		ok('admin.php?action=forums&job=rights&id='.$id, 'Data successfully inserted!');
	}
	else {
		error('admin.php?action=forums&job=rights_add&id='.$id, 'There was an error while inserting data!');
	}
}
elseif ($job == 'rights_delete') {
	echo head();
	$id = $gpc->get('id', int);
	if (!is_id($id)) {
		error('admin.pgp?action=forums&job=manage', 'Forum not found');
	}
	$did = $gpc->get('delete', arr_int);
	if (count($did) > 0) {
		$db->query('DELETE FROM '.$db->pre.'fgroups WHERE fid IN('.implode(',',$did).') AND bid = "'.$id.'"', __LINE__, __FILE__);
		$anz = $db->affected_rows();	
		ok('admin.php?action=forums&job=rights&id='.$id, $anz.' entries deleted!');
	}
	else {
		error('admin.php?action=forums&job=rights&id='.$id, 'You have not chosen which entry shall be deleted!');
	}
}
elseif ($job == 'cat_add') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=forums&job=cat_add2">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Add Category</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Name:<br />
   <span class="stext">Maximum: 200 characters</span>
   </td>
   <td class="mbox" width="50%"><input type="text" name="name" size="50" /></td> 
  </tr>
  <tr>
   <td class="mbox" width="50%">Description:<br />
   <span class="stext">
   You can optionally type in a short description for this category.<br />
   HTML is allowed; BB-Code is not allowed!</span></td>
   <td class="mbox" width="50%"><textarea name="description" rows="2" cols="50"></textarea></td> 
  </tr>
  <tr>
   <td class="mbox" width="50%">Position:</td>
   <td class="mbox" width="50%">
    <select name="sort_where">
     <option value="-1">Before</option>
     <option value="1" selected="selected">After</option>
    </select>&nbsp;<?php echo SelectBoardStructure('sort', ADMIN_SELECT_CATEGORIES); ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Parent Forum:</td>
   <td class="mbox" width="50%">
   	<select name="parent" size="1">
   	 <option value="0" selected="selected">No one</option>
   	 <?php echo SelectBoardStructure('parent', ADMIN_SELECT_FORUMS, null, true); ?>
   	</select>
   </td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Add" /></td> 
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'cat_add2') {
	echo head();
	
	$sort = $gpc->get('sort', int);
	$sortx = $gpc->get('sort_where', int);
	$parent = $gpc->get('parent', int);
	$name = $gpc->get('name', str);
	$description = $gpc->get('description', str);
	$position = null;
	
	if (strlen($name) < 2) {
		error('admin.php?action=forums&job=cat_add', 'Name is too short (Minimum: 2 characters)');
	}
	elseif (strlen($name) > 200) {
		error('admin.php?action=forums&job=cat_add', 'Name is too long (Maximum: 200 characters)');
	}

	$positions = array();
	$result = $db->query("SELECT id, position FROM {$db->pre}categories WHERE parent = '{$parent}' ORDER BY position");
	while ($pos = $db->fetch_assoc($result)) {
		if ($pos['id'] == $sort) {
			$position = $pos['position']+$sortx;
		}
		else {
			$positions[$pos['id']] = $pos['position'];
		}
	}
	if ($position == null) {
		if (count($positions) > 0) {
			$position = iif($sortx == 1, max($positions), min($positions));
		}
		else {
			$position = 0;
		}
	}
	else {
		$id = array_search($position, $positions);
		$move = array();
		while (is_id($id)) {
			$move[$id] = $positions[$id]+$sortx;
			$id = array_search($move[$id], $positions);
		}
		if (count($move) > 0) {
			$op = iif($sortx == 1, '+', '-');
			$idlist = implode(',', array_keys($move));
			$db->query("UPDATE {$db->pre}categories SET position = position {$op} 1 WHERE id IN({$idlist})");
		}
	}
	
	$db->query("
	INSERT INTO {$db->pre}categories (name, description, position, parent) 
	VALUES ('{$name}', '{$description}', '{$position}', '{$parent}')
	", __LINE__, __FILE__);

	$delobj = $scache->load('categories');
	$delobj->delete();
	$delobj = $scache->load('forumtree');
	$delobj->delete();

	ok('admin.php?action=forums&job=manage', 'Category successfully created!');
}
elseif ($job == 'cat_edit') {
	echo head();
	$id = $gpc->get('id', int);

	$result = $db->query("SELECT id, name, description, parent FROM {$db->pre}categories WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows($result) == 0) {
		error('admin.pgp?action=forums&job=manage', 'Category not found');
	}
	$row = $gpc->prepare($db->fetch_assoc($result));
	?>
<form name="form" method="post" action="admin.php?action=forums&job=cat_edit2&id=<?php echo $row['id']; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Edit Category</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Name:<br />
   <span class="stext">Maximum: 200 characters</span>
   </td>
   <td class="mbox" width="50%"><input type="text" name="name" size="50" value="<?php echo $row['name']; ?>" /></td> 
  </tr>
  <tr>
   <td class="mbox" width="50%">Description:<br />
   <span class="stext">
   You can optionally type in a short description for this category.<br />
   HTML is allowed; BB-Code is not allowed!</span></td>
   <td class="mbox" width="50%"><textarea name="description" rows="2" cols="50"><?php echo $row['description']; ?></textarea></td> 
  </tr>
  <tr>
   <td class="mbox" width="50%">Parent Forum:</td>
   <td class="mbox" width="50%">
   	<select name="parent" size="1">
   	 <option value="0"<?php echo iif($row['parent'] == '0', ' selected="selected"'); ?>>No one</option>
   	 <?php echo SelectBoardStructure('parent', ADMIN_SELECT_FORUMS, $row['parent'], true); ?>
   	</select>
   </td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Add" /></td> 
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'cat_edit2') {
	echo head();
	
	$id = $gpc->get('id', int);
	$parent = $gpc->get('parent', int);
	$name = $gpc->get('name', str);
	$description = $gpc->get('description', str);

	$parent_notice = false;
	if ($parent > 0) {
		$subs = array();
		$parent_forums = $scache->load('parent_forums');
		$parents = $parent_forums->get();
		$result = $db->query("SELECT parent FROM {$db->pre}categories WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
		$row = $db->fetch_assoc($result);
		foreach ($parents as $id => $p_arr) {
			array_shift($p_arr);
			if (in_array($row['parent'], $p_arr)) {
				$subs[] = $id;
			}
		}
		if (in_array($parent, $subs)) {
			$parent_notice = true;
			$parent = $row['parent'];
		}
	}

	if (strlen($name) < 2) {
		error('admin.php?action=forums&job=cat_edit&id='.$id, 'Name is too short (Minimum: 2 characters)');
	}
	elseif (strlen($name) > 200) {
		error('admin.php?action=forums&job=cat_edit&id='.$id, 'Name is too long (Maximum: 200 characters)');
	}
	
	$db->query("UPDATE {$db->pre}categories SET name = '{$name}', description = '{$description}', parent = '{$parent}' WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);

	$delobj = $scache->load('categories');
	$delobj->delete();
	$delobj = $scache->load('forumtree');
	$delobj->delete();
	
	if ($parent_notice == false) {
		ok('admin.php?action=forums&job=manage', 'Category successfully edited!');
	}
	else{
		error('admin.php?action=forums&job=manage', 'Category successfully edited, but the parent forum was not changed, because you had specified a subforum of this category.');
	}
}
elseif ($job == 'cat_delete') {
	echo head();
	$id = $gpc->get('id', int);
	
	$result = $db->query("SELECT id FROM {$db->pre}forums WHERE parent = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows() > 0) {
		error('admin.php?action=forums&job=manage', 'Until you can delete this category, you have to delete all forums this category contains.');
	}
	
	$db->query("DELETE FROM {$db->pre}categories WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);

	$delobj = $scache->load('categories');
	$delobj->delete();
	$delobj = $scache->load('forumtree');
	$delobj->delete();

	ok('admin.php?action=forums&job=manage', 'Category successfully deleted!');
}
elseif ($job == 'prefix') {
	echo head();
	$id = $gpc->get('id', int);
	if (!is_id($id)) {
		error('admin.php?action=forums&job=manage', 'Invalid ID given.');
	}
	$result = $db->query('SELECT * FROM '.$db->pre.'prefix WHERE bid = "'.$id.'" ORDER BY value', __LINE__, __FILE__);
?>
<form name="form" method="post" action="admin.php?action=forums&job=prefix_delete&id=<?php echo $id; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="3">Manage Prefixes</td>
  </tr>
  <tr> 
   <td class="ubox" width="10%">Delete</td>
   <td class="ubox" width="70%">Value</td> 
   <td class="ubox" width="20%">Standard</td> 
  </tr>
  <?php
  $has_standard = false;
  while($prefix = $db->fetch_assoc($result)) {
  	if ($prefix['standard'] == 1) {
  		$has_standard = true;
  	}
  ?>
  <tr> 
   <td class="mbox" width="10%"><input type="checkbox" name="delete[]" value="<?php echo $prefix['id']; ?>"></td>
   <td class="mbox" width="70%"><a href="admin.php?action=forums&amp;job=prefix_edit&amp;id=<?php echo $prefix['id']; ?>"><?php echo $prefix['value']; ?></a></td>
   <td class="mbox" width="20%" align="center"><?php echo noki($prefix['standard']); ?></td> 
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="3" align="center"><input type="submit" name="Submit" value="Delete"></td> 
  </tr>
 </table>
</form><br />
<form name="form" method="post" action="admin.php?action=forums&job=prefix_add&id=<?php echo $id; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Add Prefix</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Value:</td>
   <td class="mbox" width="50%"><input type="text" name="name" size="50" /></td> 
  </tr>
<?php if ($has_standard == false) { ?>
  <tr> 
   <td class="mbox" width="50%">Standard:</td>
   <td class="mbox" width="50%"><input type="checkbox" name="standard" value="1" /></td> 
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Add"></td> 
  </tr>
 </table>
</form> 
<?php
	echo foot();
}
elseif ($job == 'prefix_edit') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}prefix WHERE id = '{$id}'", __LINE__, __FILE__);
	$row = $db->fetch_assoc($result);
?>
<form name="form" method="post" action="admin.php?action=forums&job=prefix_edit2&id=<?php echo $id; ?>">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Edit Prefix</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Value:</td>
   <td class="mbox" width="50%"><input type="text" name="name" size="50" value="<?php echo htmlspecialchars($row['value']); ?>" /></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Standard:<br /><span class="stext">If another prefix is standard in this category, the status will be removed.</span></td>
   <td class="mbox" width="50%"><input type="checkbox" name="standard" value="1" <?php echo iif($row['standard'] == 1, ' checked="checked"'); ?> /></td> 
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Edit"></td> 
  </tr>
 </table>
</form> 
<?php
	echo foot();
}
elseif ($job == 'prefix_edit2') {
	echo head();
	$id = $gpc->get('id', int);
	$val = $gpc->get('name', str);
	$standard = $gpc->get('standard', int);
	
	$result = $db->query('SELECT bid, standard FROM '.$db->pre.'prefix WHERE id = "'.$id.'"', __LINE__, __FILE__);
	$row = $db->fetch_assoc($result);

	$result = $db->query('SELECT id FROM '.$db->pre.'prefix WHERE bid = "'.$row['bid'].'" AND value = "'.$val.'" AND id != "'.$id.'" LIMIT 1', __LINE__, __FILE__);
	if ($db->num_rows() > 0) {
		error('admin.php?action=forums&job=prefix&id='.$id, 'This value already exists!');
	}
	else {
		if ($row['standard'] != $standard && $standard == 1) {
			$db->query("UPDATE {$db->pre}prefix SET standard = '0' WHERE standard = '1' AND bid = '{$row['bid']}' LIMIT 1", __LINE__, __FILE__);
		}
		$db->query("UPDATE {$db->pre}prefix SET value = '{$val}', standard = '{$standard}' WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
		$delobj = $scache->load('prefix');
		$delobj->delete();
		ok('admin.php?action=forums&job=prefix&id='.$row['bid'], 'Prefix successfully edited!');
	}
}
elseif ($job == 'prefix_delete') {
	echo head();
	$id = $gpc->get('id', int);
	$did = $gpc->get('delete', arr_int);
	$did = implode(',', $did);
	$delobj = $scache->load('prefix');
	$delobj->delete();
	$db->query('DELETE FROM '.$db->pre.'prefix WHERE id IN('.$did.') AND bid = "'.$id.'"', __LINE__, __FILE__);
	$i = $db->affected_rows();
	ok('admin.php?action=forums&job=prefix&id='.$id, $i.' prefixes deleted!');
}
elseif ($job == 'prefix_add') {
	echo head();
	$id = $gpc->get('id', int);
	$val = $gpc->get('name', str);
	$standard = $gpc->get('standard', int);
	$result = $db->query('SELECT id FROM '.$db->pre.'prefix WHERE bid = "'.$id.'" AND value = "'.$val.'" LIMIT 1', __LINE__, __FILE__);
	if ($db->num_rows() > 0) {
		error('admin.php?action=forums&job=prefix&id='.$id, 'This value already exists!');
	}
	else {
		$db->query("INSERT INTO {$db->pre}prefix (bid, value, standard) VALUES ('{$id}', '{$val}', '{$standard}')", __LINE__, __FILE__);
		$delobj = $scache->load('prefix');
		$delobj->delete();
		ok('admin.php?action=forums&job=prefix&id='.$id, 'Prefix successfully added!');
	}
}
?>
