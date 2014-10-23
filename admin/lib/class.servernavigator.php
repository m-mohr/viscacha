<?php
/*
*  ServerNavigator 1.0
*
*  modified by Matthias Mohr, 2005/2006
*
*  @author: Carlos Reche
*  @e-mail: carlosreche@yahoo.com
*
*  Dez 20, 2004
*
*/
class ServerNavigator
{

	var $use_image_icons;	   // (bool)	Sets if should use icons
	var $show_subfolders_size; // (bool)	Sets if should get subfolders size (makes the processing slower)

	var $script_file;		   // (string)  Script's name

	var $root;				   // (string)  Path to root
	var $path;				   // (string)  Path to showed dir
	
	var $icon;				   // (array) 	Array with cached data

	var $plain;
	var $extract;

	function ServerNavigator($use_image_icons = true, $show_subfolders_size = false)
	{
	global $gpc;
	
		// Suggestion: ZIP- (und -RAR) Dateien entpacken-Option(?)
		$this->plain 				= array(
			'txt','php','php3','php4','php5','phtml','shtml','html','htm','css','js','pl','cgi','inc','asp','bat','cfm',
			'pm','log','xml','ini','csv','dat','sql','htc','htaccess','htusers','inf','tex','tsv','xsl','xslt','klip',
			'food','mbox','phps','py','lua','cfg','sh','c','p','cpp'
		);
		$this->extract 				= array('zip', 'tar', 'gz');
	
		$this->use_image_icons		= (bool)$use_image_icons;
		$this->show_subfolders_size = (bool)$show_subfolders_size;
		$this->script_file			= $this->realPath('admin.php').'?action=explorer';
		$this->path = $gpc->get('path', none);
		if (empty($this->path)) {
			$this->path = './';
		}
		if (substr($this->path, strlen($this->path)-1, strlen($this->path) ) != DIRECTORY_SEPARATOR) {
			$this->path .= DIRECTORY_SEPARATOR;
		}

		// Calculates path to root
		$levels = count(explode("/", $this->script_file)) - 2;

		for ($i = 0, $this->root = ""; $i < $levels; $i++)
		{
			$this->root .= "../";
		}

	}
	
	function ext() {
		global $db;
		if (count($this->icon) == 0) {
			$this->icon = array();
			$result = $db->query('SELECT extension, icon, mimetype, stream FROM '.$db->pre.'filetypes');
			while ($row = $db->fetch_assoc($result)) {
				$extension = explode(',', $row['extension']);
				unset($row['extension']);
				foreach ($extension as $e) {
					$e = strtolower($e);
					$this->icon[$e] = $row;
				}
			}
			$this->icon['directory'] = array(
			'extension' => 'directory',
			'icon' => 'folder',
			'mimetype' => 'text/html',
			'stream' => 'inline'
			);
		}
	}

	function icons($ext) {
		global $my, $tpl;
		$this->ext();
		$ext = strtolower($ext);
		if ($this->use_image_icons && is_a($tpl, 'tpl')) {
			if (isset($this->icon[$ext])) {
				$row = $this->icon[$ext];
			}
			else {
				return '&nbsp;';
			}
			return '<img src="'.$tpl->img("filetypes/".$row['icon']).'" alt="'.$row['mimetype'].'" border="0" />&nbsp;';
		}
		return '';
	}


	function checkExtract($file) {
		$extension = preg_replace("/^.*?\\.(\w{1,8})$/", "\\1", $file);
		return in_array($extension, $this->extract);
	}
	
	function checkEdit($file) {
		$extension = preg_replace("/^.*?\\.(\w{1,8})$/", "\\1", $file);
		return in_array($extension, $this->plain);
	}


