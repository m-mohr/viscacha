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
				$c = $categories;
				while (!empty($c[$forum['parent']]['parent'])) {
					$temp = $c[$forum['parent']]['parent'];
					unset($c[$forum['parent']]);
					if (isset($forums[$temp]) == false) {
						continue;
					}
					$forum['parent'] = $this->data[$id][] = $temp;
				}
			}
			$this->export();
		}
	}

}
?>