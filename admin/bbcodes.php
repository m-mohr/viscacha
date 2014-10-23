<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "bbcodes.php") die('Error: Hacking Attempt');

if ($job == 'man_smileys') {
	echo head();
	$fd = $gpc->get('fdelete');
	$deleteid = $gpc->get('delete', arr_int);
	$fe = $gpc->get('fedit');
	$editid = $gpc->get('edit', arr_int);
	if (!empty($fd) && count($deleteid) > 0) {
	   	$scache = new scache('smileys');
	   	$scache->deletedata();
		$db->query('DELETE FROM '.$db->pre.'smileys WHERE id IN ('.implode(',', $deleteid).')',__LINE__,__FILE__);
		$anz = $db->affected_rows();
	
		ok('admin.php?action=bbcodes&job='.$gpc->get('temp4', str), $anz.' entries were deleted successfully!');
	}
	elseif (!empty($fe) && count($editid) > 0) {
		
		$result = $db->query('SELECT * FROM '.$db->pre.'smileys WHERE id IN ('.implode(',', $editid).')',__LINE__,__FILE__);
		?>
<form name="form" method="post" enctype="multipart/form-data" action="admin.php?action=bbcodes&job=edit_smiley">
 <table class="border">
  <tr> 
   <td class="obox">Edit <?php echo count($editid); ?> Smileys</td>
  </tr>
  <tr> 
   <td class="ubox" align="center"><input type="submit" name="Submit" value="Send"></td> 
  </tr>
 </table><br />
 <?php while($row = $db->fetch_assoc($result)) { ?>
 <input type="hidden" name="id[]" value="<?php echo $row['id']; ?>">
 <table class="border">
  <tr> 
   <td class="mbox" width="50%">Code:</td>
   <td class="mbox" width="50%"><input type="text" name="search_<?php echo $row['id']; ?>" size="50" value="<?php echo $row['search']; ?>"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Image:<br><font class="stext">URL or relative path to the image. Only when you do not upload an image.<br />{folder} is a placeholder for the adresses to the smiley directories.</font></td>
   <td class="mbox" width="50%"><input type="text" name="replace_<?php echo $row['id']; ?>" size="50" value="<?php echo $row['replace']; ?>"></td> 
  </tr>
  <tr>
   <td class="mbox" width="50%">Description:<br><font class="stext">Optional</font></td>
   <td class="mbox" width="50%"><input type="text" name="desc_<?php echo $row['id']; ?>" size="50" value="<?php echo $row['desc']; ?>"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Show directly:<br><font class="stext">Gibt an ob der Smiley direkt zum schnellen klicken neben den BB-Codes angezeigt wird oder erst im Popup-Menü.</font></td>
   <td class="mbox" width="50%"><input type="checkbox" name="show_<?php echo $row['id']; ?>" value="1"<?php echo iif($row['show'] == 1, ' checked="checked"'); ?>></td> 
  </tr>
 </table><br />
 <?php } ?>
 <table class="border">
  <tr> 
   <td class="ubox" align="center"><input type="submit" name="Submit" value="Send"></td> 
  </tr>
 </table>
</form> 
		<?php
	}
	else {
		error('admin.php?action=bbcodes&job=smiley', 'Keine (gültige) Eingabe gemacht!');
	}
}
elseif ($job == 'edit_smiley') {
	echo head();
	$id = $gpc->get('id', arr_int);
	foreach ($id as $i) {
		$search = $gpc->get('search_'.$i, str);
		$replace = $gpc->get('replace_'.$i, str);
		$desc = $gpc->get('desc_'.$i, str);
		$show = $gpc->get('show_'.$i, int);
		$db->query("UPDATE {$db->pre}smileys AS s SET s.search = '{$search}', s.replace = '{$replace}', s.desc = '{$desc}', s.show = '{$show}' WHERE s.id = '{$i}' LIMIT 1",__LINE__,__FILE__);
	}
	$scache = new scache('smileys');
	$scache->deletedata();
	ok('admin.php?action=bbcodes&job=smiley', count($id).' Smileys wurden editiert.');
}
elseif ($job == 'smiley') {
	echo head();
	
	$result = $db->query("SELECT id, name, smileyfolder, smileypath FROM {$db->pre}designs WHERE publicuse = '1'",__LINE__,__FILE__);
	$design = array();
	$folders = array();
	$des = array();
	while ($row = $db->fetch_assoc($result)) {
		$design[$row['id']] = $row;
		$row['smileyfolder'] = str_replace('{folder}', $config['furl'], $row['smileyfolder']);
		$folders[$row['id']] = $row['smileyfolder'];
		$md5 = $row['smileyfolder'];
		if (!isset($des[$md5])) {
			$des[$md5] = array();
		}
		$des[$md5][] = $row['id'];
	}
	$folders = array_unique($folders);
	
	$result = $db->query("SELECT * FROM {$db->pre}smileys AS s ORDER BY s.show DESC");
?>
<form name="form" method="post" action="admin.php?action=bbcodes&job=man_smileys">
<input name="temp4" value="smiley" type="hidden">
 <table class="border">
  <tr> 
   <td class="obox" colspan="<?php echo 6+count($folders); ?>">Manage Smileys</td>
  </tr>
  <tr class="ubox">
   <td width="5%" rowspan="2">DEL</td>
   <td width="5%" rowspan="2">Edit</td>
   <td width="10%" rowspan="2">Code</td> 
   <td width="30%" rowspan="2">URL</td>
   <td width="10%" colspan="<?php echo count($folders); ?>">Images/Designs</td>
   <td width="5%" rowspan="2">Show directly</td> 
   <td width="35%" rowspan="2">Description</td> 
  </tr>
  <tr class="ubox">
  <?php foreach ($des as $id) { ?>
   <td align="center"><?php echo implode(',', $id); ?></td>
  <?php } ?>
  </tr>
<?php
	while ($row = $db->fetch_assoc($result)) {
		$imgsrc = array();
		foreach ($folders as $id => $url) {
			$imgsrc[$id] = str_replace('{folder}', $url, $row['replace']);
		}
?> 
  <tr class="mbox">
   <td width="5%"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
   <td width="5%"><input type="checkbox" name="edit[]" value="<?php echo $row['id']; ?>"></td>
   <td width="10%"><?php echo $row['search']; ?></td> 
   <td width="30%"><?php echo $row['replace']; ?></td>
   <?php foreach ($imgsrc as $design => $src) { ?>
   <td align="center">
   <img src="<?php echo $src; ?>" alt="Design: <?php echo $design; ?>" border="0" />&nbsp;
   </td>
   <?php } ?>
   <td width="5%" align="center"><?php echo noki($row['show'], ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=bbcodes&job=ajax_smileypos&id='.$row['id'].'\')"'); ?></td> 
   <td width="35%"><?php echo $row['desc']; ?></td> 
  </tr>
<?php } ?>
  <tr> 
   <td class="ubox" colspan="<?php echo 6+count($folders); ?>" align="center"><input type="submit" name="fdelete" value="Delete"> <input type="submit" name="fedit" value="Edit"></td> 
  </tr>
 </table>
</form>
<br>
<form name="form" method="post" enctype="multipart/form-data" action="admin.php?action=bbcodes&job=add_smiley">
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Add Smiley</td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Code:</td>
   <td class="mbox" width="50%"><input type="text" name="code" size="50"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Image:<br><font class="stext">URL or relative path to the image. Only when you do not upload an image.<br />{folder} = <?php echo $my->smileyfolder; ?></font></td>
   <td class="mbox" width="50%"><input type="text" name="img" size="50"></td> 
  </tr>
   <td class="mbox" width="50%">Upload an image<br><font class="stext">Erlaubte Dateitypen: .gif, .jpg, .jpeg, .png, .jpe, .bmp<br />Maximale Dateigröße: 200 KB</font></td>
   <td class="mbox" width="50%"><input type="file" name="upload_0" size="40" /></td>
  <tr> 
   <td class="mbox" width="50%">Description:<br><font class="stext">Optional</font></td>
   <td class="mbox" width="50%"><input type="text" name="desc" size="50"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Show directly:<br><font class="stext">Gibt an ob der Smiley direkt zum schnellen klicken neben den BB-Codes angezeigt wird oder erst im Popup-Menü.</font></td>
   <td class="mbox" width="50%"><input type="checkbox" name="show" value="1"></td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Add"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'ajax_smileypos') {
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT b.show FROM {$db->pre}smileys AS b WHERE id = '{$id}' LIMIT 1",__LINE__,__FILE__);
	$use = $db->fetch_assoc($result);
	$use = invert($use['show']);
	$db->query("UPDATE {$db->pre}smileys AS b SET b.show = '{$use}' WHERE id = '{$id}' LIMIT 1",__LINE__,__FILE__);
    $scache = new scache('smileys');
    $scache->deletedata();
	die(strval($use));
}
elseif ($job == 'add_smiley') {
	echo head();
	$error = array();
	
	$ups = 1;
	$filesize = 200*1024;
	$filetypes = array('.gif', '.jpg', '.png', '.bmp', '.jpeg', '.jpe');
	$path = 'temp/';
	$dir = realpath($path);

	$insertuploads = array();
	$inserterrors = array();
	require("classes/class.upload.php");
	
	$img = $gpc->get('img', str);
	
	$has_upload = false;

	$result = $db->query("SELECT id, name, smileyfolder, smileypath FROM {$db->pre}designs WHERE publicuse = '1'",__LINE__,__FILE__);
	$folders = array();
	$folders2 = array();
	while ($row = $db->fetch_assoc($result)) {
		$row['smileypath'] = str_replace('{folder}', $config['fpath'], $row['smileypath']);
		$row['smileyfolder'] = str_replace('{folder}', $config['furl'], $row['smileyfolder']);
		$folders[] = $row['smileypath'];
		$folders2[] = $row['smileyfolder'];
	}
	$folders = array_unique($folders);
	$folders2 = array_unique($folders2);
	
	for ($i = 0; $i < $ups; $i++) {
	    if (empty($_FILES['upload_'.$i]['name'])) {
	    	continue;
	    }
	 
	    $my_uploader = new uploader();
		$my_uploader->max_filesize($filesize);
		if (isset($imgwidth) && isset($imgheight)) {
			$my_uploader->max_image_size($imgwidth, $imgheight);
		}
		if ($my_uploader->upload('upload_'.$i, $filetypes)) {
			$my_uploader->save_file($dir, 2);
			if ($my_uploader->return_error()) {
				$error[] = $my_uploader->return_error();
			}
			else {
				$has_upload = $gpc->save_str($my_uploader->fileinfo('name'));
			}
		}
		else {
			$error[] = $my_uploader->return_error();
		}
	}
	if (strlen($gpc->get('code', str)) < 2) {
		$error[] = 'Code ist zu kurz';
	}
	if (!$has_upload && strlen($img) < 5) {
		$error[] = 'Imagepfad zu kurz';
	}
	if (strlen($gpc->get('show', int)) != 1 && $gpc->get('show', int) != 0) {
		$error[] = 'Falsche Angabe(n)';
	}
	if (count($error) > 0) {
	    error('admin.php?action=bbcodes&job=smiley', $error);
	}
	if ($has_upload) {
		foreach ($folders as $dest) {
			$filesystem->copy($path.$has_upload, $dest.'/'.$has_upload);
		}
		$img = '{folder}/'.$has_upload;
	}
	else {
		foreach ($folders2 as $dir) {
			$img = str_replace($dir, '{folder}', $img);
		}
	}
	$db->query("INSERT INTO {$db->pre}smileys (`search`,`replace`,`desc`,`show`) VALUES ('".$gpc->get('code', str)."','".$img."','".$gpc->get('desc', str)."','".$gpc->get('show', int)."')",__LINE__,__FILE__);

    $scache = new scache('smileys');
    $scache->deletedata();

	ok('admin.php?action=bbcodes&job=smiley', 'Smiley was successfully added');
}
elseif ($job == 'word') {
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}bbcode WHERE type = 'word'",__LINE__,__FILE__);
?>
<form name="form" method="post" action="admin.php?action=bbcodes&job=del">
<input name="temp4" value="word" type="hidden">
 <table class="border">
  <tr> 
   <td class="obox" colspan=4>Manage Glossary</b></td>
  </tr>
  <tr>
   <td class="ubox" width="5%">Delete</td>
   <td class="ubox" width="15%">Abbreviation</td> 
   <td class="ubox" width="30%">Phrase</td>
   <td class="ubox" width="50%">Description</td> 
  </tr>
