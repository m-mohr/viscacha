<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

/*
 Start with:
 new text2image;
 (void) prepare()
 (void) build();
 (str) output(str image_format);
 (str) save(str filename)
 (void) destroy()

*/


class text2image {

	var $text;
	var $angle;
	var $size;
	var $font;
	var $img;

	function text2image() {
		// Nothing to do...
	}

	function prepare($text, $angle=0, $size=10, $font='classes/fonts/trebuchet.ttf') {
		$this->text = rawurldecode($text); // Line breaks with [nl]

		$this->angle = $angle+0;
		$this->size = $size+0;
		$this->font = $font;
	}

	function imagettfbbox($size, $angle, $font, $text) {
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
	    return $bbox;
	}

	function imagecolorallocate($hex) {
		$color = str_replace('#', '', $hex);
		if (strlen($color) == 3) {
			$ret = array(
				'r' => str_repeat(hexdec(substr($color, 0, 1)), 2),
				'g' => str_repeat(hexdec(substr($color, 1, 1)), 2),
				'b' => str_repeat(hexdec(substr($color, 2, 1)), 2)
			);
		}
		else {
			$ret = array(
				'r' => hexdec(substr($color, 0, 2)),
				'g' => hexdec(substr($color, 2, 2)),
				'b' => hexdec(substr($color, 4, 2))
			);
		}
		$gd = imagecolorallocate($this->img, $ret['r'], $ret['g'], $ret['b']);
		return $gd;
	}

	function build ($margin = 2, $bg = 'ffffff', $fg = '000000') {
		$TextBoxSize = $this->imagettfbbox($this->size, $this->angle, $this->font, preg_replace("/\[nl\]/is", "\r\n", $this->text));

		$TxtBx_Lwr_L_x = $TextBoxSize[0];
		$TxtBx_Lwr_L_y = $TextBoxSize[1];
		$TxtBx_Lwr_R_x = $TextBoxSize[2];
		$TxtBx_Lwr_R_y = $TextBoxSize[3];
		$TxtBx_Upr_R_x = $TextBoxSize[4];
		$TxtBx_Upr_R_y = $TextBoxSize[5];
		$TxtBx_Upr_L_x = $TextBoxSize[6];
		$TxtBx_Upr_L_y = $TextBoxSize[7];
		if($this->angle <= 90 || $this->angle >= 270 ){
			$width = max($TxtBx_Lwr_R_x, $TxtBx_Upr_R_x) - min($TxtBx_Lwr_L_x, $TxtBx_Upr_L_x);
			$height = max($TxtBx_Lwr_L_y, $TxtBx_Lwr_R_y) - min($TxtBx_Upr_R_y, $TxtBx_Upr_L_y);
			$x = -(min($TxtBx_Upr_L_x, $TxtBx_Lwr_L_x));
			$y = -(min($TxtBx_Upr_R_y, $TxtBx_Upr_L_y));
		}
		else{
			$width = max($TxtBx_Lwr_L_x, $TxtBx_Upr_L_x) - min($TxtBx_Lwr_R_x, $TxtBx_Upr_R_x);
			$height = max($TxtBx_Upr_R_y, $TxtBx_Upr_L_y) - min($TxtBx_Lwr_L_y, $TxtBx_Lwr_R_y);
			$x = -(min($TxtBx_Lwr_R_x,$TxtBx_Upr_R_x));
			$y = -(min($TxtBx_Lwr_L_y, $TxtBx_Lwr_R_y));
		}
		$this->img = imagecreate($width+$margin,$height+$margin);
		// Only PHP-Version 4.3.2 or higher
		if (function_exists('imageantialias')) {
			imageantialias($this->img, true);
		}
		$bgc = $this->imagecolorallocate($bg);
		$fgc = $this->imagecolorallocate($fg);
		imagefill($this->img, 0, 0, $bgc);
		imagecolortransparent($this->img, $bgc);
		imagettftext($this->img, $this->size, $this->angle, $x+ceil($margin/2), $y+ceil($margin/2), $fgc, $this->font, preg_replace("/\[nl\]/is", "\r\n", $this->text));
	}

	function output($format = 'png') {
		if (($format == 'jpeg' || $format == 'jpe' || $format == 'jpeg') && function_exists('imagejpeg')) {
			header("Content-Type: image/jpeg");
			imagejpeg($this->img, '', 90);
		}
		elseif ($format == 'gif' && function_exists('imagegif')) {
			header("Content-Type: image/gif");
			imagegif($this->img);
		}
		else {
			header("Content-Type: image/png");
			imagepng($this->img);
		}
		$this->destroy();
		exit;
	}

	function base64() {
		$this->text = base64_decode($this->text);
	}

	function destroy() {
		imagedestroy($this->img);
	}

	// TO DO:
	function save($file) {

	}
	function cache() {

	}

}
?>
