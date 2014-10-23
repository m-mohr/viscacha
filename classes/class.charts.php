<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

/**
 * PowerGraphic
 * Version 1.0
 *
 * Author: Carlos Reche
 * Created: Sep 20, 2004
 * E-mail: carlosreche@yahoo.com
 * Sorocaba, SP - Brazil
 *
 * Editor: Matthias Mohr
 * Last Modification: Aug 02, 2009
 *
 * Authors' comments:
 * PowerGraphic creates 6 different types of graphics with how many parameters you want. You can
 * change the appearance of the graphics in 3 different skins, and you can still cross data from 2
 * graphics in only 1! It's a powerful script, and I recommend you read all the instructions
 * to learn how to use all of this features. Don't worry, it's very simple to use it.
 *
 * This script is free. Please keep the credits.
 */

/**
 * INSTRUNCTIONS OF HOW TO USE THIS SCRIPT  (Please, take a minute to read it. It's important!)
 * NOTE: make sure that your PHP is compiled to work with GD Lib.
 *
 * Here is a list of all parameters you may set:
 * title	  =>  Title of the graphic
 * axis_x	  =>  Name of values from Axis X
 * axis_y	  =>  Name of values from Axis Y
 * graphic_1  =>  Name of Graphic_1 (only shown if you are gonna cross data from 2 different graphics)
 * graphic_2  =>  Name of Graphic_2 (same comment of above)
 *
 * type  =>  Type of graphic (values 1 to 6)
 * 1 => Vertical bars (default), 2 => Horizontal bars, 3 => Dots, 4 => Lines, 5 => Pie, 6 => Donut
 *
 * skin   => Skin of the graphic (values 1 to 3)
 * 1 => Simple (default), 2 => Office, 3 => Matrix, 4 => Spring
 *
 * x[0]  =>  Name of the first parameter in Axis X
 * x[1]  =>  Name of the second parameter in Axis X
 * ... (etc)
 *
 * y[0]  =>  Value from "graphic_1" relative for "x[0]"
 * y[1]  =>  Value from "graphic_1" relative for "x[1]"
 * ... (etc)
 *
 * z[0]  =>  Value from "graphic_2" relative for "x[0]"
 * z[1]  =>  Value from "graphic_2" relative for "x[1]"
 * ... (etc)
 *
 * NOTE: You can't cross data between graphics if you use "pie" or "donut" graphic. Values for "z"
 * won't be considerated.
 */

class PowerGraphic {

	var $x;
	var $y;
	var $z;

	var $title;
	var $axis_x;
	var $axis_y;
	var $graphic_1;
	var $graphic_2;
	var $type;
	var $skin;
	var $credits;
	var $dp;
	var $ds;

	var $width;
	var $height;
	var $height_title;
	var $alternate_x;

	var $total_parameters;
	var $sum_total;
	var $biggest_value;
	var $biggest_parameter;
	var $available_types;

	var $scale;
	var $fontFile;
	var $legend_lineheight;

	function PowerGraphic() {
		$this->x = $this->y = $this->z = array();

		$this->fontFile = './classes/fonts/trebuchet.ttf';

		$this->biggest_x		= NULL;
		$this->biggest_y		= NULL;
		$this->alternate_x		= false;
		$this->graphic_2_exists = false;
		$this->total_parameters = 0;
		$this->sum_total		= 1;

		$this->title	 = "";
		$this->axis_x	 = "";
		$this->axis_y	 = "";
		$this->graphic_1 = "";
		$this->graphic_2 = "";
		$this->type		 = 1;
		$this->skin		 = 1;
		$this->credits   = "";
		$this->dp = ',';
		$this->ds = '.';

		$this->legend_exists		= false;
		$this->biggest_graphic_name = $this->graphic_1;
		$this->height_title			= $this->font_size(5) + 20;
		$this->legend_lineheight	= $this->string_height('0,00 %', 3) + 8;
		$this->space_between_bars   = 40;
		$this->space_between_dots   = 40;
		$this->higher_value			= 0;
		$this->higher_value_str		= 0;

		$this->scale 			   = 255;
		$this->width			   = 0;
		$this->height			   = 0;
		$this->graphic_area_width  = 0;
		$this->graphic_area_height = 0;
		$this->graphic_area_x1	   = 30;
		$this->graphic_area_y1	   = 20 + $this->height_title;
		$this->graphic_area_x2	   = $this->graphic_area_x1 + $this->graphic_area_width;
		$this->graphic_area_y2	   = $this->graphic_area_y1 + $this->graphic_area_height;

		$this->available_types = array(
			1 => 'Vertical Bars',
			2 => 'Horizontal Bars',
			3 => 'Dots',
			4 => 'Lines',
			5 => 'Pie',
			6 => 'Donut'
		);
		$this->available_skins = array(
			1 => 'Simple (Standard)',
			2 => 'Office',
			3 => 'Matrix',
			4 => 'Spring'
		);
	}

