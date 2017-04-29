<?php
class cache_categories extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->execute("SELECT id, name, description, parent, position FROM {$db->pre}categories ORDER BY position");
		    $this->data = array();
		    while ($row = $result->fetch()) {
		        $this->data[$row['id']] = $row;
		    }
		    $this->export();
		}
	}

}
?>