<?php while ($row = $db->fetch_assoc($result)) { ?> 
  <tr>
   <td class="mbox" width="5%"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
   <td class="mbox" width="15%"><?php echo $row['search']; ?></td> 
   <td class="mbox" width="30%"><?php echo $row['replace']; ?></td>
   <td class="mbox" width="50%"><?php echo $row['desc']; ?></td> 
  </tr>
<?php } ?>
  <tr> 
   <td class="ubox" width="100%" colspan=4 align="center"><input type="submit" name="Submit" value="Delete"></td> 
  </tr>
 </table>
</form>
<br>
<form name="form" method="post" action="admin.php?action=bbcodes&job=add">
<input name="temp4" value="word" type="hidden">
 <table class="border">
  <tr> 
   <td class="obox" colspan=2>Add Word</b></td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Abbreviation:</td>
   <td class="mbox" width="50%"><input type="text" name="temp1" size="50"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Phrase:</td>
   <td class="mbox" width="50%"><input type="text" name="temp2" size="50"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Description:</td>
   <td class="mbox" width="50%"><textarea name="temp3" cols="50" rows="3"></textarea></td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Add"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'censor') {
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}bbcode WHERE type = 'censor'",__LINE__,__FILE__);
?>
<form name="form" method="post" action="admin.php?action=bbcodes&job=del">
<input name="temp4" value="censor" type="hidden">
 <table class="border">
  <tr> 
   <td class="obox" colspan=3>Manage Censorship</b></td>
  </tr>
  <tr>
   <td class="ubox" width="10%">Delete</td>
   <td class="ubox" width="45%">Word</td> 
   <td class="ubox" width="45%">Censored Word</td>
  </tr>