	function start() {

		$x = $this->x;
		$y = $this->y;
		$z = $this->z;
		$this->x = array();
		$this->y = array();
		$this->z = array();
		$biggest_x = 0;

		$i = 0;
		// Defines array $temp
		foreach ($x as $id => $value) {

			if (strlen($value) < 1) {
				continue;
			}
			if (empty($y[$id])) {
				$y[$id] = 0;
			}

			$str_size = $this->string_width($value, 3);
			if ($str_size > $biggest_x) {
				$biggest_x = $str_size;
				$this->biggest_x = $value;
			}
			if ($this->biggest_parameter < $str_size) {
				$this->biggest_parameter = $str_size;
			}
			if ($y[$id] > $this->biggest_y) {
				$this->biggest_y = number_format(round($y[$id], 1), 1, ".", "");
			}

			$this->x[$i] = $value;
			$this->y[$i] = $y[$id];

			if ((!empty($z[$i])) && (preg_match("~^(1|2|3|4)$~", $this->type))) {
				$this->graphic_2_exists = true;

				if (empty($z[$id])) {
					$zvalue = $z[$id] = 0;
				}
				else {
					$zvalue = $z[$id];
				}
				$this->z[$i] = $zvalue;

				if ($zvalue > $this->biggest_y) {
					$this->biggest_y = number_format(round($zvalue, 1), 1, ".", "");
				}
			}

			$i++;

		}

		if (($this->type == 5 ||  $this->type == 6) || ($this->graphic_2_exists == true && (!empty($this->graphic_1) || !empty($this->graphic_2))) ) {
			$this->legend_exists = true;
		}
		$this->biggest_graphic_name = ($this->string_width($this->graphic_1, 3) > $this->string_width($this->graphic_2, 3)) ? $this->graphic_1 : $this->graphic_2;
		$this->height_title			= (!empty($this->title)) ? ($this->string_height($this->title, 5) + 20) : 10;
		$this->space_between_bars   = ($this->type == 1) ? 40 : 30;
		$this->graphic_area_y1	   = 20 + $this->height_title;
		$this->graphic_area_x2	   = $this->graphic_area_x1 + $this->graphic_area_width;
		$this->graphic_area_y2	   = $this->graphic_area_y1 + $this->graphic_area_height;

		$this->total_parameters	   = count($this->x);
		$this->sum_total		   = array_sum($this->y);
		$this->space_between_bars += ($this->graphic_2_exists == true) ? 10 : 0;

		$this->calculate_higher_value();
		$this->calculate_width();
		$this->calculate_height();

		$this->create_graphic();
	}

