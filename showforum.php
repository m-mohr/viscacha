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

DEFINE('SCRIPTNAME', 'showforum');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$board = $gpc->get('id', int);

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();
$my->p = $slog->Permissions($board);
$my->pb = $slog->GlobalPermissions();
$my->mp = $slog->ModPermissions($board);

$fc = cache_cat_bid();
if (empty($board) || !isset($fc[$board])) {
	error($lang->phrase('query_string_error'));
}
$info = $fc[$board];

if ($my->p['admin'] == 1 || $my->p['gmod'] == 1 || $my->mp[0] == 1) {
	$modcp = TRUE;
}
else {
	$modcp = FALSE;
}

$topforums = get_headboards($fc, $info);
$breadcrumb->Add($info['name']);

forum_opt($info['opt'], $info['optvalue'], $info['id']);

echo $tpl->parse("header");
echo $tpl->parse("menu");

$mymodules->load('showforum_top');

$subforums = BoardSelect($board);

$mymodules->load('showforum_middle');

$filter = $gpc->get('sort', int);
if ($filter == 2) {
	$marksql = ' AND mark = "a" ';
}
elseif ($filter == 3) {
	$marksql = ' AND mark = "n" ';
}
elseif ($filter == 4) {
	$marksql = ' AND mark = "g" ';
}
elseif ($filter == 5) {
	$marksql = ' AND (mark = "g" OR mark = "n" OR mark = "a") ';
}
elseif ($filter == 1) {
	$marksql = ' AND mark != "b" ';
}
elseif ($filter == 6) {
	$marksql = ' AND posts = 0 ';
}
elseif ($filter == 0) {
	$marksql = '';
}
else {
	if ($my->opt_hidebad == 1) {
		$marksql = ' AND mark != "b" ';
	}
	else {
		$marksql = '';
	}
}

if (!empty($marksql)) {
	$result = $db->query("SELECT COUNT(*) FROM {$db->pre}topics WHERE board = '{$board}' ".$marksql,__LINE__,__FILE__);
	$vlasttopics = $db->fetch_array($result);
	$info['topics'] = $vlasttopics[0];
}

$pages = pages($info['topics'], 'forumzahl', 'showforum.php?id='.$board.'sort='.$_GET['sort'].'&amp;');
$inner['index_bit'] = '';
if ($info['topics'] > 0) {
	$start = $_GET['page']*$config['forumzahl'];
	$start = $start-$config['forumzahl'];
	$result = $db->query("
	SELECT prefix, vquestion, posts, mark, id, board, topic, date, status, last, last_name, sticky, name 
	FROM {$db->pre}topics WHERE board = '{$board}' $marksql
	ORDER BY sticky DESC, last DESC LIMIT {$start}, ".$config['forumzahl']
	,__LINE__,__FILE__);
	
	$prefix = cache_prefix($board);
	$memberdata = cache_memberdata();

	while ($row = $gpc->prepare($db->fetch_object($result))) {
		$pref = '';
		$showprefix = FALSE;
		if (isset($prefix[$row->prefix]) && $row->prefix > 0) {
			$showprefix = TRUE;
		}
		else {
			$prefix[$row->prefix] = '';
		}
		
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
		
		if ($row->mark == 'n') {
			$pref .= $lang->phrase('forum_mark_n'); 
		}
		elseif ($row->mark == 'a') {
			$pref .= $lang->phrase('forum_mark_a');
		}
		elseif ($row->mark == 'b') {
			$pref .= $lang->phrase('forum_mark_b');
		}
		elseif ($row->mark == 'g') {
			$pref .= $lang->phrase('forum_mark_g');
		}
		elseif ($row->status == '2') {
			$pref .= $lang->phrase('forum_moved');
		}
		elseif ($row->sticky == '1') {
			$pref .= $lang->phrase('forum_announcement');
		}

		if ((isset($my->mark['t'][$row->id]) && $my->mark['t'][$row->id] > $row->last) || $row->last < $my->clv) {
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
		$inner['index_bit'] .= $tpl->parse("showforum/index_bit");
	}
}
else {
	$inner['index_bit'] .= $tpl->parse("showforum/index_bit_empty");
}

echo $tpl->parse("showforum/index");

$mymodules->load('showforum_bottom');

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();		
?>
