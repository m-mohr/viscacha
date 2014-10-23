<?php
class cache_grabrss extends CacheItem {

	function load() {
		global $db, $gpc;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->query("SELECT id, file, title, entries, max_age FROM {$db->pre}grab",__LINE__,__FILE__);
		    $this->data = array();
		    while ($row = $db->fetch_assoc($result)) {
				$row['max_age'] = $row['max_age'] * 60; // Calculate the seconds
		        $this->data[$row['id']] = $row;
		    }
		    $this->export();
		}
	}

}
?>
