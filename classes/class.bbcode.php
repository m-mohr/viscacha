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

define('SP_CHANGE', 1);
define('SP_COPY', 2);
define('SP_NEW', 4);

define('CBBC_BUTTONDIR', 'templates/editor/images/');

class BBCode {

	var $smileys;
	var $bbcodes;
	var $custombb;
	var $profile;
	var $cfg;
	var $reader;
	var $noparse;
	var $pid;
	var $benchmark;
	var $author;
	var $index;
	var $url_regex;
	var $url_regex2;
	var $url_protocol;
	var $currentCBB;

	function BBCode ($profile = 'viscacha') {
		$this->benchmark = array(
			'smileys' => 0,
			'bbcode' => 0
		);
		$this->smileys = null;
		$this->bbcodes = null;
		$this->custombb = null;
		$this->currentCBB = null;
		$this->profile = '';
		$this->cfg = array();
		$this->reader = '';
		$this->noparse = array();
		$this->pid = 0;
		$this->author = -1;
		$this->index = 0;

		// See: http://en.wikipedia.org/wiki/URI_scheme
		$this->url_protocol = "((?:https?|s?ftp|nntp|gopher|ldaps?|snmp|telnet|cvs|svn|ed2k|feed|ircs?|lastfm|mms|callto|ssh|teamspeak)://|www\.)";
		$url_word = URL_SPECIALCHARS;
		$url_auth = "(?:(?:[{$url_word}_\d\-\.]{1,}\:)?[{$url_word}\d\-\._]{1,}@)?"; // Authorisation information
		$url_host = "(?:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|[{$url_word}\d\.\-]{2,}\.[a-z]{2,7})(?:\:\d+)?"; // Host (domain, tld, ip, port)
		$url_path = "(?:\/[{$url_word}ß\d\/;\-%\~,\.\+\!&=_]*)?"; // Path
		$url_query = "(?:\?[{$url_word}ß\d=\&;\.:,\_\-\/%\+\~\[\]]*)?"; // Query String
		$url_fragment = "(?:#[\w\d]*)?"; // Fragment

		// URL RegExp - Two matches predefined: First is whole url, second is URI scheme
		$this->url_regex = "({$this->url_protocol}{$url_auth}{$url_host}{$url_path}{$url_query}{$url_fragment})";
		$this->url_regex2 = "({$this->url_protocol}{$url_auth}{$url_host}{$url_path}(?:\?[{$url_word}ß\d=\&;\.:,\_\-\/%\+\~]*)?{$url_fragment})";

		if (!class_exists('ConvertRoman')) {
			include_once('classes/class.convertroman.php');
		}

		$this->setProfile($profile, SP_NEW);
	}
	function setName($name) {
		$this->reader = $name;
	}
	function setAuthor($id = -1) {
		$this->author = $id;
	}
	function noparse_id() {
		$this->pid++;
		return $this->pid;
	}
	function replacePID($text) {
		foreach ($this->noparse as $key => $value) {
			$text = str_replace('<!PID:'.$key.'>', $value, $text);
		}
		return $text;
	}
	function cb_list ($matches) {
		list(, $type, $pattern) = $matches;
		$liarray = preg_split('/(\n\s?-\s|\[\*\])/', "\n".$pattern); // Add line break for the first "-", it will be trimmed in the next line.
		$liarray = array_map('trim', $liarray);
		$list = '';
		foreach ($liarray as $li) {
			if (!empty($li)) {
				$list .= '<li>'.$li.'</li>';
			}
		}
		if (empty($list)) {
			return '';
		}
		if (!empty($type)) {
			if ($type == 'a' || $type == 'A' || $type == 'i' || $type == 'I') {
				$list = "<ol type='{$type}'>{$list}</ol>";
			}
			else {
				$list = "<ol>{$list}</ol>";
			}
		}
		else {
			$list = "<ul>{$list}</ul>";
		}
		return $list;
	}
	function code_trim ($code) {
		$code = preg_replace('/^([\s\t]*\n+)?(.+?)(\n+[\s\t]*)?$/s', '\2', $code);
		return $code;
	}
	function code_prepare($code, $inline = false) {
		$code = str_replace("]", "&#93;", $code);
		$code = str_replace("[", "&#91;", $code);
		$code = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $code);
		if ($inline == true) {
			$code = str_replace("  ", "&nbsp;&nbsp;", trim($code));
		}
		else {
			$code = str_replace(" ", "&nbsp;", $code);
		}
		return $code;
	}
	function cb_hlcode ($matches) {
		global $lang, $scache;
		$pid = $this->noparse_id();
		list(,, $sclang, $code, $nl) = $matches;

		$code = trim($code, "\r\n");
		$rows = explode("\n", $code);
		if (count($rows) > 1 || $this->wordwrap($code) != $code) {
			$scache->loadClass('UniversalCodeCache');
			$cache = new UniversalCodeCache();
			$cache->setData($code, $sclang);
			$data = $cache->get();
			if ($cache->hasLanguage()) {
				$lang->assign('lang_name', $data['language']);
				$title = $lang->phrase('geshi_hlcode_title');
			}
			else {
				$title = $lang->phrase('bb_sourcecode');
			}
			$html = '<div class="highlightcode"><a class="bb_blockcode_options" href="misc.php?action=download_code&amp;fid='.$cache->getHash().'">'.$lang->phrase('geshi_hlcode_txtdownload').'</a>';
			$html .= '<strong>'.$title.'</strong>';
			$html .= '<div class="bb_blockcode">'.$data['parsed'].'</div></div>';
			$this->noparse[$pid] = $html;
		}
		else {
			$code = trim($code);
			$code = $this->code_prepare($code, (count($rows) <= 1));
			$this->noparse[$pid] = '<code class="bb_inlinecode">'.$code.'</code>';
			if (!empty($nl)) {
				$this->noparse[$pid] .= '<br />';
			}
		}
		return '<!PID:'.$pid.'>';
	}
	function cb_mail ($email) {
		global $lang;
		$pid = $this->noparse_id();
		$this->noparse[$pid] = 'images.php?action=textimage&amp;text='.base64_encode($email[1]).'&amp;enc=1';
		$html = '<img alt="'.$lang->phrase('bbcodes_email').'" src="<!PID:'.$pid.'>" border="0" />';
		return $html;
	}
	function cb_header ($matches) {
		list(,$size,$content) = $matches;
		if ($size == 'small') {
			$fontsize = 12;
			$level = 6;
		}
		elseif ($size == 'large') {
			$fontsize = 14;
			$level = 4;
		}
		else {
			$fontsize = 13;
			$level = 5;
		}
		$content = $this->wordwrap($content, ceil($this->profile['wordwrap_wordlength']*(8/$fontsize)));
		$o = "<h{$level} class=\"bb_header\" style=\"font-size: {$fontsize}pt;\">{$content}</h{$level}>";
		return $o;
	}
	function cb_size ($matches) {
		list(,$size,$content) = $matches;
		if ($size != 'extended') {
			if ($size == 'small') {
				$fontsize = 0.8;
			}
			elseif ($size == 'large') {
				$fontsize = 1.5;
				$content = $this->wordwrap($content, ceil($this->profile['wordwrap_wordlength']*(1/1.5)));
			}
			else {
				$fontsize = 1;
			}
			$o = "<span style=\"font-size: {$fontsize}em;\">{$content}</span>";
		}
		else {
			$content = $this->wordwrap($content, ceil($this->profile['wordwrap_wordlength']*0.75));
			$o = "<span class=\"bb_size_extended\">{$content}</span>";
		}

		return $o;
	}
	function cb_note ($matches) {
		list(,$desc,$word) = $matches;
		$this->index++;
		$pid = $this->noparse_id();
		$o = "<acronym title=\"<!PID:{$pid}>\" id=\"menu_tooltip_{$this->index}\" onmouseover=\"RegisterTooltip({$this->index})\">{$word}</acronym><div class=\"tooltip tooltip_body\" id=\"popup_tooltip_{$this->index}\"><!PID:{$pid}></div>";
		$this->noparse[$pid] = $desc;
		return $o;
	}
	function cb_image ($matches) {
		list(, $url, $extension) = $matches;

		$pid = $this->noparse_id();
		$this->noparse[$pid] = $url;

		return '<img src="<!PID:'.$pid.'>" alt=""'.iif($this->profile['resizeImg'] > 0, ' name="resize"').' />';
	}
	function cb_auto_url ($matches) {
		list(, $prefix, $url,, $suffix) = $matches;
		return $this->cb_url($url, false, $prefix, $suffix);
	}
	function cb_title_url ($matches) {
		list(, $url,, $title) = $matches;
		return $this->cb_url($url, $title);
	}
	function cb_plain_url ($matches) {
		list(, $url,) = $matches;
		return $this->cb_url($url);
	}
	function cb_url ($url, $title = false, $prefix = '', $suffix = '') {
		global $config;

		if (strtolower(substr($url, 0, 4)) == 'www.') {
			$url = "http://{$url}";
		}

		$pid = $this->noparse_id();
		$this->noparse[$pid] = $url;

		if ($title != false) { // Ein Titel wurde angegeben
			$ahref = '<a href="<!PID:'.$pid.'>" target="_blank">'.$title.'</a>';
			return $ahref;
		}
		elseif ($this->profile['reduceUrl'] == 1 && strxlen($url) >= $this->profile['reducelength']) { // Die URL wird als Titel genommen und gekürzt
			$before = ceil($this->profile['reducelength']/5);
			$after = strpos($url, '/', 8);
			$func = 'substr';
			if ($after === false) {
				$after = ceil($this->profile['reducelength']/3);
				$func = 'subxstr';
			}
			$newurl = $func($url, 0, $after+1).$this->profile['reducesep'].subxstr($url, -$before);
			$pid2 = $this->noparse_id();
			$this->noparse[$pid2] = $newurl;
			$ahref = '<a href="<!PID:'.$pid.'>" target="_blank"><!PID:'.$pid2.'></a>';
		}
		else { // Die URL wird ungekürzt als Titel genommen
			$ahref = '<a href="<!PID:'.$pid.'>" target="_blank"><!PID:'.$pid.'></a>';
		}

		return $prefix.$ahref.$suffix;
	}
	function cb_plain_list ($matches) {
		list(, $type, $pattern) = $matches;
		$liarray = preg_split('/(\n\s?-\s|\[\*\])/',$pattern);
		$list = "\n";
		$i = 0;
		$pre = '  ';
		foreach ($liarray as $li) {
			$li = trim($li);
			if (empty($li)) {
				continue;
			}
			$i++;
			if (!empty($type)) {
				if ($type == 'i' || $type == 'I') {
					$converter = new ConvertRoman($i);
					$a = $converter->result();
					if ($type == 'i') {
						$a = strtolower($a);
					}
					$list .= $pre."{$a}. {$li}\n";
				}
				elseif ($type == 'a' || $type == 'A') {
					$converter = new ConvertRoman($i, TRUE);
					$a = $converter->result();
					if ($type == 'a') {
						$a = strtolower($a);
					}
					$list .= $pre."{$a}. {$li}\n";
				}
				else {
					$list .= $pre."{$i}. {$li}\n";
				}
			}
			else {
				$list .= $pre."- {$li}\n";
			}
		}
		// A workaround for a bug in the parser
		$list = preg_replace('/ +?([a-zA-Z0-9\-\.]+?) \n/is', '', $list);
		return $list;
	}
	function cb_plain_code ($matches) {
		global $lang;
		$pid = $this->noparse_id();

		list(,,$code) = $matches;
		$rows = explode("\n",$code);
		$code = $this->code_prepare($code);

		if (count($rows) > 1) {
			$a = 0;
			$code = '';
			$lines = strlen(count($rows));
			foreach ($rows as $row) {
				$a++;
				$code .= leading_zero($a, $lines).": {$row}\n";
			}

			$this->noparse[$pid] = "\n".$lang->phrase('bb_sourcecode')."\n-------------------\n{$code}-------------------\n";
		}
		else {
			$this->noparse[$pid] = $code;
		}
		return '<!PID:'.$pid.'>';
	}
	function strip_bbcodes ($text) {
		$this->cache_bbcode();
		$text = preg_replace('/(\r\n|\r|\n)/', "\n", $text);
		$text = preg_replace('/\[hide\](.+?)\[\/hide\]/is', '', $text);
		$text = preg_replace("~\[url={$this->url_regex2}\](.+?)\[\/url\]~is", "\\3 (\\1)", $text);

		$search = array(
			'[sub]','[/sub]',
			'[sup]','[/sup]',
			'[u]','[/u]',
			'[b]','[/b]',
			'[i]','[/i]',
			'[email]','[/email]',
			'[url]','[/url]',
			'[img]','[/img]',
			'[tt]', '[/tt]',
			'[table]', '[/table]',
			'[quote]', '[/quote]',
			'[edit]', '[/edit]',
			'[ot]', '[/ot]',
			'[hr]'
		);

		$replace = array_fill(0, count($search), '');
		$text = str_ireplace($search, $replace, $text);

		$text = preg_replace('/\[code(=\w+?)?\](.+?)\[\/code\]\n?/is', '\2', $text);

		while (preg_match('/\[list(?:=(a|A|I|i|OL|ol))?\](.+?)\[\/list\]/is',$text)) {
			$text = preg_replace('/\[list(?:=(a|A|I|i|OL|ol))?\](.+?)\[\/list\]/is', '\2', $text);
		}
		$text = preg_replace('/\[note=([^\]]+?)\](.+?)\[\/note\]/is', "\\2", $text);
		$text = preg_replace('/\[color=(\#?[0-9A-F]{3,6})\](.+?)\[\/color\]/is', "\\2", $text);
		$text = preg_replace('/\[align=(left|center|right|justify)\](.+?)\[\/align\]/is', "\\2", $text);
		$text = preg_replace('/\n?\[h=(middle|small|large)\](.+?)\[\/h\]\n?/is', "\n\\2\n", $text);
		$text = preg_replace('/\[size=(small|extended|large)\](.+?)\[\/size\]/is', "\\2", $text);

		while (preg_match('/\[quote=(.+?)\](.+?)\[\/quote\]/is',$text)) {
			$text = preg_replace('/\[quote=(.+?)\](.+?)\[\/quote\]\n?/is', "\n\\2\n", $text);
		}
		while (preg_match('/\[edit=(.+?)\](.+?)\[\/edit\]/is',$text)) {
			$text = preg_replace('/\[edit=(.+?)\](.+?)\[\/edit\]\n?/is', "\n\\2\n", $text);
		}

		$text = str_ireplace('[tab]', "	", $text);
		$text = str_ireplace('[reader]', $this->reader, $text);

		$text = preg_replace('/\[[^\/\r\n\[\]]+?\](.+?)\[\/[^\/\s\r\n\[\]]+?\]/is', "\\1", $text);

		$text = $this->parseDoc($text);
		$text = $this->nl2br($text, 'plain');
		$text = $this->censor($text);
		return $text;
	}

	// Possible values for $type: html, plain (with linebreaks)
	function parse ($text, $type = 'html') {
		global $lang, $my;
		$thiszm1=benchmarktime();
		$this->cache_bbcode();
		$this->noparse = array();
		$text = preg_replace("/(\r\n|\r|\n)/", "\n", $text);
		if($type == 'html' && (!empty($my->p['admin']) || ($my->id > 0 && $my->id == $this->author))) {
			$text = preg_replace('/\n?\[hide\](.+?)\[\/hide\]/is', '<br /><div class="bb_hide"><strong>'.$lang->phrase('bb_hidden_content').'</strong><span>\1</span></div>', $text);
		}
		else {
			$text = preg_replace('/\[hide\](.+?)\[\/hide\]/is', '', $text);
		}
		if ($type == 'plain') {
			$text = preg_replace("~\[url={$this->url_regex2}\](.+?)\[\/url\]~is", "\\3 (\\1)", $text);

			$search = array(
			'[sub]','[/sub]',
			'[sup]','[/sup]',
			'[u]','[/u]',
			'[b]','[/b]',
			'[i]','[/i]',
			'[email]','[/email]',
			'[url]','[/url]',
			'[img]','[/img]',
			'[tt]', '[/tt]'
			);
			$text = str_ireplace($search, '', $text);

			$text = empty($this->profile['disallow']['code']) ? preg_replace_callback('/\[code(=\w+?)?\](.+?)\[\/code\]/is', array(&$this, 'cb_plain_code'), $text) : $text;

			while (empty($this->profile['disallow']['list']) && preg_match('/\[list(?:=(a|A|I|i|OL|ol))?\](.+?)\[\/list\]/is',$text)) {
				$text = preg_replace_callback('/\[list(?:=(a|A|I|i|OL|ol))?\](.+?)\[\/list\]/is', array(&$this, 'cb_plain_list'), $text);
			}

			$text = preg_replace('/\[note=([^\]]+?)\](.+?)\[\/note\]/is', "\\1 (\\2)", $text);
			$text = preg_replace('/\[color=(\#?[0-9A-F]{3,6})\](.+?)\[\/color\]/is', "\\2", $text);
			$text = preg_replace('/\[align=(left|center|right|justify)\](.+?)\[\/align\]/is', "\\2", $text);
			$text = empty($this->profile['disallow']['h']) ? preg_replace('/\n?\[h=(middle|small|large)\](.+?)\[\/h\]\n?/is', "\\2", $text) : $text;
			$text = preg_replace('/\[size=(small|extended|large)\](.+?)\[\/size\]/is', "\\2", $text);

			while (preg_match('/\[quote=(.+?)\](.+?)\[\/quote\]/is',$text)) {
				$text = preg_replace('/\[quote=(.+?)\](.+?)\[\/quote\]\n?/is', "\n".$lang->phrase('bb_quote_by')." \\1:\n-------------------\n\\2\n-------------------\n", $text);
			}
			while (preg_match('/\[quote](.+?)\[\/quote\]/is',$text)) {
				$text = preg_replace('/\[quote](.+?)\[\/quote\]\n?/is', "\n".$lang->phrase('bb_quote')."\n-------------------\n\\1\n-------------------\n", $text);
			}
			while (empty($this->profile['disallow']['edit']) && preg_match('/\[edit\](.+?)\[\/edit\]/is',$text)) {
				$text = preg_replace('/\[edit\](.+?)\[\/edit\]\n?/is', "\n".$lang->phrase('bb_edit_author')."\n-------------------\n\\1\n-------------------\n", $text);
			}
			while (empty($this->profile['disallow']['edit']) && preg_match('/\[edit=(.+?)\](.+?)\[\/edit\]/is',$text)) {
				$text = preg_replace('/\[edit=(.+?)\](.+?)\[\/edit\]\n?/is', "\n".$lang->phrase('bb_edit_mod')." \\1:\n-------------------\n\\2\n-------------------\n", $text);
			}
			while (empty($this->profile['disallow']['ot']) && preg_match('/\[ot\](.+?)\[\/ot\]/is',$text)) {
				$text = preg_replace('/\[ot\](.+?)\[\/ot\]\n?/is', "\n".$lang->phrase('bb_offtopic')."\n-------------------\n\\1\n-------------------\n", $text);
			}
			$text = preg_replace('/\[table(=[^\]]+)?\](.+?)\[\/table\]\n?/is', "\n", $text); // ToDo: Plain Table

			$text = preg_replace('/(\[hr\]){1,}/is', "\n-------------------\n", $text);
			$text = str_ireplace('[tab]', "	", $text);

			$text = $this->customBB($text, $type);
		}
		else {
			$text = empty($this->profile['disallow']['code']) ? preg_replace_callback('/\[code(=(\w+?))?\](.+?)\[\/code\](\n?)/is', array(&$this, 'cb_hlcode'), $text) : $text;

			while (preg_match('/\[quote=(.+?)\](.+?)\[\/quote\]/is',$text, $values)) {
				$pid = $this->noparse_id();
				if (check_hp($values[1])) {
					$this->noparse[$pid] = '<a href="'.$values[1].'" target="_blank">'.$values[1].'</a>';
				}
				else {
					$this->noparse[$pid] = $values[1]; //"\\1";
				}
				$text = preg_replace('/\[quote=(.+?)\](.+?)\[\/quote\]\n?/is', "<div class='bb_quote'><strong>".$lang->phrase('bb_quote_by')." <!PID:{$pid}>:</strong><br /><blockquote>\\2</blockquote></div>", $text, 1);
			}

			$text = $this->ListWorkAround($text);

			$text = preg_replace_callback('/\[note=([^\]]+?)\](.+?)\[\/note\]/is', array(&$this, 'cb_note'), $text);

			$text = empty($this->profile['disallow']['img']) ? preg_replace_callback("~\[img\]([^?&=\[\]]+\.(png|gif|bmp|jpg|jpe|jpeg))\[\/img\]~is", array($this, 'cb_image'), $text) : $text;
			$text = preg_replace_callback("~\[img\]{$this->url_regex2}\[\/img\]~is", array(&$this, 'cb_plain_url'), $text); // Correct invalid image urls

			$text = preg_replace('/\[color=\#?([0-9A-F]{3,6})\](.+?)\[\/color\]/is', '<span style="color: #\1">\2</span>', $text);
			$text = preg_replace('/\[align=(left|center|right|justify)\](.+?)\[\/align\]/is', "<p style='text-align: \\1'>\\2</p>", $text);

			$text = preg_replace_callback("/\[email\]([a-z0-9\-_\.\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+?)\[\/email\]/is", array(&$this, 'cb_mail'), $text);
			$text = empty($this->profile['disallow']['h']) ? preg_replace_callback('/\n?\[h=(middle|small|large)\](.+?)\[\/h\]\n?/is', array(&$this, 'cb_header'), $text) : $text;
			$text = preg_replace_callback('/\[size=(small|extended|large)\](.+?)\[\/size\]/is', array(&$this, 'cb_size'), $text);

			while (preg_match('/\[quote](.+?)\[\/quote\]/is',$text)) {
				$text = preg_replace('/\[quote](.+?)\[\/quote\]\n?/is', "<div class='bb_quote'><strong>".$lang->phrase('bb_quote')."</strong><br /><blockquote>\\1</blockquote></div>", $text);
			}
			while (empty($this->profile['disallow']['edit']) && preg_match('/\[edit\](.+?)\[\/edit\]/is',$text)) {
				$text = preg_replace('/\[edit\](.+?)\[\/edit\]\n?/is', "<div class='bb_edit'><strong>".$lang->phrase('bb_edit_author')."</strong><br /><ins>\\1</ins></div>", $text);
			}
			while (empty($this->profile['disallow']['edit']) && preg_match('/\[edit=(.+?)\](.+?)\[\/edit\]/is',$text)) {
				$text = preg_replace('/\[edit=(.+?)\](.+?)\[\/edit\]\n?/is', "<div class='bb_edit'><strong>".$lang->phrase('bb_edit_mod')." \\1:</strong><br /><ins>\\2</ins></div>", $text);
			}
			while (empty($this->profile['disallow']['ot']) && preg_match('/\[ot\](.+?)\[\/ot\]/is',$text)) {
				$text = preg_replace('/\[ot\](.+?)\[\/ot\]\n?/is', "<div class='bb_ot'><strong>".$lang->phrase('bb_offtopic')."</strong><br /><span>\\1</span></div>", $text);
			}

			$text = preg_replace('/\[b\](.+?)\[\/b\]/is', "<strong>\\1</strong>", $text);
			$text = preg_replace('/\[i\](.+?)\[\/i\]/is', "<em>\\1</em>", $text);
			$text = preg_replace('/\[u\](.+?)\[\/u\]/is', "<u>\\1</u>", $text);
			$text = preg_replace('/\[sub\](.+?)\[\/sub\]/is', "<sub>\\1</sub>", $text);
			$text = preg_replace('/\[sup\](.+?)\[\/sup\]/is', "<sup>\\1</sup>", $text);
			$text = preg_replace('/\n?(\[hr\]){1,}\n?/is', "<hr />", $text);

			$text = preg_replace('/\[tt\](.+?)\[\/tt\]/is', "<tt>\\1</tt>", $text);

			$text = preg_replace_callback("~\[url\]{$this->url_regex2}\[\/url\]~is", array(&$this, 'cb_plain_url'), $text);
			$text = preg_replace_callback("~\[url={$this->url_regex2}\](.+?)\[\/url\]~is", array(&$this, 'cb_title_url'), $text);

			$text = preg_replace_callback('/\[table(=(\d+\%;head|head;\d+\%|\d+\%|head))?\]\n*(.+?)\n*\[\/table\]\n?/is', array(&$this, 'cb_table'), $text);

			// Old way of doing this, superseded by table bb code - $text = $this->tab2space($text);
			$text = str_ireplace("\t", "[tab]", $text); // Avoid conflicts with custom bb codes
			// Apply custom bb codes
			$text = $this->customBB($text, $type); // BEFORE Auto URL Parsing
			// Auto replace urls
			$text = preg_replace_callback("~([\t\r\n\x20\(\),\.:;\?!\<>\[\]]|^){$this->url_regex}([\t\r\n\x20\(\)\[\]<>]|$)~is", array(&$this, 'cb_auto_url'), $text);
			// Apply tabs finally
			$text = str_ireplace('[tab]', "&nbsp; &nbsp;&nbsp;", $text); // One normal whitespace to avoid really long lines

			if ($this->profile['useSmileys']) {
				$text = $this->parseSmileys($text);
			}
			$text = $this->wordwrap($text);
		}
		$text = str_ireplace('[reader]', $this->reader, $text);
		$text = $this->parseDoc($text);
		$text = $this->dict($text, $type);
		$text = $this->replace($text);
		$text = $this->nl2br($text, $type);
		$text = $this->replacePID($text);
		$text = $this->censor($text);
		$text = $this->highlight($text);
		$thiszm2=benchmarktime();
		$this->benchmark['bbcode'] += $thiszm2-$thiszm1;
		return $text;
	}

	function ListWorkAround($text) {
		if (empty($this->profile['disallow']['list'])) {
			$char = chr(5);
			$text = str_ireplace('[/list]', '[/list]'.$char, $text);
			$text = str_ireplace('[list', $char.'[list', $text);
			while (preg_match('/'.$char.'\[list(?:=(a|A|I|i|OL|ol))?\]([^'.$char.']+)\[\/list\]'.$char.'/is',$text, $treffer)) {
				$text = preg_replace_callback('/\n?'.$char.'\[list(?:=(a|A|I|i|OL|ol))?\]([^'.$char.']+)\[\/list\]'.$char.'\n?/is', array(&$this, 'cb_list'), $text);
			}
		}
		return $text;
	}

	function nl2br ($text, $type = 'html') {
		$text = str_ireplace('[br]', "\n", $text);
		if ($type == 'plain') {
			$text = str_replace("\n", " \n", $text); // Evtl. Leerzeichen oder nur Zeilenumbruch...
		}
		else {
			$text = str_replace("\n", "<br />\n", $text);
		}
		// Conver \r from custom bb-codes back to \n
		$text = str_replace("\r", "\n", $text);
		return $text;
	}
	function parseDoc ($text) {
		if ($this->profile['reduceEndChars'] == 1) {
			$text = preg_replace('/\!{2,}1{0,}/i', "!", $text);
			$text = preg_replace('/\?{2,}(&szlig;){0,}/i', "?", $text);
			$text = preg_replace('/\.{4,}/i', "...", $text);
		}
		if ($this->profile['reduceNL'] == 1) {
			$text = preg_replace("/\n{3,}/i", "\n\n", $text);
		}
		return $text;
	}
	function parseTitle ($topic) {
		$topic = str_replace("\t", ' ', $topic);
		$topic = $this->censor($topic);
		if($topic == strtoupper($topic) && $this->profile['topicuppercase'] == 1) {
			return ucwords(strtolower($topic));
		}
		else {
			return $topic;
		}
	}

	function cb_table($data, $type = 'html') {
		list(,,$args,$code) = $data;
		$table_content = array();
		$table_head = array();
		$table_rows = array();
		$table_cols = array();
		$bbcode_table = array(
			'width' => null,
			'head' => array(),
			'table' => array()
		);
		if (preg_match('~((\d+)\%)~', $args, $matches)) {
			if ($matches[2] <= 100) {
				$bbcode_table['width'] = $matches[1];
			}
		}
		$args = explode(';', strtolower($args));
		if (array_search('head', $args) === false) {
			$bbcode_table['head']['enabled'] = false;
		}
		else {
			$bbcode_table['head']['enabled'] = true;
		}

		do {
			$code = preg_replace("~\n\n~", "\n", $code);
		} while (preg_match("~\n\n~", $code));

		$table_content = explode("\n",$code);
		$bbcode_table['table']['rows'] = count($table_content);
		for($i=0;$i<$bbcode_table['table']['rows'];$i++){
			// Testing for old style behaviour
			if (stripos($table_content[$i], '[tab]') === false) {
				$table_content[$i] = explode('|',$table_content[$i]);
			}
			else {
				$table_content[$i] = preg_split('~\[tab\]~i',$table_content[$i]);
			}
		}
		$bbcode_table['table']['cols'] = count($table_content[0]);

		if($bbcode_table['table']['rows']+$bbcode_table['table']['cols']==2) {
			return $code;
		}

		$bbcode_table['head']['enabled'] = ($bbcode_table['head']['enabled'] && $bbcode_table['table']['rows']>1) ? true : false;

		if($bbcode_table['head']['enabled'] == true){
			for($i=0;$i<($bbcode_table['table']['cols']);$i++){
				if(!empty($table_content[0][$i]) || (isset($table_content[0][$i]) && $table_content[0][$i] == 0)){
					$table_head[$i] = $table_content[0][$i];
				}
				else {
					$table_head[$i] = '&nbsp;';
				}
			}
			for($i=1;$i<$bbcode_table['table']['rows'];$i++){
				$table_content[($i-1)] = $table_content[$i];
			}
			$bbcode_table['table']['rows']--;
		}
		$table_rows = array();
		for($i=0;$i<$bbcode_table['table']['rows'];$i++){
			for($j=0;$j<$bbcode_table['table']['cols'];$j++){
				if(empty($table_content[$i][$j])){
					$table_rows[$i][$j] = '&nbsp;';

				}
				else{
					$table_rows[$i][$j] = $table_content[$i][$j];
				}
			}
		}

		$style = ' style="width:'.floor(100/$bbcode_table['table']['cols']).'%;"';

		if($bbcode_table['head']['enabled'] == true){
			$table_head = '<tr><th'.$style.'>'.implode('</th><th'.$style.'>',$table_head).'</th></tr>';
		}
		else{
			$table_head = '';
		}

		for($i=0;$i<$bbcode_table['table']['rows'];$i++){
			$table_rows[$i] = '<td'.iif($bbcode_table['head']['enabled'], $style).'>'.implode('</td><td'.iif($bbcode_table['head']['enabled'], $style).'>', $table_rows[$i]).'</td>';
			$table_rows[$i] = '<tr>'.$table_rows[$i].'</tr>';
		}

		$table_rows = implode('',$table_rows);
		$table_html = '<table class="bb_table"';
		if ($bbcode_table['width'] != null){
			$table_html .= ' style="width:'.$bbcode_table['width'].';"';
		}
		$table_html .= '>'.$table_head.$table_rows.'</table>';
		return $table_html;
	}

	/**
	 * Converts tabs to the appropriate amount of spaces while preserving formatting
	 *
	 * Currently not used!
	 *
	 * @deprecated Since Viscacha 0.8 Gold
	 * @author	  Aidan Lister <aidan@php.net>
	 * @version	 1.2.0
	 * @param	   string	$text	 The text to convert
	 * @param	   int	   $spaces   Number of spaces per tab column
	 * @return	  string	The text with tabs replaced
	 */
	function tab2space ($text, $spaces = 4) {
		$char = chr(7);
		$lines = explode("\n", $text);
		foreach ($lines as $line) {
			while (false !== $tab_pos = strpos($line, "\t")) {
				$start  = substr($line, 0, $tab_pos);
				$min	= $tab_pos/$spaces - floor($tab_pos/$spaces);
				$tab	= str_repeat($char, (1-$min)*$spaces);
				$end	= substr($line, $tab_pos+1);
				$line   = $start . $tab . $end;
			}
			$result[] = $line;
		}
		$result = str_replace($char.$char, '&nbsp; ', implode("\n", $result));
		return str_replace($char, '&nbsp;', $result);
	}
	function getBenchmark($type='bbcode') {
		return round($this->benchmark[$type], 5);
	}
	function parseSmileys ($text, $type = 'html') {
		$start = benchmarktime();
		if ($type != 'plain') {
			$this->cache_smileys();
			foreach ($this->smileys as $smiley) {
				// Old way to replace smileys - use this when you have problems with smileys
				// $text = str_replace(' '.$smiley['search'], ' <img src="'.$smiley['replace'].'" border="0" alt="'.$smiley['desc'].'" />', $text);
				if (strpos($text, $smiley['search']) !== false) {
					$pattern = '~(\r|\n|\t|\s|\>|\<|^)'.preg_quote($smiley['search'], '~').'(\r|\n|\t|\s|\>|\<|$)~s';
					while (preg_match($pattern, $text)) {
						$text = preg_replace(
							$pattern,
							'\1<img src="'.$smiley['replace'].'" border="0" alt="'.$smiley['desc'].'" />\2',
							$text
						);
					}
				}
			}
		}
		$this->benchmark['smileys'] += benchmarktime() - $start;
		return $text;
	}
	function getSmileys () {
		$this->cache_smileys();
		return $this->smileys;
	}
	function getCustomBB () {
		$this->cache_bbcode();
		return $this->custombb;
	}
	function existsProfile($name) {
		return isset($this->cfg[$name]);
	}
	function setProfile ($name = 'standard', $new = SP_CHANGE) {
		if ($new == SP_COPY) {
			$this->cfg[$name] = array();
			foreach ($this->profile as $key => $value) {
				if ($key == 'name') {
					$this->cfg[$name]['name'] = $name;
				}
				elseif ($key == 'disallow') {
					$this->cfg[$name]['disallow'] = array(
					'img' => FALSE,
					'code' => FALSE,
					'list' => FALSE,
					'edit' => FALSE,
					'ot' => FALSE,
					'h' => FALSE
					);
				}
				else {
					$this->cfg[$name][$key] = $value;
				}
			}
		}
		elseif ($new == SP_NEW) {
			$this->cfg[$name] = array(
				'name' => $name,
				'wordwrap' => 1,
				'wordwrap_char' => '-',
				'wordwrap_wordlength' => 70,
				'useSmileys' => 0,
				'SmileyUrl' => '',
				'useDict' => 0,
				'useReplace' => 1,
				'useCensor' => 1,
				'reduceEndChars' => 1,
				'reduceNL' => 1,
				'reduceUrl' => 0,
				'topicuppercase' => 1,
				'reducelength' => 60,
				'reducesep' => ' ... ',
				'resizeImg' => 0,
				'highlight_class' => 0,
				'highlight' => array(),
				'disallow' => array(
					'img' => false,
					'code' => false,
					'list' => false,
					'edit' => false,
					'ot' => false,
					'h' => false
				)
			);
		}
		unset($this->profile);
		$this->profile = &$this->cfg[$name];
	}
	function setFunc($func) {
		$this->profile['disallow'][$func] = true;
	}
	function setSmileys ($use = 1) {
		$this->profile['useSmileys'] = $use;
		if (!isset($this->profile['SmileyUrl'])) {
			$this->profile['SmileyUrl'] = '';
		}
	}
	function setSmileyDir ($url = '') {
		$this->profile['SmileyUrl'] = $url;
	}
	function setMisc ($dict = 1, $censor = 1, $resizeimg = 0) {
		$this->profile['useDict'] = $dict;
		$this->profile['useCensor'] = $censor;
		$this->profile['resizeImg'] = $resizeimg;
	}
	function setReplace ($use = 1) {
		$this->profile['useReplace'] = $use;
	}
	function setWordwrap ($use = 1, $wordlength = 70, $char = ' ') {
		$this->profile['wordwrap'] = $use;
		$this->profile['wordwrap_wordlength'] = $wordlength;
		$this->profile['wordwrap_char'] = $char;
	}
	function setDoc ($reduce_endchars = 1, $reduce_nl = 1, $topicupper = 1) {
		$this->profile['reduceEndChars'] = $reduce_endchars;
		$this->profile['reduceNL'] = $reduce_nl;
		$this->profile['topicuppercase'] = $topicupper;
	}
	function setURL ($reduce_url, $maxurllength, $maxurltrenner) {
		$this->profile['reduceUrl'] = $reduce_url;
		$this->profile['reducelength'] = $maxurllength;
		$this->profile['reducesep'] = $maxurltrenner;
	}
	function setHighlight ($class, $words) {
		$this->profile['highlight_class'] = $class;
		$this->profile['highlight'] = $words;
	}
	function ResizeImgSize() {
		return $this->profile['resizeImg'];
	}
	function highlight($text) {
		if (isset($this->profile['highlight']) && count($this->profile['highlight']) > 0) {
			$class = htmlspecialchars($this->profile['highlight_class'], ENT_QUOTES);
			foreach ($this->profile['highlight'] as $token) {
				if (strxlen($token) > 2) {
					$token = preg_quote($token, '#');
					$text = str_replace(
						'\"',
						'"',
						substr(
							preg_replace(
								'#(\>(((?>([^><]+|(?R)))*)\<))#se',
								"preg_replace('#\b({$token})\b#i', '<span class=\"{$class}\">\\\\1</span>', '\\0')",
								">{$text}<"
							),
							1,
							-1
						)
					);
				}
			}
		}
		return $text;
	}
	function dict($text, $type = 'html') {
		if ($this->profile['useDict'] == 1) {
			$this->cache_bbcode();
			foreach ($this->bbcodes['word'] as $word) {
				$this->index++;
				$word['search'] = trim($word['search']);
				if ($type == 'plain') {
					$text = str_replace(
						'\"',
						'"',
						substr(
							preg_replace(
								'#(\>(((?>([^><]+|(?R)))*)\<))#se',
								"preg_replace(
									'#\b({$word['search']})\b#i',
									'\\\\1 ({$word['replace']})',
									'\\0'
								)",
								'>' . $text . '<',
								1 // Only the first occurance
							)
							, 1,
							-1
						)
					);
				}
				else {
					$word['search'] = htmlspecialchars($word['search']);
					$text = str_replace(
						'\"',
						'"',
						substr(
							preg_replace(
								'#(\>(((?>([^><]+|(?R)))*)\<))#se',
								"preg_replace(
									'#\b({$word['search']})\b#i',
									'<acronym title=\"{$word['replace']}\" id=\"menu_tooltip_{$this->index}\" onmouseover=\"RegisterTooltip({$this->index})\">\\\\1</acronym><div class=\"tooltip\" id=\"popup_tooltip_{$this->index}\"><span id=\"header_tooltip_{$this->index}\"></span><div class=\"tooltip_body\">{$word['desc']}</div></div>',
									'\\0'
								)",
								'>' . $text . '<',
								1 // Only the first occurance
							),
							1,
							-1
						)
					);
				}
			}
		}
		return $text;
	}
	function censor ($text) {
		$this->cache_bbcode();
		if ($this->profile['useCensor'] == 2) {
			foreach ($this->bbcodes['censor'] as $word) {
				$letters = str_split($word['search']);
				$word['search'] = array();
				foreach ($letters as $letter) {
					$word['search'][] = preg_quote($letter, '~');
				}
				$word['search'] = implode("(\s|\.|\[[^\]]+?\])?", $word['search']);
				$text = preg_replace("~".$word['search']."~is", $word['replace'], $text);
			}
		}
		elseif ($this->profile['useCensor'] == 1) {
			foreach ($this->bbcodes['censor'] as $word) {
				$text = str_ireplace($word['search'], $word['replace'], $text);
			}
		}
		return $text;
	}
	function replace ($text) {
		$this->cache_bbcode();
		if (isset($this->profile['useReplace']) && $this->profile['useReplace'] == 1) {
			foreach ($this->bbcodes['replace'] as $word) {
				$text = str_ireplace($word['search'], $word['replace'], $text);
			}
		}
		return $text;
	}
	function cbb_helper($matches) {
		if ($this->currentCBB != null) {
			$index = $this->currentCBB['twoparams'] ? 2 : 1;
			$bbcodereplacement = preg_replace('~\{param(:(?:\\\}|[^\}])+)?\}~i', $matches[$index], $this->currentCBB['bbcodereplacement']);
			if ($this->currentCBB['twoparams']) {
				$pid = $this->noparse_id();
				$this->noparse[$pid] = $matches[1];
				$bbcodereplacement = preg_replace('~\{option(:(?:\\\}|[^\}])+)?\}~i', "<!PID:{$pid}>", $bbcodereplacement);
			}
			return $bbcodereplacement;
		}
		else {
			return $matches[0];
		}
	}
	function customBB ($text, $type='html') {
		$this->getCustomBB();
		foreach ($this->custombb as $re) {
			if ($type == 'plain') {
				$re['bbcodereplacement'] = strip_tags($re['bbcodereplacement']);
			}
			$this->currentCBB = $re;
			$text = preg_replace_callback('~'.$re['bbregexp'].'~is', array(&$this, 'cbb_helper'), $text);
			$this->currentCBB = null;
		}
		return $text;
	}
	function wordwrap ($text, $length = FALSE) {
		if (empty($text) || $this->profile['wordwrap'] == 0) {
			return $text;
		}
		if ($length == FALSE) {
			$length = $this->profile['wordwrap_wordlength'];
		}
		$text = preg_replace("~([^\n\r\s&\./<>\[\]\\\]{".intval($length)."})~i", "\\1".$this->profile['wordwrap_char'], $text);
		return $text;
	}
	function cache_bbcode () {
		global $scache;
		if ($this->bbcodes == null) {
			$cache = $scache->load('bbcode');
			$this->bbcodes = $cache->get();
			$scache->unload('bbcode');
		}
		if ($this->custombb == null) {
			$cache = $scache->load('custombb');
			$this->custombb = $cache->get();
			$scache->unload('custombb');
		}
	}
	function cache_smileys () {
		if ($this->smileys == null) {
			global $scache;
			$cache = $scache->load('smileys');
			$cache->seturl($this->profile['SmileyUrl']);
			$this->smileys = $cache->get();
		}
	}

	function getEditorArea ($id, $content = '', $taAttr = '', $maxlength = null, $disable = array()) {
		global $tpl, $lang, $scache, $config;
		if ($maxlength == null) {
			$maxlength = $config['maxpostlength'];
		}
		if (!is_array($disable)) {
			$disable = array($disable);
		}

		$lang->group("bbcodes");

		$taAttr = ' '.trim($taAttr);

		$cbb = $this->getCustomBB();
		foreach ($cbb as $key => $bb) {
			if (empty($bb['buttonimage'])) {
				unset($cbb[$key]);
				continue;
			}
			$cbb[$key]['title'] = htmlspecialchars($bb['title']);
			if ($bb['twoparams']) {
				$cbb[$key]['href'] = "InsertTags('{$id}', '[{$bb['bbcodetag']}=]','[/{$bb['bbcodetag']}]');";
			}
			else {
				$cbb[$key]['href'] = "InsertTags('{$id}', '[{$bb['bbcodetag']}]','[/{$bb['bbcodetag']}]');";
			}
		}

		$codelang = $scache->load('syntaxhighlight');
		$clang = $codelang->get();

		$this->cache_smileys();
		$smileys = array(0 => array(), 1 => array());
		foreach ($this->smileys as $bb) {
			if ($bb['show'] == 1) {
				$smileys[1][] = $bb;
			}
			else {
				$smileys[0][] = $bb;
			}
		}

		$tpl->globalvars(compact("id", "content", "taAttr", "cbb", "clang", "smileys", "maxlength", "disable"));
		return $tpl->parse("main/bbhtml");
	}

	function replaceTextOnce($original, $newindex) {
		global $lang;
		$lang->assign('originalid', $original);
		return $lang->get_text($newindex);
	}
}

