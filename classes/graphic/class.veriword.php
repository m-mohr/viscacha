<?php
## Verification Word v2
#######
## Author:	Huda M Elmatsani
## Editor:	Matthias Mohr for Viscacha
## Email:	justhuda at netrada.co.id
#######
## Copyright (c) 2004 Huda M Elmatsani All rights reserved.
## This program is free for any purpose use.
########

class VeriWord {

	/* path to font directory*/
	var $dir_font 	= './classes/fonts/';
	/* path to background image directory*/
	var $dir_noise 	= "./classes/graphic/noises/";
	var $word 		= "";
	var $wordarray 	= array();
	var $im_width 	= 0;
	var $im_height 	= 0;
	var $im_type	= ""; //image type: jpeg, png
	var $sess_file 	= './data/captcha.php';
	var $type 		= 0;
	var $noises 	= array();
	var $fonts 		= array();
	var $filter 	= 0;
	
	function VeriWord() {
	
		// Get Font-Files
		$handle = opendir($this->dir_font);
		$pre = 'captcha_';
		while ($file = readdir($handle)) {
			if ($file != "." && $file != ".." && !is_dir($this->dir_font.$file)) {					  
				$nfo = pathinfo($this->dir_font.$file);
				$prefix = substr($nfo['basename'], 0, strlen($pre));
				if ($nfo['extension'] == 'ttf' && $prefix == $pre) {
					$this->fonts[] = $nfo['basename'];
				}
			}
		}

		// Get Noise-Files
		$handle = opendir($this->dir_noise);
		while ($file = readdir($handle)) {
			if ($file != "." && $file != ".." && !is_dir($this->dir_noise.$file)) {					  
				$nfo = pathinfo($this->dir_noise.$file);
				if ($nfo['extension'] == 'jpg' || $nfo['extension'] == 'jpeg') {
					$this->noises[] = $nfo['basename'];
				}
			}
		}
		
	}

	function set_filter ($filter) {
		$this->filter = $filter;
	}

	function set_size ($w=200, $h=80) {
		if ($w > 1000 || $h > 1000) {
			$w=200;
			$h=80;
		}
		$this->im_width = $w;
		$this->im_height = $h;
	}

	function set_veriword($type=0) {
		if ($type == 0) {
			$this->word = $this->pick_number();
		}
		else {
			$this->word = $this->pick_word();
		}
		return $this->set_session();
	}

	function check_session($fid, $word) {
		$floods = file($this->sess_file);
		foreach ($floods as $row) {
			if (strlen($row) < 47) {
				continue;
			}
			$data = explode("\t",$row);
			if ($data[0] == $fid && strcasecmp($data[2], $word) == 0){
				return TRUE;
			}
		}
		return FALSE;
	}
	
	function set_session() {
	
		$fid = md5(microtime());
		$floods = array();
		$word = &$this->word;
		$floods = file($this->sess_file);
		$save = array();
		$limit = time()-60*60;
		
//		if (count($floods) > 1000) {
//			die('We can not accept registrations at the moment, because spam bots are attacking the script. Please try again in an hour!');
//		}
		
		foreach ($floods as $row) {
			if (strlen($row) < 47) {
				continue;
			}
			$row = trim($row);
			$data = explode("\t",$row);
			if ($data[1] > $limit){
				$save[] = $row;
			}
		}
		$save[] = $fid."\t".time()."\t".$word;
		
		file_put_contents($this->sess_file, implode("\n",$save));
		
		return $fid;
	}

