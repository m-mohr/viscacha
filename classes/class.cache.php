<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2006  Matthias Mohr, MaMo Net
	
	Author: Matthias Mohr
	Publisher: http://www.mamo-net.de
	Start Date: May 22, 2004

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	
	-- Caching Suite v2.0 --
	This class is part of Viscacha.
*/

if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "class.cache.php") die('Error: Hacking Attempt');

class CacheItem {

	var $name;
	var $file;
	var $data;
	
	function CacheItem ($filename, $cachedir = "cache/") {
		$this->name = $filename;
		$this->file = $cachedir.$filename.".inc.php";
		$this->data = null;
	}
	
	function export() {
	
	    $ser_data = serialize($this->data);
	    if (file_put_contents($this->file,$ser_data) > 0) {
	        return true;
	    }
	    else {
	        return false;
	    }
	
	}
	
	function import() {
	
		if (file_exists($this->file)) {
	        $data = file_get_contents($this->file);
	        $this->data = unserialize($data);
	        return true;
	    }
	    else {
	        return false;
	    }
	
	}
	
	function expired($max_age) {
		if ($this->age() > $max_age) {
			$this->delete();
			return false;
		}
		else {
			return true;
		}
	}
	
	function age() {
		if (file_exists($this->file)) {
			$age = time()-filemtime($this->file);
			return $age;
		}
	}
	
	function exists($max_age = null) {
	    if (file_exists($this->file) && filesize($this->file) > 4) {
			if ($max_age != null) {
				return !($this->expired($max_age));
			}
	        return true;
	    }
	    else {
	        return false;
	    }
	}
	
	function delete() {
		global $filesystem;
	    if (file_exists($this->file)) {
	    	if ($filesystem->unlink($this->file)) {
	        	return true;
	       	}
	    }
	    return false;
	}
	
	function load() {
		// Will be implemented in sub-class
	}
	
	function get($max_age = null) {
		if ($this->data == null || $this->expired($max_age)) {
			$this->load();
		}
		return $this->data;
	}
	
	function set($data) {
		$this->data = $data;
	}

}

class CacheServer {

	var $cachedir;
	var $data;

	function CacheServer($cachedir = 'cache/') {
		$this->cachedir = $cachedir;
		$this->data = array();
	}
	
	function load($name, $sourcedir = 'classes/cache/') {
		$class = "cache_{$name}";
		$file = $sourcedir.$name.'.inc.php';
		if (!class_exists($class) && file_exists($file)) {
			include($file);
		}
		if (class_exists($class)) {
			$object = new $class($name, $this->cachedir);
		}
		else {
			trigger_error('Cache Class of type '.$name.' could not be loaded.', E_USER_NOTICE);
			$object = new CacheItem($name, $this->cachedir);
		}
		$this->data[$name] = $object;
		return $object;
	}
	
	function unload($name) {
		unset($this->data[$name]);
	}
}
?>