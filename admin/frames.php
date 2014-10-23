<?php

if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "frames.php") die('Error: Hacking Attempt');

if ($job == 'menu') {
	$interface = $gpc->get('interface', int);
	if ($interface == 1) {
		$my->settings['admin_interface'] = invert($my->settings['admin_interface']);
	}
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>
	<head>
	<title><?php echo $config['fname']; ?>: Administration Control Panel > Navigation</title>
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
	<meta http-equiv="pragma" content="no-cache">
	<link rel="stylesheet" type="text/css" href="admin/html/menu.css">
	<link rel="copyright" href="http://www.mamo-net.de">
	<script src="admin/html/menu.js" language="Javascript" type="text/javascript"></script>
	</head>
	<body onload="init()">
	<p class="center"><a href="admin.php?action=index" target="Main"><img src="admin/html/images/logo.png" alt="Viscacha" /></a></p>
	<p class="stext center"><a href="admin.php?action=frames&amp;job=menu&amp;interface=1" target="_self">Switch to <?php echo iif($my->settings['admin_interface'] == 1, 'simple', 'extended'); ?> Interface</a></p>
	<p class="stext center"><a href="javascript:All();">Expand All</a> | <a href="javascript:All(1);">Collapse All</a></p>
	<?php if ($my->settings['admin_interface'] == 1) { ?>
	 <div class="border">
	  <h3><img id="img_admin_menu1" src="admin/html/images/plus.gif" alt="collapse" /> Settings</h3>
	  <ul id="part_admin_menu1">
		<li>&raquo; <a href="admin.php?action=settings" target="Main">Viscacha Settings</a></li>
		<li>&raquo; <a href="admin.php?action=misc&amp;job=phpinfo" target="Main">PHP Info</a></li>
		<li>&raquo; <a href="admin.php?action=settings&amp;job=version" target="Main">Version Check</a></li>
      </ul>
     </div>
	 <div class="border">
	  <h3><img id="img_admin_menu2" src="admin/html/images/plus.gif" alt="collapse" /> Content Management</h3>
	  <ul id="part_admin_menu2">
	   <li>&raquo; <a href="admin.php?action=cms&amp;job=doc" target="Main">Documents &amp; Pages</a></li>
	   <li>&raquo; <a href="admin.php?action=cms&amp;job=nav" target="Main">Navigation Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=cms&amp;job=com" target="Main">Component Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=cms&amp;job=plugins" target="Main">PlugIn Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=explorer" target="Main">File Manager</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu3" src="admin/html/images/plus.gif" alt="collapse" /> Forums &amp; Categories</h3>
	  <ul id="part_admin_menu3">
	   <li>&raquo; <a href="admin.php?action=forums&amp;job=manage" target="Main">Forum &amp; Category Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=forums&amp;job=mods" target="Main">Moderator Manager</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu4" src="admin/html/images/plus.gif" alt="collapse" /> Members</h3>
	  <ul id="part_admin_menu4">
	   <li>&raquo; <a href="admin.php?action=members&amp;job=manage" target="Main">Member List</a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=search" target="Main">Search Members</a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=inactive" target="Main">Inactive Members</a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=activate" target="Main">Moderate Members</a></li>
	   <li>&raquo; <a href="admin.php?action=groups&amp;job=manage" target="Main">Usergroup Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=profilefield&amp;job=manage" target="Main">Profile Field Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=newsletter" target="Main">Newsletter Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=banned" target="Main">Blocked IP Addresses</a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=ips" target="Main">Search IP Addresses</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu5" src="admin/html/images/plus.gif" alt="collapse" /> Topics &amp; Posts</h3>
	  <ul id="part_admin_menu5">
	   <li>&raquo; <a href="admin.php?action=posts&amp;job=postrating" target="Main">Postratings</a></li>
	  </ul>
	 </div>
	 <div class="border">
	  <h3><img id="img_admin_menu6" src="admin/html/images/plus.gif" alt="collapse" /> Syndication (Feeds)</h3>
	  <ul id="part_admin_menu6">
	   <li>&raquo; <a href="admin.php?action=cms&amp;job=feed" target="Main">Import of Feeds</a></li>
	   <li>&raquo; <a href="admin.php?action=misc&amp;job=feedcreator" target="Main">Export/Creation of Feeds</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu7" src="admin/html/images/plus.gif" alt="collapse" /> Templates &amp; Styles</h3>
	  <ul id="part_admin_menu7">
	   <li>&raquo; <a href="admin.php?action=designs&amp;job=design" target="Main">Design Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=designs&amp;job=templates" target="Main">Template Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=designs&amp;job=css" target="Main">Stylesheet Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=designs&amp;job=images" target="Main">Image Manager</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu8" src="admin/html/images/plus.gif" alt="collapse" /> Languages</h3>
	  <ul id="part_admin_menu8">
	   <li>&raquo; <a href="admin.php?action=language&amp;job=manage" target="Main">Language Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=language&amp;job=phrase" target="Main">Phrase Manager</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu9" src="admin/html/images/plus.gif" alt="collapse" /> Text Processing</h3>
	  <ul id="part_admin_menu9">
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=smileys" target="Main">Smiley Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=word" target="Main">Glossary Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=censor" target="Main">Censorship Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=replace" target="Main">Vocabulary Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=codefiles" target="Main">Syntax Highlighting Manager</a></li>
       <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=custombb" target="Main">Custom BB Code Manager</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu10" src="admin/html/images/plus.gif" alt="collapse" /> Crawler &amp; Robots</h3>
	  <ul id="part_admin_menu10">
	   <li>&raquo; <a href="admin.php?action=spider&amp;job=manage" target="Main">Crawler &amp; Robot Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=spider&amp;job=pending" target="Main">Pending Robots</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu11" src="admin/html/images/plus.gif" alt="collapse" /> Database</h3>
	  <ul id="part_admin_menu11">
	    <li>&raquo; <a href="admin.php?action=db&amp;job=backup" target="Main">Backup</a></li>
	    <li>&raquo; <a href="admin.php?action=db&amp;job=restore" target="Main">Restore</a></li>
	    <li>&raquo; <a href="admin.php?action=db&amp;job=optimize" target="Main">Optimize &amp; Repair Tables</a></li>
	    <li>&raquo; <a href="admin.php?action=db&amp;job=query" target="Main">Execute SQL Queries</a></li>
	    <li>&raquo; <a href="admin.php?action=db&amp;job=status" target="Main">Status &amp; Database</a></li>
	   </ul>
	 </div>
	 <div class="border">
	  <h3><img id="img_admin_menu12" src="admin/html/images/plus.gif" alt="collapse" /> Managing Tools</h3>
	  <ul id="part_admin_menu12">
	   <li>&raquo; <a href="admin.php?action=misc&amp;job=cache" target="Main">Cache Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=misc&amp;job=captcha" target="Main">Captcha Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=filetypes&amp;job=manage" target="Main">File Type Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=misc&amp;job=onlinestatus" target="Main">Online Status Indication</a></li>
	   <li>&raquo; <a href="admin.php?action=cron&amp;job=manage" target="Main">Scheduled Tasks Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=misc&amp;job=spellcheck" target="Main">Spell Check Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=misc&amp;job=sessionmails" target="Main">Trash-E-Mail addresses</a></li>
      </ul>
     </div>
	 <div class="border">
	   <h3><img id="img_admin_menu13" src="admin/html/images/plus.gif" alt="collapse" /> Statistics &amp; Logs</h3>
	  <ul id="part_admin_menu13">
	    <li>&raquo; <a href="admin.php?action=slog&amp;job=s_general" target="Main">Statistics</a></li>
	    <li>&raquo; <a href="admin.php?action=slog&amp;job=l_cron" target="Main">Scheduled Task Log</a></li>
	    <li>&raquo; <a href="admin.php?action=slog&amp;job=l_mysqlerror" target="Main">MySQL Error Log</a></li>
	   </ul>
	 </div>
	 <?php ($code = $plugins->load('admin_navigation_extended')) ? eval($code) : null; ?>
	 <div class="border">
	   <h3><img id="img_admin_menu14" src="admin/html/images/plus.gif" alt="collapse" /> Useful Links</h3>
	  <ul id="part_admin_menu14">
	   <li>&raquo; <a href="index.php<?php echo SID2URL_1; ?>" target="_blank">Go to Forum</a></li>
	   <li>&raquo; <a href="admin.php?action=logout<?php echo SID2URL_x; ?>" target="_top">Sign off</a></li>
	   <li>&raquo; <a href="admin.php?action=misc&amp;job=credits" target="Main">Credits &amp; License</a></li>
	   <li>&raquo; <a href="http://www.viscacha.org" target="_blank">Support</a></li>
	  </ul>
	 </div>
	 <?php } else { ?>
	 <div class="border">
	  <h3><img id="img_admin_menu1_simple" src="admin/html/images/plus.gif" alt="collapse" /> Settings</h3>
	  <ul id="part_admin_menu1_simple">
		<li>&raquo; <a href="admin.php?action=settings" target="Main">Viscacha Settings</a></li>
		<li>&raquo; <a href="admin.php?action=designs&amp;job=design&amp;interface=1" target="Main">Design Manager</a></li>
		<li>&raquo; <a href="admin.php?action=language&amp;job=manage" target="Main">Language Manager</a></li>
		<li>&raquo; <a href="admin.php?action=settings&amp;job=version" target="Main">Version Check</a></li>
      </ul>
     </div>
	 <div class="border">
	  <h3><img id="img_admin_menu2_simple" src="admin/html/images/plus.gif" alt="collapse" /> Content Management</h3>
	  <ul id="part_admin_menu2_simple">
	   <li>&raquo; <a href="admin.php?action=cms&amp;job=doc" target="Main">Documents &amp; Pages</a></li>
	   <li>&raquo; <a href="admin.php?action=cms&amp;job=nav" target="Main">Navigation Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=cms&amp;job=com" target="Main">Component Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=cms&amp;job=plugins" target="Main">PlugIn Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=explorer" target="Main">File Manager</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu3_simple" src="admin/html/images/plus.gif" alt="collapse" /> Forums &amp; Posts</h3>
	  <ul id="part_admin_menu3_simple">
	   <li>&raquo; <a href="admin.php?action=forums&amp;job=manage" target="Main">Forum &amp; Category Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=forums&amp;job=mods" target="Main">Moderator Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=posts&amp;job=postrating" target="Main">Postratings</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu4_simple" src="admin/html/images/plus.gif" alt="collapse" /> Members</h3>
	  <ul id="part_admin_menu4_simple">
	   <li>&raquo; <a href="admin.php?action=members&amp;job=manage" target="Main">Member List</a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=activate" target="Main">Moderate Members</a></li>
	   <li>&raquo; <a href="admin.php?action=groups&amp;job=manage" target="Main">Usergroup Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=profilefield&amp;job=manage" target="Main">Profile Field Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=newsletter" target="Main">Newsletter Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=banned" target="Main">Blocked IP Addresses</a></li>
	   <li>&raquo; <a href="admin.php?action=members&amp;job=ips" target="Main">Search IP Addresses</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu5_simple" src="admin/html/images/plus.gif" alt="collapse" /> Text Processing</h3>
	  <ul id="part_admin_menu5_simple">
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=smileys" target="Main">Smiley Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=censor" target="Main">Censorship Manager</a></li>
       <li>&raquo; <a href="admin.php?action=bbcodes&amp;job=custombb" target="Main">Custom BB Code Manager</a></li>
	  </ul>
	 </div>
	 <div class="border">
	  <h3><img id="img_admin_menu6_simple" src="admin/html/images/plus.gif" alt="collapse" /> Managing Tools</h3>
	  <ul id="part_admin_menu6_simple">
	   <li>&raquo; <a href="admin.php?action=db&amp;job=backup" target="Main">Backup &amp; Restore Database</a></li>
	   <li>&raquo; <a href="admin.php?action=spider&amp;job=manage" target="Main">Crawler &amp; Robot Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=filetypes&amp;job=manage" target="Main">File Type Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=slog&amp;job=s_general" target="Main">Statistics</a></li>
      </ul>
     </div>
	 <?php ($code = $plugins->load('admin_navigation_simple')) ? eval($code) : null; ?>
	 <div class="border">
	   <h3><img id="img_admin_menu7_simple" src="admin/html/images/plus.gif" alt="collapse" /> Useful Links</h3>
	  <ul id="part_admin_menu7_simple">
	   <li>&raquo; <a href="index.php<?php echo SID2URL_1; ?>" target="_blank">Go to Forum</a></li>
	   <li>&raquo; <a href="admin.php?action=misc&amp;job=phpinfo" target="Main">PHP Info</a></li>
	   <li>&raquo; <a href="admin.php?action=logout<?php echo SID2URL_x; ?>" target="_top">Sign off</a></li>
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
  <title><?php echo $config['fname']; ?>: Viscacha Admin Control Panel</title>
  <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
  <meta http-equiv="pragma" content="no-cache" />
  <link rel="copyright" href="http://www.mamo-net.de" />
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
