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

define('SCRIPTNAME', 'members');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$my->p = $slog->Permissions();

if ($_GET['action'] == 'team' && $my->p['team'] == 0) {
	errorLogin();
}
elseif ($_GET['action'] != 'team' && $my->p['members'] == 0) {
	errorLogin();
}

if ($_GET['action'] == 'team') {
	$breadcrumb->Add($lang->phrase('members'), 'members.php'.SID2URL_1);
	$breadcrumb->Add($lang->phrase('team'));
}
else {
	$breadcrumb->Add($lang->phrase('members'));
}

if ($_GET['action'] == 'team') {

	($code = $plugins->load('team_top')) ? eval($code) : null;

	$groups = $scache->load('groups');
	$team = $groups->team();

	$cache = array();
	$t = array_merge($team['admin'], $team['gmod']);
	foreach ($t as $row) {
		$cache[] = "FIND_IN_SET({$row},groups)";
	}

	$result = $db->query('
	SELECT id, name, mail, hp, location, fullname, groups
	FROM '.$db->pre.'user
	WHERE '.implode(' OR ',$cache).'
	ORDER BY name ASC
	');

	$admin_cache = array();
	$gmod_cache = array();

	while($row = $gpc->prepare($db->fetch_object($result))) {
		$gids = explode(',',$row->groups);
		foreach ($gids as $gid) {
			if (in_array($gid, $team['admin'])) {
				$admin_cache[] = $row;
			}
			elseif (in_array($gid, $team['gmod'])) {
				$gmod_cache[] = $row;
			}
		}
	}

	$result = $db->query('
	SELECT m.time, m.mid, u.name as member, m.bid, f.name as board, u.mail, u.hp, u.location, u.fullname
	FROM '.$db->pre.'moderators AS m
		LEFT JOIN '.$db->pre.'user AS u ON u.id = m.mid
		LEFT JOIN '.$db->pre.'forums AS f ON f.id = m.bid
	ORDER BY u.name ASC
	');

	$inner['moderator_bit'] = '';
	if ($db->num_rows($result) > 0) {
		$mod_cache = array();
		$mid_cache = array();
		while($row = $gpc->prepare($db->fetch_object($result))) {
			$mod_cache[$row->mid][] = $row;
			$mid_cache[] = $row->mid;
		}

		if(isset($mid_cache)) {
			$mid_cache = array_unique($mid_cache);
			$lastmod = '';
			$echoline = '';
			foreach ($mid_cache as $mid) {
				$inner['moderator_bit_forum'] = array();
				if(isset($mod_cache[$mid])) {
					$mod  = $mod_cache[$mid];
					$anz2 = count($mod);
					$forschleife = $anz2-1;
					for($i = 0; $i < $anz2; $i++) {
						if ($config['team_mod_dateuntil'] == 1 && !empty($mod[$i]->time)) {
							$mod[$i]->time = gmdate($lang->phrase('dformat2'),times($mod[$i]->time));
						}
						else {
							$mod[$i]->time = 0;
						}
						$inner['moderator_bit_forum'][] = $mod[$i];
						if ($i != $forschleife) {
							continue;
						}
						($code = $plugins->load('team_moderator_prepared')) ? eval($code) : null;
						$inner['moderator_bit'] .= $tpl->parse("team/moderator_bit");
					}
				}
			}
		}
	}

	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	($code = $plugins->load('team_prepared')) ? eval($code) : null;
	echo $tpl->parse("team/index");
	($code = $plugins->load('team_end')) ? eval($code) : null;

}
else {

	$available = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9');

	($code = $plugins->load('members_start')) ? eval($code) : null;

	$fields = explode(',', $config['mlist_fields']);

	$im = false;
	$colspan = 1+count($fields);
	if (in_array('fullname', $fields)) {$colspan--; }
	if (in_array('icq', $fields)) {		$colspan--; $im = true; }
	if (in_array('aol', $fields)) {		$colspan--; $im = true; }
	if (in_array('yahoo', $fields))	{	$colspan--; $im = true; }
	if (in_array('msn', $fields)) {		$colspan--; $im = true; }
	if (in_array('jabber', $fields)) {	$colspan--; $im = true; }
	if (in_array('skype', $fields)) {	$colspan--; $im = true; }
	if ($im == true) {					$colspan++; }

	$_GET['order'] = strtolower($_GET['order']);
	if ($_GET['order'] != 'desc') {
		$_GET['order'] = 'asc';
	}
	$_GET['sort'] = strtolower($_GET['sort']);
	if ($_GET['sort'] != 'hp' && $_GET['sort'] != 'online' && $_GET['sort'] != 'posts' && $_GET['sort'] != 'regdate' && $_GET['sort'] != 'location' && $_GET['sort'] != 'gender' && $_GET['sort'] != 'birthday' && $_GET['sort'] != 'lastvisit') {
		$sqlorderby = "name {$_GET['order']}";
	}
	else {
		$sqlorderby = "{$_GET['sort']} {$_GET['order']}, name {$_GET['order']}";
	}

	$sqlwhere = array();
	$_GET['letter'] = $gpc->get('letter', db_esc);
	if (strxlen($_GET['letter']) == 1) {
		if ($_GET['letter'] == '#') {
			$sqlwhere[] = "LEFT(name, 1) REGEXP '^[^".implode('', $available)."]'";
		}
		else {
			$sqlwhere[] = "LEFT(name, 1) = '{$_GET['letter']}'";
		}
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
	if (count($sqlwhere) == 0) {
		$sqlwhere[] = '1=1';
	}
	$sqlwhere = implode(' AND ', $sqlwhere);

	$query_page = 	http_build_query(
						array(
							'letter' => rawurlencode($_GET['letter']),
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
							'letter' => rawurlencode($_GET['letter']),
							'id' => $_GET['id'],
							'page' => $_GET['page']
						)
					);

	($code = $plugins->load('members_queries')) ? eval($code) : null;

	$result = $db->query("SELECT COUNT(*) FROM {$db->pre}user WHERE {$sqlwhere}");
	$count = $db->fetch_num($result);

	$temp = pages($count[0], $config['mlistenzahl'], "members.php?{$query_page}&amp;", $_GET['page']);
	$start = $_GET['page']*$config['mlistenzahl'];
	$start = $start-$config['mlistenzahl'];

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

	$result = $db->query("
	SELECT {$sqlselect}
	FROM {$db->pre}user AS u
	{$sqljoin}
	WHERE {$sqlwhere}
	ORDER BY {$sqlorderby}
	LIMIT {$start},{$config['mlistenzahl']}
	");

	if ($count[0] > 0 && $db->num_rows($result) == 0) {
		error($lang->phrase('query_string_error'), 'members.php'.SID2URL_1);
	}

	$inner['index_bit'] = '';
	while ($row = $gpc->prepare($db->fetch_assoc($result))) {
		if (isset($row['regdate'])) {
			$row['regdate'] = gmdate($lang->phrase('dformat2'), times($row['regdate']));
		}
		if (isset($row['location'])) {
			$row['location'] = iif(!empty($row['location']), $row['location'], $lang->phrase('location_no_data'));
		}
		if (isset($row['gender'])) {
			if ($row['gender'] == 'm' || $row['gender'] == 'w') {
				$row['gender'] = $lang->phrase('gender_'.$row['gender']);
			}
			else {
				$row['gender'] = $lang->phrase('gender_na');
			}
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
		if(isset($row['lastvisit'])) {
			$row['lastvisit'] = iif ($row['lastvisit'] > 0, gmdate($lang->phrase('dformat1'), times($row['lastvisit'])), $lang->phrase('members_na'));
		}
		if (isset($row['online'])) {
			$row['lang_online'] = $lang->phrase('profile_'.iif($row['online'] == 1, 'online', 'offline'));
		}
		($code = $plugins->load('members_prepare_bit')) ? eval($code) : null;
		$inner['index_bit'] .= $tpl->parse("members/index_bit");
	}

	$letter = array(
		'' => array('url' => '', 'html' => $lang->phrase('members_all'))
	);
	$specials = false;
	$result = $db->query("SELECT DISTINCT UPPER(LEFT(name,1)) AS letter FROM {$db->pre}user ORDER BY letter");
	while ($row = $db->fetch_assoc($result)) {
		if (in_array($row['letter'], $available)) {
			$letter[$row['letter']] = array('url' => rawurlencode($row['letter']), 'html' => $row['letter']);
		}
		elseif ($specials == false) {
			$letter['#'] = array('url' => rawurlencode('#'), 'html' => '#');
		}
	}
	ksort($letter);

	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	($code = $plugins->load('members_prepared')) ? eval($code) : null;
	echo $tpl->parse("members/index");
	($code = $plugins->load('members_end')) ? eval($code) : null;

}

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();
?>