<?php
class cache_version_check extends CacheItem {

	private $tempXml;
	
	function cache_version_check ($filename, $cachedir = "cache/") {
		$this->CacheItem($filename, $cachedir);
		$this->max_age = 60*60*24;
	}

	function load () {
		global $config;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$comp = get_remote('http://version.viscacha.org/compare/?version='.base64_encode($config['version']));
			if ($comp < 1 && $comp > 3) {
				$comp = 0;
			}

			$current_version = get_remote('http://version.viscacha.org/version');
			if ($current_version == REMOTE_CLIENT_ERROR || $current_version == REMOTE_INVALID_URL) {
				$current_version = null;
			}

			$this->data = array(
				'comp' => $comp,
				'version' => $current_version,
				'news' => ''
			);

			if (!$this->readRssInfo()) {
				$this->data['news'] = get_remote('http://version.viscacha.org/news');
			}
			
			$this->export();
		}
	}
	
	function readRssInfo() {
		if (!viscacha_function_exists('xml_parser_create')) {
			return false;
		}

		global $config;
		$rssData = get_remote('http://version.viscacha.org/news/rss/?version='.base64_encode($config['version']));

		$xml = xml_parser_create();
		xml_parser_set_option($xml, XML_OPTION_CASE_FOLDING, true);
		xml_set_element_handler($xml, array($this, "startRssElement"), array($this, "endRssElement"));
		xml_set_character_data_handler($xml, array($this, "getRssElementData"));
		$success = xml_parse($xml, $rssData);
		xml_parser_free($xml);

		if (!$success || empty($this->data['news'])) {
			$this->data['news'] = '';
			return false;
		}
		else {
			$this->data['news'] = '<ul>'.$this->data['news'].'</ul>';
			return true;
		}
	}
	
	function startRssElement($parser, $name, $attrs) {
		$name = strtolower($name);
		switch($name) {
			case 'item':
				$this->tempXml = array(
					'currentElement' => null,
					'elements' => array(
						'title' => '',
						'link' => '',
						'description' => '',
						'pubDate' => ''
					)
				);
				break;
			case 'title':
			case 'link':
			case 'description':
			case 'pubDate':
				if (is_array($this->tempXml)) {
					$this->tempXml['currentElement'] = $name;
				}
				break;
		}
	}

	function endRssElement($parser, $name) {
		$name = strtolower($name);
		if (is_array($this->tempXml)) {
			switch($name) {
				case 'item':
					if (!empty($this->tempXml['elements']['title'])) {
						$this->data['news'] .= '<li><a href="'.$this->tempXml['elements']['link'].'" style="font-weight: bold;" target="_blank">'.$this->tempXml['elements']['title'].'</a>';
						if (!empty($this->tempXml['elements']['description'])) {
							$this->data['news'] .= '<br /><span style="font-size: 0.9em;">'.$this->tempXml['elements']['description'].'</span>';
						}
						$this->data['news'] .= '</li>';
					}
					$this->tempXml = null;
					break;
				case 'title':
				case 'link':
				case 'description':
				case 'pubDate':
					$this->tempXml['elements']['description'] = htmlentities(trim($this->tempXml['elements']['description']));
					$this->tempXml['currentElement'] = null;
					break;
			}
		}
	}

	function getRssElementData($parser, $data) {
		if (is_array($this->tempXml) && !empty($this->tempXml['currentElement'])) {
			$this->tempXml['elements'][$this->tempXml['currentElement']] .= $data;
		}
	}
	
}
?>