<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2006  Matthias Mohr, MaMo Net
	
	Author: Matthias Mohr
	Publisher: http://www.mamo-net.de
	Start Date: May 22, 2004

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "function.global.php") die('Error: Hacking Attempt');

// Caching-Class
require_once('classes/class.cache.php');
// INI-File-Class
include_once("classes/class.ini.php");
// A class for Languages
require_once("classes/class.language.php");
// Gets modules
require_once("classes/class.plugins.php");
// Gets a file with Output-functions
require_once("classes/class.docoutput.php");
// BB-Code Class
include_once ("classes/class.bbcode.php");

$scache = new CacheServer();
$myini = new INI();
$lang = new lang();
$plugins = new PluginSystem();

// Database functions
require_once('classes/database/'.$config['dbsystem'].'.inc.php');
$db = new DB($config['host'], $config['dbuser'], $config['dbpw'], $config['database'], $config['pconnect'], true, $config['dbprefix']);

// Construct base bb-code object
$bbcode = new BBCode();

define('REMOTE_INVALID_URL', 100);
define('REMOTE_CLIENT_ERROR', 200);
define('REMOTE_FILESIZE_ERROR', 300);
define('REMOTE_IMAGE_HEIGHT_ERROR', 400);
define('REMOTE_IMAGE_WIDTH_ERROR', 500);
define('REMOTE_EXTENSION_ERROR', 600);
define('REMOTE_IMAGE_ERROR', 700);

function get_remote($file) {
	if (!class_exists('Snoopy')) {
		include('classes/class.snoopy.php');
	}
	
	if (!preg_match('/^(http:\/\/)([\wäöüÄÖÜ@\-_\.]+)\:?([0-9]*)\/(.*)$/', $file, $url_ary)) {
		return REMOTE_INVALID_URL;
	}

	$snoopy = new Snoopy;
	if (is_id($url_ary[3])) {
		$snoopy->port = $url_ary[3];
	}
	else {
		$snoopy->port = null;
	}
	$status = $snoopy->fetch($file);
	if ($status == true) {
		return $snoopy->results;
	}
	else {
		return REMOTE_CLIENT_ERROR;
	}
}

function checkRemotePic($pic, $id) {
	global $config, $filesystem;

	$avatar_data = get_remote($pic);
	if ($avatar_data == REMOTE_CLIENT_ERROR || $avatar_data == REMOTE_INVALID_URL) {
		return $avatar_data;
	}

	if (strlen($avatar_data) > $config['avfilesize']) {
		return REMOTE_FILESIZE_ERROR;
	}
	
	$filename = md5(uniqid($id));
	$origfile = 'temp/'.$filename;
	$filesystem->file_put_contents($origfile, $avatar_data);

	if (filesize($origfile) > $config['avfilesize']) {
		return REMOTE_FILESIZE_ERROR;
	}
    $imageinfo = @getimagesize($origfile);
    if (is_array($imageinfo)) {
    	list($width, $height, $type) = $imageinfo;
    }
    else {
    	return REMOTE_IMAGE_ERROR;
    }
	if ($width > $config['avwidth']) {
		return REMOTE_IMAGE_WIDTH_ERROR;
	}
	if ($height > $config['avheight']) {
		return REMOTE_IMAGE_HEIGHT_ERROR;
	}
    $types = explode(',', $config['avfiletypes']);
    $ext = image_type_to_extension($type);
	if (!in_array($ext, $types)) {
		return REMOTE_EXTENSION_ERROR;
	}

	$dir = 'uploads/pics/';
	$pic = $dir.$id.$ext;
	removeOldImages($dir, $id);
	@$filesystem->copy($origfile, $pic);
	
	return $pic;
}

function ini_maxupload() {
    $keys = array(
    'post_max_size' => 0,
    'upload_max_filesize' => 0
    );
    foreach ($keys as $key => $bytes) {
        $val = trim(@ini_get($key));
        $last = strtolower($val{strlen($val)-1});
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        $keys[$key] = $val;
    }
    return min($keys);
}

