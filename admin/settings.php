<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "settings.php") die('Error: Hacking Attempt');

// Loading Config-Data
include('classes/class.phpconfig.php');
$c = new manageconfig();

if ($job == 'ftp') {
	$config = $gpc->prepare($config);
	
	$path = 'N/A';
	if (isset($_SERVER['DOCUMENT_ROOT'])) {
		$path = str_replace(realpath($_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR, '', realpath('../'));
	}
	
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=ftp2">
	<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	 <tr> 
	  <td class="obox" colspan="2"><b>FTP Settings</b></td>
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">FTP-Server:<br /><span class="stext">You can leave it empty for disabling FTP.</span></td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_server" size="50" value="<?php echo $config['ftp_server']; ?>"></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">FTP-Port:</td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_port" value="21" size="4" value="<?php echo $config['ftp_port']; ?>"></td> 
	 </tr>
	 <tr>
	  <td class="mbox" width="50%">FTP-Startpath:<br /><span class="stext">Path, from which the FTP-Program works. This path should be the relative FTP-path to your Viscacha-Installation. If the directory containing Viscacha is your FTP-account path, you just need an &quot;/&quot; under *nix-systems. Path determined by the script:<code><?php echo $path; ?></code></span></td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_path" value="<?php echo $config['ftp_path']; ?>" size="50"></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">FTP-Username</span></td>
	  <td class="mbox" width="50%"><input type="text" name="ftp_user" value="<?php echo $config['ftp_user']; ?>" size="50"></td> 
	 </tr>
	 <tr> 
	  <td class="mbox" width="50%">FTP-Password:</td>
	  <td class="mbox" width="50%"><input type="password" name="ftp_pw" value="<?php echo $config['ftp_pw']; ?>" size="50"></td> 
	 </tr>
	 </tr>
	  <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	 </tr>
	</table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'ftp2') {
	echo head();

	$c->getdata();
	$c->updateconfig('ftp_server', str);
	$c->updateconfig('ftp_user', str);
	$c->updateconfig('ftp_pw', str);
	$c->updateconfig('ftp_path', str);
	$c->updateconfig('ftp_port', int);
	$c->savedata();

	ok('admin.php?action=settings&job=ftp');
}
elseif ($job == 'posts') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=posts2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Topics &amp; Posts</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Allow guests to post without specifying an e-mail-address:</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="guest_email_optional" value="1"<?php echo iif($config['guest_email_optional'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Number of Posts per Page:</td>
	   <td class="mbox" width="50%"><input type="text" name="topiczahl" value="<?php echo $config['topiczahl']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximum length for &quot;Reason for editing&quot;:</td>
	   <td class="mbox" width="50%"><input type="text" name="maxeditlength" value="<?php echo $config['maxeditlength']; ?>" size="6"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Minimum length for &quot;Reason for editing&quot;:<br /><span class="stext">0 = Makes field optional</span></td>
	   <td class="mbox" width="50%"><input type="text" name="mineditlength" value="<?php echo $config['mineditlength']; ?>" size="6"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Time Limit on Editing of Posts:<br /><span class="stext">Time limit (in minutes) to impose on editing of messages. After this time limit only moderators will be able to edit the message. 0 = Disabled.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="edit_edit_time" value="<?php echo $config['edit_edit_time']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Time Limit on Deleting of Posts:<br /><span class="stext">Time limit (in minutes) to impose on deleting of messages. After this time limit only moderators will be able to delete the message. 0 = Disabled.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="edit_delete_time" value="<?php echo $config['edit_delete_time']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximum number of threads which can be quoted in a new post by multiquote:<br /><span class="stext">If there are more threads in the cache to multiquote, only the first Xs will be included in the textfield.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="maxmultiquote" value="<?php echo $config['maxmultiquote']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Length of Titles:</td>
	   <td class="mbox" width="50%">
	    Minimum <input type="text" name="mintitlelength" value="<?php echo $config['mintitlelength']; ?>" size="4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	    Maximum <input type="text" name="maxtitlelength" value="<?php echo $config['maxtitlelength']; ?>" size="8">
	   </td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Length of Posts:</td>
	   <td class="mbox" width="50%">
	    Minimum <input type="text" name="minpostlength" value="<?php echo $config['minpostlength']; ?>" size="4">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	    Maximum <input type="text" name="maxpostlength" value="<?php echo $config['maxpostlength']; ?>" size="8">
	   </td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Automatic resizing of too big pictures:<br /><span class="stext">Pictures pasted in through [img]-BB-Code and which are too big for the design, can automatically be resized by using Javascript. A click on the picture will show it  in original size.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="resizebigimg" value="1"<?php echo iif($config['resizebigimg'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximum width for included pictures:<br /><span class="stext">Picture width in pixels. Only relevant if "automatic resizing" is selected.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="resizebigimgwidth" value="<?php echo $config['resizebigimgwidth']; ?>" size="6"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Number of Subscriptions/Favorites per Page:</td>
	   <td class="mbox" width="50%"><input type="text" name="abozahl" value="<?php echo $config['abozahl']; ?>" size="4"></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table>
	 <br class="minibr" />
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Topics &amp; Posts &raquo; PDF</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Activate PDF-output for Topics:<br /><span class="stext">Independent from Usergroupsettings.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pdfdownload" value="1"<?php echo iif($config['pdfdownload'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Compress PDF-Output:<br /><span class="stext">If the output is compressed, the file can be downloaded much faster, but it will need more server performance.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pdfcompress" value="1"<?php echo iif($config['pdfcompress'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table>
	 <br class="minibr" />
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Topics &amp; Posts &raquo; Postrating</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Enable and show Postrating:</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="postrating" value="1"<?php echo iif($config['postrating'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Do not shot the rating until a minimum amount of votes are received:</td>
	   <td class="mbox" width="50%"><input type="text" name="postrating_counter" value="<?php echo $config['postrating_counter']; ?>" size="5"></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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
	$c->updateconfig('pdfdownload', int);
	$c->updateconfig('pdfcompress', int);
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
	$c->savedata();

	ok('admin.php?action=settings&job=posts');
}
elseif ($job == 'profile') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=profile2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Profile (edit &amp; view) </b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximum length for usernames:</td>
	   <td class="mbox" width="50%"><input type="text" name="maxnamelength" value="<?php echo $config['maxnamelength']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Minimum length for usernames:</td>
	   <td class="mbox" width="50%"><input type="text" name="minnamelength" value="<?php echo $config['minnamelength']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximum length for passwords:</td>
	   <td class="mbox" width="50%"><input type="text" name="maxpwlength" value="<?php echo $config['maxpwlength']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Minimum length for passwords:</td>
	   <td class="mbox" width="50%"><input type="text" name="minpwlength" value="<?php echo $config['minpwlength']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximum length for &quot;personal sites&quot;:</td>
	   <td class="mbox" width="50%"><input type="text" name="maxaboutlength" value="<?php echo $config['maxaboutlength']; ?>" size="8"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximum memory for notes per user:<br /><span class="stext">in Bytes</span></td>
	   <td class="mbox" width="50%"><input type="text" name="maxnoticelength" value="<?php echo $config['maxnoticelength']; ?>" size="8"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Number of topics shown in the list of own topics:<br /><span class="stext">look for: &quot;<a href="editprofile.php?action=mylast" target="_blank">My last threads</a>&quot;</span></td>
	   <td class="mbox" width="50%"><input type="text" name="mylastzahl" value="<?php echo $config['mylastzahl']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Show online-status in profile:<br /><span class="stext">Will show the users online-status in his profile.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="osi_profile" value="1"<?php echo iif($config['osi_profile'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Allow vCard-Download:<br /><span class="stext">A vCard is an electronic &quot;visiting card&quot; which can be imported into the local adressbook of another user via mouse click.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="vcard_dl" value="1"<?php echo iif($config['vcard_dl'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Allow vCard-Download for guests:<br /><span class="stext">Activate the vCard Download also for not registered users (guests).</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="vcard_dl_guests" value="1"<?php echo iif($config['vcard_dl_guests'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Allow users to change their username:<br /><span class="stext">If this option is activated, the users are able to change their username.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="changename_allowed" value="1"<?php echo iif($config['changename_allowed'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Show number of written threads in profile:<br /><span class="stext">The complete number of contributed threads can be shown in the users profile. This option may slowdown the performance during big forums.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="showpostcounter" value="1"<?php echo iif($config['showpostcounter'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Show Memberrating:<br /><span class="stext">Show the rating based on the postrating.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="memberrating" value="1"<?php echo iif($config['memberrating'] == 1,' checked="checked"'); ?>></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Do not shot the rating until a minimum amount of votes are received:</td>
	   <td class="mbox" width="50%"><input type="text" name="memberrating_counter" value="<?php echo $config['memberrating_counter']; ?>" size="5"></td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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
	$c->updateconfig('vcard_dl_guests', int);
	$c->updateconfig('vcard_dl', int);
	$c->updateconfig('showpostcounter', int);
	$c->updateconfig('maxnamelength', int);
	$c->updateconfig('minnamelength', int);
	$c->updateconfig('minpwlength', int);
	$c->updateconfig('maxpwlength', int);
	$c->updateconfig('maxaboutlength', int);
	$c->updateconfig('maxnoticelength', int);
	$c->updateconfig('memberrating', int);
	$c->updateconfig('memberrating_counter', int);
	$c->savedata();

	ok('admin.php?action=settings&job=profile');
}
elseif ($job == 'signature') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=signature2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Signatures</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximum Signature length:</td>
	   <td class="mbox" width="50%"><input type="text" name="maxsiglength" value="<?php echo $config['maxsiglength']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Allow [img]-BB-Code (Pictures):</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sig_bbimg" value="1"<?php echo iif($config['sig_bbimg'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Allow [code]-BB-Code (Sourcecode):</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sig_bbcode" value="1"<?php echo iif($config['sig_bbcode'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Allow [list]-BB-Code (Lists):</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sig_bblist" value="1"<?php echo iif($config['sig_bblist'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Allow [edit]-BB-Code (Additional edit):</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sig_bbedit" value="1"<?php echo iif($config['sig_bbedit'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Allow [ot]-BB-Code (Off-Topic):</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sig_bbot" value="1"<?php echo iif($config['sig_bbot'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Allow [h]-BB-Code (Headlines):</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="" value="1"<?php echo iif($config['sig_bbh'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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

	ok('admin.php?action=settings&job=signature');
}
elseif ($job == 'search') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=search2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Search</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Minimum length of Searchterm:<br /><span class="stext">This option allows to ignore small words. Words with less characters as here set will be ignored. </span></td>
	   <td class="mbox" width="50%"><input type="text" name="searchminlength" value="<?php echo $config['searchminlength']; ?>" size="3"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximum number of search results:<br /><span class="stext">After reaching the maximum number the search will be stopped in order to relieve the server.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="maxsearchresults" value="<?php echo $config['maxsearchresults']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Activate Floodblocking for Search:<br /><span class="stext">Flooding is a command which when very fast repeated in extreme case can inhibit normal work or bring the server down.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="floodsearch" value="1"<?php echo iif($config['floodsearch'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Number of Search results per Page:</td>
	   <td class="mbox" width="50%"><input type="text" name="searchzahl" value="<?php echo $config['searchzahl']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Number of active Topics per Page:</td>
	   <td class="mbox" width="50%"><input type="text" name="activezahl" value="<?php echo $config['activezahl']; ?>" size="4"></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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

	ok('admin.php?action=settings&job=search');
}
elseif ($job == 'server') {
	$config = $gpc->prepare($config);
	
	$gdv = 'GD not found!';
	if (function_exists('gd_info')) {
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
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=server2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>PHP, Webserver and Filesystem</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">GD Version:<br /><span class="stext">Version of <a href="http://www.boutell.com/gd/" target="_blank">GD</a> installed on your server. You can find the version by searching for 'GD' on your <a href="admin.php?action=misc&job=phpinfo" target="Main">phpinfo()</a> output. Detected GD Version: <?php echo $gdv; ?></span></td>
	   <td class="mbox" width="50%"><select name="gdversion">
	   <option value="1"<?php echo iif($config['gdversion'] == 1, ' selected="selected"'); ?>>1.x</option>
	   <option value="2"<?php echo iif($config['gdversion'] == 2, ' selected="selected"'); ?>>2.x</option>
	   </select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">PHP-Error reporting:<br /><span class="stext">Types of Errors shown by the parser. More information: <a href="http://www.php.net/manual/ref.errorfunc.php#errorfunc.constants" target="_blank">Error Handling: Constants</a> und <a href="http://www.php.net/error-reporting" target="_blank">error_reporting()</a>.</span></td>
	   <td class="mbox" width="50%"><select name="error_reporting">
	   <option value="-1"<?php echo iif($config['error_reporting'] == -1, ' selected="selected"'); ?>>PHP-Standard</option>
	   <option value="1"<?php echo iif($config['error_reporting'] == 1, ' selected="selected"'); ?>>E_ERROR: Fatal Runtime-Error</option>
	   <option value="2"<?php echo iif($config['error_reporting'] == 2, ' selected="selected"'); ?>>E_WARNING: Warnings on script Runtime. </option>
	   <option value="4"<?php echo iif($config['error_reporting'] == 4, ' selected="selected"'); ?>>E_PARSE: Parse-Error. </option>
	   <option value="8"<?php echo iif($config['error_reporting'] == 8, ' selected="selected"'); ?>>E_NOTICE: Information on runtime.</option>
	   <option value="2047"<?php echo iif($config['error_reporting'] == 2047, ' selected="selected"'); ?>>E_ALL: All Errors and Warnings (Exception: E_STRICT).</option>
	   <?php if (version_compare(PHP_VERSION, '5.0.0', '>=')) { ?>
	   <option value="2048"<?php echo iif($config['error_reporting'] == 2048, ' selected="selected"'); ?>>E_STRICT: Information by the runtime-system (PHP5).</option>
	   <?php } ?>
	   </select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Use own Error-Handler:<br /><span class="stext">Activate this option to use custom error handler (see: <a href="http://www.php.net/manual/function.set-error-handler.php" target="_blank">set_error_handler</a>).</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="error_handler" value="1"<?php echo iif($config['error_handler'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Save PHP-Errors to log-file:<br /><span class="stext">Only if &quot;Use own Error-Handler&quot; is activated! This option should be activated only for debugging purposes!</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="error_log" value="1"<?php echo iif($config['error_log'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Test the filesystem for correctly set CHMODS:<br /><span class="stext">Activating this option will check at every call if CHMOD for files and folders are right set. This option should be activated oly if changes were made on the filesystem before, for example after installation or update.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="check_filesystem" value="1"<?php echo iif($config['check_filesystem'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">.htaccess: Redirect all Subdomains to Top-Domain:<br /><span class="stext">http://www.mamo-net.de will be http://mamo-net.de. Though eventually all other subdomains will be redirected!</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="correctsubdomains" value="1"<?php echo iif($config['hterrordocs'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">.htaccess: Use Error-Documents:<br /><span class="stext">On Server-Errors (400, 401, 403, 404, 500) the custom Error-sites will be shown. Example: <a href="misc.php?action=error&id=404" target="_blank">Error 404</a></span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="hterrordocs" value="1"<?php echo iif($config['hterrordocs'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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
	$c->updateconfig('error_reporting', int);
	$c->updateconfig('correctsubdomains', int);
	$c->updateconfig('hterrordocs', int);
	$c->updateconfig('check_filesystem', int);
	$c->savedata();

	$filesystem->unlink('.htaccess');

	ok('admin.php?action=settings&job=server');
}
elseif ($job == 'session') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=session2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Sessionsystem</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Session-ID length:<br /><span class="stext"></span></td>
	   <td class="mbox" width="50%"><select name="sid_length">
	   <option value="32"<?php echo iif($config['sid_length'] == '32', ' selected="selected"'); ?>>32 Characters</option>
	   <option value="64"<?php echo iif($config['sid_length'] == '64', ' selected="selected"'); ?>>64 Characters</option>
	   <option value="96"<?php echo iif($config['sid_length'] == '96', ' selected="selected"'); ?>>96 Characters</option>
	   <option value="128"<?php echo iif($config['sid_length'] == '128', ' selected="selected"'); ?>>128 Characters</option>
	   </select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Time for check on inactive users in Session-table:<br /><span class="stext">in seconds</span></td>
	   <td class="mbox" width="50%"><input type="text" name="sessionrefresh" value="<?php echo $config['sessionrefresh']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Time after users will be set inactive:<br /><span class="stext">in minutes</span></td>
	   <td class="mbox" width="50%"><input type="text" name="sessionsave" value="<?php echo $config['sessionsave']; ?>" size="4"></td> 
	  </tr>
	  <tr>
	   <td class="mbox" width="50%">Activate Floodblocking:<br /><span class="stext">Flooding is a command which when very fast repeated in extreme case can inhibit normal work or bring the server down.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="enableflood" value="1"<?php echo iif($config['enableflood'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="mbox" width="50%">Session IP validation:<br /><span class="stext">Makes a take over more difficult. Determines how much of the users IP is used to validate a session. All compares the complete address (this is not recommended), A.B.C the first x.x.x (recommended), A.B the first x.x, None disables checking.</span></td>
	   <td class="mbox" width="50%">
	   <select name="session_checkip">
	    <option value="4"<?php echo iif($config['session_checkip'] == 4,' selected="selected"'); ?>>All</option>
	    <option value="3"<?php echo iif($config['session_checkip'] == 3,' selected="selected"'); ?>>A.B.C</option>
	    <option value="2"<?php echo iif($config['session_checkip'] == 2,' selected="selected"'); ?>>A.B</option>
	    <option value="0"<?php echo iif($config['session_checkip'] == 0,' selected="selected"'); ?>>None</option>
	   </select>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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
	$c->savedata();

	ok('admin.php?action=settings&job=session');
}
elseif ($job == 'boardcat') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=boardcat2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Forums &amp; Categories</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Number of Topics per Forumpage:<br /><span class="stext">Number of topics that in the topic overview are shown per page.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="forumzahl" value="<?php echo $config['forumzahl']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Show Subforums in Forumoverview:</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="showsubfs" value="1"<?php echo iif($config['showsubfs'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Syncronize Forumstatistics on change:<br /><span class="stext">Not recommended for big Forums! If this option is activated, the number of threads, topics etc. will be equalized with data assets by every change, otherwise it will be adapted manually.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="updateboardstats" value="1"<?php echo iif($config['updateboardstats'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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
	$c->updateconfig('showsubfs', int);
	$c->updateconfig('updateboardstats', int);
	$c->savedata();

	ok('admin.php?action=settings&job=boardcat');
}
elseif ($job == 'user') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=user2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Member- &amp; Teamlist</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Members per Page:<br /><span class="stext">Number of Members in the Memberlist shown per page.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="mlistenzahl" value="<?php echo $config['mlistenzahl']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Teamlist - Show time period of Moderator rights:<br /><span class="stext">Show in the moderator teamlist how long the user has moderator rights.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="team_mod_dateuntil" value="1"<?php echo iif($config['team_mod_dateuntil'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'user2') {
	echo head();

	$c->getdata();
	$c->updateconfig('mlistenzahl', int);
	$c->updateconfig('team_mod_dateuntil', int);
	$c->savedata();

	ok('admin.php?action=settings&job=user');
}
elseif ($job == 'cmsp') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=cmsp2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>CMS &amp; Portal</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Which file should be used as Homepage:</td>
	   <td class="mbox" width="50%"><select name="indexpage">
	   <option value="forum"<?php echo iif($config['indexpage'] == 'forum', ' selected="selected"'); ?>>Forumoverview</option>
	   <option value="portal"<?php echo iif($config['indexpage'] == 'portal', ' selected="selected"'); ?>>Portal</option>
	   </select></td> 
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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
	   <td class="obox" colspan="2"><b>Administration</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Number of private Messages per page:</td>
	   <td class="mbox" width="50%"><input type="text" name="pmzahl" value="<?php echo $config['pmzahl']; ?>" size="4"></td> 
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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
	$c->savedata();

	ok('admin.php?action=settings&job=pm');
}
elseif ($job == 'email') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=email2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>E-Mails</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Mode of dispatch:<br /><span class="stext"></span></td>
	   <td class="mbox" width="50%"><select name="type">
	   <option value="0"<?php echo iif($config['smtp'] != 1 && $config['sendmail'] != 1, ' selected="selected"'); ?>>PHP internal Mail-Function</option>
	   <option value="1"<?php echo iif($config['sendmail'] == 1, ' selected="selected"'); ?>>Sendmail-Dispatch</option>
	   <option value="2"<?php echo iif($config['smtp'] == 1, ' selected="selected"'); ?>>SMTP-Dispatch</option>
	   </select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Sendmail - Host:<br /><span class="stext">Only if Sendmail is activated.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="sendmail_host" value="<?php echo $config['sendmail_host']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">SMTP - Host:<br /><span class="stext">Only if SMTP is activated.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="smtp_host" value="<?php echo $config['smtp_host']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">SMTP - Authentification:<br /><span class="stext">Only if SMTP is activated.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="smtp_auth" value="1"<?php echo iif($config['smtp_auth'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">SMTP - Username:<br /><span class="stext">Only if SMTP Authentification is activated.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="smtp_username" value="<?php echo $config['smtp_username']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">SMTP - Password:<br /><span class="stext">Only if SMTP Authentification is activated.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="smtp_password" value="<?php echo $config['smtp_password']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Block "Trash"-E-Mail Adresses:<br /><span class="stext">The Domains can be edited <a href="admin.php?action=misc&job=sessionmails">here</a>.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="sessionmails" value="1"<?php echo iif($config['sessionmails'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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
	$c->savedata();

	ok('admin.php?action=settings&job=email');
}
elseif ($job == 'lang') {
	$config = $gpc->prepare($config);
	echo head();
	
	// ToDo: Übersetzen
	$charsets = array(
	'ISO-8859-1' => 'Westeuropäisch, Latin-1',
//	'ISO-8859-2' => 'Osteuropäisch, Latin-2',
//	'ISO-8859-3' => 'Südeuropäisch, Latin-3',
//	'ISO-8859-4' => 'Baltisch, Latin-4',
//	'ISO-8859-5' => 'Kyrillisch',
//	'ISO-8859-6' => 'Arabisch',
//	'ISO-8859-7' => 'Griechisch',
//	'ISO-8859-8' => 'Hebräisch',
//	'ISO-8859-9' => 'Türkisch, Latin-5',
//	'ISO-8859-10' => 'Nordisch, Latin-6',
//	'ISO-8859-11' => 'Thai',
//	'ISO-8859-13' => 'Baltisch, Latin-7',
//	'ISO-8859-14' => 'Keltisch, Latin-8',
	'ISO-8859-15' => 'Westeuropäisch, Latin-9',
//	'ISO-8859-16' => 'Südosteuropäisch, Latin-10',
	'UTF-8' => 'ASCII-kompatibles Multi-Byte 8-Bit Unicode.',
	'cp866' => 'DOS-spezifischer Kyrillischer Zeichensatz. (Ab PHP version 4.3.2)',
	'cp1251' => 'Windows-spezifischer Kyrillischer Zeichensatz. (Ab PHP version 4.3.2)',
	'cp1252' => 'Windows spezifischer Zeichensatz für westeuropäische Sprachen.',
	'KOI8-R' => 'Russisch. (Ab PHP version 4.3.2)',
	'BIG5' => 'Traditionelles Chinesisch, hauptsächlich in Taiwan verwendet.',
	'GB2312' => 'Vereinfachtes Chinesisch, nationaler Standard-Zeichensatz.',
	'BIG5-HKSCS' => 'traditionelles Chinesisch mit Hongkong-spezifischen Erweiterungen',
	'Shift_JIS' => 'Japanisch',
	'EUC-JP' => 'Japanisch'
	);
	
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=lang2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Internationalizement &amp; Languages </b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Activate Character sets:<br /><span class="stext">Activate support for asian languages. Should only be activated if problems occur.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="asia" value="1"<?php echo iif($config['asia'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr>
	   <td class="mbox" width="50%">Character Set in which incoming Data will be converted to:<br /><span class="stext">Information: ISO-8895-15 is the same character set as ISO-8895-1, however completed with the Euro-character as well as French and Finnish characters.</span></td>
	   <td class="mbox" width="50%"><select name="asia_charset">
	   <?php foreach ($charsets as $key => $opt) { ?>
	   <option value="<?php echo $key; ?>"<?php echo iif($config['asia_charset'] == $key, ' selected="selected"'); ?>><?php echo $key.': '.$opt; ?></option>
	   <?php } ?>
	   </select>
	   </td>
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'lang2') {
	echo head();

	$c->getdata();
	$c->updateconfig('asia',int);
	$c->updateconfig('asia_charset',str);
	$c->savedata();

	ok('admin.php?action=settings&job=lang');
}
elseif ($job == 'captcha') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=captcha2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Spam-Bot-Protection (CAPTCHA)</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Show Text-Code instead of CAPTCHA-Image:<br /><span class="stext">Examples: see below.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="botgfxtest_text_verification" value="1"<?php echo iif($config['botgfxtest_text_verification'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">CAPTCHA: Use "wave"-filter on Spam-Bot-Protection-Picture:</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="botgfxtest_filter" value="1"<?php echo iif($config['botgfxtest_filter'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">CAPTCHA: Dyeing letters:<br /><span class="stext">If you choose this option, the letters are shown in different colors.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="botgfxtest_colortext" value="1"<?php echo iif($config['botgfxtest_colortext'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">CAPTCHA: File type:</td>
	   <td class="mbox" width="50%">
	   <select name="botgfxtest_format">
	   <option value="jpg"<?php echo iif($config['botgfxtest_format'] != 'png',' selected="selected"'); ?>>JPEG</option>
	   <option value="png"<?php echo iif($config['botgfxtest_format'] == 'png',' selected="selected"'); ?>>PNG</option>
	   </select>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">CAPTCHA: Quality of the picture:<br /><span class="stext">In percent (100 = very good, 0 = impossible to read). Only possible when you use JPEG pictures.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="botgfxtest_quality" value="<?php echo $config['botgfxtest_quality']; ?>" size="5">%</td> 
	  </tr>
	  <tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table>
	<br class="minibr" />
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Spam-Bot-Protection (CAPTCHA) &raquo; Registration</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Activate Spam-Bot-Protection at Registration:</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="botgfxtest" value="1"<?php echo iif($config['botgfxtest'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">CAPTCHA: Standard image width:<br /><span class="stext">In Pixels.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="botgfxtest_width" value="<?php echo $config['botgfxtest_width']; ?>" size="5">px</td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">CAPTCHA: Standard image height:<br /><span class="stext">In Pixels.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="botgfxtest_height" value="<?php echo $config['botgfxtest_height']; ?>" size="5">px</td> 
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table>
	<br class="minibr" />
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Spam-Bot-Protection (CAPTCHA) &raquo; Posting</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Activate Spam-Bot-Protection at Posting of guests</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="botgfxtest_posts" value="1"<?php echo iif($config['botgfxtest_posts'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">CAPTCHA: Standard image width:<br /><span class="stext">In Pixels.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="botgfxtest_posts_width" value="<?php echo $config['botgfxtest_posts_width']; ?>" size="5">px</td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">CAPTCHA: Standard image height:<br /><span class="stext">In Pixels.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="botgfxtest_posts_height" value="<?php echo $config['botgfxtest_posts_height']; ?>" size="5">px</td> 
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table> 
	</form><br />
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="4"><b>Examples for CAPTCHA-Images and Text-Codes</b></td>
	  </tr>
	  <tr> 
	   <td class="ubox" width="50%" colspan="2" align="center">CAPTCHA-Images</td>
	   <td class="ubox" width="50%" colspan="2" align="center">Text-Codes</td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="25%" align="center"><img src="admin/html/images/captcha.jpg" border="0" /></td>
	   <td class="mbox" width="25%" align="center"><img src="admin/html/images/captcha2.jpg" border="0" /></td>
	   <td class="mbox" width="25%"><div class="center" style="padding: 2px; font-size: 7px; line-height:7px; font-family: Courier New, monospace">&nbsp;########&nbsp;&nbsp;&nbsp;######&nbsp;&nbsp;&nbsp;&nbsp;########&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;########&nbsp;&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;<br>&nbsp;########&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;######&nbsp;&nbsp;&nbsp;&nbsp;#####&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;########&nbsp;&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;######&nbsp;&nbsp;&nbsp;&nbsp;########&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;########&nbsp;&nbsp;</div></td>
	   <td class="mbox" width="25%"><div class="center" style="padding: 2px; font-size: 7px; line-height:7px; font-family: Courier New, monospace">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;######&nbsp;&nbsp;########&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;###&nbsp;&nbsp;&nbsp;&nbsp;<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;##&nbsp;&nbsp;&nbsp;<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;#####&nbsp;&nbsp;&nbsp;######&nbsp;&nbsp;&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;#########&nbsp;<br>&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#&nbsp;#&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;<br>&nbsp;&nbsp;######&nbsp;&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;##&nbsp;</div></td>
	  </tr>
	 </table>
	<?php
	echo foot();
}
elseif ($job == 'captcha2') {
	echo head();

	$c->getdata();
	$c->updateconfig('botgfxtest',int);
	$c->updateconfig('botgfxtest_posts', int);
	$c->updateconfig('botgfxtest_filter', int);
	$c->updateconfig('botgfxtest_colortext', int);
	$c->updateconfig('botgfxtest_width', int);
	$c->updateconfig('botgfxtest_height', int);
	$c->updateconfig('botgfxtest_posts_width', int);
	$c->updateconfig('botgfxtest_posts_height', int);
	$c->updateconfig('botgfxtest_format', str);
	$c->updateconfig('botgfxtest_quality', int);
	$c->updateconfig('botgfxtest_text_verification',int);
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
	   <td class="obox" colspan="2"><b>Registration</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">User activation:<br /><span class="stext"></span></td>
	   <td class="mbox" width="50%"><select name="confirm_registration">
	   <option value="11"<?php echo iif($config['confirm_registration'] == '11', ' selected="selected"'); ?>>Users are activated immediately</option>
	   <option value="10"<?php echo iif($config['confirm_registration'] == '10', ' selected="selected"'); ?>>Activation per e-mail</option>
	   <option value="01"<?php echo iif($config['confirm_registration'] == '01', ' selected="selected"'); ?>>Activation through Administrator</option>
	   <option value="00"<?php echo iif($config['confirm_registration'] == '00', ' selected="selected"'); ?>>Activation per e-mail and through Administrator</option>
	   </select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">User has to accept rules on registration:<br /><span class="stext">The behaviour conditions <!-- Ersetzen durch Link zu ACP -->(<a href="misc.php?action=rules" target="_blank">look up</a>) must be read and accepted.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="acceptrules" value="1"<?php echo iif($config['acceptrules'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table> 
	</form>
	<?php
	echo foot();
}
elseif ($job == 'register2') {
	echo head();

	$c->getdata();
	$c->updateconfig('confirm_registration',str);
	$c->updateconfig('acceptrules',int);
	$c->savedata();

	ok('admin.php?action=settings&job=register');
}
elseif ($job == 'spellcheck') {
	$config = $gpc->prepare($config);
	$ext = get_loaded_extensions();
	if (in_array("pspell", $ext)) {
		$ps = "<span style='color: green;'>vorhanden</span>";
	}
	else {
		$ps = "<span style='color: red;'>nicht vorhanden</span>";
	}
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=spellcheck2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Spellcheck</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Enable Spellchecker:<br /><span class="stext">Weitere Einstellungen finden Sie unter "<a href="admin.php?action=misc&job=spellcheck">Spellchecking</a>".</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="spellcheck" value="1"<?php echo iif($config['spellcheck'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Spellcheck-System:<br /><span class="stext">It is recommended to use Pspell, because both MySQL and as well Textfiles may extremely stress the server. Pspell is on your system <?php echo $ps; ?>.</span></td>
	   <td class="mbox" width="50%"><select name="pspell">
	   <option value="pspell"<?php echo iif($config['pspell'] == 'pspell', ' selected="selected"'); ?>>PSpell/Aspell (recommended)</option>
	   <option value="mysql"<?php echo iif($config['pspell'] == 'mysql', ' selected="selected"'); ?>>MySQL/PHP</option>
	   <option value="php"<?php echo iif($config['pspell'] == 'php', ' selected="selected"'); ?>>Textfiles/PHP</option>
	   </select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Ignore Words with less characters then:<br /><span class="stext">This setting allows to jump over short words. Words with less then the here indicated number of characters will be jumped over.</span></td>
	   <td class="mbox" width="50%"><input type="text" name="spellcheck_ignore" value="<?php echo $config['spellcheck_ignore']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Mode of the supplied suggestions:</td>
	   <td class="mbox" width="50%"><select name="spellcheck_mode">
	   <option value="0"<?php echo iif($config['spellcheck_mode'] == 0, ' selected="selected"'); ?>>Fast mode (smallest number of suggestions)</option>
	   <option value="1"<?php echo iif($config['spellcheck_mode'] == 1, ' selected="selected"'); ?>>Normal mode (more suggestions)</option>
	   <option value="2"<?php echo iif($config['spellcheck_mode'] == 2, ' selected="selected"'); ?>>Slow mode (Many suggestions)</option>
	   </select></td> 
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'spellcheck2') {
	echo head();

	$c->getdata();
	$c->updateconfig('spellcheck',int);
	$c->updateconfig('spellcheck_ignore',int);
	$c->updateconfig('spellcheck_mode',int);
	$c->updateconfig('pspell',str);
	$c->savedata();

	ok('admin.php?action=settings&job=spellcheck');
}
elseif ($job == 'jabber') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=jabber2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4">
	  <tr> 
	   <td class="obox" colspan="2"><b>Jabber</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Enable Jabber-Support:<br /><span class="stext">Activates the dispatch of messages over Jabber. The profile field is <em>not</em> concerned.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="enable_jabber" value="1"<?php echo iif($config['enable_jabber'] == 1,' checked="checked"'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Jabber-Server (and Port):<br /><span class="stext">Indicate Jabber-Server without protocol. In normal Jabber addresses this entry is the same as the text after the @. The port can be attached separated with ":". Example for the address username@domain.com and the port 5222: "domain.com:5222":</span></td>
	   <td class="mbox" width="50%"><input type="text" name="jabber_server" value="<?php echo $config['jabber_server']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Jabber-Username:<br /><span class="stext">Jabber-Account of a username for dispatching Jabber-messages. In normal Jabber addresses this entry is the same as the text before the @. Example: username@domain.com = "username".</span></td>
	   <td class="mbox" width="50%"><input type="text" name="jabber_user" value="<?php echo $config['jabber_user']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Jabber-Password:<br /><span class="stext">Password to the Jabber account indicated above.</span></td>
	   <td class="mbox" width="50%"><input type="password" name="jabber_pass" value="<?php echo $config['jabber_pass']; ?>" size="50"></td> 
	  </tr>
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'jabber2') {
	echo head();

	$c->getdata();
	$c->updateconfig('enable_jabber',int);
	$c->updateconfig('jabber_server',str);
	$c->updateconfig('jabber_user',str);
	$c->updateconfig('jabber_pass',str);
	$c->savedata();

	ok('admin.php?action=settings&job=jabber');
}
elseif ($job == 'db') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=db2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>Database</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Database-System:</td>
	   <td class="mbox" width="50%"><select name="dbsystem"><option value="mysql"<?php echo iif($config['dbsystem'] == 'mysql', ' selected="selected"'); ?>>MySQL</option></select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Database-Host:<br><font class="stext">Frequently "localhost"</font></td>
	   <td class="mbox" width="50%"><input type="text" name="host" value="<?php echo $config['host']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Database-User:</td>
	   <td class="mbox" width="50%"><input type="text" name="dbuser" value="<?php echo $config['dbuser']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Database-Password:</td>
	   <td class="mbox" width="50%"><input type="password" name="dbpw" value="<?php echo $config['dbpw']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Database-Name:<br><font class="stext">Database in which the tables for the Forum are saved.</font></td>
	   <td class="mbox" width="50%"><input type="text" name="database" value="<?php echo $config['database']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Tableprefix:<br><font class="stext">Prefix for the tables of this Viscacha installation.<br>Attention: Tables will not automatically be renamed!</font></td>
	   <td class="mbox" width="50%"><input type="text" name="dbprefix" value="<?php echo $config['dbprefix']; ?>" size="10"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Important Tables:<br /><font class="stext">Those Tables will automatically be optimized by cron job! Indicate Tables separated by "," without their prefix.</font></td>
	   <td class="mbox" width="50%"><input type="text" name="optimizetables" value="<?php echo $config['optimizetables']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Persistent Connection:<br /><font class="stext">SQL connections which will not be closed after the End of the Script. If a connection is requested, it will be checked if a connection has already been established.<br>Source: <a href="http://www.php.net/manual/features.persistent-connections.php" target="_blank">php.net - Persistent Databaseconnections</a></font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pconnect" value="1"<?php echo iif($config['pconnect'],' checked'); ?>></td> 
	  </tr>
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'db2') {
	echo head();

	$c->getdata();
	$c->updateconfig('host',str);
	$c->updateconfig('dbuser',str);
	$c->updateconfig('dbpw',str);
	$c->updateconfig('database',str);
	$c->updateconfig('pconnect',int);
	$c->updateconfig('dbprefix',str);
	$c->updateconfig('dbsystem',str);
	$c->updateconfig('optimizetables',str);
	$c->savedata();

	ok('admin.php?action=settings&job=db');
}
elseif ($job == 'attupload') {
	$config = $gpc->prepare($config);
	echo head();
	
	$array = explode('|',$config['tpcfiletypes']);
	$array2 = array();
	foreach ($array as $row) {
		if (strpos($row, '.') == 0) {
			$array2[] = substr($row,1);
		}
		else {
			$array2[] = $row;
		}
	}
	$config['tpcfiletypes'] = implode(',',$array2);
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=attupload2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>Thread Uploads</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Activate Thread Uploads:</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="tpcallow" value="1"<?php echo iif($config['tpcallow'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Allowed File Formats for Upload:<br /><font class="stext">Indicate separated by ",".</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcfiletypes" value="<?php echo $config['tpcfiletypes']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Max. File Size for Upload in Bytes:<br /><font class="stext">1 KB = 1024 Bytes</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcfilesize" value="<?php echo $config['tpcfilesize']; ?>" size="10"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Max. Width for Pictures:<br /><font class="stext">Empty = Any</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcwidth" value="<?php echo $config['tpcwidth']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Max. Height for Pictures:<br /><font class="stext">Empty = Any</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcheight" value="<?php echo $config['tpcheight']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Width for resized Pictures in Pixels:</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcthumbwidth" value="<?php echo $config['tpcthumbwidth']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Height for resized Pictures in Pixels:</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcthumbheight" value="<?php echo $config['tpcthumbheight']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Max. Number of Uploads per Thread:</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcmaxuploads" value="<?php echo $config['tpcmaxuploads']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Limit Downloadspeed:<br /><font class="stext">Here you can throttle the Downloadspeed for Thread Uploads! Indicate the max. Dowloadspeed <b> in KB </b> . 0 = No Limit</font></td>
	   <td class="mbox" width="50%"><input type="text" name="tpcdownloadspeed" value="<?php echo $config['tpcdownloadspeed']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'attupload2') {
	echo head();

	$c->getdata();
	
	// Beitragsupload
	$array = explode(',',$gpc->get('tpcfiletypes'));
	$array2 = array();
	foreach ($array as $row) {
		$array2[] = '.'.$row;
	}
	$ft = implode('|',$array2);

	$c->updateconfig('tpcallow',int);
	$c->updateconfig('tpcdownloadspeed',int);
	$c->updateconfig('tpcmaxuploads',int);
	$c->updateconfig('tpcheight',int);
	$c->updateconfig('tpcwidth',int);
	$c->updateconfig('tpcfilesize',int);
	$c->updateconfig('tpcfiletypes',str, $ft);
	$c->updateconfig('tpcthumbwidth',int);
	$c->updateconfig('tpcthumbheight',int);
	
	$c->savedata();

	ok('admin.php?action=settings&job=attupload');
}
elseif ($job == 'avupload') {
	$config = $gpc->prepare($config);
	echo head();
	
	$array = explode('|',$config['avfiletypes']);
	$array2 = array();
	foreach ($array as $row) {
		if (strpos($row, '.') == 0) {
			$array2[] = substr($row,1);
		}
		else {
			$array2[] = $row;
		}
	}
	$ft = implode(',',$array2);
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=avupload2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>Profile pictures &amp; Avatars</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Allowed file formats for profile pictures:<br /><font class="stext">Indicate separated by ",".</font></td>
	   <td class="mbox" width="50%"><input type="text" name="avfiletypes" value="<?php echo $ft; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Max. file size for profile pictures in Bytes:<br /><font class="stext">1 KB = 1024 Byte</font></td>
	   <td class="mbox" width="50%"><input type="text" name="avfilesize" value="<?php echo $config['avfilesize']; ?>" size="10"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Max. width for profile pictures:<br /><font class="stext">Empty = any</font></td>
	   <td class="mbox" width="50%"><input type="text" name="avwidth" value="<?php echo $config['avwidth']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Max. height for profile pictures:<br /><font class="stext">Empty = any</font></td>
	   <td class="mbox" width="50%"><input type="text" name="avheight" value="<?php echo $config['avheight']; ?>" size="5"></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'avupload2') {
	echo head();

	$c->getdata();
	$array = explode(',',$gpc->get('avfiletypes', none));
	$array2 = array();
	foreach ($array as $row) {
		$array2[] = '.'.$row;
	}
	$ft = implode('|',$array2);
	
	$c->updateconfig('avfiletypes',str, $ft);
	$c->updateconfig('avfilesize',int);
	$c->updateconfig('avwidth',int);
	$c->updateconfig('avheight',int);
	
	$c->savedata();

	ok('admin.php?action=settings&job=avupload');
}

elseif ($job == 'cron') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=cron2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>Scheduled Settings</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Scheduled Tasks in page:<br><span class="stext">If this option is activated, Viscacha will check if there are Tasks to be done at every page call. For a better Performance you can page out this task, by loading the file <a href="cron.php" target="_blank">cron.php</a>with a scheduled cron job.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pccron" value="1"<?php echo iif($config['pccron'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Max. number of tasks wich will be executed per page impression:<br><font class="stext">For bigger boards a small number (1-2) is strongly recommended, for small boards the number can be higher (3-5). 0 = execute all!</font></td>
	   <td class="mbox" width="50%"><input type="text" name="pccron_maxjobs" value="<?php echo $config['pccron_maxjobs']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Use Task-Log-File:<br><font class="stext">The log file can be viewed <a href="admin.php?action=slog&job=l_cron" target="_blank">here</a>.</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pccron_uselog" value="1"<?php echo iif($config['pccron_uselog'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Send reports per e-mail:</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="pccron_sendlog" value="1"<?php echo iif($config['pccron_sendlog'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">E-mail-address for reports:</td>
	   <td class="mbox" width="50%"><input type="text" name="pccron_sendlog_email" value="<?php echo $config['pccron_sendlog_email']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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

	ok('admin.php?action=settings&job=cron');
}
elseif ($job == 'general') {
	echo head();
	
	if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['PHP_SELF'])) {
		$furl = "http://".$_SERVER['HTTP_HOST'].rtrim(viscacha_dirname($_SERVER['PHP_SELF']), '/\\');
	}
	else {
		$furl = "Unable to analyze URL.";
	}
	
	$config = $gpc->prepare($config);
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=general2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>General Forum Settings</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Page name:<br><font class="stext">Is used, among other, in e-mails and should not exceed 64 characters.</font></td>
	   <td class="mbox" width="50%"><input type="text" name="fname" value="<?php echo $config['fname']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Short page description:<br><font class="stext">HTML is allowed</font></td>
	   <td class="mbox" width="50%"><input type="text" name="fdesc" value="<?php echo $config['fdesc']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Page URL:<br><font class="stext">URL without closing "/".<br>URL determined by the script: <?php echo $furl; ?></font></td>
	   <td class="mbox" width="50%"><input type="text" name="furl" value="<?php echo $config['furl']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Path to the Forum:<br /><font class="stext">Path to your Viscacha installation (without closing "/").<br />Path determined by the script: <?php echo str_replace('\\', '/', realpath('./')); ?></font></td>
	   <td class="mbox" width="50%"><input type="text" name="fpath" value="<?php echo $config['fpath']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Forum e-mail-address:<br /><font class="stext">Will be used for every outgoing e-mail.</font></td>
	   <td class="mbox" width="50%"><input type="text" name="forenmail" value="<?php echo $config['forenmail']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Output benchmark results and debug information:</td>
	   <td class="mbox" width="50%"><input type="checkbox" name="benchmarkresult" value="1"<?php echo iif($config['benchmarkresult'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table>
	</form> 
	<?php
	echo foot();
}
elseif ($job == 'general2') {
	echo head();

	$c->getdata();
	$c->updateconfig('fname',str);
	$c->updateconfig('fdesc',str);
	$c->updateconfig('furl',str);
	$c->updateconfig('fpath',str);
	$c->updateconfig('forenmail',str);
	$c->updateconfig('benchmarkresult',int);
	$c->savedata();

	ok('admin.php?action=settings&job=general');
}
elseif ($job == 'sitestatus') {
	$obox = file_get_contents('data/offline.php');
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=sitestatus2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2">Switch Viscacha on and off</td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Switch off:<br><span class="stext">From time to time, you may want to turn your site off to the public while you perform maintenance, update versions, etc. When you turn your forum off, visitors will receive a message that states that the forum is temporarily unavailable. However administrators can use the site as usual.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="foffline" value="1"<?php echo iif($config['foffline'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Offline message:<br><span class="stext">This message will be shown if page is switched off.<br />HTML is allowed!</span></td>
	   <td class="mbox" width="50%"><textarea class="texteditor" name="template" rows="5" cols="60"><?php echo $obox; ?></textarea></td> 
	  </tr>
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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

	ok('admin.php?action=settings&job=sitestatus');
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
	   <td class="obox" colspan="2"><b>Date and Time</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Forum Timezone:<br><font class="stext">Standard Timezone for the Forum!</font></td>
	   <td class="mbox" width="50%"><select name="timezone"> 
					<option selected value="<?php echo $config['timezone']; ?>">Maintain Timezone (GMT <?php echo $config['timezone']; ?>)</option>
					<option value="-12">(GMT -12:00) Eniwetok, Kwajalein</option>
					<option value="-11">(GMT -11:00) Midway-Inseln, Samoa</option>
					<option value="-10">(GMT -10:00) Hawaii</option>
					<option value="-9">(GMT -09:00) Alaska</option>
					<option value="-8">(GMT -08:00) Tijuana, Lod Angeles, Seattle, Vancouver</option>
					<option value="-7">(GMT -07:00) Arizona, Denver, Salt Lake City, Calgary</option>
					<option value="-6">(GMT -06:00) Mexiko-Stadt, Saskatchewan, Zentralamerika</option>
					<option value="-5">(GMT -05:00)  Bogot&aacute;, Lima, Quito, Indiana (Ost), New York, Toronto</option>
					<option value="-4">(GMT -04:00) Caracas, La Paz, Montreal, Quebec, Santiago</option>
					<option value="-3.5">(GMT -03:30) Neufundland</option>
					<option value="-3">(GMT -03:00) Brasilia, Buenos Aires, Georgetown, Gr&ouml;nland</option>
					<option value="-2">(GMT -02:00) Mittelatlantik</option>
					<option value="-1">(GMT -01:00) Azoren, Kapverdische Inseln</option>
					<option value="0">(GMT) Casablance, Monrovia, Dublin, Edinburgh, Lissabon, London</option>
					<option value="+1">(GMT +01:00) Amsterdam, Berlin, Bern, Rom, Stockholm, Wien, Paris</option>
					<option value="+2">(GMT +02:00) Athen, Istanbul, Minsk, Kairo, Jerusalem</option>
					<option value="+3">(GMT +03:00) Bagdad, Moskau, Nairobi</option>
					<option value="+3.5">(GMT +03:30) Teheran</option>
					<option value="+4">(GMT +04:00) Muskat, Tiflis</option>
					<option value="+4.5">(GMT +04:30) Kabul</option>
					<option value="+5">(GMT +05:00) Islamabad</option>
					<option value="+5.5">(GMT +05:30) Kalkutta, Neu-Delhi</option>
					<option value="+5.75">(GMT +05:45) Katmandu</option>
					<option value="+6">(GMT +06:00) Almaty, Nowosibirsk, Dhaka</option>
					<option value="+6.5">(GMT +06:30) Rangun</option>
					<option value="+7">(GMT +07:00) Bangkok, Hanoi, Jakarta</option>
					<option value="+8">(GMT +08:00) Ulan Bator, Singapur, Peking, Hongkong</option>
					<option value="+9">(GMT +09:00) Irkutsk, Osaka, Sapporo, Tokyo, Seoul</option>
					<option value="+9.5">(GMT +09:30) Adelaide, Darwin</option>
					<option value="+10">(GMT +10:00) Brisbane, Canberra, Melbourne, Sydney, Wladiwostok</option>
					<option value="+11">(GMT +11:00) Salomonen, Neukaledonien</option>
					<option value="+12">(GMT +12:00) Auckland, Wellington, Fidschi, Kamtschatka</option>
				</select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Use "today" and "yesterday":</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="new_dformat4" value="1"<?php echo iif($config['new_dformat4'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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

	ok('admin.php?action=settings&job=fgeneral');
}
elseif ($job == 'http') {
	$config = $gpc->prepare($config);
	
	if (!extension_loaded("zlib") || !function_exists('gzcompress')) {
		$gzip = '<span style="color: #aa0000;">not enabled</span>';
	}
	else {
		$gzip = '<span style="color: #00aa00;">enabled</span>';
	}
	
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=http2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2">Headers, Cookies &amp; GZIP</td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Activate GZip-compression:<br><span class="stext">Selecting yes will enable GZIP to reduce bandwidth requirements, but there will be a small performance overhead instead. This feature requires the Zlib library, which is <?php echo $gzip; ?>! If you are already using mod_gzip on your server, do not enable this option.</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="gzip" value="1"<?php echo iif($config['gzip'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">GZip Compression Level:<br><span class="stext">The Compression Level has to be between 0 (minimum) and 9 (maximum). It is strongly recommend that you use a level between 1 and 3 for optimum results.</span></td>
	   <td class="mbox" width="50%"><select size="1" name="gzcompression">
	   <?php 
	   	for($i=0;$i<10;$i++) {
	   		if ($i == $config['gzcompression']) {
	   			echo "<option value=\"$i\" selected>$i</option>";
	   		}
			else {
	   			echo "<option value=\"$i\">$i</option>";
			}
		}
    	?>
  		</select></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Prevent browser caching:<br /><span class="stext">Send no-cache HTTP headers. These are very effective, so adding them may cause server load to increase due to an increase in page requests.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="nocache" value="1"<?php echo iif($config['nocache'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Pefix for cookies:<br><font class="stext">Only characters (a-z) and "_"!</font></td>
	   <td class="mbox" width="50%"><input type="text" size="10" name="cookie_prefix" value="<?php echo $config['cookie_prefix']; ?>"></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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

	ok('admin.php?action=settings&job=http');
}
elseif ($job == 'textprocessing') {
	$config = $gpc->prepare($config);
	
	if (!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['PHP_SELF'])) {
		$surl = "http://".$_SERVER['HTTP_HOST'].rtrim(viscacha_dirname($_SERVER['PHP_SELF']), '/\\').'/images/smileys';
	}
	else {
		$surl = "Unable to analyze URL.";
	}
	
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=textprocessing2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>BB-Code &amp; Text processing</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Censor Texts:<br>
	   <font class="stext">The words to censor can be specified <a href="admin.php?action=bbcodes&job=censor">here</a>. The extended censoring will make the censor more accurate and find out also letters which are separated with eventual characters.</font></td>
	   <td class="mbox" width="50%">
	   <input type="radio" name="censorstatus" value="0"<?php echo iif($config['censorstatus'] == 0,' checked'); ?>> No censor<br>
	   <input type="radio" name="censorstatus" value="1"<?php echo iif($config['censorstatus'] == 1,' checked'); ?>> Normal censor<br>
	   <input type="radio" name="censorstatus" value="2"<?php echo iif($config['censorstatus'] == 2,' checked'); ?>> Extended censor
	   </td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Mark glossary entries and show explanation:<br><font class="stext">Hereby you can mark the <a href="admin.php?action=bbcodes&job=word">glossary entries</a> and show their explanation.</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="dictstatus" value="1"<?php echo iif($config['dictstatus'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Replace vocabulary:<br><font class="stext">You can replace the <a href="admin.php?action=bbcodes&job=replace">vocabulary</a> automatically (however explicit selectable in every thread).</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="wordstatus" value="1"<?php echo iif($config['wordstatus'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Shorten line break:<br><font class="stext">More than 3 line break can automatically be shorten.</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="reduce_nl" value="1"<?php echo iif($config['reduce_nl'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Improve pointing:<br><font class="stext">You can let more then 2 question marks, 2 exclamation marks or more then 4 points automatically be shorten. </font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="reduce_endchars" value="1"<?php echo iif($config['reduce_endchars'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Correct continuous capitalization:<br><font class="stext">If the whole title is capitalized, only the first letter of a word will be thus leaved.<br /> Example: "NEED HELP!" will be "Need Help!".</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="topicuppercase" value="1"<?php echo iif($config['topicuppercase'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Decimal places after comma:</td>
	   <td class="mbox" width="50%"><input type="text" name="decimals" value="<?php echo $config['decimals']; ?>" size="8"></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table><br />
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>BB-Code &amp; Text processing &raquo; Wordwrap</b></td>
	  </tr>
	  <tr>
	   <td class="mbox" width="50%">Wordwrap: Separate too long words:<br><font class="stext">You can let too long words, which destroy the design, automatically be separated after a determined amount of characters.</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="wordwrap" value="1"<?php echo iif($config['wordwrap'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Wordwrap: Number of characters for separation:</font></td>
	   <td class="mbox" width="50%"><input type="text" name="maxwordlength" value="<?php echo $config['maxwordlength']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Wordwrap: Character or HTML-Tag with wich too long words will be separated:<br><font class="stext">For example a line break with &lt;br /&gt; or a dash (-).</font></td>
	   <td class="mbox" width="50%"><input type="text" name="maxwordlengthchar" value="<?php echo $config['maxwordlengthchar']; ?>" size="8"></td> 
	  </tr>
  	  <tr> 
	   <td class="mbox" width="50%">URL-Wordwrap: Shorten too long URLs automatically:<br><font class="stext">Too long URLs can automatically be shorten, without destroying the link.</font></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="reduce_url" value="1"<?php echo iif($config['reduce_url'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">URL-Wordwrap: Number of characters for separation:</font></td>
	   <td class="mbox" width="50%"><input type="text" name="maxurllength" value="<?php echo $config['maxurllength']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">URL-Wordwrap: Character for URL separation:</font></td>
	   <td class="mbox" width="50%"><input type="text" name="maxurltrenner" value="<?php echo $config['maxurltrenner']; ?>" size="8"></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table><br />
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>BB-Code &amp; Text processing &raquo; Smileys</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Number of smileys shown in a row:</td>
	   <td class="mbox" width="50%"><input type="text" name="smileysperrow" value="<?php echo $config['smileysperrow']; ?>" size="8"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Path to the smiley-directory:<br /><span class="stext">Path to the directory containing the smiley images (without closing "/") e.g. <tt><?php echo str_replace('\\', '/', realpath('./')); ?>/images/smileys</tt> .</span></td>
	   <td class="mbox" width="50%"><input type="text" name="smileypath" value="<?php echo $config['smileypath']; ?>" size="60"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">URL to the smiley-directory:<br /><span class="stext">URL to the directory containing the smiley images (without closing "/") e.g. <tt><?php echo $surl; ?></tt> .</span></td>
	   <td class="mbox" width="50%"><input type="text" name="smileyurl" value="<?php echo $config['smileyurl']; ?>" size="60"></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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
	$c->updateconfig('smileysperrow',int);
	$c->updateconfig('topicuppercase',int);
	$c->savedata();

	ok('admin.php?action=settings&job=textprocessing');
}
elseif ($job == 'syndication') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=syndication2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>Content Syndication (Javascript, RSS, ...)</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Activate newsfeed for forum posts:<br /><span class="stext">The Newsfeed-Formats can be administered <a href="admin.php?action=misc&amp;job=feedcreator">here</a>.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="syndication" value="1"<?php echo iif($config['syndication'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Maximum number of characters in a text:</td>
	   <td class="mbox" width="50%"><input type="text" name="rsschars" value="<?php echo $config['rsschars']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Time in minutes for caching the newsfeeds:</td>
	   <td class="mbox" width="50%"><input type="text" name="rssttl" value="<?php echo $config['rssttl']; ?>" size="4"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Newsfeed icon:<br /><span class="stext">Size: 16x16 Pixel; Formats: gif, jp(e)g</span></td>
	   <td class="mbox" width="50%"><input type="text" name="syndication_klipfolio_icon" value="<?php echo $config['syndication_klipfolio_icon']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Klipfolio newsfeeds banner:<br /><span class="stext">Size: 234x60 Pixel; Formats: gif, jp(e)g</span></td>
	   <td class="mbox" width="50%"><input type="text" name="syndication_klipfolio_banner" value="<?php echo $config['syndication_klipfolio_banner']; ?>" size="50"></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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
	$c->updateconfig('syndication_klipfolio_banner',str);
	$c->updateconfig('syndication_klipfolio_icon',str);
	$c->updateconfig('rssttl',int);
	$c->updateconfig('rsschars',int);
	$c->savedata();

	ok('admin.php?action=settings&job=syndication');
}
elseif ($job == 'spiders') {
	$config = $gpc->prepare($config);
	echo head();
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=spiders2">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="2"><b>Crawler &amp; Robots</b></td>
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Activate logging of visits and last visits:<br /><span class="stext">The Crawler and Robots can be administered <a href="admin.php?action=spider&amp;job=manage">here</a>.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="spider_logvisits" value="1"<?php echo iif($config['spider_logvisits'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="mbox" width="50%">Activate logging of missing IPs or User Agents:<br /><span class="stext">The pending Crawler and Robots can be administered <a href="admin.php?action=spider&amp;job=pending">here</a>.</span></td>
	   <td class="mbox" width="50%"><input type="checkbox" name="spider_pendinglist" value="1"<?php echo iif($config['spider_pendinglist'],' checked'); ?>></td> 
	  </tr>
	  <tr> 
	   <td class="ubox" width="100%" colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td> 
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

	ok('admin.php?action=settings&job=spiders');
}
elseif ($job == 'version') {
	echo head();
	$comp = get_remote('http://version.viscacha.org/compare/?version='.base64_encode($config['version']));
	$version = get_remote('http://version.viscacha.org/version');
	$news = get_remote('http://version.viscacha.org/news');
	if ($comp == '3') {
		$res = "Your Viscacha is <strong>not up-to-date</strong>. The current version is {$version}!";
	}
	elseif ($comp == '1') {
		$res = "Your Viscacha is a not yet approved test version.";
	}
	elseif ($comp == '2') {
		$res = "Your Viscacha is up-to-date!";
	}
	else {
		$res = "Error on synchronization or no connection!";
	}
	if (!$news) {
		$news = 'Could not connect to server.';
	}
	if (!$version) {
		$version = 'No connection';
	}
	?>
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="4">Version Check</td>
	  </tr>
	  <tr> 
	   <td class="mmbox" width="25%">Your version:</td>
	   <td class="mbox" width="25%"><?php echo $config['version']; ?></td>
	   <td class="mmbox" width="25%">Current version:</td> 
	   <td class="mbox" width="25%"><?php echo $version; ?></td>
	  </tr>
	  <tr> 
	   <td class="mbox" colspan="4"><?php echo $res; ?></td>
	  </tr>
	 </table><br />
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox">Latest Announcement</td> 
	  </tr>
	  <tr> 
	   <td class="mbox"><?php echo $news; ?></td>
	  </tr>
	 </table>
	<?php
	echo foot();
}
elseif ($job == 'custom') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query("
	SELECT s.*, g.name AS groupname
	FROM {$db->pre}settings AS s
		LEFT JOIN {$db->pre}settings_groups AS g ON s.sgroup = g.id
	WHERE s.sgroup = '{$id}'
	ORDER BY s.name
	", __LINE__, __FILE__);
	?>
	<form name="form" method="post" action="admin.php?action=settings&job=custom2&id=<?php echo $id; ?>">
	 <table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
	  <tr> 
	   <td class="obox" colspan="4"><b>Custom Settings</b></td>
	  </tr>
	<?php
	if ($db->num_rows() > 0) {
		while ($row = $db->fetch_assoc($result)) {
			call_user_func('custom_'.$row['type'], $row);
		}
	}
	else {
	?>
	  <tr> 
	   <td class="mbox" colspan="4" align="center">No custom settings added for this category. You can add a new setting <a href="admin.php?action=settings&job=new">here</a>.</td>
	  </tr>
	<?php
	}
	?>
	  <tr> 
	   <td class="ubox" colspan="4" align="center"><input type="submit" name="Submit" value="Submit"></td> 
	  </tr>
	 </table>
	</form> 	
	<?php
	echo foot();
}
elseif ($job == 'custom2') {
	echo head();
	$id = $gpc->get('id', int);
	$c->getdata();

	$result = $db->query("
	SELECT s.*, g.name AS groupname
	FROM {$db->pre}settings AS s
		LEFT JOIN {$db->pre}settings_groups AS g ON s.sgroup = g.id
	WHERE s.sgroup = '{$id}'
	ORDER BY s.name
	", __LINE__, __FILE__);
	while ($row = $db->fetch_assoc($result)) {
		$c->updateconfig(array($row['groupname'], $row['name']), none);
	}
	
	$c->savedata();

	ok('admin.php?action=settings&job=custom&id='.$id);
}
elseif ($job == 'delete') {
	echo head();
	$name = $gpc->get('name', str);
	$id = $gpc->get('id', int);
	$db->query("DELETE FROM {$db->pre}settings WHERE name = '{$name}' AND sgroup = '{$id}' LIMIT 1");
	$upd = $db->affected_rows();
	if ($upd == 1) {
		$result = $db->query("SELECT name FROM {$db->pre}settings_groups WHERE id = '{$id}'");
		$row = $db->fetch_assoc($result);
		$c->getdata();
		$c->delete(array($row['name'], $name));
		$c->savedata();
		ok('admin.php?action=settings&job=custom&id='.$id,'Custom Setting deleted!');
	}
	else {
		error('admin.php?action=settings&job=custom&id='.$id,'Custom setting not available.');
	}
}
elseif ($job == 'delete_group') {
	echo head();
	$id = $gpc->get('id', int);
	$result = $db->query("
	SELECT s.name, g.name AS groupname
	FROM {$db->pre}settings AS s
		LEFT JOIN {$db->pre}settings_groups AS g ON s.sgroup = g.id
	WHERE s.sgroup = '{$id}'");
	while ($row = $db->fetch_assoc($result)) {
		$c->getdata();
		$c->delete(array($row['groupname'], $row['name']));
		$c->savedata();
	}
	$db->query("DELETE FROM {$db->pre}settings WHERE sgroup = '{$id}'");
	$db->query("DELETE FROM {$db->pre}settings_groups WHERE id = '{$id}' LIMIT 1");
	
	ok('admin.php?action=settings','Custom Setting Group deleted!');
}
elseif ($job == 'new_group') {
	echo head()
	?>
<form action="admin.php?action=settings&job=new_group2" method="post">
<table border="0" align="center" class="border">
<tr>
<td class="obox" colspan="2">Add Setting group</td>
</tr>
<tr>
<td class="mbox" width="40%">Title</td>
<td class="mbox" width="60%"><input type="text" name="title" value="" size="40"></td>
</tr>
<tr>
<td class="mbox" width="40%">Group Name<br /><span class="stext">This will be the name of the setting group as used in scripts and templates. If the name is "<code>value</code>", the variable is <code>$config['value']['entries']</code>.  You can use only alphanumerical characters and the underscore.</span></td>
<td class="mbox" width="60%"><input type="text" name="name" value="" size="40"></td>
</tr>
<tr>
<td class="mbox" width="40%">Description</td>
<td class="mbox" width="60%"><textarea name="description" rows="4" cols="50"></textarea></td>
</tr>
<tr><td class="ubox" colspan="2" align="center"><input type="submit" value="Add Group"></td></tr>
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
	
	if (strlen($title) < 3 || strlen($title) > 120) {
		error('admin.php?action=settings&job=custom','Title is too short or too long.');
	}
	if (strlen($name) < 3 || strlen($name) > 120) {
		error('admin.php?action=settings&job=custom','Group Name is too short or too long.');
	}
	
	$db->query("INSERT INTO {$db->pre}settings_groups (title, name, description) VALUES ('{$title}', '{$name}', '{$desc}')");
	
	ok('admin.php?action=settings&job=custom', 'Group inserted!');
}
elseif ($job == 'new') {
	echo head();
	$result = $db->query("SELECT id, title FROM {$db->pre}settings_groups ORDER BY title");
	?>
<form action="admin.php?action=settings&job=new2" method="post">
<table border="0" align="center" class="border">
<tr>
<td class="obox" colspan="2">Add Setting</td>
</tr>
<tr>
<td class="mbox" width="40%">Setting Title</td>
<td class="mbox" width="60%"><input type="text" name="title" value="" size="40"></td>
</tr>
<tr>
<td class="mbox" width="40%">Description</td>
<td class="mbox" width="60%"><textarea name="description" rows="4" cols="50"></textarea></td>
</tr>
<tr>
<td class="mbox" width="40%">Group</td>
<td class="mbox" width="60%"><select name="group">
<?php while ($row = $db->fetch_assoc($result)) { ?>
<option value="<?php echo $row['id']; ?>"><?php echo $row['title']; ?></option>
<?php } ?>
</select></td>
</tr>
<tr>
<td class="mbox" width="40%">Setting Name<br /><span class="stext">This will be the name of the setting as used in scripts and templates. If the name is "<code>value</code>", the variable is <code>$config['groupname']['value']</code>. You can use only alphanumerical characters and the underscore.</span></td>
<td class="mbox" width="60%"><input type="text" name="name" value="" size="40"></td>
</tr>
<tr>
<td class="mbox" width="40%">Setting Type</td>
<td class="mbox" width="60%">
<select name="type">
<option value="select">Select</option>
<option value="checkbox">Checkbox</option>
<option value="text">Text (one line)</option>
<option selected="selected" value="textarea">Textarea</option>
</select>
</td>
</tr>
<tr>
<td class="mbox" width="40%">Setting Type Values<br />
<span class="stext">
Only for Select-Fields.<br />
<strong>Format:</strong> (each entry in a new line)<br />
<code>value=title</code><br />
<code>value</code> is a value which can only contain letters, numbers and underscores.<br />
<code>title</code> is a one line value shown in the select box.<br />
</span></td>
<td class="mbox" width="60%"><textarea name="typevalue" rows="6" cols="50"></textarea></td>
</tr>
<tr>
<td class="mbox" width="40%">(Standard-)Value<br /><span class="stext">Can be changed later. If it is a select-box then this value has to be one of the <code>value</code>'s. Can not be changed if it is a checkbox.</span></td>
<td class="mbox" width="60%"><input type="text" name="value" value="" size="40"></td>
</tr>
<tr><td class="ubox" colspan="2" align="center"><input type="submit" value="Add Setting"></td></tr>
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
	
	$result = $db->query("SELECT name FROM {$db->pre}settings_groups WHERE id = '{$group}'");
	$row = $db->fetch_assoc($result);
	
	if (isset($config[$row['name']][$name]) || strlen($name) < 3 || strlen($name) > 120) {
		error('admin.php?action=settings&job=custom','Name already exists.');
	}
	if ($type != 'checkbox' && $type != 'text' && $type != 'textarea' && $type != 'select') {
		error('admin.php?action=settings&job=custom','Invalid type.');
	}
	if ($type == 'select') {
		$typevalue = str_replace("\r\n", "\n", trim($typevalue));
		$typevalue = str_replace("\r", "\n", $typevalue);
		$arr_value = prepare_custom($typevalue);
		$typevalue = $gpc->save_str($typevalue);
		if (empty($arr_value[$value])) {
			error('admin.php?action=settings&job=new','Value is not given in Setting Type Values.');
		}
	}
	else {
		$typevalue = '';
	}
	
	$db->query("
INSERT INTO {$db->pre}settings (name, title, description, type, optionscode, value, sgroup) 
VALUES ('{$name}', '{$title}', '{$desc}', '{$type}', '{$typevalue}', '".$gpc->save_str($value)."', '{$group}')
");
	
	$c->getdata();
	$c->updateconfig(array($row['name'], $name), none, $value);
	$c->savedata();
	
	ok('admin.php?action=settings&job=custom&id='.$group, 'Setting inserted!');
}
else {
	echo head();
	$result = $db->query("SELECT id, title, description FROM {$db->pre}settings_groups ORDER BY title");
	?>
<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
 <tr> 
  <td class="obox" colspan="2"><b>Viscacha Settings</b></td>
 </tr>
 <tr class="mbox"><td width="30%">
  <a href="admin.php?action=settings&job=sitestatus">Switch Viscacha on or off</a>
 </td><td width="70%">
  <span class="stext">Here you can temporarily deactivate the system for non-administrators to do maintenance work or updates.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=general">General Settings</a>
 </td><td>
  <span class="stext">General settings like changing names or addresses.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=db">Datenbank</a>
 </td><td>
  <span class="stext">Database configuration: Host, user, password, database, system etc.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=ftp">FTP</a>
 </td><td>
  <span class="stext">FTP-User data: Host, user, password for FTP access, backups and file operations.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=datetime">Date- and Time</a>
 </td><td>
  <span class="stext">Dateformat and timeoutput, timezone and something similar.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=cron">Scheduled Tasks</a>
 </td><td>
  <span class="stext">"<a href="admin.php?action=cron&job=manage">Scheduled Tasks</a>" Settings.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=avupload">Profile pictures &amp; Avatars</a>
 </td><td>
  <span class="stext">User pictures can be limited.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=attupload">Thread attachments</a>
 </td><td>
  <span class="stext">Limits and settings for thread attachments.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=textprocessing">BB-Code &amp; Text processing</a>
 </td><td>
  <span class="stext">BB-Code parser configuration (Wordwrap, pointing, censor ...)</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=syndication">Syndication</a>
 </td><td>
  <span class="stext">General settings for <a href="admin.php?action=misc&job=feedcreator">provided newsfeeds</a>(Javascript, RSS, Atom, Clipfolio).</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=jabber">Jabber</a>
 </td><td>
  <span class="stext">Jabber-Account settings and Jabber-delivery.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=spellcheck">Spellcheck</a>
 </td><td>
  <span class="stext"><a href="admin.php?action=misc&job=spellcheck">Spell verification </a> settings.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=register">Registration</a>
 </td><td>
  <span class="stext">Registration and Forum rules.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=email">E-mails</a>
 </td><td>
  <span class="stext">E-mail-delivery (PHP, SMTP, Sendmail).</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=lang">Internationalization &amp; Languages</a>
 </td><td>
  <span class="stext">Internationalization (<a href="admin.php?action=language&job=manage">Languages</a> and character sets)</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=profile">Profile (edit &amp; view)</a>
 </td><td>
  <span class="stext">Profile settings. Minimum lengths, vCards etc.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=signature">Signatures</a>
 </td><td>
  <span class="stext">Signature settings like BB-Code limits.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=posts">Topics &amp; Posts</a>
 </td><td>
  <span class="stext">Minimum lengths and maximum lengths, PDF-output, editing and other settings for Threads an Topics.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=search">Search</a>
 </td><td>
  <span class="stext">Search results and search settings.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=server">PHP, Webserver and Filesystem</a>
 </td><td>
  <span class="stext">Webserver settings (.htaccess), PHP and files on the server.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=http">Headers, Cookies &amp; GZIP</a>
 </td><td>
  <span class="stext">Cookies, Page compression and HTTP-Headers.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=session">Sessionsystem</a>
 </td><td>
  <span class="stext">Flood blocking und Sessions in the Forum.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=boardcat">Forums &amp; categories</a>
 </td><td>
  <span class="stext">Forums, subforums, statistics and categories.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=user">Member- &amp; Teamlist</a>
 </td><td>
  <span class="stext">Member- &amp; Teamlist settings.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=pm">Private Messaging</a>
 </td><td>
  <span class="stext">Private Messaging settings.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=captcha">Spam-Bot-Protection (CAPTCHA)</a>
 </td><td>
  <span class="stext">Image based verification to prevent automatic registration or posting. Spam-Bot-Protection with <a href="admin.php?action=misc&job=captcha">CAPTCHA</a>-Images.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=spiders">Crawler &amp; Robots</a>
 </td><td>
  <span class="stext">Logging of Crawlers and Robots that have visited the site.</span>
 </td></tr>
 <tr class="mbox"><td>
  <a href="admin.php?action=settings&job=cmsp">CMS &amp; Portal</a>
 </td><td>
  <span class="stext">Portal, homepage and site administration.</span>
 </td></tr>
</table>
<?php if ($db->num_rows($result)) { ?>
</table><br class="minibr" />
<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
 <tr> 
  <td class="obox" colspan="3"><b>Custom Settings</b></td>
 </tr>
 <?php while ($row = $db->fetch_assoc($result)) { ?>
 <tr class="mbox">
  <td width="30%"><a href="admin.php?action=settings&job=custom&id=<?php echo $row['id']; ?>"><?php echo $row['title']; ?></a></td>
  <td width="60%" class="stext"><?php echo $row['description']; ?></td>
  <td width="10%"><a href="admin.php?action=settings&job=delete_group&id=<?php echo $row['id']; ?>">Delete Group</a></td>
 </tr>
 <?php } ?>
</table>
	<?php
	}
	echo foot();
}

function custom_select($arr) {
	global $config;
	$val = prepare_custom($arr['optionscode']);
?>
<tr> 
 <td class="mbox" width="35%"><?php echo $arr['title']; ?><br /><span class="stext"><?php echo $arr['description']; ?></span></td>
 <td class="mbox" width="45%">
 <select name="<?php echo $arr['name']; ?>">
 <?php foreach ($val as $key => $value) { ?>
  <option value="<?php echo $key; ?>"<?php echo iif($config[$arr['groupname']][$arr['name']] == $key, ' selected="selected"'); ?>><?php echo $value; ?></option>
 <?php } ?>
 </select>
 </td>
 <td class="mbox" width="10%"><a href="admin.php?action=settings&job=delete&name=<?php echo $arr['name']; ?>&id=<?php echo $arr['sgroup']; ?>">Delete Setting</a></td>
 <td class="mbox" width="10%"><code>$config['<?php echo $arr['groupname']; ?>']['<?php echo $arr['name']; ?>']</code></td>
</tr>
<?php
}
function custom_checkbox($arr) {
	global $config;
?>
<tr> 
 <td class="mbox" width="35%"><?php echo $arr['title']; ?><br /><span class="stext"><?php echo $arr['description']; ?></span></td>
 <td class="mbox" width="45%"><input type="checkbox" name="<?php echo $arr['name']; ?>" value="<?php echo $config[$arr['groupname']][$arr['name']]; ?>"<?php echo iif($config[$arr['name']],' checked="checked"'); ?> /></td>
 <td class="mbox" width="10%"><a href="admin.php?action=settings&job=delete&name=<?php echo $arr['name']; ?>&id=<?php echo $arr['sgroup']; ?>">Delete Setting</a></td>
 <td class="mbox" width="10%"><code>$config['<?php echo $arr['groupname']; ?>']['<?php echo $arr['name']; ?>']</code></td>
</tr>
<?php
}
function custom_text($arr) {
	global $config;
?>
<tr> 
 <td class="mbox" width="35%"><?php echo $arr['title']; ?><br /><span class="stext"><?php echo $arr['description']; ?></span></td>
 <td class="mbox" width="45%"><input type="text" name="<?php echo $arr['name']; ?>" value="<?php echo $config[$arr['groupname']][$arr['name']]; ?>" /></td>
 <td class="mbox" width="10%"><a href="admin.php?action=settings&job=delete&name=<?php echo $arr['name']; ?>&id=<?php echo $arr['sgroup']; ?>">Delete Setting</a></td>
 <td class="mbox" width="10%"><code>$config['<?php echo $arr['groupname']; ?>']['<?php echo $arr['name']; ?>']</code></td>
</tr>
<?php
}
function custom_textarea($arr) {
	global $config;
?>
<tr> 
 <td class="mbox" width="35%"><?php echo $arr['title']; ?><br /><span class="stext"><?php echo $arr['description']; ?></span></td>
 <td class="mbox" width="45%"><textarea cols="50" rows="4" name="<?php echo $arr['name']; ?>"><?php echo $config[$arr['groupname']][$arr['name']]; ?></textarea></td>
 <td class="mbox" width="10%"><a href="admin.php?action=settings&job=delete&name=<?php echo $arr['name']; ?>&id=<?php echo $arr['sgroup']; ?>">Delete Setting</a></td>
 <td class="mbox" width="10%"><code>$config['<?php echo $arr['groupname']; ?>']['<?php echo $arr['name']; ?>']</code></td>
</tr>
<?php
}
function prepare_custom($str) {
	$str = trim($str);
	$explode = explode("\n", $str);
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
			error();
		}
	}
	return $arr;
}
?>
