<?php

if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "frames.php") die('Error: Hacking Attempt');

if ($job == 'menu') {
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>
	<head>
	<title><?php echo $config['fname']; ?>: Administrations Control Panel > Navigation</title>
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
	<meta http-equiv="pragma" content="no-cache">
	<link rel="stylesheet" type="text/css" href="admin/html/menu.css">
	<link rel="copyright" href="http://www.mamo-net.de">
	<script src="admin/html/menu.js" language="Javascript" type="text/javascript"></script>
	</head>
	<body onload="init()">
	<a href="admin.php?action=index" target="Main"><img src="admin/html/images/logo.jpg" alt="Viscacha"></a><br />
	<a href="javascript:All();">Expand All</a> | <a href="javascript:All(1);">Collapse All</a>
	 <div class="border">
	  <h3><img id="img_admin_menu1" name="collapse" src="admin/html/images/plus.gif" alt="" /> Settings</h3>
	  <ul id="part_admin_menu1">
		<li>&raquo; <a href="admin.php?action=settings" target="Main">Change Settings</a></li>
		<li>&raquo; <a href="admin.php?action=settings&job=new" target="Main">Add new Setting</a></li>
		<li>&raquo; <a href="admin.php?action=settings&job=version" target="Main">Version Check</a></li>
      </ul>
     </div>
	 <div class="border">
	  <h3><img id="img_admin_menu11" name="collapse" src="admin/html/images/plus.gif" alt=""> Content Management</h3>
	  <ul id="part_admin_menu11">
	   <li>&raquo; <a href="admin.php?action=cms&job=nav" target="Main">Navigation Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=cms&job=com" target="Main">Component Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=cms&job=doc" target="Main">Documents &amp; Pages</a></li>
	   <li>&raquo; <a href="admin.php?action=cms&job=plugins" target="Main">PlugIn Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=explorer" target="Main">File Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=cms&job=feed" target="Main">Newsfeed Syndication</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu5" name="collapse" src="admin/html/images/plus.gif" alt=""> Forums &amp; Categories</h3>
	  <ul id="part_admin_menu5">
	   <li>&raquo; <a href="admin.php?action=forums&job=manage" target="Main">Forum &amp; Category Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=forums&job=addcat" target="Main">Add new Category</a></li>
	   <li>&raquo; <a href="admin.php?action=forums&job=addforum" target="Main">Add new Forum</a></li>
	   <li>&raquo; <a href="admin.php?action=forums&job=mods" target="Main">Moderator Manager</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu8" name="collapse" src="admin/html/images/plus.gif" alt=""> Members</h3>
	  <ul id="part_admin_menu8">
	   <li>&raquo; <a href="admin.php?action=members&job=manage" target="Main">List of Members</a></li>
	   <li>&raquo; <a href="admin.php?action=members&job=search" target="Main">Search for Members</a></li>
	   <li>&raquo; <a href="admin.php?action=members&job=banned" target="Main">Blocked IP Addresses</a></li>
	   <li>&raquo; <a href="admin.php?action=members&job=newsletter" target="Main">Newsletter Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=members&job=emaillist" target="Main">Export Email Addresses</a></li>
	   <li>&raquo; <a href="admin.php?action=members&job=activate" target="Main">Moderate/Unlock Members</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu6" name="collapse" src="admin/html/images/plus.gif" alt=""> Usergroups</h3>
	  <ul id="part_admin_menu6">
	   <li>&raquo; <a href="admin.php?action=groups&job=manage" target="Main">Usergroup Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=groups&job=add" target="Main">Add new Usergroup</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu9" name="collapse" src="admin/html/images/plus.gif" alt=""> Templates &amp; Styles</h3>
	  <ul id="part_admin_menu9">
	   <li>&raquo; <a href="admin.php?action=designs&job=design" target="Main">Design Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=designs&job=templates" target="Main">Template Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=designs&job=css" target="Main">Stylesheet Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=designs&job=images" target="Main">Image Manager</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu15" name="collapse" src="admin/html/images/plus.gif" alt=""> Languages</h3>
	  <ul id="part_admin_menu15">
	   <li>&raquo; <a href="admin.php?action=language&job=manage" target="Main">Language Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=language&job=phrase" target="Main">Phrase Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=language&job=import" target="Main">Import Language</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu10" name="collapse" src="admin/html/images/plus.gif" alt=""> Text Processing</h3>
	  <ul id="part_admin_menu10">
	   <li>&raquo; <a href="admin.php?action=bbcodes&job=smiley" target="Main">Smilie Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&job=word" target="Main">Glossary Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&job=censor" target="Main">Censorship Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&job=replace" target="Main">Vocabulary Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=bbcodes&job=codefiles" target="Main">Syntax Highlighting Manager</a></li>
   <!--<li>&raquo; <a href="admin.php?action=bbcodes&job=custombb" target="Main">BB Code Manager</a></li> Later -->
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu2" name="collapse" src="admin/html/images/plus.gif" alt=""> Crawler &amp; Robots</h3>
	  <ul id="part_admin_menu2">
	   <li>&raquo; <a href="admin.php?action=spider&job=manage" target="Main">Crawler &amp; Robot Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=spider&job=add" target="Main">Add new Robot</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu12" name="collapse" src="admin/html/images/plus.gif" alt=""> File Types</h3>
	  <ul id="part_admin_menu12">
	   <li>&raquo; <a href="admin.php?action=filetypes&job=manage" target="Main">File Type Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=filetypes&job=add" target="Main">Add new File Type</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu3" name="collapse" src="admin/html/images/plus.gif" alt=""> Scheduled Tasks</h3>
	  <ul id="part_admin_menu3">
	   <li>&raquo; <a href="admin.php?action=cron&job=manage" target="Main">Task Manager</a></li>
	   <li>&raquo; <a href="admin.php?action=cron&job=add" target="Main">Add new Task</a></li>
	  </ul>
	 </div>
	 <div class="border">
	   <h3><img id="img_admin_menu4" name="collapse" src="admin/html/images/plus.gif" alt=""> Database</h3>
	  <ul id="part_admin_menu4">
	    <li>&raquo; <a href="admin.php?action=db&job=backup" target="Main">Backup</a></li>
	    <li>&raquo; <a href="admin.php?action=db&job=restore" target="Main">Restore</a></li>
	    <li>&raquo; <a href="admin.php?action=db&job=optimize" target="Main">Optimize &amp; Repair Tables</a></li>
	    <li>&raquo; <a href="admin.php?action=db&job=query" target="Main">Execute SQL Queries</a></li>
	    <li>&raquo; <a href="admin.php?action=db&job=status" target="Main">Status &amp; Database</a></li>
	   </ul>
	 </div>
	 <div class="border">
	  <h3><img id="img_admin_menu14" name="collapse" src="admin/html/images/plus.gif" alt="" /> Managing Tools</h3>
	  <ul id="part_admin_menu14">
	   <li>&raquo; <a href="admin.php?action=misc&job=cache" target="Main">Cache Manager</a></li>
	   <li>&raquo; <s><a href="admin.php?action=misc&job=captcha" target="Main">Captcha Manager</a></s></li>
	   <li>&raquo; <s><a href="admin.php?action=misc&job=spellcheck" target="Main">Spell Checker</a></s></li>
	   <li>&raquo; <a href="admin.php?action=misc&job=feedcreator" target="Main">Feedcreator</a></li>
	   <li>&raquo; <a href="admin.php?action=misc&job=sessionmails" target="Main">Wegwerf-Emailadressen</a></li>
	   <li>&raquo; <a href="admin.php?action=misc&job=onlinestatus" target="Main">Onlinestatus-Server</a></li>
      </ul>
     </div>
	 <div class="border" border="0" cellspacing="1" cellpadding="2" align="center">
	   <h3><img id="img_admin_menu13" name="collapse" src="admin/html/images/plus.gif" alt=""> Statistics &amp; Logs</h3>
	  <ul id="part_admin_menu13">
	    <li>&raquo; <a href="admin.php?action=slog&job=s_general" target="Main">General Statistics</a></li>
	    <li>&raquo; <a href="admin.php?action=slog&job=l_cron" target="Main">Scheduled Task Log</a></li>
	    <li>&raquo; <a href="admin.php?action=slog&job=l_mysqlerror" target="Main">MySQL Error Log</a></li>
	   </ul>
	 </div>
	 <div class="border" border="0" cellspacing="1" cellpadding="2" align="center">
	   <h3><img id="img_admin_menu7" name="collapse" src="admin/html/images/plus.gif" alt=""> Useful Links</h3>
	  <ul id="part_admin_menu7">
	   <li>&raquo; <a href="index.php<?php echo SID2URL_1; ?>" target="_blank">Go to Forum</a></li>
	   <li>&raquo; <a href="log.php?action=logout<?php echo SID2URL_x; ?>" target="_top">Sign off</a></li>
	   <li>&raquo; <a href="admin.php?action=misc&job=phpinfo" target="Main">PHP Info</a></li>
	   <li>&raquo; <a href="admin.php?action=misc&job=credits" target="Main">Credits &amp; License</a></li>
	   <li>&raquo; <a href="http://www.mamo-net.de" target="_blank">Support</a></li>
	  </ul>
	 </div>
	</body>
	</html>
	<?php
}
else {
	?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN" "http://www.w3.org/TR/REC-html40/frameset.dtd">
	<html>
	<head>
	<title><?php echo $config['fname']; ?>: Viscacha Admin Control Panel</title>
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
	<meta http-equiv="pragma" content="no-cache">
	<link rel="copyright" href="http://www.mamo-net.de">
	</head>
	<frameset framespacing="0" border="0" cols="190,*" frameborder="0">
	  <frame name="Menu" target="Main" src="admin.php?action=frames&job=menu" scrolling="yes" noresize>
	  <frame name="Main" src="admin.php?action=index" scrolling="auto" noresize>
	  <noframes>
	  <body>
	  Your browser does not seem to support frames or frame support has been disabled.<br><br>
	  Go to:<br>
	  <ul>
	  <li>Viscacha Admin Control Panel:
	  	<ul>
	  	<li><a href="admin.php?action=frames&job=menu">Navigation</a></li>
	  	</ul>
	  </li>
	  </ul>
	  <li>Download a &quot;modern&quot; Browser:
	  	<ul>
	  	<li><a href="http://www.mozilla.com">Mozilla Firefox</a></li>
	  	<li><a href="http://www.opera.com">Opera</a></li>
	  	<li><a href="http://www.apple.com/safari">Safari (Only Mac)</a></li>
	  	</ul>
	  </li>
	  </ul>
	  </body>
	  </noframes>
	</frameset>
	</html>
	<?php
}

?>
