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

DEFINE('SCRIPTNAME', 'popup');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();

($code = $plugins->load('popup_start')) ? eval($code) : null;

if ($_GET['action'] == "hlcode") {
	if (strlen($_GET['fid']) != 32) {
		echo $tpl->parse("popup/header");
		error($lang->phrase('query_string_error'), 'javascript:parent.close();');
	}

	$codeObj = new CacheItem($_GET['fid'], 'cache/geshicode/');
	if (!$codeObj->import()) {
		echo $tpl->parse("popup/header");
		error($lang->phrase('query_string_error'), 'javascript:parent.close();');
	}
	$sourcecode = $codeObj->get();
	
	$sourcecode['source'] = @html_entity_decode($sourcecode['source'], ENT_QUOTES, $lang->phrase('charset'));

	($code = $plugins->load('popup_hlcode_start')) ? eval($code) : null;

	if ($_GET['temp'] == 1) {
		viscacha_header('Cache-control: private');
		viscacha_header('Content-Type: text/plain');
		viscacha_header('Content-Length: '.strlen($sourcecode['source']));
		viscacha_header('Content-Disposition: attachment; filename='.date('d-m-Y_H-i').'.txt');
		echo $sourcecode['source'];
		exit;
	}
	else {
		require_once('classes/class.geshi.php');
		$geshi = new GeSHi($sourcecode['source'], strtolower($sourcecode['language']), 'classes/geshi');
		$geshi->set_encoding($lang->phrase('charset'));
		// Use classes for colouring
		$geshi->enable_classes();
		// Output in a div instead in a pre-element
		$geshi->set_header_type(GESHI_HEADER_DIV);
		// Linenumbers on  - each 5th element is bold
		$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 5); 
		$lang_name = $geshi->get_language_name();
		
		// Print Stylesheet
		$htmlhead .= '<style type="text/css"><!-- '.$geshi->get_stylesheet().' --></style>';
		echo $tpl->parse("popup/header");
		
		($code = $plugins->load('popup_hlcode_initialized')) ? eval($code) : null;
		
		$sourcecode['hl'] = $geshi->parse_code();
		echo $tpl->parse("popup/hlcode");
		
		($code = $plugins->load('popup_hlcode_end')) ? eval($code) : null;
	}
}
elseif ($_GET['action'] == "filetypes") {

	($code = $plugins->load('popup_filetypes_query')) ? eval($code) : null;
	
	$result = $db->query("SELECT * FROM {$db->pre}filetypes WHERE extension LIKE '%{$_GET['type']}%'",__LINE__,__FILE__);
	$nr = $db->num_rows($result);

	$cache = array();
	while ($row = $db->fetch_assoc($result)) {
		$row['extension'] = str_replace(',', ', ', $row['extension']);
		$cache[] = $row;
	}

	echo $tpl->parse("popup/header");
	($code = $plugins->load('popup_filetypes_prepared')) ? eval($code) : null;
	echo $tpl->parse("popup/filetypes");
	($code = $plugins->load('popup_filetypes_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "code") {
	$codelang = $scache->load('syntaxhighlight');
	$clang = $codelang->get();
	($code = $plugins->load('popup_code_start')) ? eval($code) : null;	
	echo $tpl->parse("popup/header");
	echo $tpl->parse("popup/code");
	($code = $plugins->load('popup_code_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "showpost") {
	echo $tpl->parse("popup/header");

	($code = $plugins->load('popup_showpost_query')) ? eval($code) : null;
	$result = $db->query("
	SELECT t.status, t.prefix, r.topic_id, r.board, r.edit, r.dosmileys, r.dowords, r.id, r.topic, r.comment, r.date, u.name as uname, r.name as gname, u.id as mid, u.groups, u.fullname, u.hp, u.pic, r.email as gmail, r.guest, u.signature, u.regdate, u.location 
	FROM {$db->pre}replies AS r 
		LEFT JOIN {$db->pre}user AS u ON r.name=u.id 
		LEFT JOIN {$db->pre}topics AS t ON t.id = r.topic_id 
	WHERE r.id = '{$_GET['id']}' 
	LIMIT 1
	",__LINE__,__FILE__);

	$found = $db->num_rows($result);
	if ($found == 1) {
		$row = $gpc->prepare($db->fetch_object($result));
	
		$my->p = $slog->Permissions($row->board);
	
		if (empty($row->topic_id)) {
			$row->topic_id = $row->id;
		}
	}
	$error = array();
	if ($found == 0) {
		$error[] = $lang->phrase('query_string_error');
	}
	if ($found == 1 && $my->p['forum'] == 0) {
		$error[] = $lang->phrase('not_allowed');
	}
	if (count($error) > 0) {
		errorLogin($error,'javascript:self.close();');
	}
	
	$catbid = $scache->load('cat_bid');
	$fc = $catbid->get();
	$last = $fc[$row->board];
	
	forum_opt($last['opt'], $last['optvalue'], $last['id']);
	
	($code = $plugins->load('popup_showpost_start')) ? eval($code) : null;
	if ($config['tpcallow'] == 1) {
		$uploads = $db->query("SELECT id, tid, mid, file, hits FROM {$db->pre}uploads WHERE tid = ".$_GET['id'],__LINE__,__FILE__);
	}
	$inner['upload_box'] = '';
	
	if ($row->guest == 0) {
		$row->mail = '';
		$row->name = $row->uname;
	}
	else {
		$row->mail = $row->gmail;
		$row->name = $row->gname;
		$row->mid = 0;
		$row->groups = GROUP_GUEST;
	}
	$new = iif($row->date > $my->clv, 'new', 'old');
	
	BBProfile($bbcode);
	$bbcode->setSmileys($row->dosmileys);
	if ($config['wordstatus'] == 0) {
		$row->dowords = 0;
	}
	$bbcode->setReplace($row->dowords);
	$bbcode->setAuthor($row->mid);
	if ($row->status == 2) {
		$row->comment = $bbcode->ReplaceTextOnce($row->comment, 'moved');
	}
	$row->comment = $bbcode->parse($row->comment);
	
	if ($my->opt_showsig == 1) {
		BBProfile($bbcode, 'signature');
		$row->signature = $bbcode->parse($row->signature);
	}
	
	$row->date = str_date($lang->phrase('dformat1'), times($row->date));
	$row->regdate = gmdate($lang->phrase('dformat2'), times($row->regdate));
	$row->level = $slog->getStatus($row->groups, ', ');
	if (empty($row->location)) {
		$row->location = $lang->phrase('showtopic_na');
	}
	if ($row->groups != NULL && (!empty($row->fullname) || (!empty($row->signature) && $my->opt_showsig == 1))) {
		$bottom = TRUE;
	}
	else {
		$bottom = FALSE;
	}

	if ($config['tpcallow'] == 1 && isset($uploads) && $db->num_rows($uploads) > 0) {
		while ($file = $db->fetch_assoc($uploads)) {
			$file['file'] = trim($file['file']);
			$uppath = 'uploads/topics/'.$file['file'];
			$fsize = filesize($uppath);
			$fsize = formatFilesize($fsize);
			($code = $plugins->load('popup_showpost_attachments_prepared')) ? eval($code) : null;
			$inner['upload_box'] .= $tpl->parse("popup/showpost_upload");
		}
	}

	if (!empty($row->edit)) {
		$edits = explode("\n", $row->edit);
		$anz = count($edits);
		$anz--;
		$lastdata = explode("\t", $edits[$anz-1]);
		$date = gmdate($lang->phrase('dformat1'), times($lastdata[1]));
		BBProfile($bbcode);
		$why = iif(empty($lastdata[2]), $lang->phrase('post_editinfo_na'), $bbcode->wordwrap($lastdata[2]));
	}
	
	($code = $plugins->load('popup_showpost_prepared')) ? eval($code) : null;
	echo $tpl->parse("popup/showpost");
	($code = $plugins->load('popup_showpost_end')) ? eval($code) : null;

}
elseif ($_GET['action'] == "edithistory") {
	echo $tpl->parse("popup/header");

	($code = $plugins->load('popup_edithistory_query')) ? eval($code) : null;
	$result = $db->query("
	SELECT r.ip, r.topic_id, r.board, r.edit, r.id, r.topic, r.date, u.name as uname, r.name as gname, u.id as mid, u.groups, r.email as gmail, r.guest 
	FROM {$db->pre}replies AS r 
		LEFT JOIN {$db->pre}user AS u ON r.name=u.id 
	WHERE r.id = '{$_GET['id']}' 
	LIMIT 1
	",__LINE__,__FILE__);
	
	$found = $db->num_rows($result);
	if ($found == 1) {
		$row = $gpc->prepare($db->fetch_assoc($result));
		$my->p = $slog->Permissions($row['board']);
	}
	$error = array();
	if ($found == 0) {
		$error[] = $lang->phrase('query_string_error');
	}
	if ($found == 1 && $my->p['forum'] == 0) {
		$error[] = $lang->phrase('not_allowed');
	}
	if (count($error) > 0) {
		errorLogin($error,'javascript:self.close();');
	}
	
	$catbid = $scache->load('cat_bid');
	$fc = $catbid->get();
	$last = $fc[$row['board']];
	
	forum_opt($last['opt'], $last['optvalue'], $last['id']);
	
	($code = $plugins->load('popup_edithistory_start')) ? eval($code) : null;
	
	if ($row['guest'] == 0) {
		$row['mail'] = '';
		$row['name'] = $row['uname'];
	}
	else {
		$row['mail'] = $row['gmail'];
		$row['name'] = $row['gname'];
		$row['mid'] = 0;
		$row['groups'] = GROUP_GUEST;
	}
	
	$row['date'] = str_date($lang->phrase('dformat1'), times($row['date']));
	
	$edit = array();
	if (!empty($row['edit'])) {
		$edits = explode("\n", $row['edit']);
		$i = 0;
		foreach ($edits as $e) {
			$e = trim($e);
			if (empty($e)) {
				continue;
			}
			$data = explode("\t", $e);
			$edit[$i] = array(
				'date' => str_date($lang->phrase('dformat1'), times($data[1])),
				'reason' => @iif(empty($data[2]), $lang->phrase('post_editinfo_na'), $data[2]),
				'name' => $data[0],
				'ip' => @iif(isset($data[3]), $data[3])
			);
			($code = $plugins->load('popup_edithistory_entry_prepared')) ? eval($code) : null;
			$i++;
		}
	}
	($code = $plugins->load('popup_edithistory_prepared')) ? eval($code) : null;
	echo $tpl->parse("popup/edithistory");
	($code = $plugins->load('popup_edithistory_end')) ? eval($code) : null;
}
elseif ($_GET['action'] == "postrating") {
	$rtg = $gpc->get('rating', int);

	($code = $plugins->load('popup_postrating_start')) ? eval($code) : null;

	if ($my->vlogin) {
		
		$result = $db->query("SELECT * FROM {$db->pre}replies WHERE id = '{$_GET['id']}'", __LINE__, __FILE__);
		$post = $db->fetch_assoc($result);

		if ($post['name'] == $my->id) {
			$error = $lang->phrase('postrating_you_posted');
		}
		
		$result = $db->query("
		SELECT mid, pid, tid, rating 
		FROM {$db->pre}postratings 
		WHERE mid = '{$my->id}' AND pid = '{$_GET['id']}'
		", __LINE__, __FILE__);
		$rating = $db->fetch_assoc($result);
		$rating['rating'] = intval($rating['rating']);

		if ($post['name'] != $my->id) {
			if (!empty($rtg) && $rating['rating'] != 1 && $rating['rating'] != -1) {
				$result = $db->query("SELECT topic_id, name, email, guest FROM {$db->pre}replies WHERE id = '{$_GET['id']}'", __LINE__, __FILE__);
				$topic = $db->fetch_assoc($result);
				if ($topic['guest'] == 0) {
					$aid = $topic['name'];
				}
				else {
					$aid = 0;
				}
				
				$db->query("INSERT INTO {$db->pre}postratings SET aid = '{$aid}', mid = '{$my->id}', pid = '{$_GET['id']}', tid = '{$topic['topic_id']}', rating = '{$rtg}'", __LINE__, __FILE__);
				$rating = array(
					'rating' => $rtg,
					'pid' => $_GET['id'],
					'tid' => $_GET['topic_id'],
					'mid' => $my->id
				);
			}
		
			if ($db->affected_rows() != 1) { 
				$error = $lang->phrase('unknown_error');
			}
			elseif ($rating['rating'] == 1) {
				$error = $lang->phrase('postrating_rated_positive');
			}
			elseif ($rating['rating'] == -1) {
				$error = $lang->phrase('postrating_rated_negative');
			}
			else {
				$error = $lang->phrase('query_string_error');
			}
		}

	}
	else {
		$error = $lang->phrase('log_not_logged');
	}

	echo $tpl->parse("popup/header");
	($code = $plugins->load('popup_postrating_prepared')) ? eval($code) : null;
	echo $tpl->parse("popup/postrating");
	($code = $plugins->load('popup_postrating_end')) ? eval($code) : null;
	echo $tpl->parse("popup/footer");
}

($code = $plugins->load('popup_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("popup/footer");
$phpdoc->Out();
$db->close();
?>
