<?php
class cache_spiders extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
			// ORDER BY for Optimization. Often found crawlers are first in list and the script can stopp earlier [see slog::log_robot()]
		    $result = $db->query("SELECT id, user_agent, bot_ip, name, type FROM {$db->pre}spider ORDER BY bot_visits DESC");
		    $this->data = array();
		    while ($row = $db->fetch_assoc($result)) {
		        $this->data[$row['id']] = $row;
		    }
		    $this->export();
		}
	}

}
?>