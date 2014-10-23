<?php
/*
This is a file with functions that emulate the needed php-functions for backward compatibility.
The following functions are emulated:
mhash, sha1, str_shuffle, array_change_key_case, html_entity_decode, array_chunk, file_put_contents,
file_get_contents, str_ireplace, str_split, version_compare, is_a, http_build_query, array_key_exists
*/


/* Viscacha related */

@set_magic_quotes_runtime(0);
@ini_set('magic_quotes_gpc',0);

if (isset($config['error_reporting']) && $config['error_reporting'] > 0) {
	error_reporting($config['error_reporting']);
}

/* Fixed php functions */


// You should use fixed_dirname instead of dirname
// Written by Manuel Lemos
function fixed_dirname($path) {
	$end=strrpos($path,"/");
	return((gettype($end)=="integer" && $end>1) ? substr($path,0,$end) : "");
}

// Variable headers are not secure in php (HTTP response Splitting). Better use viscacha_header()
// viscacha_header() removes \r, \n, \0
function viscacha_header($header) {
	$header = str_replace("\n", '', $header);
	$header = str_replace("\r", '', $header);
	$header = str_replace("\0", '', $header);
	header($header);
}

// PHP is stupid: No lcfirst()
function lcfirst($p) {
	return strtolower($p{0}).substr($p, 1);
}


/* Missing constants from PHP-Compat */

/**
 * Replace constant DIRECTORY_SEPARATOR
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/reserved.constants.standard
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.1 $
 * @since       PHP 4.0.6
 */
if (!defined('DIRECTORY_SEPARATOR')) {
    define('DIRECTORY_SEPARATOR',
        strtoupper(substr(PHP_OS, 0, 3) == 'WIN') ? '\\' : '/'
    );
}

/**
 * Replace constant E_STRICT
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/ref.errorfunc
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.11 $
 * @since       PHP 5
 */
if (!defined('E_STRICT')) {
    define('E_STRICT', 2048);
}

/**
 * Replace constant PATH_SEPARATOR
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/ref.dir
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.13 $
 * @since       PHP 4.3.0
 */
if (!defined('PATH_SEPARATOR')) {
    define('PATH_SEPARATOR',
        strtoupper(substr(PHP_OS, 0, 3) == 'WIN') ? ';' : ':'
     );
}

/**
 * Replace PHP_EOL constant
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/reserved.constants.core
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.2 $
 * @since       PHP 5.0.2
 */
if (!defined('PHP_EOL')) {
    switch (strtoupper(substr(PHP_OS, 0, 3))) {
        // Windows
        case 'WIN':
            define('PHP_EOL', "\r\n");
            break;

        // Mac
        case 'DAR':
            define('PHP_EOL', "\r");
            break;

        // Unix
        default:
            define('PHP_EOL', "\n");
    }
}

/**
 * Replace upload error constants
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/features.file-upload.errors
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.1 $
 * @since       PHP 4.3.0
 */
if (!defined('UPLOAD_ERR_OK')) {
    define('UPLOAD_ERR_OK', 0);
}

if (!defined('UPLOAD_ERR_INI_SIZE')) {
    define('UPLOAD_ERR_INI_SIZE', 1);
}

if (!defined('UPLOAD_ERR_FORM_SIZE')) {
    define('UPLOAD_ERR_FORM_SIZE', 2);
}

if (!defined('UPLOAD_ERR_PARTIAL')) {
    define('UPLOAD_ERR_PARTIAL', 3);
}

if (!defined('UPLOAD_ERR_NO_FILE')) {
    define('UPLOAD_ERR_NO_FILE', 4);
}

/**
 * Replace filesystem constants
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/ref.filesystem
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.8 $
 * @since       PHP 5
 */
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

// html_entity_decode()
if (!defined('ENT_NOQUOTES')) {
    define('ENT_NOQUOTES', 0);
}

if (!defined('ENT_COMPAT')) {
    define('ENT_COMPAT', 2);
}

// array_change_key_case()
if (!defined('ENT_QUOTES')) {
    define('ENT_QUOTES', 3);
}

