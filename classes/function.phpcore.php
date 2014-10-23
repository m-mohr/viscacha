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

/* Viscacha related ini settings */
@set_magic_quotes_runtime(0);
@ini_set('magic_quotes_gpc',0);

/* Fixed php functions */

define('ENCODING_LIST', 'ISO-8859-1, ISO-8859-15, UTF-8, ASCII, cp1252, cp1251, GB2312, SJIS, KOI8-R');
// IDNA Convert Class
include_once (dirname(__FILE__).'/class.idna.php');

function convert_host_to_idna($host) {
	$idna = new idna_convert();
	if (viscacha_function_exists('mb_convert_encoding')) {
		$host = mb_convert_encoding($host, 'UTF-8', ENCODING_LIST);
	}
	else {
		$host = utf8_encode($host);
	}
	$host = $idna->encode($host);
	return $host;
}

function fsockopen_idna($host, $port, $timeout) {
	$host = convert_host_to_idna($host);
	$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
	return array($fp, $errno, $errstr, $host);
}

function is_id ($x) {
	return (is_numeric($x) && $x >= 1 ? intval($x) == $x : false);
}

// Fixes problems with suhosin blacklist
function viscacha_function_exists($func) {
	if (extension_loaded('suhosin')) {
		$suhosin = @ini_get("suhosin.executor.func.blacklist");
		if (empty($suhosin) == false) {
			$suhosin = explode(',', $suhosin);
			$suhosin = array_map('trim', $suhosin);
			$suhosin = array_map('strtolower', $suhosin);
			return (function_exists($func) == true && array_search($func, $suhosin) === false);
		}
	}
	return function_exists($func);
}

// Variable headers are not secure in php (HTTP response Splitting).
// Better use viscacha_header() instead of header().
// viscacha_header() removes \r, \n, \0
function viscacha_header($header) {
	$header = str_replace("\n", '', $header);
	$header = str_replace("\r", '', $header);
	$header = str_replace("\0", '', $header);
	header($header);
}

/**
 * Sends a http status code to the client.
 *
 * Aditional header data can be send depending on the code number given in the first parameter.
 * Only some error codes support this and each error code has its own additional header data.
 * Supported additional headers:
 * - 301/302/307 => Location: Specify a new location (url)
 * - 401 => WWW-Authenticate: Specify a page name
 * - 503 => Retry-after: Specify the time the page is unavailable
 *
 * @param int $code Error Code Number
 * @param mixed $additional Additional Header data (depends in error code number)
 * @return boolean
 */
function sendStatusCode($code, $additional = null) {
	$status = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Moved Temporarily', // Found
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Authorization Required', // Unauthorized
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Time-Out',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Large',
		415 => 'Unsupported Media Type',
		416 => 'Request Rang Not Satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Temporarily Unavailable', // Service Unavailable
		504 => 'Gateway Time-Out',
		505 => 'HTTP Version not supported'
	);

	if (isset($status[$code])) {
		viscacha_header("HTTP 1.1 {$code} {$status[$code]}");
		viscacha_header("Status: {$code} {$status[$code]}");

		// Additional headers
		if ($additional != null) {
			switch ($code) {
				case '301':
				case '302':
				case '307':
					viscacha_header("Location: {$additional}");
				break;
				case '401':
					viscacha_header('WWW-Authenticate: Basic Realm="'.$additional.'"');
				break;
				case '503':
					viscacha_header("Retry-After: {$additional}");
				break;
			}
		}

		return true;
	}
	else {
		return false;
	}
}

/* Some other important functions */

// Function to determine which OS is used
function isWindows() {
	if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
		return true;
	}
	elseif (isset($_SERVER['OS']) && strpos(strtolower($_SERVER['OS']), 'Windows') !== false) {
		return true;
	}
	elseif (viscacha_function_exists('php_uname') && stristr(@php_uname(), 'windows')) {

	}
	else {
		return false;
	}
}
function isMac() {
	$mac = strtoupper(substr(PHP_OS, 0, 3));
	return ($mac == 'MAC' || $mac == 'DAR');
}

