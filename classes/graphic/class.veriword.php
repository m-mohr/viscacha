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

class VeriWord {

	var $datasource = 'data/captcha.php';
	var $chars = "23456789ABCDEFGHJKLMNPRSTUVWXYZ";
	var $dir_fonts = './classes/fonts/';
	var $dir_noises = "./classes/graphic/noises/";
	var $dimensions;
	var $session;
	var $settings;

	function VeriWord() {
		global $config;
		$this->session = null;
		$this->dimensions = array(
			'w' => $config['botgfxtest_width'],
			'h' => $config['botgfxtest_height']
		);
		$this->settings = array();
	}

	function check() {
		global $gpc;
		$this->session = array(
			'word' => $gpc->get('response'),
			'id' => $gpc->get('challenge')
		);

		if (empty($this->session['id']) || empty($this->session['word'])) {
			return CAPTCHA_FAILURE;
		}

		$lines = file($this->datasource);
		$lines = array_map('trim', $lines);
		foreach ($lines as $row) {
			if (empty($row)) {
				continue;
			}
			$data = explode("\t", $row);
			if ($data[0] == $this->session['id'] && strcasecmp($data[2], $this->session['word']) == 0){
				return CAPTCHA_OK;
			}
		}
		return CAPTCHA_MISTAKE;
	}

	function generateCode($tabindex = 0) {
		global $tpl;
		$session = $this->_newSession();
		$data = array(
			'session' => $session,
			'tabindex' => $tabindex,
			'width' => $this->dimensions['w'],
			'height' => $this->dimensions['h']
		);
		$tpl->globalvars($data);
		return $tpl->parse('main/veriword');
	}

	function makeImage($session_expired_lang = 'Session Error<br>Refresh the Page') {
		global $config, $gpc;
		$challenge = $gpc->get('challenge');
		$this->settings['colortext'] = (bool) $config['botgfxtest_colortext'];
		$this->settings['filter'] = (bool) $config['botgfxtest_filter'];
		$jpeg_quality = (int) $config['botgfxtest_quality'];
		$format = ($config['botgfxtest_format'] == 'png') ? 'PNG' : 'JPEG';
		if ((ImageTypes() & constant("IMG_{$format}")) == false) {
			$format = ($format == 'PNG') ? 'JPEG' : 'PNG';
		}

		$lines = file($this->datasource);
		$lines = array_map('trim', $lines);
		foreach ($lines as $row) {
			if (empty($row)) {
				continue;
			}
			$data = explode("\t", $row);
			if ($data[0] == $challenge){
				$this->session = array(
					'word' => $data[2],
					'id' => $data[1]
				);
				break;
			}
		}
		if (empty($this->session['word'])) {
			$this->_errorImage($session_expired_lang);
		}

		// Generate the image
		$im = $this->_drawImage();

		send_nocache_header();
		switch($format){
			case 'PNG' :
				header("Content-type: image/png");
				imagepng($im);
			break;
			default:
				header("Content-type: image/jpeg");
				imagejpeg($im, '', $jpeg_quality);
		}
		imagedestroy($im);
	}

	function getError() {
		return null;
	}

	function _errorImage($text) {
		require('classes/graphic/class.text2image.php');
		$img = new text2image();
		$img->prepare($text, 0, 8);
		$img->build(4);
		$img->output();
	}

	function _newSession() {
		global $filesystem;

		$id = md5(microtime());
		$word = '';
		for ($i=1; $i <= rand(5,6); $i++) {
			$word .= substr($this->chars, mt_rand(0,strlen($this->chars)-1), 1);
		}
		$this->session = compact("word", "id");


		$time = time();
		$limit = $time-6*60*60; // 6h Zeit

		$lines = file($this->datasource);
		$lines = array_map('trim', $lines);

		$save = array("{$id}\t{$time}\t{$word}");
		foreach ($lines as $row) {
			if (empty($row)) {
				continue;
			}
			$data = explode("\t", $row);
			if (isset($data[1]) && $data[1] > $limit){
				$save[] = $row;
			}
		}

		$filesystem->file_put_contents($this->datasource, implode("\n", $save));

		return $this->session;
	}

