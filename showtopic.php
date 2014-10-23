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

DEFINE('SCRIPTNAME', 'showtopic');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();

$result = $db->query('SELECT id, topic, posts, sticky, status, last, board, vquestion, prefix FROM '.$db->pre.'topics WHERE id = '.$_GET['id'].' LIMIT 1',__LINE__,__FILE__);
$info = $gpc->prepare($db->fetch_assoc($result));

$my->p = $slog->Permissions($info['board']);
$my->mp = $slog->ModPermissions($info['board']);

$error = array();
if ($db->num_rows($result) < 1) {
	$error[] = $lang->phrase('query_string_error');
}
if ($my->p['forum'] == 0) {
	$error[] = $lang->phrase('not_allowed');
}
if (count($error) > 0) {
	errorLogin($error,'forum.php'.SID2URL_1);
}

$fc = cache_cat_bid();
$last = $fc[$info['board']];

$q = urldecode($gpc->get('q', str));
if (strlen($q) > 2) {
	$qUrl = '&q='.urlencode($q);
}
else {
	$qUrl = '';
}
if ($_GET['action'] == 'firstnew') {
	if ($info['last'] > $my->clv) {
		$result = $db->query('SELECT COUNT(*) AS count FROM '.$db->pre.'replies WHERE topic_id = '.$info['id'].' AND date > '.$my->clv,__LINE__,__FILE__);
		$new = $db->fetch_assoc($result);
		$tp = ($info['posts']+1) - $new['count'];
		$pgs = ceil($tp/$config['topiczahl']);
		if ($pgs < 1) {
			$pgs = 1;
		}
		viscacha_header('Location: showtopic.php?id='.$info['id'].'&page='.$pgs.$qUrl.SID2URL_JS_x.'#firstnew');
		exit;
	}
}
elseif ($_GET['action'] == 'last') {
	$result = $db->query('SELECT id FROM '.$db->pre.'replies WHERE topic_id = '.$info['id'].' ORDER BY date DESC LIMIT 1',__LINE__,__FILE__);
	$new = $db->fetch_array($result);
	$pgs = ceil(($info['posts']+1)/$config['topiczahl']);
	viscacha_header('Location: showtopic.php?id='.$info['id'].'&page='.$pgs.$qUrl.SID2URL_JS_x.'#p'.$new[0]);
	exit;
}
elseif ($_GET['action'] == 'mylast') {
	$result = $db->query('SELECT date, id FROM '.$db->pre.'replies WHERE topic_id = '.$info['id'].' AND name="'.$my->id.'" ORDER BY date DESC LIMIT 1');
	$mylast =$db->fetch_array($result);
	$result = $db->query('SELECT COUNT(*) AS count FROM '.$db->pre.'replies WHERE topic_id = '.$info['id'].' AND date > '.$mylast[0],__LINE__,__FILE__);
	$new = $db->fetch_assoc($result);
	$tp = ($info['posts']+1) - $new['count'];
	$pgs = ceil($tp/$config['topiczahl']);
	if ($pgs < 1) {
		$pgs = 1;
	}
	viscacha_header('Location: showtopic.php?id='.$info['id'].'&page='.$pgs.$qUrl.SID2URL_JS_x.'#p'.$mylast[1]);
	exit;
}
elseif ($_GET['action'] == 'jumpto') {
	$result = $db->query('SELECT date, id FROM '.$db->pre.'replies WHERE topic_id = "'.$info['id'].'" AND id="'.$gpc->get('topic_id', int).'" ORDER BY date DESC LIMIT 1');
	$mylast =$db->fetch_array($result);
	$result = $db->query('SELECT COUNT(*) AS count FROM '.$db->pre.'replies WHERE topic_id = "'.$info['id'].'" AND date > "'.$mylast[0].'"',__LINE__,__FILE__);
	$new = $db->fetch_assoc($result);
	$tp = ($info['posts']+1) - $new['count'];
	$pgs = ceil($tp/$config['topiczahl']);
	if ($pgs < 1) {
		$pgs = 1;
	}
	viscacha_header('Location: showtopic.php?id='.$info['id'].'&page='.$pgs.$qUrl.SID2URL_JS_x.'#p'.$mylast[1]);
	exit;
}

$mymodules->load('showtopic_redirect');

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
$breadcrumb->Add($pre.$info['topic']);

forum_opt($last['opt'], $last['optvalue'], $last['id']);

echo $tpl->parse("header");
echo $tpl->parse("menu");

$start = $_GET['page']*$config['topiczahl'];
$start = $start-$config['topiczahl'];

// Some speed optimisation
$speeder = $info['posts']+1;
// Speed up the first pages with less than 10 posts
if ($speeder > $config['topiczahl']) {
	$searchsql = " LIMIT ".$start.",".$config['topiczahl'];
}
else {
	$searchsql = " LIMIT ".$speeder;
}

$temp = pages($speeder, 'topiczahl', "showtopic.php?id=".$info['id']."&amp;");
	
$bbcode = initBBCodes(TRUE);
$q = explode(' ', trim($q));
$memberdata = cache_memberdata();

$inner['index_bit'] = '';
$inner['vote_result'] = '';
$inner['related'] = '';

$mymodules->load('showtopic_top');

