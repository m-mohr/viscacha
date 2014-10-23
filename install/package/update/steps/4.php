<?php
require('../data/config.inc.php');
if (!class_exists('filesystem')) {
	require_once('../classes/class.filesystem.php');
	$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
	$filesystem->set_wd($config['ftp_path']);
}
$lf = './locked.txt';
$filesystem->file_put_contents($lf, '');
?>
<div class="bbody">
<p>The update is completed. You can visit the Admin Control Panel with your username ans password. 
Please go through the settings, templates, language packs ... and change all things to fit your needs. 
After doing this, you can switch your board "online".
If you have problems, visit <a href="http://docs.viscacha.org" target="_blank">Viscacha.org</a>.</p>
<p class="hl_false">
For your security please completely remove the installation directory (<code><?php echo realpath('./'); ?></code>) including all files and sub-directory! 
<?php if (file_exists($lf)) { ?>
It is locked at the moment, but we highly recommend to remove the directory.
<?php } ?>
</p>
</div>
<div class="bfoot center"><a class="submit" href="../admin/">Go to Admin Control Panel</a></div>