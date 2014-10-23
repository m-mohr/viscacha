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

if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

define('FLOOD_TYPE_POSTING', 'pos');
define('FLOOD_TYPE_EDIT', 'edi');
define('FLOOD_TYPE_STANDARD', 'sta');
define('FLOOD_TYPE_PWRENEW', 'pwr');
define('FLOOD_TYPE_PWMAIL', 'pwm');
define('FLOOD_TYPE_SEARCH', 'sea');
define('FLOOD_TYPE_LOGIN', 'log');

function flood_protect($type = FLOOD_TYPE_STANDARD) {
	global $config, $my, $slog, $db;

	if ($config['enableflood'] == 0 || $my->p['flood'] == 0) {
		return true;
	}
	if ($my->p['guest'] == 1) {
		$value = $slog->getIP();
		$field = 'ip';
	}
	else {
		$value = $my->id;
		$field = 'mid';
	}
	$result = $db->query("SELECT time FROM {$db->pre}flood WHERE type = '{$type}' AND {$field} = '{$value}' LIMIT 1");
	if ($db->num_rows($result) == 1) {
		$data = $db->fetch_assoc($result);
		if ($data['time'] > (time()-$my->p['flood'])) {
			return false;
		}
	}
	return true;
}

function set_flood($type = FLOOD_TYPE_STANDARD) {
	global $config, $my, $slog, $db;

	if ($config['enableflood'] == 0 || $my->p['flood'] == 0) {
		return false;
	}
	if ($my->p['guest'] == 1) {
		$value = $slog->getIP();
		$field = 'ip';
	}
	else {
		$value = $my->id;
		$field = 'mid';
	}
	$time = time();
	$limit = $time - $my->p['flood'];
	// Alte Daten löschen (zu alte oder eigene)
	$db->query("DELETE FROM {$db->pre}flood WHERE (time <= '{$limit}' AND type != '".FLOOD_TYPE_LOGIN."') OR (type = '{$type}' AND {$field} = '{$value}')");
	// Daten einfügen
	$db->query("INSERT INTO {$db->pre}flood SET time = '{$time}', {$field} = '{$value}', type = '{$type}'");
	return true;
}

// Returns false if all free attempts failed
function set_failed_login() {
	global $slog, $db, $config, $lang, $filesystem;
	if ($config['login_attempts_max'] == 0) {
		return -1;
	}

	$ip = $slog->getIP();
	$time = time();
	$limit = $time - $config['login_attempts_time']*60;
	$result = $db->query("SELECT COUNT(*) FROM {$db->pre}flood WHERE ip = '{$ip}' AND time > '{$limit}' AND type = '".FLOOD_TYPE_LOGIN."'");
	$data = $db->fetch_num($result);
	$data[0]++;

	if ($data[0] >= $config['login_attempts_max']) {
		// Bann setzen
		$until = $time + $config['login_attempts_time']*60;
		$lang->assign('ip', $ip);
		$line = "ip\t{$ip}\t{$until}\t0\t{$time}\t".str_replace(array("\r", "\n", "\t"), ' ', $lang->phrase('login_attempts_banned'));

		$banned = file_get_contents('data/bannedip.php');
		$banned = trim($banned, "\r\n");
		// No check for double data at the moment, because don't know what to do with the old data.
		// ToDo: Add a check
		$filesystem->file_put_contents('data/bannedip.php', trim($banned."\n".$line, "\r\n"));

		// Clear login attempts after banning
		clear_login_attempts();
		return $config['login_attempts_max'];
	}
	else {
		// Add one login attempt
		$db->query("INSERT INTO {$db->pre}flood SET time = '{$time}', ip = '{$ip}', type = '".FLOOD_TYPE_LOGIN."'");
		return $data[0];
	}
}

function clear_login_attempts() {
	global $slog, $db, $config;
	if ($config['login_attempts_max'] > 0) {
		$db->query("DELETE FROM {$db->pre}flood WHERE type = '".FLOOD_TYPE_LOGIN."' AND ip = '".$slog->getIP()."'");
	}
}

?>