	function output_word($fid) {

		$floods = file($this->sess_file);
		foreach ($floods as $row) {
			if (strlen($row) < 47) {
				continue;
			}
			$data = explode("\t",$row);
			if ($data[0] == $fid){
				$this->word = $data[2];
			}
		}

		$t = array(
			array('    ###    ',' ########  ','  ######  ',' ######   ',' ######## ',' ######## ','  ######   ',' ##     ## ',' #### ','       ## ',' ##    ## ',' ##       ',' ##     ## ',' ##    ## ','   #####   ',' ########  ','  #######  ',' ########  ','  ######  ',' ######## ',' ##     ## ',' ##     ## ',' ##      ## ',' ##     ## ',' ##    ## ',' ######## '),
			array('   ## ##   ',' ##     ## ',' ##    ## ',' ##   ##  ',' ##       ',' ##       ',' ##    ##  ',' ##     ## ','  ##  ','       ## ',' ##   ##  ',' ##       ',' ###   ### ',' ###   ## ','  ##   ##  ',' ##     ## ',' ##     ## ',' ##     ## ',' ##    ## ','    ##    ',' ##     ## ',' ##     ## ',' ##  ##  ## ','  ##   ##  ','  ##  ##  ','      ##  '),
			array('  ##   ##  ',' ##     ## ',' ##       ',' ##    ## ',' ##       ',' ##       ',' ##        ',' ##     ## ','  ##  ','       ## ',' ##  ##   ',' ##       ',' #### #### ',' ####  ## ',' ##     ## ',' ##     ## ',' ##     ## ',' ##     ## ',' ##       ','    ##    ',' ##     ## ',' ##     ## ',' ##  ##  ## ','   ## ##   ','   ####   ','     ##   '),
			array(' ##     ## ',' ########  ',' ##       ',' ##    ## ',' ######   ',' ######   ',' ##   #### ',' ######### ','  ##  ','       ## ',' #####    ',' ##       ',' ## ### ## ',' ## ## ## ',' ##     ## ',' ########  ',' ##     ## ',' ########  ','  ######  ','    ##    ',' ##     ## ',' ##     ## ',' ##  ##  ## ','    ###    ','    ##    ','    ##    '),
			array(' ######### ',' ##     ## ',' ##       ',' ##    ## ',' ##       ',' ##       ',' ##    ##  ',' ##     ## ','  ##  ',' ##    ## ',' ##  ##   ',' ##       ',' ##     ## ',' ##  #### ',' ##     ## ',' ##        ',' ##  ## ## ',' ##   ##   ','       ## ','    ##    ',' ##     ## ','  ##   ##  ',' ##  ##  ## ','   ## ##   ','    ##    ','   ##     '),
			array(' ##     ## ',' ##     ## ',' ##    ## ',' ##   ##  ',' ##       ',' ##       ',' ##    ##  ',' ##     ## ','  ##  ',' ##    ## ',' ##   ##  ',' ##       ',' ##     ## ',' ##   ### ','  ##   ##  ',' ##        ',' ##    ##  ',' ##    ##  ',' ##    ## ','    ##    ',' ##     ## ','   ## ##   ',' ##  ##  ## ','  ##   ##  ','    ##    ','  ##      '),
			array(' ##     ## ',' ########  ','  ######  ',' ######   ',' ######## ',' ##       ','  ######   ',' ##     ## ',' #### ','  ######  ',' ##    ## ',' ######## ',' ##     ## ',' ##    ## ','   #####   ',' ##        ','  ##### ## ',' ##     ## ','  ######  ','    ##    ','  #######  ','    ###    ','  ###  ###  ',' ##     ## ','    ##    ',' ######## ')
		);
		
		$set = array(
		'A' => 0,
		'B' => 1,
		'C' => 2,
		'D' => 3,
		'E' => 4,
		'F' => 5,
		'G' => 6,
		'H' => 7,
		'I' => 8,
		'J' => 9,
		'K' => 10,
		'L' => 11,
		'M' => 12,
		'N' => 13,
		'O' => 14,
		'P' => 15,
		'Q' => 16,
		'R' => 17,
		'S' => 18,
		'T' => 19,
		'U' => 20,
		'V' => 21,
		'W' => 22,
		'X' => 23,
		'Y' => 24,
		'Z' => 25
		);
	
		$text = '';
		for ($i = 0; $i < 7; $i++) {
			foreach ($this->wordarray as $v) {
				$v = strtoupper($v);
				$text .= $t[$i][$set[$v]];	
			}
			$text .= "<br>";
		}
	
		return str_replace(' ', '&nbsp;', $text);
	}

	function output_image($fid, $type='jpeg') {
		$floods = file($this->sess_file);
		foreach ($floods as $row) {
			if (strlen($row) < 47) {
				continue;
			}
			$data = explode("\t",$row);
			if ($data[0] == $fid){
				$this->word = $data[2];
			}
		}

		/* make it not case sensitive*/
		$this->im_type = strtolower($type);

		/* check image type availability */
		$this->validate_type();

		/* draw the image  */	
		$this->draw_image();
		
		/* show the image  */			
		switch($this->im_type){
			case 'jpeg' :
			case 'jpg' 	:
				header("Content-type: image/jpeg");
				imagejpeg($this->im);
				break;
			case 'png' :
				header("Content-type: image/png");
				imagepng($this->im);
				break;
		}
		exit;
	}

