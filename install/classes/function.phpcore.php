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

// Small hack for the new php 5.3 timezone warnings
date_default_timezone_set(@date_default_timezone_get());

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
function viscacha_header($header, $replace = true, $code = 0) {
	$header = str_replace("\n", '', $header);
	$header = str_replace("\r", '', $header);
	$header = str_replace("\0", '', $header);
	if ($code > 0) {
		header($header, $replace, $code);
	}
	else {
		header($header, $replace);
	}
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

		// Send status code
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
		return true;
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
 * Replace array_intersect_key()
 *
 * @category	PHP
 * @package		PHP_Compat
 * @license		LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright	2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link		http://php.net/function.array_intersect_key
 * @author		Tom Buskens <ortega@php.net>
 * @version		$Revision: 1.9 $
 * @since		PHP 5.1.0
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
?>