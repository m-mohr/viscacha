<?php
class cache_team_ag extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->query("SELECT id, gmod, admin FROM {$db->pre}groups WHERE admin = '1' OR gmod = '1'",__LINE__,__FILE__);
		    $this->data = array('gmod' => array(), 'admin' => array());
		    while ($id = $db->fetch_assoc($result)) {
		    	if ($id['admin'] == 1) {
		        	$this->data['admin'][] = $id['id'];
		        }
		        elseif ($id['gmod'] == 1) {
		        	$this->data['gmod'][] = $id['id'];
		        }
		    }
		    $this->export();
		}
	}

}
?>