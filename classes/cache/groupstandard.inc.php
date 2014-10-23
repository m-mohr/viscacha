<?php
class cache_groupstandard extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->query("SELECT id, guest FROM {$db->pre}groups WHERE core = '1' AND admin != '1' LIMIT 2",__LINE__,__FILE__);
		    $this->data = array();
		    while ($id = $db->fetch_assoc($result)) {
		    	if ($id['guest'] == 1) {
		        	$this->data['group_guest'] = $id['id'];
		        }
		        else {
		        	$this->data['group_member'] = $id['id'];
		        }
		    }
		    $this->export();
		}
	}

}
?>