<?php
class cache_version_check extends CacheItem {
	function load () {
		global $config;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$this->data = array(
				'comp' => get_remote('http://version.viscacha.org/compare/?version='.base64_encode($config['version'])),
				'version' => get_remote('http://version.viscacha.org/version'),
				'news' => get_remote('http://version.viscacha.org/news')
			);
			$this->export();
		}
	}
}
?>