/**
 * getDocRoot fixes a problem with Windows where PHP does not have $_SERVER['DOCUMENT_ROOT']
 * built in. getDocRoot returns what $_SERVER['DOCUMENT_ROOT'] should have. It should work on
 * other builds, such as Unix, but is best used with Windows. There are two return cases for
 * Windows, one is the document root for the server's web files (c:/inetpub/wwwroot), the
 * other version is the first folder beyond that point (if documents are stored in user folders).
 *
 * @author Allan Bogh - Buckwheat469@hotmail.com
 * @version 1.0 - based on research on www.helicron.net/php
 *
 * @param $folderFix - This optional parameter tells the function to include the first folder in
 *						the return (c:/inetpub/wwwroot/userfolder instead of c:/inetpub/wwwroot).
 *						Set to true if folder should be returned.
 * @return The document root string.
 **/
function getDocumentRoot(){
	//sets up the localpath
	$localpath = getenv("SCRIPT_NAME");
 	$localpath = substr($localpath, strpos($localpath, '/', iif(strlen($localpath) >= 1, 1, 0)), strlen($localpath));

	//realpath sometimes doesn't work, but gets the full path of the file
	$absolutepath = realpath($localpath);
	if((!isset($absolutepath) || $absolutepath=="") && isset($_SERVER['ORIG_PATH_TRANSLATED'])){
		$absolutepath = $_SERVER['ORIG_PATH_TRANSLATED'];
	}

	//checks if Windows is being used to replace the \ to /
	if(isWindows() == true){
		$absolutepath = str_replace("\\","/",$absolutepath);
	}

	//prepares the document root string
	$docroot = substr($absolutepath,0,strpos($absolutepath,$localpath));
	return $docroot;
}

// if function for templates
function iif($if, $true, $false = '') {
	return ($if ? $true : $false);
}

// extracts the top directory
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

/* Error constants from PHP-Compat */
if (!defined('E_STRICT')) {
	define('E_STRICT', 2048);
}
if (!defined('E_RECOVERABLE_ERROR')) {
	define('E_RECOVERABLE_ERROR', 4096);
}
if (!defined('E_DEPRECATED')) {
	define('E_DEPRECATED', 8192);
}
if (!defined('E_USER_DEPRECATED')) {
	define('E_USER_DEPRECATED', 16384);
}
/* EOL constant from PHP-Compat */
if (!defined('PHP_EOL')) {
	if (isWindows() == true) {
		define('PHP_EOL', "\r\n");
	}
	elseif (isMac() == true) {
		define('PHP_EOL', "\r");
	}
	else {
		define('PHP_EOL', "\n");
	}
}
/* File constants from PHP-Compat */
if (!defined('FILE_USE_INCLUDE_PATH')) {
	define('FILE_USE_INCLUDE_PATH', 1);
}
if (!defined('FILE_IGNORE_NEW_LINES')) {
	define('FILE_IGNORE_NEW_LINES', 2);
}
if (!defined('LOCK_EX')) {
	define('LOCK_EX', 2);
}
if (!defined('FILE_SKIP_EMPTY_LINES')) {
	define('FILE_SKIP_EMPTY_LINES', 4);
}
if (!defined('FILE_APPEND')) {
	define('FILE_APPEND', 8);
}
if (!defined('FILE_NO_DEFAULT_CONTEXT')) {
	define('FILE_NO_DEFAULT_CONTEXT', 16);
}

$imagetype_extension = array('gif', 'jpg', 'png', 'swf', 'psd', 'bmp', 'tiff', 'jpc', 'jp2', 'jpf', 'jb2', 'swc', 'aiff', 'wbmp', 'xbm');

if (!defined('IMAGETYPE_GIF')) {
	define('IMAGETYPE_GIF', 1);
}

if (!defined('IMAGETYPE_JPEG')) {
	define('IMAGETYPE_JPEG', 2);
}