	function showContent($print = true)
	{

		$dir_list = $file_list = $subdir_size_list = array();
		$total_dir_size = 0;

		if (($dir_handle = opendir($this->path)) === false)
		{
			$this->error("Could NOT open dir: " . realpath($this->path));
			return false;
		}


		while (($file = @readdir($dir_handle)) !== false)
		{
			if ($file == '.'  ||  $file == '..')
			{
				continue;
			}


			if (is_dir($this->path . $file))
			{
				$dir_list[] = $file;

				if ($this->show_subfolders_size)
				{
					$stack = array($this->path . $file . '/');
					$size  = 0;

					while (count($stack) > 0)
					{
						$subdir		   = array_shift($stack);
						$subdir_handle = @opendir($subdir);

						while (($subdir_file = @readdir($subdir_handle))  !==  false)
						{
							if ($subdir_file == '.'  ||  $subdir_file == '..')
							{
								continue;
							}

							if ($this->show_subfolders_size)

							if (is_dir($subdir . $subdir_file))
							{
								array_push($stack, $subdir.$subdir_file.'/');
							}

							$size += @filesize($subdir . $subdir_file);
						}
						@closedir($subdir_handle);
					}

					$subdir_size_list[$file] = $size;
					$total_dir_size			+= $size;
				}
			}
			else {
				$file_list[] = $file;
				$total_dir_size += @filesize($this->path . $file);
			}

		}

		@closedir($dir_handle);

		natcasesort($dir_list);
		natcasesort($file_list);


		$total_files  = count($dir_list) + count($file_list);
		$total_size   = $this->formatSize($total_dir_size);
		$print_spacer = (count($file_list) > 0  &&  count($dir_list) > 0)  ?  true  :  false;

		$page_link			= $this->realPath(viscacha_dirname($this->script_file) . '/' . $this->path);
		$rp = realpath($this->path);
		$root = realpath('../'.$this->root);
		$rp = str_replace($root, '', $rp);
		$heading_path		= $this->realPath($rp);
		$heading_path_parts = explode("/", $heading_path);
		$levels				= count($heading_path_parts) - 2;

		for ($heading_path = ""; $levels > -1; $levels--)
		{
			for ($i = 0, $path = ""; $i < $levels; $i++) { $path .= "../"; }
			$link = '&amp;path='.urlencode($this->realPath($this->path . $path));
			$heading_path .= '<a href="'.$this->script_file.$link.'">'.array_shift($heading_path_parts).'</a>'.DIRECTORY_SEPARATOR;
		}

		$newdir = $this->script_file . '&amp;path=' . urlencode(str_replace('/\\', '/', $this->path));
		$newdir_html = '<span style="float: right;">[<a href="'.$newdir.'&job=newdir">Create new directory</a>]</span>';

		$html = '	   <table cellpadding="4" cellspacing="0" class="border">';
		$html .= "\n".'		 <tr>';
		$html .= "\n".'		   <td class="obox">Filemanager</td>';
		$html .= "\n".'		 </tr>';
		$html .= "\n".'		 <tr>';
		$html .= "\n".'		   <td class="ubox">Directory: ' . realpath('../'.$this->root) . $heading_path . '</td>';
		$html .= "\n".'		 </tr>';
		$html .= "\n".'	   </table><br />';
		$html .= "\n".'	   <table cellpadding="4" cellspacing="0" class="border">';

		
		if (count($dir_list) > 0) {	
			$html .= "\n".'		 <tr>';
			$html .= "\n".'		   <td class="obox" colspan="5">'.$newdir_html.' Directories</td>';
			$html .= "\n".'		 </tr>';
			$html .= "\n".'		 <tr>';
			$html .= "\n".'		   <td class="ubox" width="30%">Directory</td>';
			$html .= "\n".'		   <td class="ubox" width="9%">Size</td>';
			$html .= "\n".'		   <td class="ubox" width="20%">Created on</td>';
			$html .= "\n".'		   <td class="ubox" width="8%">CHMOD</td>';
			$html .= "\n".'		   <td class="ubox" width="33%">Action</td>';
			$html .= "\n".'		 </tr>';
		}

		while (($dir = array_shift($dir_list)) !== NULL)
		{

			$path_url = '&amp;path=' . urlencode(str_replace('/\\', '/', $this->path) . $dir . '/');
			$link = $this->script_file . $path_url;
			$size = ($this->show_subfolders_size)  ?  $this->formatSize($subdir_size_list[$dir])  :  "&nbsp;";
			$chmod = get_chmod($this->path . $dir);

			$icon = $this->icons('directory');

			$html .= "\n".'		 <tr>';
			$html .= "\n".'		   <td class="mbox">';
			$html .= "\n".'			 <a href="' .  $link . '" target="Main">' . $icon . $dir . '</a>';
			$html .= "\n".'		   </td>';
			$html .= "\n".'		   <td class="mbox" align="right">';
			$html .= "\n".'			 ' . $size;
			$html .= "\n".'		   </td>';
			$html .= "\n".'		   <td class="mbox">';
			$html .= "\n".'			 ' . date("d.m.y, H:i", @filectime($this->path . $dir));
			$html .= "\n".'		   </td>';
			$html .= "\n".'		   <td class="mbox" align="right">';
			$html .= "\n".'			 ' . $chmod;
			$html .= "\n".'		   </td>';
			$html .= "\n".'		   <td class="mbox">';
			$html .= "\n".'			[<a href="'.$link.'&job=chmod&type=dir">CHMOD</a>] [<a href="'.$link.'&job=rename&type=dir">Rename</a>] [<a href="'.$link.'&job=delete&type=dir">Delete</a>]';
			$html .= "\n".'		   </td>';
			$html .= "\n".'		 </tr>';
		}


		if ($print_spacer)
		{
			$html .= "\n".'   </table><br />';
			$html .= "\n".'   <table cellpadding="4" cellspacing="0" class="border">';
		}

		if (count($file_list) > 0) {	
			$html .= "\n".'		 <tr>';
			$html .= "\n".'		   <td class="obox" colspan="6">'.iif(!$print_spacer, $newdir_html).'Files</td>';
			$html .= "\n".'		 </tr>';
			$html .= "\n".'		 <tr>';
			$html .= "\n".'		   <td class="ubox" width="30%">Total: '.$total_files.' files ('.$total_size.')</td>';
			$html .= "\n".'		   <td class="ubox" width="7%">Size</td>';
			$html .= "\n".'		   <td class="ubox" width="12%">Last modified</td>';
			$html .= "\n".'		   <td class="ubox" width="12%">Created in</td>';
			$html .= "\n".'		   <td class="ubox" width="7%">CHMOD</td>';
			$html .= "\n".'		   <td class="ubox" width="33%">Action</td>';			
			$html .= "\n".'		 </tr>';
		}

		while (($file = array_shift($file_list))  !==  NULL)
		{

			$extension = preg_replace("/^.*?\\.(\w{1,8})$/", "\\1", $file);

			$path_url = '&amp;path=' . urlencode(str_replace('/\\', '/', $this->path) . $file);
			$link = $this->script_file . $path_url;

			$icon = $this->icons($extension);
			
			$html .= "\n".'		 <tr>';
			$html .= "\n".'		   <td class="mbox">';
			$html .= "\n".'			 <a href="' . str_replace('/\\', '/', $this->path) . $file . '">' . $icon . $file . '</a>';
			$html .= "\n".'		   </td>';
			$html .= "\n".'		   <td class="mbox" align="right">';
			$html .= "\n".'			 ' . $this->formatSize(@filesize($this->path . $file));
			$html .= "\n".'		   </td>';
			$html .= "\n".'		   <td class="mbox">';
			$html .= "\n".'			 ' . date("d.m.y, H:i", @filemtime($this->path . $file));
			$html .= "\n".'		   </td>';
			$html .= "\n".'		   <td class="mbox">';
			$html .= "\n".'			 ' . date("d.m.y, H:i", @filectime($this->path . $file));
			$html .= "\n".'		   </td>';
			$html .= "\n".'		   <td class="mbox" align="right">';
			$html .= "\n".'			 ' . get_chmod($this->path . $file);
			$html .= "\n".'		   </td>';
			$html .= "\n".'		   <td class="mbox">';
			$html .= "\n".'			 [<a href="'.$link.'&job=chmod">CHMOD</a>] [<a href="'.$link.'&job=rename">Rename</a>] [<a href="'.$link.'&job=delete">Delete</a>]';
			$html .= iif(in_array($extension, $this->extract), ' [<a href="'.$link.'&job=extract">Extract</a>]').iif(in_array($extension, $this->plain), ' [<a href="'.$link.'&job=edit">Edit</a>]');
			$html .= "\n".'		   </td>';
			$html .= "\n".'		 </tr>';

		}

		$html .= "\n".'	   </table>';


		
		if ($print) {
			echo $html;
		}
		else {
			return $html;
		}
	}
	