<?php while ($row = $db->fetch_assoc($result)) { ?> 
  <tr>
   <td class="mbox" width="10%"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
   <td class="mbox" width="45%"><?php echo $row['search']; ?></td> 
   <td class="mbox" width="45%"><?php echo $row['replace']; ?></td>
  </tr>
<?php } ?>
  <tr> 
   <td class="ubox" width="100%" colspan=3 align="center"><input type="submit" name="Submit" value="Delete"></td> 
  </tr>
 </table>
</form>
<br>
<form name="form" method="post" action="admin.php?action=bbcodes&job=add">
<input name="temp4" value="censor" type="hidden">
 <table class="border">
  <tr> 
   <td class="obox" colspan=2>Add Word</b></td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Word:</td>
   <td class="mbox" width="50%"><input type="text" name="temp1" size="50"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Censored Word:</td>
   <td class="mbox" width="50%"><input type="text" name="temp2" size="50"></td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Add"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'replace') {
	echo head();
	$result = $db->query("SELECT * FROM {$db->pre}bbcode WHERE type = 'replace'",__LINE__,__FILE__);
?>
<form name="form" method="post" action="admin.php?action=bbcodes&job=del">
<input name="temp4" value="replace" type="hidden">
 <table class="border">
  <tr> 
   <td class="obox" colspan=3>Manage Vocabulary</b></td>
  </tr>
  <tr>
   <td class="ubox" width="10%">Delete</td>
   <td class="ubox" width="45%">Word</td> 
   <td class="ubox" width="45%">Replacement</td>
  </tr>
