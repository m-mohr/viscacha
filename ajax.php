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

define('SCRIPTNAME', 'ajax');
define('VISCACHA_CORE', '1');
define('TEMPSHOWLOG', 1);

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$my->p = $slog->Permissions();

$action = $gpc->get('action', str);

viscacha_header("Content-type: text/plain");
send_nocache_header();

($code = $plugins->load('ajax_start')) ? eval($code) : null;

// Schliesst oder oeffnet einen Beitrag mittels AJAX
if ($action == 'openclosethread') {
    $result = $db->query("SELECT status, board FROM {$db->pre}topics WHERE id = '{$_GET['id']}'");
    $row = $db->fetch_assoc($result);
    $my->p = $slog->Permissions($row['board']);
    $my->mp = $slog->ModPermissions($row['board']);

    $request = 1;

    if ($my->p['admin'] == 1 || $my->p['gmod'] == 1 || $my->mp[0] == 1) {
	    if ($row['status'] == 0) {
	    	$db->query("UPDATE {$db->pre}topics SET status = '1' WHERE id = '{$_GET['id']}'");
			if ($db->affected_rows() == 1) {
	        	$request = 3;
	    	}
	    }
	    else {
	    	$db->query("UPDATE {$db->pre}topics SET status = '0' WHERE id = '{$_GET['id']}'");
			if ($db->affected_rows() == 1) {
	        	$request = 4;
	    	}
	    }
	}
	else {
		$request = 2;
	}

	echo $request;
}
elseif ($action == 'markforumread') {
	$board = $gpc->get('id', int);
	$my->p = $slog->Permissions($board);
	if (!is_id($board) || $my->p['forum'] == 0) {
		echo '0';
	}
	$slog->setForumRead($board);
	$slog->updatelogged();
	echo '1';
}
elseif ($action == 'doubleudata') {
	if (strlen($_GET['name']) > 3) {
		$request = 1;
		if (!$my->vlogin) {
			if (double_udata('name',$_GET['name']) == false) {
				$request = 5;
			}
			else {
				$request = 6;
			}
		}
		echo $request;
	}
	else {
		echo 8;
	}
}
elseif ($action == 'searchmember') {
	$request = 1;
	if (strlen($_GET['name']) > 2) {
		$result = $db->query('SELECT name FROM '.$db->pre.'user WHERE name LIKE "%'.$_GET['name'].'%" ORDER BY name ASC LIMIT 50');
		$user = array();
		while ($row = $db->fetch_assoc($result)) {
			$user[] = $row['name'];
		}
		$request = implode(',', $gpc->prepare($user));
		echo $request;
	}
	else {
		echo 8;
	}
}
elseif ($action == 'search') {
	$search = $gpc->get('search', str);
	if (strlen($search) > 2) {
		$search = urldecode($search);
		$search = preg_replace("/(\s){1,}/is"," ",$search);
	    $search = preg_replace("/\*{1,}/is",'*',$search);
		$ignorewords = $lang->get_words();
		$searchwords = splitWords($search);
		$ignored = array();
		foreach ($searchwords as $sw) {
			$sw = trim($sw);
			if ($sw[0] == '-') {
				$sw2 = substr($sw, 1);
			}
			else {
				$sw2 = $sw;
			}
			$sw2 = str_replace('*','',$sw2);
			if (in_array(strtolower($sw2), $ignorewords) || strxlen($sw2) < $config['searchminlength']) {
				$ignored[] = $sw2;
			}
		}
		if (count($ignored) > 0) {
			echo implode(',', $ignored);
		}
		else {
			echo 1;
		}
	}
	else {
		echo 1;
	}
}

($code = $plugins->load('ajax_end')) ? eval($code) : null;

$phpdoc->Out(0);
$db->close();
?>