	function uploadForm($uploadfields, $print = true) {
		$path = urlencode($this->realPath($this->path));
		$html = '<form name="form2" method="post" enctype="multipart/form-data" action="admin.php?action=explorer&job=upload&path='.$path.'">';
		$html .= '<table cellpadding="4" cellspacing="0" class="border">';
		$html .= '<tr><td class="obox" colspan="2">Upload files</td></tr>';
		
		for ($i = 0; $i < $uploadfields; $i++) {
			$html .= '<tr><td class="mbox">File '.($i+1).'</td><td class="mbox"><input type="file" name="upload_'.$i.'" size="80" /></td></tr>';
		}
		$html .= '<tr><td class="ubox" colspan="2" align="center"><input type="submit" value="Upload" /></td></tr></table></form>';

		if ($print) {
			echo $html;
		}
		else {
			return $html;
		}
	}

	function realPath($path)
	{
		if ($path == "")
		{
			return false;
		}

		$path = trim(preg_replace("/\\\\/", "/", (string)$path));

		if (!preg_match("/(\.\w{1,4})$/", $path)  &&  !preg_match("/\?[^\\/]+$/", $path)  &&  !preg_match("/\\/$/", $path))
		{
			$path .= '/';
		}

		preg_match_all("/^(\\/|\w:\\/|(http|ftp)s?:\\/\\/[^\\/]+\\/)?(.*)$/i", $path, $matches, PREG_SET_ORDER);

		$path_root = $matches[0][1];
		$path_dir  = $matches[0][3];

		$path_dir = preg_replace(  array("/^\\/+/", "/\\/+/"),  array("", "/"),  $path_dir  );

		$path_parts = explode("/", $path_dir);

		for ($i = $j = 0, $real_path_parts = array(); $i < count($path_parts); $i++)
		{
			if ($path_parts[$i] == '.')
			{
				continue;
			}
			else if ($path_parts[$i] == '..')
			{
				if (  (isset($real_path_parts[$j-1])  &&  $real_path_parts[$j-1] != '..')  ||  ($path_root != "")  )
				{
					array_pop($real_path_parts);
					$j--;
					continue;
				}
			}

			array_push($real_path_parts, $path_parts[$i]);
			$j++;
		}

		return $path_root . implode("/", $real_path_parts);
	}

