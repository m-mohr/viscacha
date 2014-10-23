<?php
class cache_components extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->query("SELECT id, file FROM {$db->pre}component WHERE active = '1'",__LINE__,__FILE__);
		    $this->data = array();
		    while ($comp = $db->fetch_assoc($result)) {
		        $this->data[$comp['id']] = $comp;
		    }
		    $this->export();
		}
	}

}
?>