<?php
$error = array();
include('data/config.inc.php');
require_once('install/classes/class.filesystem.php');
$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
$filesystem->set_wd($config['ftp_path'], $config['fpath']);
if (isset($_REQUEST['save']) && $_REQUEST['save'] == 1) {
	require_once('install/classes/database/'.$config['dbsystem'].'.inc.php');
	$db = new DB($config['host'], $config['dbuser'], $config['dbpw'], $config['database'], $config['dbprefix']);
	$db->setPersistence($config['pconnect']);
	$db->connect(false);
	if (!$db->hasConnection()) {
		?>
	<div class="bbody">Could not connect to database! Pleasy try again later or check the database settings!</div>
	<div class="bfoot center"><a class="submit" href="index.php?package=install&amp;step=<?php echo $step-2; ?>">Go back</a> <a class="submit" href="index.php?package=install&amp;step=<?php echo $step; ?>">Refresh</a></div>
		<?php
	}
	else {
		if (!$db->select_db()) {
			?>
	<div class="bbody">Could not find database <em><?php echo $db->database; ?></em>! Please create a new database with this name or choose another database!</div>
	<div class="bfoot center"><a class="submit" href="index.php?package=install&amp;step=<?php echo $step-2; ?>">Go back</a> <a class="submit" href="index.php?package=install&amp;step=<?php echo $step; ?>">Refresh</a></div>
			<?php
		}
		else {
			if (!isset($_REQUEST['name'])) {
				$_REQUEST['name'] = '';
			}
			else {
				$_REQUEST['name'] = GPC_escape($_REQUEST['name']);
			}
			if (!isset($_REQUEST['pw'])) {
				$_REQUEST['pw'] = '';
			}
			else {
				$_REQUEST['pw'] = GPC_escape($_REQUEST['pw']);
			}
			if (!isset($_REQUEST['email'])) {
				$_REQUEST['email'] = '';
			}
			else {
				$_REQUEST['email'] = GPC_escape($_REQUEST['email'], GPC_DB);
			}
			if (!isset($_REQUEST['pwx'])) {
				$_REQUEST['pwx'] = '';
			}
			else {
				$_REQUEST['pwx'] = GPC_escape($_REQUEST['pwx']);
			}

			if (strlen($_REQUEST['name']) > 50) {
				$error[] = 'Name is too long (max. 50 chars)';
			}
			if (strlen($_REQUEST['name']) < 3) {
				$error[] = 'Name is too short (min. 3 chars)';
			}
			if (strlen($_REQUEST['pw']) > 64) {
				$error[] = 'Password is too long (max. 64 chars)';
			}
			if (strlen($_REQUEST['pw']) < 4) {
				$error[] = 'Passwort is too short (min. 4 chars)';
			}
			if (strlen($_REQUEST['email']) > 200) {
				$error[] = 'Email address is too long (max. 200 chars)';
			}
			if (strlen($_REQUEST['email']) < 7 || strpos($_REQUEST['email'], '@') === false) {
				$error[] = 'The specified email address is not valid';
			}
			if ($_REQUEST['pw'] != $_REQUEST['pwx']) {
				$error[] = 'The specified passwords are not exactly the same';
			}

			$result = $db->query('SELECT id FROM '.$db->pre.'user WHERE name = "'.$_REQUEST['name'].'" LIMIT 1');
			if ($db->num_rows($result) > 0) {
				$error[] = 'The specified user name is already in use';
			}

			if (count($error) > 0) {
				?>
		<div class="bbody">
			<ul>
				<?php foreach ($error as $msg) { ?>
				<li><?php echo $msg; ?></li>
				<?php } ?>
			</ul>
		</div>
		<div class="bfoot center"><a class="submit" href="index.php?package=install&amp;step=<?php echo $step-1; ?>">Go back</a></div>
				<?php
			}
			else {
			    $reg = time();
			    $_REQUEST['pwx'] = md5($_REQUEST['pwx']);
				$db->query("INSERT INTO {$db->pre}user (name, pw, mail, regdate, confirm, groups, signature, about, notice) VALUES ('{$_REQUEST['name']}', '{$_REQUEST['pwx']}', '{$_REQUEST['email']}', '{$reg}', '11', '1', '', '', '')");
				?>
		<div class="bfoot">Your account (<em><?php echo $_REQUEST['name']; ?></em>) has been created!</div>
				<?php
			}
		}
	}
}

// Cache löschen
$cachedir = 'cache/';
if ($dh = @opendir($dir)) {
	while (($file = readdir($dh)) !== false) {
		if (strpos($file, '.inc.php') !== false) {
			$fileTrim = str_replace('.inc.php', '', $file);
			$filesystem->unlink($cachedir.$file);
		}
    }
	closedir($dh);
}

if (count($error) == 0) {
	$lf = './locked.txt';
	$filesystem->file_put_contents($lf, '', true);
?>
<div class="bbody">
<p>The installation is completed. You can access the Admin Control Panel with your username and password.
Please go through the settings and change everything to fit your needs.
After doing this, you can switch your board "online". By default it is switched "offline".
If you have problems, visit <a href="http://docs.viscacha.org" target="_blank">Viscacha.org</a>.</p>
<p class="hl_false">
For your server security please completely remove the installation directory (<addr><?php echo realpath('./install/'); ?></addr>) including all files and sub-folders!
<?php if (file_exists($lf)) { ?>
It is locked at the moment, but we highly recommend to remove the directory.
<?php } ?>
</p>
</div>
<div class="bfoot center"><a class="submit" href="../admin/">Go to Admin Control Panel</a></div>
<?php } ?>