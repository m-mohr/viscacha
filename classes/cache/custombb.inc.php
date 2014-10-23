<?php
class cache_custombb extends CacheItem {
	function load () {
		global $db;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$this->data = array();
			$result = $db->query("SELECT * FROM {$db->pre}bbcode ORDER BY id",__LINE__,__FILE__);
			while ($bb = $db->fetch_assoc($result)) {
				if ($bb['twoparams']) {
					$bb['bbcodereplacement'] = str_replace('{param}', '\2', $bb['bbcodereplacement']);
					$bb['bbcodereplacement'] = str_replace('{option}', '\1', $bb['bbcodereplacement']);
				}
				else {
					$bb['bbcodereplacement'] = str_replace('{param}', '\1', $bb['bbcodereplacement']);
				}
				$this->data[] = $bb;
			}
			$this->export();
		}
	}
}
?>