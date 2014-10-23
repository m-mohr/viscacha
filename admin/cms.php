<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "cms.php") die('Error: Hacking Attempt');

if ($job == 'plugins') {
	viscacha_header('Pragma: no-cache');
	echo head();
?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="4">
   	<span style="float: right;">
   	<a href="admin.php?action=cms&job=plugins_add">[<s>Plugin hinzuf&uuml;gen</s>]</a>
   	</span>Plugins verwalten
   </td>
  </tr>
  <tr> 
   <td class="ubox">Plugin</td>
   <td class="ubox">Status</td>
   <td class="ubox">Reihenfolge</td>
   <td class="ubox">Aktion</td>
  </tr>
<?php
	$result = $db->query("SELECT * FROM {$db->pre}menu WHERE module = '1' ORDER BY ordering, id");
	$cat = array();
	while ($row = $db->fetch_assoc($result)) {
		$position = explode(',', $row['position']);
		foreach ($position as $pos) {
			if (!isset($cat[$pos])) {
				$cat[$pos] = array();
			}
			$cat[$pos][] = $row;
		}
	}
	$positions = array_keys($cat);
	natsort($positions);

	foreach ($positions as $pos) {
	?>
	<tr>
		<td class="ubox" colspan="4">Hook: <strong><?php echo $pos; ?></strong></td>
	</tr>
		<?php
		foreach ($cat[$pos] as $head) {
		?>
		<tr class="mbox">
		<td width="50%">
		<?php echo $head['name']; ?><?php echo iif ($head['active'] == 0, ' (<em>Inaktiv</em>)'); ?>
		</td>
		<td width="10%">
		<?php 
		if ($head['active'] == 1) {
			echo '<a href="admin.php?action=cms&plug=1&job=nav_active&id='.$head['id'].'&int1=0">Deaktivieren</a>';
		}
		else {
			echo '<a href="admin.php?action=cms&plug=1&job=nav_active&id='.$head['id'].'&int1=1">Aktivieren</a>';
		}
		?>
		</td>
		<td width="15%" align="right"><?php echo $head['ordering']; ?>&nbsp;&nbsp;
		<a href="admin.php?action=cms&plug=1&job=nav_move&id=<?php echo $head['id']; ?>&int1=-1"><img src="admin/html/images/asc.gif" border="0" alt="Hoch"></a>&nbsp;
		<a href="admin.php?action=cms&plug=1&job=nav_move&id=<?php echo $head['id']; ?>&int1=1"><img src="admin/html/images/desc.gif" border="0" alt="Runter"></a>
		</td>
		<td width="35%">
			<select size="1" name="c" onchange="locate(this.value)">
				<option value="" selected>Bitte wählen</option>
				<option value="admin.php?action=cms&job=plugins_info&id=<?php echo $head['id']; ?>">Informationen</option>
				<option value="admin.php?action=cms&job=plugins_config&id=<?php echo $head['id']; ?>" style="text-decoration:line-through;">Konfiguration</option>
				<option value="admin.php?action=cms&job=plugins_edit&id=<?php echo $head['id']; ?>" style="text-decoration:line-through;">&Auml;ndern</option>
				<option value="admin.php?action=cms&job=plugins_delete&id=<?php echo $head['id']; ?>" style="text-decoration:line-through;">L&ouml;schen</option>
			</select>
		</td>
		</tr>
		<?php
		}
	}
	?></table><?php
	echo foot();
}
elseif ($job == 'plugins_edit') {
	$nav = $gpc->get('nav', int);
	echo head();
	?>
	<input type="hidden" name="nav" value="<?php echo $nav; ?>">
	<?php
	echo foot();
}
elseif ($job == 'plugins_edit2') {
	echo head();
	$nav = $gpc->get('nav', int);
	if ($nav == 1) {
		$url = 'admin.php?action=cms&job=nav';
	}
	else {
		$url = 'admin.php?action=cms&job=plugins';
	}
	ok($url);
}
elseif ($job == 'plugins_info') {
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT * FROM {$db->pre}menu WHERE id = '{$id}' AND module = '1' LIMIT 1");
	$row = $db->fetch_assoc($result);
	$cfg = $myini->read('modules/'.$row['link'].'/config.ini');
	$cfg = array_merge($row, $cfg);
	
	echo head();
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2">Informationen</b></td>
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
			   <td class="mbox" width="25%"><?php echo $subkey; ?></td>
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
elseif ($job == 'nav') {
	viscacha_header('Pragma: no-cache');
	echo head();
?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="4">
   	<span style="float: right;">
   	[<a href="admin.php?action=cms&job=nav_add">Link erstellen</a>]
   	[<a href="admin.php?action=cms&job=nav_addbox">Box erstellen</a>]
   	[<a href="admin.php?action=cms&job=nav_addplugin">PlugIn hinzufügen</a>]
   	</span>Navigation verwalten
   </td>
  </tr>
  <tr> 
   <td class="ubox">Link</td>
   <td class="ubox">Status</td>
   <td class="ubox">Reihenfolge</td>
   <td class="ubox">Aktion</td>
  </tr>
<?php
	$result = $db->query("SELECT * FROM {$db->pre}menu WHERE FIND_IN_SET('navigation', position) ORDER BY ordering, id");
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
	?>
	<tr class="mmbox">
	<td width="50%">
	<?php echo $head['name']; ?><?php echo iif ($head['module'] == 1, ' (<em>PlugIn</em>)').iif ($head['active'] == 0, ' (<em>Inaktiv</em>)'); ?>
	</td>
	<td width="10%">
	<?php 
	if ($head['active'] == 1) {
		echo '<a href="admin.php?action=cms&job=nav_active&id='.$head['id'].'&int1=0">Deaktivieren</a>';
	}
	else {
		echo '<a href="admin.php?action=cms&job=nav_active&id='.$head['id'].'&int1=1">Aktivieren</a>';
	}
	?>
	</td>
	<td width="15%"><?php echo $head['ordering']; ?>&nbsp;&nbsp;
	<a href="admin.php?action=cms&job=nav_move&id=<?php echo $head['id']; ?>&int1=-1"><img src="admin/html/images/asc.gif" border="0" alt="Hoch"></a>&nbsp;
	<a href="admin.php?action=cms&job=nav_move&id=<?php echo $head['id']; ?>&int1=1"><img src="admin/html/images/desc.gif" border="0" alt="Runter"></a>
	</td>
	<td width="35%">
		<select size="1" name="c" onchange="locate(this.value)">
			<option value="" selected>Bitte wählen</option>
			<?php if ($head['module'] == 1) { ?>
			<option value="admin.php?action=cms&job=plugins_info&id=<?php echo $head['id']; ?>">Informationen</option>
			<option value="admin.php?action=cms&job=plugins_config&nav=1&id=<?php echo $head['id']; ?>" style="text-decoration:line-through;">Konfiguration</option>
			<option value="admin.php?action=cms&job=plugins_edit&nav=1&id=<?php echo $head['id']; ?>" style="text-decoration:line-through;">&Auml;ndern</option>
			<option value="admin.php?action=cms&job=plugins_delete&nav=1&id=<?php echo $head['id']; ?>" style="text-decoration:line-through;">L&ouml;schen</option>
			<?php } else { ?>
			<option value="admin.php?action=cms&job=nav_edit&id=<?php echo $head['id']; ?>">Ändern</option>
			<option value="admin.php?action=cms&job=nav_delete&id=<?php echo $head['id']; ?>">Löschen</option>	
			<?php } ?>
		</select>
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
				<a href='<?php echo $link['link']; ?>' target='<?php echo $link['param']; ?>'><?php echo $link['name']; ?></a>			
				<?php } echo iif ($link['active'] == '0', ' (<i>Inaktiv</i>)'); ?></font><br>
				</td>
				<td class="mbox" width="10%">
				<?php 
				if ($link['active'] == 1) {
					echo '<a href="admin.php?action=cms&job=nav_active&id='.$link['id'].'&int1=0">Deaktivieren</a>';
				}
				else {
					echo '<a href="admin.php?action=cms&job=nav_active&id='.$link['id'].'&int1=1">Aktivieren</a>';
				}
				?>
				</td>
				<td class="mbox" width="15%" align="right"><?php echo $link['ordering']; ?>&nbsp;&nbsp;
				<a href="admin.php?action=cms&job=nav_move&id=<?php echo $link['id']; ?>&int1=-1"><img src="admin/html/images/asc.gif" border="0" alt="Hoch"></a>&nbsp;
				<a href="admin.php?action=cms&job=nav_move&id=<?php echo $link['id']; ?>&int1=1"><img src="admin/html/images/desc.gif" border="0" alt="Runter"></a>
				</font></td>			
				<td class="mbox" width="25%"><font class="mtext">
					<select size="1" name="c" onchange="locate(this.value)">
						<option value="" selected>Bitte wählen</option>
						<option value="admin.php?action=cms&job=nav_edit&id=<?php echo $link['id'].SID2URL_x; ?>">Ändern</option>
						<option value="admin.php?action=cms&job=nav_delete&id=<?php echo $link['id'].SID2URL_x; ?>">Löschen</option>
					</select>
				</font></td>
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
							<?php } echo iif ($sublink['active'] == '0', ' (<i>Inaktiv</i>)'); ?></font><br>
							</td>
							<td class="mbox" width="10%">
							<?php 
							if ($sublink['active'] == 1) {
								echo '<a href="admin.php?action=cms&job=nav_active&id='.$sublink['id'].'&int1=0">Deaktivieren</a>';
							}
							else {
								echo '<a href="admin.php?action=cms&job=nav_active&id='.$sublink['id'].'&int1=1">Aktivieren</a>';
							}
							?>
							</td>
							<td class="mbox" width="15%" align="right"><?php echo $sublink['ordering']; ?>&nbsp;&nbsp;
							<a href="admin.php?action=cms&job=nav_move&id=<?php echo $sublink['id']; ?>&int1=-1"><img src="admin/html/images/asc.gif" border="0" alt="Hoch"></a>&nbsp;
							<a href="admin.php?action=cms&job=nav_move&id=<?php echo $sublink['id']; ?>&int1=1"><img src="admin/html/images/desc.gif" border="0" alt="Runter"></a>
							</td>			
							<td class="mbox" width="25%">
								<select size="1" name="c" onchange="locate(this.value)">
									<option value="" selected>Bitte wählen</option>
									<option value="admin.php?action=cms&job=nav_edit&id=<?php echo $sublink['id']; ?>">Ändern</option>
									<option value="admin.php?action=cms&job=nav_delete&id=<?php echo $sublink['id']; ?>">Löschen</option>
								</select>
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
	$result = $db->query("SELECT * FROM {$db->pre}menu WHERE id = '{$id}' LIMIT 1");
	$data = $db->fetch_assoc($result);
	$data['group_array'] = explode(',', $data['groups']);
	
	$groups = $db->query("SELECT id, name FROM {$db->pre}groups");
	
	if ($data['sub'] > 0) {
		$result = $db->query("SELECT id, name, sub FROM {$db->pre}menu WHERE FIND_IN_SET('navigation', position) AND module != '1' ORDER BY ordering, id");
		$cache = array(0 => array());
		while ($row = $db->fetch_assoc($result)) {
			if (!isset($cache[$row['sub']]) || !is_array($cache[$row['sub']])) {
				$cache[$row['sub']] = array();
			}
			$cache[$row['sub']][] = $row;
		}
	}
	?>
