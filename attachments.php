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

DEFINE('SCRIPTNAME', 'attachments');

include ("data/config.inc.php");
include ("classes/function.viscacha_frontend.php");

$zeitmessung1 = t1();

$slog = new slog();
$my = $slog->logged();
$lang->init($my->language);
$tpl = new tpl();

if ($config['tpcallow'] == 0 && $_GET['action'] == "thumbnail") {
	include('classes/graphic/class.thumbnail.php');
	$thumb = new thumbnail();
	$thumb->create_error('#0 '.$lang->phrase('thumb_error'));
}
elseif ($config['tpcallow'] == 0) {
	error($lang->phrase('upload_switched_off'));
}

if ($_GET['action'] == "thumbnail") {

	include('classes/graphic/class.thumbnail.php');
	$thumb = new thumbnail();

	if (!$_GET['id']) {
		$thumb->create_error('#1 '.$lang->phrase('thumb_error'));
	}
	else {
		$result = $db->query('
		SELECT u.id, u.file, t.board 
		FROM '.$db->pre.'uploads AS u LEFT JOIN '.$db->pre.'topics AS t ON t.id = u.tid 
		WHERE u.id = '.$_GET['id']
		,__LINE__,__FILE__);
		$row = $db->fetch_assoc($result);

		$my->p = $slog->Permissions($row['board']);
		$uppath = 'uploads/topics/'.$row['file'];

	    if ($db->num_rows($result) != 1) {
	    	$thumb->create_error('#2 '.$lang->phrase('thumb_error'));
	    }
	    if ($my->p['forum'] == 0 || $my->p['downloadfiles'] == 0) {
	    	$thumb->create_error('#3 '.$lang->phrase('thumb_error'));
	    }

		if (!file_exists($uppath)) {
			$thumb->create_error('#4 '.$lang->phrase('thumb_error'));
		}

		$chachepath = 'uploads/topics/thumbnails/'.$row['id'].get_extension($uppath);
		$thumb->set_cacheuri($chachepath);
		
		if (file_exists($chachepath) == FALSE) {
			$thumbnail_source = $thumb->create_thumbnail($uppath);
			$thumb->create_image($thumbnail_source);
		}
		$thumb->get_image();
		exit();
	}

}
elseif ($_GET['action'] == "attachment") {

	if ($_GET['id'] < 1) {
		echo $tpl->parse("header");
		error($lang->phrase('no_id_given'));
	}
	else {
		$result = $db->query('
		SELECT u.tid, u.file, t.board 
		FROM '.$db->pre.'uploads AS u LEFT JOIN '.$db->pre.'topics AS t ON t.id = u.tid 
		WHERE u.id = '.$_GET['id'].' AND u.tid > 0 
		LIMIT 1
		',__LINE__,__FILE__);
		$row = $db->fetch_assoc($result);
		
		$my->p = $slog->Permissions($row['board']);

		$file = NULL;
	    if ($db->num_rows($result) != 1) {
	    	echo $tpl->parse("header");
	    	error($lang->phrase('no_upload_found'));
	    }
	    if ($my->p['forum'] == 0 || $my->p['downloadfiles'] == 0) {
	    	echo $tpl->parse("header");
	    	errorLogin();
	    }

		$uppath = 'uploads/topics/'.$row['file'];

		if (!file_exists($uppath)) {
			error(array($lang->phrase('no_upload_found')));
		}

        $db->query('UPDATE '.$db->pre.'uploads SET hits = hits+1 WHERE id = '.$_GET['id'],__LINE__,__FILE__);
        
        $mime = get_mimetype($uppath);

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
			viscacha_header('Content-Disposition: '.$mime['browser'].'; filename="'.$row['file'].'"');
			readfile($uppath);
		}
		exit();
	}
}
else {
	
	$error = FALSE;
	if ($_GET['type'] == 'addreply' && $_GET['id'] > 0) {
		$result = $db->query("SELECT id, board, name, status FROM {$db->pre}topics WHERE id = '{$_GET['id']}' LIMIT 1",__LINE__,__FILE__);
		if ($db->num_rows($result) != 1) {
			$error = TRUE;
		}
		$upinfo = $db->fetch_assoc($result);
		$upinfo['name'] = $gpc->prepare($my->id);
		if ($upinfo['status'] != 0) {
			$error = TRUE;
		}
		$upinfo['topic_id'] = $_GET['id'];
	}
	elseif ($_GET['type'] == 'newtopic' && $_GET['id'] > 0) {
		$upinfo = array(
		'board' => $_GET['id'],
		'name' => $my->id,
		'topic_id' => 0,
		'id' => 0
		);
	}
	elseif ($_GET['type'] == 'edit' && $_GET['id'] > 0) {
		$result = $db->query("SELECT id, board, name, topic_id FROM {$db->pre}replies WHERE id = '{$_GET['id']}' LIMIT 1",__LINE__,__FILE__);
		if ($db->num_rows($result) != 1) {
			$error = TRUE;
		}
		$upinfo = $db->fetch_assoc($result);
		$upinfo['name'] = $gpc->prepare($upinfo['name']);
	}
	else {
		$error = TRUE;
	}
	if ($error) {
		echo $tpl->parse("popup/header");
		error($lang->phrase('query_string_error'), 'javascript: self.close();');
	}
	$my->p = $slog->Permissions($upinfo['board']);
	$my->mp = $slog->ModPermissions($upinfo['board']);
	
	if ($my->p['attachments'] != 1) {
		echo $tpl->parse("popup/header");
		errorLogin($lang->phrase('not_allowed'), 'javascript: self.close();');
	}
	
	if ($_GET['action'] == "save") {
	
		if (is_array($_POST['delete']) && count($_POST['delete']) > 0) {
			if ($my->mp[0] == 1 || $upinfo['name'] == $my->id) {
				$ids = array();
				foreach ($_POST['delete'] as $key => $value) {
					if (is_int($key) && $key > 0){
						$ids[] = $key;
					}
				}
				$result = $db->query('SELECT file FROM '.$db->pre.'uploads WHERE mid = "'.$upinfo['name'].'" AND id IN ('.implode(',', $ids).')',__LINE__,__FILE__);
				while ($row = $db->fetch_array($result)) {
					if (file_exists('uploads/topics/'.$row[0])) {
						@unlink('uploads/topics/'.$row[0]);
					}
				}
				$db->query('DELETE FROM '.$db->pre.'uploads WHERE mid = "'.$upinfo['name'].'" AND id IN ('.implode(',', $ids).')',__LINE__,__FILE__);
				viscacha_header('Location: attachments.php?type='.$_GET['type'].'&id='.$_GET['id'].SID2URL_JS_x);
			}
		}
		else {
	
			$insertuploads = array();
			$inserterrors = array();
			require("classes/class.upload.php");
	
		    for ($i = 0; $i < $config['tpcmaxuploads']; $i++) {
			    if (empty($_FILES['upload_'.$i]['name'])) {
			    	continue;
			    }
	
		        $my_uploader = new uploader();
		        $my_uploader->max_filesize($config['tpcfilesize']);
		        $my_uploader->max_image_size($config['tpcwidth'], $config['tpcheight']);
		        if ($my_uploader->upload('upload_'.$i, explode('|', $config['tpcfiletypes']), 1)) {
		            $my_uploader->save_file('uploads/topics/', '2');
		        }
		        if ($my_uploader->return_error()) {
			            array_push($inserterrors,$my_uploader->return_error());
			    }
			    array_push($insertuploads,$my_uploader->file['name']);
			}
		    if (count($inserterrors) > 0) {;
				error($inserterrors,'attachments.php?type='.$_GET['type'].'&amp;id='.$_GET['id'].SID2URL_x);
			}
	
			if ($_GET['type'] == 'edit' && ($my->mp[0] == 1 || $upinfo['name'] == $my->id)) {
				$upper = $upinfo['name'];
				$tid = $upinfo['id'];
			}
			else {
				$upper = $my->id;
				$tid = 0;
			}
	
			if (count($insertuploads) > 0 && count($insertuploads) <= $config['tpcmaxuploads']) {
			    foreach ($insertuploads as $up) {
			        $up = trim($up);
			        $db->query("INSERT INTO {$db->pre}uploads (file,tid,mid,topic_id) VALUES ('$up','$tid','$upper','{$upinfo['topic_id']}')",__LINE__,__FILE__);
			    }
			}
	
			viscacha_header('Location: attachments.php?type='.$_GET['type'].'&id='.$_GET['id'].SID2URL_JS_x);
		}
	}
	else {
		echo $tpl->parse("popup/header");
		
		$filetypes = implode($lang->phrase('listspacer'), explode('|',$config['tpcfiletypes']));
		$filesize = formatFilesize($config['tpcfilesize']);

		if ($_GET['type'] == 'edit' && ($my->mp[0] == 1 || $upinfo['name'] == $my->id)) {
			$result = $db->query('SELECT id, file FROM '.$db->pre.'uploads WHERE mid = "'.$upinfo['name'].'" AND tid = "'.$upinfo['id'].'"',__LINE__,__FILE__);
		}
		else {
			$result = $db->query('SELECT id, file FROM '.$db->pre.'uploads WHERE mid = "'.$my->id.'" AND topic_id = "'.$upinfo['id'].'" AND tid = "0"',__LINE__,__FILE__);
		}
		
		$free = $config['tpcmaxuploads'] - $db->num_rows($result);
		if ($free < 1) {
			$free = 0;
		}
		
		$uploads = array();
		while ($row = $db->fetch_assoc($result)) {
	        $fsize = filesize('uploads/topics/'.$row['file']);
	        $fsize = formatFilesize($fsize);
			$uploads[] = array(
			'file' => $row['file'],
			'filesize' => $fsize,
			'id' => $row['id']
			);
		}
		
		echo $tpl->parse("attachments");
		echo $tpl->parse("popup/footer");
	}
}

$slog->updatelogged();
$zeitmessung = t2();
$phpdoc->Out();
$db->close();
?>
