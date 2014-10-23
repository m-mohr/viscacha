<?php
define('CHMOD_FILE', 'is_file');
define('CHMOD_DIR', 'is_dir');

function check_writable($filename, $chmod = 0666, $notcreate = false, $func = CHMOD_FILE) {
	global $filesystem;
	if (file_exists($filename) || $notcreate == true) {
		if (!is_writable($filename)) {
			if (!$filesystem->chmod($filename, $chmod)) {
				trigger_error("Cannot change the chmod of file ($filename) to ".octdec($chmod), E_USER_WARNING);
			}
		}
	}
	else {
		$filesystem->file_put_contents($filename, '');
		check_writable($filename, $chmod, true);
	}
}
function check_executable($dir, $chmod = 0777, $notcreate = false, $func = CHMOD_DIR) {
	global $filesystem;
	if (file_exists($dir) || $notcreate == true) {
		if (!is_executable($dir)) {
			if (!$filesystem->chmod($dir, $chmod)) {
				trigger_error("Cannot change the chmod of directory ($dir) to ".octdec($chmod), E_USER_WARNING);
			}
		}
	}
	else {
		$filesystem->mkdir($dir, $chmod);
		check_executable($dir, $chmod, true);
	}
}
function check_executable_r($dir, $chmod = 0777, $func = CHMOD_DIR, $first = false) {
	$dh = opendir($dir);
	if (!$first) {
		check_executable($dir, $chmod, false, $func);
	}
	while ($file = readdir($dh)) {
		if($file != '.' && $file != '..') {
			$fullpath = $dir.DIRECTORY_SEPARATOR.$file;
			if(is_dir($fullpath)) {
				check_executable($fullpath, $chmod, false, $func);
				check_executable_r($fullpath, $chmod, $func, true);
			}
		}
	}
	closedir($dh);
}
function check_writable_r($dir, $chmod = 0666, $func = CHMOD_FILE, $first = false) {
	$dh = opendir($dir);
	if (!$first) {
		check_writable($dir, $chmod, false, $func);
	}
	while ($file = readdir($dh)) {
		if($file != '.' && $file != '..') {
			$fullpath = $dir.DIRECTORY_SEPARATOR.$file;
			if(is_file($fullpath)) {
				check_writable($fullpath, $chmod, false, $func);
			}
			elseif (is_dir($fullpath)) {
				check_writable_r($fullpath, $chmod, $func, true);
			}
		}
	}
	closedir($dh);
}

function chmod_str2num($mode) {
   $realmode = "";
   $legal =  array("","w","r","x","-");
   $attarray = preg_split("//",$mode);
   for($i=0;$i<count($attarray);$i++){
       if($key = array_search($attarray[$i],$legal)){
           $realmode .= $legal[$key];
       }
   }
   $mode = str_pad($realmode,9,'-');
   $trans = array('-'=>'0','r'=>'4','w'=>'2','x'=>'1');
   $mode = strtr($mode,$trans);
   $newmode = '';
   $newmode .= $mode[0]+$mode[1]+$mode[2];
   $newmode .= $mode[3]+$mode[4]+$mode[5];
   $newmode .= $mode[6]+$mode[7]+$mode[8];
   return $newmode;
}

function chmod_str2oct($mode) {
	$mode = '0'.$mode;
	return octdec($mode);
}

function get_chmod($file, $numeric = true) {
	$perms = @fileperms($file);

	if (($perms & 0xC000) == 0xC000) {
	   // Socket
	   $info = 's';
	} elseif (($perms & 0xA000) == 0xA000) {
	   // Symbolic Link
	   $info = 'l';
	} elseif (($perms & 0x8000) == 0x8000) {
	   // Regular
	   $info = '-';
	} elseif (($perms & 0x6000) == 0x6000) {
	   // Block special
	   $info = 'b';
	} elseif (($perms & 0x4000) == 0x4000) {
	   // Directory
	   $info = 'd';
	} elseif (($perms & 0x2000) == 0x2000) {
	   // Character special
	   $info = 'c';
	} elseif (($perms & 0x1000) == 0x1000) {
	   // FIFO pipe
	   $info = 'p';
	} else {
	   // Unknown
	   $info = 'u';
	}
	
	// Owner
	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ?
	           (($perms & 0x0800) ? 's' : 'x' ) :
	           (($perms & 0x0800) ? 'S' : '-'));
	
	// Group
	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ?
	           (($perms & 0x0400) ? 's' : 'x' ) :
	           (($perms & 0x0400) ? 'S' : '-'));
	
	// World
	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ?
	           (($perms & 0x0200) ? 't' : 'x' ) :
	           (($perms & 0x0200) ? 'T' : '-'));
	
	if ($numeric) {
		$info = chmod_str2num($info);
	}
	
	return $info;
}


if ($config['check_filesystem'] == 1) {
	check_writable_r('data');
	check_writable_r('feeds');
	check_writable('.htaccess');
	check_executable_r('cache');
	check_executable_r('temp');
	check_executable_r('uploads');
	check_executable_r('data');
	check_executable('feeds');
}
?>
