<?php
class cache_smileys extends CacheItem {

	var $url;
	
	function load () {
		global $db;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$this->data = array();
			$result = $db->query("SELECT s.search, s.replace, s.desc, s.show FROM {$db->pre}smileys AS s");
			$this->data = array();
			while ($smiley = $db->fetch_assoc($result)) {
				$smiley['jssearch'] = addslashes($smiley['search']);
				$smiley['desc'] = htmlspecialchars($smiley['desc']);
				$smiley['search'] = htmlentities($smiley['search'], ENT_QUOTES);
				$smiley['replace'] = str_replace('{folder}', $this->url, $smiley['replace']);
				$this->data[] = $smiley;
			}
			$this->export();
		}
		$this->smileys = $this->data;
	}
	
	function seturl ($url) {
		$this->url = $url;
	}
	
	function rebuildable() {
		return false;
	}

}
?>