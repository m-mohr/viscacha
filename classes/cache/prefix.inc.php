<?php
class cache_prefix extends CacheItem {

	function load () {
		global $db;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$result = $db->execute("SELECT * FROM {$db->pre}prefix");
			$this->data = array();
			while ($row = $result->fetch()) {
				if (!isset($this->data[$row['bid']])) {
					$this->data[$row['bid']] = array();
				}
				$this->data[$row['bid']][$row['id']] = array('value' => $row['value'], 'standard' => $row['standard']);
			}
			$this->export();
		}
	}
	
	function get($board = null) {
		if ($this->data == null) {
			$this->load();
		}
		if ($board != null) {
			if (!isset($this->data[$board])) {
				$this->data[$board] = array();
			}
			return $this->data[$board];
		}
		else {
			return $this->data;
		}
	}
	
	function addEmptyPrefix(array $list) {
		global $lang;
		$emptyPrefix = array(0 => array('value' => $lang->phrase('prefix_empty'), 'standard' => "0"));
		$list = $emptyPrefix + $list;
		return $list;
	}

}
?>