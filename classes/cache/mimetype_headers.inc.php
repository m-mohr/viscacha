<?php
class cache_mimetype_headers extends CacheItem {

	function load() {
		global $db;
	    if ($this->exists() == true) {
	        $this->import();
	    }
	    else {
	        $result = $db->query("SELECT extension, mimetype, stream FROM {$db->pre}filetypes WHERE mimetype != 'application/octet-stream' AND stream != 'attachment'",__LINE__,__FILE__);
	        $this->data = array();
	        while ($row = $db->fetch_assoc($result)) {
	        	$extensions = explode(',', $row['extension']);
				foreach ($extensions as $extension) {
	            	$extension = strtolower($extension);
					$this->data[$extension] = array(
						'mimetype' => $row['mimetype'],
						'stream' => $row['stream']
					);
	            }
	        }
	        $this->export();
	    }
	}

}
?>