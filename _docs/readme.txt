########################################
# Readme for Viscacha 0.8 RC 4         #
########################################

== Table of Contents ==
1. Installation
2. CHMODs
3. Update
4. System requirements
5. Contact


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
- All files in the directory "admin/data/"
- All files in the directories "data" and "data/cron"
- All files in the directory "docs"
- All files in the directory "language" and all files in the subdirectories of
  "language"
- All files in the directory "templates" and all files in the subdirectories of
  "templates"


== Update ==
First make a complete backup of your (old) data!
Note: You can only update from Viscacha 0.8 RC3 to 0.8 RC4!

1. Upload (and overwrite) the following files (* = an ID):
 - addreply.php
 - ajax.php
 - editprofile.php
 - external.php
 - images.php
 - manageforum.php
 - managemembers.php
 - managetopic.php
 - misc.php
 - newtopic.php
 - pdf.php
 - popup.php
 - profile.php
 - register.php
 - search.php
 - showforum.php
 - showtopic.php

 - admin/html/admin.js
 - admin/html/editor.js

 - admin/lib/class.servernavigator.php
 - admin/lib/function.language.php
 - admin/lib/function.viscacha_backend.php

 - admin/bbcodes.php
 - admin/cms.php
 - admin/db.php
 - admin/designs.php
 - admin/forums.php
 - admin/language.php
 - admin/members.php
 - admin/misc.php
 - admin/packages.php
 - admin/posts.php
 - admin/settings.php
 - admin/spider.php
 - admin/start.php

 - classes/cache/index_moderators.inc.php
 - classes/cache/package_browser.inc.php

 - classes/database/class.db_driver.php
 - classes/database/mysql.inc.php
 - classes/database/mysqli.inc.php

 - classes/feedcreator/atom10.inc.php
 - classes/feedcreator/googlesitemap.inc.php
 - classes/feedcreator/html.inc.php
 - classes/feedcreator/javascript.inc.php
 - classes/feedcreator/klipfolio.inc.php
 - classes/feedcreator/klipfood.inc.php
 - classes/feedcreator/opml.inc.php
 - classes/feedcreator/pie01.inc.php
 - classes/feedcreator/rss091.inc.php
 - classes/feedcreator/rss10.inc.php
 - classes/feedcreator/rss20.inc.php
 - classes/feedcreator/xbel.inc.php
 
 - classes/ftp/class.ftp.php

 - classes/graphic/class.thumbnail.php
 - classes/graphic/class.veriword.php

 - classes/mail/class.phpmailer.php

 - classes/magpie_rss/rss_fetch.inc.php

 - classes/class.bbcode.php
 - classes/class.breadcrumb.php
 - classes/class.cache.php
 - classes/class.convertroman.php
 - classes/class.feedcreator.php
 - classes/class.filesystem.php
 - classes/class.gpc.php
 - classes/class.language.php
 - classes/class.permissions.php
 - classes/class.plugins.php
 - classes/class.template.php
 - classes/class.upload.php
 - classes/class.zip.php
 - classes/function.flood.php
 - classes/function.frontend_init.php
 - classes/function.global.php
 - classes/function.phpcore.php
 - classes/function.profilefields.php
 - classes/function.viscacha_frontend.php

 - Delete the whole directory "classes/fpdf" on the server!
 - (Re)Upload the whole directory "classes/fpdf" from your hard disk.


2. Upload all files from the directory "language/*/mails/" (* = an ID) from your
   local PC.
       Note: Files from the directory language/1/ are German, files from the
             directory language/2/ are English. Upload only the files from the
             language you need into the correct directory.


3. Upload (and overwrite) the following files (* = an ID):
       Note: Files from the directory language/1/ are German, files from the
             directory language/2/ are English. Upload only the files from the
             language you need into the correct directory.
 - language/*/admin/bbcodes.lng.php
 - language/*/admin/db.lng.php
 - language/*/admin/designs.lng.php
 - language/*/admin/explorer.lng.php
 - language/*/admin/forums.lng.php
 - language/*/admin/javascript.lng.php
 - language/*/admin/language.lng.php
 - language/*/admin/members.lng.php
 - language/*/admin/misc.lng.php
 - language/*/admin/packages.lng.php
 - language/*/admin/settings.lng.php
 - language/*/admin/start.lng.php

 - language/*/mails/admin_confirmed.php
 - language/*/mails/digest_d.php
 - language/*/mails/digest_s.php
 - language/*/mails/digest_w.php
 - language/*/mails/mass_topic_moved.php
 - language/*/mails/new_member.php
 - language/*/mails/new_reply.php
 - language/*/mails/new_topic.php
 - language/*/mails/newpm.php
 - language/*/mails/pwremind.php
 - language/*/mails/pwremind2.php
 - language/*/mails/register_00.php
 - language/*/mails/register_01.php
 - language/*/mails/register_10.php
 - language/*/mails/report_post.php
 - language/*/mails/topic_moved.php

 - language/*/classes.lng.php


4. Delete the following files (* = an ID):
 - language/*/phpmailer.class.lng.php
 - language/*/thumbnail.class.lng.php


5. Upload the following files from the directory "templates" (* = an ID):
 - templates/1/admin/topic/status.html
 - templates/1/newtopic/index.html
 - templates/lang2js.php
 - templates/menu.js


Finally upload the install/ directory and execute the update script.
After you the update is ready and you are back in your Admin Control Panel
again, please check for Updates of your installed Packages!


== System requirements ==

Minimum system requirements:
 - PHP Version: 4.1.0 and above
 - PHP-Extensions: mysql, pcre, gd, zlib
 - MySQL Version: 4.0 and above

Normal system requirements:
 - PHP Version: 4.3.0 and above
 - PHP-Extensions: mysql, pcre, gd, zlib, xml
 - MySQL Version: 4.1 and above

Optimal system requirements:
 - PHP Version: 5.0.0 and above
 - PHP-Extensions: mysql, pcre, gd, zlib, xml, pspell, iconv, mbstring, mhash,
                   sockets, mime_magic
 - MySQL Version: 5.0 and above (Strict mode off)

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