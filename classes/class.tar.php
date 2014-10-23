<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

/*
** PHP TAR Implementation
**
** GNU Tar creation and extraction
** Usage based on the C and Perl GNU modules
**
** Copyright (c) 2001 - 2003 Invision Power Services
** Code by Matt Mecham <matt@invisionpower.com>
** Report all bugs / improvements to matt@ibforums.com
**
** Modified by Matthias Mohr, http://www.viscacha.org
**
** This code has been created and released under the GNU license and may be
** freely used and distributed.
*/

/*************************************************************
|
| EXTRACTION USAGE:
|
| $tar = new tar("/foo/bar", "myTar.tar");
| $files = $tar->list_files();
| $tar->extract_files( "/extract/to/here/dir" );
|
| CREATION USAGE:
|
| $tar = new tar("/foo/bar" , "myNewTar.tar");
| $tar->add_files( $file_names_with_path_array );
| (or $tar->add_directory( "/foo/bar/myDir" ); to archive a complete dir)
| $tar->write_tar();
|
*************************************************************/

class tar {

	var $tar_header_length = '512';
	var $tar_unpack_header = 'a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8chksum/a1typeflag/a100linkname/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor/a155prefix';
	var $tar_pack_header = 'A100 A8 A8 A8 A12 A12 A8 A1 A100 A6 A2 A32 A32 A8 A8 A155';
	var $unpack_dir = "";
	var $pack_dir = "";
	var $error = "";
	var $tar_in_mem = array();
	var $tar_filename = "";
	var $filehandle = "";
	var $warnings = array();
	var $ignore_chmod = false;
	var $tarfile_name = "";
	var $tarfile_path = "";
	var $tarfile_path_name = "";
	var $workfiles = array();

	//+--------------------------------------------------------------------------
	// Set the tarname. If we are extracting a tarball, then it must be the
	// path to the tarball, and it's name (eg: $tar->new_tar("/foo/bar" ,'myTar.tar')
	// or if we are creating a tar, then it must be the path and name of the tar file
	// to create.
	//+--------------------------------------------------------------------------
	function tar($tarpath, $tarname) {

		$this->tarfile_name = $tarname;
		$this->tarfile_path = $tarpath;

		// Make sure there isn't a trailing slash on the path
		$this->tarfile_path = rtrim($this->tarfile_path, '/\\');

		$this->tarfile_path_name = $this->tarfile_path .'/'. $this->tarfile_name;

	}

	//+--------------------------------------------------------------------------
	// Easy way to overwrite defaults
	//+--------------------------------------------------------------------------
	function ignore_chmod($value = true) {
		$this->ignore_chmod = (bool) $value;
	}

	//+--------------------------------------------------------------------------
	// Returns an array with all the filenames in the tar file
	//
	// $advanced == false - return name only
	// $advanced == true  - return name, size, mtime, mode
	//+--------------------------------------------------------------------------
	function list_files($advanced = false) {

		$data = $this->read_tar();

		$final = array();

		foreach($data as $d) {
			if ($advanced == true) {
				$final[] = array (
								'name'  => $d['name'],
								'size'  => $d['size'],
								'mtime' => $d['mtime'],
								'mode'  => substr(decoct($d['mode']), -4),
							);
			}
			else {
				$final[] = $d['name'];
			}
		}

		return $final;
	}

	//+--------------------------------------------------------------------------
	// Add a directory to the tar files.
	//
	// $tar->add_directory( str(TO DIRECTORY) )
	//	Can be used in the following methods.
	//	  $tar->add_directory( "/foo/bar" );
	//	  $tar->write_tar( "/foo/bar" );
	//+--------------------------------------------------------------------------
	function add_directory($dir, $remove_path = false) {

		$this->error = "";

		if (!is_dir($dir)) {
			$this->error = "Extract files error: Destination directory ($dir) does not exist";
			return false;
		}

		$this->get_dir_contents($dir);
		$rpath = ($remove_path == false) ? '' : $dir;
		$this->add_files($this->workfiles, $rpath);

	}

