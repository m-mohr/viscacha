<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// PK: MultiLangAdmin
$lang->group("admin/posts");

($code = $plugins->load('admin_posts_jobs')) ? eval($code) : null;

if ($_GET['job'] == 'reports') {
	echo head();
	$page = $gpc->get('page', int, 1);

	$count = $db->fetch_num($db->query("SELECT COUNT(*) FROM {$db->pre}replies WHERE report != ''"));

    $perpage = 10;
	$pages = pages($count[0], 'admin.php?action=posts&amp;job=reports&amp;', $perpage);
	$start = ($page-1)*$perpage;

	$result = $db->query("
	SELECT t.prefix, r.topic_id, r.id, r.report, t.board, t.topic, r.topic AS title, r.date, r.name, f.name AS forumname
	FROM {$db->pre}replies AS r
	    LEFT JOIN {$db->pre}topics AS t ON r.topic_id = t.id
	    LEFT JOIN {$db->pre}forums AS f ON f.id = t.board
	WHERE r.report != ''
	ORDER BY r.topic_id DESC, r.date DESC
	LIMIT {$start}, {$perpage}"
	);
	$num = $db->num_rows($result);
	?>
<form method="post" action="admin.php?action=posts&amp;job=reports2">
<table class="border">
  <tr>
	<td class="obox" colspan="5"><?php echo $lang->phrase('admin_reported_posts');?></td>
  </tr>
<?php if ($num == 0) { ?>
  <tr>
	<td class="mbox" colspan="5"><?php echo $lang->phrase('admin_no_reported_posts'); ?></td>
  </tr>
<?php } else { ?>
  <tr>
  	<td class="ubox" colspan="5"><?php echo $pages; ?></td>
  </tr>
  <tr class="obox">
  	<th width="2%"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /></th>
	<th width="40%"><?php echo $lang->phrase('admin_post_topic_forum'); ?></th>
	<th width="20%"><?php echo $lang->phrase('admin_date_author'); ?></th>
	<th width="38%"><?php echo $lang->phrase('admin_message'); ?></th>
  </tr>
	<?php

	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();

	$prefix_obj = $scache->load('prefix');

	while ($row = $gpc->prepare($db->fetch_object($result))) {
		$prefix_arr = $prefix_obj->get($row->board);
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

		?>
        <tr class="mbox">
        <td><input type="checkbox" value="<?php echo $row->id; ?>" name="delete[]" /></td>
        <td>
			<strong><a href="showtopic.php?action=jumpto&topic_id=<?php echo $row->id.SID2URL_x; ?>" target="_blank"><?php echo $row->title; ?></a></strong><br />
        	<span class="stext">
        		<a target="_blank" href="showtopic.php?id=<?php echo $row->id; ?>"><?php echo iif($showprefix, '['.$prefix.'] ').$row->topic; ?></a><br />
        		<?php echo $row->forumname; ?>
        	</span>
        </td>
        <td>
        	<?php echo gmdate('d.m.Y H:i', times($row->date)); ?><br />
        	<?php echo iif($row->mid, "<a href='admin.php?action=members&amp;job=edit&amp;id=".$row->mid."'>".$row->name."</a>", $row->name); ?>
        </td>
        <td align="center"><textarea cols="30" rows="3" style="width: 99%;"><?php echo $row->report; ?></textarea></td>
        </tr>
		<?php
    }
    ?>
  <tr>
	<td class="ubox" colspan="5"><span class="right"><?php echo $pages; ?></span><input type="submit" value="<?php echo $lang->phrase('admin_report_reset'); ?>" /></td>
  </tr>
<?php } ?>
</table>
</form>
    <?php
	echo foot();
}
elseif ($_GET['job'] == 'reports2') {
	echo head();
	$delete = $gpc->get('delete', arr_int);
	if (count($delete) > 0) {
		$din = implode(',', $delete);
		$db->query("UPDATE {$db->pre}replies SET report = '' WHERE id IN ({$din})");
		ok('admin.php?action=posts&job=reports', $lang->phrase('admin_reports_set_as_done'));
	}
	else {
		error('admin.php?action=posts&job=reports', $lang->phrase('admin_havent_checked_box'));
	}
}
?>