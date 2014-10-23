<?php
$dataGiven = false;
include('data/config.inc.php');
if (!empty($config['ftp_server'])) {
	require_once("install/classes/ftp/class.ftp.php");
	$pemftp_class = pemftp_class_module();
	if ($pemftp_class !== null) {
		require_once("install/classes/ftp/class.ftp_{$pemftp_class}.php");
		$ftp = new ftp(false, false);
		if($ftp->SetServer($config['ftp_server'], $config['ftp_port'])) {
			if ($ftp->connect()) {
				if ($ftp->login($config['ftp_user'], $config['ftp_pw'])) {
					if ($ftp->chdir($config['ftp_path']) && $ftp->file_exists('data/config.inc.php')) {
						$dataGiven = true;
					}
				}
			}
			$ftp->quit();
		}
	}
}
?>
<div class="bbody">
<p>
Before we start the automatic update (file updates, updating CHMODs), you have to read the manual update instructions.
Please follow the steps and do the tasks.
More Information:
<?php if (file_exists('_docs/readme.txt')) { ?>
<a href="_docs/readme.txt" target="_blank">_docs/readme.txt</a>
<?php } else { ?>
_docs/readme.txt
<?php } ?>
</p>
<p>
<strong>Update instructions:</strong><br />
<ol class="upd_instr">
 <li>Make sure you have a <b>complete backup of your data</b> (FTP + MySQL)!</li>
 <li><b>You should specified the ftp data in your <a href="../admin.php?addr=<?php echo rawurlencode('admin.php?action=settings&job=ftp'); ?>" target="_blank">Admin Control Panel</a></b> (Viscacha Settings > FTP) before you continue with the next step or the CHMODs may not be set correctly!</li>
 <li>No manual CSS changes this release! All files will be patched automatically. For more information on manual update instructions see _docs/readme.txt.</li>
 <li>After the update <b>check for updates of your installed packages</b> in the ACP!</li>
</ol>
</p>
</div>
<div class="bfoot center">
<?php if ($dataGiven) { ?>
<input type="submit" value="Continue" />
<?php } else { ?>
<input type="submit" value="Continue without FTP support" />
<?php } ?>
</div>