	//+--------------------------------------------------------------------------
	// add files:
	//  Takes an array of files, and adds them to the tar file
	//  Optionally takes a path to remove from the paths in the file.
	//+--------------------------------------------------------------------------
	function add_files($files, $remove_path = '') {

		$count	= 0;

		foreach ($files as $file) {

			// is it a Mac OS X work file?
			if (preg_match("/\.ds_store/i", $file )) {
				continue;
			}

			$typeflag = 0;
			$data= "";
			$linkname = "";

			$stat = stat($file);
			// Did stat fail?
			if (!is_array($stat)) {
				$this->warnings[] = "Stat failed on {$file}";
				continue;
			}

			$mode  = fileperms($file);
			$uid   = $stat[4];
			$gid   = $stat[5];
			$rdev  = $stat[6];
			$size  = filesize($file);
			$mtime = filemtime($file);

			if (is_file($file)) {
				// It's a plain file, so lets suck it up
				$typeflag = 0;
				if ($FH = fopen($file, 'rb')) {
					$data = fread($FH, filesize($file));
					fclose($FH);
				}
				else {
					$this->warnings[] = "Failed to open $file";
					continue;
				}
			}
			else if (is_link($file)) {
				$typeflag = 1;
				$linkname = @readlink($file);
			}
			else if (is_dir($file)) {
				$typeflag = 5;
			}
			else {
				// Sockets, Pipes and char/block specials are not
				// supported, so - lets use a silly value to keep the
				// tar ball legitimate.
				$typeflag = 9;
			}

			// Add this data to our in memory tar file
			$this->tar_in_mem[] = array (
										'name'	 => str_replace($remove_path, '', $file),
										'mode'	 => $mode,
										'uid'	  => $uid,
										'gid'	  => $gid,
										'size'	 => strlen($data),
										'mtime'	=> $mtime,
										'chksum'   => "	  ",
										'typeflag' => $typeflag,
										'linkname' => $linkname,
										'magic'	=> "ustar\0",
										'version'  => '00',
										'uname'	=> 'unknown',
										'gname'	=> 'unknown',
										'devmajor' => "",
										'devminor' => "",
										'prefix'   => "",
										'data'	 => $data
									);
			// Clear the stat cache
			@clearstatcache();

			$count++;
		}

		//Return the number of files to anyone who's interested
		return $count;

	}

	function get_dir_contents($dir) {

		$dir = rtrim($dir, '/\\');
		if (file_exists($dir)) {
			if (is_dir($dir)) {
				$handle = opendir($dir);
				while (($filename = readdir($handle)) !== false) {
					if ($filename != '.' && $filename != '..') {
						if (is_dir($dir.'/'.$filename)) {
							$this->get_dir_contents($dir.'/'.$filename);
						}
						else {
							$this->workfiles[] = $dir.'/'.$filename;
						}
					}
				}
				closedir($handle);
			}
			else {
				$this->error = "{$dir} is not a directory";
				return false;
			}
		}
		else {
			$this->error = "Could not locate {$dir}";
			return false;
		}
	}

