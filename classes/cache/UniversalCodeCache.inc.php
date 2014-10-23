<?php
class UniversalCodeCache extends CacheItem {

	var $lng;
	var $source;

	function UniversalCodeCache() {
		$this->name = '';
		$this->file = '';
		$this->data = null;
		$this->source = '';
		$this->max_age = null;
		$this->lng = '';
	}

	function setHash($hash) {
		$this->name = $hash;
		$this->file = "cache/geshicode/{$this->name}.inc.php";
		return $this->exists();
	}

	function setData($source, $lng = '') {
		global $gpc;
		$this->setHash(md5($lng.$source));
		$this->source = $gpc->plain_str($source, false);
		if (!empty($lng)) {
			$this->lng = strtolower($lng);
		}
		else {
			$this->lng = 'text';
		}
	}

	function load() {
		if ($this->exists() == true) {
		    $this->import();
		    $this->lng = $this->data['language'];
		    $this->source = $this->data['source'];
		}
		else {
			if (!class_exists('GeSHi')) {
				include_once('classes/class.geshi.php');
			}
			global $lang;
			$language = $this->hasLanguage() ? $this->lng : 'text';
			$geshi = new GeSHi($this->source, $language, 'classes/geshi');
			$geshi->set_encoding($lang->charset());
			$geshi->enable_classes(false);
			$geshi->set_header_type(GESHI_HEADER_DIV);
			$geshi->enable_keyword_links(true);
			$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5);
			if (!$this->hasLanguage()) {
				$geshi->enable_highlighting(false);
			    $geshi->set_numbers_highlighting(false);
			    $geshi->set_brackets_highlighting(false);
				$language = '';
			}
			else {
				$language = $geshi->get_language_name($language);
			}

			$this->data = array(
				'language' => $language,
				'parsed' => $geshi->parse_code(),
				'source' => $this->source
			);
		    $this->export();
		}
	}

	function rebuildable() {
		return false;
	}

	function administrable() {
		return false;
	}

	function hasLanguage() {
		return !empty($this->lng);
	}

	function getHash() {
		return $this->name;
	}

}
?>