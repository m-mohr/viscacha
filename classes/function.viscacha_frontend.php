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

if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

require_once("classes/function.frontend_init.php");

define('BOARD_STATE_OLD', 0);
define('BOARD_STATE_NEW', 1);
define('BOARD_STATE_LOCKED', 2);
define('BOARD_STATE_WWW', 3);

function getRedirectURL($standard = true) {
	global $gpc;
	$loc = strip_tags($gpc->get('redirect', none));
	$loc = preg_replace('~(\?|&)s=[A-Za-z0-9]*~i', '', $loc);
	if (check_hp($loc)) {
		$url = parse_url($loc);
		$file = !empty($url['path']) ? basename($url['path']) : '';
	}
	else {
		$file = basename($loc);
	}
	if (strpos($file, '?') !== false) {
		$parts = explode('?', $file, 2);
		$file = $parts[0];
		if (!empty($parts[1])) {
			parse_str($parts[1], $q);
			if (!empty($q['action']) && substr($q['action'], -1) == '2') {
				$loc = ''; // When the last char of the value of action is 2 we have in most cases a "POST" form
			}
		}
	}
	if (empty($loc) || !file_exists($file) || $file == 'log.php' || $file == 'register.php') {
		if ($standard == true) {
			$loc = 'index.php';
 		}
 		else {
 			$loc = '';
 		}
	}
	if (!empty($loc)) {
		if (strpos($loc, '?') === false) {
			$loc .= SID2URL_1;
		}
		else {
			$loc .= SID2URL_x;
		}
	}
	return $loc;
}

function getRequestURI() {
	global $config;
	$method = (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) == 'GET');
	if (empty($_SERVER['REQUEST_URI']) == false && $method == true) {
		$request_uri = '';
		$var = parse_url($config['furl']);
		$request_uri = sprintf('http%s://%s%s',
			(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == TRUE ? 's': ''),
			(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $var['host']),
			$_SERVER['REQUEST_URI']
		);
		if (check_hp($request_uri)) {
			$request_uri = preg_replace('~(\?|&)s=[A-Za-z0-9]*~i', '', $request_uri);
			$url = parse_url($request_uri);
			if (empty($url['path'])) {
				$url['path'] = '';
			}
			$file = basename($url['path']);
			if (!empty($loc) && file_exists($file) && $file != 'log.php' && $file != 'register.php') {
				if (strpos($loc, '?') === false) {
					$request_uri .= SID2URL_1;
				}
				else {
					$request_uri .= SID2URL_x;
				}
			}
			return $request_uri;
		}
	}
	return '';
}

function getRefererURL() {
	global $config;
	$request_uri = '';
	if (check_hp($_SERVER['HTTP_REFERER'])) {
		$url = parse_url($_SERVER['HTTP_REFERER']);
		if (!empty($url['query'])) {
			parse_str($url['query'], $q);
			if (!empty($q['action']) && substr($q['action'], -1) == '2') {
				return ''; // When the last char of the value of action is 2 we have in most cases a "POST" form
			}
		}
		if (!empty($url['host']) && strpos($config['furl'], $url['host']) !== FALSE) {
			$request_uri = $_SERVER['HTTP_REFERER'];
		}
		$request_uri = preg_replace('~(\?|&)s=[A-Za-z0-9]*~i', '', $request_uri);
		if (empty($url['path'])) {
			$url['path'] = '';
		}
		$file = basename($url['path']);
		if (!empty($loc) && file_exists($file) && $file != 'log.php' && $file != 'register.php') {
			if (strpos($loc, '?') === false) {
				$request_uri .= SID2URL_1;
			}
			else {
				$request_uri .= SID2URL_x;
			}
		}
		return $request_uri;
	}
	else {
		return '';
	}
}

function cmp_edit_date($a, $b) {
	if ($a['date'] < $b['date']) {
		return -1;
	}
	if ($a['date'] == $b['date']) {
		return 0;
	}
	if ($a['date'] > $b['date']) {
		return 1;
	}
}