/**
 * orders a multidimentional array on the base of a label-key
 *
 * @param $arr, the array to be ordered
 * @param $l the "label" identifing the field
 * @param $f the ordering function to be used,
 *    strnatcasecmp() by default
 * @return  TRUE on success, FALSE on failure.
 */
function array_columnsort(&$arr, $l , $f='strnatcasecmp') {
	return uasort($arr, create_function('$a, $b', "return $f(\$a['$l'], \$b['$l']);"));
}

function array_empty($array) {
	$array = array_unique($array);
	if (count($array) == 0) {
		return true;
	}
	elseif (count($array) == 1) {
		$current = current($array);
		if (empty($current)) {
			return true;
		}
		else {
			return false;
		}
	}
	else {
		foreach ($array as $val) {
			if (!empty($val)) {
				return false;
			}
		}
		return true;
	}
}

function array_empty_trim($arr) {
	$array = array();
	foreach($arr as $key => $val) {
		$trimmed = trim($val);
		if (!empty($trimmed)) {
			$array[$key] = $val;
		}
	}
	return $array;
}
function send_nocache_header() {
	if (!empty($_SERVER['SERVER_SOFTWARE']) && strstr($_SERVER['SERVER_SOFTWARE'], 'Apache/2')) {
		header ('Cache-Control: no-cache, no-store, must-revalidate, pre-check=0, post-check=0');
	}
	else {
		header ('Cache-Control: private, no-store, must-revalidate, pre-check=0, post-check=0, max-age=0');
	}
	$now = gmdate('D, d M Y H:i:s').' GMT'; // rfc2616 - Section 14.21
	header ('Expires: '.$now);
	header ('Last-Modified: '.$now);
	header ('Pragma: no-cache');
}

function doctypes() {
	$data = file('data/documents.php');
	$arr = array();
	foreach ($data as $line) {
		list($id, $title, $tpl, $parser, $inline, $file) = explode("\t", $line, 6);
		$arr[$id] = array(
			'title' => $title,
			'template' => $tpl,
			'parser' => $parser,
			'inline' => $inline,
			'remote' => $file
		);
	}
	return $arr;
}

function file2array($file) {

	$filearray = array();
	$lines = file($file);
	
	foreach ($lines as $row) {
		$row = rtrim($row);
		$row = explode("\t",$row, 2);
		$filearray[$row[0]] = $row[1];
	}

	return $filearray;
}

function invert ($int) {
	if ($int == 1) {
		$int = 0;
	}
	elseif (empty($int)) {
		$int = 1;
	}
	else {
		$int = NULL;
	}

	return $int;
}

function extract_dir($source, $realpath = true) { 
	if ($realpath) {
		$source = realpath($source);
	}
	else {
		$source = rtrim($source, '/\\');
	}
	$pos = strrpos($source, '/');
	if ($pos === false) {
		$pos = strrpos($source, '\\');
	}
	if ($pos > 0) {
		$dest = substr($source, 0, $pos+1);
	}
	else {
		$dest = '';
	}
	return $dest; 
}

/*	Delete a file, or a folder and its contents
*	@author      Aidan Lister <aidan@php.net>
*	@version     1.0.0
*	@param       string   $dirname    The directory to delete
*	@return      bool     Returns true on success, false on failure
*/
function rmdirr($dirname) {
	global $filesystem;
	if (!file_exists($dirname)) {
		return false;
	}
	if (is_file($dirname)) {
		return $filesystem->unlink($dirname);
	} 
	$dir = dir($dirname);
	while (false !== $entry = $dir->read()) {
		if ($entry == '.' || $entry == '..') {
			continue;
		}
		if (is_dir("$dirname/$entry")) {
			rmdirr("$dirname/$entry");
		} 
		else {
			$filesystem->unlink("$dirname/$entry");
		}
	}  
	$dir->close(); 
	return $filesystem->rmdir($dirname);
}
/** 
 * Copy a file, or recursively copy a folder and its contents 
 * 
 * @author      Aidan Lister <aidan@php.net> 
 * @version     1.0.1 
 * @link        http://aidanlister.com/repos/v/function.copyr.php 
 * @param       string   $source    Source path 
 * @param       string   $dest      Destination path 
 * @return      bool     Returns TRUE on success, FALSE on failure 
 */ 
