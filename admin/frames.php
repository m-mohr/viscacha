<?php

if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }
// PK: MultiLangAdmin
$lang->group("admin/frames");

if ($job == 'menu') {
	$interface = $gpc->get('interface', int);
	if ($interface == 1) {
		$my->settings['admin_interface'] = invert($my->settings['admin_interface']);
	}
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>
	<head>
	<title><?php echo $lang->phrase("admin_navigationtitle");?></title>
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
	<meta http-equiv="pragma" content="no-cache">
	<link rel="stylesheet" type="text/css" href="admin/html/menu.css">
	<link rel="copyright" href="http://www.viscacha.org">
	<script src="templates/global.js" language="Javascript" type="text/javascript"></script>
	<script src="admin/html/admin.js" language="Javascript" type="text/javascript"></script>
	</head>
	<body onload="init()">
	<p class="center"><a href="admin.php?action=index" target="Main"><img src="admin/html/images/logo.png" alt="Viscacha" /></a></p>
	<p class="stext center"><a href="admin.php?action=frames&amp;job=menu&amp;interface=1" target="_self"><?php echo $lang->phrase(iif($my->settings['admin_interface'] == 1, 'admin_switch_simple', 'admin_switch_extended')); ?></a></p>
	<p class="stext center"><a href="javascript:All();"><?php echo $lang->phrase("admin_expand_all"); ?></a> | <a href="javascript:All(1);"><?php echo $lang->phrase("admin_collapse_all"); ?></a></p>
	<?php if ($my->settings['admin_interface'] == 1) { ?>
	 <div class="border">
	  <h3><img id="img_admin_menu1" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_settings");?></h3>
	  <ul id="part_admin_menu1">
		<li>&raquo; <a href="admin.php?action=settings" target="Main"><?php echo $lang->phrase("admin_viscacha_settings");?></a></li>
		<li>&raquo; <a href="admin.php?action=misc&amp;job=phpinfo" target="Main"><?php echo $lang->phrase("admin_php_info");?></a></li>
		<li>&raquo; <a href="admin.php?action=settings&amp;job=version" target="Main"><?php echo $lang->phrase("admin_version_check");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	  <h3><img id="img_admin_menu2" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_content_manager");?></h3>
	  <ul id="part_admin_menu2">
	   <li>&raquo; <a href="admin.php?action=cms&amp;job=doc" target="Main"><?php echo $lang->phrase("admin_documents_pages");?></a></li>
	   <li>&raquo; <a href="admin.php?action=cms&amp;job=nav" target="Main"><?php echo $lang->phrase("admin_navigation_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=explorer" target="Main"><?php echo $lang->phrase("admin_file_manager");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	  <h3><img id="img_admin_menu15" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_packages");?></h3>
	  <ul id="part_admin_menu15">
	   <li>&raquo; <a href="admin.php?action=packages&amp;job=package" target="Main"><?php echo $lang->phrase("admin_package_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=packages&amp;job=plugins" target="Main"><?php echo $lang->phrase("admin_plugin_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=packages&amp;job=browser" target="Main"><?php echo $lang->phrase("admin_package_browser");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu3" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_forum_categories");?></h3>
	  <ul id="part_admin_menu3">
	   <li>&raquo; <a href="admin.php?action=forums&amp;job=manage" target="Main"><?php echo $lang->phrase("admin_forum_category_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=forums&amp;job=mods" target="Main"><?php echo $lang->phrase("admin_moderator_manager");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu4" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_members");?></h3>
	  <ul id="part_admin_menu4">
	   <li>&raquo; <a href="admin.php?action=members&amp;job=manage" target="Main"><?php echo $lang->phrase("admin_members_list");?></a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=search" target="Main"><?php echo $lang->phrase("admin_search_members");?></a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=inactive" target="Main"><?php echo $lang->phrase("admin_inactive_members");?></a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=activate" target="Main"><?php echo $lang->phrase("admin_moderate_members");?></a></li>
	   <li>&raquo; <a href="admin.php?action=groups&amp;job=manage" target="Main"><?php echo $lang->phrase("admin_group_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=profilefield&amp;job=manage" target="Main"><?php echo $lang->phrase("admin_profile_fields");?></a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=emailsearch" target="Main"><?php echo $lang->phrase("admin_newsletter_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=banned" target="Main"><?php echo $lang->phrase("admin_blocked_ip");?></a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=ips" target="Main"><?php echo $lang->phrase("admin_search_ip");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu5" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_topics_posts");?></h3>
	  <ul id="part_admin_menu5">
	   <li>&raquo; <a href="admin.php?action=posts&amp;job=reports" target="Main"><?php echo $lang->phrase("admin_reported_posts");?></a></li>
	   <li>&raquo; <a href="admin.php?action=posts&amp;job=postrating" target="Main"><?php echo $lang->phrase("admin_rate");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	  <h3><img id="img_admin_menu6" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_syndication");?></h3>
	  <ul id="part_admin_menu6">
	   <li>&raquo; <a href="admin.php?action=cms&amp;job=feed" target="Main"><?php echo $lang->phrase("admin_import_feeds");?></a></li>
	   <li>&raquo; <a href="admin.php?action=misc&amp;job=feedcreator" target="Main"><?php echo $lang->phrase("admin_export_feeds");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu7" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_templates");?></h3>
	  <ul id="part_admin_menu7">
	   <li>&raquo; <a href="admin.php?action=designs&amp;job=design" target="Main"><?php echo $lang->phrase("admin_design_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=designs&amp;job=templates" target="Main"><?php echo $lang->phrase("admin_template_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=designs&amp;job=css" target="Main"><?php echo $lang->phrase("admin_css_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=designs&amp;job=images" target="Main"><?php echo $lang->phrase("admin_img_manager");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu8" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_lang");?></h3>
	  <ul id="part_admin_menu8">
	   <li>&raquo; <a href="admin.php?action=language&amp;job=manage" target="Main"><?php echo $lang->phrase("admin_lang_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=language&amp;job=phrase" target="Main"><?php echo $lang->phrase("admin_phrase_manager");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu9" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_txtprocessing");?></h3>
	  <ul id="part_admin_menu9">
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=smileys" target="Main"><?php echo $lang->phrase("admin_smiley_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=word" target="Main"><?php echo $lang->phrase("admin_glossary");?></a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=censor" target="Main"><?php echo $lang->phrase("admin_censorship");?></a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=replace" target="Main"><?php echo $lang->phrase("admin_vocabulary");?></a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=codefiles" target="Main"><?php echo $lang->phrase("admin_syntaxhighlighting");?></a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=custombb" target="Main"><?php echo $lang->phrase("admin_bbcodes");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu10" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_crawler");?></h3>
	  <ul id="part_admin_menu10">
	   <li>&raquo; <a href="admin.php?action=spider&amp;job=manage" target="Main"><?php echo $lang->phrase("admin_crawler_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=spider&amp;job=pending" target="Main"><?php echo $lang->phrase("admin_pending_robots");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu11" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_database");?></h3>
	  <ul id="part_admin_menu11">
		<li>&raquo; <a href="admin.php?action=db&amp;job=backup" target="Main"><?php echo $lang->phrase("admin_backup");?></a></li>
		<li>&raquo; <a href="admin.php?action=db&amp;job=restore" target="Main"><?php echo $lang->phrase("admin_restore");?></a></li>
		<li>&raquo; <a href="admin.php?action=db&amp;job=optimize" target="Main"><?php echo $lang->phrase("admin_optimize_tables");?></a></li>
		<li>&raquo; <a href="admin.php?action=db&amp;job=query" target="Main"><?php echo $lang->phrase("admin_execute_sql_queries");?></a></li>
		<li>&raquo; <a href="admin.php?action=db&amp;job=status" target="Main"><?php echo $lang->phrase("admin_status_database");?></a></li>
	   </ul>
	 </div>
	 <div class="border">
	  <h3><img id="img_admin_menu12" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_managing_tools");?></h3>
	  <ul id="part_admin_menu12">
	   <li>&raquo; <a href="admin.php?action=misc&amp;job=cache" target="Main"><?php echo $lang->phrase("admin_cache_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=misc&amp;job=captcha" target="Main"><?php echo $lang->phrase("admin_captcha_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=filetypes&amp;job=manage" target="Main"><?php echo $lang->phrase("admin_filetype_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=cron&amp;job=manage" target="Main"><?php echo $lang->phrase("admin_scheduler");?></a></li>
	   <li>&raquo; <a href="admin.php?action=misc&amp;job=sessionmails" target="Main"><?php echo $lang->phrase("admin_trashmail");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu13" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_stats_logs");?></h3>
	  <ul id="part_admin_menu13">
		<li>&raquo; <a href="admin.php?action=slog&amp;job=s_general" target="Main"><?php echo $lang->phrase("admin_statistics");?></a></li>
		<li>&raquo; <a href="admin.php?action=slog&amp;job=l_cron" target="Main"><?php echo $lang->phrase("admin_scheduler_log");?></a></li>
		<li>&raquo; <a href="admin.php?action=slog&amp;job=l_mysqlerror" target="Main"><?php echo $lang->phrase("admin_sqlerror_log");?></a></li>
	   </ul>
	 </div>
	 <?php ($code = $plugins->load('admin_navigation_extended')) ? eval($code) : null; ?>
	 <div class="border">
	   <h3><img id="img_admin_menu14" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_useful_links");?></h3>
	  <ul id="part_admin_menu14">
	   <li>&raquo; <a href="index.php<?php echo SID2URL_1; ?>" target="_blank"><?php echo $lang->phrase("admin_goto_forum");?></a></li>
	   <li>&raquo; <a href="admin.php?action=logout<?php echo SID2URL_x; ?>" target="_top"><?php echo $lang->phrase("admin_signoff");?></a></li>
	   <li>&raquo; <a href="admin.php?action=misc&amp;job=credits" target="Main"><?php echo $lang->phrase("admin_credits");?></a></li>
	   <li>&raquo; <a href="http://www.viscacha.org" target="_blank"><?php echo $lang->phrase("admin_supportlink");?></a></li>
	  </ul>
	 </div>
	 <?php } else { ?>
	 <div class="border">
	  <h3><img id="img_admin_menu1_simple" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_settings");?></h3>
	  <ul id="part_admin_menu1_simple">
		<li>&raquo; <a href="admin.php?action=settings" target="Main"><?php echo $lang->phrase("admin_viscacha_settings");?></a></li>
		<li>&raquo; <a href="admin.php?action=designs&amp;job=design&amp;interface=1" target="Main"><?php echo $lang->phrase("admin_design_manager");?></a></li>
		<li>&raquo; <a href="admin.php?action=language&amp;job=manage" target="Main"><?php echo $lang->phrase("admin_lang_manager");?></a></li>
		<li>&raquo; <a href="admin.php?action=settings&amp;job=version" target="Main"><?php echo $lang->phrase("admin_version_check");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	  <h3><img id="img_admin_menu2_simple" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_content_manager");?></h3>
	  <ul id="part_admin_menu2_simple">
	   <li>&raquo; <a href="admin.php?action=cms&amp;job=doc" target="Main"><?php echo $lang->phrase("admin_documents_pages");?></a></li>
	   <li>&raquo; <a href="admin.php?action=cms&amp;job=nav" target="Main"><?php echo $lang->phrase("admin_navigation_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=packages&amp;job=package" target="Main"><?php echo $lang->phrase("admin_package_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=explorer" target="Main"><?php echo $lang->phrase("admin_file_manager");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu3_simple" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_forums_posts");?></h3>
	  <ul id="part_admin_menu3_simple">
	   <li>&raquo; <a href="admin.php?action=forums&amp;job=manage" target="Main"><?php echo $lang->phrase("admin_forum_category_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=forums&amp;job=mods" target="Main"><?php echo $lang->phrase("admin_moderator_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=posts&amp;job=reports" target="Main"><?php echo $lang->phrase("admin_reported_posts");?></a></li>
	   <li>&raquo; <a href="admin.php?action=posts&amp;job=postrating" target="Main"><?php echo $lang->phrase("admin_rate");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu4_simple" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_members");?></h3>
	  <ul id="part_admin_menu4_simple">
	   <li>&raquo; <a href="admin.php?action=members&amp;job=manage" target="Main"><?php echo $lang->phrase("admin_members_list");?></a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=activate" target="Main"><?php echo $lang->phrase("admin_moderate_members");?></a></li>
	   <li>&raquo; <a href="admin.php?action=groups&amp;job=manage" target="Main"><?php echo $lang->phrase("admin_group_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=profilefield&amp;job=manage" target="Main"><?php echo $lang->phrase("admin_profile_fields");?></a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=emailsearch" target="Main"><?php echo $lang->phrase("admin_newsletter_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=banned" target="Main"><?php echo $lang->phrase("admin_blocked_ip");?></a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=ips" target="Main"><?php echo $lang->phrase("admin_search_ip");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu5_simple" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_txtprocessing");?></h3>
	  <ul id="part_admin_menu5_simple">
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=smileys" target="Main"><?php echo $lang->phrase("admin_smiley_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=censor" target="Main"><?php echo $lang->phrase("admin_censorship");?></a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=custombb" target="Main"><?php echo $lang->phrase("admin_bbcodes");?></a></li>
	  </ul>
	 </div>
	 <div class="border">
	  <h3><img id="img_admin_menu6_simple" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_managing_tools");?></h3>
	  <ul id="part_admin_menu6_simple">
	   <li>&raquo; <a href="admin.php?action=db&amp;job=backup" target="Main"><?php echo $lang->phrase("admin_backup_restore");?></a></li>
	   <li>&raquo; <a href="admin.php?action=spider&amp;job=manage" target="Main"><?php echo $lang->phrase("admin_crawler_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=filetypes&amp;job=manage" target="Main"><?php echo $lang->phrase("admin_filetype_manager");?></a></li>
	   <li>&raquo; <a href="admin.php?action=slog&amp;job=s_general" target="Main"><?php echo $lang->phrase("admin_statistics");?></a></li>
	   <li>&raquo; <a href="admin.php?action=misc&amp;job=cache" target="Main"><?php echo $lang->phrase("admin_cache_manager");?></a></li>
	  </ul>
	 </div>
	 <?php ($code = $plugins->load('admin_navigation_simple')) ? eval($code) : null; ?>
	 <div class="border">
	   <h3><img id="img_admin_menu7_simple" src="admin/html/images/plus.gif" alt="collapse" /> <?php echo $lang->phrase("admin_useful_links");?></h3>
	  <ul id="part_admin_menu7_simple">
	   <li>&raquo; <a href="index.php<?php echo SID2URL_1; ?>" target="_blank"><?php echo $lang->phrase("admin_goto_forum");?></a></li>
	   <li>&raquo; <a href="admin.php?action=misc&amp;job=phpinfo" target="Main"><?php echo $lang->phrase("admin_php_info");?></a></li>
	   <li>&raquo; <a href="admin.php?action=logout<?php echo SID2URL_x; ?>" target="_top"><?php echo $lang->phrase("admin_signoff");?></a></li>
	   <li>&raquo; <a href="http://www.viscacha.org" target="_blank"><?php echo $lang->phrase("admin_supportlink");?></a></li>
	  </ul>
	 </div>
	 <?php } ($code = $plugins->load('admin_navigation')) ? eval($code) : null; ?>
	</body>
	</html>
	<?php
}
else {
	$addr = rawurldecode($gpc->get('addr', none));
	$path = parse_url($addr);
	if (!empty($path['path'])) {
		$file = basename($path['path'], '.php');
	}
	else {
		$file = null;
	}
	if ($file != 'admin') {
		$addr = 'admin.php?action=index';
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN" "http://www.w3.org/TR/REC-html40/frameset.dtd">
<html>
 <head>
  <title><?php echo $lang->phrase("admin_navigationtitle");?></title>
  <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
  <meta http-equiv="pragma" content="no-cache" />
  <link rel="copyright" href="http://www.viscacha.org" />
 </head>
 <frameset cols="200,*" frameborder="0" framespacing="0" border="0">
  <frame name="Menu" src="admin.php?action=frames&amp;job=menu" scrolling="auto" noresize="noresize" />
  <frame name="Main" src="<?php echo $addr; ?>" scrolling="auto" noresize="noresize" />
  <noframes>
   <body>
	<p>Your browser does not seem to support frames or frame support has been disabled.</p>
	What do you want to do?
	 <ul>
	  <li><a href="admin.php?action=frames&amp;job=menu">Viscacha Admin Control Panel Navigation</a></li>
	 </ul>
	<br />
	 Download a &quot;modern&quot; Browser:
	 <ul>
	  <li><a href="http://www.mozilla.com">Mozilla Firefox</a></li>
	  <li><a href="http://www.opera.com">Opera</a></li>
	  <li><a href="http://www.apple.com/safari">Safari (Only Mac)</a></li>
	 </ul>
   </body>
  </noframes>
 </frameset>
</html>
<?php
}

?>
