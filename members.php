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

define('SCRIPTNAME', 'members');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$my->p = $slog->Permissions();

if ($_GET['action'] == 'team') {
	Breadcrumb::universal()->add($lang->phrase('members'), 'members.php'.SID2URL_1);
	Breadcrumb::universal()->add($lang->phrase('team'));

	if ($my->p['team'] == 0) {
		errorLogin();
	}

	($code = $plugins->load('team_top')) ? eval($code) : null;
	
	$my->pb = $slog->GlobalPermissions();

	$groups = $scache->load('groups');
	$team = $groups->team();

	$sqlConditions = array();
	$t = array_merge($team['admin'], $team['gmod']);
	foreach ($t as $row) {
		$sqlConditions[] = "FIND_IN_SET({$row},groups)";
	}

	$admins = array();
	$gmods = array();
	$mods = array();

	$result = $db->execute('
		SELECT id, name, mail, hp, location, fullname, groups
		FROM '.$db->pre.'user
		WHERE deleted_at IS NULL AND '.implode(' OR ', $sqlConditions).'
		ORDER BY name ASC
	');
	while($row = $result->fetchObject()) {
		$gids = explode(',', $row->groups);
		foreach ($gids as $gid) {
			if (in_array($gid, $team['admin'])) {
				$admins[] = $row;
			}
			elseif (in_array($gid, $team['gmod'])) {
				$gmods[] = $row;
			}
		}
	}

	$result = $db->execute('
		SELECT u.id, u.name, u.mail, u.hp, u.location, u.fullname, f.id AS forum_id, f.name as forum_name, f.invisible AS forum_invisible
		FROM '.$db->pre.'moderators AS m
			INNER JOIN '.$db->pre.'user AS u ON u.id = m.mid
			LEFT JOIN '.$db->pre.'forums AS f ON f.id = m.bid
		WHERE f.invisible != "2"
		ORDER BY u.name ASC
	');

	while($row = $result->fetchObject()) {
		if ($row->forum_invisible == 1 && empty($my->pb[$row->forum_id]['forum'])) {
			continue;
		}
		
		if (!isset($mods[$row->id])) {
			$row->forums = array();
			$mods[$row->id] = $row;
		}
		
		$mods[$row->id]->forums[$row->forum_id] = $row->forum_name;

		($code = $plugins->load('team_moderator_prepared')) ? eval($code) : null;
	}

	($code = $plugins->load('team_prepared')) ? eval($code) : null;
	echo $tpl->parse("members/team", compact('admins', 'gmods', 'mods'));
	($code = $plugins->load('team_end')) ? eval($code) : null;
}
else {
	Breadcrumb::universal()->add($lang->phrase('members'));

	if ($my->p['members'] == 0) {
		errorLogin();
	}

	($code = $plugins->load('members_start')) ? eval($code) : null;

	$fields = explode(',', $config['mlist_fields']);

	$colspan = 1+count($fields);
	if (in_array('fullname', $fields)) {$colspan--; }

	$_GET['order'] = mb_strtolower($_GET['order']);
	if ($_GET['order'] != 'desc') {
		$_GET['order'] = 'asc';
	}
	$_GET['sort'] = mb_strtolower($_GET['sort']);
	if ($_GET['sort'] != 'hp' && $_GET['sort'] != 'online' && $_GET['sort'] != 'posts' && $_GET['sort'] != 'regdate' && $_GET['sort'] != 'location' && $_GET['sort'] != 'gender' && $_GET['sort'] != 'birthday' && $_GET['sort'] != 'lastvisit') {
		$sqlorderby = "name {$_GET['order']}";
	}
	else {
		$sqlorderby = "{$_GET['sort']} {$_GET['order']}, name {$_GET['order']}";
	}

	$sqlwhere = array('deleted_at IS NULL');
	$letter = $gpc->get('letter', db_esc);
	if (mb_strlen($letter) == 1) {
		$sqlwhere[] = "LEFT(name, 1) = '{$letter}'";
	}
	if ($config['mlist_showinactive'] == 0) {
		$sqlwhere[] = "confirm = '11'";
	}
	$groups = array();
	$g = $gpc->get('g', arr_int);
	if ($config['mlist_filtergroups'] > 0) {
		$group_cache = $scache->load('groups');
		$statusdata = $group_cache->status();
		foreach ($statusdata as $id => $row) {
			if ($row['guest'] != 1) {
				$groups[$id] = $row['title'];
			}
		}
		$sqlwhere_findinset = array();
		foreach ($g as $key => $value) {
			if (isset($statusdata[$value])) {
				$sqlwhere_findinset[] = "FIND_IN_SET({$value}, groups)";
			}
			else {
				unset($g[$key]);
			}
		}
		if (count($sqlwhere_findinset) == 1) {
			$sqlwhere[] = current($sqlwhere_findinset);
		}
		elseif (count($sqlwhere_findinset) > 1) {
			$sqlwhere[] = '('.implode(' OR ', $sqlwhere_findinset).')';
		}
	}
	$sqlwhere = implode(' AND ', $sqlwhere);

	$query_page = 	http_build_query(
						array(
							'letter' => rawurlencode($letter),
							'id' => $_GET['id'],
							'sort' => $_GET['sort'],
							'order' => $_GET['order']
						)
					);
	$query_letter =	http_build_query(
						array(
							'id' => $_GET['id'],
							'page' => 1,
							'sort' => $_GET['sort'],
							'order' => $_GET['order']
						)
					);
	$query_th =		http_build_query(
						array(
							'letter' => rawurlencode($letter),
							'id' => $_GET['id'],
							'page' => $_GET['page']
						)
					);

	($code = $plugins->load('members_queries')) ? eval($code) : null;

	$result = $db->execute("SELECT COUNT(*) FROM {$db->pre}user WHERE {$sqlwhere}");
	$count = $result->fetchOne();

	$temp = pages($count, $config['mlistenzahl'], "members.php?{$query_page}&amp;", $_GET['page']);
	$start = ($_GET['page'] - 1) * $config['mlistenzahl'];

	$sqljoin = '';
	$online_key = array_search('online', $fields);
	if ($online_key !== false) {
		$sqljoin = "LEFT JOIN {$db->pre}session AS s ON s.mid = u.id";
		unset($fields[$online_key]);
		$online = true;
	}
	else {
		$online = false;
	}

	$fields[] = 'name';
	$fields[] = 'id';
	$key = array_search('pm', $fields);
	if ($key !== false) {
		unset($fields[$key]);
		$pm = true;
	}
	else {
		$pm = false;
	}
	$sqlselect = $fields;
	$key = array_search('lastvisit', $sqlselect);
	if ($key !== false) {
		$sqlselect[$key] = 'u.lastvisit';
	}
	$sqlselect = implode(',', $sqlselect);
	if ($online_key !== false && $online_key !== null) {
		$sqlselect .= ", IF (s.mid > 0, 1, 0) AS online";
	}

	$result = $db->execute("
	SELECT {$sqlselect}
	FROM {$db->pre}user AS u
	{$sqljoin}
	WHERE {$sqlwhere}
	ORDER BY {$sqlorderby}
	LIMIT {$start},{$config['mlistenzahl']}
	");

	$members = array();
	while ($row = $result->fetch()) {
		if (isset($row['location'])) {
			$row['location'] = iif(!empty($row['location']), $row['location'], $lang->phrase('location_no_data'));
		}
		if (isset($row['posts'])) {
			$row['posts'] = numbers($row['posts']);
		}
		if (isset($row['birthday'])) {
			$bday = explode('-', $row['birthday']);
			if ($row['birthday'] != '1000-00-00' && $row['birthday'] != '0000-00-00') {
				$row['birthday'] = iif($bday[0] > 1000, $lang->phrase('members_bday_full'), $lang->phrase('members_bday_short'));
			}
			else {
				$row['birthday'] = $lang->phrase('members_na');
			}
		}
		if(!empty($row['pic']) && !file_exists($row['pic'])) {
			$row['pic'] = '';
		}
		($code = $plugins->load('members_prepare_bit')) ? eval($code) : null;
		$members[] = $row;
	}

	$letters = array(
		'' => array('url' => '', 'html' => $lang->phrase('members_all')),
	);
	$result = $db->execute("SELECT DISTINCT UPPER(LEFT(name,1)) AS letter FROM {$db->pre}user WHERE deleted_at IS NULL ORDER BY letter");
	while ($row = $result->fetch()) {
		$letters[$row['letter']] = array('url' => rawurlencode($row['letter']), 'html' => $row['letter']);
	}
	ksort($letters);

	($code = $plugins->load('members_prepared')) ? eval($code) : null;
	echo $tpl->parse("members/index");
	($code = $plugins->load('members_end')) ? eval($code) : null;
}

$slog->updatelogged();
$phpdoc->Out();