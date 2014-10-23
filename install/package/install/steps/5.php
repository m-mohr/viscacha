<?php
if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['PHP_SELF'])) {
	$source = rtrim(viscacha_dirname($_SERVER['PHP_SELF']), '/\\');
	$pos = strrpos($source, '/');
	if ($pos === false) {
		$pos = strrpos($source, '\\');
	}
	if ($pos > 0) {
		$dest = substr($source, 0, $pos+1);
		$furl = "http://".$_SERVER['HTTP_HOST'].rtrim($dest, '/\\');
	}
	else {
		$furl = '';
	}
}
else {
	$furl = '';
}
if (isset($_SERVER['SERVER_ADMIN'])) {
	$email = $_SERVER['SERVER_ADMIN'];
}
else {
	$email = '';
}
$fpath = str_replace('\\', '/', realpath('../'));
?>
<div class="bbody">
	<input type="hidden" name="save" value="1" />
	<table class="tables">
	 <tr> 
	  <td class="mbox" width="50%">Name der Seite:<br /><span class="stext">Sollte 64 Zeichen nicht überschreiten</span></td>
	  <td class="mbox" width="50%"><input type="text" name="fname" value="" size="50" /></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">Kurze Beschreibung der Seite:<br /><span class="stext">HTML ist möglich.</span></td>
	  <td class="mbox" width="50%"><input type="text" name="fdesc" value="" size="50" /></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">URL des Seite:<br /><span class="stext">Url zum Ordner in dem die Dateien liegen (ohne / am Ende).</span></td>
	  <td class="mbox" width="50%"><input type="text" name="furl" value="<?php echo $furl; ?>" size="50" /></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">Pfad zum Forum:<br /><span class="stext">Pfad zum Ordner in dem die Dateien liegen (ohne / am Ende).</span></td>
	  <td class="mbox" width="50%"><input type="text" name="fpath" value="<?php echo $fpath; ?>" size="50" /></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">Emailadresse des Forums:<br /><span class="stext">Wird bei allen ausgehenden Emails verwendet.</span></td>
	  <td class="mbox" width="50%"><input type="text" name="forenmail" value="<?php echo $email; ?>" size="50" /></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">Prefix f&uuml;r Cookies:<br /><span class="stext">Nur Buchstaben von a-z und _ benutzen!</span></td>
	  <td class="mbox" width="50%"><input type="text" size="10" name="cookie_prefix" value="vc" /></td> 
	 </tr>
	</table>
</div>
<div class="bfoot center"><input type="submit" value="Continue" /></div>
