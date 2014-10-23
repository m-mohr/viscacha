<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// TR: MultiLangAdmin
$lang->group("admin/misc");

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
				$cache = $scache->load($name);
				$result[$name]['rebuild'] = $cache->rebuildable();
				if ($cache->administrable() == false) {
					unset($result[$name]);
				}
			}
		}
	}
	ksort($result);

	$pluginsize = 0;
	$files = 0;
	$dir = 'cache/modules/';
	if ($dh = @opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if (strpos($file, '.php') !== false) {
				$files++;
				$pluginsize += filesize($dir.$file);
			}
		}
		closedir($dh);
	}
	?>
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="4">
   <span style="float: right;">
   	<a class="button" href="admin.php?action=misc&amp;job=cache_refresh_all"><?php echo $lang->phrase('admin_misc_rebuild_all'); ?></a>
   	<a class="button" href="admin.php?action=misc&amp;job=cache_delete_all"><?php echo $lang->phrase('admin_misc_delete_all'); ?></a>
   </span>
   <b><?php echo $lang->phrase('admin_misc_cache_manager'); ?></b></td>
  </tr>
  <tr>
   <td class="ubox" width="35%"><?php echo $lang->phrase('admin_misc_cache_name'); ?></td>
   <td class="ubox" width="10%"><?php echo $lang->phrase('admin_misc_file_size'); ?></td>
   <td class="ubox" width="15%"><?php echo $lang->phrase('admin_misc_approx_age'); ?></td>
   <td class="ubox" width="40%"><?php echo $lang->phrase('admin_misc_options'); ?></td>
  </tr>
  <tr>
   <td class="mbox"><b><?php echo $lang->phrase('admin_misc_plugin'); ?></b> (<?php echo $files.' '.$lang->phrase('admin_misc_files'); ?>)</td>
   <td class="mbox" nowrap="nowrap" align="right"><?php echo iif ($pluginsize > 0, formatFilesize($pluginsize), '-'); ?></td>
   <td class="mbox" nowrap="nowrap">-</td>
   <td class="mbox"><a class="button" href="admin.php?action=misc&amp;job=cache_delete_plugins"><?php echo $lang->phrase('admin_misc_delete_cache'); ?></a></td>
  </tr>
  <?php foreach ($result as $name => $row) { $age = fileAge($row['age']); ?>
  <tr>
   <td class="mbox"><?php echo $name; ?></td>
   <td class="mbox" nowrap="nowrap" align="right"><?php echo iif ($row['cached'], formatFilesize($row['size']), '-'); ?></td>
   <td class="mbox" nowrap="nowrap"><?php echo iif($row['cached'], $lang->phrase('admin_misc_approx_x'), '-'); ?></td>
   <td class="mbox">
   <?php if ($row['cached']) { ?>
   <a class="button" href="admin.php?action=misc&amp;job=cache_view&amp;file=<?php echo $name; ?>"><?php echo $lang->phrase('admin_misc_view_contents'); ?></a>
   <a class="button" href="admin.php?action=misc&amp;job=cache_delete&amp;file=<?php echo $name; ?>"><?php echo $lang->phrase('admin_misc_delete_cache'); ?></a>
   <?php } if ($row['rebuild']) { ?>
   <a class="button" href="admin.php?action=misc&amp;job=cache_refresh&amp;file=<?php echo $name; ?>"><?php echo $lang->phrase('admin_misc_rebuild_cache'); ?></a>
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
   <td class="obox"><b><?php echo $lang->phrase('admin_misc_cache_manager'); ?> &raquo; <?php echo $file; ?></b></td>
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
	$name = $gpc->get('file', str);
	$file = $name.'.inc.php';
	echo head();
	$not = true;
	if (file_exists('classes/cache/'.$file)) {
		$cache = $scache->load($name);
		$cache->delete();
		if ($job == 'cache_refresh') {
			$cache->load();
			if ($cache->rebuildable() == false){
				$not = false;
			}
		}
	}
	else {
		if (file_exists('cache/'.$file)) {
			$filesystem->unlink('cache/'.$file);
			if ($job == 'cache_refresh') {
				$not = false;
			}
			else {
				$not = true;
			}
		}
		else {
			$not = null;
		}
	}
	if ($not == null) {
		error('admin.php?action=misc&job=cache', $lang->phrase('admin_misc_cacha_file_not_specified'));
	}
	else if ($not == false) {
		error('admin.php?action=misc&job=cache', $lang->phrase('admin_misc_cache_file_deleted_will_created_when_needed'));
	}
	else {
		ok('admin.php?action=misc&job=cache', iif($job == 'cache_refresh', $lang->phrase('admin_misc_cache_file_rebuilt'), $lang->phrase('admin_misc_cache_file_deleted_will_rebuilt_when_needed')));
	}
}
elseif ($job == 'cache_delete_all' || $job == 'cache_refresh_all') {
	echo head();
	$classesdir = 'classes/cache/';
	$cachedir = 'cache/';
	$dir = iif ($job == 'cache_refresh_all', $classesdir, $cachedir);
	if ($dh = @opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if (strpos($file, '.inc.php') !== false) {
				$fileTrim = str_replace('.inc.php', '', $file);
				if (file_exists($classesdir.$file)) {
					$cache = $scache->load($fileTrim);
					$cache->delete();
					if ($job == 'cache_refresh_all' && $cache->rebuildable() == true) {
						$cache->load();
					}
				}
				else {
					$filesystem->unlink($cachedir.$file);
				}
			}
		}
		closedir($dh);
	}
	ok('admin.php?action=misc&job=cache', iif($job == 'cache_refresh_all', $lang->phrase('admin_misc_cache_files_rebuilt_some_deleted'), $lang->phrase('admin_misc_cache_deleted_rebuilt_when_needed')));
}
elseif ($job == 'cache_delete_plugins') {
	echo head();
	$dir = 'cache/modules/';
	if ($dh = @opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if (strpos($file, '.php') !== false) {
				$filesystem->unlink($dir.$file);
			}
		}
		closedir($dh);
	}
	ok('admin.php?action=misc&job=cache', $lang->phrase('admin_misc_cache_deleted_rebuilt_when_needed'));
}
elseif ($job == 'onlinestatus') {
	echo head();
	$b = file_get_contents('data/imservers.php');
	?>
<form name="form" method="post" action="admin.php?action=misc&job=onlinestatus2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_misc_online_status_server'); ?></b></td>
  </tr>
  <tr>
   <td class="mbox" width="30%">
   <?php echo $lang->phrase('admin_misc_server'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_misc_per_line_one_user'); ?><br /><a href="http://osi.viscacha.org/" target="_blank"><?php echo $lang->phrase('admin_misc_online_status_server_overview'); ?></a></span>
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
   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_misc_online_status_server_info'); ?></b></td>
  </tr>
  <tr>
   <td class="mbox">
   <p><strong><?php echo $lang->phrase('admin_misc_online_status_meaning_title'); ?></strong><br />
   <?php echo $lang->phrase('admin_misc_online_status_meaning'); ?></p>
   <p><strong><?php echo $lang->phrase('admin_misc_from_where_data_for_online_status'); ?></strong><br />
   <?php echo $lang->phrase('admin_misc_from_where_data_for_online_status_info'); ?>
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
	ok('admin.php?action=misc&job=onlinestatus');
}
elseif ($job == 'sessionmails') {
	echo head();
	$mails = file_get_contents('data/sessionmails.php');
	?>
<form name="form" method="post" action="admin.php?action=misc&job=sessionmails2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_misc_disposable_mail_address_provider'); ?></b></td>
  </tr>
  <tr>
   <td class="mbox" width="30%">
   <?php echo $lang->phrase('admin_misc_provider_domain'); ?><br />
   <span class="stext"><?php echo $lang->phrase('admin_misc_per_line_one_domain'); ?><br /><?php echo $lang->phrase('admin_misc_provider_domain_format'); ?></span>
   </td>
   <td class="mbox" width="70%"><textarea name="mails" rows="10" cols="90"><?php echo $mails; ?></textarea></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_misc_submit'); ?>"></td>
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
	ok('admin.php?action=misc&job=sessionmails', $lang->phrase('admin_misc_data_saved'));
}
elseif ($job == 'feedcreator') {
	echo head();
	$data = file('data/feedcreator.inc.php');
?>
<form name="form" method="post" action="admin.php?action=misc&job=feedcreator_delete">
 <table class="border">
  <tr>
   <td class="obox" colspan="5"><?php echo $lang->phrase('admin_misc_creation_export_of_feeds'); ?> (<?php echo count($data); ?>)</b></td>
  </tr>
  <tr>
   <td class="ubox" width="10%"><?php echo $lang->phrase('admin_misc_delete'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /> <?php echo $lang->phrase('admin_misc_all'); ?></span></td>
   <td class="ubox" width="30%"><?php echo $lang->phrase('admin_misc_name'); ?></td>
   <td class="ubox" width="30%"><?php echo $lang->phrase('admin_misc_file_class'); ?></td>
   <td class="ubox" width="15%"><?php echo $lang->phrase('admin_misc_shown'); ?></td>
   <td class="ubox" width="15%"><?php echo $lang->phrase('admin_misc_download'); ?></td>
  </tr>
<?php
foreach ($data as $r) {
	$row = explode('|', $r);
	$row = array_map('trim', $row);
?>
  <tr>
   <td class="mbox" width="10%"><input type="checkbox" name="delete[]" value="<?php echo $row[0]; ?>"></td>
   <td class="mbox" width="30%"><a href="external.php?action=<?php echo $row[0]; ?>" target="_blank" title="<?php echo $lang->phrase('admin_misc_show_feed'); ?>"><?php echo $row[2]; ?></a></td>
   <td class="mbox" width="30%"><?php echo $row[1]; ?> (<?php echo $row[0]; ?>)</td>
   <td class="mbox" width="15%"><?php echo noki($row[3]); ?> <a class="button" href="admin.php?action=misc&job=feedcreator_active&id=<?php echo $row[0]; ?>&key=3"><?php echo $lang->phrase('admin_misc_change'); ?></a></td>
   <td class="mbox" width="15%"><?php echo noki($row[4]); ?> <a class="button" href="admin.php?action=misc&job=feedcreator_active&id=<?php echo $row[0]; ?>&key=4"><?php echo $lang->phrase('admin_misc_change'); ?></a></td>
  </tr>
<?php } ?>
  <tr>
   <td class="ubox" width="100%" colspan="5" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_misc_delete'); ?>"></td>
  </tr>
 </table>
</form>
<br>
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=misc&job=feedcreator_add">
<table class="border">
<tr><td class="obox" colspan="2"><?php echo $lang->phrase('admin_misc_add_new_feed_creator'); ?></td></tr>
<tr class="mbox"><td><?php echo $lang->phrase('admin_misc_upload_file'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_misc_permitted_file_types_php'); ?></span></td><td><input type="file" name="upload" size="50" /></td></tr>
<tr class="mbox"><td><?php echo $lang->phrase('admin_misc_upload_name'); ?></td><td><input type="text" name="name" size="50" /></td></tr>
<tr class="mbox"><td><?php echo $lang->phrase('admin_misc_name_of_class'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_misc_name_of_class_info'); ?></span></td><td><input type="text" name="class" size="50" /></td></tr>
<tr class="mbox"><td><?php echo $lang->phrase('admin_misc_upload_shown'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_misc_shown_info'); ?></span></td><td><input type="checkbox" name="active" value="1" /></td></tr>
<tr class="mbox"><td><?php echo $lang->phrase('admin_misc_upload_download'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_misc_download_feed_info'); ?></span></td><td><input type="checkbox" name="dl" value="1" /></td></tr>
<tr><td class="ubox" colspan="2" align="center"><input accesskey="s" type="submit" value="<?php echo $lang->phrase('admin_misc_upload_add'); ?>" /></td></tr>
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
	sendStatusCode(307, $config['furl'].'/admin.php?action=misc&job=feedcreator');

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
		array_push($inserterrors, $lang->phrase('admin_misc_file_does_not_exist'));
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
				error('admin.php?action=misc&job=feedcreator', $lang->phrase('admin_misc_could_not_parse_class_name'));
			}
		}
		$data[] = "{$class}|{$file}|{$name}|{$active}|{$dl}";
		$filesystem->file_put_contents('data/feedcreator.inc.php', implode("\n", $data));
		ok('admin.php?action=misc&job=feedcreator');
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
	ok('admin.php?action=misc&job=feedcreator', $lang->phrase('admin_misc_files_deleted'));
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
   <td class="obox"><?php echo $lang->phrase('admin_misc_captcha_manager'); ?></td>
  </tr>
  <tr>
   <td class="mbox">
   <ul>
   <li style="padding: 3px;"><?php echo $lang->phrase('admin_misc_bg_pictures'); ?> <?php echo $noises; ?> <a class="button" href="admin.php?action=misc&amp;job=captcha_noises"><?php echo $lang->phrase('admin_misc_administrate'); ?></a></li>
   <li style="padding: 3px;"><?php echo $lang->phrase('admin_misc_fonts'); ?> <?php echo $fonts; ?> <a class="button" href="admin.php?action=misc&amp;job=captcha_fonts"><?php echo $lang->phrase('admin_misc_administrate'); ?></a></li>
   <li style="padding: 3px;"><a href="admin.php?action=settings&amp;job=captcha"><?php echo $lang->phrase('admin_misc_settings'); ?></a></li>
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
	ok('admin.php?action=misc&job=captcha_noises', $lang->phrase('admin_misc_bg_pictures_deleted'));
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
   <td class="obox" colspan="3"><?php echo $lang->phrase('admin_misc_captcha_manager'); ?> &raquo; <?php echo $lang->phrase('admin_misc_bg_noises'); ?></td>
  </tr>
  <tr>
   <td class="ubox" width="10%"><?php echo $lang->phrase('admin_misc_delete'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /> <?php echo $lang->phrase('admin_misc_all'); ?></span></td>
   <td class="ubox" width="90%"><?php echo $lang->phrase('admin_misc_bg_picture_preview'); ?></td>
  </tr>
  <?php foreach ($fonts as $path) { ?>
  <tr>
   <td class="mbox"><input type="checkbox" name="delete[]" value="<?php echo basename($path, ".jpg"); ?>" /></td>
   <td class="mbox"><img border="1" src="admin.php?action=misc&job=captcha_noises_view&file=<?php echo basename($path, ".jpg"); ?>" /></td>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_misc_delete'); ?>"></td>
  </tr>
 </table>
</form>
<br />
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=explorer&job=upload&cfg=captcha_noises">
 <table class="border" cellpadding="3" cellspacing="0" border="0">
  <tr><td class="obox"><?php echo $lang->phrase('admin_misc_upload_new_bg_noises'); ?></td></tr>
  <tr>
   <td class="mbox">
	<?php echo $lang->phrase('admin_misc_upload_new_bg_noises_info'); ?><br /><br />
	<strong><?php echo $lang->phrase('admin_misc_upload_file'); ?></strong>
	<br /><input type="file" name="upload_0" size="40" />
   </td>
  </tr>
  <tr><td class="ubox" align="center"><input accesskey="s" type="submit" value="<?php echo $lang->phrase('admin_misc_upload'); ?>" /></td></tr>
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
	ok('admin.php?action=misc&job=captcha_fonts', $lang->phrase('admin_misc_fonts_deleted'));
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
   <td class="obox" colspan="3"><?php echo $lang->phrase('admin_misc_captcha_manager_fonts'); ?></td>
  </tr>
  <tr>
   <td class="ubox" width="10%"><?php echo $lang->phrase('admin_misc_delete'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /> <?php echo $lang->phrase('admin_misc_all'); ?></span></td>
   <td class="ubox" width="90%"><?php echo $lang->phrase('admin_misc_front_preview'); ?></td>
  </tr>
  <?php foreach ($fonts as $path) { $name = basename($path, ".ttf"); ?>
  <tr>
   <td class="mbox"><input type="checkbox" name="delete[]" value="<?php echo $name; ?>" /></td>
   <td class="mbox"><img border="1" alt="" src="images.php?action=textimage&amp;file=<?php echo $name; ?>&amp;text=1234567980ABCDEF&amp;size=30" /></td>
  </tr>
  <?php } ?>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_misc_delete'); ?>"></td>
  </tr>
 </table>
</form>
<br />
<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=explorer&job=upload&cfg=captcha_fonts">
 <table class="border" cellpadding="3" cellspacing="0" border="0">
  <tr><td class="obox"><?php echo $lang->phrase('admin_misc_upload_new_font'); ?></td></tr>
  <tr>
   <td class="mbox">
	<?php echo $lang->phrase('admin_misc_upload_new_font_info'); ?><br />
	<strong><?php echo $lang->phrase('admin_misc_upload_file'); ?></strong>
	<br /><input type="file" name="upload_0" size="40" />
   </td>
  </tr>
  <tr><td class="ubox" align="center"><input accesskey="s" type="submit" value="<?php echo $lang->phrase('admin_misc_upload'); ?>" /></td></tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == "credits") {
	echo head();

	$ext = get_loaded_extensions();
	if (in_array("zlib", $ext)) {
		$zlibext = "<span style='color: green'>".$lang->phrase('admin_misc_ok')."</span>";
	}
	else {
		$zlibext = "<span style='color: red'>".$lang->phrase('admin_misc_n_a')."</span>";
	}
	if (in_array("mysql", $ext)) {
		$mylibext = "<span style='color: green'>".$lang->phrase('admin_misc_ok')."</span>";
	}
	else {
		$mylibext = "<span style='color: red'>".$lang->phrase('admin_misc_n_a')."</span>";
	}
	if (in_array("mysqli", $ext)) {
		$myilibext = "<span style='color: green'>".$lang->phrase('admin_misc_ok')."</span>";
	}
	else {
		$myilibext = "<span style='color: red'>".$lang->phrase('admin_misc_n_a')."</span>";
	}
	if (in_array("pcre", $ext)) {
		$relibext = "<span style='color: green'>".$lang->phrase('admin_misc_ok')."</span>";
	}
	else {
		$relibext = "<span style='color: red'>".$lang->phrase('admin_misc_n_a')."</span>";
	}
	if (in_array("gd", $ext)) {
		$gdlibext = "<span style='color: green'>".$lang->phrase('admin_misc_ok')."</span>";
	}
	else {
		$gdlibext = "<span style='color: red'>".$lang->phrase('admin_misc_n_a')."</span>>";
	}
	if (in_array("xml", $ext)) {
		$xmllibext = "<span style='color: green'>".$lang->phrase('admin_misc_ok')."</span>";
	}
	else {
		$xmllibext = "<span style='color: red'>".$lang->phrase('admin_misc_n_a')."</span>";
	}
	if (in_array("iconv", $ext)) {
		$ivlibext = "<span style='color: green'>".$lang->phrase('admin_misc_ok')."</span>";
	}
	else {
		$ivlibext = "<span style='color: red'>".$lang->phrase('admin_misc_n_a')."</span>";
	}
	if (in_array("mbstring", $ext)) {
		$mblibext = "<span style='color: green'>".$lang->phrase('admin_misc_ok')."</span>";
	}
	else {
		$mblibext = "<span style='color: red'>".$lang->phrase('admin_misc_n_a')."</span>";
	}
	if (in_array("mhash", $ext)) {
		$mhashext = "<span style='color: green'>".$lang->phrase('admin_misc_ok')."</span>";
	}
	else {
		$mhashext = "<span style='color: red'>".$lang->phrase('admin_misc_n_a')."</span>";
	}

	if (version_compare(PHP_VERSION, '5.0.0', '>=')) {
		$phpv = '<span style="color: green">'.$lang->phrase('admin_misc_yes').'</span>';
	}
	else {
		$phpv = '<span style="color: red">'.$lang->phrase('admin_misc_no').'</span>';
	}

	$webserver = get_webserver();
	?>
<table class="border">
<tr><td class="obox">Credits</td></tr>
<tr><td class="mbox">
	<p class="center">
		<small><a href="http://www.viscacha.org" target="_blank">The Viscacha Project</a> proudly presents...</small><br />
		<big style="font-weight: bold; color: #336699;">Viscacha <?php echo $config['version'];?></big>
	</p>
	<br class="minibr" />
	<p>
		<strong>Crew</strong>:<br />
		Software engineer: <a href="http://www.mamo-net.de" target="_blank">Matthias Mohr</a> et al.<br />
		<em>Thanks to all testers and users who reported bugs and helped while development.</em>
	</p>
	<br class="minibr" />
	<p>
		<strong>Used Scripts</strong>:
		<ul>
		<li><a href="http://www.phpclasses.org/browse/author/152329.html" target="_blank">Roman Numeral Conversion by Huda M Elmatsani</a> (Roman Numeral Conversion; Freeware)</li>
		<li><a href="http://www.phpconcept.net" target="_blank">PclZip Library 2.6 by Vincent Blavet</a> (Zip File Handling; LPGL)</li>
		<li><a href="http://qbnz.com/highlighter" target="_blank">GeSHi 1.0.8.2 by Nigel McNie and Benny Baumann</a> (Syntax Highlighting; GPL)</li>
		<li><a href="http://magpierss.sourceforge.net" target="_blank">MagPieRSS 0.72 by kellan</a> (Parsing Newsfeeds; GPL)</li>
		<li><a href="http://phpmailer.sourceforge.net/" target="_blank">PHPMailer 2.0.3 (with POP3 and SMTP) by Andy Prevost</a> (Sending E-mails; LGPL)</li>
		<li><a href="http://www.bitfolge.de" target="_blank">FeedCreator v1.7.x by Kai Blankenhorn</a> (Creating Newsfeeds; LGPL)</li>
		<li><a href="http://pear.php.net/package/PHP_Compat" target="_blank">PHP_Compat 1.6.0a2 by Aidan Lister, Stephan Schmidt</a> (PHP Core Functions; PHP)</li>
		<li><a href="http://www.phpclasses.org/browse/author/169072.html" target="_blank">PowerGraphic 1.0 by Carlos Reche</a> (Charts &amp; Diagrams; GPL)</li>
		<li><a href="http://www.invisionpower.com" target="_blank">PHP TAR by Matt Mecham</a> (TAR File Handling; GPL)</li>
		<li><a href="http://www.phpclasses.org/browse/author/98157.html" target="_blank">Advanced FTP client class (Build 2008-09-17) by Alexey Dotsenko</a> (PHP FTP Client; Freely Distributable)</li>
		<li><a href="http://phlymail.com/en/downloads/idna/" target="_blank">Net_IDNA 0.6.0 by phlyLabs</a> (Punycode Converter; LGPL)</li>
		<li><a href="http://www.openwebware.com" target="_blank">openWYSIWYG 1.4.7 by openwebware.com</a> (WYSIWYG Editor; LGPL)</li>
		<li>and many more code snippets, classes and functions...</li>
		</ul>
		<br class="minibr" />
		<strong>Used Images</strong>:
		<ul>
		<li><a href="http://www.everaldo.com" target="_blank">Crystal icons by Everaldo Coelho, www.everaldo.com</a></li>
		<li><a href="http://www.smileyarchiv.net" target="_blank">Smileys by Matthias Mohr, Smileyarchiv.net</a></li>
		</ul>
		<br class="minibr" />
		<strong><?php echo $lang->phrase('admin_misc_my_server'); ?></strong>:
		<ul>
		<li><?php echo $lang->phrase('admin_misc_php_version'); ?> <?php echo PHP_VERSION; ?>, <?php echo $lang->phrase('admin_misc_compatible').' '.$phpv; ?></li>
		<li><?php echo $lang->phrase('admin_misc_server_software'); ?> <?php echo $webserver; ?></li>
		</ul>
		<br class="minibr" />
		<strong><?php echo $lang->phrase('admin_misc_my_php_extensions'); ?></strong>:
		<ul>
		<li>PCRE-<?php echo $lang->phrase('admin_misc_extension').' '.$relibext; ?></li>
		<li>MySQL-<?php echo $lang->phrase('admin_misc_extension').' '.$mylibext; ?> / MySQLi-<?php echo $lang->phrase('admin_misc_extension').' '.$myilibext; ?></li>
		<li>GD-<?php echo $lang->phrase('admin_misc_extension').' '.$gdlibext; ?></li>
		<li>Zlib-<?php echo $lang->phrase('admin_misc_extension').' '.$zlibext; ?></li>
		<li>XML-<?php echo $lang->phrase('admin_misc_extension').' '.$xmllibext; ?></li>
		<li>IconV-<?php echo $lang->phrase('admin_misc_extension').' '.$ivlibext; ?></li>
		<li>MBString-<?php echo $lang->phrase('admin_misc_extension').' '.$mblibext; ?></li>
		<li>MHash-<?php echo $lang->phrase('admin_misc_extension').' '.$mhashext; ?></li>
		</ul>
	</p>
	<br class="minibr" />
	<p>
		<strong>License</strong>:<br />
		Viscacha is Free Software released under the GNU/GPL License.<br />
		Some parts of this Software are released under other Licenses.<br />
		You can read the licence texts here:
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
		$content = $lang->phrase('admin_misc_license_not_forun');
	}
	echo head();
	?>
<table class="border">
<tr><td class="obox"><?php echo $lang->phrase('admin_misc_license'); ?> <?php echo strtoupper($license); ?></td></tr>
<tr><td class="mbox"><pre><?php echo htmlspecialchars($content); ?></pre></td></tr>
</table>
	<?php
	echo foot();
}
?>