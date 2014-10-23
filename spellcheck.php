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

DEFINE('SCRIPTNAME', 'spellcheck');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();

if ($config['spellcheck'] == 0) {
	error('Spellcheck wurde deaktiviert!', 'self.close()');
}
$action = $gpc->get('action', str);

($code = $plugins->load('spellcheck_start')) ? eval($code) : null;

if ($action == "execute") {
	include("classes/spellchecker/function.php");
	echo $tpl->parse("spellcheck/execute");
}
elseif ($action == "frames") {
	echo $tpl->parse("spellcheck/frames");
}
elseif ($action == "controls") {
	echo $tpl->parse("spellcheck/controls");
}
elseif ($action == "blank") {
	echo '';
}

($code = $plugins->load('spellcheck_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
$phpdoc->Out();
$db->close();
?>