	//+--------------------------------------------------------------------------
	// Extract the tarball
	// $tar->extract_files( str(TO DIRECTORY), [ array( FILENAMES )  ] )
	//	Can be used in the following methods.
	//	  $tar->extract( "/foo/bar" , $files );
	// 	  This will seek out the files in the user array and extract them
	//	$tar->extract( "/foo/bar" );
	//	Will extract the complete tar file into the user specified directory
	// Returns: Files that could not be extracted
	//+--------------------------------------------------------------------------
	function extract_files($to_dir, $files = null) {
		global $filesystem;

		$this->error = "";

		// Make sure the $to_dir is pointing to a valid dir, or we error and return
		if (!is_dir($to_dir)) {
			$this->error = "Extract files error: Destination directory ($to_dir) does not exist";
			return false;
		}
		$to_dir = realpath($to_dir).DIRECTORY_SEPARATOR;

		//+------------------------------
		// Get the file info from the tar
		//+------------------------------
		$in_files = $this->read_tar();
		if (!empty($this->error)) {
			return false;
		}

		$error_files = array();
		foreach ($in_files as $k => $file) {

			$error_files[$k] = $file['name'];

			//---------------------------------------------
			// Are we choosing which files to extract?
			//---------------------------------------------
			if (is_array($files) && !in_array($file['name'], $files)) {
				continue;
			}

			//---------------------------------------------
			// GNU TAR format dictates that all paths *must* be in the *nix
			// format - if this is not the case, blame the tar vendor, not me!
			//---------------------------------------------
			if (preg_match("#/#", $file['name'])) {
				$path_info = explode("/", $file['name'] );
				$file_name = array_pop($path_info);
			}
			else {
				$path_info = array();
				$file_name = $file['name'];
			}

			//---------------------------------------------
			// If we have a path, then we must build the directory tree
			//---------------------------------------------
			$cur_dir = $to_dir;
			if (count($path_info) > 0) {
				foreach($path_info as $dir_component) {
					if (empty($dir_component)) {
						continue;
					}
					$cur_dir .= $dir_component.DIRECTORY_SEPARATOR;
					if ((file_exists($cur_dir)) && (!is_dir($cur_dir))) {
						$this->warnings[] = "{$cur_dir} exists, but is not a directory";
						continue;
					}
					if (!is_dir($cur_dir)) {
						$filesystem->mkdir($cur_dir, 0777);
					}
					else {
						$filesystem->chmod($cur_dir, 0777);
					}
				}
			}

			//---------------------------------------------
			// check the typeflags, and work accordingly
			//---------------------------------------------
			if (empty($file['typeflag'])) {
				$chmod_changed = false;
				if (file_exists($cur_dir.$file_name)) {
					$chmod = get_chmod($cur_dir.$file_name, true);
					$filesystem->chmod($cur_dir.$file_name, 0666);
					$chmod_changed = true;
				}
				if ($filesystem->file_put_contents($cur_dir.$file_name, $file['data'])) {
					unset($error_files[$k]);
				}
				else {
					$this->warnings[] = "Could not write data to {$cur_dir}{$file_name}";
				}
				if ($chmod_changed == true) {
					$filesystem->chmod($cur_dir.$file_name, $chmod);
				}
			}
			else if ($file['typeflag'] == 5) {
				if ((file_exists($cur_dir.$file_name)) && (!is_dir($cur_dir.$file_name))) {
					$this->warnings[] = "{$cur_dir}{$file_name} exists, but is not a directory";
					continue;
				}
				if (!is_dir($cur_dir.$file_name)) {
					if ($filesystem->mkdir($cur_dir.$file_name, 0777)) {
						unset($error_files[$k]);
					}
				}
				else {
					$filesystem->chmod($cur_dir.$file_name, 0777);
					unset($error_files[$k]);
				}
			}
			else if ($file['typeflag'] == 6) {
				$this->warnings[] = "Cannot handle named pipes";
				continue;
			}
			else if ($file['typeflag'] == 1) {
				$this->warnings[] = "Cannot handle system links";
				continue;
			}
			else if ($file['typeflag'] == 4) {
				$this->warnings[] = "Cannot handle device files";
				continue;
			}
			else if ($file['typeflag'] == 3) {
				$this->warnings[] = "Cannot handle device files";
				continue;
			}
			else {
				$this->warnings[] = "Unknown typeflag found";
				continue;
			}

			if ($this->ignore_chmod == false) {
				if (!$filesystem->chmod($cur_dir.$file_name, $file['mode'])) {
					$this->warnings[] = "CHMOD {$file['mode']} on {$cur_dir}{$file_name} failed!";
				}
			}

			@touch($cur_dir.$file_name, $file['mtime']);
		}

		return $error_files;
	}

