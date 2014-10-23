<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "misc.php") die('Error: Hacking Attempt');

($code = $plugins->load('admin_misc_jobs')) ? eval($code) : null;

if ($job == 'phpinfo') {
	phpinfo();
}
elseif ($job == 'cache') {
	echo head();
	$result = array();
	$dir = "cache/";
	$handle = opendir($dir);
	while ($file = readdir($handle)) {
		if ($file != "." && $file != ".." && !is_dir($dir.$file)) {					  
			$nfo = pathinfo($dir.$file);
			if ($nfo['extension'] == 'php') {
				$name = str_replace('.inc.php', '', $nfo['basename']);
				$result[$name] = array(
					'file' => $nfo['basename'],
					'size' => filesize($dir.$file),
					'age' => time()-filemtime($dir.$file),
					'rebuild' => file_exists("classes/cache/{$name}.inc.php"),
					'cached' => true
				);
			}
		}
	}
	$dir = "classes/cache/";
	$handle = opendir($dir);
	while ($file = readdir($handle)) {
		if ($file != "." && $file != ".." && !is_dir($dir.$file)) {					  
			$nfo = pathinfo($dir.$file);
			if ($nfo['extension'] == 'php') {
				$name = str_replace('.inc.php', '', $nfo['basename']);
				if (!isset($result[$name])) {
					$result[$name] = array(
						'file' => $nfo['basename'],
						'size' => null,
						'age' => null,
						'rebuild' => true,
						'cached' => false
					);
				}
			}
		}
	}
	ksort($result);
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="4">
   <span style="float: right;">
   	<a class="button" href="admin.php?action=misc&amp;job=cache_refresh_all">Rebuild All</a>
   	<a class="button" href="admin.php?action=misc&amp;job=cache_delete_all">Delete All</a>
   </span>
   <b>Cache-Manager</b></td>
  </tr>
  <tr>
   <td class="ubox" width="35%">Cache Name</td>
   <td class="ubox" width="10%">File Size</td>
   <td class="ubox" width="15%">Approximate Age</td>
   <td class="ubox" width="40%">Options</td>
  </tr>
  <?php foreach ($result as $name => $row) { ?>
  <tr>
   <td class="mbox"><?php echo $name; ?></td>
   <td class="mbox" nowrap="nowrap" align="right"><?php echo iif ($row['cached'], formatFilesize($row['size']), '-'); ?></td>
   <td class="mbox" nowrap="nowrap"><?php echo iif($row['cached'], 'approx. '.fileAge($row['age']), '-'); ?></td>
   <td class="mbox">
   <?php if ($row['cached']) { ?>
   <a class="button" href="admin.php?action=misc&amp;job=cache_view&amp;file=<?php echo $name; ?>">View Contents</a> 
   <a class="button" href="admin.php?action=misc&amp;job=cache_delete&amp;file=<?php echo $name; ?>">Delete Cache</a>
   <?php } if ($row['rebuild']) { ?>
   <a class="button" href="admin.php?action=misc&amp;job=cache_refresh&amp;file=<?php echo $name; ?>">Rebuild Cache</a> 
   <?php } ?>
   </td>
  </tr>
  <?php } ?>
 </table>
	<?php
	echo foot();
}
elseif ($job == 'cache_view') {
	$file = $gpc->get('file', str);
	echo head();
	$cache = new CacheItem($file);
	$cache->import();
	$data = $cache->get();

	// ToDo: Better appearance
	ob_start();
	print_r($data);
	$out = ob_get_contents();
	ob_end_clean();
	$out = htmlspecialchars($out);
	
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox"><b>Cache-Manager &raquo; <?php echo $file; ?></b></td>
  </tr>
  <tr> 
   <td class="mbox">
   <pre><?php echo $out; ?></pre>
   </td>
  </tr>
 </table>
	<?php
	echo foot();
}
elseif ($job == 'cache_delete' || $job == 'cache_refresh') {
	$file = $gpc->get('file', str);
	echo head();
	$cache = $scache->load($file);
	$cache->delete();
	if ($job == 'cache_refresh') {
		$cache->load();
	}
	ok('admin.php?action=misc&job=cache', iif($job == 'cache_refresh', 'The cache-file was rebuilt.', 'The cache-file was deleted. It will be rebuild the next time it is needed.'));
}
elseif ($job == 'cache_delete_all' || $job == 'cache_refresh_all') {
	echo head();
	$dir = iif ($job == 'cache_refresh_all', 'classes/cache', 'cache');
	if ($dh = @opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if (strpos($file, '.inc.php') !== false) {
				$file = str_replace('.inc.php', '', $file);
				$cache = $scache->load($file);
				$cache->delete();
				if ($job == 'cache_refresh_all') {
					$cache->load();
				}
			}
	    }
		closedir($dh);
	}
	ok('admin.php?action=misc&job=cache', iif($job == 'cache_refresh_all', 'The cache-files were rebuilt.', 'The cache-files were deleted. They will be rebuild the next time they are needed.'));
}
elseif ($job == 'onlinestatus') {
	echo head();
	$b = file_get_contents('data/imservers.php');
	?>
<form name="form" method="post" action="admin.php?action=misc&job=onlinestatus2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2"><b>Online-Status Server</b></td>
  </tr>
  <tr>
   <td class="mbox" width="30%">
   Server:<br />
   <span class="stext">Per line one server.<br /><a href="http://osi.viscacha.org/" target="_blank">Online-Status Server overview</a></span>
   </td>
   <td class="mbox" width="70%"><textarea name="servers" rows="10" cols="90"><?php echo $b; ?></textarea></td> 
  </tr>
  <tr> 
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
  </tr>
 </table>
</form>
<br />
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2"><b>Information concerning the online-status server</b></td>
  </tr>
  <tr>
   <td class="mbox">
   <p><strong>What is the meaning of online-status?</strong><br />
   In the user-profiles you can mention the addresses of instant messengers. In the profile these addresses will be linked to a website which shows the current status of the given address.</p>
   <p><strong>Where come the datas for the online-status from?</strong><br />
   The datas of the messengers ICQ, Yahoo, AOL and Skype are taken directly from the servers of the respective messenger-provider. Jabber and MSN does not provide such a service. Therefore an inofficel source, the service of <a href="http://www.onlinestatus.org" target="_blank">Onlinestatus.org</a>, is used.
   This service provides a programm which can read and return the datas of the messengers. Due to the fact that this program is distributed to several servers which can change freqently, there must be mentioned a list of servers in the field above where the status could read from.<br />
   An overview of available servers and further information you can find here: <a href="http://osi.viscacha.org/" target="_blank">Online-Status-Server-overview</a>.
   </p>
   </td> 
  </tr>
 </table>
	<?php
	echo foot();
}
elseif ($job == 'onlinestatus2') {
	echo head();
	$filesystem->file_put_contents('data/imservers.php', $gpc->get('servers', none));
	ok('admin.php?action=misc&job=onlinestatus', 'Datas are saved');
}
elseif ($job == 'sessionmails') {
	echo head();
	$mails = file_get_contents('data/sessionmails.php');
	?>
<form name="form" method="post" action="admin.php?action=misc&job=sessionmails2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2"><b>Disposable e-mail address provider</b></td>
  </tr>
  <tr>
   <td class="mbox" width="30%">
   Provider-domain:<br />
   <span class="stext">Per line one domain.<br />Format: <code>name.tld</code> (without http, www, @, ...)</span>
   </td>
   <td class="mbox" width="70%"><textarea name="mails" rows="10" cols="90"><?php echo $mails; ?></textarea></td> 
  </tr>
  <tr> 
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'sessionmails2') {
	echo head();
	$mails = $gpc->get('mails', none);
	$filesystem->file_put_contents('data/sessionmails.php', $mails);
	ok('admin.php?action=misc&job=sessionmails', 'Datas are saved');
}
elseif ($job == 'feedcreator') {
	echo head();
	$data = file('data/feedcreator.inc.php');
?>
<form name="form" method="post" action="admin.php?action=misc&job=feedcreator_delete">
 <table class="border">
  <tr> 
   <td class="obox" colspan="5">Creation and Export of Newsfeeds (<?php echo count($data); ?>)</b></td>
  </tr>
  <tr>
   <td class="ubox" width="10%">Delete<br /><span class="stext"><input type="checkbox" onclick="check_all('delete[]');" name="all" value="1" /> All</span></td>
   <td class="ubox" width="30%">Name</td> 
   <td class="ubox" width="30%">File (Class)</td>
   <td class="ubox" width="15%">Shown</td>
   <td class="ubox" width="15%">Download</td>
  </tr>
<?php
foreach ($data as $r) {
	$row = explode('|', $r);
	$row = array_map('trim', $row);
?>
  <tr>
   <td class="mbox" width="10%"><input type="checkbox" name="delete[]" value="<?php echo $row[0]; ?>"></td>
   <td class="mbox" width="30%"><a href="external.php?action=<?php echo $row[0]; ?>" target="_blank" title="Show feed"><?php echo $row[2]; ?></a></td>
   <td class="mbox" width="30%"><?php echo $row[1]; ?> (<?php echo $row[0]; ?>)</td>
   <td class="mbox" width="15%"><?php echo noki($row[3]); ?> <a class="button" href="admin.php?action=misc&job=feedcreator_active&id=<?php echo $row[0]; ?>&key=3">Change</a></td>
   <td class="mbox" width="15%"><?php echo noki($row[4]); ?> <a class="button" href="admin.php?action=misc&job=feedcreator_active&id=<?php echo $row[0]; ?>&key=4">Change</a></td>
  </tr>
<?php } ?>
  <tr> 
   <td class="ubox" width="100%" colspan="5" align="center"><input type="submit" name="Submit" value="Delete"></td> 
  </tr>
 </table>
</form>
<br>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=misc&job=feedcreator_add">
<table class="border">
<tr><td class="obox" colspan="2">Add new Feedcreator</td></tr>
<tr class="mbox"><td>Upload file:<br /><span class="stext">permitted file types: .php<br />maximum file size: 200 KB</span></td><td><input type="file" name="upload" size="50" /></td></tr>
<tr class="mbox"><td>Name:</td><td><input type="text" name="name" size="50" /></td></tr>
<tr class="mbox"><td>Name of the class:<br /><span class="stext">If no value is mentioned Viscacha will try to filter the name itself.</span></td><td><input type="text" name="class" size="50" /></td></tr>
<tr class="mbox"><td>Shown:<br /><span class="stext">Specifys whether this feed will be shown. It does not regulate whether a feed is active or not!</span></td><td><input type="checkbox" name="active" value="1" /></td></tr>
<tr class="mbox"><td>Download:<br /><span class="stext">Specifys whether this feed should be offered for downloads or shown directly in the browser.</span></td><td><input type="checkbox" name="dl" value="1" /></td></tr>
<tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" value="Upload &amp; Add" /></td></tr>
</table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'feedcreator_active') {
	$d = $gpc->get('id', str);
	$key = $gpc->get('key', int);
	if ($key == 3 || $key == 4) {
		$data = file('data/feedcreator.inc.php');
		$n = array();
		foreach ($data as $r) {
			$row = explode('|', $r);
			$row = array_map('trim', $row);
			if (strtoupper($row[0]) == strtoupper($d)) {
				$row[$key] = invert($row[$key]);
			}
			$n[] = implode('|', $row);
		}
		$filesystem->file_put_contents('data/feedcreator.inc.php', implode("\n", $n));
	}
    viscacha_header('Location: admin.php?action=misc&job=feedcreator');

}
elseif ($job == 'feedcreator_add') {
	echo head();
	$name = $gpc->get('name', str);
	$class = $gpc->get('class', str);
	$active = $gpc->get('active', str);
	$dl = $gpc->get('dl', str);
	$dir = realpath('./classes/feedcreator/').DIRECTORY_SEPARATOR;
	
	$inserterrors = array();
	require("classes/class.upload.php");
	$my_uploader = new uploader();
	$my_uploader->max_filesize(200*1024);
	$my_uploader->file_types(array('php'));
	$my_uploader->set_path($dir);
	if ($my_uploader->upload('upload')) {
		if ($my_uploader->save_file()) {
			$file = $my_uploader->fileinfo('filename');
		}
	}
	if ($my_uploader->upload_failed()) {
		array_push($inserterrors, $my_uploader->get_error());
	}
	if (empty($file)) {
		array_push($inserterrors, 'File does not exist!');
	}
	if (count($inserterrors) > 0) {
		error('admin.php?action=misc&job=feedcreator', $inserterrors);
	}
	else {
		$data = file('data/feedcreator.inc.php');
		$data = array_map('trim', $data);
		
		if (empty($class)) {
			$source = file_get_contents('classes/feedcreator/'.$file);
			preg_match('/[\s\t\n\r]+class[\s\t]+([^\s\t\n\r]+)[\s\t]+extends[\s\t]+FeedCreator[\s\t\n\r]+\{/i', $source, $treffer);
			$class = $treffer[1];
			if (empty($class)) {
				error('admin.php?action=misc&job=feedcreator', 'Could not parse Class-Name.');
			}
		}
		$data[] = "{$class}|{$file}|{$name}|{$active}|{$dl}";
		$filesystem->file_put_contents('data/feedcreator.inc.php', implode("\n", $data));
		ok('admin.php?action=misc&job=feedcreator', 'Added');
	}
}
elseif ($job == 'feedcreator_delete') {
	echo head();
	$d = $gpc->get('delete', arr_str);
	$d = array_map('strtoupper', $d);
	$data = file('data/feedcreator.inc.php');
	$n = array();
	foreach ($data as $r) {
		$row = explode('|', $r);
		$row = array_map('trim', $row);
		if (in_array(strtoupper($row[0]), $d)) {
			$file = 'classes/feedcreator/'.$row[1];
			if (file_exists($file)) {
				$filesystem->unlink($file);
			}
			continue;
		}
		else {
			$n[] = implode('|', $row);
		}
	}
	$filesystem->file_put_contents('data/feedcreator.inc.php', implode("\n", $n));
    ok('admin.php?action=misc&job=feedcreator', 'Files have been deleted');
}
elseif ($job == "captcha") {
	echo head();
	$fonts = 0;
	$dir = 'classes/fonts/';
	if ($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if(preg_match('/captcha_\d+\.ttf/i', $file)) {
				$fonts++;
			}
		}
		closedir($dh);
	}
	$noises = 0;
	$dir = 'classes/graphic/noises/';
	if ($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if(get_extension($file) == 'jpg') {
				$noises++;
			}
		}
		closedir($dh);
	}
	?>
 <table class="border">
  <tr> 
   <td class="obox">Captcha Manager</td>
  </tr>
  <tr>
   <td class="mbox">
   <ul>
   <li style="padding: 3px;">Background pictures: <?php echo $noises; ?> <a class="button" href="admin.php?action=misc&amp;job=captcha_noises">administrate</a></li>
   <li style="padding: 3px;">Fonts: <?php echo $fonts; ?> <a class="button" href="admin.php?action=misc&amp;job=captcha_fonts">administrate</a></li>
   <li style="padding: 3px;"><a href="admin.php?action=settings&amp;job=captcha">Settings</a></li>
   </ul>
   </td>
  </tr>
 </table>
	<?php
	echo foot();
}
elseif ($job == "captcha_noises_delete") {
	echo head();
	$delete = $gpc->get('delete', arr_str);
	$deleted = 0;
	foreach ($delete as $filename) {
		$filesystem->unlink('classes/graphic/noises/'.$filename.'.jpg');
		if (!file_exists('classes/graphic/noises/'.$filename.'.jpg')) {
			$deleted++;
		}
	}
	ok('admin.php?action=misc&job=captcha_noises', $deleted.' Background pictures have been deleted.');
}
elseif ($job == "captcha_noises_view") {
	$file = $gpc->get('file', str);
	viscacha_header('Content-Type: image/jpeg');
	viscacha_header('Content-Disposition: inline; filename="'.$file.'.jpg"');
	readfile('classes/graphic/noises/'.$file.'.jpg');
}
elseif ($job == "captcha_noises") {
	$fonts = array();
	$dir = 'classes/graphic/noises/';
	if ($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if(get_extension($file) == 'jpg') {
				$fonts[] = $dir.$file;
			}
		}
		closedir($dh);
	}
	echo head();
	?>
