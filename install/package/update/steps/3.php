<div class="bbody">
Preparing update...<br />
<?php
require('../data/config.inc.php');
require_once('lib/function.variables.php');
require_once('../classes/class.phpconfig.php');

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

// data
$c = new manageconfig();
$c->getdata('../data/config.inc.php');
$c->updateconfig('version', str, VISCACHA_VERSION);
$c->updateconfig('disableregistration', int, 0);
$c->updateconfig('hidedesign', int, 0);
$c->updateconfig('hidelanguage', int, 0);
$c->updateconfig('mlist_fields', str, 'fullname,pm,regdate,hp,icq,yahoo,aol,msn,jabber,skype');
$c->updateconfig('mlist_filtergroups', int, 0);
$c->updateconfig('mlist_showinactive', int, 0);
$c->updateconfig('register_notification', str, '');
$c->updateconfig('updatepostcounter', int, 1);
$c->savedata();

$d = file('../data/cron/crontab.inc.php');
$d = array_map('trim', $d);
$d[] = '0	*/6	*	*	*	recountpostcounts.php	#Recount User Post Counter';
$filesystem->file_put_contents('../data/cron/crontab.inc.php', implode("\n", $d));

$filesystem->unlink('../images/1/bbcodes/wiki.gif');

$db->query("ALTER TABLE `{$db->pre}forums` ADD `count_posts` enum('0','1') NOT NULL default '1' AFTER `last_topic`", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}user` ADD `posts` mediumint(7) unsigned NOT NULL default '0' AFTER `regdate`", __LINE__, __FILE__);

// Update Postcounts
$result = $db->query("SELECT COUNT(*) AS new, u.posts, u.id FROM {$db->pre}replies AS r LEFT JOIN {$db->pre}user AS u ON u.id = r.name WHERE r.guest = '0' GROUP BY u.id", __LINE__, __FILE__);		
$i = 0;
while ($row = $db->fetch_assoc($result)) {
	if ($row['new'] != $row['posts']) {
		$i++;
		$db->query("UPDATE {$db->pre}user SET posts = '{$row['new']}' WHERE id = '{$row['id']}'",__LINE__,__FILE__);
	}
}

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