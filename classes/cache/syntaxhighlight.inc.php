<?php
class cache_syntaxhighlight extends CacheItem {

	function load() {
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
			require_once('classes/class.geshi.php');
	        $this->data = array();
	        $source = 'classes/geshi/';
	        $d = dir($source);
	        while (false !== ($entry = $d->read())) {
	            if (get_extension($entry) == 'php' && !is_dir($source.$entry)) {
	                include_once($source.$entry);
	                if (!isset($language_data['NO_INDEX'])) {
		                $short = str_replace('.php', '', $entry);
		                $this->data[$short]['file'] = $entry;
		                $this->data[$short]['name'] = $language_data['LANG_NAME'];
		                $this->data[$short]['short'] = $short;
	                }
	            }
	        }
	        $d->close();
	        asort($this->data);
		    $this->export();
		}
	}

}
?>