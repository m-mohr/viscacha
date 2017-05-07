<?php
class cache_loaddesign extends CacheItem {

	function load () {
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$this->data = Viscacha\View\Theme::all(false);
			$this->export();
		}
	}

}