	function create_graphic() {

		$this->img = imagecreatetruecolor($this->width, $this->height);
		$this->load_color_palette();

		// Fill background
		imagefill($this->img, 0, 0, $this->color['background']);

		// Draw title
		if (!empty($this->title)) {
			$center = (int) ($this->width / 2) - ($this->string_width($this->title, 5) / 2);
			$top = (int) ($this->height_title / 2) - ($this->string_height($this->title, 5) / 2);
			$this->imagestring($this->img, 5, $center, $top, $this->title, $this->color['title']);
		}


		// Draw axis and background lines for "vertical bars", "dots" and "lines"
		if (preg_match("~^(1|3|4)$~", $this->type)) {
			if ($this->legend_exists == true) {
				$this->draw_legend();
			}

			$higher_value_y	   = $this->graphic_area_y1 + (0.1 * $this->graphic_area_height);
			$higher_value_size = 0.9 * $this->graphic_area_height;

			$less = $this->string_width($this->higher_value_str, 3);

			imageline($this->img, $this->graphic_area_x1, $higher_value_y, $this->graphic_area_x2, $higher_value_y, $this->color['bg_lines']);
			$this->imagestring($this->img, 3, ($this->graphic_area_x1-$less-7), ($higher_value_y-7), $this->higher_value_str, $this->color['axis_values']);

			for ($i = 1; $i < 10; $i++) {
				$dec_y = $i * ($higher_value_size / 10);
				$x1 = $this->graphic_area_x1;
				$y1 = $this->graphic_area_y2 - $dec_y;
				$x2 = $this->graphic_area_x2;
				$y2 = $this->graphic_area_y2 - $dec_y;

				imageline($this->img, $x1, $y1, $x2, $y2, $this->color['bg_lines']);
				if ($i % 2 == 0) {
					$value = $this->number_formated($this->higher_value * $i / 10);
					$less2 = $this->string_width($value, 3);
					$this->imagestring($this->img, 3, ($x1-$less-7+($less-$less2)), ($y2-7), $value, $this->color['axis_values']);
				}
			}

			// Axis X
			$this->imagestring($this->img, 3, $this->graphic_area_x2+10, $this->graphic_area_y2, $this->axis_x, $this->color['title']);
			imageline($this->img, $this->graphic_area_x1, $this->graphic_area_y2, $this->graphic_area_x2, $this->graphic_area_y2, $this->color['axis_line']);
			// Axis Y
			$axis_height = $this->string_height($this->axis_y, 3) + 5;
			$this->imagestring($this->img, 3, 15, $this->graphic_area_y1-$axis_height, $this->axis_y, $this->color['title']);
			imageline($this->img, $this->graphic_area_x1, $this->graphic_area_y1, $this->graphic_area_x1, $this->graphic_area_y2, $this->color['axis_line']);
		}

		// Draw axis and background lines for "horizontal bars"
		else if ($this->type == 2) {
			if ($this->legend_exists == true) {
				$this->draw_legend();
			}

			$higher_value_x	   = $this->graphic_area_x2 - (0.2 * $this->graphic_area_width);
			$higher_value_size = 0.8 * $this->graphic_area_width;

			imageline($this->img, ($this->graphic_area_x1+$higher_value_size), $this->graphic_area_y1, ($this->graphic_area_x1+$higher_value_size), $this->graphic_area_y2, $this->color['bg_lines']);
			$this->imagestring($this->img, 3, (($this->graphic_area_x1+$higher_value_size) - ($this->string_width($this->higher_value_str, 3)/2)), ($this->graphic_area_y2+2), $this->higher_value_str, $this->color['axis_values']);

			for ($i = 1, $alt = 15; $i < 10; $i++) {
				$dec_x = number_format(round($i * ($higher_value_size  / 10), 1), 1, ".", "");

				imageline($this->img, ($this->graphic_area_x1+$dec_x), $this->graphic_area_y1, ($this->graphic_area_x1+$dec_x), $this->graphic_area_y2, $this->color['bg_lines']);
				if ($i % 2 == 0) {
					$alt   = (strlen($this->biggest_y) > 4 && $alt != 15) ? 15 : 2;
					$value = $this->number_formated($this->higher_value * $i / 10);
					$this->imagestring($this->img, 3, (($this->graphic_area_x1+$dec_x) - ($this->string_width($value, 3)/2)), ($this->graphic_area_y2+$alt), $value, $this->color['axis_values']);
				}
			}

			// Axis X
			$this->imagestring($this->img, 3, ($this->graphic_area_x2+10), ($this->graphic_area_y2), $this->axis_y, $this->color['title']);
			imageline($this->img, $this->graphic_area_x1, $this->graphic_area_y2, $this->graphic_area_x2, $this->graphic_area_y2, $this->color['axis_line']);
			// Axis Y
			$axis_height = $this->string_height($this->axis_y, 3) + 5;
			$this->imagestring($this->img, 3, 15, ($this->graphic_area_y1-$axis_height), $this->axis_x, $this->color['title']);
			imageline($this->img, $this->graphic_area_x1, $this->graphic_area_y1, $this->graphic_area_x1, $this->graphic_area_y2, $this->color['axis_line']);
		}


		// Draw legend box for "pie" or "donut"
		else if ($this->type == 5 ||  $this->type == 6) {
			$this->draw_legend();
		}



		/**
		* Draw graphic: VERTICAL BARS
		*/
		if ($this->type == 1) {
			$num = 0;
			$x   = $this->graphic_area_x1 + 20;

			foreach ($this->x as $i => $parameter) {
				if (isset($this->z[$i])) {
					$size = round($this->z[$i] * $higher_value_size / $this->higher_value);
					$x1   = $x + 10;
					$y1   = ($this->graphic_area_y2 - $size);
					$x2   = $x1 + 20;
					$y2   = $this->graphic_area_y2;
					imagefilledrectangle($this->img, $x1+1, $y1-1, $x2+1, $y2, $this->color['bars_2_shadow']);
					imagefilledrectangle($this->img, $x1+2, $y1-2, $x2+2, $y2, $this->color['bars_2_shadow']);
					imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color['bars_2']);
				}

				$str_height = $this->string_height($parameter, 3);
				$str_width = $this->string_width($parameter, 3);
				$biggest_x = $this->string_height($this->biggest_x, 3);
				$correction = $str_height-$this->font_size(3)-1;

				$size = round($this->y[$i] * $higher_value_size / $this->higher_value);
				$alt  = (($num % 2 == 0) && ($this->string_width($this->biggest_x, 3) > 35)) ? $biggest_x*1.5-$correction : 2-$correction;
				$x1   = $x;
				$y1   = ($this->graphic_area_y2 - $size);
				$x2   = $x1 + 20;
				$y2   = $this->graphic_area_y2;
				$x   += $this->space_between_bars;
				$num++;

				imagefilledrectangle($this->img, $x1+1, $y1-1, $x2+1, $y2, $this->color['bars_1_shadow']);
				imagefilledrectangle($this->img, $x1+2, $y1-2, $x2+2, $y2, $this->color['bars_1_shadow']);
				imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color['bars_1']);

				if ($this->biggest_parameter > 76) {
					$this->imagestringup($this->img, 3, $x1+($str_height/2)-$correction, $y2+5+$str_width, $parameter, $this->color['axis_values']);
				}
				else {
					$this->imagestring($this->img, 3, ((($x1+$x2)/2) - ($str_width/2)) + 2, ($y2+$alt+2), $parameter, $this->color['axis_values']);
				}
			}
		}

		/**
		* Draw graphic: HORIZONTAL BARS
		*/
		else if ($this->type == 2) {
			$y = 10;

			foreach ($this->x as $i => $parameter) {
				if (isset($this->z[$i])) {
					$size = round($this->z[$i] * $higher_value_size / $this->higher_value);
					$x1   = $this->graphic_area_x1 + 1;
					$y1   = $this->graphic_area_y1 + $y + 10;
					$x2   = $x1 + $size;
					$y2   = $y1 + 15;
					imagefilledrectangle($this->img, $x1, $y1+1, $x2+1, $y2+1, $this->color['bars_2_shadow']);
					imagefilledrectangle($this->img, $x1, $y1+2, $x2+2, $y2+2, $this->color['bars_2_shadow']);
					imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color['bars_2']);
					$this->imagestring($this->img, 3, ($x2+7), ($y1+7), $this->number_formated($this->z[$i], 2), $this->color['bars_2_shadow']);
				}

				$str_height = $this->string_height($parameter, 3);
				$str_width = $this->string_width($parameter, 3);
				$correction = $str_height-$this->font_size(3)-1;

				$size = round(($this->y[$i] / $this->higher_value) * $higher_value_size);
				$x1   = $this->graphic_area_x1 + 1;
				$y1   = $this->graphic_area_y1 + $y;
				$x2   = $x1 + $size;
				$y2   = $y1 + 15;
				$y   += $this->space_between_bars;

				imagefilledrectangle($this->img, $x1, $y1+1, $x2+1, $y2+1, $this->color['bars_1_shadow']);
				imagefilledrectangle($this->img, $x1, $y1+2, $x2+2, $y2+2, $this->color['bars_1_shadow']);
				imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color['bars_1']);
				$this->imagestring($this->img, 3, ($x2+7), ($y1+2), $this->number_formated($this->y[$i], 2), $this->color['bars_1_shadow']);

				$this->imagestring($this->img, 3, ($x1 - ($str_width+7)), ($y1+2-$correction), $parameter, $this->color['axis_values']);
			}
		}


		/**
		* Draw graphic: DOTS or LINE
		*/
		else if ($this->type == 3 ||  $this->type == 4) {

			$x[0] = $this->graphic_area_x1+1;

			foreach ($this->x as $i => $parameter) {
				if ($this->graphic_2_exists == true) {
					$size  = round($this->z[$i] * $higher_value_size / $this->higher_value);
					$z[$i] = $this->graphic_area_y2 - $size;
				}

				$str_height = $this->string_height($parameter, 3);
				$str_width = $this->string_width($parameter, 3);
				$biggest_x = $this->string_height($this->biggest_x, 3);
				$correction = $str_height-$this->font_size(3)-1;

				$alt   = (($i % 2 == 0) && ($this->string_width($this->biggest_x, 3) > 35)) ? $biggest_x*1.5-$correction : 2-$correction;
				$size  = round($this->y[$i] * $higher_value_size / $this->higher_value);
				$y[$i] = $this->graphic_area_y2 - $size;

				if ($i != 0) {
					imageline($this->img, $x[$i], ($this->graphic_area_y1+10), $x[$i], ($this->graphic_area_y2-1), $this->color['bg_lines']);
				}
				if ($this->biggest_parameter > 76) {
					$this->imagestringup($this->img, 3, ($x[$i]-($str_height/2)-$correction), ($this->graphic_area_y2+$str_width+5), $parameter, $this->color['axis_values']);
				}
				else {
					$this->imagestring($this->img, 3, ($x[$i]-($str_width/2)), ($this->graphic_area_y2+$alt+2), $parameter, $this->color['axis_values']);
				}

				$x[$i+1] = $x[$i] + 40;
			}

			foreach ($x as $i => $value_x) {
				if ($this->graphic_2_exists == true) {
					if ($this->type == 4) {
						$color = $this->color['line_2_shadow'];
					}
					else {
						$color = $this->color['line_2'];
					}
					if (isset($z[$i+1])) {
						// Draw lines
						if ($this->type == 4) {
							imageantialias($this->img, true);
							imageline($this->img, $x[$i], $z[$i], $x[$i+1], $z[$i+1], $this->color['line_2']);
							imageline($this->img, $x[$i], ($z[$i]+1), $x[$i+1], ($z[$i+1]+1), $this->color['line_2']);
							imageantialias($this->img, false);
						}
						imagefilledrectangle($this->img, $x[$i]-1, $z[$i]-1, $x[$i]+2, $z[$i]+2, $color);
					}
					else { // Draw last dot
						imagefilledrectangle($this->img, $x[$i-1]-1, $z[$i-1]-1, $x[$i-1]+2, $z[$i-1]+2, $color);
					}
				}

				if (count($y) > 1) {
					if ($this->type == 4) {
						$color = $this->color['line_1_shadow'];
					}
					else {
						$color = $this->color['line_1'];
					}
					if (isset($y[$i+1])) {
						// Draw lines
						if ($this->type == 4) {
							imageantialias($this->img, true);
							imageline($this->img, $x[$i], $y[$i], $x[$i+1], $y[$i+1], $this->color['line_1']);
							imageline($this->img, $x[$i], ($y[$i]+1), $x[$i+1], ($y[$i+1]+1), $this->color['line_1']);
							imageantialias($this->img, false);
						}
						imagefilledrectangle($this->img, $x[$i]-1, $y[$i]-1, $x[$i]+2, $y[$i]+2, $color);
					}
					else { // Draw last dot
						imagefilledrectangle($this->img, $x[$i-1]-1, $y[$i-1]-1, $x[$i-1]+2, $y[$i-1]+2, $color);
					}
				}

			}
		}


		/**
		* Draw graphic: PIE or DONUT
		*/
		else if ($this->type == 5 ||  $this->type == 6) {
			$center_x = ($this->graphic_area_x1 + $this->graphic_area_x2) / 2;
			$center_y = ($this->graphic_area_y1 + $this->graphic_area_y2) / 2;
			$width	  = $this->graphic_area_width;
			$height   = $this->graphic_area_height;
			$start	  = 0;
			$sizes	  = array();

			foreach ($this->x as $i => $parameter) {
				if ($this->sum_total == 0) {
					$size = 360;
				}
				else {
					$size	 = $this->y[$i] * 360 / $this->sum_total;
				}
				$sizes[] = $size;
				$start  += $size;
			}
			$start = 270;

			// Draw PIE
			if ($this->type == 5) {
				$height /= 1.25;

				if ($this->sum_total == 0) {
					for($i=15; $i >= 0; $i--) {
						imagefilledarc($this->img, $center_x, ($center_y+$i), $width, $height, $start, ($start+$size), $this->color['gray_shadow'], IMG_ARC_NOFILL);
					}
					imagefilledarc($this->img, $center_x, $center_y, $width, $height, $start, ($start+$size), $this->color['gray'], IMG_ARC_PIE);
				}
				else {
					$num_color = 1;
					foreach ($sizes as $i => $size) {
						$shadowColor = 'arc_'.$num_color.'_shadow';
						if ($size > 0) {
							for($i=15; $i >= 0; $i--) {
								imagefilledarc($this->img, $center_x, ($center_y+$i), $width, $height, $start, ($start+$size), $this->color[$shadowColor], IMG_ARC_NOFILL);
							}
							$start += $size;
						}
						if ($num_color >= $this->color['palette_count']) {
							$num_colot = 1;
						}
						else {
							$num_color++;
						}
					}

					$start = 270;
					$num_color = 1;
					// Draw pieces
					foreach ($sizes as $i => $size) {
						$color = 'arc_' . $num_color;
						if ($size > 0) {
							imagefilledarc($this->img, $center_x, $center_y, $width, $height, $start, ($start+$size), $this->color[$color], IMG_ARC_PIE);
							$start += $size;
						}
						if ($num_color >= $this->color['palette_count']) {
							$num_colot = 1;
						}
						else {
							$num_color++;
						}
					}
				}
			}

			// Draw DONUT
			else if ($this->type == 6) {
				if ($this->sum_total == 0) {
					imagefilledarc($this->img, $center_x, $center_y, $width, $height, $start, ($start+$size), $this->color['gray'], IMG_ARC_PIE);
					imagefilledarc($this->img, $center_x, $center_y, 100, 100, 0, 360, $this->color['background'], IMG_ARC_PIE);
				}
				else {
					$num_color = 1;
					foreach ($sizes as $i => $size) {
						$color = 'arc_' . $num_color;
						if ($size > 0) {
							imagefilledarc($this->img, $center_x, $center_y, $width, $height, $start, ($start+$size), $this->color[$color], IMG_ARC_PIE);
							$start += $size;
						}
						if ($num_color >= $this->color['palette_count']) {
							$num_colot = 1;
						}
						else {
							$num_color++;
						}
					}
					imagefilledarc($this->img, $center_x, $center_y, 100, 100, 0, 360, $this->color['background'], IMG_ARC_PIE);
					imagearc($this->img, $center_x, $center_y, 100, 100, 0, 360, $this->color['bg_legend']);
					imagearc($this->img, $center_x, $center_y, ($width+1), ($height+1), 0, 360, $this->color['bg_legend']);
				}
			}
		}


		if (!empty($this->credits)) {
			$this->draw_credits();
		}


		header('Content-type: image/png');
		imagepng($this->img);
		imagedestroy($this->img);
	}

	function calculate_width() {
		switch ($this->type) {
			// Vertical bars
			case 1:
				$this->legend_box_width   = ($this->legend_exists == true) ? ($this->string_width($this->biggest_graphic_name, 3) + 25) : 0;
				$this->graphic_area_width = ($this->space_between_bars * $this->total_parameters) + 30;
				$this->graphic_area_x1   += $this->string_width(($this->higher_value_str), 3);
				$this->width += $this->graphic_area_x1 + 20;
				$this->width += ($this->legend_exists == true) ? 50 : (($this->string_width($this->axis_x, 3)) + 10);
				break;

			// Horizontal bars
			case 2:
				$this->legend_box_width   = ($this->legend_exists == true) ? ($this->string_width($this->biggest_graphic_name, 3) + 25) : 0;
				$this->graphic_area_width = ($this->string_width($this->higher_value_str, 3) > 50) ? (5 * ($this->string_width($this->higher_value_str, 3)) * 0.85) : 200;
				$this->graphic_area_x1 += 7 * strlen($this->biggest_x);
				$this->width += ($this->legend_exists == true) ? 60 : (($this->string_width($this->axis_y, 3)) + 30);
				$this->width += $this->graphic_area_x1;
				break;

			// Dots
			case 3:
				$this->legend_box_width   = ($this->legend_exists == true) ? ($this->string_width($this->biggest_graphic_name, 3) + 25) : 0;
				$this->graphic_area_width = ($this->space_between_dots * $this->total_parameters) - 10;
				$this->graphic_area_x1   += $this->string_width(($this->higher_value_str), 3);
				$this->width += $this->graphic_area_x1 + 20;
				$this->width += ($this->legend_exists == true) ? 40 : (($this->string_width($this->axis_x, 3)) + 10);
				break;

			// Lines
			case 4:
				$this->legend_box_width   = ($this->legend_exists == true) ? ($this->string_width($this->biggest_graphic_name, 3) + 25) : 0;
				$this->graphic_area_width = ($this->space_between_dots * $this->total_parameters) - 10;
				$this->graphic_area_x1   += $this->string_width(($this->higher_value_str), 3);
				$this->width += $this->graphic_area_x1 + 20;
				$this->width += ($this->legend_exists == true) ? 40 : (($this->string_width($this->axis_x, 3)) + 10);
				break;

			// Pie
			case 5:
				$this->legend_box_width   = $this->string_width($this->biggest_x, 3) + 85;
				$this->graphic_area_width = 200;
				$this->width += 90;
				break;

			// Donut
			case 6:
				$this->legend_box_width   = $this->string_width($this->biggest_x, 3) + 85;
				$this->graphic_area_width = 180;
				$this->width += 90;
				break;
		}

		$this->width += $this->graphic_area_width;
		$this->width += $this->legend_box_width;

		$titlewidth = $this->string_width($this->title, 5);
		if ($titlewidth > $this->width) {
			$this->width = $titlewidth+20;
		}

		$this->graphic_area_x2 = $this->graphic_area_x1 + $this->graphic_area_width;
		$this->legend_box_x1   = $this->graphic_area_x2 + 40;
		$this->legend_box_x2   = $this->legend_box_x1 + $this->legend_box_width;
	}

	function calculate_height() {
		switch ($this->type) {
			// Vertical bars
			case 1:
				$this->legend_box_height   = ($this->graphic_2_exists == true) ? 40 : 0;
				$this->graphic_area_height = 150;
				if ($this->biggest_parameter > 76) {
					$this->height += $this->biggest_parameter+45;
				}
				else {
					$this->height += 75;
				}
				break;

			// Horizontal bars
			case 2:
				$this->legend_box_height   = ($this->graphic_2_exists == true) ? 40 : 0;
				$this->graphic_area_height = ($this->space_between_bars * $this->total_parameters) + 10;
				$this->height += 75;
				break;

			// Dots
			case 3:
				$this->legend_box_height   = ($this->graphic_2_exists == true) ? 40 : 0;
				$this->graphic_area_height = 150;
				if ($this->biggest_parameter > 76) {
					$this->height += $this->biggest_parameter+45;
				}
				else {
					$this->height += 75;
				}
				break;

			// Lines
			case 4:
				$this->legend_box_height   = ($this->graphic_2_exists == true) ? 40 : 0;
				$this->graphic_area_height = 150;
				if ($this->biggest_parameter > 76) {
					$this->height += $this->biggest_parameter+45;
				}
				else {
					$this->height += 75;
				}
				break;

			// Pie
			case 5:
				$this->legend_box_height   = (!empty($this->axis_x)) ? 30 : 4;
				$this->legend_box_height  += ($this->legend_lineheight * $this->total_parameters);
				$this->graphic_area_height = 120;
				$this->height += 50;
				break;

			// Donut
			case 6:
				$this->legend_box_height   = (!empty($this->axis_x)) ? 30 : 4;
				$this->legend_box_height  += ($this->legend_lineheight * $this->total_parameters);
				$this->graphic_area_height = 180;
				$this->height += 50;
				break;
		}

		$this->height += $this->height_title;
		$this->height += ($this->legend_box_height > $this->graphic_area_height) ? ($this->legend_box_height - $this->graphic_area_height) : 0;
		$this->height += $this->graphic_area_height;

		$this->graphic_area_y2 = $this->graphic_area_y1 + $this->graphic_area_height;
		$this->legend_box_y1   = $this->graphic_area_y1 + 10;
		$this->legend_box_y2   = $this->legend_box_y1 + $this->legend_box_height;
	}

	function draw_legend() {
		$x1 = $this->legend_box_x1;
		$y1 = $this->legend_box_y1;
		$x2 = $this->legend_box_x2;
		$y2 = $this->legend_box_y2;

		imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color['bg_legend']);

		$x = $x1 + 5;
		$y = $y1 + 5;

		$line_height = $this->legend_lineheight - 8;

		// Draw legend values for VERTICAL BARS, HORIZONTAL BARS, DOTS and LINES
		if (preg_match("~^(1|2|3|4)$~", $this->type)) {
			$color_1 = ($this->type == 1 ||  $this->type == 2) ? $this->color['bars_1']   : $this->color['line_1'];
			$color_2 = ($this->type == 1 ||  $this->type == 2) ? $this->color['bars_2'] : $this->color['line_2'];
			$correction_1 = $line_height-$this->string_height($this->graphic_1, 3);
			$correction_2 = $line_height-$this->string_height($this->graphic_2, 3);

			imagefilledrectangle($this->img, $x, $y, ($x+$line_height), ($y+$line_height), $color_1);
			imagerectangle($this->img, $x, $y, ($x+$line_height), ($y+$line_height), $this->color['title']);
			$this->imagestring($this->img, 3, ($x+$line_height+5), ($y-1+$correction_1), $this->graphic_1, $this->color['axis_values']);
			$y += $line_height + 8;
			imageline($this->img, ($x1+5), ($y-4), ($x2-5), ($y-4), $this->color['bg_lines']);
			imagefilledrectangle($this->img, $x, $y, ($x+$line_height), ($y+$line_height), $color_2);
			imagerectangle($this->img, $x, $y, ($x+$line_height), ($y+$line_height), $this->color['title']);
			$this->imagestring($this->img, 3, ($x+$line_height+5), ($y-1+$correction_2), $this->graphic_2, $this->color['axis_values']);
		}

		// Draw legend values for PIE or DONUT
		else if ($this->type == 5 ||  $this->type == 6) {
			if (!empty($this->axis_x)) {
				$this->imagestring($this->img, 3, ((($x1+$x2)/2) - ($this->string_width($this->axis_x, 3)/2)), $y, $this->axis_x, $this->color['title']);
				$y += 25;
			}

			$num = 1;

			foreach ($this->x as $i => $parameter) {

				if(!isset($this->color['arc_' . $num])) {
					$num = 1;
				}
				$color = 'arc_' . $num;

				if ($this->sum_total != 0) {
					$percent = $this->number_formated(round(($this->y[$i] * 100 / $this->sum_total), 2), 2);
				}
				else {
					$percent = $this->number_formated(0);
				}
				$percent .= ' %';

				$less = $this->string_width($percent, 3) + 10;

				if ($num != 1) {
					imageline($this->img, ($x1+5), ($y-4), ($x2-5), ($y-4), $this->color['bg_lines']);
				}
				imagefilledrectangle($this->img, $x, $y, ($x+$line_height), ($y+$line_height), $this->color[$color]);
				imagerectangle($this->img, $x, $y, ($x+$line_height), ($y+$line_height), $this->color['title']);

				$correction = $line_height-$this->string_height($parameter, 3);

				$this->imagestring($this->img, 3, ($x+$line_height+5), ($y-1+$correction), $parameter, $this->color['axis_values']);
				$this->imagestring($this->img, 3, ($x2-$less), ($y-1), $percent, $this->color['axis_values']);

				$y += $line_height + 8;
				$num++;
			}
		}
	}

	function imagettfbbox($size, $angle, $font, $text) {
		$box = imagettfbbox($size, $angle, $font, $text);

		$min_x = min(array($box[0], $box[2], $box[4], $box[6]));
		$max_x = max(array($box[0], $box[2], $box[4], $box[6]));
		$min_y = min(array($box[1], $box[3], $box[5], $box[7]));
		$max_y = max(array($box[1], $box[3], $box[5], $box[7]));

		return array(
			'left' => ($min_x >= -1) ? -abs($min_x + 1) : abs($min_x + 2),
			'top' => abs($min_y),
			'width' => abs($max_x - $min_x),
			'height' => abs($max_y - $min_y),
			'box' => $box
		);
	}

	function font_size($font) {
		if ($font <= 1) {
			$size = 8;
		} else if ($font <= 3) {
			$size = 9;
		} else if ($font >= 4) {
			$size = 12;
		}
		return $size;
	}

	function string_width($string, $size) {
		$size = $this->font_size($size);
		$z = $this->imagettfbbox($size, 0, $this->fontFile, $string);
		return $z['width'];
	}

	function string_height($string, $size) {
		$size = $this->font_size($size);
		$z = $this->imagettfbbox($size, 0, $this->fontFile, $string);
		return $z['height'];
	}

	function imagestring($im, $font, $x, $y, $str, $col) {
		$size = $this->font_size($font);
		$y = $y + $this->string_height($str, $font);
		imagettftext($im, $size, 0, $x, $y, $col, $this->fontFile, $str);
		return true;
	}

	function imagestringup($im, $font, $x, $y, $str, $col) {
		$size = $this->font_size($font);
		$x = $x + $this->string_height($str, $font);
		imagettftext($im, $size, 90, $x, $y, $col, $this->fontFile, $str);
		return true;
	}

	function calculate_higher_value() {
		$digits   = strlen(round($this->biggest_y));
		$interval = pow(10, ($digits-1));
		$this->higher_value		= round(($this->biggest_y - ($this->biggest_y % $interval) + $interval), 1);
		$this->higher_value_str = $this->number_formated($this->higher_value);
	}

	function number_formated($number, $dec_size = 1) {
		return number_format(round($number, $dec_size), $dec_size, $this->dp, $this->ds);
	}

	// Mod: Powered by can be changed!
	function draw_credits() {
		$this->imagestring($this->img, 1, ($this->width-$this->string_width($this->credits, 1)-3), ($this->height-$this->string_height($this->credits, 1)-3), $this->credits, $this->color['title']);
	}

	function load_color_palette() {

		$colour_palette = array(
			array(255,0,0),
			array(255,255,0),
			array(0,255,0),
			array(0,255,255),
			array(0,0,255),
			array(255,0,255),
			array(128,0,0),
			array(128,128,0),
			array(0,128,0),
			array(0,128,128),
			array(0,0,128),
			array(128,0,128),
			array(97,97,97),
			array(192,192,192),
			array(255,128,128),
			array(255,255,128),
			array(128,255,128),
			array(128,255,255),
			array(128,128,255),
			array(255,128,255),
			array(194,0,0),
			array(194,194,0),
			array(0,194,0),
			array(0,194,194),
			array(0,0,194),
			array(194,0,194),
			array(255,192,128),
			array(194,255,128),
			array(128,255,192),
			array(128,194,255),
			array(192,128,255),
			array(255,128,94),
			array(161,97,33),
			array(97,161,33),
			array(33,161,97),
			array(33,97,161),
			array(97,33,161),
			array(161,33,97),
			array(255,128,0),
			array(128,255,0),
			array(0,255,128),
			array(0,128,255),
			array(128,0,255),
			array(255,0,128),
			array(128,64,0),
			array(64,128,0),
			array(0,128,64),
			array(0,64,128),
			array(64,0,128),
			array(128,0,64),
		);

		switch ($this->skin) {
			// Office
			case 2:
				$background = array(220, 220, 220);
				$this->color['title']		= imagecolorallocate($this->img,   0,   0, 100);
				$this->color['gray']		= array(175, 175, 175);
				$this->color['axis_values'] = imagecolorallocate($this->img,  50,  50,  50);
				$this->color['axis_line']   = imagecolorallocate($this->img, 100, 100, 100);
				$this->color['bg_lines']	= imagecolorallocate($this->img, 240, 240, 240);
				$this->color['bg_legend']   = imagecolorallocate($this->img, 205, 205, 205);

				if ($this->type == 1 ||  $this->type == 2) {
					$colours = array(
						array(100, 150, 200),
						array(200, 250, 150),
					);
				}
				else if ($this->type == 3 ||  $this->type == 4) {
					$colours = array(
						array(100, 150, 200),
						array(230, 100, 100),
					);
				}
				else if ($this->type == 5 ||  $this->type == 6) {
					$colours = $colour_palette;
				}
				break;

			// Matrix
			case 3:
				$background = array(0, 0, 0);
				$this->color['title']		= imagecolorallocate($this->img, 255, 255, 255);
				$this->color['gray']		= array(175, 175, 175);
				$this->color['axis_values'] = imagecolorallocate($this->img,   0, 230,   0);
				$this->color['axis_line']   = imagecolorallocate($this->img,   0, 200,   0);
				$this->color['bg_lines']	= imagecolorallocate($this->img, 100, 100, 100);
				$this->color['bg_legend']   = imagecolorallocate($this->img,  70,  70,  70);

				if ($this->type == 1 ||  $this->type == 2) {
					$colours = array(
						array(50, 200, 50),
						array(255, 255, 255)
					);
				}
				else if ($this->type == 3 ||  $this->type == 4) {
					$colours = array(
						array(180, 180, 180),
						array(0, 180, 0)
					);
				}
				else if ($this->type == 5 ||  $this->type == 6) {
					$colours = $colour_palette;
				}
				break;


			// Spring
			case 4:
				$background = array(250, 250, 220);
				$this->color['title']		= imagecolorallocate($this->img, 130,  30,  40);
				$this->color['gray']		= array(175, 175, 175);
				$this->color['axis_values'] = imagecolorallocate($this->img,  50, 150,  50);
				$this->color['axis_line']   = imagecolorallocate($this->img,  50, 100,  50);
				$this->color['bg_lines']	= imagecolorallocate($this->img, 200, 224, 180);
				$this->color['bg_legend']   = imagecolorallocate($this->img, 230, 230, 200);

				if ($this->type == 1 ||  $this->type == 2) {
					$colours = array(
						array(255, 170,  80),
						array(250, 230,  80)
					);
				}
				else if ($this->type == 3 ||  $this->type == 4) {
					$colours = array(
						array(230, 100,   0),
						array(220, 200,  50)
					);
				}
				else if ($this->type == 5 ||  $this->type == 6) {
					$colours = $colour_palette;
				}
				break;


			// Simple (1)
			default:
				$background = array(255, 255, 255);
				$this->color['title']		= imagecolorallocate($this->img,   0,   0, 0);
				$this->color['gray']		= array(175, 175, 175);
				$this->color['axis_values'] = imagecolorallocate($this->img,   0,   0,  0);
				$this->color['axis_line']   = imagecolorallocate($this->img,   0,   0, 0);
				$this->color['bg_lines']	= imagecolorallocate($this->img, 200, 200, 200);
				$this->color['bg_legend']   = imagecolorallocate($this->img, 255, 255, 255);

				if ($this->type == 1 ||  $this->type == 2) {
					$colours = array(
						array(80, 139, 199),
						array(200, 250, 150)
					);
				}
				else if ($this->type == 3 ||  $this->type == 4) {
					$colours = array(
						array(100, 150, 200),
						array(230, 100, 100)
					);
				}
				else if ($this->type == 5 ||  $this->type == 6) {
					$colours = $colour_palette;

				}
				break;
			}
		$this->color['background']  = imagecolorallocate($this->img, $background[0], $background[1], $background[2]);
		$this->scale = ceil(array_sum($background)/count($background));

		$this->color['gray_shadow']	= imagecolorallocate($this->img, $this->get_shadow($this->color['gray'][0]), $this->get_shadow($this->color['gray'][1]), $this->get_shadow($this->color['gray'][2]));
		$this->color['gray']		= imagecolorallocate($this->img, $this->color['gray'][0], $this->color['gray'][1], $this->color['gray'][2]);

 		if ($this->type == 1 ||  $this->type == 2) {
			$i = 1;
			foreach ($colours as $array) {
				$this->color['bars_'.$i]		 	= imagecolorallocate($this->img, $array[0], $array[1], $array[2]);
				$this->color['bars_'.$i.'_shadow'] 	= imagecolorallocate($this->img, $this->get_shadow($array[0]), $this->get_shadow($array[1]), $this->get_shadow($array[2]));
				$i++;
			}
 		}
 		else if ($this->type == 3 ||  $this->type == 4) {
			$i = 1;
			foreach ($colours as $array) {
				$this->color['line_'.$i]		 	= imagecolorallocate($this->img, $array[0], $array[1], $array[2]);
				$this->color['line_'.$i.'_shadow'] 	= imagecolorallocate($this->img, $this->get_shadow($array[0]), $this->get_shadow($array[1]), $this->get_shadow($array[2]));
				$i++;
			}
 		}
 		elseif ($this->type == 5 ||  $this->type == 6) {
			$i = 1;
			$this->color['palette_count'] = count($colours);
			foreach ($colours as $array) {
				$this->color['arc_'.$i]		 		= imagecolorallocate($this->img, $array[0], $array[1], $array[2]);
				$this->color['arc_'.$i.'_shadow'] 	= imagecolorallocate($this->img, $this->get_shadow($array[0]), $this->get_shadow($array[1]), $this->get_shadow($array[2]));
				$i++;
			}
 		}


	}

	function get_shadow($color) {
		if ($this->scale > 100) {
			$shadow = $color * 0.5;
		}
		else {
			if (($this->scale*1.4) > 210) {
				$shadow = $color * 1.2;
			}
			else {
				$shadow = $color * 1.4;
			}
			if ($shadow > 255) {
				$shadow = 255;
			}
		}
		return $shadow;
	}

	function reset_values() {
		$this->title	 = NULL;
		$this->axis_x	 = NULL;
		$this->axis_y	 = NULL;
		$this->type		 = NULL;
		$this->skin		 = NULL;
		$this->graphic_1 = NULL;
		$this->graphic_2 = NULL;
		$this->credits   = '';
		$this->x = $this->y = $this->z = array();
	}

}

?>