function DocCodePagination($cc) {
	$pos1 = stripos($cc, '{pagebreak}');
	if ($pos1 === false) {
		return array($cc, 1);
	}
	else {
		$page = $_GET['page']-1;
		$pgc = preg_split("~(<br[^>]*>|\n|\r)*\{pagebreak\}(<br[^>]*>|\n|\r)*~i", $cc, -1, PREG_SPLIT_NO_EMPTY);
		if (!isset($pgc[$page])) {
			$page = 0;
			$_GET['page'] = 1;
		}
		return array($pgc[$page], count($pgc));
	}
}

function DocCodeParser($syntax, $parser = 1) {
	global $bbcode, $info;
	if ($parser == 2) {
		ob_start();
		$code = str_replace('<'.'?php','<'.'?',$syntax);
		$code = '?'.'>'.trim($code).'<'.'?';
		extract($GLOBALS, EXTR_SKIP);
		eval($code);
		$syntax = ob_get_contents();
		ob_end_clean();
	}
	elseif ($parser == 3) {
		BBProfile($bbcode);
		$syntax = $bbcode->parse($syntax);
	}
	elseif ($parser == 0) {
		$syntax = htmlspecialchars($syntax, ENT_NOQUOTES);
	}
	return $syntax;
}

function GroupCheck($groups) {
	global $slog;
	if ($groups == 0 || count(array_intersect(explode(',', $groups), $slog->groups)) > 0) {
		return true;
	}
	else {
		return false;
	}
}

function numbers ($nvar,$deci=null) {
	global $config, $lang;

	if (!is_numeric($nvar)) {
		return $nvar;
	}

	if ($deci == null) {
		$deci = $config['decimals'];
	}
	if (strpos($nvar, '.') === false) {
		$deci = 0;
	}

	return number_format($nvar, $deci, $lang->phrase('decpoint'), $lang->phrase('thousandssep'));
}

function formatFilesize($byte) {
	global $lang;
	$string = $lang->phrase('fs_byte');
	if($byte>=1024) {
		$byte/=1024;
		$string = $lang->phrase('fs_kb');
	}
	if($byte>=1024) {
		$byte/=1024;
		$string = $lang->phrase('fs_mb');
	}
	if($byte>=1024) {
		$byte/=1024;
		$string = $lang->phrase('fs_gb');
	}
	if($byte>=1024) {
		$byte/=1024;
		$string = $lang->phrase('fs_tb');
	}

	if(numbers($byte) != $byte) {
		$byte=numbers($byte);
	}
	return $byte." ".$string;
}

function show_feeds() {
	$data = file('data/feedcreator.inc.php');
	$data = array_map('trim', $data);
	foreach ($data as $feed) {
		$feed = explode("|", $feed);
		if ($feed[3] == 1 && file_exists('classes/feedcreator/'.$feed[1])) {
			$f[$feed[0]] = $feed[2];
		}
	}
	return $f;
}

function get_headboards($fc, $last, $returnids = FALSE) {
	global $breadcrumb;

	$headids = array($last['id']);
	$bc_cache = array();

	while ($last['bid'] > 0 && $fc[$last['bid']]['bid'] > -1) {
		$last = $fc[$last['bid']];
		$bc_cache[] = array(
			'title' => $last['name'],
			'url' => "showforum.php?id=".$last['id'].SID2URL_x
		);
		$headids[] = $last['id'];
	}
	$bc_cache = array_reverse($bc_cache);

	foreach ($bc_cache as $row) {
		$breadcrumb->Add($row['title'], $row['url']);
	}

	if ($returnids == TRUE) {
		return $headids;
	}
}

