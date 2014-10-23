<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2006  Matthias Mohr, MaMo Net
	
	Author: Matthias Mohr
	Publisher: http://www.mamo-net.de
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

DEFINE('SCRIPTNAME', 'images');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);

if ($_GET['action'] == 'vote') {
	$result = $db->query('SELECT id, topic, posts, sticky, status, last, board, vquestion, prefix FROM '.$db->pre.'topics WHERE id = '.$_GET['id'].' LIMIT 1',__LINE__,__FILE__);
	$info = $gpc->prepare($db->fetch_assoc($result));

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
		
		$PG->x[$i] = $row['answer'];
		$PG->y[$i] = $row['votes'];
		
		$i++;
	}
	
	$PG->credits   = $lang->phrase('vote_counter').$votes;
	
	$PG->start();
}

$slog->updatelogged();
$zeitmessung = t2();
$db->close();
?>
