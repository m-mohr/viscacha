<?php
class cache_custombb extends CacheItem {
	function getRegexp($type, $param = true) {
		switch (\Str::lower($type)) {
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
				if (count($parts) == 2 && \Str::lower($parts[0]) == 'regexp') {
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
			$result = $db->execute("SELECT * FROM {$db->pre}bbcode ORDER BY id");
			while ($bb = $result->fetch()) {
				preg_match('~\{param(?::((?:\\\}|[^\}])+))?\}~iu', $bb['replacement'], $type); // Old: \{param(:(\\\}|[^\}]+))?\}
				if (empty($type[1])) {
					$type[1] = null;
				}
				$param = '('.$this->getRegexp($type[1]).')';

				if ($bb['twoparams']) {
					preg_match('~\{option(?::((?:\\\}|[^\}])+))?\}~iu', $bb['replacement'], $type); // Old: \{option(:(\\\}|[^\}]+))?\}
					// Paramter for Opening Tag
					if (empty($type[1])) {
						$type[1] = null;
					}
					$option = '=('.$this->getRegexp($type[1], false).')';
				}
				else {
					$option = '';
				}
				$bb['replacement'] = str_replace(array("\r\n", "\n"), "\r", $bb['replacement']);

				if (!is_url($bb['buttonimage'])) {
					if (!empty($bb['buttonimage']) && @file_exists(CBBC_BUTTONDIR.$bb['buttonimage'])) {
						$bb['buttonimage'] = $config['furl'].'/'.CBBC_BUTTONDIR.$bb['buttonimage'];
					}
					else {
						$bb['buttonimage'] = '';
					}
				}

				$bb['bbregexp'] = '\['.$bb['tag'].$option.'\]'.$param.'\[\/'.$bb['tag'].'\]';

				$this->data[] = $bb;
			}
			$this->export();
		}
	}
}
?>