<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2009  The Viscacha Project

	Author: Matthias Mohr (et al.)
	Publisher: The Viscacha Project, http://www.viscacha.org
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

define('SCRIPTNAME', 'admin');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("admin/data/config.inc.php");

if (empty($config['cryptkey']) || empty($config['database']) || empty($config['dbsystem'])) {
	trigger_error('Viscacha is currently not installed. How to install Viscacha is described in the file "_docs/readme.txt"!', E_USER_ERROR);
}
if ((empty($config['dbpw']) || empty($config['dbuser'])) && $config['local_mode'] == 0) {
	trigger_error('You have specified database authentification data that is not safe. Please change your database user and the database password!', E_USER_ERROR);
}

include ("admin/lib/function.viscacha_backend.php");

($code = $plugins->load('admin_start')) ? eval($code) : null;

if ($my->p['admin'] == 1) {

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
	elseif ($action == 'packages') {
		include('admin/packages.php');
	}
	elseif ($action == 'admin') {
		include('admin/packages_admin.php');
	}
	elseif ($action == 'profilefield') {
		include('admin/profilefield.php');
	}
	elseif ($action == 'posts') {
		include('admin/posts.php');
	}
	elseif ($action == 'locate') {
		$url = $gpc->get('url', none);
		$url = addslashes($url);
		if (!empty($url)) {
			$db->close();
			sendStatusCode(307, $url);
			exit;
		}
		else {
			echo head();
			if (!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'action=locate') === false) {
				$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
			}
			else {
				$url = 'javascript:history.back(-1);';
			}
			error($url, $lang->phrase('admin_choose_valid_location_option'));
		}
	}
	else {
		if (strlen($action) == 0) {
			include('admin/frames.php');
		}
		else {
			$error = true;
			($code = $plugins->load('admin_include')) ? eval($code) : null;
			if ($error == true) {
				echo head();
				error('admin.php?action=index'.SID2URL_x, $lang->phrase('admin_requested_page_doesnot_exist'));
			}
		}
	}
}
elseif ($action == 'logout' && $my->vlogin) {
	$slog->sid_logout();
	echo head();
	ok('admin.php', $lang->phrase('admin_successfully_logged_off'));
}
else {
	($code = $plugins->load('admin_notallowed')) ? eval($code) : null;
	if ($my->p['admin'] == 0 && $my->vlogin) {
		echo head();
		error('index.php'.SID2URL_1, $lang->phrase('admin_not_allowed_to_view_this_page'));
	}

	$addr = rawurldecode($gpc->get('addr', none));
	if ($action == "login2") {
		$log_status = $slog->sid_login(true);
		if ($log_status == false) {
			$attempts = set_failed_login();
			if ($attempts == $config['login_attempts_max']) {
				header('Location: index.php'.SID2URL_1);
			}
			else {
				echo head();
				error('admin.php'.iif(!empty($addr), '?addr='.rawurlencode($addr)), $lang->phrase('admin_incorrect_username_or_password_entered'));
			}
		}
		else {
			clear_login_attempts();
			echo head();
			ok('admin.php'.iif(!empty($addr), '?addr='.rawurlencode($addr)), $lang->phrase('admin_successfully_logged_in'));
		}
	}
	else {
		echo head();
		AdminLogInForm();
	}
	echo foot();
}

($code = $plugins->load('admin_end')) ? eval($code) : null;

$slog->updatelogged();
$db->close();
?>