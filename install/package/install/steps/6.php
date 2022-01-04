<?php
include('data/config.inc.php');
if (isset($_REQUEST['save']) && $_REQUEST['save'] == 1) {
	require_once('install/classes/class.filesystem.php');
	$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
	$filesystem->set_wd($config['ftp_path'], $config['fpath']);
	include('install/classes/class.phpconfig.php');
	$c = new manageconfig();
	$c->getdata('data/config.inc.php');
	$c->updateconfig('fname', html_enc);
	$c->updateconfig('fdesc', html_enc);
	$_REQUEST['furl'] = empty($_REQUEST['furl']) ? getFUrl() : $_REQUEST['furl'];
	if (strtolower(substr($_REQUEST['furl'], 0, 4)) == 'www.') {
		$https = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://');
		$_REQUEST['furl'] = $https.$_REQUEST['furl'];
	}
	$c->updateconfig('furl', str);
	$_REQUEST['fpath'] = !empty($_REQUEST['fpath']) ? $_REQUEST['fpath'] : str_replace('\\', '/', realpath('./'));
	$c->updateconfig('fpath', str);
	$c->updateconfig('forenmail', str);
	$c->updateconfig('cookie_prefix', str);
	$c->updateconfig('langdir', int);
	$langdir = $c->data['langdir'];
	if (empty($c->data['cryptkey']) == true) {
		$c->updateconfig('cryptkey', str, md5(microtime()));
	}
	$c->updateconfig('foffline', int, 1);
	$c->updateconfig('version', str, VISCACHA_VERSION);
	if (!empty($c->data['forenmail']) && empty($c->data['pccron_sendlog_email'])) {
		$c->updateconfig('pccron_sendlog_email', str, $c->data['forenmail']);
	}
	$c->savedata();

	$c->getdata('admin/data/config.inc.php', 'admconfig');
	$c->updateconfig('default_language', int, 0);
	$c->savedata();

?>
<div class="bfoot center">Basic Settings saved!</div>
<div class="bbody">
	<input type="hidden" name="save" value="1" />
	<table class="tables">
	 <tr>
	  <td class="mbox" width="50%">Server on which the database resides:</td>
	  <td class="mbox" width="50%"><input type="text" name="host" value="<?php echo $config['host']; ?>" size="50" /></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">Database Username:</td>
	  <td class="mbox" width="50%"><input type="text" name="dbuser" value="<?php echo $config['dbuser']; ?>" size="50" /></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">Database Password:</td>
	  <td class="mbox" width="50%"><input type="password" name="dbpw" value="<?php echo $config['dbpw']; ?>" size="50" /></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">Database Name:<br /><span class="stext">Database where the tables for the Forum are saved to</span></td>
	  <td class="mbox" width="50%"><input type="text" name="database" value="<?php echo $config['database']; ?>" size="50" /></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">Database Tables Prefix:<br /><span class="stext">Don't use the same prefix for two installs! Use only alphanumerical chars and _.</span></td>
	  <td class="mbox" width="50%"><input type="text" name="dbprefix" value="<?php echo $config['dbprefix']; ?>" size="10" /></td>
	 </tr>
	</table>
</div>
<div class="bfoot center"><input type="submit" value="Continue" /></div>
