########################################
# Readme for Viscacha 0.8 RC 6         #
########################################

== Table of Contents ==
1.	Installation
2.	Update Viscacha
2.1	Stylesheet changes
3.	System requirements
4.	Contact


== 1 == Installation ==

1. Upload all files per ftp onto your server.
   Note: You do not need to upload the folder install/files/ for the
         installation! The folder is only required for the update.
2. Call the "install/" directory in the Viscacha-root-directory and
   follow the steps.
3. You have a "fresh" Viscacha-Installation on your server.

Note: More information on how to set up the CHMODs you can get while
      installing the application. In the ACP you can also see a more
      detailed list of the required CHMODs.


== 2 == Update ==

1. Make a complete backup of your data (FTP + MySQL)!
2. Upload the install/ directory
3. Call / Execute the update script (Call the "install/" directory).
4. Follow all the steps while the update script is running.
5. After the update is ready and you are back in your Admin Control
   Panel, please check for Updates of your installed Packages!

Note: You can only update from Viscacha 0.8 RC5.

== 2.1 == Stylesheet Changes ==
This changes are for later design updates.
This steps will also be shown in the update script!

You have to apply the following changes (for all CSS files) to all
your installed designs. * is a placeholder for a Design-ID (1,2,3).
The CSS definitions can vary depending on your modifications to the
styles.

== Change in file designs/*/ie.css ==

1. Search and delete:
------------------------------------------------------------------------
* html .editor_textarea_outer .popup {
	border-top-width: 0px;
}
* html .editor_textarea_outer .popup strong {
	border-width: 0px;
	border-top: 1px solid #888888;
}
* html .editor_textarea_outer .popup li {
	border-top: 1px solid #c4c4c4;
}
------------------------------------------------------------------------

2. Add at the end of the file:
------------------------------------------------------------------------
* html .editor_textarea_outer .popup {
	height: expression( this.scrollHeight > 249 ? "250px" : "auto" );
	overflow-x: expression( this.scrollHeight > 249 && this.scrollWidth <= 200 ? "hidden" : "auto" );
}
* html .editor_textarea_outer .popup strong {
	width: 196px;
}
* html .editor_textarea_outer .popup li {
	width: 194px;
}
.bb_blockcode li {
	white-space: normal;
}
------------------------------------------------------------------------


== Changes in file designs/*/standard.css ==

1. Search:
------------------------------------------------------------------------
.bb_blockcode li {
	white-space: pre;
	font-family: 'Courier New', monospace;
	font-weight: normal;
	font-style: normal;
	margin-left: 4px;
}
------------------------------------------------------------------------

Replace with:
------------------------------------------------------------------------
.bb_blockcode * {
	font-family: 'Courier New', monospace;
}
.bb_blockcode li {
	margin-left: 12px;
	white-space: pre;
}
------------------------------------------------------------------------

2. Search:
------------------------------------------------------------------------
.editor_textarea_inner {
	background-color: #FFFFFF;
	font-size: 9pt;
	border-width: 0px;
	width: 100%;
	overflow: auto;
	margin: -4px;
	padding: 4px;
}
------------------------------------------------------------------------

In this part of the stylesheet delete:
------------------------------------------------------------------------
	overflow: auto;
------------------------------------------------------------------------

3. Search:
------------------------------------------------------------------------
.editor_textarea_outer .popup {
------------------------------------------------------------------------

Add below:
------------------------------------------------------------------------
	overflow: auto;
------------------------------------------------------------------------

4. Search:
------------------------------------------------------------------------
.bbcolor {
	padding: 10px;
	background-color: #ffffff;
	line-height: 13px;
	font-size: 13px;
}
.bbcolor span {
	width: 10px;
	height: 13px;
	display: block;
	float: left;
	cursor: pointer;
}
.bbcolor img {
	width: 10px;
	height: 13px;
	border-width: 0px;
}
.bbcolor img:hover {
	width: 8px;
	height: 11px;
	border: 1px solid #ffffff;
}
------------------------------------------------------------------------

Replace with:
------------------------------------------------------------------------
.bbcolor {
	padding: 10px;
	background-color: #ffffff;
	line-height: 12px;
	font-size: 12px;
}
.bbcolor img {
	width: 8px;
	height: 10px;
	display: block;
	float: left;
	cursor: pointer;
	border-width: 1px;
	border-style: solid;
}
------------------------------------------------------------------------


== System requirements ==

Minimum system requirements:
 - PHP Version: 4.3.0 and above
 - PHP-Extensions: mysql or mysqli, pcre, gd, zlib
 - MySQL Version: 4.0 and above

Normal system requirements:
 - PHP Version: 5.0.0 and above
 - PHP-Extensions: mysql or mysqli, pcre, gd, zlib, xml, mime_magic
 - MySQL Version: 4.1 and above

Optimal system requirements:
 - PHP Version: 5.2.0 and above
 - PHP-Extensions: mysql or mysqli, pcre, gd, zlib, xml, mime_magic,
                   mbstring, sockets, xdiff
 - MySQL Version: 5.0 and above (Strict mode off)

If you are testing Viscacha, please give me some feedback how Viscacha
worked, which errors occurred and which server configuration was used.

Following information are useful for me:
- Operating system (of the server)
- Server software and version
- E-mail-server (SMTP, Sendmail, PHP's mail() function)
- MySQL version (strict mode enabled?)
- PHP version
- Status of the extensions: mysql, mysqli, pcre, gd, zlib, xml,
                            mime_magic, mbstring, sockets, xdiff
- The following settings in the file php.ini:
  - safe_mode
  - magic_quotes_gpc
  - register_globals
  - open_basedir


== Contact ==

Please contact us only through our support forums on
http://www.viscacha.org!

Bugtracker and ToDo List: http://bugs.viscacha.org