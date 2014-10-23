<?php
class cache_index_moderators extends CacheItem {
	function load() {
		global $db, $scache;
		$memberdata_obj = $scache->load('memberdata');
		$memberdata = $memberdata_obj->get();
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
		    $result = $db->query('SELECT mid, bid FROM '.$db->pre.'moderators WHERE time > '.time().' OR time IS NULL',__LINE__,__FILE__);
		    $this->data = array();
		    while($row = $db->fetch_assoc($result)) {
		    	if (isset($memberdata[$row['mid']])) {
		    		$row['name'] = $memberdata[$row['mid']];
		    		$this->data[$row['bid']][] = $row;
		    	}
		    }
			$this->export();
		}
	}
}
?>