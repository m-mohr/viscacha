<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "misc.php") die('Error: Hacking Attempt');

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
				$result[] = array(
				'file' => $nfo['basename'],
				'size' => filesize($dir.$file),
				'age' => time()-filemtime($dir.$file)
				);
			}
		}
	}
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="4">
   <span style="float: right;">[<a href="admin.php?action=misc&job=cache_refresh_all">Refresh All</a>]</span>
   <b>Cache-Manager</b></td>
  </tr>
  <tr>
   <td class="ubox" width="40%">Cache Name</td>
   <td class="ubox" width="10%">Size</td>
   <td class="ubox" width="10%">Age</td>
   <td class="ubox" width="40%">Options</td>
  </tr>
  <?php
  foreach ($result as $row) {
  	$name = str_replace('.inc.php', '', $row['file']);
  ?>
  <tr>
   <td class="mbox" width="40%"><?php echo $name; ?></td>
   <td class="mbox" width="10%" nowrap="nowrap" align="right"><?php echo formatFilesize($row['size']); ?></td>
   <td class="mbox" width="10%" nowrap="nowrap">ca. <?php echo fileAge($row['age']); ?></td>
   <td class="mbox" width="40%" align="right">[<a href="admin.php?action=misc&job=cache_view&file=<?php echo $name; ?>">View Contents</a>] [<a href="admin.php?action=misc&job=cache_refresh&file=<?php echo $name; ?>">Refresh Cache</a>]</td>
  </tr>
  <?php } ?>
 </table>
	<?php
	echo foot();
}
elseif ($job == 'cache_view') {
	$file = $gpc->get('file', str);
	echo head();
	$cache = new scache($file);
	$data = $cache->importdata();
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox"><b>Cache-Manager &raquo; <?php echo $file; ?></b></td>
  </tr>
  <tr> 
   <td class="mbox">
   <pre><?php print_r($data); ?></pre>
   </td>
  </tr>
 </table>
	<?php
	echo foot();
}
elseif ($job == 'cache_refresh') {
	$file = $gpc->get('file', str);
	echo head();
	$cache = new scache($file);
	$cache->deletedata();
	ok('admin.php?action=misc&job=cache', 'Die Cache-Datei wurde gelöscht, deswegen wird diese Datei vorerst nicht mehr in der Übersicht aufgeführt. Wenn sie das nächste mal gebraucht wird, wird der Cache automatisch neu aufgebaut und in der Übersicht wieder angezeigt.');
}
elseif ($job == 'cache_refresh_all') {
	echo head();
	$dir = 'cache';
	if ($dh = @opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if (strpos($file, '.inc.php') !== false) {
				$file = str_replace('.inc.php', '', $file);
		    	$cache = new scache($file);
				$cache->deletedata();
			}
	    }
		closedir($dh);
	}
	ok('admin.php?action=misc&job=cache', 'Die Cache-Dateien wurden gelöscht, deswegen werden diese Dateien vorerst nicht mehr in der Übersicht aufgeführt. Wenn sie das nächste mal gebraucht werden, wird der Cache automatisch neu aufgebaut und in der Übersicht wieder angezeigt.');
}
elseif ($job == 'onlinestatus') {
	echo head();
	$b = file_get_contents('data/imservers.php');
	?>
<form name="form" method="post" action="admin.php?action=members&job=onlinestatus2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2"><b>Onlinestatus-Server</b></td>
  </tr>
  <tr>
   <td class="mbox" width="30%">
   Server:<br />
   <span class="stext">Pro Zeile ein Server.<br /><a href="http://www.onlinestatus.org/forum/usage.php" target="_blank">Onlinestatus-Server-Übersicht</a><br /></span>
   </td>
   <td class="mbox" width="70%"><textarea name="ips" rows="10" cols="90"><?php echo $b; ?></textarea></td> 
  </tr>
  <tr> 
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'onlinestatus2') {
	echo head();
	$filesystem->file_put_contents('data/imservers.php', $gpc->get('template', none));
	ok('admin.php?action=misc&job=onlinestatus', 'Daten wurden gespeichert');
}
elseif ($job == 'sessionmails') {
	echo head();
	$b = file_get_contents('data/sessionmails.php');
	?>
<form name="form" method="post" action="admin.php?action=members&job=sessionmails2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2"><b>Wegwerf-E-Mail-Adressen-Anbieter</b></td>
  </tr>
  <tr>
   <td class="mbox" width="30%">
   Anbieter-Domain:<br />
   <span class="stext">Pro Zeile eine Domain.<br />Format: <code>name.tld</code> (ohne http, www, @, ...)</span>
   </td>
   <td class="mbox" width="70%"><textarea name="ips" rows="10" cols="90"><?php echo $b; ?></textarea></td> 
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
	$filesystem->file_put_contents('data/sessionmails.php', $gpc->get('template', none));
	ok('admin.php?action=misc&job=sessionmails', 'Daten wurden gespeichert');
}
elseif ($job == 'feedcreator') {
	echo head();
	$data = file('data/feedcreator.inc.php');
?>
<form name="form" method="post" action="admin.php?action=misc&job=feedcreator_delete">
 <table class="border">
  <tr> 
   <td class="obox" colspan="5">Feedcreator (<?php echo count($data); ?>)</b></td>
  </tr>
  <tr>
   <td class="ubox" width="10%">Delete</td>
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
   <td class="mbox" width="15%"><?php echo noki($row[3]); ?> [<a href="admin.php?action=misc&job=feedcreator_active&id=<?php echo $row[0]; ?>&key=3">Ändern</a>]</td>
   <td class="mbox" width="15%"><?php echo noki($row[4]); ?> [<a href="admin.php?action=misc&job=feedcreator_active&id=<?php echo $row[0]; ?>&key=4">Ändern</a>]</td>
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
<tr><td class="obox" colspan="2">Add Feedcreator</td></tr>
<tr class="mbox"><td>Datei hochladen:<br /><span class="stext">Erlaubte Dateitypen: .php<br />Maximale Dateigröße: 200 KB</span></td><td><input type="file" name="upload" size="50" /></td></tr>
<tr class="mbox"><td>Name:</td><td><input type="text" name="name" size="50" /></td></tr>
<tr class="mbox"><td>Name der Klasse:<br /><span class="stext">Wenn kein Wert angegeben wird, versucht Viscacha den Namen selbst zu filtern.</span></td><td><input type="text" name="class" size="50" /></td></tr>
<tr class="mbox"><td>Shown:<br /><span class="stext">Gibt an ob dieser Feed angezeigt wird. Dies regelt nicht ob ein Feed aktiv ist oder nicht!</span></td><td><input type="checkbox" name="active" value="1" /></td></tr>
<tr class="mbox"><td>Download:<br /><span class="stext">Gibt an ob dieser Feed zum Download angeboten oder direkt im Browser angezeigt werden soll.</span></td><td><input type="checkbox" name="dl" value="1" /></td></tr>
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
	$dir = realpath('./classes/feedcreator/');
	
	$inserterrors = array();
	require("classes/class.upload.php");
	$my_uploader = new uploader();
	$my_uploader->max_filesize(200*1024);
	if ($my_uploader->upload('upload', array('.php'))) {
		if (strlen($my_uploader->return_error()) > 0) {
			array_push($inserterrors,$my_uploader->return_error());
		}
		$my_uploader->save_file($dir, 2);
		$file = $my_uploader->file['name'];
	}
	else {
		if (strlen($my_uploader->return_error()) > 0) {
			array_push($inserterrors,$my_uploader->return_error());
		}
		else {
			if (count($inserterrors) == 0) {
				array_push($inserterrors, 'An unexpected error occurred');
			}
		}
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
    ok('admin.php?action=misc&job=feedcreator', 'Dateien wurden gelöscht');
}
elseif ($job == "captcha") {
	echo head();
	?>
 <table class="border">
  <tr> 
   <td class="obox">Captcha Manager</td>
  </tr>
  <tr>
   <td class="mbox">Not implemented yet.</td>
  </tr>
 </table>
	<?php
	echo foot();
}
elseif ($job == "spellcheck") {
	echo head();
	?>
 <table class="border">
  <tr> 
   <td class="obox">Spell Checker</td>
  </tr>
  <tr>
   <td class="mbox">Not implemented yet.</td>
  </tr>
 </table>
	<?php
	echo foot();
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
	
	if (version_compare(PHP_VERSION, '4.0.6', '>=')) {
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
	</p><p>
	<strong>Crew</strong>:<br />
	Software engineer: <a href="http://www.mamo-net.de" target="_blank">Matthias Mohr</a><br />
	<em>Thanks to all testers and users who reported bugs to me.</em>
	</p><p>
	<strong>Used Scripts</strong> (most are modified):
	<ul>
	<li><a href="http://www.fpdf.org" target="_blank">FPDF 1.53 by Olivier Plathey</a> (PDF Creation, Freeware)</li>
	<li><a href="http://www.angryrobot.com" target="_blank">File-Upload-Class 2.15 by David Fox, Angryrobot Productions</a> (File-Uploads; BSD)</li>
	<li><a href="http://www.phpclasses.org/browse/author/152329.html" target="_blank">Roman Numeral Conversion by Huda M Elmatsani</a> (Roman Numeral Conversion; Freeware)</li>
	<li><a href="http://www.phpclasses.org/browse/author/152329.html" target="_blank">Image Converter by Huda M Elmatsani</a> (Convert Images; Freeware)</li>
	<li><a href="http://www.flaimo.com" target="_blank">vCard-Class 1.001 by Michael Wimmer</a> (vCard Output; Unspecified)</li>
	<li><a href="http://www.phpconcept.net" target="_blank">PclZip Library 2.4 by Vincent Blavet</a> (Zip File Handling; LPGL)</li>
	<li><a href="http://qbnz.com/highlighter" target="_blank">GeSHi 1.0.7.6 by Nigel McNie</a> (Syntax Highlighting; GPL)</li>
	<li><a href="http://magpierss.sourceforge.net" target="_blank">MagPieRSS 0.72 by kellan</a> (Parsing Newsfeeds; GPL)</li>
	<li><a href="http://phpmailer.sourceforge.net/" target="_blank">PHPMailer 1.73 by Brent R. Matzelle and SMTP Class 1.02 by Chris Ryan</a> (Sending E-Mails with SMTP; LGPL)</li>
	<li><a href="http://cjphp.netflint.net" target="_blank">Class.Jabber.PHP v0.4.2 by Nathan Fritz</a> (Jabber Messages; GPL)</li>
	<li><a href="http://www.bitfolge.de" target="_blank">FeedCreator v1.7.2 by Kai Blankenhorn</a> (Creating Newsfeeds; LGPL)</li>
	<li><a href="http://spellerpages.sourceforge.net/" target="_blank">Speller Pages 0.5.1 by James Shimada</a> (Spell Checker User Interface; LPGL)</li>
	<li><a href="http://pear.php.net/package/PHP_Compat" target="_blank">PHP_Compat 1.5.0 by Aidan Lister, Stephan Schmidt</a> (PHP Core Functions; PHP)</li>
	<li><a href="http://www.phpclasses.org/browse/author/169072.html" target="_blank">ServerNavigator 1.0 by Carlos Reche</a> (Basic File Manager; GPL)</li>
	<li><a href="http://www.phpclasses.org/browse/author/169072.html" target="_blank">PowerGraphic 1.0 by Carlos Reche</a> (Charts &amp; Diagrams; GPL)</li>
	<li><a href="http://www.invisionpower.com" target="_blank">PHP TAR by Matt Mecham</a> (TAR File Handling; GPL)</li>
	<li><a href="http://www.fckeditor.net" target="_blank">FCKeditor 2.1 by Frederico Caldeira Knabben</a> (WYSIWYG Editor; LGPL)</li>
	<li><a href="http://www.phpclasses.org/browse/author/98157.html" target="_blank">Advanced FTP client class (Build 2005-08-01) by Alexey Dotsenko</a> (PHP FTP Client; Freely Distributable)</li>
	<li>and many more code snippets, classes and functions...</li>
	</ul>
	<strong>Used Images</strong>:
	<ul>
	<li><a href="http://www.everaldo.com" target="_blank">Crystal icons by Everaldo Coelho, www.everaldo.com</a></li>
	<li><a href="http://www.smileyarchiv.net" target="_blank">Smileys by Matthias Mohr, Smileyarchiv.net</a></li>
	</ul>
	</p>
	<p>
	<strong>My Server:</strong>:
	<ul>
	<li>PHP-Version: <?php echo PHP_VERSION; ?>, compatible: <?php echo $phpv; ?></li>
	<li>Server-Software: <?php echo $webserver; ?>
	</ul>
	<strong>My PHP-Extensions:</strong>
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
	<p>
	<strong>License:</strong><br />
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
<tr><td class="obox">License: <?php echo strtoupper($v); ?></td></tr>
<tr><td class="mbox"><pre>
<?php echo htmlspecialchars($content); ?>
</pre></td></tr>
</table>
	<?php
	echo foot();
}
?>