	//+--------------------------------------------------------------------------
	// Writes the tarball into the directory / file specified in the constructor
	//+--------------------------------------------------------------------------
	function write_tar() {
		global $filesystem;

		if ($this->tarfile_path_name == "") {
			$this->error = 'No filename or path was specified to create a new tar file';
			return false;
		}

		if (count($this->tar_in_mem) < 1) {
			$this->error = 'No data to write to the new tar file';
			return false;
		}

		$tardata = "";
		foreach ($this->tar_in_mem as $file) {
			$prefix = "";
			$tmp	= "";
			$last   = "";

			// make sure the filename isn't longer than 99 characters.
			if (strlen($file['name']) > 99) {
				$pos = strrpos($file['name'], "/");
				if ($pos !== false) {
					// filename alone is longer than 99 characters!
					$this->error[] = "Filename {$file['name']} exceeds the length allowed by GNU Tape ARchives";
					continue;
				}
				$prefix = substr($file['name'], 0, $pos);  // Move the path to the prefix
				$file['name'] = substr($file['name'], ($pos+1));
				if (strlen($prefix) > 154) {
					$this->error[] = "File path exceeds the length allowed by GNU Tape ARchives";
					continue;
				}
			}

			// BEGIN FORMATTING (a8a1a100)
			$mode  = sprintf("%6s ", decoct($file['mode']));
			$uid   = sprintf("%6s ", decoct($file['uid']));
			$gid   = sprintf("%6s ", decoct($file['gid']));
			$size  = sprintf("%11s ", decoct($file['size']));
			$mtime = sprintf("%11s ", decoct($file['mtime']));
			$tmp  = pack("a100a8a8a8a12a12",$file['name'],$mode,$uid,$gid,$size,$mtime);

			$last  = pack("a1"   , $file['typeflag']);
			$last .= pack("a100" , $file['linkname']);
			$last .= pack("a6", "ustar"); // magic
			$last .= pack("a2", "" ); // version
			$last .= pack("a32", $file['uname']);
			$last .= pack("a32", $file['gname']);
			$last .= pack("a8", ""); // devmajor
			$last .= pack("a8", ""); // devminor
			$last .= pack("a155", $prefix);
			//$last .= pack("a12", "");
			$test_len = $tmp . $last . "12345678";
			$last .= str_repeat("\0", ($this->tar_header_length - strlen($test_len)));

			// Here comes the science bit, handling
			// the checksum.
			$checksum = 0;
			for ($i = 0 ; $i < 148 ; $i++ ) {
				$checksum += ord( substr($tmp, $i, 1) );
			}
			for ($i = 148 ; $i < 156 ; $i++) {
				$checksum += ord(' ');
			}
			for ($i = 156, $j = 0 ; $i < 512 ; $i++, $j++) {
				$checksum += ord( substr($last, $j, 1) );
			}
			$checksum = sprintf("%6s ", decoct($checksum));

			$tmp .= pack("a8", $checksum);
			$tmp .= $last;
			$tmp .= $file['data'];

			// Tidy up this chunk to the power of 512
			if ($file['size'] > 0) {
				if ($file['size'] % 512 != 0) {
					$homer = str_repeat( "\0" , (512 - ($file['size'] % 512)) );
					$tmp .= $homer;
				}
			}

			$tardata .= $tmp;
		}

		// Add the footer
		$tardata .= pack("a512", "");

		// print it to the tar file
		if ($filesystem->file_put_contents($this->tarfile_path_name, $tardata)) {
			return true;
		}
		else {
			$this->error[] = "File {$this->tarfile_path_name} is not writable.";
			return false;
		}
	}

