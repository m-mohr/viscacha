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

define('SCRIPTNAME', 'portal');
if (!defined('VISCACHA_CORE')) {
	define('VISCACHA_CORE', '1');
}

require_once("data/config.inc.php");

if ($config['indexpage'] == SCRIPTNAME && !defined('IS_INCLUDED')) {
	header("HTTP/1.0 301 Moved Permanently");
    header('Location: index.php');
    exit;
}

require_once("classes/function.viscacha_frontend.php");

if ($plugins->countPlugins('portal') == 0) {
	if ($config['indexpage'] == SCRIPTNAME) {
		error($lang->phrase('docs_not_found'), 'forum.php'.SID2URL_1);
	}
	else {
		$slog->updatelogged();
		$db->close();
		header("HTTP/1.0 301 Moved Permanently");
	    header('Location: forum.php');
	    exit;
	}
}

$my->p = $slog->Permissions();
$my->pb = $slog->GlobalPermissions();

$breadcrumb->Add($lang->phrase('portal_title'));
echo $tpl->parse("header");

BBProfile($bbcode);

($code = $plugins->load('portal')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");

$phpdoc->Out();
$db->close();
?>
