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

DEFINE('SCRIPTNAME', 'misc');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();

if ($_GET['action'] == "boardin") {
	
	$board = $gpc->get('board', int);

	$fc = cache_cat_bid();
	if (empty($board) || !isset($fc[$board])) {
		error($lang->phrase('query_string_error'));
	}
	$row = $fc[$board];

	if ($row['opt'] == 'pw') {
		$my->p = $slog->Permissions($board);
	    if ($row['optvalue'] == $_POST['pw']) {
	    	$my->pwfaccess[$board] = $row['optvalue'];
	        ok($lang->phrase('goboardpw_success'), 'showforum.php?id='.$board);
	    }
	    else {
	        error($lang->phrase('goboardpw_wrong_password'));
	    }
	}
	else {
		viscacha_header('Location: showforum.php?id='.$board.SID2URL_JS_x);
	}

}
elseif ($_GET['action'] == "wwo") {

	$my->p = $slog->Permissions();
	if ($my->p['wwo'] == 0) {
		errorLogin();
	}
	
	$breadcrumb->Add($lang->phrase('wwo_detail_title'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	
	$wwo = array();
	$wwo['i']=0; 
	$wwo['r']=0; 
	$wwo['g']=0; 
	$wwo['b']=0;
	$inner['wwo_bit_bot'] = '';
	$inner['wwo_bit_member'] = '';
	$inner['wwo_bit_guest'] = '';

	// Foren cachen
	$cat_cache =  cache_cat_bid();
	// Wraps cachen
	$wrap_cache = cache_wraps();
	// Mitglieder
	$memberdata = cache_memberdata();
	// Cache
	$cache = array();

	$lang->group('wwo');

    $mymodules->load('misc_wwo_top');

	$result=$db->query("SELECT ip, mid, active, wiw_script, wiw_action, wiw_id, remoteaddr FROM {$db->pre}session ORDER BY active DESC",__LINE__,__FILE__);
	while ($row = $gpc->prepare($db->fetch_object($result))) {
		$wwo['i']++;
		$bot = 0;
		$time = gmdate($lang->phrase('dformat3'), times($row->active));
		if (isset($memberdata[$row->mid])) {
			$row->name = $memberdata[$row->mid];
		}
		else {
			$row->name = NULL;
		}
		
		// Für Plugins ein Modul-Standort zum einfügen von anderen Positionen
		
		switch (strtolower($row->wiw_script)) {
		case 'members':
		case 'managetopic':
		case 'managemembers':
		case 'manageforum':
		case 'forum':
		case 'edit':
		case 'team':
		case 'spellcheck':
		case 'portal':
		case 'register':
		case 'editprofile':
		case 'components':
			$loc = $lang->phrase('wwo_'.$row->wiw_script);
			break;
		case 'log':
			if ($row->wiw_action == 'pwremind' || $row->wiw_action == 'pwremind2') {
				$loc = $lang->phrase('wwo_log_pwremind');
			}
			elseif ($row->wiw_action == 'logout') {
				$loc = $lang->phrase('wwo_log_logout');
			}
			else {
				$loc = $lang->phrase('wwo_log_login');
			}
			break;
		case 'attachments':
			if ($row->wiw_action == 'thumbnail' || $row->wiw_action == 'attachment') {
				$loc = $lang->phrase('wwo_attachments_view');
			}
			else {
				$loc = $lang->phrase('wwo_attachments_write');
			}
			break;
		case 'docs':
			$id = $row->wiw_id;
			if (isset($wrap_cache[$id]['title'])) {
				$title = $wrap_cache[$id]['title'];
			}
			else {
				$title = $lang->phrase('wwo_fallback');
			}
			$loc = $lang->phrase('wwo_docs');
			break;
		case 'showforum':
			$id = $row->wiw_id;
			if (isset($cat_cache[$id]['name'])) {
				$title = $cat_cache[$id]['name'];
			}
			else {
				$title = $lang->phrase('wwo_fallback');
			}
			$loc = $lang->phrase('wwo_showforum');
			break;
		case 'newtopic':
			$id = $row->wiw_id;
			if (is_id($id)) {
				if (isset($cat_cache[$id]['name'])) {
					$title = $cat_cache[$id]['name'];
				}
				else {
					$title = $lang->phrase('wwo_fallback');
				}
				$loc = $lang->phrase('wwo_newtopic_forum');
			}
			else {
				$loc = $lang->phrase('wwo_newtopic');
			}
			break;
		case 'profile':
			$id = $row->wiw_id;
			if (isset($memberdata[$id])) {
				$title = $memberdata[$id];
			}
			else {
				$title = $lang->phrase('wwo_fallback');
			}
			if ($row->wiw_action == 'sendmail' || $row->wiw_action == 'mail' || $row->wiw_action == 'sendjabber') {
				$loc = $lang->phrase('wwo_profile_send');
			}
			else {
				$loc = $lang->phrase('wwo_profile');
			}
			break;
		case 'popup':
			if ($row->wiw_action == 'hlcode') {
				$loc = $lang->phrase('wwo_popup_hlcode');
			}
			elseif ($row->wiw_action == 'filetypes') {
				$loc = $lang->phrase('wwo_popup_filetypes');
			}
			elseif ($row->wiw_action == 'showpost') {
				$id = $row->wiw_id;
				if (!isset($cache['p'.$id])) {
					$result2 = $db->query('SELECT topic FROM '.$db->pre.'replies WHERE id = "'.$id.'" LIMIT 1');
					$nfo = $db->fetch_assoc($result2);
					$cache['p'.$id] = $gpc->prepare($nfo['topic']);
				}
				$title = $cache['p'.$id];
				if (empty($title)) {
					$title = $lang->phrase('wwo_fallback');
				}
				$loc = $lang->phrase('wwo_popup_showpost');
			}
			else {
				$loc = $lang->phrase('wwo_popup');
			}
			break;
		case 'pm':
			if ($row->wiw_action == 'show') {
				$loc = $lang->phrase('wwo_pm_view');
			}
			elseif ($row->wiw_action == 'massmanage' || $row->wiw_action == 'massdelete' || $row->wiw_action == 'massmove' || $row->wiw_action == 'delete' || $row->wiw_action == 'delete2') {
				$loc = $lang->phrase('wwo_pm_manage');
			}
			elseif ($row->wiw_action == 'save' || $row->wiw_action == "new" || $row->wiw_action == "preview" || $row->wiw_action == "quote" || $row->wiw_action == 'reply') {
				$loc = $lang->phrase('wwo_pm_write');
			}
			else {
				$loc = $lang->phrase('wwo_pm');
			}
			break;
		case 'search':
			if ($row->wiw_action == 'active') {
				$loc = $lang->phrase('wwo_search_active');
			}
			else {
				$loc = $lang->phrase('wwo_search');
			}
			break;
		case 'addreply':
		case 'showtopic':
		case 'print':
		case 'pdf':
			$id = $row->wiw_id;
			if (!isset($cache['t'.$id])) {
				$result2 = $db->query('SELECT topic FROM '.$db->pre.'topics WHERE id = "'.$id.'" LIMIT 1');
				$nfo = $db->fetch_assoc($result2);
				$cache['t'.$id] = $gpc->prepare($nfo['topic']);
			}
			$title = $cache['t'.$id];
			if (empty($title)) {
				$title = $lang->phrase('wwo_fallback');
			}
			$loc = $lang->phrase('wwo_'.$row->wiw_script);
			break;
		case 'misc':
			switch ($row->wiw_action) {
			case 'wwo':
			case 'bbhelp':
			case 'rules':
				$loc = $lang->phrase('wwo_misc_'.$row->wiw_action);
				break;
			case 'error':
				$loc = $lang->phrase('wwo_misc_error');
				break;
			default:
				$loc = $lang->phrase('wwo_misc');
				break;
			}
			break;
		default:
			$loc = $lang->phrase('wwo_default');
		}
		
		$mymodules->load('misc_wwo_bit');

		if ($row->mid >= 1) {
			$wwo['r']++;
			$inner['wwo_bit_member'] .= $tpl->parse("misc/wwo_bit");
		}
		elseif (($botdetect = BotDetection($slog->bots, $row->remoteaddr)) != false) {
			$wwo['b']++;
			$bot = 1;
			$inner['wwo_bit_bot'] .= $tpl->parse("misc/wwo_bit");
		}
		else {
			$wwo['g']++;
			$inner['wwo_bit_guest'] .= $tpl->parse("misc/wwo_bit");
		}
	}
	echo $tpl->parse("misc/wwo");
    $mymodules->load('misc_wwo_bottom');
}
elseif ($_GET['action'] == "vote") {

	$allow = TRUE;
	$result = $db->query("SELECT v.id FROM {$db->pre}vote AS v LEFT JOIN {$db->pre}votes AS r ON v.id=r.aid WHERE v.tid = '{$_GET['id']}' AND r.mid = '".$my->id."' LIMIT 1",__LINE__,__FILE__);
	if ($db->num_rows() > 0) {
		$allow = FALSE;
	}
	$result = $db->query("SELECT board FROM {$db->pre}topics WHERE id = '{$_GET['id']}' LIMIT 1",__LINE__,__FILE__);
	$info = $db->fetch_assoc($result);

	$my->p = $slog->Permissions($info['board']);

    $error = array();
	if (!is_id($_GET['id'])) {
		$error[] = $lang->phrase('query_string_error');
	}
	if (!is_id($_POST['temp'])) {
		$error[] = $lang->phrase('vote_no_value_checked');
	}
	if (!$allow) {
		$error[] = $lang->phrase('already_voted');
	}
	if ($my->p['forum'] == 0 || $my->p['voting'] == 0 || !$my->vlogin) {
		$error[] = $lang->phrase('not_allowed');
	}
	if (count($error) > 0) {
		errorLogin($error);
	}
	else {
		$db->query("INSERT INTO {$db->pre}votes (mid, aid) VALUES ('{$my->id}','{$_POST['temp']}')",__LINE__,__FILE__);
		ok($lang->phrase('data_success'), 'showtopic.php?id='.$_GET['id'].SID2URL_x);
	}
}
elseif ($_GET['action'] == "bbhelp") {
	$my->p = $slog->Permissions();
	$bbcode = initBBCodes();
	$smileys = $bbcode->getSmileys();
	$breadcrumb->Add($lang->phrase('bbhelp_title'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	echo $tpl->parse("misc/bbhelp");
}
elseif ($_GET['action'] == "markasread") {
	$my->p = $slog->Permissions();
	if (!empty($_SERVER['HTTP_REFERER'])) {
		$url = parse_url($_SERVER['HTTP_REFERER']);
		if (strpos($config['furl'], $url['host']) !== FALSE) {
			$loc = htmlspecialchars($_SERVER['HTTP_REFERER']);
		}
	}
	if (empty($loc)) {
		$loc = 'javascript: history.back(-1);';
	}
	$slog->mark_read();
	ok($lang->phrase('marked_as_read'), $loc);
}
elseif ($_GET['action'] == "markforumasread") {
	$board = $gpc->get('board', int);
	$my->p = $slog->Permissions($board);
	if (!is_id($board) || $my->p['forum'] == 0) {
		errorLogin();
	}
	
	$result = $db->query('SELECT id FROM '.$db->pre.'topics WHERE board = '.$board.' AND last > '.$my->clv,__LINE__,__FILE__);
	while ($row = $db->fetch_assoc($result)) {
		$my->mark['t'][$row['id']] = time();
	}
	
	$my->mark['f'][$board] = time();
	$slog->updatelogged();
	ok($lang->phrase('marked_as_read'), 'showforum.php?id='.$board);

}
elseif ($_GET['action'] == "rules") {
	$my->p = $slog->Permissions();
	$breadcrumb->Add($lang->phrase('rules_title'));
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	$rules = $lang->get_words('rules');
	echo $tpl->parse("misc/rules");
}
elseif ($_GET['action'] == "error") {
	$my->p = $slog->Permissions();
	$errid = $gpc->get('id', int);
	$breadcrumb->Add($lang->phrase('htaccess_error_'.$_GET['id']));
	echo $tpl->parse("header");
	if ($errid != 400 && $errid != 404 && $errid != 401 && $errid != 403  && $errid != 500) {
		$errid = 0;
	}
	echo $tpl->parse("misc/error");
}
else {
	error($lang->phrase('query_string_error'));
}

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();
?>
