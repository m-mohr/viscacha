<div class="bbody">
<p>
Before we start the automatic update (file updates, updating CHMODs), you have to read the manual update instructions.
Please follow the steps and do the tasks.
More Information:
<?php if (file_exists('_docs/readme.txt')) { ?>
<a href="_docs/readme.txt" target="_blank">_docs/readme.txt</a>
<?php } else { ?>
_docs/readme.txt
<?php } ?>
</p>
<p>
<strong>Update instructions:</strong><br />
<ol class="upd_instr">
<li>Make sure you have a <b>complete backup of your data</b> (FTP + MySQL)!</li>
<li>You should have specified the ftp data in your Admin Control Panel</b> otherwise CHMODs can't be set correctly!</li>
<li>Open the file <b>designs/*/ie.css</b>:<br />
<em>You have to apply the following changes (for all CSS files) to all your installed designs. * is a placeholder for a Design-ID (1,2,3,...). The CSS definitions can vary depending on your modifications to the styles.</em>
<ol>
<li>
Search and delete:<br />
<code>#popup_bbsmileys {<br />
&nbsp;&nbsp;&nbsp;&nbsp;overflow: scroll;<br />
}</code>
</li>
<li>
Search and delete:<br />
<code>* html #popup_bbsmileys {<br />
&nbsp;&nbsp;&nbsp;&nbsp;height: 200px;<br />
}</code>
</li>
<li>
Add at the end of the file:<br />
<code>* html .editor_textarea_outer .popup {<br />
&nbsp;&nbsp;&nbsp;&nbsp;border-top-width: 0px;<br />
}<br />
* html .editor_textarea_outer .popup strong {<br />
&nbsp;&nbsp;&nbsp;&nbsp;border-width: 0px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;border-top: 1px solid #888888;<br />
}<br />
* html .editor_textarea_outer .popup li {<br />
&nbsp;&nbsp;&nbsp;&nbsp;border-top: 1px solid #c4c4c4;<br />
}</code>
</li>
</ol>
</li>
<li>Open the file <b>designs/*/print.css</b>:
<ol>
<li>
Search:<br />
<code>.bb_blockcode_header {<br />
&nbsp;&nbsp;&nbsp;&nbsp;display: block;<br />
}</code>
Replace with:<br />
<code>.bb_blockcode_options {<br />
&nbsp;&nbsp;&nbsp;&nbsp;display: none;<br />
}</code>
</li>
<li>
Search and delete:<br />
<code>.bb_blockcode td {<br />
&nbsp;&nbsp;&nbsp;&nbsp;font-family: Courier New, monospace;<br />
&nbsp;&nbsp;&nbsp;&nbsp;font-size: 11px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;line-Height: 13px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;white-space: nowrap;<br />
&nbsp;&nbsp;&nbsp;&nbsp;vertical-align: top;<br />
}</code>
</li>
</ol>
</li>
<li>Open the file <b>designs/*/standard.css</b>:
<ol>
<li>
Search:<br />
<code>&nbsp;&nbsp;&nbsp;&nbsp;padding: auto 2px auto 2px;</code>
Replace with:<br />
<code>&nbsp;&nbsp;&nbsp;&nbsp;padding: 1px 2px 1px 2px;</code>
</li>
<li>
Search:<br />
<code>.bb_blockcode_header {<br />
&nbsp;&nbsp;&nbsp;&nbsp;display: block;<br />
}<br />
.bb_blockcode {<br />
&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #839FBC;<br />
&nbsp;&nbsp;&nbsp;&nbsp;background-color: #F5F8FA;<br />
&nbsp;&nbsp;&nbsp;&nbsp;padding: 2px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;margin: 0px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;margin-left: 10px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;overflow: auto;<br />
&nbsp;&nbsp;&nbsp;&nbsp;max-height: 400px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;min-height: 50px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;width: 550px;<br />
}<br />
.bb_blockcode td {<br />
&nbsp;&nbsp;&nbsp;&nbsp;font-family: "Courier New", monospace;<br />
&nbsp;&nbsp;&nbsp;&nbsp;font-size: 11px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;line-Height: 13px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;white-space: nowrap;<br />
&nbsp;&nbsp;&nbsp;&nbsp;vertical-align: top;<br />
}</code>
Replace with:<br />
<code>.bb_blockcode {<br />
&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #839FBC;<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #F5F8FA;<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 4px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;margin-left: 15px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;overflow: auto;<br />

&nbsp;&nbsp;&nbsp;&nbsp;max-height: 400px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;min-height: 50px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 560px;<br />

}<br />

.bb_blockcode li {<br />

&nbsp;&nbsp;&nbsp;&nbsp;white-space: pre;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-family: 'Courier New', monospace;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-weight: normal;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-style: normal;<br />

&nbsp;&nbsp;&nbsp;&nbsp;margin-left: 4px;<br />

}<br />

.bb_blockcode a {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-bottom: 1px dotted #000000;<br />

}<br />

.bb_blockcode a:hover {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-bottom: 1px solid #000000;<br />

}<br />

.bb_blockcode_options {<br />

&nbsp;&nbsp;&nbsp;&nbsp;float: right;<br />

&nbsp;&nbsp;&nbsp;&nbsp;margin: 0px 40px -1px 0px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #839FBC;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-bottom: 0px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #F5F8FA;<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 1px 5px 1px 5px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-size: 8pt;<br />

}<br />

.bb_blockcode_options:hover, .bb_blockcode_options:focus {<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #E1E8EF;<br />

}</code>
</li>
<li>
Search and delete:<br />
<code>/* Smiley Interface */<br />

