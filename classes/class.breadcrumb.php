<?php
/*
  Viscacha - An advanced bulletin board solution to manage your content easily
  Copyright (C) 2004-2017, Lutana
  http://www.viscacha.org

  Authors: Matthias Mohr et al.
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

class Breadcrumb {
	
	private static $global;
	
    public static function universal() {
		if (self::$global === null) {
			self::$global = new Breadcrumb();
		}
		return self::$global;
	}

	protected $content;

	public function __construct() {
		$this->content = array();
	}

	public function add($title, $url = null) {
		$this->content[] = array(
			'title' => $title,
			'url' => $url
		);
	}

	public function addUrl($url) {
		$last = array_pop($this->content);
		$this->content[] = array(
			'title' => $last['title'],
			'url' => $url
		);
	}

	public function resetUrl() {
		$last = array_pop($this->content);
		$this->content[] = array(
			'title' => $last['title'],
			'url' => NULL
		);
	}

	public function build($divider = ' > ', $linked = false) {
		$parts = array();
		foreach ($this->content as $key => $row) {
			$row['title'] = viscacha_htmlspecialchars($row['title']);
			if (!empty($row['url']) && $linked) {
				$parts[$key] = '<a href="' . $row['url'] . '">' . $row['title'] . '</a>';
			}
			else {
				$parts[$key] = $row['title'];
			}
		}
		return implode($divider, $parts);
	}

	function get() {
		return $this->content;
	}

}