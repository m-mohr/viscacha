<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// PK: MultiLangAdmin
$lang->group("admin/cms");

require('classes/class.phpconfig.php');
$myini = new INI();

function BBCodeToolBox($id, $content = '', $taAttr = '') {
	global $tpl, $lang, $scache, $config;

	$lang->group("bbcodes");

	$taAttr = ' '.trim($taAttr);

	$cache = $scache->load('custombb');
	$cbb = $cache->get();
	foreach ($cbb as $key => $bb) {
		if (empty($bb['buttonimage'])) {
			unset($cbb[$key]);
			continue;
		}
		$cbb[$key]['title'] = viscacha_htmlspecialchars($bb['title']);
		if ($bb['twoparams']) {
			$cbb[$key]['href'] = "InsertTags('{$id}', '[{$bb['tag']}=]','[/{$bb['tag']}]');";
		}
		else {
			$cbb[$key]['href'] = "InsertTags('{$id}', '[{$bb['tag']}]','[/{$bb['tag']}]');";
		}
	}

	$cache = $scache->load('smileys');
	$cache->seturl($config['smileyurl']);
	$smileydata = $cache->get();
	$smileys = array(0 => array(), 1 => array());
	foreach ($smileydata as $bb) {
	   	if ($bb['show'] == 1) {
			$smileys[1][] = $bb;
		}
		else {
			$smileys[0][] = $bb;
		}
	}
	?>
	<script src="admin/html/editor/bbcode.js" type="text/javascript"></script>
	<table class="editor_textarea_outer">
		<tr><td class="editor_toolbar">
			<a id="menu_bbcolor_<?php echo $id; ?>" href="#" onmouseover="RegisterMenu('bbcolor_<?php echo $id; ?>');" class="editor_toolbar_dropdown"><img src="admin/html/images/desc.gif" alt="<?php echo $lang->phrase('bbcodes_expand'); ?>" /> <?php echo $lang->phrase('bbcodes_color'); ?></a>
			<div class="popup" id="popup_bbcolor_<?php echo $id; ?>">
			<div class="bbcolor">
				<script type="text/javascript">
					document.write(generateColorPicker("InsertTags('<?php echo $id; ?>', '[color=<color>]', '[/color]')"));
				</script>
			</div>
			</div>
			<img src="admin/html/editor/images/seperator.gif" alt="" />
			<a id="menu_bbsize" href="#" onmouseover="RegisterMenu('bbsize');" class="editor_toolbar_dropdown"><img src="admin/html/images/desc.gif" alt="<?php echo $lang->phrase('bbcodes_expand'); ?>" /> <?php echo $lang->phrase('bbcodes_size'); ?></a>
			<div class="popup" id="popup_bbsize">
		   	<ul>
				<li><span onclick="InsertTags('<?php echo $id; ?>', '[size=large]','[/size]')" style="font-size: 1.3em;"><?php echo $lang->phrase('bbcodes_size_large'); ?></span></li>
				<li><span onclick="InsertTags('<?php echo $id; ?>', '[size=small]','[/size]')" style="font-size: 0.8em;"><?php echo $lang->phrase('bbcodes_size_small'); ?></span></li>
				<li><span onclick="InsertTags('<?php echo $id; ?>', '[size=extended]','[/size]')" style="letter-spacing: 3px;"><?php echo $lang->phrase('bbcodes_size_extended'); ?></span></li>
			</ul>
			</div>
			<img src="admin/html/editor/images/seperator.gif" alt="" />
			<a id="menu_bbhx_<?php echo $id; ?>" href="#" onmouseover="RegisterMenu('bbhx_<?php echo $id; ?>');" class="editor_toolbar_dropdown"><img src="admin/html/images/desc.gif" alt="<?php echo $lang->phrase('bbcodes_expand'); ?>" /> <?php echo $lang->phrase('bbcodes_header'); ?></a>
			<div class="popup" id="popup_bbhx_<?php echo $id; ?>">
			<ul>
				<li><h4 onclick="InsertTags('<?php echo $id; ?>', '[h=large]','[/h]')" style="margin: 0px; font-size: 14pt;"><?php echo $lang->phrase('bbcodes_header_h1'); ?></h4></li>
				<li><h5 onclick="InsertTags('<?php echo $id; ?>', '[h=middle]','[/h]')" style=" margin: 0px; font-size: 13pt;"><?php echo $lang->phrase('bbcodes_header_h2'); ?></h5></li>
				<li><h6 onclick="InsertTags('<?php echo $id; ?>', '[h=small]','[/h]')" style="margin: 0px; font-size: 12pt;"><?php echo $lang->phrase('bbcodes_header_h3'); ?></h6></li>
			</ul>
			</div>
			<img src="admin/html/editor/images/seperator.gif" alt="" />
			<a id="menu_bbtable_<?php echo $id; ?>" href="#" onmouseover="RegisterMenu('bbtable_<?php echo $id; ?>');" class="editor_toolbar_dropdown"><img src="admin/html/images/desc.gif" alt="<?php echo $lang->phrase('bbcodes_expand'); ?>" /> <?php echo $lang->phrase('bbcodes_table'); ?></a>
			<div class="popup" id="popup_bbtable_<?php echo $id; ?>">
			<div class="bbtable">
				<input type="checkbox" style="height: 2em;" id="table_head_<?php echo $id; ?>" value="1" /> <?php echo $lang->phrase('bbcodes_table_show_head'); ?>
				<br class="newinput" /><hr class="formsep" />
				<input type="text" size="4" id="table_rows_<?php echo $id; ?>" value="2" /> <?php echo $lang->phrase('bbcodes_table_rows'); ?>
				<br class="newinput" /><hr class="formsep" />
				<input type="text" size="4" id="table_cols_<?php echo $id; ?>" value="2" /> <?php echo $lang->phrase('bbcodes_table_cols'); ?>
				<br class="newinput" /><hr class="formsep" />
				<div class="center">[ <b><a href="javascript:InsertTable('<?php echo $id; ?>')"><?php echo $lang->phrase('bbcodes_table_insert_table'); ?></a></b> ]</div>
			</div>
			</div>
			<?php
			echo iif(count($cbb), '<img src="admin/html/editor/images/seperator.gif" alt="" />');
			foreach ($cbb as $bb) { ?>
			<img src="<?php echo $bb['buttonimage']; ?>" onclick="<?php echo $bb['href']; ?>" title="<?php echo $bb['title']; ?>" alt="<?php echo $bb['title']; ?>" class="editor_toolbar_button" />
			<?php } ?>
		</td></tr>
		<tr><td class="editor_toolbar">
			<img src="admin/html/editor/images/bold.gif" onclick="InsertTags('<?php echo $id; ?>', '[b]','[/b]');" title="<?php echo $lang->phrase('bbcodes_bold'); ?>" alt="<?php echo $lang->phrase('bbcodes_bold'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/italic.gif" onclick="InsertTags('<?php echo $id; ?>', '[i]','[/i]');" title="<?php echo $lang->phrase('bbcodes_italic'); ?>" alt="<?php echo $lang->phrase('bbcodes_italic'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/underline.gif" onclick="InsertTags('<?php echo $id; ?>', '[u]','[/u]');" title="<?php echo $lang->phrase('bbcodes_underline'); ?>" alt="<?php echo $lang->phrase('bbcodes_underline'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/seperator.gif" alt="" />
			<img src="admin/html/editor/images/left.gif" onclick="InsertTags('<?php echo $id; ?>','[align=left]','[/align]');" title="<?php echo $lang->phrase('bbcodes_align_left'); ?>" alt="<?php echo $lang->phrase('bbcodes_align_left'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/center.gif" onclick="InsertTags('<?php echo $id; ?>','[align=center]','[/align]');" title="<?php echo $lang->phrase('bbcodes_align_center'); ?>" alt="<?php echo $lang->phrase('bbcodes_align_center'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/right.gif" onclick="InsertTags('<?php echo $id; ?>','[align=right]','[/align]');" title="<?php echo $lang->phrase('bbcodes_align_right'); ?>" alt="<?php echo $lang->phrase('bbcodes_align_right'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/justify.gif" onclick="InsertTags('<?php echo $id; ?>','[align=justify]','[/align]');" title="<?php echo $lang->phrase('bbcodes_align_justify'); ?>" alt="<?php echo $lang->phrase('bbcodes_align_justify'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/seperator.gif" alt="" />
			<img src="admin/html/editor/images/img.gif" onclick="InsertTags('<?php echo $id; ?>', '[img]','[/img]');" title="<?php echo $lang->phrase('bbcodes_img'); ?>" alt="<?php echo $lang->phrase('bbcodes_img'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/url.gif" onclick="InsertTagsURL('<?php echo $id; ?>', '[url={param1}]{param2}','[/url]');" title="<?php echo $lang->phrase('bbcodes_url'); ?>" alt="<?php echo $lang->phrase('bbcodes_url'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/email.gif" onclick="InsertTags('<?php echo $id; ?>', '[email]','[/email]');" title="<?php echo $lang->phrase('bbcodes_email'); ?>" alt="<?php echo $lang->phrase('bbcodes_email'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/seperator.gif" alt="" />
			<img src="admin/html/editor/images/quote.gif" onclick="InsertTags('<?php echo $id; ?>', '[quote]','[/quote]');" title="<?php echo $lang->phrase('bbcodes_quote'); ?>" alt="<?php echo $lang->phrase('bbcodes_quote'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/ot.gif" onclick="InsertTags('<?php echo $id; ?>', '[ot]','[/ot]');" title="<?php echo $lang->phrase('bbcodes_ot'); ?>" alt="<?php echo $lang->phrase('bbcodes_ot'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/edit.gif" onclick="InsertTags('<?php echo $id; ?>', '[edit]','[/edit]');" title="<?php echo $lang->phrase('bbcodes_edit'); ?>" alt="<?php echo $lang->phrase('bbcodes_edit'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/code.gif" onclick="InsertTags('<?php echo $id; ?>', '[code]','[/code]');" title="<?php echo $lang->phrase('bbcodes_code'); ?>" alt="<?php echo $lang->phrase('bbcodes_code'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/seperator.gif" alt="" />
			<img src="admin/html/editor/images/list_unordered.gif" onclick="InsertTagsList('<?php echo $id; ?>');" title="<?php echo $lang->phrase('bbcodes_list'); ?>" alt="<?php echo $lang->phrase('bbcodes_list'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/list_ordered.gif" onclick="InsertTagsList('<?php echo $id; ?>', 'ol');" title="<?php echo $lang->phrase('bbcodes_list_ol'); ?>" alt="<?php echo $lang->phrase('bbcodes_list_ol'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/seperator.gif" alt="" />
			<img src="admin/html/editor/images/hr.gif" onclick="InsertTags('<?php echo $id; ?>', '[hr]','');" title="<?php echo $lang->phrase('bbcodes_hr'); ?>" alt="<?php echo $lang->phrase('bbcodes_hr'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/tt.gif" onclick="InsertTags('<?php echo $id; ?>', '[tt]','[/tt]');" title="<?php echo $lang->phrase('bbcodes_tt'); ?>" alt="<?php echo $lang->phrase('bbcodes_tt'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/seperator.gif" alt="" />
			<img src="admin/html/editor/images/subscript.gif" onclick="InsertTags('<?php echo $id; ?>', '[sub]','[/sub]');" title="<?php echo $lang->phrase('bbcodes_sub'); ?>" alt="<?php echo $lang->phrase('bbcodes_sub'); ?>" class="editor_toolbar_button" />
			<img src="admin/html/editor/images/superscript.gif" onclick="InsertTags('<?php echo $id; ?>', '[sup]','[/sup]');" title="<?php echo $lang->phrase('bbcodes_sup'); ?>" alt="<?php echo $lang->phrase('bbcodes_sup'); ?>" class="editor_toolbar_button" />
		</td></tr>
		<tr><td class="editor_toolbar" style="height: auto; overflow: auto;">
			<?php foreach ($smileys[1] as $bb) { ?>
			<img src="<?php echo $bb['replace']; ?>" onclick="InsertTags('<?php echo $id; ?>', ' <?php echo $bb['jssearch'] ?> ', '');" title="<?php echo $bb['desc']; ?>" alt="<?php echo $bb['desc']; ?>" class="editor_toolbar_smiley" onmouseover="buttonOverSmiley(this)" onmouseout="buttonOutSmiley(this)" /></a>
			<?php } ?>
		<img src="admin/html/editor/images/seperator.gif" alt="" />
			<?php if (count($smileys[0]) > 0) { ?>
			<a id="menu_bbsmileys_<?php echo $id; ?>" href="#" onmouseover="RegisterMenu('bbsmileys_<?php echo $id; ?>');" class="editor_toolbar_dropdown"><img src="admin/html/images/desc.gif" alt="" /> <?php echo $lang->phrase('more_smileys'); ?></a>
			<div class="popup" id="popup_bbsmileys_<?php echo $id; ?>">
			<strong><?php echo $lang->phrase('more_smileys'); ?></strong>
			<ul class="bbsmileys">
			<?php foreach ($smileys[0] as $bb) { ?>
			  <li><span class="popup_line stext" onclick="InsertTags('<?php echo $id; ?>', ' <?php echo $bb['jssearch'] ?> ', '')"><img src="<?php echo $bb['replace']; ?>" alt="<?php echo $bb['desc']; ?>" /> <?php echo $bb['desc']; ?></span></li>
			<?php }?>
			</ul>
			</div>
			<?php } ?>
		</td></tr>
		<tr><td class="editor_textarea_td">
			<textarea name="<?php echo $id; ?>" id="<?php echo $id; ?>" class="editor_textarea_inner"<?php echo $taAttr; ?>><?php echo $content; ?></textarea>
		</td></tr>
		<tr><td class="editor_statusbar" style="text-align: right;">
			<a href="javascript:resize_textarea('<?php echo $id; ?>', 1);"><?php echo $lang->phrase('textarea_increase_size'); ?></a> &middot;
			<a href="javascript:resize_textarea('<?php echo $id; ?>', -1);"><?php echo $lang->phrase('textarea_decrease_size'); ?></a>
		</td></tr>
	</table>
	<?php
}
function parseNavPosSetting() {
	global $admconfig;
	$explode = preg_split("~(\r\n|\r|\n)+~u", trim($admconfig['nav_positions']));
	$arr = array();
	foreach ($explode as $val) {
		$dat = explode('=', $val, 2);
		$arr[$dat[0]] = $dat[1];
	}
	return $arr;
}
function attachWYSIWYG() {
	$r = '<script type="text/javascript" src="admin/html/editor/wysiwyg.js"></script>';
	$r .= '<script type="text/javascript"> WYSIWYG.attach("all", full); </script>';
	return $r;
}
function getNavTitle() {
	global $gpc, $db;
	$title = $gpc->get('title', none);
	$title = trim($title);
	$parts = explode('->', $title);
	if (!empty($parts[0])) {
		$parts[0] = mb_strtolower($parts[0]);
		if ($parts[0] == 'doc' || $parts[0] == 'lang') {
			$title = $db->escape($title);
		}
		else {
			$title = $gpc->save_str($title);
		}
		return $title;
	}
	else {
		return '';
	}
}

