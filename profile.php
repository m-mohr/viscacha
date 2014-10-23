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

define('SCRIPTNAME', 'profile');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$my->p = $slog->Permissions();

$is_guest = false;
$is_member = false;
$url_ext = '';
$guest = $gpc->get('guest', int);

$memberdata_obj = $scache->load('memberdata');
$memberdata = $memberdata_obj->get();

if (isset($memberdata[$_GET['id']])) {
	$username = $memberdata[$_GET['id']];
}
else {
	$username = $lang->phrase('fallback_no_username');
}

if ($my->p['profile'] != 1) {
	errorLogin();
}

if ($guest > 0) {
	$result = $db->query("SELECT email, name, guest FROM {$db->pre}replies WHERE id = '{$guest}' AND guest = '1' LIMIT 1");
	$guest_data = $db->fetch_assoc($result);
	if ($db->num_rows($result) == 1) {
		$is_guest = true;
		$username = $guest_data['name'];
		$email = $guest_data['email'];
		$url_ext = '&amp;guest='.$guest;
	}
	else {
		$is_guest = false;
	}
}
else {
	$is_guest = false;
}
if (isset($memberdata[$_GET['id']])) {
	$is_member = true;
}

($code = $plugins->load('profile_start')) ? eval($code) : null;

$breadcrumb->Add($lang->phrase('members'), 'members.php'.SID2URL_1);
$breadcrumb->Add($lang->phrase('profile_title'), 'profile.php?id='.$_GET['id'].$url_ext.SID2URL_x);

