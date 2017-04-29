<?php
class cache_bbcode extends CacheItem {
	function load () {
		global $db;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$this->data = array(
				'censor' => array(),
				'bb' => array()
			);
			$result = $db->execute("SELECT * FROM {$db->pre}textparser");
			while ($bb = $result->fetch()) {
				$this->data['censor'][] = $bb;
			}
			$this->export();
		}
	}
}
?>