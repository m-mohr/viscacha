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

DEFINE('SCRIPTNAME', 'print');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();

$result = $db->query('SELECT topic, posts, sticky, status, last, board, vquestion, prefix FROM '.$db->pre.'topics WHERE id = '.$_GET['id'].' LIMIT 1',__LINE__,__FILE__);
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

$fc = cache_cat_bid();
$last = $fc[$info['board']];

$pre = '';
if ($info['prefix'] > 0) {
	$prefix = cache_prefix($info['board']);
	if (isset($prefix[$info['prefix']])) {
		$pre = $prefix[$info['prefix']];
		$pre = $lang->phrase('showtopic_prefix_title');
	}
}

$topforums = get_headboards($fc, $last, TRUE);
$breadcrumb->Add($last['name'], "showforum.php?id=".$last['id'].SID2URL_x);
$breadcrumb->Add($pre.$info['topic'], "showtopic.php?id={$_GET['id']}&amp;page=".$_GET['page'].SID2URL_x);

forum_opt($last['opt'], $last['optvalue'], $last['id']);

$start = $_GET['page']*$config['topiczahl'];
$start = $start-$config['topiczahl'];

// Some speed optimisation
$speeder = $info['posts']+1;
if ($speeder > $config['topiczahl']) {
	$searchsql = " LIMIT ".$start.",".$config['topiczahl'];
}
else {
	$searchsql = " LIMIT ".$speeder;
}
	
$bbcode = initBBCodes();
$memberdata = cache_memberdata();

$inner['index_bit'] = '';
$inner['vote_result'] = '';

// prepare for vote
if (!empty($info['vquestion']) && $_GET['page'] == 1) {
	$votes = 0;
	
	$cachev = array();
	$aids = array();
	$vresult = $db->query("
	SELECT COUNT(r.id) as votes, v.id, v.answer
	FROM {$db->pre}vote AS v LEFT JOIN {$db->pre}votes AS r ON r.aid=v.id 
	WHERE v.tid = '{$_GET['id']}' GROUP BY v.id ORDER BY v.id",__LINE__,__FILE__);
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
	$rresult = $db->query("SELECT mid, aid FROM {$db->pre}votes WHERE aid IN({$tids})",__LINE__,__FILE__);
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
	$inner['vote_result'] = $tpl->parse("print/vote");
}

if ($config['tpcallow'] == 1) {
	$result = $db->query("SELECT id, tid, mid, file, hits FROM {$db->pre}uploads WHERE topic_id = ".$_GET['id'],__LINE__,__FILE__);
	$uploads = array();
	while ($row = $db->fetch_assoc($result)) {
		$uploads[$row['tid']][] = $row;
	}
}

$result = $db->query("
SELECT r.edit, r.dosmileys, r.dowords, r.id, r.topic, r.comment, r.date, u.name as uname, r.name as gname, u.id as mid, u.groups, u.fullname, r.email as gmail
FROM {$db->pre}replies AS r LEFT JOIN {$db->pre}user AS u ON r.name=u.id 
WHERE r.topic_id = '{$_GET['id']}'  ".$searchsql,__LINE__,__FILE__);

while ($row = $gpc->prepare($db->fetch_object($result))) {
	$inner['upload_box'] = '';
	
	if (empty($row->gmail)) {
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
			$file['file'] = trim($file['file']);
			$uppath = 'uploads/topics/'.$file['file'];
			$info = get_extension($uppath, TRUE);
			
			// Dateigroesse
			$fsize = filesize($uppath);
			$fsize = formatFilesize($fsize);
			
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
	$inner['index_bit'] .= $tpl->parse("print/index_bit");
} 

$mymodules->load('print_top');

$slog->updatelogged();
$zeitmessung = t2();

echo $tpl->parse("print/index");

$mymodules->load('print_bottom');

$phpdoc->Out();
$db->close();
?>
