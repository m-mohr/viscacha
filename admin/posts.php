<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

($code = $plugins->load('admin_posts_jobs')) ? eval($code) : null;

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
	 <?php echo SelectBoardStructure('board', ADMIN_SELECT_FORUMS); ?>
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
	$board = $gpc->get('board', int);
	$page = $gpc->get('page', int, 1);
	
	$count = $db->fetch_num($db->query("SELECT COUNT(*) FROM {$db->pre}postratings AS p LEFT JOIN {$db->pre}topics AS t ON p.tid = t.id  WHERE t.board = '{$board}' AND t.status != '2' GROUP BY p.tid"));
	$temp = pages($count[0], "admin.php?action=members&job=memberrating&amp;", 25);
	
	if ($count[0] < 1) {
		error('admin.php?action=posts&job=postrating', 'The forum does not contain any rated posts.');
	}
	
	$catbid = $scache->load('cat_bid');
	$fc = $catbid->get();
	$info = $fc[$board];

    $perpage = 30;

	$pages = pages($count[0], 'admin.php?action=posts&amp;job=postrating2&amp;board='.$board.'&amp;', $perpage);
	
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
	$prefix_arr = $prefix_obj->get($board);
	
	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();
	
	while ($row = $gpc->prepare($db->fetch_object($result))) {
		$pref = '';
		$showprefix = false;
		if (isset($prefix_arr[$row->prefix]) && $row->prefix > 0) {
			$prefix = $prefix_arr[$row->prefix]['value'];
			$showprefix = true;
		}
		else {
			$prefix = '';
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

		if ($row->status == '2') {
			$pref .= $lang->phrase('forum_moved');
		}
		else {
			if (empty($row->mark) && !empty($info['auto_status'])) {
				$row->mark = $info['auto_status'];
			}
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
			if ($row->status == '1') {
				$pref .= 'Closed: ';
			}
		}
		
		$percent = round((($row->ravg*50)+50));
		?>
        <tr class="mbox">
        <td><img src="images.php?action=threadrating&id=<?php echo $row->id; ?>" alt="<?php echo $percent; ?>%" title="<?php echo $percent; ?>%"  /> <?php echo $percent; ?>% (<?php echo $row->rcount; ?>)</td>
        <td><?php echo $pref; ?><a target="_blank" href="showtopic.php?id=<?php echo $row->id; ?>"><?php echo iif($showprefix, '['.$prefix.'] ').$row->topic; ?></a></td>
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
