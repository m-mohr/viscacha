<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// BF: MultiLangAdmin
$lang->group("admin/db");

function highlight_sql_query($sql) {
	require_once('classes/class.geshi.php');
	$path = 'classes/geshi';
	$language = 'mysql';
	if (!file_exists($path.'/'.$language.'.php')) {
		$language = 'sql';
		if (!file_exists($path.'/'.$language.'.php')) {
			return null;
		}
	}
	$geshi = new GeSHi($sql, $language, $path);
	$geshi->enable_classes(false);
	$geshi->set_header_type(GESHI_HEADER_DIV);
	$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);
	return $geshi->parse_code();
}

function exec_query_form ($query = '') {
	global $db, $lang;
	$tables = $db->list_tables();
	$lang->assign('maxfilesize', formatFilesize(ini_maxupload()));
?>
<script type="text/javascript" src="templates/editor/bbcode.js"></script>
<form name="form" method="post" action="admin.php?action=db&job=query2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_db_execute_queries'); ?></td>
  </tr>
  <tr>
	<td class="mbox" width="90%">
	<span style="float: right;"><?php echo $lang->phrase('admin_db_semicolon_sep_list'); ?></span><strong><?php echo $lang->phrase('admin_db_queries'); ?></strong>
	<textarea name="query" id="query" rows="10" cols="90" class="texteditor" style="width: 100%; height: 200px;"><?php echo iif(!empty($query), $query); ?></textarea>
	</td>
	<td class="mbox" width="10%">
	<strong><?php echo $lang->phrase('admin_db_tables'); ?></strong>
	<div style="overflow: scroll; height: 200px; width: 150px; border: 1px solid #336699; padding: 2px;">
	<?php foreach ($tables as $table) { ?>
	<a href="javascript:InsertTags('query', '`<?php echo $table; ?>`', '');"><?php echo $table; ?></a><br />
	<?php } ?>
	</div>
	</td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_db_form_submit'); ?>"></td>
  </tr>
 </table>
</form>
<br />
<?php if (empty($query)) { ?>
<form name="form" method="post" action="admin.php?action=db&amp;job=query2&amp;type=1" enctype="multipart/form-data">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
   <td class="obox"><b><?php echo $lang->phrase('admin_db_import_sql_file'); ?></b></td>
  </tr>
  <tr>
  	<td class="mbox">
  	<input type="file" name="upload" size="80" /><br />
  	<span class="stext"><?php echo $lang->phrase('admin_db_allowed_filetypes_max_filesize'); ?></span>
  	</td>
  </tr>
  <tr>
   <td class="ubox" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_db_form_submit'); ?>"></td>
  </tr>
 </table>
</form>
<br />
<?php
	}
}

($code = $plugins->load('admin_db_jobs')) ? eval($code) : null;