if (($_GET['action'] == 'mail' || $_GET['action'] == 'sendmail') && $is_member) {
	$result=$db->query('SELECT id, name, opt_hidemail, mail FROM '.$db->pre.'user WHERE id = '.$_GET['id'],__LINE__,__FILE__);
	$row = $slog->cleanUserData($db->fetch_object($result));
	$breadcrumb->Add($lang->phrase('profile_mail_2'));

	if ($my->vlogin && $row->opt_hidemail != 1) {
		if ($_GET['action'] == 'sendmail') {

			$error = array();
			if (flood_protect() == FALSE) {
				$error[] = $lang->phrase('flood_control');
			}
			if (strxlen($_POST['comment']) > $config['maxpostlength']) {
				$error[] = $lang->phrase('comment_too_long');
			}
			if (strxlen($_POST['comment']) < $config['minpostlength']) {
				$error[] = $lang->phrase('comment_too_short');
			}
			if (strxlen($_POST['topic']) > $config['maxtitlelength']) {
				$error[] = $lang->phrase('title_too_long');
			}
			if (strxlen($_POST['topic']) < $config['mintitlelength']) {
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
				xmail($to, $from, $_POST['topic'], $gpc->unescape($_POST['comment']));
				ok($lang->phrase('email_sent'),"profile.php?id=".$_GET['id'].SID2URL_x);
			}

		}
		else {
			if ($row->opt_hidemail == 0) {
				$chars = array('@','.');
				$entities = array('&#64;','&#46;');
				$row->mail = str_replace($chars, $entities, $row->mail);
			}

			if (strlen($_GET['fid']) == 32) {
				$data = $gpc->prepare(import_error_data($_GET['fid']));
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
elseif ($_GET['action'] == "ims" && $is_member) {
	$error = array();
	if ($my->p['profile'] == 0) {
		$error[] = $lang->phrase('not_allowed');
	}

	$sqlfields = '';

	($code = $plugins->load('profile_ims_start')) ? eval($code) : null;

	if ($_GET['type'] == 'icq' || $_GET['type'] == 'aol' || $_GET['type'] == 'yahoo' || $_GET['type'] == 'msn' || $_GET['type'] == 'jabber' || $_GET['type'] == 'skype') {
		$imtext = $lang->phrase('im_'.$_GET['type']);
	}
	else {
		$error[] = $lang->phrase('query_string_error');
	}

	$result = $db->query("SELECT id, name, icq, aol, yahoo, msn, jabber, skype {$sqlfields} FROM {$db->pre}user WHERE id = '{$_GET['id']}'",__LINE__,__FILE__);

	$row = $slog->cleanUserData($db->fetch_assoc($result));
	if (empty($row[$_GET['type']])) {
		$error[] = $lang->phrase('im_no_data');
	}

	if (count($error) > 0) {
		errorLogin($error, 'profile.php?id='.$_GET['id'].SID2URL_x);
	}
	else {
		$t = $_GET['type'];
		$d = $row[$_GET['type']];

		$breadcrumb->Add($imtext);
		echo $tpl->parse("header");
		echo $tpl->parse("menu");
		include("classes/class.imstatus.php");
		$imstatus = new IMStatus();
		$status = $imstatus->$t($d);
		if ($status) {
			$imstatus = $lang->phrase('im_status_'.$status);
		}
		else {
			$imstatus = $lang->phrase('im_no_connection').'<!-- Error #'.$imstatus->error(IM_ERRNO).' occurred during query: '.$imstatus->error(IM_ERRSTR).' -->';
		}
		($code = $plugins->load('profile_ims_prepared')) ? eval($code) : null;
		echo $tpl->parse("profile/ims");
		($code = $plugins->load('profile_ims_start')) ? eval($code) : null;
	}
}
elseif ($_GET['action'] == 'emailimage' && $is_guest) {
	if (headers_sent()) {
		exit;
	}
	include('classes/graphic/class.text2image.php');
	$img = new text2image();
	$img->prepare($email, 0, 10, 'classes/fonts/trebuchet.ttf');
	$img->build();
	$img->output();
	exit;
}
elseif ($is_guest) {
	$breadcrumb->resetUrl();
	echo $tpl->parse("header");
	echo $tpl->parse("menu");
	$group = 'fallback_no_username';
	($code = $plugins->load('profile_guest_prepared')) ? eval($code) : null;
	echo $tpl->parse("profile/guest");
}
elseif ($is_member) {
	($code = $plugins->load('profile_member_start')) ? eval($code) : null;

	$result = $db->query("SELECT * FROM {$db->pre}user AS u LEFT JOIN {$db->pre}userfields AS f ON u.id = f.ufid WHERE u.id = {$_GET['id']}",__LINE__,__FILE__);

	$breadcrumb->resetUrl();
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

		$row->p = $slog->Permissions(0,$row->groups, true);
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
		$bbcode->setReplace(0);
		$bbcode->setAuthor($row->id);
		$row->about = $bbcode->parse($row->about);

		BBProfile($bbcode, 'signature');
		$row->signature = $bbcode->parse($row->signature);

		// Set the instant-messengers
		if ($row->jabber || $row->icq > 0 || $row->aol || $row->msn || $row->yahoo || $row->skype) {
			$imanz = 1;
		}
		else {
			$imanz = 0;
		}

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
			$show_bday = true;
		}
		else {
			$show_bday = false;
		}
		if (isset($bday[1]) && $bday[1] > 0 && $bday[1] < 13) {
			$bday[1] = $lang->phrase('months_'.intval($bday[1]));
		}

		$osi = '';
		if ($config['osi_profile'] == 1) {
			$result = $db->query('SELECT mid, active FROM '.$db->pre.'session WHERE mid = '.$_GET['id'],__LINE__,__FILE__);
			$wwo = $db->fetch_num($result);
			if ($wwo[0] > 0) {
				$wwo[1] = gmdate($lang->phrase('dformat3'),times($wwo[1]));
				$osi = 1;
			}
			else {
				$osi = 0;
			}
		}

		// Custom Profile Fields
		include_once('classes/class.profilefields.php');
		$pfields = new ProfileFieldViewer($row->id);
		$pfields->setUserData($row);
		$customfields = $pfields->getAll();

		if ($config['memberrating'] == 1) {
			$result = $db->query("SELECT rating FROM {$db->pre}postratings WHERE aid = '{$row->id}'");
			$ratings = array();
			while ($dat = $db->fetch_assoc($result)) {
				$ratings[] = $dat['rating'];
			}
			$ratingcounter = count($ratings);
			if ($ratingcounter> 0 && $ratingcounter >= $config['memberrating_counter']) {
				$row->rating = round(array_sum($ratings)/$ratingcounter*50)+50;
			}
			else {
				$row->rating = $lang->phrase('profile_na');
			}
		}

		($code = $plugins->load('profile_member_prepared')) ? eval($code) : null;
		echo $tpl->parse("profile/index");
		($code = $plugins->load('profile_member_end')) ? eval($code) : null;
	}
	else {
		$group = 'fallback_no_username_group';
		($code = $plugins->load('profile_member_fallback')) ? eval($code) : null;
		echo $tpl->parse("profile/guest");
	}
}
else {
	$db->close();
	viscacha_header('Location: members.php');
	exit;
}

($code = $plugins->load('profile_end')) ? eval($code) : null;

$slog->updatelogged();
$zeitmessung = t2();
echo $tpl->parse("footer");
$phpdoc->Out();
$db->close();
?>
