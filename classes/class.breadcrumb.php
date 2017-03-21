<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2009  The Viscacha Project

	Author: Matthias Mohr (et al.)
	Publisher: The Viscacha Project, http://www.viscacha.org
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

class breadcrumb {

    var $content = array();
    var $cache = array();

    function __construct() {
    }

    function Add($title, $url = NULL) {
    	$this->content[] = array(
    	    'title' => $title,
    	    'url' => $url
    	);
    }

    function AddUrl($url) {
		$last = array_pop($this->content);
    	$this->content[] = array(
    	    'title' => $last['title'],
    	    'url' => $url
    	);
    }

    function ResetUrl() {
		$last = array_pop($this->content);
    	$this->content[] = array(
    	    'title' => $last['title'],
    	    'url' => NULL
    	);
    }
	
	function build($divider = ' > ', $linked = false) {
        $cache = array();
        foreach ($this->content as $key => $row) {
        	$row['title'] = viscacha_htmlspecialchars($row['title']);
            if (!empty($row['url']) && $linked) {
                $cache[$key] = '<a href="'.$row['url'].'">'.$row['title'].'</a>';
            }
            else {
                $cache[$key] = $row['title'];
            }
        }
        return implode($divider, $cache);
	}

    function getArray() {
        return $this->content;
    }
}
?>