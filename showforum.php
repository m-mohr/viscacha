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

define('SCRIPTNAME', 'showforum');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$board = $gpc->get('id', int);

$my->p = $slog->Permissions($board);
$my->pb = $slog->GlobalPermissions();
$my->mp = $slog->ModPermissions($board);

$catbid = $scache->load('cat_bid');
$fc = $catbid->get();
if (empty($board) || !isset($fc[$board])) {
	error($lang->phrase('query_string_error'));
}
$info = $fc[$board];

if ($my->p['admin'] == 1 || $my->p['gmod'] == 1 || $my->mp[0] == 1) {
	$modcp = true;
}
else {
	$modcp = false;
}

$topforums = get_headboards($fc, $info);
Breadcrumb::universal()->add($info['name']);

forum_opt($info);

$prefix_obj = $scache->load('prefix');
$prefix_arr = $prefix_obj->get($board);
array_columnsort($prefix_arr, "value");

$filter = $gpc->get('sort', int);
if ($filter == 6) {
	$marksql = ' AND posts = 0 ';
}
elseif ($filter == 0) {
	$marksql = '';
}
else {
	$marksql = '';
}

$prefix_filter = $gpc->get('prefix', int, -1);
if ($prefix_filter >= 0) {
	$marksql .= " AND prefix = '{$prefix_filter}' ";
}

($code = $plugins->load('showforum_filer_query')) ? eval($code) : null;

if (!empty($marksql)) {
	$result = $db->query("SELECT COUNT(*) FROM {$db->pre}topics WHERE board = '{$board}' {$marksql}");
	$vlasttopics = $db->fetch_num($result);
	$info['topics'] = $vlasttopics[0];
}

if ($info['forumzahl'] < 1) {
	$info['forumzahl'] = $config['forumzahl'];
}
if (ceil($info['topics']/$info['forumzahl']) < $_GET['page']) {
	$_GET['page'] = 1;
}
$pages = pages($info['topics'], $info['forumzahl'], 'showforum.php?id='.$board.'&amp;sort='.$_GET['sort'].'&amp;', $_GET['page']);

($code = $plugins->load('showforum_forums_start')) ? eval($code) : null;

$subforums = BoardSelect($board);

($code = $plugins->load('showforum_forums_end')) ? eval($code) : null;

$topics = array();
if ($info['topics'] > 0) {
	$start = ($_GET['page'] - 1) * $info['forumzahl'];

	($code = $plugins->load('showforum_query')) ? eval($code) : null;
	$result = $db->query("
	SELECT t.prefix, t.vquestion, t.posts, t.id, t.board, t.topic, t.date, t.status, t.last, t.sticky,
		u.name, u.id AS uid, l.id AS luid, l.name AS luname
	FROM {$db->pre}topics AS t
		LEFT JOIN {$db->pre}user AS u ON u.id = t.name
		LEFT JOIN {$db->pre}user AS l ON l.id = t.last_name
	WHERE t.board = '{$board}' {$marksql}
	ORDER BY t.sticky DESC, t.last DESC
	LIMIT {$start}, {$info['forumzahl']}
	");

	while ($row = $db->fetch_object($result)) {
		if (isset($prefix_arr[$row->prefix]) && $row->prefix > 0) {
			$row->prefix = $prefix_arr[$row->prefix]['value'];
		}
		else {
			$row->prefix = '';
		}

		$row->type = '';
		if ($row->status == 2) {
			$row->type .= $lang->phrase('forum_moved');
		}
		else if ($row->sticky == '1') {
			$row->type .= $lang->phrase('forum_announcement');
		}

		$row->read = $slog->isTopicRead($row->id, $row->last);

		$row->pages = '';
		$last = $fc[$row->board];
		if ($last['topiczahl'] < 1) {
			$last['topiczahl'] = $config['topiczahl'];
		}
		$row->pages = pages($row->posts+1, $last['topiczahl'], "showtopic.php?id=".$row->id."&amp;", 0, '_small', false);

		($code = $plugins->load('showforum_entry_prepared')) ? eval($code) : null;

		$topics[] = $row;
	}
}

($code = $plugins->load('showforum_prepared')) ? eval($code) : null;
echo $tpl->parse("showforum", compact("info", "pages", "topics", "subforums", "filter", "prefix_filter", "board"));
($code = $plugins->load('showforum_end')) ? eval($code) : null;

$slog->updatelogged();
$phpdoc->Out();