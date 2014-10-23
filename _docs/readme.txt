########################################
# Readme for Viscacha 0.8 RC 3         #
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

First make a backup of your (old) data (including the files and the database)!
With this backup all plugins and components will be deleted (and replaced) by
the installer!

Upload (and overwrite) the following files (* = an ID):
 - addreply.php
 - admin.php
 - attachments.php
 - components.php
 - docs.php
 - editprofile.php
 - images.php
 - log.php
 - manageforum.php
 - managemembers.php
 - managetopic.php
 - members.php
 - misc.php
 - pdf.php
 - pm.php
 - popup.php
 - portal.php
 - profile.php
 - register.php
 - search.php
 - showtopic.php

 - Upload all files in the directory "admin":
    - admin/bbcodes.php
    - admin/cms.php
    - admin/cron.php
    - admin/db.php
    - admin/designs.php
    - admin/explorer.php
    - admin/filetypes.php
    - admin/forums.php
    - admin/frames.php
    - admin/groups.php
    - admin/language.php
    - admin/members.php
    - admin/misc.php
    - admin/packages.php
    - admin/posts.php
    - admin/profilefield.php
    - admin/settings.php
    - admin/slog.php
    - admin/spider.php
    - admin/start.php

 - Upload the following files from the subfolders in the directory "admin":
    - admin/data/config.inc.php
    - admin/data/hooks.txt
    - admin/html/images/avg.gif
    - admin/html/admin.js
    - admin/html/menu.css
    - admin/html/standard.css
    - admin/lib/class.servernavigator.php
    - admin/lib/function.language.php
    - admin/lib/function.settings.php
    - admin/lib/function.viscacha_backend.php

 - Upload the following files from the directory "classes"
    - classes/cache/cat_bid.inc.php
    - classes/cache/components.inc.php
    - classes/cache/custombb.inc.php
    - classes/cache/groups.inc.php
    - classes/cache/index_moderators.inc.php
    - classes/cache/modules_navigation.inc.php
    - classes/cache/package_browser.inc.php
    - classes/cache/version_check.inc.php
    - classes/cron/jobs/deletegeshi.php
    - classes/cron/jobs/deletesearch.php
    - classes/cron/jobs/deletetemp.php
    - classes/cron/jobs/deletethumbnails.php
    - classes/cron/jobs/digestdaily.php
    - classes/cron/jobs/digestweekly.php
    - classes/database/class.db_driver.php
    - classes/database/mysql.inc.php
    - classes/database/mysqli.inc.php
    - classes/fpdf/class.php
    - classes/fpdf/extension.php
    - classes/ftp/class.ftp.php
    - classes/ftp/class.ftp_pure.php
    - classes/ftp/class.ftp_sockets.php
    - classes/geshi/bash.php
    - classes/geshi/c.php
    - classes/geshi/cpp.php
    - classes/geshi/css.php
    - classes/geshi/delphi.php
    - classes/geshi/html4strict.php
    - classes/geshi/pascal.php
    - classes/geshi/perl.php
    - classes/geshi/php.php
    - classes/geshi/qbasic.php
    - classes/geshi/rails.php
    - classes/geshi/ruby.php
    - classes/geshi/visualfoxpro.php
    - classes/geshi/xml.php
    - classes/graphic/class.text2image.php
    - classes/graphic/class.thumbnail.php
    - classes/graphic/class.veriword.php
    - classes/magpie_rss/rss_parse.inc.php
    - classes/mail/class.smtp.php
    - classes/class.bbcode.php
    - classes/class.breadcrumb.php
    - classes/class.cache.php
    - classes/class.docoutput.php
    - classes/class.feedcreator.php
    - classes/class.filesystem.php
    - classes/class.geshi.php
    - classes/class.gpc.php
    - classes/class.imageconverter.php
    - classes/class.ini.php
    - classes/class.jabber.php
    - classes/class.language.php
    - classes/class.permissions.php
    - classes/class.plugins.php
    - classes/class.snoopy.php
    - classes/class.template.php
    - classes/class.upload.php
    - classes/class.zip.php
    - classes/function.errorhandler.php
    - classes/function.flood.php
    - classes/function.frontend_init.php
    - classes/function.global.php
    - classes/function.gpc.php
    - classes/function.phpcore.php
    - classes/function.profilefields.php
    - classes/function.viscacha_frontend.php
    
 - Delete the whole directory "modules" on the server!
 - Delete the whole directory "components" on the server!
 - Upload all files from the directory "modules" from your local PC.

 - Upload the following files from the directory "languages" (* = an ID):
   Note: Files from the directory language/1/ are German, files from the
         directory language/2/ are English. Upload only the files from the
         language you need into the correct directory.
    - ALL FILES / WHOLE DIRECTORY: language/*/admin/
    - language/*/modules.lng.php
    - language/*/mails/report_post.php
    - language/*/bbcodes.lng.php
    - language/*/timezones.lng.php

 - Upload the following files from the directory "templates" (* = an ID):
    - templates/*/admin/topic/reports.html
    - templates/*/edit/edit.html
    - templates/*/editprofile/about.html
    - templates/*/editprofile/notice.html
    - templates/*/editprofile/pic.html
    - templates/*/editprofile/pw.html
    - templates/*/editprofile/signature.html
    - templates/*/log/login.html
    - templates/*/log/pwremind.html
    - templates/*/main/boardpw.html
    - templates/*/main/not_allowed.html
    - templates/*/main/smileys.html
    - templates/*/misc/report_post.html
    - templates/*/members/index.html
    - templates/*/members/index_bit.html
    - templates/*/newtopic/startvote.html
    - templates/*/pm/new.html
    - templates/*/pm/show.html
    - templates/*/profile/ims.html
    - templates/*/profile/index.html
    - templates/*/profile/mail.html
    - templates/*/register/resend.html
    - templates/*/search/index.html
    - templates/*/showtopic/image_box.html
    - templates/*/showtopic/index.html
    - templates/*/showtopic/index_bit.html
    - templates/*/spellcheck/frames.html
    - templates/*/team/index.html
    - templates/*/team/moderator_bit.html
    - templates/*/addreply.html
    - templates/*/banned.html
    - templates/*/categories.html
    - templates/*/footer.html
    - templates/*/menu.html
    - templates/*/menu_noscript.html
    - templates/*/offline.html
    - templates/editor.js
    - templates/global.js
    - templates/lang2js.php
    - templates/menu.js
    - templates/spellChecker.js

 - Delete the whole directory "templates/*/modules" on the server!
 - Delete the whole directory "templates/*/components" on the server!
 - Upload all files from the directory "templates/*/modules" from your local PC.

