<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "posts.php") die('Error: Hacking Attempt');

if ($_GET['job'] == 'postrating') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=posts&job=postrating2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2"><b>Postrating &raquo; Choose Forum</b></td>
  </tr>
  <tr>
   <td class="mbox">Forum to show:</td>
   <td class="mbox">
	<select name="board" size="1">
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
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
  </tr>
 </table>
</form>	
	<?php
	echo foot();
}
elseif ($_GET['job'] == 'postrating2') {
	echo head();
	$id = $gpc->get('board', str);
	$page = $gpc->get('page', int, 1);
	
	list($type, $board) = explode('_', $id, 2);
	if ($type != 'f') {
		error('admin.php?action=posts&job=postrating', 'Please choose a valid forum (not a category)!');
	}
	
	$count = $db->fetch_num($db->query("SELECT COUNT(*) FROM {$db->pre}postratings AS p LEFT JOIN {$db->pre}topics AS t ON p.tid = t.id  WHERE t.board = '{$board}' AND t.status != '2' GROUP BY p.tid"));
	$temp = pages($count[0], "admin.php?action=members&job=memberrating&amp;", 25);
	
	if ($count[0] < 1) {
		error('admin.php?action=posts&job=postrating', 'Forum does not contain any posts.');
	}

    $perpage = 30;

	$pages = pages($count[0], 'admin.php?action=posts&amp;job=postrating2&amp;board='.$id.'&amp;', $perpage);
	
	$start = ($page-1)*$perpage;
	
	$result = $db->query("
	SELECT t.prefix, t.posts, t.mark, t.id, t.board, t.topic, t.date, t.status, t.last, t.last_name, t.sticky, t.name, 
	    avg(p.rating) AS ravg, count(*) AS rcount 
	FROM {$db->pre}postratings AS p 
	    LEFT JOIN {$db->pre}topics AS t ON p.tid = t.id 
	WHERE t.board = '{$board}' AND t.status != '2' 
	GROUP BY p.tid
	ORDER BY ravg DESC 
	LIMIT {$start}, {$perpage}"
	,__LINE__,__FILE__);

	?>
<table class="border">
  <tr>
	<td class="ubox" colspan="5"><?php echo $pages; ?></td>
  </tr>
  <tr class="obox">
    <th width="18%">Rating (Votes)</th>
	<th width="38%">Topic</th>
	<th width="18%">Creation</th>
	<th width="8%">Replies</th>
	<th width="18%">Last Post</th>
  </tr>
	<?php
	
	$prefix_obj = $scache->load('prefix');
	$prefix = $prefix_obj->get($board);
	
	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();
	
	while ($row = $gpc->prepare($db->fetch_object($result))) {
		$pref = '';
		$showprefix = FALSE;
		if (isset($prefix[$row->prefix]) && $row->prefix > 0) {
			$showprefix = TRUE;
		}
		else {
			$prefix[$row->prefix] = '';
		}
		
		if(is_id($row->name) && isset($memberdata[$row->name])) {
			$row->mid = $row->name;
			$row->name = $memberdata[$row->name];
		}
		else {
			$row->mid = FALSE;
		}
		
		if (is_id($row->last_name) && isset($memberdata[$row->last_name])) {
			$row->last_name = $memberdata[$row->last_name];
		}
		
		$rstart = str_date('d.m.Y H:i', times($row->date));
		$rlast = str_date('d.m.Y H:i', times($row->last));
			
		if ($row->mark == 'n') {
			$pref .= 'News: '; 
		}
		elseif ($row->mark == 'a') {
			$pref .= 'Artcle: ';
		}
		elseif ($row->mark == 'b') {
			$pref .= 'Bad: ';
		}
		elseif ($row->mark == 'g') {
			$pref .= 'Good: ';
		}
		if ($row->sticky == '1') {
			$pref .= 'Announcement: ';
		}
		if ($row->status == 1) {
			$pref .= 'Closed: ';
		}
		
		$percent = round((($row->ravg*50)+50));
		?>
        <tr class="mbox">
        <td><img src="images.php?action=threadrating&id=<?php echo $row->id; ?>" alt="<?php echo $percent; ?>%" title="<?php echo $percent; ?>%"  /> <?php echo $percent; ?>% (<?php echo $row->rcount; ?>)</td>
        <td><?php echo $pref; ?><a target="_blank" href="showtopic.php?id=<?php echo $row->id; ?>"><?php echo iif($showprefix, '['.$prefix[$row->prefix].'] ').$row->topic; ?></a></td>
        <td class="stext"><?php echo $rstart; ?><br />by <?php echo iif($row->mid, "<a href='admin.php?action=members&amp;job=edit&amp;id=".$row->mid."'>".$row->name."</a>", $row->name); ?></td>
        <td align="center"><?php echo $row->posts; ?></td>
        <td align="right" class="stext"><?php echo $rlast; ?><br />by <?php echo $row->last_name; ?></td>
        </tr>
		<?php
    }
    ?>
  <tr> 
	<td class="ubox" colspan="5"><?php echo $pages; ?></td>
  </tr>
</table>
    <?php
	echo foot();
}
?>
