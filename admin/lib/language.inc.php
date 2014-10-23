<?php
function getLangVarsHelp() {
	$var = 'Das Sprachensystem unterstützt das variable Einsetzen von Platzhaltern in Phrasen und Texten. Alle Platzhalter werden von { und } umschlossen. Man kann die Variablen aus PHP wie folgt benutzen:<br />';
	$var .= 'Normale Variablen des Typs <code>$var</code> werden zu <code>{$var}</code>,<br />';
	$var .= 'Arrays des Typs <code>$var[&#039;key&#039;]</code> werden zu <code>{@var->key}</code> und <br />';
	$var .= 'Objekte des Typs <code>$var->key</code> werden zu <code>{%var->key}</code>.';
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
		@include($file);
	}
	if (!isset($lang) || !is_array($lang)) {
		$lang = array();
	}
	return $lang;
}

function createParentDir($parentfile, $path) {
	global $filesystem;
	$parents = array();
	while(($pos = strrpos($parentfile, '/')) !== false) {
		$parentfile = substr($parentfile, 0, $pos);
		$parents[] = $parentfile;
	}
	$parents = array_reverse($parents);
	foreach ($parents as $dir) {
		$path = $path.'/'.$dir;
		if (!file_exists($path)) {
			$filesystem->mkdir($path);
		}
	}
}
?>