function count_nl($str='',$max=NULL) {
	if (empty($str)) {
		return 0;
	}
	else {
		preg_match_all("/\r\n|\r|\n/", $str, $treffer);
		$count = count($treffer[0]);
		if ($max == NULL) {
			return $count;
		}
		else {
			if ($max < $count) {
				return $max;
			}
			else {
				return $count;
			}
		}
	}
}

function get_mimetype($file) {
	global $db, $scache;

	$ext = strtolower(get_extension($file));

	$mimetype_headers = $scache->load('mimetype_headers');
	$mime = $mimetype_headers->get();

	if (isset($mime[$ext])) {
		return array(
		'mime' => $mime[$ext]['mimetype'],
		'browser' => $mime[$ext]['stream']
		);
	}
	else {
		return array(
		'mime' => 'application/octet-stream',
		'browser' => 'attachment'
		);
	}
}

define('PAGES_NUM', 1);
define('PAGES_CURRENT', 2);
define('PAGES_SEPARATOR', 4);

/**
 * Gives out html formatted page numbers.
 *
 * It uses the set of templates specified in the last parameter.
 * The template sets are in the directory "main" and are prefixed with "pages".
 * Example: the last parameter is "_small", the main template is "pages_small.html".
 *
 * @param int $anzposts Number of entries
 * @param int $perpage Number of entries per page
 * @param string $uri URL to the page with & or ? at the end (page=X will be appended)
 * @param int $p The current page
 * @param string $template Name of template set (see description)
 * @param boolean $linkrel Enable/Disable the browser based navigation (default: enabled)
 * @return string HTML formatted page numbers and prefix
 */
function pages ($anzposts, $perpage, $uri, $p = 1, $template = '', $linkrel = true) {
	global $config, $tpl, $lang;

	if (!is_id($anzposts)) {
		$anzposts = 1;
	}
	if (!is_id($perpage)) {
		$perpage = 10;
	}

	// Last page / Number of pages
	$anz = ceil($anzposts/$perpage);
	// Array with all page numbers
	$available_pages = range(1, $anz);
	// Page data for template
	$pages = array();

	if ($anz > 10) {
		// What we want to be shown if available
		$show = array(
			1,
			2,
			$p-2,
			$p-1,
			$p,
			$p+1,
			$p+2,
			$anz-1,
			$anz
		);
		$show = array_unique($show);
		foreach ($show as $num) {
			if (in_array($num, $available_pages) == true) {
				if (in_array($num-1, $show) == false && $num > 1) { // Add separator when page numbers are missing
					$pages[$num-1] = array(
						'type' => PAGES_SEPARATOR,
						'url' => null,
						'separator' => false
					);
				}
				$pages[$num] = array(
					'type' => iif($num == $p, PAGES_CURRENT, PAGES_NUM),
					'url' => $uri.'page='.$num.SID2URL_x,
					'separator' => in_array($num+1, $show)
				);
			}
		}
	}
	else {
		for ($i = 1; $i <= $anz; $i++) {
			$pages[$i] = array(
				'type' => iif($i == $p, PAGES_CURRENT, PAGES_NUM),
				'url' => $uri.'page='.$i.SID2URL_x,
				'separator' => ($i != $anz)
			);
		}
	}

	if ($linkrel) {
		if (!defined('LINK_FIRST_PAGE')) {
			define('LINK_FIRST_PAGE', $pages[1]['url']);
		}
		if (!defined('LINK_PREVIOUS_PAGE') && isset($pages[$p-1])) {
			define('LINK_PREVIOUS_PAGE', $pages[$p-1]['url']);
		}
		if (!defined('LINK_NEXT_PAGE') && isset($pages[$p+1])) {
			define('LINK_NEXT_PAGE', $pages[$p+1]['url']);
		}
		if (!defined('LINK_LAST_PAGE') && isset($pages[$anz]) && $anz > 1) {
			define('LINK_LAST_PAGE', $pages[$anz]['url']);
		}
	}

	ksort($pages);

	$tpl->globalvars(compact("uri", "anz", "pages"));
	$lang->assign('anz', $anz);
	return $tpl->parse("main/pages".$template);
}

