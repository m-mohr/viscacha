<?php
/* 
* Caching Class 1.1
* (c) 2004 by MaMo Net, www.mamo-net.de
* License: GPL 
*/

class scache {

	var $filename;
	var $file;
	
	function scache ($filename, $cachedir = "cache/") {
		$this->filename = $filename;
		$this->file = $cachedir.$filename.".inc.php";
	}
	
	function exportdata($data) {
	
	    $wdata = serialize($data);
	    if (file_put_contents($this->file,$wdata) > 0) {
	        return TRUE;
	    }
	    else {
	        return FALSE;
	    }
	
	}
	
	function importdata() {
	
		if (file_exists($this->file)) {
	        $data = file_get_contents($this->file);
	        $cdata = unserialize($data);
	        return $cdata;
	    }
	    else {
	        return FALSE;
	    }
	
	}
	function existsdata($max_age = FALSE) {
	
	    if (file_exists($this->file) && filesize($this->file) > 4) {
			if ($max_age != FALSE) {
				$last = filemtime($this->file);
				$expired = time()-$max_age;
				if ($last < $expired) {
					$this->deletedata();
					return FALSE;
				}
			}
	        return TRUE;
	    }
	    else {
	        return FALSE;
	    }
	
	}
	function deletedata() {
		global $filesystem;
	    if (file_exists($this->file)) {
	    	if ($filesystem->unlink($this->file)) {
	        	return TRUE;
	       	}
	    }
	    return FALSE;
	
	}

}
?>
