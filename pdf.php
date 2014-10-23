<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2007  Matthias Mohr, MaMo Net

	Author: Matthias Mohr
	Publisher: http://www.viscacha.org
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

DEFINE('SCRIPTNAME', 'pdf');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

// PDF powered by FPDF (www.fpdf.org)
include('classes/fpdf/class.php');
include('classes/fpdf/extension.php');

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();

($code = $plugins->load('pdf_topic_query')) ? eval($code) : null;
$result = $db->query('
SELECT id, topic, posts, sticky, status, last, board, vquestion, prefix
FROM '.$db->pre.'topics
WHERE id = "'.$_GET['id'].'"
LIMIT 1
',__LINE__,__FILE__);
$info = $gpc->prepare($db->fetch_assoc($result));

$my->p = $slog->Permissions($info['board']);

$error = array();
if ($db->num_rows($result) == 0) {
	$error[] = $lang->phrase('query_string_error');
}
if ($my->p['forum'] == 0 || $config['pdfdownload'] == 0 || $my->p['pdf'] == 0) {
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

forum_opt($last, 'pdf');

$start = $_GET['page']*$last['topiczahl'];
$start = $start-$last['topiczahl'];

// Some speed optimisation
$speeder = $info['posts']+1;
if ($speeder > $last['topiczahl']) {
	$searchsql = " LIMIT {$start},{$last['topiczahl']}";
}
else {
	$searchsql = " LIMIT {$speeder}";
}

BBProfile($bbcode);

$memberdata_obj = $scache->load('memberdata');
$memberdata = $memberdata_obj->get();
$pdf = new PDF();
$pdftitle = $prefix.$info['topic'];
$pdf->SetCompression($config['pdfcompress']);
$pdf->AliasNbPages('[Pages]');
$pdf->SetCreator('Viscacha '.$config['version']);
$pdf->SetAuthor($config['fname']);
$pdf->SetSubject($pdftitle);
$pdf->SetTitle($pdftitle);
$pdf->AddPage();

($code = $plugins->load('pdf_start')) ? eval($code) : null;

$inner['body'] = '';
// prepare for vote
if (!empty($info['vquestion']) && $_GET['page'] == 1) {
	$votes = 0;

	$inner['head'] = $lang->phrase('pdf_vote').$info['vquestion'];

	$cachev = array();
	$aids = array();
	$vresult = $db->query("
	SELECT COUNT(r.id) as votes, v.id, v.answer
	FROM {$db->pre}vote AS v LEFT JOIN {$db->pre}votes AS r ON r.aid=v.id
	WHERE v.tid = '{$info['id']}' GROUP BY v.id ORDER BY v.id",__LINE__,__FILE__);
	while ($row = $db->fetch_assoc($vresult)) {
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
	}

	foreach ($cachev as $key => $row) {
		if ($votes > 0) {
			$row['percent'] = $row['votes'] / $votes * 100;
			if (strstr($row['percent'], '.') > 0) {
				$row['percent'] = sprintf("%01.1f", $row['percent']);
			}
		}
		else {
			$row['percent'] = 0;
		}
		$inner['body'] .= "- ".$row['answer']." <i>".$lang->phrase('pdf_vote_result')."</i><br>";
	}
	$inner['foot'] = $lang->phrase('pdf_vote_voters').$votes;
	($code = $plugins->load('pdf_vote_prepared')) ? eval($code) : null;
	$pdf->Bookmark($inner['head'],0,-1);
	$pdf->PrintVote($inner['head'], $inner['body'], $inner['foot']);
}

if ($config['tpcallow'] == 1) {
	$result = $db->query("SELECT id, tid, mid, file, source, hits FROM {$db->pre}uploads WHERE topic_id = ".$info['id'],__LINE__,__FILE__);
	$uploads = array();
	while ($row = $db->fetch_assoc($result)) {
		$uploads[$row['tid']][] = $row;
	}
}

($code = $plugins->load('pdf_query')) ? eval($code) : null;
$result = $db->query("
SELECT r.edit, r.dosmileys, r.dowords, r.id, r.topic, r.comment, r.date, u.name as uname, r.name as gname, u.id as mid, u.groups, u.fullname, r.email as gmail, r.guest
FROM {$db->pre}replies AS r
	LEFT JOIN {$db->pre}user AS u ON r.name=u.id
WHERE r.topic_id = '{$info['id']}'
ORDER BY r.date ASC
{$searchsql}
",__LINE__,__FILE__);

while ($row = $db->fetch_object($result)) {
	$inner['upload_box'] = '';

	if ($row->guest == 0) {
		$row->mail = '';
		$row->name = $row->uname;
	}
	else {
		$row->mail = $row->gmail;
		$row->name = $row->gname;
		$row->groups = GROUP_GUEST;
	}

	$bbcode->setSmileys(0);
	if ($config['wordstatus'] == 0) {
		$row->dowords = 0;
	}
	$bbcode->setReplace($row->dowords);
	if ($info['status'] == 2) {
		$row->comment = $bbcode->ReplaceTextOnce($row->comment, 'moved');
	}
	$row->comment = $bbcode->parse($row->comment, 'pdf');
	$row->date = gmdate($lang->phrase('dformat1'), times($row->date));

	if (isset($uploads[$row->id]) && $config['tpcallow'] == 1) {
		$row->comment .= '<br><hr><b>'.$gpc->save_str($lang->phrase('pdf_attachments')).'</b>';
		foreach ($uploads[$row->id] as $file) {
			$uppath = 'uploads/topics/'.$file['source'];

			// Dateigroesse
			$fsize = filesize($uppath);
			$fsize = formatFilesize($fsize);

			($code = $plugins->load('pdf_attachments_prepared')) ? eval($code) : null;
			// Ausgabe
			$row->comment .= '<br><a href="'.$config['furl'].'/misc.php?action=attachment&id='.$file['id'].'">'.$file['file'].'</a> '.$gpc->save_str($lang->phrase('pdf_attachments_filesize'));
		}
	}

	($code = $plugins->load('pdf_entry_prepared')) ? eval($code) : null;
	$pdf->Bookmark($row->topic, 0, -1);
	$pdf->PrintTopic($row->topic, $pdf->unhtmlentities($lang->phrase('pdf_postinfo')), $row->comment);
}

($code = $plugins->load('pdf_prepared')) ? eval($code) : null;
$pdf->Output($info['id'].'-'.$_GET['page'].'.pdf','D');

$slog->updatelogged();
$phpdoc->Out(0);
$db->close();
?>
