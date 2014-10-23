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

class OutputDoc {

var $enc;
var $level;
var $sid;
var $cfg;

function OutputDoc($cfg) {
	$this->enc = $this->CanGZIP();
	$this->cfg = $cfg;
}

function Encoding() {
	return $this->enc;
}

function AddSid($content) {
	if (!empty($this->sid)) {
	    $own_url = ini_isSecureHttp() ? 'https://' : 'http://';
	    $own_url = preg_quote($own_url.$_SERVER['HTTP_HOST'], '~');
		$content = preg_replace_callback('~<a([^>]+?)href=("|\')('.$own_url.'(:\d*)?/?([a-zA-Z0-9\-\.:;_\?\,/\\\+&%\$#\=\~\[\]]*)?|([a-zA-Z0-9\-\._/\~]*)?[\w-]+?\.\w+?(\?[a-zA-Z0-9\-\.:;_\?\,/\\\+&%\$#\=\~\[\]]*)?)("|\')~i', array(&$this, 'ConstructLink'), $content);
	}
    return $content;
}

function ConstructLink($matches) {
	list(,$prehref,,$url) = $matches;
	if (substr($url,-1) == '?') {
		$url = substr($url,0,strlen($url)-1);
	}
	$info = parse_url($url);
	if (isset($info['query'])) {
		$info['query'] = '?'.$info['query'];
		if (!preg_match('~(\?|&amp;|&)s=[A-Za-z0-9]*?~i', $info['query'])) {
			$url .= '&amp;s='.$this->sid;
		}
		// Auskommentieren, wenn alte (oder leere) SIDs nicht ersetzt werden sollen
		else {
			$url = preg_replace('~(\?|&amp;|&)s=([A-Za-z0-9]*)~i', '\1s='.$this->sid,$url);
		}
	}
	else {
		$url .= '?s='.$this->sid;
	}
	return '<a'.$prehref.'href="'.$url.'"';
}

function CanGZIP() {
	if (headers_sent() || connection_aborted() || !extension_loaded("zlib") || !viscacha_function_exists('gzcompress')){
		return false;
	}
	if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) {
		return "x-gzip";
	}
	if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') !== false) {
		return "gzip";
	}
	return false;
}
function Out($skip = 1){
	global $breadcrumb, $config, $plugins;
	$this->sid = SID2URL;
	$Contents = ob_get_contents();
	ob_end_clean();
	$Contents = $this->AddSid($Contents);

	($code = $plugins->load('docout_parse')) ? eval($code) : null;

	if ($this->enc != false && $skip == 1 && $this->cfg == 1) {
		viscacha_header("Content-Encoding: ".$this->enc);
		print "\x1f\x8b\x08\x00\x00\x00\x00\x00";
		$Size = strlen($Contents);
		$Crc = crc32($Contents);
		$Contents = gzcompress($Contents,$this->level);
		$Contents = substr($Contents, 0, strlen($Contents) - 4);
		print $Contents;
		print pack('V',$Crc);
		print pack('V',$Size);
	}
	else{
		print $Contents;
	}
}
function Start($compression = 0){
	$this->level = $compression;
	ob_start();
	ob_implicit_flush(0);
}

}
?>