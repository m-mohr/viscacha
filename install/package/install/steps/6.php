<?php
include('../data/config.inc.php');
if (isset($_REQUEST['save']) && $_REQUEST['save'] == 1) {
	require_once('../classes/class.filesystem.php');
	$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
	$filesystem->set_wd($config['ftp_path']);
	include('../classes/class.phpconfig.php');
	$c = new manageconfig();
	$c->getdata('../data/config.inc.php');
	$c->updateconfig('fname',str);
	$c->updateconfig('fname',str);
	$c->updateconfig('fdesc',str);
	$c->updateconfig('furl',str);
	$c->updateconfig('fpath',str);
	$c->updateconfig('forenmail',str);
	$c->updateconfig('cookie_prefix',str);
	if (empty($c->data['cryptkey']) == true) {
		$c->updateconfig('cryptkey',str,md5(microtime()));
	}
	$c->updateconfig('foffline',int,1);
	$c->updateconfig('version',str,VISCACHA_VERSION);
	if (!empty($c->data['forenmail']) && empty($c->data['pccron_sendlog_email'])) {
		$c->updateconfig('pccron_sendlog_email',str,$c->data['forenmail']);
	}
	$c->savedata();
?>
<div class="bfoot center">Basic Settings saved!</div>
<?php
}
$select = '';
if (extension_loaded('mysql') == true) {
	$mysqlext = '<span style="color: green;">Available</span>';
	$select = 'mysql';
}
else {
	$mysqlext = '<span style="color: red;">Unavailable</span>';
}
if (extension_loaded('mysqli') == true) {
	$mysqliext = '<span style="color: green;">Available</span>';
	$select = 'mysqli';
}
else {
	$mysqliext = '<span style="color: red;">Unavailable</span>';
}
if (!empty($config['dbsystem'])) {
	$select = $config['dbsystem'];
}
?>
<div class="bbody">
	<input type="hidden" name="save" value="1" />
	<table class="tables">
	 <tr>
	  <td class="mbox" width="50%">
	  	Database Driver:<br />
	  	<span class="stext">
	  	<a href="http://www.php.net/manual/ref.mysqli.php" target="_blank">mysqli</a>-Extension only for MySQL >= 4.1: <?php echo $mysqliext; ?><br />
	  	<a href="http://www.php.net/manual/ref.mysql.php" target="_blank">mysql</a>-Extension: <?php echo $mysqlext; ?>
	  	</span>
	  </td>
	  <td class="mbox" width="50%">
	  <select name="dbsystem">
	  	<option value="mysql"<?php echo iif($select == 'mysql', ' selected="selected"'); ?>>MySQL Standard (mysql)</option>
	  	<option value="mysqli"<?php echo iif($select == 'mysqli', ' selected="selected"'); ?>>MySQL Improved (mysqli)</option>
	  </select>
	  </td>
	 </tr>
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
	 <tr>
	  <td class="mbox" width="50%">Use a persistent connection:<br /><span class="stext">Only for "MySQL Standard". For more information visit: <a href="http://www.php.net/manual/features.persistent-connections.php" target="_blank">php.net - Persistent Database Connections</a></span></td>
	  <td class="mbox" width="50%"><input type="checkbox" name="pconnect" value="1"<?php echo iif($config['pconnect'] == 1, ' checked="checked"'); ?> /></td>
	 </tr>
	</table>
</div>
<div class="bfoot center"><input type="submit" value="Continue" /></div>
