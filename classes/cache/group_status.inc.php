<?php
class cache_group_status extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
			$this->data = array();
		    $result = $db->query("SELECT id, admin, guest, title, core FROM {$db->pre}groups ORDER BY core DESC",__LINE__,__FILE__);
		    while ($row = $db->fetch_assoc($result)) {
		        $this->data[$row['id']] = $row;
		    }
		    $this->export();
		}
	}

}
?>