<?php
class cache_loadlanguage extends CacheItem {

	function load () {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->query("SELECT id, language, detail FROM {$db->pre}language WHERE publicuse != '0'",__LINE__,__FILE__);
		    $this->data = array();
		    while ($row = $db->fetch_assoc($result)) {
		        $this->data[$row['id']] = $row;
		    }
		    $this->export();
		}
	}

}
?>