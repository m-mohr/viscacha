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

define('SCRIPTNAME', 'search');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$my->p = $slog->Permissions();
$my->pb = $slog->GlobalPermissions();

if ($my->p['search'] == 0) {
	error($lang->phrase('query_string_error'));
}

$breadcrumb->Add($lang->phrase('search'));

($code = $plugins->load('search_start')) ? eval($code) : null;

if ($_GET['action'] == "search") {

	if ($config['floodsearch'] == 1) {
		if (flood_protect(FLOOD_TYPE_SEARCH) == false) {
			error($lang->phrase('flood_control'));
		}
		set_flood(FLOOD_TYPE_SEARCH);
	}
	$boards = $gpc->get('boards', arr_int);
	$search = preg_replace("/(\s){1,}/is", " ", $gpc->get('search', str));
    $search = preg_replace("/\*{1,}/is", '*', $search);
    $searchwords = splitWords($search);
	$ignorewords = $lang->get_words();

	$ignored = array();
	$used = array();
	foreach ($searchwords as $sw) {
		if ($sw{0} == '-') {
			$sw2 = substr($sw, 1);
		}
		else {
			$sw2 = $sw;
		}
		$sw2 = str_replace('*', '', $sw2);
		if (in_array(strtolower($sw2), $ignorewords) || strxlen($sw2) < $config['searchminlength']) {
			$ignored[] = $sw2;
		}
		else {
			$used[] = str_replace('*', '%', $sw);
		}
	}

	$name = $gpc->get('name', str);
	if (strxlen($name) >= $config['searchminlength']) {
		$result = $db->query("SELECT id FROM {$db->pre}user WHERE name = '{$name}' LIMIT 1");
		if ($db->num_rows($result) == 1) {
			list($rname) = $db->fetch_num($result);
		}
		else {
			$rname = $name;
		}
	}

	if ((count($used) == 0 || count($used) > 8) && empty($rname)) {
		error($lang->phrase('illegal_search'));
	}

	$sql_where_like = '';

	if ($gpc->get('opt_2', int) == 1) {
		$op = 'OR ';
	}
	else {
		$op = 'AND ';
	}
	if ($gpc->get('opt_1', int) == 1) {
		$binary = ' BINARY';
	}
	else {
		$binary = '';
	}

	$range = count($used);
	for ($i=0;$i<$range;$i++) {
		$str = $used[$i];
		if ($str{0} == '-') {
			$not = 'NOT ';
			$str = substr($str, 1);
		}
		else {
			$not = '';
		}
		if ($i > 0) {
			$sql_where_like .= $op.$not;
		}
		if ($gpc->get('opt_0', int) == 0) {
			$sql_where_like .= "(r.topic LIKE{$binary} '%{$str}%' OR r.comment LIKE{$binary} '%{$str}%') ";
		}
		else {
			$sql_where_like .= "r.topic LIKE{$binary} '%{$str}%' ";
		}
	}

	if (array_empty($boards)) {
		$boards = $slog->getBoards();
	}
	$sql_where = $slog->sqlinboards('r.board', 1, $boards)." ";

	if (count($used) > 0) {
		$sql_where .= "({$sql_where_like}) ";
	}

	if (empty($rname) == false) {
		if (count($used) > 0) {
			$sql_where .= "AND ";
		}
		$sql_where .= "r.name = '{$rname}' ";
	}

	if (strxlen($name) >= $config['searchminlength']) {
		$used[] = $name;
	}
	else {
		$ignored[] = $name;
	}

	$having = '';
	$temp = $gpc->get('temp', int);
	$temp2 = $gpc->get('temp2', int);
	if ($temp > 0 && $temp < 366) {
		$sql_where .= "AND t.last ";
		if ($temp2 == 1) {
			$sql_where .= '<=';
		}
		else {
			$sql_where .= '>=';
		}
		$timestamp = time()-60*60*24*$temp;
		$sql_where .= " '{$timestamp}' ";
		$having = " LEFT JOIN {$db->pre}topics AS t ON t.id = r.topic_id";
	}
	$having .= " LEFT JOIN {$db->pre}forums AS f ON f.id = r.board ";
	$sql_where .= " AND f.invisible != '2' ";

	($code = $plugins->load('search_search_query')) ? eval($code) : null;
	$result = $db->query("
	SELECT r.topic_id
	FROM {$db->pre}replies AS r {$having}
	WHERE {$sql_where}
	GROUP BY r.topic_id
	LIMIT {$config['maxsearchresults']}
	");

	$searchresult = array();
	while ($row = $db->fetch_assoc($result)) {
		$searchresult[] = $row['topic_id'];
	}

	if (count($searchresult) > 0) {
		$data = array(
			'ids' => $searchresult,
			'ignored' => $ignored,
			'used' => $used,
			'search' => $gpc->get('search', str),
			'name' => $gpc->get('name', str),
			'boards' => $gpc->get('boards', arr_int),
			'opt_0' => $gpc->get('opt_0', int),
			'opt_1' => $gpc->get('opt_1', int),
			'opt_2' => $gpc->get('opt_2', int),
			'temp' => $gpc->get('temp', int),
			'temp2' => $gpc->get('temp2', int),
			'sort' => $gpc->get('sort', str),
			'order' => $gpc->get('order', str)
		);
		$fid = md5(microtime());
		file_put_contents('cache/search/'.$fid.'.inc.php', serialize($data));
		$slog->updatelogged();
		$db->close();
		viscacha_header('Location: search.php?action=result&fid='.$fid.SID2URL_JS_x);
		exit;
	}
	else {
		error($lang->phrase('search_nothingfound'), 'search.php'.SID2URL_1);
	}
}
elseif ($_GET['action'] == "result") {
	$fid = $gpc->get('fid');
	if (!is_hash($fid)) {
		error($lang->phrase('query_string_error'), 'search.php'.SID2URL_1);
	}
	$file = "cache/search/{$fid}.inc.php";
	if (!file_exists($file)) {
		error($lang->phrase('search_doesntexist'), 'search.php'.SID2URL_1);
	}
	$data = file_get_contents($file);
	$data = unserialize($data);

	$ignored = array();
	foreach ($data['ignored'] as $row) {
	    $row = trim($row);
	    if (!empty($row)) {
	        $ignored[] = $row;
	    }
	}

	$start = $_GET['page']*$config['searchzahl'];
	$start = $start-$config['searchzahl'];

	switch ($data['sort']) {
		case 'topic':
		case 'posts':
		case 'date':
		case 'last':
			$order = $data['sort'];
			break;
		case 'name':
		case 'board':
			$order = $data['sort'].", last";
			break;
		default:
			$order = 'last';
			break;
	}

	if ($data['order'] == 1) {
		$order .= ' ASC';
	}
	else {
		$order .= ' DESC';
	}

	($code = $plugins->load('search_result_query')) ? eval($code) : null;
	$result = $db->query("
	SELECT prefix, vquestion, posts, mark, id, board, topic, date, status, last, last_name, sticky, name
	FROM {$db->pre}topics
	WHERE id IN (".implode(',', $data['ids']).") ".$slog->sqlinboards('board')."
	ORDER BY {$order}"
	);

	$cache = array();
	while ($row = $gpc->prepare($db->fetch_object($result))) {
		($code = $plugins->load('search_result_prepare')) ? eval($code) : null;
		$cache[] = $row;
	}

	$count = count($cache);
	if ($count == 0) {
		error($lang->phrase('illegal_search'), 'search.php'.SID2URL_1);
	}
	$pages = array_chunk($cache, $config['searchzahl']);

	$temp = pages($count, $config['searchzahl'], "search.php?action=result&amp;fid=".$fid.SID2URL_x."&amp;", $_GET['page']);

	$catbid = $scache->load('cat_bid');
	$forums = $catbid->get();
	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();
	$prefix_obj = $scache->load('prefix');
	$prefix_arr = $prefix_obj->get();

	$inner['index_bit'] = '';

	if (!isset($pages[$_GET['page']-1])) {
		$pages[$_GET['page']-1] = array();
	}

	foreach ($pages[$_GET['page']-1] as $row) {
		$pref = '';
		$prefix = '';
		$showprefix = false;
		if (isset($prefix_arr[$row->board][$row->prefix]) && $row->prefix > 0) {
			$showprefix = true;
			$prefix = $prefix_arr[$row->board][$row->prefix]['value'];
		}
		$info = $forums[$row->board];

		if(is_id($row->name) && isset($memberdata[$row->name])) {
			$row->mid = $row->name;
			$row->name = $memberdata[$row->name];
		}
		else {
			$row->mid = FALSE;
		}

		if (is_id($row->last_name) && isset($memberdata[$row->last_name])) {
			$row->last_name = $memberdata[$row->last_name];
		}

		$rstart = str_date($lang->phrase('dformat1'),times($row->date));
		$rlast = str_date($lang->phrase('dformat1'),times($row->last));

		if ($row->status == '2') {
			$pref .= $lang->phrase('forum_moved');
		}
		else {
			if ($row->mark === null && !empty($info['auto_status'])) {
				$row->mark = $info['auto_status'];
			}
			if ($row->mark == 'n') {
				$pref .= $lang->phrase('forum_mark_n');
			}
			elseif ($row->mark == 'a') {
				$pref .= $lang->phrase('forum_mark_a');
			}
			elseif ($row->mark == 'b') {
				$pref .= $lang->phrase('forum_mark_b');
			}
			elseif ($row->mark == 'g') {
				$pref .= $lang->phrase('forum_mark_g');
			}
			if ($row->sticky == '1') {
				$pref .= $lang->phrase('forum_announcement');
			}
		}

		if ($slog->isTopicRead($row->id, $row->last)) {
	 		$firstnew = 0;
			if ($row->status == 1 || $row->status == 2) {
			   	$alt = $lang->phrase('forum_icon_closed');
				$src = $tpl->img('dir_closed');
			}
			else {
			   	$alt = $lang->phrase('forum_icon_old');
			   	$src = $tpl->img('dir_open');
	 		}
	 	}
	  	else {
	  		$firstnew = 1;
			if ($row->status == 1 || $row->status == 2) {
				$alt = $lang->phrase('forum_icon_closed');
				$src = $tpl->img('dir_closed2');
			}
			else {
				$alt = $lang->phrase('forum_icon_new');
				$src = $tpl->img('dir_open2');
			}
		}
		$qhighlight = urlencode(implode(' ', $data['used']));

		if ($info['topiczahl'] < 1) {
			$info['topiczahl'] = $config['topiczahl'];
		}

		if ($row->posts > $info['topiczahl']) {
			$topic_pages = pages($row->posts+1, $info['topiczahl'], "showtopic.php?id=".$row->id."&amp;", 0, '_small', false);
		}
		else {
			$topic_pages = '';
		}

		($code = $plugins->load('search_result_entry_prepared')) ? eval($code) : null;
		$inner['index_bit'] .= $tpl->parse("search/result_bit");
	}

	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	($code = $plugins->load('search_result_prepared')) ? eval($code) : null;
	echo $tpl->parse("search/result");
	($code = $plugins->load('search_result_end')) ? eval($code) : null;

}
elseif ($_GET['action'] == "active") {

	$breadcrumb->AddUrl('search.php'.SID2URL_1);
	$breadcrumb->Add($lang->phrase('active_topics_title'));

    unset($count);

	$sqlwhere = "";
	if ($_GET['type'] == 'abo' || $_GET['type'] == 'fav') {
	    if (!$my->vlogin) {
	        error($lang->phrase('not_allowed'));
	    }
		$timestamp = $my->clv;
		$ids = array();
   		$result = $db->query("SELECT tid FROM {$db->pre}abos WHERE mid = '{$my->id}' AND type ".iif($_GET['type'] == 'fav', '=', '!=')." 'f'");
   		if ($db->num_rows($result) > 0) {
       		while ($row = $db->fetch_assoc($result)) {
       			$ids[] = $row['tid'];
       		}
       		$sqlwhere .= " id IN (".implode(',', $ids).") AND ";
   		}
   		else {
		    $count = 0;
	        echo $tpl->parse("search/active");
   		}
	}
	elseif (preg_match("/(days|hours)-(\d{1,2})/i", $_GET['type'], $type)) {
		$type[1] = strtolower($type[1]);
		if (empty($type[1])) {
			$type[1] = 'days';
		}
		if (empty($type[2])) {
			$type[2] = 1;
		}
		if ($type[2] > 14) {
			$type[2] = 14;
		}
		if ($type[1] == 'days') {
			$type[2] = $type[2]*24;
		}
		$timestamp = time()-60*60*$type[2];
	}
	else { // $_GET['type'] == 'lastvisit'
		$timestamp = $my->clv;
	}

	($code = $plugins->load('search_actiev_start')) ? eval($code) : null;

	if (!isset($count)) {
    	$sqlwhere = " last > '{$timestamp}' ";

    	$start = ($_GET['page']-1)*$config['activezahl'];

    	($code = $plugins->load('search_actiev_query')) ? eval($code) : null;
    	$result = $db->query("
    	SELECT COUNT(*)
    	FROM {$db->pre}topics AS t
    		LEFT JOIN {$db->pre}forums AS f ON f.id = t.board
    	WHERE f.invisible != '2' AND f.active_topic = '1' AND {$sqlwhere} ".$slog->sqlinboards('t.board')
    	);
    	list($count) = $db->fetch_num($result);

    	$result = $db->query("
    	SELECT t.prefix, t.vquestion, t.posts, t.mark, t.id, t.board, t.topic, t.date, t.status, t.last, t.last_name, t.sticky, t.name
    	FROM {$db->pre}topics AS t
    		LEFT JOIN {$db->pre}forums AS f ON f.id = t.board
    	WHERE f.invisible != '2' AND f.active_topic = '1' AND {$sqlwhere} ".$slog->sqlinboards('t.board')."
    	ORDER BY t.last DESC
    	LIMIT {$start}, {$config['activezahl']}"
    	);

    	if ($count > 0) {
    		$temp = pages($count, $config['activezahl'], "search.php?action=active&amp;type=".$_GET['type'].SID2URL_x."&amp;", $_GET['page']);

			$catbid = $scache->load('cat_bid');
			$forums = $catbid->get();
			$prefix_obj = $scache->load('prefix');
			$prefix_arr = $prefix_obj->get();
    		$memberdata_obj = $scache->load('memberdata');
			$memberdata = $memberdata_obj->get();

    		$inner['index_bit'] = '';
    		while ($row = $gpc->prepare($db->fetch_object($result))) {
    			$pref = '';
    			$showprefix = false;
    			$prefix = '';
    			if ($row->prefix > 0 && isset($prefix_arr[$row->board][$row->prefix])) {
    				$showprefix = true;
    				$prefix = $prefix_arr[$row->board][$row->prefix]['value'];
    			}

    			$info = $forums[$row->board];

    			if(is_id($row->name) && isset($memberdata[$row->name])) {
    				$row->mid = $row->name;
    				$row->name = $memberdata[$row->name];
    			}
    			else {
    				$row->mid = FALSE;
    			}

    			if (is_id($row->last_name) && isset($memberdata[$row->last_name])) {
    				$row->last_name = $memberdata[$row->last_name];
    			}

    			$rstart = str_date($lang->phrase('dformat1'),times($row->date));
    			$rlast = str_date($lang->phrase('dformat1'),times($row->last));

				if ($row->status == '2') {
					$pref .= $lang->phrase('forum_moved');
				}
				else {
					if ($row->mark === null && !empty($info['auto_status'])) {
						$row->mark = $info['auto_status'];
					}
					if ($row->mark == 'n') {
						$pref .= $lang->phrase('forum_mark_n');
					}
					elseif ($row->mark == 'a') {
						$pref .= $lang->phrase('forum_mark_a');
					}
					elseif ($row->mark == 'b') {
						$pref .= $lang->phrase('forum_mark_b');
					}
					elseif ($row->mark == 'g') {
						$pref .= $lang->phrase('forum_mark_g');
					}
					if ($row->sticky == '1') {
						$pref .= $lang->phrase('forum_announcement');
					}
				}

    			if ($slog->isTopicRead($row->id, $row->last)) {
    		 		$firstnew = 0;
    				if ($row->status == 1 || $row->status == 2) {
    				   	$alt = $lang->phrase('forum_icon_closed');
    					$src = $tpl->img('dir_closed');
    				}
    				else {
    				   	$alt = $lang->phrase('forum_icon_old');
    				   	$src = $tpl->img('dir_open');
    		 		}
    		 	}
    		  	else {
    		  		$firstnew = 1;
    				if ($row->status == 1 || $row->status == 2) {
    					$alt = $lang->phrase('forum_icon_closed');
    					$src = $tpl->img('dir_closed2');
    				}
    				else {
    					$alt = $lang->phrase('forum_icon_new');
    					$src = $tpl->img('dir_open2');
    				}
    			}

				if ($info['topiczahl'] < 1) {
					$info['topiczahl'] = $config['topiczahl'];
				}

				if ($row->posts > $info['topiczahl']) {
					$topic_pages = pages($row->posts+1, $info['topiczahl'], "showtopic.php?id=".$row->id."&amp;", 0, '_small', false);
				}
				else {
					$topic_pages = '';
				}

    			($code = $plugins->load('search_active_entry_prepared')) ? eval($code) : null;
    			$inner['index_bit'] .= $tpl->parse("search/active_bit");
    		}
    	}

		echo $tpl->parse("header");
		echo $tpl->parse("menu");
    	($code = $plugins->load('search_active_prepared')) ? eval($code) : null;
    	echo $tpl->parse("search/active");
    	($code = $plugins->load('search_active_end')) ? eval($code) : null;
	}
}
else {
	$forums = BoardSubs();
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	($code = $plugins->load('search_form_start')) ? eval($code) : null;
	echo $tpl->parse("search/index");
	($code = $plugins->load('search_form_end')) ? eval($code) : null;
}

($code = $plugins->load('search_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();
?>