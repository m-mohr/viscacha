########################################
# Readme for Viscacha 0.8 RC 5         #
########################################

== Table of Contents ==
1.   Installation
2.   Update Viscacha
2.1 Stylesheet changes
3.   System requirements
4.   Contact


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

Note: You can only update the versions 0.8 RC4 and 0.8 RC4 PL1.

== 2.1 == Stylesheet Changes ==
This changes are for later design updates.
This steps will also be shown in the update script!

== 2.1.1 == designs/*/ie.css ==
Search and delete:
------------------------------------------------------------------------
#popup_bbsmileys {
	overflow: scroll;
}
------------------------------------------------------------------------

Search and delete:
------------------------------------------------------------------------
* html .tables_bbsmileys td {
	border: 0px;
}
------------------------------------------------------------------------

Search and delete:
------------------------------------------------------------------------
* html #popup_bbsmileys {
	height: 200px;
}
------------------------------------------------------------------------

Add at the end of the file:
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

== 2.1.2 == designs/*/print.css ==
Search:
------------------------------------------------------------------------
.bb_blockcode_header {
	display: block;
}
------------------------------------------------------------------------
Replace with:
------------------------------------------------------------------------
.bb_blockcode_options {
	display: none;
}
------------------------------------------------------------------------

Search and delete:
------------------------------------------------------------------------
.bb_blockcode td {
	font-family: Courier New, monospace;
	font-size: 11px;
	line-Height: 13px;
	white-space: nowrap;
	vertical-align: top;
}
------------------------------------------------------------------------

== 2.1.3 == designs/*/standard.css ==
Search:
------------------------------------------------------------------------
	padding: auto 2px auto 2px;
------------------------------------------------------------------------
Replace with:
------------------------------------------------------------------------
	padding: 1px 2px 1px 2px;
------------------------------------------------------------------------

Add at the end of the file:
------------------------------------------------------------------------
/* Document Missing Language Notice Box */
.notice_box {
	border: 1px solid #839FBC;
	background-color: #ffffff;
	padding: 4px;
	font-size: 8pt;
}
.notice_box strong {
	float: left;
	display: block;
	width: 70px;
	text-align: center;
	color: maroon;
}
.notice_box span {
	margin-left: 75px;
	display: block;
}
/* Pagination */
.pagination .page_number {
	border: 1px solid #B6BCC1;
	color: #24486C;
	padding: 0px 2px;
	margin: 0px 2px;
	text-decoration: none;
}
.pagination .page_number_current, .pagination .page_number:hover {
	border: 1px solid #336699;
	background-color: #336699;
	color: #FFFFFF;
	padding: 0px 2px;
	margin: 0px 2px;
}
.pagination .page_more {
	margin: 0px 2px;
}
.pagination .page_separator {
	display: none;
}
/* BB-Code-Editor */
.editor_textarea_outer {
	border: 1px solid #888888;
	width: 100%;
	border-collapse: collapse;
}
.editor_textarea_outer td {
	padding: 4px;
}
.editor_toolbar {
	border-bottom: 1px solid #C9C9C9;
	background-color: #F4F4F4;
	height: 28px;
}
.editor_toolbar_dropdown {
	border: 1px solid #F4F4F4;
	padding: 2px;
	color: #000000;
	font-size: 9pt;
}
.editor_toolbar_dropdown:hover {
	border: 1px solid #999999;
}
.editor_toolbar_smiley {
	border: 1px solid #F4F4F4;
	padding: 2px;
}
.editor_toolbar_smiley_on {
	border: 1px solid #999999;
	padding: 2px;
}
.editor_toolbar_button {
	width: 20px;
	height: 20px;
	border: 1px solid #F4F4F4;
}
.editor_toolbar_button_on {
	width: 20px;
	height: 20px;
	border: 1px solid #999999;
}
.editor_textarea_inner {
	background-color: #FFFFFF;
	font-size: 9pt;
	border-width: 0px;
	width: 100%;
	overflow: auto;
}
.editor_statusbar {
	font-size: 8pt;
	background-color: #F4F4F4;
	border-top: 1px solid #C9C9C9;
}
.editor_statusbar a {
	color: #000000;
	text-decoration: none;
}
.editor_statusbar a:hover {
	text-decoration: underline;
}

