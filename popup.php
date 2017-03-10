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

define('SCRIPTNAME', 'popup');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

($code = $plugins->load('popup_start')) ? eval($code) : null;

if ($_GET['action'] == "edithistory") {
	echo $tpl->parse("popup/header");

	($code = $plugins->load('popup_edithistory_query')) ? eval($code) : null;
	$result = $db->query("
	SELECT r.ip, r.topic_id, t.board, r.edit, r.id, r.topic, r.date, u.name as uname, r.name as gname, u.id as mid, u.groups, r.email as gmail, r.guest
	FROM {$db->pre}replies AS r
		LEFT JOIN {$db->pre}topics AS t ON t.id = r.topic_id
		LEFT JOIN {$db->pre}user AS u ON r.name = u.id AND r.guest = '0'
	WHERE r.id = '{$_GET['id']}'
	LIMIT 1
	");

	$found = $db->num_rows($result);
	if ($found == 1) {
		$row = $gpc->prepare($db->fetch_assoc($result));
		$my->p = $slog->Permissions($row['board']);
	}
	$error = array();
	if ($found == 0) {
		$error[] = $lang->phrase('query_string_error');
	}
	if ($found == 1 && $my->p['forum'] == 0) {
		$error[] = $lang->phrase('not_allowed');
	}
	if (count($error) > 0) {
		errorLogin($error,'javascript:self.close();');
	}

	$catbid = $scache->load('cat_bid');
	$fc = $catbid->get();
	$last = $fc[$row['board']];

	forum_opt($last);

	($code = $plugins->load('popup_edithistory_start')) ? eval($code) : null;

	if ($row['guest'] == 0) {
		$row['mail'] = '';
		$row['name'] = $row['uname'];
	}
	else {
		$row['mail'] = $row['gmail'];
		$row['name'] = $row['gname'];
		$row['mid'] = 0;
		$row['groups'] = GROUP_GUEST;
	}

	$row['date'] = str_date($lang->phrase('dformat1'), times($row['date']));

	$edit = array();
	if (!empty($row['edit'])) {
		preg_match_all('~^([^\t]+)\t(\d+)\t([^\t]*)\t([\d\.]+)$~m', $row['edit'], $edits, PREG_SET_ORDER);
		foreach ($edits as $e) {
			$edit[] = array(
				'date' => str_date($lang->phrase('dformat1'), times($e[2])),
				'reason' => empty($e[3]) ? $lang->phrase('post_editinfo_na') : $e[3],
				'name' => $e[1],
				'ip' => empty($e[4]) ? '-' : $e[4]
			);
			($code = $plugins->load('popup_edithistory_entry_prepared')) ? eval($code) : null;
		}
	}
	($code = $plugins->load('popup_edithistory_prepared')) ? eval($code) : null;
	echo $tpl->parse("popup/edithistory");
	($code = $plugins->load('popup_edithistory_end')) ? eval($code) : null;
}

($code = $plugins->load('popup_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("popup/footer");
$phpdoc->Out();
$db->close();
?>