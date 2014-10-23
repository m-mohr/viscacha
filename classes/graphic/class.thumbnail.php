<?php

class thumbnail {

var $path;
var $lang;

function thumbnail () {
    ImageTypes();
    
	$lang = new lang();
    $lang->group("thumbnail.class");
    $this->lang = $lang->return_array();

    $this->path = '';
	$this->mime = array();
    
    if (function_exists('imagejpeg') && IMG_JPEG) {
    	define('IMAGEJPEG', true);
    }
    else {
    	define('IMAGEJPEG', false);
    }
    if (function_exists('imagegif') && IMG_GIF) {
    	define('IMAGEGIF', true);
    }
    else {
    	define('IMAGEGIF', false);
    }
    if (function_exists('imagepng') && IMG_PNG) {
    	define('IMAGEPNG', true);
    }
    else {
    	define('IMAGEPNG', false);
    }
}

function create_error($text) {
	require('classes/graphic/class.text2image.php');
	$img = new text2image();
	$img->prepare(preg_replace("/<br>/is","\r\n",$text), 0, 8, '../fonts/trebuchet.ttf');
	$img->build(4);
	$img->output();
	exit;
}

function get_image() {
	$mime = get_mimetype($this->path);
	viscacha_header('Content-Type: '.$mime['mime']);
	readfile($this->path);
	exit;
}

function create_image(&$image) {

	if ($image != NULL) {
		$type = get_extension($this->path, TRUE);
	
		imageinterlace ($image, 0);
		if ($type == 'gif' AND IMAGEGIF) {
			imagegif($image, $this->path);
			imagedestroy($image);
		}
		elseif (($type == 'png' OR $type == 'gif') AND IMAGEPNG) {
			imagepng($image, $this->path);
			imagedestroy($image);
		}
		elseif (IMAGEJPEG) {
			imagejpeg($image, $this->path, 70);
			imagedestroy($image);
		}
		else {
			$this->create_error($this->lang['tne_badtype']);
		}
	}
}

function set_cacheuri($path) {
	$this->path = $path;
}

function create_thumbnail($attachment) {
	global $config;
	$thumbnail = NULL;

	$ext = get_extension($attachment);
	if($ext == '.gif' or $ext == '.png' or $ext == '.jpg' or $ext == '.jpeg' or $ext == '.jpe') {
		
		$imageinfo = getimagesize($attachment);
		$new_width = $width = $imageinfo[0];
		$new_height = $height = $imageinfo[1];
		if ($width > $config['tpcthumbwidth'] OR $height > $config['tpcthumbheight']) {
			switch($imageinfo[2]) {
				case 1:
					if (!(function_exists('imagecreatefromgif') AND $image = @imagecreatefromgif($attachment))) {
						$this->create_error($this->lang['tne_giferror']);
					}
					break;
				case 2:
					if (!(function_exists('imagecreatefromjpeg') AND $image = imagecreatefromjpeg($attachment))) {
						$this->create_error($this->lang['tne_jpgerror']);
					}
					break;
				case 3:
					if (!(function_exists('imagecreatefrompng') AND $image = imagecreatefrompng($attachment))) {
						$this->create_error($this->lang['tne_pngerror']);
					}
					break;
			}
			if ($image) {
				$xratio = $width / $config['tpcthumbwidth'];
				$yratio = $height /$config['tpcthumbheight'];
				if ($xratio > $yratio) {
					$new_width = round($width / $xratio);
					$new_height = round($height / $xratio);
				}
				else {
					$new_width = round($width / $yratio);
					$new_height = round($height / $yratio);
				}
				if ($config['gdversion'] == 1) {
					if (!($thumbnail = @imagecreate($new_width, $new_height))) {
						$this->create_error($this->lang['tne_gd1error']);
					}
					imagecopyresized($thumbnail, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				}
				else {
					if (!($thumbnail = @imagecreatetruecolor($new_width, $new_height))) {
						$this->create_error($this->lang['tne_truecolorerror']);
					}
					@imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
					
					if (!version_compare(PHP_VERSION, '4.3.2', 'eq')) {
						$this->UnsharpMask($thumbnail);
					}
					if($ext == '.png') {
						imagetruecolortopalette($thumbnail, true, 256);
					}
				}
                return $thumbnail;
			}
			else {
				$this->create_error($this->lang['tne_imageerror']);
			}
		}
		else {
			if ($imageinfo[0] == 0 AND $imageinfo[1] == 0) {
				return FALSE;
			}
		}
	}
	$this->set_cacheuri($attachment);
	return NULL;
}

function UnsharpMask($img, $amount=80, $radius=0.5, $threshold=3)	{

////////////////////////////////////////////////////////////////////////////////////////////////
////
////                  p h p U n s h a r p M a s k
////
////	Unsharp mask algorithm by Torstein Hønsi 2003.
////	         thoensi_at_netcom_dot_no.
////	           Please leave this notice.
////
///////////////////////////////////////////////////////////////////////////////////////////////


	// $img is an image that is already created within php using
	// imgcreatetruecolor. No url! $img must be a truecolor image.

	// Attempt to calibrate the parameters to Photoshop:
	if ($amount > 500)	$amount = 500;
	$amount = $amount * 0.016;
	if ($radius > 50)	$radius = 50;
	$radius = $radius * 2;
	if ($threshold > 255)	$threshold = 255;
	
	$radius = abs(round($radius)); 	// Only integers make sense.
	if ($radius == 0) {
		return $img; imagedestroy($img); break;		}
	$w = imagesx($img); $h = imagesy($img);
	$imgCanvas = imagecreatetruecolor($w, $h);
	$imgCanvas2 = imagecreatetruecolor($w, $h);
	$imgBlur = imagecreatetruecolor($w, $h);
	$imgBlur2 = imagecreatetruecolor($w, $h);
	imagecopy ($imgCanvas, $img, 0, 0, 0, 0, $w, $h);
	imagecopy ($imgCanvas2, $img, 0, 0, 0, 0, $w, $h);
	

	// Gaussian blur matrix:
	//						
	//	1	2	1		
	//	2	4	2		
	//	1	2	1		
	//						
	//////////////////////////////////////////////////

	// Move copies of the image around one pixel at the time and merge them with weight
	// according to the matrix. The same matrix is simply repeated for higher radii.
	for ($i = 0; $i < $radius; $i++)	{
		imagecopy ($imgBlur, $imgCanvas, 0, 0, 1, 1, $w - 1, $h - 1); // up left
		imagecopymerge ($imgBlur, $imgCanvas, 1, 1, 0, 0, $w, $h, 50); // down right
		imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 1, 0, $w - 1, $h, 33.33333); // down left
		imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 1, $w, $h - 1, 25); // up right
		imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 1, 0, $w - 1, $h, 33.33333); // left
		imagecopymerge ($imgBlur, $imgCanvas, 1, 0, 0, 0, $w, $h, 25); // right
		imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 20 ); // up
		imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 16.666667); // down
		imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 0, $w, $h, 50); // center
		imagecopy ($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);

		// During the loop above the blurred copy darkens, possibly due to a roundoff
		// error. Therefore the sharp picture has to go through the same loop to 
		// produce a similar image for comparison. This is not a good thing, as processing
		// time increases heavily.
		imagecopy ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h);
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 50);
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 33.33333);
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 25);
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 33.33333);
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 25);
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 20 );
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 16.666667);
		imagecopymerge ($imgBlur2, $imgCanvas2, 0, 0, 0, 0, $w, $h, 50);
		imagecopy ($imgCanvas2, $imgBlur2, 0, 0, 0, 0, $w, $h);
		
		}

	// Calculate the difference between the blurred pixels and the original
	// and set the pixels
	for ($x = 0; $x < $w; $x++)	{ // each row
		for ($y = 0; $y < $h; $y++)	{ // each pixel
				
			$rgbOrig = ImageColorAt($imgCanvas2, $x, $y);
			$rOrig = (($rgbOrig >> 16) & 0xFF);
			$gOrig = (($rgbOrig >> 8) & 0xFF);
			$bOrig = ($rgbOrig & 0xFF);
			
			$rgbBlur = ImageColorAt($imgCanvas, $x, $y);
			
			$rBlur = (($rgbBlur >> 16) & 0xFF);
			$gBlur = (($rgbBlur >> 8) & 0xFF);
			$bBlur = ($rgbBlur & 0xFF);
			
			// When the masked pixels differ less from the original
			// than the threshold specifies, they are set to their original value.
			$rNew = (abs($rOrig - $rBlur) >= $threshold) 
				? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig)) 
				: $rOrig;
			$gNew = (abs($gOrig - $gBlur) >= $threshold) 
				? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig)) 
				: $gOrig;
			$bNew = (abs($bOrig - $bBlur) >= $threshold) 
				? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig)) 
				: $bOrig;
			
			
						
			if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
    				$pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);
    				ImageSetPixel($img, $x, $y, $pixCol);
				}
}
		}

	imagedestroy($imgCanvas);
	imagedestroy($imgCanvas2);
	imagedestroy($imgBlur);
	imagedestroy($imgBlur2);
	
	return $img;

	}

}
?>