	function pick_number() {
	    $newpass = ""; 
        $string="1324657890";
        mt_srand((double)microtime()*1000000); 

        for ($i=1; $i <= 5; $i++) { 
            $newpass .= substr($string, mt_rand(0,strlen($string)-1), 1); 
        } 
		return $newpass;
	}
	
	function pick_word() {
	    $newpass = ""; 
        $string="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        mt_srand((double)microtime()*1000000); 

        for ($i=1; $i <= 5; $i++) { 
            $a = substr($string, mt_rand(0,strlen($string)-1), 1); 
            $newpass .= $a;
            $this->wordarray[$i] = $a;
        } 
		return $newpass;
	}

	function get_font() {
		srand ((float)microtime()*1000000);
		shuffle($this->fonts);
		$f = array_shift($this->fonts);
		if(file_exists($this->dir_font.$f)) {
			return $this->dir_font.$f;
		}
		else return false;
	}


	function draw_text() {

		/* pick one font type randomly from font directory */
		$text_font 	= $this->get_font();
		/* angle for text inclination */
		srand ((double)microtime()*1000000);
		$text_angle = rand(-2,2);

		/* create canvas for text drawing */
		$im_text 		= @imagecreate ($this->im_width, $this->im_height); 
   		$bg_color 		= imagecolorallocate ($im_text, 255, 255, 255); 
		//imagefill ( $im_text, 0, 0, $bg_color );

		/* pick color for text */
		$text_color 	= imagecolorallocate ($im_text, 0, 0, 0);

                /* numeric means built-in font */
		if(is_numeric($text_font)) {

			$text_width 	= imagefontwidth($text_font) * strlen($this->word);
			$text_height 	= imagefontheight($text_font);
			$margin			= $text_width * 0.25; 
			$im_string 		= @imagecreatetruecolor( $text_width + $margin, $text_height + $margin ); 
			if (!$im_string) { 
				$im_string 	= imageCreate( $text_width + $margin, $text_height + $margin ); // For older Versions 
			}

			/* calculate center position of text */
			$text_x     	= $margin/2;
			$text_y 		= $margin/2;
	
			imagestring ( $im_string, $text_font, $text_x, $text_y, $this->word, $text_color );

			imagecolortransparent($im_string, $bg_color);

			//resize the text, because built-in font is very small
			$done = @imagecopyresampled ($im_text, 
							$im_string, 
							0, 0, 0, 0, 
							$this->im_width, 
							$this->im_height, 
							$text_width+$margin, 
							$text_height+$margin);

		if (!$done) {
			imagecopyresized ($im_text, 
							$im_string, 
							0, 0, 0, 0, 
							$this->im_width, 
							$this->im_height, 
							$text_width+$margin, 
							$text_height+$margin);
		}

		} else {
			/* initial text size */
			$text_size  = 30;  
			/* calculate text width and height */
			$box 		= imagettfbbox ( $text_size, $text_angle, $text_font, $this->word);
			$text_width	= $box[2]-$box[0]; //text width
			$text_height= $box[5]-$box[3]; //text height
	
			/* adjust text size */
			$text_size  = round((20 * $this->im_width)/$text_width);  
	
			/* recalculate text width and height */
			$box 		= imagettfbbox ( $text_size, $text_angle, $text_font, $this->word);
			$text_width	= $box[2]-$box[0]; //text width
			$text_height= $box[5]-$box[3]; //text height
	
			/* calculate center position of text */
			$text_x     	= ($this->im_width - $text_width)/2;
			$text_y 		= ($this->im_height - $text_height)/2;
			
	
			/* draw text into canvas */
			imagettftext	(	$im_text,
								$text_size,
								$text_angle,
								$text_x,
								$text_y,
								$text_color, 
								$text_font, 
								$this->word);
		}

		/* draw angled lines with random degree and various y position) */
		imagesetthickness ( $im_text, rand(0,2));
		imageline( $im_text, 0, $this->im_height/2 + rand(10,-10), $this->im_width, $this->im_height/2 + rand(10,-10), $text_color );

		/* remove background color */
		imagecolortransparent($im_text, $bg_color);
		if ($this->filter == 1) {
			$im_text = $this->wave($im_text, 3);
		}
		return $im_text;
	}

	function get_noise() {
		/* pick one noise image randomly from image directory */
		srand ((float)microtime()*1000000);
		shuffle($this->noises);
		$n = array_shift($this->noises);
		if(file_exists($this->dir_noise.$n)) {
			return $this->dir_noise.$n;
		}
		else return FALSE;
	}

