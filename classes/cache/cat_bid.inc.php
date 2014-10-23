<?php
class cache_cat_bid extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->query("SELECT name, id, bid, opt, optvalue, topics, prefix, c_order, topiczahl, forumzahl FROM {$db->pre}cat ORDER BY bid",__LINE__,__FILE__);
		    $this->data = array();
		    while ($row = $db->fetch_assoc($result)) {
		        $this->data[$row['id']] = $row;
		    }
		    $this->export();
		}
	}

}
?>