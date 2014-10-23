<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

class tpl {

	var $dir;
	var $altdir;
	var $contents;
	var $vars;
	var $oldvars;
	var $benchmark;
	var $sent;
	var $imgdir = FALSE;
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
		$this->stdimgdir = './images/'.$cache[$config['templatedir']]['images'].'/';

		$this->contents = '';
		$this->benchmark = array('all' => 0, 'ok' => 0, 'error' => 0, 'time' => 0, 'detail' => array('0' => array('time' => 'N/A', 'file' => 'footer.html')) );
		$this->vars = $this->oldvars = array();
		$this->sent = array();

		if (!$this->setdir()) {
			die('Template-Directory does not exist');
		}
	}

	function img ($name) {
		$gif = '.gif';
		$png = '.png';
		if (file_exists($this->imgdir.$name.$gif) && $this->imgdir) {
			return $this->imgdir.$name.$gif;
		}
		elseif (file_exists($this->imgdir.$name.$png) && $this->imgdir) {
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

	function parse($thisfile,$thisext='html') {

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

	function setdir($dirv=NULL) {
		global $my;

		if ($dirv == NULL) {
			$dirv = $my->templateid;
		}

		$dir = "./templates/$dirv/";
		if (is_dir($dir)) {
			$this->dir = $dir;
			return true;
		}
		elseif (is_dir($dir)) {
			$this->dir = $this->altdir;
			return true;
		}
		else {
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