define('EDITOR_IMAGEDIR', './uploads/images/');
$supportedextentions = array('gif','png','jpeg','jpg');

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
	$result = $db->execute("SELECT * FROM {$db->pre}menu ORDER BY position, ordering, id");
	$sqlcache = array();
	$cat = array();
	$sub = array();
	while ($row = $result->fetch()) {
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
			$type[] = $lang->phrase('admin_cms_plugin');
		}
		if ($head['active'] == 0) {
			$type[] = $lang->phrase('admin_cms_inactive');
		}
	?>
	<tr class="mmbox">
	<td width="50%">
	<?php echo $plugins->navLang($head['name'], true); ?><?php echo iif(count($type) > 0, ' ['.implode('; ', $type).']' ); ?>
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
				echo $plugins->navLang($link['name'], true);
			}
			else {
				?>
				<a href="<?php echo $link['link']; ?>" target="<?php echo $link['param']; ?>"><?php echo $plugins->navLang($link['name'], true); ?></a>
				<?php } echo iif ($link['active'] == '0', ' ['.$lang->phrase('admin_cms_inactive').']'); ?><br />
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
							echo $plugins->navLang($sublink['name'], true);
						}
						else {
							?>
							<a href='<?php echo $sublink['link']; ?>' target='<?php echo $sublink['param']; ?>'><?php echo $plugins->navLang($sublink['name'], true); ?></a>
							<?php } echo iif ($sublink['active'] == '0', ' ['.$lang->phrase('admin_cms_inactive').']'); ?></font><br>
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
	$result = $db->execute("SELECT * FROM {$db->pre}menu WHERE id = '{$id}' LIMIT 1");
	$data = $result->fetch();
	$data['group_array'] = explode(',', $data['groups']);
	$pos = parseNavPosSetting();

	$groups = $db->execute("SELECT id, name FROM {$db->pre}groups");
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
<?php
if ($data['sub'] > 0) {
	$result = $db->execute("SELECT id, name, sub, position FROM {$db->pre}menu WHERE module = '0' ORDER BY position, ordering, id");
	$cache = array(0 => array());
	while ($row = $result->fetch()) {
		if (!isset($cache[$row['sub']]) || !is_array($cache[$row['sub']])) {
			$cache[$row['sub']] = array();
		}
		$cache[$row['sub']][] = $row;
	}
?>
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
   	$last = null;
	foreach ($cache[0] as $row) {
	   	if ($last != $row['position']) {
	   		if ($last != null) {
				echo '</optgroup>';
	   		}
	   		$last = $row['position'];
	   		echo '<optgroup label="'.viscacha_htmlspecialchars($pos[$last]).'">';
	   	}
   		$select = iif($row['id'] == $data['sub'], ' selected="selected"');
   		echo '<option style="font-weight: bold;" value="'.$row['id'].'"'.$select.'>'.$plugins->navLang($row['name'], true).'</option>';
   		if (isset($cache[$row['id']])) {
   			foreach ($cache[$row['id']] as $row) {
   				$select = iif($row['id'] == $data['sub'], ' selected="selected"');
   				echo '<option value="'.$row['id'].'"'.$select.'>+&nbsp;'.$plugins->navLang($row['name'], true).'</option>';
   			}
   		}
	}
	?>
	</optgroup>
   </select>
   </td>
  </tr>
<?php
}
if ($data['module'] > 0) {
	$plugs = $db->execute("SELECT * FROM {$db->pre}plugins WHERE position = 'navigation' ORDER BY ordering");
?>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_plugin'); ?></td>
   <td class="mbox" width="50%">
   <select name="plugin">
   <?php while ($row = $plugs->fetch()) { ?>
   <option value="<?php echo $row['id']; ?>"<?php echo iif($row['id'] == $data['module'], ' selected="selected"'); ?>><?php echo $row['name']; ?></option>
   <?php } ?>
   </select>
   </td>
  </tr>
<?php
}
if ($data['sub'] == 0) {
	$sort = $db->execute("SELECT id, name, position FROM {$db->pre}menu WHERE sub = '0' ORDER BY position, ordering, id");
?>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_sort_in_after'); ?></td>
   <td class="mbox" width="50%">
   <select name="sort">
   	<?php
   	$last = null;
   	while ($row = $sort->fetch()) {
	   	if ($last != $row['position']) {
	   		if ($last != null) {
				echo '</optgroup>';
	   		}
	   		$last = $row['position'];
	   		if (!isset($pos[$last])) {
	   			$pos[$last] = $row['position'];
	   		}
		   	echo '<optgroup label="'.viscacha_htmlspecialchars($pos[$last]).'">';
		   	unset($pos[$last]);
	   	}
   		echo '<option value="'.$row['id'].'"'.iif($row['id'] == $data['id'], ' selected="selected"').'">'.$plugins->navLang($row['name'], true).'</option>';
	}
	foreach ($pos as $key => $name) {
		?>
		</optgroup>
		<optgroup label="<?php echo viscacha_htmlspecialchars($name); ?>">
		<option value="pos_<?php echo $key; ?>">&lt;<?php echo $lang->phrase('admin_cms_sort_in_here'); ?>&gt;</option>
		<?php
	}
	?>
	</optgroup>
   </select>
   </td>
  </tr>
<?php } ?>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_groups'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_cms_nav_groups_text'); ?></span></td>
   <td class="mbox" width="50%">
   <?php while ($row = $groups->fetch()) { ?>
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
	$result = $db->execute("SELECT * FROM {$db->pre}menu WHERE id = '{$id}' LIMIT 1");
	$data = $result->fetch();

	$title = getNavTitle();
	if (empty($title)) {
		error('admin.php?action=cms&job=nav_addbox', $lang->phrase('admin_cms_err_no_title'));
	}

	$active = $gpc->get('active', int);
	$groups = $gpc->get('groups', arr_int);
	$result = $db->execute('SELECT COUNT(*) FROM '.$db->pre.'groups');
	$count = $result->fetchOne();
	if (count($groups) == $count) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}
	if ($data['sub'] > 0) {
		$target = $gpc->get('target', str);
		$url = $gpc->get('url', str);
		$sub = $gpc->get('sub', int);
		$result = $db->execute("SELECT position FROM {$db->pre}menu WHERE id = '{$sub}'");
		$pos = $gpc->save_str($result->fetch());
		$db->execute("UPDATE {$db->pre}menu SET name = '{$title}', link = '{$url}', param = '{$target}', groups = '{$groups}', sub = '{$sub}', active = '{$active}', position = '{$pos['position']}' WHERE id = '{$id}' LIMIT 1");
	}
	else {
		$sort = $gpc->get('sort', str);
		if (mb_substr($sort, 0, 4) == 'pos_') {
			$sort = array(
				'ordering' => 0,
				'position' => mb_substr($sort, 4)
			);
		}
		else {
			$sort = $db->fetch("SELECT id, ordering, position FROM {$db->pre}menu WHERE id = '{$sort}'");
			if ($sort['id'] > $id) {
				$sort['ordering']++;
			}
		}
		$module_sql = '';
		if ($data['module'] > 0) {
			$plug = $gpc->get('plugin', int);
			$position = $db->fetchOne("SELECT position FROM {$db->pre}plugins WHERE id = '{$plug}'");
			if ($position) {
				$module_sql = ", module = '{$plug}'";
				$filesystem->unlink('data/cache/modules/'.$plugins->_group($position).'.php');
				// Do not do that anymore, because it may be required
				// $db->execute("UPDATE {$db->pre}plugins SET active = '{$active}' WHERE id = '{$plug}' LIMIT 1");
			}
		}
		$db->execute("UPDATE {$db->pre}menu SET name = '{$title}', groups = '{$groups}', active = '{$active}', ordering = '{$sort['ordering']}', position = '{$sort['position']}' {$module_sql} WHERE id = '{$id}'");
	}
	$scache->load('modules_navigation')->delete();
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

	$result = $db->execute("SELECT id, sub FROM {$db->pre}menu WHERE sub = '{$id}'");
	while($row = $result->fetch()) {
		$delete[] = $row['id'];
		$result2 = $db->execute("SELECT id FROM {$db->pre}menu WHERE sub = '{$row['id']}'");
		while($row2 = $result2->fetch()) {
			$delete[] = $row2['id'];
		}
	}

	$count = count($delete);
	$ids = implode(',', $delete);
	$stmt = $db->execute("DELETE FROM {$db->pre}menu WHERE id IN ({$ids}) LIMIT {$count}");
	$anz = $stmt->getAffectedRows();

	$scache->load('modules_navigation')->delete();

	ok('admin.php?action=cms&job=nav', $lang->phrase('admin_cms_entries_deleted'));
}
elseif ($job == 'nav_move') {
	$id = $gpc->get('id', int);
	$pos = $gpc->get('value', int);
	if ($id < 1) {
		error('admin.php?action=cms&job=nav', $lang->phrase('admin_cms_invalid_id_given'));
	}
	if ($pos < 0) {
		$db->execute('UPDATE '.$db->pre.'menu SET ordering = ordering-1 WHERE id = '.$id);
	}
	elseif ($pos > 0) {
		$db->execute('UPDATE '.$db->pre.'menu SET ordering = ordering+1 WHERE id = '.$id);
	}

	$scache->load('modules_navigation')->delete();

	sendStatusCode(302, $config['furl'].'/admin.php?action=cms&job=nav');
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
	$db->execute('UPDATE '.$db->pre.'menu SET active = "'.$pos.'" WHERE id = '.$id);

	$plug = $gpc->get('plug', int);
	if ($plug > 0) {
		$position = $db->fetchOne("SELECT position FROM {$db->pre}plugins WHERE id = '{$plug}'");
		if ($position) {
			$module_sql = ", module = '{$plug}'";
			$filesystem->unlink('data/cache/modules/'.$plugins->_group($position).'.php');
			// Do not do that anymore, because it may be required
			// $db->execute("UPDATE {$db->pre}plugins SET active = '{$pos}' WHERE id = '{$plug}' LIMIT 1");
		}
	}

	$scache->load('modules_navigation')->delete();
	sendStatusCode(302, $config['furl'].'/admin.php?action=cms&job=nav');
}
elseif ($job == 'nav_addplugin') {
	echo head();
	$id = $gpc->get('id', int);
	$sort = $db->execute("SELECT id, name, position FROM {$db->pre}menu WHERE sub = '0' ORDER BY position, ordering, id");
	$plugs = $db->execute("SELECT id, name FROM {$db->pre}plugins WHERE position = 'navigation' ORDER BY ordering");
	$groups = $db->execute("SELECT id, name FROM {$db->pre}groups");
	$pos = parseNavPosSetting();
	?>
<form name="form" method="post" action="admin.php?action=cms&amp;job=nav_addplugin2">
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
   <?php while ($row = $plugs->fetch()) { ?>
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
   	while ($row = $sort->fetch()) {
	   	if ($last != $row['position']) {
	   		if ($last != null) {
				echo '</optgroup>';
	   		}
	   		$last = $row['position'];
	   		if (!isset($pos[$last])) {
	   			$pos[$last] = $row['position'];
	   		}
		   	echo '<optgroup label="'.viscacha_htmlspecialchars($pos[$last]).'">';
		   	unset($pos[$last]);
	   	}
   		echo '<option value="'.$row['id'].'">'.$plugins->navLang($row['name'], true).'</option>';
	}
	foreach ($pos as $key => $name) {
		?>
		</optgroup>
		<optgroup label="<?php echo viscacha_htmlspecialchars($name); ?>">
		<option value="pos_<?php echo $key; ?>">&lt;<?php echo $lang->phrase('admin_cms_sort_in_here'); ?>&gt;</option>
		<?php
	}
	?>
	</optgroup>
   </select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_cms_nav_groups'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_cms_nav_plug_groups_text'); ?></span></td>
   <td class="mbox" width="50%">
   <?php while ($row = $groups->fetch()) { ?>
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
	$result = $db->execute("SELECT id, name, active FROM {$db->pre}plugins WHERE id = '{$plug}' AND position = 'navigation'");
	$data = $result->fetch();
	$title = getNavTitle();
	if (empty($title)) {
		$title = $data['name'];
	}
	$sort = $gpc->get('sort', str);
	if (mb_substr($sort, 0, 4) == 'pos_') {
		$sort = array(
			'ordering' => 0,
			'position' => mb_substr($sort, 4)
		);
	}
	else {
		$result = $db->execute("SELECT ordering, position FROM {$db->pre}menu WHERE id = '{$sort}'");
		$sort = $result->fetch();
	}
	$groups = $gpc->get('groups', arr_int);
	$result = $db->execute('SELECT COUNT(*) FROM '.$db->pre.'groups');
	$count = $result->fetchOne();
	if (count($groups) == $count) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}
	$db->execute("INSERT INTO {$db->pre}menu (name, groups, ordering, active, module, position) VALUES ('{$title}','{$groups}','{$sort['ordering']}','{$data['active']}','{$data['id']}','{$sort['position']}')");
	$scache->load('modules_navigation')->delete();
	ok('admin.php?action=cms&job=nav', $lang->phrase('admin_cms_plugins_successfully_added'));
}
elseif ($job == 'nav_add') {
	echo head();
	$groups = $db->execute("SELECT id, name FROM {$db->pre}groups");
	$result = $db->execute("SELECT id, name, sub, position FROM {$db->pre}menu WHERE module = '0' ORDER BY position, ordering, id");
	$cache = array(0 => array());
	while ($row = $result->fetch()) {
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
	   		echo '<optgroup label="'.viscacha_htmlspecialchars($pos[$last]).'">';
	   	}
   		echo '<option style="font-weight: bold;" value="'.$row['id'].'">'.$plugins->navLang($row['name'], true).'</option>';
   		if (isset($cache[$row['id']])) {
   			foreach ($cache[$row['id']] as $row) {
   				echo '<option value="'.$row['id'].'">+&nbsp;'.$plugins->navLang($row['name'], true).'</option>';
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
   <?php while ($row = $groups->fetch()) { ?>
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
	$title = getNavTitle();
	$target = $gpc->get('target', str);
	$url = $gpc->get('url', str);
	$sub = $gpc->get('sub', int);
	$sort = $gpc->get('sort', int);
	$groups = $gpc->get('groups', arr_int);
	if (empty($title)) {
		error('admin.php?action=cms&job=nav_addbox', $lang->phrase('admin_cms_err_no_title'));
	}

	$pos = $db->fetchOne("SELECT position FROM {$db->pre}menu WHERE id = '{$sub}' LIMIT 1");
	if (empty($pos)) {
		$pos = array('left');
	}

	if ($sort == 1) {
		$sort = $db->fetchOne("SELECT MAX(ordering) FROM {$db->pre}menu WHERE sub = '{$sub}' LIMIT 1");
		$sort = $sort+1;
	}
	elseif ($sort == 0) {
		$sort = $db->fetchOne("SELECT MIN(ordering) FROM {$db->pre}menu WHERE sub = '{$sub}' LIMIT 1");
		$sort = $sort-1;
	}
	else {
		$sort = 0;
	}

	$result = $db->execute('SELECT COUNT(*) FROM '.$db->pre.'groups');
	$count = $result->fetchOne();
	if (count($groups) == $count) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}
	$db->execute("INSERT INTO {$db->pre}menu (name, groups, ordering, link, param, sub, position) VALUES ('{$title}','{$groups}','{$sort}','{$url}','{$target}','{$sub}','{$pos[0]}')");
	$scache->load('modules_navigation')->delete();
	ok('admin.php?action=cms&job=nav', $lang->phrase('admin_cms_link_successfully_added'));
}
elseif ($job == 'nav_addbox') {
	echo head();
	$sort = $db->execute("SELECT id, name, position FROM {$db->pre}menu WHERE sub = '0' ORDER BY position, ordering, id");
	$groups = $db->execute("SELECT id, name FROM {$db->pre}groups");
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
   	while ($row = $sort->fetch()) {
	   	if ($last != $row['position']) {
	   		if ($last != null) {
				echo '</optgroup>';
	   		}
	   		$last = $row['position'];
	   		if (!isset($pos[$last])) {
	   			$pos[$last] = $row['position'];
	   		}
		   	echo '<optgroup label="'.viscacha_htmlspecialchars($pos[$last]).'">';
		   	unset($pos[$last]);
	   	}
   		echo '<option value="'.$row['id'].'">'.$plugins->navLang($row['name'], true).'</option>';
	}
	foreach ($pos as $key => $name) {
		?>
		</optgroup>
		<optgroup label="<?php echo viscacha_htmlspecialchars($name); ?>">
		<option value="pos_<?php echo $key; ?>">&lt;<?php echo $lang->phrase('admin_cms_sort_in_here'); ?>&gt;</option>
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
   <?php while ($row = $groups->fetch()) { ?>
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
	$title = getNavTitle();
	if (empty($title)) {
		error('admin.php?action=cms&job=nav_addbox', $lang->phrase('admin_cms_err_no_title'));
	}


	$sort = $gpc->get('sort', str);
	if (mb_substr($sort, 0, 4) == 'pos_') {
		$sort = array(
			'ordering' => 0,
			'position' => mb_substr($sort, 4)
		);
	}
	else {
		$result = $db->execute("SELECT ordering, position FROM {$db->pre}menu WHERE id = '{$sort}'");
		$sort = $result->fetch(); // Keine Erhöhung des Prioritätswerts nötig, da ID der neuen Box > ID gewählten Box
	}

	$groups = $gpc->get('groups', arr_int);
	$result = $db->execute('SELECT COUNT(*) FROM '.$db->pre.'groups');
	$count = $result->fetchOne();
	if (count($groups) == $count) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}
	$db->execute("INSERT INTO {$db->pre}menu (name, groups, ordering, position) VALUES ('{$title}','{$groups}','{$sort['ordering']}','{$sort['position']}')");
	$scache->load('modules_navigation')->delete();
	ok('admin.php?action=cms&job=nav', $lang->phrase('admin_cms_box_successfully_added'));
}
elseif ($job == 'nav_docslist') {
	echo head();
	$wrap_obj = $scache->load('wraps');
	$wraps = $wrap_obj->get();
	?>
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox"><?php echo $lang->phrase('admin_cms_existing_documents_and_pages'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox">
	   <?php foreach ($wraps as $id => $data) { ksort($data['titles']); ?>
	   <input type="radio" name="data" onclick="insert_doc('docs.php?id=<?php echo $id; ?>','doc-><?php echo $id; ?>')"> <?php echo implode(' / ', $data['titles']); ?><br>
	   <?php } ?>
	   </td>
	 </table>
	<?php
	echo foot();
}
elseif ($job == 'nav_comslist') {
	echo head();
	$result = $db->execute("
		SELECT p.id, p.title, c.name
		FROM {$db->pre}packages AS p
			LEFT JOIN {$db->pre}plugins AS c ON c.module = p.id
		WHERE c.position = CONCAT('component_', p.internal)
	");
	?>
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox"><?php echo $lang->phrase('admin_cms_existing_documents'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox">
	   <?php while ($row = $result->fetch()) { ?>
	   <input type="radio" name="data" onclick="insert_doc('components.php?cid=<?php echo $row['id']; ?>','<?php echo viscacha_htmlentities($row['title']); ?>')"> <?php echo $row['name']; ?> (<?php echo $lang->phrase('admin_cms_nav_package').' '.$row['title']; ?>)<br />
	   <?php } ?>
	   </td>
	 </table>
	<?php
	echo foot();
}
elseif ($job == 'doc_create_table') {
	$htmlhead .= '<script type="text/javascript" src="admin/html/editor/wysiwyg-popup.js"></script>';
	echo head();
	?>
	<table class="border" style="width: 490px;" border="0" cellpadding="4" cellspacing="0" align="center">
		<tr><td class="obox" colspan="4"><?php echo $lang->phrase('admin_wysiwyg_table_properties'); ?></td></tr>
		<tr class="mbox">
			<td><?php echo $lang->phrase('admin_wysiwyg_table_rows'); ?></td>
			<td><input type="text" size="4" id="rows" name="rows" value="2" /></td>
			<td><?php echo $lang->phrase('admin_wysiwyg_table_cols'); ?></td>
			<td><input type="text" size="4" id="cols" name="cols" value="2" /></td>
		</tr><tr class="mbox">
			<td><?php echo $lang->phrase('admin_wysiwyg_table_width'); ?></td>
			<td>
				<input type="text" name="width" id="width" value="100" size="10" />
				<select name="widthType" id="widthType">
					<option value="%">%</option>
					<option value="px">px</option>
				</select>
			</td>
			<td><?php echo $lang->phrase('admin_wysiwyg_alignment'); ?></td>
			<td>
				<select name="alignment" id="alignment">
					<option value=""<?php echo $lang->phrase('admin_wysiwyg_alignment_not_set'); ?></option>
					<option value="left"><?php echo $lang->phrase('admin_wysiwyg_alignment_left'); ?></option>
					<option value="right"><?php echo $lang->phrase('admin_wysiwyg_alignment_right'); ?></option>
					<option value="center"><?php echo $lang->phrase('admin_wysiwyg_alignment_center'); ?></option>
				</select>
			</td>
		</tr><tr class="mbox">
			<td><?php echo $lang->phrase('admin_wysiwyg_padding'); ?></td>
			<td><input type="text" id="padding" name="padding" value="2" size="4" />px</td>
			<td><?php echo $lang->phrase('admin_wysiwyg_bgcolor'); ?></td>
			<td>
				<input type="text" name="backgroundcolor" id="backgroundcolor" value="none">
				<input type="button" value="<?php echo $lang->phrase('admin_wysiwyg_choose'); ?>" onClick="WYSIWYG_ColorInst.choose('backgroundcolor');" />
			</td>
		</tr><tr class="mbox">
			<td><?php echo $lang->phrase('admin_wysiwyg_border_width'); ?></td>
			<td><input type="text" size="4" id="borderwidth" name="borderwidth" value="0" />px</td>
			<td><?php echo $lang->phrase('admin_wysiwyg_border_color'); ?></td>
			<td>
				<input type="text" name="bordercolor" id="bordercolor" value="none">
				<input type="button" value="<?php echo $lang->phrase('admin_wysiwyg_choose'); ?>" onClick="WYSIWYG_ColorInst.choose('bordercolor');" />
			</td>
		</tr><tr class="mbox">
			<td><?php echo $lang->phrase('admin_wysiwyg_border_style'); ?></td>
			<td>
				<select id="borderstyle" name="borderstyle">
					<option value="none">none</option>
					<option value="solid">solid</option>
					<option value="double">double</option>
					<option value="dotted">dotted</option>
					<option value="dashed">dashed</option>
					<option value="groove">groove</option>
					<option value="ridge">ridge</option>
					<option value="inset">inset</option>
					<option value="outset">outset</option>
				</select>
			</td>
			<td><?php echo $lang->phrase('admin_wysiwyg_border_collapse'); ?></td>
			<td><input type="checkbox" name="bordercollapse" id="bordercollapse" checked="checked" /></td>
		</tr><tr>
			<td class="ubox" colspan="4" align="center">
				<input type="submit" value="<?php echo $lang->phrase('admin_wysiwyg_form_submit'); ?>" onClick="buildTable(WYSIWYG_Popup.getParam('wysiwyg'));" />
				<input type="submit" value="<?php echo $lang->phrase('admin_wysiwyg_form_cancel'); ?>" onClick="window.close();" />
			</td>
		</tr>
	</table>
	<?php
	echo foot(true);
}
elseif ($job == 'doc_insert_hr') {
	$htmlhead .= '<script type="text/javascript" src="admin/html/editor/wysiwyg-popup.js"></script>';
	echo head();
	?>
	<form name="hr_form">
	<table class="border" width="300" border="0" cellpadding="4" cellspacing="0" align="center">
		<tr><td class="obox" colspan="3"><?php echo $lang->phrase('admin_wysiwyg_insert_hr'); ?></td></tr>
		<tr class="mbox">
			<td><?php echo $lang->phrase('admin_wysiwyg_width'); ?></td>
			<td><input type="text" name="width" id="width" value="" size="10" /></td>
			<td>
				<select name="widthgroup" id="widthgroup" size="1">
					<option value="1"><?php echo $lang->phrase('admin_wysiwyg_width_full'); ?></option>
					<option value="2">px</option>
					<option value="3">%</option>
				</select>
			</td>
		</tr><tr class="mbox">
			<td><?php echo $lang->phrase('admin_wysiwyg_height'); ?></td>
			<td colspan="2"><input type="text" name="height" id="height" value="" size="10" /></td>
		</tr><tr class="mbox">
			<td><?php echo $lang->phrase('admin_wysiwyg_alignment'); ?></td>
			<td colspan="2">
				<select name="align" id="align" size="1">
					<option value="1"><?php echo $lang->phrase('admin_wysiwyg_alignment_center'); ?></option>
					<option value="2"><?php echo $lang->phrase('admin_wysiwyg_alignment_left'); ?></option>
					<option value="3"><?php echo $lang->phrase('admin_wysiwyg_alignment_right'); ?></option>
				</select>
			</td>
		</tr><tr class="mbox">
			<td norwap="nowrap"><?php echo $lang->phrase('admin_wysiwyg_no_shade'); ?></td>
			<td colspan="2"><input type="checkbox" name="shade" id="shade" value="1" /></td>
		</tr><tr class="mbox">
			<td norwap="nowrap"><?php echo $lang->phrase('admin_wysiwyg_color'); ?></td>
			<td><input type="text" name="color" id="color" value="none" size="10" /></td>
			<td><input type="button" onClick="WYSIWYG_ColorInst.choose('color');" value="<?php echo $lang->phrase('admin_wysiwyg_choose'); ?>" /></td>
		</tr><tr>
			<td class="ubox" colspan="3" align="center">
				<input type="submit" value="<?php echo $lang->phrase('admin_wysiwyg_form_submit'); ?>" onClick="createHR(WYSIWYG_Popup.getParam('wysiwyg'));" />
				<input type="submit" value="<?php echo $lang->phrase('admin_wysiwyg_form_cancel'); ?>" onClick="window.close();" />
			</td>
   		</tr>
   	</table>
	</form>
	<?php
	echo foot(true);
}
elseif ($job == 'doc_insert_hyperlink') {
	$htmlhead .= '<script type="text/javascript" src="admin/html/editor/wysiwyg-popup.js"></script>';
	echo head('loadLink();');
	?>
	<table class="border" width="360" border="0" cellpadding="4" cellspacing="0" align="center">
		<tr><td class="obox" colspan="3"><?php echo $lang->phrase('admin_wysiwyg_insert_link'); ?></td></tr>
		<tr class="mbox">
			<td><?php echo $lang->phrase('admin_wysiwyg_url'); ?></td>
			<td colspan="2"><input type="text" name="linkUrl" id="linkUrl" value="http://" size="50" /></td>
		</tr><tr class="mbox">
			<td><?php echo $lang->phrase('admin_wysiwyg_target'); ?></td>
			<td><input type="text" name="linkTarget" id="linkTarget" value="" /></td>
			<td>
				<select name="linkTargetChooser" id="linkTargetChooser" onchange="updateTarget(this.value);">
					<option value="" selected="selected"><?php echo $lang->phrase('admin_wysiwyg_custom_target'); ?></option>
					<option value="_blank">_blank</option>
					<option value="_self">_self</option>
					<option value="_parent">_parent</option>
					<option value="_top">_top</option>
				</select>
			</td>
		</tr><tr class="mbox">
			<td><?php echo $lang->phrase('admin_wysiwyg_name'); ?></td>
			<td colspan="2"><input type="text" name="linkName" id="linkName" value="" /></td>
		</tr><tr>
			<td class="ubox" colspan="3" align="center">
				<input type="submit" value="<?php echo $lang->phrase('admin_wysiwyg_form_submit'); ?>" onClick="insertHyperLink(WYSIWYG_Popup.getParam('wysiwyg'));" />
				<input type="submit" value="<?php echo $lang->phrase('admin_wysiwyg_form_cancel'); ?>" onClick="window.close();" />
			</td>
		</tr>
	</table>
	<?php
	echo foot(true);
}
elseif ($job == 'doc_select_color') {
	$htmlhead .= '<script type="text/javascript" src="admin/html/editor/wysiwyg-popup.js"></script>';
	echo head("loadColor();");
	?>
	<form onSubmit="selectColor(document.getElementById('enterColor').value);">
	<table class="border" border="0" cellspacing="0" cellpadding="4" style="width: 232px;">
	 <tr>
	  <td class="obox"><?php echo $lang->phrase('admin_wysiwyg_select_color'); ?></td>
	 </tr>
	 <tr class="mbox" align="center">
	  <td>
		<?php echo $lang->phrase('admin_wysiwyg_hey_code'); ?> <input type="text" size="10" name="enterColor" id="enterColor" /><br /><br class="minibr" />
		<input type="submit" value="<?php echo $lang->phrase('admin_wysiwyg_form_submit'); ?>" />
		<input type="button" onclick="self.close();" value="<?php echo $lang->phrase('admin_wysiwyg_form_cancel'); ?>" />
	  </td>
	 </tr>
	 <tr>
	  <td class="obox"><?php echo $lang->phrase('admin_wysiwyg_preview'); ?></td>
	 </tr>
	 <tr class="mbox">
	  <td align="center" id="PreviewColor"><?php echo $lang->phrase('admin_wysiwyg_color_preview'); ?></td>
	 </tr>
	 <tr>
	  <td class="obox"><?php echo $lang->phrase('admin_wysiwyg_predefined_colors'); ?></td>
	 </tr>
	 <tr class="mbox">
	  <td align="center">
	   <div class="colorpicker-td">
		<script type="text/javascript">document.write(generateColorPicker("previewColor('<color>')", 'assets/empty.gif'));</script>
	   </div>
	  </td>
	 </tr>
	</table>
	</form>
	<?php
	echo foot(true);
}
elseif ($job == 'doc_select_image') {
	/********************************************************************
	 * openImageLibrary addon Copyright (c) 2006 openWebWare.com
	 * Contact us at devs@openwebware.com
	 * This copyright notice MUST stay intact for use.
	 ********************************************************************/
	$leadon = realpath(EDITOR_IMAGEDIR).DIRECTORY_SEPARATOR;
	$leadon = str_replace('\\', '/', $leadon);
	$dir = $gpc->get('dir', path);
	$dotdotdir = false;
	$dirok = false;
	if(!empty($dir)) {
		if ($dir == '..') {
			$leadon = extract_dir($leadon, true).DIRECTORY_SEPARATOR;
			$leadon = str_replace('\\', '/', $leadon);
			$dir = '';
		}
		else {
			$leadon .= $dir.DIRECTORY_SEPARATOR;
			$dotdotdir = true;
		}
	}

	if(!file_exists($leadon)) {
		$leadon = realpath(EDITOR_IMAGEDIR).DIRECTORY_SEPARATOR;
		$leadon = str_replace('\\', '/', $leadon);
	}

	clearstatcache();
	$n = 0;
	$dirs = array();
	$files = array();
	if ($handle = opendir($leadon)) {
		while (false !== ($file = readdir($handle))) {
			//first see if this file is required in the listing
			if ($file == "." || $file == "..") {
				continue;
			}
			if (is_dir($leadon.$file) == true) {
				$dirs[] = $file;
			}
			else if (is_file($leadon.$file) == true) {
				$ext = mb_strtolower(get_extension($file));
				if(in_array($ext, $supportedextentions)) {
					$files[] = $file;
				}
			}
		}
		closedir($handle);
	}

	natcasesort($dirs);
	natcasesort($files);

	echo head('style="background-color: #ffffff;"');
	?>
	<script type="text/javascript">
		function selectImage(url) {
			if(parent) {
				parent.document.getElementById("src").value = url;
			}
		}

		if(parent) {
			parent.document.getElementById("framedir").value = '<?php echo iif($dotdotdir, $dir); ?>';
		}

	</script>
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center" style="width: 230px;">
		<tr>
			<td>
			  <?php
				if($dotdotdir) {
					?>
					<a href="admin.php?action=cms&job=doc_select_image&dir=<?php echo extract_dir($dir); ?>"><em><?php echo $lang->phrase('admin_wysiwyg_prev_dir'); ?></em></a><br>
					<?php
				}
				$i = -1;
				foreach ($dirs as $i => $dirname) {
					?>
					<a href="admin.php?action=cms&job=doc_select_image&dir=<?php echo urlencode($dirname); ?>"><?php echo $dirname; ?></a><br />
					<?php
				}
				if ($i >= 0 || $dotdotdir) {
					echo "</td></tr><tr><td>";
				}
				foreach ($files as $filename) {
					?>
					<a href="javascript:selectImage('<?php echo EDITOR_IMAGEDIR.$filename; ?>');"><?php echo $filename; ?></a><br />
					<?php
				}
				if (count($files) == 0) {
					echo $lang->phrase('admin_wysiwyg_no_files_found');
				}
				?>
			</td>
		</tr>
	</table>
	<?php
	echo foot(true);
}
elseif ($job == 'doc_insert_image') {
	$wysiwyg = $gpc->get('wysiwyg', str);
	$leadon = realpath(EDITOR_IMAGEDIR).DIRECTORY_SEPARATOR;
	$image_file = null;

	$dirhandler = opendir($leadon);
	$dirlist = array();
	while ($dir = readdir ($dirhandler)) {
		if ($dir != '.' && $dir != '..' && is_dir($dir)) {
			$dirlist[] = $dir;
		}
	}
	closedir($dirhandler);
	natcasesort($dirlist);

	// upload file
	$error = null;
	if (!empty($_FILES['file']['name'])) {
		$path = $leadon;

		$qdir = $gpc->get('dir', path);
		$ndir = $gpc->get('newdir', path);
		if($qdir == '#') {
			if (!preg_match('/[^\w\d\-\.]/iu', $qdir) || empty($ndir)) {
				$error = $lang->phrase('admin_wysiwyg_folder_restrictions');
			}
			else {
				if ($filesystem->mkdir($leadon.$ndir, 0777)) {
					$path = $leadon.$ndir;
				}
			}
		}

		if ($error === null) {
			require("classes/class.upload.php");
			$my_uploader = new uploader();
			$my_uploader->max_filesize(ini_maxupload());
			$my_uploader->file_types($supportedextentions);
			$my_uploader->set_path($path);
			if ($my_uploader->upload('file')) {
				$my_uploader->save_file();
			}
			if ($my_uploader->upload_failed()) {
				$error = $my_uploader->get_error();
			}
			$image_file = $path.$my_uploader->fileinfo('filename');
			if (!file_exists($image_file)) {
				$error = $lang->phrase('admin_cms_file_does_not_exist');
			}
			$image_file = str_replace(realpath($config['fpath']).DIRECTORY_SEPARATOR, '', $image_file);
			$image_file = str_replace(DIRECTORY_SEPARATOR, '/', $image_file);
		}
	}

	$filesize = formatFilesize(ini_maxupload());

	$htmlhead .= '<script type="text/javascript" src="admin/html/editor/wysiwyg-popup.js"></script>';
	echo head(' onLoad="loadImage();"');
	?>
<form method="post" action="admin.php?action=cms&amp;job=doc_insert_image&amp;wysiwyg=<?php echo $wysiwyg; ?>" enctype="multipart/form-data">
<table class="border" border="0" cellspacing="0" cellpadding="4" align="center" style="width: 700px;">
	<tr>
		<td class="obox" colspan="3"><?php echo $lang->phrase('admin_wysiwyg_upload_x'); ?></td>
	</tr>
	<tr class="mbox">
		<td><?php echo $lang->phrase('admin_wysiwyg_folder'); ?></td>
		<td>
			<select name="dir" onchange="dirSelect(this)">
				<option value=""<?php echo iif(empty($dir), ' selected="selected"'); ?>>Hauptverzeichnis</option>
				<?php if (count($dirlist) > 0) { ?>
				<optgroup label="Existierende Verzeichnisse">
					<?php foreach ($dirlist as $dir) { ?>
					<option value="<?php echo $dir; ?>"<?php echo iif($dir == $qdir, ' selected="selected"'); ?>><?php echo $dir; ?></option>
					<?php } ?>
				</optgroup>
				<?php } ?>
				<option value="#"<?php echo iif($dir == '#', ' selected="selected"'); ?>>Neues Verzeichnis erstellen:</option>
			</select>
			<input type="text" name="newdir" id="newdir" style="display: none;" size="30" />
		</td>
		<td rowspan="2" valign="middle" align="center" class="ubox"><input type="submit" value="<?php echo $lang->phrase('admin_wysiwyg_form_upload'); ?>"></td>
	</tr>
	<tr class="mbox">
		<td><?php echo $lang->phrase('admin_wysiwyg_file'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_wysiwyg_max_filesize'); ?></span></td>
		<td>
			<input type="file" name="file" size="50" />
			<?php if ($error !== null) { ?><br /><span class="stext"><?php echo $error; ?></span><?php } ?>
		</td>
	</tr>
</table>
<input type="hidden" name="framedir" id="framedir" value="" />
</form>
<br class="minibr" />
<table class="border" border="0" cellspacing="0" cellpadding="4" align="center" style="width: 700px;">
	<tr>
		<td class="obox" colspan="4"><?php echo $lang->phrase('admin_wysiwyg_insert_img'); ?></td>
		<td class="obox"><?php echo $lang->phrase('admin_wysiwyg_select_img'); ?></td>
	</tr>
	<tr class="mbox">
		<td><?php echo $lang->phrase('admin_wysiwyg_image_url'); ?></td>
		<td colspan="3"><input type="text" name="src" id="src" value="<?php echo iif(!empty($image_file), $image_file); ?>" size="50" /></td>
		<td rowspan="8" width="250">
			<iframe id="chooser" height="260" width="250" frameborder="0" src="admin.php?action=cms&amp;job=doc_select_image&amp;dir=<?php echo urlencode($dir); ?>"></iframe>
		</td>
	</tr><tr class="mbox">
		<td><?php echo $lang->phrase('admin_wysiwyg_alt_text'); ?></td>
		<td colspan="3"><input type="text" name="alt" id="alt" value="" size="50" /></td>
	</tr>
	<tr><td class="obox" colspan="4"><?php echo $lang->phrase('admin_wysiwyg_layout'); ?></td></tr>
	<tr class="mbox">
	  <td width="120"><?php echo $lang->phrase('admin_wysiwyg_width'); ?></td>
	  <td width="105"><input type="text" name="width" id="width" value="" size="10" />px</td>
	  <td width="120"><?php echo $lang->phrase('admin_wysiwyg_height'); ?></td>
	  <td width="105"><input type="text" name="height" id="height" value="" size="10" />px</td>
	</tr>
	<tr class="mbox">
	  <td><?php echo $lang->phrase('admin_wysiwyg_hspace'); ?></td>
	  <td><input type="text" name="hspace" id="hspace" value="" size="10" /></td>
	  <td><?php echo $lang->phrase('admin_wysiwyg_vspace'); ?></td>
	  <td><input type="text" name="vspace" id="vspace" value="" size="10" /></td>
	</tr>
	<tr class="mbox">
	  <td><?php echo $lang->phrase('admin_wysiwyg_border_width'); ?></td>
	  <td><input type="text" name="border" id="border" value="0" size="10" />px</td>
	  <td><?php echo $lang->phrase('admin_wysiwyg_alignment'); ?></td>
	  <td>
		<select name="align" id="align">
		 <option value=""><?php echo $lang->phrase('admin_wysiwyg_alignment_not_set'); ?></option>
		 <option value="left"><?php echo $lang->phrase('admin_wysiwyg_alignment_left'); ?></option>
		 <option value="right"><?php echo $lang->phrase('admin_wysiwyg_alignment_right'); ?></option>
		 <option value="bottom"><?php echo $lang->phrase('admin_wysiwyg_alignment_bottom'); ?></option>
		 <option value="middle"><?php echo $lang->phrase('admin_wysiwyg_alignment_middle'); ?></option>
		 <option value="top"><?php echo $lang->phrase('admin_wysiwyg_alignment_top'); ?></option>
		</select>
	  </td>
	</tr>
	<tr class="mbox">
	  <td><?php echo $lang->phrase('admin_wysiwyg_border_color'); ?></td>
	  <td colspan="3">
	  	<input type="text" name="bordercolor" id="bordercolor" value="none" size="10" />
	  	<input type="button" value="<?php echo $lang->phrase('admin_wysiwyg_choose'); ?>" onClick="WYSIWYG_ColorInst.choose('bordercolor', 1);" />
	  </td>
	</tr>
	<tr class="mbox">
	  <td colspan="4" class="ubox" align="center">
		<input type="submit" value="<?php echo $lang->phrase('admin_wysiwyg_form_submit'); ?>" onclick="insertImage();return false;">
		<input type="button" value="<?php echo $lang->phrase('admin_wysiwyg_form_cancel'); ?>" onclick="window.close();">
	  </td>
	</tr>
	</table>
	</form>
	<?php
	echo foot(true);
}
elseif ($job == 'doc') {
	$language_obj = $scache->load('loadlanguage');
	$language = $language_obj->get();

	$result = $db->execute("
		SELECT d.id, u.name AS author, d.update, d.icomment, c.lid, c.title, c.active
		FROM {$db->pre}documents AS d
			LEFT JOIN {$db->pre}documents_content AS c ON d.id = c.did
			LEFT JOIN {$db->pre}user AS u ON u.id = d.author
		ORDER BY c.title
	");
	$data = array();
	while ($row = $result->fetch()) {
		if(empty($row['author'])) {
			$row['author'] = $lang->phrase('admin_cms_unknown');
		}
		if ($row['update'] > 0) {
			$row['update'] = gmdate('d.m.Y', times($row['update'])).'<br />'.gmdate('H:i', times($row['update']));
		}
		else {
			$row['update'] = $lang->phrase('admin_cms_unknown');
		}
		if (mb_strlen($row['icomment']) > 100) {
			$row['icomment'] = mb_substr($row['icomment'], 0, 100).'...';
		}
		$newRow = array(
			'title' => $row['title'],
			'active' => $row['active']
		);
		if (!isset($data[$row['id']])) {
			$row['languages'] = array($row['lid'] => $newRow);
			$data[$row['id']] = $row;
		}
		else if (!in_array($row['lid'], $data[$row['id']]['languages'])) {
			$data[$row['id']]['languages'][$row['lid']] = $newRow;
		}
	}


	echo head();
?>
<form name="form" method="post" action="admin.php?action=cms&job=doc_delete">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="8">
   <span style="float: right;"><a class="button" href="admin.php?action=cms&job=doc_add"><?php echo $lang->phrase('admin_cms_create_new_document'); ?></a></span>
	<?php echo $lang->phrase('admin_cms_manage_documents_and_pages'); ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" width="2%"><?php echo $lang->phrase('admin_cms_doc_delete'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /> <?php echo $lang->phrase('admin_cms_doc_delete_all'); ?></span></td>
   <td class="ubox" width="30%"><?php echo $lang->phrase('admin_cms_doc_title'); ?></td>
   <td class="ubox" width="14%"><?php echo $lang->phrase('admin_cms_doc_av_languages'); ?></td>
   <td class="ubox" width="3%"><?php echo $lang->phrase('admin_cms_doc_published'); ?></td>
   <td class="ubox" width="12%"><?php echo $lang->phrase('admin_cms_doc_author'); ?></td>
   <td class="ubox" width="8%"><?php echo $lang->phrase('admin_cms_doc_last_change'); ?></td>
   <td class="ubox" width="14%"><?php echo $lang->phrase('admin_cms_doc_id'); ?></td>
   <td class="ubox" width="14%"><?php echo $lang->phrase('admin_cms_doc_action'); ?></td>
  </tr>
<?php
	foreach ($data as $id => $row) {
		$rowspan = count($data[$id]['languages']);
		$i = 0;
		foreach ($data[$id]['languages'] as $lid => $row2) {
			$i++;
			?>
  			<tr>
  			<?php if ($i == 1) { ?>
  			 <td class="mbox center" rowspan="<?php echo $rowspan; ?>"><input type="checkbox" name="delete[]" value="<?php echo $id; ?>"></td>
   			<?php } ?>
   			 <td class="mbox"><a href="admin.php?action=cms&job=doc_edit&id=<?php echo $id; ?>"><?php echo $row2['title']; ?></a></td>
   			 <td class="mbox stext">
   			<?php
   			 if (isset($row['languages'][$lid]) && isset($language[$lid])) {
	   			echo $language[$lid]['language'];
	   		 }
	   		 else if (isset($row['languages'][$lid]) && !isset($language[$lid])) {
	   			echo "<em>".$lang->phrase('admin_cms_unknown')."</em> ({$lid})";
	   		 }
	   		?>
   			 </td>
  			 <td class="mbox center"><?php echo noki($row2['active'], ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=cms&job=doc_ajax_active&id='.$id.'&lid='.$lid.'\')"'); ?></td>
			<?php if ($i == 1) { ?>
  			 <td class="mbox" rowspan="<?php echo $rowspan; ?>"><?php echo $row['author']; ?></td>
			 <td class="mbox center" rowspan="<?php echo $rowspan; ?>"><?php echo $row['update']; ?></td>
			 <td class="mbox stext" rowspan="<?php echo $rowspan; ?>"><?php echo nl2br($row['icomment']); ?></td>
			 <td class="mbox" rowspan="<?php echo $rowspan; ?>">
			  <a class="button" href="docs.php?id=<?php echo $id.SID2URL_x; ?>" target="_blank"><?php echo $lang->phrase('admin_cms_view'); ?></a>
			  <a class="button" href="admin.php?action=cms&job=doc_edit&id=<?php echo $id; ?>"><?php echo $lang->phrase('admin_cms_edit'); ?></a>
			 </td>
			<?php } ?>
			</tr>
<?php
		}
	}
?>
  <tr>
   <td class="ubox" width="100%" colspan="8" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_cms_form_delete'); ?>"></td>
  </tr>
 </table>
</form>
<?php
	echo foot();
}
elseif ($job == 'doc_ajax_active') {
	$id = $gpc->get('id', int);
	$lid = $gpc->get('lid', int);
	$result = $db->execute("SELECT active FROM {$db->pre}documents_content WHERE did = '{$id}' AND lid = '{$lid}' LIMIT 1");
	$use = $result->fetch();
	$use = invert($use['active']);
	$db->execute("UPDATE {$db->pre}documents_content SET active = '{$use}' WHERE did = '{$id}' AND lid = '{$lid}' LIMIT 1");
	$scache->load('wraps')->delete();
	die(strval($use));
}
elseif ($job == 'doc_add') {
	echo head();
	$parser = docparser();
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_cms_create_doc_step_1'); ?></td>
  </tr>
  <tr>
   <td class="mbox">
    <?php echo $lang->phrase('admin_cms_doc_parser'); ?>
    <ul>
    <?php foreach ($parser as $type => $name) { ?>
	 <li><a href="admin.php?action=cms&job=doc_add2&parser=<?php echo $type; ?>"><?php echo $name; ?></a></li>
    <?php } ?>
	</ul>
   </td>
  </tr>
 </table>
	<?php
	echo foot();
}
elseif ($job == 'doc_add2') {
	$type = $gpc->get('parser', db_esc);
	$types = docparser();
	if (!isset($types[$type])) {
		$type = 'html';
	}
	$language_obj = $scache->load('loadlanguage');
	$language = $language_obj->get();
	if ($type != 'bbcode') {
		$htmlhead .= attachWYSIWYG();
	}
	echo head(' onload="hideLanguageBoxes()"');
  	$groups = $db->execute("SELECT id, name FROM {$db->pre}groups");
?>
<form id="form" method="post" action="admin.php?action=cms&job=doc_add3&parser=<?php echo $type; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox"><?php echo $lang->phrase('admin_cms_create_doc_step_2'); ?></td>
  </tr>
 <tr>
  <td class="ubox"><?php echo $lang->phrase('admin_cms_doc_global_settings'); ?></td>
  </tr>
  <tr>
   <td class="mbox">
	<span class="stext right"><?php echo $lang->phrase('admin_cms_doc_groups_text'); ?></span>
    <?php echo $lang->phrase('admin_cms_doc_groups'); ?><br />
    <?php while ($row = $groups->fetch()) { ?>
     <span style="margin-right: 1em;"><input type="checkbox" name="groups[]" checked="checked" value="<?php echo $row['id']; ?>"> <?php echo $row['name']; ?></span>
    <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox">
    <?php echo $lang->phrase('admin_cms_doc_internal_note'); ?><br />
    <textarea name="icomment" class="texteditor" cols="80" rows="3"></textarea>
   </td>
  </tr>
  <tr>
   <td class="mbox">
    <?php echo $lang->phrase('admin_cms_doc_template'); ?><br />
    <select name="tpl" cols="80" rows="3">
     <option value="default">Default</option>
     <option value="article">Article</option>
     <option value="blank">Blank</option>
	</select>
   </td>
  </tr>
<?php foreach ($language as $lid => $data) { ?>
  <tr>
   <td class="ubox">
   	<input type="checkbox" id="use_<?php echo $lid; ?>" name="use[<?php echo $lid; ?>]" value="1" title="<?php echo $lang->phrase('admin_cms_doc_click_for_adding_lang'); ?>" onclick="return changeLanguageUsage(<?php echo $lid; ?>)" />
   	<strong><?php echo $data['language']; ?></strong>
   </td>
  </tr>
  <tbody id="language_<?php echo $lid; ?>">
  <tr>
   <td class="mbox">
	<?php echo $lang->phrase('admin_cms_news_title'); ?><br />
	<input type="text" name="title[<?php echo $lid; ?>]" size="60" />
   </td>
  </tr>
  <tr>
   <td class="mbox">
   <?php if($type == 'bbcode') { ?>
		<strong><a class="right" href="misc.php?action=bbhelp<?php echo SID2URL_x; ?>" target="_blank"><?php echo $lang->phrase('bbcode_help'); ?></a></strong>
		<?php echo $lang->phrase('admin_cms_doc_sourcecode'); ?>
		<br />
		<?php BBCodeToolBox("template[{$lid}]", '', 'rows="18" cols="110" class="texteditor editor_textarea_inner"'); ?>
    <?php } else { ?>
		<?php echo $lang->phrase('admin_cms_doc_sourcecode'); ?>
		<br /><textarea id="template[<?php echo $lid; ?>]" name="template[<?php echo $lid; ?>]" rows="20" cols="110" class="texteditor"></textarea>
	<?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox">
	<?php echo $lang->phrase('admin_cms_doc_active'); ?><br />
	<input type="checkbox" value="1" name="active[<?php echo $lid; ?>]" />
   </td>
  </tr>
  </tbody>
  <?php } ?>
  <tr><td class="ubox" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_cms_form_add'); ?>" /></td></tr>
 </table>
</form>
<?php
echo foot();
}
elseif ($job == 'doc_add3') {
	echo head();

	$parser = $gpc->get('parser', db_esc);
	$tpl = $gpc->get('tpl', db_esc);
	$icomment = $gpc->get('icomment', str);
	$title = $gpc->get('title', arr_str);
	$active = $gpc->get('active', arr_int);
	$use = $gpc->get('use', arr_int);
  	$groups = $gpc->get('groups', arr_int);
  	$content = $gpc->get('template', arr_none);

	$i = 0;
	foreach ($use as $lid => $usage) {
		if ($usage == 1) {
			$i++;
		}
	}
	if ($i == 0) {
		error('javascript:history.back(-1);', $lang->phrase('admin_cms_havent_checked_box'));
	}

	$result = $db->execute('SELECT COUNT(*) FROM '.$db->pre.'groups');
	$count = $result->fetchOne();
	if (count($groups) == $count) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}

	$time = time();

	$db->execute("INSERT INTO {$db->pre}documents (`author`, `date`, `update`, `parser`, `template`, `groups`, `icomment`) VALUES ('{$my->id}', '{$time}' , '{$time}' , '{$parser}', '{$tpl}', '{$groups}', '{$icomment}')");
	$did = $db->getInsertId();

	foreach ($use as $lid => $usage) {
		if ($usage == 1) {
			if (mb_strlen($content[$lid]) < 20) {
				$content[$lid] = trim(strip_tags($content[$lid]));
			}
			if (empty($content[$lid]) && $format['remote'] != 1) {
				continue;
			}
			if (empty($title[$lid])) {
				$title[$lid] = mb_substr(strip_tags($content[$lid]), 0, 50).'...';
			}
			if (empty($active[$lid])) {
				$active[$lid] = 0;
			}
			$content[$lid] = $db->escape($content[$lid]);
			$lid = $gpc->save_int($lid);
			$db->execute("INSERT INTO {$db->pre}documents_content ( `did` , `lid` , `title` , `content` , `active` ) VALUES ('{$did}', '{$lid}', '{$title[$lid]}', '{$content[$lid]}', '{$active[$lid]}')");
		}
	}

	$scache->load('wraps')->delete();

	ok('admin.php?action=cms&job=doc', $lang->phrase('admin_cms_document_successfully_added'));
}
elseif ($job == 'doc_delete') {
	echo head();
	$delete = $gpc->get('delete', arr_int);
	if (count($delete) > 0) {
		$deleteids = implode(',', $delete);
		$stmt = $db->execute("DELETE FROM {$db->pre}documents WHERE id IN ({$deleteids})");
		$anz = $stmt->getAffectedRows();
		$db->execute("DELETE FROM {$db->pre}documents_content WHERE did IN ({$deleteids})");

		$scache->load('wraps')->delete();

		ok('admin.php?action=cms&job=doc', $lang->phrase('admin_cms_documents_deleted'));
	}
	else {
		error('admin.php?action=cms&job=doc', $lang->phrase('admin_cms_havent_checked_box'));
	}
}
elseif ($job == 'doc_edit') {
	$id = $gpc->get('id', int);
	$types = docparser();

	$row = $db->fetch("
		SELECT d.*, u.name AS author_name
		FROM {$db->pre}documents AS d
			LEFT JOIN {$db->pre}user AS u ON u.id = d.author
		WHERE d.id = '{$id}'");
	if (!$row) {
		echo head();
		error('admin.php?action=cms&job=doc', $lang->phrase('admin_cms_invalid_id_given'));
	}

	$result = $db->execute("SELECT content, active, title, lid FROM {$db->pre}documents_content WHERE did = '{$id}'");
	$content = array();
	while ($row2 = $result->fetch()) {
		$content[$row2['lid']] = $row2;
	}

	$format = $types[$row['parser']];
	$groups = $db->execute("SELECT id, name FROM {$db->pre}groups");
	$garr = explode(',', $row['groups']);

	$language_obj = $scache->load('loadlanguage');
	$language = $language_obj->get();

	if ($row['parser'] != 'bbcode') {
		$htmlhead .= attachWYSIWYG();
	}
	echo head(' onload="hideLanguageBoxes()"');
?>
<form id="form" method="post" action="admin.php?action=cms&job=doc_edit2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox"><?php echo $lang->phrase('admin_cms_edit_doc'); ?></td>
  </tr>
 <tr>
  <td class="ubox"><?php echo $lang->phrase('admin_cms_doc_global_settings'); ?></td>
  </tr>
  <tr>
   <td class="mbox"><span class="stext right"><?php echo $lang->phrase('admin_cms_doc_groups_text'); ?></span><?php echo $lang->phrase('admin_cms_doc_groups'); ?><br />
   <?php while ($g = $groups->fetch()) { ?>
	<span style="margin-right: 1em;"><input type="checkbox" name="groups[]"<?php echo iif($row['groups'] == 0 || in_array($g['id'], $garr),'checked="checked"'); ?> value="<?php echo $g['id']; ?>"> <?php echo $g['name']; ?></span>
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox"><?php echo $lang->phrase('admin_cms_doc_internal_note'); ?><br />
   <textarea name="icomment" class="texteditor" cols="80" rows="3"><?php echo $gpc->prepare($row['icomment']); ?></textarea>
   </td>
  </tr>
   <td class="mbox">
    <?php echo $lang->phrase('admin_cms_doc_template'); ?><br />
    <select name="tpl" cols="80" rows="3">
     <option value="default"<?php echo iif($row['template'] == 'default', ' selected="selected"'); ?>>Default</option>
     <option value="article"<?php echo iif($row['template'] == 'article', ' selected="selected"'); ?>>Article</option>
     <option value="blank"<?php echo iif($row['template'] == 'blank', ' selected="selected"'); ?>>Blank</option>
	</select>
   </td>
  <tr>
   <td class="mbox">
	<?php echo $lang->phrase('admin_cms_doc_author_change'); ?><br />
	<input type="radio" value="<?php echo $row['author']; ?>" name="author" checked="checked" /> <?php echo $lang->phrase('admin_cms_keep_current_author'); ?> <strong><?php echo !empty($row['author_name']) ? $row['author_name'] : $lang->phrase('admin_cms_unknown'); ?></strong><br />
	<input type="radio" value="<?php echo $my->id; ?>" name="author" /> <?php echo $lang->phrase('admin_cms_change_author_to'); ?> <strong><?php echo $my->name; ?></strong>
   </td>
  </tr>
<?php
	foreach ($language as $lid => $data) {
		if (isset($content[$lid])) {
			$row2 = $content[$lid];
		}
		else {
			$row2 = array(
				'content' => '',
				'active' => 0,
				'title' => '',
				'lid' => $lid
			);
		}
?>
  <tr>
   <td class="ubox">
   	<input type="checkbox"<?php echo iif(isset($content[$lid]), ' checked="checked"'); ?> id="use_<?php echo $lid; ?>" name="use[<?php echo $lid; ?>]" value="1" title="<?php echo $lang->phrase('admin_cms_doc_click_for_adding_lang'); ?>" onclick="return changeLanguageUsage(<?php echo $lid; ?>)" />
   	<strong><?php echo $data['language']; ?></strong>
   </td>
  </tr>
  <tbody id="language_<?php echo $lid; ?>">
  <tr>
   <td class="mbox">
	<?php echo $lang->phrase('admin_cms_news_title'); ?><br />
	<input type="text" name="title[<?php echo $lid; ?>]" size="60" value="<?php echo $gpc->prepare($row2['title']); ?>" />
   </td>
  </tr>
  <tr>
   <td class="mbox">
	<?php if($row['parser'] == 'bbcode') { ?>
		<strong><a class="right" href="misc.php?action=bbhelp<?php echo SID2URL_x; ?>" target="_blank"><?php echo $lang->phrase('bbcode_help'); ?></a></strong>
		<?php echo $lang->phrase('admin_cms_doc_sourcecode'); ?>
		<br />
		<?php BBCodeToolBox("template[{$lid}]", $row2['content'], 'rows="18" cols="110" class="texteditor editor_textarea_inner"'); ?>
	<?php } else { ?>
		<?php echo $lang->phrase('admin_cms_doc_sourcecode'); ?>
		<br /><textarea id="template[<?php echo $lid; ?>]" name="template[<?php echo $lid; ?>]" rows="20" cols="110" class="texteditor"><?php echo $row2['content']; ?></textarea>
	<?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox">
	<?php echo $lang->phrase('admin_cms_doc_active'); ?><br />
	<input type="checkbox" value="1" name="active[<?php echo $lid; ?>]"<?php echo iif($row2['active'] == 1, ' checked="checked"'); ?> />
   </td>
  </tr>
  </tbody>
<?php } ?>
  <tr><td class="mbox"><?php echo $lang->phrase('admin_cms_doc_checkboxes_help'); ?></td></tr>
  <tr><td class="ubox" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_cms_form_edit'); ?>" /></td></tr>
 </table>
</form>
<?php
echo foot();
}
elseif ($job == 'doc_edit2') {
	echo head();

	$id = $gpc->get('id', int);
	$icomment = $gpc->get('icomment', str);
	$title = $gpc->get('title', arr_str);
	$active = $gpc->get('active', arr_int);
	$author = $gpc->get('author', int);
	$use = $gpc->get('use', arr_int);
  	$groups = $gpc->get('groups', arr_int);
  	$content = $gpc->get('template', arr_none);
	$tpl = $gpc->get('tpl', db_esc);

	$i = 0;
	foreach ($use as $lid => $usage) {
		if ($usage == 1) {
			$i++;
		}
	}
	if ($i == 0) {
		error('javascript:history.back(-1);', $lang->phrase('admin_cms_havent_checked_box'));
	}

	$result = $db->execute('SELECT COUNT(*) FROM '.$db->pre.'groups');
	$count = $result->fetchOne();
	if (count($groups) == $count) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}

	$time = time();

	$db->execute("UPDATE {$db->pre}documents SET `update` = '{$time}', `groups` = '{$groups}', `author` = '{$author}', `icomment` = '{$icomment}', `template` = '{$tpl}' WHERE id = '{$id}' LIMIT 1");

	$language_obj = $scache->load('loadlanguage');
	$language = $language_obj->get();

	foreach ($language as $lid => $x) {
		if (empty($use[$lid])) {
			$usage = 0;
		}
		else {
			$usage = 1;
		}
		$lid = $gpc->save_int($lid);
		if (mb_strlen($content[$lid]) < 20) {
			$content[$lid] = trim(strip_tags($content[$lid]));
		}
		if (empty($content[$lid]) || $usage != 1) {
			$db->execute("DELETE FROM {$db->pre}documents_content WHERE did = '{$id}' AND lid = '{$lid}'");
		}
		elseif ($usage == 1) {
			if (empty($title[$lid])) {
				$title[$lid] = mb_substr(strip_tags($content[$lid]), 0, 50).'...';
			}
			if (empty($active[$lid])) {
				$active[$lid] = 0;
			}
			$result = $db->fetchOne("SELECT lid FROM {$db->pre}documents_content WHERE did = '{$id}' AND lid = '{$lid}'");
			$content[$lid] = $db->escape($content[$lid]);
			if ($result) {
				$db->execute("UPDATE {$db->pre}documents_content SET `title` = '{$title[$lid]}', `content` = '{$content[$lid]}', `active` = '{$active[$lid]}' WHERE did = '{$id}' AND lid = '{$lid}'");
			}
			else {
				$db->execute("INSERT INTO {$db->pre}documents_content ( `did` , `lid` , `title` , `content` , `active` ) VALUES ('{$id}', '{$lid}', '{$title[$lid]}', '{$content[$lid]}', '{$active[$lid]}')");
			}
		}
	}

	$scache->load('wraps')->delete();

	ok('admin.php?action=cms&job=doc', $lang->phrase('admin_cms_document_successfully_changed'));
}
?>