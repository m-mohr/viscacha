<?php
class cache_memberdata extends CacheItem {
	function load() {
		global $db, $gpc;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$this->data = array();
			$result = $db->query("SELECT id, name FROM {$db->pre}user",__LINE__,__FILE__);
			while ($row = $db->fetch_assoc($result)) {
				$this->data[$row['id']] = $gpc->prepare($row['name']);
			}
			$olduserdata = file('data/deleteduser.php');
			foreach ($olduserdata as $row) {
				$row = trim($row);
				if (!empty($row)) {
					$row = explode("\t", $row);
					$this->data[$row[0]] = $row[1];
				}
			}
			$this->export();
		}
	}
}
?>
