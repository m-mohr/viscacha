<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "language.php") die('Error: Hacking Attempt');

include('classes/class.phpconfig.php');

$langbase = array(
	'global' => 'Global/Standard',
	'modules' => 'Module',
	'javascript' => 'JavaScript',
	'wwo' => 'Wer ist Wo online',
	'thumbnail.class' => 'Grafik-Klasse',
	'phpmailer.class' => 'E-Mail-Klasse'
);

require('lib/language.inc.php');

if ($job == 'manage') {
	echo head();
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="6">Language Files</td>
  </tr>
  <tr>
   <td class="ubox" width="18%">Language</td>
   <td class="ubox" width="43%">Description</td>
   <td class="ubox" width="5%">Published</td>
   <td class="ubox" width="34%">Action</td>
  </tr>
  <?php while ($row = $db->fetch_assoc($result)) { ?>
  <tr>
   <td class="mbox"><?php echo $row['language'].iif($config['langdir'] == $row['id'], '<br /><span class="stext">Default</span>'); ?></td>
   <td class="mbox stext"><?php echo $row['detail']; ?></td>
   <td class="mbox" align="center"><?php echo noki($row['publicuse'], ' onmouseover="HandCursor(this)" onclick="ajax_noki(this, \'action=language&job=ajax_publicuse&id='.$row['id'].'\')"'); ?></td>
   <td class="mbox">
   [<a href="admin.php?action=language&amp;job=lang_edit&amp;id=<?php echo $row['id']; ?>">Edit</a>]
   [<a href="admin.php?action=language&amp;job=lang_copy&amp;id=<?php echo $row['id']; ?>" title="You can copy this language pack to translate it later.">Copy</a>]
   [<a href="admin.php?action=language&amp;job=export&amp;id=<?php echo $row['id']; ?>">Export</a>]
   [<a href="admin.php?action=language&amp;job=lang_delete&amp;id=<?php echo $row['id']; ?>">Delete</a>]
   <?php if ($row['publicuse'] == 1 && $config['langdir'] != $row['id']) { ?>
   [<a href="admin.php?action=language&amp;job=lang_default&amp;id=<?php echo $row['id']; ?>">Set as default</a>]
   <?php } ?>
   [<a href="forum.php?language=<?php echo $row['id']; ?>" target="_blank">View</a>]
   </td>
  </tr>
  <?php } ?>
 </table>
	<?php
	echo foot();
}
elseif ($job == 'ajax_publicuse') {
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT publicuse FROM {$db->pre}language WHERE id = '{$id}' LIMIT 1");
	$use = $db->fetch_assoc($result);
	if ($use['publicuse'] == 1) {
		if ($id == $config['langdir']) {
			die('You can not unpublish this language until you have defined another default language.');
		}
		$db->query("SELECT * FROM {$db->pre}language WHERE publicuse = '1'");
		if ($db->num_rows() == 1) {
			die('You can not unpublish this language, because no other language is published.');
		}
	}
	$use = invert($use['publicuse']);
	$db->query("UPDATE {$db->pre}language SET publicuse = '{$use}' WHERE id = '{$id}' LIMIT 1");
	$delobj = $scache->load('loadlanguage');
	$delobj->delete();
	die(strval($use));
}
elseif ($job == 'import') {
	echo head();
	$result = $db->query('SELECT id, language FROM '.$db->pre.'language ORDER BY language');
	?>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=language&job=import2">
 <table class="border" cellpadding="4" cellspacing="0" border="0">
  <tr><td class="obox" colspan="2">Import Languagepack</td></tr>
  <tr><td class="mbox"><em>Either</em> upload a file:<br /><span class="stext">Allowed file types: .zip - Maximum file size: 1 MB</span></td>
  <td class="mbox"><input type="file" name="upload" size="40" /></td></tr>
  <tr><td class="mbox"><em>or</em> select a file from the server:<br /><span class="stext">Path starting from the Viscacha-root-directory: <?php echo $config['fpath']; ?></span></td>
  <td class="mbox"><input type="text" name="server" size="50" /></td></tr>
  <tr><td class="mbox">Overwrite Language:<br /><span class="stext">Selecting a language here will cause the imported language to overwrite an existing language. Leave blank to create a new language.</span></td>
  <td class="mbox"><select name="overwrite">
    <option value="0">- Create a new language -</option>
   <?php while ($row = $db->fetch_assoc($result)) { ?>
    <option value="<?php echo $row['id']; ?>"><?php echo $row['language']; ?></option>
   <?php } ?>
  </select></td></tr>
  <tr><td class="mbox">Delete file after import:</td>
  <td class="mbox"><input type="checkbox" name="delete" value="1" checked="checked" /></td></tr>
  <tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" value="Send" /></td></tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'import2') {

	$overwrite = $gpc->get('overwrite', int);
	$server = $gpc->get('server', none);
	$del = $gpc->get('delete', int);
	$inserterrors = array();
	
	if (!empty($_FILES['upload']['name'])) {
		$filesize = 1024*1024;
		$filetypes = array('.zip');
		$dir = realpath('temp/');
	
		$insertuploads = array();
		require("classes/class.upload.php");
		 
		$my_uploader = new uploader();
		$my_uploader->max_filesize($filesize);
		if ($my_uploader->upload('upload', $filetypes)) {
			$my_uploader->save_file($dir, 2);
			if ($my_uploader->return_error()) {
				array_push($inserterrors,$my_uploader->return_error());
			}
		}
		else {
			array_push($inserterrors,$my_uploader->return_error());
		}
		$file = $dir.'/'.$my_uploader->file['name'];
		if (!file_exists($file)) {
			$inserterrors[] = 'File ('.$file.') does not exist.';
		}
	}
	elseif (file_exists($server)) {
		$ext = get_extension($server, true);
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
	echo head();
	if (count($inserterrors) > 0) {
		error('admin.php?action=language&job=import', $inserterrors);
	}
	
	$tempdir = 'temp/'.md5(microtime()).'/';
	
	require_once('classes/class.zip.php');
	$archive = new PclZip($file);
	$failure = $archive->extract($tempdir);
	if ($failure < 1) {
		unset($archive);
		if ($del == 1) {
			$filesystem->unlink($file);
		}
		rmdirr($tempdir);
		error('admin.php?action=language&job=import', 'ZIP-archive could not be read or the folder is empty.');
	}

	$inserted = false;
	if ($overwrite == 0) {
		$db->query("INSERT INTO {$db->pre}language (language, detail) VALUES ('New Languagepack', 'If you see this text, then something went wrong while importing a language pack. You can delete this language pack.')");
		$inserted = true;
		$overwrite = $db->insert_id();
	}
	$newdir = "language/{$overwrite}/";
	
	copyr($tempdir, $newdir);
	rmdirr($tempdir);
	
	$info = return_array('settings', $overwrite);
	if (isset($info['lang_name'])) {
		$db->query("UPDATE {$db->pre}language SET language = '{$info['lang_name']}', detail = '{$info['lang_description']}' WHERE id = '{$overwrite}' LIMIT 1");
		unset($archive);
		if ($del == 1) {
			$filesystem->unlink($file);
		}
		$delobj = $scache->load('loadlanguage');
		$delobj->delete();
		ok('admin.php?action=language&job=manage', 'Languagepack import successful.');
	}
	else {
		if ($inserted) {
			$db->query("DELETE FROM {$db->pre}language WHERE id = '{$overwrite}' LIMIT 1");
		}
		unset($archive);
		if ($del == 1) {
			$filesystem->unlink($file);
		}
		error('admin.php?action=language&job=import', 'Languagepack could not be imported. Probably the files could not be copied successful, the settings.lng.php is missing or it is damaged.');
	}
}
elseif ($job == 'export') {
	$id = $gpc->get('id', int);
	
	$result = $db->query('SELECT language, detail FROM '.$db->pre.'language WHERE id = "'.$id.'" LIMIT 1');
	$row = $db->fetch_assoc($result);
	
	$file = convert2adress($row['language'].'-v'.str_replace(' ', '', $config['version'])).'.zip';
	$dir = "language/{$id}/";
	$tempdir = "temp/";
	
	require_once('classes/class.zip.php');
	$archive = new PclZip($tempdir.$file);
	$v_list = $archive->create($dir, PCLZIP_OPT_REMOVE_PATH, $dir, PCLZIP_OPT_COMMENT, "{$row['language']}\n\n{$row['detail']}\n\nVersion: {$config['version']}");
	if ($v_list == 0) {
		echo head();
		unset($archive);
		if ($del > 0) {
			$filesystem->unlink($tempdir.$file);
		}
		error('admin.php?action=language&job=manage', $archive->errorInfo(true));
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
elseif ($job == 'lang_copy') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=language&job=lang_copy2&id=<?php echo $gpc->get('id', int); ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="6">Copy language file</td>
  </tr>
  <tr>
   <td class="mbox" width="40%">Name for the new languagepack:</td>
   <td class="mbox" width="60%"><input type="text" name="name" size="60" /></td>
  </tr>
  <tr>
   <td class="mbox" width="40%">Description for the languagepack:</td>
   <td class="mbox" width="60%"><textarea name="desc" rows="3" cols="70"></textarea></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Copy" /></td> 
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'lang_copy2') {
	echo head();
	$id = $gpc->get('id', int);
	$name = $gpc->get('name', str);
	$desc = $gpc->get('desc', str);
	$db->query("INSERT INTO {$db->pre}language (language, detail) VALUES ('{$name}', '{$desc}')");
	$newid = $db->insert_id();
	$filesystem->mkdir("language/{$newid}/", 0755);
	copyr("language/{$id}/", "language/{$newid}/");
	$delobj = $scache->load('loadlanguage');
	$delobj->delete();
	ok('admin.php?action=language&job=manage', 'Languagepack has been copied successful.');
}
elseif ($job == 'lang_delete') {
	echo head();
	$id = $gpc->get('id', int);
	?>
	<table class="border" border="0" cellspacing="0" cellpadding="4">
	<tr><td class="obox">Delete languagepack</td></tr>
	<tr><td class="mbox">
	<p align="center">Would you really like to delete this language pack?</p>
	<p align="center">
	<a href="admin.php?action=language&job=lang_delete2&id=<?php echo $id; ?>"><img border="0" align="middle" alt="" src="admin/html/images/yes.gif"> Yes</a>
	&nbsp&nbsp;&nbsp;&nbsp&nbsp;&nbsp;
	<a href="javascript: history.back(-1);"><img border="0" align="middle" alt="" src="admin/html/images/no.gif"> No</a>
	</p>
	</td></tr>
	</table>
	<?php
	echo foot();
}
elseif ($job == 'lang_delete2') {
	echo head();
	$id = $gpc->get('id', int);
	$db->query("DELETE FROM {$db->pre}language WHERE id = '{$id}' LIMIT 1");
	if ($db->affected_rows() == 1) {
		rmdirr("language/{$id}/");
		$delobj = $scache->load('loadlanguage');
		$delobj->delete();
		ok('admin.php?action=language&job=manage', 'Languagepack has been deleted successful.');
	}
	else {
		error('admin.php?action=language&job=manage', 'Languagepack could not be deleted.');
	}
}
elseif ($job == 'lang_settings') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query("SELECT language, detail, publicuse FROM {$db->pre}language WHERE id = '{$id}' LIMIT 1");
	$data = $gpc->prepare($db->fetch_assoc($result));
	$settings = $gpc->prepare(return_array('settings', $id));
	if (empty($settings['html_read_direction'])) {
		$settings['html_read_direction'] = 'ltr';
	}
	if (empty($settings['rss_language'])) {
		$settings['rss_language'] = 'en';
	}
	
	$rsslang = file2array('admin/data/rss_language.php');
	?>
<script language="JavaScript">
<!--
function errordefault(box) {
	alert('You can not unpublish this language until you have defined another default language.');
	box.checked = true;
	return false;
}
-->
</script>
<form name="form" method="post" action="admin.php?action=language&job=lang_settings2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr> 
   <td class="obox" colspan="2">Edit language file &raquo; settings</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Name for the languagepack:</td>
   <td class="mbox" width="50%"><input type="text" name="language" size="50" value="<?php echo $data['language']; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Description for the languagepack:</td>
   <td class="mbox" width="50%"><textarea name="desc" rows="3" cols="60"><?php echo $data['detail']; ?></textarea></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Compatible with version:<br /><span class="stext">Your current Viscacha-Version: <?php echo $config['version']; ?></span></td>
   <td class="mbox" width="50%"><input type="text" name="compatible_version" size="20" value="<?php echo isset($settings['compatible_version']) ? $settings['compatible_version'] : $config['version']; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Languagepack is public usable:</td>
   <td class="mbox" width="50%"><input<?php echo iif($config['langdir'] == $id, ' onclick="errordefault(this)"'); ?> type="checkbox" name="use" value="1"<?php echo iif($data['publicuse'] == 1, ' checked="checked"'); ?> /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Writing direction:</td>
   <td class="mbox" width="50%">
   <select name="html_read_direction">
   <option value="ltr"<?php echo iif($settings['html_read_direction'] == 'ltr', ' selected="selected"'); ?>>ltr: From left to right</option>
   <option value="rtl"<?php echo iif($settings['html_read_direction'] == 'rtl', ' selected="selected"'); ?>>rtl: From right to left</option>
   </select>
  </tr>
  <tr>
   <td class="mbox" width="50%">RSS language symbols:</td>
   <td class="mbox" width="50%">
   <select name="rss_language">
   <?php foreach ($rsslang as $key => $val) { ?>
   <option value="<?php echo $key; ?>"<?php echo iif($settings['rss_language'] == $key, ' selected="selected"'); ?>><?php echo $val; ?></option>
   <?php } ?>
   </select>
  </tr>
  <tr>
   <td class="mbox" width="50%">Symbol for spell check:<br /><span class="stext">Consists of the two-letter ISO 639-language-code and, after underscore, a optional two-letter ISO 3166-country-code.</span></td>
   <td class="mbox" width="50%"><input type="text" name="spellcheck_dict" size="8" value="<?php echo isset($settings['spellcheck_dict']) ? $settings['spellcheck_dict'] : ''; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Thousand seperator:</td>
   <td class="mbox" width="50%"><input type="text" name="thousandssep" size="2" value="<?php echo isset($settings['thousandssep']) ? $settings['thousandssep'] : ','; ?>" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Decimal seperator:</td>
   <td class="mbox" width="50%"><input type="text" name="decpoint" size="2" value="<?php echo isset($settings['decpoint']) ? $settings['decpoint'] : '.'; ?>" /></td>
  </tr>
  <tr> 
   <td class="mbox" width="50%">Charset:</td>
   <td class="mbox" width="50%"><input type="text" name="charset" value="<?php echo isset($settings['charset']) ? $settings['charset'] : 'ISO-8859-1'; ?>" size="20"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Format for contributions:<br><span class="stext">For contributions, last visit etc. Abbreviation according to the PHP-function: date(). Further information: <a href="http://www.php.net/manual-lookup.php?function=date" target="_blank">PHP: date()</a></span></td>
   <td class="mbox" width="50%"><input type="text" name="dformat1" value="<?php echo isset($settings['dformat1']) ?  $settings['dformat1'] : 'd.m.Y, H:i'; ?>" size="20"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Format for the date of registry:</span><br><span class="stext">Abbreviation according to the PHP-function: date(). Further information: <a target="_blank" href="http://www.php.net/manual-lookup.php?function=date">PHP: date()</a></span></td>
   <td class="mbox" width="50%"><input type="text" name="dformat2" value="<?php echo isset($settings['dformat2']) ? $settings['dformat2'] : 'd.m.Y, H:i'; ?>" size="20"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Format for last activity (in der Online-Liste):</span><br><span class="stext">Abbreviation according to the PHP-function: date(). Further information: <a target="_blank" href="http://www.php.net/manual-lookup.php?function=date">PHP: date()</a></span></td>
   <td class="mbox" width="50%"><input type="text" name="dformat3" value="<?php echo isset($settings['dformat3']) ? $settings['dformat3'] : 'H:i'; ?>" size="20"></td> 
  </tr>
  <tr> 
   <td class="mbox" width="50%">Format, that is used after "Today" and "Yesterday":</span><br><span class="stext">Only if it is activated above! For contribitions, last visit etc. Abbreviation according to the PHP-function: date(). Further information: <a target="_blank" href="http://www.php.net/manual-lookup.php?function=date">PHP: date()</a></span></td>
   <td class="mbox" width="50%"><input type="text" name="dformat4" value="<?php echo isset($settings['dformat4']) ? $settings['dformat4'] : 'H:i'; ?>" size="20"></td> 
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Save" /></td> 
  </tr>
 </table>
</form>	
	<?php
	echo foot();
}
elseif ($job == 'lang_settings2') {
	echo head();
	$id = $gpc->get('id', int);
	$use = $gpc->get('use', int);
	$detail = $gpc->get('desc', str);
	$lang = $gpc->get('language', str);
	$error = '';
	
	$result = $db->query("SELECT publicuse FROM {$db->pre}language WHERE id = '{$id}' LIMIT 1");
	$puse = $db->fetch_assoc($result);
	if ($puse['publicuse'] == 1 && $use == 0) {
		if ($id == $config['langdir']) {
			$error .= ', but you can not unpublish this language until you have defined another default language';
			$use = 1;
		}
		$db->query("SELECT * FROM {$db->pre}language WHERE publicuse = '1'");
		if ($db->num_rows() == 1) {
			$error .= ', but you can not unpublish this language, because no other language is published';
			$use = 1;
		}
	}
	
	$db->query("UPDATE {$db->pre}language SET publicuse = '{$use}', language = '{$lang}', detail = '{$detail}' WHERE id = '{$id}' LIMIT 1");
	
	$c = new manageconfig();
	$c->getdata("language/{$id}/settings.lng.php", 'lang');
	$c->updateconfig('html_read_direction', str);
	$c->updateconfig('rss_language', str);
	$c->updateconfig('thousandssep', str);
	$c->updateconfig('decpoint', str);
	$c->updateconfig('spellcheck_dict', str);
	$c->updateconfig('lang_name', str, $lang);
	$c->updateconfig('lang_description', str, $detail);
	$c->updateconfig('compatible_version', str);
	$c->updateconfig('dformat1',str);
	$c->updateconfig('dformat2',str);
	$c->updateconfig('dformat3',str);
	$c->updateconfig('dformat4',str);
	$c->updateconfig('charset',str);
	$c->savedata();
	
	$delobj = $scache->load('loadlanguage');
	$delobj->delete();
	
	ok('admin.php?action=language&job=lang_edit&id='.$id, 'Changes were successfully changed'.$error.'.');	
}
elseif ($job == 'lang_ignore') {
	echo head();
	$id = $gpc->get('id', int);
	$file = "language/{$id}/words/search.inc.php";
	if (!file_exists($file)) {
		$ignore = '';
	}
	else {
		$ignore = file_get_contents($file);
	}
	?>
<form name="form" method="post" action="admin.php?action=language&job=lang_ignore2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr> 
   <td class="obox" colspan="2">Edit language file &raquo; ignored search keys</td>
  </tr>
  <tr>
   <td class="mbox" width="40%" valign="top">
   Here are the words listed which should be ignored during the search run to prevent the search for frequently occured words leading to 1st unmanageable many matches and 2nd an significant affect of the searching speed.<br /><br />
   Only one word in each line. Please type the words completly in lower case letters. Special symbols should be occured in two forms. Examples: <br />
   &Auml; = ae and &auml;,<br />
   &szlig; = ss and &szlig;,<br />
   &eacute; = e and &eacute;,<br />
   &Ccedil; = c and &ccedil;
   </td>
   <td class="mbox" width="60%" align="center">
   <textarea name="ignore" rows="25" cols="50"><?php echo $ignore; ?></textarea>
   </td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Save" /></td> 
  </tr>
 </table>
</form>		
	<?php
	echo foot();
}
elseif ($job == 'lang_ignore2') {
	echo head();
	
	$id = $gpc->get('id', int);
	$ignore = $gpc->get('ignore', none);
	$lines = preg_split('`[\n\r]+`', trim($ignore)) ;
	$lines = array_map('trim', $lines);
	$lines = array_map('strtolower', $lines);
	$lines = array_unique($lines);
	sort($lines);
	if (!is_dir("language/{$id}/words/")) {
		$filesystem->mkdir("language/{$id}/words/", 0777);
	}
	$filesystem->file_put_contents("language/{$id}/words/search.inc.php", implode("\n", $lines));
	
	ok('admin.php?action=language&job=lang_edit&id='.$id);	
}
elseif ($job == 'lang_rules') {
	echo head();
	$id = $gpc->get('id', int);
	$rules = $gpc->get('rules', arr_str);
	$delete = $gpc->get('delete', arr_int);
	$c = $gpc->get('c', int);
	$file = "language/{$id}/words/rules.inc.php";
	if (!file_exists($file)) {
		$rules = array();
	}
	else {
		$rules = file($file);
	}
	$i = 1;
	?>
<form name="form" method="post" action="admin.php?action=language&job=lang_rules2&id=<?php echo $id; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr> 
   <td class="obox">Edit language file &raquo; Terms of behaviour</td>
  </tr>
  <tr>
   <td class="ubox">Existing rules:</td> 
  </tr>
  <tr>
   <td class="mbox">
   <ol>
   <?php foreach ($rules as $rule) { ?>
    <li><input type="text" name="rules[<?php echo $i; ?>]" size="110" value="<?php echo $gpc->prepare($rule); ?>" />&nbsp;&nbsp;<input type="checkbox" name="delete[<?php echo $i; ?>]" value="1"> Delete</li>
   <?php $i++; } if (count($rules) == 0) { ?>
   	<li><em>No rule has been created yet!</em></li>
   <?php } ?>
   </ol>
   </td>
  </tr>
  <tr>
   <td class="ubox" align="center">Add new rule(s):</td> 
  </tr>
  <tr>
   <td class="mbox">
  <?php if ($c > 0) { ?>
   <ol start="<?php echo $i; ?>">
   <?php for($ii=1;$ii<=$c;$ii++) { ?>
   <li><input type="text" name="rules[<?php echo $ii+$i; ?>]" size="110" value="" /></li>
   <?php } ?>
   </ol>
  <?php } else { ?>
   Add <input type="text" name="c" size="3" value="0" /> new rules after saving!
  <?php } ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" align="center"><input type="submit" name="Submit" value="Save" /></td> 
  </tr>
 </table>
</form>		
	<?php
	echo foot();
}
elseif ($job == 'lang_rules2') {
	echo head();
	
	$id = $gpc->get('id', int);
	$rules = $gpc->get('rules', arr_str);
	$delete = $gpc->get('delete', arr_int);
	$c = $gpc->get('c', int);
	$newrules = array();
	foreach ($rules as $rid => $rule) {
		if (!isset($delete[$rid]) && !empty($rule)) {
			$newrules[$rid] = $rule;
		}
	}
	ksort($newrules);
	if (!is_dir("language/{$id}/words/")) {
		$filesystem->mkdir("language/{$id}/words/", 0777);
	}
	$filesystem->file_put_contents("language/{$id}/words/rules.inc.php", implode("\n", $newrules));
	
	if ($c > 0) {
		ok('admin.php?action=language&job=lang_rules&c='.$c.'&id='.$id, 'Settings were saved successfully! You can add the new rules now.');
	}
	else {
		ok('admin.php?action=language&job=lang_edit&id='.$id);
	}	
}
elseif ($job == 'lang_txttpl') {
	echo head();
	$id = $gpc->get('id', int);
	$file = $gpc->get('file', str);
	$path = "language/{$id}/texts/{$file}.php";
	if (!file_exists($path)) {
		error('admin.php?action=language&job=lang_edit&id='.$id, "The specified file does not exist: {$path}");
	}
	$tpl = file_get_contents($path);
	?>
<form name="form" method="post" action="admin.php?action=language&job=lang_txttpl2&id=<?php echo $id; ?>&file=<?php echo $file; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr> 
   <td class="obox">Edit language file &raquo; Text templates</td>
  </tr>
  <tr>
   <td class="ubox"><?php echo getLangVarsHelp(); ?></td> 
  </tr>
  <tr>
   <td class="mbox" align="center">
   <textarea name="tpl" rows="15" cols="120"><?php echo $tpl; ?></textarea>
   </td>
  </tr>
  <tr>
   <td class="ubox" align="center"><input type="submit" name="Submit" value="Save" /></td> 
  </tr>
 </table>
</form>		
	<?php
	echo foot();
}
elseif ($job == 'lang_txttpl2') {
	echo head();
	
	$id = $gpc->get('id', int);
	$file = $gpc->get('file', str);
	if (!is_dir("language/{$id}/texts/")) {
		$filesystem->mkdir("language/{$id}/texts/", 0777);
	}
	$path = "language/{$id}/texts/{$file}.php";
	if (!file_exists($path)) {
		error('admin.php?action=language&job=lang_edit&id='.$id, "The specified file does not exist: {$path}");
	}
	$tpl = $gpc->get('tpl', none);
	$filesystem->file_put_contents($path, $tpl);
	
	ok('admin.php?action=language&job=lang_edit&id='.$id);	
}
elseif ($job == 'lang_emailtpl') {
	echo head();
	$id = $gpc->get('id', int);
	$file = $gpc->get('file', str);
	$path = "language/{$id}/mails/{$file}.php";
	if (!file_exists($path)) {
		error('admin.php?action=language&job=lang_emails&id='.$id, "The specified file does not exist: {$path}");
	}
	$xml = file_get_contents($path);
    preg_match("|<title>(.+?)</title>.*?<comment>(.+?)</comment>|is", $xml, $tpl);
	?>
<form name="form" method="post" action="admin.php?action=language&job=lang_emailtpl2&id=<?php echo $id; ?>&file=<?php echo $file; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr> 
   <td class="obox" colspan="2">Edit language file &raquo; e-mail-texts &raquo; <?php echo $file; ?></td>
  </tr>
  <tr>
   <td class="ubox" width="20%">Help:</td>
   <td class="ubox" width="80%"><?php echo getLangVarsHelp(); ?></td> 
  </tr>
  <tr>
   <td class="mbox" width="20%">Subject:</td>
   <td class="mbox" width="80%"><input type="text" name="title" value="<?php echo $gpc->prepare($tpl[1]); ?>" size="80"></td>
  </tr>
  <tr>
   <td class="mbox" width="20%">Message:</td>
   <td class="mbox" width="80%"><textarea name="tpl" rows="10" cols="80"><?php echo $tpl[2]; ?></textarea></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Save" /></td> 
  </tr>
 </table>
</form>		
	<?php
	echo foot();
}
elseif ($job == 'lang_emailtpl2') {
	echo head();
	
	$id = $gpc->get('id', int);
	$file = $gpc->get('file', str);
	if (!is_dir("language/{$id}/mails/")) {
		$filesystem->mkdir("language/{$id}/mails/", 0777);
	}
	$path = "language/{$id}/mails/{$file}.php";
	if (!file_exists($path)) {
		error('admin.php?action=language&job=lang_emails&id='.$id, "The specified file does not exist: {$path}");
	}
	$tpl = $gpc->get('tpl', none);
	$title = $gpc->get('title', none);
	
	$xml = "<mail>\n\t<title>{$title}</title>\n\t<comment>{$tpl}</comment>\n</mail>";

	$filesystem->file_put_contents($path, $xml);
	
	ok('admin.php?action=language&job=lang_emails&id='.$id);	
}
elseif ($job == 'lang_emails') {
	echo head();
	$id = $gpc->get('id', int);
	$path = "language/{$id}/mails/";
	if (!is_dir($path)) {
		$filesystem->mkdir($path, 0777);
	}
	$help = file2array('admin/data/lang_email.php');
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox">Edit language file &raquo; e-mail-texts</td>
  </tr>
  <tr>
   <td class="mbox">
   <ul>
    <?php 
    $i = 0;
	$result = opendir($path);
	while (($file = readdir($result)) !== false) {
		$info = pathinfo($path.$file);
		if ($info['extension'] == 'php') {
			$n = substr($info['basename'], 0, -(strlen($info['extension']) + ($info['extension'] == '' ? 0 : 1)));
			$i++;
		?>
	   	<li><a href="admin.php?action=language&job=lang_emailtpl&id=<?php echo $id; ?>&file=<?php echo $n; ?>">
	   	<?php echo $n; ?></a><?php echo isset($help[$n]) ? "<br /><span class=\"stext\">{$help[$n]}</span>" : ''; ?>
	   	</li>
	    <?php
	    }
    }
    closedir($result);
    if ($i == 0) {
    	?>
    	<li><em>Nothing found...</em> :(</li>
    	<?php
    }
    ?>
   </ul>
   </td>
  </tr>
 </table>
	<?php
	echo foot();
}
elseif ($job == 'lang_array') {
	echo head(' onload="init()"');
	$id = $gpc->get('id', int);
	$page = $gpc->get('page', int, 1);
	$file = $gpc->get('file', str);
	$lng = return_array($file, $id);
	$lng = array_map('htmlspecialchars', $lng);
	ksort($lng);
	$lng = array_chunk($lng, 50, true);
	$pages = count($lng);
	$pages_html = "Seiten ({$pages}):";
	// Ersetzen durch Buchstaben (?) -> [A] [B] ...
	for($i=1;$i<=$pages;$i++) {
   		$pages_html .= ' ['.iif($i == $page, "<strong>{$i}</strong>", "<a href='admin.php?action=language&job=lang_array&id={$id}&file={$file}&page={$i}'>{$i}</a>").']';
	}
	
	?>
<form name="form" method="post" action="admin.php?action=language&job=lang_array2&id=<?php echo $id; ?>&file=<?php echo $file; ?>&page=<?php echo $page; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr> 
   <td class="obox" colspan="2">Edit language file &raquo; <?php echo isset($langbase[$file]) ? $langbase[$file] : ucfirst($file); ?></td>
  </tr>
  <tr>
   <td class="mbox stext" colspan="2"><?php echo getLangVarsHelp(); ?></td> 
  </tr>
  <tr>
   <td class="ubox" colspan="2"><?php echo $pages_html; ?></td> 
  </tr>
  <?php
  foreach ($lng[$page-1] as $key => $value) {
  	$word = explode('_', $key);
  	$word = array_map('ucfirst', $word);
  	$word = implode(' ', $word);
  ?>
  <tr>
   <td class="mbox" width="50%"><img name="c" id="img_lang_<?php echo $key; ?>" src="admin/html/images/plus.gif" alt=""> <?php echo $word; ?>
   <div id="part_lang_<?php echo $key; ?>" class="stext">
   <strong>Variable:</strong> <code>$lang->phrase('<?php echo $key; ?>')</code><br />
   <strong>Original:</strong> <?php echo $value; ?>
   </div>
   </td>
   <td class="mbox" width="50%"><input type="text" name="lang_<?php echo $key; ?>" size="70" value="<?php echo $value ?>" /></td>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="2" align="right">
   <?php echo $pages_html; ?>
   </td> 
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Save" /></td> 
  </tr>
 </table>
</form>	
	<?php
	echo foot();
}
elseif ($job == 'lang_array2') {
	echo head();
	$id = $gpc->get('id', int);
	$file = $gpc->get('file', str);
	$page = $gpc->get('page', int);

	$keys = array_keys($_REQUEST);
	$sent = array();
	foreach ($keys as $key) {
		if (substr($key, 0, 5) == 'lang_') {
			$sent[$key] = substr($key, 5, strlen($key));
		}
	}

	$c = new manageconfig();
	$c->getdata("language/{$id}/{$file}.lng.php", 'lang');
	foreach ($sent as $post => $key) {
		$c->updateconfig($key, str, $_REQUEST[$post]);
	}
	$c->savedata();
	
	ok('admin.php?action=language&job=lang_array&id='.$id.'&file='.$file.'&page='.$page);	
}
elseif ($job == 'lang_com') {
	echo head(' onload="init()"');
	$id = $gpc->get('id', int);
	$cid = $gpc->get('cid', int);
	$file = $gpc->get('file', str);
	$files = array();
	
	$dir = "language/{$id}/components/{$cid}/";
	if (is_dir($dir)) {
	   if ($dh = opendir($dir)) {
	       while (($fileh = readdir($dh)) !== false) {
				$ext = substr($fileh, -8, 8);
				if ($ext == '.lng.php') {
					$files[] = substr($fileh, 0, strlen($fileh)-8);
				}
	       }
	       closedir($dh);
		}
	}
	if (count($files) == 0) {
		error('admin.php?action=language&job=lang_edit&id='.$id, 'This component has no language-file');
	}
	if (count($files) > 0 && empty($file)) {
		$file = current($files);
	}

	$lng = return_array('components/'.$cid.'/'.$file, $id);
	ksort($lng);
	sort($files);
	$pages_html = "Dateien:";
	foreach ($files as $page) {
	  		$pages_html .= ' ['.iif($file == $page, "<strong>{$page}</strong>", "<a href='admin.php?action=language&job=lang_com&id={$id}&file={$page}&cid={$cid}'>{$page}</a>").']';
	}
	
	?>
	<form name="form" method="post" action="admin.php?action=language&job=lang_com2&id=<?php echo $id; ?>&file=<?php echo $file; ?>&cid=<?php echo $cid; ?>">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2">Edit language file &raquo; component: <?php echo $cid; ?> &raquo; <?php echo ucfirst($file); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox stext" colspan="2"><?php echo getLangVarsHelp(); ?></td> 
	  </tr>
	  <?php if (count($files) > 1) { ?>
	  <tr>
	   <td class="ubox" colspan="2">
	   <?php echo $pages_html; ?>
	   </td> 
	  </tr>
	  <?php
	  }
	  foreach ($lng as $key => $value) {
	  	$word = explode('_', $key);
	  	$word = array_map('ucfirst', $word);
	  	$word = implode(' ', $word);
	  ?>
	  <tr>
	   <td class="mbox" width="50%"><img name="c" id="img_lang_<?php echo $key; ?>" src="admin/html/images/plus.gif" alt=""> <?php echo $word; ?>
	   <div id="part_lang_<?php echo $key; ?>" class="stext">
	   <strong>Variable:</strong> <code>$lang->phrase('<?php echo $key; ?>')</code><br />
	   <strong>Original:</strong> <?php echo $value; ?>
	   </div>
	   </td>
	   <td class="mbox" width="50%"><input type="text" name="lang_<?php echo $key; ?>" size="70" value="<?php echo $value ?>" /></td>
	  </tr>
	  <?php } if (count($files) > 1) { ?>
	  <tr>
	   <td class="ubox" colspan="2">
	   <?php echo $pages_html; ?>
	   </td> 
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
elseif ($job == 'lang_com2') {
	echo head();
	$id = $gpc->get('id', int);
	$file = $gpc->get('file', str);
	$cid = $gpc->get('cid', int);

	$keys = array_keys($_REQUEST);
	$sent = array();
	foreach ($keys as $key) {
		if (substr($key, 0, 5) == 'lang_') {
			$sent[$key] = substr($key, 5, strlen($key));
		}
	}

	$c = new manageconfig();
	$c->getdata("language/{$id}/components/{$cid}/{$file}.lng.php", 'lang');
	foreach ($sent as $post => $key) {
		$c->updateconfig($key, str, $gpc->prepare($_REQUEST[$post]));
	}
	$c->savedata();
	
	ok('admin.php?action=language&job=lang_com&id='.$id.'&file='.$file.'&cid='.$cid);	
}
elseif ($job == 'lang_default') {
	echo head();
	$id = $gpc->get('id', int);
	
	$c = new manageconfig();
	$c->getdata();
	$c->updateconfig('langdir', int, $id);
	$c->savedata();
	
	$delobj = $scache->load('loadlanguage');
	$delobj->delete();
	
	ok('admin.php?action=language&job=manage');
}
elseif ($job == 'lang_edit') {
	echo head();
	$id = $gpc->get('id', int);
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox">Edit language file</td>
  </tr>
  <tr>
   <td class="mbox">
   <ul>
   <li><a href="admin.php?action=language&job=lang_settings&id=<?php echo $id; ?>">Settings</a></li>
   <li>Normal language file and phrases:
	   <ul>
	   <?php
		$dir = 'language/'.$id.'/';
		$files = array();
		$d = dir($dir);
		while (FALSE !== ($entry = $d->read())) {
			if (substr($entry, -8, 8) == '.lng.php') {
				$basename = substr($entry, 0, strlen($entry)-8);
				if ($basename != 'settings') {
					$files[$basename] = isset($langbase[$basename]) ? $langbase[$basename] : ucfirst($basename);
				}
		   	}
	   	}
		$d->close();
		foreach ($files as $file => $name) {
		?>
	    <li><a href="admin.php?action=language&job=lang_array&id=<?php echo $id; ?>&file=<?php echo $file; ?>"><?php echo $name; ?></a></li>
		<?php } ?>
	   </ul>
   </li>
   <li>Language file of the components:
	   <ul>
	   <?php
		$result = $db->query("SELECT * FROM {$db->pre}component ORDER BY active DESC");
		while ($row = $db->fetch_assoc($result)) {
			$cfg = $myini->read('components/'.$row['id'].'/components.ini');
			$c = array_merge($row, $cfg);
		?>
	   	<li><a href="admin.php?action=language&job=lang_com&id=<?php echo $id; ?>&cid=<?php echo $c['id']; ?>"><?php echo $c['config']['name']; ?></a> (<?php echo $c['id']; ?>)</li>
	   <?php } ?>
	   </ul>
   </li>
   <li><a href="admin.php?action=language&job=lang_rules&id=<?php echo $id; ?>">Terms of behaviour</a></li>
   <li><a href="admin.php?action=language&job=lang_emails&id=<?php echo $id; ?>">E-mail-texts</a></li>
   <li>Textvorlagen:
	   <ul>
	   <li><a href="admin.php?action=language&job=lang_txttpl&id=<?php echo $id; ?>&file=moved">Topic was moved</a></li>
	   <li><a href="admin.php?action=language&job=lang_txttpl&id=<?php echo $id; ?>&file=notice">Copied contributions</a></li>
	   </ul>
   </li>
   <li><a href="admin.php?action=language&job=lang_ignore&id=<?php echo $id; ?>">Ignored search keys</a></li>
   </ul>
   </td>
  </tr>
 </table>
	<?php
	echo foot();
}
elseif ($job == 'phrase') {
	echo head();
	$cache = array();
	$diff = array();
	$complete = array();
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	while($row = $db->fetch_assoc($result)) {
		$cache[$row['id']] = $row;
		$diff[$row['id']] = dir_array('language/'.$row['id'], 'language/'.$row['id']);
		$complete = array_merge($complete, array_diff($diff[$row['id']], $complete) );
	}
	usort($complete, 'sort_dirlist');
	$width = floor(75/count($cache));
	?>
<form name="form" method="post" action="admin.php?action=language&job=phrase_delete">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="<?php echo count($cache)+1; ?>">
   <span style="float: right;">[<a href="admin.php?action=language&job=phrase_add_lngfile">Add new Language File</a>] [<a href="admin.php?action=language&job=phrase_add_mailfile">Add new Mail File</a>]</span>
   Phrase Manager</td>
  </tr>
  <tr>
   <td class="mmbox" width="25%">&nbsp;</td>
   <?php foreach ($cache as $row) { ?>
   <td class="mmbox" align="center" width="<?php echo $width; ?>%"><?php echo $row['language']; ?> [<a href="admin.php?action=language&job=lang_edit&id=<?php echo $row['id']; ?>">Edit</a>]</td>
   <?php } ?>
  </tr>
  <?php foreach ($complete as $file) { ?>
  <tr>
   <td class="mmbox" nowrap="nowrap">
   <input type="checkbox" name="delete[]" value="<?php echo urlencode(base64_encode($file)); ?>">
   <?php if (substr($file, -8, 8) == '.lng.php') { ?>
   <a href="admin.php?action=language&job=phrase_file&file=<?php echo urlencode(base64_encode($file)); ?>"><?php echo $file; ?></a>
   <?php } else { echo $file; } ?>
   </td>
   <?php
   foreach ($cache as $row) { 
   	$status = in_array($file, $diff[$row['id']]);
   ?>
   <td class="mbox" align="center"><?php echo noki($status).iif(!$status, ' [<a href="admin.php?action=language&job=phrase_copy&file='.urlencode(base64_encode($file)).'&id='.$row['id'].'">Add</a>]'); ?></td>
   <?php } ?>
  </tr>
  <?php } ?>
  <tr> 
   <td class="ubox" align="center" colspan="<?php echo count($cache)+1; ?>"><input type="submit" value="Delete selected items"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'phrase_file_find') {
	$file = $gpc->get('file', none);
	echo head();
?>
<form name="form" method="get" action="admin.php">
<input type="hidden" name="action" value="language" />
<input type="hidden" name="job" value="phrase_file" />
<input type="hidden" name="show" value="search" />
<input type="hidden" name="file" value="<?php echo $file; ?>" />
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2">Phrase Manager &raquo; Find Phrase</td>
  </tr>
  <tr>
   <td class="mbox" width="40%">Keyword:<br />The keyword will be searched in the keys (unique identifier for phrases) and in the values (the phrases itself).</td>
   <td class="mbox" width="60%"><input type="text" name="key" size="40" /></td>
  <tr> 
   <td class="ubox" align="center" colspan="2"><input type="submit" value="Find"></td>
  </tr>
 </table>
</form>
<?php
	echo foot();
}
elseif ($job == 'phrase_file') {
	echo head();
	$file = $gpc->get('file', none);
	$encfile = base64_decode($file);
	$group = substr($encfile, 0, strlen($encfile)-8);
	$page = $gpc->get('page', int, 1);
	$show = $gpc->get('show', str);
	$cache = array();
	$diff = array();
	$complete = array();
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	while($row = $db->fetch_assoc($result)) {
		$cache[$row['id']] = $row;
		$diff[$row['id']] = array_keys(return_array($group, $row['id']));
		$complete = array_merge($complete, array_diff($diff[$row['id']], $complete) );
	}
	if ($show == 'diff') {
		$same = call_user_func_array('array_intersect', $diff);
		$complete = array_diff($complete, $same);
	}
	$search = $gpc->get('key', none);
	if ($show == 'search') {
		if (strlen($search) < 3) {
			error('admin.php?action=language&job=phrase_file_find', 'The keyword is too short. (Minimum: 3)');
		}
		foreach ($complete as $key => $value) {
			if (stristr($key, $search) === false && stristr($value, $search) === false) {
				unset($complete[$key]);
			}
		}
	}
	sort($complete);
	$width = floor(75/count($cache));
	if ($show == 'diff' || $show == 'search') {
		$perpage = 100;
	}
	else {
		$perpage = 50;
	}
	$data = array_chunk($complete, $perpage);
	if (!isset($data[$page-1])) {
		$page = 1;
	}
	$pages = count($data);
	$pages_html = "Pages ({$pages}):";
	// Ersetzen durch Buchstaben (?) -> [A] [B] ...
	for($i=1;$i<=$pages;$i++) {
   		$pages_html .= ' ['.iif($i == $page, "<strong>{$i}</strong>", "<a href='admin.php?action=language&job=phrase_file&file={$file}&page={$i}&show={$show}&key={$search}'>{$i}</a>").']';
	}
	?>
<form name="form" method="post" action="admin.php?action=language&job=phrase_file_delete&file=<?php echo $file; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="<?php echo count($cache)+1; ?>">
   <span style="float: right;">
   <?php if ($show == 'diff') { ?>
    [<a href="admin.php?action=language&job=phrase_file&file=<?php echo $file; ?>">Show all phrases</a>] 
   <?php } else { ?>
    [<a href="admin.php?action=language&job=phrase_file&file=<?php echo $file; ?>&show=diff">Show only differences</a>] 
   <?php } ?>
    [<a href="admin.php?action=language&job=phrase_file_find&file=<?php echo $file; ?>">Find phrases</a>]
    [<a href="admin.php?action=language&job=phrase_add&file=<?php echo $file; ?>">Add new phrase</a>]
   </span>
   Phrase Manager &raquo; <?php echo $encfile; ?></td>
  </tr>
  <?php if (count($cache) < 2 && $show == 'diff') { ?>
  <tr>
   <td class="mbox" colspan="<?php echo count($cache)+1; ?>">Not enough languages found to compare. At least 2 languages have to be installed!</td> 
  </tr>
  <?php } elseif (!isset($data[$page-1]) || count($data[$page-1]) == 0) { ?>
  <tr>
   <td class="mbox" colspan="<?php echo count($cache)+1; ?>">No phrases have been saved yet. [<a href="admin.php?action=language&job=phrase_add&file=<?php echo $file; ?>">Add new Phrase</a>]</td> 
  </tr>
  <?php } else { ?>
  <tr>
   <td class="ubox" colspan="<?php echo count($cache)+1; ?>"><?php echo $pages_html; ?></td> 
  </tr>
  <tr>
   <td class="mmbox" width="25%">&nbsp;</td>
   <?php foreach ($cache as $row) { ?>
   <td class="mmbox" align="center" width="<?php echo $width; ?>%"><?php echo $row['language']; ?></td>
   <?php } ?>
  </tr>
  <?php foreach ($data[$page-1] as $phrase) { ?>
  <tr>
   <td class="mmbox" nowrap="nowrap"><input type="checkbox" name="delete[]" value="<?php echo $phrase; ?>">&nbsp;[<a href="admin.php?action=language&job=phrase_file_edit&file=<?php echo $file; ?>&phrase=<?php echo $phrase; ?>">Edit</a>]&nbsp;<?php echo $phrase; ?></td>
   <?php
   foreach ($cache as $row) {
   	$status = in_array($phrase, $diff[$row['id']]);
   ?>
   <td class="mbox" align="center"><?php echo noki($status).iif(!$status, ' [<a href="admin.php?action=language&job=phrase_file_copy&file='.$file.'&id='.$row['id'].'&phrase='.$phrase.'">Add</a>]'); ?></td>
   <?php } ?>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="<?php echo count($cache)+1; ?>"><?php echo $pages_html; ?></td> 
  </tr>
  <tr> 
   <td class="ubox" align="center" colspan="<?php echo count($cache)+1; ?>"><input type="submit" value="Delete selected phrases"></td>
  </tr>
  <?php } ?>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'phrase_copy') {
	$lang = $gpc->get('id', int);
	$file = $gpc->get('file', none);
	$encfile = base64_decode($file);
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=language&job=phrase_copy2&file=<?php echo $file; ?>&id=<?php echo $lang; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2">Phrase Manager &raquo; <?php echo $encfile; ?> &raquo; Copy file</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Template directory:<br />
   <span class="stext">Specify the directory wherefrom the file should be copied.</span></td>
   <td class="mbox" width="50%"><select name="dir">
	<?php
	while($row = $db->fetch_assoc($result)) {
		if (file_exists('language/'.$row['id'].'/'.$encfile)) {
	?>
   	<option value="<?php echo $row['id']; ?>"><?php echo $row['language']; ?> (ID: <?php echo $row['id']; ?>)</option>
	<?php } } ?>
   </select></td>
  </tr>
  <tr> 
   <td class="ubox" align="center" colspan="2"><input type="submit" value="Copy file"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'phrase_copy2') {
	$dest = $gpc->get('id', int);
	$file = base64_decode($gpc->get('file', none));
	$source = $gpc->get('dir', int);
	echo head();
	createParentDir($file, 'language/'.$dest);
	$dest = 'language/'.$dest.'/'.$file;
	$source = 'language/'.$source.'/'.$file;
	if (file_exists($dest)) {
		error('admin.php?action=language&job=phrase', 'This file already exists. It has not been overwritten.');
	}
	if (file_exists($source) && $filesystem->copy($source, $dest)) {
		ok('admin.php?action=language&job=phrase', 'File was copied successful');
	}
	else {
		error('admin.php?action=language&job=phrase', 'Sourcefile does not exists or file could not be copied.');
	}
}
elseif ($job == 'phrase_file_edit') {
	echo head();
	
	$phrase = $gpc->get('phrase', none);
	$file = $gpc->get('file', none);
	$encfile = base64_decode($file);
	$basefile = substr($encfile, 0, strlen($encfile)-8);
	
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	$cache = array();
	while($row = $db->fetch_assoc($result)) {
	  	$phrases = return_array($basefile, $row['id']);
	  	if (!isset($phrases[$phrase])) {
	  		$row['phrase'] = '';
	  	}
	  	else {
	  		$row['phrase'] = $phrases[$phrase];
	  	}
	  	unset($phrases);
	  	$cache[$row['id']] = $row;
	}
	?>
<form name="form" method="post" action="admin.php?action=language&job=phrase_file_edit2&file=<?php echo $file; ?>">
 <table class="border" border="0" cellspacing="0" cellpediting="4" align="center">
  <tr> 
   <td class="obox" colspan="2">Phrase Manager &raquo; Edit new Phrase to Package</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Varname:<br />
   <span class="stext">Varname is a value which can only contain letters, numbers and underscores.</span></td>
   <td class="mbox" width="50%"><input type="hidden" name="varname" size="50" value="<?php echo $phrase; ?>" /><code><?php echo $phrase; ?></code></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Text:<br />
   <span class="stext">This is the default language used.</span></td>
   <td class="mbox" width="50%"><input type="text" name="text" size="50" value="<?php echo htmlspecialchars($cache[$config['langdir']]['phrase']); ?>" /></td>
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
  <?php foreach ($cache as $row) { ?>
  <tr>
   <td class="mbox" width="50%"><em><?php echo $row['language']; ?></em> Translation:<br /><span class="stext">Optional. HTML is allowed but not recommended.</span></td>
   <td class="mbox" width="50%"><input type="text" name="langt[<?php echo $row['id']; ?>]" size="50" value="<?php echo htmlspecialchars($row['phrase']); ?>" /></td>
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
elseif ($job == 'phrase_file_edit2') {
	echo head();
	
	$file = $gpc->get('file', none);
	$encfile = base64_decode($file);
	$varname = $gpc->get('varname', none);
	$text = $gpc->get('text', none);
	$lang = $gpc->get('langt', none);
	
	$c = new manageconfig();
	foreach ($lang as $id => $t) {
		if (empty($t)) {
			$t = $text;
		}
		$c->getdata("language/{$id}/{$encfile}", 'lang');
		$c->updateconfig($varname, str, $t);
		$c->savedata();
	}
	
	ok('admin.php?action=language&job=phrase_file&file='.$file);
}
elseif ($job == 'phrase_file_copy') {
	$lang = $gpc->get('id', int);
	$file = $gpc->get('file', none);
	$encfile = base64_decode($file);
	$phrase = $gpc->get('phrase', str);
	$result = $db->query("SELECT * FROM {$db->pre}language WHERE id != '{$lang}' ORDER BY language",__LINE__,__FILE__);
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=language&job=phrase_file_copy2&phrase=<?php echo $phrase; ?>&file=<?php echo $file; ?>&id=<?php echo $lang; ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2">Phrase Manager &raquo; <?php echo $encfile; ?> &raquo; Copy phrase</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Language which should be used as original:<br />
   <span class="stext">Specify the directory/language wherefrom the phrase should be copied.</span></td>
   <td class="mbox" width="50%"><select name="dir">
	<?php
	$basefile = substr($encfile, 0, strlen($encfile)-8);
	while($row = $db->fetch_assoc($result)) {
		if (file_exists('language/'.$row['id'].'/'.$encfile)) {
			$langarr = return_array($basefile, $row['id']);
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
elseif ($job == 'phrase_file_copy2') {
	echo head();
	$dest = $gpc->get('id', int);
	$source = $gpc->get('dir', int);
	$file = $gpc->get('file', none);
	$encfile = base64_decode($file);
	$phrase = $gpc->get('phrase', str);
	$destpath = 'language/'.$dest.'/'.$encfile;
	$c = new manageconfig();
	if (!file_exists($destpath)) {
		createParentDir($encfile, 'language/'.$dest);
		$c->createfile($destpath, 'lang');
	}
	$encfile = substr($encfile, 0, strlen($encfile)-8);
	$langarr = return_array($encfile, $source);
	if (!isset($langarr[$phrase])) {
		error('admin.php?action=language&job=phrase_file&file='.$file, 'Phrase not found!');
	}
	$c->getdata($destpath, 'lang');
	$c->updateconfig($phrase, str, $langarr[$phrase]);
	$c->savedata();
	ok('admin.php?action=language&job=phrase_file&file='.$file);
}
elseif ($job == 'phrase_delete') {
	echo head();
	$delete = $gpc->get('delete', arr_none);
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	while($row = $db->fetch_assoc($result)) {
		foreach ($delete as $base) {
			$base = base64_decode($base);
			$path = "language/{$row['id']}/{$base}";
			if (file_exists($path)) {
				$filesystem->unlink($path);
			}
		}
	}
	ok('admin.php?action=language&job=phrase', 'Deleted selected files.');
}
elseif ($job == 'phrase_file_delete') {
	echo head();
	$delete = $gpc->get('delete', arr_str);
	$file = $gpc->get('file', none);
	$encfile = base64_decode($file);
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	$c = new manageconfig();
	while($row = $db->fetch_assoc($result)) {
		$path = "language/{$row['id']}/{$encfile}";
		if (file_exists($path)) {
			$c->getdata($path, 'lang');
			foreach ($delete as $phrase) {
				$c->delete($phrase);
			}
			$c->savedata();
		}
	}
	ok('admin.php?action=language&job=phrase_file&file='.$file, 'Selected phrases were successfully deleted.');
}
elseif ($job == 'phrase_add_lngfile') {
	echo head();
	?>
<form name="form" method="post" action="admin.php?action=language&job=phrase_add_lngfile2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2">Phrase Manager &raquo; Add new Language File</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Filename:<br />
   <span class="stext">Filename is a value which can only contain letters, numbers, underscores and dots. Do not add an extension to the filename!</span></td>
   <td class="mbox" width="50%"><input type="text" name="file" size="50" />.lng.php</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Directory:<br />
   <span class="stext">In this directory the file will be saved. <code>ID</code> is variable.</span></td>
   <td class="mbox" width="50%"><select name="dir">
    <option value="<?php echo base64_decode(''); ?>">language/ID/ (Main directory for language files)</option>
   <?php 
   $result = $db->query("SELECT * FROM {$db->pre}component ORDER BY active DESC");
   while ($row = $db->fetch_assoc($result)) {
	$cfg = $myini->read('components/'.$row['id'].'/components.ini');
	$row = array_merge($row, $cfg);
   ?>
    <option value="<?php echo base64_decode('components/'.$row['id'].'/'); ?>">language/ID/components/<?php echo $row['id']; ?> (Component: <?php echo $row['config']['name']; ?>)</option>
   <?php } ?>
   </select></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Create" /></td> 
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'phrase_add_lngfile2') {
	$dir = base64_decode($gpc->get('dir', none));
	$file = $gpc->get('file', none);
	$c = new manageconfig();
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	while($row = $db->fetch_assoc($result)) {
		$c->createfile("language/{$row['id']}/{$dir}{$file}.lng.php", 'lang');
	}
	echo head();
	ok('admin.php?action=language&job=phrase_file&file='.urlencode(base64_encode("{$dir}{$file}.lng.php")), 'Language file sucessfully created.');
}
elseif ($job == 'phrase_add_mailfile') {
	echo head();
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	?>
<form name="form" method="post" action="admin.php?action=language&job=phrase_add_mailfile2">
 <table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr> 
   <td class="obox" colspan="2">Add new Mail File</td>
  </tr>
  <tr>
   <td class="mbox" width="30%">Filename:<br />
   <span class="stext">Filename is a value which can only contain letters, numbers, underscores and dots. Do not add an extension to the filename!</span></td>
   <td class="mbox" width="70%"><input type="text" name="file" size="80">.php</td>
  </tr>
  <tr>
   <td class="mmbox" width="30%">Help:</td>
   <td class="mmbox stext" width="70%"><?php echo getLangVarsHelp(); ?></td> 
  </tr>
  <tr>
   <td class="mbox" width="30%">Title:</td>
   <td class="mbox" width="70%"><input type="text" name="title" size="80" value="{@config->fname}: Your Title"></td>
  </tr>
  <tr>
   <td class="mbox" width="30%">Text:</td>
   <td class="mbox" width="70%"><textarea name="tpl" rows="8" cols="80">Hallo,

You can compose your text here...

Yours sincerely,
Your {@config->fname} Team
{@config->furl}</textarea></td>
  </tr>
  <tr> 
   <td class="obox" colspan="2">Translations</td>
  </tr>
  <tr>
   <td class="mmbox" colspan="2"><ul>
	<li>When inserting a custom mail, you may also specify the translations into whatever languages you have installed.</li>
	<li>If you do leave a translation box (text or title) blank, it will inherit the text or title from the box above.</li>
   </ul></td> 
  </tr>
  <?php while($row = $db->fetch_assoc($result)) { ?>
  <tr> 
   <td class="ubox" colspan="2"><b><?php echo $row['language']; ?></b> Translation:</td>
  </tr>
  <tr>
   <td class="mbox" width="30%">Title:</td>
   <td class="mbox" width="70%"><input type="text" name="titles[<?php echo $row['id']; ?>]" size="80"></td>
  </tr>
  <tr>
   <td class="mbox" width="30%">Text:</td>
   <td class="mbox" width="70%"><textarea name="texts[<?php echo $row['id']; ?>]" rows="5" cols="80"></textarea></td>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Create" /></td> 
  </tr>
 </table>
</form>		
	<?php
	echo foot();
}
elseif ($job == 'phrase_add_mailfile2') {
	echo head();
	
	$file = $gpc->get('file', str);
	$tpl = $gpc->get('tpl', none);
	$title = $gpc->get('title', none);
	$titles = $gpc->get('titles', none);
	$texts = $gpc->get('texts', none);

	foreach ($titles as $id => $tit) {
		if (!empty($texts[$id])) {
			$tex = $texts[$id];
		}
		else {
			$tex = $tpl;
		}
		if (empty($tit)) {
			$tit = $title;
		}
		$xml = "<mail>\n\t<title>{$tit}</title>\n\t<comment>{$tex}</comment>\n</mail>";
		$filesystem->file_put_contents("language/{$id}/mails/{$file}.php", $xml);
	}

	ok('admin.php?action=language&job=phrase');	
}
elseif ($job == 'phrase_add') {
	echo head();
	$file = base64_decode($gpc->get('file', none));
	$result = $db->query('SELECT * FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	?>
<form name="form" method="post" action="admin.php?action=language&job=phrase_add2&file=<?php echo $gpc->get('file', none); ?>">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2">Phrase Manager &raquo; <?php echo $file; ?> &raquo; Add new Phrase</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Varname:<br />
   <span class="stext">Varname is a value which can only contain letters, numbers and underscores.</span></td>
   <td class="mbox" width="50%"><input type="text" name="varname" size="50" value="" /></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">Text:</td>
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
  <?php
  while($row = $db->fetch_assoc($result)) {
  if (file_exists('language/'.$row['id'].'/'.$file)) {
  ?>
  <tr>
   <td class="mbox" width="50%"><em><?php echo $row['language']; ?></em> Translation:<br /><span class="stext">Optional. HTML is allowed but not recommended.</span></td>
   <td class="mbox" width="50%"><input type="text" name="langt[<?php echo $row['id']; ?>]" size="50" /></td>
  </tr>
  <?php } } ?>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Save" /></td> 
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'phrase_add2') {
	echo head();
	$varname = $gpc->get('varname', none);
	$text = $gpc->get('text', none);
	$file = base64_decode($gpc->get('file', none));
	$lang = $gpc->get('langt', none);
	
	$c = new manageconfig();
	foreach ($lang as $id => $t) {
		if (empty($t)) {
			$t = $text;
		}
		$c->getdata("language/{$id}/{$file}", 'lang');
		$c->updateconfig($varname, str, $t);
		$c->savedata();
	}
	
	ok('admin.php?action=language&job=phrase_file&file='.urlencode(base64_encode($file)));	
}
?>
