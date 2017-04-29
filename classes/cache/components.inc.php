<?php
class cache_components extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$result = $db->execute("
				SELECT p.id AS cid, c.id, p.internal
				FROM {$db->pre}packages AS p
					LEFT JOIN {$db->pre}plugins AS c ON c.module = p.id
				WHERE c.active = '1' AND p.active = '1' AND c.position = CONCAT('component_', p.internal)
			");
			$this->data = array();
			while ($comp = $result->fetch()) {
				$this->data[$comp['cid']] = $comp;
			}
			$this->export();
		}
	}

}
?>