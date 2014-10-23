<div class="bbody">
<p>
Before we start the automatic update, you have to read the manual update instructions.
Please follow the steps and do the tasks.
More Information:
<?php if (file_exists('../_docs/readme.txt')) { ?>
<a href="../_docs/readme.txt" target="_blank">_docs/readme.txt</a>
<?php } else { ?>
_docs/readme.txt
<?php } ?>
</p>
<p>
<strong>Update instructions:</strong><br />
<textarea class="codearea">First make a complete backup of your (old) data!

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
again, please check for Updates of your installed Packages!</textarea>
</p>
</div>
<div class="bfoot center"><input type="submit" value="Continue" /></div>