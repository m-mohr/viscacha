<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// AM: MultiLangAdmin
$lang->group("admin/forums");

function ForumSubs ($tree, $cat, $board, $char = '+', $level = 0) {
	global $lang;
	foreach ($tree as $cid => $boards) {
		$cdata = $cat[$cid];
		?>
		<tr>
			<td class="mmbox" width="50%"><?php echo str_repeat($char, $level).' <b>'.$cdata['name']; ?></b></td>
			<td class="mmbox" width="10%"><?php echo $cdata['position']; ?>&nbsp;&nbsp;
				<a href="admin.php?action=forums&job=cat_move&id=<?php echo $cdata['id']; ?>&move=-1"><img src="admin/html/images/asc.gif" border="0" alt="<?php echo $lang->phrase('admin_forum_up'); ?>"></a>&nbsp;
				<a href="admin.php?action=forums&job=cat_move&id=<?php echo $cdata['id']; ?>&move=1"><img src="admin/html/images/desc.gif" border="0" alt="<?php echo $lang->phrase('admin_forum_down'); ?>"></a>
			</td>
			<td class="mmbox" width="30%">
			  <form name="act" action="admin.php?action=locate" method="post">
			  	<select size="1" name="url" onchange="locate(this.value)">
			  	<option value="" selected="selected"><?php echo $lang->phrase('admin_forum_please_choose'); ?></option>
				 <optgroup label="<?php echo $lang->phrase('admin_forum_label_general'); ?>">
				  <option value="admin.php?action=forums&job=cat_edit&id=<?php echo $cdata['id']; ?>"><?php echo $lang->phrase('admin_forum_edit_category'); ?></option>
				  <option value="admin.php?action=forums&job=cat_delete&id=<?php echo $cdata['id']; ?>"><?php echo $lang->phrase('admin_forum_delete_category'); ?></option>
				 </optgroup>
				</select>
				<input type="submit" value="<?php echo $lang->phrase('admin_forum_form_go'); ?>">
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
				<a href="admin.php?action=forums&job=forum_move&id=<?php echo $bdata['id']; ?>&move=-1"><img src="admin/html/images/asc.gif" border="0" alt="<?php echo $lang->phrase('admin_forum_up'); ?>"></a>&nbsp;
				<a href="admin.php?action=forums&job=forum_move&id=<?php echo $bdata['id']; ?>&move=1"><img src="admin/html/images/desc.gif" border="0" alt="<?php echo $lang->phrase('admin_forum_down'); ?>"></a>
				</td>
			   <td class="mbox" width="30%">
				<form name="act" action="admin.php?action=locate" method="post">
			  		<select size="1" name="url" onchange="locate(this.value)">
			  		<option value="" selected="selected"><?php echo $lang->phrase('admin_forum_please_choose'); ?></option>
					 <optgroup label="<?php echo $lang->phrase('admin_forum_label_general'); ?>">
					  <option value="admin.php?action=forums&job=forum_edit&id=<?php echo $bdata['id']; ?>"><?php echo $lang->phrase('admin_forum_edit_forum'); ?></option>
					  <option value="admin.php?action=forums&job=forum_delete&id=<?php echo $bdata['id']; ?>"><?php echo $lang->phrase('admin_forum_delete_forum'); ?></option>
					 </optgroup>
					 <?php if ($bdata['opt'] != 're') { ?>
					 <optgroup label="<?php echo $lang->phrase('admin_forum_label_permissions'); ?>">
					  <option value="admin.php?action=forums&job=rights&id=<?php echo $bdata['id']; ?>"><?php echo $lang->phrase('admin_forum_manage_usergroups'); ?></option>
					  <option value="admin.php?action=forums&job=rights_add&id=<?php echo $bdata['id']; ?>"><?php echo $lang->phrase('admin_forum_add_usergroup'); ?></option>
					 </optgroup>
					 <optgroup label="<?php echo $lang->phrase('admin_forum_label_prefixes'); ?>">
					  <option value="admin.php?action=forums&job=prefix&id=<?php echo $bdata['id']; ?>"><?php echo $lang->phrase('admin_forum_manage'); ?></option>
					 </optgroup>
					 <optgroup label="<?php echo $lang->phrase('admin_forum_label_statistics'); ?>">
					  <option value="admin.php?action=forums&job=forum_recount&id=<?php echo $bdata['id']; ?>"><?php echo $lang->phrase('admin_forum_recount'); ?></option>
					 </optgroup>
					 <optgroup label="<?php echo $lang->phrase('admin_forum_label_moderators'); ?>">
					  <option value="admin.php?action=forums&job=mods&id=<?php echo $bdata['id']; ?>"><?php echo $lang->phrase('admin_forum_manage'); ?></option>
					  <option value="admin.php?action=forums&job=mods_add&id=<?php echo $bdata['id']; ?>"><?php echo $lang->phrase('admin_forum_add'); ?></option>
					 </optgroup>
					 <?php } ?>
					</select>
					<input type="submit" value="<?php echo $lang->phrase('admin_forum_form_go'); ?>" />
				</form>
			   </td>
			  </tr>
			<?php
			ForumSubs($sub, $cat, $board, $char, $level+2);
		}
	}
}

($code = $plugins->load('admin_forums_jobs')) ? eval($code) : null;

if ($job == 'mods_ajax_changeperm') {
	$mid = $gpc->get('mid', int);
	$bid = $gpc->get('bid', int);
	$key = $gpc->get('key', str);
	if(!is_id($mid) || !is_id($bid) || empty($key)) {
		die($lang->phrase('admin_forum_key_not_valid'));
	}
	$result = $db->query("SELECT {$key} FROM {$db->pre}moderators WHERE bid = '{$bid}' AND mid = '{$mid}' LIMIT 1");
	$perm = $db->fetch_assoc($result);
	if ($db->num_rows($result) == 0) {
		die($lang->phrase('admin_forum_not_found'));
	}
	$perm = invert($perm[$key]);
	$db->query("UPDATE {$db->pre}moderators SET {$key} = '{$perm}' WHERE bid = '{$bid}' AND mid = '{$mid}' LIMIT 1");
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
	);
	?>
