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

DEFINE('SCRIPTNAME', 'managetopic');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();

$result = $db->query('SELECT board, mark, id, last_name, prefix, topic FROM '.$db->pre.'topics WHERE id = "'.$_GET['id'].'" LIMIT 1',__LINE__,__FILE__);
if ($db->num_rows($result) != 1) {
	error(array($lang->phrase('query_string_error')));
}
$info = $db->fetch_assoc($result);
$info['last_name'] = $gpc->prepare($info['last_name']);

$my->p = $slog->Permissions($info['board']);
$my->mp = $slog->ModPermissions($info['board']);

// preparing data for breadcrumb
$fc = cache_cat_bid();
$last = $fc[$info['board']];
$topforums = get_headboards($fc, $last, TRUE);
$pre = '';
if ($info['prefix'] > 0) {
	$prefix = cache_prefix($info['board']);
	if (isset($prefix[$info['prefix']])) {
		$pre = $prefix[$info['prefix']];
		$pre = $lang->phrase('showtopic_prefix_title');
	}
}
$breadcrumb->Add($last['name'], "showforum.php?id=".$last['id'].SID2URL_x);
$breadcrumb->Add($pre.$info['topic'], "showtopic.php?id=".$info['id'].SID2URL_x);

$breadcrumb->Add($lang->phrase('teamcp'));

echo $tpl->parse("header");

$fc = cache_cat_bid();
$last = $fc[$info['board']];
forum_opt($last['opt'], $last['optvalue'], $last['id']);