	function _getFont() {
		$pre = 'captcha_';
		$ext = 'ttf';
		$fonts = array();
		$handle = opendir($this->dir_fonts);
		while ($file = readdir($handle)) {
			if ($file != "." && $file != ".." && !is_dir($this->dir_fonts.$file)) {
				$info = pathinfo($this->dir_fonts.$file);
				$prefix = substr($info['basename'], 0, strlen($pre));
				if (strtolower($info['extension']) == $ext && $prefix == $pre) {
					$fonts[] = $info['basename'];
				}
			}
		}
		if (count($fonts) == 0) {
			return $this->dir_fonts.'trebuchet.ttf';
		}
		else {
			$key = mt_rand(0, count($fonts)-1);
			return $this->dir_fonts.$fonts[$key];
		}
	}

	function _getNoise() {
		$ext = array('png', 'jpeg', 'jpg');
		$noises = array();
		$handle = opendir($this->dir_noises);
		while ($file = readdir($handle)) {
			if ($file != "." && $file != ".." && !is_dir($this->dir_noises.$file)) {
				$info = pathinfo($this->dir_noises.$file);
				if (in_array(strtolower($info['extension']), $ext)) {
					$noises[] = $info['basename'];
				}
			}
		}
		$key = mt_rand(0, count($noises)-1);
		return $this->dir_noises.$noises[$key];
	}

	function _imagecreate($w, $h) {
		$im = @imagecreatetruecolor($w, $h);
		if (!$im) {
			$im = imagecreate($w, $h);
		}
		return $im;
	}

