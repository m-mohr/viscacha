<?php
$error = array();
if (isset($_REQUEST['save']) && $_REQUEST['save'] == 1) {
	include('../data/config.inc.php');
	require_once('../classes/class.filesystem.php');
	$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
	$filesystem->set_wd($config['ftp_path']);
	require_once('../classes/database/'.$config['dbsystem'].'.inc.php');
	$db = new DB($config['host'], $config['dbuser'], $config['dbpw'], $config['database'], $config['pconnect'], false, $config['dbprefix']);
	$db->errlogfile = '../'.$db->errlogfile;
	$db->pre = $db->prefix();
	$db->connect(false);
	if (!is_resource($db->conn)) {
		?>
	<div class="bbody">Could not connect to database! Pleasy try again later or check the database settings!</div>
	<div class="bfoot center"><a class="submit" href="index.php?step=<?php echo $step-2; ?>">Go back</a> <a class="submit" href="index.php?step=<?php echo $step; ?>">Refresh</a></div>
		<?php
	}
	else {
		if (!$db->select_db()) {
			?>
	<div class="bbody">Could not find database <em><?php echo $db->database; ?></em>! Please create a new database with this name or choose another database!</div>
	<div class="bfoot center"><a class="submit" href="index.php?step=<?php echo $step-2; ?>">Go back</a> <a class="submit" href="index.php?step=<?php echo $step; ?>">Refresh</a></div>
			<?php
		}
		else {
			if (!isset($_REQUEST['name'])) {
				$_REQUEST['name'] = '';
			}
			if (!isset($_REQUEST['pw'])) {
				$_REQUEST['pw'] = '';
			}
			if (!isset($_REQUEST['email'])) {
				$_REQUEST['email'] = '';
			}
			if (!isset($_REQUEST['pwx'])) {
				$_REQUEST['pwx'] = '';
			}

			if (strlen($_REQUEST['name']) > 50) {
				$error[] = 'Name ist zu lang (max. 50 Zeichen)';
			}
			if (strlen($_REQUEST['name']) < 3) {
				$error[] = 'Name ist zu kurz (min. 3 Zeichen)';
			}
			if (strlen($_REQUEST['pw']) > 64) {
				$error[] = 'Passwort ist zu lang (max. 64 Zeichen)';
			}
			if (strlen($_REQUEST['pw']) < 4) {
				$error[] = 'Passwort ist zu kurz (min. 4 Zeichen)';
			}
			if (strlen($_REQUEST['email']) > 200) {
				$error[] = 'E-Mail-Adresse zu lang (max. 200 Zeichen)';
			}
			if (strlen($_REQUEST['email']) < 7 || strpos($_REQUEST['email'], '@') === false) {
				$error[] = 'Keine gültige E-Mail-Adresse angegeben';
			}
			if ($_REQUEST['pw'] != $_REQUEST['pwx']) {
				$error[] = 'Passwörter stimmen nicht überein';
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
		<div class="bfoot center"><a class="submit" href="index.php?step=<?php echo $step-1; ?>">Go back</a></div>
				<?php
			}
			else {
			    $reg = time();
			    $_REQUEST['pwx'] = md5($_REQUEST['pwx']);
				$db->query("INSERT INTO {$db->pre}user (name, pw, mail, regdate, confirm, groups) VALUES ('{$_REQUEST['name']}', '{$_REQUEST['pwx']}', '{$_REQUEST['email']}', '{$reg}', '11', '1')",__LINE__,__FILE__);
				?>
		<div class="bfoot">Your account (<em><?php echo $_REQUEST['name']; ?></em>) has been created!</div>
				<?php
			}
		}
	}
}
if (count($error) == 0) {
?>
<div class="bbody">
<p>The installation is completed. You can access the Admin Control Panel with your username and password.
Please go through the settings and change everything to fit your needs.
After doing this, you can switch your board "online". By default it is switched "offline".
If you have problems, visit <a href="http://docs.viscacha.org" target="_blank">Viscacha.org</a>.</p>
<p class="hl_false">For your server security please completely remove the installation directory (<code><?php echo realpath('./'); ?></code>) including all files and sub-folders!</p>
</div>
<div class="bfoot center"><a class="submit" href="../admin/">Go to Admin Control Panel</a></div>
<?php } ?>
