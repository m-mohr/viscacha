<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2007  Matthias Mohr, MaMo Net

	Author: Matthias Mohr
	Publisher: http://www.viscacha.org
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

	function BBCode ($profile = 'viscacha') {
	    $this->benchmark = array(
	    	'smileys' => 0,
	    	'bbcode' => 0
	    );
		$this->smileys = null;
		$this->bbcodes = null;
		$this->custombb = null;
		$this->profile = '';
		$this->cfg = array();
		$this->reader = '';
		$this->noparse = array();
		$this->pid = 0;
		$this->author = -1;

		if (!class_exists('ConvertRoman')) {
			include('classes/class.convertroman.php');
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
	    $liarray = preg_split('/(\n\s?-\s|\[\*\])/',$pattern);
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
			$code = str_replace("  ", "&nbsp;&nbsp;", $code);
		}
		else {
			$code = str_replace(" ", "&nbsp;", $code);
		}
		return $code;
	}
	function cb_code ($matches) {
		global $lang;
		$pid = $this->noparse_id();
		list(,$code,$nl) = $matches;

	    $rows = explode("\n",$code);
	    $code = $this->code_prepare($code, (count($rows) <= 1));

	    if (count($rows) > 1) {
	    	$a = 0;
	    	$aa = array();
		    foreach ($rows as $row) {
		        $a++;
		        $aa[] = "$a:&nbsp;";
		    }

			$aa = implode("<br />",$aa);

		    $this->noparse[$pid] = '<strong class="bb_blockcode_header">'.$lang->phrase('bb_sourcecode').'</strong><div class="bb_blockcode"><table><tr><td width="1%">'.$aa.'</td><td width="99%">'.$this->nl2br($code).'</td></tr></table></div>';
		}
		else {
			$this->noparse[$pid] = '<code class="bb_inlinecode">'.$code.'</code>';
			if (!empty($nl)) {
				$this->noparse[$pid] .= '<br />';
			}
		}
	    return '<!PID:'.$pid.'>';
	}
	function cb_hlcode ($matches) {
		global $lang;
		$pid = $this->noparse_id();
		list(, $sclang, $code, $nl) = $matches;


	    $rows = explode("\n",$code);

	    if (count($rows) > 1) {
	    	$code = $code2 = $this->code_prepare($code);
		    $a = 0;
		    $aa = array();
			$unique = md5($code);

			$cache = new CacheItem($unique, 'cache/geshicode/');
			if ($cache->exists() == false) {
				$export = array(
				'language' => $sclang,
				'source' => $code
				);
			    $cache->set($export);
			    $cache->export();
			}

		    foreach ($rows as $row) {
		        $a++;
		        $aa[] = "$a:&nbsp;";
		    }

			$aa = implode("<br />",$aa);

		    $this->noparse[$pid] = '<strong class="bb_blockcode_header"><a target="_blank" href="popup.php?action=hlcode&amp;fid='.$unique.SID2URL_x.'">'.$lang->phrase('bb_ext_sourcecode').'</a></strong><div class="bb_blockcode"><table><tr><td width="1%">'.$aa.'</td><td width="99%">'.$this->nl2br($code2).'</td></tr></table></div>';
		}
		else {
			$code2 = $this->code_prepare($code, (count($rows) <= 1));
			$this->noparse[$pid] = '<code class="bb_inlinecode">'.$code2.'</code>';
			if (!empty($nl)) {
				$this->noparse[$pid] .= '<br />';
			}
		}
		return '<!PID:'.$pid.'>';
	}
	function cb_mail ($email) {
		global $lang;
		list(,$email) = $email;
		$html = '<img alt="'.$lang->phrase('bbcodes_email').'" src="images.php?action=textimage&amp;text='.base64_encode($email).'&amp;enc=1" border="0" />';
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
		$pid = $this->noparse_id();
		$o = "<acronym title=\"<!PID:".$pid.">\">{$word}</acronym>";
		$this->noparse[$pid] = $desc;
		return $o;
	}
	function cb_auto_url ($matches) {
		list(,$url,,,,,$chop) = $matches;
		return $this->cb_url($url, false, true, $chop);
	}
	function cb_title_url ($matches) {
		list(,$url,,$title) = $matches;
		return $this->cb_url($url, $title);
	}
	function cb_url ($url, $title = false, $img = false, $chop = '') {
		global $config;
		if (is_array($url)) {
			list(,$url) = $url;
		}

		if ($img == true && (preg_match("~(\[/?[\w\d]{1,10}\])~", $chop) || preg_match("/(([^?&=\[\]]+?)\.(png|gif|bmp|jpg|jpe|jpeg))/is", $url))) {
			return $url.$chop;
		}

		if (strtolower(substr($url, 0, 4)) == 'www.') {
			$url = "http://{$url}";
		}

		$specialchars = array('&quot;','&gt;','&lt;','&#039;');
		foreach ($specialchars as $char) {
			$full = $url.$chop;
			if (($pos = strpos($full, $char)) !== false) {
				$url = substr($full, 0, $pos);
				$chop = substr($full, $pos);
				break;
			}
		}

		$pid = $this->noparse_id();
		$this->noparse[$pid] = $url;

		if ($title != false) {
		    $ahref = '<a href="<!PID:'.$pid.'>" target="_blank">'.$title.'</a>';
		    return $ahref;
		}
		elseif ($this->profile['reduceUrl'] == 1 && strxlen($url) >= $config['maxurllength']) {
			$prefix = ceil($config['maxurllength']/5);
			$suffix = strpos($url, '/', 8);
			if ($suffix === false) {
			   $suffix = ceil($config['maxurllength']/3);
			}
			$newurl = substr($url, 0, $suffix+1).$config['maxurltrenner'].substr($url, -$prefix);
			$pid2 = $this->noparse_id();
			$this->noparse[$pid2] = $newurl;
			$ahref = '<a href="<!PID:'.$pid.'>" target="_blank"><!PID:'.$pid2.'></a>';
		}
		else{
		   $ahref = '<a href="<!PID:'.$pid.'>" target="_blank"><!PID:'.$pid.'></a>';
		}

	    return $ahref.$chop;
	}
	function cb_pdf_list ($matches) {
		list(, $type, $pattern) = $matches;
	    $liarray = preg_split('/(\n\s?-\s|\[\*\])/',$pattern);
	    $list = '<br>';
	    $i = 0;
	    $pre = '&nbsp;&nbsp;';
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
					$list .= $pre."{$a}.&nbsp;{$li}<br>";
		        }
		        elseif ($type == 'a' || $type == 'A') {
					$converter = new ConvertRoman($i, TRUE);
					$a = $converter->result();
					if ($type == 'a') {
						$a = strtolower($a);
					}
					$list .= $pre."{$a}.&nbsp;{$li}<br>";
		        }
		        else {
		            $list .= $pre."{$i}.&nbsp;{$li}<br>";
		        }
		    }
		    else {
		        $list .= $pre."-&nbsp;{$li}<br>";
		    }
	    }
	    // A workaround for a bug in the parser
	    $list = preg_replace('/(&nbsp;)+?([a-zA-Z0-9\-\.]+?)&nbsp;<br>/is', '', $list);
	    return $list;
	}
	function cb_pdf_code ($matches) {
		global $lang;
		$pid = $this->noparse_id();

		list(,,$code,$nl) = $matches;
	    $rows = explode("\n",$code);
		$code = $this->code_prepare($code);

	    if (count($rows) > 1) {
	    	$a = 0;
	    	$code = '';
	    	$lines = strlen(count($rows));
		    foreach ($rows as $row) {
		        $a++;
		        $code .= "<tt>".leading_zero($a, $lines)."</tt>: <code>{$row}</code><br>";
		    }

		    $this->noparse[$pid] = '<br><b>'.$lang->phrase('bb_sourcecode').'</b><br>'.htmlentities($code);
		}
		else {
			$this->noparse[$pid] = '<code>'.htmlentities($code).'</code>';
			if (!empty($nl)) {
				$this->noparse[$pid] .= '<br>';
			}
		}
	    return '<!PID:'.$pid.'>';
	}
	function cb_pdf_header ($matches) {
		list(,$size,$content) = $matches;
		if ($size == 'small') {
			$level = 3;
		}
		elseif ($size == 'large') {
			$level = 1;
		}
		else {
			$level = 2;
		}
		$o = "<h{$level}>{$content}</h{$level}>";
		return $o;
	}
	function cb_pdf_size ($matches) {
		list(,$size,$content) = $matches;
		if ($size != 'extended') {
			if ($size == 'small') {
				$fontsize = 7;
			}
			elseif ($size == 'large') {
				$fontsize = 9;
			}
			else {
				$fontsize = 8;
			}
			$content = "<font size=\"{$fontsize}\">{$content}</font>";
		}

		return $content;
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
		$text = preg_replace("~\[url=((telnet://|callto://|irc://|teamspeak://|http://|https://|ftp://|www.|mailto:|ed2k://|\w+?.\w{2,7})[a-z0-9;\/\?:@=\&\$\-_\.\+!\*'\(\),\~%#]+?)\](.+?)\[\/url\]~is", "\\3", $text);

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

		$text = str_ireplace('[tab]', "    ", $text);
		$text = str_ireplace('[reader]', $this->reader, $text);

		$text = preg_replace('/\[[^\/\r\n\[\]]+?\](.+?)\[\/[^\/\s\r\n\[\]]+?\]/is', "\\1", $text);

		$text = $this->parseDoc($text);
		$text = $this->nl2br($text, 'plain');
		$text = $this->censor($text);
		return $text;
	}

	// Possible values for $type: html, pdf, plain (with linebreaks)
	function parse ($text, $type = 'html') {
		global $lang, $my;
		$thiszm1=benchmarktime();
		$this->cache_bbcode();
		$this->noparse = array();
		$text = preg_replace('/(\r\n|\r|\n)/', "\n", $text);
		if ($type != 'pdf') {
			$text = str_replace('$', '&#36;', $text);
		}
		if($type != 'pdf' && $type != 'plain' && ((isset($my->p['admin']) && $my->p['admin'] == 1) || ($my->id > 0 && $my->id == $this->author))) {
		    $text = preg_replace('/\n?\[hide\](.+?)\[\/hide\]/is', '<br /><div class="bb_hide"><strong>'.$lang->phrase('bb_hidden_content').'</strong><span>\1</span></div>', $text);
		}
		else {
		    $text = preg_replace('/\[hide\](.+?)\[\/hide\]/is', '', $text);
		}
		if ($type == 'plain') {
			$text = preg_replace("~\[url=((telnet://|callto://|irc://|teamspeak://|http://|https://|ftp://|www.|mailto:|ed2k://|\w+?.\w{2,7})[a-z0-9;\/\?:@=\&\$\-_\.\+!\*'\(\),\~%#]+?)\](.+?)\[\/url\]~is", "\\3 (\\1)", $text);

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
			'[table]', '[/table]'
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

			$text = preg_replace('/(\[hr\]){1,}/is', "\n-------------------\n", $text);
			$text = str_ireplace('[tab]', "    ", $text);
		}
		elseif ($type == 'pdf') {
			$text = empty($this->profile['disallow']['code']) ? preg_replace_callback('/\[code(=\w+?)?\](.+?)\[\/code\](\n?)/is', array(&$this, 'cb_pdf_code'), $text) : $text;
			while (empty($this->profile['disallow']['list']) && preg_match('/\[list(?:=(a|A|I|i|OL|ol))?\](.+?)\[\/list\]/is',$text)) {
				$text = preg_replace_callback('/\n?\[list(?:=(a|A|I|i|OL|ol))?\](.+?)\[\/list\]\n?/is', array(&$this, 'cb_pdf_list'), $text);
			}
			$text = preg_replace('/\[note=([^\]]+?)\](.+?)\[\/note\]/is', "\\1 (<i>\\2</i>)", $text);

			$text = preg_replace("~\[url\]((telnet://|callto://|irc://|teamspeak://|http://|https://|ftp://|www.|mailto:|ed2k://|\w+?.\w{2,7})+:\/\/[a-z0-9;\/\?:@=\&\$\-_\.\+!\*'\(\),\~%#]+?)\[\/url\]~is", "<a href=\"\\1\">\\1</a>", $text);
			$text = preg_replace("~\[url=((telnet://|callto://|irc://|teamspeak://|http://|https://|ftp://|www.|mailto:|ed2k://|\w+?.\w{2,7})[a-z0-9;\/\?:@=\&\$\-_\.\+!\*'\(\),\~%#]+?)\](.+?)\[\/url\]~is", "<a href=\"\\1\">\\3</a>", $text);
			$text = empty($this->profile['disallow']['img']) ? preg_replace("/\[img\](([^?&=\[\]]+?)\.(png|gif|bmp|jpg|jpe|jpeg))\[\/img\]/is", "<img src=\"\\1\">", $text) : $text;
			$text = preg_replace("/\[img\](.+?)\[\/img\]/is", "<a href=\"\\1\">\\1</a>", $text); // Correct incorrect urls

			$text = preg_replace('/\[color=\#?([0-9A-F]{3,6})\](.+?)\[\/color\]/is', "<font color=\"#\\1\">\\2</font>", $text);
			$text = preg_replace('/\[align=(left|center|right|justify)\](.+?)\[\/align\]/is', "<p align=\"\\1\">\\2</p>", $text);

			$text = preg_replace("/\[email\]([a-z0-9\-_\.\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+?)\[\/email\]/is", "<a href=\"mailto:\\1\">\\1</a>", $text);
			$text = empty($this->profile['disallow']['h']) ? preg_replace_callback('/\n?\[h=(middle|small|large)\](.+?)\[\/h\]\n?/is', array(&$this, 'cb_pdf_header'), $text) : $text;
			$text = preg_replace_callback('/\[size=(small|extended|large)\](.+?)\[\/size\]/is', array(&$this, 'cb_pdf_size'), $text);

			while (preg_match('/\[quote=(.+?)\](.+?)\[\/quote\]/is',$text)) {
				$text = preg_replace('/\[quote=(.+?)\](.+?)\[\/quote\]\n?/is', "<br><b>".$lang->phrase('bb_quote_by')." \\1:</b><hr><i>\\2</i><hr>", $text);
			}
			while (preg_match('/\[quote](.+?)\[\/quote\]/is',$text)) {
				$text = preg_replace('/\[quote](.+?)\[\/quote\]\n?/is', "<br><b>".$lang->phrase('bb_quote')."</b><hr><i>\\1</cite></i><hr>", $text);
			}
			while (empty($this->profile['disallow']['edit']) && preg_match('/\[edit\](.+?)\[\/edit\]/is',$text)) {
				$text = preg_replace('/\[edit\](.+?)\[\/edit\]\n?/is', "<br><b>".$lang->phrase('bb_edit_author')."</b><hr>\\1<hr>", $text);
			}
			while (empty($this->profile['disallow']['edit']) && preg_match('/\[edit=(.+?)\](.+?)\[\/edit\]/is',$text)) {
				$text = preg_replace('/\[edit=(.+?)\](.+?)\[\/edit\]\n?/is', "<br><b>".$lang->phrase('bb_edit_mod')." \\1:</b><hr>\\2<hr>", $text);
			}
			while (empty($this->profile['disallow']['ot']) && preg_match('/\[ot\](.+?)\[\/ot\]/is',$text)) {
				$text = preg_replace('/\[ot\](.+?)\[\/ot\]\n?/is', "<br><b>".$lang->phrase('bb_offtopic')."</b><hr><span style=\"color: #999999\" size=\"7\">\\1</span><hr>", $text);
			}

			$text = preg_replace('/\[b\](.+?)\[\/b\]/is', "<b>\\1</b>", $text);
			$text = preg_replace('/\[i\](.+?)\[\/i\]/is', "<i>\\1</i>", $text);
			$text = preg_replace('/\[u\](.+?)\[\/u\]/is', "<u>\\1</u>", $text);
			$text = preg_replace('/\[tt\](.+?)\[\/tt\]/is', "<tt>\\1</tt>", $text);
			$text = preg_replace('/\n?(\[hr\]){1,}\n?/is', "<hr>", $text);

			$text = preg_replace('/\[sub\](.+?)\[\/sub\]/is', "<sub>\\1</sub>", $text);
			$text = preg_replace('/\[sup\](.+?)\[\/sup\]/is', "<sup>\\1</sup>", $text);

			$text = str_ireplace('[tab]', "\t", $text);
			$text = preg_replace_callback('/\[table(=[^\]]+)?\](.+?)\[\/table\]\n?/is', array(&$this, 'cb_plain_table'), $text);

			$text = $this->tab2space($text);
			$text = $this->parseSmileys($text);
		}
		else {
			$text = empty($this->profile['disallow']['code']) ? preg_replace_callback('/\[code\](.+?)\[\/code\](\n?)/is', array(&$this, 'cb_code'), $text) : $text;
			$text = empty($this->profile['disallow']['code']) ? preg_replace_callback('/\[code=(\w+?)\](.+?)\[\/code\](\n?)/is', array(&$this, 'cb_hlcode'), $text) : $text;

			if (empty($this->profile['disallow']['list'])) {
				$char = chr(5);
				$text = str_ireplace('[/list]', '[/list]'.$char, $text);
				$text = str_ireplace('[list', $char.'[list', $text);
				while (preg_match('/'.$char.'\[list(?:=(a|A|I|i|OL|ol))?\]([^'.$char.']+)\[\/list\]'.$char.'/is',$text, $treffer)) {
					$text = preg_replace_callback('/\n?'.$char.'\[list(?:=(a|A|I|i|OL|ol))?\]([^'.$char.']+)\[\/list\]'.$char.'\n?/is', array(&$this, 'cb_list'), $text);
				}
			}

			$text = preg_replace_callback('/\[note=([^\]]+?)\](.+?)\[\/note\]/is', array(&$this, 'cb_note'), $text);

			$text = preg_replace_callback("~\[url\]((telnet://|callto://|irc://|teamspeak://|http://|https://|ftp://|www.|mailto:|ed2k://|\w+?.\w{2,7})+:\/\/[a-z0-9;\/\?:@=\&\$\-_\.\+!\*'\(\),\~%#]+?)\[\/url\]~is", array(&$this, 'cb_url'), $text);
			$text = preg_replace_callback("~\[url=((telnet://|callto://|irc://|teamspeak://|http://|https://|ftp://|www.|mailto:|ed2k://|\w+?.\w{2,7})[a-z0-9;\/\?:@=\&\$\-_\.\+!\*'\(\),\~%#]+?)\](.+?)\[\/url\]~is", array(&$this, 'cb_title_url'), $text);
			$text = preg_replace_callback("~((et://|svn://|telnet://|callto://|irc://|teamspeak://|http://|https://|ftp://|ed2k://|www.)[a-zA-Z0-9\-\.@]+\.[a-zA-Z0-9]{1,7}(:\d*)?/?([a-zA-Z0-9\-\.:;_\?\,/\\\+&%\$#\=\~]*)?([a-zA-Z0-9/\\\+\=\?]{1}))([^\'\"\<\>\s\r\n\t]{0,8})~is", array(&$this, 'cb_auto_url'), $text);

			$text = empty($this->profile['disallow']['img']) ? preg_replace("/\[img\](([^?&=\[\]]+?)\.(png|gif|bmp|jpg|jpe|jpeg))\[\/img\]/is", '<img src="\1" alt=""'.iif($this->profile['resizeImg'] > 0, ' name="resize"').' />', $text) : $text;
			$text = preg_replace("/\[img\](.+?)\[\/img\]/is", '<a href="\1" target="_blank">\1</a>', $text); // Correct incorrect urls

			$text = preg_replace('/\[color=\#?([0-9A-F]{3,6})\](.+?)\[\/color\]/is', '<span style="color: #\1">\2</span>', $text);
			$text = preg_replace('/\[align=(left|center|right|justify)\](.+?)\[\/align\]/is', "<p style='text-align: \\1'>\\2</p>", $text);

			$text = preg_replace_callback("/\[email\]([a-z0-9\-_\.\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+?)\[\/email\]/is", array(&$this, 'cb_mail'), $text);
			$text = empty($this->profile['disallow']['h']) ? preg_replace_callback('/\n?\[h=(middle|small|large)\](.+?)\[\/h\]\n?/is', array(&$this, 'cb_header'), $text) : $text;
			$text = preg_replace_callback('/\[size=(small|extended|large)\](.+?)\[\/size\]/is', array(&$this, 'cb_size'), $text);

			while (preg_match('/\[quote=(.+?)\](.+?)\[\/quote\]/is',$text, $values)) {
				if (isset($values[1]) && check_hp($values[1])) {
					$quote_html = '<a href="\1" target="_blank">\1</a>';
				}
				else {
					$quote_html = "\\1";
				}
				$text = preg_replace('/\[quote=(.+?)\](.+?)\[\/quote\]\n?/is', "<div class='bb_quote'><strong>".$lang->phrase('bb_quote_by')." {$quote_html}:</strong><br /><blockquote>\\2</blockquote></div>", $text);
			}
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
			$text = preg_replace_callback('/\[table(=(\d+\%;head|head;\d+\%|\d+\%|head))?\]\n*(.+?)\n*\[\/table\]\n?/is', array(&$this, 'cb_table'), $text);
			$text = str_ireplace('[tab]', "\t", $text);

			$text = $this->tab2space($text);
			$text = $this->parseSmileys($text);
			$text = $this->wordwrap($text);
		}
		$text = str_ireplace('[reader]', $this->reader, $text);
		$text = $this->customBB($text, $type);
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

	function nl2br ($text, $type = 'html') {
		$text = str_ireplace('[br]', "\n", $text);
		if ($type == 'plain') {
			$text = str_replace("\n", " \n", $text); // Evtl. Leerzeichen oder nur Zeilenumbruch...
		}
		elseif ($type == 'pdf') {
			$text = str_replace("\n", "<br>\n", $text);
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
			$text = preg_replace('/\!{2,}1?/i', "!", $text);
			$text = preg_replace('/\?{2,}(&szlig;)?/i', "?", $text);
			$text = preg_replace('/\.{4,}/i', "...", $text);
		}
		if ($this->profile['reduceNL'] == 1) {
			$text = preg_replace('/\n{3,}/i', "\n\n", $text);
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
	function cb_table($data) {
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
			$code = preg_replace("#(\n\n)#", "\n\n", $code);
		} while (preg_match("#\n\n#", $code));

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
				if(empty($table_content[0][$i])){
					$table_head[$i] = '&nbsp;';
				}
				else{
					$table_head[$i] = $table_content[0][$i];
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
	function cb_plain_table ($text, $tag = 'tt') {
		$length = array();
		$lines = explode("\n", $text[2]);
		$char = chr(7);
		foreach ($lines as $line) {
			if (empty($line)) {
				continue;
			}
			$i = 0;
			$td = explode("\t", $line);
			foreach ($td as $cell) {
				$cell   = strip_tags($cell);
				$tab_pos= strxlen($cell);
				$min	= $tab_pos/4 - floor($tab_pos/4);
				$tab	= (1-$min)*4;
				if ($tab < 1) {
					$tab = 4;
				}
				$line   = strlen($cell)+$tab;
				if (!isset($result[$i]) || $line > $result[$i]) {
					$result[$i] = $line;
				}
				$i++;
			}
		}
		$table = array();
		foreach ($lines as $line) {
			if (empty($line)) {
				continue;
			}
			$i = 0;
			$td = explode("\t", $line);
			$line = '';
			foreach ($td as $cell) {
				$spaces = $result[$i];
				$length = strxlen(strip_tags($cell));
				$min	= $spaces - $length;
				$tab	= str_repeat($char, $min);
				$line  .= $cell.$tab;
				$i++;
			}
			$table[] = $line;
		}
		$text = str_replace($char, '&nbsp;', implode("\n", $table));
		return "<{$tag} class=\"bb_table\">{$text}</{$tag}>";
	}
	/**
	 * Converts tabs to the appropriate amount of spaces while preserving formatting
	 *
	 * @author      Aidan Lister <aidan@php.net>
	 * @version     1.2.0
	 * @param       string    $text     The text to convert
	 * @param       int       $spaces   Number of spaces per tab column
	 * @return      string    The text with tabs replaced
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
		return str_replace($char, '&nbsp;', implode("\n", $result));
	}
	function getBenchmark($type='bbcode') {
	    return substr($this->benchmark[$type], 0, 7);
	}
	function parseSmileys ($text, $type = 'html') {
	    $thiszm1 = benchmarktime();
	    $this->cache_smileys();
		if ($type != 'plain') {
			foreach ($this->smileys as $smiley) {
				$text = str_replace(' '.$smiley['search'], ' <img src="'.$smiley['replace'].'" border="0" alt="'.$smiley['desc'].'" />', $text);
			}
		}
		$thiszm2 = benchmarktime();
		$this->benchmark['smileys'] += $thiszm2-$thiszm1;
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
				'wordwrap_asia' => 0,
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
	function setWordwrap ($use = 1, $wordlength = 70, $char = ' ', $asia = 0) {
		$this->profile['wordwrap'] = $use;
		$this->profile['wordwrap_wordlength'] = $wordlength;
		$this->profile['wordwrap_char'] = $char;
		$this->profile['wordwrap_asia'] = $asia;
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
			$class = $this->profile['highlight_class'];
			foreach ($this->profile['highlight'] as $token) {
				if (strlen($token) > 2) {
					$text = str_replace('\"', '"', substr(preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "preg_replace('#\b(" . $token . ")\b#i', '<span class=\"" . $class . "\">\\\\1</span>', '\\0')", '>' . $text . '<'), 1, -1));
				}
			}
		}
		return $text;
	}
	function dict($text, $type = 'html') {
		if ($this->profile['useDict'] == 1) {
			$this->cache_bbcode();
			foreach ($this->bbcodes['word'] as $word) {
				$ws = $word['search'];
			    $wr = $word['replace'];
			    if ($type == 'pdf' || $type == 'plain') {
			    	$text = str_replace(
			    		'\"',
			    		'"',
			    		substr(
			    			preg_replace(
			    				'#(\>(((?>([^><]+|(?R)))*)\<))#se',
			    				"preg_replace(
			    					'#\b(".$ws.")\b#i',
			    					'\\\\1 (".$wr.")',
			    					'\\0'
			    				)",
			    				'>' . $text . '<'
			    			)
			    			, 1,
			    			-1
			    		)
			    	);
	                // $text = str_ireplace(, "{$word['search']} ({$word['replace']})", $text);
	            }
	            else {
	            	$ws = htmlentities($ws);
	            	$text = str_replace(
	            		'\"',
	            		'"',
	            		substr(
	            			preg_replace(
	            				'#(\>(((?>([^><]+|(?R)))*)\<))#se',
	            				"preg_replace(
	            					'#\b(".$ws.")\b#i',
	            					'<acronym title=\"".$wr."\">\\\\1</acronym>',
	            					'\\0'
	            				)",
	            				'>' . $text . '<'
	            			),
	            			1,
	            			-1
	            		)
	            	);
	                // $text = str_ireplace($word['search'], "", $text);
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
	function customBB ($text, $type='html') {
		$this->getCustomBB();
		foreach ($this->custombb as $re) {
			// Paramter for Opening Tag
			$param = ($re['twoparams'] ? '=([^\]\'\"]*?)' : '');
			// Opening Tag
			$regexp = '\['.$re['bbcodetag'].$param.'\]';
			// Getting content
			$regexp .= '(.+?)';
			// Closing Tag
			$regexp .= '\[\/'.$re['bbcodetag'].'\]';
			if ($type == 'plain') {
				$re['bbcodereplacement'] = strip_tags($re['bbcodereplacement']);
			}
           	$text = preg_replace('~'.$regexp.'~is', $re['bbcodereplacement'], $text);
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
		$text = preg_replace("~([^\n\r\s&\./<>\[\]\\\]{".$length.'})~i', "\\1".$this->profile['wordwrap_char'], $text);
		if ($this->profile['wordwrap_asia'] == 0) {
			$text = preg_replace("~(&amp;#?\w{2,5};)(&amp;#?\w{2,5};)(&amp;#?\w{2,5};)(&amp;#?\w{2,5};)(&amp;#?\w{2,5};)~iU", "\\1\\2\\3\\4\\5 ", $text);
		}
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
	function getsmileyhtml ($perrow = 5) {
	    global $tpl, $config;
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
		$smileys[1] = array_chunk($smileys[1], $perrow);

		end($smileys[1]);
		$last = current($smileys[1]);
		$lastKey = key($smileys[1]);
		reset($smileys[1]);
		$colspan = $perrow - count($last);

		$tpl->globalvars(compact("smileys", "colspan", "lastKey"));
		return $tpl->parse("main/smileys");
	}
	function getbbhtml ($file = "main/bbhtml") {
	    global $tpl, $lang;
	    $lang->group("bbcodes");
	    $cbb = $this->getCustomBB();
	    foreach ($cbb as $key => $bb) {
	    	if (empty($bb['buttonimage'])) {
	    		unset($cbb[$key]);
	    		continue;
	    	}
	    	$cbb[$key]['title'] = htmlspecialchars($bb['title']);
	    	if ($bb['twoparams']) {
	    		$cbb[$key]['href'] = "InsertTagsParams('[{$bb['bbcodetag']}={param1}]{param2}','[/{$bb['bbcodetag']}]');";
	    	}
	    	else {
	    		$cbb[$key]['href'] = "InsertTags('[{$bb['bbcodetag']}]','[/{$bb['bbcodetag']}]');";
	    	}
	    }
	    $tpl->globalvars(compact("cbb"));
	    return $tpl->parse($file);
	}
	function replaceTextOnce($original, $newindex) {
		global $lang;
		$lang->assign('originalid', $original);
		return $lang->get_text($newindex);
	}
}

function BBProfile(&$bbcode, $profile = 'standard') {
	global $config, $my;
	if (!$bbcode->existsProfile($profile)) {
		if ($config['resizebigimg'] == 0) {
			$config['resizebigimgwidth'] = 0;
		}

		if ($profile == 'signature') {
			$bbcode->setProfile('signature', SP_NEW);
			$bbcode->setProfile($profile, SP_NEW);
			$bbcode->setMisc($config['dictstatus'], $config['censorstatus'], $config['resizebigimgwidth']);
			$bbcode->setWordwrap($config['wordwrap'], $config['maxwordlength'], $config['maxwordlengthchar'], $config['asia']);
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
			$bbcode->setWordwrap($config['wordwrap'], $config['maxwordlength'], $config['maxwordlengthchar'], $config['asia']);
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

?>
