<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

/*
*	This script is written by Matthias Mohr, 2005 for Viscacha
*	Some parts of this script are written by Setec Astronomy - setec@freemail.it
*
*	This script is distributed under the GNU GPL License!
*/
define ("IM_ABORT", 0);
define ("IM_ONLINE", 1);
define ("IM_OFFLINE", 2);
define ("IM_UNKNOWN", 3);
define ("IM_AWAY", 4);

define ("IM_ICQ", "icq");
define ("IM_AIM", "aol");
define ("IM_JABBER", "jabber");
define ("IM_MSN", "msn");
define ("IM_YAHOO", "yahoo");
define ("IM_SKYPE", "skype");

define ("IM_ERRNO", 1);
define ("IM_ERRSTR", 2);

class IMStatus {
	var $timeout = 3;
	var $server = array();
	var $errno = false;
	var $errstr = false;

	function IMStatus () {
		// For Servers see: http://www.onlinestatus.org/usage.html
		$server = file('data/imservers.php');
		$this->server = array_map('trim', $server);
	}

	function error($type) {
		if ($type == IM_ERRNO) {
			return $this->errno;
		}
		elseif ($type == IM_ERRSTR) {
			return $this->errstr;
		}
		else {
			return $this->errno.": ".$this->errstr."\n";
		}
	}

	function icq ($account) {
		$host = "status.icq.com";
		$path = "/online.gif?icq=".$account;

		$fp = fsockopen ($host, 80, $this->errno, $this->errstr, $this->timeout);
		if (!$fp) {
			return $this->fallback($account, IM_ICQ);
		}
		else {
			fputs ($fp,"GET ".$path." HTTP/1.1\r\n");
			fputs ($fp,"HOST: ".$host."\r\n");
			fputs ($fp,"User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Viscacha)\r\n");
			fputs ($fp,"Connection: close\r\n\r\n");

			$raw_headers = '';
			while (!feof ($fp)) {
				$raw_headers .= fgets ($fp, 128);
			}
		}
		fclose ($fp);

		$location = $this->parse_header($raw_headers, 'Location');

		$filename = basename ($location);
		switch ($filename) {
			case "online0.gif":
				return IM_OFFLINE;
				break;
			case "online1.gif":
				return IM_ONLINE;
				break;
			default:
				return IM_UNKNOWN;
				break;
		}
	}

	function skype ($account) {
		$host = "mystatus.skype.com";
		$path = "/{$account}.xml";

		$fp = fsockopen ($host, 80, $this->errno, $this->errstr, $this->timeout);
		if (!$fp) {
			$this->errno = 1;
			$this->errstr = "Unable to connect to http://mystatus.skype.com/{$account}.xml";
			return IM_ABORT;
		}
		else {
			fputs ($fp,"GET ".$path." HTTP/1.1\r\n");
			fputs ($fp,"HOST: ".$host."\r\n");
			fputs ($fp,"User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Viscacha)\r\n");
			fputs ($fp,"Connection: close\r\n\r\n");

			$raw_headers = '';
			while (!feof ($fp)) {
				$raw_headers .= fgets ($fp, 128);
			}
		}
		fclose ($fp);

		$pattern = "~xml:lang=\"NUM\">(\d)</~";
		preg_match($pattern, $raw_headers, $match);
		/*
		 * get the status number:
		 * 0	UNKNOWN			Not opted in or no data available.
		 * 1	OFFLINE			The user is Offline
		 * 2	ONLINE			The user is Online
		 * 3	AWAY			The user is Away
		 * 4	NOT AVAILABLE	The user is Not Available
		 * 5	DO NOT DISTURB	The user is Do Not Disturb (DND)
		 * 6	INVISIBLE		The user is Invisible or appears Offline
		 * 7	SKYPE ME		The user is in Skype Me mode
		 */
		switch ($match[1]) {
			case "1":
			case "6":
				return IM_OFFLINE;
				break;
			case "2":
			case "7":
				return IM_ONLINE;
				break;
			case "3":
			case "4":
			case "5":
				return IM_AWAY;
				break;
			default:
				return IM_UNKNOWN;
				break;
		}
	}