<?php while ($row = $db->fetch_assoc($result)) { ?> 
  <tr>
   <td class="mbox" width="10%"><input type="checkbox" name="delete[]" value="<?php echo $row['id']; ?>"></td>
   <td class="mbox" width="45%"><?php echo $row['search']; ?></td> 
   <td class="mbox" width="45%"><?php echo $row['replace']; ?></td>
  </tr>
<?php } ?>
  <tr> 
   <td class="ubox" width="100%" colspan=3 align="center"><input type="submit" name="Submit" value="Delete"></td> 
  </tr>
 </table>
</form>
<br>
<form name="form" method="post" action="admin.php?action=bbcodes&job=add">
<input name="temp4" value="replace" type="hidden">
 <table class="border">
  <tr> 
   <td class="obox" colspan=2>Add Word</b></td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Word:</td>
   <td class="mbox" width="50%"><input type="text" name="temp1" size="50"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Replacement:</td>
   <td class="mbox" width="50%"><input type="text" name="temp2" size="50"></td> 
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan=2 align="center"><input type="submit" name="Submit" value="Add"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'add') {
	echo head();
	$type = $gpc->get('temp4', str);
	
	$error = array();
	if ($type != 'word' && $type != 'censor' && $type != 'replace') {
		$error[] = "Kein gültiger Typ angegeben";
	}
	if (strlen($gpc->get('temp1', str)) < 2) {
		$error[] = "Angabe 1 zu kurz";
	}
	if (strlen($gpc->get('temp2', str)) < 2) {
		$error[] = "Angabe 2 zu kurz";
	}
	if (strlen($gpc->get('temp3', str)) < 2 && $type == 'word') {
		$error[] = "Beschreibung zu kurz";
	}
	if (count($error) > 0) {
		error('admin.php?action=bbcodes&job='.$type, $error);
	}
	
	$db->query("INSERT INTO {$db->pre}bbcode (`search`,`replace`,`type`,`desc`) VALUES ('".$gpc->get('temp1', str)."','".$gpc->get('temp2', str)."','{$type}','".$gpc->get('temp3', str)."')",__LINE__,__FILE__);

	$scache = new scache('bbcode');
	$scache->deletedata();

	ok('admin.php?action=bbcodes&job='.$type, 'Daten wurden hinzugefügt');
}
elseif ($job == 'del') {
	echo head();
	$delete = $gpc->get('delete', arr_int);
	$type = $gpc->get('temp4', str);
	if (count($delete) > 0) {
		error('admin.php?action=bbcodes&job='.$type, 'Sie haben keine gültige Auswahl getroffen.');
	}
	$db->query('DELETE FROM '.$db->pre.'bbcode WHERE id IN ('.implode(',',$delete).')',__LINE__,__FILE__);
	$anz = $db->affected_rows();
    $scache = new scache('bbcode');
    $scache->deletedata();
	ok('admin.php?action=bbcodes&job='.$type, $anz.' entries were deleted successfully!');
}
elseif ($job == 'codefiles') {
	echo head();
	include_once('classes/class.geshi.php');
	$clang = array();
	$d = dir("classes/geshi");
	while (false !== ($entry = $d->read())) {
		if (get_extension($entry,TRUE) == 'php' && !is_dir("classes/geshi/".$entry)) {
			include_once("classes/geshi/".$entry);
			$short = str_replace('.php','',$entry);
			$clang[$short]['file'] = $entry;
			$clang[$short]['name'] = $language_data['LANG_NAME'];
		}
	}
	$d->close();
	asort($clang);
?>
<form name="form" method="post" action="admin.php?action=bbcodes&job=del_codefiles">
 <table class="border">
  <tr> 
   <td class="obox" colspan="3">Syntax Highlighting Manager (<?php echo count($clang); ?> Languages)</b></td>
  </tr>
  <tr>
   <td class="ubox" width="10%">Delete</td>
   <td class="ubox" width="45%">Language</td> 
   <td class="ubox" width="45%">File</td>
  </tr>
<?php foreach ($clang as $row) { ?> 
  <tr>
   <td class="mbox" width="10%"><input type="checkbox" name="delete[]" value="<?php echo $row['file']; ?>"></td>
   <td class="mbox" width="45%"><?php echo $row['name']; ?></td> 
   <td class="mbox" width="45%"><?php echo $row['file']; ?></td>
  </tr>
<?php } ?>
  <tr> 
   <td class="ubox" width="100%" colspan="3" align="center"><input type="submit" name="Submit" value="Delete"></td> 
  </tr>
 </table>
</form>
<br>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=explorer&job=upload&cfg=codefiles">
<table class="border">
<tr><td class="obox">Add Syntax Highlighting Files</td></tr>
<tr><td class="mbox">
Um eine Datei hinzuzufügen, klicken Sie auf die "Durchsuchen"-Schaltfläche und wählen Sie eine Datei aus.
Klicken Sie dann auf "Senden", um den Vorgang abzuschließen.<br /><br />
Erlaubte Dateitypen: .php<br />
Maximale Dateigröße: 200 KB<br /><br />
<strong>Datei hochladen:</strong>
<br /><input type="file" name="upload_0" size="40" />
</td></tr>
<tr><td class="ubox" align="center"><input accesskey="s" type="submit" value="Upload" /></td></tr>
</table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'del_codefiles') {
	echo head();
	$d = $gpc->get('delete', arr_str);
	foreach ($d as $file) {
		$file = 'classes/geshi/'.$file;
		if (file_exists($file)) {
			$filesystem->unlink($file);
		}
	}
    $scache = new scache('syntax-highlight');
    $scache->deletedata();
    ok('admin.php?action=bbcodes&job=codefiles', 'Dateien wurden gelöscht');
}
?>
