<?php
class cache_grabrss extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->query("SELECT id, file, title, entries, max_age FROM {$db->pre}grab",__LINE__,__FILE__);
		    $this->data = array();
		    while ($row = $db->fetch_assoc($result)) {
				$row['title'] = htmlentities($row['title'], ENT_QUOTES);
		        $this->data[$row['id']] = $row;
		    }
		    $this->export();
		}
	}

}
?>
