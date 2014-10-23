<?php
class cache_forumtree extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == TRUE) {
		    $this->import();
		}
		else {
			$parent = array();
			$sub = array();
			$empty = array();
			$result = $db->query("
			SELECT f.id, c.parent AS bid, f.parent AS cid, c.id AS cat_id
			FROM {$db->pre}categories AS c 
				LEFT JOIN {$db->pre}forums AS f ON c.id = f.parent 
			ORDER BY c.position, f.position
			");
			while($row = $db->fetch_assoc($result)) {
				if (!empty($row['id'])) {
					if ($row['bid'] == 0) {
						$parent[$row['cid']][$row['id']] = array();
					}
					else {
						$sub[$row['bid']][$row['cid']][$row['id']] = array();
					}
				}
				else {
					if ($row['bid'] == 0) {
						$parent[$row['cat_id']] = array();
					}
					else {
						$sub[$row['bid']][$row['cat_id']] = array();
					}
				}
			}
			$this->data = $this->forumtree_array($parent, $sub);
			$this->export();
		}
	}
	function forumtree_array($temp, $sub) {
		foreach ($temp as $cid => $boards) {
			foreach ($boards as $bid => $arr) {
				if (isset($sub[$bid])) {
					$sub[$bid] = $this->forumtree_array($sub[$bid], $sub);
					$temp[$cid][$bid] = $sub[$bid];
				}
			}
		}
		return $temp;
	}

}
?>