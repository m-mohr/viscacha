<?php
class cache_custombb extends CacheItem {
	function getRegexp($type, $param = true) {
		switch (strtolower($type)) {
			case 'hexcolor':
				return '#?[\da-f]{3,6}';
			case 'int':
				return '\d+';
			case 'float':
				return '(?:-|+)?\d+(?:\.|,)?\d*';
			case 'hex':
				return '[\da-f]{1,}';
			case 'simpletext':
				return '[^><\[\]\'"]+';
			case 'url':
				return URL_REGEXP;
			case 'email':
				return EMAIL_REGEXP;
			case 'alnum':
				return '[a-z\d]+';
			case 'alpha':
				return '[a-z]';
			default:
				$parts = explode(':', $type, 2);
				if (count($parts) == 2 && strtolower($parts[0]) == 'regexp') {
					return $parts[1];
				}
				else if ($param) {
					return '.+?';
				}
				else {
					return '[^\]\'\"\r\n\t]*?';
				}
		}
	}

	function load () {
		global $db, $config;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$this->data = array();
			$result = $db->query("SELECT * FROM {$db->pre}bbcode ORDER BY id");
			while ($bb = $db->fetch_assoc($result)) {
				preg_match('~\{param(?::((?:\\\}|[^\}])+))?\}~i', $bb['bbcodereplacement'], $type); // Old: \{param(:(\\\}|[^\}]+))?\}
				if (empty($type[1])) {
					$type[1] = null;
				}
				$param = '('.$this->getRegexp($type[1]).')';

				if ($bb['twoparams']) {
					preg_match('~\{option(?::((?:\\\}|[^\}])+))?\}~i', $bb['bbcodereplacement'], $type); // Old: \{option(:(\\\}|[^\}]+))?\}
					// Paramter for Opening Tag
					if (empty($type[1])) {
						$type[1] = null;
					}
					$option = '=('.$this->getRegexp($type[1], false).')';
				}
				else {
					$option = '';
				}
				$bb['bbcodereplacement'] = str_replace(array("\r\n", "\n"), "\r", $bb['bbcodereplacement']);

				if (!preg_match('~^'.URL_REGEXP.'$~i', $bb['buttonimage'])) {
					if (!empty($bb['buttonimage']) && @file_exists(CBBC_BUTTONDIR.$bb['buttonimage'])) {
						$bb['buttonimage'] = $config['furl'].'/'.CBBC_BUTTONDIR.$bb['buttonimage'];
					}
					else {
						$bb['buttonimage'] = '';
					}
				}

				$bb['bbregexp'] = '\['.$bb['bbcodetag'].$option.'\]'.$param.'\[\/'.$bb['bbcodetag'].'\]';

				$this->data[] = $bb;
			}
			$this->export();
		}
	}
}
?>