if (!defined('IMAGETYPE_PNG')) {
	define('IMAGETYPE_PNG', 3);
}

if (!defined('IMAGETYPE_SWF')) {
	define('IMAGETYPE_SWF', 4);
}

if (!defined('IMAGETYPE_PSD')) {
	define('IMAGETYPE_PSD', 5);
}

if (!defined('IMAGETYPE_BMP')) {
	define('IMAGETYPE_BMP', 6);
}

if (!defined('IMAGETYPE_TIFF_II')) {
	define('IMAGETYPE_TIFF_II', 7);
}

if (!defined('IMAGETYPE_TIFF_MM')) {
	define('IMAGETYPE_TIFF_MM', 8);
}

if (!defined('IMAGETYPE_JPC')) {
	define('IMAGETYPE_JPC', 9);
}

if (!defined('IMAGETYPE_JP2')) {
	define('IMAGETYPE_JP2', 10);
}

if (!defined('IMAGETYPE_JPX')) {
	define('IMAGETYPE_JPX', 11);
}

if (!defined('IMAGETYPE_JB2')) {
	define('IMAGETYPE_JB2', 12);
}

if (!defined('IMAGETYPE_SWC')) {
	define('IMAGETYPE_SWC', 13);
}

if (!defined('IMAGETYPE_IFF')) {
	define('IMAGETYPE_IFF', 14);
}

if (!defined('IMAGETYPE_WBMP')) {
	define('IMAGETYPE_WBMP', 15);
}

if (!defined('IMAGETYPE_XBM')) {
	define('IMAGETYPE_XBM', 16);
}

/* Missing functions */

/**
 * Replace image_type_to_extension()
 *
 * Function is not documented yet. It is maybe different from the original function!
 *
 * @link		http://php.net/function.image_type_to_extension
 * @author		Matthias Mohr
 * @require		PHP 4.0.0 (trigger_error)
 */
if(!viscacha_function_exists('image_type_to_extension')) {
	function image_type_to_extension($imagetype, $include_dot = true) {
		if(empty($imagetype)) {
			return false;
		}
		if (!is_bool($include_dot)) {
			trigger_error('Argument 2 has to be a boolean!', E_WARNING);
			return false;
		}
		if ($include_dot == true) {
			$dor = '.';
		}
		else {
			$dot = '';
		}
		switch($imagetype) {
			case IMAGETYPE_GIF		: return $dot.'gif';
			case IMAGETYPE_JPEG		: return $dot.'jpg';
			case IMAGETYPE_PNG		: return $dot.'png';
			case IMAGETYPE_SWF		: return $dot.'swf';
			case IMAGETYPE_PSD		: return $dot.'psd';
			case IMAGETYPE_BMP		: return $dot.'bmp';
			case IMAGETYPE_TIFF_II	: return $dot.'tiff';
			case IMAGETYPE_TIFF_MM	: return $dot.'tiff';
			case IMAGETYPE_JPC		: return $dot.'jpc';
			case IMAGETYPE_JP2		: return $dot.'jp2';
			case IMAGETYPE_JPX		: return $dot.'jpf';
			case IMAGETYPE_JB2		: return $dot.'jb2';
			case IMAGETYPE_SWC		: return $dot.'swc';
			case IMAGETYPE_IFF		: return $dot.'aiff';
			case IMAGETYPE_WBMP		: return $dot.'wbmp';
			case IMAGETYPE_XBM		: return $dot.'xbm';
			default					: return false;
		}
	}
}

/**
 * Replace htmlspecialchars_decode()
 *
 * @link		http://php.net/function.htmlspecialchars_decode
 * @author		Matthias Mohr
 * @since		PHP 5.1.0
 * @require		PHP 4.0.0 (trigger_error)
 */
