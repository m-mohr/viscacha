<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "cms.php") die('Error: Hacking Attempt');

require('classes/class.phpconfig.php');
require('lib/language.inc.php');

function SelectPackageLinks ($head) {
	?>
  <form style="float: right;" name="act" action="admin.php?action=locate" method="post">
  	<select size="1" name="url" onchange="locate(this.value)">
	 <option value="" selected="selected">Please choose</option>
	 <optgroup label="Management">
	  <option value="admin.php?action=cms&job=plugins_add&id=<?php echo $head['module']; ?>">Add Plugin</option>
	  <option value="admin.php?action=cms&job=package_info&id=<?php echo $head['module']; ?>">Information</option>
	  <?php if (isset($configs[$head['module']]) == true) { ?>
	   <option value="admin.php?action=settings&job=custom&id=<?php echo $configs[$head['module']]; ?>">Configuration</option>
	  <?php } ?>
	  <option value="admin.php?action=cms&job=package_export&id=<?php echo $head['module']; ?>">Export</option>
	  <option value="admin.php?action=cms&job=package_delete&id=<?php echo $head['module']; ?>">Delete</option>
	 </optgroup>
	 <optgroup label="Status">
	  <option value="admin.php?action=cms&job=plugins_active_all&value=1&id=<?php echo $head['module']; ?>">Activate all</option>
	  <option value="admin.php?action=cms&job=plugins_active_all&value=0&id=<?php echo $head['module']; ?>">Deactivate all</option>
	 </optgroup>
	</select>
	<input type="submit" value="Go" />
  </form>
	<?php
}

function BBCodeToolBox() {
	global $db, $scache, $config;

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
	<a id="menu_bbsmileys" style="display: block;text-align: center;width: 140px;" href="javascript:Link()"><img border="0" src="admin/html/images/desc.gif" alt="" /> more Smileys...</a>
	<script type="text/javascript">RegisterMenu('bbsmileys');</script>
	<div class="popup" id="popup_bbsmileys" style="height: 200px;width: 255px;overflow: auto;">
	<strong>Smileys</strong>
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
	<a id="menu_bbcolor" href="javascript:Link()"><img src="admin/html/images/desc.gif" alt="" /> Color</a>
		<script type="text/javascript">RegisterMenu('bbcolor');</script>
		<DIV class="popup" id="popup_bbcolor">
		<strong>Choose Color</strong>
		<div class="bbody">
		<script type="text/javascript">document.write(writeRow());</script>
		</div>
		</DIV>
	<a id="menu_bbsize" href="javascript:Link()"><img src="admin/html/images/desc.gif" alt="" /> Size</a>
		<script type="text/javascript">RegisterMenu('bbsize');</script>
		<div class="popup" id="popup_bbsize">
		<strong>Choose Size</strong>
	   	<ul>
			<li><span class="popup_line" onclick="InsertTagsMenu('[size=large]','[/size]','bbsize')" style="font-size: 1.3em;">Big Font</span></li>
			<li><span class="popup_line" onclick="InsertTagsMenu('[size=small]','[/size]','bbsize')" style="font-size: 0.8em;">Small Font</span></li>
			<li><span class="popup_line" onclick="InsertTagsMenu('[size=extended]','[/size]','bbsize')" style="letter-spacing: 3px;">Extended Font</span></li>
		</ul>
		</div>
	<a id="menu_bbalign" href="javascript:Link()"><img src="admin/html/images/desc.gif" alt="" /> Alignment</a>
		<script type="text/javascript">RegisterMenu('bbalign');</script>
		<DIV class="popup" id="popup_bbalign">
	   <strong>Choose Alignment</strong>
		<ul>
			<li><span class="popup_line" onclick="InsertTagsMenu('[align=left]','[/align]','bbalign')" style="text-align: left;">Left</span></li>
			<li><span class="popup_line" onclick="InsertTagsMenu('[align=center]','[/align]','bbalign')" style="text-align: center;">Center</span></li>
			<li><span class="popup_line" onclick="InsertTagsMenu('[align=right]','[/align]','bbalign')" style="text-align: right;">Right</span></li>
			<li><span class="popup_line" onclick="InsertTagsMenu('[align=justify]','[/align]','bbalign')" style="text-align: justify;">Justify</span></li>
		</ul>
		</DIV>
	<a id="menu_bbhx" href="javascript:Link()"><img src="admin/html/images/desc.gif" alt="" /> Heading</a>
		<script type="text/javascript">RegisterMenu('bbhx');</script>
		<div class="popup" id="popup_bbhx">
		<strong>Choose Heading</strong>
		<ul>
			<li><h4 class="popup_line" onclick="InsertTagsMenu('[h=large]','[/h]','bbhx')" style="margin: 0px; font-size: 14pt;">Heading 1</h4></li>
			<li><h5 class="popup_line" onclick="InsertTagsMenu('[h=middle]','[/h]','bbhx')" style=" margin: 0px; font-size: 13pt;">Heading 2</h5></li>
			<li><h6 class="popup_line" onclick="InsertTagsMenu('[h=small]','[/h]','bbhx')" style="margin: 0px; font-size: 12pt;">Heading 3</h6></li>
		</ul>
		</div>
	<a id="menu_help" href="misc.php?action=bbhelp<?php echo SID2URL_x; ?>" style="cursor: help;" target="_blank"><img src="./images/1/bbcodes/help.gif" alt="" /> <strong>Help</strong></a>
	<?php if ($config['spellcheck'] == 1) { ?>
	<script type="text/javascript" src="templates/spellChecker.js"></script>
	<a href="javascript:openSpellChecker(textfield);"><img src="./images/1/bbcodes/spellcheck.gif" alt="Spell Check" /></a>
	<?php } ?>
	<br />
	<a href="javascript:InsertTags('[b]','[/b]');" title="Boldface"><img src="./images/1/bbcodes/b.gif" alt="Boldface" /></a>
	<a href="javascript:InsertTags('[i]','[/i]');" title="Italic"><img src="./images/1/bbcodes/i.gif" alt="Italic" /></a>
	<a href="javascript:InsertTags('[u]','[/u]');" title="Underline"><img src="./images/1/bbcodes/u.gif" alt="Underline" /></a>
	<a href="javascript:InsertTags('[hr]','');" title="Horizontal Ruler"><img src="./images/1/bbcodes/hr.gif" alt="Horizontal Ruler" /></a>
	<a href="javascript:InsertTags('[img]','[/img]');" title="Image"><img src="./images/1/bbcodes/img.gif" alt="Image" /></a>
	<a href="javascript:InsertTagsParams('[url={param1}]{param2}','[/url]','Please provide URL (with http://)','Please provide text for the link');" title="Internet address (URL)"><img src="./images/1/bbcodes/url.gif" alt="Internet address (URL)" /></a>
	<a href="javascript:InsertTags('[email]','[/email]');" title="E-mail address"><img src="./images/1/bbcodes/email.gif" alt="E-mail address" /></a>
	<a href="javascript:InsertTags('[quote]','[/quote]');" title="Quote"><img src="./images/1/bbcodes/quote.gif" alt="Quote" /></a>
	<a href="javascript:InsertTags('[ot]','[/ot]');" title="Off Topic"><img src="./images/1/bbcodes/ot.gif" alt="Off Topic" /></a>
	<a href="javascript:popup_code();" title="Source Code (Syntax Highlighting)"><img src="./images/1/bbcodes/code.gif" alt="Source Code (Syntax Highlighting)" /></a>
	<a href="javascript:InsertTags('[edit]','[/edit]');" title="Later additions / Marking of edited passages"><img src="./images/1/bbcodes/edit.gif" alt="Later additions / Marking of edited passages" /></a>
	<a href="javascript:list();" title="Unordered list"><img src="./images/1/bbcodes/ul.gif" alt="Unordered list" /></a>
	<a href="javascript:list('ol');" title="Ordered list"><img src="./images/1/bbcodes/ol.gif" alt="Ordered list" /></a>
	<a title="Definition / Explanation" href="javascript:InsertTagsParams('[note={param1}]{param2}','[/note]','Please enter the definition of the word','Please enter the word to be defined');"><img src="./images/1/bbcodes/note.gif" alt="Definition / Explanation" /></a>
	<a href="javascript:InsertTags('[tt]','[/tt]');" title="Typewriter text"><img src="./images/1/bbcodes/tt.gif" alt="Typewriter text" /></a>
	<a href="javascript:InsertTags('[sub]','[/sub]');" title="Subscript"><img src="./images/1/bbcodes/sub.gif" alt="Subscript" /></a>
	<a href="javascript:InsertTags('[sup]','[/sup]');" title="Superscript"><img src="./images/1/bbcodes/sup.gif" alt="Superscript" /></a>
	<?php foreach ($cbb as $bb) { ?>
	<a href="javascript:<?php echo $bb['href']; ?>" title="<?php echo $bb['title']; ?>"><img src="<?php echo $bb['buttonimage']; ?>" alt="<?php echo $bb['title']; ?>" /></a>
	<?php } ?>
	</div>
  </td>
 </tr>
</table>
	<?php
}

($code = $plugins->load('admin_cms_jobs')) ? eval($code) : null;

