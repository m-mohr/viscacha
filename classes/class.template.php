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

class Theme {

	private $themes;
	private $dir;
	private $vars;
	private $sent;
	private $blade;

	public function __construct($theme, $fallback) {
		global $config, $scache;

		$theme_cache = $scache->load('loaddesign');
		$this->themes = $theme_cache->get();

		if (isset($this->themes[$theme])) {
			$this->dir = $this->themes[$theme]['path'];
		} else if (isset($this->themes[$fallback])) {
			$this->dir = $this->themes[$fallback]['path'];
		} else {
			trigger_error('Theme directory does not exist', E_USER_ERROR);
		}

		$this->vars = array();
		$this->sent = array();

		$this->blade = new Blade($this->getTemplateFolder(), $this->getCacheFolder());
		$this->blade->setMode($config['debug']);
		$this->blade->setFileExtension('.html');
	}

	public function getFolder() {
		return $this->dir;
	}


	public function getCacheFolder() {
		return 'data/cache/' . $this->getFolder();
	}

	public function getTemplateFolder() {
		return $this->dir . '/templates';
	}

	public function getTemplateFile($template) {
		return $this->getTemplateFolder() . '/' . $template . $this->blade->getFileExtension();
	}
	
	public static function all($includeHidden = true) {
		$ini = new INI();
		$path = 'themes/';
		$dir = new DirectoryIterator($path);
		$data = array();
		foreach ($dir as $fileinfo) {
			if (!$fileinfo->isDir() || $fileinfo->isDot()) {
				continue;
			}
			$hidden = (mb_substr($fileinfo->getFilename(), 0, 1) == '.');
			if ($includeHidden || !$hidden) {
				$themeFolder = $path . $fileinfo->getFilename();
				$themeData = $ini->read($themeFolder.'/theme.ini');
				if (isset($themeData['info']['name'])) {
					$data[$fileinfo->getFilename()] = array(
						'id' => $fileinfo->getFilename(),
						'path' => $themeFolder,
						'meta' => $themeData['info'],
						'hidden' => $hidden
					);
				}
			}
		}
		return $data;
	}

	public function assignVars($vars) {
		$this->vars = array_merge($this->vars, $vars);
	}

	public function hasTemplate($file) {
		return file_exists($this->getTemplateFile($file));
	}

	public function parse($template) {
		Debug::startMeasurement("tpl::parse({$template})");

		$tplpath = $this->getTemplateFile($template);
		$debugInfo = array('file' => $tplpath, 'error' => false, 'type' => 'tpl');

		if (!file_exists($tplpath)) {
			$debugInfo['error'] = true;
			Debug::stopMeasurement("tpl::parse()", $debugInfo);
			return null;
		}

		$content = $this->blade->run(
				str_replace('/', '.', $template), array_merge($GLOBALS, $this->vars)
		);

		$this->sent[] = $tplpath;
		$this->vars = array();

		Debug::stopMeasurement("tpl::parse({$template})", $debugInfo);

		return $content;
	}

	public function wasTemplateSent($template) {
		return in_array($this->getTemplateFile($template), $this->sent);
	}

}

class Blade extends bladeone\BladeOne {

	/**
	 * Set mode of the engine.
	 * 
	 * Can only be called once.
	 * 
	 * @param int 0 = default, compile and cache; 1 = force recompile
	 */
	public function setMode($mode) {
		if (!defined('BLADEONE_MODE')) {
			define("BLADEONE_MODE", $mode);
		}
	}

	public function compileLang($expression) {
		return '<?php echo static::e($lang->phrase' . $expression . '); ?>';
	}

	public function compileElselang($expression) {
		return '<?php else: echo static::e($lang->phrase' . $expression . '); endif; ?>';
	}

	public function compileTheme($expression) {
		return '<?php echo $tpl->getFolder() . "/" . ' . $expression . '; ?>';
	}

	public function compileSelected($expression) {
		return "<?php if{$expression} { echo ' selected=\"selected\"'; } ?>";
	}

	public function compileChecked($expression) {
		return "<?php if{$expression} { echo ' checked=\"checked\"'; } ?>";
	}

	public function compileBreadcrumb($expression) {
		$expression = trim($expression, '()');
		return "<?php echo \Breadcrumb::universal()->build({$expression}) ?>";
	}

	public function compileHook($expression) {
		$expression = trim($expression, '()');
		return '<?php ($code = $plugins->load("' . $expression . '")) ? eval($code) : null; ?>';
	}

	public function compileNavigation($expression) {
		$expression = trim($expression, '()');
		return '<?php ($code = $plugins->navigation("' . $expression . '")) ? eval($code) : null; ?>';
	}

	public function compileDatetime($expression) {
		return $this->makeDateTime($expression, 'datetime');
	}

	public function compileDate($expression) {
		return $this->makeDateTime($expression, 'date');
	}

	public function compileTime($expression) {
		return $this->makeDateTime($expression, 'time');
	}

	public function compilerelDatetime($expression) {
		return $this->makeDateTime($expression, 'reldatetime');
	}

	protected function makeDateTime($expression, $format = null) {
		global $lang;
		$expression = trim($expression, '()');
		$default = null;
		if (strpos($expression, ',') !== FALSE) {
			list($expression, $default) = preg_split('~\s*,\s*~u', $expression, 2);
			if ($lang->exists($default)) {
				$default = '$lang->phrase(' . $default . ')';
			}
		}
		$expression = 'times(' . $expression . ')';

		if ($format == 'reldatetime') {
			$formatter = 'str_date(' . $expression . ')';
		} else {
			$formatter = 'gmdate($lang->phrase("' . $format . '_format"), ' . $expression . ')';
		}

		if ($default) {
			return "<?php echo (empty({$expression}) ? {$default} : {$formatter}); ?>";
		} else {
			return "<?php echo {$formatter}; ?>";
		}
	}

}