	function _drawImage() {
		// Shorter use of dimensions
		extract($this->dimensions);

		/* get the noise image file*/
		$img_file = $this->_getNoise();
		if($img_file) {
			// Get noise file from stock
			$im_noise = @imagecreatefromjpeg($img_file);
		}
		else {
			// No noise file found, create a random noise
			$im_noise = $this->_imagecreate($w, $h);
			$bg_color = imagecolorallocate($im_noise, 255, 255, 255);
			imagefill($im_noise, 0, 0, $bg_color);

			for ($i = 0; $i < $h; $i++) {
				$line_color = imagecolorallocate($im_noise, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
				imagesetthickness($im_noise, mt_rand(1, 5));
				imageline(
					$im_noise,
					mt_rand(0, 30), // x1
					$i + mt_rand(-10, 10), // y1
					$w - mt_rand(0, 30), // x2
					$i + mt_rand(-10, 10), // y2
					$line_color
				);
			}
		}

		$im = $this->_imagecreate($w, $h);

		// Resize noise
		imagecopyresampled ($im, $im_noise, 0, 0, 0, 0, $w, $h, imagesx($im_noise), imagesy($im_noise));
		imagedestroy($im_noise);
		// Insert Text
		$im_text = $this->_draw_text();
		imagecopymerge ($im, $im_text, 0, 0, 0, 0, $w, $h, mt_rand(70,90));
		imagedestroy($im_text);

		return $im;
	}

	function _draw_text() {
		$text_font = $this->_getFont();
		$text_angle = mt_rand(-15,15);
		if ($text_angle > 8) {
			$spacing = mt_rand(3,8);
		}
		else {
			$spacing = mt_rand(0,5);
		}

		$text_size = $this->dimensions['h'];

		$box = $this->_imagettfbbox($text_size, $text_angle, $text_font, $this->session['word'], $spacing);

		$text_width = $this->_math_diff($box[2], $box[0]);
		$text_height = $this->_math_diff($box[5], $box[3]);
		$margin = ceil( ($text_width/strlen($this->session['word'])) * 0.5);

		$im_string = $this->_imagecreate( ceil($text_width + $margin*2), ceil($text_height + $margin*2) );

		$bg_color = imagecolorallocate ($im_string, 255, 255, 255);
		if ($this->settings['colortext']) {
			$color_array	= array();
			$color_array[]  = array(mt_rand(200,255), mt_rand(0,50), mt_rand(0,50)); // Rot
			$color_array[]  = array(mt_rand(0,50), mt_rand(200,255), mt_rand(0,50)); // Grün
			$color_array[]  = array(mt_rand(0,50), mt_rand(0,50), mt_rand(200,255)); // Blau
			$color_array[]  = array(mt_rand(200,255), mt_rand(0,50), mt_rand(200,255)); // Pink/Violett
			$color_array[]  = array(mt_rand(0,50), mt_rand(170,230), mt_rand(170,230)); // Türkis
			$color_array[]  = array(mt_rand(0,50), mt_rand(0,50), mt_rand(0,50)); // Grey
			$color_array[]  = array(mt_rand(150,200), mt_rand(40,150), mt_rand(0,40)); // Braun-ähnlich

			$text_color = array();
			$entries = strlen($this->session['word']);
			for($i = 0; $i < $entries; $i++) {
				$key = array_rand($color_array);
				$rgb = $color_array[$key];
				$text_color[] = imagecolorallocate ($im_string, $rgb[0], $rgb[1], $rgb[2]);
			}
		}
		else {
			$text_color = array();
			$entries = strlen($this->session['word']);
			for($i = 0; $i < $entries; $i++) {
				$rgb = mt_rand(0,50);
				$text_color[] = imagecolorallocate ($im_string, $rgb, $rgb, $rgb);
			}
		}

		imagefill($im_string, 0, 0, $bg_color);

		$this->_imagettftext($im_string, ceil($text_size*0.9), $text_angle, $margin, $margin + $text_height, $text_color, $text_font, $this->session['word'], $spacing);

		if ($this->settings['filter']) {
			$im_string = $this->_wave($im_string, mt_rand(2,8), true);
		}

		imagecolortransparent($im_string, $bg_color);

		$im_text = $this->_imagecreate($this->dimensions['w'], $this->dimensions['h']);
		imagecopyresampled ($im_text, $im_string, 0, 0, 0, 0, $this->dimensions['w'], $this->dimensions['h'], ceil($text_width+$margin*2), ceil($text_height+$margin*2));

		imagedestroy($im_string);

		return $im_text;
	}

	function _imagettftext($im, $size, $angle, $x, $y, $color, $font, $text, $spacing = 0) {
		$numchar = strlen($text);
		$w = 0;
		for($i = 0; $i < $numchar; $i++) {
			   $char = substr($text, $i, 1);

			if (is_array($color)) {
				$c = array_pop($color);
			}
			else {
				$c = $color;
			}
			imagettftext($im, $size, $angle, ($x + $w + ($i * $spacing)), $y, $c, $font, $char);

			$width = $this->_imagettfbbox($size, $angle, $font, $char);
			$w = $w + $width[2];
		}
	}

	function _imagettfbbox($size, $angle, $font, $text, $spacing = 0) {
		// Get the boundingbox from imagettfbbox(), which is correct when angle is 0
		$bbox = imagettfbbox($size, 0, $font, $text);
		if ($angle == 0) {
			return $bbox;
		}

		// Rotate the boundingbox
		$angle = pi() * 2 - $angle * pi() * 2 / 360;
		for ($i=0; $i<4; $i++) {
			$x = $bbox[$i * 2];
			$y = $bbox[$i * 2 + 1];
			$bbox[$i * 2] = cos($angle) * $x - sin($angle) * $y;
			$bbox[$i * 2 + 1] = sin($angle) * $x + cos($angle) * $y;
		}

		$bbox[2] += (strlen($text)-1)*$spacing;
		$bbox[4] += (strlen($text)-1)*$spacing;

		return $bbox;
	}

	function _math_diff($x1, $x2) {
		$max = max($x1, $x2);
		$min = min($x1, $x2);
		$diff = $max-$min;
		if ($diff < 0) {
			$diff = $diff * (-1);
		}
		return $diff;
	}

	/**
	* Apply a wave filter to an image
	*
	* @param	image	 image			  Image  to convert
	* @param	int		   wave			   Amount of wave to apply
	* @param	bool	randirection	Randomize direction of wave
	*
	* @return	 image
	*/
	function _wave(&$image, $wave = 10, $randirection = true) {
		$image_width = imagesx($image);
		$image_height = imagesy($image);

		$temp = $this->_imagecreate($image_width, $image_height);

		if ($randirection) {
			$direction = (mt_rand(0, 1) == 1) ? true : false;
		}

		for ($x = 0; $x < $image_width; $x++) {
			for ($y = 0; $y < $image_height; $y++) {

				$xo = $wave * sin(2 * 3.1415 * $y / 128);
				$yo = $wave * cos(2 * 3.1415 * $x / 128);

				if ($direction) {
					$newx = $x - $xo;
					$newy = $y - $yo;
				}
				else {
					$newx = $x + $xo;
					$newy = $y + $yo;
				}

				if (($newx > 0 AND $newx < $image_width) AND ($newy > 0 AND $newy < $image_height)) {
					$index = imagecolorat($image, $newx, $newy);
					$colors = imagecolorsforindex($image, $index);
					$color = imagecolorresolve($temp, $colors['red'], $colors['green'], $colors['blue']);
				}
				else {
					$color = imagecolorresolve($temp, 255, 255, 255);
				}

				imagesetpixel($temp, $x, $y, $color);
			}
		}

		return $temp;
	}
}
?>