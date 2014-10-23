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

if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

function flood_protect() {
	global $config, $my;

	//Konfigurieren
	if ($config['enableflood'] == 0 || $my->p['flood'] == 0) {
		return TRUE;
	}
	if ($my->p['guest'] == 1) {
		$filet = 'g';
	}
	else {
		$filet = 'm';
	}
	$file = 'data/'.$filet.'_flood.php';

    // Daten Laden
    if (file_exists($file) == true) {
		$load = file_get_contents($file);
		$floods = explode("\n",$load);
	}
	else {
		return TRUE;
	}

	// Daten prüfen
	foreach ($floods as $row) {
		if (strlen($row) < 3) {
			continue;
		}
		$data = explode("\t",$row);
		if ($filet == 'm') {
			if ($data[0] == $my->id && $data[1] > (time()-$my->p['flood'])){
				return FALSE;
			}
			else  {
				return TRUE;
			}
		}
		else {
			if ($data[0] == getip() && $data[1] > (time()-$my->p['flood'])){
				return FALSE;
			}
			else  {
				return TRUE;
			}
		}
	}
	return TRUE;
}
function set_flood() {
	global $config, $my, $filesystem;

	if ($config['enableflood'] == 0 || $my->p['flood'] == 0) {
		return FALSE;
	}

	if ($my->p['guest'] == 1) {
		$filet = 'g';
	}
	else {
		$filet = 'm';
	}
	$file = 'data/'.$filet.'_flood.php';

    // Daten Laden
    if (file_exists($file) == true) {
		$load = file_get_contents($file);
		$floods = explode("\n",$load);
	}
	if (file_exists($file) == false || count($floods) == 0) {
		$floods = array();
	}
	// Daten prüfen
	$save = array();
	foreach ($floods as $row) {
		if (strlen($row) < 3) {
			continue;
		}
		$data = explode("\t",$row);

		if ($filet == 'm') {
			if ($data[0] == $my->id && $data[1] > (time()-$my->p['flood'])){
				$save[] = $my->id."\t".time();
				$set = 1;
			}
			if ($data[0] != $my->id && $data[1] > (time()-$my->p['flood'])){
				$save[] = $row;
			}
		}
		else {
			if ($data[0] == getip() && $data[1] > (time()-$my->p['flood'])){
				$save[] = getip()."\t".time();
				$set = 1;
			}
			if ($data[0] != getip() && $data[1] > (time()-$my->p['flood'])){
				$save[] = $row;
			}
		}
	}
	if (isset($set) == FALSE) {
		if ($filet == 'm') {
			$save[] = $my->id."\t".time();
		}
		else {
			$save[] = getip()."\t".time();
		}
	}
    // Daten Speichern
	$filesystem->file_put_contents($file,implode("\n",$save));
	return TRUE;
}

?>
