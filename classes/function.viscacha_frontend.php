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

use Viscacha\System\PhpSys;

require_once("classes/function.frontend_init.php");

define('BOARD_STATE_OLD', 0);
define('BOARD_STATE_NEW', 1);
define('BOARD_STATE_LOCKED', 2);
define('BOARD_STATE_WWW', 3);

function getRedirectURL($standard = true) {
	global $gpc;
	$loc = strip_tags($gpc->get('redirect', none));
	$loc = preg_replace('~(\?|&)s=[A-Za-z0-9]*~iu', '', $loc);
	if (is_url($loc)) {
		$url = parse_url($loc);
		$file = !empty($url['path']) ? basename($url['path']) : '';
	}
	else {
		$file = basename($loc);
	}
	if (\Str::contains($file, '?')) {
		$parts = explode('?', $file, 2);
		$file = $parts[0];
		if (!empty($parts[1])) {
			parse_str($parts[1], $q);
			if (!empty($q['action']) && \Str::substr($q['action'], -1) == '2') {
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
		if (!\Str::contains($loc, '?')) {
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
	$method = (isset($_SERVER['REQUEST_METHOD']) && \Str::upper($_SERVER['REQUEST_METHOD']) == 'GET');
	if (empty($_SERVER['REQUEST_URI']) == false && $method == true) {
		$request_uri = '';
		$var = parse_url($config['furl']);
		$request_uri = sprintf('http%s://%s%s',
			(PhpSys::isHttps() ? 's': ''),
			(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $var['host']),
			$_SERVER['REQUEST_URI']
		);
		if (is_url($request_uri)) {
			$request_uri = preg_replace('~(\?|&)s=[A-Za-z0-9]*~iu', '', $request_uri);
			$url = parse_url($request_uri);
			if (empty($url['path'])) {
				$url['path'] = '';
			}
			$file = basename($url['path']);
			if (!empty($loc) && file_exists($file) && $file != 'log.php' && $file != 'register.php') {
				if (!\Str::contains($loc, '?')) {
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
	if (is_url($_SERVER['HTTP_REFERER'])) {
		$url = parse_url($_SERVER['HTTP_REFERER']);
		if (!empty($url['query'])) {
			parse_str($url['query'], $q);
			if (!empty($q['action']) && \Str::substr($q['action'], -1) == '2') {
				return ''; // When the last char of the value of action is 2 we have in most cases a "POST" form
			}
		}
		if (!empty($url['host']) && \Str::contains($config['furl'], $url['host'])) {
			$request_uri = $_SERVER['HTTP_REFERER'];
		}
		$request_uri = preg_replace('~(\?|&)s=[A-Za-z0-9]*~iu', '', $request_uri);
		if (empty($url['path'])) {
			$url['path'] = '';
		}
		$file = basename($url['path']);
		if (!empty($loc) && file_exists($file) && $file != 'log.php' && $file != 'register.php') {
			if (!\Str::contains($loc, '?')) {
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
	$pos1 = \Str::indexOf($cc, '{pagebreak}', false);
	if ($pos1 === false) {
		return array($cc, '');
	}
	else {
		$page = $_GET['page']-1;
		$pgc = preg_split("~(<br[^>]*>|\n|\r)*\{pagebreak\}(<br[^>]*>|\n|\r)*~iu", $cc, -1, PREG_SPLIT_NO_EMPTY);
		if (!isset($pgc[$page])) {
			$page = 0;
			$_GET['page'] = 1;
		}
		$id = \Str::toHtml($_GET['id']);
		$pages = pages(count($pgc), 1, "docs.php?id={$id}&amp;", $_GET['page']);
		return array($pgc[$page], $pages);
	}
}

function DocCodeParser($syntax, $parser = 'html') {
	if ($parser == 'php') {
		$code = str_replace('<'.'?php','<'.'?',$syntax);
		$code = '?'.'>'.trim($code).'<'.'?';
		extract($GLOBALS, EXTR_SKIP);
		ob_start();
		eval($code);
		$syntax = ob_get_contents();
		ob_end_clean();
	}
	elseif ($parser == 'bbcode') {
		global $bbcode;
		BBProfile($bbcode);
		$bbcode->setSmileys();
		$syntax = $bbcode->parse($syntax);
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
		Breadcrumb::universal()->add($row['title'], $row['url']);
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
		preg_match_all("/\r\n|\r|\n/u", $str, $treffer);
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
	$ext = get_extension($file);
	switch($ext) {
		case 'gif':
		case 'png':
		case 'bmp':
			return array(
				'mime' => 'image/'.$ext,
				'browser' => 'inline'
			);
		case 'jpeg':
		case 'jpg':
			return array(
				'mime' => 'image/jpeg',
				'browser' => 'inline'
			);
		case 'txt':
			return array(
				'mime' => 'text/plain',
				'browser' => 'inline'
			);
		case 'html':
		case 'htm':
			return array(
				'mime' => 'text/html',
				'browser' => 'attachment' // inline might lead to an XSS attack
			);
		case 'pdf':
			return array(
				'mime' => 'application/pdf',
				'browser' => 'inline'
			);
		default:
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
 * The template sets are in the directory "main".
 *
 * @param int $anzposts Number of entries
 * @param int $perpage Number of entries per page
 * @param string $uri URL to the page with & or ? at the end (page=X will be appended)
 * @param int $page The current page
 * @param string $template Name of template set
 * @return string HTML formatted page numbers and prefix
 */
function pages ($anzposts, $perpage, $uri, $page = 1, $template = 'pages') {
	global $tpl;

	if ($anzposts < 0) {
		$anzposts = 0;
	}
	if ($perpage < 1) {
		$perpage = 10;
	}

	// Last page / Number of pages
	$pagecount = ceil($anzposts/$perpage);
	// Theoretical page number we want to show and remove duplicates
	if ($page == 0) {
		$pages = array_unique(array(1,2,$pagecount-1,$pagecount));
	}
	else if ($pagecount <= 5) {
		$pages = range(1, $pagecount);
	}
	else {
		$pages = array_unique(array(
			1,
			$page - 1,
			$page,
			$page + 1,
			$pagecount
		));
		// Filter invalid page numbers
		$pages = array_filter($pages, function($value) use ($pagecount) {
			return ($value >= 1 && $value <= $pagecount);
		});
		// normalize keys (enumerated, beginning with 0, increasing by 1)
		$pages = array_values($pages);
	}

	$tpl->assignVars(compact("uri", "page", "pagecount", "pages"));
	return $tpl->parse("main/{$template}");
}

function UpdateTopicStats($topic) {
	global $db;
	$resultc = $db->execute("SELECT COUNT(*) as posts FROM {$db->pre}replies WHERE topic_id = '{$topic}' AND tstart = '0'");
	$count = $resultc->fetch();
	$result = $db->execute("SELECT date, name FROM {$db->pre}replies WHERE topic_id = '{$topic}' ORDER BY date DESC LIMIT 1");
	$last = $result->fetch();
	$result = $db->execute("SELECT id, date, name FROM {$db->pre}replies WHERE topic_id = '{$topic}' ORDER BY date ASC LIMIT 1");
	$start = $result->fetch();
	$db->execute("UPDATE {$db->pre}topics SET posts = '{$count['posts']}', last = '{$last['date']}', last_name = '{$last['name']}', date = '{$start['date']}', name = '{$start['name']}' WHERE id = '{$topic}'");
	$db->execute("UPDATE {$db->pre}replies SET tstart = '1' WHERE id = '{$start['id']}'");
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

	$index_moderators = $scache->load('index_moderators');
	$mod_cache = $index_moderators->get();

	$prefix_obj = $scache->load('prefix');
	$prefix = $prefix_obj->get();

	($code = $plugins->load('forums_query')) ? eval($code) : null;
	// Fetch Forums
	$result = $db->execute("
	SELECT
		f.id, f.name, f.description, f.opt, f.optvalue, f.parent, f.topics, f.replies, f.last_topic, f.invisible,
		t.topic as l_topic, t.prefix AS l_prefix, t.id as l_tid, t.last as l_date, u.name AS l_name, u.id AS l_uid, f.id AS l_bid
	FROM {$db->pre}forums AS f
		LEFT JOIN {$db->pre}topics AS t ON f.last_topic = t.id
		LEFT JOIN {$db->pre}user AS u ON t.last_name= u.id
	ORDER BY f.parent, f.position
	");

	$keys = array(
		'l_prefix' => null,
		'l_topic_full' => null,
		'l_topic' => null,
		'l_tid' => null,
		'l_date' => null,
		'l_uid' => null,
		'l_name' => null,
		'l_bid' => null
	);

	while($row = $result->fetch()) {
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

			// Rechte und Gelesensystem
			if ($forum['opt'] != 're') {
				if (!check_forumperm($forum)) {
					if ($forum['invisible'] != 0) {
						$forum['show'] = false;
					}
					$forum['state'] = BOARD_STATE_LOCKED;
					$forum['topics'] = '-';
					$forum['replies'] = '-';
					$forum['l_topic'] = false;
					$forum['l_topic_full'] = '';
				}
				else {
					if ($slog->isForumRead($forum['id'], $forum['l_date']) || $forum['topics'] < 1) {
						$forum['state'] = BOARD_STATE_OLD;
					}
					else {
					   	$forum['state'] = BOARD_STATE_NEW;
					}
					if (!empty($forum['l_topic'])) {
						if (isset($prefix[$forum['id']][$forum['l_prefix']]) && $forum['l_prefix'] > 0) {
							$forum['l_prefix'] = '[' . $prefix[$forum['id']][$forum['l_prefix']]['value'] . ']';
						}
						else {
							$forum['l_prefix'] = '';
						}

						if (\Str::length($forum['l_topic']) > $config['lasttopic_chars']) {
							$forum['l_topic_full'] = $forum['l_prefix'].$forum['l_topic'];
							$forum['l_topic'] = \Str::limit($forum['l_topic'], $config['lasttopic_chars']);
						}
						else {
							$forum['l_topic_full'] = '';
						}
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
						if ($sub_cache[$forum['id']][$i]['opt'] != 're') {
							if (!check_forumperm($sub_cache[$forum['id']][$i])) {
								if ($sub_cache[$forum['id']][$i]['invisible'] != 0) {
									$show = false;
								}
								else {
									$sub_cache[$forum['id']][$i]['state'] = BOARD_STATE_LOCKED;
								}
							}
							else {
								if ($slog->isForumRead($sub_cache[$forum['id']][$i]['id'], $sub_cache[$forum['id']][$i]['l_date']) || $sub_cache[$forum['id']][$i]['topics'] < 1) {
									$sub_cache[$forum['id']][$i]['state'] = BOARD_STATE_OLD;
								}
								else {
								   	$sub_cache[$forum['id']][$i]['state'] = BOARD_STATE_NEW;
								}
							}
						}
						else {
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
		$tpl->assignVars(compact("cats", "board", "hidden", "error_state"));
		echo $tpl->parse("categories");
	} // Else: This is a forum without sub forums (that should be displayed)

	return $found;
}


function GoBoardPW ($bpw, $bid) {
	extract($GLOBALS, EXTR_SKIP);
	if(!isset($my->pwfaccess[$bid]) || $my->pwfaccess[$bid] != $bpw) {
		($code = $plugins->load('frontend_goboardpw')) ? eval($code) : null;
		$tpl->assignVars(compact("bid"));
		echo $tpl->parse("main/boardpw");
		$slog->updatelogged();
		$phpdoc->send();
		exit;
	}
}

function general_message($errortpl, $errorhook, $errormsg, $errorurl, $EOS) {
	extract($GLOBALS, EXTR_SKIP);

	if ($errorurl == null) {
		$errorurl = getRefererURL();
	}

	if (!empty($errorurl)) {
		$js_errorurl = \Str::fromHtml($errorurl, ENT_NOQUOTES);
		$errorurl = preg_replace('~&(?!amp;)~iu', '&amp;', $errorurl);
	}
	else {
		$js_errorurl = $errorurl = "javascript:history.back(-1)";
	}

	if (!isset($my->p)) {
		$my->p = $slog->Permissions();
	}

	Breadcrumb::universal()->add($lang->phrase('breadcrumb_errorok'));
	if (!$tpl->wasTemplateSent('header') && !$tpl->wasTemplateSent('popup/header')) {
		echo $tpl->parse('header');
	}

	($code = $plugins->load('frontend_'.$errorhook)) ? eval($code) : null;
	$tpl->assignVars(compact("errormsg", "errorurl", "js_errorurl"));
	echo $tpl->parse("main/{$errortpl}");

	if ($EOS != null) {
		echo $tpl->parse($EOS);
	}
	elseif ($tpl->wasTemplateSent('popup/header')) {
		echo $tpl->parse('popup/footer');
	}
	else {
		echo $tpl->parse('footer');
	}
	$slog->updatelogged();
	$phpdoc->send();
	exit;
}

function errorLogin($errormsg = null, $errorurl = null) {
	if ($errormsg == null) {
		global $lang;
		$errormsg = array($lang->phrase('not_allowed'));
	}
	elseif (!is_array($errormsg)) {
		$errormsg = array($errormsg);
	}

	if ($errorurl == null) {
		$errorurl = 'index.php' . SID2URL_1;
	}

	general_message('not_allowed', 'errorlogin', $errormsg, $errorurl, null);
}

function error($errormsg = null, $errorurl = null, $EOS = null) {
	if ($errormsg == null) {
		global $lang;
		$errormsg = array($lang->phrase('unknown_error'));
	}
	elseif (!is_array($errormsg)) {
		$errormsg = array($errormsg);
	}
	
	if (!empty($errorurl) && stripos($errorurl, 'javascript:') === false) {
		Viscacha\View\FlashMessage::addError($errormsg);
		global $slog;
		$slog->updatelogged();
		sendStatusCode(302, \Str::fromHtml($errorurl));
	}

	general_message('error', 'error', $errormsg, $errorurl, $EOS);
}

function ok($errormsg = null, $errorurl = null, $EOS = null) {
	if ($errormsg == null) {
		global $lang;
		$errormsg = $lang->phrase('unknown_ok');
	}
	
	if (!empty($errorurl) && stripos($errorurl, 'javascript:') === false) {
		Viscacha\View\FlashMessage::addConfirmation($errormsg);
		global $slog;
		$slog->updatelogged();
		sendStatusCode(302, \Str::fromHtml($errorurl));
	}

	general_message('ok', 'ok', $errormsg, $errorurl, $EOS);
}

function forum_opt($array, $check = 'forum') {
	global $my, $lang, $tpl;
	if (!is_array($array)) {
		error($lang->phrase('query_string_error'));
	}
	extract($array, EXTR_PREFIX_ALL, 'f');
	if ($f_opt == 'pw' && (!isset($my->pwfaccess[$f_id]) || $my->pwfaccess[$f_id] != $f_optvalue)) {
		if (!$tpl->wasTemplateSent('header')) {
			echo $tpl->parse('header');
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
		$fid = generate_uid();
	}

	$cache = new CacheItem($fid, 'temp/errordata/');
	$cache->set($fc);
	$cache->export();
	return $fid;
}

function count_filled($array) {
	$int = 0;
	foreach ($array as $val) {
		if (!empty($val)) {
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