	function aol ($account) {

		$host = "big.oscar.aol.com";
		$path = "/{$account}?on_url=true&off_url=false";

		$fp = fsockopen ($host, 80, $this->errno, $this->errstr, $this->timeout);
		if (!$fp) {
			return $this->fallback($account, IM_AIM);
		}
		else {
			fputs ($fp,"GET ".$path." HTTP/1.1\r\n");
			fputs ($fp,"HOST: ".$host."\r\n");
			fputs ($fp,"User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Viscacha)\r\n");
			fputs ($fp,"Connection: close\r\n\r\n");

			$raw_headers = '';
			while (!feof ($fp)) {
				$raw_headers .= fgets ($fp, 128);
			}
		}
		fclose ($fp);

		$location = $this->parse_header($raw_headers, 'Location');

		$filename = basename ($location);
		switch ($filename) {
			case "false":
				return IM_OFFLINE;
				break;
			case "true":
				return IM_ONLINE;
				break;
			default:
				return IM_UNKNOWN;
				break;
		}

	}

	function jabber ($account) {
		return $this->fallback($account, IM_JABBER);
	}

	function msn ($account) {
		return $this->fallback($account, IM_MSN);
	}

	function yahoo ($account) {
		// Use fsockopen to set a timeout
		// if you don't want a timeout use this code:
		// $lines = @file("http://opi.yahoo.com/online?m=t&u=".$this->account);
		$fp = @fsockopen("opi.yahoo.com", 80, $this->errno, $this->errstr, $this->timeout);
		if (!$fp) {
			return $this->fallback($account, IM_YAHOO);
		}
		else {
			$out = "GET /online?m=t&u=".$account." HTTP/1.1\r\n";
			$out .= "Host: opi.yahoo.com\r\n";
			$out .= "Connection: Close\r\n\r\n";
			fwrite($fp, $out);
			while (!feof($fp)) {
			   $lines[] = fgets($fp, 128);
			}
			fclose($fp);
		}

		if ($lines !== false) {
			$response = implode ("", $lines);
			if (strpos ($response, "NOT ONLINE") !== false) {
				return IM_OFFLINE;
			}
			elseif (strpos ($response, "ONLINE") !== false) {
				return IM_ONLINE;
			}
			else {
				return IM_UNKNOWN;
			}
		}
		else {
			$this->errno = 1;
			$this->errstr = "Unable to connect to http://opi.yahoo.com";
			return IM_ABORT;
		}
	}

	function fallback($account, $medium) {

		srand((double)microtime()*1000000);
		$random = rand(0,count($this->server)-1);
		$server = $this->server[$random];

	    $url = parse_url($server);
		$url["host"] = !isset($url["host"]) ? "localhost" : $url["host"];
		$url["port"] = !isset($url["port"]) ? "80" : $url["port"];
		$url["path"] = !isset($url["path"]) ? "/" : trim($url["path"]);

		if (substr ($url["path"], -1) != "/") {
			$url["path"] .= "/";
		}
		$url["path"] .= $medium . "/" . $account . "/onurl=online/offurl=offline";

		$fp = @fsockopen ($url["host"], $url["port"], $this->errno, $this->errstr, $this->timeout);
		if (!$fp) {
			return IM_ABORT;
		}
		else {
			$url["port"] = ':'.$url["port"];

			fputs ($fp,"GET " . $url["path"] . " HTTP/1.1\r\n");
			fputs ($fp,"HOST: " . $url["host"] . $url["port"] . "\r\n");
			fputs ($fp,"User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Viscacha)\r\n");
			fputs ($fp,"Connection: close\r\n\r\n");

			$raw_headers = '';
			while (!feof ($fp)) {
				$raw_headers .= fgets ($fp, 128);
			}
		}
		fclose ($fp);

		$location = $this->parse_header($raw_headers, 'Location');
		$parse_location = parse_url ($location);
		$parse_location["host"] = !isset($parse_location["host"]) ? "unknown" : $parse_location["host"];
		switch ($parse_location["host"]) {
			case "online":
				return IM_ONLINE;
				break;
			case "offline":
				return IM_OFFLINE;
				break;
			default:
				return IM_UNKNOWN;
				break;
		}
	}

	function parse_header($raw_headers, $index = NULL) {
		$headers = array ();
		$tmp_headers = explode ("\n", $raw_headers);

		foreach ($tmp_headers as $header) {
			$tokens = explode (":", $header, 2);
			if (isset ($tokens[0]) && (trim($tokens[0]) != "")) {
				if (!isset ($tokens[1])) { $tokens[1] = ""; }
				$headers[] = array ($tokens[0] => trim($tokens[1]));
			}
		}

		if ($index != NULL) {
			$i = "";
			foreach ($headers as $header) {
				if (isset($header[$index])) {
					$i = $header[$index];
					break;
				}
			}
			return $i;
		}
		else {
			return $headers;
		}
	}

}
?>
