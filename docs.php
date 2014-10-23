<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2007  Matthias Mohr, MaMo Net
	
	Author: Matthias Mohr
	Publisher: http://www.viscacha.org
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

DEFINE('SCRIPTNAME', 'docs');
define('VISCACHA_CORE', '1');

//DEFINE('TEMPSHOWLOG', 1);
include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();
$my->p = $slog->Permissions();

$id = $gpc->get('id', int);
$nonadmin = '';
if ($my->p['admin'] != 1) {
	$nonadmin = "AND active = '1'";
}
($code = $plugins->load('docs_query')) ? eval($code) : null;
$result = $db->query("SELECT * FROM {$db->pre}documents WHERE id = '{$id}' {$nonadmin} LIMIT 1");
if ($db->num_rows() != 1) {
	error($lang->phrase('docs_not_found'));
}
else {
	$info = $db->fetch_assoc($result);
}

/*
Parser:
 0 = Keiner, 1 = HTML, 2 = PHP(+HTML), 3 = BB-Code
Template:
 Leer = Ausgabe, Vorhanden = Einfügen
Inline:
 0 = Template hinzufügen, 1 = Template in der Datei
*/

if ($my->p['docs'] == 1 && GroupCheck($info['groups'])) {
	$memberdata_obj = $scache->load('memberdata');
	$memberdata = $memberdata_obj->get();
	if(is_id($info['author']) && isset($memberdata[$info['author']])) {
		$info['name'] = $memberdata[$info['author']];
	}
	else {
		$info['name'] = $lang->phrase('fallback_no_username');
	}
	($code = $plugins->load('docs_prepare')) ? eval($code) : null;
	if ($info['date'] > 0 ) {
		$info['date'] = str_date($lang->phrase('dformat1'), times($info['date']));
	}
	else {
		$info['date'] = $lang->phrase('docs_date_na');
	}
	if ($info['update'] > 0) {
		$info['update'] = str_date($lang->phrase('dformat1'), times($info['update']));
	}
	else {
		$info['date'] = $lang->phrase('docs_date_na');
	}
	$type = doctypes();
	if (isset($type[$info['type']])) {
		$typedata = $type[$info['type']];
	}
	else {
		$typedata = array(
			'title' => 'Fallback',
			'template' => '',
			'parser' => 1,
			'inline' => 1,
			'remote' => 0
		);
	}
	
	if ($typedata['inline'] == 0) {
		if ((empty($info['content']) || $typedata['remote'] == 1) && $typedata['template'] != 'frame') {
			$info['content'] = @file_get_contents($info['file']);
		}
		$info['content'] = DocCodeParser($info['content'], $typedata['parser']);
		$breadcrumb->Add($info['title']);
		echo $tpl->parse("header");
		($code = $plugins->load('docs_body_start')) ? eval($code) : null;
		if (empty($typedata['template'])) {
			echo $info['content'];
		}
		else {
			echo $tpl->parse("docs/{$typedata['template']}");
		}
	}
	else {
		if (empty($info['content'])) {
			$info['content'] = @file_get_contents($info['file']);
		}
		($code = $plugins->load('docs_html_start')) ? eval($code) : null;
		if (empty($typedata['template'])) {
			preg_match("~<body([^>]+?)>~is", $info['content'], $match_body_attr);
			preg_match("~<title>(.+?)</title>~is", $info['content'], $match_title);
			preg_match("~<body[^>]*?>(.+?)</body>~is", $info['content'], $match_body);
			preg_match("~<head[^>]*?>(.+?)</head>~is", $info['content'], $match_head);
			
			if (!empty($match_head[1])) {
				$match_head[1] = preg_replace("~<title>(.+?)</title>~is", "", $match_head[1]);
				$htmlhead .= $match_head[1];
			}
			if (!empty($match_body_attr[1])) {
				$htmlbody .= $match_body_attr[1];
			}
			if (!empty($match_title[1])) {
				$info['title'] = $match_title[1];
			}
			$breadcrumb->Add($info['title']);
			if (!empty($match_body[1])) {
				$info['content'] = $match_body[1];
			}
			echo $tpl->parse("header");
			($code = $plugins->load('docs_html_parser_prepared')) ? eval($code) : null;
			echo DocCodeParser($info['content'], $typedata['parser']);
		}
		else {
			$breadcrumb->Add($info['title']);
			$info['content'] = DocCodeParser($info['content'], $typedata['parser']);
			echo $tpl->parse("header");
			($code = $plugins->load('docs_html_template_prepared')) ? eval($code) : null;
			echo $tpl->parse("docs/{$typedata['template']}");
		}

	}
	($code = $plugins->load('docs_end')) ? eval($code) : null;
}
else {
	errorLogin();
}

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();		
?>