if (!viscacha_function_exists('htmlspecialchars_decode')) {
	function htmlspecialchars_decode($str, $quote_style = ENT_COMPAT) {
		return strtr($str, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
	}
}

/**
 * Replace file_put_contents()
 *
 * @category	PHP
 * @package		PHP_Compat
 * @link		http://php.net/function.file_put_contents
 * @author		Aidan Lister <aidan@php.net>
 * @version		$Revision: 1.25 $
 * @internal	resource_context is not supported
 * @since		PHP 5
 * @require		PHP 4.0.0 ()
 */
if (!viscacha_function_exists('file_put_contents')) {
	function file_put_contents($filename, $content, $flags = null, $resource_context = null) {
		// If $content is an array, convert it to a string
		if (is_array($content)) {
			$content = implode('', $content);
		}

		// If we don't have a string, throw an error
		if (!is_scalar($content)) {
			trigger_error('file_put_contents() The 2nd parameter should be either a string or an array',
				E_USER_WARNING);
			return false;
		}

		// Get the length of data to write
		$length = strlen($content);

		// Check what mode we are using
		$mode = ($flags & FILE_APPEND) ?
					'a' :
					'wb';

		// Check if we're using the include path
		$use_inc_path = ($flags & FILE_USE_INCLUDE_PATH) ?
					true :
					false;

		// Open the file for writing
		if (($fh = @fopen($filename, $mode, $use_inc_path)) === false) {
			// trigger_error('file_put_contents() failed to open stream: Permission denied', E_USER_WARNING);
			return false;
		}

		// Attempt to get an exclusive lock
		$use_lock = ($flags & LOCK_EX) ? true : false ;
		if ($use_lock === true) {
			if (!flock($fh, LOCK_EX)) {
				return false;
			}
		}

		// Write to the file
		$bytes = 0;
		if (($bytes = @fwrite($fh, $content)) === false) {
			$errormsg = sprintf('file_put_contents() Failed to write %d bytes to %s',
							$length,
							$filename);
			trigger_error($errormsg, E_USER_WARNING);
			return false;
		}

		// Close the handle
		@fclose($fh);

		// Check all the data was written
		if ($bytes != $length) {
			$errormsg = sprintf('file_put_contents() Only %d of %d bytes written, possibly out of free disk space.',
							$bytes,
							$length);
			trigger_error($errormsg, E_USER_WARNING);
			return false;
		}

		// Return length
		return $bytes;
	}
}

/**
 * Replace stripos()
 *
 * @category	PHP
 * @package		PHP_Compat
 * @link		http://php.net/function.stripos
 * @author		Aidan Lister <aidan@php.net>
 * @version		$Revision: 1.13 $
 * @since		PHP 5
 * @require		PHP 4.0.0 ()
 */
if (!viscacha_function_exists('stripos')) {
	function stripos($haystack, $needle, $offset = null) {
		if (!is_scalar($haystack)) {
			trigger_error('stripos() expects parameter 1 to be string, ' .
				gettype($haystack) . ' given', E_USER_WARNING);
			return false;
		}

		if (!is_scalar($needle)) {
			trigger_error('stripos() needle is not a string or an integer.', E_USER_WARNING);
			return false;
		}

		if (!is_int($offset) && !is_bool($offset) && !is_null($offset)) {
			trigger_error('stripos() expects parameter 3 to be long, ' .
				gettype($offset) . ' given', E_USER_WARNING);
			return false;
		}

		// Manipulate the string if there is an offset
		$fix = 0;
		if (!is_null($offset)) {
			if ($offset > 0) {
				$haystack = substr($haystack, $offset, strlen($haystack) - $offset);
				$fix = $offset;
			}
		}

		$segments = explode(strtolower($needle), strtolower($haystack), 2);

		// Check there was a match
		if (count($segments) === 1) {
			return false;
		}

		$position = strlen($segments[0]) + $fix;
		return $position;
	}
}

/**
 * Replace str_ireplace()
 *
 * This function does not support the $count argument because
 * it cannot be optional in PHP 4 and the performance cost is
 * too great when a count is not necessary.
 *
 * @category	PHP
 * @package		PHP_Compat
 * @license		LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright	2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link		http://php.net/function.str_ireplace
 * @author		Aidan Lister <aidan@php.net>
 * @author		Arpad Ray <arpad@php.net>
 * @version		$Revision: 1.24 $
 * @since		PHP 5
 * @require		PHP 4.0.0 (trigger_error)
 */
if (!viscacha_function_exists('str_ireplace')) {
	function str_ireplace($search, $replace, $subject) {
		// Sanity check
		if (is_string($search) && is_array($replace)) {
			trigger_error('Array to string conversion', E_USER_NOTICE);
			$replace = (string) $replace;
		}

		// If search isn't an array, make it one
		$search = (array) $search;
		$length_search = count($search);

		// build the replace array
		$replace = is_array($replace)
		? array_pad($replace, $length_search, '')
		: array_pad(array(), $length_search, $replace);

		// If subject is not an array, make it one
		$was_string = false;
		if (is_string($subject)) {
			$was_string = true;
			$subject = array ($subject);
		}

		// Prepare the search array
		foreach ($search as $search_key => $search_value) {
			$search[$search_key] = '/' . preg_quote($search_value, '/') . '/i';
		}

		// Prepare the replace array (escape backreferences)
		$replace = str_replace(array('\\', '$'), array('\\\\', '\$'), $replace);

		$result = preg_replace($search, $replace, $subject);
		return $was_string ? $result[0] : $result;
	}
}

/**
 * Replace str_split()
 *
 * @category	PHP
 * @package		PHP_Compat
 * @link		http://php.net/function.str_split
 * @author		Aidan Lister <aidan@php.net>
 * @version		$Revision: 1.15 $
 * @since		PHP 5
 * @require		PHP 4.0.0 ()
 */
if (!viscacha_function_exists('str_split')) {
	function str_split($string, $split_length = 1) {
		if (!is_scalar($split_length)) {
			trigger_error('str_split() expects parameter 2 to be long, ' .
				gettype($split_length) . ' given', E_USER_WARNING);
			return false;
		}

		$split_length = (int) $split_length;
		if ($split_length < 1) {
			trigger_error('str_split() The length of each segment must be greater than zero', E_USER_WARNING);
			return false;
		}

		// Select split method
		if ($split_length < 65536) {
			// Faster, but only works for less than 2^16
			preg_match_all('/.{1,' . $split_length . '}/s', $string, $matches);
			return $matches[0];
		} else {
			// Required due to preg limitations
			$arr = array();
			$idx = 0;
			$pos = 0;
			$len = strlen($string);

			while ($len > 0) {
				$blk = ($len < $split_length) ? $len : $split_length;
				$arr[$idx++] = substr($string, $pos, $blk);
				$pos += $blk;
				$len -= $blk;
			}

			return $arr;
		}
	}
}

/**
 * Replace array_intersect_key()
 *
 * @category	PHP
 * @package		PHP_Compat
 * @license		LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright	2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link		http://php.net/function.array_intersect_key
 * @author		Tom Buskens <ortega@php.net>
 * @version		$Revision: 1.9 $
 * @since		PHP 5.0.2
 * @require		PHP 4.0.0 (trigger_error)
 */
if (!viscacha_function_exists('array_intersect_key')) {
	function array_intersect_key() {
		$args = func_get_args();
		$array_count = count($args);
		if ($array_count < 2) {
			trigger_error('Wrong parameter count for array_intersect_key()', E_USER_WARNING);
			return;
		}

		// Check arrays
		for ($i = $array_count; $i--;) {
			if (!is_array($args[$i])) {
				trigger_error('array_intersect_key() Argument #' .
					($i + 1) . ' is not an array', E_USER_WARNING);
				return;
			}
		}

		// Intersect keys
		$arg_keys = array_map('array_keys', $args);
		$result_keys = call_user_func_array('array_intersect', $arg_keys);

		// Build return array
		$result = array();
		foreach($result_keys as $key) {
			$result[$key] = $args[0][$key];
		}
		return $result;
	}
}

/**
 * Replace array_combine()
 *
 * @category	PHP
 * @package		PHP_Compat
 * @link		http://php.net/function.array_combine
 * @author		Aidan Lister <aidan@php.net>
 * @version		$Revision: 1.21 $
 * @since		PHP 5
 * @require		PHP 4.0.0 ()
 */
if (!viscacha_function_exists('array_combine')) {
	function array_combine($keys, $values) {
		if (!is_array($keys)) {
			trigger_error('array_combine() expects parameter 1 to be array, ' .
				gettype($keys) . ' given', E_USER_WARNING);
			return;
		}

		if (!is_array($values)) {
			trigger_error('array_combine() expects parameter 2 to be array, ' .
				gettype($values) . ' given', E_USER_WARNING);
			return;
		}

		$key_count = count($keys);
		$value_count = count($values);
		if ($key_count !== $value_count) {
			trigger_error('array_combine() Both parameters should have equal number of elements', E_USER_WARNING);
			return false;
		}

		if ($key_count === 0 || $value_count === 0) {
			trigger_error('array_combine() Both parameters should have number of elements at least 0', E_USER_WARNING);
			return false;
		}

		$keys	= array_values($keys);
		$values	= array_values($values);

		$combined = array();
		for ($i = 0; $i < $key_count; $i++) {
			$combined[$keys[$i]] = $values[$i];
		}

		return $combined;
	}
}

/**
 * Replace function http_build_query()
 *
 * @category	PHP
 * @package		PHP_Compat
 * @license		LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright	2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link		http://php.net/function.http-build-query
 * @author		Stephan Schmidt <schst@php.net>
 * @author		Aidan Lister <aidan@php.net>
 * @version		$Revision: 1.22 $
 * @since		PHP 5
 * @require		PHP 4.0.0 (trigger_error)
 */
if (!viscacha_function_exists('http_build_query')) {
	function php_compat_http_build_query($formdata, $numeric_prefix = null) {
		// If $formdata is an object, convert it to an array
		if (is_object($formdata)) {
			$formdata = get_object_vars($formdata);
		}

		// Check we have an array to work with
		if (!is_array($formdata)) {
			trigger_error('http_build_query() Parameter 1 expected to be Array or Object. Incorrect value given.',
				E_USER_WARNING);
			return false;
		}

		// If the array is empty, return null
		if (empty($formdata)) {
			return;
		}

		// Argument seperator
		$separator = ini_get('arg_separator.output');
		if (strlen($separator) == 0) {
			$separator = '&';
		}

		// Start building the query
		$tmp = array ();
		foreach ($formdata as $key => $val) {
			if (is_null($val)) {
				continue;
			}

			if (is_integer($key) && $numeric_prefix != null) {
				$key = $numeric_prefix . $key;
			}

			if (is_scalar($val)) {
				array_push($tmp, urlencode($key) . '=' . urlencode($val));
				continue;
			}

			// If the value is an array, recursively parse it
			if (is_array($val) || is_object($val)) {
				array_push($tmp, http_build_query_helper($val, urlencode($key)));
				continue;
			}

			// The value is a resource
			return null;
		}

		return implode($separator, $tmp);
	}


	// Helper function
	function http_build_query_helper($array, $name) {
		$tmp = array ();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				array_push($tmp, http_build_query_helper($value, sprintf('%s[%s]', $name, $key)));
			} elseif (is_scalar($value)) {
				array_push($tmp, sprintf('%s[%s]=%s', $name, urlencode($key), urlencode($value)));
			} elseif (is_object($value)) {
				array_push($tmp, http_build_query_helper(get_object_vars($value), sprintf('%s[%s]', $name, $key)));
			}
		}

		// Argument seperator
		$separator = ini_get('arg_separator.output');
		if (strlen($separator) == 0) {
			$separator = '&';
		}

		return implode($separator, $tmp);
	}
}
?>