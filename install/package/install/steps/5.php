<?php
include('data/config.inc.php');

function getLangNameByPath ($dir) {
	$file = realpath($dir).DIRECTORY_SEPARATOR.'settings.lng.php';
	if (file_exists($file)) {
		include($file);
		if (!empty($lang['lang_name'])) {
			return $lang['lang_name'];
		}
	}
	return null;
}

if (empty($config['furl']) == false) {
	$furl = $config['furl'];
}
else {
	// HTTP_HOST is having the correct browser url in most cases...
	$server_name = (!empty($_SERVER['HTTP_HOST'])) ? strtolower($_SERVER['HTTP_HOST']) : ((!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME'));
	$https = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://');

	$source = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
	if (!$source) {
		$source = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
	}
	// Replace backslashes and doubled slashes (could happen on some proxy setups)
	$source = str_replace(array('\\', '//', '/install'), '/', $source);
	$source = trim(trim(dirname($source)), '/');

	$furl = $https.$server_name.'/'.$source;
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

// Determine path to script
$fpath = !empty($config['fpath']) ? $config['fpath'] : str_replace('\\', '/', realpath('./'));

$langarr = array();
$d = dir('language/');
while (false !== ($entry = $d->read())) {
	$dir = $d->path.DIRECTORY_SEPARATOR.$entry;
	if (is_id($entry)  && is_dir($dir)) {
		$name = getLangNameByPath($dir);
		$langarr[$entry] = $name;
	}
}
$d->close();

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
	  <td class="mbox" width="50%">Email address:<br /><span class="stext">The emails will be sent from this address.</span></td>
	  <td class="mbox" width="50%"><input type="text" name="forenmail" value="<?php echo $email; ?>" size="50" /></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">Standard language:<br /><span class="stext">This language will be set as standard language for the Forum and the Admin CP. You can change this settings later.</span></td>
	  <td class="mbox" width="50%">
	  	<select name="langdir">
	  		<?php foreach ($langarr as $lid => $lname) { ?>
	  		<option value="<?php echo $lid; ?>"<?php echo iif($config['langdir'] == $lid, ' selected="selected"'); ?>><?php echo $lname; ?></option>
	  		<?php } ?>
	  	</select>
	  </td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">Cookie prefix:<br /><span class="stext">Only the characters a-z and _ are allowed!</span></td>
	  <td class="mbox" width="50%"><input type="text" size="10" name="cookie_prefix" value="<?php echo $config['cookie_prefix']; ?>" /></td>
	 </tr>
	</table>
</div>
<div class="bfoot center"><input type="submit" value="Continue" /></div>