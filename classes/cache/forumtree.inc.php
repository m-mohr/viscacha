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
			$full = array();
			$result = $db->query("SELECT b.id, b.bid, b.cid FROM {$db->pre}cat AS b LEFT JOIN {$db->pre}categories AS c ON c.id = b.cid ORDER BY c.c_order, c.id, b.c_order, b.id");
			while($row = $db->fetch_assoc($result)) {
				if ($row['bid'] == 0) {
					$parent[$row['cid']][$row['id']] = array();
				}
				else {
					$sub[$row['bid']][$row['cid']][$row['id']] = array();
				}
				$full[] = $row['cid'];
			}
			$result = $db->query("SELECT id FROM {$db->pre}categories ORDER BY c_order, id");
			while ($row = $db->fetch_assoc($result)) {
				$empty[] = $row['id'];
			}
			$empty = array_diff($empty, $full);
	
			$this->data = $this->forumtree_array($parent, $sub);
			foreach ($empty as $row) {
				$this->data[$row] = array();	
			}
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