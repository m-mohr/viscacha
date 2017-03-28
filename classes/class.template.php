<?php
/*
	Viscacha - An advanced bulletin board solution to manage your content easily
	Copyright (C) 2004-2017, Lutana
	http://www.viscacha.org

	Authors: Matthias Mohr et al.
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

use eftec\bladeone;

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

		$this->benchmark = array();
		$this->vars = array();
		$this->sent = array();

		define("BLADEONE_MODE", $config['debug']);
		$this->blade = new bladeone\BladeOne($this->dir, 'data/cache/' . $this->dir);
		$this->blade->setFileExtension('.html');
        $this->blade->directive('lang', function ($expression) {
            return '<?php echo static::e($lang->phrase'.$expression.'); ?>';
        });
        $this->blade->directive('elselang', function ($expression) {
            return '<?php else: echo static::e($lang->phrase'.$expression.'); endif; ?>';
        });
        $this->blade->directive('img', function ($expression) {
            return '<?php echo $tpl->img'.$expression.'; ?>';
        });
        $this->blade->directive('selected', function ($expression) {
			return "<?php if{$expression} { echo ' selected=\"selected\"'; } ?>";
        });
        $this->blade->directive('checked', function ($expression) {
			return "<?php if{$expression} { echo ' checked=\"checked\"'; } ?>";
        });
        $this->blade->directive('breadcrumb', function ($expression) {
			$expression = trim($expression, '()');
			return "<?php echo \Breadcrumb::universal()->build({$expression}) ?>";
        });
        $this->blade->directive('hook', function ($expression) {
			$expression = trim($expression, '()');
			return '<?php ($code = $plugins->load("'.$expression.'")) ? eval($code) : null; ?>';
        });
        $this->blade->directive('navigation', function ($expression) {
			$expression = trim($expression, '()');
			return '<?php ($code = $plugins->navigation("'.$expression.'")) ? eval($code) : null; ?>';
        });
        $this->blade->directive('datetime', function ($expression) {
			return $this->compileDateTime($expression, 'datetime');
        });
        $this->blade->directive('date', function ($expression) {
			return $this->compileDateTime($expression, 'date');
        });
        $this->blade->directive('time', function ($expression) {
			return $this->compileDateTime($expression, 'time');
        });
        $this->blade->directive('reldatetime', function ($expression) {
			return $this->compileDateTime($expression, 'reldatetime');
        });
	}
	
	public function compileDateTime($expression, $format = null) {
		global $lang;
		$expression = trim($expression, '()');
		$default = null;
		if (strpos($expression, ',') !== FALSE) {
			list($expression, $default) = preg_split('~\s*,\s*~', $expression, 2);
			if ($lang->exists($default)) {
				$default = '$lang->phrase('.$default.')';
			}
		}
		$expression = 'times('.$expression.')';

		if ($format == 'reldatetime') {
			$formatter = 'str_date('.$expression.')';
		}
		else {
			$formatter = 'gmdate($lang->phrase("'.$format.'_format"), '.$expression.')';
		}
		
		if ($default) {
			return "<?php echo (empty({$expression}) ? {$default} : {$formatter}); ?>";
		}
		else {
			return "<?php echo {$formatter}; ?>";
		}
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
		Debug::startMeasurement("tpl::parse({$file})");

		$tplpath = $this->getPath($file);
		$debugInfo = array('file' => $tplpath, 'error' => false, 'type' => 'tpl');

		if (!file_exists($tplpath)) {
			$debugInfo['error'] = true;
			Debug::stopMeasurement("tpl::parse()", $debugInfo);
			return null;
		}

		$content = $this->blade->run(
			str_replace('/', '.', $file),
			array_merge($GLOBALS, $this->vars)
		);

		$this->sent[] = $tplpath;
		$this->vars = array();

		Debug::stopMeasurement("tpl::parse({$file})", $debugInfo);

		return $content;
	}

	public function tplsent($file) {
		return in_array($this->getPath($file), $this->sent);
	}
	
	protected function getPath($file) {
		return $this->dir . '/' . $file . $this->blade->getFileExtension();
	}

}
