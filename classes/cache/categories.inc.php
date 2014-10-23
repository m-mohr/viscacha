<?php
class cache_categories extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->query("SELECT id, name, description, parent, position FROM {$db->pre}categories ORDER BY position",__LINE__,__FILE__);
		    $this->data = array();
		    while ($row = $db->fetch_assoc($result)) {
		        $this->data[$row['id']] = $row;
		    }
		    $this->export();
		}
	}

}
?>