function t2 ($start = null) {
	if ($start === null) {
		$start = SCRIPT_START_TIME;
	}
	$duration = benchmarktime() - $start;
	$duration = round($duration, 5);
	return $duration;
}


function UpdateTopicStats($topic) {
	global $db;
	$resultc = $db->query("SELECT COUNT(*) as posts FROM {$db->pre}replies WHERE topic_id = '{$topic}' AND tstart = '0'");
	$count = $db->fetch_assoc($resultc);
	$result = $db->query("SELECT date, name FROM {$db->pre}replies WHERE topic_id = '{$topic}' ORDER BY date DESC LIMIT 1");
	$last = $db->fetch_assoc($result);
	$result = $db->query("SELECT id, date, name FROM {$db->pre}replies WHERE topic_id = '{$topic}' ORDER BY date ASC LIMIT 1");
	$start = $db->fetch_assoc($result);
	$db->query("UPDATE {$db->pre}topics SET posts = '{$count['posts']}', last = '{$last['date']}', last_name = '{$last['name']}', date = '{$start['date']}', name = '{$start['name']}' WHERE id = '{$topic}'");
	$db->query("UPDATE {$db->pre}replies SET tstart = '1' WHERE id = '{$start['id']}'");
	return $count['posts'];
}

function SubStats($rtopics, $rreplys, $rid, $cat_cache,$bids=array()) {
	if(isset($cat_cache[$rid])) {
		$sub  = $cat_cache[$rid];
		foreach ($sub as $subfs) {
		array_push($bids, $subfs['id']);
			if (isset($cat_cache[$subfs['id']])) {
				$substats = SubStats(0, 0, $subfs['id'], $cat_cache, $bids);
				$rtopics = $rtopics+$subfs['topics']+$substats[0];
				$rreplys = $rreplys+$subfs['replies']+$substats[1];
				$bids = $substats[2];
			}
			else {
				$rtopics = $rtopics+$subfs['topics'];
				$rreplys = $rreplys+$subfs['replies'];
			}
		}
	}
	return array($rtopics, $rreplys, $bids);
}


