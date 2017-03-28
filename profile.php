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

define('SCRIPTNAME', 'profile');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$my->p = $slog->Permissions();

if ($my->p['profile'] != 1) {
	errorLogin();
}

($code = $plugins->load('profile_start')) ? eval($code) : null;

Breadcrumb::universal()->add($lang->phrase('members'), 'members.php'.SID2URL_1);
Breadcrumb::universal()->add($lang->phrase('profile_title'), 'profile.php?id='.$_GET['id'].SID2URL_x);

if (($_GET['action'] == 'mail' || $_GET['action'] == 'sendmail')) {
	$result=$db->query("SELECT id, name, opt_hidemail, mail FROM {$db->pre}user WHERE deleted_at IS NULL AND id = '{$_GET['id']}'");
	$row = $slog->cleanUserData($db->fetch_object($result));
	Breadcrumb::universal()->add($lang->phrase('profile_mail_2'));

	if ($my->vlogin && $row->opt_hidemail != 1) {
		if ($_GET['action'] == 'sendmail') {
			$error = array();
			if (flood_protect() == FALSE) {
				$error[] = $lang->phrase('flood_control');
			}
			if (mb_strlen($_POST['comment']) > $config['maxpostlength']) {
				$error[] = $lang->phrase('comment_too_long');
			}
			if (mb_strlen($_POST['comment']) < $config['minpostlength']) {
				$error[] = $lang->phrase('comment_too_short');
			}
			if (mb_strlen($_POST['topic']) > $config['maxtitlelength']) {
				$error[] = $lang->phrase('title_too_long');
			}
			if (mb_strlen($_POST['topic']) < $config['mintitlelength']) {
				$error[] = $lang->phrase('title_too_short');
			}
			($code = $plugins->load('profile_mail_errorhandling')) ? eval($code) : null;
			if (count($error) > 0) {
				$data = array(
					'topic' => $_POST['topic'],
					'comment' => $_POST['comment']
				);
				($code = $plugins->load('profile_mail_errordata')) ? eval($code) : null;
				$fid = save_error_data($data);
				error($error,"profile.php?action=mail&amp;id={$_GET['id']}&amp;fid=".$fid.SID2URL_x);
			}
			else {
				set_flood();
				$to = array('0' => array('name' => $row->name, 'mail' => $row->mail));
				$from = array('name' => $my->name, 'mail' => $my->mail);
				xmail($to, $from, $gpc->get('topic', html_enc), $gpc->get('comment', html_enc));
				ok($lang->phrase('email_sent'),"profile.php?id=".$_GET['id'].SID2URL_x);
			}

		}
		else {
			$fid = $gpc->get('fid', str);
			if (is_hash($fid)) {
				$data = $gpc->unescape(import_error_data($fid));
			}
			else {
				$data = array(
					'comment' => '',
					'topic' => ''
				);
			}
			echo $tpl->parse("header");
			($code = $plugins->load('profile_mail_prepared')) ? eval($code) : null;
			echo $tpl->parse("profile/mail");
			($code = $plugins->load('profile_mail_end')) ? eval($code) : null;

		}
	}
	else {
		errorLogin();
	}
}
else {
	($code = $plugins->load('profile_member_start')) ? eval($code) : null;

	$result = $db->query("SELECT * FROM {$db->pre}user AS u LEFT JOIN {$db->pre}userfields AS f ON u.id = f.ufid WHERE u.deleted_at IS NULL AND u.id = {$_GET['id']}");

	Breadcrumb::universal()->resetUrl();
	echo $tpl->parse("header");
	echo $tpl->parse("menu");

	if ($db->num_rows($result) == 1) {
		$row = $slog->cleanUserData($db->fetch_object($result));

		$days2 = null;
		if ($config['showpostcounter'] == 1) {
			$days2 = $row->posts / ((time() - $row->regdate) / 86400);
			$days2 = sprintf("%01.2f", $days2);
			if ($row->posts < $days2) {
				$days2 = $row->posts;
			}
		}

		$row->posts = numbers($row->posts);

		$row->p = $slog->StrangerPermissions($row->groups, true);
		$row->level = $slog->getStatus($row->groups);

		$row->regdate = gmdate($lang->phrase('dformat2'), times($row->regdate));
		if ($row->lastvisit > 0) {
			$row->lastvisit = str_date($lang->phrase('dformat1'), times($row->lastvisit));
		}
		else {
			$row->lastvisit = $lang->phrase('profile_never');
		}

		BBProfile($bbcode);
		$bbcode->setSmileys(1);
		$bbcode->setAuthor($row->id);
		$row->about = $bbcode->parse($row->about);

		BBProfile($bbcode, 'signature');
		$row->signature = $bbcode->parse($row->signature);

		if ($row->gender == 'm') {
			$gender = $lang->phrase('gender_m');
		}
		elseif ($row->gender == 'w') {
			$gender = $lang->phrase('gender_w');
		}
		else {
			$gender = $lang->phrase('gender_na');
		}
		$bday = explode('-',$row->birthday);
		if (count($bday) == 3 && $row->birthday != '0000-00-00' && $row->birthday != '1000-00-00') {
			if ($bday[0] > 1000) {
				$bday_age = getAge($bday);
			}
			else {
				$bday_age = null;
				$bday[0] = 0;
			}
			$show_bday = true;
		}
		else {
			$show_bday = false;
		}
		if (isset($bday[1]) && $bday[1] > 0 && $bday[1] < 13) {
			$bday[1] = $lang->phrase('months_'.intval($bday[1]));
		}

		$result = $db->query('SELECT mid, active FROM '.$db->pre.'session WHERE mid = '.$_GET['id']);
		$wwo = $db->fetch_num($result);
		$osi = false;
		if ($wwo[0] > 0) {
			$wwo[1] = gmdate($lang->phrase('dformat3'),times($wwo[1]));
			$osi = true;
		}

		// Custom Profile Fields
		include_once('classes/class.profilefields.php');
		$pfields = new ProfileFieldViewer($row->id);
		$pfields->setUserData($row);
		$customfields = $pfields->getAll();

		($code = $plugins->load('profile_member_prepared')) ? eval($code) : null;
		echo $tpl->parse("profile/index");
		($code = $plugins->load('profile_member_end')) ? eval($code) : null;
	}
	else {
		error($lang->phrase('profile_deleted_member'));
	}
}

($code = $plugins->load('profile_end')) ? eval($code) : null;

$slog->updatelogged();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();