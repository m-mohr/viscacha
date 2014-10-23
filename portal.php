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

DEFINE('SCRIPTNAME', 'portal');

require_once("data/config.inc.php");
require_once("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
if ($config['indexpage'] == SCRIPTNAME && !defined('IS_INCLUDED')) {
	header("HTTP/1.0 301 Moved Permanently");
    header('Location: index.php');
}
$lang->init($my->language);
$tpl = new tpl();

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