// prepare for vote
if (!empty($info['vquestion'])) {
	$votes = 0;
	if (!$my->vlogin || $my->p['voting'] == 0 || $_GET['temp'] == 1) {
		$showresults = TRUE;
	}
	else {
		$showresults = FALSE;
	}
	
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
	if (count($aids) > 0) {
		$voter = array();
		$tids = implode(',', $aids);
		$rresult = $db->query("SELECT mid, aid FROM {$db->pre}votes WHERE aid IN({$tids})",__LINE__,__FILE__);
		while ($row = $db->fetch_assoc($rresult)) {
			if ($row['mid'] == $my->id) {
				$showresults = TRUE;
			}
			if (!isset($voter[$row['aid']]) || ! is_array($voter[$row['aid']])) {
				$voter[$row['aid']] = array();
			}
			$voter[$row['aid']][$row['mid']] = $memberdata[$row['mid']]; // Array mit den Namen der Leute und deren Antwort
		}
		
		if (!$showresults) {
		    $mymodules->load('showtopic_vote_top');
			$inner['vote_result'] = $tpl->parse("showtopic/vote");
		}
		else {
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
				if (count($voter[$row['id']]) > 0) {
				    $voter[$row['id']][0] = implode(', ', $voter[$row['id']]);
				}
				else {
				    $voter[$row['id']][0] = '-';
				}
			}
			$mymodules->load('showtopic_vote_result_top');
			$inner['vote_result'] = $tpl->parse("showtopic/vote_result");
		}
	}
}

if ($config['tpcallow'] == 1) {
	$result = $db->query("SELECT id, tid, mid, file, hits FROM {$db->pre}uploads WHERE topic_id = ".$info['id'],__LINE__,__FILE__);
	$uploads = array();
	while ($row = $db->fetch_assoc($result)) {
		$uploads[$row['tid']][] = $row;
	}
	if (count($uploads) > 0) {
		$fileicons = cache_fileicons();
	}
}

$result = $db->query("
SELECT r.edit, r.dosmileys, r.dowords, r.id, r.topic, r.comment, r.date, u.name as uname, r.name as gname, u.id as mid, u.groups, u.fullname, u.hp, u.pic, r.email as gmail, u.signature, u.regdate, u.location 
FROM {$db->pre}replies AS r LEFT JOIN {$db->pre}user AS u ON r.name=u.id 
WHERE r.topic_id = '{$info['id']}' ORDER BY date ASC".$searchsql,__LINE__,__FILE__);

$firstnew = 0;
while ($row = $gpc->prepare($db->fetch_object($result))) {
	$inner['upload_box'] = '';
	$inner['image_box'] = '';
	
	if (empty($row->gmail)) {
		$row->mail = '';
		$row->name = $row->uname;
	}
	else {
		$row->mail = $row->gmail;
		$row->name = $row->gname;
		$row->groups = GROUP_GUEST;
		$row->mid = 0;
	}

    if ($firstnew == 1) {
        $firstnew = 2;
    }
	if ($row->date > $my->clv && $firstnew == 0) {
		$firstnew = 1;
	}
	$new = iif($row->date > $my->clv, 'new', 'old');
	
	$bbcode->setProfile();
	$bbcode->setSmileys($row->dosmileys);
	if ($config['wordstatus'] == 0) {
		$row->dowords = 0;
	}
	$bbcode->setReplace($row->dowords);
	$bbcode->setAuthor($row->mid);
	if ($info['status'] == 2) {
		$row->comment = $bbcode->ReplaceTextOnce($row->comment, 'moved');
	}
	if (count($q) > 0) {
		$bbcode->setHighlight('highlight', $q);
	}
	$row->comment = $bbcode->parse($row->comment);
	
	if ($my->opt_showsig == 1) {
		$bbcode->setProfile('signature');
		$row->signature = $bbcode->parse($row->signature);
	}

	$row->date = str_date($lang->phrase('dformat1'), times($row->date));
	$row->regdate = gmdate($lang->phrase('dformat2'), times($row->regdate));
	$row->level = $slog->getStatus($row->groups, ', ');
	if (empty($row->location)) {
		$row->location = $lang->phrase('showtopic_na');
	}

	if (!empty($row->fullname) || (!empty($row->signature) && $my->opt_showsig == 1)) {
		$bottom = TRUE;
	}
	else {
		$bottom = FALSE;
	}
	
	if (isset($uploads[$row->id]) && $config['tpcallow'] == 1) {
		foreach ($uploads[$row->id] as $file) {
			$file['file'] = trim($file['file']);
			$uppath = 'uploads/topics/'.$file['file'];
			$imginfo = get_extension($uppath, TRUE);
			
			if (!isset($fileicons[$imginfo])) {
				$icon = 'unknown.gif';
			}
			else {
				$icon = $fileicons[$imginfo];
			}
			
			// Dateigroesse
			$fsize = filesize($uppath);
			$fsize = formatFilesize($fsize);
			
			// Sonderbehandlung von Bildern (gif,jp(e)g,png,swf):
			if ($imginfo == 'gif' or $imginfo == 'jpg' or $imginfo == 'jpeg'  or $imginfo == 'jpe' or $imginfo == 'png') {
				//Bildgroesse
				$imagesize = getimagesize($uppath);
				$inner['image_box'] .= $tpl->parse("showtopic/image_box");
			}
			else {
				$inner['upload_box'] .= $tpl->parse("showtopic/upload_box");
			}
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
	$mymodules->load('showtopic_bit');
	$inner['index_bit'] .= $tpl->parse("showtopic/index_bit");
} 

echo $tpl->parse("showtopic/index");
$mymodules->load('showtopic_bottom');


$my->mark['t'][$info['id']] = time();

// Erstelle ein Array mit schon gelesenen Beiträgen
$keys = array();
while (list($key, $val) = each ($my->mark['t'])) {
   $keys[] = $key;
}
$inkeys = implode(",",$keys);
foreach ($topforums as $tf) {
	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'topics WHERE board = "'.$tf.'" AND last > "'.$my->clv.'" AND id NOT IN('.$inkeys.')',__LINE__,__FILE__);
	$row = $db->fetch_array($result);
	if ($row[0] == 0) {
		$my->mark['f'][$tf] = time();
	}
}

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();
?>
