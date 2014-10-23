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

define('SCRIPTNAME', 'showtopic');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

($code = $plugins->load('showtopic_topic_query')) ? eval($code) : null;
$result = $db->query("SELECT id, topic, posts, sticky, status, last, board, vquestion, prefix FROM {$db->pre}topics WHERE id = '{$_GET['id']}' LIMIT 1");
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

$catbid = $scache->load('cat_bid');
$fc = $catbid->get();
$last = $fc[$info['board']];
if ($last['topiczahl'] < 1) {
	$last['topiczahl'] = $config['topiczahl'];
}

$q = urldecode($gpc->get('q', str));
if (strxlen($q) > 2) {
	$qUrl = '&q='.urlencode($q);
	$qUrl2 = '&amp;q='.urlencode($q);
}
else {
	$qUrl = $qUrl2 = '';
}

if ($_GET['action'] == 'firstnew') {
	if ($info['last'] >= $my->clv) {
		$result = $db->query("SELECT COUNT(*) AS count FROM {$db->pre}replies WHERE topic_id = '{$info['id']}' AND date <= '{$my->clv}'");
		$old = $db->fetch_assoc($result);
		$tp = $old['count'] + 1; // Number of old post (with topic start post) + 1, to get the first new post, not the last old post
		$pgs = ceil($tp/$last['topiczahl']);
		if ($pgs < 1) {
			$pgs = 1;
		}
		$db->close();
		sendStatusCode(307, 'showtopic.php?id='.$info['id'].'&page='.$pgs.$qUrl.SID2URL_JS_x.'#firstnew');
		exit;
	}
}
elseif ($_GET['action'] == 'last') {
	// Todo: Resourcen sparender wäre es in der Themenansicht einen Anker "last" zu setzen und diesen anzuspringen... damit wäre diese Query gespart
	$result = $db->query('SELECT id FROM '.$db->pre.'replies WHERE topic_id = '.$info['id'].' ORDER BY date DESC LIMIT 1');
	$new = $db->fetch_num($result);
	$pgs = ceil(($info['posts']+1)/$last['topiczahl']);
	$db->close();
	sendStatusCode(307, 'showtopic.php?id='.$info['id'].'&page='.$pgs.$qUrl.SID2URL_JS_x.'#p'.$new[0]);
	exit;
}
elseif ($_GET['action'] == 'mylast') {
	$result = $db->query('SELECT date, id FROM '.$db->pre.'replies WHERE topic_id = '.$info['id'].' AND name="'.$my->id.'" ORDER BY date DESC LIMIT 1');
	$mylast =$db->fetch_num($result);
	$result = $db->query('SELECT COUNT(*) AS count FROM '.$db->pre.'replies WHERE topic_id = '.$info['id'].' AND date > '.$mylast[0]);
	$new = $db->fetch_assoc($result);
	$tp = ($info['posts']+1) - $new['count'];
	$pgs = ceil($tp/$last['topiczahl']);
	if ($pgs < 1) {
		$pgs = 1;
	}
	$db->close();
	sendStatusCode(307, 'showtopic.php?id='.$info['id'].'&page='.$pgs.$qUrl.SID2URL_JS_x.'#p'.$mylast[1]);
	exit;
}
elseif ($_GET['action'] == 'jumpto') {
	$result = $db->query('SELECT date, id FROM '.$db->pre.'replies WHERE topic_id = "'.$info['id'].'" AND id="'.$gpc->get('topic_id', int).'" ORDER BY date DESC LIMIT 1');
	$mylast =$db->fetch_num($result);
	$result = $db->query('SELECT COUNT(*) AS count FROM '.$db->pre.'replies WHERE topic_id = "'.$info['id'].'" AND date > "'.$mylast[0].'"');
	$new = $db->fetch_assoc($result);
	$tp = ($info['posts']+1) - $new['count'];
	$pgs = ceil($tp/$last['topiczahl']);
	if ($pgs < 1) {
		$pgs = 1;
	}
	$db->close();
	sendStatusCode(307, 'showtopic.php?id='.$info['id'].'&page='.$pgs.$qUrl.SID2URL_JS_x.'#p'.$mylast[1]);
	exit;
}

($code = $plugins->load('showtopic_redirect')) ? eval($code) : null;

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
$breadcrumb->Add($prefix.$info['topic']);

