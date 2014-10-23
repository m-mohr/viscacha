<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

function custom_select($arr) {
	global $config, $lang;
	$val = prepare_custom($arr['optionscode']);
?>
<tr>
 <td class="mbox" width="35%"><?php echo $arr['title']; ?><br /><span class="stext"><?php echo $arr['description']; ?></span></td>
 <td class="mbox" width="35%">
 <select name="<?php echo $arr['name']; ?>">
 <?php foreach ($val as $key => $value) { ?>
  <option value="<?php echo $key; ?>"<?php echo iif($config[$arr['groupname']][$arr['name']] == $key, ' selected="selected"'); ?>><?php echo $value; ?></option>
 <?php } ?>
 </select>
 </td>
 <td class="mbox" width="10%"><?php if ($arr['package'] > 0) { ?><s><?php echo $lang->phrase('admin_delete_settings'); ?></s><?php } else { ?><a class="button" href="admin.php?action=settings&job=delete&name=<?php echo $arr['name']; ?>&id=<?php echo $arr['sgroup']; ?>"><?php echo $lang->phrase('admin_delete_settings'); ?><?php } ?></td>
 <td class="mbox" width="20%"><code>$config['<?php echo $arr['groupname']; ?>']['<?php echo $arr['name']; ?>']</code></td>
</tr>
<?php
}
function custom_checkbox($arr) {
	global $config, $lang;
?>
<tr>
 <td class="mbox" width="35%"><?php echo $arr['title']; ?><br /><span class="stext"><?php echo $arr['description']; ?></span></td>
 <td class="mbox" width="35%"><input type="checkbox" name="<?php echo $arr['name']; ?>" value="<?php echo $config[$arr['groupname']][$arr['name']]; ?>"<?php echo iif($config[$arr['groupname']][$arr['name']],' checked="checked"'); ?> /></td>
 <td class="mbox" width="10%"><?php if ($arr['package'] > 0) { ?><s><?php echo $lang->phrase('admin_delete_settings'); ?><?php } ?></td>
 <td class="mbox" width="20%"><code>$config['<?php echo $arr['groupname']; ?>']['<?php echo $arr['name']; ?>']</code></td>
</tr>
<?php
}
function custom_text($arr) {
	global $config, $lang;
?>
<tr>
 <td class="mbox" width="35%"><?php echo $arr['title']; ?><br /><span class="stext"><?php echo $arr['description']; ?></span></td>
 <td class="mbox" width="35%"><input type="text" name="<?php echo $arr['name']; ?>" value="<?php echo $config[$arr['groupname']][$arr['name']]; ?>" /></td>
 <td class="mbox" width="10%"><?php if ($arr['package'] > 0) { ?><s><?php echo $lang->phrase('admin_delete_settings'); ?></s><?php } else { ?><a class="button" href="admin.php?action=settings&job=delete&name=<?php echo $arr['name']; ?>&id=<?php echo $arr['sgroup']; ?>"><?php echo $lang->phrase('admin_delete_settings'); ?></a><?php } ?></td>
 <td class="mbox" width="20%"><code>$config['<?php echo $arr['groupname']; ?>']['<?php echo $arr['name']; ?>']</code></td>
</tr>
<?php
}
function custom_textarea($arr) {
	global $config, $lang;
?>
<tr>
 <td class="mbox" width="35%"><?php echo $arr['title']; ?><br /><span class="stext"><?php echo $arr['description']; ?></span></td>
 <td class="mbox" width="35%"><textarea cols="50" rows="4" name="<?php echo $arr['name']; ?>"><?php echo $config[$arr['groupname']][$arr['name']]; ?></textarea></td>
 <td class="mbox" width="10%"><?php if ($arr['package'] > 0) { ?><s><?php echo $lang->phrase('admin_delete_settings'); ?></s><?php } else { ?><a class="button" href="admin.php?action=settings&job=delete&name=<?php echo $arr['name']; ?>&id=<?php echo $arr['sgroup']; ?>"><?php echo $lang->phrase('admin_delete_settings'); ?></a><?php } ?></td>
 <td class="mbox" width="20%"><code>$config['<?php echo $arr['groupname']; ?>']['<?php echo $arr['name']; ?>']</code></td>
</tr>
<?php
}
function prepare_custom($str) {
	global $lang;
	$str = trim($str);
	$explode = preg_split("~(\r\n|\r|\n)+~", $str);
	$arr = array();
	foreach ($explode as $val) {
		$dat = explode('=', $val);
		if (count($dat) > 2) {
			$k = array_shift($dat);
			$dat = implode('=', $dat);
			$arr[$k] = $dat;
		}
		elseif (count($dat) == 2) {
			$arr[$dat[0]] = $dat[1];
		}
		else {
			error('admin.php?action=settings', $lang->phrase('admin_could_not_prepare_custom settings'));
		}
	}
	return $arr;
}
?>