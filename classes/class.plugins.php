<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2007  Matthias Mohr, MaMo Net

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
*/

if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

class PluginSystem {

	var $cache;
	var $pos;
	var $sqlcache;
	var $menu;

	function PluginSystem() {
		$this->cache = array();
		$this->pos = array();
		$this->sqlcache = null;
		$this->menu = null;
	}

	function load($pos) {
		$group = $this->_group($pos);
		$this->_load_group($pos);
		if (isset($this->cache[$group][$pos])) {
			return implode("\r\n", $this->cache[$group][$pos]);
		}
		else {
			return '';
		}
	}

	function uninstall($id) {
		return $this->_setup('uninstall', $id);
	}

	function install($id) {
		return $this->_setup('install', $id);
	}

	function navigation() {
		global $tpl;

		$group = 'navigation';
		$this->_load_group($group);
		$this->_cache_navigation();

		$code = '';

		if (isset($this->menu[0])) {
			foreach ($this->menu[0] as $row) {
				if ($row['module'] > 0) {
					if (isset($this->cache[$group]['navigation'][$row['module']]) && $this->_check_permissions($row['groups'])) {
						$code .= $this->cache[$group]['navigation'][$row['module']];
					}
				}
				else {
					if ($this->_check_permissions($row['groups'])) {
						$navigation = $this->_prepare_navigation($row['id']);
						$tpl->globalvars(compact("row","navigation"));
						$html = $tpl->parse("modules/navigation");
						$code .= " ?".">{$html}<"."?php \r\n";
					}
				}
			}
		}
		return $code;
	}

	function countPlugins($pos){
		global $db;
		$result = $db->query("SELECT COUNT(*) as num FROM {$db->pre}plugins WHERE position = '{$pos}' AND active = '1'");
		$info = $db->fetch_assoc($result);
		return $info['num'];
	}

	function _setup($hook, $id) {
		global $myini;

		$source = '';
		$inifile = 'modules/'.$id.'/config.ini';
		$ini = $myini->read($inifile);
	    if (isset($ini['php'][$hook])) {
	    	$file = $ini['php'][$hook];
		  	$sourcefile = 'modules/'.$id.'/'.$file;
		  	if (file_exists($sourcefile)) {
			   	$source = file_get_contents($sourcefile);
    		}
			else {
				trigger_error('Setup for Plugin not found! File '.$sourcefile.' could not be loaded while executing '.$hook.'.', E_USER_WARNING);
			}
    	}

		return $source;
	}

	function _cache_navigation() {
		global $scache;
		if ($this->menu == null) {
			$cache = $scache->load('modules_navigation');
			$this->menu = $cache->get();
		}
	}

	function _prepare_navigation($id) {
		if (!isset($this->menu[$id])) {
			return array();
		}
		else {
			$navigation = array();
			foreach ($this->menu[$id] as $row) {
				if ($this->_check_permissions($row['groups'])) {
					$row['navigation'] = $this->_prepare_navigation($row['id']);
					$navigation[] = $row;
				}
			}
			return $navigation;
		}
	}

	function _load_group($pos) {
		$group = $this->_group($pos);
		$file = 'cache/modules/'.$group.'.php';

		if (file_exists($file) == true) {
			$code = file_get_contents($file);
			$code = unserialize($code);
		}
		else {
			$code = $this->_build_code($pos);
		}
		$this->cache[$group] = $code;
	}

	function _build_code($pos) {
		global $myini, $db;
		$group = $this->_group($pos);
		$file = 'cache/modules/'.$group.'.php';

		if ($this->sqlcache == null) {
			$this->sqlcache = array();
			$this->sqlcache[$group] = array();
	        $result = $db->query("SELECT id, module, position FROM {$db->pre}plugins WHERE active = '1' ORDER BY ordering",__LINE__,__FILE__);
	        while ($row = $db->fetch_assoc($result)) {
	        	$row['group'] = $this->_group($row['position']);
	            $this->sqlcache[$row['group']][$row['position']][$row['id']] = $row['module'];
	        }
	    }
	    if (!isset($this->sqlcache[$group])) {
	    	$this->sqlcache[$group] = array();
	    }

	    $cfgdata = array();
	    $code = array();
	    foreach ($this->sqlcache[$group] as $position => $mods) {
	    	$code[$position] = '';
	    	foreach ($mods as $id => $plugin) {
	    		if (!isset($cfgdata[$plugin])) {
		    		$inifile = 'modules/'.$plugin.'/config.ini';
		    		$cfgdata[$plugin] = $myini->read($inifile);
	    		}
	    		if (isset($cfgdata[$plugin]['php'])) {
		    		foreach ($cfgdata[$plugin]['php'] as $phpposition => $phpfile) {
		    			if ($position == $phpposition) {
				    		$sourcefile = 'modules/'.$plugin.'/'.$phpfile;
				    		if (file_exists($sourcefile)) {
					    		$source = file_get_contents($sourcefile);
					    		if (!isset($code[$position][$id])) {
					    			$code[$position][$id] = '';
					    		}
					    		$code[$position][$id] .= '$pluginid = "'.$plugin.'";'."\r\n".$source."\r\n";
				    		}
			    		}
			    	}
	    		}
	    	}
	    }

		$save = serialize($code);
		$save = file_put_contents($file, $save);

		return $code;
	}

	function _group($pos) {
		$offset = strpos ($pos, '_');
		if ($offset === false) {
			return $pos;
		}
		else {
			return substr($pos, 0, $offset);
		}
	}

	function _check_permissions($groups) {
	    global $slog;
	    if ($groups == 0 || count(array_intersect(explode(',', $groups), $slog->groups)) > 0) {
	        return true;
	    }
	    else {
	        return false;
	    }
	}

}
?>