forum_opt($last);

// Some speed optimisation
$speeder = $info['posts']+1;
$start = $_GET['page']*$last['topiczahl'];
$start = $start-$last['topiczahl'];
$temp = pages($speeder, $last['topiczahl'], "showtopic.php?id=".$info['id'].$qUrl2."&amp;", $_GET['page']);

define('LINK_PRINT_PAGE', "print.php?id={$info['id']}&amp;page={$_GET['page']}".SID2URL_x);

echo $tpl->parse("header");
echo $tpl->parse("menu");

($code = $plugins->load('showtopic_start')) ? eval($code) : null;

$q = explode(' ', trim($q));

$memberdata_obj = $scache->load('memberdata');
$memberdata = $memberdata_obj->get();

$inner['index_bit'] = array();
$inner['vote_result'] = '';
$inner['related'] = '';

// Do we have a vote?
if (!empty($info['vquestion'])) {
	// Yeah, we have - Create an empty array for the data
	$vote = array(
		'count' => 0, // Number of all votes
		'question' => $info['vquestion'], // The question of the vote
		'entries' => array(), // Each option (with title of the option, ...)
		'voted' => 0, // The option (answer id) the requesting user has chosen (0 = no vote yet)
		'results' => false, // Show the result page (true) or the form to chose the options (false)
		'voter' => array(), // The voter for each option
		'phrase' => null // Phrase to switch to the survey form (change/add)
	);

	if (!$my->vlogin || $my->p['voting'] == 0 || $_GET['temp'] == 1 || $info['status'] != 0) {
		$vote['results'] = true;
	}

	// Get data: count of votes per option, option id, option text
	$vresult = $db->query("
		SELECT COUNT(r.id) as votes, v.id, v.answer
		FROM {$db->pre}vote AS v
			LEFT JOIN {$db->pre}votes AS r ON r.aid = v.id
		WHERE v.tid = '{$info['id']}'
		GROUP BY v.id
		ORDER BY v.id
	");
	if ($db->num_rows($vresult) > 0) {
		// Collect and cache data for a single query instead of multiple
		while ($row = $db->fetch_assoc($vresult)) {
			$vote['entries'][$row['id']] = $row;
			$vote['voter'][$row['id']] = array();
			$vote['count'] += $row['votes'];
		}

		// Now get more data (for what the users voted exactly)
		$sql_aid_in = implode(',', array_keys($vote['entries']));
		$vresult = $db->query("SELECT mid, aid FROM {$db->pre}votes WHERE aid IN({$sql_aid_in})");
		while ($row = $db->fetch_assoc($vresult)) {
			// Save the data for the member who is calling this page
			if ($row['mid'] == $my->id) {
				if ($config['vote_change'] != 1 || ($config['vote_change'] == 1 && $_GET['temp'] != 2)) {
					$vote['results'] = true;
				}
				$vote['voted'] = $row['aid'];
			}
			// Create element in array with name (+ member id as key) at the selected answer
			$vote['voter'][$row['aid']][$row['mid']] = $memberdata[$row['mid']];
		}

		if ($vote['results'] == false) {
			// When we only show the form to submit/change a vote
		    ($code = $plugins->load('showtopic_vote_prepared')) ? eval($code) : null;
			$inner['vote_result'] = $tpl->parse("showtopic/vote");
		}
		else {
			// Show the results
			foreach ($vote['entries'] as $key => $row) {
				if ($row['votes'] > 0) {
					$row['percent'] = $row['votes'] / $vote['count'] * 100;
					if (strstr($row['percent'], '.') > 0) {
						$row['percent'] = numbers($row['percent'], 1);
					}
				}
				else {
					$row['percent'] = 0;
				}
				$vote['entries'][$key] = $row;

				// Make comma separated string from array of users
				// Keys: (0 = Voter separated by comma, 1,2,3,... = Voter name with id as key)
				if (count($vote['voter'][$row['id']]) > 0) {
				    $vote['voter'][$row['id']][0] = implode(', ', $vote['voter'][$row['id']]);
				}
				else {
				    $vote['voter'][$row['id']][0] = '-';
				}
			}
			if ($my->vlogin && $my->p['voting'] == 1 && $info['status'] == 0) {
				$vote['phrase'] = iif($vote['voted'] > 0, 'vote_change_option', 'vote_go_form');
			}
			($code = $plugins->load('showtopic_vote_result_prepared')) ? eval($code) : null;
			$inner['vote_result'] = $tpl->parse("showtopic/vote_result");
		}
	}
}

if ($config['tpcallow'] == 1) {
	$result = $db->query("SELECT id, tid, mid, file, source, hits FROM {$db->pre}uploads WHERE topic_id = '{$info['id']}'");
	$uploads = array();
	while ($row = $db->fetch_assoc($result)) {
		$uploads[$row['tid']][] = $row;
	}
	if (count($uploads) > 0) {
		$fileicons_obj = $scache->load('fileicons');
		$fileicons = $fileicons_obj->get();
	}
}

if ($config['postrating'] == 1) {
	$result = $db->query("SELECT pid, mid, rating FROM {$db->pre}postratings WHERE tid = '{$info['id']}'");
	$ratings = array();
	while ($row = $db->fetch_assoc($result)) {
		if (!isset($ratings[$row['pid']])) {
			$ratings[$row['pid']] = array();
		}
		$ratings[$row['pid']][$row['mid']] = $row['rating'];
	}
}

// Speed up the first pages with less than 10 posts
if ($speeder > $last['topiczahl']) {
	$sql_limit = " LIMIT {$start},{$last['topiczahl']}";
}
else {
	$sql_limit = " LIMIT {$speeder}";
}
$sql_select = iif($config['pm_user_status'] == 1, ", IF (s.mid > 0, 1, 0) AS online");
$sql_join = iif($config['pm_user_status'] == 1, "LEFT JOIN {$db->pre}session AS s ON s.mid = u.id");
($code = $plugins->load('showtopic_query')) ? eval($code) : null;
$result = $db->query("
SELECT
	r.id, r.edit, r.dosmileys, r.dowords, r.topic, r.comment, r.date, r.email as gmail, r.guest, r.name as gname, r.report, r.tstart,
	u.id as mid, u.name as uname, u.mail, u.regdate, u.posts, u.fullname, u.hp, u.signature, u.location, u.gender, u.birthday, u.pic, u.lastvisit, u.icq, u.yahoo, u.aol, u.msn, u.jabber, u.skype, u.groups,
	f.* {$sql_select}
FROM {$db->pre}replies AS r
	LEFT JOIN {$db->pre}user AS u ON r.name = u.id AND r.guest = '0'
	LEFT JOIN {$db->pre}userfields AS f ON u.id = f.ufid AND r.guest = '0'
	{$sql_join}
WHERE r.topic_id = '{$info['id']}'
ORDER BY date ASC
{$sql_limit}
");

$firstnew = 0;
$firstnew_url = null;
if ($info['last'] >= $my->clv) {
	$firstnew_url = 'showtopic.php?action=firstnew&amp;id='.$info['id'].$qUrl2.SID2URL_x;
}

// Custom Profile Fields
include_once('classes/class.profilefields.php');
$pfields = new ProfileFieldViewer();
$rel_post_num = $start;
while ($row = $db->fetch_object($result)) {
	$inner['upload_box'] = '';
	$inner['image_box'] = '';

	$row = $slog->cleanUserData($row);
	$rel_post_num++;
	$row->rel_post_num = $rel_post_num;

	if ($row->guest == 0) {
		$row->mail = '';
		$row->name = $row->uname;
	}
	else {
		$row->mail = $row->gmail;
		$row->name = $row->gname;
		$row->groups = GROUP_GUEST;
		$row->mid = 0;
	}

	// Custom Profile Fields
	$pfields->setUserId($row->mid);
	$pfields->setUserData($row);

	if ($firstnew > 0) {
		$firstnew++;
	}
	if ($row->date >= $my->clv && $firstnew == 0) {
		$firstnew = 1;
		$firstnew_url = "#firstnew";
	}

	$diff = time()-$row->date;
	if ($config['edit_edit_time'] == 0) {
	    $edit_seconds = $diff;
	}
	else {
	    $edit_seconds = $config['edit_edit_time']*60;
	}
	$can_edit = ((($row->mid == $my->id && $row->guest == 0 && $edit_seconds >= $diff) || $my->mp[0] == 1) && $my->p['edit'] == 1 && $last['readonly'] == 0 && !($info['status'] != 0 && $my->mp[0] != 1));

	$new = iif($row->date >= $my->clv, 'new', 'old');

	BBProfile($bbcode);
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
		BBProfile($bbcode, 'signature');
		$row->signature = $bbcode->parse($row->signature);
	}

	if ($config['post_user_status'] == 1) {
		$row->lang_online = $lang->phrase('profile_'.iif($row->online == 1, 'online', 'offline'));
	}
	$row->date = str_date($lang->phrase('dformat1'), times($row->date));
	$row->regdate = gmdate($lang->phrase('dformat2'), times($row->regdate));
	$row->level = $slog->getStatus($row->groups, ', ');
	if (empty($row->location)) {
		$row->location = $lang->phrase('showtopic_na');
	}

	if ((!empty($row->fullname) && $config['fullname_posts'] == 1) || (!empty($row->signature) && $my->opt_showsig == 1)) {
		$bottom = true;
	}
	else {
		$bottom = false;
	}

	if (isset($uploads[$row->id]) && $config['tpcallow'] == 1) {
		foreach ($uploads[$row->id] as $file) {
			$uppath = 'uploads/topics/'.$file['source'];
			$imginfo = get_extension($uppath);

			if (!isset($fileicons[$imginfo])) {
				$icon = 'unknown';
			}
			else {
				$icon = $fileicons[$imginfo];
			}

			// Dateigroesse
			$fsize = filesize($uppath);
			$fsize = formatFilesize($fsize);

			$is_img = ($imginfo == 'gif' || $imginfo == 'jpg' || $imginfo == 'jpeg'  || $imginfo == 'jpe' || $imginfo == 'png') ? true : false;

			($code = $plugins->load('showtopic_attachments_prepared')) ? eval($code) : null;

			if ($is_img == true) {
				$imagesize = getimagesize($uppath);
				$inner['image_box'] .= $tpl->parse("showtopic/image_box");
			}
			else {
				$inner['upload_box'] .= $tpl->parse("showtopic/upload_box");
			}
		}
	}

	$edit = array();
	if (!empty($row->edit)) {
		preg_match_all('~^([^\t]+)\t(\d+)\t([^\t]*)\t([\d\.]+)$~m', $row->edit, $edits, PREG_SET_ORDER);
		BBProfile($bbcode);
		foreach ($edits as $e) {
			$edit[] = array(
				$e[1],
				gmdate($lang->phrase('dformat1'), times($e[2])),
				empty($e[3]) ? $lang->phrase('post_editinfo_na') : $bbcode->wordwrap($e[3]),
				empty($e[4]) ? '-' : $e[4]
			);
		}
		$anz = count($edit);
		if ($anz == 0) {
			$row->edit = null;
		}
		$lastdata = end($edit);
		if ($lastdata != false) {
			list(, $date, $why, ) = $lastdata;
		}
	}

	// Ratings
	$showrating = false;
	$row->rating = 50;
	$ratingcounter = 0;
	if ($config['postrating'] == 1) {
		if (!isset($ratings[$row->id])) {
			$ratings[$row->id] = array();
		}
		if ($my->vlogin && $my->id != $row->mid && !isset($ratings[$row->id][$my->id])) {
			$showrating = true;
		}
		$ratingcounter = count($ratings[$row->id]);
		if (count($ratings[$row->id]) > 0) {
			$row->rating = round(array_sum($ratings[$row->id])/$ratingcounter*50)+50;
		}
	}

	($code = $plugins->load('showtopic_entry_prepared')) ? eval($code) : null;
	$inner['index_bit'][] = $tpl->parse("showtopic/index_bit");
	($code = $plugins->load('showtopic_entry_added')) ? eval($code) : null;
}

if ($my->vlogin && is_id($info['id'])) {
	$result = $db->query("SELECT id, type FROM {$db->pre}abos WHERE mid = '{$my->id}' AND tid = '{$info['id']}'");
	$abox = $db->fetch_assoc($result);
}
else {
	$abox = array('id' => null, 'type' => null);
}

$inner['index_bit'] = implode('', $inner['index_bit']);

($code = $plugins->load('showtopic_prepared')) ? eval($code) : null;
echo $tpl->parse("showtopic/index");

$slog->setTopicRead($info['id'], $topforums);

($code = $plugins->load('showtopic_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();
?>