<?php
class cache_parent_forums extends CacheItem {

	function load() {
		global $db, $scache;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
			$categories_obj = $scache->load('categories');
			$categories = $categories_obj->get();
			$categories_obj = $scache->load('cat_bid');
			$forums = $categories_obj->get();
			
			$this->data = array();
			foreach ($forums as $id => $forum) {
				$this->data[$id] = array();
				$this->data[$id][] = $id;
				while (!empty($categories[$forum['parent']]['parent'])) {
					$this->data[$id][] = $categories[$forum['parent']]['parent'];
					$forum['parent'] = $categories[$forum['parent']]['parent'];
				}
			}
			$this->export();
		}
	}

}
?>