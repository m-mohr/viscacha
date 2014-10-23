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

DEFINE('SCRIPTNAME', 'newtopic');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$board = $gpc->get('id', int);

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();
$my->p = $slog->Permissions($board);

$catbid = $scache->load('cat_bid');
$fc = $catbid->get();
if (empty($board) || !isset($fc[$board])) {
	error($lang->phrase('query_string_error'));
}
$last = $fc[$board];
forum_opt($last, 'posttopics');

if ($config['tpcallow'] == 1 && $my->p['attachments'] == 1) { 
	$p_upload = 1;
}
else {
	$p_upload = 0;
}

get_headboards($fc, $last);
$breadcrumb->Add($last['name'], "showforum.php?id=".$last['id'].SID2URL_x);
$breadcrumb->Add($lang->phrase('newtopic_title'));

($code = $plugins->load('newtopic_start')) ? eval($code) : null;

if ($_GET['action'] == "startvote") {

	$my->mp = $slog->ModPermissions($board);

	if (empty($_GET['temp'])) {
		$_GET['temp'] = $_POST['temp'];
	}
	if ($_GET['temp'] < 1) {
		$_GET['temp'] = 2;
	}

	($code = $plugins->load('newtopic_startvote_start')) ? eval($code) : null;
	
	$result = $db->query("
	SELECT id, vquestion, name 
	FROM {$db->pre}topics 
	WHERE id = '{$_GET['topic_id']}' 
	LIMIT 1
	", __LINE__, __FILE__);
	
	$info = $db->fetch_assoc($result);
	$error = array();
	if ($my->p['addvotes'] == 0 || !empty($info['vquestion']) || ($info['name'] != $my->id && $my->mp[0] == 0)) {
		$error[] = $lang->phrase('not_allowed');
	}
	if ($db->num_rows() != 1) {
		$error[] = $lang->phrase('query_string_error');
	}
	if (count($error) > 0) {
		errorLogin($error,"showforum.php?id=".$board.SID2URL_x);
	}

	$error = array();
	if ($_GET['temp'] < 2) {
		$error[] = $lang->phrase('min_replies_vote');
	}
	if ($_GET['temp'] > 50) {
		$error[] = $lang->phrase('max_replies_vote');
	}

	if (strlen($_GET['fid']) == 32) {
		$data = $gpc->prepare(import_error_data($_GET['fid']));
	}
	else {
		$data = array_fill(1, $_GET['temp'], '');
		$data['question'] = '';
	}

	$breadcrumb->Add($lang->phrase('add_vote_to_thread'));

	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	($code = $plugins->load('newtopic_startvote_prepared')) ? eval($code) : null;
	echo $tpl->parse("newtopic/startvote");
	($code = $plugins->load('newtopic_startvote_end')) ? eval($code) : null;

}
elseif ($_GET['action'] == "savevote") {
	
	$result = $db->query('SELECT id, vquestion FROM '.$db->pre.'topics WHERE id = "'.$_GET['topic_id'].'" LIMIT 1');
	$info = $db->fetch_assoc($result);
	$error = array();
	if ($my->p['addvotes'] == 0 || !empty($info['vquestion'])) {
		$error[] = $lang->phrase('not_allowed');
	}
	if ($db->num_rows() != 1) {
		$error[] = $lang->phrase('query_string_error');
	}
	if (count($error) > 0) {
		errorLogin($error,"showforum.php?id=".$board.SID2URL_x);
	}
	
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
	($code = $plugins->load('newtopic_savevote_errorhandling')) ? eval($code) : null;
	
	if (count($error) > 0) {
		$_POST['notice']['question'] = $_POST['question'];
		($code = $plugins->load('newtopic_savevote_errordata')) ? eval($code) : null;
		$fid = save_error_data($_POST['notice']);
		error($error,"newtopic.php?action=startvote&amp;id={$board}&amp;topic_id={$_GET['topic_id']}&amp;temp={$_GET['temp']}&amp;fid=".$fid.SID2URL_x);
	}
	else {
		$sqlwhere = array();
		foreach ($_POST['notice'] as $uval) {
			if (!empty($uval) && strlen($uval) < 255) {
				array_push($sqlwhere, "({$_GET['topic_id']}, '{$uval}')");
			}
		}
		$sqlwhere = implode(", ",$sqlwhere);

		($code = $plugins->load('newtopic_savevote_queries')) ? eval($code) : null;

		$db->query("UPDATE {$db->pre}topics SET vquestion = '{$_POST['question']}' WHERE id = '{$info['id']}'",__LINE__,__FILE__);
		$db->query("INSERT INTO {$db->pre}vote (tid, answer) VALUES {$sqlwhere}",__LINE__,__FILE__);
		$inserted = $db->affected_rows();
		if ($inserted > 1) {
			ok($lang->phrase('data_success'),"showtopic.php?id={$_GET['topic_id']}");
		}
		else {
			$db->query("UPDATE {$db->pre}topics SET vquestion = '' WHERE id = '{$_GET['topic_id']}'",__LINE__,__FILE__);
			error($lang->phrase('add_vote_failed'),"showtopic.php?id={$_GET['topic_id']}");
		}
	}
}
elseif ($_GET['action'] == "save") {

	$error = array();

	if (!$my->vlogin) {
		if ($config['botgfxtest_posts'] == 1) {
			include("classes/graphic/class.veriword.php");
			$vword = new VeriWord();
		    if($_POST['letter']) {
		        if ($vword->check_session($_POST['captcha'], $_POST['letter']) == FALSE) {
		        	$error[] = $lang->phrase('veriword_mistake');
		        }
		    }
		    else {
		        $error[] = $lang->phrase('veriword_failed');
		    }
		}
		if (!check_mail($_POST['email']) && ($config['guest_email_optional'] == 0 || !empty($_POST['email']))) {
			$error[] = $lang->phrase('illegal_mail');
		}
		if (double_udata('name',$_POST['name']) == false) {
			$error[] = $lang->phrase('username_registered');
		}
		if (is_id($_POST['name'])) {
			$error[] = $lang->phrase('username_registered');
		}
		if (strxlen($_POST['name']) > $config['maxnamelength']) {
			$error[] = $lang->phrase('name_too_long');
		}
		if (strxlen($_POST['name']) < $config['minnamelength']) {
			$error[] = $lang->phrase('name_too_short');
		}
		if (strxlen($_POST['email']) > 200) {
			$error[] = $lang->phrase('email_too_long');
		}
		$pname = $_POST['name'];
		$pid = 0;
		$pnameid = $_POST['name'];
	}
	else {
		$pname = $my->name;
		$pid = $my->id;
		$pnameid = $my->id;
	}
	if (flood_protect() == FALSE) {
		$error[] = $lang->phrase('flood_control');
	}
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
	
	$prefix_obj = $scache->load('prefix');
	$prefix_arr = $prefix_obj->get($board);
	
	if (!isset($prefix_arr[$_POST['opt_0']]) && $last['prefix'] == 1) {
		$error[] = $lang->phrase('prefix_not_optional');
	}

	BBProfile($bbcode);
	$_POST['topic'] = $bbcode->parseTitle($_POST['topic']);

	($code = $plugins->load('newtopic_save_errorhandling')) ? eval($code) : null;

	if (count($error) > 0 || !empty($_POST['Preview'])) {
		$data = array(
			'topic' => $_POST['topic'],
			'comment' => $_POST['comment'],
			'prefix' => $_POST['opt_0'],
			'dosmileys' => $_POST['dosmileys'],
			'dowords' => $_POST['dowords'],
			'vote' => $_POST['opt_2'],
			'replies' => $_POST['temp'],
			'guest' => 1
		);
		if (!$my->vlogin) {
			if ($config['guest_email_optional'] == 0 && empty($_POST['email'])) {
				$data['email'] = '';
			}
			else {
				$data['email'] = $_POST['email'];
			}
			$data['name'] = $_POST['name'];
			$data['guest'] = 1;
		}
		($code = $plugins->load('newtopic_save_errordata')) ? eval($code) : null;
		$fid = save_error_data($data);
		if (!empty($_POST['Preview'])) {
			viscacha_header("Location: newtopic.php?action=preview&id={$board}&fid=".$fid.SID2URL_JS_x);
		}
		else {
			error($error,"newtopic.php?id={$board}&amp;fid=".$fid.SID2URL_x);
		}
	}
	else {
		set_flood();

		$date = time();

		if ($my->vlogin) {
			$guest = 0;
		}
		else {
			$guest = 1;
		}

		($code = $plugins->load('newtopic_save_savedata')) ? eval($code) : null;
		
		$db->query("
		INSERT INTO {$db->pre}topics (board,topic,name,date,last,last_name,prefix) 
		VALUES ('{$board}','{$_POST['topic']}','{$pnameid}','{$date}','{$date}','{$pnameid}','{$_POST['opt_0']}')
		",__LINE__,__FILE__); 
		$tredirect = $db->insert_id();
		
		$db->query("
		INSERT INTO {$db->pre}replies (board,topic,topic_id,name,comment,dosmileys,dowords,email,date,tstart,ip,guest) 
		VALUES ('{$board}','{$_POST['topic']}','{$tredirect}','{$pnameid}','{$_POST['comment']}','{$_POST['dosmileys']}','{$_POST['dowords']}','{$_POST['email']}','{$date}','1','{$my->ip}','{$guest}')
		",__LINE__,__FILE__);
		$rredirect = $db->insert_id();

		$db->query("UPDATE {$db->pre}uploads SET topic_id = '{$tredirect}', tid = '{$rredirect}' WHERE mid = '{$pid}' AND topic_id = '0' AND tid = '0'",__LINE__,__FILE__);

		if ($_POST['page'] && $my->vlogin) {
			$type = NULL;
			if ($_POST['page'] == '1') {
				$type='';
			}
			elseif ($_POST['page'] == '2') {
				$type='d';
			}
			elseif ($_POST['page'] == '3') {
				$type='w';
			}
			if ($type != NULL) {
				$db->query("INSERT INTO {$db->pre}abos (mid,tid,type) VALUES ('{$my->id}','{$tredirect}','{$type}')",__LINE__,__FILE__);
			}
		}
		
		$close = $gpc->get('close', int);
		$pin = $gpc->get('pin', int);
		$stat = $gpc->get('status', int);
		if (($close == 1 || $pin == 1 || $stat > 0) && $my->vlogin) {
			$my->mp = $slog->ModPermissions($board);
			if ($close == 1 && $my->mp[0] == 1) {
				$db->query("UPDATE {$db->pre}topics SET status = '1' WHERE id = '{$tredirect}'",__LINE__,__FILE__);
			}
			if ($pin == 1 && $my->mp[0] == 1) {
				$db->query("UPDATE {$db->pre}topics SET sticky = '1' WHERE id = '{$tredirect}'",__LINE__,__FILE__);	
			}
			if (($stat == 1 && $my->mp[3] == 1) || ($stat == 2 && $my->mp[2] == 1)) {
				if ($stat == 1) {
					$input = 'a';
				}
				if ($stat == 2) {
					$input = 'n';
				}
				$db->query("UPDATE {$db->pre}topics SET mark = '{$input}' WHERE id = '{$tredirect}'",__LINE__,__FILE__);
			}
		}

		$db->query ("UPDATE {$db->pre}forums SET topics = topics+1, last_topic = '{$tredirect}' WHERE id = '{$board}'");	
		$catobj = $scache->load('cat_bid');
		$catobj->delete();

		if (count($last['topic_notification']) > 0) {
			$to = array_combine(array_fill(1, count($last['topic_notification']), 'mail'), $last['topic_notification']);
			$data = $lang->get_mail('new_topic');
			$from = array();
			xmail($to, $from, $data['title'], $data['comment']);
		}
				
		($code = $plugins->load('newtopic_save_end')) ? eval($code) : null;
		
		if ($_POST['opt_2'] == '1') {
			ok($lang->phrase('new_thread_vote_success'),"newtopic.php?action=startvote&amp;id={$board}&amp;topic_id={$tredirect}&amp;temp={$_POST['temp']}");
		}
		else {
			ok($lang->phrase('data_success'),"showtopic.php?id={$tredirect}");
		}
	}

}
else {
	$my->mp = $slog->ModPermissions($board);

	echo $tpl->parse("header");
	echo $tpl->parse("menu");

	BBProfile($bbcode);
	$inner['smileys'] = $bbcode->getsmileyhtml($config['smileysperrow']);
	$inner['bbhtml'] = $bbcode->getbbhtml();

	$prefix_obj = $scache->load('prefix');
	$prefix_arr = $prefix_obj->get($board);
	
	if (strlen($_GET['fid']) == 32) {
		$data = $gpc->prepare(import_error_data($_GET['fid']));
		$info = array($data['topic']);
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
			$prefix = '';
			if (isset($prefix_arr[$data['prefix']])) {
				$prefix = $prefix_arr[$data['prefix']]['value'];
			}
		}
	}
	else {
		$data = array(
			'prefix' => 0,
			'vote' => '',
			'replies' => '',
			'name' => '',
			'email' => '',
			'comment' => '',
			'dosmileys' => 1,
			'dowords' => 1,
			'topic' => ''
		);
		$_GET['action'] = '';
	}
	
	if (count($prefix_arr) > 0) {
		array_columnsort($prefix_arr, "value");
		if ($last['prefix'] == 0) {
			$prefix_arr_standard = $prefix_arr;
			array_columnsort($prefix_arr_standard, "standard");
			$standard = end($prefix_arr_standard);
			if ($standard['standard'] == 1) {
				$sel = key($prefix_arr_standard);
			}
			else {
				$sel = 0;
			}
			unset($prefix_arr_standard, $standard);
			$prefix_arr = array($lang->phrase('prefix_empty')) + $prefix_arr;
		}
		else {
			$sel = -1;
		}
		if ($data['prefix'] > 0) {
			$sel = $data['prefix'];
		}
		$inner['index_prefix'] = $tpl->parse("newtopic/index_prefix");
	}
	else {
		$inner['index_prefix'] = '';
	}
	
	if ($config['botgfxtest_posts'] == 1) {
		include("classes/graphic/class.veriword.php");
		$vword = new VeriWord();
		$veriid = $vword->set_veriword($config['botgfxtest_text_verification']);
		if ($config['botgfxtest_text_verification'] == 1) {
			$code = $vword->output_word($veriid);
		}
	}
	
	($code = $plugins->load('newtopic_form_prepared')) ? eval($code) : null;
	
	echo $tpl->parse("newtopic/index");
	
	($code = $plugins->load('newtopic_form_end')) ? eval($code) : null;
}

($code = $plugins->load('newtopic_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();		
?>
