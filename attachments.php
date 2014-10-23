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

error_reporting(E_ALL);

define('SCRIPTNAME', 'attachments');
define('VISCACHA_CORE', '1');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

if ($config['tpcallow'] == 0 && $_GET['action'] == "thumbnail") {
	include('classes/graphic/class.thumbnail.php');
	$thumb = new thumbnail();
	$thumb->create_error('#0 '.$lang->phrase('thumb_error'));
}
elseif ($config['tpcallow'] == 0) {
	error($lang->phrase('upload_switched_off'));
}

($code = $plugins->load('attachments_start')) ? eval($code) : null;

if ($_GET['action'] == "thumbnail") {

	include('classes/graphic/class.thumbnail.php');
	$thumb = new thumbnail();

	if (!is_id($_GET['id'])) {
		$thumb->create_error('#1 '.$lang->phrase('thumb_error'));
	}
	else {
		($code = $plugins->load('attachments_thumbnail_queries')) ? eval($code) : null;
		$result = $db->query('
		SELECT u.id, u.source, t.board
		FROM '.$db->pre.'uploads AS u
			LEFT JOIN '.$db->pre.'topics AS t ON t.id = u.tid
		WHERE u.id = '.$_GET['id']
		);
		$row = $db->fetch_assoc($result);

		$my->p = $slog->Permissions($row['board']);
		$uppath = 'uploads/topics/'.$row['source'];

		if ($db->num_rows($result) != 1) {
			$thumb->create_error('#2 '.$lang->phrase('thumb_error'));
		}
		if ($my->p['forum'] == 0 || $my->p['downloadfiles'] == 0) {
			$thumb->create_error('#3 '.$lang->phrase('thumb_error'));
		}

		if (!file_exists($uppath)) {
			$thumb->create_error('#4 '.$lang->phrase('thumb_error'));
		}

		$chachepath = 'uploads/topics/thumbnails/'.$row['id'].get_extension($uppath, true);
		$thumb->set_cacheuri($chachepath);

		if (file_exists($chachepath) == false) {
			$thumbnail_source = $thumb->create_thumbnail($uppath);
			$thumb->create_image($thumbnail_source);
		}
		$thumb->get_image();
		exit();
	}

}
elseif ($_GET['action'] == "attachment") {

	if (!is_id($_GET['id'])) {
		error($lang->phrase('no_id_given'));
	}
	else {
		($code = $plugins->load('attachments_attachment_queries')) ? eval($code) : null;
		$result = $db->query('
		SELECT u.tid, u.file, u.source, t.board
		FROM '.$db->pre.'uploads AS u
			LEFT JOIN '.$db->pre.'topics AS t ON t.id = u.tid
		WHERE u.id = '.$_GET['id'].' AND u.tid > 0
		LIMIT 1
		');
		$row = $db->fetch_assoc($result);

		$my->p = $slog->Permissions($row['board']);

		$file = NULL;
		if ($db->num_rows($result) != 1) {
			error($lang->phrase('no_upload_found'));
		}
		if ($my->p['forum'] == 0 || $my->p['downloadfiles'] == 0) {
			errorLogin();
		}

		$uppath = 'uploads/topics/'.$row['source'];

		if (!file_exists($uppath)) {
			error($lang->phrase('no_upload_found'));
		}

		$db->query('UPDATE '.$db->pre.'uploads SET hits = hits+1 WHERE id = '.$_GET['id']);

		$mime = get_mimetype($uppath);

		($code = $plugins->load('attachments_attachment_prepared')) ? eval($code) : null;
		if ($config['tpcdownloadspeed'] > 0 && $mime['browser'] == 'attachment') {
			$rundeslimit = round($config['tpcdownloadspeed']*1024);

			viscacha_header('Cache-control: private');
			viscacha_header('Content-Type: '.$mime['mime']);
			viscacha_header('Content-Length: '.filesize($uppath));
			viscacha_header('Content-Disposition: '.$mime['browser'].'; filename="'.$row['file'].'"');

			flush();
			$fd = fopen($uppath, "r");
			while(!feof($fd)) {
				echo fread($fd, $rundeslimit);
				flush();
				sleep(1);
			}
			fclose ($fd);
		}
		else {
			viscacha_header('Content-Type: '.$mime['mime']);
			viscacha_header('Content-Length: '.filesize($uppath));
			viscacha_header('Content-Disposition: '.$mime['browser'].'; filename="'.$row['file'].'"');
			readfile($uppath);
		}
		($code = $plugins->load('attachments_attachment_end')) ? eval($code) : null;
		$slog->updatelogged();
		$db->close();
		exit();
	}
}
else {
	$error = false;

	($code = $plugins->load('attachments_upload_start')) ? eval($code) : null;
	if ($_GET['type'] == 'addreply' && is_id($_GET['id'])) {
		$result = $db->query("SELECT id, board, name, status FROM {$db->pre}topics WHERE id = '{$_GET['id']}' LIMIT 1");
		if ($db->num_rows($result) != 1) {
			$error = true;
		}
		$upinfo = $db->fetch_assoc($result);
		$upinfo['name'] = $gpc->prepare($my->id);
		if ($upinfo['status'] != 0) {
			$error = true;
		}
		$upinfo['topic_id'] = $_GET['id'];
	}
	elseif ($_GET['type'] == 'newtopic' && is_id($_GET['id'])) {
		$upinfo = array(
		'board' => $_GET['id'],
		'name' => $my->id,
		'topic_id' => 0,
		'id' => 0
		);
	}
	elseif ($_GET['type'] == 'edit' && $_GET['id'] > 0) {
		$result = $db->query("SELECT id, board, name, topic_id FROM {$db->pre}replies WHERE id = '{$_GET['id']}' LIMIT 1");
		if ($db->num_rows($result) != 1) {
			$error = true;
		}
		$upinfo = $db->fetch_assoc($result);
		$upinfo['name'] = $gpc->prepare($upinfo['name']);
	}
	else {
		$error = true;
	}
	($code = $plugins->load('attachments_upload_error')) ? eval($code) : null;
	if ($error == true) {
		echo $tpl->parse("popup/header");
		error($lang->phrase('query_string_error'), 'javascript:self.close();');
	}
	$my->p = $slog->Permissions($upinfo['board']);
	$my->mp = $slog->ModPermissions($upinfo['board']);

	if ($my->p['attachments'] != 1) {
		echo $tpl->parse("popup/header");
		errorLogin($lang->phrase('not_allowed'), 'javascript:self.close();');
	}

	if ($_GET['action'] == "save") {
		($code = $plugins->load('attachments_upload_save_start')) ? eval($code) : null;
		if (is_array($_POST['delete']) && count($_POST['delete']) > 0) {
			if ($my->mp[0] == 1 || $upinfo['name'] == $my->id) {
				$ids = array();
				foreach ($_POST['delete'] as $key => $value) {
					if (is_int($key) && $key > 0){
						$ids[] = $key;
					}
				}

				$result = $db->query('
				SELECT source
				FROM '.$db->pre.'uploads
				WHERE mid = "'.$upinfo['name'].'" AND id IN ('.implode(',', $ids).')
				');

				while ($row = $db->fetch_num($result)) {
					if (file_exists('uploads/topics/'.$row[0])) {
						$filesystem->unlink('uploads/topics/'.$row[0]);
					}
				}

				$db->query('
				DELETE FROM '.$db->pre.'uploads
				WHERE mid = "'.$upinfo['name'].'" AND id IN ('.implode(',', $ids).')
				');

				$slog->updatelogged();
				$db->close();
				viscacha_header('Location: attachments.php?type='.$_GET['type'].'&id='.$_GET['id'].SID2URL_JS_x);
				exit;
			}
		}
		else {
			$insertuploads = array();
			$inserterrors = array();
			require("classes/class.upload.php");

			($code = $plugins->load('attachments_upload_save_add_start')) ? eval($code) : null;

			for ($i = 0; $i < $config['tpcmaxuploads']; $i++) {

				$field = "upload_{$i}";
				if (empty($_FILES[$field]['name'])) {
					continue;
				}

				$my_uploader = new uploader();
				$my_uploader->max_filesize($config['tpcfilesize']);
				$my_uploader->max_image_size($config['tpcwidth'], $config['tpcheight']);
				$my_uploader->file_types(explode(',', $config['tpcfiletypes']));
				$my_uploader->set_path('uploads/topics/');
				($code = $plugins->load('attachments_upload_add_prepare')) ? eval($code) : null;
				if ($my_uploader->upload($field)) {
					if ($my_uploader->save_file()) {
						array_push($insertuploads, array('file' => $my_uploader->fileinfo('name'), 'source' => $my_uploader->fileinfo('filename')));
					}
				}
				if ($my_uploader->upload_failed()) {
					array_push($inserterrors, $my_uploader->get_error());
				}
			}

			if ($_GET['type'] == 'edit' && ($my->mp[0] == 1 || $upinfo['name'] == $my->id)) {
				$upper = $upinfo['name'];
				$tid = $upinfo['id'];
			}
			else {
				$upper = $my->id;
				$tid = 0;
			}

			($code = $plugins->load('attachments_upload_save_add_queries')) ? eval($code) : null;
			if (count($insertuploads) > 0 && count($insertuploads) <= $config['tpcmaxuploads']) {
				foreach ($insertuploads as $uploaddata) {
					$uploaddata['file'] = $db->escape_string($uploaddata['file']);
					$uploaddata['source'] = $db->escape_string($uploaddata['source']);
					$db->query("INSERT INTO {$db->pre}uploads (file,source,tid,mid,topic_id) VALUES ('{$uploaddata['file']}','{$uploaddata['source']}','{$tid}','{$upper}','{$upinfo['topic_id']}')");
				}
			}

			($code = $plugins->load('attachments_upload_save_add_end')) ? eval($code) : null;

			if (count($inserterrors) > 0) {
				echo $tpl->parse('popup/header');
				error($inserterrors, 'attachments.php?type='.$_GET['type'].'&amp;id='.$_GET['id'].SID2URL_x);
			}
			else {
				$slog->updatelogged();
				$db->close();
				viscacha_header('Location: attachments.php?type='.$_GET['type'].'&id='.$_GET['id'].SID2URL_JS_x);
				exit;
			}
		}
	}
	else {
		echo $tpl->parse("popup/header");

		$filetypes = implode($lang->phrase('listspacer'), explode(',',$config['tpcfiletypes']));
		$filesize = formatFilesize($config['tpcfilesize']);

		if ($_GET['type'] == 'edit' && ($my->mp[0] == 1 || $upinfo['name'] == $my->id)) {
			$result = $db->query('SELECT id, file, source FROM '.$db->pre.'uploads WHERE mid = "'.$upinfo['name'].'" AND tid = "'.$upinfo['id'].'"');
		}
		elseif ($_GET['type'] == 'newtopic' || $_GET['type'] == 'addreply') {
			$result = $db->query('SELECT id, file, source FROM '.$db->pre.'uploads WHERE mid = "'.$my->id.'" AND topic_id = "'.$upinfo['id'].'" AND tid = "0"');
		}
		($code = $plugins->load('attachments_upload_form_start')) ? eval($code) : null;

		$free = $config['tpcmaxuploads'] - $db->num_rows($result);
		if ($free < 1) {
			$free = 0;
		}

		$uploads = array();
		while ($row = $db->fetch_assoc($result)) {
			$fsize = filesize('uploads/topics/'.$row['source']);
			$fsize = formatFilesize($fsize);
			($code = $plugins->load('attachments_upload_form_upload')) ? eval($code) : null;
			$uploads[] = array(
			'file' => $row['file'],
			'filesize' => $fsize,
			'id' => $row['id']
			);
		}

		($code = $plugins->load('attachments_upload_form_prepared')) ? eval($code) : null;
		echo $tpl->parse("attachments");
		($code = $plugins->load('attachments_upload_form_end')) ? eval($code) : null;
		echo $tpl->parse("popup/footer");
	}
}

$slog->updatelogged();
$zeitmessung = t2();
$phpdoc->Out();
$db->close();
?>