<?php 
$path = '/';
if (isset($_SERVER['DOCUMENT_ROOT'])) {
	$path = str_replace(realpath($_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR, '', realpath('../'));
}

?>
<div class="bbody">
	<input type="hidden" name="save" value="1" />
	<table class="tables">
	 <tr>
	  <td class="mbox" width="50%">FTP-Server:<br /><span class="stext">You can leave it empty for disabling FTP.</span></td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_server" value="" size="50"></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">FTP-Port:</td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_port" value="21" size="4"></td> 
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">FTP-Startpfad:<br /><span class="stext">Pfad von dem aus das FTP-Programm arbeiten soll. Dieser Pfad soltle ausgehen vom normalen Start-FTP-Pfad relativ zu ihrem Viscacha-Verzeichnis zeigen. Wenn der FTP-Account direkt auf das Viscacha-Verzeichnis zeigt, reicht ein "/" unter *nix-Systemen.</span></td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_path" value="<?php echo $path; ?>" size="50"></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">FTP-Account-Benutzername</span></td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_user" value="" size="50"></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">FTP-Account-Passwort:</td>
	  <td class="mbox" width="50%"><input type="password" name="ftp_pw" value="" size="50"></td> 
	 </tr>
	</table>
</div>
<div class="bfoot center"><input type="submit" value="Continue" /></div>