<form name="form" method="post" action="admin.php?action=forums&job=mods_delete<?php echo iif($bid > 0, '&id='.$bid); ?>">
  <table class="border">
	<tr>
	  <td class="obox" colspan="<?php echo $colspan; ?>"><span style="float: right;"><a class="button" href="admin.php?action=forums&amp;job=mods_add&amp;id=<?php echo $bid; ?>"><?php echo $lang->phrase('admin_forum_add_moderator'); ?></a></span><?php echo $lang->phrase('admin_forum_moderator_manager'); ?></td>
	</tr>
	<tr class="ubox">
	  <td width="5%" rowspan="2"><?php echo $lang->phrase('admin_forum_delete'); ?><br />
		  <span class="stext">
		  <input type="checkbox" onClick="check_all(this);" name="all" value="delete[]" />
		  <?php echo $lang->phrase('admin_forum_all'); ?></span></td>
	  <td width="30%" rowspan="2"><?php if ($bid == 0) { ?>
		  <a<?php echo iif($orderby == 'member', ' style="font-weight: bold;"'); ?> href="admin.php?action=forums&job=mods&order=member"><?php echo $lang->phrase('admin_forum_order_by_name'); ?></a>
		  <?php } else { echo $lang->phrase('admin_forum_order_by_name'); } ?>
	  </td>
	  <?php if ($bid == 0) { ?>
	  <td width="30%" rowspan="2"><a<?php echo iif($orderby != 'member', ' style="font-weight: bold;"'); ?> href="admin.php?action=forums&job=mods&order=board"><?php echo $lang->phrase('admin_forum_order_by_forum'); ?></a> </td>
	  <?php } ?>
	  <td width="20%" rowspan="2"><?php echo $lang->phrase('admin_forum_period'); ?></td>
	  <td width="21%" colspan="3" align="center"><?php echo $lang->phrase('admin_forum_status'); ?></td>
	  <td width="14%" colspan="2" align="center"><?php echo $lang->phrase('admin_forum_topic'); ?></td>
	</tr>
	<tr class="ubox">
	  <td width="7%"><?php echo $lang->phrase('admin_forum_rating'); ?></td>
	  <td width="7%"><?php echo $lang->phrase('admin_forum_articles'); ?></td>
	  <td width="7%"><?php echo $lang->phrase('admin_forum_news'); ?></td>
	  <td width="7%"><?php echo $lang->phrase('admin_forum_move'); ?></td>
	  <td width="7%"><?php echo $lang->phrase('admin_forum_delete'); ?></td>
	</tr>
	<?php
	while ($row = $db->fetch_assoc($result)) {
	if ($row['time'] > -1) {
		$row['time'] = $lang->phrase('admin_forum_until').gmdate('M d, Y',times($row['time']));
	}
	else {
		$row['time'] = '<em>'.$lang->phrase('admin_forum_no_restriction').'</em>';
	}
	$p1 = ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=forums&job=mods_ajax_changeperm&mid='.$row['mid'].'&bid='.$row['bid'].'&key=';
	$p2 = '\')"';
?>
	<tr>
	  <td class="mbox" width="5%" align="center"><input type="checkbox" value="<?php echo $row['mid'].'_'.$row['bid']; ?>" name="delete[]"></td>
	  <td class="mbox" width="30%"><a href="admin.php?action=members&amp;job=edit&amp;id=<?php echo $row['mid']; ?>"><?php echo $row['user']; ?></a></td>
	  <?php if ($bid == 0) { ?>
	  <td class="mbox" width="30%"><a href="admin.php?action=forums&amp;job=mods&id=<?php echo $row['cat_id']; ?>"><?php echo $row['cat']; ?></a></td>
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
	  <td class="ubox" width="100%" colspan="<?php echo $colspan; ?>" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_forum_form_delete'); ?>"></td>
	</tr>
  </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'mods_delete') {
	echo head();
	$id = $gpc->get('id', int);
	$del = $gpc->get('delete', arr_none);
	$deleteids = array();

	foreach ($del as $did) {
		list($mid, $bid) = explode('_', $did);
		$mid = $gpc->save_int($mid);
		$bid = $gpc->save_int($bid);
		$deleteids[] = " (mid = '{$mid}' AND bid = '{$bid}') ";
	}
	if (count($deleteids) > 0) {
		$db->query("DELETE FROM {$db->pre}moderators WHERE ".implode(' OR ',$deleteids));
		$anz = $db->affected_rows();
		$delobj = $scache->load('index_moderators');
		$delobj->delete();
	}
	else {
		$anz = 0;
	}
	if ($anz > 0) {
		ok('admin.php?action=forums&job=mods'.iif($id > 0, '&id='.$id), $lang->phrase('admin_forum_entries_deleted'));
	}
	else {
		error('admin.php?action=forums&job=mods'.iif($id > 0, '&id='.$id), $lang->phrase('admin_forum_invalid_data_sent'));

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
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_forum_add_moderator'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_forum'); ?></td>
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
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_username'); ?></td>
   <td class="mbox" width="50%">
   	<input type="text" name="name" id="name" size="50" onblur="ajax_searchmember(this, 'sugg')" onkeyup="ajax_searchmember(this, 'sugg', key(event));" /><br />
   	<span class="stext"><?php echo $lang->phrase('admin_forum_sugestions'); ?> <span id="sugg">-</span></span>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_period'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_forum_valid_until'); ?></span></td>
   <td class="mbox" width="50%">
   	<?php echo $lang->phrase('admin_forum_day'); ?> <input type="text" name="day" size="4" />&nbsp;&nbsp;&nbsp;&nbsp;
   	<?php echo $lang->phrase('admin_forum_month'); ?> <input type="text" name="month" size="4" />&nbsp;&nbsp;&nbsp;&nbsp;
   	<?php echo $lang->phrase('admin_forum_year'); ?> <input type="text" name="weekday" size="6" />
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_status_allowed_to'); ?></td>
   <td class="mbox" width="50%">
   <input type="checkbox" name="ratings" value="1" checked="checked" /> <?php echo $lang->phrase('admin_forum_set_ratings'); ?><br />
   <input type="checkbox" name="news" value="1" checked="checked" /> <?php echo $lang->phrase('admin_forum_topic_news'); ?><br />
   <input type="checkbox" name="article" value="1" checked="checked" /> <?php echo $lang->phrase('admin_forum_topic_article'); ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_manage_posts'); ?></td>
   <td class="mbox" width="50%">
   <input type="checkbox" name="delete" value="1" checked="checked" /> <?php echo $lang->phrase('admin_forum_delete_topics'); ?><br />
   <input type="checkbox" name="move" value="1" checked="checked" /> <?php echo $lang->phrase('admin_forum_move_topics'); ?>
   </td>
  </tr>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_forum_add'); ?>"></td>
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
		error('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_not_found_id'));
	}
	$uid = $db->fetch_num($db->query('SELECT id FROM '.$db->pre.'user WHERE name = "'.$temp1.'" LIMIT 1'));
	if ($uid[0] < 1) {
		error('admin.php?action=forums&job=mods_add'.iif($bid > 0, '&id='.$id), $lang->phrase('admin_forum_member_not_found'));
	}
	if ($month > 0 && $day > 0 && $weekday > 0) {
		$timestamp = "'".times(gmmktime(0, 0, 0, $month, $day, $weekday, -1))."'";
	}
	else {
		$timestamp = 'NULL';
	}

	$news = $gpc->get('news', int);
	$article = $gpc->get('article', int);
	$rating = $gpc->get('ratings', int);
	$move = $gpc->get('move', int);
	$delete = $gpc->get('delete', int);

	$db->query("
	INSERT INTO {$db->pre}moderators (mid, bid, s_rating, s_news, s_article, p_delete, p_mc, time)
	VALUES ('{$uid[0]}', '{$id}', '{$rating}', '{$news}', '{$article}', '{$delete}', '{$move}', {$timestamp})
	");

	if ($db->affected_rows() == 1) {
		$delobj = $scache->load('index_moderators');
		$delobj->delete();
		ok('admin.php?action=forums&job=mods'.iif($bid > 0, '&id='.$id), $lang->phrase('admin_forum_moderator_added'));
	}
	else {
		error('admin.php?action=forums&job=mods'.iif($bid > 0, '&id='.$id), $lang->phrase('admin_forum_not_insert_database'));
	}
}
elseif ($job == 'manage') {
	send_nocache_header();
	echo head();
	?>
<table class="border">
  <tr>
	<td class="obox" colspan="3">
  <span style="float: right;"><a class="button" href="admin.php?action=forums&job=cat_add"><?php echo $lang->phrase('admin_forum_new_categroy'); ?></a> <a class="button" href="admin.php?action=forums&job=forum_add"><?php echo $lang->phrase('admin_forum_new_forum'); ?></a></span>
  <?php echo $lang->phrase('admin_forum_infomanage'); ?>
  </td>
  </tr>
  <tr>
	<td class="ubox" width="50%"><b><?php echo $lang->phrase('admin_forum_head_title'); ?></b></td>
	<td class="ubox" width="20%"><b><?php echo $lang->phrase('admin_forum_ordering'); ?></b></td>
	<td class="ubox" width="30%"><b><?php echo $lang->phrase('admin_forum_action'); ?></b></td>
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
	$result = $db->query("SELECT id, name FROM {$db->pre}forums WHERE id = '{$id}' LIMIT 1");
	if ($db->num_rows($result) == 0) {
		error('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_invalid_id'));
	}
	$forum = $db->fetch_assoc($result);
	?>
	<table class="border">
	<tr><td class="obox"><?php echo $lang->phrase('admin_forum_delete_forum'); ?></td></tr>
	<tr><td class="mbox">
		<p align="center"><?php echo $lang->phrase('admin_forum_really_delete_data'); ?></p>
		<p align="center">
			<a href="admin.php?action=forums&amp;job=forum_delete2&amp;id=<?php echo $id; ?>">
				<img alt="<?php echo $lang->phrase('admin_forum_yes'); ?>" border="0" src="admin/html/images/yes.gif" /> <?php echo $lang->phrase('admin_forum_yes'); ?>
			</a>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="javascript: history.back(-1);">
				<img border="0" alt="<?php echo $lang->phrase('admin_forum_no'); ?>" src="admin/html/images/no.gif" /> <?php echo $lang->phrase('admin_forum_no'); ?>
			</a>
		</p>
	</td></tr>
	</table>
	<?php
	echo foot();
}
elseif ($job == 'forum_delete2') {
	echo head();

	$result = $db->query("SELECT id FROM {$db->pre}forums WHERE id = '{$id}' LIMIT 1");
	if ($db->num_rows($result) == 0) {
		error('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_invalid_id'));
	}

	$id = array();
	$result = $db->query("SELECT id FROM {$db->pre}topics WHERE board = '{$_GET['id']}'");
	if ($db->num_rows($result) > 0) {
		while ($row = $db->fetch_assoc($result)) {
			$id[] = $row['id'];
		}
		$ids = implode(',', $id);

		$db->query ("DELETE FROM {$db->pre}replies WHERE board = '{$_GET['id']}'");
		$uresult = $db->query ("SELECT id, source FROM {$db->pre}uploads WHERE topic_id IN({$ids})");
		while ($urow = $db->fetch_assoc($uresult)) {
			$filesystem->unlink('uploads/topics/'.$urow['source']);
			$thumb = 'uploads/topics/thumbnails/'.$urow['id'].get_extension($urow['source'], true);
			if (file_exists($thumb)) {
				$filesystem->unlink($thumb);
			}
		}
		$db->query ("DELETE FROM {$db->pre}uploads WHERE topic_id IN({$ids})");
		$db->query ("DELETE FROM {$db->pre}postratings WHERE tid IN({$ids})");
		$db->query ("DELETE FROM {$db->pre}abos WHERE tid IN({$ids})");
		$db->query ("DELETE FROM {$db->pre}topics WHERE board = '{$_GET['id']}'");
		$votes = $db->query("SELECT id FROM {$db->pre}vote WHERE tid IN({$ids})");
		$voteaids = array();
		while ($row = $db->fetch_num($votes)) {
			$voteaids[] = $row[0];
		}
		if (count($voteaids) > 0) {
			$db->query ("DELETE FROM {$db->pre}votes WHERE id IN(".implode(',', $voteaids).")");
		}
		$db->query ("DELETE FROM {$db->pre}vote WHERE tid IN({$ids})");
	}
	$db->query("DELETE FROM {$db->pre}fgroups WHERE bid = '{$_GET['id']}'");
	$db->query("DELETE FROM {$db->pre}moderators WHERE bid = '{$_GET['id']}'");
	$db->query("DELETE FROM {$db->pre}prefix WHERE bid = '{$_GET['id']}'");
	$db->query("DELETE FROM {$db->pre}forums WHERE id = '{$_GET['id']}' LIMIT 1");

	$delobj = $scache->load('cat_bid');
	$delobj->delete();
	$delobj = $scache->load('fgroups');
	$delobj->delete();
	$delobj = $scache->load('forumtree');
	$delobj->delete();
	$delobj = $scache->load('parent_forums');
	$delobj->delete();
	$delobj = $scache->load('prefix');
	$delobj->delete();

	ok('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_forum_deleted'));
}
elseif ($job == 'forum_edit') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}forums WHERE id = '{$id}' LIMIT 1");
	if ($db->num_rows($result) == 0) {
		error('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_invalid_id'));
	}
	$row = $db->fetch_assoc($result);
	?>
