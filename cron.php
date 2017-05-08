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

	pseudo-cron v1.3
	(c) 2003,2004 Kai Blankenhorn
	www.bitfolge.de/pseudocron
	kaib@bitfolge.de
	modified by: 	Matthias Mohr, 2005
					DigiLog multimedia, 2005
*/

error_reporting(E_ALL);

define('SCRIPTNAME', 'cron');
define('VISCACHA_CORE', '1');
define('NON_HTML_RESPONSE', 1);
define('CONSOLE_REQUEST', 1);

require_once("data/config.inc.php");
include("classes/function.viscacha_frontend.php");

$cron = new \Viscacha\System\Cron\Executor();
$cron->sendPixelImage();

($code = $plugins->load('cron_start')) ? eval($code) : null;

if (!$config['foffline']) {
	@ignore_user_abort(true);
	@set_time_limit(60);

	$cron->setLogPath("data/cron/");
	$cron->setClassPath("classes/system/cron/jobs/");
	$cron->enableFileLogging($config['pccron_uselog'] == 1);
	$cron->enableMailLogging($config['pccron_sendlog'] == 1, $config['pccron_sendlog_email']);
	$cron->execute("data/cron/crontab.inc.php", $config['pccron_maxjobs']);
}

($code = $plugins->load('cron_end')) ? eval($code) : null;