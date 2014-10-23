<?php
class cache_parent_forums extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
			$parent = array();
			$result = $db->query("SELECT id, bid FROM {$db->pre}cat");
			while($row = $db->fetch_assoc($result)) {
				$parent[$row['id']] = $row['bid'];
			}
			$this->data = array();
			foreach ($parent as $id => $bid) {
				$this->data[$id] = array();
				$this->data[$id][] = $id;
				while ($bid > 0) {
					$this->data[$id][] = $bid;
					$bid = $parent[$bid];
				}
			}
			$this->export();
		}
	}

}
?>