function copyr($source, $dest) {
	global $filesystem;
    if (is_file($source)) {
        return $filesystem->copy($source, $dest);
    }
    if (!is_dir($dest)) {
        $filesystem->mkdir($dest);
    }
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        if ($entry == '.' || $entry == '..') {
            continue;
        }
        if ($dest !== "$source/$entry") {
            copyr("$source/$entry", "$dest/$entry");
        }
    }

    $dir->close();
    return true;
}

function serverload($int = false) {
	if ($int == false) {
		$unknown = 'Unknown';
	}
	else {
		$unknown = -1;
	}
	if(strtolower(substr(PHP_OS, 0, 3)) === 'win') {
		return $unknown;
	}
	elseif(@file_exists("/proc/loadavg")) {
		$load = @file_get_contents("/proc/loadavg");
		$serverload = explode(" ", $load);
		$serverload[0] = round($serverload[0], 4);
		if(!$serverload) {
			$load = @exec("uptime");
			$load = split("load averages?: ", $load);
			if (isset($load[1])) {
				$serverload = @explode(",", $load[1]);
			}
		}
	}
	else {
		$load = @exec("uptime");
		$load = split("load averages?: ", $load);
		if (isset($load[1])) {
			$serverload = @explode(",", $load[1]);
		}
	}
	if (isset($serverload[0])) {
		$returnload = trim($serverload[0]);
	}
	if(empty($returnload)) {
		$returnload = $unknown;
	}
	return $returnload;
}

function convert2adress($url) { 
    
   $url = strtolower($url); 
    
   $find = array(' ', 
      '&quot;', 
      '&', 
      '\r\n', 
      '\n', 
      '/', 
      '\\', 
      '+', 
      '<', 
      '>'); 
   $url = str_replace ($find, '-', $url); 
    
   $find = array('é', 
      'è', 
      'ë', 
      'ê', 
      'É', 
      'È', 
      'Ë', 
      'Ê'); 
   $url = str_replace ($find, 'e', $url); 
    
   $find = array('í', 
      'ì', 
      'î', 
      'ï', 
      'Í', 
      'Ì', 
      'Î', 
      'Ï'); 
   $url = str_replace ($find, 'i', $url); 
    
   $find = array('ó', 
      'ò', 
      'ô', 
      'Ó', 
      'Ò', 
      'Ô'); 
   $url = str_replace ($find, 'o', $url); 
    
   $find = array('ö', 
       'Ö'); 
   $url = str_replace ($find, 'oe', $url); 
    
   $find = array('á', 
      'à', 
      'â', 
      'Á', 
      'À', 
      'Â'); 
   $url = str_replace ($find, 'a', $url); 
    
   $find = array('ä', 
       'Ä'); 
   $url = str_replace ($find, 'ae', $url); 
    
   $find = array('ú', 
      'ù', 
      'û', 
      'Ú', 
      'Ù', 
      'Û'); 
   $url = str_replace ($find, 'u', $url); 
    
   $find = array('ü', 
       'Ü'); 
   $url = str_replace ($find, 'ue', $url); 
    
   $find = array('ß'); 
   $url = str_replace ($find, 'ss', $url); 
    
   $find = array('/[^a-z0-9\-<>]/', 
      '/[\-]+/', 
      '/<[^>]*>/'); 
    
   $repl = array('', 
      '-', 
      ''); 
    
   $url =  preg_replace ($find, $repl, $url); 
    
   $url = str_replace ('--', '-', $url); 

   return $url; 
}

function is_id ($x) {
   return (is_numeric($x) && $x >= 1 ? intval($x) == $x : false);
}

function removeOldImages ($dir, $name) {
	global $filesystem;
    $dir = realpath($dir);
    $dir_open = @opendir($dir);
    while (($dir_content = readdir($dir_open)) !== false) {
        if ($dir_content != '.' && $dir_content != '..') {
            $ext = get_extension($dir_content, true);
            $fname = str_ireplace($ext, '', $dir_content);
            if ($fname == $name) {
                @$filesystem->unlink($dir.'/'.$dir_content);
            }
        }
    }
    closedir($dir_open);
}

