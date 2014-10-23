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

class tpl {

	var $dir;
	var $altdir;
	var $contents;
	var $vars;
	var $oldvars;
	var $benchmark;
	var $sent;
	var $imgdir;
	var $stdimgdir;


	function tpl() {
		global $config, $my, $gpc, $scache;

		$admin = $gpc->get('admin', str);

		if ($admin != $config['cryptkey']) {
			$fresh = false;
		}
		else {
			$fresh = true;
		}
		$loaddesign_obj = $scache->load('loaddesign');
		$cache = $loaddesign_obj->get($fresh);

		$this->dir = '';
		$this->altdir = './templates/'.$cache[$config['templatedir']]['template'].'/';
		if (!empty($my->imagesid) && $my->imagesid != $cache[$config['templatedir']]['images']) {
			$this->imgdir = './images/'.$cache[$my->template]['images'].'/';
		}
		else {
			$this->imgdir = false;
		}
		$this->stdimgdir = './images/'.$cache[$config['templatedir']]['images'].'/';

		$this->contents = '';
		$this->benchmark = array('all' => 0, 'ok' => 0, 'error' => 0, 'time' => 0, 'detail' => array('0' => array('time' => 'N/A', 'file' => 'footer.html')) );
		$this->vars = $this->oldvars = array();
		$this->sent = array();

		if (!$this->setdir()) {
			trigger_error('Template-Directory does not exist', E_USER_ERROR);
		}
	}

	function img ($name) {
		$gif = '.gif';
		$png = '.png';
		if ($this->imgdir != false && file_exists($this->imgdir.$name.$gif)) {
			return $this->imgdir.$name.$gif;
		}
		elseif ($this->imgdir != false && file_exists($this->imgdir.$name.$png)) {
			return $this->imgdir.$name.$png;
		}
		elseif (file_exists($this->stdimgdir.$name.$gif)) {
			return $this->stdimgdir.$name.$gif;
		}
		elseif (file_exists($this->stdimgdir.$name.$png)) {
			return $this->stdimgdir.$name.$png;
		}
		else {
			return 'images/empty.gif';
		}
	}

    function globalvars ($vars) {
    	$this->oldvars = array_merge($this->vars, $this->oldvars);
        $this->vars = $vars;
    }

    function exists($thisfile, $thisext='html') {
		$thisext = '.'.$thisext;
		if (file_exists($this->dir.$thisfile.$thisext) || file_exists($this->altdir.$thisfile.$thisext)) {
			return true;
		}
		else {
			return false;
		}
    }

	function parse($thisfile, $thisext='html') {

    	$thiszm1=benchmarktime();
		$this->benchmark['all']++;

		$file_unique = FALSE;
		$thisext = '.'.$thisext;

		if (file_exists($this->dir.$thisfile.$thisext)) {
			$file_unique = $this->dir.$thisfile.$thisext;
		}
		elseif (file_exists($this->altdir.$thisfile.$thisext)) {
			$file_unique = $this->altdir.$thisfile.$thisext;
		}

		if ($file_unique == FALSE) {
		    $this->benchmark['error']++;
		    $this->benchmark['detail'][] = array('time' => 0, 'file' => $thisfile.$thisext);
			return '<!-- File does not exist: '.$this->dir.$thisfile.$thisext.' and '.$this->altdir.$thisfile.$thisext.' -->';
		}

		extract($GLOBALS, EXTR_SKIP);
		extract($this->oldvars, EXTR_SKIP);
		extract($this->vars);

        $this->benchmark['ok']++;

		ob_start();
		include($file_unique);
		$this->contents = ob_get_contents();
		ob_end_clean();

		$this->sent[] = $thisfile.$thisext;
		$this->oldvars = array_merge($this->vars, $this->oldvars);
		$this->vars = array();

    	$thiszm2=benchmarktime();

    	$this->benchmark['time'] += $thiszm2-$thiszm1;

    	$this->benchmark['detail'][] = array('time' => substr($thiszm2-$thiszm1,0,7), 'file' => $file_unique);

        return $this->contents;
	}

	function setdir($dirv = null) {
		if ($dirv == null) {
			global $my;
			$dirv = $my->templateid;
		}

		$dir = "./templates/{$dirv}/";
		if (is_dir($dir)) {
			$this->dir = $dir;
			return true;
		}
		else {
			$this->dir = $this->altdir;
			return false;
		}
	}

	function getdir() {
		return $this->dir;
	}

	function tplsent($file,$ext='html') {
		$tpl = $file.'.'.$ext;
		if(in_array($tpl, $this->sent)) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

}
?>