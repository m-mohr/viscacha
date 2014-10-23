<?php
if (isset($_REQUEST['save']) && $_REQUEST['save'] == 1) {
	include('../data/config.inc.php');
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
	$c->updateconfig('cryptkey',str,md5(microtime()));
	$c->updateconfig('foffline',int,1);
	$c->updateconfig('version',str,VISCACHA_VERSION);
	if (!empty($c->data['forenmail'])) {
		$c->updateconfig('pccron_sendlog_email',str,$c->data['forenmail']);
	}
	$c->savedata();
?>
<div class="bfoot center">Basic Settings saved!</div>
<?php } ?>
<div class="bbody">
	<input type="hidden" name="save" value="1" />
	<table class="tables">
	 <tr> 
	  <td class="mbox" width="50%">Database Driver:</td>
	  <td class="mbox" width="50%">
	  <select name="dbsystem">
	  	<option value="mysql">MySQL</option>
	  </select>
	  </td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">Server on which the database resides:</td>
	  <td class="mbox" width="50%"><input type="text" name="host" value="localhost" size="50" /></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">Database Username:</td>
	  <td class="mbox" width="50%"><input type="text" name="dbuser" value="" size="50" /></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">Database Password:</td>
	  <td class="mbox" width="50%"><input type="password" name="dbpw" value="" size="50" /></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">Database Name:<br /><span class="stext">Database where the tables for the Forum are saved to</span></td>
	  <td class="mbox" width="50%"><input type="text" name="database" value="" size="50" /></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">Database Tables Prefix:<br /><span class="stext">Don't use the same prefix for two installs!</span></td>
	  <td class="mbox" width="50%"><input type="text" name="dbprefix" value="v_" size="10" /></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">Use a persistent connection:<br /><span class="stext">For more information visit: <a href="http://www.php.net/manual/features.persistent-connections.php" target="_blank">php.net - Persistent Database Connections</a></span></td>
	  <td class="mbox" width="50%"><input type="checkbox" name="pconnect" value="1" /></td>
	 </tr>
	</table>
</div>
<div class="bfoot center"><input type="submit" value="Continue" /></div>