#menu_bbsmileys {<br />

&nbsp;&nbsp;&nbsp;&nbsp;display: block;<br />

&nbsp;&nbsp;&nbsp;&nbsp;text-align: center;<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 140px;<br />

}<br />

#popup_bbsmileys {<br />

&nbsp;&nbsp;&nbsp;&nbsp;max-height: 200px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 255px;<br />

}<br />

.tables_bbsmileys {<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 100%;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-collapse: collapse;<br />

&nbsp;&nbsp;&nbsp;&nbsp;margin-bottom: 0px;<br />

}<br />

.tables_bbsmileys td {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #839FBC;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-width: 1px 0px 0px 1px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 3px;<br />

}<br />

.bbsmileys {<br />

&nbsp;&nbsp;&nbsp;&nbsp;margin-bottom: 5px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 140px<br />

}<br />

.bbsmileys td {<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 3px;<br />

}<br />

/* BB-Code Interface */<br />

#menu_bbcolor, #menu_bbsize, #menu_bbhx, #menu_help, #menu_bbalign {<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-size: 9pt;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-weight: bold;<br />

}<br />

#codebuttons a, #menu_bbcolor, #menu_bbsize, #menu_bbhx, #menu_help, #menu_bbalign {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #336699;<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #F5F8FA;<br />

}<br />

#codebuttons br {<br />

&nbsp;&nbsp;&nbsp;&nbsp;clear: left;<br />

}<br />

#codebuttons a {<br />

&nbsp;&nbsp;&nbsp;&nbsp;height: 18px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;float: left;<br />

&nbsp;&nbsp;&nbsp;&nbsp;display: block;<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 2px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;margin: 1px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;vertical-align: middle;<br />

}<br />

#codebuttons a:hover {<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #BCCADA;<br />

}<br />

#codebuttons img {<br />

&nbsp;&nbsp;&nbsp;&nbsp;vertical-align: middle;<br />

}<br />

.bbcolor {<br />

&nbsp;&nbsp;&nbsp;&nbsp;line-height: 10px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-size: 10px;<br />

}<br />

