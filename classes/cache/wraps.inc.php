<?php
class cache_wraps extends CacheItem {

	function load () {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->execute("
		    	SELECT d.id, d.groups, c.lid, c.title
		    	FROM {$db->pre}documents AS d
		    		LEFT JOIN {$db->pre}documents_content AS c ON d.id = c.did
		    	WHERE active = '1'
		    ");
		    $this->data = array();
		    while ($row = $result->fetch()) {
		    	if (!isset($this->data[$row['id']])) {
		    		$this->data[$row['id']] = array(
		    			'titles' => array(),
		    			'groups' => $row['groups']
		    		);
		    	}
		        $this->data[$row['id']]['titles'][$row['lid']] = $row['title'];
		    }
		    $this->export();
		}
	}

}
?>