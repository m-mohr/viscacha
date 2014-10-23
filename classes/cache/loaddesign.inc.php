<?php
class cache_loaddesign extends CacheItem {

	function load () {
		global $db;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$result = $db->query("SELECT id, template, stylesheet, images, name FROM {$db->pre}designs WHERE publicuse = '1'",__LINE__,__FILE__);
			$this->data = array();
			while ($row = $db->fetch_assoc($result)) {
				$this->data[$row['id']] = $row;
			}
			$this->export();
		}
	}
	
	function get ($fresh = false) {
		if ($fresh == true) {
			global $db;
			$result = $db->query("SELECT id, template, stylesheet, images, name FROM {$db->pre}designs",__LINE__,__FILE__);
			$design = array();
			while ($row = $db->fetch_assoc($result)) {
				$design[$row['id']] = $row;
			}
			return $design;
		}
		else {
			if ($this->data == null) {
				$this->load();
			}
			return $this->data;
		}
	}

}
?>