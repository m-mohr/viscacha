<?php
require_once('classes/class.cache.php');

function cache_memberdata() {
	global $db, $gpc;
	$scache = new scache('memberdata');
	if ($scache->existsdata() == TRUE) {
		$cache = $scache->importdata();
	}
	else {
		$result = $db->query("SELECT id, name FROM {$db->pre}user",__LINE__,__FILE__);
		$cache = array();
		while ($row = $db->fetch_assoc($result)) {
			$cache[$row['id']] = $gpc->prepare($row['name']);
		}
		$olduserdata = file('data/deleteduser.php');
		foreach ($olduserdata as $row) {
			$row = trim($row);
			if (!empty($row)) {
				$row = explode("\t", $row);
				$cache[$row[0]] = $row[1];
			}
		}
		$scache->exportdata($cache);
	}
	return $cache;
}

function cache_prefix ($board = NULL) {
	global $db;
	$scache = new scache('prefix');
	if ($scache->existsdata() == TRUE) {
		$cache = $scache->importdata();
	}
	else {
		$result = $db->query("SELECT * FROM {$db->pre}prefix",__LINE__,__FILE__);
		$cache = array();
		while ($bb = $db->fetch_assoc($result)) {
			if (!isset($cache[$bb['bid']])) {
				$cache[$bb['bid']] = array();
			}
			$cache[$bb['bid']][$bb['id']] = $bb['value'];
		}
		$scache->exportdata($cache);
	}

	if ($board != NULL) {
		if (!isset($cache[$board])) {
			$cache[$board] = array();
		}
		return $cache[$board];
	}
	else {
		return $cache;
	}
}

function cache_cat_bid() {
	global $db;
	$scache = new scache('cat_bid');
	if ($scache->existsdata() == TRUE) {
	    $fc = $scache->importdata();
	}
	else {
	    $cresult = $db->query("SELECT name, id, bid, opt, optvalue, topics, prefix, c_order FROM {$db->pre}cat ORDER BY bid",__LINE__,__FILE__);
	    $fc = array();
	    while ($catc = $db->fetch_assoc($cresult)) {
	        $fc[$catc['id']] = $catc;
	    }
	    $scache->exportdata($fc);
	}

	return $fc;
}

function cache_forumtree() {
	global $db;
    $cache = new scache('forumtree');
	if ($cache->existsdata() == TRUE) {
	    $forums = $cache->importdata();
	}
	else {
		$parent = array();
		$sub = array();
		$empty = array();
		$full = array();
		$result = $db->query("SELECT b.id, b.bid, b.cid FROM {$db->pre}cat AS b LEFT JOIN {$db->pre}categories AS c ON c.id = b.cid ORDER BY c.c_order, c.id, b.c_order, b.id");
		while($row = $db->fetch_assoc($result)) {
			if ($row['bid'] == 0) {
				$parent[$row['cid']][$row['id']] = array();
			}
			else {
				$sub[$row['bid']][$row['cid']][$row['id']] = array();
			}
			$full[] = $row['cid'];
		}
		$result = $db->query("SELECT id FROM {$db->pre}categories ORDER BY c_order, id");
		while ($row = $db->fetch_assoc($result)) {
			$empty[] = $row['id'];
		}
		$empty = array_diff($empty, $full);

		$forums = cache_forumtree_array($parent, $sub);
		foreach ($empty as $row) {
			$forums[$row] = array();	
		}
		$cache->exportdata($forums);
	}
	return $forums;
}
function cache_forumtree_array($temp, $sub) {
	foreach ($temp as $cid => $boards) {
		foreach ($boards as $bid => $arr) {
			if (isset($sub[$bid])) {
				$sub[$bid] = cache_forumtree_array($sub[$bid], $sub);
				$temp[$cid][$bid] = $sub[$bid];
			}
		}
	}
	return $temp;
}

function cache_categories() {
	global $db;
    $scache = new scache('categories');
	if ($scache->existsdata() == TRUE) {
	    $cat_cache = $scache->importdata();
	}
	else {
	    $cresult = $db->query("SELECT id, name, desctxt, c_order FROM {$db->pre}categories ORDER BY c_order, id",__LINE__,__FILE__);
	    $cat_cache = array();
	    while ($catc = $db->fetch_assoc($cresult)) {
	        $cat_cache[$catc['id']] = $catc;
	    }
	    $scache->exportdata($cat_cache);
	}
	return $cat_cache;
}

function cache_loadlanguage () {
	global $db;
	$scache2 = new scache('load-language');
	if ($scache2->existsdata() == TRUE) {
	    $cache2 = $scache2->importdata();
	}
	else {
	    $result = $db->query("SELECT id, language, detail FROM {$db->pre}language WHERE publicuse != 0",__LINE__,__FILE__);
	    $cache2 = array();
	    while ($row = $db->fetch_assoc($result)) {
	        $cache2[$row['id']] = $row;
	    }
	    $scache2->exportdata($cache2);
	}
	return $cache2;
}

function cache_loaddesign () {
	global $db, $config;
	$scache = new scache('load-designs');
	if ($scache->existsdata() == TRUE) {
	    $design = $scache->importdata();
	}
	else {
	    $result = $db->query("SELECT id, template, stylesheet, images, name, smileyfolder FROM {$db->pre}designs WHERE publicuse = '1'",__LINE__,__FILE__);
	    $design = array();
	    while ($row = $db->fetch_assoc($result)) {
			$row['smileyfolder'] = str_replace('{folder}', $config['furl'], $row['smileyfolder']);
	        $design[$row['id']] = $row;
	    }
	    $scache->exportdata($design);
	}

	return $design;
}

function cache_fileicons() {
	global $db;
	$scache = new scache('fileicons');
	if ($scache->existsdata() == TRUE) {
		$cache = $scache->importdata();
	}
	else {
		$result = $db->query("SELECT extension, icon FROM {$db->pre}filetypes",__LINE__,__FILE__);
		$cache = array();
		while ($row = $db->fetch_assoc($result)) {
			$ext = explode(',', $row['extension']);
			foreach ($ext as $ft) {
				$cache[$ft] = $row['icon'];
			}
		}
		$scache->exportdata($cache);
	}
	
	return $cache;
}
function cache_wraps() {
	global $db;
	$scache = new scache('wraps');
	if ($scache->existsdata() == TRUE) {
	    $fc = $scache->importdata();
	}
	else {
	    $result = $db->query("SELECT id, title, active FROM {$db->pre}documents",__LINE__,__FILE__);
	    $fc = array();
	    while ($row = $db->fetch_assoc($result)) {
	        $fc[$row['id']] = $row;
	    }
	    $scache->exportdata($fc);
	}
	return $fc;
}

function cache_spiders() {
	global $db;
	$s3cache = new scache('spiders');
	if ($s3cache->existsdata() == TRUE) {
	    $botua = $s3cache->importdata();
	}
	else {
	    $cresult = $db->query("SELECT user_agent, name, type FROM {$db->pre}spider",__LINE__,__FILE__);
	    $botua = array();
	    while ($catc = $db->fetch_assoc($cresult)) {
	        $botua[] = $catc;
	    }
	    $s3cache->exportdata($botua);
	}
	return $botua;
}

?>
