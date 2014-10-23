<?php
class cache_fileicons extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$result = $db->query("SELECT extension, icon FROM {$db->pre}filetypes");
			$this->data = array();
			while ($row = $db->fetch_assoc($result)) {
				$ext = explode(',', $row['extension']);
				foreach ($ext as $ft) {
					$this->data[$ft] = $row['icon'];
				}
			}
			$this->export();
		}
	}

}
?>