if ($my->vlogin && $my->mp[0] == 1) { 
	if ($_GET['action'] == "delete") {
	    if ($my->mp[0] == 1 && $my->mp[4] == 0) {
	    	errorLogin($lang->phrase('not_allowed'), 'showtopic.php?id='.$info['id'].SID2URL_x);
	    }
	    echo $tpl->parse("menu");
	    echo $tpl->parse("admin/topic/delete");
	}
	elseif ($_GET['action'] == "delete2") {
	    if ($my->mp[0] == 1 && $my->mp[4] == 0) {
	    	errorLogin($lang->phrase('not_allowed'), 'showtopic.php?id='.$info['id'].SID2URL_x);
	    }
		$db->query ("DELETE FROM {$db->pre}replies WHERE topic_id = '{$info['id']}'",__LINE__,__FILE__);
		$anz = $db->affected_rows();
		$uresult = $db->query ("SELECT file FROM {$db->pre}uploads WHERE topic_id = '{$info['id']}'",__LINE__,__FILE__);
		while ($urow = $db->fetch_array($uresult)) {
		    @unlink('uploads/topics/'.$urow[0]);
		    if (file_exists('uploads/topics/thumbnails/'.$urow[0])) {
		    	@unlink('uploads/topics/thumbnails/'.$urow[0]);
		    }
		}
		$db->query ("DELETE FROM {$db->pre}uploads WHERE topic_id = '{$info['id']}'",__LINE__,__FILE__);
		$anz += $db->affected_rows();
		$db->query ("DELETE FROM {$db->pre}abos WHERE tid = '{$info['id']}'",__LINE__,__FILE__);
		$anz += $db->affected_rows();
		$db->query ("DELETE FROM {$db->pre}fav WHERE tid = '{$info['id']}'",__LINE__,__FILE__);
		$anz += $db->affected_rows();
		$db->query ("DELETE FROM {$db->pre}topics WHERE id = '{$info['id']}'",__LINE__,__FILE__);
		$anz += $db->affected_rows();
		$votes = $db->query("SELECT id FROM {$db->pre}vote WHERE tid = '{$info['id']}'",__LINE__,__FILE__);
		$voteaids = array();
		while ($row = $db->fetch_array($votes)) {
			$voteaids[] = $row[0];
		}
		if (count($voteaids) > 0) {
			$db->query ("DELETE FROM {$db->pre}votes WHERE id IN (".implode(',', $voteaids).")",__LINE__,__FILE__);
			$anz += $db->affected_rows();
		}
		$db->query ("DELETE FROM {$db->pre}vote WHERE id = '{$info['id']}'",__LINE__,__FILE__);
		$anz += $db->affected_rows();
		
		UpdateBoardStats($info['board']);
		ok($lang->phrase('x_entries_deleted'),"showforum.php?id=".$info['board'].SID2URL_x);
	}
	elseif ($_GET['action'] == "move") {
		$my->pb = $slog->GlobalPermissions();
	    if ($my->mp[0] == 1 && $my->mp[5] == 0) {
	    	errorLogin($lang->phrase('not_allowed'), 'showtopic.php?id='.$info['id'].SID2URL_x);
	    }
		$forums = BoardSubs();
		echo $tpl->parse("menu");
		echo $tpl->parse("admin/topic/move");  
	}
	elseif ($_GET['action'] == "move2") {		
	    if ($my->mp[0] == 1 && $my->mp[5] == 0) {
	    	errorLogin($lang->phrase('not_allowed'), 'showtopic.php?id='.$info['id'].SID2URL_x);
	    }
	    
	    $result = $db->query("SELECT r.date, r.topic, r.name, r.email, u.name AS uname, u.mail AS uemail FROM {$db->pre}replies AS r LEFT JOIN {$db->pre}user AS u ON u.id = r.name WHERE topic_id = '{$info['id']}' AND tstart = '1'",__LINE__,__FILE__);
	    $old = $db->fetch_assoc($result);
	    
	    $board = $gpc->get('board', int);
	    
	    $db->query("UPDATE {$db->pre}topics SET board = '{$board}' WHERE id = '{$info['id']}' LIMIT 1",__LINE__,__FILE__); 	    
	    $anz = $db->affected_rows();
	    $db->query("UPDATE {$db->pre}replies SET board = '{$board}' WHERE topic_id = '{$info['id']}'",__LINE__,__FILE__); 
		$anz += $db->affected_rows();
		
		if ($_POST['temp'] == 1) {
	    	$db->query("INSERT INTO {$db->pre}topics SET status = '2', topic = '{$old['topic']}', board='{$info['board']}', name = '{$old['name']}', date = '{$old['date']}', last_name = '{$info['last_name']}', prefix = '{$info['prefix']}', last = '{$old['date']}'",__LINE__,__FILE__);	
	    	$tid = $db->insert_id();
	    	$db->query("INSERT INTO {$db->pre}replies SET tstart = '1', topic_id = '{$tid}', comment = '{$info['id']}', topic = '{$old['topic']}', board='{$info['board']}', name = '{$old['name']}', email = '{$old['email']}', date = '{$old['date']}'",__LINE__,__FILE__);	
		}
	    if ($_POST['temp2'] == 1) {
	    	if (empty($old['email'])) {
	    		$old['email'] = $old['uemail'];
	    		$old['name'] = $old['uname'];
	    	}
		    $data = $lang->get_mail('topic_moved');
			$to = array('0' => array('name' => $old['name'], 'mail' => $old['email']));
			$from = array();
			xmail($to, $from, $data['title'], $data['comment']);
	    }
	    UpdateBoardStats($info['board']);
	    UpdateBoardStats($board);
	    ok($lang->phrase('x_entries_moved'),'showtopic.php?id='.$info['id']);
	}
	
	elseif ($_GET['action'] == "status") {
	    if ($my->mp[0] == 1 && $my->mp[1] == 0 && $my->mp[2] == 0 && $my->mp[3] == 0) {
	    	errorLogin($lang->phrase('not_allowed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	    }
	    echo $tpl->parse("menu");
	    echo $tpl->parse("admin/topic/status");
	}
	elseif ($_GET['action'] == "status2") {
		$input = '';
		$notallowed = FALSE;
	    if ($my->mp[0] == 1 && $my->mp[1] == 0 && $my->mp[2] == 0 && $my->mp[3] == 0) {
	    	$notallowed = TRUE;
	    }  
	    if ($_POST['temp'] == '1') {
			if ($my->mp[1] == 1) {
		    	$input = 'g';
		    }
		    else {
		    	$notallowed = TRUE;
		    }
	    }
	    if ($_POST['temp'] == '2') {
			if ($my->mp[1] == 1) {
		    	$input = 'b';
		    }
		    else {
		    	$notallowed = TRUE;
		    }
	    }
	    if ($_POST['temp'] == '3') {
			if ($my->mp[3] == 1) {
		    	$input = 'a';
		    }
		    else {
		    	$notallowed = TRUE;
		    }
	    }
	    if ($_POST['temp'] == '4') {
			if ($my->mp[2] == 1) {
		    	$input = 'n';
		    }
		    else {
		    	$notallowed = TRUE;
		    }
	    }
	    if ($notallowed) {
	    	errorLogin($lang->phrase('not_allowed'), 'showtopic.php?id='.$info['id'].SID2URL_x);
	    }
	    $db->query("UPDATE {$db->pre}topics SET mark = '".$input."' WHERE id = '".$info['id']."'",__LINE__,__FILE__);	
	    if ($db->affected_rows() == 1) {
	        ok($lang->phrase('admin_topicstatus_changed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	    }
	    else {
	        error($lang->phrase('admin_failed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	    }
	}
	elseif ($_GET['action'] == "pin") {	
	    $db->query("UPDATE {$db->pre}topics SET sticky = '1' WHERE id = '".$info['id']."'",__LINE__,__FILE__);	
	    if ($db->affected_rows() == 1) {
	        ok($lang->phrase('admin_topicstatus_changed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	    }
	    else {
	        error($lang->phrase('admin_failed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	    }
	}
	elseif ($_GET['action'] == "unpin") {
	    $db->query("UPDATE {$db->pre}topics SET sticky = '0' WHERE id = '".$info['id']."'",__LINE__,__FILE__);	
	    if ($db->affected_rows() == 1) {
	        ok($lang->phrase('admin_topicstatus_changed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	    }
	    else {
	        error($lang->phrase('admin_failed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	    }   
	}
	elseif ($_GET['action'] == "close") {
	    $db->query("UPDATE {$db->pre}topics SET status = '1' WHERE id = '".$info['id']."'",__LINE__,__FILE__);	
	    if ($db->affected_rows() == 1) {
	        ok($lang->phrase('admin_topicstatus_changed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	    }
	    else {
	        error($lang->phrase('admin_failed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	    }
	}
	elseif ($_GET['action'] == "open") {
	    $db->query("UPDATE {$db->pre}topics SET status = '0' WHERE id = '".$info['id']."'",__LINE__,__FILE__);	
	    if ($db->affected_rows() == 1) {
	        ok($lang->phrase('admin_topicstatus_changed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	    }
	    else {
	        error($lang->phrase('admin_failed'),'showtopic.php?id='.$info['id'].SID2URL_x);
	    }      
	}
	elseif ($_GET['action'] == "stat") {
	    UpdateTopicStats($info['id']);
	    ok($lang->phrase('data_success'),'showtopic.php?id='.$info['id'].SID2URL_x);
	}
	elseif ($_GET['action'] == "vote_export") {
		require_once("classes/class.charts.php");
		$PG = new PowerGraphic();
		
		$skin = $gpc->get('skin', int, 1);
		$modus = $gpc->get('modus', int, 1);

	    echo $tpl->parse("menu");
	    echo $tpl->parse("admin/topic/vote_export");
	}
	elseif ($_GET['action'] == "vote_edit") {
		$error = array();

		$result = $db->query('SELECT id, topic, posts, sticky, status, last, board, vquestion, prefix FROM '.$db->pre.'topics WHERE id = '.$_GET['id'].' LIMIT 1',__LINE__,__FILE__);
		$info = $gpc->prepare($db->fetch_assoc($result));

		$result = $db->query("SELECT id, answer FROM {$db->pre}vote WHERE tid = '{$info['id']}' ORDER BY id",__LINE__,__FILE__);
		
	    echo $tpl->parse("menu");
	    echo $tpl->parse("admin/topic/vote_edit");
	}
	elseif ($_GET['action'] == "vote_edit2") {
		$error = array();
		if (strxlen($_POST['question']) > $config['maxtitlelength']) {
			$error[] = $lang->phrase('question_too_long');
		}
		if (strxlen($_POST['question']) < $config['mintitlelength']) {
			$error[] = $lang->phrase('question_too_short');
		}
		if (count_filled($_POST['notice']) < 2) {
			$error[] = $lang->phrase('min_replies_vote');
		}
		if (count_filled($_POST['notice']) > 50) {
			$error[] = $lang->phrase('max_replies_vote');
		}
		if (count($error) > 0) {
			error($error,'managetopic.php?action=vote_edit&amp;id='.$_GET['id'].SID2URL_x);
		}
		else {
			$db->query("UPDATE {$db->pre}topics SET vquestion = '{$_POST['question']}' WHERE id = '{$_GET['id']}' LIMIT 1",__LINE__,__FILE__);
			$result = $db->query("SELECT id, answer FROM {$db->pre}vote WHERE tid = '{$info['id']}' ORDER BY id",__LINE__,__FILE__);
			while($row = $db->fetch_assoc($result)) {
				if (!empty($_POST['notice'][$row['id']]) && strlen($_POST['notice'][$row['id']]) < 255) {
					$db->query("UPDATE {$db->pre}vote SET answer = '{$_POST['notice'][$row['id']]}' WHERE id = '{$row['id']}'",__LINE__,__FILE__);
				}
			}
			if (!empty($_POST['notice'][0]) && strlen($_POST['notice'][0]) < 255) {
				$db->query("INSERT INTO {$db->pre}vote (tid, answer) VALUES ('{$_GET['id']}','{$_POST['notice'][0]}')",__LINE__,__FILE__);
			}
			ok($lang->phrase('data_success'),"showtopic.php?id={$_GET['id']}");
		}
	}
}
else {
    errorLogin($lang->phrase('not_allowed'));
}

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();	
?>
