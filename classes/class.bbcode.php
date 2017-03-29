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

define('SP_CHANGE', 1);
define('SP_COPY', 2);
define('SP_NEW', 4);

define('CBBC_BUTTONDIR', 'assets/editor/images/');

class BBCode {

	protected $smileys;
	protected $bbcodes;
	protected $custombb;
	protected $profile;
	protected $cfg;
	protected $noparse;
	protected $pid;
	protected $url_regex;
	protected $url_regex2;
	protected $currentCBB;

	public function __construct($profile = 'viscacha') {
		$this->smileys = null;
		$this->bbcodes = null;
		$this->custombb = null;
		$this->currentCBB = null;
		$this->profile = '';
		$this->cfg = array();
		$this->noparse = array();
		$this->pid = 0;

		// See: http://en.wikipedia.org/wiki/URI_scheme
		$url_protocol = "([a-z]{3,9}://|www\.)";
		$url_word = URL_SPECIALCHARS;
		$url_auth = "(?:(?:[{$url_word}_\d\-\.]{1,}\:)?[{$url_word}\d\-\._]{1,}@)?"; // Authorisation information
		$url_host = "(?:\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}|[{$url_word}\d\.\-]{2,}\.[a-z]{2,7})(?:\:\d+)?"; // Host (domain, tld, ip, port)
		$url_path = "(?:\/[{$url_word}\d\/;\-%@\~,\.\+\!=_]*)?"; // Path
		$url_query = "(?:\?[{$url_word}\d=\&;\.:,\_\-\/%@\+\~\[\]]*)?"; // Query String
		$url_fragment = "(?:#[\w\d\-]*)?"; // Fragment
		// URL RegExp - Two matches predefined: First is whole url, second is URI scheme
		$this->url_regex = "({$url_protocol}{$url_auth}{$url_host}{$url_path}{$url_query}{$url_fragment})";
		$this->url_regex2 = "({$url_protocol}{$url_auth}{$url_host}{$url_path}(?:\?[{$url_word}\d=\&;\.:,\_\-\/%\+\~]*)?{$url_fragment})";

		$this->setProfile($profile, SP_NEW);
	}

	protected function noparse_id() {
		$this->pid++;
		return $this->pid;
	}

	protected function replacePID($text) {
		foreach ($this->noparse as $key => $value) {
			$text = str_replace('<!PID:' . $key . '>', $value, $text);
		}
		return $text;
	}

	protected function cb_list($matches) {
		list(, $type, $pattern) = $matches;
		$liarray = preg_split('/(\n\s?-\s|\[\*\])/u', "\n" . $pattern); // Add line break for the first "-", it will be trimmed in the next line.
		$liarray = array_map('trim', $liarray);
		$list = '';
		foreach ($liarray as $li) {
			if (!empty($li)) {
				$list .= '<li>' . $li . '</li>';
			}
		}
		if (empty($list)) {
			return '';
		}
		if (!empty($type)) {
			if ($type == 'a' || $type == 'A') {
				$list = "<ol type='{$type}'>{$list}</ol>";
			} else {
				$list = "<ol>{$list}</ol>";
			}
		} else {
			$list = "<ul>{$list}</ul>";
		}
		return $list;
	}

	protected function code_trim($code) {
		$code = preg_replace('/^([\s\t]*\n+)?(.+?)(\n+[\s\t]*)?$/su', '\2', $code);
		return $code;
	}

