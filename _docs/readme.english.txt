########################################
# Installation Viscacha 0.8 Beta 2     #
########################################


== Preambel ==

This is the second public release of Viscacha (0.8). Some of the Feature
are missing, but I am working to complete these features for version 0.9.
You can commit bugs and suggestions to the bugtracker (see below) and I 
will check this entries and fix or implement them in most cases.

This version is meant only for testing purposes only and in productive use
there can occur some problems. This is version 0.8 and not 1.0. Until 
version 1.0 is released there will be some major changes that can affect 
the compatibility and my result in lost data. Plugins and Components are 
currently on the newest state, but there can be some minor changes in the
API of plugins and components. All available hooks of the plugin system you
can find in the file hooks.txt. If there is a hook missing, please contact 
the support and we will implement this hook into the next version of Viscacha.


== Installation ==

Upload all files per ftp onto your server. Then call the "install/" directory 
in the Viscacha-root-directory and follow the steps. Then a "fresh" Viscacha-
Installation will be available on your server.


== CHMODs ==
Some of the Viscacha files need more permissions on the server than they have 
normally on the server. It may happen, that it fails to set the CHMODs while 
setting up Viscacha. In this case you have to set them manually:

Following directories need CHMOD 777:
- "admin/backup"
- "cache" and all subdirectories
- "classes/cron/jobs"
- "classes/feedcreator"
- "classes/fonts"
- "classes/geshi"
- "classes/graphic/noises"
- "components"
- "data" and all subdirectories
- "designs" and all subdirectories
- "docs"
- "feeds"
- "images" and all subdirectories
- "language" and all subdirectories
- "temp" and all subdirectories
- "templates" and all subdirectories
- All subdirectories of "uploads"

Following files need CHMOD 666:
- admin/data/notes.php
- All files in the directories "data" and "data/cron"
- All files in the directory "docs"
- All files in the directory "language" and all files in the subdirectories of "language"
- All files in the directory "templates" and all files in the subdirectories of "templates"


== Update ==

First make a backup of your old data!

Delete the following directories completely and afterwards upload these directories
from the Viscacha 0.8 Beta 2 package:
- languages/
- modules/
- templates/1/
- cache/
- temp/

If you do not need this directory anymore, remove it completely:
- smilies (moved to images/smileys)

Replace (upload) all following directories and files:
- admin/ (except for admin/licenses/notes.php!)
- classes/
- docs/credits.php
- images/smileys/
- templates/editor.js
- templates/global.js
- templates/editor
- /
- install/

Replace or upload the following images to all installed image sets:
- negative.gif
- positive.gif
- skype.gif
- ucp_abos.gif
- ucp_signature.gif

Delete this image in each image set:
- ucp_fav.gif

Open the standard.css in all installed designs (stylesheets) and do the
following four steps:

1. Add the code between the dashes (-):
---------------------------------------------------
.popup_noscript {
	text-align: center;
	background-color: #ffffff;
	border: 1px solid #839FBC;
	border-top: 0px;
}
.popup_noscript li {
	display: inline;
	font-weight: bold;
	padding-right: 0.8em; 
	padding-left: 0.8em;
}
.popup_noscript strong {
	text-align: left;
	display: block;
	padding: 4px;
	background-color: #BCCADA;
	border-top: 1px solid #839FBC;
	border-bottom: 1px solid #839FBC;
	color: #336699;
	font-size: 9pt;
}
.popup_noscript ul {
	padding: 4px; 
	margin: 0px; 
	list-style-type: none;
}
---------------------------------------------------

2. Find the following line:
.navigation_cat .nav_sub, .navigation_cat .nav {
and replace it with the following line:
.navigation_cat ul ul, .navigation_cat ul {

3. Find the following line:
.navigation_cat .nav_sub {
and replace it with the following line:
.navigation_cat ul ul {

4. Find the following line:
.navigation_cat .nav {
and replace it with the following line:
.navigation_cat ul {


== System requirements ==

Minimum system requirements:
 - PHP Version: 4.1.0 and above
 - PHP-Extensions: mysql, pcre, gd, zlib
 - MySQL Version: 3.23.57 and above
  
Normal system requirements:
 - PHP Version: 4.3.0 and above
 - PHP-Extensions: mysql, pcre, gd, zlib, xml
 - MySQL Version: 4.0 and above
  
Optimal system requirements:
 - PHP Version: 5.0.0 and above
 - PHP-Extensions: mysql, pcre, gd, zlib, xml, pspell, iconv, mbstring, mhash, 
                      sockets
 - MySQL Version: 4.1 and above

If you are testing Viscacha, please give me some feedback how Viscacha worked,
which errors occured and which server configuration was used.

Following information interest me:
- Operating system (of the server)
- Server software and version
- E-mail-server (SMTP, Sendmail, PHP's mail() function)
- MySQL version
- PHP version
- Status of the extensions: mysql, pcre, gd, zlib, xml, pspell, iconv, mbstring, mhash
- The following settings in the file php.ini: 
  - safe_mode
  - magic_quotes_gpc
  - register_globals
  - register_long_arrays
  - sql.safe_mode


== Contact ==

E-mail: webmaster@mamo-net.de
ICQ: 104458187
AIM: mamonede8
YIM: mamonede
Jabber: MaMo@jabber.ccc.de
MSN: ma_mo_web@hotmail.com

Bugtracker: http://bugs.viscacha.org