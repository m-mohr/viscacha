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
	$result = $db->execute("SELECT id, topic, posts, sticky, status, last, board, vquestion, prefix FROM {$db->pre}topics WHERE id = '{$_GET['id']}'");
	$info = $result->fetch();

	$PG = new Viscacha\Graphic\Charts();

	$skin = $gpc->get('skin', int, 1);
	$modus = $gpc->get('modus', int, 1);

	$PG->title     = $gpc->plain_str($info['vquestion']);
	$PG->axis_y    = $gpc->plain_str($lang->phrase('vote_export_votes'));
	$PG->type      = $modus;
	$PG->skin      = $skin;
	$PG->dp 	   = $lang->phrase('decpoint');
	$PG->ds 	   = $lang->phrase('thousandssep');

	$votes = 0;
	$i = 0;
	$result = $db->execute("SELECT COUNT(r.id) as votes, v.id, v.answer FROM {$db->pre}vote AS v LEFT JOIN {$db->pre}votes AS r ON r.aid=v.id WHERE v.tid = '{$info['id']}' GROUP BY v.id ORDER BY v.id");
	while ($row = $result->fetch()) {
		$votes += $row['votes'];

		$PG->x[$i] = $gpc->plain_str($row['answer']);
		$PG->y[$i] = $row['votes'];

		$i++;
	}

	$PG->credits   = $gpc->plain_str($lang->phrase('vote_counter').$votes);

	$PG->start();
}
elseif ($_GET['action'] == 'textimage') {
	$img = new Viscacha\Graphic\Text2Image();
	$text = $gpc->get('text', none);
	$angle = $gpc->get('angle', int);
	$size = $gpc->get('size', int);
	$bg = $gpc->get('bg');
	$fg = $gpc->get('fg');
	$file = $gpc->get('file');
	$enc = $gpc->get('enc');

	if (empty($text)) {
		$text = '-';
	}
	else {
		$text = mb_substr($text, 0, 256);
	}
	if ($size < 6 || $size > 50) {
		$size = 10;
	}
	if (strlen($bg) != 3 && strlen($bg) != 6) {
		$bg = 'ffffff';
	}
	if (strlen($fg) != 3 && strlen($fg) != 6) {
		$fg = '000000';
	}
	if (!preg_match('/^[\w\d\-\.]+$/u', $file) || !file_exists("./classes/fonts/{$file}.ttf")) {
		$file = null;
	}
	else {
		$file = "./classes/fonts/{$file}.ttf";
	}
	$img->prepare($text, $angle, $size, $file);
	if (!empty($enc)) {
		$img->base64();
	}
	$img->build(4, $bg, $fg);
	$img->output();
}
elseif ($_GET['action'] == 'm_email') {
	$email = $lang->phrase('profile_mail_1');
	
	$row = $db->fetch("SELECT id, opt_hidemail, mail FROM {$db->pre}user WHERE id = '{$_GET['id']}' AND deleted_at IS NULL");
	if (isset($row['opt_hidemail']) && $row['opt_hidemail'] == 0) {
		$email = $row['mail'];
	}

	$img = new Viscacha\Graphic\Text2Image();
	$img->prepare($email, 0, 10);
	$img->build();
	$img->output();
}
($code = $plugins->load('images_end')) ? eval($code) : null;

$slog->updatelogged();