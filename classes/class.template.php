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

require "classes/class.bladeone.php";

class tpl {

	private $dir;
	private $vars;
	public $benchmark;
	private $sent;
	private $imgdir;
	private $blade;

	public function __construct() {
		global $config, $my, $gpc, $scache;

		$admin = $gpc->get('admin', str);

		if ($admin != $config['cryptkey']) {
			$fresh = false;
		} else {
			$fresh = true;
		}
		$loaddesign_obj = $scache->load('loaddesign');
		$cache = $loaddesign_obj->get($fresh);

		if (!empty($my->templateid) && $my->templateid != $cache[$config['templatedir']]['template']) {
			$this->dir = 'templates/' . $cache[$my->template]['template'];
		} else {
			$this->dir = 'templates/' . $cache[$config['templatedir']]['template'];
		}
		if (!is_dir($this->dir)) {
			trigger_error('Template directory does not exist', E_USER_ERROR);
		}

		if (!empty($my->imagesid) && $my->imagesid != $cache[$config['templatedir']]['images']) {
			$this->imgdir = 'images/' . $cache[$my->template]['images'] . '/';
		} else {
			$this->imgdir = 'images/' . $cache[$config['templatedir']]['images'] . '/';
		}
		if (!is_dir($this->imgdir)) {
			trigger_error('Image directory does not exist', E_USER_WARNING);
		}

		$this->benchmark = array('all' => 0, 'ok' => 0, 'error' => 0, 'time' => 0, 'detail' => array('0' => array('time' => 'N/A', 'file' => 'footer.html')));
		$this->vars = array();
		$this->sent = array();

		define("BLADEONE_MODE", $config['debug']);
		$this->blade = new eftec\bladeone\BladeOne($this->dir, 'cache/' . $this->dir);
		$this->blade->setFileExtension('.html');
        $this->blade->directive('lang', function ($expression) {
			$expression = trim($expression, '()');
            return "<?php echo \$lang->phrase('{$expression}'); ?>";
        });
        $this->blade->directive('img', function ($expression) {
			$expression = trim($expression, '()');
            return "<?php echo \$tpl->img('{$expression}'); ?>";
        });
        $this->blade->directive('selected', function ($expression) {
			return "<?php if{$expression} { echo ' selected=\"selected\"'; } ?>";
        });
        $this->blade->directive('checked', function ($expression) {
			return "<?php if{$expression} { echo ' checked=\"checked\"'; } ?>";
        });
        $this->blade->directive('breadcrumb', function ($expression) {
			$expression = trim($expression, '()');
			return "<?php echo \$breadcrumb->build({$expression}) ?>";
        });
}

	public function img($name) {
		$gif = '.gif';
		$png = '.png';
		if ($this->imgdir != false && file_exists($this->imgdir . $name . $gif)) {
			return $this->imgdir . $name . $gif;
		} elseif ($this->imgdir != false && file_exists($this->imgdir . $name . $png)) {
			return $this->imgdir . $name . $png;
		} else {
			return 'images/empty.gif';
		}
	}

	public function globalvars($vars) {
		$this->vars = array_merge($this->vars, $vars);
	}

	public function exists($file) {
		return file_exists($this->getPath($file));
	}

	public function parse($file) {
		$start = benchmarktime();
		$this->benchmark['all'] ++;

		$tplpath = $this->getPath($file);
		if (!file_exists($tplpath)) {
			$this->benchmark['error'] ++;
			$this->benchmark['detail'][] = array('time' => 0, 'file' => $tplpath);
			return "<!-- File does not exist: {$tplpath} -->";
		}

		$content = $this->blade->run(
			str_replace('/', '.', $file),
			array_merge($GLOBALS, $this->vars)
		);

		$this->sent[] = $tplpath;
		$this->vars = array();

		$delta = benchmarktime() - $start;
		$this->benchmark['ok'] ++;
		$this->benchmark['time'] += $delta;
		$this->benchmark['detail'][] = array('time' => mb_substr($delta, 0, 7), 'file' => $tplpath);

		return $content;
	}

	public function tplsent($file) {
		return in_array($this->getPath($file), $this->sent);
	}
	
	protected function getPath($file) {
		return $this->dir . DIRECTORY_SEPARATOR . $file . $this->blade->getFileExtension();
	}

}
