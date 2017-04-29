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

($code = $plugins->load('docs_start')) ? eval($code) : null;

$result = $db->execute("
	SELECT d.id, u.id AS author, u.name, d.date, d.update, d.date AS date2, d.update AS update2, d.parser, d.template, d.groups
	FROM {$db->pre}documents AS d
		LEFT JOIN {$db->pre}user AS u ON u.id = d.author
	WHERE d.id = '{$id}'
");
if ($result->getResultCount() == 0) {
	error($lang->phrase('docs_not_found'));
}
$info = $result->fetch();

$result2 = $db->execute("SELECT * FROM  {$db->pre}documents_content WHERE did = '{$id}'");
$data = array();
while ($row = $result2->fetch()) {
	$data[$row['lid']] = $row;
}

($code = $plugins->load('docs_queried')) ? eval($code) : null;

if (!GroupCheck($info['groups'])) {
	errorLogin();
}

$lid = getDocLangID($data);
$document = $data[$lid];
if ($lid != $my->language) { // We don't use the correct language... Let's print a notice
	FlashMessage::addNotice($lang->phrase('doc_wrong_language_shown'));
}

if(empty($info['name'])) {
	$info['name'] = $lang->phrase('fallback_no_username');
}
if ($info['date'] > 0 ) {
	$info['date'] = date($lang->phrase('datetime_format'), times($info['date']));
}
else {
	$info['date'] = $lang->phrase('docs_date_na');
}
if ($info['update'] > 0) {
	$info['update'] = date($lang->phrase('datetime_format'), times($info['update']));
}
else {
	$info['update'] = $lang->phrase('docs_date_na');
}
	
$document['content'] = DocCodeParser($document['content'], $info['parser']);

($code = $plugins->load('docs_prepared')) ? eval($code) : null;

Breadcrumb::universal()->add($document['title']);

($code = $plugins->load('docs_output_start')) ? eval($code) : null;

echo $tpl->parse("header");
echo $tpl->parse("docs/{$info['template']}");
echo $tpl->parse("footer");

($code = $plugins->load('docs_end')) ? eval($code) : null;

$slog->updatelogged();
$phpdoc->Out();