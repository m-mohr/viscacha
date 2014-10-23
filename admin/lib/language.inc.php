<?php
function getLangVarsHelp() {
	$var = 'The language system supports the variable insertion of wildcard-characters in phrases and texts. All wildcards will be bracket by { and }. You can use the variables from PHP as follows:<br />';
	$var .= 'Normal variables of the type <code>$var</code> will become <code>{$var}</code>,<br />';
	$var .= 'Arrays of the type <code>$var[&#039;key&#039;]</code> will become <code>{@var->key}</code> and<br />';
	$var .= 'Objects of the type <code>$var->key</code> will become <code>{%var->key}</code>.';
	return $var;
}

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
	if (file_exists($file)) {
		include($file);
	}
	if (!isset($lang) || !is_array($lang)) {
		$lang = array();
	}
	return $lang;
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
	global $db, $myini;
	$l = array();
	$result = $db->query('SELECT id FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
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
