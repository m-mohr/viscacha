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

define('SCRIPTNAME', 'edit');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

($code = $plugins->load('edit_post_query')) ? eval($code) : null;

$result = $db->query('
SELECT r.topic, r.board, r.name, r.comment, r.topic_id, r.dosmileys, r.dowords, t.posts, r.topic_id, r.date, t.prefix, r.id, r.edit, t.vquestion, r.tstart, t.status, r.guest
FROM '.$db->pre.'replies AS r
	LEFT JOIN '.$db->pre.'topics AS t ON r.topic_id = t.id
WHERE r.id = "'.$_GET['id'].'"
LIMIT 1
');

if ($db->num_rows($result) != 1) {
	error(array($lang->phrase('query_string_error')));
}
$info = $gpc->prepare($db->fetch_assoc($result));

$my->p = $slog->Permissions($info['board']);
$my->mp = $slog->ModPermissions($info['board']);

$cat_bid_obj = $scache->load('cat_bid');
$fc = $cat_bid_obj->get();
$last = $fc[$info['board']];
forum_opt($last, 'edit');

$prefix_obj = $scache->load('prefix');
$prefix_arr = $prefix_obj->get($info['board']);

$prefix = '';
if ($info['prefix'] > 0) {
	if (isset($prefix_arr[$info['prefix']])) {
		$prefix = $prefix_arr[$info['prefix']]['value'];
		$prefix = $lang->phrase('showtopic_prefix_title');
	}
}
get_headboards($fc, $last);
$breadcrumb->Add($last['name'], "showforum.php?id=".$last['id'].SID2URL_x);
$breadcrumb->Add($prefix.$info['topic'], 'showtopic.php?id='.$info['topic_id'].SID2URL_x);
$breadcrumb->Add($lang->phrase('edit'));
echo $tpl->parse("header");

if ($info['status'] != 0) {
	error($lang->phrase('topic_closed'), 'showtopic.php?action=jumpto&id='.$info['topic_id'].'&topic_id='.$info['id'].SID2URL_x);
}

$diff = time()-$info['date'];
if ($config['edit_edit_time'] == 0) {
    $edit_seconds = $diff;
}
else {
    $edit_seconds = $config['edit_edit_time']*60;
}
$delete_seconds = $config['edit_delete_time']*60;

$del_mod = ($my->mp[4] == 1 && ($info['topic_id'] > 0 || $info['posts'] == 0));
$del_user = ($delete_seconds >= $diff && ($info['topic_id'] > 0 || $info['posts'] == 0));
$p_upload = ($config['tpcallow'] == 1 && $my->p['attachments'] == 1);

$allowed = ((($info['name'] == $my->id && $info['guest'] == 0 && $edit_seconds >= $diff) || $my->mp[0] == 1) && $my->p['edit'] == 1 && $last['readonly'] == 0 && $info['status'] == 0);

($code = $plugins->load('edit_start')) ? eval($code) : null;

if ($allowed == true) {

	if ($_GET['action'] == "save") {

		if ($_POST['temp'] == '1' && $my->mp[4] == '1') {
			if ($info['tstart'] == 0 || $info['posts'] == 0) {
				if ($config['updatepostcounter'] == 1 && $last['count_posts'] == 1) {
					if ($info['tstart'] == 1) {
						$result = $db->query("SELECT COUNT(*) AS posts, name FROM {$db->pre}replies WHERE guest = '0' AND topic_id = '{$info['id']}' GROUP BY name");
						while ($row = $db->fetch_assoc($result)) {
							$db->query("UPDATE {$db->pre}user SET posts = posts-{$row['posts']} WHERE id = '{$row['name']}'");
						}
					}
					else {
						if ($info['guest'] == 0 && $last['count_posts'] == 1) {
							$db->query("UPDATE {$db->pre}user SET posts = posts-1 WHERE id = '{$info['name']}'");
						}
					}
				}
				$db->query ("DELETE FROM {$db->pre}replies WHERE id = '{$info['id']}'");
				$uresult = $db->query ("SELECT source FROM {$db->pre}uploads WHERE tid = '{$info['id']}'");
				while ($urow = $db->fetch_num($uresult)) {
				    $filesystem->unlink('uploads/topics/'.$urow[0]);
				}
				$db->query ("DELETE FROM {$db->pre}uploads WHERE tid = '{$info['id']}'");
				$db->query ("DELETE FROM {$db->pre}postratings WHERE pid = '{$info['id']}'");
				if ($info['tstart'] == 1) {
					$db->query ("DELETE FROM {$db->pre}abos WHERE tid = '{$info['topic_id']}'");
					$db->query ("DELETE FROM {$db->pre}topics WHERE id = '{$info['topic_id']}'");
					$votes = $db->query("SELECT id FROM {$db->pre}vote WHERE tid = '{$info['id']}'");
					$voteaids = array();
					while ($row = $db->fetch_num($votes)) {
						$voteaids[] = $row[0];
					}
					if (count($voteaids) > 0) {
						$db->query ("DELETE FROM {$db->pre}votes WHERE id IN (".implode(',', $voteaids).")");
					}
					$db->query ("DELETE FROM {$db->pre}vote WHERE tid = '{$info['id']}'");
				}
				($code = $plugins->load('edit_save_delete')) ? eval($code) : null;
				if ($config['updateboardstats'] == 1) {
					UpdateBoardStats($info['board']);
				}
				else {
					UpdateBoardLastStats($info['board']);
				}
				UpdateTopicStats($info['topic_id']);

				ok($lang->phrase('edit_postdeleted'),iif($info['tstart'] == 1, "showforum.php?id=".$info['board'], "showtopic.php?action=last&id=".$info['topic_id']).SID2URL_x);
			}
			else {
				error($lang->phrase('threadstarts_no_delete'),"edit.php?id=".$info['id'].SID2URL_x);
			}
		}
		else {
			$error = array();
			if (strxlen($_POST['comment']) > $config['maxpostlength']) {
				$error[] = $lang->phrase('comment_too_long');
			}
			if (strxlen($_POST['comment']) < $config['minpostlength']) {
				$error[] = $lang->phrase('comment_too_short');
			}
			if (strxlen($_POST['topic']) > $config['maxtitlelength']) {
				$error[] = $lang->phrase('title_too_long');
			}
			if (strxlen($_POST['topic']) < $config['mintitlelength']) {
				$error[] = $lang->phrase('title_too_short');
			}
			if (strxlen($_POST['about']) > $config['maxeditlength']) {
				$error[] = $lang->phrase('edit_reason_too_long');
			}
			if (strxlen($_POST['about']) < $config['mineditlength']) {
				$error[] = $lang->phrase('edit_reason_too_short');
			}
			if (!isset($prefix[$_POST['opt_0']]) && $last['prefix'] == 1) {
				$error[] = $lang->phrase('prefix_not_optional');
			}
			($code = $plugins->load('edit_save_errorhandling')) ? eval($code) : null;

			BBProfile($bbcode);
			$_POST['topic'] = $bbcode->parseTitle($_POST['topic']);

			if (count($error) > 0 || !empty($_POST['Preview'])) {
				$data = array(
					'topic' => $_POST['topic'],
					'comment' => $_POST['comment'],
					'prefix' => $_POST['opt_0'],
					'dosmileys' => $_POST['dosmileys'],
					'dowords' => $_POST['dowords'],
					'about' => $_POST['about']
				);
				($code = $plugins->load('edit_save_errordata')) ? eval($code) : null;
				$fid = save_error_data($data);
				if (!empty($_POST['Preview'])) {
					$slog->updatelogged();
					$db->close();
					sendStatusCode(307, $config['furl']."/edit.php?action=preview&id={$info['id']}&fid=".$fid.SID2URL_JS_x);
					exit;
				}
				else {
					error($error,"edit.php?id={$info['id']}&amp;fid=".$fid.SID2URL_x);
				}
			}
			else {
				$info['edit'] .= $my->name."\t".time()."\t".$_POST['about']."\t".$my->ip."\n";
				($code = $plugins->load('edit_save_queries')) ? eval($code) : null;

				$db->query ("
				UPDATE {$db->pre}replies
				SET edit = '{$info['edit']}', topic = '{$_POST['topic']}', comment = '{$_POST['comment']}', dosmileys = '{$_POST['dosmileys']}', dowords = '{$_POST['dowords']}'
				WHERE id = '{$_GET['id']}'
				");

				if ($info['tstart'] == '1') {

					$db->query ("
					UPDATE {$db->pre}topics
					SET prefix = '{$_POST['opt_0']}', topic = '{$_POST['topic']}'
					WHERE id = '{$info['topic_id']}'
					");

				}
				ok($lang->phrase('data_success'),'showtopic.php?action=jumpto&id='.$info['topic_id'].'&topic_id='.$info['id'].SID2URL_x);
			}
		}
	}
	else {
		echo $tpl->parse("menu");

		BBProfile($bbcode);

		($code = $plugins->load('edit_form_start')) ? eval($code) : null;

		$fid = $gpc->get('fid', str);
		if (is_hash($fid)) {
			$data = $gpc->unescape(import_error_data($fid));
			if ($_GET['action'] == 'preview') {
				$bbcode->setSmileys($data['dosmileys']);
				if ($config['wordstatus'] == 0) {
					$dowords = 0;
				}
				else {
					$dowords = $data['dowords'];
				}
				$bbcode->setReplace($dowords);
				$data['formatted_comment'] = $bbcode->parse($data['comment']);
				$data['formatted_prefix'] = '';
				if (isset($prefix_arr[$data['prefix']])) {
					$data['formatted_prefix'] = $prefix_arr[$data['prefix']]['value'];
				}
			}
		}
		else {
			$data = array(
				'topic' => $info['topic'],
				'comment' => $info['comment'],
				'prefix' => $info['prefix'],
				'dosmileys' => $info['dosmileys'],
				'dowords' => $info['dowords'],
				'about' => ''
			);
		}

		if (count($prefix_arr) > 0 && $info['tstart'] == 1) {
			array_columnsort($prefix_arr, "value");
			if ($last['prefix'] == 0) {
				$prefix_arr = array($lang->phrase('prefix_empty')) + $prefix_arr;
			}
			$sel = $data['prefix'];
			$inner['index_prefix'] = $tpl->parse("edit/prefix");
		}
		else {
			$inner['index_prefix'] = '';
		}

		($code = $plugins->load('edit_form_prepared')) ? eval($code) : null;

		echo $tpl->parse("edit/edit");

		($code = $plugins->load('edit_form_end')) ? eval($code) : null;
	}
}
else {
	if ($edit_seconds < $diff) {
		errorLogin($lang->phrase('not_allowed_time_exceed'));
	}
	else {
		errorLogin();
	}
}
($code = $plugins->load('edit_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();
?>