if (!defined('CASE_LOWER')) {
    define('CASE_LOWER', 0);
}

if (!defined('CASE_UPPER')) {
    define('CASE_UPPER', 1);
}

// mhash()
if (!defined('MHASH_CRC32')) {
    define('MHASH_CRC32', 0);
}

if (!defined('MHASH_MD5')) {
    define('MHASH_MD5', 1);
}

if (!defined('MHASH_SHA1')) {
    define('MHASH_SHA1', 2);
}

if (!defined('MHASH_HAVAL256')) {
    define('MHASH_HAVAL256', 3);
}

if (!defined('MHASH_RIPEMD160')) {
    define('MHASH_RIPEMD160', 5);
}

if (!defined('MHASH_TIGER')) {
    define('MHASH_TIGER', 7);
}

if (!defined('MHASH_GOST')) {
    define('MHASH_GOST', 8);
}

if (!defined('MHASH_CRC32B')) {
    define('MHASH_CRC32B', 9);
}

if (!defined('MHASH_HAVAL192')) {
    define('MHASH_HAVAL192', 11);
}

if (!defined('MHASH_HAVAL160')) {
    define('MHASH_HAVAL160', 12);
}

if (!defined('MHASH_HAVAL128')) {
    define('MHASH_HAVAL128', 13);
}

if (!defined('MHASH_TIGER128')) {
    define('MHASH_TIGER128', 14);
}

if (!defined('MHASH_TIGER160')) {
    define('MHASH_TIGER160', 15);
}

if (!defined('MHASH_MD4')) {
    define('MHASH_MD4', 16);
}

if (!defined('MHASH_SHA256')) {
    define('MHASH_SHA256', 17);
}

if (!defined('MHASH_ADLER32')) {
    define('MHASH_ADLER32', 18);
}

/* Missing functions from PHP-Compat */

/**
 * Replace mhash()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.mhash
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.1 $
 * @since       PHP 4.1.0
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('mhash')) {
    function mhash($hashtype, $data, $key = '')    
    {
        switch ($hashtype) {
            case MHASH_MD5:
                $key = str_pad((strlen($key) > 64 ? pack("H*", md5($key)) : $key), 64, chr(0x00));
                $k_opad = $key ^ (str_pad('', 64, chr(0x5c)));
                $k_ipad = $key ^ (str_pad('', 64, chr(0x36)));
                return pack("H*", md5($k_opad . pack("H*", md5($k_ipad .  $data))));
            case MHASH_SHA1:
                return pack('H*', sha1($data));
            case MHASH_CRC32:
            	return pack('H*', crc32($data));
            default:
                return false;

            break;
        }
    }
}

/*
** Date modified: 1st October 2004 20:09 GMT
*
** PHP implementation of the Secure Hash Algorithm ( SHA-1 )
*
** This code is available under the GNU Lesser General Public License:
** http://www.gnu.org/licenses/lgpl.txt
*
** Based on the PHP implementation by Marcus Campbell
** http://www.tecknik.net/sha-1/
*
** This is a slightly modified version by me Jerome Clarke ( sinatosk@gmail.com )
** because I feel more comfortable with this
*/