function secure_path($path) {
	global $gpc;
	$path = $gpc->secure_null($path);
	$sd = realpath($path);
	$dr = realpath($_SERVER['DOCUMENT_ROOT']);
	if (!file_exists($sd)) {
		trigger_error('File '.$sd.' does not exist!', E_USER_WARNING);
	}
	if (strpos($path, '://') !== FALSE) {
		die('Hacking attemp (Path: Protocol)');
	}
	if (strpos($sd, $dr) === FALSE && file_exists($sd)) {
		die('Hacking attemp (Path: Not in Document_Root)');
	}
	$sd = str_replace($dr, '', $sd);
	if (DIRECTORY_SEPARATOR != '/') {
		$sd = str_replace(DIRECTORY_SEPARATOR, '/', $sd);
	}
	$char = substr($sd, strlen($sd)-1, 1);
	if (!is_file($sd) && $char != '/') {
		$sd .= '/';
	}
	return $sd;
}

function check_hp($hp) {
	if (preg_match("~^https?://[a-zA-Z0-9\-\.@]+\.[a-zA-Z0-9]{1,7}(:[A-Za-z0-9]*)?/?([a-zA-Z0-9\-\.:_\?\,;/\\\+&%\$#\=\~]*)?$~i", $hp)) {
		return true;
	}
	else {
		return false;
	}
}
function check_mail($email) {
	global $config;
	if(preg_match("/^([_a-zA-Zäöü0-9-]+(\.[_a-zA-Z0-9äöu-]+)*@[a-zA-Zäöu0-9-]+(\.[a-zA-Z0-9äöü-]+)*(\.[a-zA-Z]{2,}))/si",$email)){
	    if ($config['sessionmails'] == 0) {
	    	// get the domain in lower case
	    	$domain = strstr($email, '@');
	    	$domain = substr($domain, 1);
	    	$domain = strtolower($domain);
	    	// get the known doamins in lower case
	    	$sessionmails = file('data/sessionmails.php');
	    	$sessionmails = array_map("trim", $sessionmails);
	    	$sessionmails = array_map("strtolower", $sessionmails);
			// compare the data and return the result
			if (in_array($domain, $sessionmails)) {
				return FALSE;
			}
			else {
				return TRUE;
			}
	    }
	    else {
	    	return TRUE;
	    }
	}
	else {
		return FALSE;
	}
}