<form action="admin.php?action=misc&job=captcha_noises_delete" name="form2" method="post">
 <table class="border">
  <tr> 
   <td class="obox" colspan="3">Captcha Manager &raquo; Background noises</td>
  </tr>
  <tr>
   <td class="ubox" width="10%">Delete<br /><span class="stext"><input type="checkbox" onclick="check_all('delete[]');" name="all" value="1" /> All</span></td>
   <td class="ubox" width="90%">Preview of the background image</td>
  </tr>
  <?php foreach ($fonts as $path) { ?>
  <tr>
   <td class="mbox"><input type="checkbox" name="delete[]" value="<?php echo basename($path, ".jpg"); ?>" /></td>
   <td class="mbox"><img border="1" src="admin.php?action=misc&job=captcha_noises_view&file=<?php echo basename($path, ".jpg"); ?>" /></td>
  </tr>
  <?php } ?>
  <tr> 
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Delete"></td> 
  </tr>
 </table>
</form>
<br />
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=explorer&job=upload&cfg=captcha_noises">
 <table class="border" cellpadding="3" cellspacing="0" border="0">
  <tr><td class="obox">Upload new background noises</td></tr>
  <tr>
   <td class="mbox">
	To add a file, click on the "Browse"-button an select the file.
	Afterwards click on "Submit" to finish the process.<br /><br />
	Permitted file types: .jpg<br />
	Maximum file size: 200 KB<br />
	Recommended picture size: 150x40 pixel - maximum picture size: 300x80 pixel<br /><br />
	<strong>Upload file:</strong>
	<br /><input type="file" name="upload_0" size="40" />
   </td>
  </tr>
  <tr><td class="ubox" align="center"><input accesskey="s" type="submit" value="Upload" /></td></tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == "captcha_fonts_delete") {
	echo head();
	$delete = $gpc->get('delete', arr_str);
	$deleted = 0;
	foreach ($delete as $filename) {
		$filesystem->unlink('classes/fonts/'.$filename.'.ttf');
		if (!file_exists('classes/fonts/'.$filename.'.ttf')) {
			$deleted++;
		}
	}
	ok('admin.php?action=misc&job=captcha_fonts', $deleted.' Fonts have been deleted.');
}
elseif ($job == "captcha_fonts") {
	$fonts = array();
	$dir = 'classes/fonts/';
	if ($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if(preg_match('/captcha_\d+\.ttf/i', $file)) {
				$fonts[] = $dir.$file;
			}
		}
		closedir($dh);
	}
	echo head();
	?>