function BoardSelect($board = 0) {
	global $config, $my, $tpl, $db, $gpc, $lang, $scache, $plugins, $slog;

	$found = false;
	$sub_cache = $forum_cache = $last_cache = $forums = $cat = array();

	$categories_obj = $scache->load('categories');
	$cat_cache = $categories_obj->get();

	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();

	$index_moderators = $scache->load('index_moderators');
	$mod_cache = $index_moderators->get();

	($code = $plugins->load('forums_query')) ? eval($code) : null;
	// Fetch Forums
	$result = $db->query("
	SELECT
		f.id, f.name, f.description, f.opt, f.optvalue, f.parent, f.topics, f.replies, f.last_topic, f.invisible,
		t.topic as l_topic, t.id as l_tid, t.last as l_date, u.name AS l_uname, t.last_name AS l_name, f.id AS l_bid
	FROM {$db->pre}forums AS f
		LEFT JOIN {$db->pre}topics AS t ON f.last_topic=t.id
		LEFT JOIN {$db->pre}user AS u ON t.last_name=u.id
	ORDER BY f.parent, f.position
	");

	$keys = array('l_topic' => null, 'l_tid' => null, 'l_date' => null, 'l_uname' => null, 'l_name' => null, 'l_bid' => null);

	while($row = $db->fetch_assoc($result)) {
		$row['name'] = $gpc->prepare($row['name']);
		$row['l_uname'] = $gpc->prepare($row['l_uname']);
		$row['l_name'] = $gpc->prepare($row['l_name']);
		$row['bid'] = $cat_cache[$row['parent']]['parent'];
		// Caching for Subforums
		if (!empty($row['bid'])) {
			$sub_cache[$row['bid']][] = $row;
		}
		// Caching the Forums
		if ($row['bid'] == $board) {
			$forum_cache[$row['parent']][] = $row;
		}
		$last_cache[$row['id']] = $row;
		($code = $plugins->load('forums_caching')) ? eval($code) : null;
	}

	$cats = array();
	$hidden = 0;
	// Work with the chached data!
	foreach ($cat_cache as $cat) {
		$cat['forums'] = array();
		if (isset($forum_cache[$cat['id']]) == false) {
			continue;
		}
		foreach ($forum_cache[$cat['id']] as $forum) {
			$found = true;
			$forum['new'] = false;
			$forum['show'] = true;

			// Subforendaten vererben (Letzter Beitrag, Markierung)
			if(isset($sub_cache[$forum['id']])) {
				$substats = SubStats($forum['topics'], $forum['replies'], $forum['id'], $sub_cache);
				$forum['topics'] = $substats[0];
				$forum['replies'] = $substats[1];
			}

			// Letzter Beitrag
			$last = $last_cache[$forum['id']];
			if(isset($sub_cache[$forum['id']])) {
				foreach ($substats[2] as $last_bid) {
					$sub = $last_cache[$last_bid];
					if ($last['l_date'] < $sub['l_date'] && check_forumperm($sub)) {
						$last = $sub;
					}
				}
			}
			$forum = array_merge($forum, array_intersect_key($last, $keys));

			if (is_id($forum['l_name']) && isset($memberdata[$forum['l_name']])) {
				$forum['l_name'] = array($forum['l_uname'], $forum['l_name']);
			}
			else {
				$forum['l_name'] = array($forum['l_name'], 0);
			}

			// Rechte und Gelesensystem
			if ($forum['opt'] != 're') {
				if (!check_forumperm($forum)) {
					if ($forum['invisible'] != 0) {
						$forum['show'] = false;
					}
					$forum['foldimg'] = $tpl->img('cat_locked');
					$forum['state'] = BOARD_STATE_LOCKED;
					$forum['topics'] = '-';
					$forum['replies'] = '-';
					$forum['l_topic'] = false;
				}
				else {
					if ($slog->isForumRead($forum['id'], $forum['l_date']) || $forum['topics'] < 1) {
						$forum['foldimg'] = $tpl->img('cat_open');
						$forum['state'] = BOARD_STATE_OLD;
					}
					else {
					   	$forum['foldimg'] = $tpl->img('cat_red');
					   	$forum['state'] = BOARD_STATE_NEW;
					   	$forum['new'] = true;
					}
					if (!empty($forum['l_topic'])) {
						if (strxlen($forum['l_topic']) > $config['lasttopic_chars']) {
							$forum['l_topic'] = subxstr($forum['l_topic'], 0, $config['lasttopic_chars']);
							$forum['l_topic'] .= "...";
						}
						$forum['l_topic'] = $gpc->prepare($forum['l_topic']);
						$forum['l_date'] = str_date($lang->phrase('dformat1'), times($forum['l_date']));

					}
				}
			}
			$forum['topics'] = numbers($forum['topics']);
			$forum['replies'] = numbers($forum['replies']);

			// Moderatoren
			$forum['mod'] = array();
			if(isset($mod_cache[$forum['id']])) {
				$anz2 = count($mod_cache[$forum['id']]);
				for($i = 0; $i < $anz2; $i++) {
					if ($anz2 != $i+1) {
						$mod_cache[$forum['id']][$i]['sep'] = ', ';
					}
					else {
						$mod_cache[$forum['id']][$i]['sep'] = '';
					}
					$forum['mod'][] = $mod_cache[$forum['id']][$i];
				}
			}
			// Unterforen
			$forum['sub'] = array();
			if ($config['showsubfs']) {
				if(isset($sub_cache[$forum['id']])) {
					$anz2 = count($sub_cache[$forum['id']]);
					$sub = array();
					for($i = 0; $i < $anz2; $i++) {
						$show = true;
						$sub_cache[$forum['id']][$i]['new'] = false;
						if ($sub_cache[$forum['id']][$i]['opt'] != 're') {
							if (!check_forumperm($sub_cache[$forum['id']][$i])) {
								if ($sub_cache[$forum['id']][$i]['invisible'] != 0) {
									$show = false;
								}
								else {
									$sub_cache[$forum['id']][$i]['foldimg'] = $tpl->img('subcat_locked');
									$sub_cache[$forum['id']][$i]['state'] = BOARD_STATE_LOCKED;
								}
							}
							else {
								if ($slog->isForumRead($sub_cache[$forum['id']][$i]['id'], $sub_cache[$forum['id']][$i]['l_date']) || $sub_cache[$forum['id']][$i]['topics'] < 1) {
									$sub_cache[$forum['id']][$i]['foldimg'] = $tpl->img('subcat_open');
									$sub_cache[$forum['id']][$i]['state'] = BOARD_STATE_OLD;
								}
								else {
								   	$sub_cache[$forum['id']][$i]['foldimg'] = $tpl->img('subcat_red');
								   	$sub_cache[$forum['id']][$i]['state'] = BOARD_STATE_NEW;
								   	$sub_cache[$forum['id']][$i]['new'] = true;
								}
							}
						}
						else {
							$sub_cache[$forum['id']][$i]['foldimg'] = $tpl->img('subcat_redirect');
							$sub_cache[$forum['id']][$i]['state'] = BOARD_STATE_WWW;
						}
						if ($show == true) {
							$forum['sub'][] = $sub_cache[$forum['id']][$i];
						}
					}
				}
			}
			($code = $plugins->load('forums_entry_prepared')) ? eval($code) : null;
			if ($forum['show'] == true) {
				$cat['forums'][] = $forum;
			}
			elseif ($forum['invisible'] != 2) {
				$hidden++;
			}
		}
		if (count($cat['forums']) > 0) {
			$cats[] = $cat;
		}
	}

	($code = $plugins->load('forums_prepared')) ? eval($code) : null;
	$error_state = (count($cats) == 0 && $board == 0);
	if (count($cats) > 0 || $error_state) {
		$tpl->globalvars(compact("cats", "board", "hidden", "error_state"));
		echo $tpl->parse("categories");
	} // Else: This is a forum without sub forums (that should be displayed)

	return $found;
}


function GoBoardPW ($bpw, $bid) {
	extract($GLOBALS, EXTR_SKIP);
	if(!isset($my->pwfaccess[$bid]) || $my->pwfaccess[$bid] != $bpw) {
		($code = $plugins->load('frontend_goboardpw')) ? eval($code) : null;
		$tpl->globalvars(compact("bid"));
		echo $tpl->parse("main/boardpw");
		$slog->updatelogged();
		$zeitmessung = t2();
		$tpl->globalvars(compact("zeitmessung"));
		echo $tpl->parse("footer");
		$phpdoc->Out();
		$db->close();
		exit;
	}
}

function general_message($errortpl, $errorhook, $errormsg, $errorurl, $EOS) {
	extract($GLOBALS, EXTR_SKIP);

	if ($errorurl == null) {
		$errorurl = getRefererURL();
	}

	if (!empty($errorurl)) {
		$js_errorurl = html_entity_decode($errorurl, ENT_NOQUOTES);
		$errorurl = preg_replace('~&(?!amp;)~i', '&amp;', $errorurl);
	}
	else {
		$js_errorurl = $errorurl = "javascript:history.back(-1)";
	}

	if (!isset($my->p)) {
		$my->p = $slog->Permissions();
	}

	$breadcrumb->Add($lang->phrase('breadcrumb_errorok'));
	if (!$tpl->tplsent('header') && !$tpl->tplsent('popup/header')) {
		echo $tpl->parse('header');
	}

	($code = $plugins->load('frontend_'.$errorhook)) ? eval($code) : null;
	$tpl->globalvars(compact("errormsg", "errorurl", "js_errorurl"));
	echo $tpl->parse("main/{$errortpl}");

	$slog->updatelogged();
	$zeitmessung = t2();
	$tpl->globalvars(compact("zeitmessung"));
	if ($EOS != null) {
		echo $tpl->parse($EOS);
	}
	elseif ($tpl->tplsent('popup/header')) {
		echo $tpl->parse('popup/footer');
	}
	else {
		echo $tpl->parse('footer');
	}
	$phpdoc->Out();
	$db->close();
	exit;
}

function errorLogin($errormsg = null, $errorurl = null, $EOS = null) {
	if ($errormsg == null) {
		global $lang;
		$errormsg = array($lang->phrase('not_allowed'));
	}
	elseif (!is_array($errormsg)) {
		$errormsg = array($errormsg);
	}

	if ($errorurl == null) {
		$errorurl = htmlspecialchars(getRequestURI());
	}

	general_message('not_allowed', 'errorlogin', $errormsg, $errorurl, $EOS);
}

function error($errormsg = null, $errorurl = null, $EOS = null) {
	if ($errormsg == null) {
		global $lang;
		$errormsg = array($lang->phrase('unknown_error'));
	}
	elseif (!is_array($errormsg)) {
		$errormsg = array($errormsg);
	}

	general_message('error', 'error', $errormsg, $errorurl, $EOS);
}

function ok($errormsg = null, $errorurl = null, $EOS = null) {
	if ($errormsg == null) {
		global $lang;
		$errormsg = $lang->phrase('unknown_ok');
	}

	general_message('ok', 'ok', $errormsg, $errorurl, $EOS);
}

function forum_opt($array, $check = 'forum') {
	global $my, $lang, $tpl;
	extract($array, EXTR_PREFIX_ALL, 'f');
	if ($f_opt == 'pw' && (!isset($my->pwfaccess[$f_id]) || $my->pwfaccess[$f_id] != $f_optvalue)) {
		if (!$tpl->tplsent('header')) {
			echo $tpl->parse('header');
		}
		if (!$tpl->tplsent('menu')) {
			echo $tpl->parse('menu');
		}
		GoBoardPW($f_optvalue, $f_id);
	}
	elseif ($f_opt == "re") {
		error($lang->phrase('forumopt_re'), $f_optvalue);
	}
	elseif ($f_invisible == 2) {
		error($lang->phrase('query_string_error'));
	}
	elseif (($check == 'postreplies' || $check == 'posttopics' || $check == 'edit') && $f_readonly == '1') {
		error($lang->phrase('forum_is_read_only'));
	}
	elseif ($my->p[$check] == 0 || $my->p['forum'] == 0) {
		errorLogin();
	}

}

function import_error_data($fid) {
	$cache = new CacheItem($fid, 'temp/errordata/');
	$cache->import();
	$data = $cache->get();
	return $data;
}
function save_error_data($fc, $fid = '') {
	global $gpc;
	if (!is_hash($fid)) {
		$fid = md5(microtime());
	}

	$cache = new CacheItem($fid, 'temp/errordata/');
	$cache->set($fc);
	$cache->export();
	return $fid;
}

function count_filled($array) {
	$int = 0;
	foreach ($array as $val) {
		if (!empty($val) || strlen($val) > 0) {
			$int++;
		}
	}
	return $int;
}

function get_pmdir ($dir) {
	global $lang, $plugins;

	if ($dir == '1') {
		$dir_name = $lang->phrase('pm_dirs_inbox');
	}
	elseif ($dir == '2') {
		$dir_name = $lang->phrase('pm_dirs_outbox');
	}
	elseif ($dir == '3') {
		$dir_name = $lang->phrase('pm_dirs_archive');
	}
	else {
		$dir_name = false;
	}
	return $dir_name;
}
?>