	function formatSize($size_bytes)
	{
		if ($size_bytes < 1024) {
			$size	 = number_format($size_bytes, 0, ".", ",");
			$measure = 'B';
		}
		else if (($size_bytes/1024) < 1024) {
			$size	 = number_format(  ($size_bytes/1024)  , 2, ".", ",");
			$measure = 'KB';
		} else if (($size_bytes/(1024*1024)) < 1024) {
			$size	 = number_format(  ($size_bytes/(1024*1024))  , 2, ".", ",");
			$measure = 'MB';
		} else {
			$size	 = number_format(  ($size_bytes/(1024*1024*1024))  , 2, ".", ",");
			$measure = 'GB';
		}

		return $size . ' ' . $measure;
	}

	function error($message, $print = true)
	{
$error = <<<OOO
<table class="border" border="0" cellspacing="0" cellpadding="4">
  <tr>
	<td class="obox">An error occured:</td>
  </tr>
  <tr>
	<td class="mbox">{$message}</td>
  </tr>
</table>
OOO;
		if ($print) {
			echo $error;
		} else {
			return $error;
		}
	}



	function show()
	{
		$body  = $this->showContent(false);
		$title = $this->realPath($_SERVER['HTTP_HOST'] . '/' . viscacha_dirname($this->script_file) . '/' . $this->path);
		$title = preg_replace("/^.*?\\/?([^\\/]+)\\/?$/", "\\1", $title);

		echo $body;
		
	}

	// Configuration

	function useImageIcons($should_use = true)
	{
		$this->use_image_icons = (bool)$should_use;
	}

	function showSubfoldersSize($should_show = true)
	{
		$this->show_subfolders_size = (bool)$should_show;
	}

}



?>
