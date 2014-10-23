<?php
include('../data/config.inc.php');
if (empty($config['furl']) == false) {
	$furl = $config['furl'];
}
elseif (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['PHP_SELF'])) {
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
if (empty($config['forenmail']) == false) {
	$email = $config['forenmail'];
}
elseif (isset($_SERVER['SERVER_ADMIN'])) {
	$email = $_SERVER['SERVER_ADMIN'];
}
else {
	$email = '';
}
if (empty($config['fpath']) == false) {
	$fpath = $config['fpath'];
}
else {
	$fpath = str_replace('\\', '/', realpath('../'));
}
?>
<div class="bbody">
	<input type="hidden" name="save" value="1" />
	<table class="tables">
	 <tr>
	  <td class="mbox" width="50%">Site name:<br /><span class="stext">Maximum: 64 characters</span></td>
	  <td class="mbox" width="50%"><input type="text" name="fname" value="<?php echo $config['fname']; ?>" size="50" /></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">Site description:<br /><span class="stext">HTML is allowed.</span></td>
	  <td class="mbox" width="50%"><input type="text" name="fdesc" value="<?php echo $config['fdesc']; ?>" size="50" /></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">Site URL:<br /><span class="stext">URL without trailing slash (/) to the folder containing the files.</span></td>
	  <td class="mbox" width="50%"><input type="text" name="furl" value="<?php echo $furl; ?>" size="50" /></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">Path to site:<br /><span class="stext">Path without trailing slash (/) to the folder containing the files.</span></td>
	  <td class="mbox" width="50%"><input type="text" name="fpath" value="<?php echo $fpath; ?>" size="50" /></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">Email address :<br /><span class="stext">The emails will be sent from this address.</span></td>
	  <td class="mbox" width="50%"><input type="text" name="forenmail" value="<?php echo $email; ?>" size="50" /></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">Cookie prefix:<br /><span class="stext">Only the characters a-z and _ are allowed!</span></td>
	  <td class="mbox" width="50%"><input type="text" size="10" name="cookie_prefix" value="<?php echo $config['cookie_prefix']; ?>" /></td>
	 </tr>
	</table>
</div>
<div class="bfoot center"><input type="submit" value="Continue" /></div>
