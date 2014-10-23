<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2009  The Viscacha Project

	Author: Matthias Mohr (et al.)
	Publisher: The Viscacha Project, http://www.viscacha.org
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

if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

define('URL_SPECIALCHARS', 'a-zA-ZáàâÁÀÂçÇéèëêÉÈËÊíìîïÍÌÎÏóòôÓÒÔúùûÚÙÛäÄöÖüÜ');
define('URL_REGEXP', 'https?://['.URL_SPECIALCHARS.'\d\-\.@]+(?:\.[a-z]{2,7})?(?::\d+)?/?(?:['.URL_SPECIALCHARS.'ß\d\-\.:_\?\,;/\\\+&%\$#\=\~\[\]]*['.URL_SPECIALCHARS.'ß\d\-\.:_\?\,;/\\\+&%\$#\=\~])?');
define('EMAIL_REGEXP', "[".URL_SPECIALCHARS."\d!#\$%&'\*\+/=\?\^_\{\|\}\~\-]+(?:\.[".URL_SPECIALCHARS."\d!#$%&'\*\+/=\?\^_\{\|\}\~\-]+)*@(?:[".URL_SPECIALCHARS."\d](?:[".URL_SPECIALCHARS."\d\-]*[".URL_SPECIALCHARS."\d])?\.)+[".URL_SPECIALCHARS."\d](?:[".URL_SPECIALCHARS."\d\-]*[".URL_SPECIALCHARS."\d])?");

define('REMOTE_INVALID_URL', 100);
define('REMOTE_CLIENT_ERROR', 200);
define('REMOTE_FILESIZE_ERROR', 300);
define('REMOTE_IMAGE_HEIGHT_ERROR', 400);
define('REMOTE_IMAGE_WIDTH_ERROR', 500);
define('REMOTE_EXTENSION_ERROR', 600);
define('REMOTE_IMAGE_ERROR', 700);

define('CAPTCHA_FAILURE', 0);
define('CAPTCHA_OK', 1);
define('CAPTCHA_MISTAKE', 2);
define('CAPTCHA_TYPE_2', 'ReCaptcha');
define('CAPTCHA_TYPE_1', 'VeriWord');

// Caching-Class
require_once('classes/class.cache.php');
// INI-File-Class
include_once("classes/class.ini.php");
// Gets modules
require_once("classes/class.plugins.php");
// Gets a file with Output-functions
require_once("classes/class.docoutput.php");
// BB-Code Class
include_once ("classes/class.bbcode.php");

$scache = new CacheServer();
$plugins = new PluginSystem();

// Construct base bb-code object
$bbcode = new BBCode();

function is_hash($string) {
	return (bool) preg_match("/^[a-f\d]{32}$/i", $string);
}

function newCAPTCHA($place = null) {
	global $config;
	$place = 'botgfxtest'.iif(!empty($place), '_'.$place);
	$cfg = $config[$place];
	$type = constant('CAPTCHA_TYPE_'.$cfg);
	$filename = strtolower($type);
	require_once("classes/graphic/class.{$filename}.php");
	$obj = new $type();
	return $obj;
}

function splitWords($text) {
	$word_seperator = "\\.\\,;:\\+!\\?\\_\\|\s\"'\\#\\[\\]\\%\\{\\}\\(\\)\\/\\\\";
	return preg_split('/['.$word_seperator.']+?/', $text, -1, PREG_SPLIT_NO_EMPTY);
}

function checkmx_idna($host) {
	if (empty($host)) {
		return false;
	}
	$idna = new idna_convert();
	$host_idna = $idna->encode($host);
	if (viscacha_function_exists('checkdnsrr')) {
		if (checkdnsrr($host_idna, 'MX') === false) {
			return false;
		}
		else {
			return true;
		}
	}
	else {
	   @exec("nslookup -querytype=MX {$host_idna}", $output);
	   while(list($k, $line) = each($output)) {
		   # Valid records begin with host name
		   if(preg_match("~^(".preg_quote($host, '~')."|".preg_quote($host_idna, '~').")~i", $line)) {
			   return true;
		   }
	   }
	   return false;
   }
}

