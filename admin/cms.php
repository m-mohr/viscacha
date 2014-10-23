<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// PK: MultiLangAdmin
$lang->group("admin/cms");

require('classes/class.phpconfig.php');
$myini = new INI();

function BBCodeToolBox() {
	global $db, $scache, $config, $lang;

	$cache = $scache->load('smileys');
	$cache->seturl($config['smileyurl']);
	$csmileys = $cache->get();
	$smileys = array(0 => array(), 1 => array());
	foreach ($csmileys as $bb) {
	   	if ($bb['show'] == 1) {
			$smileys[1][] = $bb;
		}
		else {
			$smileys[0][] = $bb;
		}
	}
	$smileys[1] = array_chunk($smileys[1], 5);

	$cache = $scache->load('custombb');
	$cbb = $cache->get();
	foreach ($cbb as $key => $bb) {
   		if (empty($bb['buttonimage'])) {
			unset($cbb[$key]);
			continue;
		}
		$cbb[$key]['title'] = htmlspecialchars($bb['title']);
		if ($bb['twoparams']) {
			$cbb[$key]['href'] = "InsertTagsParams('[{$bb['bbcodetag']}={param1}]{param2}','[/{$bb['bbcodetag']}]');";
		}
		else {
			$cbb[$key]['href'] = "InsertTags('[{$bb['bbcodetag']}]','[/{$bb['bbcodetag']}]');";
		}
	}
	?>
<script type="text/javascript" src="admin/html/editor.js"></script>
<table class="invisibletable">
 <tr>
  <td width="30%">
	<table style="margin-bottom: 5px;width: 140px">
	<?php foreach ($smileys[1] as $row) { ?>
		<tr>
		<?php foreach ($row as $bb) { ?>
			<td class="center"><a href="javascript:InsertTagsMenu(' <?php echo $bb['jssearch'] ?> ', '', 'bbsmileys')"><img border="0" src="<?php echo $bb['replace']; ?>" alt="<?php echo $bb['desc']; ?>" /></a></td>
		<?php } ?>
		</tr>
	<?php } ?>
	</table>
	<a id="menu_bbsmileys" style="display: block;text-align: center;width: 140px;" href="javascript:Link()"><img border="0" src="admin/html/images/desc.gif" alt="" /> <?php echo $lang->phrase('admin_cms_more_smileys'); ?></a>
	<script type="text/javascript">RegisterMenu('bbsmileys');</script>
	<div class="popup" id="popup_bbsmileys" style="height: 200px;width: 255px;overflow: auto;">
	<strong><?php echo $lang->phrase('admin_cms_head_smileys'); ?></strong>
	<table style="width: 250px;border-collapse: collapse;margin-bottom: 5px;">
	<?php foreach ($smileys[0] as $bb) { ?>
	  <tr class="mbox">
		<td width="20%" class="center"><a href="javascript:InsertTagsMenu(' <?php echo $bb['jssearch'] ?>', ' ', 'bbsmileys')"><img border="0" src="<?php echo $bb['replace']; ?>" alt="<?php echo $bb['desc']; ?>" /></a></td>
		<td width="20%" class="center"><?php echo $bb['search']; ?></td>
		<td width="60%"><span class="stext"><?php echo $bb['desc']; ?></span></td>
	  </tr>
	<?php } ?>
	</table>
	</div>
  </td>
  <td width="70%">
	<div class="label" id="codebuttons">
	<a id="menu_bbcolor" href="javascript:Link()"><img src="admin/html/images/desc.gif" alt="" /> <?php echo $lang->phrase('admin_cms_head_color'); ?></a>
		<script type="text/javascript">RegisterMenu('bbcolor');</script>
		<DIV class="popup" id="popup_bbcolor">
		<strong><?php echo $lang->phrase('admin_cms_head_choose_color'); ?></strong>
		<div class="bbody">
		<script type="text/javascript">document.write(writeRow());</script>
		</div>
		</DIV>
	<a id="menu_bbsize" href="javascript:Link()"><img src="admin/html/images/desc.gif" alt="" /> <?php echo $lang->phrase('admin_cms_head_size'); ?></a>
		<script type="text/javascript">RegisterMenu('bbsize');</script>
		<div class="popup" id="popup_bbsize">
		<strong><?php echo $lang->phrase('admin_cms_head_choose_size'); ?></strong>
	   	<ul>
			<li><span class="popup_line" onclick="InsertTagsMenu('[size=large]','[/size]','bbsize')" style="font-size: 1.3em;"><?php echo $lang->phrase('admin_cms_big_font'); ?></span></li>
			<li><span class="popup_line" onclick="InsertTagsMenu('[size=small]','[/size]','bbsize')" style="font-size: 0.8em;"><?php echo $lang->phrase('admin_cms_small_font'); ?></span></li>
			<li><span class="popup_line" onclick="InsertTagsMenu('[size=extended]','[/size]','bbsize')" style="letter-spacing: 3px;"><?php echo $lang->phrase('admin_cms_extended_font'); ?></span></li>
		</ul>
		</div>
	<a id="menu_bbalign" href="javascript:Link()"><img src="admin/html/images/desc.gif" alt="" /> <?php echo $lang->phrase('admin_cms_head_alignment'); ?></a>
		<script type="text/javascript">RegisterMenu('bbalign');</script>
		<DIV class="popup" id="popup_bbalign">
	   <strong><?php echo $lang->phrase('admin_cms_head_choose_alignment'); ?></strong>
		<ul>
			<li><span class="popup_line" onclick="InsertTagsMenu('[align=left]','[/align]','bbalign')" style="text-align: left;"><?php echo $lang->phrase('admin_cms_left'); ?></span></li>
			<li><span class="popup_line" onclick="InsertTagsMenu('[align=center]','[/align]','bbalign')" style="text-align: center;"><?php echo $lang->phrase('admin_cms_center'); ?></span></li>
			<li><span class="popup_line" onclick="InsertTagsMenu('[align=right]','[/align]','bbalign')" style="text-align: right;"><?php echo $lang->phrase('admin_cms_right'); ?></span></li>
			<li><span class="popup_line" onclick="InsertTagsMenu('[align=justify]','[/align]','bbalign')" style="text-align: justify;"><?php echo $lang->phrase('admin_cms_justify'); ?></span></li>
		</ul>
		</DIV>
	<a id="menu_bbhx" href="javascript:Link()"><img src="admin/html/images/desc.gif" alt="" /> <?php echo $lang->phrase('admin_cms_head_heading'); ?></a>
		<script type="text/javascript">RegisterMenu('bbhx');</script>
		<div class="popup" id="popup_bbhx">
		<strong><?php echo $lang->phrase('admin_cms_head_choose_heading'); ?></strong>
		<ul>
			<li><h4 class="popup_line" onclick="InsertTagsMenu('[h=large]','[/h]','bbhx')" style="margin: 0px; font-size: 14pt;"><?php echo $lang->phrase('admin_cms_heading_1'); ?></h4></li>
			<li><h5 class="popup_line" onclick="InsertTagsMenu('[h=middle]','[/h]','bbhx')" style=" margin: 0px; font-size: 13pt;"><?php echo $lang->phrase('admin_cms_heading_2'); ?></h5></li>
			<li><h6 class="popup_line" onclick="InsertTagsMenu('[h=small]','[/h]','bbhx')" style="margin: 0px; font-size: 12pt;"><?php echo $lang->phrase('admin_cms_heading_3'); ?></h6></li>
		</ul>
		</div>
	<a id="menu_help" href="misc.php?action=bbhelp<?php echo SID2URL_x; ?>" style="cursor: help;" target="_blank"><img src="./images/1/bbcodes/help.gif" alt="" /> <strong><?php echo $lang->phrase('admin_cms_head_help'); ?></strong></a>
	<?php if ($config['spellcheck'] == 1) { ?>
	<script type="text/javascript" src="templates/spellChecker.js"></script>
	<a href="javascript:openSpellChecker(textfield);"><img src="./images/1/bbcodes/spellcheck.gif" alt="Spell Check" /></a>
	<?php } ?>
	<br />
	<a href="javascript:InsertTags('[b]','[/b]');" title="<?php echo $lang->phrase('admin_cms_tag_boldface'); ?>"><img src="./images/1/bbcodes/b.gif" alt="<?php echo $lang->phrase('admin_cms_tag_boldface'); ?>" /></a>
	<a href="javascript:InsertTags('[i]','[/i]');" title="<?php echo $lang->phrase('admin_cms_tag_italic'); ?>"><img src="./images/1/bbcodes/i.gif" alt="<?php echo $lang->phrase('admin_cms_tag_italic'); ?>" /></a>
	<a href="javascript:InsertTags('[u]','[/u]');" title="<?php echo $lang->phrase('admin_cms_tag_underline'); ?>"><img src="./images/1/bbcodes/u.gif" alt="<?php echo $lang->phrase('admin_cms_tag_underline'); ?>" /></a>
	<a href="javascript:InsertTags('[hr]','');" title="<?php echo $lang->phrase('admin_cms_tag_horizontal_ruler'); ?>"><img src="./images/1/bbcodes/hr.gif" alt="<?php echo $lang->phrase('admin_cms_tag_horizontal_ruler'); ?>" /></a>
	<a href="javascript:InsertTags('[img]','[/img]');" title="<?php echo $lang->phrase('admin_cms_tag_image'); ?>"><img src="./images/1/bbcodes/img.gif" alt="<?php echo $lang->phrase('admin_cms_tag_image'); ?>" /></a>
	<a href="javascript:InsertTagsParams('[url={param1}]{param2}','[/url]',$lang->phrase('admin_cms_tag_url_please_provide_url'),$lang->phrase('admin_cms_tag_url_please_provide_text'));" title="<?php echo $lang->phrase('admin_cms_tag_url'); ?>"><img src="./images/1/bbcodes/url.gif" alt="<?php echo $lang->phrase('admin_cms_tag_url'); ?>" /></a>
	<a href="javascript:InsertTags('[email]','[/email]');" title="<?php echo $lang->phrase('admin_cms_tag_email'); ?>"><img src="./images/1/bbcodes/email.gif" alt="<?php echo $lang->phrase('admin_cms_tag_email'); ?>" /></a>
	<a href="javascript:InsertTags('[quote]','[/quote]');" title="<?php echo $lang->phrase('admin_cms_tag_quote'); ?>"><img src="./images/1/bbcodes/quote.gif" alt="<mla_tag_quote>Quote" /></a>
	<a href="javascript:InsertTags('[ot]','[/ot]');" title="<?php echo $lang->phrase('admin_cms_tag_off_topic'); ?>"><img src="./images/1/bbcodes/ot.gif" alt="<?php echo $lang->phrase('admin_cms_tag_off_topic'); ?>" /></a>
	<a href="javascript:popup_code();" title="<?php echo $lang->phrase('admin_cms_tag_source_code'); ?>"><img src="./images/1/bbcodes/code.gif" alt="<?php echo $lang->phrase('admin_cms_tag_source_code'); ?>" /></a>
	<a href="javascript:InsertTags('[edit]','[/edit]');" title="<?php echo $lang->phrase('admin_cms_tag_edited_passage'); ?>"><img src="./images/1/bbcodes/edit.gif" alt="<?php echo $lang->phrase('admin_cms_tag_edited_passage'); ?>" /></a>
	<a href="javascript:list();" title="<?php echo $lang->phrase('admin_cms_tag_unordered_list'); ?>"><img src="./images/1/bbcodes/ul.gif" alt="<?php echo $lang->phrase('admin_cms_tag_unordered_list'); ?>" /></a>
	<a href="javascript:list('ol');" title="<?php echo $lang->phrase('admin_cms_tag_ordered_list'); ?>"><img src="./images/1/bbcodes/ol.gif" alt="<?php echo $lang->phrase('admin_cms_tag_ordered_list'); ?>" /></a>
	<a href="javascript:InsertTagsParams('[note={param1}]{param2}','[/note]',$lang->phrase('admin_cms_tag_definition_please_enter_definition'),$lang->phrase('admin_cms_tag_definition_please_enter_word'));" title="<?php echo $lang->phrase('admin_cms_tag_definition'); ?>"><img src="./images/1/bbcodes/note.gif" alt="<?php echo $lang->phrase('admin_cms_tag_definition'); ?>" /></a>
	<a href="javascript:InsertTags('[tt]','[/tt]');" title="<?php echo $lang->phrase('admin_cms_tag_typewriter'); ?>"><img src="./images/1/bbcodes/tt.gif" alt="<?php echo $lang->phrase('admin_cms_tag_typewriter'); ?>" /></a>
	<a href="javascript:InsertTags('[sub]','[/sub]');" title="<?php echo $lang->phrase('admin_cms_tag_subscript'); ?>"><img src="./images/1/bbcodes/sub.gif" alt="<?php echo $lang->phrase('admin_cms_tag_subscript'); ?>" /></a>
	<a href="javascript:InsertTags('[sup]','[/sup]');" title="<?php echo $lang->phrase('admin_cms_tag_superscript'); ?>"><img src="./images/1/bbcodes/sup.gif" alt="<?php echo $lang->phrase('admin_cms_tag_superscript'); ?>" /></a>
	<?php foreach ($cbb as $bb) { ?>
	<a href="javascript:<?php echo $bb['href']; ?>" title="<?php echo $bb['title']; ?>"><img src="<?php echo $bb['buttonimage']; ?>" alt="<?php echo $bb['title']; ?>" /></a>
	<?php } ?>
	</div>
  </td>
 </tr>
</table>
	<?php
}
function parseNavPosSetting() {
	global $admconfig;
	$explode = preg_split("~(\r\n|\r|\n)+~", trim($admconfig['nav_positions']));
	$arr = array();
	foreach ($explode as $val) {
		$dat = explode('=', $val, 2);
		$arr[$dat[0]] = $dat[1];
	}
	return $arr;
}

