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

define('SCRIPTNAME', 'print');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

($code = $plugins->load('print_topic_query')) ? eval($code) : null;
$result = $db->query('
SELECT topic, posts, sticky, status, last, board, vquestion, prefix
FROM '.$db->pre.'topics
WHERE id = '.$_GET['id'].'
LIMIT 1
');
$info = $gpc->prepare($db->fetch_assoc($result));

$my->p = $slog->Permissions($info['board']);

$error = array();
if ($db->num_rows($result) < 1) {
	$error[] = $lang->phrase('query_string_error');
}
if ($my->p['forum'] == 0) {
	$error[] = $lang->phrase('not_allowed');
}
if (count($error) > 0) {
	errorLogin($error);
}

$catbid = $scache->load('cat_bid');
$fc = $catbid->get();
$last = $fc[$info['board']];
if ($last['topiczahl'] < 1) {
	$last['topiczahl'] = $config['topiczahl'];
}

$prefix = '';
if ($info['prefix'] > 0) {
	$prefix_obj = $scache->load('prefix');
	$prefix_arr = $prefix_obj->get($info['board']);
	if (isset($prefix_arr[$info['prefix']])) {
		$prefix = $prefix_arr[$info['prefix']]['value'];
		$prefix = $lang->phrase('showtopic_prefix_title');
	}
}

$topforums = get_headboards($fc, $last, TRUE);
$breadcrumb->Add($last['name'], "showforum.php?id=".$last['id'].SID2URL_x);
$breadcrumb->Add($prefix.$info['topic'], "showtopic.php?id={$_GET['id']}&amp;page=".$_GET['page'].SID2URL_x);

forum_opt($last);

($code = $plugins->load('print_start')) ? eval($code) : null;

$start = $_GET['page']*$last['topiczahl'];
$start = $start-$last['topiczahl'];

// Some speed optimisation
$speeder = $info['posts']+1;
if ($speeder > $last['topiczahl']) {
	$searchsql = " LIMIT ".$start.",".$last['topiczahl'];
}
else {
	$searchsql = " LIMIT ".$speeder;
}

BBProfile($bbcode);

$memberdata_obj = $scache->load('memberdata');
$memberdata = $memberdata_obj->get();

$inner['index_bit'] = '';
$inner['vote_result'] = '';

// prepare for vote
if (!empty($info['vquestion']) && $_GET['page'] == 1) {
	$votes = 0;

	$cachev = array();
	$aids = array();
	$vresult = $db->query("
	SELECT COUNT(r.id) as votes, v.id, v.answer
	FROM {$db->pre}vote AS v
		LEFT JOIN {$db->pre}votes AS r ON r.aid=v.id
	WHERE v.tid = '{$_GET['id']}'
	GROUP BY v.id
	ORDER BY v.id
	");
	while ($row = $db->fetch_assoc($vresult)) {
		$row['answer'] = $gpc->prepare($row['answer']);
		$cachev[] = $row;
		$votes += $row['votes'];
		if (!isset($aids[$row['id']])) {
			$aids[$row['id']] = $row['id'];
		}
	}
	$voter = array();
	$tids = implode(',', $aids);
	$rresult = $db->query("SELECT mid, aid FROM {$db->pre}votes WHERE aid IN({$tids})");
	while ($row = $db->fetch_assoc($rresult)) {
		if (!isset($voter[$row['aid']]) || ! is_array($voter[$row['aid']])) {
			$voter[$row['aid']] = array();
		}
		$voter[$row['aid']][$row['mid']] = $memberdata[$row['mid']]; // Array mit den Namen der Leute und deren Antwort
	}

	foreach ($cachev as $key => $row) {
		if ($votes > 0) {
			$row['percent2'] = ceil($row['votes'] / $votes * 200);
			$row['percent'] = $row['votes'] / $votes * 100;
			if (strstr($row['percent'], '.') > 0) {
				$row['percent'] = sprintf("%01.1f", $row['percent']);
			}
		}
		else {
			$row['percent'] = 0;
			$row['percent2'] = 0;
		}
		$cachev[$key] = $row;
		if (!isset($voter[$row['id']])) {
			$voter[$row['id']] = array();
		}
		$voter[$row['id']][0] = implode(', ', $voter[$row['id']]);
	}
	($code = $plugins->load('print_vote_prepared')) ? eval($code) : null;
	$inner['vote_result'] = $tpl->parse("print/vote");
}

if ($config['tpcallow'] == 1) {
	$result = $db->query("SELECT id, tid, mid, file, source, hits FROM {$db->pre}uploads WHERE topic_id = ".$_GET['id']);
	$uploads = array();
	while ($row = $db->fetch_assoc($result)) {
		$uploads[$row['tid']][] = $row;
	}
}

($code = $plugins->load('print_query')) ? eval($code) : null;
$result = $db->query("
SELECT r.edit, r.dosmileys, r.dowords, r.id, r.topic, r.comment, r.date, u.name as uname, r.name as gname, u.id as mid, u.groups, u.fullname, r.email as gmail, r.guest
FROM {$db->pre}replies AS r
	LEFT JOIN {$db->pre}user AS u ON r.name = u.id AND r.guest = '0'
WHERE r.topic_id = '{$_GET['id']}' {$searchsql}
");

while ($row = $db->fetch_object($result)) {
	$inner['upload_box'] = '';

	$row = $slog->cleanUserData($row);

	if ($row->guest == 0) {
		$row->mail = '';
		$row->name = $row->uname;
	}
	else {
		$row->mail = $row->gmail;
		$row->name = $row->gname;
		$row->groups = GROUP_GUEST;
	}

	$bbcode->setSmileys($row->dosmileys);
	if ($config['wordstatus'] == 0) {
		$row->dowords = 0;
	}
	$bbcode->setReplace($row->dowords);
	if ($info['status'] == 2) {
		$row->comment = $bbcode->ReplaceTextOnce($row->comment, 'moved');
	}
	$row->comment = $bbcode->parse($row->comment);

	$row->date = gmdate($lang->phrase('dformat1'), times($row->date));

	if (isset($uploads[$row->id]) && $config['tpcallow'] == 1) {
		foreach ($uploads[$row->id] as $file) {
			$uppath = 'uploads/topics/'.$file['source'];
			$info = get_extension($uppath);

			// Dateigroesse
			$fsize = filesize($uppath);
			$fsize = formatFilesize($fsize);

			($code = $plugins->load('print_attachments_prepared')) ? eval($code) : null;
			$inner['upload_box'] .= $tpl->parse("print/upload_box");
		}
	}

	if (!empty($row->edit)) {
		$edits = explode("\n", $row->edit);
		$anz = count($edits);
		$anz--;
		$lastdata = explode("\t", $edits[$anz-1]);
		$date = gmdate($lang->phrase('dformat1'), times($lastdata[1]));
		$why = iif(empty($lastdata[2]), $lang->phrase('post_editinfo_na'), $bbcode->wordwrap($lastdata[2]));
	}

	($code = $plugins->load('print_entry_prepared')) ? eval($code) : null;
	$inner['index_bit'] .= $tpl->parse("print/index_bit");
}

($code = $plugins->load('print_prepared')) ? eval($code) : null;
echo $tpl->parse("print/index");
($code = $plugins->load('print_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
$phpdoc->Out();
$db->close();
?>