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

define('SCRIPTNAME', 'components');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$my->p = $slog->Permissions();

$cid = $gpc->get('cid', int);

$com = $scache->load('components');
$cache = $com->get();

($code = $plugins->load('components_start')) ? eval($code) : null;

if (isset($cache[$cid])) {
	DEFINE('COM_CID', $cid);
	DEFINE('COM_ID', $cache[$cid]['package']);
	DEFINE('COM_DIR', 'modules/'.COM_ID.'/');
	$myini = new INI();
	$ini = $myini->read(COM_DIR.'component.ini');
	$mod = $gpc->get('file', str, 'frontpage');
	if (!isset($ini['module'][$mod])) {
		DEFINE('COM_MODULE', 'frontpage');
	}
	else {
		DEFINE('COM_MODULE', $mod);
	}
	DEFINE('COM_MODULE_FILE', $ini['module'][COM_MODULE]);
	DEFINE('COM_FILE', $ini['module']['frontpage']);

	if (!file_exists(COM_DIR.COM_FILE)) {
		error($lang->phrase('section_not_available'));
	}
	else {
		define('COM_LANG_OLD_DIR', $lang->getdir(true));
        $lang->setdir(COM_LANG_OLD_DIR.DIRECTORY_SEPARATOR.COM_DIR);
        ($code = $plugins->load('components_prepared')) ? eval($code) : null;
	    if (COM_MODULE == 'frontpage') {
            include(COM_DIR.COM_FILE);
        }
        else {
            include(COM_DIR.COM_MODULE_FILE);
        }
        $lang->setdir(COM_LANG_OLD_DIR);
	}
	($code = $plugins->load('components_end')) ? eval($code) : null;
}
else {
	error($lang->phrase('component_na'));
}

$slog->updatelogged();
$phpdoc->Out();
$db->close();
?>