.editor_textarea_outer .popup {
	border: 1px solid #888888;
	max-height: 250px;
}
.editor_textarea_outer .popup strong {
	text-align: center;
	color: #000000;
	background-color: #eeeeee;
	border-width: 0px;
	border-bottom: 1px solid #888888;
}
.editor_textarea_outer .popup li {
	border-width: 0px;
	padding: 2px 3px 1px 3px;
}
.editor_textarea_outer .popup li a, .editor_textarea_outer .popup_line {
	color: #000000;
	text-decoration: none;
	border: 1px solid #ffffff;
}
.editor_textarea_outer .popup li a:hover, .editor_textarea_outer .popup_line:hover {
	background-color: #eeeeee;
	border: 1px solid #cccccc;
}
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
.bbtable {
	background-color: #ffffff;
	padding: 4px;
}
.bbtable input {
	width: 35px;
	float: left;
	background-color: #ffffff;
	border: 1px solid #888888;
	text-align: center;
	margin-right: 5px;
}
.bbsmileys img {
	margin: 2px 8px 2px 4px;
}
------------------------------------------------------------------------

Delete:
------------------------------------------------------------------------
/* Smiley Interface */
#menu_bbsmileys {
	display: block;
	text-align: center;
	width: 140px;
}
#popup_bbsmileys {
	max-height: 200px;
	width: 255px;
}
.tables_bbsmileys {
	width: 100%;
	border-collapse: collapse;
	margin-bottom: 0px;
}
.tables_bbsmileys td {
	border: 1px solid #839FBC;
	border-width: 1px 0px 0px 1px;
	padding: 3px;
}
.bbsmileys {
	margin-bottom: 5px;
	width: 140px
}
.bbsmileys td {
	padding: 3px;
}
/* BB-Code Interface */
#menu_bbcolor, #menu_bbsize, #menu_bbhx, #menu_help, #menu_bbalign {
	font-size: 9pt;
	font-weight: bold;
}
#codebuttons a, #menu_bbcolor, #menu_bbsize, #menu_bbhx, #menu_help, #menu_bbalign {
	border: 1px solid #336699;
	background-color: #F5F8FA;
}
#codebuttons br {
	clear: left;
}
#codebuttons a {
	height: 18px;
	float: left;
	display: block;
	padding: 2px;
	margin: 1px;
	vertical-align: middle;
}
#codebuttons a:hover {
	background-color: #BCCADA;
}
#codebuttons img {
	vertical-align: middle;
}
.bbcolor {
	line-height: 10px;
	font-size: 10px;
}
.bbcolor span {
	width: 10px;
	height: 10px;
	display: block;
	float: left;
	cursor: pointer;
}

/* BB-Code Ausgabe */
.highlightcode a {
	border-bottom: 1px dotted #336699;
	/* target-new: tab; */
}
.highlightcode a:hover {
	border-bottom: 1px solid #336699;
}
------------------------------------------------------------------------

Delete:
------------------------------------------------------------------------
/* Spellchecker */
.disabled {
	font-size: 11px;
	background-color: #F5F8FA;
	border: 1px solid #336699;
	color: #cccccc;
}
.spellcheckbutton {
	width: 90px;
}
.spellcheckinput {
	width: 180px;
}
/* Higlighting for bad spelled words */
.mistake {
	border: 0;
	border-bottom: 1px dotted red;
	background-color: #E1CCCD;
	text-align: center;
	font-family: monospace;
	font-size: 10pt;
}
.transparent {
	border: 0;
	background-color: #ffffff;
	text-align: center;
	font-family: monospace;
	font-size: 10pt;
	border-bottom: 1px dotted red
}
.spellchecktext {
	font-family: monospace;
	font-size: 10pt;
}
------------------------------------------------------------------------

Search:
------------------------------------------------------------------------
.bb_blockcode_header {
	display: block;
}
.bb_blockcode {
	border: 1px solid #839FBC;
	background-color: #F5F8FA;
	padding: 2px;
	margin: 0px;
	margin-left: 10px;
	overflow: auto;
	max-height: 400px;
	min-height: 50px;
	width: 550px;
}
.bb_blockcode td {
	font-family: "Courier New", monospace;
	font-size: 11px;
	line-Height: 13px;
	white-space: nowrap;
	vertical-align: top;
}
------------------------------------------------------------------------
Replace with:
------------------------------------------------------------------------
.bb_blockcode {
	border: 1px solid #839FBC;
	background-color: #F5F8FA;
	padding: 4px;
	margin-left: 15px;
	overflow: auto;
	max-height: 400px;
	min-height: 50px;
	width: 560px;
}
.bb_blockcode li {
	white-space: pre;
	font-family: 'Courier New', monospace;
	font-weight: normal;
	font-style: normal;
	margin-left: 4px;
}
.bb_blockcode a {
	border-bottom: 1px dotted #000000;
}
.bb_blockcode a:hover {
	border-bottom: 1px solid #000000;
}
.bb_blockcode_options {
	float: right;
	margin: 0px 40px -1px 0px;
	border: 1px solid #839FBC;
	border-bottom: 0px;
	background-color: #F5F8FA;
	padding: 1px 5px 1px 5px;
	font-size: 8pt;
}
.bb_blockcode_options:hover, .bb_blockcode_options:focus {
	background-color: #E1E8EF;
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