if (!function_exists('sha1')) {
function sha1_str2blks_SHA1($str) {
   $strlen_str = strlen($str);
   
   $nblk = (($strlen_str + 8) >> 6) + 1;
   
   for ($i=0; $i < $nblk * 16; $i++) $blks[$i] = 0;
   
   for ($i=0; $i < $strlen_str; $i++)
   {
       $blks[$i >> 2] |= ord(substr($str, $i, 1)) << (24 - ($i % 4) * 8);
   }
   
   $blks[$i >> 2] |= 0x80 << (24 - ($i % 4) * 8);
   $blks[$nblk * 16 - 1] = $strlen_str * 8;
   
   return $blks;
}

function sha1_safe_add($x, $y)
{
   $lsw = ($x & 0xFFFF) + ($y & 0xFFFF);
   $msw = ($x >> 16) + ($y >> 16) + ($lsw >> 16);
   
   return ($msw << 16) | ($lsw & 0xFFFF);
}

function sha1_rol($num, $cnt)
{
   return ($num << $cnt) | sha1_zeroFill($num, 32 - $cnt);    
}

function sha1_zeroFill($a, $b)
{
   $bin = decbin($a);
   
   $strlen_bin = strlen($bin);
   
   $bin = $strlen_bin < $b ? 0 : substr($bin, 0, $strlen_bin - $b);
   
   for ($i=0; $i < $b; $i++) $bin = '0'.$bin;
   
   return bindec($bin);
}

function sha1_ft($t, $b, $c, $d)
{
   if ($t < 20) return ($b & $c) | ((~$b) & $d);
   if ($t < 40) return $b ^ $c ^ $d;
   if ($t < 60) return ($b & $c) | ($b & $d) | ($c & $d);
   
   return $b ^ $c ^ $d;
}

function sha1_kt($t)
{
   if ($t < 20) return 1518500249;
   if ($t < 40) return 1859775393;
   if ($t < 60) return -1894007588;
   
   return -899497514;
}

function sha1($str, $raw_output=FALSE)
{
   if ( $raw_output === TRUE ) return pack('H*', sha1($str, FALSE));
   
   $x = sha1_str2blks_SHA1($str);
   $a =  1732584193;
   $b = -271733879;
   $c = -1732584194;
   $d =  271733878;
   $e = -1009589776;
   
   $x_count = count($x);
   
   for ($i = 0; $i < $x_count; $i += 16)
   {
       $olda = $a;
       $oldb = $b;
       $oldc = $c;
       $oldd = $d;
       $olde = $e;
       
       for ($j = 0; $j < 80; $j++)
       {
           $w[$j] = ($j < 16) ? $x[$i + $j] : sha1_rol($w[$j - 3] ^ $w[$j - 8] ^ $w[$j - 14] ^ $w[$j - 16], 1);
           
           $t = sha1_safe_add(sha1_safe_add(sha1_rol($a, 5), sha1_ft($j, $b, $c, $d)), sha1_safe_add(sha1_safe_add($e, $w[$j]), sha1_kt($j)));
           $e = $d;
           $d = $c;
           $c = sha1_rol($b, 30);
           $b = $a;
           $a = $t;
       }
       
       $a = sha1_safe_add($a, $olda);
       $b = sha1_safe_add($b, $oldb);
       $c = sha1_safe_add($c, $oldc);
       $d = sha1_safe_add($d, $oldd);
       $e = sha1_safe_add($e, $olde);
   }
   
   return sprintf('%08x%08x%08x%08x%08x', $a, $b, $c, $d, $e);
}
}