if ($job == 'plugins') {
	send_nocache_header();
	echo head();
	if (!isset($my->settings['admin_plugins_sort'])) {
		$my->settings['admin_plugins_sort'] = 1;
	}
	$sort = $gpc->get('sort', int, $my->settings['admin_plugins_sort']);
	?>
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox">Manage Plugins</td>
	  </tr>
	  <tr>
	   <td class="mbox">
	   <ul>
		   <li><a href="admin.php?action=cms&job=package_add">Add Package</a></li>
		   <li><a href="admin.php?action=cms&job=package_import">Import Package</a></li>
		   <li><a href="admin.php?action=cms&job=plugins_add">Add Plugin</a></li>
		   <li>
			   <form method="get" name="admin.php" style="display: inline;">
			   Display of:
			   	<select name="sort">
			   		<option value="0"<?php echo iif($sort == 0, ' selected="selected"'); ?>>Hooks</option>
			   		<option value="1"<?php echo iif($sort == 1, ' selected="selected"'); ?>>Packages</option>
			   	</select>
			   	<input type="submit" value="Go" />
			   	<input type="hidden" name="action" value="cms" />
			   	<input type="hidden" name="job" value="plugins" />
			   </form>
		   </li>
	   </ul>
	   </td>
	  </tr>
	 </table>
	 <br class="minibr" />
	<?php
	if ($sort == 1) {
		$package = null;
		$my->settings['admin_plugins_sort'] = 1;

		$configs = array();
		$result = $db->query("SELECT id, name FROM {$db->pre}settings_groups WHERE LEFT(name, 7) = 'module_'");
		while ($row = $db->fetch_assoc($result)) {
			$id = substr($row['name'], 7);
			$configs[$id] = $row['id'];
		}

		$result = $db->query("
		SELECT p.*, m.title, m.id as module
		FROM {$db->pre}packages AS m
			LEFT JOIN {$db->pre}plugins AS p ON p.module = m.id
		ORDER BY m.id, p.position
		", __LINE__, __FILE__);
		?>
		 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		  <tr class="obox">
		   <td>Plugin</td>
		   <td>Hook</td>
		   <td>Status</td>
		   <td>Action</td>
		  </tr>
		<?php
		while ($head = $db->fetch_assoc($result)) {
			if ($head['module'] != $package) {
				?>
				<tr>
				<td class="ubox" colspan="4">
				<?php SelectPackageLinks($head); ?>
				  Package: <strong><?php echo $head['title']; ?></strong> (<?php echo $head['module']; ?>)
				</td>
				</tr>
				<?php
				$package = $head['module'];
			}
			if ($head['name'] != null) {
				?>
				<tr class="mbox">
					<td><?php echo $head['name']; ?><?php echo iif ($head['active'] == 0, ' (<em>Inactive</em>)'); ?></td>
					<td nowrap="nowrap"><?php echo $head['position']; ?></td>
					<td nowrap="nowrap">
						<?php
						if ($head['active'] == 1) {
							echo '<a href="admin.php?action=cms&amp;job=plugins_active&amp;id='.$head['id'].'&amp;value=0">Deactivate</a>';
						}
						else {
							echo '<a href="admin.php?action=cms&amp;job=plugins_active&amp;id='.$head['id'].'&amp;value=1">Activate</a>';
						}
						?>
					</td>
					<td>
					 <a class="button" href="admin.php?action=cms&amp;job=plugins_edit&amp;id=<?php echo $head['id']; ?>">Edit</a>
					 <a class="button" href="admin.php?action=cms&amp;job=plugins_delete&amp;id=<?php echo $head['id']; ?>">Delete</a>
					</td>
				</tr>
				<?php
			}
			else {
				?>
				<tr class="mbox">
					<td colspan="4"><em>For this package there is no plugin specified.</em></td>
				</tr>
				<?php
			}
		}
		echo '</table>';
	}
	else {
		$pos = null;
		$my->settings['admin_plugins_sort'] = 0;

		$result = $db->query("
		SELECT p.*, m.title
		FROM {$db->pre}plugins AS p
			LEFT JOIN {$db->pre}packages AS m ON p.module = m.id
		ORDER BY p.position, p.ordering
		", __LINE__, __FILE__);
		?>
		 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		  <tr class="obox">
		   <td width="30%">Plugin</td>
		   <td width="28%">Package</td>
		   <td width="11%">Status</td>
		   <td width="9%">Priority</td>
		   <td width="22%">Action</td>
		  </tr>
		<?php
		while ($head = $db->fetch_assoc($result)) {
			if ($head['position'] != $pos) {
				?>
				<tr>
					<td class="ubox" colspan="5">Hook: <strong><?php echo $head['position']; ?></strong></td>
				</tr>
				<?php
				$pos = $head['position'];
			}
			?>
			<tr class="mbox">
				<td><?php echo $head['name']; ?><?php echo iif ($head['active'] == 0, ' (<em>Inactvie</em>)'); ?></td>
				<td nowrap="nowrap" title="<?php echo htmlspecialchars($head['title']); ?>">
					<?php SelectPackageLinks($head); echo $head['module']; ?>&nbsp;&nbsp;
				</td>
				<td nowrap="nowrap">
					<?php
					if ($head['active'] == 1) {
						echo '<a href="admin.php?action=cms&amp;job=plugins_active&amp;id='.$head['id'].'&amp;value=0">Deactivate</a>';
					}
					else {
						echo '<a href="admin.php?action=cms&amp;job=plugins_active&amp;id='.$head['id'].'&amp;value=1">Activate</a>';
					}
					?>
				</td>
				<td align="right" nowrap="nowrap">
					<?php echo $head['ordering']; ?>&nbsp;&nbsp;
		 			<a href="admin.php?action=cms&amp;job=plugins_move&amp;id=<?php echo $head['id']; ?>&amp;value=-1"><img src="admin/html/images/asc.gif" border="0" alt="Up"></a>&nbsp;
		 			<a href="admin.php?action=cms&amp;job=plugins_move&amp;id=<?php echo $head['id']; ?>&amp;value=1"><img src="admin/html/images/desc.gif" border="0" alt="Down"></a>
				</td>
				<td>
				 <a class="button" href="admin.php?action=cms&amp;job=plugins_edit&amp;id=<?php echo $head['id']; ?>">Edit</a>
				 <a class="button" href="admin.php?action=cms&amp;job=plugins_delete&amp;id=<?php echo $head['id']; ?>">Delete</a>
				</td>
			</tr>
			<?php
		}
		echo '</table>';
	}
	echo foot();
}
elseif ($job == 'plugins_move') {
	$id = $gpc->get('id', int);
	$pos = $gpc->get('value', int);
	if ($id < 1) {
		error('admin.php?action=cms&job=nav', 'Invalid ID given');
	}
	if ($pos < 0) {
		$db->query('UPDATE '.$db->pre.'plugins SET ordering = ordering-1 WHERE id = '.$id, __LINE__, __FILE__);
	}
	elseif ($pos > 0) {
		$db->query('UPDATE '.$db->pre.'plugins SET ordering = ordering+1 WHERE id = '.$id, __LINE__, __FILE__);
	}

	$result = $db->query("SELECT position FROM {$db->pre}plugins WHERE id = '{$id}'", __LINE__, __FILE__);
	$row = $db->fetch_assoc($result);
	$filesystem->unlink('cache/modules/'.$plugins->_group($row['position']).'.php');
	viscacha_header('Location: admin.php?action=cms&job=plugins');
}
elseif ($job == 'plugins_active') {
	$id = $gpc->get('id', int);
	$active = $gpc->get('value', int);
	if ($active != 0 && $active != 1) {
		error('admin.php?action=cms&job=nav', 'Invalid status given');
	}

	$db->query('UPDATE '.$db->pre.'plugins SET active = "'.$active.'" WHERE id = '.$id, __LINE__, __FILE__);

	$result = $db->query("SELECT position FROM {$db->pre}plugins WHERE id = '{$id}'", __LINE__, __FILE__);
	$row = $db->fetch_assoc($result);
	$filesystem->unlink('cache/modules/'.$plugins->_group($row['position']).'.php');
	viscacha_header('Location: admin.php?action=cms&job=plugins');
}
elseif ($job == 'plugins_active_all') {
	$id = $gpc->get('id', int);
	$active = $gpc->get('value', int);
	if ($active != 0 && $active != 1) {
		error('admin.php?action=cms&job=nav', 'Invalid status given');
	}

	$db->query('UPDATE '.$db->pre.'plugins SET active = "'.$active.'" WHERE module = '.$id, __LINE__, __FILE__);

	$result = $db->query("SELECT position FROM {$db->pre}plugins WHERE module = '{$id}'", __LINE__, __FILE__);
	while ($row = $db->fetch_assoc($result)) {
		$filesystem->unlink('cache/modules/'.$plugins->_group($row['position']).'.php');
	}
	viscacha_header('Location: admin.php?action=cms&job=plugins&sort=1');
}
elseif ($job == 'plugins_delete') {
	echo head();
	$id = $gpc->get('id', int);
	?>
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	<tr><td class="obox">Delete Package</td></tr>
	<tr><td class="mbox">
	<p align="center">Do you really want to delete this plugin?</p>
	<p align="center">
	<a href="admin.php?action=cms&job=plugins_delete2&id=<?php echo $id; ?>"><img border="0" align="middle" alt="" src="admin/html/images/yes.gif"> Yes</a>
	&nbsp&nbsp;&nbsp;&nbsp&nbsp;&nbsp;
	<a href="javascript: history.back(-1);"><img border="0" align="middle" alt="" src="admin/html/images/no.gif"> No</a>
	</p>
	</td></tr>
	</table>
	<?php
	echo foot();
}
elseif ($job == 'plugins_delete2') {
	$id = $gpc->get('id', int);

	$result = $db->query("SELECT * FROM {$db->pre}plugins WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	$data = $db->fetch_assoc($result);

	$dir = "modules/{$data['module']}/";
	$ini = $myini->read($dir."config.ini");
	$delete = true;
	$file = $ini['php'][$data['position']];
	foreach ($ini['php'] as $pos => $val) {
		if ($pos != $data['position'] && $file == $val) {
			$delete = false;
		}
	}
	unset($ini['php'][$data['position']]);
	if (file_exists($dir.$file) && $delete == true) {
		$filesystem->unlink($dir.$file);
	}
	$myini->write($dir."config.ini", $ini);

	$db->query("DELETE FROM {$db->pre}plugins WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);

	$filesystem->unlink('cache/modules/'.$plugins->_group($data['position']).'.php');

	echo head();
	ok('admin.php?action=cms&job=plugins', 'Plugin successfully deleted!');
}
elseif ($job == 'plugins_edit') {
	echo head();
	$pluginid = $gpc->get('id', int);
	$result = $db->query("
	SELECT p.*, m.title
	FROM {$db->pre}plugins AS p
		LEFT JOIN {$db->pre}packages AS m ON p.module = m.id
	WHERE p.id = '{$pluginid}'
	LIMIT 1
	", __LINE__, __FILE__);
	if ($db->num_rows($result) != 1) {
		error("admin.php?action=cms&job=plugins", "Plugin not found");
	}
	$package = $db->fetch_assoc($result);
	$dir = "modules/{$package['module']}/";
	$ini = $myini->read($dir.'config.ini');
	$hooks = getHookArray();
	if (!isset($ini['php'][$package['position']])) {
		$code = '';
		$codefile = 'Unknown';
	}
	else {
		$codefile = $ini['php'][$package['position']];
		$code = file_get_contents($dir.$codefile);
	}
	$cp = array();
	foreach ($ini['php'] as $ihook => $ifile) {
		if ($ifile == $codefile) {
			$cp[] = $ihook;
		}
	}
	sort($cp);
	?>
	<form method="post" action="admin.php?action=cms&job=plugins_edit2&id=<?php echo $pluginid; ?>">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	 <tr>
	  <td class="obox" colspan="2">Edit Plugin</td>
	 </tr>
	 <tr class="mbox">
	  <td width="25%">Title for Plugin:<br /><span class="stext">Maximum number of characters: 200; Minimum number of characters: 4</span></td>
	  <td width="75%"><input type="text" name="title" size="40" value="<?php echo $package['title']; ?>" /></td>
	 </tr>
	 <tr class="mbox">
	  <td>Package:</td>
	  <td><strong><?php echo $package['title']; ?></strong></td>
	 </tr>
	 <tr class="mbox">
	  <td>Hook:</td>
	  <td><select name="hook">
	  <?php foreach ($hooks as $group => $positions) { ?>
	  <optgroup label="<?php echo $group; ?>">
		  <?php foreach ($positions as $hook) { ?>
		  <option value="<?php echo $hook; ?>"<?php echo iif($hook == $package['position'], ' selected="selected"'); ?>><?php echo $hook; ?></option>
		  <?php } ?>
	  </optgroup>
	  <?php } ?>
	  </select></td>
	 </tr>
	 <tr class="mbox" valign="top">
	  <td>
	  Code:<br /><br />
	  <ul>
		<li><a href="admin.php?action=cms&amp;job=package_template&amp;id=<?php echo $package['module']; ?>" target="_blank">Add Template</a></li>
		<li><a href="admin.php?action=cms&amp;job=package_language&amp;id=<?php echo $package['module']; ?>" target="_blank">Add Phrase</a></li>
	  </ul>
	  <?php if (count($cp) > 0) { ?>
	  <br /><br /><span class="stext"><strong>Caution</strong>: Changes to the code also affect the following hooks:</span>
	  <ul>
	  <?php foreach ($cp as $ihook) { ?>
	  	<li class="stext"><?php echo $ihook; ?></li>
	  <?php } ?>
	  </ul>
	  <?php } ?>
	  </td>
	  <td><textarea name="code" rows="10" cols="80" class="texteditor"><?php echo htmlspecialchars($code); ?></textarea></td>
	 </tr>
	 <tr class="mbox">
	  <td width="25%">File for Code:<br /><span class="stext">This file is located in the folder <code><?php echo $config['fpath']; ?>/modules/<?php echo $package['id']; ?>/</code>.</span></td>
	  <td width="75%"><?php echo $codefile; ?></td>
	 </tr>
	 <tr class="mbox">
	  <td>Active:</td>
	  <td><input type="checkbox" name="active" value="1"<?php echo iif($package['active'] == 1, ' checked="checked"'); ?> /></td>
	 </tr>
	 <tr>
	  <td class="ubox" colspan="2" align="center"><input type="submit" value="Save" /></td>
	 </tr>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'plugins_edit2') {
	echo head();
	$id = $gpc->get('id', int);
	$title = $gpc->get('title', str);
	$hook = $gpc->get('hook', str);
	$code = $gpc->get('code', none);
	$active = $gpc->get('active', int);

	$result = $db->query("SELECT module, position FROM {$db->pre}plugins WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	$data = $db->fetch_assoc($result);
	$dir = "modules/{$data['module']}/";

	if (strlen($title) < 4) {
		error('admin.php?action=cms&job=plugins_edit&id='.$package['id'], 'Minimum number of characters for title: 4');
	}
	if (strlen($title) > 200) {
		error('admin.php?action=cms&job=plugins_edit&id='.$package['id'], 'Maximum number of characters for title: 200');
	}

	$db->query("UPDATE {$db->pre}plugins SET `name` = '{$title}', `active` = '{$active}', `position` = '{$hook}' WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);

	$ini = $myini->read($dir."config.ini");
	$file = $ini['php'][$data['position']];

	$filesystem->chmod($dir.$file, 0666);
	$filesystem->file_put_contents($dir.$file, $code);

	if ($data['position'] != $hook) {
		unset($ini['php'][$data['position']]);
		$ini['php'][$hook] = $file;
		$myini->write($dir."config.ini", $ini);
		$filesystem->unlink('cache/modules/'.$plugins->_group($hook).'.php');
	}

	$filesystem->unlink('cache/modules/'.$plugins->_group($data['position']).'.php');

	ok('admin.php?action=cms&job=plugins', 'Plugin successfully edited!');
}
elseif ($job == 'plugins_add') {
	echo head();
	$packageid = $gpc->get('id', int);
	if ($packageid > 0) {
		$result = $db->query("SELECT title FROM {$db->pre}packages WHERE id = '{$packageid}' LIMIT 1");
		$package = $db->fetch_assoc($result);
	}
	else {
		$result = $db->query("SELECT id, title FROM {$db->pre}packages");
	}
	$hooks = getHookArray();
	?>
	<form method="post" action="admin.php?action=cms&job=plugins_add2">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	 <tr>
	  <td class="obox" colspan="2">Add Plugin - Step 1 of 3</td>
	 </tr>
	 <tr class="mbox">
	  <td width="25%">Title for Plugin:<br /><span class="stext">Maximum number of characters: 200; Minimum number of characters: 4</span></td>
	  <td width="75%"><input type="text" name="title" size="40" /></td>
	 </tr>
	 <tr class="mbox">
	  <td>Package:</td>
	  <td>
	  <?php if ($packageid > 0) { ?>
		<strong><?php echo $package['title']; ?></strong>
		<input type="hidden" name="package" value="<?php echo $packageid; ?>" />
	  <?php } else { ?>
	  <select name="package">
	  	<?php while ($row = $db->fetch_assoc($result)) { ?>
	  	<option value="<?php echo $row['id']; ?>"><?php echo $row['title']; ?></option>
	  	<?php } ?>
	  </select>
	  <?php } ?>
	  </td>
	 </tr>
	 <tr class="mbox">
	  <td>Hook:</td>
	  <td><select name="hook">
	  <?php foreach ($hooks as $group => $positions) { ?>
	  <optgroup label="<?php echo $group; ?>">
		  <?php foreach ($positions as $hook) { ?>
		  <option value="<?php echo $hook; ?>"><?php echo $hook; ?></option>
		  <?php } ?>
	  </optgroup>
	  <?php } ?>
	  </select></td>
	 </tr>
	 <tr>
	  <td class="ubox" colspan="2" align="center"><input type="submit" value="Save" /></td>
	 </tr>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'plugins_add2') {
	echo head();
	$hook = $gpc->get('hook', str);
	$isInvisibleHook = isInvisibleHook($hook);
	$packageid = $gpc->get('package', int);
	$title = $gpc->get('title', str);
	$result = $db->query("SELECT id, title FROM {$db->pre}packages WHERE id = '{$packageid}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows() != 1) {
		echo head();
		error('admin.php?action=cms&job=plugins_add', 'Specified package ('.$packageid.') does not exist.');
	}
	$package = $db->fetch_assoc($result);
	if (strlen($title) < 4) {
		error('admin.php?action=cms&job=plugins_add&id='.$package['id'], 'Minimum number of characters for title: 4');
	}
	if (strlen($title) > 200) {
		error('admin.php?action=cms&job=plugins_add&id='.$package['id'], 'Maximum number of characters for title: 200');
	}

	if (!$isInvisibleHook) {
		$hookPriority = $db->query("SELECT id, name, ordering FROM {$db->pre}plugins WHERE position = '{$hook}' ORDER BY ordering", __LINE__, __FILE__);

		$db->query("
		INSERT INTO {$db->pre}plugins
		(`name`,`module`,`ordering`,`active`,`position`)
		VALUES
		('{$title}','{$package['id']}','-1','0','{$hook}')
		", __LINE__, __FILE__);
		$pluginid = $db->insert_id();
	}

	$filetitle = convert2adress($title);
	$dir = "modules/{$package['id']}/";
	$codefile = "{$filetitle}.php";
	$i = 1;
	while (file_exists($dir.$codefile)) {
		$codefile = "{$filetitle}_{$i}.php";
		$i++;
	}

	$last = null;
	?>
	<form method="post" action="admin.php?action=cms&job=plugins_add3&id=<?php echo $pluginid; ?>&package=<?php echo $package['id']; ?>">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	 <tr>
	  <td class="obox" colspan="2">Add Plugin - Step 2 of 3</td>
	 </tr>
	 <tr class="mbox">
	  <td width="25%">Title for Plugin:<br /><span class="stext">Maximum number of characters: 200; Minimum number of characters: 4</span></td>
	  <td width="75%"><input type="text" name="title" size="40" value="<?php echo htmlspecialchars($title); ?>" /></td>
	 </tr>
	 <tr class="mbox">
	  <td>Package:</td>
	  <td><strong><?php echo $package['title']; ?></strong> (<?php echo $package['id']; ?>)</td>
	 </tr>
	 <tr class="mbox">
	  <td>Hook:</td>
	  <td><strong><?php echo $hook; ?></strong></td>
	 </tr>
	 <tr class="mbox" valign="top">
	  <td>
	  Code:<br /><br />
	  <span class="stext">At this place you can insert PHP-Code which will be executed in the indicated hook. You don't need to use &lt;?php bzw. ?&gt;-Tags at the beginning and the end of your code. You also can use templates and phrases for this plugin (more information down of this page). More information can be found in the documentation.</span>
	  <br /><br />
	  <ul>
		<li><a href="admin.php?action=cms&amp;job=package_template&amp;id=<?php echo $package['id']; ?>" target="_blank">Add Template</a></li>
		<li><a href="admin.php?action=cms&amp;job=package_language&amp;id=<?php echo $package['id']; ?>" target="_blank">Add Phrase</a></li>
	  </ul>
	  </td>
	  <td><textarea name="code" rows="10" cols="80" class="texteditor"></textarea></td>
	 </tr>
	 <tr class="mbox">
	  <td width="25%">File for Code:<br /><span class="stext">In this file the code will be saved. This file is located in the folder <code><?php echo $config['fpath']; ?>/modules/<?php echo $package['id']; ?>/</code>. If the file exists, the code above will be ignored.</span></td>
	  <td width="75%"><input type="text" name="file" size="40" value="<?php echo $codefile; ?>" /></td>
	 </tr>
	 <?php if (!$isInvisibleHook) { ?>
	 <tr class="mbox">
	  <td>Priority:</td>
	  <td><select name="priority">
	  <?php while ($row = $db->fetch_assoc($hookPriority)) { $last = $row['name']; ?>
	  <option value="<?php echo $row['id']; ?>">Before <?php echo $row['name']; ?></option>
	  <?php } ?>
	  <option value="max">After <?php echo $last; ?></option>
	  </select></td>
	 </tr>
	 <tr class="mbox">
	  <td>Active:</td>
	  <td><input type="checkbox" name="active" value="1" /></td>
	 </tr>
	 <?php } ?>
	 <tr>
	  <td class="ubox" colspan="2" align="center"><input type="submit" value="Save" /></td>
	 </tr>
	</table>
	</form>
	<?php
}
elseif ($job == 'plugins_add3') {
	echo head();
	$id = $gpc->get('id', int);
	$package = $gpc->get('package', int);
	$title = $gpc->get('title', str);
	$code = $gpc->get('code', none);
	$file = $gpc->get('file', none);
	$priority = $gpc->get('priority', none);
	$active = $gpc->get('active', int);

	$result = $db->query("SELECT module, name, position FROM {$db->pre}plugins WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	$data = $db->fetch_assoc($result);
	$isInvisibleHook = isInvisibleHook($data['position']);

	if (!$isInvisibleHook) {
		$dir = "modules/{$data['module']}/";
	}
	else {
		$dir = "modules/{$package}/";
	}

	if (strlen($title) < 4 || strlen($title) > 200) {
		$title = $data['title'];
	}

	if (!$isInvisibleHook) {
		if (is_id($priority)) {
			$result = $db->query("SELECT id, ordering FROM {$db->pre}plugins WHERE id = '{$priority}' LIMIT 1", __LINE__, __FILE__);
			$row = $db->fetch_assoc($result);
			$priority = $row['ordering']-1;
			$result = $db->query("UPDATE {$db->pre}plugins SET ordering = ordering-1 WHERE ordering < '{$priority}' AND position = '{$data['position']}'", __LINE__, __FILE__);
		}
		else {
			$result = $db->query("SELECT MAX(ordering) AS maximum FROM {$db->pre}plugins WHERE position = '{$data['position']}'", __LINE__, __FILE__);
			$row = $db->fetch_assoc($result);
			$priority = $row['maximum']+1;
		}

		$db->query("UPDATE {$db->pre}plugins SET `name` = '{$title}', `ordering` = '{$priority}', `active` = '{$active}' WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	}

	if (file_exists($dir.$file) == false) {
		$filesystem->file_put_contents($dir.$file, $code);
		$filesystem->chmod($dir.$file, 0666);
	}

	$ini = $myini->read($dir."config.ini");
	$ini['php'][$data['position']] = $file;
	$myini->write($dir."config.ini", $ini);

	if (!$isInvisibleHook) {
		$filesystem->unlink('cache/modules/'.$plugins->_group($data['position']).'.php');
	}
	if ($data['position'] == 'navigation') {
		ok('admin.php?action=cms&job=nav_addplugin&id='.$data['module'], 'Step 3 of 3: Plugin successfully added! You have added a plugin to the hook "navigation". Before you can use it in your navigation, you have to add it to your Navigation Manager.');
	}
	else {
		ok('admin.php?action=cms&job=plugins_add&id='.$data['module'], 'Step 3 of 3: Plugin successfully added!');
	}
}
elseif ($job == 'package_template') {
	$id = $gpc->get('id', int);

	$result = $db->query("SELECT id, title FROM {$db->pre}packages WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows() != 1) {
		echo head();
		error('javascript: self.close();', 'Specified package ('.$id.') does not exist.');
	}
	$data = $db->fetch_assoc($result);
	$dir = "modules/{$data['id']}/";
	$ini = $myini->read($dir."config.ini");

	$designObj = $scache->load('loaddesign');
	$designs = $designObj->get(true);
	$standardDesign = $designs[$config['templatedir']]['template'];
	$tpldir = "templates/{$standardDesign}/modules/{$data['id']}/";

	// ToDo: Prüfen ob .html variabel sein sollte (class.template.php => Endung der Templates ist variabel, nur standardmäßig html)
	$filetitle = convert2adress($data['title']);
	$codefile = "{$filetitle}.html";
	$i = 1;
	while (file_exists($tpldir.$codefile)) {
		$codefile = "{$filetitle}_{$i}.html";
		$i++;
	}

	echo head();
	?>
	<form method="post" action="admin.php?action=cms&job=package_template_edit&id=<?php echo $data['id']; ?>">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	 <tr>
	  <td class="obox" colspan="3">
	  <span style="float: right;"><a class="button" href="javascript: self.close();">Close Window</a></span>
	  Manage Templates for Package: <?php echo $data['title']; ?></td>
	 </tr>
	 <?php if (isset($ini['template']) && count($ini['template']) > 0) { ?>
	 <tr class="mbox">
	  <td width="10%">Edit</td>
	  <td width="10%">Delete</td>
	  <td width="80%">File</td>
	 </tr>
	 <?php foreach ($ini['template'] as $key => $file) { ?>
	 <tr class="mbox">
	  <td><input type="radio" name="edit" value="<?php echo $key; ?>" /></td>
	  <td><input type="checkbox" name="delete[]" value="<?php echo $key; ?>" /></td>
	  <td><?php echo $file; ?></td>
	 </tr>
	 <?php } ?>
	 <tr>
	  <td class="ubox" colspan="3" align="center"><input type="submit" value="Submit" /></td>
	 </tr>
	 <?php } else { ?>
	 <tr class="mbox">
	  <td colspan="3">No Template available for this Package.</td>
	 </tr>
	 <?php } ?>
	</table>
	</form>
	<br class="minibr" />
	<form method="post" action="admin.php?action=cms&job=package_template_add&id=<?php echo $data['id']; ?>">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	 <tr>
	  <td class="obox" colspan="2">Add Template to Package</td>
	 </tr>
	 <tr class="mbox" valign="top">
	  <td>
	  Code:<br /><br />
	  <ul>
		<li><a href="admin.php?action=cms&amp;job=package_language&amp;id=<?php echo $data['id']; ?>" target="_blank">Add Phrase</a></li>
	  </ul>
	  </td>
	  <td><textarea name="code" rows="8" cols="80" class="texteditor"></textarea></td>
	 </tr>
	 <tr class="mbox">
	  <td width="25%">File for Code:<br /><span class="stext">In this file the code will be saved. This file is located in the folder <code><?php echo $config['fpath']; ?>/<?php echo $tpldir; ?></code>.</span></td>
	  <td width="75%"><input type="text" name="file" size="40" value="<?php echo $codefile; ?>" /></td>
	 </tr>
	 <tr>
	  <td class="ubox" colspan="2" align="center"><input type="submit" value="Save" /></td>
	 </tr>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'package_template_add') {
	$id = $gpc->get('id', int);
	$code = $gpc->get('code', none);
	$file = $gpc->get('file', none);

	$result = $db->query("SELECT id, title FROM {$db->pre}packages WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows() != 1) {
		echo head();
		error('javascript: self.close();', 'Specified package ('.$id.') does not exist.');
	}
	$data = $db->fetch_assoc($result);
	$dir = "modules/{$data['id']}/";

	$designObj = $scache->load('loaddesign');
	$designs = $designObj->get(true);
	$standardDesign = $designs[$config['templatedir']]['template'];
	$tpldir = "templates/{$standardDesign}/modules/{$data['id']}/";
	if (!is_dir($tpldir)) {
		$filesystem->mkdir($tpldir, 0777);
	}
	$filesystem->file_put_contents($tpldir.$file, $code);
	$filesystem->chmod($tpldir.$file, 0666);

	$ini = $myini->read($dir."config.ini");
	$ini['template'][] = $file;
	$myini->write($dir."config.ini", $ini);

	echo head();
	ok('admin.php?action=cms&job=package_template&id='.$data['id']);
}
elseif ($job == 'package_template_edit') {
	echo head();

	$id = $gpc->get('id', int);
	$editId = $gpc->get('edit', int, -1);
	$deleteId = $gpc->get('delete', arr_int);
	$output = -1;

	$result = $db->query("SELECT id, title FROM {$db->pre}packages WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows() != 1) {
		echo head();
		error('javascript: self.close();', 'Specified package ('.$id.') does not exist.');
	}
	$data = $db->fetch_assoc($result);
	$dir = "modules/{$data['id']}/";
	$ini = $myini->read($dir."config.ini");

	if (count($deleteId) > 0) {
		$designObj = $scache->load('loaddesign');
		$designs = $designObj->get(true);

		foreach ($deleteId as $key) {
			if (!isset($ini['template'][$key])) {
				continue;
			}
			$file = $ini['template'][$key];
			foreach ($designs as $row) {
				$tplfile = "templates/{$row['template']}/modules/{$data['id']}/{$file}";
				if (file_exists($tplfile)) {
					$filesystem->unlink($tplfile);
				}
			}
			unset($ini['template'][$key]);
		}

		$myini->write($dir."config.ini", $ini);
		$output = 0;
	}

	if ($editId > -1 && isset($ini['template'][$editId])) {
		if ($output == 0) {
			?>
			<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
			  <tr><td class="obox">Confirmation:</td></tr>
			  <tr><td class="mbox" align="center">Template(s) successfully deleted</td></tr>
			</table><br class="minibr" />
			<?php
		}
		$codefile = $ini['template'][$editId];
		$designObj = $scache->load('loaddesign');
		$designs = $designObj->get(true);

		$tpldirs = array();
		foreach ($designs as $designId => $row) {
			$dir = "templates/{$row['template']}/modules/{$data['id']}/";
			if (file_exists($dir.$codefile)) {
				$tpldirs[$row['template']]['names'][] = $row['name'];
				$tpldirs[$row['template']]['ids'][] = $row['id'];
			}
		}

		?>
		<form method="post" action="admin.php?action=cms&job=package_template_edit2&id=<?php echo $data['id']; ?>&edit=<?php echo $editId; ?>">
		<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		 <tr>
		  <td class="obox" colspan="2">Add Template to Package</td>
		 </tr>
		 <tr class="mbox" valign="top">
		  <td rowspan="<?php echo count($tpldirs); ?>">
			Code:<br /><br />
			<ul><li><a href="admin.php?action=cms&amp;job=package_language&amp;id=<?php echo $data['id']; ?>" target="_blank">Add Phrase</a></li></ul>
		  </td>
		  <?php
		  $first = true;
		  foreach ($tpldirs as $tplid => $designId) {
		  	if ( in_array($config['templatedir'], $designId['ids']) ) {
		  		$affected = 'All designs that have not defined an own template';
		  	}
		  	else {
		  		$affected = implode(', ', $designId['names']);
		  	}
		  	$dir = "templates/{$tplid}/modules/{$data['id']}/";
		  	$content = file_get_contents($dir.$codefile);
		  	if ($first == false) {
		  		echo '<tr>';
		  		$first = false;
		  	}
		  	echo '<td>';
		  	echo 'Template Group: <b>'.$tplid.'</b><br />';
		  	echo 'Design(s) affected by changes: '.$affected.'<br />';
		  	echo '<textarea name="code['.$tplid.']" rows="8" cols="80" class="texteditor">'.$content.'</textarea>';
		  	echo '</td></tr>';
		  }
		  ?>
		 <tr class="mbox">
		  <td width="25%">File for Code:</td>
		  <td width="75%"><?php echo $codefile; ?></td>
		 </tr>
		 <tr>
		  <td class="ubox" colspan="2" align="center"><input type="submit" value="Save" /></td>
		 </tr>
		</table>
		</form>
		<?php
		$output = 1;
	}

	if ($output == -1) {
		error('admin.php?action=cms&job=package_template&id='.$data['id'], 'Please choose at least one template...');
	}
	elseif ($output == 0) {
		ok('admin.php?action=cms&job=package_template&id='.$data['id'], 'Template(s) successfully deleted');
	}
}
elseif ($job == 'package_template_edit2') {
	$id = $gpc->get('id', int);
	$editId = $gpc->get('edit', int, -1);
	$code = $gpc->get('code', arr_none);

	$result = $db->query("SELECT id FROM {$db->pre}packages WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows() != 1) {
		echo head();
		error('javascript: self.close();', 'Specified package ('.$id.') does not exist.');
	}
	$data = $db->fetch_assoc($result);
	$ini = $myini->read("modules/{$data['id']}/config.ini");
	if (!isset($ini['template'][$editId])) {
		echo head();
		error('javascript: self.close();', 'Specified template ('.$id.') does not exist in INI-File.');
	}
	$file = $ini['template'][$editId];

	foreach ($code as $tpldir => $html) {
		$filepath = "templates/{$tpldir}/modules/{$data['id']}/";
		if (is_dir($filepath)) {
			$filesystem->file_put_contents($filepath.$file, $html);
		}
	}
	echo head();
	ok('admin.php?action=cms&job=package_template&id='.$id);
}
elseif ($job == 'package_language') {
	echo head();

	$id = $gpc->get('id', int);
	$result = $db->query("SELECT id, title FROM {$db->pre}packages WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows() != 1) {
		echo head();
		error('javascript: self.close();', 'Specified package ('.$id.') does not exist.');
	}
	$data = $db->fetch_assoc($result);

	$dir = "modules/{$data['id']}/";
	$ini = $myini->read($dir."config.ini");
	if (!isset($ini['language'])) {
		$ini['language'] = array();
	}

	$file = 'modules.lng.php';
	$group = substr($file, 0, strlen($file)-8);
	$page = $gpc->get('page', int, 1);
	$cache = array();
	$diff = array();
	$complete = array();
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	while($row = $db->fetch_assoc($result)) {
		$cache[$row['id']] = $row;
		$diff[$row['id']] = array_keys(return_array($group, $row['id']));
		$complete = array_merge($complete, array_diff($diff[$row['id']], $complete) );
	}
	sort($complete);
	$width = floor(75/count($cache));
	?>
<form name="form" method="post" action="admin.php?action=cms&job=package_language_delete&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="<?php echo count($cache)+1; ?>">
   <span style="float: right;"><a class="button" href="admin.php?action=cms&job=package_language_add&id=<?php echo $id; ?>">Add new Phrase</a></span>
   Phrase Manager</td>
  </tr>
  <?php if (count($ini['language']) == 0) { ?>
  <tr>
   <td class="mbox" colspan="<?php echo count($cache)+1; ?>">There were no phrases created. <a class="button" href="admin.php?action=cms&job=package_language_add&id=<?php echo $id; ?>">Add new Phrase</a></td>
  </tr>
  <?php } else { ?>
  <tr>
   <td class="mmbox" width="25%">&nbsp;</td>
   <?php foreach ($cache as $row) { ?>
   <td class="mmbox" align="center" width="<?php echo $width; ?>%"><?php echo $row['language']; ?></td>
   <?php } ?>
  </tr>
  <?php foreach ($ini['language'] as $phrase => $value) { ?>
  <tr>
   <td class="mmbox"><input type="checkbox" name="delete[]" value="<?php echo $phrase; ?>">&nbsp;<a class="button" href="admin.php?action=cms&job=package_language_edit&phrase=<?php echo $phrase; ?>&id=<?php echo $id; ?>">Edit</a>&nbsp;<?php echo $phrase; ?></td>
   <?php
   foreach ($cache as $row) {
   	$status = in_array($phrase, $diff[$row['id']]);
   ?>
   <td class="mbox" align="center"><?php echo noki($status).iif(!$status, ' <a class="button" href="admin.php?action=cms&job=package_language_copy&language='.$row['id'].'&phrase='.$phrase.'&id='.$id.'">Add</a>'); ?></td>
   <?php } ?>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" align="center" colspan="<?php echo count($cache)+1; ?>"><input type="submit" value="Delete selected phrases"></td>
  </tr>
  <?php } ?>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'package_language_add') {
	echo head();

	$id = $gpc->get('id', int);
	$result = $db->query("SELECT id, title FROM {$db->pre}packages WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows() != 1) {
		echo head();
		error('javascript: self.close();', 'Specified package ('.$id.') does not exist.');
	}
	$data = $db->fetch_assoc($result);

	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	?>
<form name="form" method="post" action="admin.php?action=cms&job=package_language_save2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2">Phrase Manager &raquo; Add new Phrase to Package</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Varname:<br />
   <span class="stext">Varname is a value which can only contain letters, numbers and underscores.</span></td>
   <td class="mbox" width="50%"><input type="text" name="varname" size="50" value="" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Text:<br />
   <span class="stext">This is the default language used also in the exported packages. It is recommended to write English here.</span></td>
   <td class="mbox" width="50%"><input type="text" name="text" size="50" /></td>
  </tr>
  <tr>
   <td class="obox" colspan="2">Translations</td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><ul>
	<li>When inserting a custom phrase, you may also specify the translations into whatever languages you have installed.</li>
	<li>If you do leave a translation box blank, it will inherit the text from the 'Text' box.</li>
   </ul></td>
  </tr>
  <?php while($row = $db->fetch_assoc($result)) { ?>
  <tr>
   <td class="mbox" width="50%"><em><?php echo $row['language']; ?></em> Translation:<br /><span class="stext">Optional. HTML is allowed but not recommended.</span></td>
   <td class="mbox" width="50%"><input type="text" name="langt[<?php echo $row['id']; ?>]" size="50" /></td>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Save" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'package_language_save2') {
	// This is used for adding AND editing!

	echo head();

	$id = $gpc->get('id', int);
	$varname = $gpc->get('varname', none);
	$text = $gpc->get('text', none);
	$lang = $gpc->get('langt', none);

	$result = $db->query("SELECT id, title FROM {$db->pre}packages WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows() != 1) {
		echo head();
		error('javascript: self.close();', 'Specified package ('.$id.') does not exist.');
	}
	$data = $db->fetch_assoc($result);

	$dir = "modules/{$data['id']}/";
	$ini = $myini->read($dir."config.ini");
	if (!isset($ini['language'])) {
		$ini['language'] = array();
	}
	$ini['language'][$varname] = $text;
	$myini->write($dir."config.ini", $ini);

	$c = new manageconfig();
	foreach ($lang as $id => $t) {
		if (empty($t)) {
			$t = $text;
		}
		$c->getdata("language/{$id}/modules.lng.php", 'lang');
		$c->updateconfig($varname, str, $t);
		$c->savedata();
	}

	ok('admin.php?action=cms&job=package_language&id='.$data['id']);
}
elseif ($job == 'package_language_delete') {
	echo head();

	$id = $gpc->get('id', int);
	$delete = $gpc->get('delete', arr_str);

	$result = $db->query("SELECT id, title FROM {$db->pre}packages WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows() != 1) {
		echo head();
		error('javascript: self.close();', 'Specified package ('.$id.') does not exist.');
	}
	$data = $db->fetch_assoc($result);

	$dir = "modules/{$data['id']}/";
	$ini = $myini->read($dir."config.ini");
	foreach ($delete as $phrase) {
		unset($ini['language'][$phrase]);
	}
	$myini->write($dir."config.ini", $ini);

	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	$c = new manageconfig();
	while($row = $db->fetch_assoc($result)) {
		$path = "language/{$row['id']}/modules.lng.php";
		if (file_exists($path)) {
			$c->getdata($path, 'lang');
			foreach ($delete as $phrase) {
				$c->delete($phrase);
			}
			$c->savedata();
		}
	}
	ok('admin.php?action=cms&job=package_language&id='.$data['id'], 'Selected phrases were successfully deleted.');
}
elseif ($job == 'package_language_copy') {
	echo head();

	$id = $gpc->get('id', int);
	$result = $db->query("SELECT id, title FROM {$db->pre}packages WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows() != 1) {
		echo head();
		error('javascript: self.close();', 'Specified package ('.$id.') does not exist.');
	}
	$data = $db->fetch_assoc($result);

	$file = 'modules.lng.php';
	$language = $gpc->get('language', int);
	$phrase = $gpc->get('phrase', str);
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=cms&job=package_language_copy2&phrase=<?php echo $phrase; ?>&language=<?php echo $language; ?>&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2">Phrase Manager &raquo; Copy</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Language to use as submittal:<br />
   <span class="stext">Specify from which directory/language the phrase should be copied.</span></td>
   <td class="mbox" width="50%"><select name="dir">
	<?php
	while($row = $db->fetch_assoc($result)) {
		if (file_exists('language/'.$row['id'].'/'.$file)) {
			$file = substr($file, 0, strlen($file)-8);
			$langarr = return_array($file, $row['id']);
			if (isset($langarr[$phrase])) {
	?>
   	<option value="<?php echo $row['id']; ?>"><?php echo $row['language']; ?> (ID: <?php echo $row['id']; ?>)</option>
	<?php } } } ?>
   </select></td>
  </tr>
  <tr>
   <td class="ubox" align="center" colspan="2"><input type="submit" value="Copy phrase"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'package_language_copy2') {
	echo head();

	$id = $gpc->get('id', int);
	$result = $db->query("SELECT id, title FROM {$db->pre}packages WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows() != 1) {
		echo head();
		error('javascript: self.close();', 'Specified package ('.$id.') does not exist.');
	}
	$data = $db->fetch_assoc($result);

	$dest = $gpc->get('language', int);
	$source = $gpc->get('dir', int);
	$file = 'modules.lng.php';
	$phrase = $gpc->get('phrase', str);
	$destpath = 'language/'.$dest.'/'.$file;
	$c = new manageconfig();
	if (!file_exists($destpath)) {
		createParentDir($file, 'language/'.$dest);
		$c->createfile($destpath, 'lang');
	}
	$file = substr($file, 0, strlen($file)-8);
	$langarr = return_array($file, $source);
	if (!isset($langarr[$phrase])) {
		error('admin.php?action=language&job=phrase_file&file='.$file, 'Phrase not found!');
	}
	$c->getdata($destpath, 'lang');
	$c->updateconfig($phrase, str, $langarr[$phrase]);
	$c->savedata();
	ok('admin.php?action=cms&job=package_language&id='.$id);
}
elseif ($job == 'package_language_edit') {
	echo head();

	$phrase = $gpc->get('phrase', none);
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT id, title FROM {$db->pre}packages WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows() != 1) {
		echo head();
		error('javascript: self.close();', 'Specified package ('.$id.') does not exist.');
	}
	$data = $db->fetch_assoc($result);

	$dir = "modules/{$data['id']}/";
	$ini = $myini->read($dir."config.ini");
	if (!isset($ini['language'][$phrase])) {
		error('admin.php?action=cms&job=plugins_edit&id=7', 'Phrase not found!');
	}

	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	?>
<form name="form" method="post" action="admin.php?action=cms&job=package_language_save2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpediting="4" align="center">
  <tr>
   <td class="obox" colspan="2">Phrase Manager &raquo; Edit Phrase</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Varname:<br />
   <span class="stext">Varname is a value which can only contain letters, numbers and underscores.</span></td>
   <td class="mbox" width="50%"><input type="hidden" name="varname" size="50" value="<?php echo $phrase; ?>" /><code><?php echo $phrase; ?></code></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Text:<br />
   <span class="stext">This is the default language used also in the exported packages. It is recommended to write English here.</span></td>
   <td class="mbox" width="50%"><input type="text" name="text" size="50" value="<?php echo htmlspecialchars(nl2whitespace($ini['language'][$phrase])); ?>" /></td>
  </tr>
  <tr>
   <td class="obox" colspan="2">Translations</td>
  </tr>
  <tr>
   <td class="ubox" colspan="2"><ul>
	<li>When editing a custom phrase, you may also specify the translations into whatever languages you have installed.</li>
	<li>If you do leave a translation box blank, it will inherit the text from the 'Text' box.</li>
   </ul></td>
  </tr>
  <?php
  while($row = $db->fetch_assoc($result)) {
  	$phrases = return_array('modules', $row['id']);
  	if (!isset($phrases[$phrase])) {
  		$phrases[$phrase] = '';
  	}
  ?>
  <tr>
   <td class="mbox" width="50%"><em><?php echo $row['language']; ?></em> Translation:<br /><span class="stext">Optional. HTML is allowed but not recommended.</span></td>
   <td class="mbox" width="50%"><input type="text" name="langt[<?php echo $row['id']; ?>]" size="50" value="<?php echo htmlspecialchars(nl2whitespace($phrases[$phrase])); ?>" /></td>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Save" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'package_add') {
	echo head();
	?>
	<form method="post" action="admin.php?action=cms&job=package_add2">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	 <tr>
	  <td class="obox" colspan="2">Add Package</td>
	 </tr>
	 <tr class="mbox">
	  <td>Title:<br /><span class="stext">Maximum number of characters: 200; Minimum number of characters: 4</span></td>
	  <td><input type="text" name="title" size="40" /></td>
	 </tr>
	 <tr class="mbox">
	  <td>Version:<br /><span class="stext">Optional</span></td>
	  <td><input type="text" name="version" size="40" value="1.0" /></td>
	 </tr>
	 <tr class="mbox">
	  <td>Copyright:<br /><span class="stext">Optional</span></td>
	  <td><input type="text" name="copyright" size="40" /></td>
	 </tr>
	 <tr>
	  <td class="ubox" colspan="2" align="center"><input type="submit" value="Add" /></td>
	 </tr>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'package_add2') {
	echo head();
	$title = $gpc->get('title', str);
	$version = $gpc->get('version', str);
	$copyright = $gpc->get('copyright', str);
	if (strlen($title) < 4) {
		error('admin.php?action=cms&job=package_add', 'Minimum number of characters for title: 4');
	}
	if (strlen($title) > 200) {
		error('admin.php?action=cms&job=package_add', 'Maximum number of characters for title: 200');
	}

	$db->query("INSERT INTO {$db->pre}packages (`title`) VALUES ('{$title}')");
	$packageid = $db->insert_id();

	$filesystem->mkdir("modules/{$packageid}/", 0777);

	$ini = array(
		'info' => array(
			'title' => $title,
			'version' => $version,
			'copyright' => $copyright
		),
		'php' => array()
	);
	$myini->write("modules/{$packageid}/config.ini", $ini);
	$filesystem->chmod("modules/{$packageid}/config.ini", 0666);

	ok('admin.php?action=cms&job=plugins_add&id='.$packageid, 'Package successfully added.');
}
elseif ($job == 'package_import') {
	echo head();
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=cms&job=package_import2">
 <table class="border" cellpadding="4" cellspacing="0" border="0">
  <tr><td class="obox" colspan="2">Import a new Package</td></tr>
  <tr><td class="mbox"><em>Either</em> upload a file:<br /><span class="stext">Allowed file types: .zip - Maximum file size: <?php echo formatFilesize(ini_maxupload()); ?></span></td>
  <td class="mbox"><input type="file" name="upload" size="40" /></td></tr>
  <tr><td class="mbox"><em>oder</em> select a file from the server:<br /><span class="stext">Path starting from the Viscacha-root-directory: <?php echo $config['fpath']; ?></span></td>
  <td class="mbox"><input type="text" name="server" size="50" /></td></tr>
  <tr><td class="mbox">Delete file after import:</td>
  <td class="mbox"><input type="checkbox" name="delete" value="1" checked="checked" /></td></tr>
  <tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" value="Send" /></td></tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'package_import2') {
	$dir = $gpc->get('dir', int);
	$server = $gpc->get('server', none);
	$del = $gpc->get('delete', int);
	$inserterrors = array();

	if (!empty($_FILES['upload']['name'])) {
		$filesize = ini_maxupload();
		$filetypes = array('zip');
		$dir = realpath('temp').DIRECTORY_SEPARATOR;

		$insertuploads = array();
		require("classes/class.upload.php");

		$my_uploader = new uploader();
		$my_uploader->max_filesize($filesize);
		$my_uploader->file_types($filetypes);
		$my_uploader->set_path($dir);
		if ($my_uploader->upload('upload')) {
			if ($my_uploader->save_file()) {
				$file = $dir.$my_uploader->fileinfo('filename');
				if (!file_exists($file)) {
					$inserterrors[] = 'File ('.$file.') does not exist.';
				}
			}
		}
		if ($my_uploader->upload_failed()) {
			array_push($inserterrors,$my_uploader->get_error());
		}

	}
	elseif (file_exists($server)) {
		$ext = get_extension($server);
		if ($ext == 'zip') {
			$file = $server;
		}
		else {
			$inserterrors[] = 'The selected file is no ZIP-file.';
		}
	}
	else {
		$inserterrors[] = 'No valid file selected.';
	}
	if (count($inserterrors) > 0) {
		echo head();
		error('admin.php?action=designs&job=design_import', $inserterrors);
	}
	$tempdir = 'temp/'.md5(microtime()).'/';
	if (file_exists($tempdir)) {
		$filesystem->chmod($tempdir, 0777);
	}
	else {
		$filesystem->mkdir($tempdir, 0777);
	}
	require_once('classes/class.zip.php');
	$archive = new PclZip($file);
	$failure = $archive->extract($tempdir);
	if ($failure < 1) {
		unset($archive);
		rmdirr($tempdir);
		echo head();
		error('admin.php?action=designs&job=design_import', 'ZIP-archive could not be read or the folder is empty.');
	}
	else {
		$c = new manageconfig();
		$ini = $myini->read("{$tempdir}config.ini");

		$db->query("INSERT INTO {$db->pre}packages (`title`) VALUES ('{$ini['info']['title']}')");
		$packageid = $db->insert_id();
		$dir = "modules/{$packageid}/";
		if (file_exists($dir)) {
			$filesystem->chmod($dir, 0777);
		}
		else {
			$filesystem->mkdir($dir, 0777);
		}
		if (isset($ini['template']) && count($ini['template']) > 0) {
			$desobj = $scache->load('loaddesign');
			$designs = $desobj->get(true);
			$tplid = $designs[$config['templatedir']]['template'];
			$tpldir = "templates/{$tplid}/modules/{$packageid}/";
			if (file_exists($tpldir)) {
				$filesystem->chmod($tpldir, 0777);
			}
			else {
				$filesystem->mkdir($tpldir, 0777);
			}
			$temptpldir = "{$tempdir}templates/";
			copyr($temptpldir, $tpldir);
			rmdirr($temptpldir);
		}
		copyr($tempdir, $dir);

		if (isset($ini['language']) && count($ini['language']) > 0) {
			$codes = array();
			$keys = array_keys($ini);
			foreach ($keys as $entry) {
			   	if (preg_match('~language_(\w{2})_?(\w{0,2})~i', $entry, $code)) {
			   		if (!isset($codes[$code[1]])) {
			   			$codes[$code[1]] = array();
			   		}
			   		if (isset($code[2])) {
			   			$codes[$code[1]][] = $code[2];
			   		}
			   		else {
			   			if (!in_array('', $codes[$code[1]])) {
			   				$codes[$code[1]][] = '';
			   			}
			   		}
			   	}
			}
			$langcodes = getLangCodes();
			foreach ($langcodes as $code => $lid) {
				$ldat = explode('_', $code);
				if (isset($codes[$ldat[0]])) {
					$count = count($codes[$ldat[0]]);
					if (in_array('', $codes[$ldat[0]])) {
						$count--;
					}
				}
				else {
					$count = -1;
				}
				if (isset($codes[$ldat[0]]) && !empty($ldat[1]) && in_array($ldat[1], $codes[$ldat[0]])) { // Nehme Original
					$src = 'language_'.$code;
				}
				elseif(isset($codes[$ldat[0]]) && in_array('', $codes[$ldat[0]])) { // Nehme gleichen Langcode, aber ohne Countrycode
					$src = 'language_'.$ldat[0];
				}
				elseif(isset($codes[$ldat[0]]) && $count > 0) { // Nehme gleichen Langcode, aber falchen Countrycode
					$src = 'language_'.$ldat[0].'_'.reset($codes[$ldat[0]]);
				}
				else { // Nehme Standard
					$src = 'language';
				}
				$c->getdata("language/{$lid}/modules.lng.php", 'lang');
				foreach ($ini[$src] as $varname => $text) {
					$c->updateconfig($varname, str, $text);
				}
				$c->savedata();
			}
		}

		if (isset($ini['php']) && count($ini['php']) > 0) {
			foreach ($ini['php'] as $hook => $plugfile) {
				if (isInvisibleHook($hook)) {
					continue;
				}
				$result = $db->query("SELECT MAX(ordering) AS maximum FROM {$db->pre}plugins WHERE position = '{$hook}'", __LINE__, __FILE__);
				$row = $db->fetch_assoc($result);
				$priority = $row['maximum']+1;
				$db->query("
				INSERT INTO {$db->pre}plugins
				(`name`,`module`,`ordering`,`active`,`position`)
				VALUES
				('{$ini['info']['title']}','{$packageid}','{$priority}','0','{$hook}')
				", __LINE__, __FILE__);
				$filesystem->unlink('cache/modules/'.$plugins->_group($hook).'.php');
			}
		}

		$confirm = true;
		$pluginid = $packageid;
		($code = $plugins->install($packageid)) ? eval($code) : null;

		rmdirr($tempdir);
	}
	unset($archive);
	if ($del > 0) {
		$filesystem->unlink($file);
	}
	if ($confirm) {
		echo head();
		ok('admin.php?action=cms&job=plugins&sort=1', 'Package successfully imported!');
	}
}
elseif ($job == 'package_export') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT id, title FROM {$db->pre}packages WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows() != 1) {
		echo head();
		error('javascript: history.back(-1);', 'Specified package ('.$id.') does not exist.');
	}
	$data = $db->fetch_assoc($result);
	$file = convert2adress($data['title']).'.zip';
	$ini = $myini->read("modules/{$data['id']}/config.ini");

	$templates = array();
	if (isset($ini['template']) && count($ini['template']) > 0) {
		$desobj = $scache->load('loaddesign');
		$designs = $desobj->get(true);
		foreach ($designs as $row) {
			if (!isset($templates[$row['template']])) {
				$valid = true;
				$dir = "templates/{$row['id']}/modules/{$data['id']}/";
				foreach ($ini['template'] as $tplfile) {
					if (!file_exists($dir.$tplfile)) {
						$valid = false;
					}
				}
				if ($valid == true) {
					$templates[$row['template']] = ($row['id'] == $config['templatedir']) ? true : false;
				}
			}
		}
		if (count($templates) == 0) {
			error('javascript: history.back(-1);', 'Package is corrupted: Templates are missing!');
		}
	}
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=cms&amp;job=package_export2&amp;id=<?php echo $id; ?>">
 <table class="border" cellpadding="4" cellspacing="0" border="0">
  <tr>
   <td class="obox" colspan="2">Export Package</td>
  </tr>
  <?php if (count($templates) > 0) { ?>
  <tr>
   <td class="mbox">Export Templates of Package:</td>
   <td class="mbox">
	<select name="tpl">
	<?php foreach ($templates as $id => $default) { ?>
	 <option value="<?php echo $id; ?>"<?php echo iif($default, ' selected="selected"'); ?>><?php echo $id.iif($default, ' (Default)'); ?></option>
	<?php } ?>
	</select>
   </td>
  </tr>
  <?php } ?>
  <tr>
   <td class="mbox">Filename:</td>
   <td class="mbox"><input type="text" name="file" size="50" value="<?php echo $file; ?>" /></td>
  </tr><tr>
   <td class="mbox">Delete file after export:</td>
   <td class="mbox"><input type="checkbox" name="delete" value="1" checked="checked" /></td></tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" value="Export" /></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'package_export2') {
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT id, title FROM {$db->pre}packages WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	if ($db->num_rows() != 1) {
		echo head();
		error('javascript: history.back(-1);', 'Specified package ('.$id.') does not exist.');
	}
	$data = $db->fetch_assoc($result);
	$file = $gpc->get('file', none);
	if (empty($file)) {
		$file = convert2adress($data['title']).'.zip';
	}

	$ini = $myini->read("modules/{$data['id']}/config.ini");
	if (!isset($ini['language']) || !is_array($ini['language']) || (is_array($ini['language']) && count($ini['language']) == 0)) {
		$ini['language'] = array();
	}
	$dirs = array();
	$langcodes = getLangCodes();
	foreach ($langcodes as $code => $lid) {
		$langdata = return_array('modules', $lid);
		$langdata = array_intersect_key($langdata, $ini['language']);
		if ($lid == $config['langdir']) {
			$ini['language'] = $langdata;
		}
		else {
			$ini['language_'.$code] = $langdata;
		}
	}
	$myini->write("modules/{$data['id']}/config.ini", $ini);

	$tpl = $gpc->get('tpl', int);
	$tempdir = "temp/";
	$error = false;

	require_once('classes/class.zip.php');
	$archive = new PclZip($tempdir.$file);
	$v_list = $archive->create(
		"modules/{$id}/",
		PCLZIP_OPT_REMOVE_PATH, "modules/{$id}/"
	);
	if ($v_list == 0) {
		$error = true;
	}
	else {
		if (isset($ini['template']) && count($ini['template']) > 0) {
			$archive = new PclZip($tempdir.$file);
			$v_list = $archive->add(
				"templates/{$tpl}/modules/{$id}/",
				PCLZIP_OPT_REMOVE_PATH, "templates/{$tpl}/modules/{$id}/",
				PCLZIP_OPT_ADD_PATH, "templates/"
			);
			if ($v_list == 0) {
				$error = true;
				break;
			}
		}
	}
	if ($error == true) {
		echo head();
		unset($archive);
		$filesystem->unlink($tempdir.$file);
		error('admin.php?action=cms&job=package_export&id='.$id, $archive->errorInfo(true));
	}
	else {
		viscacha_header('Content-Type: application/zip');
		viscacha_header('Content-Disposition: attachment; filename="'.$file.'"');
		viscacha_header('Content-Length: '.filesize($tempdir.$file));
		readfile($tempdir.$file);
		unset($archive);
		$filesystem->unlink($tempdir.$file);
	}
}
elseif ($job == 'package_info') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}plugins WHERE module = '{$id}'", __LINE__, __FILE__);
	$plugin = array();
	while ($row = $db->fetch_assoc()) {
		$plugin[] = $row;
	}
	$ini = $myini->read("modules/{$id}/config.ini");
	?>
	<table class="border">
	 <tr>
	  <td class="obox" colspan="2">Package</td>
	 </tr>
	 <tr class="mbox">
	  <td width="30%">Package:</td>
	  <td width="70%"><?php echo $ini['info']['title']; ?></td>
	 </tr>
	 <tr class="mbox">
	  <td width="30%">Version:</td>
	  <td width="70%"><?php echo $ini['info']['version']; ?></td>
	 </tr>
	 <tr class="mbox">
	  <td width="30%">Copyright:</td>
	  <td width="70%"><?php echo $ini['info']['copyright']; ?></td>
	 </tr>
	 <tr class="mbox">
	  <td width="30%">Templates:</td>
	  <td width="70%"><?php echo (isset($ini['template']) && count($ini['template']) > 0) ? implode('<br />', $ini['template']) : '-'; ?></td>
	 </tr>
	 <tr class="mbox">
	  <td width="30%">Phrases:</td>
	  <td width="70%"><?php echo (isset($ini['language']) && count($ini['language']) > 0) ? implode('<br />', array_keys($ini['language'])) : '-'; ?></td>
	 </tr>
	 <tr class="mbox">
	  <td width="30%">Hooks:</td>
	  <td width="70%"><?php echo (isset($ini['php']) && count($ini['php']) > 0) ? implode('<br />', array_unique(array_keys($ini['php']))) : '-'; ?></td>
	 </tr>
	</table>
	<br class="minibr" />
	<table class="border">
	 <tr>
	  <td class="obox" colspan="2">Plugins</td>
	 </tr>
	 <?php
	 foreach ($plugin as $row) {
		?>
		<tr class="ubox">
		 <td colspan="2"><b><?php echo $row['name']; ?></b></td>
		</tr>
		<tr class="mbox">
		 <td width="30%">Hook:</td>
		 <td width="70%"><?php echo $row['position']; ?></td>
		</tr>
		<tr class="mbox">
		 <td width="30%">File:</td>
		 <td width="70%"><?php echo $ini['php'][$row['position']]; ?></td>
		</tr>
		<tr class="mbox">
		 <td width="30%">Active:</td>
		 <td width="70%"><?php echo noki($row['active']); ?></td>
		</tr>
		<?php
	 }
	 if (count($plugin) == 0) {
	 	?>
		<tr class="mbox">
			<td colspan="2"><em>For this package there is no plugin specified.</em></td>
		</tr>
	 	<?php
	 }
	 ?>
	</table>
	<?php
	echo foot();
}
elseif ($job == 'package_delete') {
	echo head();
	$id = $gpc->get('id', int);
	?>
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	<tr><td class="obox">Delete Package</td></tr>
	<tr><td class="mbox">
	<p align="center">Do you really want to delete this package with all included plugins?</p>
	<p align="center">
	<a href="admin.php?action=cms&job=package_delete2&id=<?php echo $id; ?>"><img border="0" align="middle" alt="" src="admin/html/images/yes.gif"> Yes</a>
	&nbsp&nbsp;&nbsp;&nbsp&nbsp;&nbsp;
	<a href="javascript: history.back(-1);"><img border="0" align="middle" alt="" src="admin/html/images/no.gif"> No</a>
	</p>
	</td></tr>
	</table>
	<?php
	echo foot();
}
elseif ($job == 'package_delete2') {
	$id = $gpc->get('id', int);

	if (!is_id($id)) {
		echo head();
		error('javascript: history.back(-1);', 'Specified package ('.$id.') does not exist.');
	}

	$c = new manageconfig();
	$dir = "modules/{$id}/";
	$ini = $myini->read($dir."config.ini");

	$confirm = true;
	$pluginid = $id;
	($code = $plugins->uninstall($id)) ? eval($code) : null;

	$result = $db->query("SELECT * FROM {$db->pre}plugins WHERE module = '{$id}' GROUP BY position", __LINE__, __FILE__);
	while ($data = $db->fetch_assoc($result)) {
		$filesystem->unlink('cache/modules/'.$plugins->_group($data['position']).'.php');
	}
	$db->query("DELETE FROM {$db->pre}plugins WHERE module = '{$id}'", __LINE__, __FILE__);
	$db->query("DELETE FROM {$db->pre}packages WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	// Delete references in navigation aswell
	$db->query("DELETE FROM {$db->pre}menu WHERE module = '{$id}'", __LINE__, __FILE__);

	// Delete templates
	$designObj = $scache->load('loaddesign');
	$designs = $designObj->get(true);
	foreach ($designs as $row) {
		$tpldir = "templates/{$row['template']}/modules/{$id}/";
		if (file_exists($tpldir)) {
			rmdirr($tpldir);
		}
	}
	// Delete phrases
	if (isset($ini['language']) && count($ini['language']) > 0) {
		$result = $db->query('SELECT * FROM '.$db->pre.'language',__LINE__,__FILE__);
		while($row = $db->fetch_assoc($result)) {
			$path = "language/{$row['id']}/modules.lng.php";
			if (file_exists($path)) {
				$c->getdata($path, 'lang');
				foreach ($ini['language'] as $phrase => $value) {
					$c->delete($phrase);
				}
				$c->savedata();
			}
		}
	}
	// Delete modules
	if (file_exists($dir)) {
		rmdirr($dir);
	}

	if ($confirm) {
		echo head();
		ok('admin.php?action=cms&job=plugins&sort=1', 'Package successfully deleted!');
	}
}
elseif ($job == 'nav') {
	send_nocache_header();
	echo head();
?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="4">
   	<span style="float: right;">
   	<a class="button" href="admin.php?action=cms&job=nav_add">Add Link</a>
   	<a class="button" href="admin.php?action=cms&job=nav_addbox">Add Box</a>
   	<a class="button" href="admin.php?action=cms&job=nav_addplugin">Add PlugIn</a>
   	</span>Manage Navigation
   </td>
  </tr>
  <tr>
   <td class="ubox">Link</td>
   <td class="ubox">Status</td>
   <td class="ubox">Order</td>
   <td class="ubox">Action</td>
  </tr>
<?php
	$result = $db->query("SELECT * FROM {$db->pre}menu ORDER BY ordering, id", __LINE__, __FILE__);
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

	foreach ($cat as $head) {
		$type = array();
		if ($head['module'] > 0) {
			$type[] = '<em>PlugIn</em>';
		}
		if ($head['active'] == 0) {
			$type[] = '<em>Inactive</em>';
		}
	?>
	<tr class="mmbox">
	<td width="50%">
	<?php echo $head['name']; ?><?php echo iif(count($type) > 0, ' ('.implode('; ', $type).')' ); ?>
	</td>
	<td width="10%">
	<?php
	if ($head['active'] == 1) {
		echo '<a href="admin.php?action=cms&job=nav_active&id='.$head['id'].iif($head['module'] > 0, '&plug='.$head['module']).'&act=0">Deactivate</a>';
	}
	else {
		echo '<a href="admin.php?action=cms&job=nav_active&id='.$head['id'].iif($head['module'] > 0, '&plug='.$head['module']).'&act=1">Activate</a>';
	}
	?>
	</td>
	<td width="15%"><?php echo $head['ordering']; ?>&nbsp;&nbsp;
	<a href="admin.php?action=cms&job=nav_move&id=<?php echo $head['id']; ?>&value=-1"><img src="admin/html/images/asc.gif" border="0" alt="Up"></a>&nbsp;
	<a href="admin.php?action=cms&job=nav_move&id=<?php echo $head['id']; ?>&value=1"><img src="admin/html/images/desc.gif" border="0" alt="Down"></a>
	</td>
	<td width="35%">
	 <a class="button" href="admin.php?action=cms&job=nav_edit&id=<?php echo $head['id']; ?>">Edit</a>
	 <a class="button" href="admin.php?action=cms&job=nav_delete&id=<?php echo $head['id']; ?>">Delete</a>
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
				echo $link['name'];
			}
			else {
				?>
				<a href="<?php echo $link['link']; ?>" target="<?php echo $link['param']; ?>"><?php echo $link['name']; ?></a>
				<?php } echo iif ($link['active'] == '0', ' (<em>Inactive</em>)'); ?><br />
				</td>
				<td class="mbox" width="10%">
				<?php
				if ($link['active'] == 1) {
					echo '<a href="admin.php?action=cms&job=nav_active&id='.$link['id'].'&act=0">Deactivate</a>';
				}
				else {
					echo '<a href="admin.php?action=cms&job=nav_active&id='.$link['id'].'&act=1">Activate</a>';
				}
				?>
				</td>
				<td class="mbox" width="15%" nowrap="nowrap" align="center"><?php echo $link['ordering']; ?>&nbsp;&nbsp;
				<a href="admin.php?action=cms&job=nav_move&id=<?php echo $link['id']; ?>&value=-1"><img src="admin/html/images/asc.gif" border="0" alt="Up"></a>&nbsp;
				<a href="admin.php?action=cms&job=nav_move&id=<?php echo $link['id']; ?>&value=1"><img src="admin/html/images/desc.gif" border="0" alt="Down"></a>
				</font></td>
				<td class="mbox" width="25%">
				 <a class="button" href="admin.php?action=cms&job=nav_edit&id=<?php echo $link['id'].SID2URL_x; ?>">Edit</a>
				 <a class="button" href="admin.php?action=cms&job=nav_delete&id=<?php echo $link['id'].SID2URL_x; ?>">Delete</a>
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
							echo $sublink['name'];
						}
						else {
							?>
							<a href='<?php echo $sublink['link']; ?>' target='<?php echo $sublink['param']; ?>'><?php echo $sublink['name']; ?></a>
							<?php } echo iif ($sublink['active'] == '0', ' (<i>Inactive</i>)'); ?></font><br>
							</td>
							<td class="mbox" width="10%">
							<?php
							if ($sublink['active'] == 1) {
								echo '<a href="admin.php?action=cms&job=nav_active&id='.$sublink['id'].'&act=0">Deactivate</a>';
							}
							else {
								echo '<a href="admin.php?action=cms&job=nav_active&id='.$sublink['id'].'&act=1">Activate</a>';
							}
							?>
							</td>
							<td class="mbox" width="15%" nowrap="nowrap" align="right"><?php echo $sublink['ordering']; ?>&nbsp;&nbsp;
							<a href="admin.php?action=cms&job=nav_move&id=<?php echo $sublink['id']; ?>&value=-1"><img src="admin/html/images/asc.gif" border="0" alt="Up"></a>&nbsp;
							<a href="admin.php?action=cms&job=nav_move&id=<?php echo $sublink['id']; ?>&value=1"><img src="admin/html/images/desc.gif" border="0" alt="Down"></a>
							</td>
							<td class="mbox" width="25%">
							 <a class="button" href="admin.php?action=cms&job=nav_edit&id=<?php echo $sublink['id']; ?>">Edit</a>
							 <a class="button" href="admin.php?action=cms&job=nav_delete&id=<?php echo $sublink['id']; ?>">Delete</a>
							</td>
							</tr>
							<?php
						}
					}
			}
		}
	}
	?></table><?php
	echo foot();
}
elseif ($job == 'nav_edit') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}menu WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	$data = $db->fetch_assoc($result);
	$data['group_array'] = explode(',', $data['groups']);

	$groups = $db->query("SELECT id, name FROM {$db->pre}groups", __LINE__, __FILE__);

	if ($data['sub'] > 0) {
		$result = $db->query("SELECT id, name, sub FROM {$db->pre}menu WHERE module = '0' ORDER BY ordering, id", __LINE__, __FILE__);
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
	?>
<form name="form" method="post" action="admin.php?action=cms&job=nav_edit2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2">Edit <?php echo iif ($data['sub'] > 0, 'link', 'box'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Title:</td>
   <td class="mbox" width="50%"><input type="text" name="title" size="40" value="<?php echo $data['name']; ?>" /></td>
  </tr>
<?php if ($data['sub'] > 0) { ?>
  <tr>
   <td class="mbox" width="50%">File/URL:<br />
   <span class="stext">
   - <a href="javascript:docs();">Existing Documents</a><br />
   - <a href="javascript:coms();">Existing Components</a>
   </span></td>
   <td class="mbox" width="50%"><input type="text" name="url" size="40" value="<?php echo $data['link']; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Target:<br /><span class="stext">All links will be opened in the same window by default. This option defines the target window for the link. For example: "_blank" will open links in a new window.</span></td>
   <td class="mbox" width="50%"><input type="text" name="target" size="40" value="<?php echo $data['param']; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Parent Box/Link:</td>
   <td class="mbox" width="50%">
   <select name="sub">
   <?php foreach ($cache[0] as $row) { ?>
   <option style="font-weight: bold;" value="<?php echo $row['id']; ?>"<?php echo iif($row['id'] == $data['sub'], ' selected="selected"'); ?>><?php echo $row['name']; ?></option>
   <?php
   if (isset($cache[$row['id']])) {
   foreach ($cache[$row['id']] as $row) {
   ?>
   <option value="<?php echo $row['id']; ?>"<?php echo iif($row['id'] == $data['sub'], ' selected="selected"'); ?>>+&nbsp;<?php echo $row['name']; ?></option>
   <?php }}} ?>
   </select>
   </td>
  </tr>
<?php } if ($data['module'] > 0) { ?>
  <tr>
   <td class="mbox" width="50%">PlugIn:</td>
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
   <td class="mbox" width="50%">Groups:<br /><span class="stext">Groups which have the ability to view the box.</span></td>
   <td class="mbox" width="50%">
   <?php while ($row = $db->fetch_assoc($groups)) { ?>
	<input type="checkbox" name="groups[]"<?php echo iif($data['groups'] == 0 || in_array($row['id'], $data['group_array']), ' checked="checked"'); ?> value="<?php echo $row['id']; ?>"> <?php echo $row['name']; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Active:</td>
   <td class="mbox" width="50%"><input type="checkbox" name="active" value="1"<?php echo iif($data['active'] == 1, ' checked="checked"'); ?> /></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" value="Save" /></td>
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
		error('admin.php?action=cms&job=nav_addbox', 'Sie haben keinen Titel angegeben.');
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
		$db->query("UPDATE {$db->pre}menu SET name = '{$title}', link = '{$url}', param = '{$target}', groups = '{$groups}', sub = '{$sub}', active = '{$active}' WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	}
	else {
		if ($data['module'] > 0) {
			$plug = $gpc->get('plugin', int);
			$result = $db->query("SELECT position FROM {$db->pre}plugins WHERE id = '{$plug}'", __LINE__, __FILE__);
			if ($db->num_rows($result) > 0) {
				$module_sql = ", module = '{$plug}'";
				$row = $db->fetch_assoc($result);
				$filesystem->unlink('cache/modules/'.$plugins->_group($row['position']).'.php');
				$db->query("UPDATE {$db->pre}plugins SET active = '{$active}' WHERE id = '{$plug}' LIMIT 1", __LINE__, __FILE__);
			}
			$db->query("UPDATE {$db->pre}menu SET name = '{$title}', groups = '{$groups}', active = '{$active}'{$module_sql} WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
		}
		else {
			$db->query("UPDATE {$db->pre}menu SET name = '{$title}', groups = '{$groups}', active = '{$active}' WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
		}
	}
	$delobj = $scache->load('modules_navigation');
	$delobj->delete();
	ok('admin.php?action=cms&job=nav', 'Data successfully changed!');
}
elseif ($job == 'nav_delete') {
	echo head();
	$id = $gpc->get('id', int);
?>
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	<tr><td class="obox">Delete Component</td></tr>
	<tr><td class="mbox">
	<p align="center">Do you really want to delete this box or link (to a plugin) including all child-links?</p>
	<p align="center">
	<a href="admin.php?action=cms&job=nav_delete2&id=<?php echo $id; ?>"><img border="0" align="middle" alt="" src="admin/html/images/yes.gif"> Yes</a>
	&nbsp&nbsp;&nbsp;&nbsp&nbsp;&nbsp;
	<a href="javascript: history.back(-1);"><img border="0" align="middle" alt="" src="admin/html/images/no.gif"> No</a>
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

	ok('admin.php?action=cms&job=nav', $anz.' entries deleted.');
}
elseif ($job == 'nav_move') {
	$id = $gpc->get('id', int);
	$pos = $gpc->get('value', int);
	if ($id < 1) {
		error('admin.php?action=cms&job=nav', 'Invalid ID given');
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
		error('admin.php?action=cms&job=nav', 'Invalid ID given');
	}
	if ($pos != 0 && $pos != 1) {
		error('admin.php?action=cms&job=nav', 'Invalid status specified');
	}
	$db->query('UPDATE '.$db->pre.'menu SET active = "'.$pos.'" WHERE id = '.$id, __LINE__, __FILE__);

	$plug = $gpc->get('plug', int);
	if ($plug > 0) {
		$result = $db->query("SELECT position FROM {$db->pre}plugins WHERE id = '{$plug}'", __LINE__, __FILE__);
		if ($db->num_rows($result) > 0) {
			$module_sql = ", module = '{$plug}'";
			$row = $db->fetch_assoc($result);
			$filesystem->unlink('cache/modules/'.$plugins->_group($row['position']).'.php');
			$db->query("UPDATE {$db->pre}plugins SET active = '{$pos}' WHERE id = '{$plug}' LIMIT 1", __LINE__, __FILE__);
		}
	}

	$delobj = $scache->load('modules_navigation');
	$delobj->delete();
	viscacha_header('Location: admin.php?action=cms&job=nav');
}
elseif ($job == 'nav_addplugin') {
	echo head();
	$id = $gpc->get('id', int);
	$sort = $db->query("SELECT ordering, name FROM {$db->pre}menu WHERE sub = '0' ORDER BY ordering, id", __LINE__, __FILE__);
	$plugs = $db->query("SELECT id, name FROM {$db->pre}plugins WHERE position = 'navigation' ORDER BY ordering", __LINE__, __FILE__);
	$groups = $db->query("SELECT id, name FROM {$db->pre}groups", __LINE__, __FILE__);
	?>
<form name="form" method="post" action="admin.php?action=cms&job=nav_addplugin2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2">Add Plugin to navigation</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Title:<br /><span class="stext">Leave empty to use default.</span></td>
   <td class="mbox" width="50%"><input type="text" name="title" size="40" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">PlugIn:</td>
   <td class="mbox" width="50%">
   <select name="plugin">
   <?php while ($row = $db->fetch_assoc($plugs)) { ?>
   <option value="<?php echo $row['id']; ?>"<?php echo iif($row['id'] == $id, ' selected="selected"'); ?>><?php echo $row['name']; ?></option>
   <?php } ?>
   </select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Sort in after:</td>
   <td class="mbox" width="50%">
   <select name="sort">
   <?php while ($row = $db->fetch_assoc($sort)) { ?>
	<option value="<?php echo $row['ordering']; ?>"><?php echo $row['name']; ?></option>
   <?php } ?>
   </select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Groups:<br /><span class="stext">Groups which have the ability to view the PlugIn.</span></td>
   <td class="mbox" width="50%">
   <?php while ($row = $db->fetch_assoc($groups)) { ?>
	<input type="checkbox" name="groups[]" checked="checked" value="<?php echo $row['id']; ?>"> <?php echo $row['name']; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" value="Add" /></td>
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
	$sort = $gpc->get('sort', int);
	$groups = $gpc->get('groups', arr_int);
	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'groups', __LINE__, __FILE__);
	$count = $db->fetch_num($result);
	if (count($groups) == $count[0]) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}
	$db->query("INSERT INTO {$db->pre}menu (name, groups, ordering, active, module) VALUES ('{$title}','{$groups}','{$sort}','{$data['active']}','{$data['id']}')", __LINE__, __FILE__);
	$delobj = $scache->load('modules_navigation');
	$delobj->delete();
	ok('admin.php?action=cms&job=nav', 'PlugIn successful added');
}
elseif ($job == 'nav_add') {
	echo head();
	$groups = $db->query("SELECT id, name FROM {$db->pre}groups", __LINE__, __FILE__);
	$result = $db->query("SELECT id, name, sub FROM {$db->pre}menu WHERE module = '0' ORDER BY ordering, id", __LINE__, __FILE__);
	$cache = array(0 => array());
	while ($row = $db->fetch_assoc($result)) {
		if (!isset($cache[$row['sub']]) || !is_array($cache[$row['sub']])) {
			$cache[$row['sub']] = array();
		}
		$cache[$row['sub']][] = $row;
	}
	?>
<form name="form" method="post" action="admin.php?action=cms&job=nav_add2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2">Add a new link</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Title:</td>
   <td class="mbox" width="50%"><input type="text" name="title" size="40" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">File/URL:<br />
   <span class="stext">
   - <a href="javascript:docs();">Existing Documents</a><br />
   - <a href="javascript:coms();">Existing Components</a>
   </span></td>
   <td class="mbox" width="50%"><input type="text" name="url" size="40" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Target:<br /><span class="stext">All links will be opened in the same window by default. This option defines the target window for the link. For example: "_blank" will open links in a new window.</span></td>
   <td class="mbox" width="50%"><input type="text" name="target" size="40" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Parent Box/Link:</td>
   <td class="mbox" width="50%">
   <select name="sub">
   <?php foreach ($cache[0] as $row) { ?>
   <option style="font-weight: bold;" value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
   <?php
   if (isset($cache[$row['id']])) {
   foreach ($cache[$row['id']] as $row) {
   ?>
   <option value="<?php echo $row['id']; ?>">+&nbsp;<?php echo $row['name']; ?></option>
   <?php }}} ?>
   </select>
   </td>
  </tr>
  </tr>
  <tr>
   <td class="mbox" width="50%">Sort in:</td>
   <td class="mbox" width="50%">
   <select name="sort">
	<option value="0">at the Beginning</option>
	<option value="1">at the End</option>
   </select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Groups:<br /><span class="stext">Groups which have the ability to view the box.</span></td>
   <td class="mbox" width="50%">
   <?php while ($row = $db->fetch_assoc($groups)) { ?>
	<input type="checkbox" name="groups[]" checked="checked" value="<?php echo $row['id']; ?>"> <?php echo $row['name']; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" value="Add" /></td>
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
		error('admin.php?action=cms&job=nav_addbox', 'Sie haben keinen Titel angegeben.');
	}
	if ($sort == 1) {
		$sortx = $db->fetch_num($db->query("SELECT MAX(ordering) FROM {$db->pre}menu WHERE sub = '{$sub}' LIMIT 1", __LINE__, __FILE__));
		$sort = $sortx[0]+1;
	}
	elseif ($sort == 0) {
		$sortx = $db->fetch_num($db->query("SELECT MIN(ordering) FROM {$db->pre}menu WHERE sub = '{$sub}' LIMIT 1", __LINE__, __FILE__));
		$sort = $sortx[0]-1;
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
	$db->query("INSERT INTO {$db->pre}menu (name, groups, ordering, link, param, sub) VALUES ('{$title}','{$groups}','{$sort}','{$url}','{$target}','{$sub}')", __LINE__, __FILE__);
	$delobj = $scache->load('modules_navigation');
	$delobj->delete();
	ok('admin.php?action=cms&job=nav', 'Link successfully added.');
}
elseif ($job == 'nav_addbox') {
	echo head();
	$sort = $db->query("SELECT ordering, name FROM {$db->pre}menu WHERE sub = '0' ORDER BY ordering, id", __LINE__, __FILE__);
	$groups = $db->query("SELECT id, name FROM {$db->pre}groups", __LINE__, __FILE__);
	?>
<form name="form" method="post" action="admin.php?action=cms&job=nav_addbox2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2">Create a new box</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Title:</td>
   <td class="mbox" width="50%"><input type="text" name="title" size="40" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Sort in after:</td>
   <td class="mbox" width="50%">
   <select name="sort">
   <?php while ($row = $db->fetch_assoc($sort)) { ?>
	<option value="<?php echo $row['ordering']; ?>"><?php echo $row['name']; ?></option>
   <?php } ?>
   </select>
   </td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Groups:<br /><span class="stext">Groups which have the ability to view the box.</span></td>
   <td class="mbox" width="50%">
   <?php while ($row = $db->fetch_assoc($groups)) { ?>
	<input type="checkbox" name="groups[]" checked="checked" value="<?php echo $row['id']; ?>"> <?php echo $row['name']; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" value="Add" /></td>
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
		error('admin.php?action=cms&job=nav_addbox', 'Sie haben keinen Titel angegeben.');
	}
	$sort = $gpc->get('sort', int);
	$groups = $gpc->get('groups', arr_int);
	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'groups', __LINE__, __FILE__);
	$count = $db->fetch_num($result);
	if (count($groups) == $count[0]) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}
	$db->query("INSERT INTO {$db->pre}menu (name, groups, ordering) VALUES ('{$title}','{$groups}','{$sort}')", __LINE__, __FILE__);
	$delobj = $scache->load('modules_navigation');
	$delobj->delete();
	ok('admin.php?action=cms&job=nav', 'Box successfully added');
}
elseif ($job == 'nav_docslist') {
	echo head();
	$result = $db->query('SELECT id, title FROM '.$db->pre.'documents');
	?>
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox">Existing Documents and Pages</td>
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
	$result = $db->query("SELECT * FROM {$db->pre}component ORDER BY active", __LINE__, __FILE__);
	?>
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox">Existing Components</td>
	  </tr>
	  <tr>
	   <td class="mbox">
	   <?php
		while ($row = $db->fetch_assoc($result)) {
			$head = array();
			$cfg = $myini->read('components/'.$row['id'].'/components.ini');
			$head = array_merge($row, $cfg);
	   ?>
	   <input type="radio" name="data" onclick="insert_doc('components.php?cid=<?php echo $row['id']; ?>','<?php echo htmlentities($head['config']['name']); ?>')"> <?php echo  $head['config']['name'].iif ($head['active'] == '0', ' (<em>Inactive</em>)'); ?><br />
	   <?php } ?>
	   </td>
	 </table>
	<?php
	echo foot();
}
elseif ($job == 'com') {
	send_nocache_header();
	echo head();
?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="5"><span style="float: right;"><a class="button" href="admin.php?action=cms&job=com_add">Upload new Component</a></span>Manage Components</td>
  </tr>
  <tr>
   <td class="ubox">Name</b></td>
   <td class="ubox">Status</b></td>
   <td class="ubox">Version</b></td>
   <td class="ubox">Action</b></td>
  </tr>
<?php
	$result = $db->query("SELECT * FROM {$db->pre}component ORDER BY active DESC", __LINE__, __FILE__);
	while ($row = $db->fetch_assoc($result)) {
		$head = array();
		$cfg = $myini->read('components/'.$row['id'].'/components.ini');
		$head = array_merge($row, $cfg);
	?>
	<tr>
	<td class="mbox" width="40%">
	<?php echo $head['config']['name']; ?><?php echo iif ($head['active'] == '0', ' (<i>Inactive</i>)'); ?>
	</td>
	<td class="mbox" width="15%">
	<?php
	if ($head['active'] == 1) {
		echo '<a href="admin.php?action=cms&job=com_active&id='.$head['id'].'&value=0">Deactivate</a>';
	} else {
		echo '<a href="admin.php?action=cms&job=com_active&id='.$head['id'].'&value=1">Activate</a>';
	}
	?>
	</td>
	<td class="mbox" width="15%">
	<?php echo $head['config']['version']; ?><br />
	</td>
	<td class="mbox" width="30%">
	<form name="" action="admin.php?action=locate" method="post">
		<select size="1" name="url" onchange="locate(this.value)">
			<option value="" selected="selected">Please choose</option>
			<option value="admin.php?action=cms&job=com_info&id=<?php echo $head['id']; ?>">Information</option>
			<option value="admin.php?action=cms&job=com_admin&cid=<?php echo $head['id']; ?>">Administration</option>
			<?php if (!empty($cfg['config']['readme'])) { ?>
			<option value="admin.php?action=cms&job=com_readme&cid=<?php echo $head['id']; ?>">Readme</option>
			<?php } ?>
			<option value="admin.php?action=cms&job=com_export&id=<?php echo $head['id']; ?>">Export</option>
			<option value="admin.php?action=cms&job=com_delete&id=<?php echo $head['id']; ?>">Delete</option>
		</select>
		<input type="submit" value="Go">
	</form>
	</td>
	</tr>
	<?php
}
?>
 </table>
<?php
	echo foot();
}
elseif ($job == 'com_readme') {
	$id = $gpc->get('cid', int);
	$result = $db->query("SELECT * FROM {$db->pre}component WHERE id = {$id} LIMIT 1", __LINE__, __FILE__);
	$row = $db->fetch_assoc($result);
	$cfg = $myini->read('components/'.$row['id'].'/components.ini');
	$cfg = array_merge($row, $cfg);
	$uri = explode('?', $cfg['config']['readme']);
	$file = basename($uri[0]);
	if (isset($uri[1])) {
		parse_str($uri[1], $input);
	}
	else {
		$input = array();
	}
	if (!empty($cfg['config']['readme'])) {
		include("components/{$cfg['id']}/{$file}");
	}
	else {
		error('admin.php?action=cms&job=com', 'Readme not found!');
	}
}
elseif ($job == 'com_admin') {
	$id = $gpc->get('cid', int);
	$mod = $gpc->get('file', str, 'frontpage');
	$result = $db->query("SELECT * FROM {$db->pre}component WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	$row = $db->fetch_assoc($result);
	$cfg = $myini->read('components/'.$row['id'].'/components.ini');
	$cfg = array_merge($row, $cfg);
	if (!isset($cfg['admin'][$mod])) {
		echo head();
		error('admin.php?action=cms&job=com','Section not found!');
	}

	DEFINE('COM_ID', $id);
	DEFINE('COM_DIR', 'components/'.COM_ID.'/');
	if (!isset($cfg['admin'][$mod])) {
		DEFINE('COM_MODULE', 'frontpage');
	}
	else {
		DEFINE('COM_MODULE', $mod);
	}
	DEFINE('COM_MODULE_FILE', $cfg['admin'][COM_MODULE]);
	DEFINE('COM_FILE', $cfg['admin']['frontpage']);

	$uri = explode('?', $cfg['admin'][$mod]);
	$file = basename($uri[0]);
	if (isset($uri[1])) {
		parse_str($uri[1], $input);
	}
	else {
		$input = array();
	}
	include("components/{$cfg['id']}/{$file}");
}
elseif ($job == 'com_info') {
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}component WHERE id = {$id} LIMIT 1", __LINE__, __FILE__);
	$row = $db->fetch_assoc($result);
	$cfg = $myini->read('components/'.$row['id'].'/components.ini');
	$cfg = array_merge($row, $cfg);

	echo head();
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2">Information</b></td>
  </tr>
	<?php
	foreach ($cfg as $key => $row) {
		if (is_array($row)) {
		?>
		  <tr>
		   <td class="ubox" colspan="2"><?php echo $key; ?></td>
		  </tr>
		<?php
			foreach ($row as $subkey => $subrow) {
			?>
			  <tr>
			   <td class="mbox" width="25%"><?php echo ucfirst($subkey); ?></td>
			   <td class="mbox" width="75%"><?php echo $subrow; ?></td>
			  </tr>
			<?php
			}
		}
		else {
		?>
		  <tr>
		   <td class="mbox" width="25%"><?php echo ucfirst($key); ?></td>
		   <td class="mbox" width="75%"><?php echo $row; ?></td>
		  </tr>
		<?php
		}
	}
	?>
	</table>
	<?php
	echo foot();
}
elseif ($job == 'com_active') {
	$id = $gpc->get('id', int);
	$act = $gpc->get('value', int);
	if (!is_id($id)) {
		error('admin.php?action=cms&job=com'.SID2URL_x, 'Invalid ID given');
	}
	if ($act != 0 && $act != 1) {
		error('admin.php?action=cms&job=com'.SID2URL_x, 'Ungültigen Status angegeben');
	}
	$delobj = $scache->load('components');
	$delobj->delete();
	$db->query('UPDATE '.$db->pre.'component SET active = "'.$act.'" WHERE id = '.$id, __LINE__, __FILE__);
	viscacha_header('Location: admin.php?action=cms&job=com');
}
elseif ($job == 'com_add') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=cms&job=com_add2" enctype="multipart/form-data">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2">Import a new Component</td>
  </tr>
  <tr>
   <td class="mbox" width="50%"><em>Either</em> upload a file:<br /><span class="stext">Compressed file (.zip) containing the component. Maximum file size: <?php echo formatFilesize(ini_maxupload()); ?>. You should install only components from confidential sources!</td>
   <td class="mbox" width="50%"><input type="file" name="upload" size="40" /></td>
  </tr>
  <tr>
   <td class="mbox"><em>or</em> select a file from the server:<br /><span class="stext">Path starting from the Viscacha-root-directory: <?php echo $config['fpath']; ?></span></td>
   <td class="mbox"><input type="text" name="server" size="50" /></td>
  </tr>
  <tr>
   <td class="mbox">Delete file after import:</td>
   <td class="mbox"><input type="checkbox" name="delete" value="1" checked="checked" /></td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Upload"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'com_add2') {
	echo head();

	$del = $gpc->get('delete', int);
	$server = $gpc->get('server', none);
	$inserterrors = array();

	$sourcefile = '';
	if (!empty($_FILES['upload']['name'])) {
		require("classes/class.upload.php");
		$dir = 'temp/';
		$my_uploader = new uploader();
		$my_uploader->file_types(array('zip'));
		$my_uploader->set_path($dir);
		$my_uploader->max_filesize(ini_maxupload());
		if ($my_uploader->upload('upload')) {
			if ($my_uploader->save_file()) {
				$sourcefile = $dir.$my_uploader->fileinfo('filename');
			}
		}
		if ($my_uploader->upload_failed()) {
			array_push($inserterrors,$my_uploader->get_error());
		}
	}
	elseif (file_exists($server)) {
		$ext = get_extension($server);
		if ($ext == 'zip') {
			$sourcefile = $server;
		}
		else {
			$inserterrors[] = 'The selected file is no ZIP-file.';
		}
	}
	if (!file_exists($sourcefile)) {
		$inserterrors[] = 'No valid file selected.';
	}
	if (count($inserterrors) > 0) {
		error('admin.php?action=designs&job=design_import', $inserterrors);
	}
	else {
		$tdir = "temp/".md5(microtime()).'/';
		$filesystem->mkdir($tdir);
		if (!is_dir($tdir)) {
			error('admin.php?action=cms&job=com_add', 'Directory could not be created for extraction.');
		}
		include('classes/class.zip.php');
		$archive = new PclZip($sourcefile);
		if ($archive->extract(PCLZIP_OPT_PATH, $tdir) == 0) {
			error('admin.php?action=cms&job=com_add', $archive->errorInfo(true));
		}

		if (file_exists($tdir.'components.ini')) {
			$cfg = $myini->read($tdir.'components.ini');
		}
		else {
			error('admin.php?action=cms&job=com_add', 'components.ini file does not exist!');
		}

		if (!isset($cfg['module']['frontpage'])) {
			$cfg['module']['frontpage'] = '';
		}

		$db->query("INSERT INTO {$db->pre}component (file) VALUES ('{$cfg['module']['frontpage']}')", __LINE__, __FILE__);
		$id = $db->insert_id();

		$result = $db->query("SELECT template, stylesheet, images FROM {$db->pre}designs WHERE id = '{$config['templatedir']}'",__LINE__,__FILE__);
		$design = $db->fetch_assoc($result);

		$result = $db->query("SELECT stylesheet FROM {$db->pre}designs GROUP BY stylesheet",__LINE__,__FILE__);

		if (isset($cfg['php']) && count($cfg['php']) > 0) {
			$filesystem->mkdir("./components/{$id}");
			if (!in_array($cfg['config']['install'], $cfg['php'])) {
				$cfg['php'][] = $cfg['config']['install'];
			}
			if (!in_array($cfg['config']['uninstall'], $cfg['php'])) {
				$cfg['php'][] = $cfg['config']['uninstall'];
			}
			foreach ($cfg['php'] as $file) {
				$filesystem->copy("{$tdir}php/{$file}", "./components/{$id}/{$file}");
			}
		}
		if (isset($cfg['language']) && count($cfg['language']) > 0) {
			$d = dir($tdir);
			$codes = array();
			while (false !== ($entry = $d->read())) {
			   	if (preg_match('~language_(\w{2})_?(\w{0,2})~i', $entry, $code) && is_dir("{$tdir}/{$entry}")) {
			   		if (!isset($codes[$code[1]])) {
			   			$codes[$code[1]] = array();
			   		}
			   		if (isset($code[2])) {
			   			$codes[$code[1]][] = $code[2];
			   		}
			   		else {
			   			if (!in_array('', $codes[$code[1]])) {
			   				$codes[$code[1]][] = '';
			   			}
			   		}
			   	}
			}
			$d->close();
			$langcodes = getLangCodes();
			foreach ($langcodes as $code => $lid) {
				$ldat = explode('_', $code);
				if (isset($codes[$ldat[0]])) {
					$count = count($codes[$ldat[0]]);
					if (in_array('', $codes[$ldat[0]])) {
						$count--;
					}
				}
				else {
					$count = -1;
				}
				$filesystem->mkdir("./language/{$lid}/components/{$id}", 0777);
				if (isset($codes[$ldat[0]]) && !empty($ldat[1]) && in_array($ldat[1], $codes[$ldat[0]])) { // Nehme Original
					$src = 'language_'.$code;
				}
				elseif(isset($codes[$ldat[0]]) && in_array('', $codes[$ldat[0]])) { // Nehme gleichen Langcode, aber ohne Countrycode
					$src = 'language_'.$ldat[0];
				}
				elseif(isset($codes[$ldat[0]]) && $count > 0) { // Nehme gleichen Langcode, aber falchen Countrycode
					$src = 'language_'.$ldat[0].'_'.reset($codes[$ldat[0]]);
				}
				else { // Nehme Standard
					$src = 'language';
				}
				foreach ($cfg['language'] as $file) {
					$filesystem->copy("{$tdir}/{$src}/{$file}", "./language/{$lid}/components/{$id}/{$file}");
					$filesystem->chmod("./language/{$lid}/components/{$id}/{$file}", 0666);
				}
			}
		}

		if (isset($cfg['template']) && count($cfg['template']) > 0) {
			$filesystem->mkdir("./templates/{$design['template']}/components/{$id}", 0777);
			foreach ($cfg['template'] as $file) {
				$filesystem->copy("{$tdir}template/{$file}", "./templates/{$design['template']}/components/{$id}/{$file}");
				$filesystem->chmod("./templates/{$design['template']}/components/{$id}/{$file}", 0666);
			}
		}

		if (isset($cfg['image']) && count($cfg['image']) > 0) {
			foreach ($cfg['image'] as $file) {
				$filesystem->copy("{$tdir}image/{$file}", "./images/{$design['images']}/{$file}");
			}
		}

		if (isset($cfg['style']) && count($cfg['style']) > 0) {
			while ($css = $db->fetch_assoc($result)) {
				foreach ($cfg['style'] as $file) {
					$filesystem->copy("{$tdir}style/{$file}", "./designs/{$css['stylesheet']}/{$file}");
				}
			}
		}

		$filesystem->copy("{$tdir}components.ini","./components/{$id}/components.ini");
		$filesystem->chmod("./components/{$id}/components.ini", 0666);

		$delobj = $scache->load('components');
		$delobj->delete();

		rmdirr($tdir);
		unset($archive);
		if ($del > 0) {
			$filesystem->unlink($sourcefile);
		}

		if (empty($cfg['config']['install'])) {
			ok('admin.php?action=cms&job=com', 'Component successfully imported!');
		}
		else {
			$mod = $gpc->get('file', none, $cfg['config']['install']);
			$uri = explode('?', $mod);
			$file = basename($uri[0]);
			if (isset($uri[1])) {
				parse_str($uri[1], $input);
			}
			else {
				$input = array();
			}
			$path = "components/{$id}/{$file}";
			if (!file_exists($path)) {
				error('admin.php?action=cms&job=cms_add', 'Installation file not found.');
			}
			else {
				include($path);
			}
		}
	}
}
elseif ($job == 'com_export') {
	$id = $gpc->get('id', int);
	$tempdir = 'temp/';

	$result = $db->query("SELECT * FROM {$db->pre}component WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);
	$row = $db->fetch_assoc($result);
	$ini = $myini->read('components/'.$row['id'].'/components.ini');
	$info = array_merge($row, $ini);

	$result = $db->query("SELECT * FROM {$db->pre}designs WHERE id = '{$config['templatedir']}' LIMIT 1", __LINE__, __FILE__);
	$design = $db->fetch_assoc($result);

	$file = convert2adress($info['config']['name']).'.zip';
	$dirs = array(
		'template' => "templates/{$design['template']}/components/{$id}/",
		'image' => "images/{$design['images']}/",
		'style' => "designs/{$design['stylesheet']}/",
		'php' => "components/{$id}/"
	);
	$langcodes = getLangCodes();
	foreach ($langcodes as $code => $lid) {
		if ($lid == $config['langdir']) {
			$dirs['language'] = "language/{$lid}/components/{$id}/";
		}
		else {
			$dirs['language_'.$code] = "language/{$lid}/components/{$id}/";
		}
	}

	$error = false;
	$settings = $dirs['php']."components.ini";

	require_once('classes/class.zip.php');
	$archive = new PclZip($tempdir.$file);
	$v_list = $archive->create($settings, PCLZIP_OPT_REMOVE_PATH, $dirs['php']);
	if ($v_list == 0) {
		$error = true;
	}
	else {
		foreach ($dirs as $key => $dir) {
			$filelist = array();
			$newdir = $key;
			if (strpos($key, 'language_') !== false) {
				$key = 'language';
			}
			if (isset($ini[$key]) && count($ini[$key]) > 0) {
				foreach ($ini[$key] as $cfile) {
					$filelist[] = $dir.$cfile;
				}
				$archive = new PclZip($tempdir.$file);
				$v_list = $archive->add($filelist, PCLZIP_OPT_REMOVE_PATH, $dir, PCLZIP_OPT_ADD_PATH, $newdir);
				if ($v_list == 0) {
					$error = true;
					break;
				}
			}
		}
	}
	if ($error) {
		echo head();
		unset($archive);
		$filesystem->unlink($tempdir.$file);
		error('admin.php?action=cms&job=com', $archive->errorInfo(true));
	}
	else {
		viscacha_header('Content-Type: application/zip');
		viscacha_header('Content-Disposition: attachment; filename="'.$file.'"');
		viscacha_header('Content-Length: '.filesize($tempdir.$file));
		readfile($tempdir.$file);
		unset($archive);
		$filesystem->unlink($tempdir.$file);
	}
}
elseif ($job == 'com_delete') {
	echo head();
	$id = $gpc->get('id', int);
?>
	<table class='border' border='0' cellspacing='0' cellpadding='4' align='center'>
	<tr><td class='obox'>Delete Component</td></tr>
	<tr><td class='mbox'>
	<p align="center">Do you really want to delete this component?</p>
	<p align="center">
	<a href="admin.php?action=cms&job=com_delete2&id=<?php echo $id; ?>"><img border="0" align="middle" alt="" src="admin/html/images/yes.gif"> Yes</a>
	&nbsp&nbsp;&nbsp;&nbsp&nbsp;&nbsp;
	<a href="javascript: history.back(-1);"><img border="0" align="middle" alt="" src="admin/html/images/no.gif"> No</a>
	</p>
	</td></tr>
	</table>
<?php
	echo foot();
}
elseif ($job == 'com_delete2') {
	echo head();
	$id = $gpc->get('id', int);

	$cfg = $myini->read('components/'.$id.'/components.ini');

	$db->query("DELETE FROM {$db->pre}component WHERE id = '{$id}' LIMIT 1", __LINE__, __FILE__);

	$cache = array();
	$result = $db->query("SELECT template, stylesheet, images FROM {$db->pre}designs",__LINE__,__FILE__);
	$design = $db->fetch_assoc($result);
	while ($row = $db->fetch_assoc($design)) {
		$cache[] = $row;
	}
	$result = $db->query("SELECT id FROM {$db->pre}language",__LINE__,__FILE__);
	$languages = $db->fetch_assoc($result);

	$confirm = true;
	if (!empty($cfg['config']['uninstall'])) {
		$mod = $gpc->get('file', none, $cfg['config']['uninstall']);
		$uri = explode('?', $mod);
		$file = basename($uri[0]);
		if (isset($uri[1])) {
			parse_str($uri[1], $input);
		}
		else {
			$input = array();
		}
		$path = "components/{$id}/{$file}";
		if (!file_exists($path)) {
			error('admin.php?action=cms&job=cms_add', 'Installation file not found.');
		}
		else {
			$confirm = false;
			include($path);
		}
	}

	while ($row = $db->fetch_assoc($languages)) {
		rmdirr("./language/{$row['id']}/components/{$id}");
	}

	foreach ($cache as $design) {
		rmdirr("./templates/{$design['template']}/components/{$id}");
	}
	if (isset($cfg['image']) && count($cfg['image']) > 0) {
		foreach ($cache as $design) {
			foreach ($cfg['image'] as $file) {
				$filesystem->unlink("./images/{$design['images']}/{$file}");
			}
		}
	}
	if (isset($cfg['style']) && count($cfg['style']) > 0) {
		foreach ($cache as $design) {
			foreach ($cfg['style'] as $file) {
				$filesystem->unlink("./designs/{$design['stylesheet']}/{$file}");
			}
		}
	}
	rmdirr("./components/{$id}");

	$delobj = $scache->load('components');
	$delobj->delete();

	if (empty($cfg['config']['uninstall']) || $confirm == true) {
		ok('admin.php?action=cms&job=com', 'Component successfully uninstalled!');
	}
}
elseif ($job == 'doc') {
	$result = $db->query('SELECT * FROM '.$db->pre.'documents', __LINE__, __FILE__);
	echo head();
?>
<form name="form" method="post" action="admin.php?action=cms&job=doc_delete">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="7">
   <span style="float: right;"><a class="button" href="admin.php?action=cms&job=doc_add">Create new document</a></span>
   Manage Documents &amp; Pages
   </td>
  </tr>
  <tr>
   <td class="ubox" width="5%">Delete<br /><span class="stext"><input type="checkbox" onclick="check_all('delete[]');" name="all" value="1" /> All</span></td>
   <td class="ubox" width="40%">Title</td>
   <td class="ubox" width="5%">ID</td>
   <td class="ubox" width="20%">Author</td>
   <td class="ubox" width="15%">Last change</td>
   <td class="ubox" width="5%">Published</td>
   <td class="ubox" width="10%">Action</td>
  </tr>
<?php
	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();

	while ($row = $db->fetch_assoc($result)) {
		if(is_id($row['author']) && isset($memberdata[$row['author']])) {
			$row['author'] = $memberdata[$row['author']];
		}
		else {
			$row['author'] = 'Unknown';
		}
		if ($row['update'] > 0) {
			$row['update'] = date('d.m.Y H:i', $row['update']);
		}
		else {
			$row['update'] = 'Unknown';
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
   <a class="button" href="docs.php?id=<?php echo $row['id'].SID2URL_x; ?>" target="_blank">View</a>
   <a class="button" href="admin.php?action=cms&job=doc_edit&id=<?php echo $row['id']; ?>">Edit</a>
   </td>
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
	'0' => 'Kein Parser',
	'1' => 'HTML',
	'2' => 'PHP (HTML)',
	'3' => 'BB-Codes'
	);
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="4">Create a new document - Step 1</td>
  </tr>
  <tr>
   <td class="ubox">Title</td>
   <td class="ubox">Template</td>
   <td class="ubox">Parser</td>
   <td class="ubox">Integration of Templates</td>
  </tr>
<?php
foreach ($type as $id => $row) {
	$row['parser'] = isset($parser[$row['parser']]) ? $parser[$row['parser']] : 'Unknown';
	$row['inline'] = ($row['inline'] == 1) ? 'Statisch' : 'Dynamisch';
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
   <td class="obox" colspan="4">Create a new document - Step 2</td>
  </tr>
  <tr>
   <td class="mbox">
	<?php if ($format['inline'] == 1 && empty($format['template'])) { ?><span class="stext right">If no &lt;title&gt; can be parsed.</span><?php } ?>
	Title:<br />
	<input type="text" name="title" size="60" />
   </td>
  </tr>
  <?php if($format['remote'] != 1) { ?>
  <tr>
   <td class="mbox">
	Sourcecode:<br />
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
   <?php if($format['remote'] != 1) { ?><span class="stext right">If a path is given, the file will be saved on the filesystem instead of saving it to the database.</span><?php } ?>
   File:<br />
	<input type="text" name="file" size="60" />
   </td>
  </tr>
  <tr>
   <td class="mbox"><span class="stext right">Groups which have the ability to view the box.</span>Groups:<br />
   <?php while ($row = $db->fetch_assoc($groups)) { ?>
	<input type="checkbox" name="groups[]" checked="checked" value="<?php echo $row['id']; ?>"> <?php echo $row['name']; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox">
	Active:<br />
	<input type="checkbox" value="1" name="active" />
   </td>
  </tr>
  <tr><td class="ubox" align="center"><input type="submit" name="Submit" value="Add" /></td></tr>
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

	ok('admin.php?action=cms&job=doc', 'Document successfully added!');
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

		ok('admin.php?action=cms&job=doc', $anz.' Dokumente gelöscht');
	}
	else {
		error('admin.php?action=cms&job=doc', 'Keine Eingabe gemacht');
	}
}
elseif ($job == 'doc_edit') {
	$tpl = new tpl();
	$id = $gpc->get('id', int);
	$types = doctypes();
	$result = $db->query('SELECT * FROM '.$db->pre.'documents WHERE id = '.$id, __LINE__, __FILE__);
	$row = $db->fetch_assoc($result);
	if ($db->num_rows() == 0) {
		error('admin.php?action=cms&job=doc', 'Keine gültige ID übergeben');
	}
	$format = $types[$row['type']];
	if (!empty($row['file'])) {
		$rest = substr($row['file'], 0, 7);
		if ($rest != 'http://') {
			$row['content'] = file_get_contents($row['file']);
		}
	}
	$groups = $db->query("SELECT id, name FROM {$db->pre}groups", __LINE__, __FILE__);
	$garr = explode(',', $row['groups']);

	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();

	echo head();
?>
<form id="form" method="post" action="admin.php?action=cms&job=doc_edit2&id=<?php echo $id.SID2URL_x; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="4">Create a new document - Step 2</td>
  </tr>
  <tr>
   <td class="mbox">
	<?php if ($format['inline'] == 1 && empty($format['template'])) { ?><span class="stext right">If no &lt;title&gt; can be parsed.</span><?php } ?>
	Title:<br />
	<input type="text" name="title" size="60" value="<?php echo $gpc->prepare($row['title']); ?>" />
   </td>
  </tr>
  <?php if($format['remote'] != 1) { ?>
  <tr>
   <td class="mbox">
	Sourcecode:<br />
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
   <?php if($format['remote'] != 1) { ?><span class="stext right">If a path is given, the file will be saved on the filesystem instead of saving it to the database.</span><?php } ?>
   File:<br />
	<input type="text" name="file" value="<?php echo $row['file']; ?>" size="60" />
   </td>
  </tr>
  <tr>
   <td class="mbox"><span class="stext right">Groups which have the ability to view the box.</span>Groups:<br />
   <?php while ($g = $db->fetch_assoc($groups)) { ?>
	<input type="checkbox" name="groups[]"<?php echo iif($row['groups'] == 0 || in_array($g['id'], $garr),'checked="checked"'); ?> value="<?php echo $g['id']; ?>"> <?php echo $g['name']; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox">
	Author:<br />
	<input type="radio" value="<?php echo $row['author']; ?>" name="author" checked="checked" /> Keep current Author: <strong><?php echo isset($memberdata[$row['author']]) ? $memberdata[$row['author']] : 'Unknown'; ?></strong><br />
	<input type="radio" value="<?php echo $my->id; ?>" name="author" /> Change author to: <strong><?php echo $my->name; ?></strong>
   </td>
  </tr>
  <tr>
   <td class="mbox">
	Active:<br />
	<input type="checkbox" value="1" name="active"<?php echo iif($row['active'] == 1, ' checked="checked"'); ?> />
   </td>
  </tr>
  <tr><td class="ubox" align="center"><input type="submit" name="Submit" value="Edit" /></td></tr>
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

	if (empty($title)) {
		error('admin.php?action=cms&job=doc_edit&id='.$id, 'Title is empty!');
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

	ok('admin.php?action=cms&job=doc', 'Document successfully changed!');
}
elseif ($job == 'doc_code') {
	echo head();
	$codelang = $scache->load('syntaxhighlight');
	$clang = $codelang->get();
	?>
	<script src="admin/html/editor.js" type="text/javascript"></script>
	<table class="border">
	<tr><td class="obox">BB-Code Tag: Code</td></tr>
	<tr><td class="mbox">
	<strong>Choose the programming language for the highlighting:</strong><br /><br />
	<ul>
	   <li><input type="radio" name="data" onclick="InsertTagsCode('[code]','[/code]')" /> No Syntax Highlighting</li>
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
	$result = $db->query('SELECT * FROM '.$db->pre.'grab', __LINE__, __FILE__);
	echo head();
?>
<form name="form" method="post" action="admin.php?action=cms&job=feed_delete">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="5"><span style="float: right;"><a class="button" href="admin.php?action=cms&job=feed_add">Add a new Newsfeed</a></span>Import of Newsfeeds (<?php echo $db->num_rows(); ?>)</td>
  </tr>
  <tr>
   <td class="ubox" width="5%">Delete<br /><span class="stext"><input type="checkbox" onclick="check_all('delete[]');" name="all" value="1" /> All</span></td>
   <td class="ubox" width="5%">ID</td>
   <td class="ubox" width="35%">Title</td>
   <td class="ubox" width="45%">File</td>
   <td class="ubox" width="10%">Entries</td>
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
   <td class="mbox" width="35%"><a href="admin.php?action=cms&job=feed_edit&id=<?php echo $row['id'].SID2URL_x; ?>"><?php echo $row['title']; ?></a></td>
   <td class="mbox" width="45%"><a href="<?php echo $row['file']; ?>" target="_blank"><?php echo $row['file']; ?></a></td>
   <td class="mbox" width="10%"><?php echo $row['entries']; ?></td>
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" width="100%" colspan="5" align="center"><input type="submit" name="Submit" value="Delete"></td>
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
   <td class="obox" colspan="2">Add a new Newsfeed</td>
  </tr>
  <tr>
   <td class="mbox">Titel:<br><span class="stext">If no title can be read from the newsfeed.</td>
   <td class="mbox"><input type="text" name="temp1" size="60"></td>
  </tr>
  <tr>
   <td class="mbox">URL of the Newsfeed:<br><span class="stext">RSS 0.91, RSS 1.0, RSS 2.0 or ATOM-Newsfeed</td>
   <td class="mbox"><input type="text" name="temp2" size="60"></td>
  </tr>
  <tr>
   <td class="mbox">Number of Entries:<br><span class="stext">Maximum number of entries to show, 0 = all. Newsfeed are (normally) limited to 15 entries!</td>
   <td class="mbox"><input type="text" name="value" size="3"></td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Send"></td>
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
		error('admin.php?action=cms&job=feed_add'.SID2URL_x, 'No title specified');
	}
	if (empty($file)) {
		error('admin.php?action=cms&job=feed_add'.SID2URL_x, 'No URL specified');
	}
	if (empty($entries)) {
		$entries = 0;
	}

	$db->query('INSERT INTO '.$db->pre.'grab (title, file, entries) VALUES ("'.$title.'","'.$file.'","'.$entries.'")', __LINE__, __FILE__);

	$delobj = $scache->load('grabrss');
	$delobj->delete();

	ok('admin.php?action=cms&job=feed'.SID2URL_x, 'Newsfeed successfully added');
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

		ok('admin.php?action=cms&job=feed'.SID2URL_x, $anz.' Newsfeed(s) successfully deleted');
	}
	else {
		error('admin.php?action=cms&job=feed'.SID2URL_x, 'No newsfeed selected');
	}
}
elseif ($job == 'feed_edit') {
echo head();
$id = $gpc->get('id', int);
if (empty($id)) {
	error('admin.php?action=cms&job=feed'.SID2URL_x, 'Invalid ID given');
}
$result = $db->query('SELECT * FROM '.$db->pre.'grab WHERE id = '.$id, __LINE__, __FILE__);
$row = $db->fetch_assoc($result);

?>
<form name="form" method="post" action="admin.php?action=cms&job=feed_edit2&id=<?php echo $id.SID2URL_x; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2">Edit Document</td>
  </tr>
  <tr>
   <td class="mbox">Title:<br><span class="stext">If no title can be read from the newsfeed.</span></td>
   <td class="mbox"><input type="text" name="temp1" size="60" value="<?php echo $gpc->prepare($row['title']); ?>"></td>
  </tr>
  <tr>
   <td class="mbox">URL of the Newsfeed:<br><span class="stext">RSS 0.91, RSS 1.0, RSS 2.0 or ATOM-Newsfeed</span></td>
   <td class="mbox"><input type="text" name="temp2" size="60" value="<?php echo $row['file']; ?>"></td>
  </tr>
  <tr>
   <td class="mbox">Number of Entries:<br><span class="stext">Maximum number of entries for output, 0 = all. Newsfeed are (normally) limited to 15 entries!</span></td>
   <td class="mbox"><input type="text" name="value" size="3" value="<?php echo $row['entries']; ?>"></td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Send"></td>
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
		error('admin.php?action=cms&job=feed'.SID2URL_x, 'Invalid ID given');
	}
	if (empty($title)) {
		error('admin.php?action=cms&job=feed_edit&id='.$id.SID2URL_x, 'No title specified');
	}
	if (empty($file)) {
		error('admin.php?action=cms&job=feed_edit&id='.$id.SID2URL_x, 'No URL specified');
	}
	if (empty($entries)) {
		$entries = 0;
	}

	$db->query('UPDATE '.$db->pre.'grab SET file = "'.$file.'", title = "'.$title.'", entries = "'.$entries.'" WHERE id = "'.$id.'"', __LINE__, __FILE__);

	$delobj = $scache->load('grabrss');
	$delobj->delete();

	ok('admin.php?action=cms&job=feed'.SID2URL_x, 'Newsfeed successfully updated');
}
?>