($code = $plugins->load('admin_cms_jobs')) ? eval($code) : null;

if ($job == 'nav') {
	send_nocache_header();
	echo head();
?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox"><?php echo $lang->phrase('admin_cms_head_manage_navigation'); ?></td>
  </tr>
  <tr>
   <td class="mbox center">
   	<a class="button" href="admin.php?action=cms&amp;job=nav_add"><?php echo $lang->phrase('admin_cms_manage_navigation_add_link'); ?></a>
   	<a class="button" href="admin.php?action=cms&amp;job=nav_addbox"><?php echo $lang->phrase('admin_cms_manage_navigation_add_box'); ?></a>
   	<a class="button" href="admin.php?action=cms&amp;job=nav_addplugin"><?php echo $lang->phrase('admin_cms_manage_navifation_add_plugin'); ?></a>
   </td>
  </tr>
 </table>
 <br />
<?php
	$result = $db->query("SELECT * FROM {$db->pre}menu ORDER BY position, ordering, id", __LINE__, __FILE__);
	$sqlcache = array();
	$cat = array();
	$sub = array();
	while ($row = $db->fetch_assoc($result)) {
		$sqlcache[] = $row;
		if ($row['sub'] > 0) {
			if (!isset($sub[$row['sub']]) || !is_array($sub[$row['sub']])) {
				$sub[$row['sub']] = array();
			}
			$sub[$row['sub']][] = $row;
		}
		else {
			$cat[] = $row;
		}
	}
	$pos = parseNavPosSetting();
	$last = null;
	foreach ($cat as $head) {
		if ($head['position'] != $last) {
			if ($last != null) {
				echo '</table><br class="minibr" />';
			}
			?>
		 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		  <tr>
		   <td class="obox" colspan="4"><?php echo $lang->phrase('admin_cms_position'); ?> <?php echo $pos[$head['position']]; ?></td>
		  </tr>
		  <tr>
		   <td class="ubox"><?php echo $lang->phrase('admin_cms_link'); ?></td>
		   <td class="ubox"><?php echo $lang->phrase('admin_cms_status'); ?></td>
		   <td class="ubox"><?php echo $lang->phrase('admin_cms_order'); ?></td>
		   <td class="ubox"><?php echo $lang->phrase('admin_cms_action'); ?></td>
		  </tr>
			<?php
			$last = $head['position'];
		}
		$type = array();
		if ($head['module'] > 0) {
			$type[] = '<em>'.$lang->phrase('admin_cms_plugin').'</em>';
		}
		if ($head['active'] == 0) {
			$type[] = '<em>'.$lang->phrase('admin_cms_inactive').'</em>';
		}
	?>
	<tr class="mmbox">
	<td width="50%">
	<?php echo navLang($head['name']); ?><?php echo iif(count($type) > 0, ' ('.implode('; ', $type).')' ); ?>
	</td>
	<td width="10%">
	<?php
	if ($head['active'] == 1) {
		echo '<a href="admin.php?action=cms&job=nav_active&id='.$head['id'].iif($head['module'] > 0, '&plug='.$head['module']).'&act=0">'.$lang->phrase('admin_cms_deactivate').'</a>';
	}
	else {
		echo '<a href="admin.php?action=cms&job=nav_active&id='.$head['id'].iif($head['module'] > 0, '&plug='.$head['module']).'&act=1">'.$lang->phrase('admin_cms_activate').'</a>';
	}
	?>
	</td>
	<td width="15%"><?php echo $head['ordering']; ?>&nbsp;&nbsp;
	<a href="admin.php?action=cms&job=nav_move&id=<?php echo $head['id']; ?>&value=-1"><img src="admin/html/images/asc.gif" border="0" alt="Up"></a>&nbsp;
	<a href="admin.php?action=cms&job=nav_move&id=<?php echo $head['id']; ?>&value=1"><img src="admin/html/images/desc.gif" border="0" alt="Down"></a>
	</td>
	<td width="35%">
	 <a class="button" href="admin.php?action=cms&job=nav_edit&id=<?php echo $head['id']; ?>"><?php echo $lang->phrase('admin_cms_edit'); ?></a>
	 <a class="button" href="admin.php?action=cms&job=nav_delete&id=<?php echo $head['id']; ?>"><?php echo $lang->phrase('admin_cms_delete'); ?></a>
	</td>
	</tr>
	<?php
	if (isset($sub[$head['id']]) && count($sub[$head['id']]) > 0) {
		foreach ($sub[$head['id']] as $link) {
			?>
			<tr class="mbox">
			<td width="50%">&nbsp;&middot;&nbsp;
			<?php
			if (empty($link['link'])) {
				echo navLang($link['name']);
			}
			else {
				?>
				<a href="<?php echo $link['link']; ?>" target="<?php echo $link['param']; ?>"><?php echo navLang($link['name']); ?></a>
				<?php } echo iif ($link['active'] == '0', ' (<em>'.$lang->phrase('admin_cms_inactive').'</em>)'); ?><br />
				</td>
				<td class="mbox" width="10%">
				<?php
				if ($link['active'] == 1) {
					echo '<a href="admin.php?action=cms&job=nav_active&id='.$link['id'].'&act=0">'.$lang->phrase('admin_cms_deactivate').'</a>';
				}
				else {
					echo '<a href="admin.php?action=cms&job=nav_active&id='.$link['id'].'&act=1">'.$lang->phrase('admin_cms_activate').'</a>';
				}
				?>
				</td>
				<td class="mbox" width="15%" nowrap="nowrap" align="center"><?php echo $link['ordering']; ?>&nbsp;&nbsp;
				<a href="admin.php?action=cms&job=nav_move&id=<?php echo $link['id']; ?>&value=-1"><img src="admin/html/images/asc.gif" border="0" alt="<?php echo $lang->phrase('admin_cms_move_up'); ?>"></a>&nbsp;
				<a href="admin.php?action=cms&job=nav_move&id=<?php echo $link['id']; ?>&value=1"><img src="admin/html/images/desc.gif" border="0" alt="<?php echo $lang->phrase('admin_cms_move_down'); ?>"></a>
				</font></td>
				<td class="mbox" width="25%">
				 <a class="button" href="admin.php?action=cms&job=nav_edit&id=<?php echo $link['id'] ?>"><?php echo $lang->phrase('admin_cms_edit'); ?></a>
				 <a class="button" href="admin.php?action=cms&job=nav_delete&id=<?php echo $link['id']; ?>"><?php echo $lang->phrase('admin_cms_delete'); ?></a>
				</td>
				</tr>
				<?php
				if (isset($sub[$link['id']]) && count($sub[$link['id']]) > 0) {
					foreach ($sub[$link['id']] as $sublink) {
						?>
						<tr class="mbox">
						<td width="50%">&nbsp;&nbsp;&nbsp;<img src='admin/html/images/list.gif' border="0" alt="">&nbsp;
						<?php
						if (empty($sublink['link'])) {
							echo navLang($sublink['name']);
						}
						else {
							?>
							<a href='<?php echo $sublink['link']; ?>' target='<?php echo $sublink['param']; ?>'><?php echo navLang($sublink['name']); ?></a>
							<?php } echo iif ($sublink['active'] == '0', ' (<i>'.$lang->phrase('admin_cms_inactive').'</i>)'); ?></font><br>
							</td>
							<td class="mbox" width="10%">
							<?php
							if ($sublink['active'] == 1) {
								echo '<a href="admin.php?action=cms&job=nav_active&id='.$sublink['id'].'&act=0">'.$lang->phrase('admin_cms_deactivate').'</a>';
							}
							else {
								echo '<a href="admin.php?action=cms&job=nav_active&id='.$sublink['id'].'&act=1">'.$lang->phrase('admin_cms_activate').'</a>';
							}
							?>
							</td>
							<td class="mbox" width="15%" nowrap="nowrap" align="right"><?php echo $sublink['ordering']; ?>&nbsp;&nbsp;
							<a href="admin.php?action=cms&job=nav_move&id=<?php echo $sublink['id']; ?>&value=-1"><img src="admin/html/images/asc.gif" border="0" alt="<?php echo $lang->phrase('admin_cms_move_up'); ?>"></a>&nbsp;
							<a href="admin.php?action=cms&job=nav_move&id=<?php echo $sublink['id']; ?>&value=1"><img src="admin/html/images/desc.gif" border="0" alt="<?php echo $lang->phrase('admin_cms_move_down'); ?>"></a>
							</td>
							<td class="mbox" width="25%">
							 <a class="button" href="admin.php?action=cms&job=nav_edit&id=<?php echo $sublink['id']; ?>"><?php echo $lang->phrase('admin_cms_edit'); ?></a>
							 <a class="button" href="admin.php?action=cms&job=nav_delete&id=<?php echo $sublink['id']; ?>"><?php echo $lang->phrase('admin_cms_delete'); ?></a>
							</td>
							</tr>
							<?php
						}
					}
			}
		}
	}
	echo '</table>';
	echo foot();
}
elseif ($job == 'nav_edit') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}menu WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	$data = $db->fetch_assoc($result);
	$data['group_array'] = explode(',', $data['groups']);
	$pos = parseNavPosSetting();

	$groups = $db->query("SELECT id, name FROM {$db->pre}groups", __LINE__, __FILE__);

	if ($data['sub'] > 0) {
		$result = $db->query("SELECT id, name, sub, position FROM {$db->pre}menu WHERE module = '0' ORDER BY position, ordering, id", __LINE__, __FILE__);
		$cache = array(0 => array());
		while ($row = $db->fetch_assoc($result)) {
			if (!isset($cache[$row['sub']]) || !is_array($cache[$row['sub']])) {
				$cache[$row['sub']] = array();
			}
			$cache[$row['sub']][] = $row;
		}
	}

	if ($data['module'] > 0) {
		$plugs = $db->query("SELECT * FROM {$db->pre}plugins WHERE position = 'navigation' ORDER BY ordering", __LINE__, __FILE__);
	}

	$last = null;
	?>
