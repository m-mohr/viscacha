########################################
# Readme for Viscacha 0.8.1            #
########################################

== Table of Contents ==
1.	Installation
2.	Update Viscacha
3.	System requirements
4.	Contact


== 1 == Installation ==

Warning: Since 0.8 RC7 we provide separate packages for installation
         and update. Please use the package which contains "install"
         in the name for an installation only.

1. Download the installation package from the Viscacha homepage!
2. Upload all files per ftp onto your server.
3. Call the "install/" directory in the Viscacha-root-directory and
   follow the steps.
4. You have a "fresh" Viscacha-Installation on your server.

Note: More information on how to set up the CHMODs you can get while
      installing the application. In the ACP you can also see a more
      detailed list of the required CHMODs.


== 2 == Update ==

Warning: Since 0.8 RC7 we provide separate packages for installation
         and update. Please use the package which contains "update"
         in the name for an update only.

1. Make a complete backup of your data (FTP + MySQL)!
2. Download the update package from the Viscacha homepage!
3. Upload the install/ directory
4. Execute the update script (Call the "install/" directory).
5. Follow all the steps while the update script is running.
6. After the update is ready and you are back in your Admin Control
   Panel, please check for updates of your installed packages!

Note: You can only upgrade from Viscacha 0.8 Gold. To update from a
      prior version you have to upgrade to Viscacha 0.8 Gold first.


== System requirements ==

Minimum system requirements:
 - PHP Version: 5.0.0 and above
               (PHP 4 is unsupported as of 0.8 RC7)
 - PHP-Extensions: mysql or mysqli, pcre, gd, zlib
 - MySQL Version: 4.0 and above

Recommended system requirements:
 - PHP Version: 5.2.0 and above, 5.3.0 on Windows
 - PHP-Extensions: mysql or mysqli, pcre, gd, zlib, xml, mime_magic,
                   mbstring, sockets, xdiff, ftp
 - MySQL Version: 5.0 and above (Strict mode off)

If you are testing Viscacha, please give me some feedback how Viscacha
worked, which errors occurred and which server configuration was used.

Following information are useful for us:
- Operating system (of the server)
- Server software and version
- E-mail-server (SMTP, Sendmail, PHP's mail() function)
- MySQL version (strict mode enabled?)
- PHP version and configuration (e.q. through phpinfo() page)


== Contact ==

Please contact us only through our support forums on
http://www.viscacha.org!

Bugtracker and ToDo List: http://bugs.viscacha.org