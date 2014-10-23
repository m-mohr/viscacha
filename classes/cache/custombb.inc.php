<?php
class cache_custombb extends CacheItem {
	function load () {
		global $db, $config;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$this->data = array();
			$result = $db->query("SELECT * FROM {$db->pre}bbcode ORDER BY id");
			while ($bb = $db->fetch_assoc($result)) {
				if ($bb['twoparams']) {
					$bb['bbcodereplacement'] = str_replace('{param}', '\2', $bb['bbcodereplacement']);
					$bb['bbcodereplacement'] = str_replace('{option}', '\1', $bb['bbcodereplacement']);
				}
				else {
					$bb['bbcodereplacement'] = str_replace('{param}', '\1', $bb['bbcodereplacement']);
				}
				$bb['bbcodereplacement'] = str_replace(array("\r\n", "\n"), "\r", $bb['bbcodereplacement']);
				if (!preg_match(URL_REGEXP, $bb['buttonimage'])) {
					if (!empty($bb['buttonimage']) && @file_exists(CBBC_BUTTONDIR.$bb['buttonimage'])) {
						$bb['buttonimage'] = $config['furl'].'/'.CBBC_BUTTONDIR.$bb['buttonimage'];
					}
					else {
						$bb['buttonimage'] = '';
					}
				}
				$this->data[] = $bb;
			}
			$this->export();
		}
	}
}
?>