<form name="form" method="post" action="admin.php?action=forums&amp;job=forum_edit2&amp;id=<?php echo $id; ?>">
 <table class="border">
  <tr>
   <td class="obox" colspan="2">
   <span style="float: right;">
   <a class="button" href="admin.php?action=forums&amp;job=prefix&amp;id=<?php echo $id; ?>"><?php echo $lang->phrase('admin_forum_manage_prefixes'); ?></a>
   <a class="button" href="admin.php?action=forums&amp;job=mods&amp;id=<?php echo $id; ?>"><?php echo $lang->phrase('admin_forum_manage_moderators'); ?></a>
   <a class="button" href="admin.php?action=forums&amp;job=rights&amp;id=<?php echo $id; ?>"><?php echo $lang->phrase('admin_forum_manage_permissions'); ?></a>
   </span>
   <?php echo $lang->phrase('admin_forum_forum_settings'); ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="45%"><?php echo $lang->phrase('admin_forum_title'); ?></td>
   <td class="mbox" width="55%"><input type="text" name="name" size="70" value="<?php echo $row['name']; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_description'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_forum_info_short_description'); ?><br /><?php echo $lang->phrase('admin_forum_html_bbcode'); ?></span></td>
   <td class="mbox"><textarea name="description" rows="3" cols="70"><?php echo $row['description']; ?></textarea></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_parent_category'); ?></td>
   <td class="mbox">
   	<select name="parent" size="1">
   	 <?php echo SelectBoardStructure('parent', ADMIN_SELECT_CATEGORIES, $row['parent'], true, 'b_'.$id); ?>
   	</select>
   </td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_forum_link'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_forum_link_help'); ?></span></td>
   <td class="mbox"><input type="text" name="link" size="70" value="<?php echo iif($row['opt'] == 're', $row['optvalue']); ?>" /></td>
  </tr>
  <tr><td class="ubox" colspan="2"><?php echo $lang->phrase('admin_forum_override_settings'); ?></td></tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_number_posts'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_default_value'); ?> (<?php echo $config['topiczahl']; ?>)</span></td>
   <td class="mbox"><input type="text" name="topiczahl" size="5" value="<?php echo $row['topiczahl']; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_number_topics'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_default_value'); ?> (<?php echo $config['forumzahl']; ?>)</span></td>
   <td class="mbox"><input type="text" name="forumzahl" size="5" value="<?php echo $row['forumzahl']; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_po_title'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_po_desc').$lang->phrase('admin_forum_po_'.iif($config['post_order'] == 1, 'new', 'old')); ?></span></td>
   <td class="mbox">
    <select name="post_order">
     <option value="-1"<?php echo iif($row['post_order'] == -1, ' selected="selected"'); ?>><?php echo $lang->phrase('admin_forum_po_default'); ?></option>
     <option value="0"<?php echo iif($row['post_order'] == 0, ' selected="selected"'); ?>><?php echo $lang->phrase('admin_forum_po_old'); ?></option>
     <option value="1"<?php echo iif($row['post_order'] == 1, ' selected="selected"'); ?>><?php echo $lang->phrase('admin_forum_po_new'); ?></option>
    </select>
   </td>
  </tr>
  <tr><td class="ubox" colspan="2"><?php echo $lang->phrase('admin_forum_moderation_options'); ?></td></tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_automatic_status'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_info_topic_status'); ?></span></td>
   <td class="mbox">
	<select name="auto_status" size="1">
	 <option value=""<?php echo iif($row['auto_status'] == '', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_forum_no_status'); ?></option>
	 <option value="a"<?php echo iif($row['auto_status'] == 'a', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_forum_article'); ?></option>
	 <option value="n"<?php echo iif($row['auto_status'] == 'n', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_forum_news'); ?></option>
	</select>
   </td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_email_topic'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_forum_info_separate_address'); ?></span></td>
   <td class="mbox"><textarea name="topic_notification" rows="2" cols="70"><?php echo $row['topic_notification']; ?></textarea></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_email_reply'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_forum_info_separate_address'); ?></span></td>
   <td class="mbox"><textarea name="reply_notification" rows="2" cols="70"><?php echo $row['reply_notification']; ?></textarea></td>
  </tr>
  <tr><td class="ubox" colspan="2"><?php echo $lang->phrase('admin_forum_access_options'); ?></td></tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_forum_password'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_info_subforums_protected'); ?></span></td>
   <td class="mbox"><input type="text" name="pw" size="40" value="<?php echo iif($row['opt'] == 'pw', $row['optvalue']); ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" rowspan="3"><?php echo $lang->phrase('admin_forum_visibility'); ?></td>
   <td class="mbox">
	<input type="radio" name="invisible" value="0"<?php echo iif($row['invisible'] == '0', ' checked="checked"'); ?> checked="checked" /> <?php echo $lang->phrase('admin_forum_show_forum_everyone'); ?><br />
	<span class="stext"><?php echo $lang->phrase('admin_forum_forum_shown_locked'); ?></span>
   </td></tr><tr><td class="mbox">
	<input type="radio" name="invisible" value="1"<?php echo iif($row['invisible'] == '1', ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_forum_select_hide_forum_from_users_without_authorization'); ?><br />
	<span class="stext"><?php echo $lang->phrase('admin_forum_forum_not_shown_locked'); ?></span>
   </td></tr><tr><td class="mbox">
	<input type="radio" name="invisible" value="2"<?php echo iif($row['invisible'] == '2', ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_forum_select_hide_forum_completly'); ?><br />
	<span class="stext"><?php echo $lang->phrase('admin_forum_info_forum_access'); ?></span>
   </td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_read_only'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_prevent_new_posts'); ?></span></td>
   <td class="mbox"><input type="checkbox" name="readonly" value="1"<?php echo iif($row['readonly'] == '1', ' checked="checked"'); ?> /></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_active_topics'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_active_topic_list'); ?></span></td>
   <td class="mbox"><input type="checkbox" name="active_topic" value="1"<?php echo iif($row['active_topic'] == '1', ' checked="checked""'); ?> /></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_posts_count_user'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_count_posts_user_post'); ?> <a href="admin.php?action=members&amp;job=recount" target="_blank"><?php echo $lang->phrase('admin_forum_recount_post_manually'); ?></a>.</span></td>
   <td class="mbox"><input type="checkbox" name="count_posts" value="1"<?php echo iif($row['count_posts'] == '1', ' checked="checked""'); ?> /></td>
  </tr>
  <tr><td class="ubox" colspan="2"><?php echo $lang->phrase('admin_forum_forum_rules'); ?></td></tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_display_method'); ?></td>
   <td class="mbox">
	<select name="message_active" size="1">
	 <option value="0"<?php echo iif($row['message_active'] == '0', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_forum_dont_display_rules'); ?></option>
	 <option value="1"<?php echo iif($row['message_active'] == '1', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_forum_inline_rules'); ?></option>
	 <option value="2"<?php echo iif($row['message_active'] == '2', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_forum_link_rules'); ?></option>
	</select>
   </td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_rules_title'); ?></td>
   <td class="mbox"><input type="text" name="message_title" size="70" value="<?php echo $row['message_title']; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_rules'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_bbcode_html'); ?></span></td>
   <td class="mbox"><textarea name="message_text" rows="4" cols="70"><?php echo $row['message_text']; ?></textarea></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_forum_form_submit'); ?>" /></td>
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
	$description = $gpc->get('description', db_esc);
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
	$count_posts = $gpc->get('count_posts', int);
	$message_active = $gpc->get('message_active', int);
	$message_title = $gpc->get('message_title', str);
	$message_text = $gpc->get('message_text', str);
	$post_order = $gpc->get('post_order', int);

	$error = array();
	$result = $db->query("SELECT * FROM {$db->pre}forums WHERE id = '{$id}' LIMIT 1");
	if ($db->num_rows($result) == 0) {
		$error[] = $lang->phrase('admin_forum_invalid_id');
	}
	$data = $db->fetch_assoc($result);
	if (strlen($name) < 2) {
		$error[] = $lang->phrase('admin_forum_name_short');
	}
	if (strlen($name) > 200) {
		$error[] = $lang->phrase('admin_forum_name_long');
	}
	if ($message_active > 0 && strlen($message_title) < 2) {
		$error[] = $lang->phrase('admin_forum_rules_title_short');
	}
	if ($message_active > 0 && strlen($message_title) > 200) {
		$error[] = $lang->phrase('admin_forum_rules_title_long');
	}
	if (strlen($opt_re) > 255) {
		$error[] = $lang->phrase('admin_forum_link_too_long');
	}
	$result = $db->query("SELECT id FROM {$db->pre}categories WHERE id = '{$parent}' LIMIT 1");
	if ($db->num_rows($result) != 1) {
		$error[] = $lang->phrase('admin_forum_parent_cat_invalid');
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
		if ($count_posts != 0 && $count_posts != 1) {
			$count_posts = 1;
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
		if ($post_order != 0 && $post_order != 1) {
			$post_order = -1;
		}

		$emails = preg_split('/[\r\n]+/', $reply_notification, -1, PREG_SPLIT_NO_EMPTY);
		$reply_notification = array();
		foreach ($emails as $email) {
			if(check_mail($email, true)) {
				$reply_notification[] = $email;
			}
		}
		$reply_notification = implode("\n", $reply_notification);
		$emails = preg_split('/[\r\n]+/', $topic_notification, -1, PREG_SPLIT_NO_EMPTY);
		$topic_notification = array();
		foreach ($emails as $email) {
			if(check_mail($email, true)) {
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
		  `count_posts` = '{$count_posts}',
		  `message_active` = '{$message_active}',
		  `message_title` = '{$message_title}',
		  `message_text` = '{$message_text}',
		  `post_order` = '{$post_order}'
		WHERE id = '{$id}'
		");

		$delobj = $scache->load('cat_bid');
		$delobj->delete();
		$delobj = $scache->load('forumtree');
		$delobj->delete();
		$delobj = $scache->load('parent_forums');
		$delobj->delete();

		ok('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_successfully_added'));
	}
}
elseif ($job == 'forum_add') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=forums&job=forum_add2">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_forum_add_forum'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="45%"><?php echo $lang->phrase('admin_forum_title'); ?></td>
   <td class="mbox" width="55"><input type="text" name="name" size="70" /></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_description'); ?><br />
   <span class="stext">
   <?php echo $lang->phrase('admin_forum_info_short_description'); ?><br />
   <?php echo $lang->phrase('admin_forum_html_bbcode'); ?></span></td>
   <td class="mbox"><textarea name="description" rows="3" cols="70"></textarea></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_position'); ?></td>
   <td class="mbox">
	<select name="sort_where">
	 <option value="-1"><?php echo $lang->phrase('admin_forum_before'); ?></option>
	 <option value="1" selected="selected"><?php echo $lang->phrase('admin_forum_after'); ?></option>
	</select>&nbsp;<?php echo SelectBoardStructure('sort', ADMIN_SELECT_FORUMS); ?>
   </td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_parent_category'); ?></td>
   <td class="mbox">
   	<select name="parent" size="1">
   	 <?php echo SelectBoardStructure('parent', ADMIN_SELECT_CATEGORIES, null, true); ?>
   	</select>
   </td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_forum_link'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_forum_link_help'); ?></span></td>
   <td class="mbox"><input type="text" name="link" size="70" id="dis1" onchange="disable(this)" /></td>
  </tr>
  <tr><td class="ubox" colspan="2"><?php echo $lang->phrase('admin_forum_override_settings'); ?></td></tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_number_posts'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_default_value'); ?> (<?php echo $config['topiczahl']; ?>)</span></td>
   <td class="mbox"><input type="text" name="topiczahl" size="5" value="0" /></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_number_topics'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_default_value'); ?> (<?php echo $config['forumzahl']; ?>)</span></td>
   <td class="mbox"><input type="text" name="forumzahl" size="5" value="0" /></td>
  </tr>
  <tr><td class="ubox" colspan="2"><?php echo $lang->phrase('admin_forum_moderation_options'); ?></td></tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_po_title'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_po_desc').$lang->phrase('admin_forum_po_'.iif($config['post_order'] == 1, 'new', 'old')); ?></span></td>
   <td class="mbox">
    <select name="post_order">
     <option value="-1"><?php echo $lang->phrase('admin_forum_po_default'); ?></option>
     <option value="0"><?php echo $lang->phrase('admin_forum_po_old'); ?></option>
     <option value="1"><?php echo $lang->phrase('admin_forum_po_new'); ?></option>
    </select>
   </td>
  </tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_email_topic'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_forum_info_separate_address'); ?></span></td>
   <td class="mbox"><textarea name="topic_notification" rows="2" cols="70"></textarea></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_email_reply'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_forum_info_separate_address'); ?></span></td>
   <td class="mbox"><textarea name="reply_notification" rows="2" cols="70"></textarea></td>
  </tr>
  <tr><td class="ubox" colspan="2"><?php echo $lang->phrase('admin_forum_access_options'); ?></td></tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_forum_password'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_info_subforums_protected'); ?></span></td>
   <td class="mbox"><input type="text" name="pw" size="40" id="dis2" onchange="disable(this)" /></td>
  </tr>
  <tr>
   <td class="mbox" rowspan="3"><?php echo $lang->phrase('admin_forum_visibility'); ?></td>
   <td class="mbox">
	<input type="radio" name="invisible" value="0" checked="checked" /> <?php echo $lang->phrase('admin_forum_show_forum_everyone'); ?><br />
	<span class="stext"><?php echo $lang->phrase('admin_forum_info_forum_permission'); ?></span>
   </td></tr><tr><td class="mbox">
	<input type="radio" name="invisible" value="1" /> <?php echo $lang->phrase('admin_forum_hide_forum_authorization'); ?><br />
	<span class="stext"><?php echo $lang->phrase('admin_forum_forum_not_shown_locked'); ?></span>
   </td></tr><tr><td class="mbox">
	<input type="radio" name="invisible" value="2" /> <?php echo $lang->phrase('admin_forum_select_hide_forum_completly'); ?><br />
	<span class="stext"><?php echo $lang->phrase('admin_forum_info_forum_access'); ?></span>
   </td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_read_only'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_prevent_new_posts'); ?></span></td>
   <td class="mbox"><input type="checkbox" name="readonly" value="1" /></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_active_topics'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_info_checked_active_topic'); ?></span></td>
   <td class="mbox"><input type="checkbox" name="active_topic" value="1" checked="checked" /></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_posts_count_user'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_count_posts_user_post'); ?></span></td>
   <td class="mbox"><input type="checkbox" name="count_posts" value="1" checked="checked" /></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_copy_permissions'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_info_permissions_copy'); ?> (<em><?php echo $lang->phrase('admin_forum_experimental'); ?></em>)</span></td>
   <td class="mbox">
	<select name="copypermissions" size="1">
   	 <option value="0" selected="selected"><?php echo $lang->phrase('admin_forum_default'); ?></option>
   	 <?php echo SelectBoardStructure('copypermissions', ADMIN_SELECT_FORUMS, null, true); ?>
   	</select>
   </td>
  </tr>
  <tr><td class="ubox" colspan="2"><?php echo $lang->phrase('admin_forum_forum_rules'); ?></td></tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_display_method'); ?></td>
   <td class="mbox">
	<select name="message_active" size="1">
	 <option value="0" selected="selected"><?php echo $lang->phrase('admin_forum_dont_display_rules'); ?></option>
	 <option value="1"><?php echo $lang->phrase('admin_forum_inline_rules'); ?></option>
	 <option value="2"><?php echo $lang->phrase('admin_forum_link_rules'); ?></option>
	</select>
   </td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_rules_title'); ?></td>
   <td class="mbox"><input type="text" name="message_title" size="70" /></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_rules'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_bbcode_html'); ?></span></td>
   <td class="mbox"><textarea name="message_text" rows="4" cols="70"></textarea></td>
  </tr>
  <tr><td class="ubox" colspan="2"><?php echo $lang->phrase('admin_forum_head_prefixes'); ?></td></tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_forum_prefixes'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_info_prefix'); ?></span></td>
   <td class="mbox"><textarea name="prefix" rows="3" cols="70"></textarea></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_forum_form_submit'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'forum_add2') {
	echo head();

	$name = $gpc->get('name', str);
	$description = $gpc->get('description', db_esc);
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
	$count_posts = $gpc->get('count_posts', int);
	$perm = $gpc->get('copypermissions', int);
	$message_active = $gpc->get('message_active', int);
	$message_title = $gpc->get('message_title', str);
	$message_text = $gpc->get('message_text', str);
	$prefix = $gpc->get('prefix', none);
	$post_order = $gpc->get('post_order', int);

	$error = array();
	if (strlen($name) < 2) {
		$error[] = $lang->phrase('admin_forum_name_short');
	}
	if (strlen($name) > 200) {
		$error[] = $lang->phrase('admin_forum_name_long');
	}
	if ($message_active > 0 && strlen($message_title) < 2) {
		$error[] = $lang->phrase('admin_forum_rules_title_short');
	}
	if ($message_active > 0 && strlen($message_title) > 200) {
		$error[] = $lang->phrase('admin_forum_rules_title_long');
	}
	if (strlen($opt_re) > 255) {
		$error[] = $lang->phrase('admin_forum_link_too_long');
	}
	$result = $db->query("SELECT id FROM {$db->pre}categories WHERE id = '{$parent}' LIMIT 1");
	if ($db->num_rows($result) != 1) {
		$error[] = $lang->phrase('admin_forum_parent_cat_invalid');
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
		if ($count_posts != 0 && $count_posts != 1) {
			$count_posts = 1;
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
		if ($post_order != 0 && $post_order != 1) {
			$post_order = -1;
		}

		$emails = preg_split('/[\r\n]+/', $reply_notification, -1, PREG_SPLIT_NO_EMPTY);
		$reply_notification = array();
		foreach ($emails as $email) {
			if(check_mail($email, true)) {
				$reply_notification[] = $email;
			}
		}
		$reply_notification = implode("\n", $reply_notification);
		$emails = preg_split('/[\r\n]+/', $topic_notification, -1, PREG_SPLIT_NO_EMPTY);
		$topic_notification = array();
		foreach ($emails as $email) {
			if(check_mail($email, true)) {
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
		  `name`,`description`,`parent`,`position`,`opt`,`optvalue`,`forumzahl`,`topiczahl`,`invisible`,`readonly`,`count_posts`,
		  `auto_status`,`reply_notification`,`topic_notification`,`active_topic`,`message_active`,`message_title`,`message_text`,`post_order`
		) VALUES (
		  '{$name}','{$description}','{$parent}','{$position}','{$opt}','{$optvalue}','{$forumzahl}','{$topiczahl}','{$invisible}','{$readonly}','{$count_posts}',
		  '{$auto_status}','{$reply_notification}','{$topic_notification}','{$active_topic}','{$message_active}','{$message_title}','{$message_text}','{$post_order}'
		)
		");
		$newid = $db->insert_id();

		if ($perm > 0) {
			$columns = implode(', ', array_keys($glk_forums));
			$result = $db->query("SELECT {$columns}, gid FROM {$db->pre}fgroups WHERE bid = '{$perm}'");
			while($row = $db->fetch_assoc($result)) {
				$gid = $row['gid'];
				unset($row['gid']);
				ksort($glk_forums, SORT_STRING);
				ksort($row, SORT_STRING);
				$row_str = implode("', '", $row);
				$db->query("INSERT INTO {$db->pre}fgroups ({$columns}, bid, gid) VALUES ('{$row_str}', '{$newid}', '{$gid}')");
			}
		}
		$prefixes = preg_split('/[\r\n]+/', $prefix, -1, PREG_SPLIT_NO_EMPTY);
		if (count($prefixes) > 0) {
			$sql_values = array();
			foreach ($prefixes as $p) {
				$sql_values[] = "('{$newid}', '{$p}')";
			}
			$db->query("INSERT INTO {$db->pre}prefix (bid,value) VALUES ".implode(', ', $sql_values));
		}

		$delobj = $scache->load('cat_bid');
		$delobj->delete();
		$delobj = $scache->load('fgroups');
		$delobj->delete();
		$delobj = $scache->load('forumtree');
		$delobj->delete();
		$delobj = $scache->load('parent_forums');
		$delobj->delete();
		$delobj = $scache->load('prefix');
		$delobj->delete();

		ok('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_successfully_added'));
	}
}
elseif ($job == 'forum_recount') {
	echo head();
	$id = $gpc->get('id', int);
	if (!is_id($id)) {
		echo head();
		error('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_not_found_id'));
	}
	UpdateBoardStats($id);
	ok('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_stats_recounted'));
}
elseif ($job == 'cat_move' || $job == 'forum_move') {
	$id = $gpc->get('id', int);
	$move = $gpc->get('move', int);

	if (!is_id($id)) {
		echo head();
		error('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_not_found_id'));
	}

	$table = iif($job == 'cat_move', "{$db->pre}categories", "{$db->pre}forums");
	$cache = iif($job == 'cat_move', "categories", "cat_bid");
	$op = iif($move == 1, "+", "-");

	$db->query("UPDATE {$table} SET position = position{$op}1 WHERE id = '{$id}' LIMIT 1");

	$delobj = $scache->load('forumtree');
	$delobj->delete();
	$delobj = $scache->load($cache);
	$delobj->delete();

	sendStatusCode(307, $config['furl'].'/admin.php?action=forums&job=manage');
}
elseif ($job == 'rights') {
	echo head();
	$id = $gpc->get('id', int);
	if ($id == 0) {
		error('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_not_found_id'));
	}
	$result = $db->query("SELECT f.*, g.name, g.title, g.guest, g.core FROM {$db->pre}fgroups AS f LEFT JOIN {$db->pre}groups AS g ON g.id = f.gid WHERE f.bid = '{$id}' ORDER BY f.gid");
	$cache = array();
	$colspan = count($glk_forums)+2;
	?>