/**
 * Replace str_shuffle()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.str_shuffle
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.6 $
 * @since       PHP 4.3.0
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('str_shuffle')) {
    function str_shuffle($str)
    {
        // Init
        $str = (string) $str;

        // Seed
        list($usec, $sec) = explode(' ', microtime());
        $seed = (float) $sec + ((float) $usec * 100000);
        mt_srand($seed);

        // Shuffle
        for ($new = '', $len = strlen($str); $len > 0; $str{$p} = $str{$len}) { 
            $new .= $str{$p = mt_rand(0, --$len)};
        }

        return $new;
    }
}

/**
 * Replace array_change_key_case()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.array_change_key_case
 * @author      Stephan Schmidt <schst@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.10 $
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('array_change_key_case')) {
    function array_change_key_case($input, $case = CASE_LOWER)
    {
        if (!is_array($input)) {
            user_error('array_change_key_case(): The argument should be an array',
                E_USER_WARNING);
            return false;
        }

        $output   = array ();
        $keys     = array_keys($input);
        $casefunc = ($case == CASE_LOWER) ? 'strtolower' : 'strtoupper';

        foreach ($keys as $key) {
            $output[$casefunc($key)] = $input[$key];
        }

        return $output;
    }
}

/**
 * Replace html_entity_decode()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.html_entity_decode
 * @author      David Irvine <dave@codexweb.co.za>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.7 $
 * @since       PHP 4.3.0
 * @internal    Setting the charset will not do anything
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('html_entity_decode')) {
    function html_entity_decode($string, $quote_style = ENT_COMPAT, $charset = null)
    {
        if (!is_int($quote_style)) {
            user_error('html_entity_decode() expects parameter 2 to be long, ' .
                gettype($quote_style) . ' given', E_USER_WARNING);
            return;
        }

        $trans_tbl = get_html_translation_table(HTML_ENTITIES);
        $trans_tbl = array_flip($trans_tbl);

        // Add single quote to translation table;
        $trans_tbl['&#039;'] = '\'';

        // Not translating double quotes
        if ($quote_style & ENT_NOQUOTES) {
            // Remove double quote from translation table
            unset($trans_tbl['&quot;']);
        }

        return strtr($string, $trans_tbl);
    }
}

/**
 * Replace array_combine()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.array_chunk
 * @author      Aidan Lister <aidan@php.net>
 * @author      Thiemo Mättig (http://maettig.com)
 * @version     $Revision: 1.14 $
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('array_chunk')) {
    function array_chunk($input, $size, $preserve_keys = false)
    {
        if (!is_array($input)) {
            user_error('array_chunk() expects parameter 1 to be array, ' .
                gettype($input) . ' given', E_USER_WARNING);
            return;
        }

        if (!is_numeric($size)) {
            user_error('array_chunk() expects parameter 2 to be long, ' .
                gettype($size) . ' given', E_USER_WARNING);
            return;
        }

        $size = (int)$size;
        if ($size <= 0) {
            user_error('array_chunk() Size parameter expected to be greater than 0',
                E_USER_WARNING);
            return;
        }

        $chunks = array();
        $i = 0;

        if ($preserve_keys !== false) {
            foreach ($input as $key => $value) {
                $chunks[(int)($i++ / $size)][$key] = $value;
            }
        } else {
            foreach ($input as $value) {
                $chunks[(int)($i++ / $size)][] = $value;
            }
        }

        return $chunks;
    }
}

/**
 * Replace file_put_contents()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.file_put_contents
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.25 $
 * @internal    resource_context is not supported
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $content, $flags = null, $resource_context = null)
    {
        // If $content is an array, convert it to a string
        if (is_array($content)) {
            $content = implode('', $content);
        }

        // If we don't have a string, throw an error
        if (!is_scalar($content)) {
            user_error('file_put_contents() The 2nd parameter should be either a string or an array',
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
            user_error('file_put_contents() failed to open stream: Permission denied',
                E_USER_WARNING);
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
            user_error($errormsg, E_USER_WARNING);
            return false;
        }

        // Close the handle
        @fclose($fh);

        // Check all the data was written
        if ($bytes != $length) {
            $errormsg = sprintf('file_put_contents() Only %d of %d bytes written, possibly out of free disk space.',
                            $bytes,
                            $length);
            user_error($errormsg, E_USER_WARNING);
            return false;
        }

        // Return length
        return $bytes;
    }
}

/**
 * Replace file_get_contents()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.file_get_contents
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.21 $
 * @internal    resource_context is not supported
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('file_get_contents')) {
    function file_get_contents($filename, $incpath = false, $resource_context = null)
    {
        if (false === $fh = fopen($filename, 'rb', $incpath)) {
            user_error('file_get_contents() failed to open stream: No such file or directory',
                E_USER_WARNING);
            return false;
        }

        clearstatcache();
        if ($fsize = @filesize($filename)) {
            $data = fread($fh, $fsize);
        } else {
            $data = '';
            while (!feof($fh)) {
                $data .= fread($fh, 8192);
            }
        }

        fclose($fh);
        return $data;
    }
}

/**
 * Replace stripos()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.stripos
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.13 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('stripos')) {
    function stripos($haystack, $needle, $offset = null)
    {
        if (!is_scalar($haystack)) {
            user_error('stripos() expects parameter 1 to be string, ' .
                gettype($haystack) . ' given', E_USER_WARNING);
            return false;
        }

        if (!is_scalar($needle)) {
            user_error('stripos() needle is not a string or an integer.', E_USER_WARNING);
            return false;
        }

        if (!is_int($offset) && !is_bool($offset) && !is_null($offset)) {
            user_error('stripos() expects parameter 3 to be long, ' .
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
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.str_ireplace
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.18 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 * @note        count not by returned by reference, to enable
 *              change '$count = null' to '&$count'
 */
