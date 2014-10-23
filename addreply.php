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

DEFINE('SCRIPTNAME', 'addreply');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();

if (!empty($_POST['id'])) {
	$_GET['id'] = $_POST['id'];
}
if (!empty($_GET['id'])) {
	$_POST['id'] = $_GET['id'];
}
$result = $db->query('SELECT id, prefix, topic, board, posts, status FROM '.$db->pre.'topics WHERE id = "'.$_GET['id'].'" LIMIT 1',__LINE__,__FILE__);
$info = $db->fetch_assoc($result);
if ($info['id'] < 1 || $db->num_rows($result) != 1) {
	error($lang->phrase('query_string_error'));
}
$info['topic'] = $gpc->prepare($info['topic']);

$my->p = $slog->Permissions($info['board']);

$fc = cache_cat_bid();
$last = $fc[$info['board']];
forum_opt($last['opt'], $last['optvalue'], $last['id']);

$pre = '';
if ($info['prefix'] > 0) {
	$prefix = cache_prefix($info['board']);
	if (isset($prefix[$info['prefix']])) {
		$pre = $prefix[$info['prefix']];
		$pre = $lang->phrase('showtopic_prefix_title');
	}
}

get_headboards($fc, $last);
$breadcrumb->Add($last['name'], "showforum.php?id=".$last['id'].SID2URL_x);
$breadcrumb->Add($pre.$info['topic'], 'showtopic.php?id='.$_GET['id'].SID2URL_x);
$breadcrumb->Add($lang->phrase('addreply_title'));

if ($info['status'] != 0) {
	error($lang->phrase('topic_closed'));
}

$p_upload = 0;
if ($config['tpcallow'] == 1 && $my->p['attachments'] == 1) { 
	$p_upload = 1;
}