<form name="form" method="post" action="admin.php?action=forums&job=rights_delete&id=<?php echo $id; ?>">
 <table class="border">
  <tr>
   <td class="obox" colspan="<?php echo $colspan; ?>"><span style="float: right;"><a class="button" href="admin.php?action=forums&job=rights_add&id=<?php echo $id; ?>"><?php echo $lang->phrase('admin_forum_add_usergroup'); ?></a></span><?php echo $lang->phrase('admin_forum_permission_manager'); ?></td>
  </tr>
  <tr>
  	<td class="ubox" valign="bottom"><b><?php echo $lang->phrase('admin_forum_delete'); ?></b><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /> <?php echo $lang->phrase('admin_forum_all'); ?></span></td>
	<td class="ubox" valign="bottom"><b><?php echo $lang->phrase('admin_forum_name_public_title'); ?></b></td>
	<?php foreach ($glk_forums as $key) { ?>
   	<td class="ubox" valign="bottom" align="center">
   		<img src="images.php?action=textimage&amp;text=<?php echo rawurlencode($gls[$key]); ?>&amp;angle=90&amp;bg=<?php echo $txt2img_bg; ?>&amp;fg=<?php echo $txt2img_fg; ?>" border="0">
   	</td>
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
   <td class="ubox" width="100%" colspan="<?php echo $colspan; ?>" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_forum_form_delete'); ?>"></td>
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
		die($lang->phrase('admin_forum_invalid_id'));
	}
	$result = $db->query("SELECT `f_{$key}` AS `{$key}`, gid FROM {$db->pre}fgroups WHERE fid = '{$id}' LIMIT 1");
	$perm = $db->fetch_assoc($result);
	if ($db->num_rows($result) == 0) {
		die($lang->phrase('admin_forum_not_found'));
	}
	if (in_array($key, $guest_limitation)) {
		$result = $db->query("SELECT id FROM {$db->pre}groups WHERE guest = '1' LIMIT 1");
		$row = $db->fetch_assoc($result);
		if ($perm['gid'] == $row['id']) {
			die($lang->phrase('admin_forum_rights_guest_limit'));
		}
	}
	$perm = invert($perm[$key]);
	$db->query("UPDATE {$db->pre}fgroups SET `f_{$key}` = '{$perm}' WHERE fid = '{$id}' LIMIT 1");
	$delobj = $scache->load('fgroups');
	$delobj->delete();
	die(strval($perm));
}
elseif ($job == 'rights_add') {
	echo head();
	$id = $gpc->get('id', int);
	if ($id == 0) {
		error('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_not_found_id'));
	}
	$result = $db->query("SELECT id, name FROM {$db->pre}groups ORDER BY admin DESC , guest ASC , core ASC");
	$result2 = $db->query("SELECT gid FROM {$db->pre}fgroups WHERE bid = '{$id}'");
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
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_forum_new_usergroup'); ?></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_forum_settings'); ?></td>
  </tr>
  <tr>
	  <td class="mbox"><?php echo $lang->phrase('admin_forum_use_groups'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_info_groub_specified_permissions'); ?></span></td>
	  <td class="mbox">
	  <select name="group">
	  <option value="0"><?php echo $lang->phrase('admin_forum_all_groups'); ?></option>
	  <?php
	  foreach($cache as $row) {
	  	echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
	  }
	  ?>
	  </select>
	  </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_forum_permissions'); ?></td>
  </tr>
  <?php foreach ($glk_forums as $key) { ?>
  <tr>
   <td class="mbox" width="50%"><?php echo $gls[$key]; ?><br /><span class="stext"><?php echo $gll[$key].iif(in_array($key, $guest_limitation), ' (Guests are not allowed to do this!)'); ?></span></td>
   <td class="mbox" width="50%"><input type="checkbox" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="1" /></td>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_forum_form_add'); ?>" /></td>
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

	$result = $db->query("SELECT * FROM {$db->pre}fgroups WHERE bid = '{$id}' AND gid = '{$group}'");
	if ($db->num_rows($result) > 0) {
		error('admin.php?action=forums&job=rights&id='.$id, $lang->phrase('admin_forum_group_entry_exists'));
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
	");
	if ($db->affected_rows() == 1) {
		$delobj = $scache->load('fgroups');
		$delobj->delete();
		ok('admin.php?action=forums&job=rights&id='.$id);
	}
	else {
		error('admin.php?action=forums&job=rights_add&id='.$id);
	}
}
elseif ($job == 'rights_delete') {
	echo head();
	$id = $gpc->get('id', int);
	if (!is_id($id)) {
		error('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_not_found_id'));
	}
	$did = $gpc->get('delete', arr_int);
	if (count($did) > 0) {
		$db->query('DELETE FROM '.$db->pre.'fgroups WHERE fid IN('.implode(',',$did).') AND bid = "'.$id.'"');
		$anz = $db->affected_rows();
		$delobj = $scache->load('fgroups');
		$delobj->delete();
		ok('admin.php?action=forums&job=rights&id='.$id, $lang->phrase('admin_forum_entries_deleted'));
	}
	else {
		error('admin.php?action=forums&job=rights&id='.$id, $lang->phrase('admin_forum_rights_nothing_chosen'));
	}
}
elseif ($job == 'cat_add') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=forums&job=cat_add2">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_forum_add_category'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_name'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_forum_info_maximum'); ?></span>
   </td>
   <td class="mbox" width="50%"><input type="text" name="name" size="50" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_description'); ?><br />
   <span class="stext">
   <?php echo $lang->phrase('admin_forum_info_short_description'); ?><br />
   <?php echo $lang->phrase('admin_forum_html_bbcode'); ?></span></td>
   <td class="mbox" width="50%"><textarea name="description" rows="2" cols="50"></textarea></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_position'); ?></td>
   <td class="mbox" width="50%">
	<select name="sort_where">
	 <option value="-1"><?php echo $lang->phrase('admin_forum_before'); ?></option>
	 <option value="1" selected="selected"><?php echo $lang->phrase('admin_forum_after'); ?></option>
	</select>&nbsp;<?php echo SelectBoardStructure('sort', ADMIN_SELECT_CATEGORIES); ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_parent_forum'); ?></td>
   <td class="mbox" width="50%">
   	<select name="parent" size="1">
   	 <option value="0" selected="selected"><?php echo $lang->phrase('admin_forum_no_one'); ?></option>
   	 <?php echo SelectBoardStructure('parent', ADMIN_SELECT_FORUMS, null, true); ?>
   	</select>
   </td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_forum_form_add'); ?>" /></td>
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
		error('admin.php?action=forums&job=cat_add', $lang->phrase('admin_forum_name_short'));
	}
	elseif (strlen($name) > 200) {
		error('admin.php?action=forums&job=cat_add', $lang->phrase('admin_forum_name_long'));
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
	");

	$delobj = $scache->load('categories');
	$delobj->delete();
	$delobj = $scache->load('forumtree');
	$delobj->delete();

	ok('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_cat_created'));
}
elseif ($job == 'cat_edit') {
	echo head();
	$id = $gpc->get('id', int);

	$result = $db->query("SELECT id, name, description, parent FROM {$db->pre}categories WHERE id = '{$id}' LIMIT 1");
	if ($db->num_rows($result) == 0) {
		error('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_cat_not_found'));
	}
	$row = $gpc->prepare($db->fetch_assoc($result));
	?>
