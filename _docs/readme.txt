########################################
# Installation Viscacha 0.8 Beta 3     #
########################################


== Preambel ==

This is the third public release of Viscacha (0.8). Some of the Feature
are missing, but I am working to complete these features for version 0.9.
You can commit bugs and suggestions to the bugtracker (see below) and I 
will check these entries and fix or implement them in most cases.

This version is meant only for testing purposes only and in productive use
there can occur some problems. This is version 0.8 and not 1.0. Until 
version 1.0 is released there will be some major changes that can affect 
the compatibility and may result in lost data. Plugins and Components are 
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
- All files in the directory "language" and all files in the subdirectories of 
  "language"
- All files in the directory "templates" and all files in the subdirectories of 
  "templates"


== Update ==

First make a backup of your old data!

Replace/Upload:
- All files in admin/html/ (admin.js, menu.js, standard.css)
- All files in admin/lib/
- All files in admin/ (bbcodes.php, cms.php, db.php, designs.php, 
  explorer.php, filetypes.php, forums.php, frames.php, groups.php, 
  language.php, members.php, misc.php, posts.php, settings.php, slog.php, 
  start.php)
- All files and directories in classes/
- The file modules/16/login.php
- The file modules/2/onlinelist.php
- The file modules/3/latestnews.php
- The file modules/5/login.php
- The file modules/9/lastbox.php
- The file templates/menu.js
- All files in the root directory (addreply.php, admin.php, attachments.php, 
  cron.php, edit.php, editprofile.php, external.php, log.php, manageforum.php, 
  managemembers.php, managetopic.php, misc.php, newtopic.php, pdf.php, 
  popup.php, print.php, profile.php, search.php, showforum.php, showtopic.php, 
  team.php)

Upload to each language directory on the server the files new_reply.php and 
new_topic.php.
You can find english files in the directory with the id 2 and german files in
the directories 1 and 3. The files are in the subfolder mails. If you are 
using a language other than english or German, use the English files and 
translate them into your language. If you have questions regarding this step,
contact me via Instant Messenger or use the support forums.
If you have the English language pack installed, replace the complete directory 
with the files in the directory language/2. You have to replace all files and 
folders on account of many changes in this language pack.

Open in each design (Folder: designs) the file standard.css.
Replace the following line:
#content h3 img, #window h3 img, .tables th img, .h3 {
with this line:
#content h3 img, #window h3 img, .tables th img, .h3 img {

Find the following line:
/* Tabellenlose Formulare */
After that add this code:
/* Start .plainlabel */
.plainlabel {
	float: none;
	display: inline;
	width: auto;
	padding-right: 0px;
	font-weight: normal;
}
/* End .plainlabel */

Many changes were made in the template files. 
(Remember: * = ID of language pack)

Please update all the following files in each template pack:
- templates/*/admin/forum/index_bit.html
- templates/*/admin/members/edit.html
- templates/*/admin/benchmark.html
- templates/*/editprofile/attachments.html
- templates/*/editprofile/notice.html
- templates/*/editprofile/pic.html
- templates/*/editprofile/settings.html
- templates/*/misc/bbhelp.html
- templates/*/misc/wwo_bit.html
- templates/*/modules/11/legend.html
- templates/*/modules/12/legend.html
- templates/*/modules/13/legend.html
- templates/*/modules/16/login.html
- templates/*/modules/2/wwo.html
- templates/*/modules/5/login_guest.html
- templates/*/modules/5/login_member.html
- templates/*/modules/6/newpms.html
- templates/*/modules/7/message.html
- templates/*/modules/9/last.html
- templates/*/newtopic/index.html
- templates/*/newtopic/index_prefix.html
- templates/*/pm/delete.html
- templates/*/popup/showpost.html
- templates/*/profile/ims.html
- templates/*/profile/index.html
- templates/*/profile/mail.html
- templates/*/search/active_bit.html
- templates/*/search/index.html
- templates/*/search/result.html
- templates/*/search/result_bit.html
- templates/*/showforum/index.html
- templates/*/showforum/index_bit.html
- templates/*/showtopic/index.html
- templates/*/showtopic/index_bit.html
- templates/*/showtopic/vote.html
- templates/*/addreply.html
- templates/*/categories.html

Please add all the following files to each template pack (* = ID of language pack):
- templates/*/edit/
- templates/*/edit/edit.html
- templates/*/edit/prefix.html
- templates/*/misc/board_rules.html

Finally upload the install/ directory and execute the updater.

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
which errors occured and which server configuration was used.

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