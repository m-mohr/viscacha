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

define('SCRIPTNAME', 'showtopic');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

if (!is_id($_GET['id']) && is_id($_GET['topic_id'])) {
	$result = $db->query("SELECT topic_id FROM {$db->pre}replies WHERE id = '{$_GET['topic_id']}'");
	$_GET['id'] = $db->fetch_one($result);
}

($code = $plugins->load('showtopic_topic_query')) ? eval($code) : null;
$result = $db->query("SELECT id, topic, posts, sticky, status, last, board, vquestion, prefix FROM {$db->pre}topics WHERE id = '{$_GET['id']}'");
$info = $db->fetch_assoc($result);

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

if ($_GET['action'] == 'firstnew' && $info['last'] >= $my->clv) {
	$sql_order = iif($last['post_order'] == 1, '>', '<=');
	$result = $db->query("SELECT COUNT(*) AS count FROM {$db->pre}replies WHERE topic_id = '{$info['id']}' AND date {$sql_order} '{$my->clv}'");
	$old = $db->fetch_assoc($result);
	if ($last['post_order'] != 1) {
		$old['count']++; // Number of old post (with topic start post) + 1, to get the first new post, not the last old post
	}
	$pgs = ceil($old['count']/$last['topiczahl']);
	if ($pgs < 1) {
		$pgs = 1;
	}
	sendStatusCode(302, 'showtopic.php?id='.$info['id'].'&page='.$pgs.SID2URL_JS_x.'#firstnew');
}
elseif ($_GET['action'] == 'last') {
	// Todo: Resourcen sparender wäre es in der Themenansicht einen Anker "last" zu setzen und diesen anzuspringen... damit wäre diese Query gespart
	// For post_order = 1: Query could be saved, we can just jump to the first page, first post is the post we are looking for...
	$result = $db->query("SELECT id FROM {$db->pre}replies WHERE topic_id = '{$info['id']}' ORDER BY date DESC LIMIT 1");
	$new = $db->fetch_num($result);
	if ($last['post_order'] == 1) {
		$pgs = 1;
	}
	else {
		$pgs = ceil(($info['posts']+1)/$last['topiczahl']);
	}
	sendStatusCode(302, 'showtopic.php?id='.$info['id'].'&page='.$pgs.SID2URL_JS_x.'#p'.$new[0]);
}
elseif ($_GET['action'] == 'mylast') {
	$result = $db->query("SELECT date, id FROM {$db->pre}replies WHERE topic_id = '{$info['id']}' AND name = '{$my->id}' ORDER BY date DESC LIMIT 1");
	$mylast = $db->fetch_num($result);
	$sql_order = iif($last['post_order'] == 1, '>=', '<');
	$result = $db->query("SELECT COUNT(*) AS count FROM {$db->pre}replies WHERE topic_id = '{$info['id']}' AND date {$sql_order} {$mylast[0]}");
	$new = $db->fetch_assoc($result);
	$tp = ($info['posts']+1) - $new['count'];
	$pgs = ceil($tp/$last['topiczahl']);
	if ($pgs < 1) {
		$pgs = 1;
	}
	sendStatusCode(302, 'showtopic.php?id='.$info['id'].'&page='.$pgs.SID2URL_JS_x.'#p'.$mylast[1]);
}
elseif ($_GET['action'] == 'jumpto') {
	$result = $db->query("SELECT date, id FROM {$db->pre}replies WHERE topic_id = '{$info['id']}' AND id = '{$_GET['topic_id']}'");
	$mylast = $db->fetch_num($result);
	$sql_order = iif($last['post_order'] == 1, '<', '>');
	$result = $db->query("SELECT COUNT(*) AS count FROM {$db->pre}replies WHERE topic_id = '{$info['id']}' AND date {$sql_order} '{$mylast[0]}'");
	$new = $db->fetch_assoc($result);
	$tp = ($info['posts']+1) - $new['count'];
	$pgs = ceil($tp/$last['topiczahl']);
	if ($pgs < 1) {
		$pgs = 1;
	}
	sendStatusCode(302, 'showtopic.php?id='.$info['id'].'&page='.$pgs.SID2URL_JS_x.'#p'.$mylast[1]);
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
Breadcrumb::universal()->add($last['name'], "showforum.php?id=".$last['id'].SID2URL_x);
Breadcrumb::universal()->add($prefix.$info['topic']);

forum_opt($last);

// Some speed optimisation
$speeder = $info['posts']+1;
$start = ($_GET['page'] - 1) * $last['topiczahl'];
$temp = pages($speeder, $last['topiczahl'], "showtopic.php?id=".$info['id']."&amp;", $_GET['page']);

echo $tpl->parse("header");

($code = $plugins->load('showtopic_start')) ? eval($code) : null;

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
		$vresult = $db->query("
			SELECT u.id AS mid, u.name, v.aid
			FROM {$db->pre}votes AS v
				LEFT JOIN {$db->pre}user AS u ON u.id = v.mid
			WHERE v.aid IN({$sql_aid_in})
		");
		while ($row = $db->fetch_assoc($vresult)) {
			// Save the data for the member who is calling this page
			if ($row['mid'] == $my->id) {
				if ($config['vote_change'] != 1 || ($config['vote_change'] == 1 && $_GET['temp'] != 2)) {
					$vote['results'] = true;
				}
				$vote['voted'] = $row['aid'];
			}
			// Create element in array with name (+ member id as key) at the selected answer
			$vote['voter'][$row['aid']][$row['mid']] = $row['name'];
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
					if (mb_strstr($row['percent'], '.') > 0) {
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
	$result = $db->query("SELECT id, tid, mid, file, source FROM {$db->pre}uploads WHERE topic_id = '{$info['id']}'");
	$uploads = array();
	while ($row = $db->fetch_assoc($result)) {
		$uploads[$row['tid']][] = $row;
	}
}

// Speed up the first pages with less than 10 posts
if ($speeder > $last['topiczahl']) {
	$sql_limit = " LIMIT {$start},{$last['topiczahl']}";
}
else {
	$sql_limit = " LIMIT {$speeder}";
}
$sql_order = iif($last['post_order'] == 1, 'DESC', 'ASC');
($code = $plugins->load('showtopic_query')) ? eval($code) : null;
$result = $db->query("
SELECT
	r.id, r.edit, r.dosmileys, r.comment, r.date, r.report, r.tstart,
	u.id as mid, u.name, u.mail, u.regdate, u.posts, u.fullname, u.hp, u.signature, u.location, u.gender, u.birthday, u.pic, u.lastvisit, u.groups, u.deleted_at,
	f.*, IF (s.mid > 0, 1, 0) AS online
FROM {$db->pre}replies AS r
	LEFT JOIN {$db->pre}user AS u ON r.name = u.id
	LEFT JOIN {$db->pre}userfields AS f ON u.id = f.ufid
	LEFT JOIN {$db->pre}session AS s ON s.mid = u.id
WHERE r.topic_id = '{$info['id']}'
ORDER BY date {$sql_order}
{$sql_limit}
");

if ($last['post_order'] == 1) {
	list($firstnew_id) = $db->fetch_num($db->query("SELECT id FROM {$db->pre}replies WHERE topic_id = '{$info['id']}' AND date > '{$my->clv}'"));
}
$firstnew = 0;
$firstnew_url = null;
if ($info['last'] >= $my->clv) {
	$firstnew_url = 'showtopic.php?action=firstnew&amp;id='.$info['id'].SID2URL_x;
}

// Custom Profile Fields
include_once('classes/class.profilefields.php');
$pfields = new ProfileFieldViewer();
$rel_post_num = $start;
while ($row = $db->fetch_object($result)) {
	// Custom Profile Fields
	$pfields->setUserId($row->mid);
	$pfields->setUserData($row);

	// Uploads
	$inner['uploads'] = array();
	if (isset($uploads[$row->id]) && $config['tpcallow'] == 1) {
		foreach ($uploads[$row->id] as $file) {
			$file['path'] = 'uploads/topics/'.$file['source'];
			$file['size'] = formatFilesize(filesize($file['path']));
			$file['image'] = false;
			$file['imgwidth'] = null;
			$file['imgheight'] = null;

			$ext = get_extension($file['path']);
			if (in_array($ext, $imagetype_extension)) {
				$imagesize = getimagesize($file['path']);
				if ($imagesize !== false) {
					$file['image'] = true;
					$file['imgwidth'] = $imagesize[0];
					$file['imgheight'] = $imagesize[1];
				}
			}

			($code = $plugins->load('showtopic_attachments_prepared')) ? eval($code) : null;

			$inner['uploads'][$file['id']] = $tpl->parse("showtopic/upload_box");
		}
	}

	$rel_post_num++;
	$row->rel_post_num = $rel_post_num;

	if ($firstnew > 0) {
		$firstnew++;
	}
	if (($last['post_order'] == 0 && $row->date >= $my->clv && $firstnew == 0) ||
		($last['post_order'] == 1 && !empty($firstnew_id) && $row->id == $firstnew_id)) {
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
	$can_edit = ((($row->mid == $my->id && $edit_seconds >= $diff) || $my->mp[0] == 1) && $my->p['edit'] == 1 && $last['readonly'] == 0 && !($info['status'] != 0 && $my->mp[0] != 1));

	$row->unread = ($row->date >= $my->clv);

	BBProfile($bbcode);
	$bbcode->setSmileys($row->dosmileys);
	if ($info['status'] == 2) {
		$row->comment = $bbcode->replaceTextOnce($row->comment, 'moved');
	}
	$bbcode->addAttachments($inner['uploads']);
	$row->comment = $bbcode->parse($row->comment);

	if ($my->opt_showsig == 1) {
		BBProfile($bbcode, 'signature');
		$row->signature = $bbcode->parse($row->signature);
	}

	$row->level = $slog->getStatus($row->groups, ', ', $row->deleted_at !== null);
	if (empty($row->location)) {
		$row->location = $lang->phrase('showtopic_na');
	}

	if ((!empty($row->fullname) && $config['fullname_posts'] == 1) || (!empty($row->signature) && $my->opt_showsig == 1)) {
		$bottom = true;
	}
	else {
		$bottom = false;
	}

	$edit = array();
	if (!empty($row->edit)) {
		preg_match_all('~^([^\t]+)\t(\d+)\t([^\t]*)\t([\d\.]+)$~mu', $row->edit, $edits, PREG_SET_ORDER);
		BBProfile($bbcode);
		foreach ($edits as $e) {
			$edit[] = array(
				$e[1],
				gmdate($lang->phrase('datetime_format'), times($e[2])),
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

	($code = $plugins->load('showtopic_entry_prepared')) ? eval($code) : null;
	$inner['uploads'] = implode('', $inner['uploads']);
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

echo $tpl->parse("footer");
$slog->updatelogged();
$phpdoc->Out();