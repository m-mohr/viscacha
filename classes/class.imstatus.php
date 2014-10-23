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

require_once('classes/class.snoopy.php');

class IMStatus {

	var $timeout = 3;
	var $server = array();
	var $errstr = null;
	var $snoopy = null;

	function IMStatus () {
		// For Servers see: http://osi.viscacha.org
		$server = file('data/imservers.php');
		$this->server = array_map('trim', $server);
		$this->snoopy = new Snoopy();
		$this->snoopy->read_timeout = $this->timeout;
		$this->snoopy->maxredirs = 0;
	}

	function error($type) {
		return $this->errstr;
	}

	function icq ($account) {
		$url = 'http://status.icq.com/online.gif?icq='.$account;
		$data = $this->snoopy->fetch($url);
		if ($data === false) {
			return $this->fallback($account, IM_ICQ);
		}
		$location = $this->parse_header($this->snoopy->headers, 'Location');

		$filename = basename($location);
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
		$url = "http://mystatus.skype.com/{$account}.xml";
		$data = $this->snoopy->fetch($url);
		if ($data === false) {
			return $this->fallback($account, IM_SKYPE);
		}
		preg_match("~xml:lang=\"NUM\">(\d)</~i", $this->snoopy->results, $match);

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
		$url = "http://big.oscar.aol.com/{$account}?on_url=true&off_url=false";
		$data = $this->snoopy->fetch($url);
		if ($data === false) {
			return $this->fallback($account, IM_AIM);
		}
		$location = $this->parse_header($this->snoopy->headers, 'Location');

		$filename = basename($location);
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
		$url = "http://opi.yahoo.com/online?m=t&u={$account}";
		$data = $this->snoopy->fetch($url);
		if ($data === false) {
			return $this->fallback($account, IM_YAHOO);
		}
		else {
			$response = $this->snoopy->results;
			if (stripos($response, "NOT ONLINE") !== false) {
				return IM_OFFLINE;
			}
			elseif (stripos($response, "ONLINE") !== false) {
				return IM_ONLINE;
			}
			else {
				return IM_UNKNOWN;
			}
		}
	}

	function fallback($account, $medium) {

		$random = mt_rand(0,count($this->server)-1);
		$server = $this->server[$random];

		$url = "{$server}/{$medium}/{$account}/onurl=online/offurl=offline";
		$data = $this->snoopy->fetch($url);
		if ($data === false) {
			$this->errstr = $this->snoopy->error;
			return IM_ABORT;
		}
		$location = $this->parse_header($this->snoopy->headers, 'Location');
		if (stripos($location, "offline") !== false) {
			return IM_OFFLINE;
		}
		elseif (stripos($location, "online") !== false) {
			return IM_ONLINE;
		}
		else {
			return IM_UNKNOWN;
		}
	}

	function parse_header($tmp_headers, $index = NULL) {
		$headers = array ();
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