if ($job == 'optimize') {
	echo head();
	$tables = array();
	$data_length = 0;
	$index_length = 0;
	$data_free = 0;
	$result = $db->query("SHOW TABLE STATUS");
	while($table = $db->fetch_assoc($result)) {
		$table['Engine'] = (!empty($table['Type']) ? $table['Type'] : $table['Engine']);
		$table['possible'] = (!in_array(strtolower($table['Engine']), array('heap', 'memory')));
		$data_length += $table['Data_length'];
		$index_length += $table['Index_length'];
		$data_free += $table['Data_free'];
		$tables[] = $table;
	}
	?>
<form name="form" method="post" action="admin.php?action=db&job=optimize2">
 <table class="border">
  <tr>
   <td class="obox" colspan="6"><?php echo $lang->phrase('admin_db_repair_and_optimize'); ?></td>
  </tr>
  <tr>
   <td class="ubox" width="7%"><?php echo $lang->phrase('admin_db_repair'); ?></td>
   <td class="ubox" width="7%"><?php echo $lang->phrase('admin_db_optimize'); ?></td>
   <td class="ubox" width="47%"><?php echo $lang->phrase('admin_db_database'); ?></td>
   <td class="ubox" width="13%"><?php echo $lang->phrase('admin_db_data_length'); ?></td>
   <td class="ubox" width="13%"><?php echo $lang->phrase('admin_db_index_length'); ?></td>
   <td class="ubox" width="13%"><?php echo $lang->phrase('admin_db_overhead'); ?></td>
  </tr>
  <tr>
   <td class="mbox"><input type="checkbox" onclick="check_all(this)" name="repair_all" value="repair[]" /></td>
   <td class="mbox"><input type="checkbox" onclick="check_all(this)" name="optimize_all" value="optimize[]" /></td>
   <td class="mbox"><strong><?php echo $lang->phrase('admin_db_all'); ?></strong></td><?php
   	$data_length = formatFilesize($data_length);
   ?>
   <td class="mbox"><strong><?php echo formatFilesize($data_length); ?></strong></td>
   <td class="mbox"><strong><?php echo formatFilesize($index_length); ?></strong></td>
   <td class="mbox"><strong><?php echo formatFilesize($data_free); ?></strong></td>
  </tr>
	<?php foreach ($tables as $table) { ?>
		<tr>
		   <td class="mbox">
		   <?php if ($table['possible']) { ?>
		   	<input type="checkbox" name="repair[]" value="<?php echo $table['Name']; ?>">
		   <?php } else { echo '-'; } ?>
		   </td>
		   <td class="mbox">
		   <?php if ($table['possible']) { ?>
			<input<?php echo iif($table['Data_free'] > 0, ' checked="checked"'); ?> type="checkbox" name="optimize[]" value="<?php echo $table['Name']; ?>">
		   <?php } else { echo '-'; } ?>
		   </td>
		   <td class="mbox"><?php echo $table['Name']; ?></td>
		   <td class="mbox"><?php echo formatFilesize($table['Data_length']); ?></td>
		   <td class="mbox"><?php echo formatFilesize($table['Index_length']); ?></td>
		   <td class="mbox"><?php echo formatFilesize($table['Data_free']); ?></td>
		</tr>
	<?php } ?>
  <tr>
   <td class="ubox" colspan="6" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_db_form_submit'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'optimize2') {
	echo head();

    $rep = $gpc->get('repair', arr_str);
	if (count($rep) > 0) {
		$db->query("REPAIR TABLE ".implode(', ',$rep));
	}
	$opt = $gpc->get('optimize', arr_str);
	if (count($opt) > 0) {
		$db->query("OPTIMIZE TABLE ".implode(', ', $opt));
	}

	ok('admin.php?action=db&job=optimize', $lang->phrase('admin_db_tables_repaired_optimized'));
}
elseif ($job == 'backup') {
	echo head();
	$result = $db->list_tables();
	?>
<form name="form" method="post" action="admin.php?action=db&job=backup2">
 <table class="border">
  <tr>
   <td class="obox" colspan="5">
	<span style="float: right;">
	<a class="button" href="admin.php?action=db&amp;job=restore"><?php echo $lang->phrase('admin_db_restore'); ?></a>
	</span>
	<?php echo $lang->phrase('admin_db_backup_tables'); ?>
	</td>
  </tr>
  <tr>
   <td class="ubox" width="30%"><?php echo $lang->phrase('admin_db_export'); ?></td>
   <td class="ubox" width="70%"><?php echo $lang->phrase('admin_db_options'); ?></td>
  </tr>
  <tr>
    <td class="mbox" width="30%" valign="top">
	<select name="backup[]" size="10"  multiple="multiple">
	<?php foreach ($result as $row) { ?>
    <option value="<?php echo $row; ?>"><?php echo $row; ?></option>
	<?php } ?>
	</select>
   </td>
   <td class="mbox" width="70%" valign="top">
   <input type="checkbox" name="structure" value="1" checked="checked" /> <strong><?php echo $lang->phrase('admin_db_export_structure'); ?></strong><br />
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="drop" value="1" checked="checked" /> <?php echo $lang->phrase('admin_db_add_drop_table'); ?><br /><br />
   <input type="checkbox" name="data" value="1" checked="checked" /> <strong><?php echo $lang->phrase('admin_db_export_data'); ?></strong>
   <br /><br /><input type="checkbox" name="zip" value="1" /> <strong><?php echo $lang->phrase('admin_db_save_as_zip'); ?></strong>
   </td>
  </tr>
  <tr>
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_db_form_submit'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'backup2') {
	@ignore_user_abort(false);
	@set_time_limit(300);
	echo head();
	$tables = $gpc->get('backup', arr_str);
	if (count($tables) == 0) {
		error('admin.php?action=db&job=backup', $lang->phrase('admin_db_not_table_specified'));
	}
	$structure = $gpc->get('structure', int);
	$data = $gpc->get('data', int);
	if (empty($structure) && empty($data)) {
		error('admin.php?action=db&job=backup', $lang->phrase('admin_db_backup_options_invalid'));
	}
	$drop = $gpc->get('drop', int);
	$zip = $gpc->get('zip', int);
	$name = $db->database.'-'.gmdate('Ymd-His');

	$temp = array('zip' => $zip, 'drop' => $drop, 'steps' => 0);
	foreach ($tables as $table) {
		$result = $db->query("SELECT COUNT(*) FROM `{$table}`");
		$count = $db->fetch_num($result);
		$offset = iif($data == 1, 0, -1);
		while ($offset < $count[0]) {
			$temp[] = array(
				'table' => $table,
				'offset' => $offset,
				'structure' => ($offset == 0 && $structure == 1)
			);
			$temp['steps']++;
			$offset += $db->std_limit;
		}
	}
	$x = new CacheItem('backup_'.$name);
	$x->set($temp);
	$x->export();

    // Header
    $table_data = $db->commentdel.' Viscacha '.$db->system.'-Backup'.$db->new_line.
			      $db->commentdel.' Host: '.$db->host.$db->new_line.
			      $db->commentdel.' Database: '.$db->database.$db->new_line.
			      $db->commentdel.' Created: '.gmdate('D, d M Y H:i:s').' GMT'.$db->new_line.
			      $db->commentdel.' Tables: '.implode(', ', $tables).$db->new_line;
	$tfile = "admin/backup/{$name}.sql.tmp";
	$filesystem->file_put_contents($tfile, $table_data);
	$filesystem->chmod($tfile, 0666);

	$pubsteps = $temp['steps']+3;
	$pubstep = 2;
	$url = 'admin.php?action=db&job=backup3&name='.$name;

	$htmlhead .= '<meta http-equiv="refresh" content="2; url='.$url.'">';
	echo head();
	?>
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox"><?php echo $lang->phrase('admin_db_backup_tables_steps'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox">
	   	<div style="width: 600px; border: 1px solid black; background-color: white;"><div style="width: 1px; background-color: steelblue;">&nbsp;</div></div>
	   	<?php echo $lang->phrase('admin_db_progress'); ?> 0%<br /><br />
	   	<?php echo $lang->phrase('admin_db_backup_prepared'); ?>
	   </td>
	  </tr>
	  <tr>
	  	<td class="ubox" align="center"><a href="<?php echo $url; ?>"><?php echo $lang->phrase('admin_db_click_if_no_redirection'); ?></a></td>
	  </tr>
	 </table>
	<?php
	echo foot();
}
elseif ($job == 'backup3') {
	$name = $gpc->get('name', none);
	$step = $gpc->get('step', int);
	$page = $step+1;

	$x = new CacheItem('backup_'.$name);
	$x->import();
	$temp = $x->get();

	if (!isset($temp[$step])) {
		echo head();
		error('admin.php?action=db&job=backup');
		$filesystem->unlink($tfile);
		$x->delete();
	}

	$tfile = "admin/backup/{$name}.sql.tmp";

	$fp = fopen($tfile, 'a');
	if (is_resource($fp)) {
		$data = '';
		$offset_details = '';
		if ($temp[$step]['structure'] == true) {
			$data .= $db->new_line.$db->getStructure($temp[$step]['table'], $temp['drop']).$db->new_line;
		}
		if ($temp[$step]['offset'] >= 0) {
			$data .= $db->new_line.$db->getData($temp[$step]['table'], $temp[$step]['offset']).$db->new_line;
			$offset_details = ' {'.$temp[$step]['offset'].', '.($temp[$step]['offset']+$db->std_limit).'}';
		}

		fwrite($fp, $data);
		fclose($fp);

		$steps = $temp['steps'];
		$pubsteps = $steps+3;
		$pubstep = $step+3;
		$percent = (100/($steps+1))*$page;
		if (isset($temp[$page])) {
			$url = 'admin.php?action=db&amp;job=backup3&amp;name='.$name.'&amp;step='.$page;
		}
		else {
			$url = 'admin.php?action=db&amp;job=backup4&amp;name='.$name;
		}

		$htmlhead .= '<meta http-equiv="refresh" content="1; url='.$url.'">';
		echo head();
		?>
		 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		  <tr>
		   <td class="obox"><?php echo $lang->phrase('admin_db_backup_tables_steps'); ?></td>
		  </tr>
		  <tr>
		   <td class="mbox">
		   	<div style="width: 600px; border: 1px solid black; background-color: white;"><div style="width: <?php echo ceil($percent)*6; ?>px; background-color: steelblue;">&nbsp;</div></div>
		   <?php echo $lang->phrase('admin_db_progress').round($percent, 1); ?>%<br /><br />
		   <?php echo $lang->phrase('admin_db_backup_table_x').' '.$temp[$step]['table'].$offset_details; ?>
		   </td>
		  </tr>
		  <tr>
		  	<td class="ubox" align="center"><a href="<?php echo $url; ?>"><?php echo $lang->phrase('admin_db_click_if_no_redirection'); ?></a></td>
		  </tr>
		 </table>
		<?php
		echo foot();
	}
	else {
		error('admin.php?action=db&job=backup', $lang->phrase('admin_db_backup_missing_permissions'));
		$filesystem->unlink($tfile);
		$x->delete();
	}

}
elseif ($job == 'backup4') {
	$name = $gpc->get('name', none);

	$x = new CacheItem('backup_'.$name);
	$x->import();
	$temp = $x->get();
	$x->delete();

    // Speichern der Backup-Datei
	$tfile = "admin/backup/{$name}.sql.tmp";
	$file = "admin/backup/{$name}.sql";
	$filesystem->rename($tfile, $file);

	$ok = $lang->phrase('admin_db_backup_successfully_created');
    if (!empty($temp['zip'])) {
    	$zipfile = "admin/backup/{$name}.zip";
		require_once('classes/class.zip.php');
		$archive = new PclZip($zipfile);
		$v_list = $archive->create($file, PCLZIP_OPT_REMOVE_PATH, dirname($file));

        if ($v_list == 0) {
        	$ok = $lang->phrase('admin_db_zip_error_saved_txt').'<br />'.$lang->phrase('admin_db_error').' '.$archive->errorInfo(true);
    	}
    	else {
    		$filesystem->unlink($file);
    		$file = $zipfile;
    	}
    }

	$x->delete();

	$pubstep = $pubsteps = $temp['steps']+3;

	echo head();
	?>
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox"><?php echo $lang->phrase('admin_db_backup_tables_steps'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox">
	   	<div style="width: 600px; border: 1px solid black; background-color: white;"><div style="width: 600px; background-color: steelblue;">&nbsp;</div></div>
	    <?php echo $lang->phrase('admin_db_progress'); ?> 100%<br /><br />
	    <?php echo $ok; ?>
	   </td>
	  </tr>
	  <tr>
	  	<td class="mbox" align="center">
	  		<a class="button" href="admin.php?action=db&amp;job=download&amp;file=<?php echo basename($file); ?>"><?php echo $lang->phrase('admin_db_download'); ?></a>
	  		<a class="button" href="admin.php?action=db&amp;job=restore"><?php echo $lang->phrase('admin_db_back_to_restore'); ?></a>
	  	</td>
	  </tr>
	 </table>
	<?php
	echo foot();
}
elseif ($job == 'restore_info') {
	$mem_limit = @ini_get('memory_limit');
	if (empty($mem_limit)) {
		$mem_limit = @get_cfg_var('memory_limit');
	}
	$mem_limit = intval($mem_limit)*1024*1024;
	$ziplimit = $mem_limit / 3;
	$sqllimit = $mem_limit / 1.5;

	$dir = "./admin/backup/";
	$file = $gpc->get('file', none);

	$nfo = pathinfo($dir.$file);
    if (strtolower($nfo['extension']) == 'zip') {
		require_once('classes/class.zip.php');
		$archive = new PclZip($dir.$file);
		if (($list = $archive->listContent()) != 0) {
			if ($list[0]['size'] < $ziplimit) {
				$data = $archive->extractByIndex($list[0]['index'], PCLZIP_OPT_EXTRACT_AS_STRING);
				$data[0]['content'] = preg_split("/\r\n|\r|\n/", $data[0]['content']);
				if (count($data[0]['content']) > 0) {
					$header = array();
		            foreach ($data[0]['content'] as $h) {
		            	$comment = substr($h, 0, 2);
		            	if ($comment == '--' || $comment == '//') {
		            		$header[] = substr($h, 2);
		            	}
		            	elseif (count($header) > 0) {
		            		break;
		            	}
		            }
				}
				else {
		        	$header = $lang->phrase('admin_db_file_damaged');
		        }
			}
			else {
				$header = $lang->phrase('admin_db_file_too_large');
			}
    	}
    	else {
    		$header = $lang->phrase('admin_db_file_damaged');
    	}
    }
    elseif (strtolower($nfo['extension']) == 'sql') {
    	if (filesize($dir.$file) < $sqllimit) {
			$fd = fopen($dir.$file, "r");
			$header = array();
			while (!feof($fd)) {
				$str = fgets($fd);
				$comment = substr($str, 0, 2);
				if ($comment == '--' || $comment == '//') {
					$header[] = substr($str, 2);
				}
				elseif (count($header) > 0) {
					break;
				}
			}
			fclose ($fd);
    	}
    	else {
    		$header = $lang->phrase('admin_db_file_too_large');
    	}
    }
	else {
		$header = $lang->phrase('admin_db_unknown_file_format');
	}
    if (is_array($header) && count($header) > 0) {
		$header = array_map('trim', $header);
		$header = implode("<br />\n", $header);
    }
    if (empty($header) || array_empty($header) == true) {
    	$header = $lang->phrase('admin_db_file_no_comments');
    }
    echo $header;
}
elseif ($job == 'restore') {
	echo head();
	$result = array();
	$dir = "./admin/backup/";

	// Old names: DD_MM_YYYY-HH_MM_SS
	// New names: DBNAME-YYYYMMDD-HHMMSS

	$handle = opendir($dir);
	while ($file = readdir($handle)) {
		if ($file != "." && $file != ".." && !is_dir($dir.$file)) {
			$nfo = pathinfo($dir.$file);
			if ($nfo['extension'] == 'zip' || $nfo['extension'] == 'sql') {
				$result[] = array(
					'file' => $nfo['basename'],
					'size' => filesize($dir.$file)
				);
			}
		}
	}

	$maxfilesize = formatFilesize(ini_maxupload());
?>
<form name="form" method="post" action="admin.php?action=db&job=restore2">
 <table class="border">
  <tr>
   <td class="obox" colspan="4">
	<span style="float: right;">
	<a class="button" href="admin.php?action=db&amp;job=backup"><?php echo $lang->phrase('admin_db_backup'); ?></a>
	</span>
	<?php echo $lang->phrase('admin_db_restore_database'); ?>
   </td>
  </tr>
  <tr>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_db_restore'); ?></td>
   <td class="ubox" width="5%"><?php echo $lang->phrase('admin_db_delete'); ?><br /><span class="stext"><input type="checkbox" onclick="check_all(this);" name="all" value="delete[]" /> <?php echo $lang->phrase('admin_db_all'); ?></span></td>
   <td class="ubox" width="80%"><?php echo $lang->phrase('admin_db_information'); ?></td>
   <td class="ubox" width="10%"><?php echo $lang->phrase('admin_db_file_size'); ?></td>
  </tr>
	<?php foreach ($result as $i => $row) { ?>
		<tr>
		   <td class="mbox" width="5%" align="center"><input type="radio" name="file" value="<?php echo $row['file']; ?>"></td>
		   <td class="mbox" width="5%" align="center"><input type="checkbox" name="delete[]" value="<?php echo $row['file']; ?>"></td>
		   <td class="mbox stext" valign="top" width="90%" rowspan="2">
		   	<a class="button right" href="javascript:ajax_backupinfo('<?php echo $row['file']; ?>', 'res_<?php echo $i; ?>');"><?php echo $lang->phrase('admin_db_load_restore_info'); ?></a>
		   	<?php echo $lang->phrase('admin_db_file_x'); ?> <b><?php echo $row['file']; ?></b><br />
		   	<div id="res_<?php echo $i; ?>">---</div>
		   </td>
		   <td class="mbox stext" nowrap="nowrap" width="10%" rowspan="2"><?php echo FormatFilesize($row['size']); ?></td>
		</tr>
        <tr>
           <td class="mbox" colspan="2" align="center"><a href="admin.php?action=db&amp;job=download&amp;file=<?php echo $row['file']; ?>"><?php echo $lang->phrase('admin_db_download'); ?></a></td>
        </tr>
	<?php } ?>
  <tr>
   <td class="ubox" width="100%" colspan="4" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_db_form_submit'); ?>"></td>
  </tr>
 </table>
</form><br />
<form name="form" method="post" enctype="multipart/form-data" action="admin.php?action=explorer&job=upload&cfg=dbrestore">
 <table class="border">
  <tr>
   <td class="obox" colspan="4"><?php echo $lang->phrase('admin_db_upload_backup'); ?></td>
  </tr>
  <tr>
   <td class="mbox" width="50%">
    <?php echo $lang->phrase('admin_db_upload_backup'); ?><br />
    <span class="stext"><?php echo $lang->phrase('admin_db_allowed_filetypes_max_filesize'); ?></span>
   </td>
   <td class="mbox" width="50%"><input type="file" name="upload_0" size="40" /></td>
  </tr>
  <tr>
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_db_form_upload'); ?>"></td>
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'restore2') {
	$delete = $gpc->get('delete', arr_str);
	$file = $gpc->get('file', str);
	$dir = "./admin/backup/";

	echo head();

	$d = 0;
	if (count($delete) > 0) {
		foreach ($delete as $delfile) {
			$ext = get_extension($delfile);
			if (($ext == 'zip' || $ext == 'sql') && file_exists($dir.$delfile)) {
				$filesystem->unlink($dir.$delfile);
				$d++;
			}
		}
		ok('admin.php?action=db&job=restore', $lang->phrase('admin_db_num_backups_deleted'));
	}

	$ext = get_extension($file);
	if (($ext == 'zip' || $ext == 'sql') && file_exists($dir.$file)) {
		if ($ext == 'zip') {
			require_once('classes/class.zip.php');
			$archive = new PclZip($dir.$file);
			if (($list = $archive->listContent()) == 0) {
				error($archive->errorInfo(true));
			}
			$data = $archive->extractByIndex($list[0]['index'], PCLZIP_OPT_EXTRACT_AS_STRING);
			$lines = $data[0]['content'];
			unset($data);
		}
		elseif ($ext == 'sql') {
			$lines = file_get_contents($dir.$file);
		}
		if (isset($lines)) {
			$q = $db->multi_query($lines);

			// Clear Cache
			if ($dh = @opendir("./cache/")) {
				while (($file = readdir($dh)) !== false) {
					if (strpos($file, '.inc.php') !== false) {
						$fileTrim = str_replace('.inc.php', '', $file);
						if (file_exists("classes/cache/{$fileTrim}")) {
							$cache = $scache->load($file);
							$cache->delete();
						}
						else {
							$filesystem->unlink("./cache/{$file}");
						}
					}
			    }
				closedir($dh);
			}

			ok('admin.php?action=db&job=restore', $lang->phrase('admin_db_num_queries_seccesfully_extd'));
		}
		else {
			error('admin.php?action=db&job=restore', $lang->phrase('admin_db_file_damaged'));
		}
	}

	error('admin.php?action=db&job=restore');
}
elseif ($job == 'download') {
	$dir = "./admin/backup/";
	$file = $gpc->get('file', none);
	$ext = get_extension($file);
	if (($ext == 'zip' || $ext == 'sql') && file_exists($dir.$file)) {
		if ($ext == 'sql') {
		    viscacha_header('Content-Type: text/plain');
		}
		else {
		    viscacha_header('Content-Type: application/zip');
		}
		viscacha_header('Content-Length: '.filesize($dir.$file));
		viscacha_header('Content-Disposition: attachment; filename="'.$file.'"');
		readfile($dir.$file);
	}
	else {
		echo head();
		error('admin.php?action=db&job=restore', $lang->phrase('admin_db_file_not_found'));
	}
}
elseif ($job == 'status') {
	echo head();
	$table = $gpc->get('table', str);
	$status = $gpc->get('status', int);
	$result = $db->list_tables();

	if (!empty($table)) {
		$result11 = $db->query('SHOW TABLE STATUS FROM '.$db->database.' LIKE "'.$table.'"');
		$result12 = $db->query('DESCRIBE '.$table);
?>
  <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_db_table_information'); ?> <?php echo $table; ?></td>
  </tr>
  <tr>
   <td class="ubox" width="30%"><?php echo $lang->phrase('admin_db_name'); ?></td>
   <td class="ubox" width="70%"><?php echo $lang->phrase('admin_db_value'); ?></td>
  </tr>
	<?php
		while ($data = $db->fetch_assoc($result11)) {
			foreach ($data as $key => $val) {
	?>
		<tr>
		   <td class="mbox" width="30%"><?php echo $key; ?></td>
           <td class="mbox" width="70%"><?php echo $val; ?></td>
		</tr>
	<?php }} ?>
      <tr>
       <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_db_field_information'); ?></td>
      </tr>
		<tr>
		   <td class="mbox" colspan="2">
		   <table class="inlinetable">
        		<tr>
        		  <?php for ($i = 0; $i < $db->num_fields($result12);$i++) { ?>
                   <th><?php echo $db->field_name($result12, $i); ?></th>
                  <?php } ?>
        		</tr>
        	<?php while ($data = $db->fetch_assoc($result12)) { ?>
        		<tr>
        		  <?php foreach ($data as $key => $val) { ?>
                   <td><?php echo $val; ?></td>
                  <?php } ?>
        		</tr>
        	<?php } ?>
		   </table>
		   </td>
		</tr>
 </table><br />
 <?php
	}
	elseif ($status == 1) {
		$result1 = $db->query('SHOW STATUS');
		$result2 = $db->query('SHOW VARIABLES');
 ?>
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_db_server_variables'); ?><a name="sv">&nbsp;</a></td>
  </tr>
  <tr>
   <td class="ubox" width="30%"><?php echo $lang->phrase('admin_db_name'); ?></td>
   <td class="ubox" width="70%"><?php echo $lang->phrase('admin_db_value'); ?></td>
  </tr>
	<?php while ($row = $db->fetch_num($result2)) { ?>
		<tr>
		   <td class="mbox" width="30%"><?php echo $row[0]; ?></td>
           <td class="mbox" width="70%"><?php echo $row[1]; ?></td>
		</tr>
	<?php } ?>
 </table><br />
 <table class="border">
  <tr>
   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_db_server_stat_information'); ?><a name="ssi">&nbsp;</a></td>
  </tr>
  <tr>
   <td class="ubox" width="30%"><?php echo $lang->phrase('admin_db_name'); ?></td>
   <td class="ubox" width="70%"><?php echo $lang->phrase('admin_db_value'); ?></td>
  </tr>
	<?php while ($row = $db->fetch_num($result1)) { ?>
		<tr>
		   <td class="mbox" width="30%"><?php echo $row[0]; ?></td>
           <td class="mbox" width="70%"><?php echo $row[1]; ?></td>
		</tr>
	<?php } ?>
 </table>
	<?php
	}
	else {
	?>
	  <table class="border">
	  <tr>
	   <td class="obox"><?php echo $lang->phrase('admin_db_table_of_contents'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox">
	   <strong><?php echo $lang->phrase('admin_db_server_information'); ?></strong>:<br />
	   <ul>
		<li><a href="admin.php?action=db&amp;job=status&amp;status=1#sv"><?php echo $lang->phrase('admin_db_server_variables'); ?></a></li>
		<li><a href="admin.php?action=db&amp;job=status&amp;status=1#ssi"><?php echo $lang->phrase('admin_db_server_stat_information'); ?></a></li>
	   </ul>
	   <br />
	   <strong><?php echo $lang->phrase('admin_db_table_information'); ?></strong>:<br />
	   <ul>
		<?php foreach ($result as $row) { ?>
		<li><a href="admin.php?action=db&amp;job=status&amp;table=<?php echo $row; ?>"><?php echo $row; ?></a></li>
		<?php } ?>
	   </ul>
	   </td>
	  </tr>
	  </table>
	<?php
	}
	echo foot();
}
elseif ($job == 'query') {
	echo head();
	exec_query_form();
	echo foot();
}
elseif ($job == 'query2') {
	echo head();

	$type = $gpc->get('type', int);
	if ($type == 1) {
		$filetypes = array('zip','sql');
		$dir = 'temp/';
		$inserterrors = array();
		require("classes/class.upload.php");

		if (empty($_FILES['upload']['name'])) {
			$inserterrors[] = $lang->phrase('admin_db_no_file_specified');
		}

		$my_uploader = new uploader();
		$my_uploader->max_filesize(ini_maxupload());
		$my_uploader->file_types($filetypes);
		$my_uploader->set_path($dir);
		if ($my_uploader->upload('upload')) {
			if ($my_uploader->save_file()) {
				$file = $dir.$my_uploader->fileinfo('filename');
				if (!file_exists($file)) {
					$inserterrors[] = $lang->phrase('admin_db_file_doesnt_exist');
				}
			}
		}
		if ($my_uploader->upload_failed()) {
			array_push($inserterrors,$my_uploader->get_error());
		}

		if (count($inserterrors) > 0) {
			error('admin.php?action=db&job=query', $inserterrors);
		}
		else {
			$ext = get_extension($file);
			if (($ext == 'zip' || $ext == 'sql') && file_exists($file)) {
				if ($ext == 'zip') {
					require_once('classes/class.zip.php');
					$archive = new PclZip($file);
					if (($list = $archive->listContent()) == 0) {
						error($archive->errorInfo(true));
					}
					$data = $archive->extractByIndex($list[0]['index'], PCLZIP_OPT_EXTRACT_AS_STRING);
					$lines = $data[0]['content'];
					unset($data);
				}
				elseif ($ext == 'sql') {
					$lines = file_get_contents($file);
				}
			}
		}
	}
	else {
		$lines = $gpc->get('query', none);
	}

	$sql = str_replace('{:=DBPREFIX=:}', $db->pre, $lines);
	@exec_query_form($lines);

	if (!empty($lines)) {

		ob_start();
		$q = $db->multi_query($sql, false);
		$error = ob_get_contents();
		$error = trim($error);
		ob_end_clean();
		if (!empty($error)) {
			?>
			 <table class="border" align="center">
			  <tr><td class="obox"><?php echo $lang->phrase('admin_db_sql_error'); ?></td></tr>
			  <tr><td class="mbox"><?php echo strip_tags($error); ?></td></tr>
			 </table>
			<?php
		}
		else {
			if (count($q['queries']) <= 20) { // To avoid reaching max exec. time
				$hl = highlight_sql_query($sql);
			}
			echo '<table class="border" border="0" cellspacing="0" cellpadding="4" align="center"><tr><td class="obox">'.$lang->phrase('admin_db_queries_extd');
			echo iif($q['affected'] > 0, ' - '.$lang->phrase('admin_db_rows_affected'));
			echo '</td></tr>';
			echo iif(!empty($hl), '<tr><td class="mbox">'.$hl.'</td></tr>');
			echo '</table>';
			foreach ($q['queries'] as $num) {
				if (count($num) > 0) {
					$keys = array_keys($num[0]);
					echo '<br><table class="border" border="0" cellspacing="0" cellpadding="4" align="center"><tr>';
					foreach ($keys as $field) {
						echo '<td class="obox">'.$field.'</td>';
					}
					echo "</tr>";
					foreach ($num as $row) {
						echo "<tr>";
						foreach ($keys as $field) {
							echo '<td class="mbox">'.nl2br(htmlentities($row[$field])).'</td>';
						}
					}
					echo '</table>';
				}
			}
		}

	}
	echo foot();
}
?>