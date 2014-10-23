<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

class spellchecker {

	var $dir_words = array();
	var $txt_words = array();
	var $file = '';
	var $langcode = '';
	var $mistakes = array();
	var $errormsg = '';
	var $cache = array();
	var $suggest = array();
	var $ignore = 0;
	var $word = array();
	var $benchmarktime = 0;
	var $modus = 0;
	var $path = './dict/';

	function spellchecker($code, $ignore = 3, $modus = 0, $benchmark = FALSE) {
		if ($benchmark == TRUE) {
			$this->benchmark(1);
		}
		$this->langcode = $code;
		$this->ignore = $ignore;
		$this->modus = $modus;
	}

	function set_path($path) {
		$this->path = $path;
		return TRUE;
	}

	function init() {
		$this->file = $this->path.$this->langcode.".dic";
		if (file_exists($this->file) == TRUE) {
			$words = file($this->file);
			$this->dir_words = array_map('trim', $words);
		}
		else {
			$this->errormsg = 'Could not open dictionary "'.$this->langcode.'" at '.$this->file;
		}
	}

	function add($word) {
		$wl = file($this->file);
		$wl = array_map('trim', $wl);
		if (is_string($word)) {
			$wl[] = $word;
		}
		if (is_array($word)) {
			$wl = array_merge($wl, $word);
		}
		$wl = implode("\n", $wl);
   		if (!$handle = fopen($this->file, 'w')) {
			return false;
   		}
   		if (fwrite($handle, $wl) === FALSE) {
       		return false;
   		}
   		fclose($handle);
   		return true;
	}

	function lcfirst($p) {
		return strtolower($p{0}).substr($p, 1);
	}

	// Need Functions lcfirst
	function check_text($text) {
		$words = $this->split_text($text);
		$this->txt_words = array_unique($words);
		$mistakes = array_diff($this->txt_words, $this->dir_words);
		foreach ($mistakes as $word) {
			if (strlen($word) > $this->ignore) {
				$ord = ord($word);
				if ($ord > 96 && $ord < 123) {
					$result = array_search(ucfirst($word), $this->dir_words);
					if ($result === FALSE) {
						$this->mistakes[] = $word;
					}
				}
				elseif ($ord > 64 && $ord < 91) {
					$result = array_search($this->lcfirst($word), $this->dir_words);
					if ($result === FALSE) {
						$this->mistakes[] = $word;
					}
				}
				else {
					$this->mistakes[] = $word;
				}
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
			if (strlen($mistake) > $this->ignore) {
				$length = (strlen($mistake) > 5);
				$letter = $mistake{0};
				foreach ($this->dir_words as $word) {
					if ($this->modus == 0) {
						if ($word{0} == $letter && $length) {
							$levenshtein = 3;
						}
						else {
							$levenshtein = 2;
						}
					}
					elseif ($this->modus == 1) {
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
		return $suggestions;
	}

	function check($word) {
		$word = trim($word);
		if (isset($this->cache[$word]) == TRUE) {
			$ret = $this->cache[$word];
		}
		else {
			$result = array_search($word, $this->dir_words);
			if ($result === FALSE) {
				$ret = FALSE;
			}
			else {
				$ret = TRUE;
			}
			if ($ret == FALSE) {
				$ord = ord($word);
				if ($ord > 96 && $ord < 123) {
					$result = array_search(ucfirst($word), $this->dir_words);
					if ($result !== FALSE) {
						$ret = TRUE;
					}
				}
				elseif ($ord > 64 && $ord < 91) {
					$result = array_search($this->lcfirst($word), $this->dir_words);
					if ($result !== FALSE) {
						$ret = TRUE;
					}
				}
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
		$this->suggest[$mistake] = array();
		if (strlen($mistake) > $this->ignore) {
			$length = (strlen($mistake) > 5);
			$letter = $mistake{0};
			foreach ($this->dir_words as $word) {
				if ($this->modus == 0) {
					if ($word{0} == $letter && $length) {
						$levenshtein = 3;
					}
					else {
						$levenshtein = 2;
					}
				}
				elseif ($this->modus == 1) {
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