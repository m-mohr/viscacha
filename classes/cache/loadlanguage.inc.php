<?php
class cache_loadlanguage extends CacheItem {

	function load () {
		global $db;
		if ($this->exists() == true) {
		    $this->import();
			foreach ($this->data as $id => $row) {
				if (!file_exists("templates/language_{$id}.js") || !file_exists("admin/html/language_{$id}.js")) {
					$this->createJavascript();
					break;
				}
			}
		}
		else {
		    $result = $db->query("SELECT id, language, detail FROM {$db->pre}language WHERE publicuse != '0'");
		    $this->data = array();
		    while ($row = $db->fetch_assoc($result)) {
		        $this->data[$row['id']] = $row;
		    }
			$this->createJavascript();
		    $this->export();
		}
	}

	function delete() {
		global $filesystem;
		$this->deleteJavascript();
    	if ($filesystem->unlink($this->file)) {
        	return true;
       	}
	    return false;
	}

	function deleteJavascript() {
		global $filesystem;
		$folders = array("templates/", "admin/html/");
		foreach ($folders as $folder) {
			$dir = dir($folder);
			while (false !== ($file = $dir->read())) {
				if (preg_match('~^language_\d+\.js$~', $file)) {
					$filesystem->unlink($folder.$file);
				}
			}
			$dir->close();
		}
	}

	function createJavascript() {
		global $lang, $config, $filesystem;
		$old_lang = $lang->getdir(true);
		foreach ($this->data as $id => $details) {
			$lang->setdir($id);
			$prefix = "// JS Language file for Viscacha {$config['version']} - Language: {$details['language']}\n";
			$prefix .= "var cookieprefix = '{$config['cookie_prefix']}'\n";
			$sections = array(
				'javascript' => "templates/language_{$id}.js", // Frontend
				'admin/javascript' => "admin/html/language_{$id}.js" // Backend
			);
			foreach ($sections as $lngfile => $jsfile) {
				$code = $lang->javascript($lngfile);
				if ($code === false) {
					$code = 'alert("Could not load language file (JS)!");';
				}
				if (file_exists($jsfile)) {
					$filesystem->chmod($jsfile, 0666);
				}
				$filesystem->file_put_contents($jsfile, $prefix.$code);
			}
		}
		$lang->setdir($old_lang);
	}

}
?>