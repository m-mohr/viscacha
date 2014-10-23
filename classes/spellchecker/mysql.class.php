<?php

class spellchecker {

	var $dir_words = array();
	var $sug_words = array();
	var $txt_words = array();
	var $langcode = '';
	var $mistakes = array();
	var $errormsg = '';
	var $cache = array();
	var $suggest = array();
	var $ignore = 0;
	var $word = array();
	var $benchmarktime = 0;
	var $modus = 0;
	var $db = FALSE;

	function spellchecker($code, $ignore = 3, $modus = 0, $benchmark = FALSE) {
		if ($benchmark == TRUE) {
			$this->benchmark(1);
		}
		$this->langcode = $code;
		$this->ignore = $ignore;
		$this->modus = $modus;
	}

	function set_path($db) {
		$this->db = $db;
		return TRUE;
	}

	function init() {
		if (!$this->db) {
			$this->errormsg = 'No connection to database';
		}
	}
	
	function add($word) {
		$sqlwords = array();
		if (is_string($word)) {
			$word = addslashes($word);
			$sqlwords[] = "('{$word}', '{$this->langcode}')";
		}
		if (is_array($word)) {
			foreach ($word as $w) {
				$w = addslashes($w);
				$sqlwords[] = "('{$w}', '{$this->langcode}')";
			}
		}
		if (count($sqlwords) > 0) {
			$db->query("INSERT INTO {$db->pre}spellcheck (´word´, ´language´) VALUES ".implode(',', $sqlwords));
			if ($db->affected_rows() > 0) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	
	function get_words() {
		if (count($this->dir_words) > 0) {
			return TRUE;
		}
		else {
			$result = $this->db->query('SELECT word, SOUNDEX(word) AS snd FROM '.$this->db->pre.'spellcheck WHERE language = "'.$this->langcode.'"');
			while ($row = $this->db->fetch_assoc($result)) {
				$this->dir_words[] = $row['word'];
				$row['snd'] = substr($row['snd'], 0, 4);
				$this->sug_words[$row['snd']][] = $row['word'];
			}
			$this->db->free_result($result);
			return TRUE;
		}
	}

	function check_text($text) {
		$words = $this->split_text($text);
		$this->txt_words = array_unique($words);
		$this->get_words();
		$mistakes = array_diff($this->txt_words, $this->dir_words);
		foreach ($mistakes as $word) {
			if (strlen($word) > $this->ignore) {
				$this->mistakes[] = $word;
			}
		}
		return $this->mistakes;
	}
	
	function suggest_text ($mistakes = FALSE) {
		if ($mistakes == FALSE) {
			$mistakes = $this->mistakes;
		}
		$suggestions = array();
		foreach ($mistakes as $mistake) {
			$suggestions[$mistake] = array();
			$length = (strlen($mistake) > 6);
			if (strlen($mistake) > $this->ignore) {
				$snd = soundex($mistake);
				if (isset($this->sug_words[$snd])) {
					if ($this->modus == 0) {
						$suggestions[$mistake][] = $this->sug_words[$snd];
					}
					else {
						foreach ($this->sug_words[$snd] as $word) {
							if ($this->modus == 1) {
								if ($length) {
									$levenshtein = 3;
								}
								else {
									$levenshtein = 2;
								}
							}
							else {
								$levenshtein = 3;
							}
							if (levenshtein($word, $mistake) < $levenshtein) {
								$suggestions[$mistake][] = $word;
							}
						}
					}
				}
			}
		}
		return $suggestions;
	}

	function check($word) {
		$word = trim($word);
		if (strlen($word) <= $this->ignore) {
			$ret = TRUE;
		}
		elseif (isset($this->cache[$word]) == TRUE) {
			$ret = $this->cache[$word];
		}
		else {
			$this->get_words();
			if (!in_array($word, $this->dir_words)) {
				$this->mistakes[] = $word;
				$ret = FALSE;
			}
			else {
				$ret = TRUE;
			}
			$this->cache[$word] = $ret;
			$this->word = array(
				'word' => $word,
				'correct' => $ret
			);
		}
		return $ret;
	}

	function suggest ($mistake = FALSE) {
		if ($mistake == FALSE) {
			if ($this->word['correct'] == TRUE) {
				return TRUE;
			}
			else {
				$mistake = $this->word['word'];
			}
		}
		if (isset($this->suggest[$mistake])) {
			return $this->suggest[$mistake];
		}
		$this->suggest[$mistake] = array();
		$length = (strlen($mistake) > 6);
		if (strlen($mistake) > $this->ignore) {
			$snd = soundex($mistake);
			if (isset($this->sug_words[$snd])) {
				if ($this->modus == 0) {
					$this->suggest[$mistake][] = $this->sug_words[$snd];
				}
				else {
					foreach ($this->sug_words[$snd] as $word) {
						if ($this->modus == 1) {
							if ($length) {
								$levenshtein = 3;
							}
							else {
								$levenshtein = 2;
							}
						}
						else {
							$levenshtein = 3;
						}
						if (levenshtein($word, $mistake) < $levenshtein) {
							$this->suggest[$mistake][] = $word;
						}
					}
				}
			}
		}
		return $this->suggest[$mistake];
	}

	function error() {
		return $this->errormsg;
	}

	function split_text ($text) {
		$plain = preg_replace("/(<|\[|:).+?(]|>|:)/is", ' ', $text);
		$word_seperator = "0-9\\.,;:!\\?\\-\\|\n\r\s\"'\\[\\]\\{\\}\\(\\)\\/\\\\";
		$words = preg_split('/['.$word_seperator.']+?/', $plain, -1, PREG_SPLIT_NO_EMPTY);
		return $words;
	}

	function benchmark ($start = 0) {
		$zeitmessung = benchmarktime();
		if ($start == 0) {
			if ($this->benchmarktime == 0) {
				$this->errormsg = 'Benchmark was not started yet!';
				return -1;
			}
			$time = $zeitmessung-$this->benchmarktime; 
			return substr($time,0,6);
		}
		else {
			$this->benchmarktime = $zeitmessung;
		}
	}

}

?>