	protected function code_prepare($code, $inline = false) {
		$code = str_replace("]", "&#93;", $code);
		$code = str_replace("[", "&#91;", $code);
		$code = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $code);
		if ($inline == true) {
			$code = str_replace("  ", "&nbsp;&nbsp;", trim($code));
		} else {
			$code = str_replace(" ", "&nbsp;", $code);
		}
		return $code;
	}

	protected function cb_hlcode($matches) {
		global $lang, $scache;
		$pid = $this->noparse_id();
		list(,,, $code, $nl) = $matches;

		$code = trim($code, "\r\n");
		$rows = explode("\n", $code);
		if (count($rows) > 1 || $this->wordwrap($code) != $code) {
			$html = '<div class="highlightcode">';
			$html .= '<strong>' . $lang->phrase('bb_sourcecode') . '</strong>';
			$html .= '<div class="bb_blockcode"><ol>';
			foreach ($rows as $row) {
				$html .= '<li>' . str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $row) . '</li>';
			}
			$html .= '</ol></div></div>';
			$this->noparse[$pid] = $html;
		} else {
			$code = trim($code);
			$code = $this->code_prepare($code, (count($rows) <= 1));
			$this->noparse[$pid] = '<code class="bb_inlinecode">' . $code . '</code>';
			if (!empty($nl)) {
				$this->noparse[$pid] .= '<br />';
			}
		}
		return '<!PID:' . $pid . '>';
	}

	protected function cb_mail($email) {
		global $lang;
		$pid = $this->noparse_id();
		$this->noparse[$pid] = 'images.php?action=textimage&amp;text=' . base64_encode($email[1]) . '&amp;enc=1';
		$html = '<img alt="' . $lang->phrase('bbcodes_email') . '" src="<!PID:' . $pid . '>" border="0" />';
		return $html;
	}

	protected function cb_header($matches) {
		list(, $size, $content) = $matches;
		if ($size == 'small') {
			$fontsize = 12;
			$level = 6;
		} elseif ($size == 'large') {
			$fontsize = 14;
			$level = 4;
		} else {
			$fontsize = 13;
			$level = 5;
		}
		$content = $this->wordwrap($content, ceil($this->profile['wordwrap_wordlength'] * (8 / $fontsize)));
		$o = "<h{$level} class=\"bb_header\" style=\"font-size: {$fontsize}pt;\">{$content}</h{$level}>";
		return $o;
	}

	protected function cb_size($matches) {
		list(, $size, $content) = $matches;
		if ($size != 'extended') {
			if ($size == 'small') {
				$fontsize = 0.8;
			} elseif ($size == 'large') {
				$fontsize = 1.5;
				$content = $this->wordwrap($content, ceil($this->profile['wordwrap_wordlength'] * (1 / 1.5)));
			} else {
				$fontsize = 1;
			}
			$o = "<span style=\"font-size: {$fontsize}em;\">{$content}</span>";
		} else {
			$content = $this->wordwrap($content, ceil($this->profile['wordwrap_wordlength'] * 0.75));
			$o = "<span class=\"bb_size_extended\">{$content}</span>";
		}

		return $o;
	}

	protected function cb_image($matches) {
		list(, $url, $extension) = $matches;

		$pid = $this->noparse_id();
		$this->noparse[$pid] = $url;

		return '<img src="<!PID:' . $pid . '>" alt="" name="resize" />';
	}

	protected function cb_auto_url($matches) {
		list(, $prefix, $url,, $suffix) = $matches;
		return $this->cb_url($url, false, $prefix, $suffix);
	}

	protected function cb_title_url($matches) {
		list(, $url,, $title) = $matches;
		return $this->cb_url($url, $title);
	}

	protected function cb_plain_url($matches) {
		list(, $url, ) = $matches;
		return $this->cb_url($url);
	}

	protected function cb_url($url, $title = false, $prefix = '', $suffix = '') {
		global $config;

		if (mb_strtolower(mb_substr($url, 0, 4)) == 'www.') {
			$url = "http://{$url}";
		}

		$pid = $this->noparse_id();
		$this->noparse[$pid] = $url;

		if ($title != false) { // Ein Titel wurde angegeben
			$ahref = '<a href="<!PID:' . $pid . '>" target="_blank">' . $title . '</a>';
			return $ahref;
		} elseif ($this->profile['reduceUrl'] == 1 && mb_strlen($url) >= $this->profile['reducelength']) { // Die URL wird als Titel genommen und gekürzt
			$before = ceil($this->profile['reducelength'] / 5);
			$after = mb_strpos($url, '/', 8);
			if ($after === false) {
				$after = ceil($this->profile['reducelength'] / 3);
			}
			$newurl = mb_substr($url, 0, $after + 1) . $this->profile['reducesep'] . mb_substr($url, -$before);
			$pid2 = $this->noparse_id();
			$this->noparse[$pid2] = $newurl;
			$ahref = '<a href="<!PID:' . $pid . '>" target="_blank"><!PID:' . $pid2 . '></a>';
		} else { // Die URL wird ungekürzt als Titel genommen
			$ahref = '<a href="<!PID:' . $pid . '>" target="_blank"><!PID:' . $pid . '></a>';
		}

		return $prefix . $ahref . $suffix;
	}

	public function parse($rawText) {
		global $lang, $my;
		Debug::startMeasurement("BBCode::parse()");
		$this->cache_bbcode();
		$this->noparse = array();

		$text = preg_replace("/(\r\n|\r|\n)/u", "\n", $rawText);
		$text = viscacha_htmlspecialchars($text, ENT_NOQUOTES, false);

		$text = preg_replace('/\[hide\](.+?)\[\/hide\]/isu', '', $text); // Deprecated
		$text = str_ireplace('[reader]', '', $text); // Deprecated

		$text = empty($this->profile['disallow']['code']) ? preg_replace_callback('/\[code(=(\w+?))?\](.+?)\[\/code\](\n?)/isu', array(&$this, 'cb_hlcode'), $text) : $text;

		while (preg_match('/\[quote=(.+?)\](.+?)\[\/quote\]/isu', $text, $values)) {
			$pid = $this->noparse_id();
			if (is_url($values[1])) {
				$this->noparse[$pid] = '<a href="' . $values[1] . '" target="_blank">' . $values[1] . '</a>';
			} else {
				$this->noparse[$pid] = $values[1]; //"\\1";
			}
			$text = preg_replace('/\[quote=(.+?)\](.+?)\[\/quote\]\n?/isu', "<div class='bb_quote'><strong>" . $lang->phrase('bb_quote_by') . " <!PID:{$pid}>:</strong><br /><blockquote>\\2</blockquote></div>", $text, 1);
		}

		$text = $this->ListWorkAround($text);

		$text = preg_replace('/\[note=([^\]]+?)\](.+?)\[\/note\]/isu', '<em>\2</em> (\1)', $text); // For compatibility only

		$text = empty($this->profile['disallow']['img']) ? preg_replace_callback("~\[img\]([^?&=\[\]]+\.(png|gif|jpg|jpeg))\[\/img\]~isu", array($this, 'cb_image'), $text) : $text;
		$text = preg_replace_callback("~\[img\]{$this->url_regex2}\[\/img\]~isu", array(&$this, 'cb_plain_url'), $text); // Correct invalid image urls

		$text = preg_replace('/\[color=\#?([0-9A-F]{3,6})\](.+?)\[\/color\]/isu', '<span style="color: #\1">\2</span>', $text);
		$text = preg_replace('/\[align=(left|center|right|justify)\](.+?)\[\/align\]/isu', "<p style='text-align: \\1'>\\2</p>", $text);

		$text = preg_replace_callback("/\[email\]([a-z0-9\-_\.\+]+@[a-z0-9\-]+\.[a-z0-9\-\.]+?)\[\/email\]/isu", array(&$this, 'cb_mail'), $text);
		$text = empty($this->profile['disallow']['h']) ? preg_replace_callback('/\n?\[h=(middle|small|large)\](.+?)\[\/h\]\n?/isu', array(&$this, 'cb_header'), $text) : $text;
		$text = preg_replace_callback('/\[size=(small|extended|large)\](.+?)\[\/size\]/isu', array(&$this, 'cb_size'), $text);

		while (preg_match('/\[quote](.+?)\[\/quote\]/isu', $text)) {
			$text = preg_replace('/\[quote](.+?)\[\/quote\]\n?/isu', "<div class='bb_quote'><strong>" . $lang->phrase('bb_quote') . "</strong><br /><blockquote>\\1</blockquote></div>", $text);
		}
		while (empty($this->profile['disallow']['edit']) && preg_match('/\[edit\](.+?)\[\/edit\]/isu', $text)) {
			$text = preg_replace('/\[edit\](.+?)\[\/edit\]\n?/isu', "<div class='bb_edit'><strong>" . $lang->phrase('bb_edit_author') . "</strong><br /><ins>\\1</ins></div>", $text);
		}
		while (empty($this->profile['disallow']['edit']) && preg_match('/\[edit=(.+?)\](.+?)\[\/edit\]/isu', $text)) {
			$text = preg_replace('/\[edit=(.+?)\](.+?)\[\/edit\]\n?/isu', "<div class='bb_edit'><strong>" . $lang->phrase('bb_edit_mod') . " \\1:</strong><br /><ins>\\2</ins></div>", $text);
		}
		while (empty($this->profile['disallow']['ot']) && preg_match('/\[ot\](.+?)\[\/ot\]/isu', $text)) {
			$text = preg_replace('/\[ot\](.+?)\[\/ot\]\n?/isu', "<div class='bb_ot'><strong>" . $lang->phrase('bb_offtopic') . "</strong><br /><span>\\1</span></div>", $text);
		}

		$text = preg_replace('/\[b\](.+?)\[\/b\]/isu', "<strong>\\1</strong>", $text);
		$text = preg_replace('/\[i\](.+?)\[\/i\]/isu', "<em>\\1</em>", $text);
		$text = preg_replace('/\[u\](.+?)\[\/u\]/isu', "<u>\\1</u>", $text);
		$text = preg_replace('/\[sub\](.+?)\[\/sub\]/isu', "<sub>\\1</sub>", $text);
		$text = preg_replace('/\[sup\](.+?)\[\/sup\]/isu', "<sup>\\1</sup>", $text);
		$text = preg_replace('/\n?(\[hr\]){1,}\n?/isu', "<hr />", $text);

		$text = preg_replace('/\[tt\](.+?)\[\/tt\]/isu', "<tt>\\1</tt>", $text);

		$text = preg_replace_callback("~\[url\]{$this->url_regex2}\[\/url\]~isu", array(&$this, 'cb_plain_url'), $text);
		$text = preg_replace_callback("~\[url={$this->url_regex2}\](.+?)\[\/url\]~isu", array(&$this, 'cb_title_url'), $text);

		$text = preg_replace_callback('/\[table(=(\d+\%;head|head;\d+\%|\d+\%|head))?\]\n*(.+?)\n*\[\/table\]\n?/isu', array(&$this, 'cb_table'), $text);

		$text = str_ireplace("\t", "[tab]", $text); // Avoid conflicts with custom bb codes
		// Apply custom bb codes
		$text = $this->customBB($text); // BEFORE Auto URL Parsing
		// Auto replace urls
		$text = preg_replace_callback("~([\t\r\n\x20\(\),\.:;\?!\<>\[\]]|^){$this->url_regex}([\t\r\n\x20\(\)\[\]<>]|$)~isu", array(&$this, 'cb_auto_url'), $text);
		// Apply tabs finally
		$text = str_ireplace('[tab]', "&nbsp; &nbsp;&nbsp;", $text); // One normal whitespace to avoid really long lines

		if ($this->profile['useSmileys']) {
			$text = $this->parseSmileys($text);
		}
		$text = $this->wordwrap($text);

		$text = $this->parseDoc($text);
		$text = $this->nl2br($text);
		$text = $this->replacePID($text);
		$text = $this->censor($text);

		Debug::stopMeasurement("BBCode::parse()", array(
			'text' => substr($rawText, 0, 100),
			'profile' => $this->profile,
			'config' => $this->cfg,
			'type' => 'bbcode'
		));

		return $text;
	}

	protected function ListWorkAround($text) {
		if (empty($this->profile['disallow']['list'])) {
			$char = chr(5);
			$text = str_ireplace('[/list]', '[/list]' . $char, $text);
			$text = str_ireplace('[list', $char . '[list', $text);
			while (preg_match('/' . $char . '\[list(?:=(a|A|I|i|OL|ol))?\]([^' . $char . ']+)\[\/list\]' . $char . '/isu', $text, $treffer)) { // I and i modes are only for backward compatibility and have no effect at all
				$text = preg_replace_callback('/\n?' . $char . '\[list(?:=(a|A|I|i|OL|ol))?\]([^' . $char . ']+)\[\/list\]' . $char . '\n?/isu', array(&$this, 'cb_list'), $text);
			}
		}
		return $text;
	}

	protected function nl2br($text) {
		$text = str_ireplace('[br]', "<br />", $text);
		$text = str_replace("\n", "<br />", $text);
		return $text;
	}

	protected function parseDoc($text) {
		if ($this->profile['reduceEndChars'] == 1) {
			$text = preg_replace('/\!{2,}/iu', "!", $text);
			$text = preg_replace('/\?{2,}/iu', "?", $text);
			$text = preg_replace('/\.{4,}/iu', "...", $text);
		}
		if ($this->profile['reduceNL'] == 1) {
			$text = preg_replace("/\n{3,}/iu", "\n\n", $text);
		}
		return $text;
	}

	public function parseTitle($topic) {
		$topic = str_replace("\t", ' ', $topic);
		$topic = $this->censor($topic);
		if ($this->profile['topicuppercase'] == 1 && $topic == mb_strtoupper($topic)) {
			return mb_ucwords(strtolower($topic));
		} else {
			return $topic;
		}
	}

	protected function cb_table($data) {
		list(,, $args, $code) = $data;
		$table_content = array();
		$table_head = array();
		$table_rows = array();
		$table_cols = array();
		$bbcode_table = array(
			'width' => null,
			'head' => array(),
			'table' => array()
		);
		if (preg_match('~((\d+)\%)~u', $args, $matches)) {
			if ($matches[2] <= 100) {
				$bbcode_table['width'] = $matches[1];
			}
		}
		$args = explode(';', mb_strtolower($args));
		if (array_search('head', $args) === false) {
			$bbcode_table['head']['enabled'] = false;
		} else {
			$bbcode_table['head']['enabled'] = true;
		}

		$code = preg_replace("~[\n]+~u", "\n", $code);

		$table_content = explode("\n", $code);
		$bbcode_table['table']['rows'] = count($table_content);
		for ($i = 0; $i < $bbcode_table['table']['rows']; $i++) {
			// Testing for old style behaviour
			if (mb_stripos($table_content[$i], '[tab]') === false) {
				$table_content[$i] = explode('|', $table_content[$i]);
			} else {
				$table_content[$i] = preg_split('~\[tab\]~iu', $table_content[$i]);
			}
		}
		$bbcode_table['table']['cols'] = count($table_content[0]);

		if ($bbcode_table['table']['rows'] + $bbcode_table['table']['cols'] == 2) {
			return $code;
		}

		$bbcode_table['head']['enabled'] = ($bbcode_table['head']['enabled'] && $bbcode_table['table']['rows'] > 1);

		if ($bbcode_table['head']['enabled'] == true) {
			for ($i = 0; $i < ($bbcode_table['table']['cols']); $i++) {
				if (!empty($table_content[0][$i]) || (isset($table_content[0][$i]) && $table_content[0][$i] == 0)) {
					$table_head[$i] = $table_content[0][$i];
				} else {
					$table_head[$i] = '&nbsp;';
				}
			}
			for ($i = 1; $i < $bbcode_table['table']['rows']; $i++) {
				$table_content[($i - 1)] = $table_content[$i];
			}
			$bbcode_table['table']['rows'] --;
		}
		$table_rows = array();
		for ($i = 0; $i < $bbcode_table['table']['rows']; $i++) {
			for ($j = 0; $j < $bbcode_table['table']['cols']; $j++) {
				if (empty($table_content[$i][$j])) {
					$table_rows[$i][$j] = '&nbsp;';
				} else {
					$table_rows[$i][$j] = $table_content[$i][$j];
				}
			}
		}

		$style = ' style="width:' . floor(100 / $bbcode_table['table']['cols']) . '%;"';

		if ($bbcode_table['head']['enabled'] == true) {
			$table_head = '<tr><th' . $style . '>' . implode('</th><th' . $style . '>', $table_head) . '</th></tr>';
		} else {
			$table_head = '';
		}

		for ($i = 0; $i < $bbcode_table['table']['rows']; $i++) {
			$table_rows[$i] = '<td' . iif($bbcode_table['head']['enabled'], $style) . '>' . implode('</td><td' . iif($bbcode_table['head']['enabled'], $style) . '>', $table_rows[$i]) . '</td>';
			$table_rows[$i] = '<tr>' . $table_rows[$i] . '</tr>';
		}

		$table_rows = implode('', $table_rows);
		$table_html = '<table class="bb_table"';
		if ($bbcode_table['width'] != null) {
			$table_html .= ' style="width:' . $bbcode_table['width'] . ';"';
		}
		$table_html .= '>' . $table_head . $table_rows . '</table>';
		return $table_html;
	}

	public function parseSmileys($text) {
		$this->cache_smileys();
		foreach ($this->smileys as $smiley) {
			if (mb_strpos($text, $smiley['search']) !== false) {
				$pattern = '~(\r|\n|\t|\s|\>|\<|^)' . preg_quote($smiley['search'], '~') . '(\r|\n|\t|\s|\>|\<|$)~su';
				while (preg_match($pattern, $text)) {
					$text = preg_replace(
							$pattern, '\1<img src="' . $smiley['replace'] . '" border="0" alt="' . $smiley['desc'] . '" />\2', $text
					);
				}
			}
		}
		return $text;
	}

	public function getSmileys() {
		$this->cache_smileys();
		return $this->smileys;
	}

	public function getCustomBB() {
		$this->cache_bbcode();
		return $this->custombb;
	}

	public function existsProfile($name) {
		return isset($this->cfg[$name]);
	}

	public function setProfile($name = 'standard', $new = SP_CHANGE) {
		if ($new == SP_COPY) {
			$this->cfg[$name] = array();
			foreach ($this->profile as $key => $value) {
				if ($key == 'name') {
					$this->cfg[$name]['name'] = $name;
				} elseif ($key == 'disallow') {
					$this->cfg[$name]['disallow'] = array(
						'img' => FALSE,
						'code' => FALSE,
						'list' => FALSE,
						'edit' => FALSE,
						'ot' => FALSE,
						'h' => FALSE
					);
				} else {
					$this->cfg[$name][$key] = $value;
				}
			}
		} elseif ($new == SP_NEW) {
			$this->cfg[$name] = array(
				'name' => $name,
				'wordwrap' => 1,
				'wordwrap_char' => '-',
				'wordwrap_wordlength' => 70,
				'useSmileys' => 0,
				'SmileyUrl' => '',
				'useCensor' => 1,
				'reduceEndChars' => 1,
				'reduceNL' => 1,
				'reduceUrl' => 0,
				'topicuppercase' => 1,
				'reducelength' => 60,
				'reducesep' => ' ... ',
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

	public function setFunc($func) {
		$this->profile['disallow'][$func] = true;
	}

	public function setSmileys($use = 1) {
		$this->profile['useSmileys'] = $use;
		if (!isset($this->profile['SmileyUrl'])) {
			$this->profile['SmileyUrl'] = '';
		}
	}

	public function setSmileyDir($url = '') {
		$this->profile['SmileyUrl'] = $url;
	}

	public function setCensor($censor = 1) {
		$this->profile['useCensor'] = $censor;
	}

	public function setWordwrap($use = 1, $wordlength = 70, $char = ' ') {
		$this->profile['wordwrap'] = $use;
		$this->profile['wordwrap_wordlength'] = $wordlength;
		$this->profile['wordwrap_char'] = $char;
	}

	public function setDoc($reduce_endchars = 1, $reduce_nl = 1, $topicupper = 1) {
		$this->profile['reduceEndChars'] = $reduce_endchars;
		$this->profile['reduceNL'] = $reduce_nl;
		$this->profile['topicuppercase'] = $topicupper;
	}

	public function setURL($reduce_url, $maxurllength, $maxurltrenner) {
		$this->profile['reduceUrl'] = $reduce_url;
		$this->profile['reducelength'] = $maxurllength;
		$this->profile['reducesep'] = $maxurltrenner;
	}

	public function censor($text) {
		$this->cache_bbcode();
		if ($this->profile['useCensor'] == 2) {
			foreach ($this->bbcodes['censor'] as $word) {
				$letters = mb_str_split($word['search']);
				$word['search'] = array();
				foreach ($letters as $letter) {
					$word['search'][] = preg_quote($letter, '~');
				}
				$word['search'] = implode("(\s|\.|\[[^\]]+?\])?", $word['search']);
				$text = preg_replace("~" . $word['search'] . "~isu", $word['replace'], $text);
			}
		} elseif ($this->profile['useCensor'] == 1) {
			foreach ($this->bbcodes['censor'] as $word) {
				$text = str_ireplace($word['search'], $word['replace'], $text);
			}
		}
		return $text;
	}

	protected function cbb_helper($matches) {
		if ($this->currentCBB != null) {
			$index = $this->currentCBB['twoparams'] ? 2 : 1;
			$bbcodereplacement = preg_replace('~\{param(:(?:\\\}|[^\}])+)?\}~iu', $matches[$index], $this->currentCBB['bbcodereplacement']);
			if ($this->currentCBB['twoparams']) {
				$pid = $this->noparse_id();
				$this->noparse[$pid] = $matches[1];
				$bbcodereplacement = preg_replace('~\{option(:(?:\\\}|[^\}])+)?\}~iu', "<!PID:{$pid}>", $bbcodereplacement);
			}
			return $bbcodereplacement;
		} else {
			return $matches[0];
		}
	}

	protected function customBB($text) {
		$this->getCustomBB();
		foreach ($this->custombb as $re) {
			$this->currentCBB = $re;
			$text = preg_replace_callback('~' . $re['bbregexp'] . '~isu', array(&$this, 'cbb_helper'), $text);
			$this->currentCBB = null;
		}
		return $text;
	}

	public function wordwrap($text, $length = FALSE) {
		if (empty($text) || $this->profile['wordwrap'] == 0) {
			return $text;
		}
		if ($length == FALSE) {
			$length = $this->profile['wordwrap_wordlength'];
		}
		$text = preg_replace("~([^\n\r\s&\./<>\[\]\\\]{" . intval($length) . "})~iu", "\\1" . $this->profile['wordwrap_char'], $text);
		return $text;
	}

	protected function cache_bbcode() {
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

	protected function cache_smileys() {
		if ($this->smileys == null) {
			global $scache;
			$cache = $scache->load('smileys');
			$cache->seturl($this->profile['SmileyUrl']);
			$this->smileys = $cache->get();
		}
	}

	function getEditorArea($id, $content = '', $taAttr = '', $maxlength = null, $disable = array()) {
		global $tpl, $lang, $scache, $config;
		if ($maxlength == null) {
			$maxlength = $config['maxpostlength'];
		}
		if (!is_array($disable)) {
			$disable = array($disable);
		}

		$lang->group("bbcodes");

		$taAttr = ' ' . trim($taAttr);

		$cbb = $this->getCustomBB();
		foreach ($cbb as $key => $bb) {
			if (empty($bb['buttonimage'])) {
				unset($cbb[$key]);
				continue;
			}
			$cbb[$key]['title'] = viscacha_htmlspecialchars($bb['title']);
			if ($bb['twoparams']) {
				$cbb[$key]['href'] = "InsertTags('{$id}', '[{$bb['bbcodetag']}=]','[/{$bb['bbcodetag']}]');";
			} else {
				$cbb[$key]['href'] = "InsertTags('{$id}', '[{$bb['bbcodetag']}]','[/{$bb['bbcodetag']}]');";
			}
		}

		$this->cache_smileys();
		$smileys = array(0 => array(), 1 => array());
		foreach ($this->smileys as $bb) {
			if ($bb['show'] == 1) {
				$smileys[1][] = $bb;
			} else {
				$smileys[0][] = $bb;
			}
		}

		$tpl->globalvars(compact("id", "content", "taAttr", "cbb", "smileys", "maxlength", "disable"));
		return $tpl->parse("main/bbhtml");
	}

	public function replaceTextOnce($original, $newindex) {
		global $lang;
		$lang->assign('originalid', $original);
		return $lang->phrase($newindex);
	}

}

function BBProfile(&$bbcode, $profile = 'standard') {
	global $config, $my, $lang;
	if (!$bbcode->existsProfile($profile)) {
		$lang->group("bbcodes");

		if ($profile == 'signature') {
			$bbcode->setProfile('signature', SP_NEW);
			$bbcode->setProfile($profile, SP_NEW);
			$bbcode->setCensor($config['censorstatus']);
			$bbcode->setWordwrap($config['wordwrap'], $config['maxwordlength'], $config['maxwordlengthchar']);
			$bbcode->setDoc($config['reduce_endchars'], $config['reduce_nl'], $config['topicuppercase']);
			$bbcode->setURL($config['reduce_url'], $config['maxurllength'], $config['maxurltrenner']);
			$bbcode->setSmileyDir($config['smileyurl']);
			$bbcode->setSmileys(1);
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
		} else {
			$bbcode->setProfile($profile, SP_NEW);
			$bbcode->setCensor($config['censorstatus']);
			$bbcode->setWordwrap($config['wordwrap'], $config['maxwordlength'], $config['maxwordlengthchar']);
			$bbcode->setDoc($config['reduce_endchars'], $config['reduce_nl'], $config['topicuppercase']);
			$bbcode->setURL($config['reduce_url'], $config['maxurllength'], $config['maxurltrenner']);
			$bbcode->setSmileyDir($config['smileyurl']);
		}
	} else {
		$bbcode->setProfile($profile, SP_CHANGE);
	}
}

define('TOOLBAR_STATUS', 1);
define('TOOLBAR_FORMATTING', 2);
define('TOOLBAR_SMILEYS', 3);