<form name="form" method="post" action="admin.php?action=forums&job=cat_edit2&id=<?php echo $row['id']; ?>">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_forum_edit_category'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_name'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_forum_info_maximum'); ?></span>
   </td>
   <td class="mbox" width="50%"><input type="text" name="name" size="50" value="<?php echo $row['name']; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_description'); ?><br />
   <span class="stext">
   <?php echo $lang->phrase('admin_forum_info_short_description'); ?><br />
   <?php echo $lang->phrase('admin_forum_html_bbcode'); ?></span></td>
   <td class="mbox" width="50%"><textarea name="description" rows="2" cols="50"><?php echo $row['description']; ?></textarea></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_parent_forum'); ?></td>
   <td class="mbox" width="50%">
   	<select name="parent" size="1">
   	 <option value="0"<?php echo iif($row['parent'] == '0', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_forum_no_one'); ?></option>
   	 <?php echo SelectBoardStructure('parent', ADMIN_SELECT_FORUMS, $row['parent'], true, 'c_'.$id); ?>
   	</select>
   </td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_forum_form_submit'); ?>" /></td>
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
		$result = $db->query("SELECT parent FROM {$db->pre}categories WHERE id = '{$id}' LIMIT 1");
		$row = $db->fetch_assoc($result);
		foreach ($parents as $p_id => $p_arr) {
			array_shift($p_arr);
			if (in_array($row['parent'], $p_arr)) {
				$subs[] = $p_id;
			}
		}
		if (in_array($parent, $subs)) {
			$parent_notice = true;
			$parent = $row['parent'];
		}
	}

	if (strlen($name) < 2) {
		error('admin.php?action=forums&job=cat_edit&id='.$id, $lang->phrase('admin_forum_name_short'));
	}
	elseif (strlen($name) > 200) {
		error('admin.php?action=forums&job=cat_edit&id='.$id, $lang->phrase('admin_forum_name_long'));
	}

	$db->query("UPDATE {$db->pre}categories SET name = '{$name}', description = '{$description}', parent = '{$parent}' WHERE id = '{$id}' LIMIT 1");

	$delobj = $scache->load('categories');
	$delobj->delete();
	$delobj = $scache->load('forumtree');
	$delobj->delete();

	if ($parent_notice == false) {
		ok('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_cat_edited'));
	}
	else{
		error('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_cat_edit_partially'));
	}
}
elseif ($job == 'cat_delete') {
	echo head();
	$id = $gpc->get('id', int);

	$result = $db->query("SELECT id FROM {$db->pre}forums WHERE parent = '{$id}' LIMIT 1");
	if ($db->num_rows($result) > 0) {
		error('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_delete_all_subforums'));
	}

	$db->query("DELETE FROM {$db->pre}categories WHERE id = '{$id}' LIMIT 1");

	$delobj = $scache->load('categories');
	$delobj->delete();
	$delobj = $scache->load('forumtree');
	$delobj->delete();

	ok('admin.php?action=forums&job=manage', $lang->phrase('admin_forum_cat_deleted'));
}
elseif ($job == 'prefix') {
	echo head();
	$id = $gpc->get('id', int);
	if (!is_id($id)) {
		error('admin.php?action=forums&job=manage', 'Invalid ID given.');
	}
	$result = $db->query('SELECT * FROM '.$db->pre.'prefix WHERE bid = "'.$id.'" ORDER BY value');
?>
<form name="form" method="post" action="admin.php?action=forums&job=prefix_delete&id=<?php echo $id; ?>">
 <table class="border">
  <tr>
   <td class="obox" colspan="3"><?php echo $lang->phrase('admin_forum_manage_prefixes'); ?></td>
  </tr>
  <tr>
   <td class="ubox" width="10%"><?php echo $lang->phrase('admin_forum_delete'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /> <?php echo $lang->phrase('admin_forum_all'); ?></span></td>
   <td class="ubox" width="70%"><?php echo $lang->phrase('admin_forum_head_value'); ?></td>
   <td class="ubox" width="20%"><?php echo $lang->phrase('admin_forum_head_standard'); ?></td>
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
   <td class="ubox" colspan="3" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_forum_delete'); ?>"></td>
  </tr>
 </table>
</form><br />
<form name="form" method="post" action="admin.php?action=forums&job=prefix_add&id=<?php echo $id; ?>">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_forum_add_prefix'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_value'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="name" size="50" /></td>
  </tr>
<?php if ($has_standard == false) { ?>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_standard'); ?></td>
   <td class="mbox" width="50%"><input type="checkbox" name="standard" value="1" /></td>
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_forum_add'); ?>"></td>
  </tr>
 </table>
</form>
<?php
	echo foot();
}
elseif ($job == 'prefix_edit') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}prefix WHERE id = '{$id}'");
	$row = $db->fetch_assoc($result);
?>
<form name="form" method="post" action="admin.php?action=forums&job=prefix_edit2&id=<?php echo $id; ?>">
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_forum_edit_perfix'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_value'); ?></td>
   <td class="mbox" width="50%"><input type="text" name="name" size="50" value="<?php echo htmlspecialchars($row['value']); ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_standard'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_prefix_category_status'); ?></span></td>
   <td class="mbox" width="50%"><input type="checkbox" name="standard" value="1" <?php echo iif($row['standard'] == 1, ' checked="checked"'); ?> /></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_forum_form_submit'); ?>"></td>
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

	$result = $db->query('SELECT bid, standard FROM '.$db->pre.'prefix WHERE id = "'.$id.'"');
	$row = $db->fetch_assoc($result);

	$result = $db->query('SELECT id FROM '.$db->pre.'prefix WHERE bid = "'.$row['bid'].'" AND value = "'.$val.'" AND id != "'.$id.'" LIMIT 1');
	if ($db->num_rows($result) > 0) {
		error('admin.php?action=forums&job=prefix&id='.$id, $lang->phrase('admin_forum_prefix_value_exists'));
	}
	else {
		if ($row['standard'] != $standard && $standard == 1) {
			$db->query("UPDATE {$db->pre}prefix SET standard = '0' WHERE standard = '1' AND bid = '{$row['bid']}' LIMIT 1");
		}
		$db->query("UPDATE {$db->pre}prefix SET value = '{$val}', standard = '{$standard}' WHERE id = '{$id}' LIMIT 1");
		$delobj = $scache->load('prefix');
		$delobj->delete();
		ok('admin.php?action=forums&job=prefix&id='.$row['bid'], $lang->phrase('admin_forum_prefix_changed'));
	}
}
elseif ($job == 'prefix_delete') {
	echo head();
	$id = $gpc->get('id', int);
	$did = $gpc->get('delete', arr_int);
	$did = implode(',', $did);
	$delobj = $scache->load('prefix');
	$delobj->delete();
	$db->query('DELETE FROM '.$db->pre.'prefix WHERE id IN('.$did.') AND bid = "'.$id.'"');
	$anz = $db->affected_rows();
	ok('admin.php?action=forums&job=prefix&id='.$id, $lang->phrase('admin_forum_entries_deleted'));
}
elseif ($job == 'prefix_add') {
	echo head();
	$id = $gpc->get('id', int);
	$val = $gpc->get('name', str);
	$standard = $gpc->get('standard', int);
	$result = $db->query('SELECT id FROM '.$db->pre.'prefix WHERE bid = "'.$id.'" AND value = "'.$val.'" LIMIT 1');
	if ($db->num_rows($result) > 0) {
		error('admin.php?action=forums&job=prefix&id='.$id, $lang->phrase('admin_forum_prefix_value_exists'));
	}
	else {
		$db->query("INSERT INTO {$db->pre}prefix (bid, value, standard) VALUES ('{$id}', '{$val}', '{$standard}')");
		$delobj = $scache->load('prefix');
		$delobj->delete();
		ok('admin.php?action=forums&job=prefix&id='.$id, $lang->phrase('admin_forum_prefix_added'));
	}
}
?>