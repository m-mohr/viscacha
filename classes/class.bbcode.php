<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2006  Matthias Mohr, MaMo Net
	
	Author: Matthias Mohr
	Publisher: http://www.mamo-net.de
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

if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "class.bbcode.php") die('Error: Hacking Attempt');

DEFINE('SP_CHANGE', 1);
DEFINE('SP_COPY', 2);
DEFINE('SP_NEW', 4);

class BBCode {

	var $smileys = array();
	var $bbcodes = array();
	var $profile = '';
	var $cfg = array();
	var $reader = '';
	var $noparse = array();
	var $pid = 0;
	var $benchmark = array();
	var $author = 0;

	function BBCode () {
	    $this->benchmark = array(
	    'smileys' => 0,
	    'bbcode' => 0
	    );
		$this->cache_smileys();
		$this->cache_bbcode();
		$this->setProfile('standard');
		include('classes/class.convertroman.php');
	}
	function setName($name) {
		$this->reader = $name;
	}
	function setAuthor($id) {
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
	function cb_list ($type,$pattern) {
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
	function cb_code ($code) {
		$pid = $this->noparse_id();
	    $code = trim($code);
	    $rows = explode("\n", $code);
		$code2 = preg_replace('/\[b\](.+?)\[\/b\]/is', "<strong>\\1</strong>", $code);
		$code2 = str_replace("]", "&#93;", $code2);
		$code2 = str_replace("[", "&#91;", $code2);
		$code2 = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $code2);
	    
	    if (count($rows) > 1) {
	    	$a = 0;
	    	$aa = array();
		    foreach ($rows as $row) {
		        $a++;
		        $aa[] = "$a:&nbsp;";
		    }
		
			$aa = implode("<br>",$aa);
		
		    $this->noparse[$pid] = '<strong class="bb_blockcode_header">Quelltext:</strong><div class="bb_blockcode"><table><tr><td width="1%">'.$aa.'</td><td width="99%">'.$this->nl2br($code2).'</td></tr></table></div>';
		}
		else {
			$this->noparse[$pid] = '<code class="bb_inlinecode">'.$this->nl2br($code2).'</code>';
		}
	    return '<!PID:'.$pid.'>';
	}
	function cb_hlcode ($lang, $code) {
		$pid = $this->noparse_id();
		$code = trim($code);
	    $rows = preg_split('/(\r\n|\r|\n)/',$code);
		$code2 = preg_replace('/\[b\](.+?)\[\/b\]/is', "<strong>\\1</strong>", $code);
		$code2 = str_replace("]", "&#93;", $code2);
		$code2 = str_replace("[", "&#91;", $code2);
		$code2 = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $code2);
    
	    if (count($rows) > 1) {
		    $a = 0;
		    $aa = array();
			$unique = md5($code);
			$scache = new scache('geshicode/'.$unique);
			if ($scache->existsdata() == FALSE) {
				$export = array(
				'language' => $lang,
				'source' => trim($code)
				);
			    $scache->exportdata($export);
			}
		    
		    foreach ($rows as $row) {
		        $a++;
		        $aa[] = "$a:&nbsp;";
		    }
			
			$aa = implode("<br />",$aa);
		
		    $this->noparse[$pid] = '<strong class="bb_blockcode_header"><a target="_blank" href="popup.php?action=hlcode&amp;fid='.$unique.SID2URL_x.'">Erweiterter Quelltext</a>:</strong><div class="bb_blockcode"><table><tr><td width="1%">'.$aa.'</td><td width="99%">'.$this->nl2br($code2).'</td></tr></table></div>';
		}
		else {
			$this->noparse[$pid] = '<code class="bb_inlinecode">'.$this->nl2br($code2).'</code>';
		}
		return '<!PID:'.$pid.'>';
	}
	function cb_mail ($pattern1, $pattern2) {
	    $str = "";
	    $a = unpack("C*", "$pattern1@$pattern2");
	    foreach ($a as $b) {
	   		$str .= sprintf("%%%X", $b);
	   	}
	   	return "<a href=\"mailto: $str\">{$pattern1}&#64;$pattern2</a>";
	}
	function cb_header ($size, $content) {
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
	function cb_size ($size, $content) {
		if ($size != 'extended') {
			if ($size == 'small') {
				$fontsize = 0.8;
			}
			elseif ($size == 'large') {
				$fontsize = 1.3;
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
	function cb_note ($desc, $word) {
		$pid = $this->noparse_id();
		$o = "<acronym title=\"<!PID:".$pid.">\">{$word}</acronym>";
		$this->noparse[$pid] = $desc;
		return $o;
	}
	function cb_url ($url, $title = FALSE, $img = FALSE, $chop = FALSE) {
		global $config;

		if ($chop != FALSE) {
			$chars = explode("'", $chop);
		}
		if ((isset($chars) && (($chars[0] == '>' && $chars[1] == '<') || ($chars[0] == '"' && $chars[1] == '"'))) || ($img && preg_match("/(([^?&=].*?)\.(png|gif|bmp|jpg|jpe|jpeg))/is", $url)) || preg_match("/(\")/is", $url)) {
			return $chars[0].$url.$chars[1];
		}

		$url = trim($url);
		if (strtolower(substr($url, 0, 4)) == 'www.') {
			$url = "http://".$url;
		}
		
		$pid = $this->noparse_id();
		$this->noparse[$pid] = $url;
		
		// Workaround for a bug with ' Entity (&#039;) 
		$add039 = '';
		if ($title != FALSE) {
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
			$ahref = '<a href="<!PID:'.$pid.'>" target="_blank">'.$newurl.'</a>'.$add039;
		}
		else{  
		   $ahref = '<a href="<!PID:'.$pid.'>" target="_blank">'.$url.'</a>'.$add039;
		}
		if (isset($chars)) {
	    	return $chars[0].$ahref.$chars[1];
	    }
	    else {
	        return $ahref;
	    }
	}
	function cb_pdf_list ($type,$pattern) {
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
	function cb_pdf_code ($code) {
		$pid = $this->noparse_id();
	    $code = trim($code);
	    $rows = explode("\n", $code);
		$code2 = preg_replace('/\[b\](.+?)\[\/b\]/is', "<b>\\1</b>", $code);
		$code2 = str_replace("]", "&#93;", $code2);
		$code2 = str_replace("[", "&#91;", $code2);
		$code2 = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $code2);
	    
	    if (count($rows) > 1) {
	    	$a = 0;
	    	$code = '';
	    	$lines = strlen(count($rows));
		    foreach ($rows as $row) {
		        $a++;
		        $code .= "<tt>".leading_zero($a, $lines)."</tt>: <code>".$row."</code><br>";
		    }
		
		    $this->noparse[$pid] = '<br><b>Quelltext:</b><br>'.htmlentities($code);
		}
		else {
			$this->noparse[$pid] = '<code>'.htmlentities($code2).'</code>';
		}
	    return '<!PID:'.$pid.'>';
	}
	function cb_pdf_header ($size, $content) {
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
	function cb_pdf_size ($size, $content) {
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
	function cb_plain_list ($type,$pattern) {
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
	    $list = preg_replace('/ +?([a-zA-Z0-9\-\.]+?) <br>/is', '', $list);
	    return $list;
	}
	function cb_plain_code ($code) {
		$pid = $this->noparse_id();
	    $code = trim($code);
	    $rows = explode("\n", $code);
		$code2 = str_replace("]", "&#93;", $code);
		$code2 = str_replace("[", "&#91;", $code2);
	    
	    if (count($rows) > 1) {
	    	$a = 0;
	    	$code = '';
	    	$lines = strlen(count($rows));
		    foreach ($rows as $row) {
		        $a++;
		        $code .= leading_zero($a, $lines).": ".$row."\n";
		    }
		
		    $this->noparse[$pid] = "\nQuelltext:\n".$code;
		}
		else {
			$this->noparse[$pid] = $code2;
		}
	    return '<!PID:'.$pid.'>';
	}
	function strip_bbcodes ($text) {
		$text = preg_replace('/(\r\n|\r|\n)/', "\n", $text);
		$text = preg_replace('/\[hide\](.+?)\[\/hide\]/is', '', $text);
		$text = preg_replace("~\[url=((news:|telnet://|callto://|irc://|teamspeak://|http://|https://|ftp://|www.|mailto:|ed2k://|\w+?.\w{2,7})[a-z0-9;\/\?:@=\&\$\-_\.\+!\*'\(\),\~%#]+?)\](.+?)\[\/url\]~is", "\\3", $text);

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
		
		$text = preg_replace('/\[code(=\w+?)?\](.+?)\[\/code\]\n?/ise', '\2', $text);
			
		while (preg_match('/\[list(?:=(a|A|I|i|OL|ol))?\](.+?)\[\/list\]/is',$text)) {
			$text = preg_replace('/\[list(?:=(a|A|I|i|OL|ol))?\](.+?)\[\/list\]/ise', '\2', $text);
		}
		$text = preg_replace('/\[note=(.+?)\](.+?)\[\/note\]/is', "\\2", $text);
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
		
		$text = preg_replace('/\[[^\[\]]+?\](.+?)\[\/[^\[\]]+?\]/is', "\\1", $text);
		
		$text = $this->parseDoc($text);
		$text = $this->nl2br($text, 'plain');
		$text = $this->censor($text);
		return $text;
	}
	
	// Possible values for $type: html, pdf, plain (with linebreaks)
	function parse ($text, $type = 'html') {
		global $lang, $my; // Replace DE-Language with Variables
		$thiszm1=benchmarktime();
		$this->noparse = array();
		$text = preg_replace('/(\r\n|\r|\n)/', "\n", $text);
		$text = str_replace('$', '&#36;', $text);
		if($type != 'pdf' && $type != 'plain' && ($my->p['admin'] == 1 || ($my->id > 0 && $my->id == $this->author))) {
		    $text = preg_replace('/\n?\[hide\](.+?)\[\/hide\]/is', '<br /><div class="bb_hide"><strong>Versteckter Inhalt:</strong><span>\1</span></div>', $text);
		}
		else {
		    $text = preg_replace('/\[hide\](.+?)\[\/hide\]/is', '', $text);
		}
		if ($type == 'plain') {
			$text = preg_replace("~\[url=((news:|telnet://|callto://|irc://|teamspeak://|http://|https://|ftp://|www.|mailto:|ed2k://|\w+?.\w{2,7})[a-z0-9;\/\?:@=\&\$\-_\.\+!\*'\(\),\~%#]+?)\](.+?)\[\/url\]~is", "\\3 (\\1)", $text);

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
			$replace = array_fill(0, count($search), '');
			$text = str_ireplace($search, $replace, $text);
			
			$text = preg_replace('/\[code(=\w+?)?\](.+?)\[\/code\]\n?/ise', '$this->cb_plain_code("\2")', $text);
			
			while (preg_match('/\[list(?:=(a|A|I|i|OL|ol))?\](.+?)\[\/list\]/is',$text)) {
				$text = preg_replace('/\[list(?:=(a|A|I|i|OL|ol))?\](.+?)\[\/list\]/ise', '$this->cb_plain_list("\1", "\2")', $text);
			}
			$text = preg_replace('/\[note=(.+?)\](.+?)\[\/note\]/is', "\\1 (\\2)", $text);
			$text = preg_replace('/\[color=(\#?[0-9A-F]{3,6})\](.+?)\[\/color\]/is', "\\2", $text);
			$text = preg_replace('/\[align=(left|center|right|justify)\](.+?)\[\/align\]/is', "\\2", $text);
			$text = preg_replace('/\n?\[h=(middle|small|large)\](.+?)\[\/h\]\n?/is', "\\2", $text);
			$text = preg_replace('/\[size=(small|extended|large)\](.+?)\[\/size\]/is', "\\2", $text);
			
			while (preg_match('/\[quote=(.+?)\](.+?)\[\/quote\]/is',$text)) {
				$text = preg_replace('/\[quote=(.+?)\](.+?)\[\/quote\]\n?/is', "\nZitat von \\1:\n\\2\n", $text);
			}
			while (preg_match('/\[quote](.+?)\[\/quote\]/is',$text)) {
				$text = preg_replace('/\[quote](.+?)\[\/quote\]\n?/is', "\nZitat:\n\\1\n", $text);
			}
			while (preg_match('/\[edit\](.+?)\[\/edit\]/is',$text)) {
				$text = preg_replace('/\[edit\](.+?)\[\/edit\]\n?/is', "\nNachträgliche Anmerkung des Autors:\n\\1\n", $text);
			}
			while (preg_match('/\[edit=(.+?)\](.+?)\[\/edit\]/is',$text)) {
				$text = preg_replace('/\[edit=(.+?)\](.+?)\[\/edit\]\n?/is', "\nNachträgliche Anmerkung von \\1:\n\\2\n", $text);
			}
			while (preg_match('/\[ot\](.+?)\[\/ot\]/is',$text)) {
				$text = preg_replace('/\[ot\](.+?)\[\/ot\]\n?/is', "\nOff-Topic:\n\\1\n", $text);
			}
			
			$text = preg_replace('/(\[hr\]){1,}/is', "-------------------", $text);
			$text = str_ireplace('[tab]', "    ", $text);
		}
		elseif ($type == 'pdf') {
			$text = preg_replace('/\[code(=\w+?)?\](.+?)\[\/code\]\n?/ise', '$this->cb_pdf_code("\2")', $text);
			while (preg_match('/\[list(?:=(a|A|I|i|OL|ol))?\](.+?)\[\/list\]/is',$text)) {
				$text = preg_replace('/\n?\[list(?:=(a|A|I|i|OL|ol))?\](.+?)\[\/list\]\n?/ise', '$this->cb_pdf_list("\1", "\2")', $text);
			}
			$text = preg_replace('/\[note=(.+?)\](.+?)\[\/note\]/is', "\\1 (<i>\\2</i>)", $text);

			$text = preg_replace("~\[url\]((news:|telnet://|callto://|irc://|teamspeak://|http://|https://|ftp://|www.|mailto:|ed2k://|\w+?.\w{2,7})+:\/\/[a-z0-9;\/\?:@=\&\$\-_\.\+!\*'\(\),\~%#]+?)\[\/url\]~is", "<a href=\"\\1\">\\1</a>", $text);
			$text = preg_replace("~\[url=((news:|telnet://|callto://|irc://|teamspeak://|http://|https://|ftp://|www.|mailto:|ed2k://|\w+?.\w{2,7})[a-z0-9;\/\?:@=\&\$\-_\.\+!\*'\(\),\~%#]+?)\](.+?)\[\/url\]~is", "<a href=\"\\1\">\\3</a>", $text);
			$text = preg_replace("/\[img\](([^?&=].*?)\.(png|gif|bmp|jpg|jpe|jpeg))\[\/img\]/is", "<img src=\"\\1\">", $text);
			
			$text = preg_replace('/\[color=\#?([0-9A-F]{3,6})\](.+?)\[\/color\]/is', "<font color=\"#\\1\">\\2</font>", $text);
			$text = preg_replace('/\[align=(left|center|right|justify)\](.+?)\[\/align\]/is', "<p align=\"\\1\">\\2</p>", $text);

			$text = preg_replace("/\[email\]([a-z0-9\-_\.\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+?)\[\/email\]/is", "<a href=\"mailto:\\1\">\\1</a>", $text);
			$text = preg_replace('/\n?\[h=(middle|small|large)\](.+?)\[\/h\]\n?/ise', '$this->cb_pdf_header("\1", "\2")', $text);
			$text = preg_replace('/\[size=(small|extended|large)\](.+?)\[\/size\]/ise', '$this->cb_pdf_size("\1", "\2")', $text);

			while (preg_match('/\[quote=(.+?)\](.+?)\[\/quote\]/is',$text)) {
				$text = preg_replace('/\[quote=(.+?)\](.+?)\[\/quote\]\n?/is', "<br><b>Zitat von \\1:</b><hr><i>\\2</i><hr>", $text);
			}
			while (preg_match('/\[quote](.+?)\[\/quote\]/is',$text)) {
				$text = preg_replace('/\[quote](.+?)\[\/quote\]\n?/is', "<br><b>Zitat:</b><hr><i>\\1</cite></i><hr>", $text);
			}
			while (preg_match('/\[edit\](.+?)\[\/edit\]/is',$text)) {
				$text = preg_replace('/\[edit\](.+?)\[\/edit\]\n?/is', "<br><b>Nachträgliche Anmerkung des Autors:</b><hr>\\1<hr>", $text);
			}
			while (preg_match('/\[edit=(.+?)\](.+?)\[\/edit\]/is',$text)) {
				$text = preg_replace('/\[edit=(.+?)\](.+?)\[\/edit\]\n?/is', "<br><b>Nachträgliche Anmerkung von \\1:</b><hr>\\2<hr>", $text);
			}
			while (preg_match('/\[ot\](.+?)\[\/ot\]/is',$text)) {
				$text = preg_replace('/\[ot\](.+?)\[\/ot\]\n?/is', "<br><b>Off-Topic:</b><hr><span style=\"color: #999999\" size=\"7\">\\1</span><hr>", $text);
			}
			
			$text = preg_replace('/\[b\](.+?)\[\/b\]/is', "<b>\\1</b>", $text);
			$text = preg_replace('/\[i\](.+?)\[\/i\]/is', "<i>\\1</i>", $text);
			$text = preg_replace('/\[u\](.+?)\[\/u\]/is', "<u>\\1</u>", $text);
			$text = preg_replace('/\[tt\](.+?)\[\/tt\]/is', "<tt>\\1</tt>", $text);
			$text = preg_replace('/\n?(\[hr\]){1,}\n?/is', "<hr>", $text);
			
			$text = preg_replace('/\[sub\](.+?)\[\/sub\]/is', "<sub>\\1</sub>", $text);
			$text = preg_replace('/\[sup\](.+?)\[\/sup\]/is', "<sup>\\1</sup>", $text);
			
			$text = str_ireplace('[tab]', "\t", $text);
			$text = preg_replace('/\[table\](.+?)\[\/table\]\n?/ise', '$this->cb_table("\1", "tt")', $text);
			
			$text = $this->tab2space($text);
			$text = $this->parseSmileys($text);
		}
		else {
			$text = preg_replace('/\[code\](.+?)\[\/code\]\n?/ise', '$this->cb_code("\1")', $text);
			$text = preg_replace('/\[code=(\w+?)\](.+?)\[\/code\]\n?/ise', '$this->cb_hlcode(\'\1\',\'\2\')', $text);
			
			$char = chr(5);
			$text = str_ireplace('[/list]', '[/list]'.$char, $text);
			$text = str_ireplace('[list', $char.'[list', $text);
			while (preg_match('/'.$char.'\[list(?:=(a|A|I|i|OL|ol))?\]([^'.$char.']+)\[\/list\]'.$char.'/is',$text, $treffer)) {
				$text = preg_replace('/\n?'.$char.'\[list(?:=(a|A|I|i|OL|ol))?\]([^'.$char.']+)\[\/list\]'.$char.'\n?/ise', '$this->cb_list("\1", "\2")', $text);
			}
			
			$text = preg_replace('/\[note=(.+?)\](.+?)\[\/note\]/ise', '$this->cb_note("\1", "\2")', $text);
			
			$text = preg_replace("~\[url\]((news:|telnet://|callto://|irc://|teamspeak://|http://|https://|ftp://|www.|mailto:|ed2k://|\w+?.\w{2,7})+:\/\/[a-z0-9;\/\?:@=\&\$\-_\.\+!\*'\(\),\~%#]+?)\[\/url\]~ise", '$this->cb_url("\1")', $text);
			$text = preg_replace("~\[url=((news:|telnet://|callto://|irc://|teamspeak://|http://|https://|ftp://|www.|mailto:|ed2k://|\w+?.\w{2,7})[a-z0-9;\/\?:@=\&\$\-_\.\+!\*'\(\),\~%#]+?)\](.+?)\[\/url\]~ise", '$this->cb_url("\1", "\3")', $text);
			$text = preg_replace("~(.?)((news:|telnet://|callto://|irc://|teamspeak://|http://|https://|ftp://|www.|ed2k://)[a-zA-Z0-9\-\.@]+\.[a-zA-Z0-9]{1,7}(:\d*)?/?([a-zA-Z0-9\-\.:;_\?\,/\\\+&%\$#\=\~]*)?([a-zA-Z0-9/\\\+\=\?]{1}))(.?)~ie", '$this->cb_url("\2", FALSE, TRUE, "\1\'\7")', $text);
			
			if ($this->profile['resizeImg'] > 0) {
			    $text = preg_replace("/\[img\](([^?&=].*?)\.(png|gif|bmp|jpg|jpe|jpeg))\[\/img\]/is", "<img src='\\1' alt='' name='resize' />", $text);
			}
			else {
			    $text = preg_replace("/\[img\](([^?&=].*?)\.(png|gif|bmp|jpg|jpe|jpeg))\[\/img\]/is", "<img src='\\1' alt=''>", $text);
			}
			
			$text = preg_replace('/\[color=\#?([0-9A-F]{3,6})\](.+?)\[\/color\]/is', "<span style='color: #\\1'>\\2</span>", $text);
			$text = preg_replace('/\[align=(left|center|right|justify)\](.+?)\[\/align\]/is', "<p style='text-align: \\1'>\\2</p>", $text);

			$text = preg_replace("/\[email\]([a-z0-9\-_\.\+]+)@([a-z0-9\-]+\.[a-z0-9\-\.]+?)\[\/email\]/ise", '$this->cb_mail("\1","\2")', $text);
			$text = preg_replace('/\n?\[h=(middle|small|large)\](.+?)\[\/h\]\n?/ise', '$this->cb_header("\1", "\2")', $text);
			$text = preg_replace('/\[size=(small|extended|large)\](.+?)\[\/size\]/ise', '$this->cb_size("\1", "\2")', $text);

			while (preg_match('/\[quote=(.+?)\](.+?)\[\/quote\]/is',$text)) {
				$text = preg_replace('/\[quote=(.+?)\](.+?)\[\/quote\]\n?/is', "<blockquote class='bb_quote'><strong>Zitat von \\1:</strong><br /><cite>\\2</cite></blockquote>", $text);
			}
			while (preg_match('/\[quote](.+?)\[\/quote\]/is',$text)) {
				$text = preg_replace('/\[quote](.+?)\[\/quote\]\n?/is', "<blockquote class='bb_quote'><strong>Zitat:</strong><br /><cite>\\1</cite></blockquote>", $text);
			}
			while (preg_match('/\[edit\](.+?)\[\/edit\]/is',$text)) {
				$text = preg_replace('/\[edit\](.+?)\[\/edit\]\n?/is', "<div class='bb_edit'><strong>Nachträgliche Anmerkung des Autors:</strong><br /><ins>\\1</ins></div>", $text);
			}
			while (preg_match('/\[edit=(.+?)\](.+?)\[\/edit\]/is',$text)) {
				$text = preg_replace('/\[edit=(.+?)\](.+?)\[\/edit\]\n?/is', "<div class='bb_edit'><strong>Nachträgliche Anmerkung von \\1:</strong><br /><ins>\\2</ins></div>", $text);
			}
			while (preg_match('/\[ot\](.+?)\[\/ot\]/is',$text)) {
				$text = preg_replace('/\[ot\](.+?)\[\/ot\]\n?/is', "<div class='bb_ot'><strong>Off-Topic:</strong><br /><span>\\1</span></div>", $text);
			}
			
			$text = preg_replace('/\[b\](.+?)\[\/b\]/is', "<strong>\\1</strong>", $text);
			$text = preg_replace('/\[i\](.+?)\[\/i\]/is', "<em>\\1</em>", $text);
			$text = preg_replace('/\[u\](.+?)\[\/u\]/is', "<u>\\1</u>", $text);
			$text = preg_replace('/\[sub\](.+?)\[\/sub\]/is', "<sub>\\1</sub>", $text);
			$text = preg_replace('/\[sup\](.+?)\[\/sup\]/is', "<sup>\\1</sup>", $text);
			$text = preg_replace('/\n?(\[hr\]){1,}\n?/is', "<hr />", $text);

			$text = preg_replace('/\[tt\](.+?)\[\/tt\]/is', "<tt>\\1</tt>", $text);
			$text = str_ireplace('[tab]', "\t", $text);
			$text = preg_replace('/\[table\](.+?)\[\/table\]\n?/ise', '$this->cb_table("\1", "tt")', $text);
			
			$text = $this->tab2space($text);
			$text = $this->parseSmileys($text);
			$text = $this->wordwrap($text);
		}
		$text = str_ireplace('[reader]', $this->reader, $text);
		$text = $this->parseDoc($text);
		$text = $this->dict($text, $type);
		$text = $this->replace($text);
		// Version 1.1
		//$text = $this->customBB($text, $type);
		$text = $this->nl2br($text, $type);
		$text = $this->replacePID($text);
		$text = $this->censor($text);
		$text = $this->highlight($text);
		$thiszm2=benchmarktime();
		$this->benchmark['bbcode'] += $thiszm2-$thiszm1;
		return $text;
	}
	
	function nl2br ($text, $type = 'html') {
		if ($type == 'plain') {
			$text = str_replace("\n", " \n", $text); // Evtl. Leerzeichen oder nur Zeilenumbruch...
		}
		elseif ($type == 'pdf') {
			$text = str_replace("\n", "<br>\n", $text);
		}
		else {
			$text = nl2br($text);
		}
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
		if($topic == strtoupper($topic) && $this->profile['topicuppercase'] == 1) {
			return ucwords(strtolower($topic));
		}
		else {
			return $topic;
		}
	}
	function cb_table ($text, $tag) {
		$length = array();
		$lines = explode("\n", $text);
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
	    $thiszm1=benchmarktime();
		if ($type != 'plain') {
			foreach ($this->smileys as $smiley) {
				$smiley['search'] = htmlentities($smiley['search'], ENT_QUOTES);
				$smiley['desc'] = str_replace('"', '&quot;', $smiley['desc']);
				$smiley['replace'] = str_replace('{folder}', $this->profile['SmileyUrl'], $smiley['replace']);
				$text = str_replace(' '.$smiley['search'], ' <img src="'.$smiley['replace'].'" border="0" alt="'.$smiley['desc'].'" />', $text);
			}
		}
		$thiszm2=benchmarktime();
		$this->benchmark['smileys'] += $thiszm2-$thiszm1;
		return $text;
	}
	function getSmileys () {
		return $this->smileys;
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
					'img' => FALSE,
					'code' => FALSE,
					'list' => FALSE,
					'edit' => FALSE,
					'ot' => FALSE,
					'h' => FALSE
				)
			);
		}
		unset($this->profile);
		$this->profile = &$this->cfg[$name];
	}
	function setFunc($func) {
		$this->profile['disallow'][$func] = TRUE;
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
	    if ($this->profile['useCensor'] == 2) {
	    	foreach ($this->bbcodes['censor'] as $word) {
	            $letters = str_split($word['search']);
	            $word['search'] = '';
	            foreach ($letters as $letter) {
	                $word['search'] .= preg_quote($letter, '~')."(\s|\.|\[.+?\])?";
	            }
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
		if (isset($this->profile['useReplace']) && $this->profile['useReplace'] == 1) {
			foreach ($this->bbcodes['replace'] as $word) {
            	$text = str_ireplace($word['search'], $word['replace'], $text);
            }
		}
		return $text;
	}
	function customBB ($text, $type='html') {
		foreach ($this->bbcodes['bb'] as $regexp) {
           	$text = @preg_replace($regexp['search'], $regexp['replace'], $text);
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
		$text = preg_replace("~([^\n\r ?&./<>\"\\-\[\]]{".$length.'})~i', "\\1".$this->profile['wordwrap_char'], $text);
		if ($this->profile['wordwrap_asia'] == 0) {
			$text = preg_replace("~(&amp;#?\w{2,5};)(&amp;#?\w{2,5};)(&amp;#?\w{2,5};)(&amp;#?\w{2,5};)(&amp;#?\w{2,5};)~iU", "\\1\\2\\3\\4\\5 ", $text);
		}
		return $text;
	}
	function cache_bbcode () {
		global $db;
		$scache = new scache('bbcode');
		if ($scache->existsdata() == TRUE) {
			$cache = $scache->importdata();
		}
		else {
			$bbresult = $db->query("SELECT * FROM {$db->pre}bbcode",__LINE__,__FILE__);
			$cache = array(
				'censor' => array(),
				'bb' => array(),
				'word' => array(),
				'replace' => array()
			);
			while ($bb = $db->fetch_assoc($bbresult)) {
				$cache[$bb['type']][] = $bb;
			}
			$scache->exportdata($cache);
		}
		$this->bbcodes = $cache;
	}
	function cache_smileys () {
		global $db, $bbcode;
		$scache = new scache('smileys');
		if ($scache->existsdata() == TRUE) {
			$cache = $scache->importdata();
		}
		else {
			$bbresult = $db->query("SELECT * FROM {$db->pre}smileys",__LINE__,__FILE__);
			$cache = array();
			while ($bb = $db->fetch_assoc($bbresult)) {
				$cache[] = $bb;
			}
			$scache->exportdata($cache);
		}
		$this->smileys = $cache;
	}
	function getsmileyhtml ($perrow = 5) {
	    global $tpl, $config;
		$smileys = array(0 => array(), 1 => array());
		foreach ($this->smileys as $bb) {
			$bb['replace'] = str_replace('{folder}', $this->profile['SmileyUrl'], $bb['replace']);
			$bb['jssearch'] = addslashes($bb['search']);
		   	if ($bb['show'] == 1) {
				$smileys[1][] = $bb;
			}
			else {
				$smileys[0][] = $bb;
			}
		}
		$smileys[1] = array_chunk($smileys[1], $perrow);
		$tpl->globalvars(compact("smileys"));
		return $tpl->parse("main/smileys");
	}
	function getbbhtml ($file = "main/bbhtml") {
	    global $tpl;
	    return $tpl->parse($file);
	}
	function replaceTextOnce($original, $newindex) {
		global $lang;
		$lang->assign('originalid', $original);
		return $lang->get_text($newindex);
	}
}

function initBBCodes($signature = FALSE) {
	global $config, $my;
	$bbcode = new BBCode('standard', SP_NEW);
	if ($config['resizebigimg'] == 0) {
		$config['resizebigimgwidth'] = 0;
	}
	$bbcode->setMisc($config['dictstatus'], $config['censorstatus'], $config['resizebigimgwidth']);
	$bbcode->setWordwrap($config['wordwrap'], $config['maxwordlength'], $config['maxwordlengthchar'], $config['asia']);
	$bbcode->setDoc($config['reduce_endchars'], $config['reduce_nl'], $config['topicuppercase']);
	$bbcode->setURL($config['reduce_url'], $config['maxurllength'], $config['maxurltrenner']);
	$bbcode->setName($my->name);
	$bbcode->setSmileyDir($my->smileyfolder);
	if ($signature == TRUE) {
		initSignatureBBCodes($bbcode);
	}
	return $bbcode;
}
function initSignatureBBCodes($bbcode) {
	global $config, $my;
	$bbcode->setProfile('signature', SP_COPY);
	if ($config['resizebigimg'] == 0) {
		$config['resizebigimgwidth'] = 0;
	}
	$bbcode->setMisc($config['dictstatus'], $config['censorstatus'], $config['resizebigimgwidth']);
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
	$bbcode->setSmileys(1);
	$bbcode->setReplace($config['wordstatus']);
	$bbcode->setProfile('standard', SP_CHANGE);
}

?>
