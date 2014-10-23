########################################
# Installation Viscacha 0.8 Beta 4     #
########################################


== Preamble ==

This is the third public release of Viscacha (0.8). Some of the Feature
are missing, but I am working to complete these features for version 0.9.
You can commit bugs and suggestions to the bugtracker (see below) and I 
will check these entries and fix or implement them in most cases.

This version is meant only for testing purposes only and in productive use
there can occur some problems. This is version 0.8 and not 1.0. Until 
version 1.0 is released there will be some major changes that can affect 
the compatibility and may result in lost data. plugins and components are 
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
- All files in the directories "admin/data/"
- All files in the directories "data" and "data/cron"
- All files in the directory "docs"
- All files in the directory "language" and all files in the subdirectories of 
  "language"
- All files in the directory "templates" and all files in the subdirectories of 
  "templates"


== Update ==

First make a backup of your old data!

Delete all old files in the folder "admin/data/" except the file "notes.php" and 
then upload all new files in the folder "admin/data/" except the file "notes.php".

Upload/Replace the complete folders "admin/html" and "admin/lib".

Upload all php files in the folder "admin".

Upload/Replace the following files in the "classes/" folder:
- cache/cat_bid.inc.php
- cache/parent_forums.inc.php
- cache/syntaxhighlight.inc.php
- cache/version_check.inc.php
- cron/jobs/digestdaily.php
- cron/jobs/digestweekly.php
- cron/jobs/recountpostcounts.php
- cron/function.cron.php
- database/mysql.inc.php
- ftp/class.ftp.php
- graphic/class.text2image.php
- graphic/text2image.php
- mail/extended.phpmailer.php
- class.bbcode.php
- class.cache.php
- class.docoutput.php
- class.filesystem.php
- class.geshi.php
- class.gpc.php
- class.language.php
- class.upload.php
- function.frontend_init.php
- function.global.php
- function.gpc.php
- function.phpcore.php
- function.profilefields.php
- function.viscacha_frontend.php

Open in each design (Folder: "designs/") the file "standard.css".
Replace the following line:
.hiddenl {
with this line:
.hiddenl ul {

Upload to each image pack (Folder: "images/") the file "tt.gif" in the folder "bbcodes/".

Many changes were made in the language files. You have to update all files. If you 
translated the files, make a backup and diff the files later manually. You can not
update them. English files are in the folder "2". German files are in the folder "1".
Please remember: After updating please check the settings for all languages in your admin
control panel. Please check that the settings "Country code" and "Language_code" are set 
correctly. If they are not correct there can occur problems with updating to a newer version
or with plugins an components.

If you have installed the quick reply plugin copy the file "quick-reply.php" into the 
folder where the plugin is installed (Standard: 17). 

If you have installed the plugin to display the news on the portal copy the file 
"latestnews.php" into the folder where the plugin is installed (Standard: 3).

If you have installed the plugin to display the login box under the forum copy the 
file "login.php" into the folder where the plugin is installed (Standard: 16).

If you have installed the plugin to display the personal box in the navigation copy the 
file "login.php" into the folder where the plugin is installed (Standard: 5).

Upload the file templates/global.js.

Many changes were made in the template files. (* = ID of language pack)
Please update all the following files in each template pack:
- templates/*/admin/forum/index.html
- templates/*/editprofile/abos.html
- templates/*/editprofile/attachments.html
- templates/*/editprofile/settings.html
- templates/*/log/login.html
- templates/*/main/bbhtml.html
- templates/*/main/error.html
- templates/*/main/ok.html
- templates/*/members/index.html
- templates/*/members/index_bit.html
- templates/*/members/index_letter.html
- templates/*/misc/bbhelp.html
- templates/*/misc/wwo_bit.html
- templates/*/modules/17/quick-reply.html
- templates/*/modules/3/news.html
- templates/*/modules/5/login_guest.html
- templates/*/newtopic/index.html
- templates/*/pm/browse.html
- templates/*/pm/browse_bit.html
- templates/*/profile/index.html
- templates/*/showtopic/upload_box.html
- templates/*/team/moderator_bit.html
- templates/*/addreply.html
- templates/*/menu.html
- templates/*/register.html

Upload the following files into your Viscacha root folder:
- addreply.php
- admin.php
- docs.php
- edit.php
- editprofile.php
- log.php
- manageforum.php
- managemembers.php
- managetopic.php
- members.php
- misc.php
- newtopic.php
- pm.php
- popup.php
- profile.php
- register.php
- showtopic.php

Finally upload the install/ directory and execute the update script.

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
                   sockets, mime_magic
 - MySQL Version: 4.1 and above

If you are testing Viscacha, please give me some feedback how Viscacha worked,
which errors occurred and which server configuration was used.

Following information interest me:
- Operating system (of the server)
- Server software and version
- E-mail-server (SMTP, Sendmail, PHP's mail() function)
- MySQL version
- PHP version
- Status of the extensions: mysql, pcre, gd, zlib, xml, pspell, iconv, mbstring,
                            mhash
- The following settings in the file php.ini: 
  - safe_mode
  - magic_quotes_gpc
  - register_globals
  - open_basedir


== Contact ==

E-mail: webmaster@mamo-net.de
ICQ: 104458187
AIM: mamonede8
YIM: mamonede
Jabber: MaMo@jabber.ccc.de
MSN: ma_mo_web@hotmail.com

Bugtracker: http://bugs.viscacha.org