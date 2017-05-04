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

define('SCRIPTNAME', 'misc');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

if ($_GET['action'] == "boardin") {

	$board = $gpc->get('board', int);

	$catbid = $scache->load('cat_bid');
	$fc = $catbid->get();
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
		$slog->updatelogged();
		sendStatusCode(302, $config['furl'].'/showforum.php?id='.$board.SID2URL_JS_x);
	}

}
elseif ($_GET['action'] == "report_post" || $_GET['action'] == "report_post2") {
	($code = $plugins->load('showtopic_topic_query')) ? eval($code) : null;
	$result = $db->execute("SELECT r.id, r.report, r.topic_id, r.tstart, t.topic, t.status, t.board, t.prefix FROM {$db->pre}replies AS r LEFT JOIN {$db->pre}topics AS t ON r.topic_id = t.id WHERE r.id = '{$_GET['id']}' LIMIT 1");
	$info = $result->fetch();

	$error = array();
	if (!$info) {
		$error[] = $lang->phrase('query_string_error');
	}
	$my->p = $slog->Permissions($info['board']);
	if ($my->p['forum'] == 0) {
		$error[] = $lang->phrase('not_allowed');
	}
	if (count($error) > 0) {
		errorLogin($error, "showtopic.php?id={$info['topic_id']}".SID2URL_x);
	}

	$catbid = $scache->load('cat_bid');
	$fc = $catbid->get();
	$last = $fc[$info['board']];

	$prefix = '';
	if ($info['prefix'] > 0) {
		$prefix_obj = $scache->load('prefix');
		$prefix_arr = $prefix_obj->get($info['board']);
		if (isset($prefix_arr[$info['prefix']])) {
			$prefix = '[' . $prefix_arr[$info['prefix']]['value'] . ']';
		}
	}

	$topforums = get_headboards($fc, $last, TRUE);
	Breadcrumb::universal()->add($last['name'], "showforum.php?id=".$info['board'].SID2URL_x);
	Breadcrumb::universal()->add($prefix.$info['topic'], "showtopic.php?action=jumpto&topic_id={$info['id']}".SID2URL_x);
	Breadcrumb::universal()->add($lang->phrase('report_post'));

	forum_opt($last);

	if (empty($info['report']) == false) {
		error($lang->phrase('report_post_locked'), "showtopic.php?action=jumpto&topic_id={$info['id']}".SID2URL_x);
	}

	if ($_GET['action'] == "report_post2") {
		$error = array();
		if (flood_protect() == false) {
			$error[] = $lang->phrase('flood_control');
		}
		if (mb_strlen($_POST['comment']) < $config['minpostlength']) {
			$error[] = $lang->phrase('comment_too_short');
		}
		if (count($error) > 0) {
			error($error, "misc.php?action=report_post&id={$info['id']}".SID2URL_x);
		}
		else {
			set_flood();
			$message = $_POST['comment'];
			// Update the report
			$db->execute("UPDATE {$db->pre}replies SET report = '{$message}' WHERE id = '{$info['id']}' LIMIT 1");
			// Get administrators and global moderators
			$groups = $scache->load('groups');
			$team = $groups->team();
			$cache = array();
			$t = array_merge($team['admin'], $team['gmod']);
			foreach ($t as $row) {
				$cache[] = "FIND_IN_SET({$row}, groups)";
			}
			$cache = implode(' OR ',$cache);
			$result = $db->execute("SELECT id, name, mail, language FROM {$db->pre}user WHERE {$cache}");
			$cache = array();
			while ($row = $result->fetch()) {
				$cache[$row['id']] = $row;
			}
			// Get moderators
			$result = $db->execute("SELECT u.id, u.name, u.mail, u.language FROM {$db->pre}moderators AS m LEFT JOIN {$db->pre}user AS u ON u.id = m.mid WHERE m.bid = '{$info['board']}'");
			while ($row = $result->fetch()) {
				// If ID exists already in array then overwrite it
				$cache[$row['id']] = $row;
			}
			// E-mail them all
			$lang_dir = $lang->getdir(true);
			foreach ($cache as $row) {
				$lang->setdir($row['language']);
				$data = $lang->get_mail('report_post');
				$to = array(array('name' => $row['name'], 'mail' => $row['mail']));
				xmail($to, array(), $data['title'], $data['comment']);
			}
			$lang->setdir($lang_dir);

			ok($lang->phrase('report_post_success'), "showtopic.php?action=jumpto&topic_id={$info['id']}".SID2URL_x);
		}
	}
	else {
		echo $tpl->parse("misc/report_post");
	}
}
elseif ($_GET['action'] == "wwo") {

	$my->p = $slog->Permissions();
	$my->pb = $slog->GlobalPermissions();
	if ($my->p['wwo'] == 0) {
		errorLogin();
	}

	Breadcrumb::universal()->add($lang->phrase('wwo_detail_title'));

	$wwo = array(
		'i' => 0,
		'r' => 0,
		'g' => 0
	);
	$inner['wwo_bit_member'] = '';
	$inner['wwo_bit_guest'] = '';

	// Cache forums
	$catbid = $scache->load('cat_bid');
	$cat_cache = $catbid->get();
	// Cache documents
	$wrap_obj = $scache->load('wraps');
	$wrap_cache = $wrap_obj->get();
	// Cache
	$cache = array();

	$lang->group('wwo');

    ($code = $plugins->load('misc_wwo_start')) ? eval($code) : null;

	$result=$db->execute("
	SELECT s.ip, s.mid, s.active, s.wiw_script, s.wiw_action, s.wiw_id, s.user_agent, u.name
	FROM {$db->pre}session AS s
		LEFT JOIN {$db->pre}user AS u ON u.id = s.mid
	ORDER BY s.active DESC
	");

	while ($row = $result->fetchObject()) {
		$wwo['i']++;

		switch (mb_strtolower($row->wiw_script)) {
		case 'managetopic':
		case 'managemembers':
		case 'manageforum':
		case 'forum':
		case 'edit':
		case 'team':
		case 'portal':
		case 'register':
		case 'editprofile':
		case 'components':
			$loc = $lang->phrase('wwo_'.$row->wiw_script);
			break;
		case 'members':
			if ($row->wiw_action == 'team') {
				$loc = $lang->phrase('wwo_team');
			}
			else {
				$loc = $lang->phrase('wwo_members');
			}
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
			$loc = $lang->phrase('wwo_docs');
			if (isset($wrap_cache[$id]) && GroupCheck($wrap_cache[$id]['groups'])) {
				$lid = getDocLangID($wrap_cache[$id]['titles']);
				$loc .= '<br /><a href="docs.php?id='.$id.'">'.viscacha_htmlspecialchars($wrap_cache[$id]['titles'][$lid]).'</a>';
			}
			break;
		case 'showforum':
			$id = $row->wiw_id;
			$loc = $lang->phrase('wwo_showforum');
			if (isset($cat_cache[$id]['name']) && !(($cat_cache[$id]['opt'] == 'pw' && (!isset($my->pwfaccess[$id]) || $my->pwfaccess[$id] != $cat_cache[$id]['optvalue'])) || $my->pb[$id]['forum'] == 0)) {
				$loc .= '<br /><a href="showforum.php?id='.$id.'">'.viscacha_htmlspecialchars($cat_cache[$id]['name']).'</a>';
			}
			break;
		case 'newtopic':
			$id = $row->wiw_id;
			if (!isset($cat_cache[$id]['name']) || (($cat_cache[$id]['opt'] == 'pw' && (!isset($my->pwfaccess[$id]) || $my->pwfaccess[$id] != $cat_cache[$id]['optvalue'])) || $my->pb[$id]['forum'] == 0)) {
				$loc = $lang->phrase('wwo_newtopic');
			}
			else {
				$loc = $lang->phrase('wwo_newtopic_forum').' <a href="showforum.php?id='.$id.'">'.viscacha_htmlspecialchars($cat_cache[$id]['name']).'</a>';
			}
			break;
		case 'profile':
			$id = $row->wiw_id;
			if ($row->wiw_action == 'sendmail' || $row->wiw_action == 'mail') {
				$loc = $lang->phrase('wwo_profile_send');
			}
			else {
				$loc = $lang->phrase('wwo_profile');
			}
			break;
		case 'pm':
			if ($row->wiw_action == 'save' || $row->wiw_action == "new" || $row->wiw_action == "preview" || $row->wiw_action == "quote" || $row->wiw_action == 'reply') {
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
		case 'showtopic': // Todo: Auf eine Query begrenzen (alle IDs auf einmal auslesen am Anfang)
			$id = $row->wiw_id;
			if (!isset($cache['t'.$id])) {
				$nfo = $db->fetch("SELECT topic, board FROM {$db->pre}topics WHERE id = '{$id}'");
				if ($nfo) {
					$cache['t'.$id] = $nfo;
				}
			}
			$loc = $lang->phrase('wwo_'.$row->wiw_script);
			if (isset($cache['t'.$id]) && !(($cat_cache[$cache['t'.$id]['board']]['opt'] == 'pw' && (!isset($my->pwfaccess[$cache['t'.$id]['board']]) || $my->pwfaccess[$cache['t'.$id]['board']] != $cat_cache[$cache['t'.$id]['board']]['optvalue'])) || $my->pb[$cache['t'.$id]['board']]['forum'] == 0)) {
				$loc .= '<br /><a href="showtopic.php?id='.$id.'">'. viscacha_htmlspecialchars($cache['t'.$id]['topic']).'</a>';
			}
			break;
		case 'misc':
			switch ($row->wiw_action) {
			case 'wwo':
			case 'bbhelp':
			case 'rules':
			case 'error':
			case 'board_rules':
				$loc = $lang->phrase('wwo_misc_'.$row->wiw_action);
				break;
			case 'report_post':
			case 'report_post2':
				$loc = $lang->phrase('wwo_misc_report_post');
				break;
			default:
				$loc = $lang->phrase('wwo_misc');
				break;
			}
			break;
		default:
			$default = true;
			($code = $plugins->load('misc_wwo_location')) ? eval($code) : null;
			if ($default == true) {
				$loc = $lang->phrase('wwo_default');
			}
		}

		($code = $plugins->load('misc_wwo_entry')) ? eval($code) : null;

		if ($row->mid >= 1) {
			$wwo['r']++;
			$inner['wwo_bit_member'] .= $tpl->parse("misc/wwo_bit");
		}
		else {
			$wwo['g']++;
			$inner['wwo_bit_guest'] .= $tpl->parse("misc/wwo_bit");
		}
	}

	($code = $plugins->load('misc_wwo_prepared')) ? eval($code) : null;
	echo $tpl->parse("misc/wwo");
    ($code = $plugins->load('misc_wwo_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "vote") {
	($code = $plugins->load('misc_vote_start')) ? eval($code) : null;

	$result = $db->execute("SELECT board, status FROM {$db->pre}topics WHERE id = '{$_GET['id']}' LIMIT 1");
	$info = $result->fetch();
	$my->p = $slog->Permissions($info['board']);

	if (!$my->vlogin || $my->p['forum'] == 0 || $my->p['voting'] == 0) {
		errorLogin($lang->phrase('not_allowed'));
	}

	if ($info['status'] != 0) {
		error($lang->phrase('topic_closed'));
	}

	$answers = array();
	$voted = $db->fetchOne("
		SELECT r.id
		FROM {$db->pre}vote AS v
			LEFT JOIN {$db->pre}votes AS r ON v.id = r.aid
		WHERE v.tid = '{$_GET['id']}' AND r.mid = '{$my->id}'
	");

	$error = array();
	$result = $db->fetchOne("SELECT id FROM {$db->pre}vote WHERE tid = '{$_GET['id']}' AND id = '{$_POST['temp']}'");
	if (!$result) {
		$error[] = $lang->phrase('vote_no_value_checked');
	}
	if ($voted > 0 && $config['vote_change'] != 1) {
		$error[] = $lang->phrase('already_voted');
	}
	($code = $plugins->load('misc_vote_errorhandling')) ? eval($code) : null;
	if (count($error) > 0) {
		error($error, 'showtopic.php?id='.$_GET['id'].'&page='.$_POST['page'].SID2URL_x);
	}
	else {
		($code = $plugins->load('misc_vote_savedata')) ? eval($code) : null;
		if ($voted > 0) {
			$db->execute("UPDATE {$db->pre}votes SET aid = '{$_POST['temp']}' WHERE id = '{$voted}'");
		}
		else {
			$db->execute("INSERT INTO {$db->pre}votes (mid, aid) VALUES ('{$my->id}','{$_POST['temp']}')");
		}
		ok($lang->phrase('data_success'), 'showtopic.php?id='.$_GET['id'].'&page='.$_POST['page'].SID2URL_x);
	}
}
elseif ($_GET['action'] == "bbhelp") {
	$my->p = $slog->Permissions();
	$lang->group("bbcodes");
	BBProfile($bbcode);

	$smileys = $bbcode->getSmileys();
	$cbb = $bbcode->getCustomBB();
	foreach ($cbb as $key => $bb) {
		$cbb[$key]['syntax'] = '['.$bb['tag'].iif($bb['twoparams'], '={option}').']{param}[/'.$bb['tag'].']';
	}

	$code1 = '&lt;?php echo phpversion(); ?&gt;';
	$code2 = '&lt;?php'."\n".'echo phpversion();'."\n".'?&gt;';

	$lorem_ipsum = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.';

	$sbb = array(
		array(
			'tag' => 'b',
			'params' => 0,
			'title' => $lang->phrase('bbcodes_bold'),
			'description' => $lang->phrase('bbcodes_bold_desc'),
		),
		array(
			'tag' => 'i',
			'params' => 0,
			'title' => $lang->phrase('bbcodes_italic'),
			'description' => $lang->phrase('bbcodes_italic_desc')
		),
		array(
			'tag' => 'u',
			'params' => 0,
			'title' => $lang->phrase('bbcodes_underline'),
			'description' => $lang->phrase('bbcodes_underline_desc')
		),
		array(
			'tag' => 'color',
			'params' => 1,
			'example' => array('[color=ff9900]'.$lang->phrase('bbcodes_example_text').'[/color]')
		),
		array(
			'tag' => 'img',
			'params' => 0,
			'example' => array('[img]assets/smileys/smile.gif[/img]')
		),
		array(
			'tag' => 'url',
			'params' => 2,
			'example' => array(
				'[url]http://www.viscacha.org[/url]',
				'[url=http://www.viscacha.org]Viscacha[/url]'
			)
		),
		array(
			'tag' => 'email',
			'params' => 0,
			'example' => array('[email]kristina@mustermann.de[/email]')
		),
		array(
			'tag' => 'ot',
			'params' => 0
		),
		array(
			'tag' => 'quote',
			'params' => 2,
			'example' => array(
				'[quote]'.$lang->phrase('bbcodes_example_text').'[/quote]',
				'[quote=Julius Caesar]Veni vidi vici[/quote]',
				'[quote=http://www.viscacha.org]Viscacha is a free bulletin board system with an integrated content management system.[/quote]'
			)
		),
		array(
			'tag' => 'list',
			'params' => 2,
			'example' => array(
				'[list]'."\n".'[*]'.$lang->phrase('bbcodes_example_text')."\n".'[*]'.$lang->phrase('bbcodes_example_text2')."\n".'[*]'.$lang->phrase('bbcodes_example_text')."\n".'[/list]',
				'[list=ol]'."\n".'[*]'.$lang->phrase('bbcodes_example_text')."\n".'[*]'.$lang->phrase('bbcodes_example_text2')."\n".'[*]'.$lang->phrase('bbcodes_example_text')."\n".'[/list]',
				'[list=A]'."\n".'[*]'.$lang->phrase('bbcodes_example_text')."\n".'[*]'.$lang->phrase('bbcodes_example_text2')."\n".'[*]'.$lang->phrase('bbcodes_example_text')."\n".'[/list]',
				'[list=I]'."\n".'[*]'.$lang->phrase('bbcodes_example_text')."\n".'[*]'.$lang->phrase('bbcodes_example_text2')."\n".'[*]'.$lang->phrase('bbcodes_example_text')."\n".'[/list]',
			)
		)
	);
	$ebb = array(
		array(
			'tag' => 'size',
			'params' => 1,
			'example' => array(
				'[size=large]'.$lang->phrase('bbcodes_example_text').'[/size]',
				'[size=small]'.$lang->phrase('bbcodes_example_text').'[/size]',
				'[size=extended]'.$lang->phrase('bbcodes_example_text').'[/size]'
			)
		),
		array(
			'tag' => 'h',
			'title' => $lang->phrase('bbcodes_header'),
			'description' => $lang->phrase('bbcodes_header_desc'),
			'params' => 1,
			'example' => array(
				'[h=large]'.$lang->phrase('bbcodes_example_text').'[/h]',
				'[h=middle]'.$lang->phrase('bbcodes_example_text').'[/h]',
				'[h=small]'.$lang->phrase('bbcodes_example_text').'[/h]'
			)
		),
		array(
			'tag' => 'align',
			'params' => 1,
			'example' => array(
				'[align=left]'.$lorem_ipsum.'[/align]',
				'[align=center]'.$lorem_ipsum.'[/align]',
				'[align=right]'.$lorem_ipsum.'[/align]',
				'[align=justify]'.$lorem_ipsum.'[/align]'
			)
		),
		array(
			'tag' => 'code',
			'params' => 0,
			'example' => array(
				'[code]'.$code1.'[/code]',
				'[code]'.$code2.'[/code]'
			)
		),
		array(
			'tag' => 'edit',
			'params' => 2,
			'example' => array(
				'[edit]'.$lang->phrase('bbcodes_example_text').'[/edit]',
				'[edit=Kristina]'.$lang->phrase('bbcodes_example_text').'[/edit]'
			)
		),
		array(
			'tag' => 'hide',
			'params' => 0
		),
		array(
			'tag' => 'sub',
			'params' => 0,
			'example' => array('H[sub]2[/sub]O')
		),
		array(
			'tag' => 'sup',
			'params' => 0,
			'example' => array('cm[sup]2[/sup]')
		),
		array(
			'tag' => 'tt',
			'params' => 0
		),
		array(
			'tag' => 'reader',
			'params' => -1
		),
		array(
			'tag' => 'hr',
			'params' => -1
		),
		array(
			'tag' => 'table',
			'params' => 2,
			'example' => array(
				'[table=head;50%]'."\n".
				'#[tab]Name[tab]Age'."\n".
				'1.[tab]Otto[tab]13'."\n".
				'2.[tab]Katharina[tab]16'."\n".
				'3.[tab]Matthias[tab]19'."\n".
				'[/table]'
			)
		)
	);

	foreach (array('sbb', 'ebb') as $string) {
		foreach (${$string} as $key => $arr) {
			if ($arr['params'] == -1) {
				${$string}[$key]['syntax'] = array('['.$arr['tag'].']');
			}
			else {
				${$string}[$key]['syntax'] = array('['.$arr['tag'].iif($arr['params'], '={option}').']{param}[/'.$arr['tag'].']');
				if ($arr['params'] == 2) {
					array_unshift(${$string}[$key]['syntax'], '['.$arr['tag'].']{param}[/'.$arr['tag'].']');
				}
			}
			if (!isset($arr['title'])) {
				${$string}[$key]['title'] = $lang->phrase('bbcodes_'.$arr['tag']);
			}
			if (!isset($arr['description'])) {
				${$string}[$key]['description'] = $lang->phrase('bbcodes_'.$arr['tag'].'_desc');
			}
			if (!isset($arr['example'])) {
				${$string}[$key]['example'] = array();
				foreach (${$string}[$key]['syntax'] as $syntax) {
					${$string}[$key]['example'][] = str_replace('{param}', $lang->phrase('bbcodes_example_text'),
												str_replace('{option}', $lang->phrase('bbcodes_example_text2'), $syntax)
											  );
				}
			}
		}
	}

	Breadcrumb::universal()->add($lang->phrase('bbhelp_title'));
	($code = $plugins->load('misc_bbhelp_prepared')) ? eval($code) : null;
	echo $tpl->parse("misc/bbhelp");
	($code = $plugins->load('misc_bbhelp_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "markasread") {
	$my->p = $slog->Permissions();
	if (is_url($_SERVER['HTTP_REFERER'])) {
		$url = parse_url($_SERVER['HTTP_REFERER']);
		if (mb_strpos($config['furl'], $url['host']) !== FALSE) {
			$loc = viscacha_htmlspecialchars($_SERVER['HTTP_REFERER']);
		}
	}
	if (empty($loc)) {
		$loc = 'javascript:history.back(-1);';
	}
	$slog->setAllRead();
	ok($lang->phrase('marked_as_read'), $loc);
}
elseif ($_GET['action'] == "markforumasread") {
	$board = $gpc->get('board', int);
	$my->p = $slog->Permissions($board);
	if (!is_id($board) || $my->p['forum'] == 0) {
		errorLogin();
	}
	$slog->setForumRead($board);
	ok($lang->phrase('marked_as_read'), 'showforum.php?id='.$board);
}
elseif ($_GET['action'] == "rules") {
	$my->p = $slog->Permissions();
	Breadcrumb::universal()->add($lang->phrase('rules_title'));
	$rules = $lang->get_words('rules');
	($code = $plugins->load('misc_rules_prepared')) ? eval($code) : null;
	echo $tpl->parse("misc/rules");
	($code = $plugins->load('misc_rules_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "board_rules") {
	$my->p = $slog->Permissions($_GET['id']);
	$catbid = $scache->load('cat_bid');
	$fc = $catbid->get();

	if (!isset($fc[$_GET['id']])) {
		error($lang->phrase('query_string_error'));
	}
	$info = $fc[$_GET['id']];
	if ($info['message_active'] == '0') {
		error($lang->phrase('no_board_rules_specified'));
	}

	($code = $plugins->load('misc_board_rules_start')) ? eval($code) : null;

	$topforums = get_headboards($fc, $info);
	Breadcrumb::universal()->add($info['name'], "showforum.php?id=".$info['id'].SID2URL_x);
	Breadcrumb::universal()->add($lang->phrase('board_rules'));

	forum_opt($info);

	echo $tpl->parse("header");
	($code = $plugins->load('misc_board_rules_prepared')) ? eval($code) : null;
	echo $tpl->parse("misc/board_rules");
	($code = $plugins->load('misc_board_rules_end')) ? eval($code) : null;
	echo $tpl->parse("footer");
}
elseif ($_GET['action'] == "error") {
	$my->p = $slog->Permissions();
	$errid = $gpc->get('id', int);
	if ($errid != 400 && $errid != 404 && $errid != 403) {
		$errid = 500; // internal server error
	}
	sendStatusCode($errid);
	($code = $plugins->load('misc_error_prepared')) ? eval($code) : null;
	Breadcrumb::universal()->add($lang->phrase('htaccess_error_'.$errid));
	echo $tpl->parse("misc/error");
}
elseif ($_GET['action'] == "edithistory") {
	($code = $plugins->load('misc_edithistory_query')) ? eval($code) : null;
	$row = $db->fetch("
	SELECT r.ip, r.topic_id, t.board, r.edit, r.id, r.tstart, t.topic, t.prefix, r.date, u.name, u.id as mid, u.groups, u.deleted_at
	FROM {$db->pre}replies AS r
		LEFT JOIN {$db->pre}topics AS t ON t.id = r.topic_id
		LEFT JOIN {$db->pre}user AS u ON r.name = u.id
	WHERE r.id = '{$_GET['id']}'
	");
	if (!$row) {
		error($lang->phrase('query_string_error'));
	}

	$my->p = $slog->Permissions($row['board']);
	if ($my->p['forum'] == 0) {
		errorLogin();
	}
	
	$catbid = $scache->load('cat_bid');
	$fc = $catbid->get();
	$last = $fc[$row['board']];
	
	$prefix = '';
	if ($row['prefix'] > 0) {
		$prefix_obj = $scache->load('prefix');
		$prefix_arr = $prefix_obj->get($row['board']);
		if (isset($prefix_arr[$row['prefix']])) {
			$prefix = '[' . $prefix_arr[$row['prefix']]['value'] . ']';
		}
	}
	
	$topforums = get_headboards($fc, $last, TRUE);
	Breadcrumb::universal()->add($last['name'], "showforum.php?id=".$last['id'].SID2URL_x);
	Breadcrumb::universal()->add($prefix.$row['topic'], "showtopic.php?action=jumpto&topic_id=".$row['id'].SID2URL_x);
	Breadcrumb::universal()->add($lang->phrase('edithistory'));

	forum_opt($last);

	($code = $plugins->load('misc_edithistory_start')) ? eval($code) : null;

	$edit = array();
	if (!empty($row['edit'])) {
		preg_match_all('~^([^\t]+)\t(\d+)\t([^\t]*)\t([\d\.]+)$~mu', $row['edit'], $edits, PREG_SET_ORDER);
		foreach ($edits as $e) {
			$edit[] = array(
				'date' => $e[2],
				'reason' => empty($e[3]) ? $lang->phrase('post_editinfo_na') : $e[3],
				'name' => $e[1],
				'ip' => empty($e[4]) ? '-' : $e[4]
			);
			($code = $plugins->load('misc_edithistory_entry_prepared')) ? eval($code) : null;
		}
	}

	($code = $plugins->load('misc_edithistory_prepared')) ? eval($code) : null;
	echo $tpl->parse("misc/edithistory");
	($code = $plugins->load('misc_edithistory_end')) ? eval($code) : null;
}

($code = $plugins->load('misc_end')) ? eval($code) : null;

$slog->updatelogged();
$phpdoc->Out();