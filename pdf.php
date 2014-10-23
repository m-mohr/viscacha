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

DEFINE('SCRIPTNAME', 'pdf');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

// PDF powered by FPDF (www.fpdf.org)
define('FPDF_FONTPATH','classes/fpdf/font/');
include('classes/fpdf/class.php');
include('classes/fpdf/extension.php');

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();

$result = $db->query('SELECT id, topic, posts, sticky, status, last, board, vquestion, prefix FROM '.$db->pre.'topics WHERE id = "'.$_GET['id'].'" LIMIT 1',__LINE__,__FILE__);
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

$pdftitle = html_entity_decode($config['fname'].": ".$pre.$info['topic']);
$pdf = new PDF();
$pdf->SetCompression($config['pdfcompress']);
$pdf->AliasNbPages('[Pages]');
$pdf->SetCreator('Viscacha '.$config['version']);
$pdf->SetAuthor($config['fname']);
$pdf->SetSubject($pre.$info['topic']);
$pdf->SetTitle($pre.$info['topic']);
$pdf->AddPage(); 

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
	$pdf->Bookmark($inner['head'],0,-1);
	$pdf->PrintVote($inner['head'], $inner['body'], $inner['foot']);
}

if ($config['tpcallow'] == 1) {
	$result = $db->query("SELECT id, tid, mid, file, hits FROM {$db->pre}uploads WHERE topic_id = ".$info['id'],__LINE__,__FILE__);
	$uploads = array();
	while ($row = $db->fetch_assoc($result)) {
		$uploads[$row['tid']][] = $row;
	}
}

$result = $db->query("
SELECT r.edit, r.dosmileys, r.dowords, r.id, r.topic, r.comment, r.date, u.name as uname, r.name as gname, u.id as mid, u.groups, u.fullname, r.email as gmail
FROM {$db->pre}replies AS r LEFT JOIN {$db->pre}user AS u ON r.name=u.id 
WHERE r.topic_id = '{$info['id']}' ".$searchsql,__LINE__,__FILE__);

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

	$row->comment = trim($row->comment);
	$bbcode->setSmileys(0);
	if ($config['wordstatus'] == 0) {
		$row->dowords = 0;
	}
	$bbcode->setReplace($row->dowords);
	if ($info['status'] == 2) {
		$row->comment = $bbcode->ReplaceTextOnce($row->comment, 'moved');
	}
	$row->comment = html_entity_decode($bbcode->parse($row->comment, 'pdf'));
	$row->date = gmdate($lang->phrase('dformat1'), times($row->date));
	$row->topic = html_entity_decode($row->topic);

	if (isset($uploads[$row->id]) && $config['tpcallow'] == 1) {
		$row->comment .= '<br><hr><b>'.$lang->phrase('pdf_attachments').'</b><br>';
		foreach ($uploads[$row->id] as $file) {
			$file['file'] = trim($file['file']);
			$uppath = 'uploads/topics/'.$file['file'];
			
			// Dateigroesse
			$fsize = filesize($uppath);
			$fsize = formatFilesize($fsize);
			
			// Ausgabe
			$row->comment .= '<a href="'.$config['furl'].'/misc.php?action=attachment&id='.$file['id'].'">'.$file['file'].'</a> '.$lang->phrase('pdf_attachments_filesize').'<br>';
		}
	}
	
	$pdf->Bookmark($row->topic, 0, -1);
	$pdf->PrintTopic($row->topic, $lang->phrase('pdf_postinfo'), $row->comment);
}

$pdf->Output($info['id'].'-'.$_GET['page'].'.pdf','D');

$slog->updatelogged();
$phpdoc->Out(0);
$db->close();
?>
