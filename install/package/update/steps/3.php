<div class="bbody">
Preparing update...<br />
<?php
include('../data/config.inc.php');
require_once('lib/function.variables.php');
include('../classes/class.phpconfig.php');

if (!class_exists('filesystem')) {
	require_once('../classes/class.filesystem.php');
	$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
	$filesystem->set_wd($config['ftp_path']);
}
if (!class_exists('DB')) {
	require_once('../classes/database/'.$config['dbsystem'].'.inc.php');
	$db = new DB($config['host'], $config['dbuser'], $config['dbpw'], $config['database'], $config['pconnect'], true, $config['dbprefix']);
	$db->pre = $db->prefix();
	$db->errlogfile = '../'.$db->errlogfile;
}

// admin
$file = '../admin/data/hooks.txt';
$edit = file_get_contents($file);
$edit = preg_replace("~(-editprofile_abos_end[\r\n]+)~i", "\\1-editprofile_pic3_start\r\n-editprofile_pic3_end\r\n", $edit);
$edit = preg_replace("~(-misc_rules_end[\r\n]+)~i", "\\1-misc_board_rules_start\r\n-misc_board_rules_prepared\r\n-misc_board_rules_end\r\n", $edit);
$filesystem->chmod($file, 0666);
$filesystem->file_put_contents($file, $edit);

$file = '../admin/data/lang_email.php';
$edit = file($file);
$edit = array_map("trim", $edit);
$edit[] = "new_topic\tNotification about new topics for the board team";
$edit[] = "new_reply\tNotification about new replies for the board team";
$filesystem->chmod($file, 0666);
$filesystem->file_put_contents($file, implode("\n", $edit));

// classes
if (file_exists('../classes/function.cache.php')) {
	$filesystem->unlink('../classes/function.cache.php');
}

// data
$c = new manageconfig();
$c->getdata('../data/config.inc.php');
$c->updateconfig('version', str, VISCACHA_VERSION);
$ft = array('avfiletypes', 'tpcfiletypes');
foreach ($ft as $type) {
	$data = explode('|', $config[$type]);
	$data2 = array();
	foreach ($data as $d) {
		if (substr($d, 0, 1) == '.') {
			$d = substr($d, 1);
		}
		$data2[] = $d;
	}
	sort($data2);
	$c->updateconfig($type, str, implode(',', $data2));
}
$c->savedata();

$file = '../data/imservers.php';
$osi = array();
$osi[] = "http://imstatus.msitgroup.co.uk:81/";
$osi[] = "http://osi.hshh.org:8088/";
$osi[] = "http://www.funnyweb.dk:8080/";
$osi[] = "http://ph15.net:8000/";
$osi[] = "http://www.the-server.net:8001/";
$osi[] = "http://www.the-server.net:8002/";
$osi[] = "http://www.the-server.net:8003/";
$osi[] = "http://www.the-server.net:8000/";
$osi[] = "http://fermulator.homeip.net:8088/";
$osi[] = "http://osi.kanadian.net:8080/";
$osi[] = "http://osi.lishmirror.com:81/";
$osi[] = "http://snind.gotdns.com:8080/";
$osi[] = "http://public.hmstudios.net:8000/";
$filesystem->chmod($file, 0666);
$filesystem->file_put_contents($file, implode("\n", $osi));

// language
$dir = "../language/";
$lngids = array();
$d = dir($dir);
while (false !== ($entry = $d->read())) {
	if (is_dir($dir.$entry) && preg_match('/^\d{1,}$/', $entry) && $entry != '.' && $entry != '..') {
		$lngids[] = $entry;
	}
}
$d->close();
$c = new manageconfig();
$wwo = array();
$wwo['wwo_misc_board_rules'] = 'reads the <a href="misc.php?action=board_rules&id={$id}">forum rules</a> in the forum <a href="showforum.php?id={$id}">{$title}</a>';
$wwo['wwo_popup_showpost'] = 'views a single post: <a href="popup.php?action=showpost&id={$id}" target="showpost" onclick="showpost(this)">{$title}</a>';
$glob = array();
$glob['board_rules'] = 'Forum Rules';
$glob['editprofile_pic_delete'] = 'Delete Avatar';
$glob['forum_is_read_only'] = 'Sorry, but you are not allowed to write a post in this forum, because this forum is read only.';
$glob['no_board_rules_specified'] = 'No forum rules specified!';
$glob['no_existing_notices'] = 'You did not store any notes!';
$glob['showtopic_options_abo_remove'] = 'Unsubscribe from this topic';
$glob['subscribed_successfully'] = 'You successfully subscribed to this topic.';
$glob['unsubscribed_successfully'] = 'You successfully unsubscribed from this topic.';
$glob['upload_error_default'] = 'An unknown error occured while uploading.';
$glob['showtopic_prefix_title'] = '[{$prefix}] ';
$glob['upload_error_fileexists'] = 'File already exists.';
$glob['upload_error_maximagesize'] = 'Max. imagesize reached. Image is not allowed to be greater than {$miw} x {$mih}.';
$glob['upload_error_noaccess'] = 'Access denied. Could not copy file.';
foreach ($lngids as $lid) {
	$c->getdata('../language/'.$lid.'/global.lng.php', 'lang');
	foreach ($glob as $key => $val) {
		$c->updateconfig($key, str, $val);
	}
	$c->savedata();
	$c->getdata('../language/'.$lid.'/wwo.lng.php', 'lang');
	foreach ($wwo as $key => $val) {
		$c->updateconfig($key, str, $val);
	}
	$c->savedata();
}