To update the designs do the following steps:

1. Add the code between the dashes to all ie.css-files:
--------------------------------------------------------------------------------
#popup_bbsmileys {
	overflow: scroll;
}
* html #popup_bbsmileys {
	height: 200px;
}
--------------------------------------------------------------------------------

2. Remove from all standard.css files (the declarations can vary):
--------------------------------------------------------------------------------
.hiddenl ul {
	margin: 0px;
	padding: 0px;
	list-style-type: none;
	list-style-image: none;
}
--------------------------------------------------------------------------------

3. Remove from all standard.css files (the declarations can vary):
--------------------------------------------------------------------------------
.newsfeed_box {
	font-size: 8pt;
}
.newsfeed_box_multi {
	overflow:hidden;
	width:145px;
	height:5em;
}
--------------------------------------------------------------------------------

4. Add the following to all standard.css files:
--------------------------------------------------------------------------------
.bbody ol, .tbody ol {
	list-style-image: none;
}
--------------------------------------------------------------------------------

5. In the file standard.css replace in the definition of .popup the declarations
--------------------------------------------------------------------------------
	left: 0px;
	top: 0px;
--------------------------------------------------------------------------------
   with:
--------------------------------------------------------------------------------
	left: -1000px;
	top: -1000px;
--------------------------------------------------------------------------------

6. Replace in all standard.css files (the declarations can vary):
--------------------------------------------------------------------------------
#popup_bbsmileys {
	height: 200px;
	width: 255px;
	overflow: auto;
}
.tables_bbsmileys {
	width: 250px;
	border-collapse: collapse;
	margin-bottom: 5px;
}
--------------------------------------------------------------------------------
   with
--------------------------------------------------------------------------------
#popup_bbsmileys {
	max-height: 200px;
	width: 255px;
}
.tables_bbsmileys {
	width: 100%;
	border-collapse: collapse;
	margin-bottom: 0px;
}
--------------------------------------------------------------------------------

7. Replace in all standard.css files (the declarations can vary):
--------------------------------------------------------------------------------
.bb_table {
	border: 1px dotted #BCCADA;
	overflow: auto;
	display: block;
	padding: 1px;
	margin-top: 5px;
	margin-bottom: 5px;
}
--------------------------------------------------------------------------------
   with
--------------------------------------------------------------------------------
.bb_table {
	display: block;
	border-collapse: collapse;
	margin: 2px;
}
.bb_table td, .bb_table th {
	border: 1px solid #839FBC;
	padding: 3px;
	background-image: none;
}
.bb_table th {
	background-color: #E1E8EF;
	color: #24486C;
	text-align: center;
	font-size: 9pt;
}
--------------------------------------------------------------------------------


Finally upload the install/ directory and execute the update script. Please
excuse the hard and long update, but the massive changes made this neccessary.


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