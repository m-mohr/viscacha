<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "db.php") die('Error: Hacking Attempt');

if ($job == 'optimize') {
	echo head();
	$result = $db->list_tables();
	?>
<form name="form" method="post" action="admin.php?action=db&job=optimize2">
 <table class="border">
  <tr> 
   <td class="obox" colspan="3">Repair &amp; Optimize</td>
  </tr>
  <tr> 
   <td class="ubox" width="10%">Repair</td>
   <td class="ubox" width="10%">Optimize</td>
   <td class="ubox" width="80%">Database</td>
  </tr>
  <tr>
   <td class="mbox" width="10%"><input type="checkbox" onclick="check_all('repair[]')" name="repair_all"></td>
   <td class="mbox" width="10%"><input type="checkbox" onclick="check_all('optimize[]')" name="optimize_all"></td>
   <td class="mbox" width="80%"><strong>All tables</strong></td>
  </tr>
	<?php foreach ($result as $row) { ?>
		<tr>
		   <td class="mbox" width="10%"><input type="checkbox" name="repair[]" value="<?php echo $row; ?>"></td>
		   <td class="mbox" width="10%"><input type="checkbox" name="optimize[]" value="<?php echo $row; ?>"></td>
		   <td class="mbox" width="80%"><?php echo $row; ?></td>
		</tr>
	<?php } ?>
  <tr> 
   <td class="ubox" width="100%" colspan="3" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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
   <input type="checkbox" name="structure" value="1" checked="checked" /> <strong>Struktur exportieren</strong><br />
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="drop" value="1" checked="checked" /> Mit 'DROP TABLE IF EXISTS'<br /><br />
   <input type="checkbox" name="data" value="1" checked="checked" /> <strong>Daten exportieren</strong>
   <br /><br /><input type="checkbox" name="zip" value="1" /> <strong>Als ZIP-Datei speichern</strong>
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
	@ignore_user_abort(FALSE);
	@set_time_limit(60);
	$tables = $gpc->get('backup', arr_str);
	$structure = $gpc->get('structure', int);
	$data = $gpc->get('data', int);
	$drop = $gpc->get('drop', int);
	$zip = $gpc->get('zip', int); 
	echo head();
	$db->backup_settings("\n","--");
	$sqldata = $db->backup($tables, $structure, $data, $drop);
	$ok = "Backup successfully created!";
	if ($sqldata) {
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
	    error('admin.php?action=db&job=backup','Backup was not created on account of no data!');
	}
}
elseif ($job == 'restore') {
	echo head();
	$result = array();
	$dir = "./admin/backup/";
	$handle = opendir($dir);
	while ($file = readdir($handle)) {
		if ($file != "." && $file != ".." && !is_dir($dir.$file)) {					  
			$nfo = pathinfo($dir.$file);
			if ($nfo['extension'] == 'zip' || $nfo['extension'] == 'sql') {
			
				$date = str_replace('.zip', '', $nfo['basename']);
				$date = str_replace('.sql', '', $date);
				
		        if ($nfo['extension'] == 'zip') {
					require_once('classes/class.zip.php');
					$archive = new PclZip($dir.$file);
					if (($list = $archive->listContent()) > 0) {
						$data = $archive->extractByIndex($list[0]['index'], PCLZIP_OPT_EXTRACT_AS_STRING);
						$headers = explode("\n", $data[0]['content']);
						unset($data);
					}
		        }
		        elseif ($nfo['extension'] == 'sql') {
		            $headers = file($dir.$file);
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
				
				$result[] = array(
				'file' => $nfo['basename'],
				'size' => filesize($dir.$file),
				'date' => $date,
				'header' => $header
				);
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
   <td class="ubox" width="10%">Gr&ouml;&szlig;e</td>
  </tr>
	<?php foreach ($result as $row) { ?>
		<tr>
		   <td class="mbox" width="5%" align="center"><input type="radio" name="file" value="<?php echo $row['file']; ?>"></td>
		   <td class="mbox" width="5%" align="center"><input type="checkbox" name="delete[]" value="<?php echo $row['file']; ?>"></td>
		   <td class="mbox stext" width="90%" rowspan="2"><?php echo $row['header']; ?></td>
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
   <td class="obox" colspan="4">Backup hochladen</td>
  </tr>
  <tr>
   <td class="mbox" width="50%">
    Backupdatei (nur) hochladen:<br />
    <span class="stext">Erlaubte Dateitypen: .sql, .zip - Maximale Dateigröße: <?php echo formatFilesize(ini_maxupload()); ?></span>
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
elseif($job == 'download') {
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
		$result11 = $db->query('SHOW TABLE STATUS FROM '.$db->getcfg('db').' LIKE "'.$table.'"',__LINE__,__FILE__);
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
	<?php while ($row = $db->fetch_array($result2)) { ?>
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
	<?php while ($row = $db->fetch_array($result1)) { ?>
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
	$tables = $db->list_tables();
	?>
<form name="form" method="post" action="admin.php?action=db&job=query2">
 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr> 
   <td class="obox" colspan="2"><b>Queries</b></td>
  </tr>
  <tr> 
	<td class="mbox" width="90%">
	<span style="float: right;">semicolon-separated list</span><strong>Queries:</strong>
	<textarea name="query" id="query" rows="10" cols="90" class="texteditor" style="width: 100%; height: 200px;"></textarea>
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
	<?php
	echo foot();
}
elseif ($job == 'query2') {
	$lines = $gpc->get('query', none);
	$q = $db->multi_query($lines);
	echo head();
	echo '<table class="border" border="0" cellspacing="0" cellpadding="4" align="center"><tr><td class="obox">'.$q['ok'].' Queries executed</td></tr></table>';
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
					echo '<td class="mbox">'.htmlentities($row[$field]).'</td>';
				}
			}
			echo '</table>';
		}
	}
	echo foot();
}

?>
