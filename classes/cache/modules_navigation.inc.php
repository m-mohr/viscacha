<?php
class cache_modules_navigation extends CacheItem {

	function load () {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->query("SELECT id, name, link, param, groups, sub, module, position FROM {$db->pre}menu WHERE active = '1' ORDER BY position, ordering, id");
		    $this->data = array();
		    while ($row = $db->fetch_assoc($result)) {
		        if (!isset($this->data[$row['position']])) {
		            $this->data[$row['position']] = array();
		        }
		        if (!isset($this->data[$row['position']][$row['sub']])) {
		            $this->data[$row['position']][$row['sub']] = array();
		        }
		        $this->data[$row['position']][$row['sub']][] = $row;
		    }
		    $this->export();
		}
	}

}
?>