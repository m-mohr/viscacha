<?php
class cache_spiders extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->query("SELECT id, user_agent, bot_ip, name, type FROM {$db->pre}spider");
		    $this->data = array();
		    while ($row = $db->fetch_assoc($result)) {
		        $this->data[$row['id']] = $row;
		    }
		    $this->export();
		}
	}

}
?>