<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// MM/AM: MultiLangAdmin
$lang->group("admin/settings");
$lang->group("timezones");

// Loading Config-Data
include('classes/class.phpconfig.php');
include('admin/lib/function.settings.php');

$c = new manageconfig();
$myini = new INI();

($code = $plugins->load('admin_settings_jobs')) ? eval($code) : null;

if ($job == 'admin') {
	$config = $gpc->prepare($config);

	$loadlanguage_obj = $scache->load('loadlanguage');
	$language = $loadlanguage_obj->get();

	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&amp;job=admin2">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	 <tr>
	  <td class="obox" colspan="2"><?php echo $lang->phrase('admin_admin_control_panel_settings'); ?></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%"><?php echo $lang->phrase('admin_use_extended_navigation_interface'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_use_extended_navigation_interface_info'); ?></span></td>
	  <td class="mbox" width="50%"><input type="checkbox" name="nav_interface" value="1"<?php echo iif($admconfig['nav_interface'] == 1, ' checked="checked"'); ?> /></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%"><?php echo $lang->phrase('admin_servers_for_package_browser'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_servers_for_package_browser_info'); ?></span></td>
	  <td class="mbox" width="50%"><textarea rows="5" cols="60" name="package_server"><?php echo str_replace(";", "\n", $admconfig['package_server']); ?></textarea></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%"><?php echo $lang->phrase('admin_positions_for_navigation'); ?><br /><span class="stext">
	  	<strong><?php echo $lang->phrase('admin_positions_for_navigation1'); ?></strong><br />
		<?php echo $lang->phrase('admin_positions_for_navigation2'); ?></span>
	  </td>
	  <td class="mbox" width="50%"><textarea rows="5" cols="60" name="nav_positions"><?php echo $admconfig['nav_positions']; ?></textarea></td>
	 </tr>
	 </tr>
	  <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>" /></td>
	 </tr>
	</table>
	<br />
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	 <tr>
	  <td class="obox" colspan="2"><?php echo $lang->phrase('admin_admin_control_panel_settings_lang'); ?></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%"><?php echo $lang->phrase('admin_acp_standard_language'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_acp_standard_language_info'); ?></span></td>
	  <td class="mbox" width="50%">
		<select name="default_language">
			<option style="font-weight: bold;" value="0"<?php echo iif($admconfig['default_language'] > 0, ' selected="selected"'); ?>><?php echo $lang->phrase('admin_user_standard_language_for_acp'); ?></option>
			<?php foreach ($language as $row) { ?>
			<option value="<?php echo $row['id']; ?>"<?php echo iif($row['id'] == $admconfig['default_language'], ' selected="selected"'); ?>><?php echo $row['language']; ?></option>
			<?php } ?>
		</select>
	  </td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%"><?php echo $lang->phrase('admin_standard_language_temp'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_standard_language_temp_info'); ?></span></td>
	  <td class="mbox" width="50%">
	  	<input type="checkbox" name="temp" value="1" />
	  </td>
	 </tr>
	 </tr>
	  <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit2" value="<?php echo $lang->phrase('admin_form_submit'); ?>" /></td>
	 </tr>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'admin2') {
	echo head();

	$server = trim($gpc->get('package_server', none));
	$server = preg_replace("~(\r\n|\r|\n)~", ";", $server);

	$c->getdata('admin/data/config.inc.php', 'admconfig');
	$c->updateconfig('nav_interface', int);
	$c->updateconfig('package_server', str, $server);
	$c->updateconfig('nav_positions', str);
	$temp = $gpc->get('temp', int);
	if ($temp == 1) {
		$my->settings['default_language'] = $gpc->get('default_language', int);
	}
	else {
		$c->updateconfig('default_language', int);
	}
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'ftp') {
	$temp = $config;
	if ($gpc->get('change', int) == 1) {
		$temp['ftp_server'] = $gpc->get('ftp_server', none);
		$temp['ftp_port'] = $gpc->get('ftp_port', int);
		$temp['ftp_user'] = $gpc->get('ftp_user', none);
		$temp['ftp_pw'] = $gpc->get('ftp_pw', none);
		$temp['ftp_path'] = $gpc->get('ftp_path', none, DIRECTORY_SEPARATOR);
	}
	$temp = $gpc->prepare($temp);

	$path = '-';
	// @todo Add a better way to  determine the path for ftp

	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=ftp2">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	 <tr>
	  <td class="obox" colspan="2"><?php echo $lang->phrase('admin_ftp_settings'); ?></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%"><?php echo $lang->phrase('admin_ftp_server'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_ftp_server_info'); ?></span></td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_server" size="50" value="<?php echo $temp['ftp_server']; ?>"></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%"><?php echo $lang->phrase('admin_ftp_port'); ?></td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_port" value="21" size="4" value="<?php echo $temp['ftp_port']; ?>"></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%"><?php echo $lang->phrase('admin_ftp_startpath'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_ftp_startpath_info'); ?></span></td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_path" value="<?php echo $temp['ftp_path']; ?>" size="50"></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%"><?php echo $lang->phrase('admin_ftp_username'); ?></td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_user" value="<?php echo $temp['ftp_user']; ?>" size="50"></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%"><?php echo $lang->phrase('admin_ftp_password'); ?></td>
	  <td class="mbox" width="50%"><input type="password" name="ftp_pw" value="<?php echo $temp['ftp_pw']; ?>" size="50"></td>
	 </tr>
	 </tr>
	  <td class="ubox" colspan="2" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_test_ftp_connection'); ?>" /></td>
	 </tr>
	</table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'ftp2') {
	require_once("classes/ftp/class.ftp.php");
	require_once("classes/ftp/class.ftp_".pemftp_class_module().".php");

	$temp = array(
		'ftp_server' => $gpc->get('ftp_server', none),
		'ftp_port' => $gpc->get('ftp_port', int),
		'ftp_user' => $gpc->get('ftp_user', none),
		'ftp_pw' => $gpc->get('ftp_pw', none),
		'ftp_path' => $gpc->get('ftp_path', none, DIRECTORY_SEPARATOR)
	);

	$error = false;
	$dataGiven = count(array_unique($temp)) == 5;
	if ($dataGiven) {
		ob_start();
		$ftp = new ftp(true, true);
		if(!$ftp->SetServer($temp['ftp_server'], $temp['ftp_port'])) {
			$error = 'admin_server_port_invalid';
		}
		else {
			if (!$ftp->connect()) {
				$error = 'admin_cannot_connect_to_ftp_server';
			}
			else {
				if (!$ftp->login($temp['ftp_user'], $temp['ftp_pw'])) {
					$ftp->quit();
					$error = 'admin_cannot_authenticate_at_ftp_server';
				}
				else {

					if (!$ftp->chdir($temp['ftp_path']) || !$ftp->file_exists('data/config.inc.php')) {
						$ftp->quit();
						$lang->assign('ftp_path', $temp['ftp_path']);
						$error = 'admin_ftp_directory_does_not_exist';
					}
				}
			}
		}
		$log = ob_get_contents();
		ob_end_clean();
	}

	echo head();
	if ($error === false) {
		$c->getdata();
		$c->updateconfig('ftp_server', str, $temp['ftp_server']);
		$c->updateconfig('ftp_user', str, $temp['ftp_user']);
		$c->updateconfig('ftp_pw', str, $temp['ftp_pw']);
		$c->updateconfig('ftp_path', str, $temp['ftp_path']);
		$c->updateconfig('ftp_port', int, $temp['ftp_port']);
		$c->savedata();

		$msg = $dataGiven ? $lang->phrase('admin_connection_is_ok') : null;

		ok('admin.php?action=settings&job=settings', $msg);
	}
	else {
		?>
		<form name="form" method="post" action="admin.php?action=settings&amp;job=ftp&amp;change=1">
		<?php foreach ($temp as $key => $value) { ?>
		<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $gpc->prepare($value); ?>" />
		<?php } ?>
		<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
		 <tr>
		  <td class="obox" colspan="2"><?php echo $lang->phrase('admin_ftp_connection_test'); ?></td>
		 </tr>
		 <tr>
		  <td class="mbox" width="100%">
		   <strong><?php echo $lang->phrase($error); ?></strong><br /><br />
		   <strong><?php echo $lang->phrase('admin_ftp_command_log'); ?></strong><br />
		   <pre><?php echo $log; ?></pre>
		  </td>
		 </tr>
		 <tr>
		  <td class="ubox center"><input type="submit" value="<?php echo $lang->phrase('admin_configure_ftp_connection'); ?>" /></td>
		 </tr>
		</table>
		</form>
		<?php
		echo foot();
	}
}
elseif ($job == 'posts') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=posts2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_topics_posts_title'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_allow_guest_to_post_without_email'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="guest_email_optional" value="1"<?php echo iif($config['guest_email_optional'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_posts_per_page'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="topiczahl" value="<?php echo $config['topiczahl']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_max_length_for_reason'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="maxeditlength" value="<?php echo $config['maxeditlength']; ?>" size="6"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_min_length_for_reason'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_min_length_for_reason_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="mineditlength" value="<?php echo $config['mineditlength']; ?>" size="6"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_timelimit_post_edit'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_timelimit_post_edit_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="edit_edit_time" value="<?php echo $config['edit_edit_time']; ?>" size="5"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_timelimit_post_delete'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_timelimit_post_delete_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="edit_delete_time" value="<?php echo $config['edit_delete_time']; ?>" size="5"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_max_multiquotes'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_max_multiquotes_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="maxmultiquote" value="<?php echo $config['maxmultiquote']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_length_of_post_title'); ?></td>
	   <td class="mbox" width="50%">
	    <?php echo $lang->phrase('admin_minimum_x'); ?> <input type="text" name="mintitlelength" value="<?php echo $config['mintitlelength']; ?>" size="4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	    <?php echo $lang->phrase('admin_maximum_x'); ?> <input type="text" name="maxtitlelength" value="<?php echo $config['maxtitlelength']; ?>" size="8">
	   </td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_length_of_post_text'); ?></td>
	   <td class="mbox" width="50%">
	    <?php echo $lang->phrase('admin_minimum_x'); ?> <input type="text" name="minpostlength" value="<?php echo $config['minpostlength']; ?>" size="4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	    <?php echo $lang->phrase('admin_maximum_x'); ?> <input type="text" name="maxpostlength" value="<?php echo $config['maxpostlength']; ?>" size="8">
	   </td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_autoresize_pics'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_autoresize_pics_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="resizebigimg" value="1"<?php echo iif($config['resizebigimg'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_autoresize_max_width_pics'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_autoresize_max_width_pics_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="resizebigimgwidth" value="<?php echo $config['resizebigimgwidth']; ?>" size="6"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_number_of_subscriptions_per_page'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="abozahl" value="<?php echo $config['abozahl']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_show_real_name_post'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="fullname_posts" value="1"<?php echo iif($config['fullname_posts'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_show_online_status_post'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="post_user_status" value="1"<?php echo iif($config['post_user_status'] == 1, ' checked="checked"'); ?> /></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_enable_change_vote'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_enable_change_vote_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="vote_change" value="1"<?php echo iif($config['vote_change'] == 1, ' checked="checked"'); ?> /></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	 <br class="minibr" />
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_topics_posts_rating'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_enable_postrating'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="postrating" value="1"<?php echo iif($config['postrating'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_postrating_show_only_with_x_votes'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="postrating_counter" value="<?php echo $config['postrating_counter']; ?>" size="5"></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'posts2') {
	echo head();

	$c->getdata();
	$c->updateconfig('maxeditlength', int);
	$c->updateconfig('mineditlength', int);
	$c->updateconfig('resizebigimg', int);
	$c->updateconfig('resizebigimgwidth', int);
	$c->updateconfig('maxpostlength', int);
	$c->updateconfig('minpostlength', int);
	$c->updateconfig('maxtitlelength', int);
	$c->updateconfig('mintitlelength', int);
	$c->updateconfig('maxmultiquote', int);
	$c->updateconfig('edit_delete_time', int);
	$c->updateconfig('edit_edit_time', int);
	$c->updateconfig('topiczahl', int);
	$c->updateconfig('postrating', int);
	$c->updateconfig('postrating_counter', int);
	$c->updateconfig('guest_email_optional', int);
	$c->updateconfig('abozahl', int);
	$c->updateconfig('fullname_posts', int);
	$c->updateconfig('post_user_status', int);
	$c->updateconfig('vote_change', int);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'profile') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=profile2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_profile_edit_view'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_username_max_length'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="maxnamelength" value="<?php echo $config['maxnamelength']; ?>" size="5"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_username_min_length'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="minnamelength" value="<?php echo $config['minnamelength']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_pw_max_length'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="maxpwlength" value="<?php echo $config['maxpwlength']; ?>" size="5"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_pw_min_length'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="minpwlength" value="<?php echo $config['minpwlength']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_about_max_length'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="maxaboutlength" value="<?php echo $config['maxaboutlength']; ?>" size="8"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_notice_length'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_notice_length_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="maxnoticelength" value="<?php echo $config['maxnoticelength']; ?>" size="8"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_mylast_numer_of_posts'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_mylast_numer_of_posts_info'); ?> &quot;<a href="editprofile.php?action=mylast" target="_blank"><?php echo $lang->phrase('admin_mylast_numer_of_posts_info2'); ?></a>&quot;</span></td>
	   <td class="mbox" width="50%"><input type="text" name="mylastzahl" value="<?php echo $config['mylastzahl']; ?>" size="5"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_onlinetstatus_profile'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_onlinetstatus_profile_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="osi_profile" value="1"<?php echo iif($config['osi_profile'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_allow_change_name'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_allow_change_name_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="changename_allowed" value="1"<?php echo iif($config['changename_allowed'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_disallow_change_design'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="hidedesign" value="1"<?php echo iif($config['hidedesign'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_disallow_change_language'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="hidelanguage" value="1"<?php echo iif($config['hidelanguage'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_show_posts_per_day'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="showpostcounter" value="1"<?php echo iif($config['showpostcounter'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_update_posts_immediately'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_update_posts_immediately_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="updatepostcounter" value="1"<?php echo iif($config['updatepostcounter'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_show_memberrating'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_show_memberrating_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="memberrating" value="1"<?php echo iif($config['memberrating'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_not_show_the_rating_before'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="memberrating_counter" value="<?php echo $config['memberrating_counter']; ?>" size="5"></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'profile2') {
	echo head();

	$c->getdata();
	$c->updateconfig('osi_profile', int);
	$c->updateconfig('changename_allowed', int);
	$c->updateconfig('showpostcounter', int);
	$c->updateconfig('maxnamelength', int);
	$c->updateconfig('minnamelength', int);
	$c->updateconfig('minpwlength', int);
	$c->updateconfig('maxpwlength', int);
	$c->updateconfig('maxaboutlength', int);
	$c->updateconfig('maxnoticelength', int);
	$c->updateconfig('memberrating', int);
	$c->updateconfig('memberrating_counter', int);
	$c->updateconfig('hidedesign', int);
	$c->updateconfig('hidelanguage', int);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'signature') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=signature2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_signatures_edit'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_max_sig_length'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="maxsiglength" value="<?php echo $config['maxsiglength']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_disallow_img'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sig_bbimg" value="1"<?php echo iif($config['sig_bbimg'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_disallow_bb'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sig_bbcode" value="1"<?php echo iif($config['sig_bbcode'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_disallow_list'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sig_bblist" value="1"<?php echo iif($config['sig_bblist'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_disallow_edit'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sig_bbedit" value="1"<?php echo iif($config['sig_bbedit'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_disallow_ot'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sig_bbot" value="1"<?php echo iif($config['sig_bbot'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_disallow_h'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="" value="1"<?php echo iif($config['sig_bbh'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'signature2') {
	echo head();

	$c->getdata();
	$c->updateconfig('maxsiglength', int);
	$c->updateconfig('sig_bbimg', int);
	$c->updateconfig('sig_bbcode', int);
	$c->updateconfig('sig_bblist', int);
	$c->updateconfig('sig_bbedit', int);
	$c->updateconfig('sig_bbot', int);
	$c->updateconfig('sig_bbh', int);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'search') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=search2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_search_edit'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_min_length_search'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_min_length_search_info'); ?> </span></td>
	   <td class="mbox" width="50%"><input type="text" name="searchminlength" value="<?php echo $config['searchminlength']; ?>" size="3"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_max_search_results'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_max_search_results_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="maxsearchresults" value="<?php echo $config['maxsearchresults']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_activate_flodblocking'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_activate_flodblocking_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="floodsearch" value="1"<?php echo iif($config['floodsearch'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_number_search_results'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="searchzahl" value="<?php echo $config['searchzahl']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_number_active_topics'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="activezahl" value="<?php echo $config['activezahl']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'search2') {
	echo head();

	$c->getdata();
	$c->updateconfig('floodsearch', int);
	$c->updateconfig('maxsearchresults', int);
	$c->updateconfig('searchminlength', int);
	$c->updateconfig('searchzahl', int);
	$c->updateconfig('activezahl', int);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'server') {
	$config = $gpc->prepare($config);

	$gdv = $lang->phrase('admin_gd_not_found');
	if (viscacha_function_exists('gd_info')) {
	    $gd = @gd_info();
	}
	if (!empty($gd['GD Version'])) {
		$gdv = $gd['GD Version'];
	}
	else {
    	ob_start();
    	phpinfo();
    	$info = ob_get_contents();
    	ob_end_clean();
    	foreach(explode("\n", $info) as $line) {
     		if(strpos($line, "GD Version")!==false) {
        		$gdv = trim(str_replace("GD Version", "", strip_tags($line)));
        	}
    	}
	}

	$std_err_reporting = ($config['error_reporting'] != '0' && $config['error_reporting'] != 'E_ALL' && $config['error_reporting'] != 'E_ERROR');

	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=server2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_php_web_file_edit'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_gd_version'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_gd_version_info'); ?> <?php echo $gdv; ?></span></td>
	   <td class="mbox" width="50%"><select name="gdversion">
	   <option value="1"<?php echo iif($config['gdversion'] == 1, ' selected="selected"'); ?>>1.x</option>
	   <option value="2"<?php echo iif($config['gdversion'] == 2, ' selected="selected"'); ?>>2.x</option>
	   </select></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_php_error_report'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_php_error_report_info'); ?></span></td>
	   <td class="mbox" width="50%">
	    <select name="error_reporting">
	     <option value="-1"<?php echo iif($std_err_reporting, ' selected="selected"'); ?>><?php echo $lang->phrase('admin_php_standard'); ?></option>
	     <option value="0"<?php echo iif($config['error_reporting'] == '0', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_e_none'); ?></option>
	     <option value="E_ALL"<?php echo iif($config['error_reporting'] == 'E_ALL', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_e_all'); ?></option>
	     <option value="E_ERROR"<?php echo iif($config['error_reporting'] == 'E_ERROR', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_e_error'); ?></option>
	    </select>
	   </td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_error_handler'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_error_handler_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="error_handler" value="1"<?php echo iif($config['error_handler'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_save_php_errors'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_save_php_errors_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="error_log" value="1"<?php echo iif($config['error_log'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_htaccess_top_domain'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_htaccess_top_domain_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="correctsubdomains" value="1"<?php echo iif($config['hterrordocs'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_htaccess_error_doc'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_htaccess_error_doc_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="hterrordocs" value="1"<?php echo iif($config['hterrordocs'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'server2') {
	echo head();

	$c->getdata();
	$c->updateconfig('gdversion', int);
	$c->updateconfig('error_handler', int);
	$c->updateconfig('error_log', int);
	$c->updateconfig('error_reporting', str);
	$c->updateconfig('correctsubdomains', int);
	$c->updateconfig('hterrordocs', int);
	$c->savedata();

	$filesystem->unlink('.htaccess');

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'session') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=session2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_session_edit'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_session_id_length'); ?><br /><span class="stext"></span></td>
	   <td class="mbox" width="50%"><select name="sid_length">
	   <option value="32"<?php echo iif($config['sid_length'] == '32', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_session_32_charackters'); ?></option>
	   <option value="64"<?php echo iif($config['sid_length'] == '64', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_session_64_charackters'); ?></option>
	   <option value="96"<?php echo iif($config['sid_length'] == '96', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_session_96_charackters'); ?></option>
	   <option value="128"<?php echo iif($config['sid_length'] == '128', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_session_128_charackters'); ?></option>
	   </select></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_time_check_inactive_users'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_time_check_inactive_users_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="sessionrefresh" value="<?php echo $config['sessionrefresh']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_time_after_user_inactive'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_time_after_user_inactive_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="sessionsave" value="<?php echo $config['sessionsave']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_active_floodblocking'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_active_floodblocking_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="enableflood" value="1"<?php echo iif($config['enableflood'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_dession_ip_valadtion'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_dession_ip_valadtion_info'); ?></span></td>
	   <td class="mbox" width="50%">
	   <select name="session_checkip">
	    <option value="4"<?php echo iif($config['session_checkip'] == 4,' selected="selected"'); ?>><?php echo $lang->phrase('admin_session_all'); ?></option>
	    <option value="3"<?php echo iif($config['session_checkip'] == 3,' selected="selected"'); ?>><?php echo $lang->phrase('admin_session_a_b_c'); ?></option>
	    <option value="2"<?php echo iif($config['session_checkip'] == 2,' selected="selected"'); ?>><?php echo $lang->phrase('admin_session_a_b'); ?></option>
	    <option value="0"<?php echo iif($config['session_checkip'] == 0,' selected="selected"'); ?>><?php echo $lang->phrase('admin_session_none'); ?></option>
	   </select>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table><br />
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_session_edit'); ?> &raquo; <?php echo $lang->phrase('admin_login_attempts'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_login_attempts_max'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_login_attempts_max_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="login_attempts_max" value="<?php echo $config['login_attempts_max']; ?>" size="3"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_login_attempts_time'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_login_attempts_time_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="login_attempts_time" value="<?php echo $config['login_attempts_time']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_login_attempts_blocktime'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_login_attempts_blocktime_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="login_attempts_blocktime" value="<?php echo $config['login_attempts_blocktime']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'session2') {
	echo head();

	$c->getdata();
	$c->updateconfig('sid_length', int);
	$c->updateconfig('sessionrefresh', int);
	$c->updateconfig('sessionsave', int);
	$c->updateconfig('enableflood', int);
	$c->updateconfig('session_checkip', int);
	$c->updateconfig('login_attempts_max', int);
	$c->updateconfig('login_attempts_time', int);
	$c->updateconfig('login_attempts_blocktime', int);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'boardcat') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=boardcat2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_forum_categories_edit'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_number_of_topics'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_number_of_topics_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="forumzahl" value="<?php echo $config['forumzahl']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_length_of_topic'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_length_of_topic_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" size="5" name="lasttopic_chars" value="<?php echo $config['lasttopic_chars']; ?>" /></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_show_subforums_in_overview'); ?></font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="showsubfs" value="1"<?php echo iif($config['showsubfs'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_sync_forumstatistic'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_sync_forumstatistic_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="updateboardstats" value="1"<?php echo iif($config['updateboardstats'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'boardcat2') {
	echo head();

	$c->getdata();
	$c->updateconfig('forumzahl', int);
	$c->updateconfig('lasttopic_chars', int);
	$c->updateconfig('showsubfs', int);
	$c->updateconfig('updateboardstats', int);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'user') {
	$config = $gpc->prepare($config);
	echo head();

	$mlistfields = explode(',', $config['mlist_fields']);

	?>
	<form name="form" method="post" action="admin.php?action=settings&job=user2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_memberlist_edit'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_memberlist_per_page'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_memberlist_per_page_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="mlistenzahl" value="<?php echo $config['mlistenzahl']; ?>" size="4" /></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_memberlist_field_options'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_memberlist_field_options_info'); ?></span></td>
	   <td class="mbox" width="50%">
	   <input type="checkbox" name="mlistfields[]" value="fullname"<?php echo iif(in_array('fullname', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_real_name'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="mail"<?php echo iif(in_array('mail', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_email'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="pm"<?php echo iif(in_array('pm', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_pm'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="regdate"<?php echo iif(in_array('regdate', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_date_register'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="posts"<?php echo iif(in_array('posts', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_posts'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="hp"<?php echo iif(in_array('hp', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_hp'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="location"<?php echo iif(in_array('location', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_location'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="gender"<?php echo iif(in_array('gender', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_grender'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="birthday"<?php echo iif(in_array('birthday', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_birthday'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="pic"<?php echo iif(in_array('pic', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_avatar'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="lastvisit"<?php echo iif(in_array('lastvisit', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_last_visit'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="icq"<?php echo iif(in_array('icq', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_icq'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="yahoo"<?php echo iif(in_array('yahoo', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_yahoo'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="aol"<?php echo iif(in_array('aol', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_aol'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="msn"<?php echo iif(in_array('msn', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_msn'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="jabber"<?php echo iif(in_array('jabber', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_jabber'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="skype"<?php echo iif(in_array('skype', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_skype'); ?><br />
	   <input type="checkbox" name="mlistfields[]" value="online"<?php echo iif(in_array('online', $mlistfields), ' checked="checked"'); ?> /> <?php echo $lang->phrase('admin_memberlist_online-status'); ?>
	   </td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_show_inactive_user'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_show_inactive_user_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="mlist_showinactive"<?php echo iif($config['mlist_showinactive'] == 1,' checked="checked"'); ?> value="1" /></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%">
	    <?php echo $lang->phrase('admin_allow_users_to_filter_members'); ?><br />
	    <span class="stext"><?php echo $lang->phrase('admin_allow_users_to_filter_members_info'); ?></span></td>
	   <td class="mbox" width="50%">
	    <select name="mlist_filtergroups">
	     <option <?php echo iif($config['mlist_filtergroups'] == 0,' selected="selected"'); ?> value="0" /><?php echo $lang->phrase('admin_filter_a'); ?></option>
	     <option <?php echo iif($config['mlist_filtergroups'] == 1,' selected="selected"'); ?> value="1" /><?php echo $lang->phrase('admin_filter_b'); ?></option>
	     <option <?php echo iif($config['mlist_filtergroups'] == 2,' selected="selected"'); ?> value="2" /><?php echo $lang->phrase('admin_filter_c'); ?></option>
	    </select>
	   </td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table><br />
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_teamlist_edit'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_show_mod_rights'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_show_mod_rights_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="team_mod_dateuntil" value="1"<?php echo iif($config['team_mod_dateuntil'] == 1,' checked="checked"'); ?> /></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>" /></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'user2') {
	echo head();

	$arraylist = $gpc->get('mlistfields', arr_str);
	$arraylist = array_map('strtolower', $arraylist);
	$arraylist = array_map('trim', $arraylist);
	$list = implode(',',$arraylist);

	$c->getdata();
	$c->updateconfig('mlistenzahl', int);
	$c->updateconfig('mlist_showinactive', int);
	$c->updateconfig('mlist_filtergroups', int);
	$c->updateconfig('mlist_fields', str, $list);
	$c->updateconfig('team_mod_dateuntil', int);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'cmsp') {
	$config = $gpc->prepare($config);
	$language_obj = $scache->load('loadlanguage');
	$language = $language_obj->get();
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=cmsp2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_cms_portal_edit'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_switch_cms_portal'); ?></td>
	   <td class="mbox" width="50%"><select name="indexpage">
	   <option value="forum"<?php echo iif($config['indexpage'] == 'forum', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_switch_forum'); ?></option>
	   <option value="portal"<?php echo iif($config['indexpage'] == 'portal', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_switch_portal'); ?></option>
	   </select></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%">
	   	<?php echo $lang->phrase('admin_doclang_title'); ?><br />
	   	<span class="stext"><?php echo $lang->phrase('admin_doclang_desc'); ?></span>
	   </td>
	   <td class="mbox" width="50%"><select name="doclang">
	   <?php foreach ($language as $lid => $data) { ?>
	   <option value="<?php echo $lid; ?>"<?php echo iif($config['doclang'] == $lid, ' selected="selected"'); ?>><?php echo $data['language']; ?></option>
	   <?php } ?>
	   </select></td>
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'cmsp2') {
	echo head();

	$c->getdata();
	$c->updateconfig('indexpage', str);
	$c->updateconfig('doclang', int);
	$c->savedata();

	ok('admin.php?action=settings&job=cmsp');
}
elseif ($job == 'pm') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=pm2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_admin_edit'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_number_of_pm'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="pmzahl" value="<?php echo $config['pmzahl']; ?>" size="4" /></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_online_status_in_pm'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pm_user_status" value="1"<?php echo iif($config['pm_user_status'] == 1, ' checked="checked"'); ?> /></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'pm2') {
	echo head();

	$c->getdata();
	$c->updateconfig('pmzahl', int);
	$c->updateconfig('pm_user_status', int);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'email') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=email2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_email_edit'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_mode_od_dispatch'); ?><br /><span class="stext"></span></td>
	   <td class="mbox" width="50%"><select name="type">
	   <option value="0"<?php echo iif($config['smtp'] != 1 && $config['sendmail'] != 1, ' selected="selected"'); ?>><?php echo $lang->phrase('admin_dispatch_internal_mail'); ?></option>
	   <option value="1"<?php echo iif($config['sendmail'] == 1, ' selected="selected"'); ?>><?php echo $lang->phrase('admin_dispatch_sendmail'); ?></option>
	   <option value="2"<?php echo iif($config['smtp'] == 1, ' selected="selected"'); ?>><?php echo $lang->phrase('admin_dispatch_smtp'); ?></option>
	   </select></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_sendmail_host'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_sendmail_host_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="sendmail_host" value="<?php echo $config['sendmail_host']; ?>" size="50"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_smtp_host'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_smtp_host_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="smtp_host" value="<?php echo $config['smtp_host']; ?>" size="50"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_stmp_authentificaton'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_stmp_authentificaton_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="smtp_auth" value="1"<?php echo iif($config['smtp_auth'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_smtp_username'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_smtp_username_pw_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="smtp_username" value="<?php echo $config['smtp_username']; ?>" size="50"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_smtp_password'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_smtp_username_pw_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="smtp_password" value="<?php echo $config['smtp_password']; ?>" size="50"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_black_trash_email'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_black_trash_email_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sessionmails" value="1"<?php echo iif($config['sessionmails'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_check_email_mx_record'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_check_email_mx_record_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="email_check_mx" value="1"<?php echo iif($config['email_check_mx'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'email2') {
	echo head();

	$versand = $gpc->get('type', int);

	$c->getdata();
	if ($versand == 2) {
		$c->updateconfig('smtp', int, 1);
		$c->updateconfig('sendmail', int, 0);
	}
	elseif ($versand == 1) {
		$c->updateconfig('smtp', int, 0);
		$c->updateconfig('sendmail', int, 1);
	}
	else {
		$c->updateconfig('smtp', int, 0);
		$c->updateconfig('sendmail', int, 0);
	}
	$c->updateconfig('sendmail_host', str);
	$c->updateconfig('smtp_host', str);
	$c->updateconfig('smtp_auth', int);
	$c->updateconfig('smtp_username', str);
	$c->updateconfig('smtp_password', str);
	$c->updateconfig('sessionmails', int);
	$c->updateconfig('email_check_mx', int);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'captcha') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=captcha2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_spambot_edit'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_activate_spambot_registration'); ?></td>
	   <td class="mbox" width="50%">
	    <select name="botgfxtest">
	     <?php for($i = 0; $i <= 2; $i++) { ?>
	     <option value="<?php echo $i; ?>"<?php echo iif($config['botgfxtest'] == $i, ' selected="selected"'); ?>><?php echo $lang->phrase('admin_captcha_type'.$i); ?></option>
	     <?php } ?>
	    </select>
	   </td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_activate_spambot_at_guests'); ?></td>
	   <td class="mbox" width="50%">
	    <select name="botgfxtest_posts">
	     <?php for($i = 0; $i <= 2; $i++) { ?>
	     <option value="<?php echo $i; ?>"<?php echo iif($config['botgfxtest_posts'] == $i, ' selected="selected"'); ?>><?php echo $lang->phrase('admin_captcha_type'.$i); ?></option>
	     <?php } ?>
	    </select>
	   </td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	 <?php if ($config['botgfxtest'] == 1 || $config['botgfxtest_posts'] == 1) { ?>
	<br class="minibr" />
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_spambot_veriword'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_spambot_veriword_info'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_image_width_captcha'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_image_width_captcha_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="botgfxtest_width" value="<?php echo $config['botgfxtest_width']; ?>" size="5">px</td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_image_height_captcha'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_image_height_captcha_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="botgfxtest_height" value="<?php echo $config['botgfxtest_height']; ?>" size="5">px</td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_wave_filter_captcha'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="botgfxtest_filter" value="1"<?php echo iif($config['botgfxtest_filter'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_dyeing_letters_captcha'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_dyeing_letters_captcha_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="botgfxtest_colortext" value="1"<?php echo iif($config['botgfxtest_colortext'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_file_typ_captcha'); ?></td>
	   <td class="mbox" width="50%">
	   <select name="botgfxtest_format">
	   <option value="jpg"<?php echo iif($config['botgfxtest_format'] != 'png',' selected="selected"'); ?>><?php echo $lang->phrase('admin_captcha_jpeg'); ?></option>
	   <option value="png"<?php echo iif($config['botgfxtest_format'] == 'png',' selected="selected"'); ?>><?php echo $lang->phrase('admin_captcha_png'); ?></option>
	   </select>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_pic_quality_captcha'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_pic_quality_captcha_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="botgfxtest_quality" value="<?php echo $config['botgfxtest_quality']; ?>" size="5">%</td>
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	<?php
	}
	if ($config['botgfxtest'] == 2 || $config['botgfxtest_posts'] == 2) {
		$re_link = '<a href="http://recaptcha.net/api/getkey?app=Viscacha" target="_blank">reCaptcha</a>';
		?>
	<br class="minibr" />
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_spambot_recaptcha'); ?></b></td>
	  </tr>
	   <tr>
	   <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_spambot_recaptcha_info'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_recaptcha_public_key'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_recaptcha_public_key_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="botgfxtest_recaptcha_public" value="<?php echo $config['botgfxtest_recaptcha_public']; ?>" size="55"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_recaptcha_private_key'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_recaptcha_private_key_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="botgfxtest_recaptcha_private" value="<?php echo $config['botgfxtest_recaptcha_private']; ?>" size="55"></td>
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	 <?php } ?>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'captcha2') {
	echo head();

	$register = $gpc->get('botgfxtest', int);
	$posts = $gpc->get('botgfxtest_posts', int);

	$c->getdata();
	$c->updateconfig('botgfxtest', int);
	$c->updateconfig('botgfxtest_posts', int);
	if ($config['botgfxtest'] == 1 || $config['botgfxtest_posts'] == 1) {
		$c->updateconfig('botgfxtest_filter', int);
		$c->updateconfig('botgfxtest_colortext', int);
		$c->updateconfig('botgfxtest_width', int);
		$c->updateconfig('botgfxtest_height', int);
		$c->updateconfig('botgfxtest_format', str);
		$c->updateconfig('botgfxtest_quality', int);
	}
	if ($config['botgfxtest'] == 2 || $config['botgfxtest_posts'] == 2) {
		$c->updateconfig('botgfxtest_recaptcha_public', str);
		$c->updateconfig('botgfxtest_recaptcha_private', str);
	}

	$c->savedata();

	ok('admin.php?action=settings&job=captcha');
}
elseif ($job == 'register') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=register2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_registration_edit'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_disable_registration'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_disable_registration_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="disableregistration" value="1"<?php echo iif($config['disableregistration'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_user_activation'); ?><br /><span class="stext"></span></td>
	   <td class="mbox" width="50%"><select name="confirm_registration">
	   <option value="11"<?php echo iif($config['confirm_registration'] == '11', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_activation_immediately'); ?></option>
	   <option value="10"<?php echo iif($config['confirm_registration'] == '10', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_activation_email'); ?></option>
	   <option value="01"<?php echo iif($config['confirm_registration'] == '01', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_activation_admin'); ?></option>
	   <option value="00"<?php echo iif($config['confirm_registration'] == '00', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_activation_email_and_admin'); ?></option>
	   </select></td>
	  </tr>
  	  <tr>
   		<td class="mbox"><?php echo $lang->phrase('admin_email_notify_new_member'); ?><br />
   		<span class="stext"><?php echo $lang->phrase('admin_email_notify_new_member_info'); ?></span></td>
   		<td class="mbox"><textarea name="register_notification" rows="2" cols="70"><?php echo $config['register_notification']; ?></textarea></td>
  	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_user_accept_rules'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_user_accept_rules_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="acceptrules" value="1"<?php echo iif($config['acceptrules'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'register2') {
	echo head();

	$register_notification = $gpc->get('register_notification', none);
	$emails = preg_split('/[\r\n]+/', $register_notification, -1, PREG_SPLIT_NO_EMPTY);
	$register_notification = array();
	foreach ($emails as $email) {
		if(check_mail($email, true)) {
			$register_notification[] = $email;
		}
	}
	$register_notification = implode("\n", $register_notification);

	$c->getdata();
	$c->updateconfig('confirm_registration', str);
	$c->updateconfig('register_notification', str, $register_notification);
	$c->updateconfig('disableregistration', int);
	$c->updateconfig('acceptrules', int);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'db') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=db2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_database_edit'); ?></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2"><?php echo $lang->phrase('admin_database_info'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_database_driver'); ?></td>
	   <td class="mbox" width="50%">
	   	<select name="dbsystem">
	   		<option value="mysql"<?php echo iif($config['dbsystem'] == 'mysql', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_database_mysql_standard'); ?></option>
	   		<option value="mysqli"<?php echo iif($config['dbsystem'] == 'mysqli', ' selected="selected"'); ?>><?php echo $lang->phrase('admin_database_masql_improved'); ?></option>
	   	</select>
	   </td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_server_database_resides'); ?></td>
	   <td class="mbox" width="50%"><code><?php echo $config['host']; ?></code></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_database_username'); ?></td>
	   <td class="mbox" width="50%"><code><?php echo $config['dbuser']; ?></code></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_database_name'); ?></td>
	   <td class="mbox" width="50%"><code><?php echo $config['database']; ?></code></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_database_prefix'); ?><br><span class="stext"><?php echo $lang->phrase('admin_database_prefix_info'); ?></span></td>
	   <td class="mbox" width="50%"><code><?php echo $config['dbprefix']; ?></code></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_important_tabels'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_important_tabels_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="optimizetables" value="<?php echo $config['optimizetables']; ?>" size="50"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_persistent_connection'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_persistent_connection_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pconnect" value="1"<?php echo iif($config['pconnect'],' checked'); ?>></td>
	  </tr>
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'db2') {
	echo head();

	$c->getdata();
	$c->updateconfig('pconnect',int);
	$c->updateconfig('dbsystem',str);
	$c->updateconfig('optimizetables',str);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'attupload') {
	$config = $gpc->prepare($config);
	echo head();

	?>
	<form name="form" method="post" action="admin.php?action=settings&job=attupload2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_thread_upload_edit'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_active_tread_uploads'); ?></font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="tpcallow" value="1"<?php echo iif($config['tpcallow'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_allowed_file_format_uploads'); ?><br /><font class="stext"><?php echo $lang->phrase('admin_allowed_file_format_uploads_info'); ?></font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcfiletypes" value="<?php echo $config['tpcfiletypes']; ?>" size="50"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_max_filesize_upload_info'); ?><br /><span class="stext">1 KB = 1024 Bytes</span></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcfilesize" value="<?php echo $config['tpcfilesize']; ?>" size="10"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_max_weidth_pic'); ?><br /><font class="stext"><?php echo $lang->phrase('admin_max_weidth_pic_info'); ?></font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcwidth" value="<?php echo $config['tpcwidth']; ?>" size="5"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_max_hight_pic'); ?><br /><font class="stext"><?php echo $lang->phrase('admin_max_hight_pic_info'); ?></font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcheight" value="<?php echo $config['tpcheight']; ?>" size="5"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_width_pic_pixels'); ?></font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcthumbwidth" value="<?php echo $config['tpcthumbwidth']; ?>" size="5"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_height_pic_pixels'); ?></font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcthumbheight" value="<?php echo $config['tpcthumbheight']; ?>" size="5"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_max_number_uploads'); ?></font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcmaxuploads" value="<?php echo $config['tpcmaxuploads']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_limit_downloadspeed'); ?><br /><font class="stext"><?php echo $lang->phrase('admin_limit_downloadspeed_info'); ?></font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcdownloadspeed" value="<?php echo $config['tpcdownloadspeed']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'attupload2') {
	echo head();

	$c->getdata();

	$list = $gpc->get('tpcfiletypes', none);
	$arraylist = explode(',', $list);
	$arraylist = array_map('strtolower', $arraylist);
	$arraylist = array_map('trim', $arraylist);
	$list = implode(',',$arraylist);

	$c->updateconfig('tpcallow',int);
	$c->updateconfig('tpcdownloadspeed',int);
	$c->updateconfig('tpcmaxuploads',int);
	$c->updateconfig('tpcheight',int);
	$c->updateconfig('tpcwidth',int);
	$c->updateconfig('tpcfilesize',int);
	$c->updateconfig('tpcfiletypes',str,$list);
	$c->updateconfig('tpcthumbwidth',int);
	$c->updateconfig('tpcthumbheight',int);

	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'avupload') {
	$config = $gpc->prepare($config);
	echo head();

	?>
	<form name="form" method="post" action="admin.php?action=settings&job=avupload2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_profil_avatar_edit'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_allowed_file_format_ava'); ?><br /><span class="stext"><?php echo implode(', ', $imagetype_extension); ?><?php echo $lang->phrase('admin_allowed_file_format_ava_info'); ?> </span></td>
	   <td class="mbox" width="50%"><input type="text" name="avfiletypes" value="<?php echo $config['avfiletypes']; ?>" size="50"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_max_file_size_ava'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_max_file_size_ava_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="avfilesize" value="<?php echo $config['avfilesize']; ?>" size="10"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_max_weidth_ava'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_max_weidth_ava_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="avwidth" value="<?php echo $config['avwidth']; ?>" size="5"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_max_height_ava'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_max_height_ava_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="avheight" value="<?php echo $config['avheight']; ?>" size="5"></td>
	  </tr>
	  <tr>
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'avupload2') {
	echo head();

	$c->getdata();

	$list = $gpc->get('avfiletypes', none);
	$arraylist = explode(',', $list);
	$arraylist = array_map('strtolower', $arraylist);
	$arraylist = array_map('trim', $arraylist);
	$arraylist = array_intersect($imagetype_extension, $arraylist);
	$list = implode(',',$arraylist);

	$c->updateconfig('avfiletypes',str,$list);
	$c->updateconfig('avfilesize',int);
	$c->updateconfig('avwidth',int);
	$c->updateconfig('avheight',int);

	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}

elseif ($job == 'cron') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=cron2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_scheduled_settings'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_scheduled_task'); ?><br><span class="stext"><?php echo $lang->phrase('admin_scheduled_task_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pccron" value="1"<?php echo iif($config['pccron'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_max_numbers_of_tasks'); ?><br><font class="stext"><?php echo $lang->phrase('admin_max_numbers_of_tasks_info'); ?></font></td>
	   <td class="mbox" width="50%"><input type="text" name="pccron_maxjobs" value="<?php echo $config['pccron_maxjobs']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_task_log_file'); ?><br><font class="stext"><?php echo $lang->phrase('admin_task_log_file_info'); ?></font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pccron_uselog" value="1"<?php echo iif($config['pccron_uselog'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_send_reports_email'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pccron_sendlog" value="1"<?php echo iif($config['pccron_sendlog'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_email_for_reports'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="pccron_sendlog_email" value="<?php echo $config['pccron_sendlog_email']; ?>" size="50"></td>
	  </tr>
	  <tr>
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'cron2') {
	echo head();

	$c->getdata();
	$c->updateconfig('pccron',int);
	$c->updateconfig('pccron_maxjobs',int);
	$c->updateconfig('pccron_uselog',int);
	$c->updateconfig('pccron_sendlog',int);
	$c->updateconfig('pccron_sendlog_email',str);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'general') {
	echo head();

	// HTTP_HOST is having the correct browser url in most cases...
	$server_name = (!empty($_SERVER['HTTP_HOST'])) ? strtolower($_SERVER['HTTP_HOST']) : ((!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME'));
	$https = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://');

	$source = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
	if (!$source) {
		$source = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
	}
	// Replace backslashes and doubled slashes (could happen on some proxy setups)
	$source = str_replace(array('\\', '//', '/admin'), '/', $source);
	$source = trim(trim(dirname($source)), '/');
	$furl = $https.$server_name.'/'.$source;

	if (!check_hp($furl)) {
		$furl = $lang->phrase('admin_unable_to_analyze_url');
	}

	$fpath = str_replace('\\', '/', realpath('./'));

	?>
	<form name="form" method="post" action="admin.php?action=settings&job=general2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_general_forum_settings'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_page_name'); ?><br><span class="stext"><?php echo $lang->phrase('admin_page_name_info'); ?><font class="stext"><?php echo $furl; ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="fname" value="<?php echo $config['fname']; ?>" size="50"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_short_page_description'); ?><br><span class="stext"><?php echo $lang->phrase('admin_short_page_description_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="fdesc" value="<?php echo $config['fdesc']; ?>" size="50"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_page_url'); ?><br><span class="stext"><?php echo $lang->phrase('admin_page_url_info'); ?> <?php echo $furl; ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="furl" value="<?php echo $gpc->prepare($config['furl']); ?>" size="50"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_path_forum'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_path_forum_info'); ?> <?php echo $fpath; ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="fpath" value="<?php echo $gpc->prepare($config['fpath']); ?>" size="50"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_forum_email'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_forum_email_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="forenmail" value="<?php echo $gpc->prepare($config['forenmail']); ?>" size="50"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_output_benchmark'); ?></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="benchmarkresult" value="1"<?php echo iif($config['benchmarkresult'],' checked="checked"'); ?>></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'general2') {
	echo head();

	$c->getdata();
	$c->updateconfig('fname', html_enc);
	$c->updateconfig('fdesc', html_enc);
	$c->updateconfig('furl', str);
	$c->updateconfig('fpath', str);
	$c->updateconfig('forenmail', str);
	$c->updateconfig('benchmarkresult', int);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'sitestatus') {
	$obox = file_get_contents('data/offline.php');
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=sitestatus2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_switch_viscacha_on_off'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_switch_off'); ?><br><span class="stext"><?php echo $lang->phrase('admin_switch_off_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="foffline" value="1"<?php echo iif($config['foffline'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_offline_msg'); ?><br><span class="stext"><?php echo $lang->phrase('admin_offline_msg_info'); ?></span></td>
	   <td class="mbox" width="50%"><textarea class="texteditor" name="template" rows="5" cols="60"><?php echo $obox; ?></textarea></td>
	  </tr>
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'sitestatus2') {
	echo head();

	$c->getdata();
	$c->updateconfig('foffline',int);
	$filesystem->file_put_contents('data/offline.php',$gpc->get('template', none));
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'ajax_sitestatus') {
	$new = invert($config['foffline']);
	$c->getdata();
	$c->updateconfig('foffline', int, $new);
	$c->savedata();
	die(strval($new));
}
elseif ($job == 'datetime') {
	echo head();
	$config = $gpc->prepare($config);
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=datetime2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_date_time_edit'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_from_timezone'); ?><br><font class="stext"><?php echo $lang->phrase('admin_from_timezone_info'); ?></font></td>
	   <td class="mbox" width="50%"><select name="timezone">
			<option selected value="<?php echo $config['timezone']; ?>"><?php echo $lang->phrase('admin_timezone_maintain'); ?></option>
			<option value="-12"><?php echo $lang->phrase('timezone_n12'); ?></option>
			<option value="-11"><?php echo $lang->phrase('timezone_n11'); ?></option>
			<option value="-10"><?php echo $lang->phrase('timezone_n10'); ?></option>
			<option value="-9"><?php echo $lang->phrase('timezone_n9'); ?></option>
			<option value="-8"><?php echo $lang->phrase('timezone_n8'); ?></option>
			<option value="-7"><?php echo $lang->phrase('timezone_n7'); ?></option>
			<option value="-6"><?php echo $lang->phrase('timezone_n6'); ?></option>
			<option value="-5"><?php echo $lang->phrase('timezone_n5'); ?></option>
			<option value="-4"><?php echo $lang->phrase('timezone_n4'); ?></option>
			<option value="-3.5"><?php echo $lang->phrase('timezone_n35'); ?></option>
			<option value="-3"><?php echo $lang->phrase('timezone_n3'); ?></option>
			<option value="-2"><?php echo $lang->phrase('timezone_n2'); ?></option>
			<option value="-1"><?php echo $lang->phrase('timezone_n1'); ?></option>
			<option value="0"><?php echo $lang->phrase('timezone_0'); ?></option>
			<option value="+1"><?php echo $lang->phrase('timezone_p1'); ?></option>
			<option value="+2"><?php echo $lang->phrase('timezone_p2'); ?></option>
			<option value="+3"><?php echo $lang->phrase('timezone_p3'); ?></option>
			<option value="+3.5"><?php echo $lang->phrase('timezone_p35'); ?></option>
			<option value="+4"><?php echo $lang->phrase('timezone_p4'); ?></option>
			<option value="+4.5"><?php echo $lang->phrase('timezone_p45'); ?></option>
			<option value="+5"><?php echo $lang->phrase('timezone_p5'); ?></option>
			<option value="+5.5"><?php echo $lang->phrase('timezone_p55'); ?></option>
			<option value="+5.75"><?php echo $lang->phrase('timezone_p575'); ?></option>
			<option value="+6"><?php echo $lang->phrase('timezone_p6'); ?></option>
			<option value="+6.5"><?php echo $lang->phrase('timezone_p65'); ?></option>
			<option value="+7"><?php echo $lang->phrase('timezone_p7'); ?></option>
			<option value="+8"><?php echo $lang->phrase('timezone_p8'); ?></option>
			<option value="+9"><?php echo $lang->phrase('timezone_p9'); ?></option>
			<option value="+9.5"><?php echo $lang->phrase('timezone_p95'); ?></option>
			<option value="+10"><?php echo $lang->phrase('timezone_p10'); ?></option>
			<option value="+11"><?php echo $lang->phrase('timezone_p11'); ?></option>
			<option value="+12"><?php echo $lang->phrase('timezone_p12'); ?></option>
		</select></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_use_today_yesterday'); ?></font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="new_dformat4" value="1"<?php echo iif($config['new_dformat4'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'datetime2') {
	echo head();

	$c->getdata();
	$c->updateconfig('new_dformat4',int);
	$c->updateconfig('timezone',str);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'http') {
	$config = $gpc->prepare($config);

	if (!extension_loaded("zlib") || !viscacha_function_exists('gzcompress')) {
		$gzip = '<span style="color: #aa0000;">'.$lang->phrase('admin_not_enabled').'</span>';
	}
	else {
		$gzip = '<span style="color: #00aa00;">'.$lang->phrase('admin_enabled').'</span>';
	}

	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=http2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_headers_cookies_gzip'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_activate_gzip_compression'); ?><br><span class="stext"><?php echo $lang->phrase('admin_activate_gzip_compression_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="gzip" value="1"<?php echo iif($config['gzip'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_gzip_compression_lvl'); ?><br><span class="stext"><?php echo $lang->phrase('admin_gzip_compression_lvl_info'); ?></span></td>
	   <td class="mbox" width="50%"><select size="1" name="gzcompression">
	   <?php
	   	for($i=0;$i<10;$i++) {
	   		if ($i == $config['gzcompression']) {
	   			echo "<option value=\"{$i}\" selected=\"selected\">{$i}</option>";
	   		}
			else {
	   			echo "<option value=\"{$i}\">{$i}</option>";
			}
		}
    	?>
  		</select></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_prevent_browser_caching'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_prevent_browser_caching_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="nocache" value="1"<?php echo iif($config['nocache'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_prefix_cookies'); ?><br><font class="stext"><?php echo $lang->phrase('admin_prefix_cookies_info'); ?></font></td>
	   <td class="mbox" width="50%"><input type="text" size="10" name="cookie_prefix" value="<?php echo $config['cookie_prefix']; ?>"></td>
	  </tr>
	  <tr>
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'http2') {
	echo head();

	$c->getdata();
	$c->updateconfig('gzip',int);
	$c->updateconfig('gzcompression',int);
	$c->updateconfig('nocache',int);
	$c->updateconfig('cookie_prefix',str);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'textprocessing') {
	$config = $gpc->prepare($config);

	if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['PHP_SELF'])) {
		$surl = "http://".$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/images/smileys';
	}
	else {
		$surl = $lang->phrase('admin_unable_to_analyze_url');
	}
	$spath = str_replace('\\', '/', realpath('./')).'/images/smileys';

	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=textprocessing2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_bb_text_progressing'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_censor_texts'); ?><br />
	   <span class="stext"><?php echo $lang->phrase('admin_censor_texts_info'); ?></span></td>
	   <td class="mbox" width="50%">
	   <input type="radio" name="censorstatus" value="0"<?php echo iif($config['censorstatus'] == 0,' checked'); ?>> <?php echo $lang->phrase('admin_censor_no'); ?><br>
	   <input type="radio" name="censorstatus" value="1"<?php echo iif($config['censorstatus'] == 1,' checked'); ?>> <?php echo $lang->phrase('admin_censor_normal'); ?><br>
	   <input type="radio" name="censorstatus" value="2"<?php echo iif($config['censorstatus'] == 2,' checked'); ?>> <?php echo $lang->phrase('admin_censor_extended'); ?>
	   </td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_mark_glossary'); ?><br />
	   <span class="stext"><?php echo $lang->phrase('admin_mark_glossary_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="dictstatus" value="1"<?php echo iif($config['dictstatus'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_replace_vocabulary'); ?><br><span class="stext"><?php echo $lang->phrase('admin_replace_vocabulary_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="wordstatus" value="1"<?php echo iif($config['wordstatus'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_shorten_line_break'); ?><br><span class="stext"><?php echo $lang->phrase('admin_shorten_line_break_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="reduce_nl" value="1"<?php echo iif($config['reduce_nl'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_limit_punctuation'); ?><br><span class="stext"><?php echo $lang->phrase('admin_limit_punctuation_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="reduce_endchars" value="1"<?php echo iif($config['reduce_endchars'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_correct_all_caps'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_correct_all_caps_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="topicuppercase" value="1"<?php echo iif($config['topicuppercase'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_decimal_after_comma_decimalpoint'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="decimals" value="<?php echo $config['decimals']; ?>" size="8"></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table><br />
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_bb_text_wordwrap'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_wordwrap_too_long_words'); ?><br><span class="stext"><?php echo $lang->phrase('admin_wordwrap_too_long_words_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="wordwrap" value="1"<?php echo iif($config['wordwrap'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_wordwrap_number_characters'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="maxwordlength" value="<?php echo $config['maxwordlength']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_wordwrap_character_html_tag_long_words'); ?><br><span class="stext"><?php echo $lang->phrase('admin_wordwrap_character_html_tag_long_words_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="maxwordlengthchar" value="<?php echo $config['maxwordlengthchar']; ?>" size="8"></td>
	  </tr>
  	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_url_wordwrap_long_url'); ?><br><span class="stext"><?php echo $lang->phrase('admin_url_wordwrap_long_url_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="reduce_url" value="1"<?php echo iif($config['reduce_url'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_url_wordwrap_characters_separation'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="maxurllength" value="<?php echo $config['maxurllength']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_url_wordwrap_characters_url'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="maxurltrenner" value="<?php echo $config['maxurltrenner']; ?>" size="8"></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table><br />
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_bb_text_smileys'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_path_smiley_dir'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_path_smiley_dir_info'); ?><tt><?php echo $spath; ?></tt></span></td>
	   <td class="mbox" width="50%"><input type="text" name="smileypath" value="<?php echo $config['smileypath']; ?>" size="60"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_url_smiley_dir'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_url_smiley_dir_info'); ?><tt><?php echo $surl; ?></tt></span></td>
	   <td class="mbox" width="50%"><input type="text" name="smileyurl" value="<?php echo $config['smileyurl']; ?>" size="60"></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'textprocessing2') {
	echo head();

	$c->getdata();
	$c->updateconfig('censorstatus',int);
	$c->updateconfig('decimals',int);
	$c->updateconfig('dictstatus',int);
	$c->updateconfig('wordstatus',int);
	$c->updateconfig('reduce_nl',int);
	$c->updateconfig('reduce_endchars',int);
	$c->updateconfig('wordwrap',int);
	$c->updateconfig('maxwordlength',int);
	$c->updateconfig('maxwordlengthchar',str);
	$c->updateconfig('reduce_url',int);
	$c->updateconfig('maxurllength',int);
	$c->updateconfig('maxurltrenner',str);
	$c->updateconfig('topicuppercase',int);
	$c->updateconfig('smileypath',str);
	$c->updateconfig('smileyurl',str);
	$c->savedata();

	$delobj = $scache->load('smileys');
	$delobj->delete();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'syndication') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=syndication2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_content_syndication'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_activate_newsfeed'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_activate_newsfeed_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="syndication" value="1"<?php echo iif($config['syndication'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_insert_email_new_feeds'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_insert_email_new_feeds_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="syndication_insert_email" value="1"<?php echo iif($config['syndication_insert_email'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_newsfeed_max_characters'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="rsschars" value="<?php echo $config['rsschars']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_newsfeed_time_caching'); ?></td>
	   <td class="mbox" width="50%"><input type="text" name="rssttl" value="<?php echo $config['rssttl']; ?>" size="4"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_newsfeed_icon'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_newsfeed_icon_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="syndication_klipfolio_icon" value="<?php echo $config['syndication_klipfolio_icon']; ?>" size="50"></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_klipfolio_newsfeed_banner'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_klipfolio_newsfeed_banner_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="text" name="syndication_klipfolio_banner" value="<?php echo $config['syndication_klipfolio_banner']; ?>" size="50"></td>
	  </tr>
	  <tr>
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'syndication2') {
	echo head();

	$c->getdata();
	$c->updateconfig('syndication',int);
	$c->updateconfig('syndication_insert_email',int);
	$c->updateconfig('syndication_klipfolio_banner',str);
	$c->updateconfig('syndication_klipfolio_icon',str);
	$c->updateconfig('rssttl',int);
	$c->updateconfig('rsschars',int);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'spiders') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=spiders2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox" colspan="2"><b><?php echo $lang->phrase('admin_crawler_robots'); ?></b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_activate_logging_visits'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_activate_logging_visits_info'); ?></span></td>
	   <td class="mbox" width="50%">
	    <select name="spider_logvisits">
	     <option value="0"<?php echo iif($config['spider_logvisits'] == 0, ' selected="selected"'); ?>><?php echo $lang->phrase('admin_logvisits_no_logging'); ?></option>
	     <option value="1"<?php echo iif($config['spider_logvisits'] == 1, ' selected="selected"'); ?>><?php echo $lang->phrase('admin_logvisits_full_logging'); ?></option>
	     <option value="2"<?php echo iif($config['spider_logvisits'] == 2, ' selected="selected"'); ?>><?php echo $lang->phrase('admin_logvisits_count_logging'); ?></option>
	    </select>
	   </td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%"><?php echo $lang->phrase('admin_activate_logging_missing_ip'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_activate_logging_missing_ip_info'); ?></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="spider_pendinglist" value="1"<?php echo iif($config['spider_pendinglist'],' checked'); ?>></td>
	  </tr>
	  <tr>
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'spiders2') {
	echo head();

	$c->getdata();
	$c->updateconfig('spider_pendinglist',int);
	$c->updateconfig('spider_logvisits',int);
	$c->savedata();

	ok('admin.php?action=settings&job=settings');
}
elseif ($job == 'version') {
	echo head();

	$cache = $scache->load('version_check');
	$data = $cache->get();

	if ($data['comp'] == '3') {
		$res = $lang->phrase('admin_v_not_up2date');
	}
	elseif ($data['comp'] == '1') {
		$res = $lang->phrase('admin_v_dev_version');
	}
	elseif ($data['comp'] == '2') {
		$res = $lang->phrase('admin_v_up2date');
	}
	else {
		$res = $lang->phrase('admin_sync_error');
	}
	if (empty($data['news'])) {
		$data['news'] = $lang->phrase('admin_server_connection_failed');
	}
	if (empty($data['version'])) {
		$data['version'] = $lang->phrase('admin_no_connection');
	}
	?>
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox" colspan="4"><?php echo $lang->phrase('admin_version_check'); ?></td>
	  </tr>
	  <tr>
	   <td class="mmbox" width="25%"><?php echo $lang->phrase('admin_your_version'); ?></td>
	   <td class="mbox" width="25%"><?php echo $config['version']; ?></td>
	   <td class="mmbox" width="25%"><?php echo $lang->phrase('admin_current_version'); ?></td>
	   <td class="mbox" width="25%"><?php echo $data['version']; ?></td>
	  </tr>
	  <tr>
	   <td class="mbox" colspan="4"><?php echo $res; ?></td>
	  </tr>
	 </table><br />
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox"><?php echo $lang->phrase('admin_latest_annoucement'); ?></td>
	  </tr>
	  <tr>
	   <td class="mbox"><?php echo $data['news']; ?></td>
	  </tr>
	 </table>
	<?php
	echo foot();
}
elseif ($job == 'custom') {
	echo head();
	$id = $gpc->get('id', int);
	$package = $gpc->get('package', int);
	$result = $db->query("
	SELECT s.*, g.name AS groupname, p.id as package
	FROM {$db->pre}settings AS s
		LEFT JOIN {$db->pre}settings_groups AS g ON s.sgroup = g.id
		LEFT JOIN {$db->pre}packages AS p ON p.internal = g.name
	WHERE s.sgroup = '{$id}'
	ORDER BY s.name
	");
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=custom2&id=<?php echo $id; ?>&package=<?php echo $package; ?>">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr>
	   <td class="obox" colspan="4">
	    <span class="right"><a class="button" href="admin.php?action=settings&amp;job=new&amp;package=<?php echo $package; ?>"><?php echo $lang->phrase('admin_setting_new_setting'); ?></a></span>
	    <?php echo $lang->phrase('admin_custom_settings'); ?>
	   </td>
	  </tr>
	<?php
	if ($db->num_rows($result) > 0) {
		?>
		  <tr>
		   <td class="ubox"><?php echo $lang->phrase('admin_custom_setting'); ?></td>
		   <td class="ubox"><?php echo $lang->phrase('admin_custom_value'); ?></td>
		   <td class="ubox"><?php echo $lang->phrase('admin_custom_delete'); ?></td>
		   <td class="ubox"><?php echo $lang->phrase('admin_custom_variable'); ?></td>
		  </tr>
		<?php
		while ($row = $db->fetch_assoc($result)) {
			call_user_func('custom_'.$row['type'], $row);
		}
		?>
	  <tr>
	   <td class="ubox" colspan="4" align="center"><input type="submit" name="Submit" value="<?php echo $lang->phrase('admin_form_submit'); ?>"></td>
	  </tr>
		<?php
	}
	else {
	?>
	  <tr>
	   <td class="mbox" colspan="4" align="center"><?php echo $lang->phrase('admin_custom_settings_info'); ?> <a href="admin.php?action=settings&amp;job=new&amp;package=<?php echo $package; ?>"><?php echo $lang->phrase('admin_custom_settings_info2'); ?></a></td>
	  </tr>
	<?php
	}
	?>
	 </table>
	</form>
	<?php
	echo foot();
}
elseif ($job == 'custom2') {
	echo head();
	$id = $gpc->get('id', int);
	$package = $gpc->get('package', int);
	$c->getdata();

	$result = $db->query("
	SELECT s.*, g.name AS groupname
	FROM {$db->pre}settings AS s
		LEFT JOIN {$db->pre}settings_groups AS g ON s.sgroup = g.id
	WHERE s.sgroup = '{$id}'
	ORDER BY s.name
	");
	while ($row = $db->fetch_assoc($result)) {
		$c->updateconfig(array($row['groupname'], $row['name']), none);
	}

	$c->savedata();
	ok('admin.php?action=settings&job=custom&id='.$id.'&package='.$package);
}
elseif ($job == 'delete') {
	echo head();
	$name = $gpc->get('name', str);
	$id = $gpc->get('id', int);
	$package = $gpc->get('package', int);
	$db->query("DELETE FROM {$db->pre}settings WHERE name = '{$name}' AND sgroup = '{$id}' LIMIT 1");
	$upd = $db->affected_rows();
	if ($upd == 1) {
		$result = $db->query("SELECT name FROM {$db->pre}settings_groups WHERE id = '{$id}'");
		$row = $db->fetch_assoc($result);
		$c->getdata();
		$c->delete(array($row['name'], $name));
		$c->savedata();
		if ($package > 0) {
			$ini = $myini->read("modules/{$package}/package.ini");
			unset($ini['setting_'.$name]);
			$myini->write("modules/{$package}/package.ini", $ini);
		}
		ok('admin.php?action=settings&job=custom&id='.$id.'&package='.$package, $lang->phrase('admin_setting_deleted'));
	}
	else {
		error('admin.php?action=settings&job=custom&id='.$id.'&package='.$package, $lang->phrase('admin_setting_not_available'));
	}
}
elseif ($job == 'delete_group') {
	echo head();
	$id = $gpc->get('id', int);
	$package = $gpc->get('package', int);
	$result = $db->query("
	SELECT s.name, g.name AS groupname
	FROM {$db->pre}settings AS s
		LEFT JOIN {$db->pre}settings_groups AS g ON s.sgroup = g.id
	WHERE s.sgroup = '{$id}'");
	if ($package > 0) {
		$ini = $myini->read("modules/{$package}/package.ini");
	}
	while ($row = $db->fetch_assoc($result)) {
		$c->getdata();
		$c->delete(array($row['groupname'], $row['name']));
		if ($package > 0) {
			unset($ini['setting_'.$row['name']]);
		}
		$c->savedata();
	}
	if ($package > 0) {
		unset($ini['config']);
		$myini->write("modules/{$package}/package.ini", $ini);
	}
	$db->query("DELETE FROM {$db->pre}settings WHERE sgroup = '{$id}'");
	$db->query("DELETE FROM {$db->pre}settings_groups WHERE id = '{$id}' LIMIT 1");

	if ($package > 0) {
		ok('admin.php?action=packages&job=package_edit&id='.$package, $lang->phrase('admin_all_settings_deleted'));
	}
	else {
		ok('admin.php?action=settings', $lang->phrase('admin_setting_group_deleted'));
	}
}
elseif ($job == 'new_group') {
	$package = $gpc->get('package', int);
	echo head();
	if ($package > 0) {
		$ini = $myini->read("modules/{$package}/package.ini");
		$result = $db->query("SELECT id FROM {$db->pre}settings_groups WHERE name = '{$ini['info']['internal']}' LIMIT 1");
		if ($db->num_rows($result) > 0) {
			error('admin.php?action=packages&job=package_edit&id='.$package, $lang->phrase('admin_package_has_already_a_group'));
		}
	}
	?>
<form action="admin.php?action=settings&amp;job=new_group2&amp;package=<?php echo $package; ?>" method="post">
<table border="0" align="center" class="border">
<tr>
<td class="obox" colspan="2"><?php echo $lang->phrase('admin_add_settings_group'); ?></td>
</tr>
<tr>
<td class="mbox" width="40%"><?php echo $lang->phrase('admin_setting_group_title'); ?></td>
<td class="mbox" width="60%"><input type="text" name="title" value="" size="40"></td>
</tr>
<tr>
<td class="mbox" width="40%"><?php echo $lang->phrase('admin_group_name'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_group_name_info'); ?> <?php if ($package == 0) { ?> <?php echo $lang->phrase('admin_group_name_info_ws'); ?><?php } ?></span></td>
<td class="mbox" width="60%">
<?php if ($package > 0) { ?>
<code><?php echo $ini['info']['internal']; ?></code>
<input type="hidden" name="name" value="<?php echo $ini['info']['internal']; ?>">
<?php } else { ?>
<input type="text" name="name" value="" size="40">
<?php } ?>
</td>
</tr>
<tr>
<td class="mbox" width="40%"><?php echo $lang->phrase('admin_group_settings_description'); ?></td>
<td class="mbox" width="60%"><textarea name="description" rows="4" cols="50"></textarea></td>
</tr>
<tr><td class="ubox" colspan="2" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_form_add_group'); ?>"></td></tr>
</table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'new_group2') {
	echo head();
	$title = $gpc->get('title', str);
	$name = $gpc->get('name', str);
	$desc = $gpc->get('description', str);
	$package = $gpc->get('package', int);

	if (strlen($title) < 3 || strlen($title) > 120) {
		error('admin.php?action=settings&job=custom', $lang->phrase('admin_title_short_long'));
	}
	if (strlen($name) < 3 || strlen($name) > 120) {
		error('admin.php?action=settings&job=custom', $lang->phrase('admin_group_name_short_long'));
	}

	$result = $db->query("SELECT id FROM {$db->pre}packages WHERE internal = '{$name}'");
	$key = $db->fetch_assoc($result);
	if ($package == 0 && $key['id'] > 0) {
		$package = $key['id'];
	}
	if ($package > 0) {
		$ini = $myini->read("modules/{$package}/package.ini");
		$ini['config'] = array(
			'title' => $title,
			'description' => $desc
		);
		$myini->write("modules/{$package}/package.ini", $ini);
	}

	$db->query("INSERT INTO {$db->pre}settings_groups (title, name, description) VALUES ('{$title}', '{$name}', '{$desc}')");

	ok('admin.php?action=settings&job=custom&package='.$package, $lang->phrase('admin_group_inserted'));
}
elseif ($job == 'new') {
	echo head();
	$package = $gpc->get('package', int);
	if ($package > 0) {
		$result = $db->query("
			SELECT g.id, g.title
			FROM {$db->pre}settings_groups AS g
				LEFT JOIN {$db->pre}packages AS p ON p.internal = g.name
			WHERE p.id = '{$package}'
		");
	}
	else {
		$result = $db->query("SELECT id, title FROM {$db->pre}settings_groups ORDER BY title");
	}
	?>
<form action="admin.php?action=settings&amp;job=new2&amp;package=<?php echo $package; ?>" method="post">
<table border="0" align="center" class="border">
<tr>
<td class="obox" colspan="2"><?php echo $lang->phrase('admin_add_settings'); ?></td>
</tr>
<tr>
<td class="mbox" width="40%"><?php echo $lang->phrase('admin_setting_title'); ?></td>
<td class="mbox" width="60%"><input type="text" name="title" value="" size="40"></td>
</tr>
<tr>
<td class="mbox" width="40%"><?php echo $lang->phrase('admin_setting_description'); ?></td>
<td class="mbox" width="60%"><textarea name="description" rows="4" cols="50"></textarea></td>
</tr>
<tr>
<td class="mbox" width="40%"><?php echo $lang->phrase('admin_setting_group'); ?></td>
<td class="mbox" width="60%"><select name="group">
<?php while ($row = $db->fetch_assoc($result)) { ?>
<option value="<?php echo $row['id']; ?>"><?php echo $row['title']; ?></option>
<?php } ?>
</select></td>
</tr>
<tr>
<td class="mbox" width="40%"><?php echo $lang->phrase('admin_setting_name'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_setting_name_info'); ?></span></td>
<td class="mbox" width="60%"><input type="text" name="name" value="" size="40"></td>
</tr>
<tr>
<td class="mbox" width="40%"><?php echo $lang->phrase('admin_setting_type'); ?></td>
<td class="mbox" width="60%">
<select name="type">
<option value="select"><?php echo $lang->phrase('admin_type_select'); ?></option>
<option value="checkbox"><?php echo $lang->phrase('admin_type_checkbox'); ?></option>
<option value="text"><?php echo $lang->phrase('admin_type_text'); ?></option>
<option selected="selected" value="textarea"><?php echo $lang->phrase('admin_type_textarea'); ?></option>
</select>
</td>
</tr>
<tr>
<td class="mbox" width="40%"><?php echo $lang->phrase('admin_setting_type_values'); ?><br />
<span class="stext"><?php echo $lang->phrase('admin_setting_type_values_info'); ?></span></td>
<td class="mbox" width="60%"><textarea name="typevalue" rows="6" cols="50"></textarea></td>
</tr>
<tr>
<td class="mbox" width="40%"><?php echo $lang->phrase('admin_standard_value'); ?><br /><span class="stext"><?php echo $lang->phrase('admin_standard_value_info'); ?></span></td>
<td class="mbox" width="60%"><input type="text" name="value" value="" size="40"></td>
</tr>
<tr><td class="ubox" colspan="2" align="center"><input type="submit" value="<?php echo $lang->phrase('admin_form_add_setting'); ?>"></td></tr>
</table>
</form>
	<?php
	echo foot();
}
elseif ($job == 'new2') {
	echo head();
	$title = $gpc->get('title', str);
	$desc = $gpc->get('description', str);
	$name = $gpc->get('name', str);
	$type = $gpc->get('type', str);
	$typevalue = $gpc->get('typevalue', none);
	$value = $gpc->get('value', none);
	$group = $gpc->get('group', int);
	$package = $gpc->get('package', int);

	$result = $db->query("SELECT name FROM {$db->pre}settings_groups WHERE id = '{$group}'");
	$row = $db->fetch_assoc($result);

	if (isset($config[$row['name']][$name]) || strlen($name) < 3 || strlen($name) > 120) {
		error('admin.php?action=settings&job=custom', $lang->phrase('admin_name_exists'));
	}
	if ($type != 'checkbox' && $type != 'text' && $type != 'textarea' && $type != 'select') {
		error('admin.php?action=settings&job=custom', $lang->phrase('admin_invalid_type'));
	}
	if ($type == 'select') {
		$typevalue = str_replace("\r\n", "\n", trim($typevalue));
		$typevalue = str_replace("\r", "\n", $typevalue);
		$arr_value = prepare_custom($typevalue);
		if (!isset($arr_value[$value])) {
			error('admin.php?action=settings&job=new', $lang->phrase('admin_value_not_in_setting_type_values'));
		}
	}
	else {
		$typevalue = '';
	}

	$db->query("
	INSERT INTO {$db->pre}settings (name, title, description, type, optionscode, value, sgroup)
	VALUES ('{$name}', '{$title}', '{$desc}', '{$type}', '".$gpc->save_str($typevalue)."', '".$gpc->save_str($value)."', '{$group}')
	");

	$c->getdata();
	$c->updateconfig(array($row['name'], $name), none, $value);
	$c->savedata();

	$result = $db->query("SELECT id FROM {$db->pre}packages WHERE internal = '{$row['name']}'");
	$key = $db->fetch_assoc($result);
	if ($package == 0 && $key['id'] > 0) {
		$package = $key['id'];
	}
	if ($package > 0) {
		$ini = $myini->read("modules/{$package}/package.ini");
		$ini['setting_'.$name] = array(
			'title' => $title,
			'description' => $desc,
			'type' => $type,
			'optionscode' => $typevalue,
			'value' => $value
		);
		$myini->write("modules/{$package}/package.ini", $ini);
	}

	ok('admin.php?action=settings&job=custom&id='.$group, $lang->phrase('admin_setting_inserted'));
}
else {
	echo head();
	$result = $db->query("SELECT id, title, description, name FROM {$db->pre}settings_groups ORDER BY title");
	?>
	<table class="border">
	  <tr>
		<td colspan="3" class="obox"><?php echo $lang->phrase('admin_setting_viscacha'); ?></td>
	  </tr>
	  <tr class="ubox">
		<td width="27%"><?php echo $lang->phrase('admin_setting_sections'); ?></td>
		<td width="50%"><?php echo $lang->phrase('admin_settings_description'); ?></td>
	    <td width="23%"><?php echo $lang->phrase('admin_settings_management_tools'); ?></td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=sitestatus"><?php echo $lang->phrase('admin_switch_viscacha_on_off'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_switch_viscacha_on_off_info'); ?><b><?php echo iif($config['foffline'] == 1, $lang->phrase('admin_ws_offline'), $lang->phrase('admin_ws_online')); ?></b></td>
		<td nowrap="nowrap"><?php echo $lang->phrase('admin_setting_none'); ?></td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=admin"><?php echo $lang->phrase('admin_admin_control_pandel'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_admin_control_pandel_info'); ?></td>
	    <td nowrap="nowrap"><?php echo $lang->phrase('admin_setting_none'); ?></td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=attupload"><?php echo $lang->phrase('admin_setting_attachments'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_attachments_info'); ?></td>
		<td nowrap="nowrap">
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=filetypes&job=manage"><?php echo $lang->phrase('admin_select_file_type_manager'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=textprocessing"><?php echo $lang->phrase('admin_bb_text_progressing'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_bb_text_progressing_info'); ?></td>
		<td nowrap="nowrap">
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=bbcodes&job=smileys"><?php echo $lang->phrase('admin_select_smiley_manager'); ?></option>
		  	  <option value="admin.php?action=bbcodes&job=word"><?php echo $lang->phrase('admin_select_glossary_manager'); ?></option>
		  	  <option value="admin.php?action=bbcodes&job=censor"><?php echo $lang->phrase('admin_select_vocabulary_manager'); ?></option>
		  	  <option value="admin.php?action=bbcodes&job=codefiles"><?php echo $lang->phrase('admin_select_syntax_manager'); ?></option>
		  	  <option value="admin.php?action=bbcodes&job=custombb"><?php echo $lang->phrase('admin_select_bb_code_manager'); ?></option>
		  	  <option value="admin.php?action=bbcodes&job=custombb_test"><?php echo $lang->phrase('admin_select_test_bb_manager'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=cmsp"><?php echo $lang->phrase('admin_setting_cms_portal'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_cms_portal_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=cms&job=nav"><?php echo $lang->phrase('admin_select_navigation_manager'); ?></option>
		  	  <option value="admin.php?action=packages&job=packages"><?php echo $lang->phrase('admin_package_manager'); ?></option>
		  	  <option value="admin.php?action=cms&job=doc"><?php echo $lang->phrase('admin_select_docoments_pages'); ?></option>
		  	  <option value="admin.php?action=explorer"><?php echo $lang->phrase('admin_select_filemanager'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=spiders"><?php echo $lang->phrase('admin_setting_crawler_robots'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_crawler_robots_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=spider&amp;job=manage"><?php echo $lang->phrase('admin_select_crawler_robot_manager'); ?></option>
		  	  <option value="admin.php?action=spider&amp;job=pending"><?php echo $lang->phrase('admin_select_pending_manager'); ?></option>
		  	  <option value="admin.php?action=spider&amp;job=add"><?php echo $lang->phrase('admin_select_add_robot'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=datetime"><?php echo $lang->phrase('admin_setting_date_time'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_date_time_info'); ?></td>
		<td><?php echo $lang->phrase('admin_setting_none'); ?></td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=db"><?php echo $lang->phrase('admin_setting_database'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_database_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=db&amp;job=backup"><?php echo $lang->phrase('admin_select_backup'); ?></option>
		  	  <option value="admin.php?action=db&amp;job=restore"><?php echo $lang->phrase('admin_select_restore'); ?></option>
		  	  <option value="admin.php?action=db&amp;job=optimize"><?php echo $lang->phrase('admin_select_optimize_repair'); ?></option>
		  	  <option value="admin.php?action=db&amp;job=execute"><?php echo $lang->phrase('admin_select_execute_slq'); ?></option>
		  	  <option value="admin.php?action=db&amp;job=status"><?php echo $lang->phrase('admin_select_status_database'); ?></option>
		  	  <option value="admin.php?action=slog&amp;job=l_mysqlerror"><?php echo $lang->phrase('admin_select_sys_error_log'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=email"><?php echo $lang->phrase('admin_setting_email_option'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_email_option_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=misc&amp;job=sessionmails"><?php echo $lang->phrase('admin_select_trash_email'); ?></option>
		  	  <option value="admin.php?action=members&amp;job=emailsearch"><?php echo $lang->phrase('admin_select_newsletter_manager'); ?></option>
		  	  <option value="admin.php?action=language&amp;job=lang_emails&amp;id=<?php echo $config['langdir']; ?>"><?php echo $lang->phrase('admin_select_email_texts'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=boardcat"><?php echo $lang->phrase('admin_setting_forums_categiries'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_forums_categiries_info'); ?></td>
		<td>
		<form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=forums&amp;job=manage"><?php echo $lang->phrase('admin_select_forum_categorie_manager'); ?></option>
		  	  <option value="admin.php?action=forums&amp;job=mods"><?php echo $lang->phrase('admin_select_moderator_manager'); ?></option>
		  	  <option value="admin.php?action=forums&amp;job=cat_add"><?php echo $lang->phrase('admin_select_add_category'); ?></option>
		  	  <option value="admin.php?action=forums&amp;job=forum_add"><?php echo $lang->phrase('admin_select_add_forum'); ?></option>
		  	  <option value="admin.php?action=forums&amp;job=mods_add"><?php echo $lang->phrase('admin_select_add_moderator'); ?></option>
	      </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=ftp"><?php echo $lang->phrase('admin_setting_ftp'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_ftp_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=explorer"><?php echo $lang->phrase('admin_select_filemanager'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=general"><?php echo $lang->phrase('admin_setting_general'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_general_info'); ?></td>
		<td><?php echo $lang->phrase('admin_setting_none'); ?></td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=http"><?php echo $lang->phrase('admin_setting_http_cookie_compression'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_http_cookie_compression_info'); ?></td>
		<td><?php echo $lang->phrase('admin_setting_none'); ?></td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=user"><?php echo $lang->phrase('admin_setting_member_team_list'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_member_team_list_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=members&amp;job=manage"><?php echo $lang->phrase('admin_select_manage_member'); ?></option>
		  	  <option value="admin.php?action=members&amp;job=search"><?php echo $lang->phrase('admin_select_search_member'); ?></option>
		  	  <option value="admin.php?action=members&amp;job=memberrating"><?php echo $lang->phrase('admin_select_memberrating'); ?></option>
		  	  <option value="admin.php?action=members&amp;job=activate"><?php echo $lang->phrase('admin_select_moderate_members'); ?></option>
		  	  <option value="admin.php?action=groups&amp;job=manage"><?php echo $lang->phrase('admin_select_usergroup_manager'); ?></option>
		  	  <option value="admin.php?action=profilefield&amp;job=manage"><?php echo $lang->phrase('admin_select_profile_field_manager'); ?></option>
		  	  <option value="admin.php?action=members&amp;job=ips"><?php echo $lang->phrase('admin_select_search_ip'); ?></option>
		  	  <option value="admin.php?action=members&amp;job=banned"><?php echo $lang->phrase('admin_select_banned_members_ip'); ?></option>
		  	  <option value="admin.php?action=forums&amp;job=mods"><?php echo $lang->phrase('admin_select_moderator_manager'); ?></option>
		  	  <option value="admin.php?action=members&amp;job=emailsearch"><?php echo $lang->phrase('admin_select_newsletter_manager'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=posts"><?php echo $lang->phrase('admin_setting_posts_topics'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_posts_topics_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=posts&job=postrating"><?php echo $lang->phrase('admin_select_postratings'); ?></option>
		  	  <option value="admin.php?action=slog&job=s_general"><?php echo $lang->phrase('admin_select_topic_posts_statistic'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=pm"><?php echo $lang->phrase('admin_setting_pm'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_pm_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
			  <option value="admin.php?action=slog&job=s_general"><?php echo $lang->phrase('admin_select_pm_statistics'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=profile"><?php echo $lang->phrase('admin_setting_profile_edit'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_profile_edit_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=members&amp;job=manage"><?php echo $lang->phrase('admin_select_manage_member'); ?></option>
		  	  <option value="admin.php?action=members&amp;job=search"><?php echo $lang->phrase('admin_select_search_member'); ?></option>
		  	  <option value="admin.php?action=profilefield&amp;job=manage"><?php echo $lang->phrase('admin_select_profile_field_manager'); ?></option>
		  	  <option value="admin.php?action=misc&amp;job=onlinestatus"><?php echo $lang->phrase('admin_select_online_status'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=avupload"><?php echo $lang->phrase('admin_setting_avatar'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_avatar_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=members&amp;job=manage"><?php echo $lang->phrase('admin_select_manage_member'); ?></option>
		  	  <option value="admin.php?action=members&amp;job=search"><?php echo $lang->phrase('admin_select_search_member'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=register"><?php echo $lang->phrase('admin_setting_registration'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_registration_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=misc&amp;job=captcha"><?php echo $lang->phrase('admin_select_captcha_manager'); ?></option>
		  	  <option value="admin.php?action=misc&amp;job=sessionmails"><?php echo $lang->phrase('admin_select_trash_email'); ?></option>
		  	  <option value="admin.php?action=slog&job=s_general"><?php echo $lang->phrase('admin_select_registration_statistic'); ?></option>
		  	  <option value="admin.php?action=language&amp;job=lang_rules&amp;id=<?php echo $config['langdir']; ?>"><?php echo $lang->phrase('admin_select_terms_of_behaviour'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=cron"><?php echo $lang->phrase('admin_setting_scheduled_task'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_scheduled_task_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=cron&amp;job=manage"><?php echo $lang->phrase('admin_select_scheduled_tasks'); ?></option>
		  	  <option value="admin.php?action=cron&amp;job=add"><?php echo $lang->phrase('admin_select_add_task'); ?></option>
		  	  <option value="admin.php?action=slog&amp;job=l_cron"><?php echo $lang->phrase('admin_select_task_log'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=search"><?php echo $lang->phrase('admin_setting_search'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_search_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=language&amp;job=lang_ignore&amp;id=<?php echo $config['langdir']; ?>"><?php echo $lang->phrase('admin_select_ignored_search_words'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=server"><?php echo $lang->phrase('admin_setting_server_php'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_server_php_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=explorer"><?php echo $lang->phrase('admin_select_filemanager'); ?></option>
		  	  <option value="admin.php?action=misc&amp;job=phpinfo"><?php echo $lang->phrase('admin_select_php_info'); ?></option>
		  	  <option value="admin.php?action=misc&amp;job=cache"><?php echo $lang->phrase('admin_select_cache_manager'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=session"><?php echo $lang->phrase('admin_setting_sessionsystem'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_sessionsystem_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=groups&amp;job=manage"><?php echo $lang->phrase('admin_select_usergroup_manager'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=signature"><?php echo $lang->phrase('admin_setting_signature'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_signature_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=members&amp;job=manage"><?php echo $lang->phrase('admin_select_manage_member'); ?></option>
		  	  <option value="admin.php?action=members&amp;job=search"><?php echo $lang->phrase('admin_select_search_member'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=captcha"><?php echo $lang->phrase('admin_setting_spam_bot'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_spam_bot_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=misc&amp;job=captcha_noises"><?php echo $lang->phrase('admin_select_captcha_bg'); ?></option>
		  	  <option value="admin.php?action=misc&amp;job=captcha_fonts"><?php echo $lang->phrase('admin_select_captcha_fonts'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	  <tr class="mbox">
		<td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=syndication"><?php echo $lang->phrase('admin_setting_syndication'); ?></a></td>
		<td class="stext"><?php echo $lang->phrase('admin_setting_syndication_info'); ?></td>
		<td>
		  <form name="act" action="admin.php?action=locate" method="post">
		    <select style="width: 80%" size="1" name="url" onchange="locate(this.value)">
		      <option value="" style="font-weight: bold;"><?php echo $lang->phrase('admin_select_tools'); ?></option>
		  	  <option value="admin.php?action=cms&amp;job=feed"><?php echo $lang->phrase('admin_select_import_feeds'); ?></option>
		  	  <option value="admin.php?action=misc&amp;job=feedcreator"><?php echo $lang->phrase('admin_select_export_feeds'); ?></option>
	        </select> <input style="width: 18%" type="submit" value="<?php echo $lang->phrase('admin_form_go'); ?>">
		  </form>
		</td>
	  </tr>
	</table>
<?php
if ($db->num_rows($result) > 0) {
	$result2 = $db->query("SELECT id, title, internal FROM {$db->pre}packages");
	$cache = array();
	while ($row = $db->fetch_assoc($result2)) {
		$cache[$row['internal']] = $row;
	}
	?>
	<br class="minibr" />
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	 <tr>
	  <td class="obox" colspan="3">
	  <span class="right">
		<a class="button" href="admin.php?action=settings&amp;job=new"><?php echo $lang->phrase('admin_setting_new_setting'); ?></a>
		<a class="button" href="admin.php?action=settings&amp;job=new_group"><?php echo $lang->phrase('admin_setting_add_setting_group'); ?></a>
	  </span>
	  <?php echo $lang->phrase('admin_setting_custom_settings'); ?>
	  </td>
	 </tr>
	 <tr class="ubox">
	  <td nowrap="nowrap" width="27%"><?php echo $lang->phrase('admin_setting_sections'); ?></td>
	  <td width="50%"><?php echo $lang->phrase('admin_setting_description'); ?></td>
	  <td nowrap="nowrap" width="23%"><?php echo $lang->phrase('admin_setting_option'); ?></td>
	 </tr>
	 <?php while ($row = $db->fetch_assoc($result)) { ?>
	 <tr class="mbox">
	  <td nowrap="nowrap"><a href="admin.php?action=settings&amp;job=custom&amp;id=<?php echo $row['id']; ?>"><?php echo $row['title']; ?></a></td>
	  <td class="stext"><?php echo $row['description']; ?><?php echo isset($cache[$row['name']]) ? '<br />'.$lang->phrase('admin_package_x').$cache[$row['name']]['title'] : ''; ?></td>
	  <td nowrap="nowrap">
	  	<?php if(isset($cache[$row['name']]) == false) { ?>
	  	<a class="button" href="admin.php?action=settings&amp;job=delete_group&amp;id=<?php echo $row['id']; ?>"><?php echo $lang->phrase('admin_setting_delete_group'); ?></a>
	  	<?php } else { ?>
	  	<a class="button" href="admin.php?action=packages&amp;job=package_info&amp;id=<?php echo $cache[$row['name']]['id']; ?>"><?php echo $lang->phrase('admin_setting_package_details'); ?></a>
	  	<?php } ?>
	  </td>
	 </tr>
	 <?php } ?>
	</table>
	<?php
	}
	echo foot();
}
?>