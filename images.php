<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2007  Matthias Mohr, MaMo Net

	Author: Matthias Mohr
	Publisher: http://www.viscacha.org
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
error_reporting(E_ALL);

define('SCRIPTNAME', 'images');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$my->p = $slog->Permissions();

function ImageHexColorAllocate(&$image, $string) {
	sscanf($string, "%2x%2x%2x", $red, $green, $blue);
	return ImageColorAllocate($image,$red,$green,$blue);
}

($code = $plugins->load('images_start')) ? eval($code) : null;

if ($_GET['action'] == 'vote') {
	$result = $db->query('
	SELECT id, topic, posts, sticky, status, last, board, vquestion, prefix
	FROM '.$db->pre.'topics
	WHERE id = '.$_GET['id'].'
	LIMIT 1
	',__LINE__,__FILE__);
	$info = $db->fetch_assoc($result);

	require_once('classes/class.charts.php');
	$PG = new PowerGraphic();

	$skin = $gpc->get('skin', int, 1);
	$modus = $gpc->get('modus', int, 1);

	$PG->title     = $info['vquestion'];
	$PG->axis_y    = $lang->phrase('vote_export_votes');
	$PG->type      = $modus;
	$PG->skin      = $skin;
	$PG->dp 	   = $lang->phrase('decpoint');
	$PG->ds 	   = $lang->phrase('thousandssep');

	$votes = 0;
	$i = 0;
	$result = $db->query("SELECT COUNT(r.id) as votes, v.id, v.answer FROM {$db->pre}vote AS v LEFT JOIN {$db->pre}votes AS r ON r.aid=v.id WHERE v.tid = '{$info['id']}' GROUP BY v.id ORDER BY v.id",__LINE__,__FILE__);
	while ($row = $db->fetch_assoc($result)) {
		$votes += $row['votes'];

		$PG->x[$i] = $gpc->plain_str($row['answer'], false);
		$PG->y[$i] = $row['votes'];

		$i++;
	}

	$PG->credits   = $lang->phrase('vote_counter').$votes;

	$PG->start();
}
elseif ($_GET['action'] == 'captcha') {
	if ($_GET['type'] == 'register') {
		$width = $config['botgfxtest_width'];
		$height = $config['botgfxtest_height'];
	}
	if ($_GET['type'] == 'post') {
		$width = $config['botgfxtest_posts_width'];
		$height = $config['botgfxtest_posts_height'];
	}
	else {
		$width = $gpc->get('width', int, 160);
		$height = $gpc->get('height', int, 40);
	}

	include("classes/graphic/class.veriword.php");
	$vword = new VeriWord();
	$vword->set_filter($config['botgfxtest_filter']);
	$vword->set_color($config['botgfxtest_colortext']);
	$vword->set_size($width, $height, $config['botgfxtest_format'], $config['botgfxtest_quality']);
	send_nocache_header();
	$vword->output_image($_GET['captcha']);
}
elseif ($_GET['action'] == 'textimage') {
	require('classes/graphic/class.text2image.php');

	$img = new text2image();
	if (empty($_GET['text'])) {
		$_GET['text'] = '-';
	}
	if (empty($_GET['angle'])) {
		$_GET['angle'] = 0;
	}
	if (empty($_GET['size']) || $_GET['size'] < 6) {
		$_GET['size'] = 10;
	}
	if (!empty($_GET['bg']) && strlen($_GET['bg']) > 2) {
		$bg = $_GET['bg'];
	}
	else {
		$bg = 'ffffff';
	}
	if (!empty($_GET['fg']) && strlen($_GET['fg']) > 2) {
		$fg = $_GET['fg'];
	}
	else {
		$fg = '000000';
	}
	$img->prepare($_GET['text'], $_GET['angle'], $_GET['size'], 'classes/fonts/trebuchet.ttf');
	if (!empty($_REQUEST['enc'])) {
		$img->base64();
	}
	$img->build(4, $bg, $fg);
	$img->output();
}
elseif ($_GET['action'] == 'postrating' || $_GET['action'] == 'memberrating' || $_GET['action'] == 'threadrating') {
	$colors = array('FF0000', 'E44C00', 'E89A00', 'EBE700', '9EE800', '4DE400');

	if ($_GET['action'] == 'memberrating' && ($config['postrating'] == 1 || $my->p['admin'] == 1)) {
		$result = $db->query("SELECT rating FROM {$db->pre}postratings WHERE aid = '{$_GET['id']}'");
		$width = 100;
		$height = 8;
	}
	elseif ($_GET['action'] == 'postrating' && ($config['postrating'] == 1 || $my->p['admin'] == 1)) {
		$result = $db->query("SELECT rating FROM {$db->pre}postratings WHERE pid = '{$_GET['id']}'");
		$width = 50;
		$height = 8;
	}
	elseif ($_GET['action'] == 'threadrating' && ($config['postrating'] == 1 || $my->p['admin'] == 1)) {
		$result = $db->query("SELECT rating FROM {$db->pre}postratings WHERE tid = '{$_GET['id']}'");
		$width = 50;
		$height = 8;
	}
	else {
		$error = true;
		($code = $plugins->load('images_rating_error')) ? eval($code) : null;
		if ($error == true) {
			header("Content-type: image/png");
			$image = imagecreate(1, 1);
			$back = ImageColorAllocate($image,0,0,0);
			imagecolortransparent($image, $back);
			imagePNG($image);
			imagedestroy($image);
		}
	}
	($code = $plugins->load('images_rating_start')) ? eval($code) : null;

	$ratings = array();
	while ($row = $db->fetch_assoc($result)) {
		$ratings[] = $row['rating'];
	}
	$ratingcounter = count($ratings);
	if ($ratingcounter > 0) {
		$rating = round((array_sum($ratings)/$ratingcounter+1)*($width/2));
		$avg = array_sum($ratings)/$ratingcounter;
	}
	else {
		$rating = $width/2;
		$avg = 0;
	}
	$five = ceil(($avg+1)*2.5);

	header ("Content-type: image/png");

	$image = imagecreate($width+2, $height+2);
	$back = ImageHexColorAllocate($image, 'ffffff');
	$fill = ImageHexColorAllocate($image, $colors[$five]);
	$border = ImageHexColorAllocate($image, '000000');
	ImageFilledRectangle($image,1,1,$width,$height,$back);
	ImageFilledRectangle($image,1,1,$rating,$height,$fill);
	ImageRectangle($image,0,0,$width+1,$height+1,$border);
	imagePNG($image);
	imagedestroy($image);
}
($code = $plugins->load('images_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
$db->close();
?>
