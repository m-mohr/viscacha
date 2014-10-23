<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "db.php") die('Error: Hacking Attempt');

function highlight_sql_query($sql) {
	global $lang;
	require_once('classes/class.geshi.php');
	$path = 'classes/geshi';
	$lang = 'mysql';
	if (!file_exists($path.'/'.$lang.'.php')) {
		$lang = 'sql';
		if (!file_exists($path.'/'.$lang.'.php')) {
			return null;
		}
	}
	$geshi = new GeSHi($sql, $lang, $path);
	$geshi->enable_classes(false);
	$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);
	return $geshi->parse_code();
}

function exec_query_form ($query = '') {
	global $db;
	$tables = $db->list_tables();
?>
<form name="form" method="post" action="admin.php?action=db&job=query2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2"><b>Execute Queries</b></td>
  </tr>
  <tr> 
	<td class="mbox" width="90%">
	<span style="float: right;">semicolon-separated list</span><strong>Queries:</strong>
	<textarea name="query" id="query" rows="10" cols="90" class="texteditor" style="width: 100%; height: 200px;"><?php echo iif(!empty($query), $query); ?></textarea>
	</td>
	<td class="mbox" width="10%">
	<strong>Tables:</strong>
	<div style="overflow: scroll; height: 200px; width: 150px; border: 1px solid #336699; padding: 2px;">
	<?php foreach ($tables as $table) { ?>
	<a href="javascript:InsertTags('query', '`<?php echo $table; ?>`', '');"><?php echo $table; ?></a><br />
	<?php } ?>
	</div>
	</td>
  </tr>
  <tr> 
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
  </tr>
 </table>
</form>
<br />
<?php if (empty($query)) { ?>
<form name="form" method="post" action="admin.php?action=db&amp;job=query2&amp;type=1" enctype="multipart/form-data">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox"><b>Import SQL File</b></td>
  </tr>
  <tr>
  	<td class="mbox">
  	<input type="file" name="upload" size="80" /><br />
  	<span class="stext">Allowed file types: .sql, .zip - Maximum file size: <?php echo formatFilesize(ini_maxupload()); ?></span>
  	</td>
  </tr>
  <tr> 
   <td class="ubox" align="center"><input type="submit" name="Submit" value="Submit"></td> 
  </tr>
 </table>
</form>
<br />
<?php
	}
}

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
   <td class="obox" colspan="6">Repair &amp; Optimize</td>
  </tr>
  <tr> 
   <td class="ubox" width="7%">Repair</td>
   <td class="ubox" width="7%">Optimize</td>
   <td class="ubox" width="47%">Database</td>
   <td class="ubox" width="13%">Data Length</td>
   <td class="ubox" width="13%">Index Length</td>
   <td class="ubox" width="13%">Overhead</td>
  </tr>
  <tr>
   <td class="mbox"><input type="checkbox" onclick="check_all('repair[]')" name="repair_all"></td>
   <td class="mbox"><input type="checkbox" onclick="check_all('optimize[]')" name="optimize_all"></td>
   <td class="mbox"><strong>All</strong></td>
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
   <td class="ubox" colspan="6" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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
		$db->query("REPAIR TABLE ".implode(', ',$rep),__LINE__,__FILE__);
	}
	$opt = $gpc->get('optimize', arr_str);
	if (count($opt) > 0) {
		$db->query("OPTIMIZE TABLE ".implode(', ', $opt),__LINE__,__FILE__);
	}

	ok('admin.php?action=db&job=optimize', 'Tables repaired and/or optimized!');
}
elseif ($job == 'backup') {
	echo head();
	$result = $db->list_tables();
	?>
<form name="form" method="post" action="admin.php?action=db&job=backup2">
 <table class="border">
  <tr> 
   <td class="obox" colspan="5">Backup Tables</td>
  </tr>
  <tr> 
   <td class="ubox" width="30%">Export</td>
   <td class="ubox" width="70%">Options</td>
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
   <input type="checkbox" name="structure" value="1" checked="checked" /> <strong>Export structure</strong><br />
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="drop" value="1" checked="checked" /> Add 'DROP TABLE IF EXISTS'<br /><br />
   <input type="checkbox" name="data" value="1" checked="checked" /> <strong>Export data</strong>
   <br /><br /><input type="checkbox" name="zip" value="1" /> <strong>Save as ZIP file</strong>
   </td>
  </tr>
  <tr> 
   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'backup2') {
	@ignore_user_abort(false);
	@set_time_limit(300);
	$tables = $gpc->get('backup', arr_str);
	$structure = $gpc->get('structure', int);
	$data = $gpc->get('data', int);
	$drop = $gpc->get('drop', int);
	$zip = $gpc->get('zip', int); 
	echo head();
	$db->backup_settings("\n","--");
	$sqldata = $db->backup($tables, $structure, $data, $drop);
	$ok = "Backup successfully created!";
	if (!empty($sqldata) && strlen($sqldata) > 0) {
        // Speichern der Backup-Datei
        $file_path = "admin/backup/".date('d_m_Y-H_i_s');
        if ($zip == 1) {
        	$filesystem->file_put_contents($file_path.'.sql', $sqldata);

			require_once('classes/class.zip.php');
			$archive = new PclZip($file_path.".zip");
			$v_list = $archive->create($file_path.'.sql',PCLZIP_OPT_REMOVE_PATH, "admin/backup");
            
            if ($v_list == 0) {
            	$ok = "Could not create ZIP-File. Saved backup as normal textfile.<br />Error: ".$archive->errorInfo(true);
        		$file_path .= '.sql';
        	}
        	else {
        		$filesystem->unlink($file_path.".sql");
        		$file_path .= '.zip';
        	}
        }
        else {
        	$file_path .= '.sql';
            $filesystem->file_put_contents($file_path, $sqldata);
        }
        
        if (file_exists($file_path)) {
            ok('admin.php?action=db&job=restore',$ok);
        }
    	else {
    	    error('admin.php?action=db&job=backup','Backup was not created on account of missing permissions!');
    	}
	}
	else {
	    error('admin.php?action=db&job=backup','Backup was not created on account of missing data!');
	}
}
elseif ($job == 'restore') {
	echo head();
	$result = array();
	$dir = "./admin/backup/";
	
	$mem_limit = @ini_get('memory_limit');
	if (empty($mem_limit)) {
		$mem_limit = @get_cfg_var('memory_limit');
	}
	$mem_limit = intval($mem_limit)*1024*1024;
	$maxlimit = 2*1024*1024;
	if ($mem_limit > $maxlimit) {
		$maxlimit = $mem_limit;
	}
	
	$handle = opendir($dir);
	while ($file = readdir($handle)) {
		if ($file != "." && $file != ".." && !is_dir($dir.$file)) {					  
			$nfo = pathinfo($dir.$file);
			if ($nfo['extension'] == 'zip' || $nfo['extension'] == 'sql') {
			
				$date = str_replace('.zip', '', $nfo['basename']);
				$date = str_replace('.sql', '', $date);
				
				if (filesize($dir.$file) < $maxlimit) {
			        if ($nfo['extension'] == 'zip') {
						require_once('classes/class.zip.php');
						$archive = new PclZip($dir.$file);
						if (($list = $archive->listContent()) > 0) {
							$data = $archive->extractByIndex($list[0]['index'], PCLZIP_OPT_EXTRACT_AS_STRING);
							$headers = preg_split("/\r\n|\r|\n/", $data[0]['content']);
							unset($data);
						}
			        }
			        elseif ($nfo['extension'] == 'sql') {
						$fd = fopen($dir.$file, "r");
						while (!feof($fd)) {
							$headers[] = fgets($fd, 2048);
						}
						fclose ($fd);
			        }
			        if (isset($headers) && is_array($headers)) {
			            $header = array();
			            foreach ($headers as $h) {
			            	$comment = substr($h, 0, 2);
			            	if ($comment == '--' || $comment == '//') {
			            		$header[] = substr($h, 2);
			            	}
			            	else {
			            		break;
			            	}
			            }
			            $header = array_map('trim', $header);
			            $header = implode("<br />\n", $header);
			        }
			        else {
			        	$header = 'Can not read information. This file is maybe damaged.';
			        }
		        }
		        else {
		        	$header = 'File is too big for opening.';
		        }
				
				$result[] = array(
					'file' => $nfo['basename'],
					'size' => filesize($dir.$file),
					'date' => $date,
					'header' => $header
				);
				unset($header, $buffer, $headers);
			}
		}
	}
?>
<form name="form" method="post" action="admin.php?action=db&job=restore2">
 <table class="border">
  <tr> 
   <td class="obox" colspan="4">Restore Database</td>
  </tr>
  <tr> 
   <td class="ubox" width="5%">Restore</td>
   <td class="ubox" width="5%">Delete</td>
   <td class="ubox" width="80%">Information</td>
   <td class="ubox" width="10%">File Size</td>
  </tr>
	<?php foreach ($result as $row) { ?>
		<tr>
		   <td class="mbox" width="5%" align="center"><input type="radio" name="file" value="<?php echo $row['file']; ?>"></td>
		   <td class="mbox" width="5%" align="center"><input type="checkbox" name="delete[]" value="<?php echo $row['file']; ?>"></td>
		   <td class="mbox stext" width="90%" rowspan="2">File: <b><?php echo $row['file']; ?></b><br /><?php echo $row['header']; ?></td>
		   <td class="mbox stext" nowrap="nowrap" width="10%" rowspan="2"><?php echo FormatFilesize($row['size']); ?></td>
		</tr>
        <tr>
           <td class="mbox" colspan="2" align="center"><a href="admin.php?action=db&amp;job=download&amp;file=<?php echo $row['file']; ?>">Download</a></td>
        </tr>
	<?php } ?>
  <tr> 
   <td class="ubox" width="100%" colspan="4" align="center"><input type="submit" name="Submit" value="Submit"></td> 
  </tr>
 </table>
</form><br />
<form name="form" method="post" enctype="multipart/form-data" action="admin.php?action=explorer&job=upload&cfg=dbrestore">
 <table class="border">
  <tr> 
   <td class="obox" colspan="4">Upload a Backup</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">
    Upload a backup:<br />
    <span class="stext">Allowed file types: .sql, .zip - Maximum file size: <?php echo formatFilesize(ini_maxupload()); ?></span>
   </td>
   <td class="mbox" width="50%"><input type="file" name="upload_0" size="40" /></td>
  </tr>
  <tr> 
   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Upload"></td> 
  </tr>
 </table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'restore2') {
	$delete = $gpc->get('delete', arr_str);
	$edit = $gpc->get('file', str);
	$dir = "./admin/backup/";
	
	echo head();
	
	$d = 0;
	if (count($delete) > 0) {
		foreach ($delete as $file) {
			$ext = get_extension($file, true);
			if (($ext == 'zip' || $ext == 'sql') && file_exists($dir.$file)) {
				$filesystem->unlink($dir.$file);
				$d++;
			}
		}
		ok('admin.php?action=db&job=restore', $d.' backups deleted');
	}
	
	$ext = get_extension($edit, true);
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
			ok('admin.php?action=db&job=restore', $q['ok'].' queries successfully executed.');
		}
		else {
			error('admin.php?action=db&job=restore', 'Can not read information. This file is maybe damaged.');
		}
	}
	
	error('admin.php?action=db&job=restore');
}
elseif ($job == 'download') {
	$dir = "./admin/backup/";
	$file = $gpc->get('file', none);
	$ext = get_extension($file, true);
	if (($ext == 'zip' || $ext == 'sql') && file_exists($dir.$file)) {
		if ($ext == 'sql') {
		    viscacha_header('Content-Type: text/plain');
		}
		else {
		    viscacha_header('Content-Type: application/zip');
		}
		viscacha_header('Content-Disposition: attachment; filename="'.$file.'"');
		readfile($dir.$file);
	}
	else {
		error('admin.php?action=db&job=restore', 'File not found');
	}
}
elseif ($job == 'status') {
	echo head();
	$table = $gpc->get('table', str);
	$status = $gpc->get('status', int);
	$result = $db->list_tables();

	if (!empty($table)) {
		$result11 = $db->query('SHOW TABLE STATUS FROM '.$db->database.' LIKE "'.$table.'"',__LINE__,__FILE__);
		$result12 = $db->query('DESCRIBE '.$table);
?>
  <table class="border">
  <tr> 
   <td class="obox" colspan="2">Table Information: <?php echo $table; ?></td>
  </tr>
  <tr> 
   <td class="ubox" width="30%">Name</td>
   <td class="ubox" width="70%">Value</td>
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
       <td class="ubox" colspan="2">Field Information</td>
      </tr>
		<tr>
		   <td class="mbox" colspan="2">
		   <table bgcolor="#cccccc" border="1" cellpadding="2" cellspacing="1" width="100%">
        		<tr>
        		  <?php for ($i = 0; $i < $db->num_fields($result12);$i++) { ?>
                   <td class="ubox"><?php echo $db->field_name($result12, $i); ?></td>
                  <?php } ?>
        		</tr>
        	<?php while ($data = $db->fetch_assoc($result12)) { ?>
        		<tr>
        		  <?php foreach ($data as $key => $val) { ?>
                   <td class="mbox"><?php echo $val; ?></td>
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
		$result1 = $db->query('SHOW STATUS',__LINE__,__FILE__);
		$result2 = $db->query('SHOW VARIABLES',__LINE__,__FILE__);
 ?>
 <table class="border">
  <tr> 
   <td class="obox" colspan="2">Server Variables<a name="sv">&nbsp;</a></td>
  </tr>
  <tr> 
   <td class="ubox" width="30%">Name</td>
   <td class="ubox" width="70%">Value</td>
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
   <td class="obox" colspan="2">Server Status Information<a name="ssi">&nbsp;</a></td>
  </tr>
  <tr> 
   <td class="ubox" width="30%">Name</td>
   <td class="ubox" width="70%">Value</td>
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
	   <td class="obox">Table of Contents</td>
	  </tr>
	  <tr> 
	   <td class="mbox"><ul>
		<?php foreach ($result as $row) { ?>
		<li><a href="admin.php?action=db&amp;job=status&amp;table=<?php echo $row; ?>">Table Information: <?php echo $row; ?></a></li>
		<?php } ?>
		<li><a href="admin.php?action=db&amp;job=status&amp;status=1#sv">Server Variables</a></li>
		<li><a href="admin.php?action=db&amp;job=status&amp;status=1#ssi">Server Status Information</a></li>
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
		$filetypes = array('.zip','.sql');
		$dir = 'temp/';
		$inserterrors = array();
		require("classes/class.upload.php");
	
		if (empty($_FILES['upload']['name'])) {
			$inserterrors[] = 'No file specified.';
		}
		
		$my_uploader = new uploader();
		$my_uploader->max_filesize(ini_maxupload());
		if ($my_uploader->upload('upload', $filetypes)) {
			$my_uploader->save_file($dir, 2);
			$errstr = $my_uploader->return_error();
			if (!empty($errstr)) {
				array_push($inserterrors, $my_uploader->return_error());
			}
		}
		else {
			array_push($inserterrors, $my_uploader->return_error());
		}
		$file = $dir.DIRECTORY_SEPARATOR.$my_uploader->file['name'];
		if (!file_exists($file)) {
			$inserterrors[] = 'File ('.$file.') does not exist.';
		}
		
		if (count($inserterrors) > 0) {
			error('admin.php?action=db&job=query', $inserterrors);
		}
		else {
			$ext = get_extension($file, true);
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
	$hl = highlight_sql_query($sql);
	
	if (!empty($lines)) {

		ob_start();
		$q = $db->multi_query($sql, false);
		$error = ob_get_contents();
		$error = trim($error);
		ob_end_clean();
		if (!empty($error)) {
			?>
			 <table class="border" align="center">
			  <tr><td class="obox">MySQL Error</td></tr>
			  <tr><td class="mbox"><?php echo strip_tags($error); ?></td></tr>
			 </table>
			<?php
		}
		else {
			echo '<table class="border" border="0" cellspacing="0" cellpadding="4" align="center"><tr><td class="obox">'.$q['ok'].' Queries executed';
			echo iif($q['affected'] > 0, ' - '.$q['affected'].' Rows affected');
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
