<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2007  Matthias Mohr, MaMo Net
	
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

DEFINE('SCRIPTNAME', 'team');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();
$my->p = $slog->Permissions();

if ($my->p['team'] == 0) {
	errorLogin();
}

$breadcrumb->Add($lang->phrase('team'));

echo $tpl->parse("header");
echo $tpl->parse("menu");

($code = $plugins->load('team_top')) ? eval($code) : null;

$groups = $scache->load('groups');
$team = $groups->team();

$cache = array();
$t = array_merge($team['admin'], $team['gmod']);
foreach ($t as $row) {
	$cache[] = "FIND_IN_SET($row,groups)";
}

$result = $db->query('
SELECT id, name, mail, hp, location, fullname, groups 
FROM '.$db->pre.'user 
WHERE '.implode(' OR ',$cache).' 
ORDER BY name ASC
',__LINE__,__FILE__);

$admin_cache = array();
$gmod_cache = array();

while($row = $gpc->prepare($db->fetch_object($result))) {
	$gids = explode(',',$row->groups);
	foreach ($gids as $gid) {
		if (in_array($gid, $team['admin'])) {
			$admin_cache[] = $row;
		}
		elseif (in_array($gid, $team['gmod'])) {
			$gmod_cache[] = $row;
		}
	}
}
	
$result = $db->query('
SELECT m.time, m.mid, u.name as member, m.bid, f.name as board, u.mail, u.hp, u.location, u.fullname 
FROM '.$db->pre.'moderators AS m 
	LEFT JOIN '.$db->pre.'user AS u ON u.id = m.mid 
	LEFT JOIN '.$db->pre.'forums AS f ON f.id = m.bid
ORDER BY u.name ASC
',__LINE__,__FILE__);

$inner['moderator_bit'] = '';
if ($db->num_rows() > 0) {
	$mod_cache = array();
	$mid_cache = array(); 
	while($row = $gpc->prepare($db->fetch_object($result))) {  
		$mod_cache[$row->mid][] = $row;
		$mid_cache[] = $row->mid;
	}
	
	if(isset($mid_cache)) {
		$mid_cache = array_unique($mid_cache);
		$lastmod = '';
		$echoline = '';
		foreach ($mid_cache as $mid) {
			$inner['moderator_bit_forum'] = array();
			if(isset($mod_cache[$mid])) {  
				$mod  = $mod_cache[$mid];
				$anz2 = count($mod);
				$forschleife = $anz2-1;
				for($i = 0; $i < $anz2; $i++) {
					if ($config['team_mod_dateuntil'] == 1) {
						$mod[$i]->time = gmdate($lang->phrase('dformat2'),times($mod[$i]->time));
					}
					else {
						$mod[$i]->time = 0;
					}
					$inner['moderator_bit_forum'][] = $mod[$i];
					if ($i != $forschleife) {
						continue;
					}
					($code = $plugins->load('team_moderator_prepared')) ? eval($code) : null;
					$inner['moderator_bit'] .= $tpl->parse("team/moderator_bit");
				}
			}
		}
	}
}
($code = $plugins->load('team_prepared')) ? eval($code) : null;

echo $tpl->parse("team/index");

($code = $plugins->load('team_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();		
?>
