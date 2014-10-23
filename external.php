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

DEFINE('SCRIPTNAME', 'external');

include ("data/config.inc.php");
DEFINE('TEMPSHOWLOG', 1);
include ("classes/function.viscacha_frontend.php");

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();
$my->p = $slog->Permissions();
$my->pb = $slog->GlobalPermissions();

if ($config['syndication'] == 0) {
	errorLogin();
}

// Get the correct formatted timzone
$posneg = $my->timezone{0};
if ($posneg != '+' && $posneg != '-') {
	$posneg = '+';
	$mtz = $my->timezone;
}
else {
	$mtz = substr($my->timezone, 1);
}
if (strpos($mtz, '.') === false) {
	$tz3 = '00';
	$tz2 = leading_zero($mtz,2);
}
else {
	$tz = explode('.',$mtz);
	$tz3 = $tz[1]/100*60;
	$tz2 = leading_zero($tz[1],2);
}
define("TIME_ZONE", $posneg.$tz2.':'.$tz3);
// Include the Feedcreator class
include("classes/class.feedcreator.php"); 

BBProfile($bbcode);

($code = $plugins->load('external_start')) ? eval($code) : null;

$action = strtoupper($_GET['action']);
$data = file('data/feedcreator.inc.php');
foreach ($data as $feed) {
	$feed = explode("|", $feed);
	$feed = array_map('trim', $feed);
	$f[$feed[0]] = array(
		'class' => $feed[0],
		'file' => $feed[1],
		'name' => $feed[2],
		'active' => $feed[3],
		'header' => $feed[4]
	);
}
if (!isset($f[$action])) {
	$t = current($f);
	$action = $t['class'];
}
$format = $f[$action];
if ($format['header'] == 1) {
	$h = false;
}
else {
	$h = true;
}

// Header of feeds
$rss = new UniversalFeedCreator(); 
$rss->encoding = $lang->phrase('charset');
$rss->setDir("feeds/topics_");
$rss->useCached($action, '', $h);
$rss->title = $config['fname']; 
$rss->description = $config['fdesc']; 
$rss->link = $config['furl']."/forum.php"; 
$rss->language = $lang->phrase('rss_language');
$rss->ttl = $config['rssttl'];
$rss->copyright = $config['fname'];
$rss->lastBuildDate = time();
$rss->editor = $config['fname'];
$rss->editorEmail = $config['forenmail'];

$sqllimit = 15;
$sqlwhere = "r.tstart = '1' AND f.invisible != '2' AND f.active_topic = '1' AND f.opt != 'pw' ".$slog->sqlinboards('t.board');
$sqlorder = "t.date DESC";
$sqljoin = $sqlfields = '';

($code = $plugins->load('external_query')) ? eval($code) : null;

// Get the last 15 topics
$result = $db->query("
SELECT r.dowords, r.comment, r.guest, f.name as forum, u.name as uname, u.mail as umail, r.name as gname, r.email as gmail, t.topic, t.id, t.board, t.date, t.status {$sqlfields} 
FROM {$db->pre}topics AS t LEFT JOIN {$db->pre}replies AS r ON t.id = r.topic_id 
	LEFT JOIN {$db->pre}user AS u ON r.name=u.id 
	LEFT JOIN {$db->pre}forums AS f ON t.board=f.id 
	{$sqljoin}
WHERE {$sqlwhere} 
ORDER BY {$sqlorder} 
LIMIT {$sqllimit}
",__LINE__,__FILE__);

// Loop through them if the site is not offline
if ($config['foffline'] == 0) {
	while ($row = $db->fetch_object($result)) {
	
	    // Formats the data
	    if ($row->guest == 0) {
	        $row->email = $row->umail;
	        $row->name = $row->uname;
	    }
		else {
	        $row->email = $row->gmail;
	        $row->name = $row->gname;
	    }
		$bbcode->setSmileys(0);
		if ($config['wordstatus'] == 0) {
			$row->dowords = 0;
		}
		$bbcode->setReplace($row->dowords);
		if ($row->status == 2) {
			$row->comment = $bbcode->ReplaceTextOnce($row->comment, 'moved');
		}
       	$row->comment = $bbcode->parse($row->comment, 'plain');
       	$row->comment = str_replace("\n", ' ', $row->comment);
	    if (strxlen($row->comment) > $config['rsschars']) {
	        $row->comment = substr($row->comment,0,strpos($row->comment, ' ', $config['rsschars']));
	        $row->comment .= ' ...';
	    }
	
	    $item = new FeedItem(); 
	    $item->title = $row->topic; 
	   	$item->link = $config['furl']."/showtopic.php?id=".$row->id;
	    $item->source = $config['furl']."/showforum.php?id=".$row->board; 
	    $item->description = $row->comment; 
	    $item->date = gmdate('r', times($row->date));
	    $item->author = $row->name;
		$item->authorEmail = $row->email;
	    $item->pubDate = gmdate('r', times($row->date));
		$item->category = $row->forum;
		
		($code = $plugins->load('external_item_prepared')) ? eval($code) : null;
	
	    $rss->addItem($item); 
	}
}
else {
    $item = new FeedItem(); 
    $item->title = $lang->phrase('offline_head_ext'); 
    $item->link = $config['furl'];
    $item->description = $lang->phrase('offline_body_ext'); 
    $item->date = gmdate('r', time());
    $item->author = $config['fname'];
	
	($code = $plugins->load('external_offline')) ? eval($code) : null;
	
    $rss->addItem($item); 
}

($code = $plugins->load('external_prepared')) ? eval($code) : null;

$rss->saveFeed($format['class'], '', $h); 

($code = $plugins->load('external_end')) ? eval($code) : null;

$phpdoc->Out();
$db->close();		
?>