if (!function_exists('str_ireplace')) {
    function str_ireplace($search, $replace, $subject, $count = null)
    {
        // Sanity check
        if (is_string($search) && is_array($replace)) {
            user_error('Array to string conversion', E_USER_NOTICE);
            $replace = (string) $replace;
        }

        // If search isn't an array, make it one
        if (!is_array($search)) {
            $search = array ($search);
        }
        $search = array_values($search);

        // If replace isn't an array, make it one, and pad it to the length of search
        if (!is_array($replace)) {
            $replace_string = $replace;

            $replace = array ();
            for ($i = 0, $c = count($search); $i < $c; $i++) {
                $replace[$i] = $replace_string;
            }
        }
        $replace = array_values($replace);

        // Check the replace array is padded to the correct length
        $length_replace = count($replace);
        $length_search = count($search);
        if ($length_replace < $length_search) {
            for ($i = $length_replace; $i < $length_search; $i++) {
                $replace[$i] = '';
            }
        }

        // If subject is not an array, make it one
        $was_array = false;
        if (!is_array($subject)) {
            $was_array = true;
            $subject = array ($subject);
        }

        // Loop through each subject
        $count = 0;
        foreach ($subject as $subject_key => $subject_value) {
            // Loop through each search
            foreach ($search as $search_key => $search_value) {
                // Split the array into segments, in between each part is our search
                $segments = explode(strtolower($search_value), strtolower($subject_value));

                // The number of replacements done is the number of segments minus the first
                $count += count($segments) - 1;
                $pos = 0;

                // Loop through each segment
                foreach ($segments as $segment_key => $segment_value) {
                    // Replace the lowercase segments with the upper case versions
                    $segments[$segment_key] = substr($subject_value, $pos, strlen($segment_value));
                    // Increase the position relative to the initial string
                    $pos += strlen($segment_value) + strlen($search_value);
                }

                // Put our original string back together
                $subject_value = implode($replace[$search_key], $segments);
            }

            $result[$subject_key] = $subject_value;
        }

        // Check if subject was initially a string and return it as a string
        if ($was_array === true) {
            return $result[0];
        }

        // Otherwise, just return the array
        return $result;
    }
}

/**
 * Replace str_split()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.str_split
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.15 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('str_split')) {
    function str_split($string, $split_length = 1)
    {
        if (!is_scalar($split_length)) {
            user_error('str_split() expects parameter 2 to be long, ' .
                gettype($split_length) . ' given', E_USER_WARNING);
            return false;
        }

        $split_length = (int) $split_length;
        if ($split_length < 1) {
            user_error('str_split() The length of each segment must be greater than zero', E_USER_WARNING);
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
/**
 * Replace version_compare()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.version_compare
 * @author      Philippe Jausions <Philippe.Jausions@11abacus.com>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.13 $
 * @since       PHP 4.1.0
 * @require     PHP 4.0.5 (user_error)
 */