<form action="admin.php?action=misc&job=captcha_fonts_delete" name="form2" method="post">
 <table class="border">
  <tr> 
   <td class="obox" colspan="3">Captcha Manager &raquo; Fonts</td>
  </tr>
  <tr>
   <td class="ubox" width="10%">Delete<br /><span class="stext"><input type="checkbox" onclick="check_all('delete[]');" name="all" value="1" /> All</span></td>
   <td class="ubox" width="90%">Preview of the font</td>
  </tr>
  <?php foreach ($fonts as $path) { ?>
  <tr>
   <td class="mbox"><input type="checkbox" name="delete[]" value="<?php echo basename($path, ".ttf"); ?>" /></td>
   <td class="mbox"><img border="1" src="classes/graphic/text2image.php?file=<?php echo basename($path, ".ttf"); ?>&amp;text=1234567890&amp;size=30" /></td>
  </tr>
  <?php } ?>
  <tr> 
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Delete"></td> 
  </tr>
 </table>
</form>
<br />
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=explorer&job=upload&cfg=captcha_fonts">
 <table class="border" cellpadding="3" cellspacing="0" border="0">
  <tr><td class="obox">Upload new font file</td></tr>
  <tr>
   <td class="mbox">
	To add a file, click on the "Browse"-button an select an file.
	Afterwards click on "Submit" to finish the process.<br /><br />
	Permitted file types: .ttf<br />
	Maximum file size: 500 KB<br />
	<strong>Upload file:</strong>
	<br /><input type="file" name="upload_0" size="40" />
   </td>
  </tr>
  <tr><td class="ubox" align="center"><input accesskey="s" type="submit" value="Upload" /></td></tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == "spellcheck") {
	echo head();
	if (!$config['spellcheck']) {
		error('admin.php?action=settings&job=spellcheck', 'Spell Check is disabled.');
	}
	$dicts = array();
	$result = $db->query('SELECT id FROM '.$db->pre.'language',__LINE__,__FILE__);
	while ($row = $db->fetch_assoc($result)) {
		@include('language/'.$row['id'].'/settings.lng.php');
		$dicts[] = $lang['spellcheck_dict'];
	}
	?>
<form name="form2" method="post" action="admin.php?action=misc&amp;job=spellcheck_add">
 <table class="border">
  <tr> 
   <td class="obox">Spell Checker &raquo; Add words to the wordlist</td>
  </tr>
  <tr>
   <td class="mbox">
   Enter custom words you want added to your personal dictionary that will be used in addition to the native dictionaries. (1 word per line.)<br /><br />
   <textarea name="words" rows="10" cols="100"></textarea><br />
   <strong>Dictionary:</strong> <select name="dict">
   <?php foreach ($dicts as $dict) { ?>
   <option value="<?php echo $dict; ?>"><?php echo $dict; ?></option>
   <?php } ?>
   </select>
   </td>
  </tr>
  <tr><td class="ubox" align="center"><input accesskey="s" type="submit" value="Save" /></td></tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == "spellcheck_add") {
	echo head();
	$dict = $gpc->get('dict', str);
	if ($config['pspell'] == 'pspell') {
		include('classes/spellchecker/pspell.class.php');
	}
	elseif ($config['pspell'] == 'mysql') {
		include('classes/spellchecker/mysql.class.php');
		global $db;
		$path = $db;
	}
	else {
		include('classes/spellchecker/php.class.php');
		$path = 'classes/spellchecker/dict/';
	}
	$sc = new spellchecker($dict,$config['spellcheck_ignore'],$config['spellcheck_mode'], true);
	if (isset($path)) {
		$sc->set_path($path);
	}
	$sc->init();

	$x = $sc->error();
	if (!empty($x)) {
		error('admin.php?action=misc&job=spellcheck', $x);
	}
	
	$words = $gpc->get('words', none);
	$word_seperator = "0-9\\.,;:!\\?\\-\\|\n\r\s\"'\\[\\]\\{\\}\\(\\)\\/\\\\";
	$words = preg_split('~['.$word_seperator.']+?~', $words, -1, PREG_SPLIT_NO_EMPTY);
	foreach ($words as $k => $w) {
		if (empty($w)) {
			unset($words[$k]);
		}
	}
	if ($sc->add($words)) {
		ok('admin.php?action=misc&job=spellcheck');
	}
	else {
		error('admin.php?action=misc&job=spellcheck');
	}
}
elseif ($job == "credits") {
	echo head();
	
	$ext = get_loaded_extensions();
	if (in_array("zlib", $ext)) {
		$zlibext = "<span style='color: green'>OK</span>";
	}
	else {
		$zlibext = "<span style='color: red'>N/A</span>";
	}
	if (in_array("mysql", $ext)) {
		$mylibext = "<span style='color: green'>OK</span>";
	}
	else {
		$mylibext = "<span style='color: red'>N/A</span>";
	}
	if (in_array("pcre", $ext)) {
		$relibext = "<span style='color: green'>OK</span>";
	}
	else {
		$relibext = "<span style='color: red'>N/A</span>";
	}
	if (in_array("gd", $ext)) {
		$gdlibext = "<span style='color: green'>OK</span>";
	}
	else {
		$gdlibext = "<span style='color: red'>N/A</span>>";
	}
	if (in_array("pspell", $ext)) {
		$pslibext = "<span style='color: green'>OK</span>";
	}
	else {
		$pslibext = "<span style='color: red'>N/A</span>";
	}
	if (in_array("xml", $ext)) {
		$xmllibext = "<span style='color: green'>OK</span>";
	}
	else {
		$xmllibext = "<span style='color: red'>N/A</span>";
	}
	if (in_array("iconv", $ext)) {
		$ivlibext = "<span style='color: green'>OK</span>";
	}
	else {
		$ivlibext = "<span style='color: red'>N/A</span>";
	}
	if (in_array("mbstring", $ext)) {
		$mblibext = "<span style='color: green'>OK</span>";
	}
	else {
		$mblibext = "<span style='color: red'>N/A</span>";
	}
	if (in_array("mhash", $ext)) {
		$mhashext = "<span style='color: green'>OK</span>";
	}
	else {
		$mhashext = "<span style='color: red'>N/A</span>";
	}
	
	if (version_compare(PHP_VERSION, '4.1.0', '>=')) {
		$phpv = '<span style="color: green">Yes</span>';
	}
	else {
		$phpv = '<span style="color: red">No</span>';
	}
	
	$webserver = get_webserver();
	?>
<table class="border">
<tr><td class="obox">Credits</td></tr>
<tr><td class="mbox">
	<p class="center">
	    <small><a href="http://www.mamo-net.de" target="_blank">MaMo Net</a> proudly presents...</small><br />
	    <big style="font-weight: bold; color: #336699;">Viscacha <?php echo $config['version'];?></big>
	</p>
	<br class="minibr" />
	<p>
		<strong>Crew</strong>:<br />
		Software engineer: <a href="http://www.mamo-net.de" target="_blank">Matthias Mohr</a><br />
		<em>Thanks to all testers and users who reported bugs to me.</em>
	</p>
	<br class="minibr" />
	<p>
		<strong>Used Scripts</strong> (most are modified):
		<ul>
		<li><a href="http://www.fpdf.org" target="_blank">FPDF 1.53 by Olivier Plathey</a> (PDF Creation, Freeware)</li>
		<li><a href="http://www.phpclasses.org/browse/author/152329.html" target="_blank">Roman Numeral Conversion by Huda M Elmatsani</a> (Roman Numeral Conversion; Freeware)</li>
		<li><a href="http://www.phpclasses.org/browse/author/152329.html" target="_blank">Image Converter by Huda M Elmatsani</a> (Convert Images; Freeware)</li>
		<li><a href="http://www.flaimo.com" target="_blank">vCard-Class 1.001 by Michael Wimmer</a> (vCard Output; Unspecified)</li>
		<li><a href="http://www.phpconcept.net" target="_blank">PclZip Library 2.5 by Vincent Blavet</a> (Zip File Handling; LPGL)</li>
		<li><a href="http://qbnz.com/highlighter" target="_blank">GeSHi 1.0.7.13 by Nigel McNie</a> (Syntax Highlighting; GPL)</li>
		<li><a href="http://magpierss.sourceforge.net" target="_blank">MagPieRSS 0.72 by kellan</a> (Parsing Newsfeeds; GPL)</li>
		<li><a href="http://phpmailer.sourceforge.net/" target="_blank">PHPMailer 1.73 by Brent R. Matzelle and SMTP Class 1.02 by Chris Ryan</a> (Sending E-Mails with SMTP; LGPL)</li>
		<li><a href="http://cjphp.netflint.net" target="_blank">Class.Jabber.PHP v0.4.3a by Nathan Fritz</a> (Jabber Messages; GPL)</li>
		<li><a href="http://www.bitfolge.de" target="_blank">FeedCreator v1.7.x by Kai Blankenhorn</a> (Creating Newsfeeds; LGPL)</li>
		<li><a href="http://spellerpages.sourceforge.net/" target="_blank">Speller Pages 0.5.1 by James Shimada</a> (Spell Checker User Interface; LPGL)</li>
		<li><a href="http://pear.php.net/package/PHP_Compat" target="_blank">PHP_Compat 1.5.0 by Aidan Lister, Stephan Schmidt</a> (PHP Core Functions; PHP)</li>
		<li><a href="http://www.phpclasses.org/browse/author/169072.html" target="_blank">ServerNavigator 1.0 by Carlos Reche</a> (Basic File Manager; GPL)</li>
		<li><a href="http://www.phpclasses.org/browse/author/169072.html" target="_blank">PowerGraphic 1.0 by Carlos Reche</a> (Charts &amp; Diagrams; GPL)</li>
		<li><a href="http://www.invisionpower.com" target="_blank">PHP TAR by Matt Mecham</a> (TAR File Handling; GPL)</li>
		<li><a href="http://www.phpclasses.org/browse/author/98157.html" target="_blank">Advanced FTP client class (Build 2005-08-01) by Alexey Dotsenko</a> (PHP FTP Client; Freely Distributable)</li>
		<li>and many more code snippets, classes and functions...</li>
		</ul>
		<br class="minibr" />
		<strong>Used Images</strong>:
		<ul>
		<li><a href="http://www.everaldo.com" target="_blank">Crystal icons by Everaldo Coelho, www.everaldo.com</a></li>
		<li><a href="http://www.smileyarchiv.net" target="_blank">Smileys by Matthias Mohr, Smileyarchiv.net</a></li>
		</ul>
		<br class="minibr" />
		<strong>My Server</strong>:
		<ul>
		<li>PHP-Version: <?php echo PHP_VERSION; ?>, compatible: <?php echo $phpv; ?></li>
		<li>Server-Software: <?php echo $webserver; ?></li>
		</ul>
		<br class="minibr" />
		<strong>My PHP-Extensions</strong>:
		<ul>
		<li>PCRE-Extension: <?php echo $relibext; ?></li>
		<li>MySQL-Extension: <?php echo $mylibext; ?></li>
		<li>GD-Extension: <?php echo $gdlibext; ?></li>
		<li>Zlib-Extension: <?php echo $zlibext; ?></li>
		<li>XML-Extension: <?php echo $xmllibext; ?></li>
		<li>PSpell-Extension: <?php echo $pslibext; ?></li>
		<li>IconV-Extension: <?php echo $ivlibext; ?></li>
		<li>MBString-Extension: <?php echo $mblibext; ?></li>
		<li>MHash-Extension: <?php echo $mhashext; ?></li>
		</ul>
	</p>
	<br class="minibr" />
	<p>
		<strong>License</strong>:<br />
		Viscacha is Free Software released under the GNU/GPL License.<br />
		Some parts of this Software are released under other Licenses.<br />
		You can read the Licence Texts here:
		<ul>
		<li><a href="admin.php?action=misc&amp;job=license&v=gpl">GNU/GPL License</li>
		<li><a href="admin.php?action=misc&amp;job=license&v=lgpl">GNU/LGPL License</li>
		<li><a href="admin.php?action=misc&amp;job=license&v=bsd">BSD License</li>
		<li><a href="admin.php?action=misc&amp;job=license&v=php">PHP License</li>
		</ul>
	</p>
</td></tr>
</table>
	<?php
}
elseif ($job == 'license') {
	$license = $gpc->get('v', str, 'gpl');
	$file = "admin/data/licenses/".$license.'.txt';
	if (file_exists($file)) {
		$content = file_get_contents($file);
	}
	else {
		$content = 'License nor found.';
	}
	echo head();
	?>
<table class="border">
<tr><td class="obox">License: <?php echo strtoupper($license); ?></td></tr>
<tr><td class="mbox"><pre>
<?php echo htmlspecialchars($content); ?>
</pre></td></tr>
</table>
	<?php
	echo foot();
}
?>
