<?php
class cache_loaddesign extends CacheItem {

	function load () {
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$this->data = Theme::all(false);
			$this->export();
		}
	}

}