if (!function_exists('version_compare')) {
    function version_compare($version1, $version2, $operator = '<')
    {
        // Check input
        if (!is_scalar($version1)) {
            user_error('version_compare() expects parameter 1 to be string, ' .
                gettype($version1) . ' given', E_USER_WARNING);
            return;
        }

        if (!is_scalar($version2)) {
            user_error('version_compare() expects parameter 2 to be string, ' .
                gettype($version2) . ' given', E_USER_WARNING);
            return;
        }

        if (!is_scalar($operator)) {
            user_error('version_compare() expects parameter 3 to be string, ' .
                gettype($operator) . ' given', E_USER_WARNING);
            return;
        }

        // Standardise versions
        $v1 = explode('.',
            str_replace('..', '.',
                preg_replace('/([^0-9\.]+)/', '.$1.',
                    str_replace(array('-', '_', '+'), '.',
                        trim($version1)))));

        $v2 = explode('.',
            str_replace('..', '.',
                preg_replace('/([^0-9\.]+)/', '.$1.',
                    str_replace(array('-', '_', '+'), '.',
                        trim($version2)))));

        // Replace empty entries at the start of the array
        while (empty($v1[0]) && array_shift($v1)) {}
        while (empty($v2[0]) && array_shift($v2)) {}

        // Release state order
        // '#' stands for any number
        $versions = array(
            'dev'   => 0,
            'alpha' => 1,
            'a'     => 1,
            'beta'  => 2,
            'b'     => 2,
            'RC'    => 3,
            '#'     => 4,
            'p'     => 5,
            'pl'    => 5);

        // Loop through each segment in the version string
        $compare = 0;
        for ($i = 0, $x = min(count($v1), count($v2)); $i < $x; $i++) {
            if ($v1[$i] == $v2[$i]) {
                continue;
            }
            $i1 = $v1[$i];
            $i2 = $v2[$i];
            if (is_numeric($i1) && is_numeric($i2)) {
                $compare = ($i1 < $i2) ? -1 : 1;
                break;
            }

            // We use the position of '#' in the versions list
            // for numbers... (so take care of # in original string)
            if ($i1 == '#') {
                $i1 = '';
            } elseif (is_numeric($i1)) {
                $i1 = '#';
            }

            if ($i2 == '#') {
                $i2 = '';
            } elseif (is_numeric($i2)) {
                $i2 = '#';
            }

            if (isset($versions[$i1]) && isset($versions[$i2])) {
                $compare = ($versions[$i1] < $versions[$i2]) ? -1 : 1;
            } elseif (isset($versions[$i1])) {
                $compare = 1;
            } elseif (isset($versions[$i2])) {
                $compare = -1;
            } else {
                $compare = 0;
            }

            break;
        }

        // If previous loop didn't find anything, compare the "extra" segments
        if ($compare == 0) {
            if (count($v2) > count($v1)) {
                if (isset($versions[$v2[$i]])) {
                    $compare = ($versions[$v2[$i]] < 4) ? 1 : -1;
                } else {
                    $compare = -1;
                }
            } elseif (count($v2) < count($v1)) {
                if (isset($versions[$v1[$i]])) {
                    $compare = ($versions[$v1[$i]] < 4) ? -1 : 1;
                } else {
                    $compare = 1;
                }
            }
        }

        // Compare the versions
        if (func_num_args() > 2) {
            switch ($operator) {
                case '>':
                case 'gt':
                    return (bool) ($compare > 0);
                    break;
                case '>=':
                case 'ge':
                    return (bool) ($compare >= 0);
                    break;
                case '<=':
                case 'le':
                    return (bool) ($compare <= 0);
                    break;
                case '==':
                case '=':
                case 'eq':
                    return (bool) ($compare == 0);
                    break;
                case '<>':
                case '!=':
                case 'ne':
                    return (bool) ($compare != 0);
                    break;
                case '':
                case '<':
                case 'lt':
                    return (bool) ($compare < 0);
                    break;
                default:
                    return;
            }
        }

        return $compare;
    }
}

/**
 * Replace array_fill()
 *
 * @category    PHP
 * @link        http://php.net/function.array_fill
 * @author      Matthias Mohr <webmaster@mamo-net.de>
 * @version     $Revision: 1.0 $
 * @since       PHP 4.2.0
 * @require     PHP 3
 */
if (!function_exists('array_fill')) {
    function array_fill($iStart, $iLen, $vValue) {
       $aResult = array();
       for ($iCount = $iStart; $iCount < $iLen + $iStart; $iCount++) {
           $aResult[$iCount] = $vValue;
       }
       return (array) $aResult;
    }
}

