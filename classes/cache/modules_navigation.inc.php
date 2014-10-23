<?php
class cache_modules_navigation extends CacheItem {

	function load () {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->query("SELECT id, name, link, param, groups, sub, module FROM {$db->pre}menu WHERE active = '1' ORDER BY ordering, id",__LINE__,__FILE__);
		    $this->data = array();
		    while ($row = $db->fetch_assoc($result)) {
		        if (!isset($this->data[$row['sub']])) {
		            $this->data[$row['sub']] = array();
		        }
		        $this->data[$row['sub']][] = $row;
		    }
		    $this->export();
		}
	}

}
?>