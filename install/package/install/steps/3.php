<?php
include('data/config.inc.php');
if (empty($config['ftp_path']) == false) {
	$path = $config['ftp_path'];
}
else {
	$path = '/';
}
?>
<div class="bfoot">You can change these data in the Administration Control Panel later.</div>
<div class="bbody">
	<input type="hidden" name="save" value="1" />
	<table class="tables">
	 <tr>
	  <td class="mbox" width="50%">FTP-Server:<br /><span class="stext">You can leave it empty for disabling FTP.</span></td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_server" value="<?php echo $config['ftp_server']; ?>" size="50" /></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">FTP-Port:</td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_port" value="<?php echo $config['ftp_port']; ?>" size="4" /></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">Path to the Viscacha directory beginning from FTP-Root:<br />
	  <span class="stext">This path should be the path starting from your FTP root folder pointing to the Viscacha directory. If the ftp account points directly to the Viscacha directory, it should be "/" on *nix-based systems.</span></td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_path" value="<?php echo $path; ?>" size="50" /></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">FTP-Account-Username:</td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_user" value="<?php echo $config['ftp_user']; ?>" size="50" /></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">FTP-Account-Password:</td>
	  <td class="mbox" width="50%"><input type="password" name="ftp_pw" value="<?php echo $config['ftp_pw']; ?>" size="50" /></td>
	 </tr>
	</table>
</div>
<div class="bfoot center"><input type="submit" value="Continue" /></div>