	//+--------------------------------------------------------------------------
	// Read the tarball - builds an associative array
	//+--------------------------------------------------------------------------
	function read_tar() {

		$filename = $this->tarfile_path_name;

		if (empty($filename)) {
			$this->error = 'No filename specified when attempting to read a tar file';
			return array();
		}

		if (!file_exists($filename)) {
			$this->error = 'Cannot locate the file '.$filename;
			return array();
		}

		$tar_info = array();

		// Open up the tar file and start the loop
		if (!$FH = fopen($filename , 'rb' )) {
			$this->error = "Cannot open {$filename} for reading";
			return array();
		}

		// Grrr, perl allows spaces, PHP doesn't. Pack strings are hard to read without
		// them, so to save my sanity, I'll create them with spaces and remove them here
		$this->tar_unpack_header = preg_replace( "/\s/", "" , $this->tar_unpack_header);

		while (!feof($FH)) {

			$buffer = fread($FH , $this->tar_header_length);

			// check the block
			$checksum = 0;

			for ($i = 0 ; $i < 148 ; $i++) {
				$checksum += ord( substr($buffer, $i, 1) );
			}
			for ($i = 148 ; $i < 156 ; $i++) {
				$checksum += ord(' ');
			}
			for ($i = 156 ; $i < 512 ; $i++) {
				$checksum += ord( substr($buffer, $i, 1) );
			}

			$fa = unpack( $this->tar_unpack_header, $buffer);

			$name	 = trim($fa['filename']);
			$mode	 = OctDec(trim($fa['mode']));
			$uid	  = OctDec(trim($fa['uid']));
			$gid	  = OctDec(trim($fa['gid']));
			$size	 = OctDec(trim($fa['size']));
			$mtime	= OctDec(trim($fa['mtime']));
			$chksum   = OctDec(trim($fa['chksum']));
			$typeflag = trim($fa['typeflag']);
			$linkname = trim($fa['linkname']);
			$magic	= trim($fa['magic']);
			$version  = trim($fa['version']);
			$uname	= trim($fa['uname']);
			$gname	= trim($fa['gname']);
			$devmajor = OctDec(trim($fa['devmajor']));
			$devminor = OctDec(trim($fa['devminor']));
			$prefix   = trim($fa['prefix']);

			if ( ($checksum == 256) && ($chksum == 0) ) {
				//EOF!
				break;
			}

			// Mod: Added empty
			if (!empty($prefix)) {
				$name = $prefix.'/'.$name;
			}

			// Some broken tars don't set the type flag
			// correctly for directories, so we assume that
			// if it ends in / it's a directory...
			if (preg_match("#/$#" , $name)) {
				$typeflag = 5;
			}

			// If it's the end of the tarball...
			$test = str_repeat( '\0' , 512 );
			if ($buffer == $test) {
				break;
			}

			// Read the next chunk
			// Mod: Protect against error on 0 byte files
			if ($size > 0) {
				$data = @fread($FH, $size);
			}
			else {
				$data = '';
			}

			if (strlen($data) != $size) {
				$this->error = "Read error on tar file";
				fclose($FH);
				return array();
			}

			$diff = $size % 512;
			if ($diff != 0) {
				// Padding, throw away
				$crap = fread( $FH, (512-$diff) );
			}

			// Protect against tarfiles with garbage at the end
			if ($name == "") {
				break;
			}

			$tar_info[] = array (
							'name' => $name,
							'mode' => $mode,
							'uid' => $uid,
							'gid' => $gid,
							'size' => $size,
							'mtime' => $mtime,
							'chksum' => $chksum,
							'typeflag' => $typeflag,
							'linkname' => $linkname,
							'magic'	=> $magic,
							'version' => $version,
							'uname'	=> $uname,
							'gname'	=> $gname,
							'devmajor' => $devmajor,
							'devminor' => $devminor,
							'prefix' => $prefix,
							'data' => $data
						);
		}

		fclose($FH);

		return $tar_info;
	}

}
?>