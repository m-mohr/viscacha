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

define('SCRIPTNAME', 'docs');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$my->p = $slog->Permissions();

$id = $gpc->get('id', int);

($code = $plugins->load('docs_query')) ? eval($code) : null;
$result = $db->query("
	SELECT d.id, u.id AS author, u.name, d.date, d.update, d.type, d.groups, c.lid, c.content, c.active, c.title
	FROM {$db->pre}documents AS d
		LEFT JOIN {$db->pre}documents_content AS c ON d.id = c.did
		LEFT JOIN {$db->pre}user AS u ON u.id = d.author
	WHERE d.id = '{$id}' ".iif($my->p['admin'] != 1, ' AND c.active = "1"')
);
if ($db->num_rows($result) == 0) {
	error($lang->phrase('docs_not_found'));
}
$info = null;
$data = array();
while ($row = $db->fetch_assoc($result)) {
	if (!is_array($info)) {
		$info = array(
			'id' => $row['id'],
			'author' => $row['author'],
			'date' => $row['date'],
			'date2' => $row['date'],
			'update' => $row['update'],
			'update2' => $row['update'],
			'type' => $row['type'],
			'groups' => $row['groups'],
			'name' => $row['name']
		);
	}
	$data[$row['lid']] = array(
		'content' => $row['content'],
		'active' => $row['active'],
		'title' => $row['title']
	);
}

/*
Parser:
 0 = None, 1 = HTML, 2 = PHP+HTML, 3 = BB-Code
Template:
 Leer = Ausgabe, Vorhanden = Einfügen
Inline:
 0 = Template hinzufügen, 1 = Template in der Datei
*/

if (GroupCheck($info['groups'])) {
	if(empty($info['name'])) {
		$info['name'] = $lang->phrase('fallback_no_username');
	}
	if ($info['date'] > 0 ) {
		$info['date'] = str_date(times($info['date']));
	}
	else {
		$info['date'] = $lang->phrase('docs_date_na');
	}
	if ($info['update'] > 0) {
		$info['update'] = str_date(times($info['update']));
	}
	else {
		$info['update'] = $lang->phrase('docs_date_na');
	}
	($code = $plugins->load('docs_prepare')) ? eval($code) : null;

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

	// Get the correct lid and merge data to one info array (compatibility)
	$lid = getDocLangID($data);
	$info = array_merge($info, $data[$lid]);

	if ($lid != $my->language) { // We don't use the correct language... Let's print a notice
		FlashMessage::addNotice($lang->phrase('doc_wrong_language_shown'));
	}

	if ($typedata['inline'] == 0) {
		if ($typedata['remote'] == 0) {
			$info['content'] = DocCodeParser($info['content'], $typedata['parser']);
		}
		else { // Only for backward compatibility of templates
			$info['file'] = $info['content'];
		}
		Breadcrumb::universal()->add($info['title']);
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
		($code = $plugins->load('docs_html_start')) ? eval($code) : null;
		if (empty($typedata['template'])) {
			preg_match("~<body([^>]+?)>~isu", $info['content'], $match_body_attr);
			preg_match("~<title>(.+?)</title>~isu", $info['content'], $match_title);
			preg_match("~<body[^>]*?>(.+?)</body>~isu", $info['content'], $match_body);
			preg_match("~<head[^>]*?>(.+?)</head>~isu", $info['content'], $match_head);

			if (!empty($match_head[1])) {
				$match_head[1] = preg_replace("~<title>(.+?)</title>~isu", "", $match_head[1]);
				$htmlhead .= $match_head[1];
			}
			if (!empty($match_body_attr[1])) {
				$htmlbody .= $match_body_attr[1];
			}
			if (!empty($match_title[1])) {
				$info['title'] = $match_title[1];
			}
			Breadcrumb::universal()->add($info['title']);
			if (!empty($match_body[1])) {
				$info['content'] = $match_body[1];
			}
			echo $tpl->parse("header");
			($code = $plugins->load('docs_html_parser_prepared')) ? eval($code) : null;
			echo DocCodeParser($info['content'], $typedata['parser']);
		}
		else {
			Breadcrumb::universal()->add($info['title']);
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

echo $tpl->parse("footer");
$slog->updatelogged();
$phpdoc->Out();