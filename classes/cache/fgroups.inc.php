<?php
class cache_fgroups extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$fields = unserialize(file_get_contents('data/group_fields.php'));
			$keys = array_combine($fields['fFields'], range(1, count($fields['fFields'])));
			$result = $db->query('SELECT bid, gid, '.implode(', ', $fields['fFields']).' FROM '.$db->pre.'fgroups');
			$this->data = array();
			while ($row = $db->fetch_assoc($result)) {
				$this->data[$row['gid']][$row['bid']] = array_intersect_key($row, $keys);
			}
			$this->export();
		}
	}

	function getGlobal($groups) {
		if ($this->data == null) {
			$this->load();
		}
		$data = array();
		foreach ($groups as $id) {
			if (isset($this->data[$id])) {
				$data[$id] = $this->data[$id];
			}
		}
		return $data;
	}
	function getBoard($groups, $boards) {
		if ($this->data == null) {
			$this->load();
		}
		if (!is_array($boards)) {
			$boards = array($boards);
		}
		foreach ($groups as $gid) {
			if (isset($this->data[$gid])) {
				foreach ($boards as $bid) {
					if (isset($this->data[$gid][$bid])) {
						$data[$bid][$gid] = $this->data[$gid][$bid];
					}
				}
			}
		}
		return $this->data;
	}

}
?>