########################################
# Readme for Viscacha 0.8 RC 2         #
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

First make a backup of your (old) data!

Upload (and overwrite) the following files (* = an ID):

- All files in the directory "admin":
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
   - admin/posts.php
   - admin/profilefield.php
   - admin/settings.php
   - admin/slog.php
   - admin/spider.php
   - admin/start.php

- All *.php and all .htaccess files in the directory "classes" and all subdirectories of the directory "classes":
   - classes/cache/parent_forums.inc.php
   - classes/cron/jobs/dboptimize.php
   - classes/cron/jobs/deletegeshi.php
   - classes/cron/jobs/deletesearch.php
   - classes/cron/jobs/deletetemp.php
   - classes/cron/jobs/deletethumbnails.php
   - classes/cron/jobs/digestdaily.php
   - classes/cron/jobs/digestweekly.php
   - classes/cron/jobs/recountpostcounts.php
   - classes/cron/class.parser.php
   - classes/cron/function.cron.php
   - classes/database/mysql.inc.php
   - classes/diff/class.diff.php
   - classes/diff/function.diff.php
   - classes/feedcreator/klipfolio.inc.php
   - classes/feedcreator/rss091.inc.php
   - classes/feedcreator/rss20.inc.php
   - classes/fpdf/class.php
   - classes/fpdf/extension.php
   - classes/ftp/class.ftp.php
   - classes/ftp/class.ftp_pure.php
   - classes/ftp/class.ftp_sockets.php
   - classes/graphic/class.text2image.php
   - classes/graphic/class.thumbnail.php
   - classes/graphic/class.veriword.php
   - classes/graphic/text2image.php
   - classes/magpie_rss/rss_cache.inc.php
   - classes/magpie_rss/rss_fetch.inc.php
   - classes/magpie_rss/rss_parse.inc.php
   - classes/magpie_rss/rss_utils.inc.php
   - classes/mail/class.phpmailer.php
   - classes/mail/class.smtp.php
   - classes/mail/extended.phpmailer.php
   - classes/spellchecker/dict/HERE YOU CAN GET DICTIONARIES
   - classes/spellchecker/function.php
   - classes/spellchecker/mysql.class.php
   - classes/spellchecker/php.class.php
   - classes/spellchecker/pspell.class.php
   - classes/class.bbcode.php
   - classes/class.breadcrumb.php
   - classes/class.cache.php
   - classes/class.charts.php
   - classes/class.convertroman.php
   - classes/class.docoutput.php
   - classes/class.feedcreator.php
   - classes/class.filesystem.php
   - classes/class.geshi.php
   - classes/class.gpc.php
   - classes/class.imageconverter.php
   - classes/class.imstatus.php
   - classes/class.ini.php
   - classes/class.jabber.php
   - classes/class.language.php
   - classes/class.permissions.php
   - classes/class.phpconfig.php
   - classes/class.plugins.php
   - classes/class.snoopy.php
   - classes/class.tar.php
   - classes/class.template.php
   - classes/class.upload.php
   - classes/class.vCard.inc.php
   - classes/class.zip.php
   - classes/function.chmod.php
   - classes/function.flood.php
   - classes/function.frontend_init.php
   - classes/function.global.php
   - classes/function.gpc.php
   - classes/function.jabber.php
   - classes/function.phpcore.php
   - classes/function.profilefields.php
   - classes/function.viscacha_frontend.php
   - classes/cache/.htaccess
   - classes/cron/.htaccess
   - classes/database/.htaccess
   - classes/diff/.htaccess
   - classes/feedcreator/.htaccess
   - classes/fpdf/.htaccess
   - classes/ftp/.htaccess
   - classes/geshi/.htaccess
   - classes/geshi/cpp-qt.php
   - classes/geshi/plsql.php
   - classes/geshi/z80.php
   - classes/magpie_rss/.htaccess
   - classes/mail/.htaccess

- admin/html/standard.css
- admin/lib/class.servernavigator.php
- admin/lib/function.viscacha_backend.php

- designs/*/ie.css
- Only if you are using the standard stylesheet re-upload designs/1/standard.css

- modules/3/latestnews.php ( The ID [3] can differ at your installation)

- templates/lang2js.php


- templates/*/addreply.html
- templates/*/main/smileys.html
- templates/*/members/index.html
- templates/*/members/index_bit.html
- templates/*/modules/11/legend.html
- templates/*/modules/12/legend.html
- templates/*/modules/13/legend.html
- templates/*/newtopic/index.html
- templates/*/pm/delete.html
- templates/*/pm/show.html
- templates/*/pm/move.html

- All .php files in the main Viscacha folder:
   - addreply.php
   - admin.php
   - ajax.php
   - attachments.php
   - components.php
   - cron.php
   - docs.php
   - edit.php
   - editprofile.php
   - external.php
   - forum.php
   - images.php
   - index.php
   - log.php
   - manageforum.php
   - managemembers.php
   - managetopic.php
   - members.php
   - misc.php
   - newtopic.php
   - pdf.php
   - pm.php
   - popup.php
   - portal.php
   - print.php
   - profile.php
   - register.php
   - search.php
   - showforum.php
   - showtopic.php
   - spellcheck.php
   - team.php


Finally upload the install/ directory and execute the update script.

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