function BBProfile(&$bbcode, $profile = 'standard') {
	global $config, $my, $lang;
	if (!$bbcode->existsProfile($profile)) {
		if ($config['resizebigimg'] == 0) {
			$config['resizebigimgwidth'] = 0;
		}

		$lang->group("bbcodes");

		if ($profile == 'signature') {
			$bbcode->setProfile('signature', SP_NEW);
			$bbcode->setProfile($profile, SP_NEW);
			$bbcode->setMisc($config['dictstatus'], $config['censorstatus'], $config['resizebigimgwidth']);
			$bbcode->setWordwrap($config['wordwrap'], $config['maxwordlength'], $config['maxwordlengthchar']);
			$bbcode->setDoc($config['reduce_endchars'], $config['reduce_nl'], $config['topicuppercase']);
			$bbcode->setURL($config['reduce_url'], $config['maxurllength'], $config['maxurltrenner']);
			if (isset($my->name)) {
				$bbcode->setName($my->name);
			}
			$bbcode->setSmileyDir($config['smileyurl']);
			$bbcode->setSmileys(1);
			$bbcode->setReplace($config['wordstatus']);
			// Disallow some bb-codes
			if ($config['sig_bbimg'] == 1) {
				$bbcode->setFunc('img');
			}
			if ($config['sig_bbcode'] == 1) {
				$bbcode->setFunc('code');
			}
			if ($config['sig_bblist'] == 1) {
				$bbcode->setFunc('list');
			}
			if ($config['sig_bbedit'] == 1) {
				$bbcode->setFunc('edit');
			}
			if ($config['sig_bbot'] == 1) {
				$bbcode->setFunc('ot');
			}
			if ($config['sig_bbh'] == 1) {
				$bbcode->setFunc('h');
			}
		}
		else {
			$bbcode->setProfile($profile, SP_NEW);
			$bbcode->setMisc($config['dictstatus'], $config['censorstatus'], $config['resizebigimgwidth']);
			$bbcode->setWordwrap($config['wordwrap'], $config['maxwordlength'], $config['maxwordlengthchar']);
			$bbcode->setDoc($config['reduce_endchars'], $config['reduce_nl'], $config['topicuppercase']);
			$bbcode->setURL($config['reduce_url'], $config['maxurllength'], $config['maxurltrenner']);
			if (isset($my->name)) {
				$bbcode->setName($my->name);
			}
			$bbcode->setSmileyDir($config['smileyurl']);
		}
	}
	else {
		$bbcode->setProfile($profile, SP_CHANGE);
	}
}

define('TOOLBAR_STATUS', 1);
define('TOOLBAR_FORMATTING', 2);
define('TOOLBAR_SMILEYS', 3);

?>