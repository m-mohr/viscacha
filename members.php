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

DEFINE('SCRIPTNAME', 'members');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();
$my->p = $slog->Permissions();

if ($my->p['members'] == 0) {
	error(array($lang->phrase('not_allowed')));
}

$breadcrumb->Add($lang->phrase('members'));
echo $tpl->parse("header");
echo $tpl->parse("menu");

($code = $plugins->load('members_start')) ? eval($code) : null;

$fields = explode(',', $config['mlist_fields']);

$im = false;
$colspan = 1+count($fields);
if (in_array('fullname', $fields)) 	$colspan--;
if (in_array('icq', $fields)) 		$colspan--; $im = true;
if (in_array('aol', $fields)) 		$colspan--; $im = true;
if (in_array('yahoo', $fields))		$colspan--; $im = true;
if (in_array('msn', $fields)) 		$colspan--; $im = true;
if (in_array('jabber', $fields))	$colspan--; $im = true;
if (in_array('skype', $fields)) 	$colspan--; $im = true;
if ($im == true)					$colspan++;

$_GET['order'] = strtolower($_GET['order']);
if ($_GET['order'] != 'desc') {
	$_GET['order'] = 'asc';
}
$_GET['sort'] = strtolower($_GET['sort']);
if ($_GET['sort'] != 'posts' && $_GET['sort'] != 'regdate' && $_GET['sort'] != 'location' && $_GET['sort'] != 'gender' && $_GET['sort'] != 'birthday' && $_GET['sort'] != 'lastvisit') {
	$sqlorderby = "name {$_GET['order']}";
}
else {
	$sqlorderby = "{$_GET['sort']} {$_GET['order']}, name {$_GET['order']}";
}

$sqlwhere = array();
if (strlen($_GET['letter']) == 1) {
	$sqlwhere[] = "LEFT(name, 1) = '{$_GET['letter']}'";
}
if ($config['mlist_showinactive'] == 0) {
	$sqlwhere[] = "confirm = '11'";
}
$groups = array();
$g = $gpc->get('g', arr_int);
if ($config['mlist_filtergroups'] > 0) {
	$group_status = $scache->load('group_status');;
	$statusdata = $group_status->get();
	foreach ($statusdata as $row) {
		if ($row['guest'] != 1) {
			$groups[$row['id']] = $row['title'];
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
						'letter' => $_GET['letter'],
						'id' => $_GET['id'],
						'sort' => $_GET['sort'],
						'order' => $_GET['order']
					)
				);
$query_letter =	http_build_query(
					array(
						'id' => $_GET['id'],
						'page' => $_GET['page'],
						'sort' => $_GET['sort'],
						'order' => $_GET['order']
					)
				);
$query_th =		http_build_query(
					array(
						'letter' => $_GET['letter'],
						'id' => $_GET['id'],
						'page' => $_GET['page']
					)
				);

($code = $plugins->load('members_queries')) ? eval($code) : null;

$result = $db->query("SELECT COUNT(*) FROM {$db->pre}user WHERE {$sqlwhere}",__LINE__,__FILE__);
$count = $db->fetch_num($result);

$temp = pages($count[0], $config['mlistenzahl'], "members.php?{$query_page}&amp;", $_GET['page']);
$start = $_GET['page']*$config['mlistenzahl'];
$start = $start-$config['mlistenzahl'];

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
$sqlselect = implode(',', $fields);

$result = $db->query("
SELECT {$sqlselect} 
FROM {$db->pre}user 
WHERE {$sqlwhere} 
ORDER BY {$sqlorderby} 
LIMIT {$start},{$config['mlistenzahl']}
",__LINE__,__FILE__);

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
		if ($row['birthday'] != null && $row['birthday'] != '0000-00-00') {
			$row['birthday'] = iif($bday[0] > 0, $lang->phrase('members_bday_full'), $lang->phrase('members_bday_short'));
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
	($code = $plugins->load('members_prepare_bit')) ? eval($code) : null;
	$inner['index_bit'] .= $tpl->parse("members/index_bit");
}

($code = $plugins->load('members_prepared')) ? eval($code) : null;

$letter = $lang->phrase('members_all');
$row = array('letter' => '');
$inner['index_letter'] = $tpl->parse("members/index_letter");
$result = $db->query("SELECT DISTINCT UPPER(LEFT(name,1)) AS letter FROM {$db->pre}user ORDER BY letter",__LINE__,__FILE__); 
while ($row = mysql_fetch_assoc($result)) {
	$letter = &$row['letter'];
	$inner['index_letter'] .= $tpl->parse("members/index_letter");
}

echo $tpl->parse("members/index");

($code = $plugins->load('members_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();	
?>
