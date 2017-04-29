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
		$result = $db->execute("
			SELECT u.id, u.source, t.board
			FROM {$db->pre}uploads AS u
				LEFT JOIN {$db->pre}topics AS t ON t.id = u.topic_id
			WHERE u.id = '{$_GET['id']}'
		");
		$row = $result->fetch();

		$my->p = $slog->Permissions($row['board']);
		$uppath = 'uploads/topics/'.$row['source'];

		if ($result->getResultCount() != 1) {
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
		$result = $db->execute("
			SELECT u.file, u.source, t.board
			FROM {$db->pre}uploads AS u
				LEFT JOIN {$db->pre}topics AS t ON t.id = u.topic_id
			WHERE u.id = '{$_GET['id']}'
			LIMIT 1
		");
		$row = $result->fetch();

		$my->p = $slog->Permissions($row['board']);

		$file = NULL;
		if ($result->getResultCount() != 1) {
			error($lang->phrase('no_upload_found'));
		}
		if ($my->p['forum'] == 0 || $my->p['downloadfiles'] == 0) {
			errorLogin();
		}

		$uppath = 'uploads/topics/'.$row['source'];

		if (!file_exists($uppath)) {
			error($lang->phrase('no_upload_found'));
		}

		$mime = get_mimetype($uppath);

		($code = $plugins->load('attachments_attachment_prepared')) ? eval($code) : null;
		viscacha_header('Content-Type: '.$mime['mime']);
		viscacha_header('Content-Length: '.filesize($uppath));
		viscacha_header('Content-Disposition: '.$mime['browser'].'; filename="'.$row['file'].'"');
		readfile($uppath);

		($code = $plugins->load('attachments_attachment_end')) ? eval($code) : null;
		$slog->updatelogged();
		exit();
	}
}
else {
	$error = false;

	($code = $plugins->load('attachments_upload_start')) ? eval($code) : null;
	if ($_GET['type'] == 'addreply' && is_id($_GET['id'])) {
		$result = $db->execute("SELECT id, board, name, status FROM {$db->pre}topics WHERE id = '{$_GET['id']}' LIMIT 1");
		if ($result->getResultCount() != 1) {
			$error = true;
		}
		$upinfo = $result->fetch();
		$upinfo['name'] = $my->id;
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
		$result = $db->execute("
				SELECT r.id, t.board, r.name, r.topic_id
				FROM {$db->pre}replies AS r 
					LEFT JOIN {$db->pre}topics AS t ON r.topic_id = t.id
				WHERE r.id = '{$_GET['id']}'
				LIMIT 1
		");
		if ($result->getResultCount() != 1) {
			$error = true;
		}
		$upinfo = $result->fetch();
	}
	else {
		$error = true;
	}
	($code = $plugins->load('attachments_upload_error')) ? eval($code) : null;
	if ($error == true) {
		error($lang->phrase('query_string_error'), 'javascript:self.close();');
	}
	$my->p = $slog->Permissions($upinfo['board']);
	$my->mp = $slog->ModPermissions($upinfo['board']);

	if ($my->p['attachments'] != 1) {
		error($lang->phrase('not_allowed'), 'javascript:self.close();');
	}

	if ($_GET['action'] == "save") {
		($code = $plugins->load('attachments_upload_save_start')) ? eval($code) : null;
		$url = 'attachments.php?type='.$_GET['type'].'&id='.$_GET['id'].SID2URL_JS_x;
		if (is_array($_POST['delete']) && count($_POST['delete']) > 0) {
			if ($my->mp[0] == 1 || $upinfo['name'] == $my->id) {
				$ids = array();
				foreach ($_POST['delete'] as $key => $value) {
					if (is_int($key) && $key > 0){
						$ids[] = $key;
					}
				}

				$result = $db->execute('
				SELECT source
				FROM '.$db->pre.'uploads
				WHERE mid = "'.$upinfo['name'].'" AND id IN ('.implode(',', $ids).')
				');

				while ($row = $result->fetch()) {
					if (file_exists('uploads/topics/'.$row['source'])) {
						$filesystem->unlink('uploads/topics/'.$row['source']);
					}
				}

				$db->execute('
				DELETE FROM '.$db->pre.'uploads
				WHERE mid = "'.$upinfo['name'].'" AND id IN ('.implode(',', $ids).')
				');

				$anz = $db->getAffectedRows();
				ok($lang->phrase('editprofile_attachments_deleted'), $url);
			}
		}
		else {
			$insertuploads = array();
			$inserterrors = array();
			require("classes/class.upload.php");

			($code = $plugins->load('attachments_upload_save_add_start')) ? eval($code) : null;

			for ($i = 1; $i <= $config['tpcmaxuploads']; $i++) {

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
					$uploaddata['file'] = $db->escape($uploaddata['file']);
					$uploaddata['source'] = $db->escape($uploaddata['source']);
					$db->execute("INSERT INTO {$db->pre}uploads (file,source,tid,mid,topic_id) VALUES ('{$uploaddata['file']}','{$uploaddata['source']}','{$tid}','{$upper}','{$upinfo['topic_id']}')");
				}
			}

			($code = $plugins->load('attachments_upload_save_add_end')) ? eval($code) : null;

			if (count($inserterrors) > 0) {
				error($inserterrors, $url);
			}
			else {
				ok($lang->phrase('data_success'), $url);
			}
		}
	}
	else {
		$filetypes = implode($lang->phrase('listspacer'), explode(',',$config['tpcfiletypes']));
		$filesize = formatFilesize($config['tpcfilesize']);

		if ($_GET['type'] == 'edit' && ($my->mp[0] == 1 || $upinfo['name'] == $my->id)) {
			$result = $db->execute('SELECT id, file, source FROM '.$db->pre.'uploads WHERE mid = "'.$upinfo['name'].'" AND tid = "'.$upinfo['id'].'"');
		}
		elseif ($_GET['type'] == 'newtopic' || $_GET['type'] == 'addreply') {
			$result = $db->execute('SELECT id, file, source FROM '.$db->pre.'uploads WHERE mid = "'.$my->id.'" AND topic_id = "'.$upinfo['id'].'" AND tid = "0"');
		}
		($code = $plugins->load('attachments_upload_form_start')) ? eval($code) : null;

		$uploads = array_fill(1, $config['tpcmaxuploads'], null);
		$i = 1;
		while ($row = $result->fetch()) {
			$fsize = filesize('uploads/topics/'.$row['source']);
			$fsize = formatFilesize($fsize);
			($code = $plugins->load('attachments_upload_form_upload')) ? eval($code) : null;
			$uploads[$i++] = array(
				'file' => $row['file'],
				'filesize' => $fsize,
				'id' => $row['id']
			);
		}

		($code = $plugins->load('attachments_upload_form_prepared')) ? eval($code) : null;
		echo $tpl->parse("attachments");
		($code = $plugins->load('attachments_upload_form_end')) ? eval($code) : null;
	}
}

$slog->updatelogged();
$phpdoc->Out();