	function draw_image() {
		
		/* get the noise image file*/
		$img_file 		= $this->get_noise();

		if($img_file) {
			/* create "noise" background image from your image stock*/
			$im_noise 	= @imagecreatefromjpeg ($img_file);
		} else {

			/* if fail to load image file, create it on the fly */
			$im_noise 	= $this->draw_noise();
		}

 		$noise_width 	= imagesx($im_noise); 
		$noise_height 	= imagesy($im_noise); 
		
		/* resize the background image to fit the size of image output */
		$this->im 		= @imagecreatetruecolor($this->im_width,$this->im_height); 
		if (!$this->im) { 
			$this->im 	= imageCreate($this->im_width,$this->im_height); // For older Versions 
		}
							
		$done = @imagecopyresampled (	$this->im, 
										$im_noise, 
										0, 0, 0, 0, 
										$this->im_width, 
										$this->im_height, 
										$noise_width, 
										$noise_height);
		if (!$done) {
			imagecopyresized (	$this->im, 
								$im_noise, 
								0, 0, 0, 0, 
								$this->im_width, 
								$this->im_height, 
								$noise_width, 
								$noise_height);
		}
		/* put text image into background image */
		imagecopymerge ( 	$this->im, 
							$this->draw_text(), 
							0, 0, 0, 0, 
							$this->im_width, 
							$this->im_height, 
							70 );

		return $this->im;
	}

	function draw_noise() {

		/* create "noise" background image*/
		$im_noise 	= @imagecreate($this->im_width,$this->im_height); 
		$bg_color 	= imagecolorallocate ($im_noise, 255, 255, 255);
		imagefill ( $im_noise, 0, 0, $bg_color );

		for($i=0; $i < $this->im_height; $i++) {
			$c = rand (0,255);
			$line_color 	= imagecolorallocate ($im_noise, $c, $c, $c);
		}

		return $im_noise;
		imagedestroy($im_noise);
	}

	function validate_type() {
		/* check image type availability*/
		$is_available = FALSE;
		
		switch($this->im_type){
			case 'jpeg' :
			case 'jpg' 	:
				if(function_exists("imagejpeg"))
				$is_available = TRUE;
				break;
			case 'png' :
				if(function_exists("imagepng"))
				$is_available = TRUE;
				break;
		}
		if(!$is_available && function_exists("imagejpeg")){
			/* if not available, cast image type to jpeg*/
			$this->im_type = "jpeg";
			return TRUE;
		}
		else if(!$is_available && !function_exists("imagejpeg")){
		   die("No image support on this PHP server"); 		
		}
		else
			return TRUE;
	}
	
	/**
	* Apply a wave filter to an image
	*
	* @param	image	image			Image  to convert
	* @param	int		wave			Amount of wave to apply
	* @param	bool	randirection	Randomize direction of wave
	*
	* @return	image
	*/
	function wave(&$image, $wave = 10, $randirection = true)
	{
		$image_width = imagesx($image);
		$image_height = imagesy($image);

		$temp = @imagecreatetruecolor($image_width, $image_height); 
		if (!$temp) { 
			$temp = imagecreate($image_width, $image_height); // For older Versions 
		}

		if ($randirection)
		{
			$direction = (mt_rand(0, 1) == 1) ? true : false;
		}

		$middlex = floor($image_width / 2);
		$middley = floor($image_height / 2);

		for ($x = 0; $x < $image_width; $x++)
		{
			for ($y = 0; $y < $image_height; $y++)
			{

				$xo = $wave * sin(2 * 3.1415 * $y / 128);
				$yo = $wave * cos(2 * 3.1415 * $x / 128);

				if ($direction)
				{
					$newx = $x - $xo;
					$newy = $y - $yo;
				}
				else
				{
					$newx = $x + $xo;
					$newy = $y + $yo;
				}

				if (($newx > 0 AND $newx < $image_width) AND ($newy > 0 AND $newy < $image_height))
				{
					$index = imagecolorat($image, $newx, $newy);
                    $colors = imagecolorsforindex($image, $index);
                    $color = imagecolorresolve($temp, $colors['red'], $colors['green'], $colors['blue']);
				}
				else
				{
					$color = imagecolorresolve($temp, 255, 255, 255);
				}

				imagesetpixel($temp, $x, $y, $color);
			}
		}

		return $temp;
	}
	
}
?>
