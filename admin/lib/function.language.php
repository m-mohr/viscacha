<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

function sort_dirlist($a, $b) {
	$ai = substr_count($a, '/');
	$bi = substr_count($b, '/');
	if ($ai  == $bi) {
		$test = array($a, $b);
		sort($test);
		return ($test[0] == $a) ? -1 : 1;
	}
	return ($ai < $bi) ? 1 : -1;
}

function dir_array($dir, $chop = false) {
	$array = array();
	$d = dir($dir);
	if ($chop != false && substr($dir, -1, 1) != '/') {
		$dir .= '/';
	}
	if ($chop != false && substr($chop, -1, 1) != '/') {
		$chop .= '/';
	}
	while (FALSE !== ($entry = $d->read())) {
		if($entry!='.' && $entry!='..') {
			$entry = $dir.$entry;
			if(is_dir($entry)) {
				$array = array_merge($array, dir_array($entry, $chop));
			}
			else {
		   		if ($chop != false) {
			   		$array[] = str_replace($chop, '', $entry);
		   		}
		   		else {
		   			$array[] = $entry;
		   		}
	   		}
   		}
   	}
	$d->close();
	return $array;
}

function return_array($group, $id) {
	$file = "language/{$id}/{$group}.lng.php";
	return arrayFromFile($file);
}
function arrayFromFile($file, $varname = 'lang') {
	if (file_exists($file)) {
		include($file);
	}
	if (!isset($$varname) || !is_array($$varname)) {
		$$varname = array();
	}
	return $$varname;
}

function createParentDir($parentfile, $path) {
	global $filesystem;
	$parents = array();
	while(($pos = strrpos($parentfile, DIRECTORY_SEPARATOR)) !== false) {
		$parentfile = substr($parentfile, 0, $pos);
		$parents[] = $parentfile;
	}
	$parents = array_reverse($parents);
	foreach ($parents as $dir) {
		$path2 = $path.DIRECTORY_SEPARATOR.$dir;
		if (!file_exists($path2)) {
			$filesystem->mkdir($path2, 0777);
		}
	}
}

function getLangCodes() {
	global $db;
	$l = array();
	$result = $db->query('SELECT id FROM '.$db->pre.'language ORDER BY language');
	while($row = $db->fetch_assoc($result)) {
		$settings = return_array('settings', $row['id']);
		if (!isset($l[$settings['spellcheck_dict']])) {
			$l[$settings['spellcheck_dict']] = array();
		}
		$l[$settings['spellcheck_dict']] = $row['id'];
	}
	return $l;
}
?>