.bbcolor span {<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 10px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;height: 10px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;display: block;<br />

&nbsp;&nbsp;&nbsp;&nbsp;float: left;<br />

&nbsp;&nbsp;&nbsp;&nbsp;cursor: pointer;<br />

}<br />

<br />

/* BB-Code Ausgabe */<br />

.highlightcode a {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-bottom: 1px dotted #336699;<br />

&nbsp;&nbsp;&nbsp;&nbsp;/* target-new: tab; */<br />

}<br />

.highlightcode a:hover {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-bottom: 1px solid #336699;<br />

}</code>
</li>
<li>
Search and delete:<br />
<code>/* Spellchecker */<br />

.disabled {<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-size: 11px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #F5F8FA;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #336699;<br />

&nbsp;&nbsp;&nbsp;&nbsp;color: #cccccc;<br />

}<br />

.spellcheckbutton {<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 90px;<br />

}<br />

.spellcheckinput {<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 180px;<br />

}<br />

/* Higlighting for bad spelled words */<br />

.mistake {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 0;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-bottom: 1px dotted red;<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #E1CCCD;<br />

&nbsp;&nbsp;&nbsp;&nbsp;text-align: center;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-family: monospace;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-size: 10pt;<br />

}<br />

.transparent {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 0;<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #ffffff;<br />

&nbsp;&nbsp;&nbsp;&nbsp;text-align: center;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-family: monospace;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-size: 10pt;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-bottom: 1px dotted red<br />

}<br />

.spellchecktext {<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-family: monospace;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-size: 10pt;<br />

}</code>
</li>
<li>
Add at the end of the file:<br />
<code>/* Document Missing Language Notice Box */<br />

.notice_box {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #839FBC;<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #ffffff;<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 4px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-size: 8pt;<br />

}<br />

.notice_box strong {<br />

&nbsp;&nbsp;&nbsp;&nbsp;float: left;<br />

&nbsp;&nbsp;&nbsp;&nbsp;display: block;<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 70px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;text-align: center;<br />

&nbsp;&nbsp;&nbsp;&nbsp;color: maroon;<br />

}<br />

.notice_box span {<br />

&nbsp;&nbsp;&nbsp;&nbsp;margin-left: 75px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;display: block;<br />

}<br />

/* Pagination */<br />

.pagination .page_number {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #B6BCC1;<br />

&nbsp;&nbsp;&nbsp;&nbsp;color: #24486C;<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 0px 2px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;margin: 0px 2px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;text-decoration: none;<br />

}<br />

.pagination .page_number_current, .pagination .page_number:hover {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #336699;<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #336699;<br />

&nbsp;&nbsp;&nbsp;&nbsp;color: #FFFFFF;<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 0px 2px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;margin: 0px 2px;<br />

}<br />

.pagination .page_more {<br />

&nbsp;&nbsp;&nbsp;&nbsp;margin: 0px 2px;<br />

}<br />

.pagination .page_separator {<br />

&nbsp;&nbsp;&nbsp;&nbsp;display: none;<br />

}<br />

/* BB-Code-Editor */<br />

.editor_textarea_outer {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #888888;<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 100%;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-collapse: collapse;<br />

}<br />

.editor_textarea_outer td {<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 4px;<br />

}<br />

.editor_toolbar {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-bottom: 1px solid #C9C9C9;<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #F4F4F4;<br />

&nbsp;&nbsp;&nbsp;&nbsp;height: 28px;<br />

}<br />

.editor_toolbar_dropdown {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #F4F4F4;<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 2px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;color: #000000;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-size: 9pt;<br />

}<br />

.editor_toolbar_dropdown:hover {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #999999;<br />

}<br />

.editor_toolbar_smiley {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #F4F4F4;<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 2px;<br />

}<br />

.editor_toolbar_smiley_on {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #999999;<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 2px;<br />

}<br />

.editor_toolbar_button {<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 20px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;height: 20px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #F4F4F4;<br />

}<br />

.editor_toolbar_button_on {<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 20px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;height: 20px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #999999;<br />

}<br />

