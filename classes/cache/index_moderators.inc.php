<?php
class cache_index_moderators extends CacheItem {

	function __construct($filename, $cachedir) {
		parent::__construct($filename, $cachedir);
		$this->max_age = 60*60; // Maximal 1 h alt
	}

	function load() {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
			$result = $db->execute("
				SELECT u.id AS mid, u.name, m.bid
				FROM {$db->pre}moderators AS m
					LEFT JOIN {$db->pre}user AS u ON u.id = m.mid
			");
		    $this->data = array();
		    while($row = $result->fetch()) {
		    	$this->data[$row['bid']][] = $row;
		    }
			$this->export();
		}
	}

}
?>