function get_remote($file) {
	if (!class_exists('Snoopy')) {
		include('classes/class.snoopy.php');
	}

	if (!preg_match('~^'.URL_REGEXP.'$~i', $file)) {
		return REMOTE_INVALID_URL;
	}

	if (preg_match('~^www\.~i', $file)) {
		$file = 'http://'.$file;
	}

	$url_ary = parse_url($file);

	$snoopy = new Snoopy;
	if (isset($url_ary['port']) && is_id($url_ary['port'])) {
		$snoopy->port = $url_ary['port'];
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
	$types = explode(',', strtolower($config['avfiletypes']));
	$ext = image_type_to_extension($type, false);
	if (!in_array($ext, $types)) {
		return REMOTE_EXTENSION_ERROR;
	}

	$dir = 'uploads/pics/';
	$pic = $dir.$id.'.'.$ext;
	removeOldImages($dir, $id);
	$filesystem->copy($origfile, $pic);

	return $pic;
}

function saveCommaSeparated($list) {
	$list = preg_replace('~[^\d,]+~i', '', $list);
	$list = explode(',', $list);
	$list = array_empty_trim($list);
	$list = implode(',', $list);
	return $list;
}

function JS_URL($url) {
	if (preg_match('~javascript:\s?([^;]+);?~i', $url, $command) && isset($command[1])) {
		$url = $command[1];
	}
	else {
		$url = 'location.href="'.$url.'"';
	}
	return $url;
}

/**
 * orders a multidimentional array on the base of a label-key
 *
 * @param $arr, the array to be ordered
 * @param $l the "label" identifing the field
 * @param $f the ordering function to be used,
 *	strnatcasecmp() by default
 * @return  TRUE on success, FALSE on failure.
 */
function array_columnsort(&$arr, $l , $f='strnatcasecmp') {
	return uasort($arr, create_function('$a, $b', "return $f(\$a['$l'], \$b['$l']);"));
}

function array_empty($array) {
	if (!is_array($array)) {
		return null;
	}
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

function array_empty_trim($array) {
	if (!is_array($array)) {
		trigger_error('array_empty_trim() expected argument to be an array!', E_USER_NOTICE);
	}
	else {
		foreach($array as $key => $value) {
			$value = trim($value);
			if (empty($value)) {
				unset($array[$key]);
			}
		}
	}
	return $array;
}

function double_udata ($opt,$val) {
	global $db;
	$result = $db->query('SELECT id FROM '.$db->pre.'user WHERE '.$opt.' = "'.$val.'" LIMIT 1');
	if ($db->num_rows($result) == 0) {
		if ($opt == 'name') {
			$olduserdata = file('data/deleteduser.php');
			foreach ($olduserdata as $row) {
				$row = trim($row);
				if (!empty($row)) {
					$row = explode("\t", $row);
					if (strtolower($row[1]) == strtolower($val)) {
						return false;
					}
				}
			}
		}
		return true;
	}
	else {
		return false;
	}
}

function getDocLangID($data) {
	global $my, $config;
	if (isset($my->language) && is_id($my->language) && isset($data[$my->language])) {
		return $my->language; // Best case: Language specified by the user
	}
	elseif (is_id($config['doclang']) && isset($data[$config['doclang']])) {
		return $config['doclang']; // Normal Case: Standard language specified for documents
	}
	elseif (is_id($config['langdir']) && isset($data[$config['langdir']])) {
		return $config['langdir']; // Worse Case: Standard language of the page
	}
	else {
		reset($data);
		return key($data); // Worst Case: Take another language... let's say just the first in the list?!
	}
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

function file2array($file, $delimiter = ';') {

	$filearray = array();
	$lines = file($file);

	foreach ($lines as $row) {
		$row = rtrim($row);
		$row = explode($delimiter,$row, 2);
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

function serverload($int = false) {
	if ($int == false) {
		$unknown = '-';
	}
	else {
		$unknown = -1;
	}
	if(isWindows() == true) {
		return $unknown;
	}
	if(@file_exists("/proc/loadavg")) {
		$load = @file_get_contents("/proc/loadavg");
		$serverload = explode(" ", $load);
		$serverload[0] = round($serverload[0], 4);
	}
	if (viscacha_function_exists('sys_getloadavg')) {
		$serverload = @sys_getloadavg();
	}
	if (empty($serverload[0]) && viscacha_function_exists('exec') == true) {
		$load = @exec("uptime");
		$load = preg_split("~load averages?: ~i", $load);
		if (isset($load[1])) {
			$serverload = explode(",", $load[1]);
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

function convert2path($path, $returnEmptyOnInvalid = false) {
	$newPath = str_replace ('\\', '/', $path);
	$newPath = str_replace (array('<', '>', ':', '?', '*', '"', "\0", "\r", "\n", "\t"), '_', $newPath);
	// Replace multiple delimiter chars with only one char
	$newPath = preg_replace('/_+/', '_', $newPath);

	if ($returnEmptyOnInvalid && $path != $newPath) {
		return "";
	}
	return $newPath;
}

function convert2adress($url, $toLower = true, $spacer = '-') {
	if ($toLower == true) {
		$url = strtolower($url);
	}

	// International umlauts
	$url = str_replace (array('á', 'à', 'â', 'Á', 'À', 'Â'),			'a', $url);
	$url = str_replace (array('ç', 'Ç'), 								'c', $url);
	$url = str_replace (array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ë', 'Ê'),	'e', $url);
	$url = str_replace (array('í', 'ì', 'î', 'ï', 'Í', 'Ì', 'Î', 'Ï'),	'i', $url);
	$url = str_replace (array('ó', 'ò', 'ô', 'Ó', 'Ò', 'Ô'), 			'o', $url);
	$url = str_replace (array('ú', 'ù', 'û', 'Ú', 'Ù', 'Û'), 			'u', $url);
	// German umlauts
	$url = str_replace (array('ä', 'Ä'), 'ae', $url);
	$url = str_replace (array('ö', 'Ö'), 'oe', $url);
	$url = str_replace (array('ü', 'Ü'), 'ue', $url);
	$url = str_replace (array('ß'), 'ss', $url);
	// Replace some special chars with delimiter
	$url = preg_replace('/[\+\s\r\n\t]+/', $spacer, $url);
	// Replace multiple delimiter chars with only one char
	$url = preg_replace('/['.preg_quote($spacer, '/').']+/', $spacer, $url);
	// Remove html and other special chars
	$url = preg_replace(array('/<[^>]*>/', '/[^a-z0-9\-\._'.preg_quote($spacer, '/').']/i'), '', $url);

	return $url;
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
		trigger_error('Hacking attemp (Path: Protocol)', E_USER_ERROR);
	}
	if (strpos($sd, $dr) === FALSE && file_exists($sd)) {
		trigger_error('Hacking attemp (Path: Not in Document_Root)', E_USER_ERROR);
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
	if (preg_match("~^".URL_REGEXP."$~i", $hp)) {
		return true;
	}
	else {
		return false;
	}
}

function check_mail($email, $simple = false) {
	global $config;
	if(preg_match("~^".EMAIL_REGEXP."$~i", $email)) {
	 	list(, $domain) = explode('@', $email);
	 	$domain = strtolower($domain);
		// Check MX record.
	 	// The idea for this is from UseBB/phpBB
	 	if ($config['local_mode'] == 0 && $config['email_check_mx'] == 1 && !$simple) {
	 		if (checkmx_idna($domain) === false) {
	 			return false;
	 		}
	 	}
		if ($config['sessionmails'] == 1 && !$simple) {
			// get the known domains in lower case
			$sessionmails = file('data/sessionmails.php');
			$sessionmails = array_map("trim", $sessionmails);
			$sessionmails = array_map("strtolower", $sessionmails);
			// compare the data and return the result
			if (in_array($domain, $sessionmails)) {
				return false;
			}
		}
		return true;
	}
	else {
		return false;
	}
}

function benchmarktime() {
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}

function strxlen($string) {
	$string = preg_replace('~&(#[0-9]+|#x[0-9a-f]+|[a-z]{1}[0-9a-z]+);~i', '-', $string);
	return strlen($string);
}

function subxstr($str, $start, $length = null) {
	if ($length === 0) {
		return ""; //stop wasting our time ;)
	}

	//check if we can simply use the built-in functions
	if (strpos($str, '&') === false) { //No entities. Use built-in functions
		if ($length === null) {
			return substr($str, $start);
		}
		else {
			return substr($str, $start, $length);
		}
	}

	// create our array of characters and html entities
	$chars = preg_split('/(&(#[0-9]+|#x[0-9a-f]+|[a-z]{1}[0-9a-z]+);)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
	$html_length = count($chars);

	// check if we can predict the return value and save some processing time
	if ($html_length === 0 || $start >= $html_length || (isset($length) && $length <= -$html_length)) {
	 	return "";
	 }

	//calculate start position
	if ($start >= 0) {
		$real_start = $chars[$start][1];
	}
	else { //start'th character from the end of string
		$start = max($start,-$html_length);
		$real_start = $chars[$html_length+$start][1];
	}

	if (!isset($length)) { // no $length argument passed, return all remaining characters
		return substr($str, $real_start);
	}
	else if ($length > 0) { // copy $length chars
		if ($start+$length >= $html_length) { // return all remaining characters
			return substr($str, $real_start);
		}
		else { //return $length characters
			return substr($str, $real_start, $chars[max($start,0)+$length][1] - $real_start);
		}
	}
	else { //negative $length. Omit $length characters from end
		return substr($str, $real_start, $chars[$html_length+$length][1] - $real_start);
	}

}

function random_word($laenge=8) {
	$newpass = "";
	$string="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+-_?!.";

	for ($i=1; $i <= $laenge; $i++) {
		$newpass .= substr($string, mt_rand(0,strlen($string)-1), 1);
	}

	return $newpass;
}

function leading_zero($int,$length=2) {
	return sprintf("%0{$length}d", $int);
}

function times ($time = false, $timezone = false) {
	global $my, $config;
	$stime = $time == false ? time() : $time;
	if ($timezone == false) {
		if (isset($my->timezone) && $my->timezone !== null && $my->timezone !== '') {
			global $my;
			$timezone = $my->timezone;
		}
		else {
			global $config;
			$timezone = $config['timezone'];
		}
	}
	$timezone = intval($timezone);

	// gmdate('I') can't detect DST so use date('I') instead
	$retime = $stime + 3600*($timezone + date("I"));

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

define('SPEC_RFC822' , 'rfc822');
define('SPEC_ISO8601', 'iso8601');
define('SPEC_UNIX'   , 'unix');

/**
 * Returns a date formatted in a standardized format.
 *
 * Possible formats:
 * - rfc822 / SPEC_RFC822
 * - iso8601 / SPEC_ISO8601
 * - unix / SPEC_UNIX (default)
 *
 * @param 	string 	Format for date
 * @param 	int 	Timestamp in GMT
 * @return 	mixed
 */
function dateSpec($format, $timestamp = null) {
	global $my;
	if ($timestamp == null) {
		$timestamp = time();
	}
	if (is_numeric($timestamp) == false) {
		trigger_error('dateSpec(): Second argument has to be an integer or null.', E_USER_NOTICE);
	}
	$timestamp = times($timestamp);
	$tz = array();
	$tz[0] = $my->timezone < 0 ? '' : '+';
	$tz[1] = sprintf("%02d", $my->timezone);
	$tz[2] = sprintf("%02d", substr($my->timezone*100, -2)*0.6);

	switch($format) {
		case SPEC_ISO8601:
		   	return (string) gmdate('Y-m-d\TH:i:s', $timestamp).$tz[0].$tz[1].':'.$tz[2];
		case SPEC_RFC822:
			return (string) gmdate("D, d M Y H:i:s ", $timestamp).implode('', $tz);
		default:
			return (int) $timestamp;
	}
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

function UpdateBoardStats($board) {
	global $db, $scache;
	$result = $db->query("SELECT COUNT(*) FROM {$db->pre}replies WHERE board='{$board}'");
	$count = $db->fetch_num ($result);

	$result = $db->query("SELECT COUNT(*) FROM {$db->pre}topics WHERE board='{$board}'");
	$count2 = $db->fetch_num($result);

	$replies = $count[0]-$count2[0];
	$topics = $count2[0];

	$result = $db->query("SELECT id FROM {$db->pre}topics WHERE board = '{$board}' ORDER BY last DESC LIMIT 1");
	$last = $db->fetch_num($result);
	if (empty($last[0])) {
		$last[0] = 0;
	}
	$db->query("
	UPDATE {$db->pre}forums SET topics = '{$topics}', replies = '{$replies}', last_topic = '{$last[0]}'
	WHERE id = '{$board}'
	");
	$delobj = $scache->load('cat_bid');
	$delobj->delete();
}

function UpdateBoardLastStats($board) {
	global $db;
	$result = $db->query("SELECT id FROM {$db->pre}topics WHERE board = '{$board}' ORDER BY last DESC LIMIT 1");
	$last = $db->fetch_num($result);
	if (empty($last[0])) {
		$last[0] = 0;
	}
	$db->query("UPDATE {$db->pre}forums SET last_topic = '{$last[0]}' WHERE id = '{$board}'");
}

function UpdateMemberStats($id) {
	global $db;
	$result = $db->query("SELECT COUNT(*) FROM {$db->pre}replies WHERE name = '{$id}' AND guest = '0'");
	$count = $db->fetch_num ($result);
	$db->query("UPDATE {$db->pre}user SET posts = '{$count[0]}' WHERE id = '{$id}'");
	return $count[0];
}

function check_ip($ip, $allow_private = false) {

   	$private_ips = array("/^0\..+$/", "/^127\.0\.0\..+$/", "/^192\.168\..+$/", "/^172\.16\..+$/", "/^10..+$/", "/^224..+$/", "/^240..+$/");

	$ok = true;
	if (!preg_match("/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/", $ip)) {
		$ok = false;
	}
	if ($allow_private == false) {
		foreach ($private_ips as $pip) {
			if (preg_match($pip, $ip)) {
				$ok = false;
			}
		}
	}
	return $ok;
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

	$ips = array_unique($ips);

	foreach ($ips as $ip) {
		$found = !(check_ip($ip));
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
			if ($forums[$id]['opt'] == 'pw' && (!isset($my->pwfaccess[$id]) || $forums[$id]['optvalue'] != $my->pwfaccess[$id])) {
				return false;
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

function selectTZ($user, $compare) {
	global $config;
	if ($user === null) {
		$user = $config['timezone'];
	}
	$user = (int) str_replace('+', '', $user);
	if ($user == $compare) {
		echo ' selected="selected"';
	}
	else {
		echo '';
	}
}

/*
Sends a plain text e-mail in UTF-8.

All parameters should be in html entity mode, not in utf-8!

Params:
	(array/string)	$to		= Recipient
					$to[]['name'] = Name of Recipient (opt)
					$to[]['mail'] = Mail of Recipient
	(array/string)	$from		= Sender (opt)
					$from['name'] = Name of Sender (opt)
					$from['mail'] = Mail of Sender (opt)
	(string)		$topic 		= Title
	(string)		$comment 	= Content
*/

function xmail ($to, $from = array(), $topic, $comment) {
	global $config, $gpc;

	require_once("classes/mail/class.phpmailer.php");

	$mail = new PHPMailer();
	$mail->CharSet = 'UTF-8';

	// Added Check_mail for better security
	// Now it is not possible to add various headers to the mail
	if (!isset($from['mail']) || !check_mail($from['mail'])) {
		$mail->From = $config['forenmail'];
	}
	else {
		$mail->From = $gpc->plain_str($from['mail']);
	}
	if (!isset($from['name'])) {
		$mail->FromName = $gpc->plain_str($config['fname']);
	}
	else {
		$mail->FromName = $gpc->plain_str($from['name']);
	}
	if ($config['smtp'] == 1) {
		$mail->Mailer   = "smtp";
		$mail->IsSMTP();
		$mail->Host	 = $config['smtp_host'];
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

	$mail->Subject = $gpc->plain_str($topic);
	if (!is_array($to)) {
		$to = array('0' => array('mail' => $to));
	}
	$i = 0;
	foreach ($to as $email) {
		$mail->IsHTML(false);
		$mail->Body = $gpc->plain_str($comment);

		if (isset($email['name'])) {
			$mail->AddAddress($gpc->plain_str($email['mail']), $gpc->plain_str($email['name']));
		}
		else {
			$mail->AddAddress($gpc->plain_str($email['mail']));
		}

		if ($config['local_mode'] == 0) {
			if ($mail->Send()) {
				$i++;
			}
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

	if ($expire != null) {
		$expire = time() + $expire;
	}
	else {
		$expire = 0;
	}
	setcookie($name, $value, $expire, null, null, ini_isSecureHttp());
}
?>