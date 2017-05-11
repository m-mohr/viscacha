<?php
/*
	Viscacha - An advanced bulletin board solution to manage your content easily
	Copyright (C) 2004-2017, Lutana
	http://www.viscacha.org

	Authors: Matthias Mohr et al.
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

error_reporting($config['debug'] ? E_ALL : E_ERROR);

require 'vendor/autoload.php';

use Viscacha\Util\Debug;

class_alias('Viscacha\Util\Str', 'Str');

Debug::init($config['debug'], $config['error_log']);

// Small hack for the new php 5.3 timezone warnings
date_default_timezone_set(@date_default_timezone_get());

/* Some useful PHP functions */

define('URL_REGEXP', 'https?://[\p{L}\p{Nd}\-\.@]+(?:\.\p{L}{2,7})?(?::\d+)?/?(?:[\p{L}\p{Nd}\-\.:_\?\,;/\\\+&%\$#\=\~\[\]]*[\p{L}\p{Nd}\-\.:_\?\,;/\\\+&%\$#\=\~])?');
define('EMAIL_REGEXP', "[\p{L}\p{Nd}!#\$%&'\*\+/=\?\^_\{\|\}\~\-]+(?:\.[\p{L}\p{Nd}!#$%&'\*\+/=\?\^_\{\|\}\~\-]+)*@(?:[\p{L}\p{Nd}](?:[\p{L}\p{Nd}\-]*[\p{L}\p{Nd}])?\.)+[\p{L}\p{Nd}](?:[\p{L}\p{Nd}\-]*[\p{L}\p{Nd}])?");

function idna($host) {
	$idna = new \Mso\IdnaConvert\IdnaConvert();
	return $idna->encode($host);
}

function is_id ($x) {
	return (is_numeric($x) && $x >= 1 ? intval($x) == $x : false);
}

function is_email($email) {
	return (bool) preg_match("~^".EMAIL_REGEXP."$~iu", $email);
}

function is_url($url) {
	return (bool) preg_match("~^".URL_REGEXP."$~iu", $url);
}

function is_hash($string, $len = 32) {
	return (bool) preg_match("/^[a-f\d]{{$len}}$/iu", $string);
}

// Generates an alpha-numeric 32 char unique ID
function generate_uid() {
	if (function_exists('random_bytes')) {
		return bin2hex(random_bytes(16));
	}
	else if (function_exists('openssl_random_pseudo_bytes')) {
		return bin2hex(openssl_random_pseudo_bytes(16));
	}
	else {
		return md5(uniqid(mt_rand(), true));
	}
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
 * orders a multidimentional array on the base of a label-key
 *
 * @param $arr, the array to be ordered
 * @param $l the "label" identifing the field
 * @param $f the ordering function to be used, \Str::compareNatural() by default
 * @return  TRUE on success, FALSE on failure.
 */
function array_columnsort(&$arr, $l , $f = array('Str', 'compareNatural')) {
	return uasort($arr, function($a, $b) {
		return call_user_func($f, $a[$l], $b[$l]);
	});
}

function array_only(array $array, array $keys) {
	return array_intersect_key($array, array_keys($keys));
}

function is_array_empty(array $array) {
	if (empty($array)) {
		return true;
	}
	$array = array_unique($array);
	if (count($array) == 1) {
		$current = reset($array);
		return empty($current);
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

function array_trim_empty(array $array) {
	foreach($array as $key => $value) {
		$value = trim($value);
		if (empty($value)) {
			unset($array[$key]);
		}
	}
	return $array;
}

/**
 * Sends a http status code to the client.
 *
 * Aditional header data can be send depending on the code number given in the first parameter.
 * Only some error codes support this and each error code has its own additional header data.
 * Supported additional headers:
 * - 201/202/301/302/307 => Location: Specify a new location (url)
 * - 401 => WWW-Authenticate: Specify a page name
 * - 503 => Retry-after: Specify the time the page is unavailable
 * After location headers the program exits.
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
				case '201':
				case '202':
				case '301':
				case '302':
				case '307':
					viscacha_header("Location: {$additional}");
					exit;
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
	$pos = \Str::lastIndexOf($source, '/');
	if ($pos === false) {
		$pos = \Str::lastIndexOf($source, '\\');
	}
	if ($pos > 0) {
		$dest = \Str::substr($source, 0, $pos+1);
	}
	else {
		$dest = '';
	}
	return $dest;
}