if ($_GET['action'] == "save") {
    $error = array();
    if (!$my->vlogin) {
		if (!check_mail($_POST['email'])) {
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
		$pnameid = $_POST['name'];
		$pid = 0;
	}
	else {
		$pname = $my->name;
		$pnameid = $my->id;
		$pid = $my->id;
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

	$bbcode = initBBCodes();
	$_POST['topic'] = $bbcode->parseTitle($_POST['topic']);

	if (count($error) > 0 || !empty($_POST['Preview2'])) {
		$data = array(
			'topic' => $_POST['topic'],
			'comment' => $_POST['comment'],
			'dosmileys' => $_POST['dosmileys'],
			'dowords' => $_POST['dowords'],
			'id' => $_POST['id']
		);
		if (!$my->vlogin) {
			$data['email'] = $_POST['email'];
			$data['name'] = $_POST['name'];
		}
		$fid = save_error_data($data);
		if (!empty($_POST['Preview2'])) {
			viscacha_header("Location: addreply.php?action=preview&id={$_POST['id']}&fid=".$fid.SID2URL_JS_x);
		}
		else {
			error($error,"addreply.php?id={$_POST['id']}&amp;fid=".$fid.SID2URL_x);
		}
	}
	else {
		set_flood();

		$date = time();
		
		$db->query("UPDATE {$db->pre}topics SET last_name = '".$pnameid."', last = '".$date."', posts = posts+1 WHERE id = '{$_POST['id']}'",__LINE__,__FILE__);
		$db->query("INSERT INTO {$db->pre}replies (board,topic,topic_id,name,comment,dosmileys,dowords,email,date) VALUES ('{$info['board']}','{$_POST['topic']}','{$_POST['id']}','{$pnameid}','{$_POST['comment']}','{$_POST['dosmileys']}','{$_POST['dowords']}','{$_POST['email']}','{$date}')",__LINE__,__FILE__); 
		$redirect = $db->insert_id();
		// Set uploads to correct reply
		$db->query("UPDATE {$db->pre}uploads SET tid = '{$redirect}' WHERE mid = '{$pid}' AND topic_id = '{$_POST['id']}' AND tid = '0'",__LINE__,__FILE__);
		
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
				$db->query("INSERT INTO {$db->pre}abos (mid,tid,type) VALUES ('{$my->id}','{$_POST['id']}','{$type}')",__LINE__,__FILE__);
			}
		}
		
		$db->query ('UPDATE '.$db->pre.'cat SET replys = replys+1, last_topic = "'.$_POST['id'].'" WHERE id = '.$info['board'],__LINE__,__FILE__);

		$sql = 'SELECT t.id, t.topic, u.name, u.mail 
        FROM '.$db->pre.'abos AS a LEFT JOIN '.$db->pre.'user AS u ON u.id = a.mid LEFT JOIN '.$db->pre.'topics AS t ON t.id = a.tid 
        WHERE a.type = "" AND a.tid = "'.$_POST['id'].'" AND a.mid != "'.$my->id.'"';
        $result = $db->query($sql,__LINE__,__FILE__);
	    while ($row = $db->fetch_assoc($result)) {
			$data = $lang->get_mail('digest_s');
			$to = array('0' => array('name' => $row['name'], 'mail' => $row['mail']));
			$from = array();
			xmail($to, $from, $data['title'], $data['comment']);
	    }

		ok($lang->phrase('data_success'),"showtopic.php?id={$_POST['id']}&amp;action=last".SID2URL_x);
	}
}
else {

	$qids = array();
	
	$bbcode = initBBCodes();

	if (strlen($_GET['fid']) == 32) {
		$data = $gpc->prepare(import_error_data($_GET['fid']));
		$_GET['id'] = $data['id'];
		$info['topic'] = $data['topic'];
		if ($_GET['action'] == 'preview') {
			$bbcode->setSmileys($data['dosmileys']);
			if ($config['wordstatus'] == 0) {
				$data['dowords'] = 0;
			}
			$bbcode->setReplace($data['dowords']);
			$data['formatted_comment'] = $bbcode->parse($data['comment']);
		}
	}
	else {
		$data = array(
			'name' => '',
			'email' => '',
			'comment' => '',
			'dosmileys' => 1,
			'dowords' => 1,
			'topic' => $lang->phrase('reply_prefix').$info['topic']
		);

		$memberdata = cache_memberdata();
		
		// Multiquote
		if(isset($_COOKIE[$config['cookie_prefix'].'_vquote'])) {
		    $pids = $_COOKIE[$config['cookie_prefix'].'_vquote'];
		    $pids = urldecode($pids);
		    if(!empty($pids) && preg_match("/^[0-9,]+$/", $pids)) {
		    	$qids = explode(',',$pids);
		    }
		    makecookie($config['cookie_prefix'].'_vquote', '', 0);
		}
		
		if (count($qids) > 0) {
			$result = $db->query('SELECT name, comment, email FROM '.$db->pre.'replies WHERE id IN('.implode(',',$qids).') LIMIT '.$config['maxmultiquote'],__LINE__,__FILE__);
			while($row = $gpc->prepare($db->fetch_assoc($result))) {
				if (empty($row['email']) && isset($memberdata[$row['name']])) {
					$row['name'] = $memberdata[$row['name']];
				}
				$row['comment'] = preg_replace('/\[hide\](.+?)\[\/hide\]/is', '', $row['comment']);
				$row['comment'] = trim($row['comment']);
				$data['comment'] .= "[quote=".$row['name']."]".$row['comment']."[/quote]\r\n";
			}
		}
	}
	
	if ($my->vlogin) { 
		$result = $db->query('SELECT id FROM '.$db->pre.'abos WHERE mid = '.$my->id.' AND tid = '.$_GET['id'],__LINE__,__FILE__); 
		$abox = $db->fetch_object($result);
	}
	
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	
	$mymodules->load('addreply_top');
	
	$inner['smileys'] = $bbcode->getsmileyhtml($config['smileysperrow']);
	$inner['bbhtml'] = $bbcode->getbbhtml();
	echo $tpl->parse("addreply");
	
	$mymodules->load('addreply_bottom');
}

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();
?>
