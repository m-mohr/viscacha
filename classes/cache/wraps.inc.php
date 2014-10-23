<?php
class cache_wraps extends CacheItem {

	function load () {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->query("SELECT id, title FROM {$db->pre}documents WHERE active = '1'",__LINE__,__FILE__);
		    $this->data = array();
		    while ($row = $db->fetch_assoc($result)) {
		        $this->data[$row['id']] = $row['title'];
		    }
		    $this->export();
		}
	}

}
?>