<form name="form" method="post" action="admin.php?action=cms&job=nav_edit2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_cms_nav_edit'); ?> <?php echo iif ($data['sub'] > 0, $lang->phrase('admin_cms_link'), $lang->phrase('admin_cms_box')); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_title'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_cms_nav_title_text'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="title" size="40" value="<?php echo $data['name']; ?>" /></td>
  </tr>
<?php if ($data['sub'] > 0) { ?>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_file_url'); ?><br />
   <span class="stext">
   - <a href="javascript:docs();"><?php echo $lang->phrase('admin_cms_nav_existing_documents'); ?></a><br />
   - <a href="javascript:coms();"><?php echo $lang->phrase('admin_cms_nav_existing_components'); ?></a>
   </span></td>
   <td class="mbox" width="50%"><input type="text" name="url" size="40" value="<?php echo $data['link']; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_target'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_cms_nav_target_text'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="target" size="40" value="<?php echo $data['param']; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_parent_box'); ?></td>
   <td class="mbox" width="50%">
   <select name="sub">
   	<?php
	foreach ($cache[0] as $row) {
	   	if ($last != $row['position']) {
	   		if ($last != null) {
				echo '</optgroup>';
	   		}
	   		$last = $row['position'];
	   		echo '<optgroup label="'.htmlspecialchars($pos[$last], ENT_QUOTES).'">';
	   	}
   		$select = iif($row['id'] == $data['sub'], ' selected="selected"');
   		echo '<option style="font-weight: bold;" value="'.$row['id'].'"'.$select.'>'.navLang($row['name']).'</option>';
   		if (isset($cache[$row['id']])) {
   			foreach ($cache[$row['id']] as $row) {
   				$select = iif($row['id'] == $data['sub'], ' selected="selected"');
   				echo '<option value="'.$row['id'].'"'.$select.'>+&nbsp;'.navLang($row['name']).'</option>';
   			}
   		}
	}
	?>
	</optgroup>
   </select>
   </td>
  </tr>
<?php } if ($data['module'] > 0) { ?>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_plugin'); ?></td>
   <td class="mbox" width="50%">
   <select name="plugin">
   <?php while ($row = $db->fetch_assoc($plugs)) { ?>
   <option value="<?php echo $row['id']; ?>"<?php echo iif($row['id'] == $data['module'], ' selected="selected"'); ?>><?php echo $row['name']; ?></option>
   <?php } ?>
   </select>
   </td>
  </tr>
<?php } ?>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_groups'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_cms_nav_groups_text'); ?></span></td>
   <td class="mbox" width="50%">
   <?php while ($row = $db->fetch_assoc($groups)) { ?>
	<input type="checkbox" name="groups[]"<?php echo iif($data['groups'] == 0 || in_array($row['id'], $data['group_array']), ' checked="checked"'); ?> value="<?php echo $row['id']; ?>"> <?php echo $row['name']; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_active'); ?></td>
   <td class="mbox" width="50%"><input type="checkbox" name="active" value="1"<?php echo iif($data['active'] == 1, ' checked="checked"'); ?> /></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_cms_form_edit'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'nav_edit2') {
	echo head();

	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}menu WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	$data = $db->fetch_assoc($result);

	$title = $gpc->get('title', str);
	$title = trim($title);
	if (empty($title)) {
		error('admin.php?action=cms&job=nav_addbox', $lang->phrase('admin_cms_err_no_title'));
	}
	$active = $gpc->get('active', int);
	$groups = $gpc->get('groups', arr_int);
	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'groups', __LINE__, __FILE__);
	$count = $db->fetch_num($result);
	if (count($groups) == $count[0]) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}
	if ($data['sub'] > 0) {
		$target = $gpc->get('target', str);
		$url = $gpc->get('url', str);
		$sub = $gpc->get('sub', int);
		$result = $db->query("SELECT position FROM {$db->pre}menu WHERE id = '{$sub}'");
		$pos = $gpc->save_str($db->fetch_assoc($result));
		$db->query("UPDATE {$db->pre}menu SET name = '{$title}', link = '{$url}', param = '{$target}', groups = '{$groups}', sub = '{$sub}', active = '{$active}', position = '{$pos['position']}' WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	}
	else {
		if ($data['module'] > 0) {
			$plug = $gpc->get('plugin', int);
			$result = $db->query("SELECT position FROM {$db->pre}plugins WHERE id = '{$plug}'", __LINE__, __FILE__);
			if ($db->num_rows($result) > 0) {
				$module_sql = ", module = '{$plug}'";
				$row = $db->fetch_assoc($result);
				$filesystem->unlink('cache/modules/'.$plugins->_group($row['position']).'.php');
				// Do not do that anymore, because it may be required
				// $db->query("UPDATE {$db->pre}plugins SET active = '{$active}' WHERE id = '{$plug}' LIMIT 1", __LINE__, __FILE__);
			}
			$db->query("UPDATE {$db->pre}menu SET name = '{$title}', groups = '{$groups}', active = '{$active}'{$module_sql} WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
		}
		else {
			$db->query("UPDATE {$db->pre}menu SET name = '{$title}', groups = '{$groups}', active = '{$active}' WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
		}
	}
	$delobj = $scache->load('modules_navigation');
	$delobj->delete();
	ok('admin.php?action=cms&job=nav', $lang->phrase('admin_cms_data_successfully_changed'));
}
elseif ($job == 'nav_delete') {
	echo head();
	$id = $gpc->get('id', int);
?>
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	<tr><td class="obox"><?php echo $lang->phrase('admin_cms_nav_delete_box_or_link'); ?></td></tr>
	<tr><td class="mbox">
	<p align="center"><?php echo $lang->phrase('admin_cms_nav_really_want_to_delete'); ?></p>
	<p align="center">
	<a href="admin.php?action=cms&job=nav_delete2&id=<?php echo $id; ?>"><img border="0" alt="" src="admin/html/images/yes.gif"> <?php echo $lang->phrase('admin_cms_yes'); ?></a>
	&nbsp&nbsp;&nbsp;&nbsp&nbsp;&nbsp;
	<a href="javascript: history.back(-1);"><img border="0" alt="" src="admin/html/images/no.gif"> <?php echo $lang->phrase('admin_cms_no'); ?></a>
	</p>
	</td></tr>
	</table>
<?php
	echo foot();
}
elseif ($job == 'nav_delete2') {
	echo head();
	$id = $gpc->get('id', int);
	$delete = array($id);

	$result = $db->query("SELECT id, sub FROM {$db->pre}menu WHERE sub = '{$id}'", __LINE__, __FILE__);
	while($row = $db->fetch_assoc($result)) {
		$delete[] = $row['id'];
		$result2 = $db->query("SELECT id FROM {$db->pre}menu WHERE sub = '{$row['id']}'", __LINE__, __FILE__);
		while($row2 = $db->fetch_assoc($result2)) {
			$delete[] = $row2['id'];
		}
	}

	$count = count($delete);
	$ids = implode(',', $delete);
	$db->query("DELETE FROM {$db->pre}menu WHERE id IN ({$ids}) LIMIT {$count}", __LINE__, __FILE__);
	$anz = $db->affected_rows();

	$delobj = $scache->load('modules_navigation');
	$delobj->delete();

	ok('admin.php?action=cms&job=nav', $lang->phrase('admin_cms_entries_deleted'));
}
elseif ($job == 'nav_move') {
	$id = $gpc->get('id', int);
	$pos = $gpc->get('value', int);
	if ($id < 1) {
		error('admin.php?action=cms&job=nav', $lang->phrase('admin_cms_invalid_id_given'));
	}
	if ($pos < 0) {
		$db->query('UPDATE '.$db->pre.'menu SET ordering = ordering-1 WHERE id = '.$id, __LINE__, __FILE__);
	}
	elseif ($pos > 0) {
		$db->query('UPDATE '.$db->pre.'menu SET ordering = ordering+1 WHERE id = '.$id, __LINE__, __FILE__);
	}

	$delobj = $scache->load('modules_navigation');
	$delobj->delete();

	viscacha_header('Location: admin.php?action=cms&job=nav');
}
elseif ($job == 'nav_active') {
	$id = $gpc->get('id', int);
	$pos = $gpc->get('act', int);
	if ($id < 1) {
		error('admin.php?action=cms&job=nav', $lang->phrase('admin_cms_invalid_id_given'));
	}
	if ($pos != 0 && $pos != 1) {
		error('admin.php?action=cms&job=nav', $lang->phrase('admin_cms_invalid_status_specified'));
	}
	$db->query('UPDATE '.$db->pre.'menu SET active = "'.$pos.'" WHERE id = '.$id, __LINE__, __FILE__);

	$plug = $gpc->get('plug', int);
	if ($plug > 0) {
		$result = $db->query("SELECT position FROM {$db->pre}plugins WHERE id = '{$plug}'", __LINE__, __FILE__);
		if ($db->num_rows($result) > 0) {
			$module_sql = ", module = '{$plug}'";
			$row = $db->fetch_assoc($result);
			$filesystem->unlink('cache/modules/'.$plugins->_group($row['position']).'.php');
			// Do not do that anymore, because it may be required
			// $db->query("UPDATE {$db->pre}plugins SET active = '{$pos}' WHERE id = '{$plug}' LIMIT 1", __LINE__, __FILE__);
		}
	}

	$delobj = $scache->load('modules_navigation');
	$delobj->delete();
	viscacha_header('Location: admin.php?action=cms&job=nav');
}
elseif ($job == 'nav_addplugin') {
	echo head();
	$id = $gpc->get('id', int);
	$sort = $db->query("SELECT id, name, position FROM {$db->pre}menu WHERE sub = '0' ORDER BY position, ordering, id", __LINE__, __FILE__);
	$plugs = $db->query("SELECT id, name FROM {$db->pre}plugins WHERE position = 'navigation' ORDER BY ordering", __LINE__, __FILE__);
	$groups = $db->query("SELECT id, name FROM {$db->pre}groups", __LINE__, __FILE__);
	$pos = parseNavPosSetting();
	?>
<form name="form" method="post" action="admin.php?action=cms&job=nav_addplugin2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_cms_nav_add_plugin'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_title'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_cms_nav_plug_title_text'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="title" size="40" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_plugin'); ?></td>
   <td class="mbox" width="50%">
   <select name="plugin">
   <?php while ($row = $db->fetch_assoc($plugs)) { ?>
   <option value="<?php echo $row['id']; ?>"<?php echo iif($row['id'] == $id, ' selected="selected"'); ?>><?php echo $row['name']; ?></option>
   <?php } ?>
   </select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_sort_in_after'); ?></td>
   <td class="mbox" width="50%">
   <select name="sort">
   	<?php
   	$last = null;
   	while ($row = $db->fetch_assoc($sort)) {
	   	if ($last != $row['position']) {
	   		if ($last != null) {
				echo '</optgroup>';
	   		}
	   		$last = $row['position'];
	   		if (!isset($pos[$last])) {
	   			$pos[$last] = $row['position'];
	   		}
		   	echo '<optgroup label="'.htmlspecialchars($pos[$last], ENT_QUOTES).'">';
		   	unset($pos[$last]);
	   	}
   		echo '<option value="'.$row['id'].'">'.navLang($row['name']).'</option>';
	}
	foreach ($pos as $key => $name) {
		?>
		</optgroup>
		<optgroup label="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>">
		<option value="pos_<?php echo $key; ?>">&lt;<?php echo $lang->phrase('admin_cms_sort_in_here'); ?>&gt;</option>
		<?php
	}
	?>
	</optgroup>
   </select>
   </select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_groups'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_cms_nav_plug_groups_text'); ?></span></td>
   <td class="mbox" width="50%">
   <?php while ($row = $db->fetch_assoc($groups)) { ?>
	<input type="checkbox" name="groups[]" checked="checked" value="<?php echo $row['id']; ?>"> <?php echo $row['name']; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_cms_form_add'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'nav_addplugin2') {
	echo head();
	$plug = $gpc->get('plugin', int);
	$result = $db->query("SELECT id, name, active FROM {$db->pre}plugins WHERE id = '{$plug}' AND position = 'navigation'", __LINE__, __FILE__);
	$data = $db->fetch_assoc();
	$title = $gpc->get('title', str);
	$title = trim($title);
	if (empty($title)) {
		$title = $data['name'];
	}
	$sort = $gpc->get('sort', str);
	if (substr($sort, 0, 4) == 'pos_') {
		$sort = array(
			'ordering' => 0,
			'position' => substr($sort, 4)
		);
	}
	else {
		$result = $db->query("SELECT ordering, position FROM {$db->pre}menu WHERE id = '{$sort}'");
		$sort = $db->fetch_assoc($result);
	}
	$groups = $gpc->get('groups', arr_int);
	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'groups', __LINE__, __FILE__);
	$count = $db->fetch_num($result);
	if (count($groups) == $count[0]) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}
	$db->query("INSERT INTO {$db->pre}menu (name, groups, ordering, active, module, position) VALUES ('{$title}','{$groups}','{$sort['ordering']}','{$data['active']}','{$data['id']}','{$sort['position']}')", __LINE__, __FILE__);
	$delobj = $scache->load('modules_navigation');
	$delobj->delete();
	ok('admin.php?action=cms&job=nav', $lang->phrase('admin_cms_plugins_successfully_added'));
}
elseif ($job == 'nav_add') {
	echo head();
	$groups = $db->query("SELECT id, name FROM {$db->pre}groups", __LINE__, __FILE__);
	$result = $db->query("SELECT id, name, sub, position FROM {$db->pre}menu WHERE module = '0' ORDER BY position, ordering, id", __LINE__, __FILE__);
	$cache = array(0 => array());
	while ($row = $db->fetch_assoc($result)) {
		if (!isset($cache[$row['sub']]) || !is_array($cache[$row['sub']])) {
			$cache[$row['sub']] = array();
		}
		$cache[$row['sub']][] = $row;
	}
	$pos = parseNavPosSetting();
	?>
<form name="form" method="post" action="admin.php?action=cms&job=nav_add2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_cms_add_new_link'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_title'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_cms_nav_title_text'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="title" size="40" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_file_url'); ?><br />
   <span class="stext">
   - <a href="javascript:docs();"><?php echo $lang->phrase('admin_cms_nav_existing_documents'); ?></a><br />
   - <a href="javascript:coms();"><?php echo $lang->phrase('admin_cms_nav_existing_components'); ?></a>
   </span></td>
   <td class="mbox" width="50%"><input type="text" name="url" size="40" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_target'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_cms_nav_target_text'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="target" size="40" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_parent_box'); ?></td>
   <td class="mbox" width="50%">
   <select name="sub">
   	<?php
   	$last = null;
   	foreach ($cache[0] as $row) {
	   	if ($last != $row['position']) {
	   		if ($last != null) {
				echo '</optgroup>';
	   		}
	   		$last = $row['position'];
	   		echo '<optgroup label="'.htmlspecialchars($pos[$last], ENT_QUOTES).'">';
	   	}
   		echo '<option style="font-weight: bold;" value="'.$row['id'].'">'.navLang($row['name']).'</option>';
   		if (isset($cache[$row['id']])) {
   			foreach ($cache[$row['id']] as $row) {
   				echo '<option value="'.$row['id'].'">+&nbsp;'.navLang($row['name']).'</option>';
   			}
   		}
	}
	?>
	</optgroup>
   </select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_sort_in'); ?></td>
   <td class="mbox" width="50%">
   <select name="sort">
	<option value="0"><?php echo $lang->phrase('admin_cms_nav_at_the_beginning'); ?></option>
	<option value="1"><?php echo $lang->phrase('admin_cms_nav_at_the_end'); ?></option>
   </select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_groups'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_cms_nav_groups_text'); ?></span></td>
   <td class="mbox" width="50%">
   <?php while ($row = $db->fetch_assoc($groups)) { ?>
	<input type="checkbox" name="groups[]" checked="checked" value="<?php echo $row['id']; ?>"> <?php echo $row['name']; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_cms_form_add'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'nav_add2') {
	echo head();
	$title = $gpc->get('title', str);
	$target = $gpc->get('target', str);
	$url = $gpc->get('url', str);
	$sub = $gpc->get('sub', int);
	$sort = $gpc->get('sort', int);
	$groups = $gpc->get('groups', arr_int);
	if (empty($title)) {
		error('admin.php?action=cms&job=nav_addbox', $lang->phrase('admin_cms_err_no_title'));
	}

	$pos = $db->fetch_num($db->query("SELECT position FROM {$db->pre}menu WHERE id = '{$sub}' LIMIT 1", __LINE__, __FILE__));
	if (empty($pos[0])) {
		$pos = array('left');
	}

	if ($sort == 1) {
		$sort = $db->fetch_num($db->query("SELECT MAX(ordering) FROM {$db->pre}menu WHERE sub = '{$sub}' LIMIT 1", __LINE__, __FILE__));
		$sort = $sort[0]+1;
	}
	elseif ($sort == 0) {
		$sort = $db->fetch_num($db->query("SELECT MIN(ordering) FROM {$db->pre}menu WHERE sub = '{$sub}' LIMIT 1", __LINE__, __FILE__));
		$sort = $sort[0]-1;
	}
	else {
		$sort = 0;
	}

	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'groups', __LINE__, __FILE__);
	$count = $db->fetch_num($result);
	if (count($groups) == $count[0]) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}
	$db->query("INSERT INTO {$db->pre}menu (name, groups, ordering, link, param, sub, position) VALUES ('{$title}','{$groups}','{$sort}','{$url}','{$target}','{$sub}','{$pos[0]}')", __LINE__, __FILE__);
	$delobj = $scache->load('modules_navigation');
	$delobj->delete();
	ok('admin.php?action=cms&job=nav', $lang->phrase('admin_cms_link_successfully_added'));
}
elseif ($job == 'nav_addbox') {
	echo head();
	$sort = $db->query("SELECT id, name, position FROM {$db->pre}menu WHERE sub = '0' ORDER BY position, ordering, id", __LINE__, __FILE__);
	$groups = $db->query("SELECT id, name FROM {$db->pre}groups", __LINE__, __FILE__);
	$pos = parseNavPosSetting();
	?>
<form name="form" method="post" action="admin.php?action=cms&job=nav_addbox2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_cms_create_a_new_box'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_title'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_cms_nav_title_text'); ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="title" size="40" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_sort_in_after'); ?></td>
   <td class="mbox" width="50%">
   <select name="sort">
   	<?php
   	$last = null;
	while ($row = $db->fetch_assoc($sort)) {
	   	if ($last != $row['position']) {
	   		if ($last != null) {
				echo '</optgroup>';
	   		}
	   		$last = $row['position'];
	   		echo '<optgroup label="'.htmlspecialchars($pos[$last], ENT_QUOTES).'">';
	   		unset($pos[$last]);
	   	}
   		echo '<option value="'.$row['id'].'">'.navLang($row['name']).'</option>';
	}
	foreach ($pos as $key => $name) {
		?>
		</optgroup>
		<optgroup label="<?php echo htmlspecialchars($name, ENT_QUOTES); ?>">
		<option value="pos_<?php echo $key; ?>">&lt;<?php echo $lang->phrase('admin_cms_nav_sort_in_here'); ?>&gt;</option>
		<?php
	}
	?>
	</optgroup>
   </select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_groups'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_cms_nav_groups_text'); ?></span></td>
   <td class="mbox" width="50%">
   <?php while ($row = $db->fetch_assoc($groups)) { ?>
	<input type="checkbox" name="groups[]" checked="checked" value="<?php echo $row['id']; ?>"> <?php echo $row['name']; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_cms_form_add'); ?>" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'nav_addbox2') {
	echo head();
	$title = $gpc->get('title', str);
	if (empty($title)) {
		error('admin.php?action=cms&job=nav_addbox', $lang->phrase('admin_cms_err_no_title'));
	}
	$sort = $gpc->get('sort', str);
	if (substr($sort, 0, 4) == 'pos_') {
		$sort = array(
			'ordering' => 0,
			'position' => substr($sort, 4)
		);
	}
	else {
		$sort = $gpc->save_int($sort);
		$result = $db->query("SELECT ordering, position FROM {$db->pre}menu WHERE id = '{$sort}'");
		$sort = $db->fetch_assoc($result);
	}
	$groups = $gpc->get('groups', arr_int);
	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'groups', __LINE__, __FILE__);
	$count = $db->fetch_num($result);
	if (count($groups) == $count[0]) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}
	$db->query("INSERT INTO {$db->pre}menu (name, groups, ordering, position) VALUES ('{$title}','{$groups}','{$sort['ordering']}','{$sort['position']}')", __LINE__, __FILE__);
	$delobj = $scache->load('modules_navigation');
	$delobj->delete();
	ok('admin.php?action=cms&job=nav', $lang->phrase('admin_cms_box_successfully_added'));
}
elseif ($job == 'nav_docslist') {
	echo head();
	$result = $db->query('SELECT id, title FROM '.$db->pre.'documents');
	?>
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox"><?php echo $lang->phrase('admin_cms_existing_documents_and_pages'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox">
	   <?php while ($row = $db->fetch_assoc($result)) { ?>
	   <input type="radio" name="data" onclick="insert_doc('docs.php?id=<?php echo $row['id']; ?>','<?php echo htmlentities($row['title']); ?>')"> <?php echo $row['title']; ?><br>
	   <?php } ?>
	   </td>
	 </table>
	<?php
	echo foot();
}
elseif ($job == 'nav_comslist') {
	echo head();
	$result = $db->query("
		SELECT c.id, c.package, p.title
		FROM {$db->pre}component AS c
			LEFT JOIN {$db->pre}packages AS p ON c.package = p.id
		WHERE p.active = '1' AND c.active = '1'
	", __LINE__, __FILE__);
	?>
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox"><?php echo $lang->phrase('admin_cms_existing_documents'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox">
	   <?php
		while ($row = $db->fetch_assoc($result)) {
			$head = array();
			$ini = $myini->read('modules/'.$row['package'].'/component.ini');
	   ?>
	   <input type="radio" name="data" onclick="insert_doc('components.php?cid=<?php echo $row['id']; ?>','<?php echo htmlentities($ini['info']['title']); ?>')"> <?php echo  $ini['info']['title']; ?> (Package: <?php echo $row['title']; ?>)<br />
	   <?php } ?>
	   </td>
	 </table>
	<?php
	echo foot();
}
elseif ($job == 'doc') {
	$result = $db->query('SELECT * FROM '.$db->pre.'documents', __LINE__, __FILE__);
	echo head();
?>
<form name="form" method="post" action="admin.php?action=cms&job=doc_delete">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="7">
   <span style="float: right;"><a class="button" href="admin.php?action=cms&job=doc_add"><?php echo $lang->phrase('admin_cms_create_new_document'); ?></a></span>
	<?php echo $lang->phrase('admin_cms_manage_documents_and_pages'); ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_cms_doc_delete'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all('delete[]');" name="all" value="1" /> <?php echo $lang->phrase('admin_cms_doc_delete_all'); ?></span></td>
   <td class="ubox" width="40%"><?php echo $lang->phrase('admin_cms_doc_title'); ?></td>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_cms_doc_id'); ?></td>
   <td class="ubox" width="20%"><?php echo $lang->phrase('admin_cms_doc_author'); ?></td>
   <td class="ubox" width="15%"><?php echo $lang->phrase('admin_cms_doc_last_change'); ?></td>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_cms_doc_published'); ?></td>
   <td class="ubox" width="10%"><?php echo $lang->phrase('admin_cms_doc_action'); ?></td>
  </tr>
<?php
	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();

	while ($row = $db->fetch_assoc($result)) {
		if(is_id($row['author']) && isset($memberdata[$row['author']])) {
			$row['author'] = $memberdata[$row['author']];
		}
		else {
			$row['author'] = $lang->phrase('admin_cms_unknown');
		}
		if ($row['update'] > 0) {
			$row['update'] = gmdate('d.m.Y H:i', times($row['update']));
		}
		else {
			$row['update'] = $lang->phrase('admin_cms_unknown');
		}
?>
  <tr>
   <td class="mbox" width="5%"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
   <td class="mbox" width="40%"><a href="admin.php?action=cms&job=doc_edit&id=<?php echo $row['id']; ?>"><?php echo $row['title']; ?></a></td>
   <td class="mbox" width="5%"><?php echo $row['id']; ?></td>
   <td class="mbox" width="20%"><?php echo $row['author']; ?></td>
   <td class="mbox" width="15%"><?php echo $row['update']; ?></td>
   <td class="mbox center" width="5%"><?php echo noki($row['active'], ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=cms&job=doc_ajax_active&id='.$row['id'].'\')"'); ?></td>
   <td class="mbox" width="10%">
   <a class="button" href="docs.php?id=<?php echo $row['id'].SID2URL_x; ?>" target="_blank"><?php echo $lang->phrase('admin_cms_view'); ?></a>
   <a class="button" href="admin.php?action=cms&job=doc_edit&id=<?php echo $row['id']; ?>"><?php echo $lang->phrase('admin_cms_edit'); ?></a>
   </td>
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" width="100%" colspan="7" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_cms_form_delete'); ?>"></td>
  </tr>
 </table>
</form>
<?php
	echo foot();
}
elseif ($job == 'doc_ajax_active') {
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT active FROM {$db->pre}documents WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	$use = $db->fetch_assoc($result);
	$use = invert($use['active']);
	$db->query("UPDATE {$db->pre}documents SET active = '{$use}' WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	$delobj = $scache->load('wraps');
	$delobj->delete();
	die(strval($use));
}
elseif ($job == 'doc_add') {
	echo head();
	$type = doctypes();
	$parser = array(
		'0' => $lang->phrase('admin_cms_doc_no_parser'),
		'1' => $lang->phrase('admin_cms_doc_html'),
		'2' => $lang->phrase('admin_cms_doc_php_html'),
		'3' => $lang->phrase('admin_cms_doc_bbcodes')
	);
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="4"><?php echo $lang->phrase('admin_cms_create_doc_step_1'); ?></td>
  </tr>
  <tr>
   <td class="ubox"><?php echo $lang->phrase('admin_cms_doc_title'); ?></td>
   <td class="ubox"><?php echo $lang->phrase('admin_cms_doc_template'); ?></td>
   <td class="ubox"><?php echo $lang->phrase('admin_cms_doc_parser'); ?></td>
   <td class="ubox"><?php echo $lang->phrase('admin_cms_doc_integration_of_templates'); ?></td>
  </tr>
<?php
foreach ($type as $id => $row) {
	$row['parser'] = isset($parser[$row['parser']]) ? $parser[$row['parser']] : $lang->phrase('admin_cms_doc_parser_unknown');
	$row['inline'] = ($row['inline'] == 1) ? $lang->phrase('admin_cms_doc_static') : $lang->phrase('admin_cms_doc_dynamic');
?>
  <tr>
   <td class="mbox"><a href="admin.php?action=cms&job=doc_add2&type=<?php echo $id; ?>"><?php echo $row['title']; ?></a></td>
   <td class="mbox"><?php echo $row['template']; ?></td>
   <td class="mbox"><?php echo $row['parser']; ?></td>
   <td class="mbox"><?php echo $row['inline']; ?></td>
  </tr>
<?php } ?>
 </table>
	<?php
	echo foot();
}
elseif ($job == 'doc_add2') {
	$tpl = new tpl();
	$type = $gpc->get('type', int);
	$types = doctypes();
	$format = $types[$type];
	echo head();
  	$groups = $db->query("SELECT id, name FROM {$db->pre}groups", __LINE__, __FILE__);
?>
<form id="form" method="post" action="admin.php?action=cms&job=doc_add3&type=<?php echo $type; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="4"><?php echo $lang->phrase('admin_cms_create_doc_step_2'); ?></td>
  </tr>
  <tr>
   <td class="mbox">
	<?php if ($format['inline'] == 1 && empty($format['template'])) { ?><span class="stext right"><?php echo $lang->phrase('admin_cms_if_no_title_can_be_parsed'); ?></span><?php } ?>
	<?php echo $lang->phrase('admin_cms_news_title'); ?><br />
	<input type="text" name="title" size="60" />
   </td>
  </tr>
  <?php if($format['remote'] != 1) { ?>
  <tr>
   <td class="mbox">
	<?php echo $lang->phrase('admin_cms_doc_sourcecode'); ?><br />
	<?php
	$editorpath = 'templates/editor/';
	$path = $tpl->altdir.'docs/'.$format['template'].'.html';
	if ($format['inline'] == 1 && file_exists($path)) {
		$preload = file_get_contents($path);
	}
	else {
		$preload = '';
	}
	if($format['parser'] == 3) {
		BBCodeToolBox();
	}
	?>
	<textarea id="template" name="template" rows="20" cols="110" class="texteditor"><?php echo $preload; ?></textarea>
	<?php if ($format['parser'] == 1) { ?>
	<link rel="stylesheet" type="text/css" href="<?php echo $editorpath; ?>rte.css" />
	<script language="JavaScript" type="text/javascript" src="<?php echo $editorpath; ?>lang/en.js"></script>
	<script language="JavaScript" type="text/javascript" src="<?php echo $editorpath; ?>richtext.js"></script>
	<script language="JavaScript" type="text/javascript" src="<?php echo $editorpath; ?>html2xhtml.js"></script>
	<script language="JavaScript" type="text/javascript">
	<!--
	window.onload = function() {
		forms = FetchElement('form');
		ta = FetchElement('template');
		forms.onsubmit = function() {
	   		updateRTE('rte');
	  		ta.value = forms.rte.value;
	  		forms.submit();
		};
		ta.style.display = 'none';
	};
	var lang = "en";
	var encoding = "iso-8859-1";
	initRTE("templates/editor/images/", "<?php echo $editorpath; ?>", '', true);
	writeRichText('rte', FetchElement('template').value, '', 750, 350, true, false, false);
	//-->
	</script>
	<?php } ?>
   </td>
  </tr>
  <?php } ?>
  <tr>
   <td class="mbox">
   <?php if($format['remote'] != 1) { ?><span class="stext right"><?php echo $lang->phrase('admin_cms_if_path_is_given'); ?></span><?php } ?>
   <?php echo $lang->phrase('admin_cms_doc_file'); ?><br />
	<input type="text" name="file" size="60" />
   </td>
  </tr>
  <tr>
   <td class="mbox"><span class="stext right"><?php echo $lang->phrase('admin_cms_doc_groups_text'); ?></span><?php echo $lang->phrase('admin_cms_doc_groups'); ?><br />
   <?php while ($row = $db->fetch_assoc($groups)) { ?>
	<input type="checkbox" name="groups[]" checked="checked" value="<?php echo $row['id']; ?>"> <?php echo $row['name']; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox">
	<?php echo $lang->phrase('admin_cms_doc_active'); ?><br />
	<input type="checkbox" value="1" name="active" />
   </td>
  </tr>
  <tr><td class="ubox" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_cms_form_add'); ?>" /></td></tr>
 </table>
</form>
<?php
echo foot();
}
elseif ($job == 'doc_add3') {
	echo head();

	$type = $gpc->get('type', int);
	$title = $gpc->get('title', str);
	$active = $gpc->get('active', int);
  	$groups = $gpc->get('groups', arr_int);
  	$file = $gpc->get('file', none);
  	$file = trim($file);

	$types = doctypes();
	$format = $types[$type];

	if ($format['remote'] != 1) {
	  	if (empty($file)) {
	  		$content = $gpc->get('template', str);
	  	}
	  	else {
	  		$content = $gpc->get('template', none);
	  		if ($filesystem->file_put_contents($file, $content) > 0) {
	  			$content = '';
	  		}
	  		else {
	  			$content = $gpc->save_str($content);
	  			$file = '';
	  		}
		}
	}
	else {
		$content = '';
	}

	if (empty($title)) {
		error('admin.php?action=cms&job=doc_add', 'Title is empty!');
	}

	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'groups', __LINE__, __FILE__);
	$count = $db->fetch_num($result);
	if (count($groups) == $count[0]) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}

	$time = time();

	$db->query("INSERT INTO {$db->pre}documents ( `title` , `content` , `author` , `date` , `update` , `type` , `groups` , `active` , `file` ) VALUES ('{$title}', '{$content}', '{$my->id}', '{$time}' , '{$time}' , '{$type}', '{$groups}', '{$active}', '{$file}')", __LINE__, __FILE__);

	$delobj = $scache->load('wraps');
	$delobj->delete();

	ok('admin.php?action=cms&job=doc', $lang->phrase('admin_cms_document_successfully_added'));
}
elseif ($job == 'doc_delete') {
	echo head();
	$delete = $gpc->get('delete', arr_int);
	if (count($delete) > 0) {
		$deleteids = array();
		foreach ($delete as $did) {
			$deleteids[] = 'id = '.$did;
		}
		$result = $db->query('SELECT file FROM '.$db->pre.'documents WHERE '.implode(' OR ',$deleteids), __LINE__, __FILE__);
		while ($row = $db->fetch_assoc($result)) {
			$rest = @substr(strtolower($row['file']), 0, 7);
			if (!empty($row['file']) && $rest != 'http://') {
				$filesystem->unlink($row['file']);
			}
		}

		$db->query('DELETE FROM '.$db->pre.'documents WHERE '.implode(' OR ',$deleteids), __LINE__, __FILE__);
		$anz = $db->affected_rows();

		$delobj = $scache->load('wraps');
		$delobj->delete();

		ok('admin.php?action=cms&job=doc', $lang->phrase('admin_cms_documents_deleted'));
	}
	else {
		error('admin.php?action=cms&job=doc', $lang->phrase('admin_cms_havent_checked_box'));
	}
}
elseif ($job == 'doc_edit') {
	echo head();
	$tpl = new tpl();
	$id = $gpc->get('id', int);
	$types = doctypes();
	$result = $db->query("SELECT * FROM {$db->pre}documents WHERE id = '{$id}'", __LINE__, __FILE__);
	$row = $db->fetch_assoc($result);
	if ($db->num_rows($result) == 0) {
		error('admin.php?action=cms&job=doc', 'Keine gültige ID übergeben');
	}
	$format = $types[$row['type']];
	if (!empty($row['file']) && $format['remote'] != 1 && !check_hp($row['file'])) {
		$row['content'] = file_get_contents($row['file']);
	}
	$groups = $db->query("SELECT id, name FROM {$db->pre}groups", __LINE__, __FILE__);
	$garr = explode(',', $row['groups']);

	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();

?>
<form id="form" method="post" action="admin.php?action=cms&job=doc_edit2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="4"><?php echo $lang->phrase('admin_cms_create_doc_step_2'); ?></td>
  </tr>
  <tr>
   <td class="mbox">
	<?php if ($format['inline'] == 1 && empty($format['template'])) { ?><span class="stext right"><?php echo $lang->phrase('admin_cms_if_no_title_can_be_parsed'); ?></span><?php } ?>
	<?php echo $lang->phrase('admin_cms_news_title'); ?><br />
	<input type="text" name="title" size="60" value="<?php echo $gpc->prepare($row['title']); ?>" />
   </td>
  </tr>
  <?php if($format['remote'] != 1) { ?>
  <tr>
   <td class="mbox">
	<?php echo $lang->phrase('admin_cms_doc_sourcecode'); ?><br />
	<?php
	if($format['parser'] == 3) {
		BBCodeToolBox();
	}
	?>
	<textarea id="template" name="template" rows="20" cols="110" class="texteditor"><?php echo $row['content']; ?></textarea>
	<?php if ($format['parser'] == 1) { ?>
	<link rel="stylesheet" type="text/css" href="templates/editor/rte.css" />
	<script language="JavaScript" type="text/javascript" src="templates/editor/lang/en.js"></script>
	<script language="JavaScript" type="text/javascript" src="templates/editor/richtext.js"></script>
	<script language="JavaScript" type="text/javascript" src="templates/editor/html2xhtml.js"></script>
	<script language="JavaScript" type="text/javascript">
	<!--
	window.onload = function() {
		forms = FetchElement('form');
		ta = FetchElement('template');
		forms.onsubmit = function() {
	   		updateRTE('rte');
	  		ta.value = forms.rte.value;
	  		forms.submit();
		};
		ta.style.display = 'none';
	};
	var lang = "en";
	var encoding = "iso-8859-1";
	initRTE("templates/editor/images/", "templates/editor/", '', true);
	writeRichText('rte', FetchElement('template').value, '', 750, 350, true, false, false);
	//-->
	</script>
	<?php } ?>
   </td>
  </tr>
  <?php } ?>
  <tr>
   <td class="mbox">
   <?php if($format['remote'] != 1) { ?><span class="stext right"><?php echo $lang->phrase('admin_cms_if_path_is_given'); ?></span><?php } ?>
   <?php echo $lang->phrase('admin_cms_doc_file'); ?><br />
	<input type="text" name="file" value="<?php echo $row['file']; ?>" size="60" />
   </td>
  </tr>
  <tr>
   <td class="mbox"><span class="stext right"><?php echo $lang->phrase('admin_cms_doc_groups_text'); ?></span><?php echo $lang->phrase('admin_cms_doc_groups'); ?><br />
   <?php while ($g = $db->fetch_assoc($groups)) { ?>
	<input type="checkbox" name="groups[]"<?php echo iif($row['groups'] == 0 || in_array($g['id'], $garr),'checked="checked"'); ?> value="<?php echo $g['id']; ?>"> <?php echo $g['name']; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox">
	<?php echo $lang->phrase('admin_cms_doc_author_change'); ?><br />
	<input type="radio" value="<?php echo $row['author']; ?>" name="author" checked="checked" /><?php echo $lang->phrase('admin_cms_keep_current_author'); ?><strong><?php echo isset($memberdata[$row['author']]) ? $memberdata[$row['author']] : 'Unknown'; ?></strong><br />
	<input type="radio" value="<?php echo $my->id; ?>" name="author" /><?php echo $lang->phrase('admin_cms_change_author_to'); ?><strong><?php echo $my->name; ?></strong>
   </td>
  </tr>
  <tr>
   <td class="mbox">
	<?php echo $lang->phrase('admin_cms_doc_active'); ?><br />
	<input type="checkbox" value="1" name="active"<?php echo iif($row['active'] == 1, ' checked="checked"'); ?> />
   </td>
  </tr>
  <tr><td class="ubox" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_cms_form_edit'); ?>" /></td></tr>
 </table>
</form>
<?php
echo foot();
}
elseif ($job == 'doc_edit2') {
	echo head();

	$id = $gpc->get('id', int);
	$title = $gpc->get('title', str);
	$active = $gpc->get('active', int);
	$author = $gpc->get('author', int);
  	$groups = $gpc->get('groups', arr_int);
  	$file = $gpc->get('file', none);
  	$file = trim($file);

	$result = $db->query("SELECT type FROM {$db->pre}documents WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows($result) == 0) {
		error('admin.php?action=cms&job=doc', $lang->phrase('admin_cms_document_doesnt_exist'));
	}
	$doc = $db->fetch_assoc($result);
	$types = doctypes();
	$format = $types[$doc['type']];

	if ($format['remote'] != 1) {
	  	if (empty($file)) {
	  		$content = $gpc->get('template', str);
	  	}
	  	else {
	  		$content = $gpc->get('template', none);
	  		if ($filesystem->file_put_contents($file, $content) > 0) {
	  			$content = '';
	  		}
	  		else {
	  			$content = $gpc->save_str($content);
	  			$file = '';
	  		}
		}
	}
	else {
		$content = '';
	}

	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'groups');
	$count = $db->fetch_num($result);
	if (count($groups) == $count[0]) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}

	$time = time();

	$db->query("UPDATE {$db->pre}documents SET `title` = '{$title}', `content` = '{$content}', `update` = '{$time}', `groups` = '{$groups}', `active` = '{$active}', `file` = '{$file}', `author` = '{$author}' WHERE id = '{$id}' LIMIT 1",__LINE__,__FILE__);

	$delobj = $scache->load('wraps');
	$delobj->delete();

	ok('admin.php?action=cms&job=doc', $lang->phrase('admin_cms_document_successfully_changed'));
}
elseif ($job == 'doc_code') {
	echo head();
	$codelang = $scache->load('syntaxhighlight');
	$clang = $codelang->get();
	?>
	<script src="admin/html/editor.js" type="text/javascript"></script>
	<table class="border">
	<tr><td class="obox"><?php echo $lang->phrase('admin_cms_bb_tag_code'); ?></td></tr>
	<tr><td class="mbox">
	<strong><?php echo $lang->phrase('admin_cms_choose_programming_language'); ?></strong><br /><br />
	<ul>
	   <li><input type="radio" name="data" onclick="InsertTagsCode('[code]','[/code]')" /> <?php echo $lang->phrase('admin_cms_no_syntax_highlighting'); ?></li>
	   <?php foreach ($clang as $row) { ?>
	   <li><input type="radio" name="data" onclick="InsertTagsCode('[code=<?php echo $row['short']; ?>]','[/code]')" /> <?php echo $row['name']; ?></li>
	   <?php } ?>
	</ul>
	</td></tr>
	</table>
	<?php
	echo foot();
}
elseif ($job == 'feed') {
	$result = $db->query("SELECT * FROM {$db->pre}grab ORDER BY title", __LINE__, __FILE__);
	$num = $db->num_rows($result);
	echo head();
?>
<form name="form" method="post" action="admin.php?action=cms&job=feed_delete">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="5"><span style="float: right;"><a class="button" href="admin.php?action=cms&job=feed_add"><?php echo $lang->phrase('admin_cms_add_newsfeed'); ?></a></span><?php echo $lang->phrase('admin_cms_impor_of_newsfeeds'); ?> (<?php echo $num; ?>)</td>
  </tr>
  <tr>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_cms_news_delete'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all('delete[]');" name="all" value="1" /> <?php echo $lang->phrase('admin_cms_news_delete_all'); ?></span></td>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_cms_news_id'); ?></td>
   <td class="ubox" width="35%"><?php echo $lang->phrase('admin_cms_news_title_head'); ?></td>
   <td class="ubox" width="45%"><?php echo $lang->phrase('admin_cms_news_file'); ?></td>
   <td class="ubox" width="10%"><?php echo $lang->phrase('admin_cms_news_entries'); ?></td>
  </tr>
<?php
	while ($row = $db->fetch_assoc($result)) {
	if ($row['entries'] == 0) {
		$row['entries'] = 'All';
	}
?>
  <tr>
   <td class="mbox" width="5%"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
   <td class="mbox" width="5%"><?php echo $row['id']; ?></td>
   <td class="mbox" width="35%"><a href="admin.php?action=cms&job=feed_edit&id=<?php echo $row['id']; ?>"><?php echo $row['title']; ?></a></td>
   <td class="mbox" width="45%"><a href="<?php echo $row['file']; ?>" target="_blank"><?php echo $row['file']; ?></a></td>
   <td class="mbox" width="10%"><?php echo $row['entries']; ?></td>
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" width="100%" colspan="5" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_cms_form_delete'); ?>"></td>
  </tr>
 </table>
</form>
<?php
	echo foot();
}
elseif ($job == 'feed_add') {
echo head();
?>
<form name="form" method="post" action="admin.php?action=cms&job=feed_add2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_cms_add_newsfeed'); ?></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_cms_news_title'); ?><br><span class="stext"><?php echo $lang->phrase('admin_cms_news_title_text'); ?></td>
   <td class="mbox"><input type="text" name="temp1" size="60"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_cms_news_url'); ?><br><span class="stext"><?php echo $lang->phrase('admin_cms_news_url_text'); ?></td>
   <td class="mbox"><input type="text" name="temp2" size="60"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_cms_news_number_of_entries'); ?><br><span class="stext"><?php echo $lang->phrase('admin_cms_news_number_of_entries_text'); ?></td>
   <td class="mbox"><input type="text" name="value" size="3"></td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_cms_form_add'); ?>"></td>
  </tr>
 </table>
</form>
<?php
	echo foot();
}
elseif ($job == 'feed_add2') {
	echo head();

	$title = $gpc->get('temp1', str);
	$file = $gpc->get('temp2', str);
	$entries = $gpc->get('value', int);

	if (empty($title)) {
		error('admin.php?action=cms&job=feed_add', $lang->phrase('admin_cms_no_title_specified'));
	}
	if (empty($file)) {
		error('admin.php?action=cms&job=feed_add', $lang->phrase('admin_cms_no_url_specified'));
	}
	if (empty($entries)) {
		$entries = 0;
	}

	$db->query('INSERT INTO '.$db->pre.'grab (title, file, entries) VALUES ("'.$title.'","'.$file.'","'.$entries.'")', __LINE__, __FILE__);

	$delobj = $scache->load('grabrss');
	$delobj->delete();

	ok('admin.php?action=cms&job=feed', $lang->phrase('admin_cms_newsfeed_successfully_added'));
}
elseif ($job == 'feed_delete') {
	echo head();
	$delete = $gpc->get('delete', arr_int);
	if (count($delete) > 0) {
		$deleteids = array();
		foreach ($delete as $did) {
			$deleteids[] = 'id = '.$did;
		}

		$db->query('DELETE FROM '.$db->pre.'grab WHERE '.implode(' OR ',$deleteids), __LINE__, __FILE__);
		$anz = $db->affected_rows();

		$delobj = $scache->load('grabrss');
		$delobj->delete();

		ok('admin.php?action=cms&job=feed', $lang->phrase('admin_cms_newsfeeds_successfully_deleted'));
	}
	else {
		error('admin.php?action=cms&job=feed', $lang->phrase('admin_cms_no_newsfeed_selected'));
	}
}
elseif ($job == 'feed_edit') {
echo head();
$id = $gpc->get('id', int);
if (empty($id)) {
	error('admin.php?action=cms&job=feed', 'Invalid ID given');
}
$result = $db->query('SELECT * FROM '.$db->pre.'grab WHERE id = '.$id, __LINE__, __FILE__);
$row = $db->fetch_assoc($result);

?>
<form name="form" method="post" action="admin.php?action=cms&job=feed_edit2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_cms_news_edit_document'); ?></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_cms_news_title'); ?><br><span class="stext"><?php echo $lang->phrase('admin_cms_news_title_text'); ?></span></td>
   <td class="mbox"><input type="text" name="temp1" size="60" value="<?php echo $gpc->prepare($row['title']); ?>"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_cms_news_url'); ?><br><span class="stext"><?php echo $lang->phrase('admin_cms_news_url_text'); ?></span></td>
   <td class="mbox"><input type="text" name="temp2" size="60" value="<?php echo $row['file']; ?>"></td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_cms_news_number_of_entries'); ?><br><span class="stext"><?php echo $lang->phrase('admin_cms_news_number_of_entries_text'); ?></span></td>
   <td class="mbox"><input type="text" name="value" size="3" value="<?php echo $row['entries']; ?>"></td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_cms_form_edit'); ?>"></td>
  </tr>
 </table>
</form>
<?php
	echo foot();
}
elseif ($job == 'feed_edit2') {
	echo head();

	$title = $gpc->get('temp1', str);
	$file = $gpc->get('temp2', str);
	$entries = $gpc->get('value', int);
	$id = $gpc->get('id', int);
	if (!is_id($id)) {
		error('admin.php?action=cms&job=feed', 'Invalid ID given');
	}
	if (empty($title)) {
		error('admin.php?action=cms&job=feed_edit&id='.$id, 'No title specified');
	}
	if (empty($file)) {
		error('admin.php?action=cms&job=feed_edit&id='.$id, 'No URL specified');
	}
	if (empty($entries)) {
		$entries = 0;
	}

	$db->query('UPDATE '.$db->pre.'grab SET file = "'.$file.'", title = "'.$title.'", entries = "'.$entries.'" WHERE id = "'.$id.'"', __LINE__, __FILE__);

	$delobj = $scache->load('grabrss');
	$delobj->delete();

	ok('admin.php?action=cms&job=feed', $lang->phrase('admin_cms_newsfeed_successfully_updated'));
}
?>