.editor_textarea_inner {<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #FFFFFF;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-size: 9pt;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-width: 0px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 100%;<br />

&nbsp;&nbsp;&nbsp;&nbsp;overflow: auto;<br />

&nbsp;&nbsp;&nbsp;&nbsp;margin: -4px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 4px;<br />

}<br />

.editor_statusbar {<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-size: 8pt;<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #F4F4F4;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-top: 1px solid #C9C9C9;<br />

}<br />

.editor_statusbar a {<br />

&nbsp;&nbsp;&nbsp;&nbsp;color: #000000;<br />

&nbsp;&nbsp;&nbsp;&nbsp;text-decoration: none;<br />

}<br />

.editor_statusbar a:hover {<br />

&nbsp;&nbsp;&nbsp;&nbsp;text-decoration: underline;<br />

}<br />

<br />

.editor_textarea_outer .popup {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #888888;<br />

&nbsp;&nbsp;&nbsp;&nbsp;max-height: 250px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 200px;<br />

}<br />

.editor_textarea_outer .popup ul {<br />

&nbsp;&nbsp;&nbsp;&nbsp;list-style-type: none;<br />

}<br />

.editor_textarea_outer .popup strong {<br />

&nbsp;&nbsp;&nbsp;&nbsp;text-align: center;<br />

&nbsp;&nbsp;&nbsp;&nbsp;color: #000000;<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #eeeeee;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-width: 0px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-bottom: 1px solid #888888;<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 2px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-size: 9pt;<br />

}<br />

.editor_textarea_outer .popup li {<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-width: 0px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 2px 3px 1px 3px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #ffffff;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-size: 9pt;<br />

}<br />

.editor_textarea_outer .popup li a, .editor_textarea_outer .popup_line {<br />

&nbsp;&nbsp;&nbsp;&nbsp;color: #000000;<br />

&nbsp;&nbsp;&nbsp;&nbsp;text-decoration: none;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #ffffff;<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #ffffff;<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 2px;<br />

}<br />

.editor_textarea_outer .popup li a:hover, .editor_textarea_outer .popup_line:hover {<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #eeeeee;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #cccccc;<br />

&nbsp;&nbsp;&nbsp;&nbsp;color: #000000;<br />

}<br />

.bbcolor {<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 10px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #ffffff;<br />

&nbsp;&nbsp;&nbsp;&nbsp;line-height: 13px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;font-size: 13px;<br />

}<br />

.bbcolor span {<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 10px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;height: 13px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;display: block;<br />

&nbsp;&nbsp;&nbsp;&nbsp;float: left;<br />

&nbsp;&nbsp;&nbsp;&nbsp;cursor: pointer;<br />

}<br />

.bbcolor img {<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 10px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;height: 13px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border-width: 0px;<br />

}<br />

.bbcolor img:hover {<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 8px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;height: 11px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #ffffff;<br />

}<br />

.bbtable {<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #ffffff;<br />

&nbsp;&nbsp;&nbsp;&nbsp;padding: 4px;<br />

}<br />

.bbtable input {<br />

&nbsp;&nbsp;&nbsp;&nbsp;width: 35px;<br />

&nbsp;&nbsp;&nbsp;&nbsp;float: left;<br />

&nbsp;&nbsp;&nbsp;&nbsp;background-color: #ffffff;<br />

&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #888888;<br />

&nbsp;&nbsp;&nbsp;&nbsp;text-align: center;<br />

&nbsp;&nbsp;&nbsp;&nbsp;margin-right: 5px;<br />

}<br />

.bbsmileys img {<br />

&nbsp;&nbsp;&nbsp;&nbsp;margin: 2px 8px 2px 4px;<br />

}</code>
</li>
</ol>
</li>
<li>After the update <b>check for updates of your installed packages</b> in the ACP!</li>
</ol>
</p>
</div>
<div class="bfoot center"><input type="submit" value="Continue" /></div>