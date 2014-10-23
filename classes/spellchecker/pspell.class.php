<?php

class spellchecker {

	var $langcode = '';
	var $mistakes = array();
	var $errormsg = '';
	var $ignore = 0;
	var $benchmarktime = 0;
	var $resid;
	var $modus = 0;
	var $lastword = '';

	function spellchecker($code, $ignore = 3, $modus = 0, $benchmark = FALSE) {
		if ($benchmark == TRUE) {
			$this->benchmark(1);
		}
		$this->langcode = $code;
		$this->ignore = $ignore;
		$this->modus = $modus;
	}

	function set_path($path) {
		return TRUE;
	}

	function init() {
		$link = pspell_config_create($this->langcode);
		pspell_config_ignore ($link, $this->ignore);
		if ($this->modus == 0) {
			pspell_config_mode($link, PSPELL_FAST);
		}
		elseif ($this->modus == 2) {
			pspell_config_mode($link, PSPELL_BAD_SPELLERS);
		}
		else {
			pspell_config_mode($link, PSPELL_NORMAL);
		}
		$this->resid = @pspell_new_config($link);

		if (!$this->resid) {
			$this->errormsg = 'Could not open dictionary "'.$this->langcode.'"';
		}
	}
	
	function check_text($text) {
		$words = $this->split_text($text);
		foreach ($words as $word) {
			if (!$this->check($word)) {
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
			$suggestions[$mistake] = $this->suggest($mistake);
		}
		return $suggestions;
	}

	function check($word) {
		$word = trim($word);
		$ret = pspell_check($this->resid, $word);
		$this->lastword = $word;
		return $ret;
	}

	function suggest ($mistake) {
		if ($mistake == FALSE) {
			$mistake = $this->lastword;
		}
		$suggest = pspell_suggest($this->resid, $mistake);
		return $suggest;
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
