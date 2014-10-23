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
*/

if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "class.plugins.php") die('Error: Hacking Attempt');

class MyModules {

var $buffer;
var $cache;
var $varcache;
var $pos;

function MyModules() {
    $this->cache = &new scache('modules');
    $this->buffer = $this->cache->importdata();
	$this->varcache = array();
}

function load($pos,$vars=NULL) {
	global $config,$my,$db,$tpl,$slog,$lang,$bbcode,$myini;

	if ($vars != NULL) {
		$this->varcache = array_keys($vars);
		extract($vars, EXTR_SKIP);
	}

	if (!isset($this->buffer[$pos])) {
        $result = $db->query("SELECT id, name, link, param, groups FROM {$db->pre}menu WHERE active = '1' AND FIND_IN_SET('{$pos}', position) AND module = '1' ORDER BY ordering, id",__LINE__,__FILE__);
        $this->buffer[$pos] = array();
        while ($row = $db->fetch_assoc($result)) {
            $this->buffer[$pos][] = $row;
        }
        $this->cache->exportdata($this->buffer);
    }

    foreach ($this->buffer[$pos] as $row) {
      	if ($row['groups'] == NULL || $this->GroupCheck($row['groups']) == TRUE) {
    		$dir = 'modules/'.$row['link'].'/';

    		$ini = $myini->read($dir."config.ini");
			$this->pos = $pos;
    		if (count($ini) > 0 && isset($ini['php'][0]) && file_exists($dir.$ini['php'][0])) {
    			include($dir.$ini['php'][0]);
    		}
    		unset($dir, $ini);
    	}
    }
    
    if ($vars != NULL) {
    	if (!isset($this->varcache) || !is_array($this->varcache)) {
    		$this->varcache = array();
    	}
    	return compact($this->varcache);
    }
}

function navigation() {
	global $config,$my,$tpl,$db,$myini;

	$scache = new scache('modules_navigation');
	if ($scache->existsdata() == TRUE) {
	    $menu = $scache->importdata();
	}
	else {
	    $result = $db->query("SELECT id, name, link, param, groups, sub, module FROM {$db->pre}menu WHERE active = '1' AND FIND_IN_SET('navigation', position) ORDER BY ordering, id",__LINE__,__FILE__);
	    $menu = array();
	    while ($row = $db->fetch_assoc($result)) {
	        if (!isset($menu[$row['sub']])) {
	            $menu[$row['sub']] = array();
	        }
	        $menu[$row['sub']][] = $row;
	    }
	    $scache->exportdata($menu);
	}

    foreach ($menu[0] as $row) {
    	if ($row['module'] == 0 && isset($menu[$row['id']])) {
    	    if ($this->GroupCheck($row['groups'])) {
        	    $nav_s = array();
            	foreach ($menu[$row['id']] as $sub) {
            	    if ($this->GroupCheck($sub['groups'])) {
                        $sub['sub'] = array();
                		if (isset($menu[$sub['id']])) {
                		    foreach ($menu[$sub['id']] as $subsub) {
                		        if ($this->GroupCheck($subsub['groups'])) {
                			        $sub['sub'][] = $subsub;
                			    }
                		    }
                		}
                        $nav_s[] = $sub;

            		}
            	}
            	$tpl->globalvars(compact("row","nav_s","nav_ss"));
            	echo $tpl->parse("modules/navigation");
        	}
        }
        else {
            if ($row['module'] == 1 && ($row['groups'] == NULL || $this->GroupCheck($row['groups']) == TRUE)) {
                $dir = 'modules/'.$row['link'].'/';
                $ini = 	$myini->read($dir."config.ini");
            	if (count($ini) > 0 && file_exists($dir.$ini['php'][0])) {
            		include($dir.$ini['php'][0]);
            	}
            	unset($dir, $ini);
        	}
        }
    }
}

function GroupCheck ($groups) {
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