/**
 * Replace array_combine()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.array_combine
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.21 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('array_combine')) {
    function array_combine($keys, $values)
    {
        if (!is_array($keys)) {
            user_error('array_combine() expects parameter 1 to be array, ' .
                gettype($keys) . ' given', E_USER_WARNING);
            return;
        }

        if (!is_array($values)) {
            user_error('array_combine() expects parameter 2 to be array, ' .
                gettype($values) . ' given', E_USER_WARNING);
            return;
        }

        $key_count = count($keys);
        $value_count = count($values);
        if ($key_count !== $value_count) {
            user_error('array_combine() Both parameters should have equal number of elements', E_USER_WARNING);
            return false;
        }

        if ($key_count === 0 || $value_count === 0) {
            user_error('array_combine() Both parameters should have number of elements at least 0', E_USER_WARNING);
            return false;
        }

        $keys    = array_values($keys);
        $values  = array_values($values);

        $combined = array();
        for ($i = 0; $i < $key_count; $i++) {
            $combined[$keys[$i]] = $values[$i];
        }

        return $combined;
    }
}

/**
 * Replace function is_a()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.is_a
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.16 $
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0 (user_error) (is_subclass_of)
 */
//  Required for lib_diff.php
if (!function_exists('is_a')) {
    function is_a($object, $class)
    {
        if (!is_object($object)) {
            return false;
        }

        if (get_class($object) == strtolower($class)) {
            return true;
        } else {
            return is_subclass_of($object, $class);
        }
    }
}

/**
 * Replace function http_build_query()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.http-build-query
 * @author      Stephan Schmidt <schst@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.16 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('http_build_query')) {
    function http_build_query($formdata, $numeric_prefix = null)
    {
        // If $formdata is an object, convert it to an array
        if (is_object($formdata)) {
            $formdata = get_object_vars($formdata);
        }

        // Check we have an array to work with
        if (!is_array($formdata)) {
            user_error('http_build_query() Parameter 1 expected to be Array or Object. Incorrect value given.',
                E_USER_WARNING);
            return false;
        }

        // If the array is empty, return null
        if (empty($formdata)) {
            return;
        }

        // Argument seperator
        $separator = ini_get('arg_separator.output');

        // Start building the query
        $tmp = array ();
        foreach ($formdata as $key => $val) {
            if (is_integer($key) && $numeric_prefix != null) {
                $key = $numeric_prefix . $key;
            }

            if (is_scalar($val)) {
                array_push($tmp, urlencode($key).'='.urlencode($val));
                continue;
            }

            // If the value is an array, recursively parse it
            if (is_array($val)) {
                array_push($tmp, __http_build_query($val, urlencode($key)));
                continue;
            }
        }

        return implode($separator, $tmp);
    }

    // Helper function
    function __http_build_query ($array, $name)
    {
        $tmp = array ();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                array_push($tmp, __http_build_query($value, sprintf('%s[%s]', $name, $key)));
            } elseif (is_scalar($value)) {
                array_push($tmp, sprintf('%s[%s]=%s', $name, urlencode($key), urlencode($value)));
            } elseif (is_object($value)) {
                array_push($tmp, __http_build_query(get_object_vars($value), sprintf('%s[%s]', $name, $key)));
            }
        }

        // Argument seperator
        $separator = ini_get('arg_separator.output');

        return implode($separator, $tmp);
    }
}

/**
 * Replace array_key_exists()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.array_key_exists
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.7 $
 * @since       PHP 4.1.0
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('array_key_exists')) {
    function array_key_exists($key, $search)
    {
        if (!is_scalar($key)) {
            user_error('array_key_exists() The first argument should be either a string or an integer',
                E_USER_WARNING);
            return false;
        }

        if (is_object($search)) {
            $search = get_object_vars($search);
        }

        if (!is_array($search)) {
            user_error('array_key_exists() The second argument should be either an array or an object',
                E_USER_WARNING);
            return false;
        }

        return in_array($key, array_keys($search));
    }
}


?>