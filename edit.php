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

DEFINE('SCRIPTNAME', 'edit');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();

($code = $plugins->load('edit_post_query')) ? eval($code) : null;

$result = $db->query('
SELECT r.topic, r.board, r.name, r.comment, r.topic_id, r.dosmileys, r.dowords, t.posts, r.topic_id, r.date, t.prefix, r.id, r.edit, t.vquestion, r.tstart, t.status
FROM '.$db->pre.'replies AS r 
	LEFT JOIN '.$db->pre.'topics AS t ON r.topic_id = t.id 
WHERE r.id = "'.$_GET['id'].'" 
LIMIT 1
',__LINE__,__FILE__);

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
	error($lang->phrase('topic_closed'), 'showtopic.php?action=jumpto&id='.$info['topic_id'].'&topic_id='.$info['id']);
}

$diff = times()-$info['date'];
if ($config['edit_edit_time'] == 0) {
    $edit_seconds = $diff;
}
else {
    $edit_seconds = $config['edit_edit_time']*60;
}
$delete_seconds = $config['edit_delete_time']*60;
if ($my->mp[4] == 1 && ($info['topic_id'] > 0 || $info['posts'] == 0)) {
	$del_mod = TRUE;
}
else {
	$del_mod = FALSE; 
}
if ($delete_seconds >= $diff && ($info['topic_id'] > 0 || $info['posts'] == 0)) {
	$del_user = TRUE;
}
else {
	$del_user = FALSE; 
}
if ($config['tpcallow'] == 1 && $my->p['attachments'] == 1) { 
	$p_upload = TRUE;
}
else {
	$p_upload = FALSE;
}

$allowed = (($info['name'] == $my->id || $my->mp[0] == 1) && $my->p['edit'] && $my->vlogin && ($edit_seconds >= $diff || $my->mp[0] == 1)) ? true : false;

($code = $plugins->load('edit_start')) ? eval($code) : null;

if ($allowed == true) {
	
	if ($_GET['action'] == "save") {
		
		if ($_POST['temp'] == '1' && $my->mp[4] == '1') {
			if ($info['tstart'] == 0 || $info['posts'] == 0) {
				$db->query ("DELETE FROM {$db->pre}replies WHERE id = '{$info['id']}'",__LINE__,__FILE__);
				$uresult = $db->query ("SELECT source FROM {$db->pre}uploads WHERE tid = '{$info['id']}'",__LINE__,__FILE__);
				while ($urow = $db->fetch_num($uresult)) {
				    $filesystem->unlink('uploads/topics/'.$urow[0]);
				}
				$db->query ("DELETE FROM {$db->pre}uploads WHERE tid = '{$info['id']}'",__LINE__,__FILE__);
				$db->query ("DELETE FROM {$db->pre}postratings WHERE pid = '{$info['id']}'",__LINE__,__FILE__);
				if ($info['tstart'] == 1) {
					$db->query ("DELETE FROM {$db->pre}abos WHERE tid = '{$info['topic_id']}'",__LINE__,__FILE__);
					$db->query ("DELETE FROM {$db->pre}topics WHERE id = '{$info['topic_id']}'",__LINE__,__FILE__);
					$votes = $db->query("SELECT id FROM {$db->pre}vote WHERE tid = '{$info['id']}'",__LINE__,__FILE__);
					$voteaids = array();
					while ($row = $db->fetch_num($votes)) {
						$voteaids[] = $row[0];
					}
					if (count($voteaids) > 0) {
						$db->query ("DELETE FROM {$db->pre}votes WHERE id IN (".implode(',', $voteaids).")",__LINE__,__FILE__);
					}
					$db->query ("DELETE FROM {$db->pre}vote WHERE tid = '{$info['id']}'",__LINE__,__FILE__);
				}
				($code = $plugins->load('edit_save_delete')) ? eval($code) : null;
				UpdateBoardStats($info['board']);
				UpdateTopicStats($info['topic_id']);
				if ($info['tstart'] == 1) {
					$url = "showforum.php?id=".$info['board'];
				}
				else {
					$url = "showtopic.php?action=last&id=".$info['topic_id'];
				}
				ok($lang->phrase('edit_postdeleted'),$url.SID2URL_x);
			}
			else {
				error($lang->phrase('threadstarts_no_delete'),"edit.php?id=".$info['id']);
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
					viscacha_header("Location: edit.php?action=preview&id={$info['id']}&fid=".$fid.SID2URL_JS_x);
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
				",__LINE__,__FILE__);
				
				if ($info['tstart'] == '1') {
				
					$db->query ("
					UPDATE {$db->pre}topics 
					SET prefix = '{$_POST['opt_0']}', topic = '{$_POST['topic']}' 
					WHERE id = '{$info['topic_id']}'
					",__LINE__,__FILE__);
					
				}
				ok($lang->phrase('data_success'),'showtopic.php?action=jumpto&id='.$info['topic_id'].'&topic_id='.$info['id']);
			}
		}
	}
	else {
		echo $tpl->parse("menu");

		BBProfile($bbcode);

		($code = $plugins->load('edit_form_start')) ? eval($code) : null;

		if (strlen($_GET['fid']) == 32) {
			$data = $gpc->prepare(import_error_data($_GET['fid']));
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

		$inner['smileys'] = $bbcode->getsmileyhtml($config['smileysperrow']);
		$inner['bbhtml'] = $bbcode->getbbhtml();

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
