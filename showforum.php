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
$breadcrumb->Add($info['name']);

forum_opt($info);

$prefix_obj = $scache->load('prefix');
$prefix_arr = $prefix_obj->get($board);
uasort($prefix_arr, function($a, $b) {
	return strnatcasecmp($a['value'], $b['value']);
});

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

echo $tpl->parse("header");

($code = $plugins->load('showforum_forums_start')) ? eval($code) : null;

$subforums = BoardSelect($board);

($code = $plugins->load('showforum_forums_end')) ? eval($code) : null;

$inner['index_bit'] = '';
if ($info['topics'] > 0) {
	$start = ($_GET['page'] - 1) * $info['forumzahl'];

	($code = $plugins->load('showforum_query')) ? eval($code) : null;
	$result = $db->query("
	SELECT prefix, vquestion, posts, id, board, topic, date, status, last, last_name, sticky, name
	FROM {$db->pre}topics
	WHERE board = '{$board}' {$marksql}
	ORDER BY sticky DESC, last DESC
	LIMIT {$start}, {$info['forumzahl']}
	");

	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();

	while ($row = $gpc->prepare($db->fetch_object($result))) {
		$pref = '';

		$showprefix = false;
		if (isset($prefix_arr[$row->prefix]) && $row->prefix > 0) {
			$showprefix = true;
			$prefix = $prefix_arr[$row->prefix]['value'];
		}
		else {
			$prefix = '';
		}

		$last = $fc[$row->board];

		if(is_id($row->name) && isset($memberdata[$row->name])) {
			$row->mid = $row->name;
			$row->name = $memberdata[$row->name];
		}
		else {
			$row->mid = FALSE;
		}

		if (is_id($row->last_name) && isset($memberdata[$row->last_name])) {
			$row->last_name = $memberdata[$row->last_name];
		}

		$rstart = str_date($lang->phrase('dformat1'),times($row->date));
		$rlast = str_date($lang->phrase('dformat1'),times($row->last));

		if ($row->status == '2') {
			$pref .= $lang->phrase('forum_moved');
		}
		else if ($row->sticky == '1') {
			$pref .= $lang->phrase('forum_announcement');
		}

		if ($slog->isTopicRead($row->id, $row->last)) {
	 		$firstnew = 0;
			if ($row->status == 1 || $row->status == 2) {
			   	$alt = $lang->phrase('forum_icon_closed');
				$src = $tpl->img('dir_closed');
			}
			else {
			   	$alt = $lang->phrase('forum_icon_old');
			   	$src = $tpl->img('dir_open');
	 		}
	 	}
	  	else {
	  		$firstnew = 1;
			if ($row->status == 1 || $row->status == 2) {
				$alt = $lang->phrase('forum_icon_closed');
				$src = $tpl->img('dir_closed2');
			}
			else {
				$alt = $lang->phrase('forum_icon_new');
				$src = $tpl->img('dir_open2');
			}
		}

		if ($last['topiczahl'] < 1) {
			$last['topiczahl'] = $config['topiczahl'];
		}

		if ($row->posts > $last['topiczahl']) {
			$topic_pages = pages($row->posts+1, $last['topiczahl'], "showtopic.php?id=".$row->id."&amp;", 0, '_small', false);
		}
		else {
			$topic_pages = '';
		}

		($code = $plugins->load('showforum_entry_prepared')) ? eval($code) : null;

		$inner['index_bit'] .= $tpl->parse("showforum/index_bit");
	}
}
else {
	($code = $plugins->load('showforum_empty')) ? eval($code) : null;
	$inner['index_bit'] .= $tpl->parse("showforum/index_bit_empty");
}

($code = $plugins->load('showforum_prepared')) ? eval($code) : null;
echo $tpl->parse("showforum/index");
($code = $plugins->load('showforum_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();
?>