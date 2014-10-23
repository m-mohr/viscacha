<?php
define('CHMOD_FILE', 'is_file');
define('CHMOD_DIR', 'is_dir');

function set_chmod($dir, $chmod, $type = 'is_file', $stop = false) {
	global $filesystem;
	if (file_exists($dir) && $type($dir)) {
		if (!check_chmod(decoct($chmod), get_chmod($dir))) {
			$filesystem->chmod($dir, $chmod);
		}
	}
	else {
		if ($type == CHMOD_DIR && !$stop) {
			$filesystem->mkdir($dir, $chmod);
			set_chmod($dir, $chmod, CHMOD_DIR, true);
		}
		elseif (!$stop) {
			$filesystem->file_put_contents($dir, '');
			set_chmod($dir, $chmod, CHMOD_FILE, true);
		}
	}
}
function set_chmod_r($dir, $chmod, $type = 'is_file', $files = array()) {
	$dh = opendir($dir);
	if (count($files) == 0 && $type == CHMOD_DIR) {
		set_chmod($dir, $chmod, $type);
		$files[] = $dir;
	}
	while ($file = readdir($dh)) {
		if($file != '.' && $file != '..') {
			$fullpath = $dir.'/'.$file;
			if($type($fullpath)) {
				set_chmod($fullpath, $chmod, $type);
				$files[] = $fullpath;
			}
			if (is_dir($fullpath)) {
				$files = set_chmod_r($fullpath, $chmod, $type, $files);
			}
		}
	}
	closedir($dh);
	return $files;
}

function get_chmod($file) {
	if (!file_exists($file)) {
		return '0000';
	}
	else {
		$perms = fileperms($file);
		$info = substr(sprintf('%o', $perms), -4);
		return $info;
	}
}

function check_chmod($min, $given) {
	$min = explode("\r\n", chunk_split($min, 1));
	$given = explode("\r\n", chunk_split(substr($given, 1, 3), 1));

	if ($given[0] >= $min[0] && $given[1] >= $min[1] && $given[2] >= $min[2]) {
		return true;
	}
	else {
		return false;
	}
}
?>
