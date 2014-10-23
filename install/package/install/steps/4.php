<?php
if (isset($_REQUEST['save']) && $_REQUEST['save'] == 1) {
	$dataGiven = false;
	include('data/config.inc.php');
	if (isset($_REQUEST['ftp_server'])) {
		$config['ftp_server'] = trim($_REQUEST['ftp_server']);
	}
	if (isset($_REQUEST['ftp_user'])) {
		$config['ftp_user'] = trim($_REQUEST['ftp_user']);
	}
	if (isset($_REQUEST['ftp_pw'])) {
		$config['ftp_pw'] = trim($_REQUEST['ftp_pw']);
	}
	if (isset($_REQUEST['ftp_port'])) {
		$config['ftp_port'] = intval(trim($_REQUEST['ftp_port']));
	}
	else {
		$config['ftp_port'] = 21;
	}
	if (isset($_REQUEST['ftp_path'])) {
		$config['ftp_path'] = trim($_REQUEST['ftp_path']);
	}
	else {
		$config['ftp_path'] = DIRECTORY_SEPARATOR;
	}

	require_once("install/classes/ftp/class.ftp.php");
	require_once("install/classes/ftp/class.ftp_".pemftp_class_module().".php");

	echo '<div class="bbody" style="display: none;"><strong>FTP-Command-Log:</strong>:<br /><pre>';

	$ftp = new ftp(true, true);
	if(!$ftp->SetServer($config['ftp_server'], $config['ftp_port'])) {
		$ftp->quit();
	}
	if (!$ftp->connect()) {
		?></pre></div>
	<div class="bbody">Could not connect to ftp server! Pleasy try again later or check the ftp server settings (server, port)!</div>
	<div class="bfoot center"><a class="submit" href="index.php?package=install&amp;step=<?php echo $step-1; ?>">Go back</a></div>
		<?php
	}
	else {
		if (!$ftp->login($config['ftp_user'], $config['ftp_pw'])) {
			$ftp->quit();
			?></pre></div>
	<div class="bbody">Could not authenticate to ftp server! Pleasy try again later or check the ftp authentication settings (username, password)!</div>
	<div class="bfoot center"><a class="submit" href="index.php?package=install&amp;step=<?php echo $step-1; ?>">Go back</a></div>
			<?php
		}
		else {
			if (!$ftp->chdir($config['ftp_path']) || !$ftp->file_exists('data/config.inc.php')) {
				$ftp->quit();
				?></pre></div>
	<div class="bbody">Directory "<?php echo $config['ftp_path']; ?>" does not exist!</div>
	<div class="bfoot center"><a class="submit" href="index.php?package=install&amp;step=<?php echo $step-1; ?>">Go back</a></div>
				<?php
			}
			else {
				if (!$ftp->chdir('install')) {
					$ftp->quit();
					?></pre></div>
		<div class="bbody">Directory "install" does not exist. Please check the path.</div>
		<div class="bfoot center"><a class="submit" href="index.php?package=install&amp;step=<?php echo $step-1; ?>">Go back</a></div>
					<?php
				}
				else {
					$ftp->quit();
					$dataGiven = true;
					require_once('install/classes/class.filesystem.php');
					$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
					$filesystem->set_wd($config['ftp_path'], $config['fpath']);
					$filesystem->chmod('data/config.inc.php', 0666);
					include('install/classes/class.phpconfig.php');
					$c = new manageconfig();
					$c->getdata('data/config.inc.php');
					$c->updateconfig('ftp_server',str);
					$c->updateconfig('ftp_user',str);
					$c->updateconfig('ftp_pw',str);
					$c->updateconfig('ftp_path',str);
					$c->updateconfig('ftp_port',int);
					$c->savedata();
					?></pre></div>
					<div class="bfoot center">FTP Settings saved!<br />Connection: OK!</div>
					<?php
				}
			}
		}
	}
}
else {
	$dataGiven = true;
}
if ($dataGiven) {
	if (!isset($filesystem)) {
		include('data/config.inc.php');
		require_once('install/classes/class.filesystem.php');
		$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
		$filesystem->set_wd($config['ftp_path'], $config['fpath']);
	}

	require('install/classes/function.chmod.php');
	$chmod = getViscachaCHMODs();
	?>
	<div class="bbody">
	<p>Some directories and files needs special permissions (CHMOD) to be writable und executable.
	This permissions will be checked and the result will be shown below.</p>
	<p>The following states can appear:<br />
	<strong class="hl_true">OK</strong>: The permissions are set correctly.<br />
	<strong class="hl_null">Failure*</strong>: The permissions are not correct, but these files are only required for changing a couple of things at the Admin Control Panel. You need not to change them until you edit these files.<br />
	<strong class="hl_false">Failure</strong>: The permissions are not correct and you have to set them manually (per FTP). You can not continue this setup until this permissions are set correctly.<br />
	</p>
	<table class="tables">
	<tr>
		<td width="70%"><strong>File / Directory (<?php echo realpath('./'); ?>)</strong></td>
		<td width="10%"><strong>Required</strong></td>
		<td width="10%"><strong>Given</strong></td>
		<td width="10%"><strong>State</strong></td>
	</tr>
	<?php
	$files = array();
	foreach ($chmod as $dat) {
		if ($dat['recursive']) {
			$filenames = array();
			if ($dat['chmod'] == CHMOD_EX) {
				$filenames = set_chmod_r($dat['path'], 0777, CHMOD_DIR);
			}
			elseif ($dat['chmod'] == CHMOD_WR) {
				$filenames = set_chmod_r($dat['path'], 0666, CHMOD_FILE);
			}
			foreach ($filenames as $f) {
				$files[] = array('path' => $f, 'chmod' => $dat['chmod'], 'recursive' => false, 'req' => $dat['req']);
			}
		}
		else {
			if ($dat['chmod'] == CHMOD_EX) {
				set_chmod($dat['path'], 0777, CHMOD_DIR);
			}
			elseif ($dat['chmod'] == CHMOD_WR) {
				set_chmod($dat['path'], 0666, CHMOD_FILE);
			}
			$files[] = $dat;
		}
	}
	@clearstatcache();
	sort($files);
	$failure = false;
	foreach ($files as $arr) {
		$path = realpath($arr['path']);
		if (empty($path)) {
			$path = $arr['path'];
		}
		$chmod = get_chmod($path);
		if (check_chmod($arr['chmod'], $chmod)) {
			$status = '<strong class="hl_true">OK</strong>';
			$int_status = true;
		}
		elseif ($arr['req'] == false) {
			$status = '<strong class="hl_null">Failure*</strong>';
			$int_status = null;
		}
		else {
			$status = '<strong class="hl_false">Failure</strong>';
			$int_status = false;
			$failure = true;
		}
		if ($arr['req'] == true || $int_status != true) {
	?>
	<tr>
		<td><?php echo $arr['path']; ?></td>
		<td><?php echo $arr['chmod']; ?></td>
		<td><?php echo $chmod; ?></td>
		<td><?php echo $status; ?></td>
	</tr>
	<?php
		}
	}
	?>
	</table>
	</div>
	<div class="bfoot center">
	<?php if (!$failure) { ?>
	<input type="submit" value="Continue" />
	<?php } else { ?>
	<a class="submit" href="index.php?package=install&amp;step=<?php echo $step; ?>">Reload page</a>
	<?php } ?>
	</div>
<?php } ?>