// templates
$file = '../templates/editor/rte.css';
$edit = file_get_contents($file);
$edit = preg_replace("~(.rteBk tbody tr td, .rteBk tr td {[\r\n]+)~i", "\\1\tborder-width: 0px;\r\n", $edit);
$filesystem->chmod($file, 0666);
$filesystem->file_put_contents($file, $edit);

$dir = "../templates/";
$tplids = array();
$d = dir($dir);
while (false !== ($entry = $d->read())) {
	if (is_dir($dir.$entry) && preg_match('/^\d{1,}$/', $entry) && $entry != '.' && $entry != '..') {
		$tplids[] = $entry;
	}
}
$d->close();
foreach ($tplids as $id) {
	$tpldir = $dir.$id;
	$filesystem->unlink($tpldir.'/edit.html');
}

// install / sql
$file = 'package/'.$package.'/db/forums.sql';
$sql = implode('', file($file));
$sql = str_replace('{:=DBPREFIX=:}', $db->pre, $sql);
$db->multi_query($sql, false);

// Transfer old data - START
function forumtree_array($temp, $sub) {
	foreach ($temp as $cid => $boards) {
		foreach ($boards as $bid => $arr) {
			if (isset($sub[$bid])) {
				$sub[$bid] = forumtree_array($sub[$bid], $sub);
				$temp[$cid][$bid] = $sub[$bid];
			}
		}
	}
	return $temp;
}
$parent = $sub = $empty = $full = $data = array();
$result = $db->query("SELECT b.id, b.bid, b.cid FROM {$db->pre}cat AS b LEFT JOIN {$db->pre}categories AS c ON c.id = b.cid ORDER BY c.c_order, c.id, b.c_order, b.id", __LINE__, __FILE__);
while($row = $db->fetch_assoc($result)) {
	if ($row['bid'] == 0) {
		$parent[$row['cid']][$row['id']] = array();
	}
	else {
		$sub[$row['bid']][$row['cid']][$row['id']] = array();
	}
	$full[] = $row['cid'];
}
$result = $db->query("SELECT id FROM {$db->pre}categories ORDER BY c_order, id", __LINE__, __FILE__);
while ($row = $db->fetch_assoc($result)) {
	$empty[] = $row['id'];
}
$empty = array_diff($empty, $full);

$data = forumtree_array($parent, $sub);
foreach ($empty as $row) {
	$data[$row] = array();	
}

function convertForumStruct($data, $parent = 0) {
	global $db;
	if (count($data) > 0) {
		foreach ($data as $cid => $boards) {
			$db->query("UPDATE `{$db->pre}categories` SET `parent` = '{$parent}' WHERE id = '{$cid}' LIMIT 1;");
			if (count($boards) > 0) {
				foreach ($boards as $bid => $cat) {
					$result = $db->query("SELECT id,name,`desc`,bid,topics,replys,cid,c_order,last_topic,opt,optvalue,forumzahl,topiczahl,prefix,invisible FROM {$db->pre}cat WHERE id = '{$bid}' LIMIT 1", __LINE__, __FILE__);
					$x = $db->fetch_assoc($result);
					$x = array_map('mysql_escape_string', $x);
					$db->query("INSERT INTO `v_forums` ( `id` , `name` , `description` , `topics` , `replies` , `parent` , `position` , `last_topic` , `opt` , `optvalue` , `forumzahl` , `topiczahl` , `prefix` , `invisible` ) VALUES ('{$x['id']}', '{$x['name']}', '{$x['desc']}', '{$x['topics']}', '{$x['replys']}', '{$x['cid']}', '{$x['c_order']}', '{$x['last_topic']}', '{$x['opt']}', '{$x['optvalue']}', '{$x['forumzahl']}', '{$x['topiczahl']}', '{$x['prefix']}', '{$x['invisible']}')", __LINE__, __FILE__);
					convertForumStruct($cat, $bid);
				}
			}
		}
	}
}

$db->query("ALTER TABLE `{$db->pre}categories` CHANGE `desctxt` `description` text NOT NULL", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}categories` CHANGE `c_order` `position` smallint(4) NOT NULL default '0'", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}categories` ADD `parent` smallint(5) UNSIGNED NOT NULL DEFAULT '0' AFTER `description`", __LINE__, __FILE__);
convertForumStruct($data);
// Transfer old data - END

$db->query("ALTER TABLE `{$db->pre}prefix` ADD `standard` enum('0','1') NOT NULL default '0' AFTER `value`", __LINE__, __FILE__);

$db->query("ALTER TABLE `{$db->pre}session` CHANGE `remoteaddr` `user_agent` text NOT NULL", __LINE__, __FILE__);

$db->query("ALTER TABLE `{$db->pre}uploads` ADD `source` varchar(128) NOT NULL default '' AFTER `file`", __LINE__, __FILE__);
$db->query("UPDATE `{$db->pre}uploads` SET source = file WHERE source = ''", __LINE__, __FILE__);

// Refresh Cache
$dirs = array('../cache/', '../cache/modules/');
foreach ($dirs as $dir) {
	if ($dh = @opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if (strpos($file, '.php') !== false) {
				$filesystem->unlink($dir.$file);
			}
	    }
		closedir($dh);
	}
}

?>
Finished Update!
</div>
<div class="bfoot center"><input type="submit" value="Continue" /></div>