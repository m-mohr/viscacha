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

DEFINE('SCRIPTNAME', 'members');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();
$my->p = $slog->Permissions();

if ($my->p['members'] == 0) {
	error(array($lang->phrase('not_allowed')));
}

$breadcrumb->Add($lang->phrase('members'));
echo $tpl->parse("header");
echo $tpl->parse("menu");

($code = $plugins->load('members_start')) ? eval($code) : null;

$letter = $lang->phrase('members_all');
$row = array('letter' => '');
$inner['index_letter'] = $tpl->parse("members/index_letter");
$result = $db->query("SELECT DISTINCT UPPER(LEFT(name,1)) AS letter FROM {$db->pre}user ORDER BY letter",__LINE__,__FILE__); 
while ($row = mysql_fetch_assoc($result)) {
	$letter = &$row['letter'];
	$inner['index_letter'] .= $tpl->parse("members/index_letter");
}


if ($_GET['order'] == '1') {
	$_GET['order'] = 'DESC';
}
else {
	$_GET['order'] = 'ASC';
}

if ($_GET['sort'] == 'regdate' || $_GET['sort'] == 'location') {
	$sort = $_GET['sort'];
}
else {
	$sort = 'name';
}

if (strlen($_GET['letter']) == 1) {
	$sqlwhere = ' WHERE LEFT(name,1) = "'.$_GET['letter'].'"';
}
else {
	$sqlwhere = '';
}

($code = $plugins->load('members_queries')) ? eval($code) : null;

$result = $db->query("SELECT COUNT(*) FROM {$db->pre}user {$sqlwhere}",__LINE__,__FILE__);
$count = $db->fetch_num($result);

$temp = pages($count[0], $config['mlistenzahl'], "members.php?sort={$_GET['sort']}&amp;letter={$_GET['letter']}&amp;order={$_GET['order']}".SID2URL_x."&amp;", $_GET['page']);
$start = $_GET['page']*$config['mlistenzahl'];
$start = $start-$config['mlistenzahl'];

$result = $db->query("
SELECT id,name,mail,hp,location,fullname,regdate 
FROM {$db->pre}user {$sqlwhere} 
ORDER BY {$sort} {$_GET['order']} 
LIMIT {$start},{$config['mlistenzahl']}
",__LINE__,__FILE__);

if ($db->num_rows() == 0) {
	error($lang->phrase('query_string_error'), 'members.php'.SID2URL_1);
}

$inner['index_bit'] = '';
while ($row = $gpc->prepare($db->fetch_object($result))) { 
	$row->regdate = gmdate($lang->phrase('dformat2'), times($row->regdate));
	($code = $plugins->load('members_prepare_bit')) ? eval($code) : null;
	$inner['index_bit'] .= $tpl->parse("members/index_bit");
}

($code = $plugins->load('members_prepared')) ? eval($code) : null;
echo $tpl->parse("members/index");
($code = $plugins->load('members_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();	
?>