function benchmarktime() {
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

function strxlen($string) {
	$string = preg_replace('~&#([0-9]+);~', '-', $string);
	return strlen($string);
}

function random_word($laenge=8) {
    $newpass = "";
    $string="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    mt_srand((double)microtime()*1000000);

    for ($i=1; $i <= $laenge; $i++) {
        $newpass .= substr($string, mt_rand(0,strlen($string)-1), 1);
    }

    return $newpass;
}

function leading_zero($int,$length=2) {
	return sprintf("%0{$length}d", $int);
}

function times ($time=FALSE) {
	global $my;

	if ($time == FALSE) {
		$stime = time();
	}
	else {
		$stime = $time;
	}

	//$retime = $stime + 3600*($my->timezone + gmdate("I"));
	// gmdate('I') has a crazy bug. it shows something else (wrong) than date('I')
	$retime = $stime + 3600*($my->timezone + date("I"));

	return $retime;

}

function str_date($format, $time=FALSE) {
	global $config, $lang;

	if ($config['new_dformat4'] == 1) {

		if ($time == FALSE) {
			$stime = times();
		}
		else {
			$stime = $time;
		}

		$today 		= times() - gmmktime (0, 0, 0, gmdate('m',$stime), gmdate('d',$stime), gmdate('Y',$stime));
		if ($today < 0) {
			$returndate = gmdate($format, $time);
		}
		elseif ($today < 86400) {
			$returndate = $lang->phrase('date_today').gmdate($lang->phrase('dformat4'), $time);
		}
		elseif ($today < 172800) {
			$returndate = $lang->phrase('date_yesterday').gmdate($lang->phrase('dformat4'), $time);
		}
		else {
			$returndate = gmdate($format, $time);
		}
	
	}
	else {
		$returndate = gmdate($format, $time);
	}
	
	return $returndate;
}

// Returns the extension in lower case ( using pathinfo() ) of an file with a leading dot (e.g. '.gif' or '.php') or not ($leading = false)
function get_extension($url, $include_dot = false) {
	$path_parts = pathinfo($url);
	if (!isset($path_parts["extension"])) {
		$path_parts["extension"] = '';
	}
	if ($include_dot == false) {
		return strtolower($path_parts["extension"]);
	}
	else {
		return '.'.strtolower($path_parts["extension"]);
	}
}
function UpdateBoardStats ($board) {
	global $config, $db, $scache;
	if ($config['updateboardstats'] == '1') {
		$result = $db->query("SELECT COUNT(*) FROM {$db->pre}replies WHERE board='{$board}'",__LINE__,__FILE__);
		$count = $db->fetch_num ($result);

		$result = $db->query("SELECT COUNT(*) FROM {$db->pre}topics WHERE board='{$board}'",__LINE__,__FILE__);
		$count2 = $db->fetch_num($result);
		
		$replies = $count[0]-$count2[0];
		$topics = $count2[0];

		$result = $db->query("SELECT id FROM {$db->pre}topics WHERE board = '{$board}' ORDER BY last DESC LIMIT 1",__LINE__,__FILE__);
	    $last = $db->fetch_num($result);
	    if (empty($last[0])) {
			$last[0] = 0;
		}
		$db->query("
		UPDATE {$db->pre}forums SET topics = '{$topics}', replies = '{$replies}', last_topic = '{$last[0]}' 
		WHERE id = '{$board}'
		",__LINE__,__FILE__);
		$delobj = $scache->load('cat_bid');
		$delobj->delete();
	}
}

function iif($if, $true, $false = '') {
	return ($if ? $true : $false);
}

function getip($dots = 4) {
	$ips = array();
	$indices = array('REMOTE_ADDR', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP');
	foreach ($indices as $index) {
		// $_SERVER is sometimes for a windows server which can't handle getenv()
		$tip = @getenv($index);
		if(!empty($tip)) {
			$ips[] = $tip;
		}
		if(!empty($_SERVER[$index])) {
			$ips[] = $_SERVER[$index];
		}
	}

   	$private_ips = array("/^0\..+$/", "/^127\.0\.0\..+$/", "/^192\.168\..+$/", "/^172\.16\..+$/", "/^10..+$/", "/^224..+$/", "/^240..+$/", "/[^\d\.]+/");
	$ips = array_unique($ips);

	foreach ($ips as $ip) {
		$found = false;
		if (!preg_match("/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/", $ip)) {
			$found = true;
		}
		foreach ($private_ips as $pip) {
			if (preg_match($pip, trim($ip))) {
				$found = true;
			}
		}
		if ($found == false) {
			return ext_iptrim(trim($ip), $dots);
		}
	}

	$b = _EnvValToInt('HTTP_USER_AGENT');
	$c = _EnvValToInt('HTTP_ACCEPT');
	$d = _EnvValToInt('HTTP_ACCEPT_LANGUAGE');
	$ip = "0.{$b}.{$c}.{$d}";
	return ext_iptrim($ip, $dots);
}

function _EnvValToInt($x) {
	$y = getenv($x);
	if (empty($y)) {
		if (isset($_SERVER[$y])) {
			$y = $_SERVER[$y];
		}
		else {
			$y = 7;
		}
	}
	$length = strlen($y)-1;
	if ($length > 0) {
		$i = ord($y{$length});
	}
	else {
		$i = 5;
	}
	return $i;
}

function ext_iptrim ($text, $peaces) {
	if ($peaces > 3) {
		return $text;
	}
	$arr = explode('.', $text);
	if ($peaces > count($arr)) {
		$peaces = count($arr);
	}
	$str = '';
	for ($i = 0; $i < $peaces; $i++) {
		$str .= $arr[$i].'.';
	}
	return $str;
}

function getAge($bday) {
	$now = times();
    if (gmdate("Y", $now) == $bday[0] && gmdate("m", $now) == $bday[1] && gmdate("d", $now) == $bday[2]) {
		$result = 0;
    }
	else {
    	$result = gmdate("Y", $now) - $bday[0];
    	if ($bday[1] > gmdate("m", $now)) {
    		$result--;
    	}
    	elseif ($bday[1] == gmdate("m", $now)) {
        	if ($bday[2] > gmdate("d",$now)) {
        		$result--;
        	}
    	}
    }
    return $result;
}

function CheckForumTree($tree, &$tree2, $board) {
	foreach ($tree as $cid => $boards) {
		foreach ($boards as $bid => $sub) {
			$bdata = $board[$bid];
			if ($bdata['opt'] == 're' || !check_forumperm($bdata)) {
				//unset();
			}
	    	CheckForumTree($sub, $tree2, $board);
	    }
	}
}

function BoardSubs ($group = true) {
	global $scache;
	
	$forumtree = $scache->load('forumtree');
	$tree = $forumtree->get();
	
	$categories_obj = $scache->load('categories');
	$categories = $categories_obj->get();
	
	$catbid = $scache->load('cat_bid');
	$boards = $catbid->get();
	
	$tree2 = array();
	$forums = SelectForums(array(), $tree, $categories, $boards, $group);
	return implode("\n", $forums);
}

function SelectForums($html, $tree, $cat, $board, $group = true, $char = '&nbsp;&nbsp;', $level = 0) {
	foreach ($tree as $cid => $boards) {
		$cdata = $cat[$cid];
		if ($group) {
			$html[] = '<optgroup label="'.str_repeat($char, $level).$cdata['name'].'"></optgroup>'; //We have to close it beacuse we can not nest optgroup
		}
		else {
			$html[] = '<option style="font-weight: bold;" value="'.$cdata['id'].'">'.str_repeat($char, $level).$cdata['name'].'</option>';
		}
		$i = 0;
		foreach ($boards as $bid => $sub) {
			$bdata = $board[$bid];
			if ($bdata['opt'] == 're' || !check_forumperm($bdata)) {
				continue;
			}
			$i++;
			$html[] = '<option value="'.$bdata['id'].'">'.str_repeat($char, $level+1).$bdata['name'].'</option>';
	    	$html = SelectForums($html, $sub, $cat, $board, $group, $char, $level+2);
	    }
	    if ($i == 0) {
	    	$x = array_pop($html);
	    }
	}
	return $html;
}

function check_forumperm($forum) {
	global $my, $scache;

	$parent_forums = $scache->load('parent_forums');
	$tree = $parent_forums->get();
	
	$catbid = $scache->load('cat_bid');
	$forums = $catbid->get();

	if (isset($tree[$forum['id']]) && is_array($tree[$forum['id']])) {
		foreach ($tree[$forum['id']] as $id) {
			if ($forums[$id]['opt'] == 'pw') {
				if (!isset($my->pwfaccess[$id]) || $forums[$id]['optvalue'] != $my->pwfaccess[$id]) {
					return false;
				}
			}
			if ($forums[$id]['invisible'] == 2) {
				return false;
			}
		}
	}
	if ($my->p['forum'] == 0) {
		if (isset($my->pb[$forum['id']]) && $my->pb[$forum['id']]['forum'] == 1) {
			return true;
		}
		else {
			return false;
		}
	}
	else {
		if (isset($my->pb[$forum['id']]) && $my->pb[$forum['id']]['forum'] == 0) {
			return false;
		}
		else {
			return true;
		}
	}
}

/*
Params:
	(array/string)	$to		= Absender
					$to[]['name'] = Empfängername (opt)
					$to[]['mail'] = Empfängeremail
	(array/string)	$from		= Absender (opt)
					$from['name'] = Absendername (opt)
					$from['mail'] = Absenderemail (opt)
	(string)		$topic 		= Titel
	(string)		$comment 	= Inhalt
	(int)			$html 		= HTML-Modus (0/1)
	(array)			$attachment = Empfänger (opt)
					$attachment[]['file'] = Anhang der verschickt werden soll
					$attachment[]['name'] = Dateiname für dei zu verschickende Datei
					$attachment[]['type'] = [path|string] (opt)
*/

function xmail ($to, $from = array(), $topic, $comment, $type='plain', $attachment = array()) {
	global $config, $my, $lang, $bbcode;
	
	require_once("classes/mail/class.phpmailer.php");
	require_once('classes/mail/extended.phpmailer.php');
	
	$mail = new PHPMailer();
	$mail->CharSet = $lang->phrase('charset');
	
	// Added Check_mail for better security
	// Now it is not possible to add various headers to the mail
	if (!isset($from['mail']) || !check_mail($from['mail'])) {
		$mail->From = $config['forenmail'];
	}
	else {
		$mail->From = $from['mail'];
	}
	if (!isset($from['name'])) {
		$mail->FromName = $config['fname'];
	}
	else {
		$mail->FromName = $from['name'];
	}
	if ($config['smtp'] == 1) {
		$mail->Mailer   = "smtp";
		$mail->IsSMTP();
		$mail->Host     = $config['smtp_host'];
		if ($config['smtp_auth'] == 1) {
			$mail->SMTPAuth = TRUE;
			$mail->Username = $config['smtp_username'];
			$mail->Password = $config['smtp_password'];
		}
	}
	elseif ($config['sendmail'] == 1) {
		$mail->IsSendmail();
		$mail->Mailer   = "sendmail";
		$mail->Sendmail = $config['sendmail_host'];
	}
	else {
		$mail->IsMail();
	}
	
	$mail->Subject = $topic;
	if (!is_array($to)) {
		$to = array('0' => array('mail' => $to));
	}
	$i = 0;
	foreach ($to as $email) {
		if ($type == 'bb') {
			BBProfile($bbcode);
			$bbcode->setSmileys(0);
			$bbcode->setReplace($config['wordstatus']);
			$row->comment = ($row->comment);
			$mail->IsHTML(TRUE);
	    	$mail->Body    = $bbcode->parse($comment);
	    	$mail->AltBody = $bbcode->parse($comment, 'plain');
		}
		elseif ($type == 'html') {
			$mail->IsHTML(TRUE);
	    	$mail->Body    = $comment;
	    	$mail->AltBody = html_entity_decode(strip_tags($comment));
	    }
	    else {
	    	$mail->Body    = html_entity_decode($comment);
	    }
	    
	    if (isset($email['name'])) {
	    	$mail->AddAddress($email['mail'], $email['name']);
	    }
	    else {
	    	$mail->AddAddress($email['mail']);
	    }
	    
	    foreach ($attachment as $file) {
	    	if ($file['type'] == 'string') {
	    		$mail->AddStringAttachment($file['file'], $file['name']);
	    	}
	    	else {
	    		$mail->AddAttachment($file['file'], $file['name']);
	    	}
	    }
	
		if ($mail->Send()) {
			$i++;
		}
		
	    $mail->ClearAddresses();
	    $mail->ClearAttachments();
	}
	return $i;
}

function getcookie($name) {
    global $config;
    if (isset($_COOKIE[$config['cookie_prefix'].'_'.$name])) {
    	return $_COOKIE[$config['cookie_prefix'].'_'.$name];
	}
	else {
		return NULL;
	}
}

function makecookie($name, $value = '', $expire = 31536000) {

	if (SCRIPTNAME == 'external') {
		return FALSE;
	}

//	if ($_SERVER['SERVER_PORT'] == '443' || isset($_SERVER['HTTPS'])) {
//		$secure = 1;
//	}
//	else {
//		$secure = 0;
//	}
	if ($expire != null) {
		$expire = time() + $expire;
	}
	else {
		$expire = 0;
	}
	setcookie($name, $value, $expire);
}
?>
