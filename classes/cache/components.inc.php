<?php
class cache_components extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->query("
				SELECT c.id, c.package, c.file
				FROM {$db->pre}component AS c
					LEFT JOIN {$db->pre}packages AS p ON c.package = p.id
				WHERE c.active = '1' AND p.active = '1'
			",__LINE__,__FILE__);
		    $this->data = array();
		    while ($comp = $db->fetch_assoc($result)) {
		        $this->data[$comp['id']] = $comp;
		    }
		    $this->export();
		}
	}

}
?>