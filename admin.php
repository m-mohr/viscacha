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

DEFINE('SCRIPTNAME', 'admin');

include ("data/config.inc.php");
include ("admin/lib/function.viscacha_backend.php");

$benchmark = benchmarktime();

if (is_dir('install/')) {
	die('For your security please completely remove the installation directory ('.realpath('install/').') including all files and sub-folders - then refresh this page');
}

$slog = new slog();
$my = $slog->logged();
$my->p = $slog->Permissions();

if ($my->p['admin'] == 1) {
	$job = $gpc->get('job', str);

	if ($action == "frames") {
		include('admin/frames.php');
	}
	elseif ($action == 'index') {
		include('admin/start.php');
	}
	elseif ($action == 'settings') {
		include('admin/settings.php');
	}
	elseif ($action == 'spider') {
		include('admin/spider.php');
	}
	elseif ($action == 'filetypes') {
		include('admin/filetypes.php');
	}
	elseif ($action == 'cron') {
		include('admin/cron.php');
	}
	elseif ($action == 'db') {
		include('admin/db.php');
	}
	elseif ($action == 'forums') {
		include('admin/forums.php');
	}
	elseif ($action == 'bbcodes') {
		include('admin/bbcodes.php');
	}
	elseif ($action == 'members') {
		include('admin/members.php');
	}
	elseif ($action == 'cms') {
		include('admin/cms.php');
	}
	elseif ($action == 'groups') {
		include('admin/groups.php');
	}
	elseif ($action == 'slog') {
		include('admin/slog.php');
	}
	elseif ($action == 'misc') {
		include('admin/misc.php');
	}
	elseif ($action == 'explorer') {
		include('admin/explorer.php');
	}
	elseif ($action == 'language') {
		include('admin/language.php');
	}
	elseif ($action == 'designs') {
		include('admin/designs.php');
	}
	else {
		if (strlen($action) == 0) {
			include('admin/frames.php');
		}
		else {
			echo head();
			error('admin.php?action=index'.SID2URL_x, 'The page you have requested does not exist.');
		}
	}
}
else {
	if ($my->p['admin'] == 0 && $my->vlogin) {
		echo head();
		error('index.php'.SID2URL_1, 'You are not allowed to view this page!');
	}
    viscacha_header('Location: log.php?location=admin.php'.SID2URL_JS_x);
}

$db->close();	
?>
