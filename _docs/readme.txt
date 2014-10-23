########################################
# Installation Viscacha 0.8 RC 1       #
########################################

== Table of Contents ==
1. Preamble
2. Installation
3. CHMODs
4. Update
5. System requirements
6. Contact


== Preamble ==

This is the fifth public release of Viscacha (0.8). Some of the Feature
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

First make a backup of your (old) data!

Upload the following files (and maybe overwrite them):
- admin/html/standard.css
- admin/html/editor.js
- admin/lib/function.viscacha_backend.inc.php
- admin/bbcodes.php
- admin/cms.php
- admin/db.php
- admin/forums.php
- admin/frames.php
- admin/groups.php
- admin/language.php
- admin/members.php
- admin/misc.php
- admin/posts.php
- admin/settings.php
- admin/start.php
- classes/cache/smileys.inc.php
- classes/cache/version_check.inc.php
- classes/geshi/bash.php
- classes/geshi/php.php
- classes/geshi/thinbasic.php
- classes/geshi/xml.php
- classes/geshi/bnf.php
- classes/geshi/io.php
- classes/geshi/mirc.php
- classes/graphic/class.veriword.php
- classes/magpie_rss/rss_fetch.inc.php
- classes/class.bbcode.php
- classes/class.cache.php
- classes/class.charts.php
- classes/class.docoutput.php
- classes/class.geshi.php
- classes/class.gpc.php
- classes/class.language.php
- classes/class.permissions.php
- classes/class.phpconfig.php
- classes/class.template.php
- classes/class.upload.php
- classes/function.global.php
- classes/function.phpcore.php
- classes/function.viscacha_frontend.php
- data/group_fields.php
- templates/editor.js
- templates/global.js
- templates/menu.js
- addreply.php
- admin.php
- attachments.php
- edit.php
- editprofile.php
- forum.php
- images.php
- log.php
- manageforum.php
- managemembers.php
- managetopic.php
- members.php
- misc.php
- newtopic.php
- pm.php
- popup.php
- portal.php
- profile.php
- register.php
- search.php
- showtopic.php
- team.php

Upload the following files if the plugin is installed (maybe it is in another directory when you have reinstalled the plugin):
- modules/14/birthday.php
- modules/3/config.ini
- modules/3/latestnews.php
- modules/4/config.ini
- modules/4/newsfeed.php
- modules/8/printword.php

Please update all the following files in each image pack: (* = ID of image pack)
- images/*/bbcodes/tt.gif
- images/*/mquote.gif
- images/*/no_mquote.gif
- images/*/offline.gif
- images/*/online.gif

Many changes were made in the template files. (* = ID of language pack)
Please update all the following files in each template pack:
- templates/*/admin/topic/post_merge.html
- templates/*/admin/topic/vote_edit.html
- templates/*/admin/benchmark.html
- templates/*/edit/edit.html
- templates/*/editprofile/about.html
- templates/*/editprofile/signature.html
- templates/*/log/login.html
- templates/*/main/bbhtml.html
- templates/*/main/error.html
- templates/*/main/ok.html
- templates/*/members/index.html
- templates/*/members/index_bit.html
- templates/*/misc/wwo_bit.html
- templates/*/modules/16/login.html
- templates/*/newtopic/index.html
- templates/*/newtopic/startvote.html
- templates/*/pm/new.html
- templates/*/pm/show.html
- templates/*/popup/showpost.html
- templates/*/register/register.html
- templates/*/register/resend.html
- templates/*/showtopic/index_bit.html
- templates/*/team/index.html
- templates/*/team/moderator_bit.html
- templates/*/addreply.html
- templates/*/categories.html
- templates/*/footer.html
- templates/*/menu_noscript.html
- templates/*/offline.html

Either overwrite file design/*/ie.css in each designs-folder or add to each file manually: (* = ID of design pack)

#content {
	width: 603px;
}
.border {
	width: 100%;
}


Either overwrite file design/*/standard.css in each designs-folder or compare the files and make the changes manually.
The following classes/definitions have been changed:
- h1
- h1 a, h1 a:hover
- .breadcrumb
- #blank {
- #content
- .bbody ul, .bbody ol, .tbody ul, .tbody ol
- .border
- .profiledata br
- .profiledata em br
- .profiledata em
- .bb_inlinecode
- .bb_quote
- .bb_quote blockquote
- .bb_ot span
- .bb_edit ins

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