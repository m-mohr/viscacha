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

define('SCRIPTNAME', 'ajax');
define('VISCACHA_CORE', '1');
define('NON_HTML_RESPONSE', 1);

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$my->p = $slog->Permissions();

$action = $gpc->get('action', str);

send_nocache_header();

($code = $plugins->load('ajax_start')) ? eval($code) : null;

if ($action == 'markforumread') {
	$board = $gpc->get('id', int);
	$my->p = $slog->Permissions($board);
	// ToDo: Make this permission check better, more like in showforum.php
	if (!is_id($board) || $my->p['forum'] == 0) {
		sendStatusCode(403);
	}
	$slog->setForumRead($board);
	$slog->updatelogged();
	sendStatusCode(200);
}
elseif ($action == 'marktopicread') {
	$topic = $gpc->get('id', int);
	$result = $db->execute("SELECT board FROM {$db->pre}topics WHERE id = '{$topic}'");
	$board = $result->fetchOne();
	
	$my->p = $slog->Permissions($board);
	// ToDo: Make this permission check better, more like in showtopic.php
	if (!is_id($topic) || $my->p['forum'] == 0) {
		sendStatusCode(403);
	}

	$cat_bid_obj = $scache->load('cat_bid');
	$forums = $cat_bid_obj->get();
	$parentForums = get_headboards($fc, $forums[$board], true);
	$slog->setTopicRead($topic, $parentForums);
	$slog->updatelogged();
	sendStatusCode(200);
}
elseif ($action == 'doubleudata') {
	if (mb_strlen($_GET['name']) > 3) {
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
	if (mb_strlen($_GET['name']) > 2) {
		$result = $db->execute('SELECT name FROM '.$db->pre.'user WHERE deleted_at IS NULL AND name LIKE "%'.$_GET['name'].'%" ORDER BY name ASC LIMIT 50');
		$user = array();
		while ($row = $result->fetch()) {
			$user[] = $row['name'];
		}
		$request = implode(',', viscacha_htmlspecialchars($user));
		echo $request;
	}
	else {
		echo 8;
	}
}
elseif ($action == 'search') {
	$search = $gpc->get('search', str);
	if (mb_strlen($search) > 2) {
		$search = urldecode($search);
		$search = preg_replace("/(\s){1,}/isu"," ",$search);
	    $search = preg_replace("/\*{1,}/isu",'*',$search);
		$ignorewords = $lang->get_words();
		$searchwords = Str::splitWords($search);
		$ignored = array();
		foreach ($searchwords as $sw) {
			$sw = trim($sw);
			if ($sw{0} == '-') {
				$sw2 = mb_substr($sw, 1);
			}
			else {
				$sw2 = $sw;
			}
			$sw2 = str_replace('*','',$sw2);
			if (in_array(mb_strtolower($sw2), $ignorewords) || mb_strlen($sw2) < $config['searchminlength']) {
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

$response->send();