<form name="form" method="post" action="admin.php?action=cms&job=nav_edit2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2">Box/Link ändern</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Titel:</td>
   <td class="mbox" width="50%"><input type="text" name="title" size="40" value="<?php echo $data['name']; ?>" /></td>
  </tr>
<?php if ($data['sub'] > 0) { ?>
  <tr> 
   <td class="mbox" width="50%">Datei/URL: (<a href="javascript:docs();">Existierende Dokumente</a>)</td>
   <td class="mbox" width="50%"><input type="text" name="url" size="40" value="<?php echo $data['link']; ?>" /></td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Target:<br /><span class="stext">Standardmäßig werden alle Verweise im aktuellen Fenster geöffnet. Mit der Option können Sie ein Zielfenster für den Verweis festlegen. "_blank" öffnet ein neues Fenster.</span></td>
   <td class="mbox" width="50%"><input type="text" name="target" size="40" value="<?php echo $data['param']; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Parent Box/Link:</td>
   <td class="mbox" width="50%">
   <select name="sub">
   <?php foreach ($cache[0] as $row) { ?>
   <option style="font-weight: bold;" value="<?php echo $row['id']; ?>"<?php echo iif($row['id'] == $data['sub'], 'selected="selected"'); ?>><?php echo $row['name']; ?></option>
   <?php
   if (isset($cache[$row['id']])) {
   foreach ($cache[$row['id']] as $row) {
   ?>
   <option value="<?php echo $row['id']; ?>"<?php echo iif($row['id'] == $data['sub'], 'selected="selected"'); ?>>+&nbsp;<?php echo $row['name']; ?></option>
   <?php }}} ?>
   </select>
   </td>
  </tr>
<?php } ?>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Gruppen:<br /><span class="stext">Gruppen denen es erlaubt ist, die Box zu betrachten.</span></td>
   <td class="mbox" width="50%">
   <?php while ($row = $db->fetch_assoc($groups)) { ?>
    <input type="checkbox" name="groups[]"<?php echo iif($data['groups'] == 0 || in_array($row['id'], $data['group_array']), 'checked="checked"'); ?> value="<?php echo $row['id']; ?>"> <?php echo $row['name']; ?><br />
   <?php } ?>
   </td>
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
	$result = $db->query("SELECT * FROM {$db->pre}menu WHERE id = '{$id}' LIMIT 1");
	$data = $db->fetch_assoc($result);
	
	$title = $gpc->get('title', str);
	$groups = $gpc->get('groups', arr_int);
	if (empty($title)) {
		error('admin.php?action=cms&job=nav_addbox', 'Sie haben keinen Titel angegeben.');
	}
	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'groups');
	$count = $db->fetch_array($result);
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
		$db->query("UPDATE {$db->pre}menu SET name = '{$title}', link = '{$url}', param = '{$target}', groups = '{$groups}', sub = '{$sub}' WHERE id = '{$id}' LIMIT 1");	
	}
	else {
		$db->query("UPDATE {$db->pre}menu SET name = '{$title}', groups = '{$groups}' WHERE id = '{$id}' LIMIT 1");	
	}
	$scache = new scache('modules_navigation');
	$scache->deletedata();
	ok('admin.php?action=cms&job=nav', 'Link/Box wurde erfolgreich geändert');
}
elseif ($job == 'nav_delete') {
	echo head();
	$id = $gpc->get('id', int);
?>
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	<tr><td class="obox">Komponente löschen</td></tr>
	<tr><td class="mbox">
	<p align="center">Wollen Sie diese Box/diesen Link mit allen evtl. vorhandenen untergeordneten Links wirklich löschen?</p>
	<p align="center">
	<a href="admin.php?action=cms&job=nav_delete2&id=<?php echo $id; ?>"><img border="0" align="middle" alt="" src="admin/html/images/yes.gif"> Ja</a>
	&nbsp&nbsp;&nbsp;&nbsp&nbsp;&nbsp;
	<a href="javascript: history.back(-1);"><img border="0" align="middle" alt="" src="admin/html/images/no.gif"> Nein</a>
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
	$result = $db->query("SELECT id, sub FROM {$db->pre}menu WHERE sub = '{$id}'");
	while($row = $db->fetch_assoc($result)) {
		$delete[] = $row['id'];
		$result2 = $db->query("SELECT id FROM {$db->pre}menu WHERE sub = '{$row['id']}'");
		while($row2 = $db->fetch_assoc($result2)) {
			$delete[] = $row2['id'];
		}
	}
	$count = count($delete);
	$ids = implode(',', $delete);
	$db->query("DELETE FROM {$db->pre}menu WHERE id IN ({$ids}) LIMIT {$count}");
	$anz = $db->affected_rows();
	ok('admin.php?action=cms&job=nav', $anz.' Links/Boxen wurden erfolgreich gelöscht.');
}
elseif ($job == 'nav_move') {
	$id = $gpc->get('id', int);
	$pos = $gpc->get('int1', int);
	if ($id < 1) {
		error('admin.php?action=cms&job=nav', 'Ungültige ID angegeben');
	}
	if ($pos < 0) {
		$db->query('UPDATE '.$db->pre.'menu SET ordering = ordering-1 WHERE id = '.$id);
	}
	elseif ($pos > 0) {
		$db->query('UPDATE '.$db->pre.'menu SET ordering = ordering+1 WHERE id = '.$id);
	}
	$scache = new scache('modules_navigation');
	$scache->deletedata();
	if ($gpc->get('plug', int) == 1) {
		$scache = new scache('modules');
		$scache->deletedata();
		viscacha_header('Location: admin.php?action=cms&job=plugins');
	}
	else {
		viscacha_header('Location: admin.php?action=cms&job=nav');
	}
}
elseif ($job == 'nav_active') {
	$id = $gpc->get('id', int);
	$pos = $gpc->get('int1', int);
	if ($id < 1) {
		error('admin.php?action=cms&job=nav', 'Ungültige ID angegeben');
	}
	if ($pos != 0 && $pos != 1) {
		error('admin.php?action=cms&job=nav', 'Ungültigen Status angegeben');
	}
	$scache = new scache('modules_navigation');
	$scache->deletedata();
	$db->query('UPDATE '.$db->pre.'menu SET active = "'.$pos.'" WHERE id = '.$id);
	if ($gpc->get('plug', int) == 1) { 
		$scache = new scache('modules');
		$scache->deletedata();
		viscacha_header('Location: admin.php?action=cms&job=plugins');
	}
	else {
		viscacha_header('Location: admin.php?action=cms&job=nav');
	}
}
elseif ($job == 'nav_addplugin') {
	echo head();
?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox">Neues PlugIn hinzufügen</td>
  </tr>
  <tr> 
   <td class="mbox">Um ein PlugIn hinzuzufügen, verfahren Sie wie mit normalen PlugIns. Evtl. müssen Sie das PlugIn beim Erstellen dem Hook "navigation" zuweisen, dann wird das PlugIn automatisch auch in die Navigation übernommen.<ul><li><a href="admin.php?action=cms&job=plugins_add" style="text-decoration:line-through;">Plugin hinzufügen</a></li></ul></td>
  </tr>
 </table>
<?php
	echo foot();
}
elseif ($job == 'nav_add') {
	echo head();
	$groups = $db->query("SELECT id, name FROM {$db->pre}groups");
	$result = $db->query("SELECT id, name, sub FROM {$db->pre}menu WHERE FIND_IN_SET('navigation', position) AND module != '1' ORDER BY ordering, id");
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
   <td class="obox" colspan="2">Neue Box erstellen</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Titel:</td>
   <td class="mbox" width="50%"><input type="text" name="title" size="40" /></td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Datei/URL: (<a href="javascript:docs();">Existierende Dokumente</a>)</td>
   <td class="mbox" width="50%"><input type="text" name="url" size="40" /></td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Target:<br /><span class="stext">Standardmäßig werden alle Verweise im aktuellen Fenster geöffnet. Mit der Option können Sie ein Zielfenster für den Verweis festlegen. "_blank" öffnet ein neues Fenster.</span></td>
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
   <td class="mbox" width="50%">Einsortieren:</td>
   <td class="mbox" width="50%">
   <select name="sort">
    <option value="0">am Anfang</option>
    <option value="1">am Ende</option>
   </select>
   </td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Gruppen:<br /><span class="stext">Gruppen denen es erlaubt ist, die Box zu betrachten.</span></td>
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
		$sortx = $db->fetch_array($db->query("SELECT MAX(ordering) FROM {$db->pre}menu WHERE sub = '{$sub}' LIMIT 1"));
		$sort = $sortx[0]+1;
	}
	elseif ($sort == 0) {
		$sortx = $db->fetch_array($db->query("SELECT MIN(ordering) FROM {$db->pre}menu WHERE sub = '{$sub}' LIMIT 1"));
		$sort = $sortx[0]-1;
	}
	else {
		$sort = 0;
	}
	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'groups');
	$count = $db->fetch_array($result);
	if (count($groups) == $count[0]) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}
	$db->query("INSERT INTO {$db->pre}menu (name, groups, ordering, link, param, sub) VALUES ('{$title}','{$groups}','{$sort}','{$url}','{$target}','{$sub}')");
	$scache = new scache('modules_navigation');
	$scache->deletedata();
	ok('admin.php?action=cms&job=nav', 'Link wurde erfolgreich hinzugefügt');
}
elseif ($job == 'nav_addbox') {
	echo head();
	$sort = $db->query("SELECT ordering, name FROM {$db->pre}menu WHERE sub = '0' AND FIND_IN_SET('navigation', position) ORDER BY ordering, id");
	$groups = $db->query("SELECT id, name FROM {$db->pre}groups");
	?>
<form name="form" method="post" action="admin.php?action=cms&job=nav_addbox2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2">Neue Box erstellen</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Titel:</td>
   <td class="mbox" width="50%"><input type="text" name="title" size="40" /></td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Einsortieren nach:</td>
   <td class="mbox" width="50%">
   <select name="sort">
   <?php while ($row = $db->fetch_assoc($sort)) { ?>
    <option value="<?php echo $row['ordering']; ?>"><?php echo $row['name']; ?></option>
   <?php } ?>
   </select>
   </td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Gruppen:<br /><span class="stext">Gruppen denen es erlaubt ist, die Box zu betrachten.</span></td>
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
	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'groups');
	$count = $db->fetch_array($result);
	if (count($groups) == $count[0]) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}
	$db->query("INSERT INTO {$db->pre}menu (name, groups, ordering) VALUES ('{$title}','{$groups}','{$sort}')");
	$scache = new scache('modules_navigation');
	$scache->deletedata();
	ok('admin.php?action=cms&job=nav', 'Box wurde erfolgreich hinzugefügt');
}
elseif ($job == 'nav_docslist') {
echo head();
$result = $db->query('SELECT id, title FROM '.$db->pre.'documents');
?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox">Existierende Dokumente und Seiten</td>
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
elseif ($job == 'com') {
	viscacha_header('Pragma: no-cache');
	echo head();
?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="5"><span style="float: right;">[<a href="admin.php?action=cms&job=com_add">Neue Komponente hochladen</a>]</span>Komponenten verwalten</td>
  </tr>
  <tr> 
   <td class="ubox">Name</b></td>
   <td class="ubox">Status</b></td>
   <td class="ubox">Version</b></td>
   <td class="ubox">Aktion</b></td>
  </tr>
<?php
	$result = $db->query("SELECT * FROM {$db->pre}component ORDER BY active DESC");
	while ($row = $db->fetch_assoc($result)) {
		$head = array();
		$cfg = $myini->read('components/'.$row['id'].'/components.ini');
		$head = array_merge($row, $cfg);
	?>
	<tr>
	<td class="mbox" width="40%">
	<?php echo $head['config']['name']; ?><?php echo iif ($head['active'] == '0', ' (<i>Inaktiv</i>)'); ?>
	</td>
	<td class="mbox" width="15%">
	<?php 
	if ($head['active'] == 1) {
		echo '<a href="admin.php?action=cms&job=com_active&id='.$head['id'].'&int1=0">Deaktivieren</a>';
	} else {
		echo '<a href="admin.php?action=cms&job=com_active&id='.$head['id'].'&int1=1">Aktivieren</a>';
	}
	?>
	</td>
	<td class="mbox" width="15%">
	<?php echo $head['config']['version']; ?><br>
	</td>
	<td class="mbox" width="30%">
		<select size="1" name="c" onchange="locate(this.value)">
			<option value="" selected>Bitte w&auml;hlen</option>
			<option value="admin.php?action=cms&job=com_info&id=<?php echo $head['id']; ?>">Informationen</option>
			<option value="admin.php?action=cms&job=com_admin&cid=<?php echo $head['id']; ?>">Administration</option>
			<?php if (!empty($cfg['config']['readme'])) { ?>
			<option value="admin.php?action=cms&job=com_readme&cid=<?php echo $head['id']; ?>">Readme</option>
			<?php } ?>
			<option value="admin.php?action=cms&job=com_export&id=<?php echo $head['id']; ?>">Exportieren</option>
			<option value="admin.php?action=cms&job=com_delete&id=<?php echo $head['id']; ?>">L&ouml;schen</option>
		</select>
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
	$result = $db->query("SELECT * FROM {$db->pre}component WHERE id = {$id} LIMIT 1");
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
		error('admin.php?action=cms&job=com', 'Keine Readme vorhanden!');
	}
}
elseif ($job == 'com_admin') {
	$id = $gpc->get('cid', int);
	$mod = $gpc->get('file', str, 'frontpage');
	$result = $db->query("SELECT * FROM {$db->pre}component WHERE id = {$id} LIMIT 1");
	$row = $db->fetch_assoc($result);
	$cfg = $myini->read('components/'.$row['id'].'/components.ini');
	$cfg = array_merge($row, $cfg);
	if (!isset($cfg['admin'][$mod])) {
		echo head();
		error('admin.php?action=cms&job=com','Section not found!');
	}
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
	$result = $db->query("SELECT * FROM {$db->pre}component WHERE id = {$id} LIMIT 1");
	$row = $db->fetch_assoc($result);
	$cfg = $myini->read('components/'.$row['id'].'/components.ini');
	$cfg = array_merge($row, $cfg);
	
	echo head();
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2">Informationen</b></td>
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
	if (!$_GET['id']) {
		error('admin.php?action=cms&job=com'.SID2URL_x, 'Ungültige ID angegeben');
	}
	if ($_GET['int1'] != 0 && $_GET['int1'] != 1) {
		error('admin.php?action=cms&job=com'.SID2URL_x, 'Ungültigen Status angegeben');
	}
	$scache = new scache('components');
	$scache->deletedata();
	$db->query('UPDATE '.$db->pre.'component SET active = "'.$_GET['int1'].'" WHERE id = '.$_GET['id']);
	viscacha_header('Location: admin.php?action=cms&job=com'.SID2URL_x);
}
elseif ($job == 'com_add') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=cms&job=com_add2" enctype="multipart/form-data">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2">Neue Komponente hochladen</b></td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Gepackte Komponente:<br><span class="stext">Komprimierte Komponentendatei (.zip). Es sollten nur Komponenten von vertraulichen Quellen installiert werden!</td>
   <td class="mbox" width="50%"><input type="file" name="upload" size="60" /></td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'com_add2') {
	echo head();
	
	if (isset($_FILES) && is_array($_FILES['upload']) && $_FILES['upload']['name']) {
		require("classes/class.upload.php");
		$my_uploader = new uploader();
		if ($my_uploader->upload('upload', array('.zip'))) {
			$my_uploader->save_file('temp/', '2');
		}
		if ($my_uploader->return_error()) {
			error('admin.php?action=cms&job=com_add', $my_uploader->return_error());
		} 
		else {
			$tdir = "temp/".time();
			$filesystem->mkdir($tdir);
			if (!is_dir($tdir)) {
				error('admin.php?action=cms&job=com_add', 'Directory could not be created for extraction.');
			}
			include('classes/class.zip.php');
			$archive = new PclZip('temp/'.$my_uploader->file['name']);
			if ($archive->extract(PCLZIP_OPT_PATH, $tdir) == 0) {
				error('admin.php?action=cms&job=com_add', $archive->errorInfo(true));
			}

			if (file_exists($tdir.'/components.ini')) {
				$cfg = $myini->read($tdir.'/components.ini');
			}
			else {
				error('admin.php?action=cms&job=com_add', 'components.ini file does not exist!');
			}

			if (!isset($cfg['module']['frontpage'])) {
				$cfg['module']['frontpage'] = '';
			}

			$db->query("INSERT INTO {$db->pre}component (file) VALUES ('{$cfg['module']['frontpage']}')");
			$id = $db->insert_id();

			$result = $db->query("SELECT template, stylesheet, images FROM {$db->pre}designs WHERE id = '{$config['templatedir']}'",__LINE__,__FILE__);
			$design = $db->fetch_assoc($result);

			if (isset($cfg['php']) && count($cfg['php']) > 0) {
				$filesystem->mkdir("./components/$id");
				foreach ($cfg['php'] as $file) {
					$filesystem->copy("$tdir/php/$file", "./components/$id/$file");
				}
			}
			if (isset($cfg['language']) && count($cfg['language']) > 0) {
				$filesystem->mkdir("./language/{$config['langdir']}/components/$id", 0777);
				foreach ($cfg['language'] as $file) {
					$filesystem->copy("$tdir/language/$file", "./language/{$config['langdir']}/components/$id/$file");
					$filesystem->chmod("./language/{$config['langdir']}/components/$id/$file", 0666);
				}
			}
			
			if (isset($cfg['template']) && count($cfg['template']) > 0) {
				$filesystem->mkdir("./templates/{$design['template']}/components/$id", 0777);
				foreach ($cfg['template'] as $file) {
					$filesystem->copy("$tdir/template/$file", "./templates/{$design['template']}/components/$id/$file");
					$filesystem->chmod("./templates/{$design['template']}/components/$id/$file", 0666);
				}
			}
			
			if (isset($cfg['image']) && count($cfg['image']) > 0) {
				foreach ($cfg['image'] as $file) {
					$filesystem->copy("$tdir/image/$file", "./images/{$design['images']}/$file");
				}
			}
			
			if (isset($cfg['style']) && count($cfg['style']) > 0) {
				foreach ($cfg['style'] as $file) {
					$filesystem->copy("$tdir/style/$file", "./designs/{$design['stylesheet']}/$file");
				}
			}

			$filesystem->copy($tdir.'/components.ini',"./components/$id/components.ini");
			$filesystem->chmod("./components/$id/components.ini", 0666);

			$scache = new scache('components');
			$scache->deletedata();
			
			rmdirr($tdir);
			$filesystem->unlink('./temp/'.$my_uploader->file['name']);
			
			if (empty($cfg['config']['install'])) {
				ok('admin.php?action=cms&job=com', 'Komponente wurde installiert!');
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
				include("components/{$id}/{$file}");
			}	
		}
	}
	else {
		error('admin.php?action=cms&job=acom_add', 'Es wurde keine Datei ausgewählt');
	}
}
elseif ($job == 'com_export') {
	$id = $gpc->get('id', int);
	$tempdir = 'temp/';
	
	$result = $db->query("SELECT * FROM {$db->pre}component WHERE id = {$id} LIMIT 1");
	$row = $db->fetch_assoc($result);
	$ini = $myini->read('components/'.$row['id'].'/components.ini');
	$info = array_merge($row, $ini);

	$result = $db->query("SELECT * FROM {$db->pre}designs WHERE id = '{$config['templatedir']}' LIMIT 1");
	$design = $db->fetch_assoc($result);
	
	$file = convert2adress($info['config']['name']).'.zip';
	$dirs = array(
		'template' => "templates/{$design['template']}/components/{$id}/",
		'image' => "images/{$design['images']}/",
		'style' => "designs/{$design['stylesheet']}/",
		'language' => "language/{$config['langdir']}/components/{$id}/",
		'php' => "components/{$id}/"
	);
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
			if (isset($ini[$key]) && count($ini[$key]) > 0) {
				foreach ($ini[$key] as $cfile) {
					$filelist[] = $dir.$cfile;
				}
				$archive = new PclZip($tempdir.$file);
				$v_list = $archive->add($filelist, PCLZIP_OPT_REMOVE_PATH, $dir, PCLZIP_OPT_ADD_PATH, $key);
				if ($v_list == 0) {
					$error = true;
					break;
				}
			}
		}
	}
	if ($error) {
		echo head();
		$filesystem->unlink($tempdir.$file);
		error('admin.php?action=cms&job=com', $archive->errorInfo(true));
	}
	else {
		viscacha_header('Content-Type: application/zip');
		viscacha_header('Content-Disposition: attachment; filename="'.$file.'"');
		viscacha_header('Content-Length: '.filesize($tempdir.$file));
		readfile($tempdir.$file);
		$filesystem->unlink($tempdir.$file);
	}
}
elseif ($job == 'com_delete') {
	echo head();
	$id = $gpc->get('id', int);
?>
	<table class='border' border='0' cellspacing='0' cellpadding='4' align='center'>
	<tr><td class='obox'>Komponente löschen</td></tr>
	<tr><td class='mbox'>
	<p align="center">Wollen Sie diese Komponente wirklich löschen?</p>
	<p align="center">
	<a href="admin.php?action=cms&job=com_delete2&id=<?php echo $id; ?>"><img border="0" align="middle" alt="" src="admin/html/images/yes.gif"> Ja</a>
	&nbsp&nbsp;&nbsp;&nbsp&nbsp;&nbsp;
	<a href="javascript: history.back(-1);"><img border="0" align="middle" alt="" src="admin/html/images/no.gif"> Nein</a>
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

	$db->query("DELETE FROM {$db->pre}component WHERE id = '".$id."' LIMIT 1");

	$result = $db->query("SELECT template, stylesheet, images FROM {$db->pre}designs WHERE id = '{$config['templatedir']}'",__LINE__,__FILE__);
	$design = $db->fetch_assoc($result);

	rmdirr("./language/{$config['langdir']}/components/$id");
	rmdirr("./templates/{$design['template']}/components/$id");
	if (isset($cfg['image']) && count($cfg['image']) > 0) {
		foreach ($cfg['image'] as $file) {
			$filesystem->unlink("./images/{$design['images']}/$file");
		}
	}
	if (isset($cfg['style']) && count($cfg['style']) > 0) {
		foreach ($cfg['style'] as $file) {
			$filesystem->unlink("./designs/{$design['stylesheet']}/$file");
		}
	}
	rmdirr("./components/$id");

	$scache = new scache('components');
	$scache->deletedata();
	
	if (empty($cfg['config']['uninstall'])) {
		ok('admin.php?action=cms&job=com', 'Komponente wurde deinstalliert!');
	}
	else {
		$mod = $gpc->get('file', none, $cfg['config']['uninstall']);
		$uri = explode('?', $mod);
		$file = basename($uri[0]);
		if (isset($uri[1])) {
			parse_str($uri[1], $input);
		}
		else {
			$input = array();
		}
		include("components/{$id}/{$file}");
	}
}
elseif ($job == 'doc') {
	$result = $db->query('SELECT * FROM '.$db->pre.'documents');
	echo head();
?>
<form name="form" method="post" action="admin.php?action=cms&job=doc_delete">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="7">
   <span style="float: right;">[<a href="admin.php?action=cms&job=doc_add">Neues Dokument erstellen</a>]</span>
   Dokumente &amp; Seiten  verwalten
   </td>
  </tr>
  <tr>
   <td class="ubox" width="5%">DEL</td>
   <td class="ubox" width="40%">Title</td>
   <td class="ubox" width="5%">ID</td>
   <td class="ubox" width="20%">Author</td>
   <td class="ubox" width="15%">Last change</td>
   <td class="ubox" width="5%">Published</td>
   <td class="ubox" width="10%">Action</td>
  </tr>
<?php
	$memberdata = cache_memberdata();
	while ($row = $db->fetch_assoc($result)) {	
		if(is_id($row['author']) && isset($memberdata[$row['author']])) {
			$row['author'] = $memberdata[$row['author']];
		}
		else {
			$row['author'] = 'Unknown';
		}
		$row['update'] = date('d.m.Y H:i', $row['update']);
?>
  <tr>
   <td class="mbox" width="5%"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
   <td class="mbox" width="40%"><a href="admin.php?action=cms&job=doc_edit&id=<?php echo $row['id']; ?>"><?php echo $row['title']; ?></a></td>
   <td class="mbox" width="5%"><?php echo $row['id']; ?></td>
   <td class="mbox" width="20%"><?php echo $row['author']; ?></td>
   <td class="mbox" width="15%"><?php echo $row['update']; ?></td>
   <td class="mbox center" width="5%"><?php echo noki($row['active'], ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=cms&job=doc_ajax_active&id='.$row['id'].'\')"'); ?></td>
   <td class="mbox" width="10%">
   [<a href="docs.php?id=<?php echo $row['id'].SID2URL_x; ?>" target="_blank">View</a>]
   [<a href="admin.php?action=cms&job=doc_edit&id=<?php echo $row['id']; ?>">Edit</a>]
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
	$result = $db->query("SELECT active FROM {$db->pre}documents WHERE id = '{$id}' LIMIT 1");
	$use = $db->fetch_assoc($result);
	$use = invert($use['active']);
	$db->query("UPDATE {$db->pre}documents SET active = '{$use}' WHERE id = '{$id}' LIMIT 1");
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
   <td class="ubox">Einbindung der Templates</td>
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
  	$groups = $db->query("SELECT id, name FROM {$db->pre}groups");
?>
<form id="form" method="post" action="admin.php?action=cms&job=doc_add3&type=<?php echo $type; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="4">Create a new document - Step 2</td>
  </tr>
  <tr>
   <td class="mbox">
	<?php if ($format['inline'] == 1 && empty($format['template'])) { ?><span class="stext right">Falls kein &lt;title&gt; geparsed werden kann.</span><?php } ?>
	Title:<br />
	<input type="text" name="title" size="60" />
   </td>
  </tr>
  <?php if($format['remote'] != 1) { ?>
  <tr>
   <td class="mbox">
	HTML-Sourcecode:<br /> 
	<?php
	$path = $tpl->altdir.'docs/'.$format['template'].'.html';
	if ($format['inline'] == 1 && file_exists($path)) {
		$preload = file_get_contents($path);
	}
	else {
		$preload = '';
	}
	?>
	<textarea id="template" name="template" rows="20" cols="110" class="texteditor"><?php echo $preload; ?></textarea>
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
   <?php if($format['remote'] != 1) { ?><span class="stext right">Wenn Sie hier einen Pfad eingeben wird die Datei anstatt in der Datenbank im Dateisystem gespeichert.</span><?php } ?>
   File:<br />
	<input type="text" name="file" size="60" />
   </td>
  </tr>
  <tr> 
   <td class="mbox"><span class="stext right">Gruppen denen es erlaubt ist, die Box zu betrachten.</span>Gruppen:<br />
   <?php while ($row = $db->fetch_assoc($groups)) { ?>
    <input type="checkbox" name="groups[]" checked="checked" value="<?php echo $row['id']; ?>"> <?php echo $row['name']; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox">
	Freigeschaltet:<br /> 
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
  		if (strlen(strip_tags($content)) > 4 && $filesystem->file_put_contents($file, $content) == 0) {
  			$content = $gpc->$this->save_str($content);
  			$file = '';
  		}
  	}
	
	if (empty($title)) {
		error('admin.php?action=cms&job=doc_add', 'Title is empty!');
	}

	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'groups');
	$count = $db->fetch_array($result);
	if (count($groups) == $count[0]) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}
	
	$time = time();
	
	$db->query("INSERT INTO {$db->pre}documents ( `title` , `content` , `author` , `date` , `update` , `type` , `groups` , `active` , `file` ) VALUES ('{$title}', '{$content}', '{$my->id}', '{$time}' , '{$time}' , '{$type}', '{$groups}', '{$active}', '{$file}')");

	ok('admin.php?action=cms&job=doc', 'Eintrag eingefügt');
}
elseif ($job == 'doc_delete') {
	echo head();
	$delete = $gpc->get('delete', arr_int);
	if (count($delete) > 0) {
		$deleteids = array();
		foreach ($delete as $did) {
			$deleteids[] = 'id = '.$did; 
		}
		$result = $db->query('SELECT file FROM '.$db->pre.'documents WHERE '.implode(' OR ',$deleteids));
		while ($row = $db->fetch_array($result)) {
			$rest = @substr(strtolower($row['file']), 0, 7);
			if (!empty($row[0]) && $rest != 'http://') {
				$filesystem->unlink($row[0]);
			}
		}

		$db->query('DELETE FROM '.$db->pre.'documents WHERE '.implode(' OR ',$deleteids));
		$anz = $db->affected_rows();
			
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
	$result = $db->query('SELECT * FROM '.$db->pre.'documents WHERE id = '.$id);
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
	$groups = $db->query("SELECT id, name FROM {$db->pre}groups");
	$garr = explode(',', $row['groups']);
	echo head();
	// ToDo: Autor ändern
?>
<form id="form" method="post" action="admin.php?action=cms&job=doc_edit2&id=<?php echo $id.SID2URL_x; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="4">Create a new document - Step 2</td>
  </tr>
  <tr>
   <td class="mbox">
	<?php if ($format['inline'] == 1 && empty($format['template'])) { ?><span class="stext right">Falls kein &lt;title&gt; geparsed werden kann.</span><?php } ?>
	Title:<br />
	<input type="text" name="title" size="60" value="<?php echo $gpc->prepare($row['title']); ?>" />
   </td>
  </tr>
  <?php if($format['remote'] != 1) { ?>
  <tr>
   <td class="mbox">
	HTML-Sourcecode:<br /> 
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
   <?php if($format['remote'] != 1) { ?><span class="stext right">Wenn Sie hier einen Pfad eingeben wird die Datei anstatt in der Datenbank im Dateisystem gespeichert.</span><?php } ?>
   File:<br />
	<input type="text" name="file" value="<?php echo $row['file']; ?>" size="60" />
   </td>
  </tr>
  <tr> 
   <td class="mbox"><span class="stext right">Gruppen denen es erlaubt ist, die Box zu betrachten.</span>Gruppen:<br />
   <?php while ($g = $db->fetch_assoc($groups)) { ?>
    <input type="checkbox" name="groups[]"<?php echo iif($row['groups'] == 0 || in_array($g['id'], $garr),'checked="checked"'); ?> value="<?php echo $g['id']; ?>"> <?php echo $g['name']; ?><br />
   <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="mbox">
	Freigeschaltet:<br /> 
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
  	$groups = $gpc->get('groups', arr_int);
  	$file = $gpc->get('file', none);
  	$file = trim($file);
  	if (empty($file)) {
  		$content = $gpc->get('template', str);
  	}
  	else {
  		$content = $gpc->get('template', none);
  		if (strlen(strip_tags($content)) > 4 && $filesystem->file_put_contents($file, $content) == 0) {
  			$content = $gpc->$this->save_str($content);
  			$file = '';
  		}
		$content = '';
  	}
	
	if (empty($title)) {
		error('admin.php?action=cms&job=doc_edit&id='.$id, 'Title is empty!');
	}

	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'groups');
	$count = $db->fetch_array($result);
	if (count($groups) == $count[0]) {
		$groups = 0;
	}
	else {
		$groups = implode(',', $groups);
	}
	
	$time = time();
	
	$db->query("UPDATE {$db->pre}documents SET `title` = '{$title}', `content` = '{$content}', `update` = '{$time}', `groups` = '{$groups}', `active` = '{$active}', `file` = '{$file}' WHERE id = '{$id}' LIMIT 1",__LINE__,__FILE__);

	ok('admin.php?action=cms&job=doc', 'Eintrag geändert');
}

elseif ($job == 'feed') {
	$result = $db->query('SELECT * FROM '.$db->pre.'grab');
	echo head();
?>
<form name="form" method="post" action="admin.php?action=cms&job=feed_delete">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="5"><span style="float: right;">[<a href="admin.php?action=cms&job=feed_add">Add a new Newsfeed</a>]</span>Newsfeed Syndication</td>
  </tr>
  <tr>
   <td class="ubox" width="5%">Del</td>
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
   <td class="mbox">Titel:<br><span class="stext">Falls kein Titel aus dem Newsfeed gelesen werden kann.</td> 
   <td class="mbox"><input type="text" name="temp1" size="60"></td> 
  </tr>
  <tr>
   <td class="mbox">URL zum Newsfeed:<br><span class="stext">RSS 0.91, RSS 1.0, RSS 2.0 oder ATOM-Newsfeed</td>
   <td class="mbox"><input type="text" name="temp2" size="60"></td>
  </tr>
  <tr>
   <td class="mbox">Anzahl der Einträge:<br><span class="stext">Anzahl der Einträge die max. ausgegeben werden, 0 = alle. Newsfeed liefern nicht mehr als 15 Einträge!</td> 
   <td class="mbox"><input type="text" name="int1" size="3"></td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
  </tr>
 </table>
</form> 
<?php
	echo foot();
}
elseif ($job == 'feed_add2') {
	echo head();

	if ($_POST['temp1'] == '') {
		error('admin.php?action=cms&job=feed_add'.SID2URL_x, 'Keinen Titel angegeben');
	}
	if ($_POST['temp2'] == '') {
		error('admin.php?action=cms&job=feed_add'.SID2URL_x, 'Keine Newsfeed-URL angegeben');
	}
	if ($_POST['int1'] == '') {
		$_POST['int1'] = 0;
	}
	
	$db->query('INSERT INTO '.$db->pre.'grab (title, file, entries) VALUES ("'.$_POST['temp1'].'","'.$_POST['temp2'].'","'.$_POST['int1'].'")');

	$scache = new scache('grabrss');
	$scache->deletedata();

	ok('admin.php?action=cms&job=feed'.SID2URL_x, 'Newsfeed eingefügt');
}
elseif ($job == 'feed_delete') {
	echo head();
	$delete = $gpc->get('delete', arr_int);
	if (count($delete) > 0) {
		$deleteids = array();
		foreach ($delete as $did) {
			$deleteids[] = 'id = '.$did; 
		}

		$sql = 'DELETE FROM '.$db->pre.'grab WHERE '.implode(' OR ',$deleteids);
		$db->query($sql);
		$anz = $db->affected_rows();
		
		$scache = new scache('grabrss');
		$scache->deletedata();
			
		ok('admin.php?action=cms&job=feed'.SID2URL_x, $anz.' Newsfeeds gelöscht');
	}
	else {
		error('admin.php?action=cms&job=feed'.SID2URL_x, 'Keine Eingabe gemacht');
	}
}
elseif ($job == 'feed_edit') {
echo head();
if ($_GET['id'] < 1) {
	error('admin.php?action=cms&job=feed'.SID2URL_x, 'Keine gültige ID übergeben');
}
$result = $db->query('SELECT * FROM '.$db->pre.'grab WHERE id = '.$_GET['id']);
$row = $db->fetch_assoc($result);

?>
<form name="form" method="post" action="admin.php?action=cms&job=feed_edit2&id=<?php echo $_GET['id'].SID2URL_x; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2">Dokument editieren</td>
  </tr>
  <tr>
   <td class="mbox">Titel:<br><span class="stext">Falls kein Titel aus dem Newsfeed gelesen werden kann.</span></td> 
   <td class="mbox"><input type="text" name="temp1" size="60" value="<?php echo $gpc->prepare($row['title']); ?>"></td> 
  </tr>
  <tr>
   <td class="mbox">URL zum Newsfeed:<br><span class="stext">RSS 0.91, RSS 1.0, RSS 2.0 oder ATOM-Newsfeed</span></td>
   <td class="mbox"><input type="text" name="temp2" size="60" value="<?php echo $row['file']; ?>"></td>
  </tr>
  <tr>
   <td class="mbox">Anzahl der Einträge:<br><span class="stext">Anzahl der Einträge die max. ausgegeben werden, 0 = alle. Newsfeed liefern nicht mehr als 15 Einträge!</span></td> 
   <td class="mbox"><input type="text" name="int1" size="3" value="<?php echo $row['entries']; ?>"></td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Abschicken"></td> 
  </tr>
 </table>
</form> 
<?php
	echo foot();
}
elseif ($job == 'feed_edit2') {
	echo head();

	if ($_GET['id'] < 1) {
		error('admin.php?action=cms&job=feed'.SID2URL_x, 'Keine gültige ID übergeben');
	}
	if ($_POST['temp1'] == '') {
		error('admin.php?action=cms&job=feed_edit&id='.$_GET['id'].SID2URL_x, 'Keinen Titel angegeben');
	}
	if ($_POST['temp2'] == '') {
		error('admin.php?action=cms&job=feed_edit&id='.$_GET['id'].SID2URL_x, 'Keine Newsfeed-URL angegeben');
	}
	if ($_POST['int1'] == '') {
		$_POST['int1'] = 0;
	}
	
	$db->query('UPDATE '.$db->pre.'grab SET file = "'.$_POST['temp2'].'", title = "'.$_POST['temp1'].'", entries = "'.$_POST['int1'].'" WHERE id = '.$_GET['id']);

	$scache = new scache('grabrss');
	$scache->deletedata();

	ok('admin.php?action=cms&job=feed'.SID2URL_x, 'Eintrag geändert');
}
?>
