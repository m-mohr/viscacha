<?php
/* Bases on:
**  Author........: catoc <catoc@163.net>
**  Requirments...: PHP4 >= 4.0.1
**                  PHP configured with --with-zlib[=DIR]
**  Read more.....: http://php.weblogs.com/http_compression
*/
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
	    $own_url = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
	    $own_url = preg_quote($own_url.$_SERVER['HTTP_HOST'], '~');
		$content = preg_replace('~<a([^>]+?)href=("|\')('.$own_url.'(:\d*)?/?([a-zA-Z0-9\-\.:;_\?\,/\\\+&%\$#\=\~\[\]]*)?|([a-zA-Z0-9\-\._/\~]*)?[\w-]+?\.\w+?(\?[a-zA-Z0-9\-\.:;_\?\,/\\\+&%\$#\=\~\[\]]*)?)("|\')~ie', '$this->ConstructLink("\1","\3")', $content);
	}
    return $content;
}

function ConstructLink($prehref, $url) {
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
	if (headers_sent() || connection_aborted() || !extension_loaded("zlib") || !function_exists('gzcompress')){
		return FALSE;
	}
	if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) {
		return "x-gzip";
	}
	if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') !== false) {
		return "gzip";
	}
	return FALSE;
}
function Out($skip = 1){
	global $breadcrumb, $config, $plugins;
	$this->sid = SID2URL;
	$Contents = ob_get_contents();
	ob_end_clean();
	$Contents = $this->AddSid($Contents);
	
	($code = $plugins->load('docout_parse')) ? eval($code) : null;
	
	if ($this->enc != FALSE && $skip == 1 && $this->cfg == 1) {
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
