/********************************************************************
 * openWYSIWYG v1.47 Copyright (c) 2006 openWebWare.com
 * Contact us at devs@openwebware.com
 * This copyright notice MUST stay intact for use.
 *
 * $Id: wysiwyg.js,v 1.22 2007/09/08 21:45:57 xhaggi Exp $
 * $Revision: 1.22 $
 *
 * An open source WYSIWYG editor for use in web based applications.
 * For full source code and docs, visit http://www.openwebware.com
 *
 * This library is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation; either version 2.1 of the License, or
 * (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
 * License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with this library; if not, write to the Free Software Foundation, Inc., 59
 * Temple Place, Suite 330, Boston, MA 02111-1307 USA
 ********************************************************************/
var WYSIWYG = {

	/**
	 * Settings class, holds all customizeable properties
	 */
	Settings: function() {

		// Images Directory
		this.ImagesDir = "templates/editor/images/";

		// Popups Directory
		this.PopupsDir = "admin.php?action=cms&job=doc_";

		// Default WYSIWYG width and height (use px or %)
		this.Width = "95%";
		this.Height = "350px";

		// Default stylesheet of the WYSIWYG editor window
		this.DefaultStyle = "font-family: \"Trebuchet MS\", Verdana, Arial, Helvetica, sans-serif; font-size: 9pt; background-color: #FFFFFF; white-space: normal;";

		// Stylesheet if editor is disabled
		this.DisabledStyle = "font-family: \"Trebuchet MS\", Verdana, Arial, Helvetica, sans-serif; font-size: 9pt; background-color: #eeeeee; white-space: normal; color: #555555;";

		// Confirmation message if you strip any HTML added by word
		this.RemoveFormatConfMessage = lng['wysiwyg_clean_word'];

		// Nofication if browser is not supported by openWYSIWYG, leave it blank for no message output.
		this.NoValidBrowserMessage = lng['wysiwyg_not_compatible'];

		// Anchor path to strip, leave it blank to ignore
		// or define auto to strip the path where the editor is placed
		// (only IE)
		this.AnchorPathToStrip = "auto";

		// Image path to strip, leave it blank to ignore
		// or define auto to strip the path where the editor is placed
		// (only IE)
		this.ImagePathToStrip = "auto";

		// Enabled the status bar update. Within the status bar
		// node tree of the actually selected element will build
		this.StatusBarEnabled = true;

		// If enabled than the capability of the IE inserting line breaks will be inverted.
		// Normal: ENTER = <p> , SHIFT + ENTER = <br>
		// Inverted: ENTER = <br>, SHIFT + ENTER = <p>
		this.InvertIELineBreaks = false;

		// Replace line breaks with <br> tags
		this.ReplaceLineBreaks = false;

		// Page that opened the WYSIWYG (Used for the return command)
		this.Opener = "#";

		// Insert image implementation
		this.ImagePopupFile = "admin.php?action=cms&job=doc_insert_image&";
		this.ImagePopupWidth = 730;
		this.ImagePopupHeight = 410;

		// Holds the available buttons displayed
		// on the toolbar of the editor
		this.Toolbar = new Array();
		this.Toolbar[0] = new Array(
			"font",
			"fontsize",
			"headings",
			"bold",
			"italic",
			"underline",
			"strikethrough",
			"seperator",
			"justifyfull",
			"justifyleft",
			"justifycenter",
			"justifyright",
			"seperator",
			"unorderedlist",
			"orderedlist",
			"seperator",
			"outdent",
			"indent",
			"seperator",
			"subscript",
			"superscript"
		);
		this.Toolbar[1] = new Array(
			"forecolor",
			"backcolor",
			"seperator",
			"inserthr",
			"insertimage",
			"createlink",
			"inserttable",
			"seperator",
			"cut",
			"copy",
			"paste",
			"removeformat",
			"removenode",
			"seperator",
			"undo",
			"redo",
			"seperator",
			"viewSource"/*,
			"maximize" */
		);

		// DropDowns
		this.DropDowns = new Array();
		// Fonts
		this.DropDowns['font'] = {
			id: "fonts",
			command: "FontName",
			label: "<font style=\"font-family:{value};font-size:11px;\">{value}</font>",
			width: "90px",
			elements: new Array(
				"Arial",
				"Sans Serif",
				"Monospace",
				"Tahoma",
				"Verdana",
				"Courier New",
				"Georgia",
				"Times New Roman",
				"Bitstream Vera Sans",
				"Helvetica",
				"Comic Sans MS"
			)
		};
		// Font sizes
		this.DropDowns['fontsize'] = {
			id: "fontsizes",
			command: "FontSize",
			label: "<font size=\"{value}\">Size {value}</font>",
			width: "54px",
			elements: new Array(
				"1",
				"2",
				"3",
				"4",
				"5",
				"6"
			)
		};
		// Headings
		this.DropDowns['headings'] = {
			id: "headings",
			command: "FormatBlock",
			label: "<{value} style=\"margin:0px;text-decoration:none;font-family:sans-serif;\">{value}</{value}>",
			width: "74px",
			elements: new Array(
				"H2",
				"H3",
				"H4",
				"H5",
				"H6"
			)
		};

		// Add the given element to the defined toolbar
		// on the defined position
		this.addToolbarElement = function(element, toolbar, position) {
			if(element != "seperator") {this.removeToolbarElement(element);}
			if(this.Toolbar[toolbar-1] == null) {
				this.Toolbar[toolbar-1] = new Array();
			}
			this.Toolbar[toolbar-1].splice(position+1, 1, element);
		};

		// Remove an element from the toolbar
		this.removeToolbarElement = function(element) {
			if(element == "seperator") {return;} // do not remove seperators
			for(var i=0;i<this.Toolbar.length;i++) {
				if(this.Toolbar[i]) {
					var toolbar = this.Toolbar[i];
					for(var j=0;j<toolbar.length;j++) {
						if(toolbar[j] != null && toolbar[j] == element) {
							this.Toolbar[i].splice(j,1);
						}
					}
				}
			}
		};

		// clear all or a given toolbar
		this.clearToolbar = function(toolbar) {
			if(typeof toolbar == "undefined") {
				this.Toolbar = new Array();
			}
			else {
				this.Toolbar[toolbar+1] = new Array();
			}
		};

	},

	// List of available block formats (not in use)
	//BlockFormats: new Array("Address", "Bulleted List", "Definition", "Definition Term", "Directory List", "Formatted", "Heading 1", "Heading 2", "Heading 3", "Heading 4", "Heading 5", "Heading 6", "Menu List", "Normal", "Numbered List"),

	// List of available actions and their respective ID and images
	ToolbarList: {
	//Name			buttonID			 	buttonTitle		 					buttonImage			 	buttonImageRollover
	"bold":			['Bold',				lng['wysiwyg_bold'],				'bold.gif',				'bold.gif'],
	"italic":		['Italic',				lng['wysiwyg_italic'],				'italic.gif',			'italic.gif'],
	"underline":	['Underline',			lng['wysiwyg_underline'],			'underline.gif',		'underline.gif'],
	"strikethrough":['Strikethrough',		lng['wysiwyg_strikethrough'],		'strikethrough.gif',	'strikethrough.gif'],
	"seperator":	['',					'',									'seperator.gif',		''],
	"subscript":	['Subscript',			lng['wysiwyg_subscript'],			'subscript.gif',		'subscript.gif'],
	"superscript":	['Superscript',			lng['wysiwyg_superscript'],			'superscript.gif',		'superscript.gif'],
	"justifyleft":	['Justifyleft',			lng['wysiwyg_left'],				'left.gif',				'left.gif'],
	"justifycenter":['Justifycenter',		lng['wysiwyg_center'],				'center.gif',			'center.gif'],
	"justifyright":	['Justifyright',		lng['wysiwyg_right'],				'right.gif',			'right.gif'],
	"justifyfull":	['Justifyfull',			lng['wysiwyg_justify'],				'justify.gif',			'justify.gif'],
	"unorderedlist":['InsertUnorderedList',	lng['wysiwyg_unordered_list'],		'list_unordered.gif',	'list_unordered.gif'],
	"orderedlist":	['InsertOrderedList',	lng['wysiwyg_ordered_list'],		'list_ordered.gif',		'list_ordered.gif'],
	"outdent":		['Outdent',				lng['wysiwyg_outdent'],				'indent_left.gif',		'indent_left.gif'],
	"indent":		['Indent',				lng['wysiwyg_indent'],				'indent_right.gif',		'indent_right.gif'],
	"cut":			['Cut',					lng['wysiwyg_cut'],					'sys_cut.gif',			'sys_cut.gif'],
	"copy":			['Copy',				lng['wysiwyg_copy'],				'sys_copy.gif',			'sys_copy.gif'],
	"paste":		['Paste',				lng['wysiwyg_paste'],				'sys_paste.gif',		'sys_paste.gif'],
	"forecolor":	['ForeColor',			lng['wysiwyg_forecolor'],			'color.gif',			'color.gif'],
	"backcolor":	['BackColor',			lng['wysiwyg_backcolor'],			'bgcolor.gif',			'bgcolor.gif'],
	"undo":			['Undo',				lng['wysiwyg_undo'],				'sys_undo.gif',			'sys_undo.gif'],
	"redo":			['Redo',				lng['wysiwyg_redo'],				'sys_redo.gif',			'sys_redo.gif'],
	"inserthr":		['InsertHR',			lng['wysiwyg_hr'],					'hr.gif',				'hr.gif'],
	"inserttable":	['InsertTable',			lng['wysiwyg_table'],				'table.gif',			'table.gif'],
	"insertimage":	['InsertImage',			lng['wysiwyg_image'],				'img.gif',				'img.gif'],
	"createlink":	['CreateLink',			lng['wysiwyg_link'],				'url.gif',				'url.gif'],
	"viewSource":	['ViewSource',			lng['wysiwyg_view_source'],			'view_html.gif',		''],
	"viewText":		['ViewText',			lng['wysiwyg_view_text'],			'view_text.gif',		''],
	"fonts":		['Fonts',				lng['wysiwyg_font_face'],			'select_font.gif',		'select_font.gif'],
	"fontsizes":	['Fontsizes',			lng['wysiwyg_font_size'],			'select_size.gif',		'select_size.gif'],
	"headings":		['Headings',			lng['wysiwyg_headings'],			'select_heading.gif',	'select_heading.gif'],
	"removenode":	['RemoveNode',			lng['wysiwyg_remove_formatting'],	'remove_node.gif', 		'remove_node.gif'],
	"removeformat":	['RemoveFormat',		lng['wysiwyg_strip_word'],			'remove_format.gif',	'remove_format.gif'],
	"maximize":		['Maximize',			lng['wysiwyg_maximize'],			'maximize.gif',			'maximize.gif']
	},

	// stores the different settings for each textarea
	// the textarea identifier is used to store the settings object
	config: new Array(),
	// Create viewTextMode global variable and set to 0
	// enabling all toolbar commands while in HTML mode
	viewTextMode: new Array(),
	// maximized
	maximized: new Array(),

	/**
	 * Get the range of the given selection
	 *
	 * @param {Selection} sel Selection object
	 * @return {Range} Range object
	 */
	getRange: function(sel, n) {
		var w = this.getEditorWindow(n);
		var range = sel.rangeCount > 0 ? sel.getRangeAt(0) : sel.createRange ? sel.createRange() : w.document.createRange();
		if (!range) {
			range = document.body && document.body.createTextRange ? w.document.body.createTextRange() : w.document.createRange();
		}
		return range;
	},

	/**
	 * Return the editor div element
	 *
	 * @param {String} n Editor identifier
	 * @return {HtmlDivElement} Iframe object
	 */
	getEditorDiv: function(n) {
		return $("wysiwyg_div_" + n);
	},

	/**
	 * Return the editor table element
	 *
	 * @param {String} n Editor identifier
	 * @return {HtmlTableElement} Iframe object
	 */
	getEditorTable: function(n) {
		return $("wysiwyg_table_" + n);
	},

	/**
	 * Get the iframe object of the WYSIWYG editor
	 *
	 * @param {String} n Editor identifier
	 * @return {HtmlIframeElement} Iframe object
	 */
	getEditor: function(n) {
		return $("wysiwyg" + n);
	},

	/**
	 * Get editors window element
	 *
	 * @param {String} n Editor identifier
	 * @return {HtmlWindowElement} Html window object
	 */
	getEditorWindow: function(n) {
		var ed = this.getEditor(n);
		return ed.contentWindow ? ed.contentWindow: ed.contentDocument;
	},

	/**
	 * Attach the WYSIWYG editor to the given textarea element
	 *
	 * @param {String} id Textarea identifier (all = all textareas)
	 * @param {Settings} settings the settings which will be applied to the textarea
	 */
	attach: function(id, settings) {
		if(id != "all") {
			this.setSettings(id, settings);
			WYSIWYG_Core.addEvent(window, "load", function generateEditor() {WYSIWYG._generate(id, settings);});
		}
		else {
			WYSIWYG_Core.addEvent(window, "load", function generateEditor() {WYSIWYG.attachAll(settings);});
		}
	},

	/**
	 * Attach the WYSIWYG editor to all textarea elements
	 *
	 * @param {Settings} settings Settings to customize the look and feel
	 */
	attachAll: function(settings) {
		var areas = document.getElementsByTagName("textarea");
		for(var i=0;i<areas.length;i++) {
			var id = areas[i].getAttribute("id");
			if(id == null || id == "") continue;
			this.setSettings(id, settings);
			WYSIWYG._generate(id, settings);
		}
	},

	/**
	 * Display an iframe instead of the textarea.
	 * It's used as textarea replacement to display HTML.
	 *
	 * @param id Textarea identifier (all = all textareas)
	 * @param settings the settings which will be applied to the textarea
	 */
	display: function(id, settings) {
		if(id != "all") {
			this.setSettings(id, settings);
			WYSIWYG_Core.addEvent(window, "load", function displayIframe() {WYSIWYG._display(id, settings);});
		}
		else {
			WYSIWYG_Core.addEvent(window, "load", function displayIframe() {WYSIWYG.displayAll(settings);});
		}
	},

	/**
	 * Display an iframe instead of the textarea.
	 * It's apply the iframe to all textareas found in the current document.
	 *
	 * @param settings Settings to customize the look and feel
	 */
	displayAll: function(settings) {
		var areas = document.getElementsByTagName("textarea");
		for(var i=0;i<areas.length;i++) {
			var id = areas[i].getAttribute("id");
			if(id == null || id == "") continue;
			this.setSettings(id, settings);
			WYSIWYG._display(id, settings);
		}
	},

	/**
	 * Set settings in config array, use the textarea id as identifier
	 *
	 * @param n Textarea identifier (all = all textareas)
	 * @param settings the settings which will be applied to the textarea
	 */
	setSettings: function(n, settings) {
		if(typeof(settings) != "object") {
			this.config[n] = new this.Settings();
		}
		else {
			this.config[n] = settings;
		}
	},

	/**
	 * Insert or modify an image
	 *
	 * @param {String} src Source of the image
	 * @param {Integer} width Width
	 * @param {Integer} height Height
	 * @param {String} align Alignment of the image
	 * @param {String} border Border size
	 * @param {String} alt Alternativ Text
	 * @param {Integer} hspace Horizontal Space
	 * @param {Integer} vspace Vertical Space
	 * @param {String} n The editor identifier (the textarea's ID)
	 */
	insertImage: function(src, width, height, align, border, alt, hspace, vspace, bordercolor, n) {

		// get editor
		var doc = this.getEditorWindow(n).document;
		// get selection and range
		var sel = this.getSelection(n);
		var range = this.getRange(sel, n);

		// the current tag of range
		var img = this.findParent("img", range);

		// element is not a link
		var update = (img == null) ? false : true;
		if(!update) {
			img = doc.createElement("img");
		}

		// set the attributes
		WYSIWYG_Core.setAttribute(img, "src", src);
		var dim = new Array();
		if (width > 0) {
			dim[0] = "width:" + width;
		}
		if (height > 0) {
			dim[1] = "height:" + height;
		}
		if (border != '') {
			if (bordercolor != '' && bordercolor != 'none') {
				dim[2] = "border: " + border + "px solid " + bordercolor + ";";
			}
			else {
				WYSIWYG_Core.setAttribute(img, "border", border);
			}
		}
		WYSIWYG_Core.setAttribute(img, "style", dim.join(";"));
		if(align != "") { WYSIWYG_Core.setAttribute(img, "align", align); } else { img.removeAttribute("align"); }
		WYSIWYG_Core.setAttribute(img, "alt", alt);
		if (hspace > 0) {
			WYSIWYG_Core.setAttribute(img, "hspace", hspace);
		}
		if (vspace > 0) {
			WYSIWYG_Core.setAttribute(img, "vspace", vspace);
		}
		img.removeAttribute("width");
		img.removeAttribute("height");

		// on update exit here
		if(update) { return; }

		// Check if IE or Mozilla (other)
		if (WYSIWYG_Core.isMSIE) {
			range.pasteHTML(img.outerHTML);
		}
		else {
			this.insertNodeAtSelection(img, n);
		}
	},

	/**
	 * Insert or modify a link
	 *
	 * @param {String} href The url of the link
	 * @param {String} target Target of the link
	 * @param {String} style Stylesheet of the link
	 * @param {String} styleClass Stylesheet class of the link
	 * @param {String} name Name attribute of the link
	 * @param {String} n The editor identifier (the textarea's ID)
	 */
	insertLink: function(href, target, style, styleClass, name, n) {

		// get editor
		var doc = this.getEditorWindow(n).document;
		// get selection and range
		var sel = this.getSelection(n);
		var range = this.getRange(sel, n);
		var lin = null;

		// get element from selection
		if(WYSIWYG_Core.isMSIE) {
			if(sel.type == "Control" && range.length == 1) {
				range = this.getTextRange(range(0));
				range.select();
			}
		}

		// find a as parent element
		lin = this.findParent("a", range);

		// check if parent is found
		var update = (lin == null) ? false : true;
		if(!update) {
			lin = doc.createElement("a");
		}

		// set the attributes
		if ((href != '' && href != 'http://') || name == '') {
			WYSIWYG_Core.setAttribute(lin, "href", href);
		}
		if (styleClass != '') {
			WYSIWYG_Core.setAttribute(lin, "class", styleClass);
			WYSIWYG_Core.setAttribute(lin, "className", styleClass);
		}
		if (target != '') {
			WYSIWYG_Core.setAttribute(lin, "target", target);
		}
		if (name != '') {
			WYSIWYG_Core.setAttribute(lin, "name", name);
		}
		if (style != '') {
			WYSIWYG_Core.setAttribute(lin, "style", style);
		}

		// on update exit here
		if(update) { return; }

		// Check if IE or Mozilla (other)
		if (WYSIWYG_Core.isMSIE) {
			range.select();
			txt = (range.htmlText) ? range.htmlText : href;
			lin.innerHTML = txt;
			range.pasteHTML(lin.outerHTML);
		}
		else {
			var node = range.startContainer;
			var pos = range.startOffset;
			if(node.nodeType != 3) {
				node = node.childNodes[pos];
			}
			if (node.tagName) {
				if (node.tagName == 'BR') {
					lin.innerHTML = href
				}
				else {
					lin.appendChild(node)
				}
			}
			else {
				lin.innerHTML = sel;
			}
			this.insertNodeAtSelection(lin, n);
		}
	},

	/**
	 * Strips any HTML added by word
	 *
	 * @param {String} n The editor identifier (the textarea's ID)
	 */
	removeFormat: function(n) {

		if ( !confirm(this.config[n].RemoveFormatConfMessage) ) { return; }
		var doc = this.getEditorWindow(n).document;
		var str = doc.body.innerHTML;

		str = str.replace(/<span([^>])*>(&nbsp;)*\s*<\/span>/gi, '');
		str = str.replace(/<span[^>]*>/gi, '');
		str = str.replace(/<\/span[^>]*>/gi, '');
		str = str.replace(/<p([^>])*>(&nbsp;)*\s*<\/p>/gi, '');
		str = str.replace(/<p[^>]*>/gi, '');
		str = str.replace(/<\/p[^>]*>/gi, '');
		str = str.replace(/<h([^>])[0-9]>(&nbsp;)*\s*<\/h>/gi, '');
		str = str.replace(/<h[^>][0-9]>/gi, '');
		str = str.replace(/<\/h[^>][0-9]>/gi, '');
		str = str.replace (/<B [^>]*>/ig, '<b>');

		// var repl_i1 = /<I[^>]*>/ig;
		// str = str.replace (repl_i1, '<i>');

		str = str.replace (/<DIV[^>]*>/ig, '');
		str = str.replace (/<\/DIV>/gi, '');
		str = str.replace (/<[\/\w?]+:[^>]*>/ig, '');
		str = str.replace (/(&nbsp;){2,}/ig, '&nbsp;');
		str = str.replace (/<STRONG>/ig, '');
		str = str.replace (/<\/STRONG>/ig, '');
		str = str.replace (/<TT>/ig, '');
		str = str.replace (/<\/TT>/ig, '');
		str = str.replace (/<FONT [^>]*>/ig, '');
		str = str.replace (/<\/FONT>/ig, '');
		str = str.replace (/STYLE=\"[^\"]*\"/ig, '');
		str = str.replace(/<([\w]+) class=([^ |>]*)([^>]*)/gi, '<$1$3');
  		str = str.replace(/<([\w]+) style="([^"]*)"([^>]*)/gi, '<$1$3');
		str = str.replace(/width=([^ |>]*)([^>]*)/gi, '');
		str = str.replace(/classname=([^ |>]*)([^>]*)/gi, '');
		str = str.replace(/align=([^ |>]*)([^>]*)/gi, '');
		str = str.replace(/valign=([^ |>]*)([^>]*)/gi, '');
		str = str.replace(/<\\?\??xml[^>]>/gi, '');
		str = str.replace(/<\/?\w+:[^>]*>/gi, '');
		str = str.replace(/<st1:.*?>/gi, '');
		str = str.replace(/o:/gi, '');

		str = str.replace(/<!--([^>])*>(&nbsp;)*\s*<\/-->/gi, '');
   		str = str.replace(/<!--[^>]*>/gi, '');
   		str = str.replace(/<\/--[^>]*>/gi, '');

		doc.body.innerHTML = str;
	},

	/**
	 * Display an iframe instead of the textarea.
	 *
	 * @private
	 * @param {String} n The editor identifier (the textarea's ID)
	 * @param {Object} settings Object which holds the settings
	 */
	_display: function(n, settings) {

		// Get the textarea element
		var textarea = $(n);

		// Validate if textarea exists
		if(textarea == null) {
			alert("No textarea found with the given identifier (ID: " + n + ").");
			return;
		}

		// Validate browser compatiblity
		if(!WYSIWYG_Core.isBrowserCompatible()) {
			if(this.config[n].NoValidBrowserMessage != "") { alert(this.config[n].NoValidBrowserMessage); }
			return;
		}

		// Load settings in config array, use the textarea id as identifier
		if(typeof(settings) != "object") {
			this.config[n] = new this.Settings();
		}
		else {
			this.config[n] = settings;
		}

		// Hide the textarea
		textarea.style.display = "none";

		// Override the width and height of the editor with the
		// size given by the style attributes width and height
		if(textarea.style.width) {
			this.config[n].Width = textarea.style.width;
		}
		if(textarea.style.height) {
			this.config[n].Height = textarea.style.height;
		}

		// determine the width + height
		var currentWidth = this.config[n].Width;
		var currentHeight = this.config[n].Height;

		// Calculate the width + height of the editor
		var ifrmWidth = "100%";
		var	ifrmHeight = "100%";
		if(currentWidth.search(/%/) == -1) {
			ifrmWidth = currentWidth;
			ifrmHeight = currentHeight;
		}

		// Create iframe which will be used for rich text editing
		var iframe = '<table cellpadding="0" cellspacing="0" border="0" style="width:' + currentWidth + '; height:' + currentHeight + ';" class="editor_textarea_outer"><tr><td valign="top">\n'
		+ '<iframe frameborder="0" id="wysiwyg' + n + '" class="editor_textarea_inner" style="width:' + ifrmWidth + ';height:' + ifrmHeight + ';"></iframe>\n'
		+ '</td></tr></table>\n';

		// Insert after the textArea both toolbar one and two
		textarea.insertAdjacentHTML("afterEnd", iframe);

		// Pass the textarea's existing text over to the content variable
		var content = textarea.value;
		var doc = this.getEditorWindow(n).document;

		// Replace all \n with <br>
		if(this.config[n].ReplaceLineBreaks) {
			content = content.replace(/(\r\n)|(\n)/ig, "<br>");
		}

		// Write the textarea's content into the iframe
		doc.open();
		doc.write(content);
		doc.close();

		// Set default style of the editor window
		WYSIWYG_Core.setAttribute(doc.body, "style", this.config[n].DefaultStyle);
	},

	/**
	 * Replace the given textarea with wysiwyg editor
	 *
	 * @private
	 * @param {String} n The editor identifier (the textarea's ID)
	 * @param {Object} settings Object which holds the settings
	 */
	_generate: function(n, settings) {

		// Get the textarea element
		var textarea = $(n);
		// Validate if textarea exists
		if(textarea == null) {
			alert("No textarea found with the given identifier (ID: " + n + ").");
			return;
		}

		// Validate browser compatiblity
		if(!WYSIWYG_Core.isBrowserCompatible()) {
			if(this.config[n].NoValidBrowserMessage != "") { alert(this.config[n].NoValidBrowserMessage); }
			return;
		}

		// Hide the textarea
		textarea.style.display = 'none';

		// Override the width and height of the editor with the
		// size given by the style attributes width and height
		if(textarea.style.width) {
			this.config[n].Width = textarea.style.width;
		}
		if(textarea.style.height) {
			this.config[n].Height = textarea.style.height
		}

		// determine the width + height
		var currentWidth = this.config[n].Width;
		var currentHeight = this.config[n].Height;

		// Calculate the width + height of the editor
		var toolbarWidth = currentWidth;
		var ifrmWidth = "100%";
		var	ifrmHeight = "100%";
		if(currentWidth.search(/%/) == -1) {
			toolbarWidth = currentWidth.replace(/px/gi, "");
			toolbarWidth = (parseFloat(toolbarWidth) + 2) + "px";
			ifrmWidth = currentWidth;
			ifrmHeight = currentHeight;
		}

		// Generate the WYSIWYG Table
		// This table holds the toolbars and the iframe as the editor
		var editor = "";
		editor += '<div id="wysiwyg_div_' + n + '" style="width:' + currentWidth  +';">';
		editor += '<table border="0" cellpadding="0" cellspacing="0" class="editor_textarea_outer" id="wysiwyg_table_' + n + '" style="width:' + currentWidth  + '; height:' + currentHeight + ';">';
		editor += '<tr><td style="height:22px;vertical-align:top;padding:0px;">';

		// Output all command buttons that belong to toolbar one
		for (var j = 0; j < this.config[n].Toolbar.length;j++) {
			if(this.config[n].Toolbar[j] && this.config[n].Toolbar[j].length > 0) {
				var toolbar = this.config[n].Toolbar[j];

				// Generate WYSIWYG toolbar one
				editor += '<table border="0" cellpadding="0" cellspacing="0" class="editor_toolbar" style="width:100%;" id="toolbar' + j + '_' + n + '">';
				editor += '<tr>';

				// Interate over the toolbar element
				for (var i = 0; i < toolbar.length;i++) {
					var id = toolbar[i];
					if (toolbar[i]) {
						if(typeof (this.config[n].DropDowns[id]) != "undefined") {
							var dropdown = this.config[n].DropDowns[id];
							editor += '<td style="width: ' + dropdown.width + ';">';
							// write the drop down content
							editor += this.writeDropDown(n, id);
							editor += '</td>';
						}
						else {

							// Get the values of the Button from the global ToolbarList object
							var buttonObj = this.ToolbarList[toolbar[i]];
							if(buttonObj) {
								var buttonID = buttonObj[0];
								var buttonTitle = buttonObj[1];
								var buttonImage = this.config[n].ImagesDir + buttonObj[2];
								var buttonImageRollover  = this.config[n].ImagesDir + buttonObj[3];

								if (toolbar[i] == "seperator") {
									editor += '<td style="width: 12px;" align="center">';
									editor += '<img src="' + buttonImage + '" border=0 unselectable="on" width="2" height="18" hspace="2" unselectable="on">';
									editor += '</td>';
								}
								// View Source button
								else if (toolbar[i] == "viewSource"){
									editor += '<td style="width: 22px;">';
									editor += '<span id="HTMLMode' + n + '"><img src="' + buttonImage +  '" border="0" unselectable="on" title="' + buttonTitle + '" id="' + buttonID + n +'" class="editor_toolbar_button" onmouseover="this.className=\'editor_toolbar_button_on\';" onmouseout="this.className=\'editor_toolbar_button\';" onclick="WYSIWYG.execCommand(\'' + n + '\', \'' + buttonID + '\');" unselectable="on" width="20" height="20"></span>';
									editor += '<span id="textMode' + n + '"><img src="' + this.config[n].ImagesDir + 'view_text.gif" border="0" unselectable="on" title="' + buttonTitle + '" id="ViewText' + n + '" class="editor_toolbar_button" onmouseover="this.className=\'editor_toolbar_button_on\';" onmouseout="this.className=\'editor_toolbar_button\';" onclick="WYSIWYG.execCommand(\'' + n + '\',\'ViewText\');" unselectable="on"  width="20" height="20"></span>';
									editor += '</td>';
								}
								else {
									editor += '<td style="width: 22px;">';
									if (buttonObj[2] == buttonObj[3]) {
										onevent = 'onmouseover="this.className=\'editor_toolbar_button_on\';" onmouseout="this.className=\'editor_toolbar_button\';"';
									}
									else {
										onevent = 'onmouseover="this.className=\'editor_toolbar_button_on\'; this.src=\'' + buttonImageRollover + '\';" onmouseout="this.className=\'editor_toolbar_button\'; this.src=\'' + buttonImage + '\';"';
									}
									editor += '<img src="' + buttonImage + '" border=0 unselectable="on" title="' + buttonTitle + '" id="' + buttonID + n + '" class="editor_toolbar_button" '+onevent+' onclick="WYSIWYG.execCommand(\'' + n + '\', \'' + buttonID + '\');" unselectable="on" width="20" height="20">';
									editor += '</td>';
								}
							}
						}
			  		}
			  	}
			  	editor += '<td>&nbsp;</td></tr></table>';
			}
		}

	 	editor += '</td></tr><tr><td valign="top" class="editor_textarea_td">\n';
		// Create iframe which will be used for rich text editing
		editor += '<iframe frameborder="0" id="wysiwyg' + n + '" class="editor_textarea_inner" style="width:100%;height:' + currentHeight + ';"></iframe>\n'
		+ '</td></tr>';
		// Status bar HTML code
		if(this.config[n].StatusBarEnabled) {
			editor += '<tr><td class="editor_statusbar" id="wysiwyg_statusbar_' + n + '">&nbsp;</td></tr>';
		}
		editor += '</table>';
		editor += '</div>';

		// Insert the editor after the textarea
		textarea.insertAdjacentHTML("afterEnd", editor);

		// Hide the "Text Mode" button
		// Validate if textMode Elements are prensent
		if($("textMode" + n)) {
			$("textMode" + n).style.display = 'none';
		}

		// Pass the textarea's existing text over to the content variable
		var content = textarea.value;
		var doc = this.getEditorWindow(n).document;


		// Replace all \n with <br>
		if(this.config[n].ReplaceLineBreaks) {
			content = content.replace(/\n\r|\n/ig, "<br>");
		}

		// Write the textarea's content into the iframe
		doc.open();
		doc.write(content);

		// Enable table highlighting
		// Update this before closing!
		WYSIWYG_Table.refreshHighlighting(n);

		doc.close();

		// Make the iframe editable in both Mozilla and IE
		// Improve compatiblity for IE + Mozilla
		if (doc.body.contentEditable) {
			doc.body.contentEditable = true;
		}
		else {
			doc.designMode = "on";
		}

		// Set default font style
		WYSIWYG_Core.setAttribute(doc.body, "style", this.config[n].DefaultStyle);

		// Event Handling
		// Update the textarea with content in WYSIWYG when user submits form
		for (var idx=0; idx < document.forms.length; idx++) {
			WYSIWYG_Core.addEvent(document.forms[idx], "submit", function xxx_aa() { WYSIWYG.updateTextArea(n); });
		}

		// close font selection if mouse moves over the editor window
		WYSIWYG_Core.addEvent(doc, "mouseover", function xxx_bb() { WYSIWYG.closeDropDowns(n); });

		// If it's true invert the line break capability of IE
		if(this.config[n].InvertIELineBreaks) {
			WYSIWYG_Core.addEvent(doc, "keypress", function xxx_cc() { WYSIWYG.invertIELineBreakCapability(n); });
		}

		// status bar update
		if(this.config[n].StatusBarEnabled) {
			WYSIWYG_Core.addEvent(doc, "mouseup", function xxx_dd() { WYSIWYG.updateStatusBar(n); });
			WYSIWYG_Core.addEvent(doc, "keyup", function xxx_ee() { WYSIWYG.updateStatusBar(n); });
		}

		// init viewTextMode var
		this.viewTextMode[n] = false;
	},

	/**
	 * Disable the given WYSIWYG Editor Box
	 *
	 * @param {String} n The editor identifier (the textarea's ID)
	 */
	disable: function(n) {

		// get the editor window
		var editor = this.getEditorWindow(n);

		// Validate if editor exists
		if(editor == null) {
			alert("No editor found with the given identifier (ID: " + n + ").");
			return;
		}

		if(editor) {
			// disable design mode or content editable feature
			if(editor.document.body.contentEditable) {
				editor.document.body.contentEditable = false;
			}
			else {
				editor.document.designMode = "Off";
			}

			// change the style of the body
			WYSIWYG_Core.setAttribute(editor.document.body, "style", this.config[n].DisabledStyle);

			// hide the status bar
			this.hideStatusBar(n);

			// hide all toolbars
			this.hideToolbars(n);
		}
	},

	/**
	 * Enables the given WYSIWYG Editor Box
	 *
	 * @param {String} n The editor identifier (the textarea's ID)
	 */
	enable: function(n) {

		// get the editor window
		var editor = this.getEditorWindow(n);

		// Validate if editor exists
		if(editor == null) {
			alert("No editor found with the given identifier (ID: " + n + ").");
			return;
		}

		if(editor) {
			// disable design mode or content editable feature
			if(editor.document.body.contentEditable){
				editor.document.body.contentEditable = true;
			}
			else {
				editor.document.designMode = "On";
			}

			// change the style of the body
			WYSIWYG_Core.setAttribute(editor.document.body, "style", this.config[n].DefaultStyle);

			// hide the status bar
			this.showStatusBar(n);

			// hide all toolbars
			this.showToolbars(n);
		}
	},

	/**
	 * Returns the node structure of the current selection as array
	 *
	 * @param {String} n The editor identifier (the textarea's ID)
	 */
	getNodeTree: function(n) {

		var sel = this.getSelection(n);
		if (sel === null) {
			return null;
		}

		var range = this.getRange(sel, n);

		// get element of range
		var tag = this.getTag(range);

		if(tag == null) { return; }

		// Fix for blank window with nothing selected - Safari
		if (tag.nodeName === "HTML") {
			nodeTree = [tag];
			nodeTree[1] = tag.childNodes[0];
			return nodeTree;
		}

		// get parent of element
		var node = this.getParent(tag);
		// init the tree as array with the current selected element
		var nodeTree = new Array(tag);
		// get all parent nodes
		var ii = 1;

		while(node != null && node.nodeName != "#document") {
			nodeTree[ii] = node;
			node = this.getParent(node);
			ii++;
		}

		return nodeTree;
	},

	/**
	 * Removes the current node of the selection
	 *
	 * @param {String} n The editor identifier (the textarea's ID)
	 */
	removeNode: function(n) {
		// get selection and range
		var sel = this.getSelection(n);
		var range = this.getRange(sel, n);
		// the current tag of range
		var tag = this.getTag(range);
		if(tag == null) { return; }
		var parent = tag.parentNode;
		if(parent == null) { return; }

		switch(tag.nodeName) {
			case "APPLET":
			case "AREA":
			case "BODY":
			case "BUTTON":
			case "CAPTION":
			case "COL":
			case "COLGROUP":
			case "DIR":
			case "HR":
			case "HTML":
			case "IFRAME":
			case "INPUT":
			case "LEGEND":
			case "LI":
			case "MAP":
			case "MENU":
			case "OBJECT":
			case "OL":
			case "OPTGROUP":
			case "OPTION":
			case "PARAM":
			case "SCRIPT":
			case "SELECT":
			case "STYLE":
			case "TABLE":
			case "TBODY":
			case "TD":
			case "TEXTAREA":
			case "TFOOT":
			case "TH":
			case "THEAD":
			case "TR":
			case "UL":
		  		return;
			break;
		}

		// Remove links from images
		if (tag.nodeName == "IMG") {
			if (parent.nodeName == "A" && parent.parentNode) {
				for(var i=0; i < parent.childNodes.length;i++) {
					var cloned = parent.childNodes[i].cloneNode(true);
					parent.parentNode.insertBefore(cloned, parent);
				}

				parent.parentNode.removeChild(parent);
			}
		}
		else {
			// copy child elements of the node to the parent element before remove the node
			for(var i=0; i < tag.childNodes.length;i++) {
				var cloned = tag.childNodes[i].cloneNode(true);
				parent.insertBefore(cloned, tag);
			}

			// Remove Node
			parent.removeChild(tag);

			// validate if parent is a link and the node is only surrounded by the link, then remove the link too
			if(parent.nodeName == "A" && !parent.hasChildNodes() && parent.parentNode) {
				parent.parentNode.removeChild(parent);
			}
		}

		// update the status bar
		this.updateStatusBar(n);
	},

	/**
	 * Get the selection of the given editor
	 *
	 * @param {String} n The editor identifier (the textarea's ID)
	 */
	getSelection: function(n) {
		var w = this.getEditorWindow(n);
		return w.getSelection ? w.getSelection() : w.document.selection;
	},

	/**
	 * Updates the status bar with the current node tree
	 *
	 * @param {String} n The editor identifier (the textarea's ID)
	 */
	updateStatusBar: function(n) {

		if (this.viewTextMode[n] == false) {
			// get the node structure
			var nodeTree = this.getNodeTree(n);
			if(nodeTree == null) { return; }
			// format the output
			var outputTree = "";
			var max = nodeTree.length - 1;
			for(var i=max;i>=0;i--) {
				if(nodeTree[i].nodeName != "HTML" && nodeTree[i].nodeName != "BODY") {
					outputTree += '<a href="javascript:WYSIWYG.selectNode(\'' + n + '\',' + i + ');">' + nodeTree[i].nodeName.toLowerCase() + '</a>';
				}
				else {
					outputTree += nodeTree[i].nodeName.toLowerCase();
				}
				if(i > 0) { outputTree += " > "; }
			}
		}
		else {
			outputTree = '&nbsp;';
		}

		// update the status bar
		var statusbar = $("wysiwyg_statusbar_" + n);
		if(statusbar){
			statusbar.innerHTML = outputTree;
		}
	},

	/**
	 * Execute a command on the editor document
	 *
	 * @param {String} command The execCommand (e.g. Bold)
	 * @param {String} n The editor identifier
	 * @param {String} value The value when applicable
	 */
	execCommand: function(n, cmd, value) {

		// When user clicks toolbar button make sure it always targets its respective WYSIWYG
		this.getEditorWindow(n).focus();

		// When in Text Mode these execCommands are enabled
		var textModeCommands = new Array("ViewText");

	  	// Check if in Text mode and a disabled command execute
		var cmdValid = false;
		for (var i = 0; i < textModeCommands.length; i++) {
			if (textModeCommands[i] == cmd) {
				cmdValid = true;
			}
		}
		if(this.viewTextMode[n] && !cmdValid) {
			alert(lng['wysiwyg_error_text_mode']);
		  	return;
		}

		// rbg to hex convertion implementation dependents on browser
		var toHexColor = WYSIWYG_Core.isMSIE ? WYSIWYG_Core._dec_to_rgb : WYSIWYG_Core.toHexColor;

		// popup screen positions
		var popupPosition = {
			left: parseInt(window.screen.availWidth / 3, 10),
			top: parseInt(window.screen.availHeight / 3, 10)
		};

		// Check the insert image popup implementation
		var imagePopupFile = this.config[n].PopupsDir + 'insert_image';
		var imagePopupWidth = 400;
		var imagePopupHeight = 210;
		var currentColor, rgb, form;
		if(typeof this.config[n].ImagePopupFile != "undefined" && this.config[n].ImagePopupFile != "") {
			imagePopupFile = this.config[n].ImagePopupFile;
		}
		if(typeof this.config[n].ImagePopupWidth && this.config[n].ImagePopupWidth > 0) {
			imagePopupWidth = this.config[n].ImagePopupWidth;
		}
		if(typeof this.config[n].ImagePopupHeight && this.config[n].ImagePopupHeight > 0) {
			imagePopupHeight = this.config[n].ImagePopupHeight;
		}

		// switch which action have to do
		switch(cmd) {
			case "Maximize":
				this.maximize(n);
			break;
			case "FormatBlock":
				WYSIWYG_Core.execCommand(n, cmd, "<" + value + ">");
			break;
			// ForeColor and
			case "ForeColor":
				var rgb = this.getEditorWindow(n).document.queryCommandValue(cmd);
			  	var currentColor = rgb != '' ? toHexColor(this.getEditorWindow(n).document.queryCommandValue(cmd)) : "000000";
			  	window.open(this.config[n].PopupsDir + 'select_color&color=' + currentColor + '&command=' + cmd + '&wysiwyg=' + n, 'popup', 'location=0,status=0,scrollbars=0,width=275,height=335,top=' + popupPosition.top + ',left=' + popupPosition.left).focus();
			break;

			// BackColor
			case "BackColor":
				var currentColor = toHexColor(this.getEditorWindow(n).document.queryCommandValue(cmd));
			  	window.open(this.config[n].PopupsDir + 'select_color&color=' + currentColor + '&command=' + cmd + '&wysiwyg=' + n, 'popup', 'location=0,status=0,scrollbars=0,width=275,height=335,top=' + popupPosition.top + ',left=' + popupPosition.left).focus();
			break;

			// InsertImage
			case "InsertImage":
				window.open(imagePopupFile + 'wysiwyg=' + n, 'popup', 'location=0,status=0,scrollbars=1,resizable=1,width=' + imagePopupWidth + ',height=' + imagePopupHeight + ',top=' + popupPosition.top + ',left=' + popupPosition.left).focus();
			break;

			// Remove a Node
			case "RemoveNode":
				this.removeNode(n);
			break;

			// Create Link
			case "CreateLink":
				window.open(this.config[n].PopupsDir + 'insert_hyperlink&wysiwyg=' + n, 'popup', 'location=0,status=0,scrollbars=1,resizable=1,width=420,height=160,top=' + popupPosition.top + ',left=' + popupPosition.left).focus();
			break;

			// InsertHR
			case "InsertHR":
				window.open(this.config[n].PopupsDir + 'insert_hr&wysiwyg=' + n, 'popup', 'location=0,status=0,scrollbars=1,resizable=1,width=400,height=250,top=' + popupPosition.top + ',left=' + popupPosition.left).focus();
			break;

			// InsertTable
			case "InsertTable":
				window.open(this.config[n].PopupsDir + 'create_table&wysiwyg=' + n, 'popup', 'location=0,status=0,scrollbars=1,resizable=1,width=530,height=260,top=' + popupPosition.top + ',left=' + popupPosition.left).focus();
			break;

			// ViewSource
			case "ViewSource":
				this.viewSource(n);
			break;

			// ViewText
			case "ViewText":
				this.viewText(n);
			break;

			// Strip any HTML added by word
			case "RemoveFormat":
				this.removeFormat(n);
			break;

			default:
				WYSIWYG_Core.execCommand(n, cmd, value);

		}

		// hide node the font + font size selection
		this.closeDropDowns(n);
	},

	/**
	* Find how far the page has scrolled
	*
	*/
	getYOffset: function(n) {
		var e = window, a = 'pageYOffset', d = document;
		if (! (a in e)) {
			e = (d.documentElement[a]) ? d.documentElement : d.body;
		}
		return e[a];
	},

	/**
	* Calculate how many toolbars, adding the heights of each one as we go
	*
	*/
	calculateToolbarHeight: function(n) {
		var a = 0, t = this.config[n].Toolbar, l = 'length';
		for (var j = 0; j < t[l]; j++) {
			if (t[j] && t[j][l] > 0) {
				var h = $('toolbar' + j + '_' + n); a += h.offsetHeight;
			}
		}
		return a;
	},

	/**
	 * Maximize the editor instance
	 *
	 * @param {String} n The editor identifier
	 */
	maximize: function(n) {
		var divElm = this.getEditorDiv(n);
		var tableElm = this.getEditorTable(n);
		var editor = this.getEditor(n);
		var setting = this.config[n];
		var size = WYSIWYG_Core.windowSize();
		var doc = document.documentElement || document.body;
		if (this.maximized[n]) {
			WYSIWYG_Core.setAttribute(doc, 'style', 'overflow:');
			WYSIWYG_Core.setAttribute(divElm, 'style', 'position:static;z-index:9998;top:0;left:0;width:' + setting.Width + ';height:100%');
			WYSIWYG_Core.setAttribute(tableElm, 'style', 'width:' + setting.Width + ';height:' + setting.Height);
			WYSIWYG_Core.setAttribute(editor, 'style', 'width:100%;height:' + setting.Height);
			this.maximized[n] = false;
		} else {
			var YOffset = (this.isOpera) ? 0 : this.getYOffset(n);
			var iFrameMaxHeight = size.height - this.calculateToolbarHeight(n) - ($('wysiwyg_statusbar_' + n).offsetHeight * 2);
			WYSIWYG_Core.setAttribute(doc, 'style', 'overflow:hidden');
			WYSIWYG_Core.setAttribute(divElm, 'style', 'position:absolute;z-index:9998;top:' + YOffset + 'px;left:0;width:' + size.width + 'px;height:' + size.height + 'px');
			WYSIWYG_Core.setAttribute(tableElm, 'style', 'width:100%;height:100%');
			WYSIWYG_Core.setAttribute(editor, 'style', 'width:100%;height:' + iFrameMaxHeight + 'px');
			this.maximized[n] = true;
		}
	},

	/**
	 * Insert HR into WYSIWYG in rich text
	 *
	 * @param {String} html The HTML being inserted (e.g. <b>hello</b>)
	 * @param {String} n The editor identifier
	 */
	insertHR: function(width, height, shade, align, n) {
		if (WYSIWYG_Core.isMSIE) {
			if (height > 0) {
				var varHeight = ' size="' + height + '"';
			} else {
				var varHeight = '';
			}
			if (width > 0) {
				var varWidth = ' width="' + width + '"';
			} else {
				var varWidth = '';
			}
			if (shade == true) {
				var varShade = ' noshade=\"noshade\"';
			} else if (shade == false) {
				var varShade = '';
			}
	   		if (align != '') {
	   			var varAlign = ' align="'+align+'"';
	   		}
	   		else {
		 		var varAlign = '';
	   		}

			this.getEditorWindow(n).document.selection.createRange().pasteHTML('<hr ' + varWidth + varHeight + varShade + varAlign + '/>');
		}
		else {
			var hr = this.getEditorWindow(n).document.createElement("hr");
			if (height > 0) {
				hr.size = height;
			}
			if (shade == true) {
				hr.noShade = true;
			}
	   		if (align != '') {
	   			hr.align = align;
	   		}
			if (width > 0 || width.indexOf('%') > -1) {
				hr.width = width;
			}
			this.insertNodeAtSelection(hr, n);
		}
	},

	/**
	 * Insert HTML into WYSIWYG in rich text
	 *
	 * @param {String} html The HTML being inserted (e.g. <b>hello</b>)
	 * @param {String} n The editor identifier
	 */
	insertHTML: function(html, n) {
		if (WYSIWYG_Core.isMSIE) {
			this.getEditorWindow(n).document.selection.createRange().pasteHTML(html);
		}
		else {
			var span = this.getEditorWindow(n).document.createElement("span");
			span.innerHTML = html;
			this.insertNodeAtSelection(span, n);
		}
	},

	/* ---------------------------------------------------------------------- *\
	  Function	: insertNodeAtSelection()
	  Description : insert HTML into WYSIWYG in rich text (mozilla)
	  Usage	   : WYSIWYG.insertNodeAtSelection(insertNode, n)
	  Arguments   : insertNode - The HTML being inserted (must be innerHTML inserted within a div element)
					n		  - The editor identifier that the HTML will be inserted into (the textarea's ID)
	\* ---------------------------------------------------------------------- */
	insertNodeAtSelection: function(insertNode, n) {

		// get editor document
		var doc = this.getEditorWindow(n).document;
		// get current selection
		var sel = this.getSelection(n);

		// get the first range of the selection
		// (there's almost always only one range)
		//var range = sel.getRangeAt(0);
		var range = this.getRange(sel, n);

		// deselect everything
		sel.removeAllRanges();

		// remove content of current selection from document
		range.deleteContents();

		// get location of current selection
		var container = range.startContainer;
		var pos = range.startOffset;

		// make a new range for the new selection
		range = doc.createRange();

		if (container.nodeType==3 && insertNode.nodeType==3) {
			// if we insert text in a textnode, do optimized insertion
			container.insertData(pos, insertNode.data);
			// put cursor after inserted text
			range.setEnd(container, pos+insertNode.length);
			range.setStart(container, pos+insertNode.length);
		}
		else {

			var afterNode;
			var beforeNode;
			if (container.nodeType==3) {
				// when inserting into a textnode
				// we create 2 new textnodes
				// and put the insertNode in between
				var textNode = container;
				container = textNode.parentNode;
				var text = textNode.nodeValue;

				// text before the split
				var textBefore = text.substr(0,pos);
				// text after the split
				var textAfter = text.substr(pos);

				beforeNode = document.createTextNode(textBefore);
				afterNode = document.createTextNode(textAfter);

				// insert the 3 new nodes before the old one
				container.insertBefore(afterNode, textNode);
				container.insertBefore(insertNode, afterNode);
				container.insertBefore(beforeNode, insertNode);

				// remove the old node
				container.removeChild(textNode);
			}
			else {
				// else simply insert the node
				afterNode = container.childNodes[pos];
				if (afterNode.nodeName === "HTML") {
					// Wasn't working in Safari. Wouldn't insert BEFORE the HTML node, so I'm appending to the BODY node.
					afterNode = afterNode.childNodes[0];
					afterNode.appendChild(insertNode);
				} else {
					container.insertBefore(insertNode, afterNode);
				}
			}

			try {
				range.setEnd(afterNode, 0);
				range.setStart(afterNode, 0);
			}
			catch(e) {
				alert(e);
			}
		}

		sel.addRange(range);
	},

	/**
	 * Writes the content of an drop down
	 *
	 * @param {String} n The editor identifier (textarea ID)
	 * @param {String} id Drop down identifier
	 * @return {String} Drop down HTML
	 */
	writeDropDown: function(n, id) {

		var dropdown = this.config[n].DropDowns[id];
		var toolbarObj = this.ToolbarList[dropdown.id];
		var image = this.config[n].ImagesDir  + toolbarObj[2];
		var imageOn  = this.config[n].ImagesDir + toolbarObj[3];
		dropdown.elements.sort();

		var output = "";
		output += '<table border="0" cellpadding="0" cellspacing="0"><tr>';
		output += '<td onMouseOver="$(\'img_' + dropdown.id + '_' + n + '\').src=\'' + imageOn + '\';" onMouseOut="$(\'img_' + dropdown.id + '_' + n + '\').src=\'' + image + '\';">';
		output += '<img src="' + image + '" id="img_' + dropdown.id + '_' + n + '" height="20" onClick="WYSIWYG.openDropDown(\'' + n + '\',\'' + dropdown.id + '\');" unselectable="on" border="0"><br>';
		output += '<span id="elm_' + dropdown.id + '_' + n + '" class="dropdown" style="width: 145px;display:none;">';
		for (var i = 0; i < dropdown.elements.length;i++) {
			if (dropdown.elements[i]) {
				var value = dropdown.elements[i];
				var label = dropdown.label.replace(/{value}/gi, value);
				// output
		  		output += '<button type="button" onClick="WYSIWYG.execCommand(\'' + n + '\',\'' + dropdown.command + '\',\'' + value + '\')\;" onMouseOver="this.className=\'mouseOver\'" onMouseOut="this.className=\'mouseOut\'" class="mouseOut" style="width: 125px;">';
		  		output += '<table cellpadding="0" cellspacing="0" border="0"><tr>';
		  		output += '<td align="left">' + label + '</td>';
		  		output += '</tr></table></button><br>';
		  	}
	  	}
  		output += '</span></td></tr></table>';

		return output;
	},

	/**
	 * Close all drop downs. You can define a exclude dropdown id
	 *
	 * @param {String} n The editor identifier (textarea ID)
	 * @param {String} exid Excluded drop down identifier
	 */
	closeDropDowns: function(n, exid) {
		if(typeof(exid) == "undefined") exid = "";
		var dropdowns = this.config[n].DropDowns;
		for(var id in dropdowns) {
			if (dropdowns[id] !== "undefined") {
				var dropdown = dropdowns[id];
				if(dropdown.id != exid) {
					var divId = "elm_" + dropdown.id + "_" + n;
					if($(divId)) $(divId).style.display = 'none';
				}
			}
		}
	},

	/**
	 * Open a defined drop down
	 *
	 * @param {String} n The editor identifier (textarea ID)
	 * @param {String} id Drop down identifier
	 */
	openDropDown: function(n, id) {
		var divId = "elm_" + id + "_" + n;
		if($(divId).style.display == "none") {
			$(divId).style.display = "block";
		}
		else {
			$(divId).style.display = "none";
		}
		$(divId).style.position = "absolute";
		this.closeDropDowns(n, id);
	},

	setOpacity: function(obj, opacity) {
		opacity = (opacity == 100)?  99.99 : opacity;
		obj.style.filter = "alpha(opacity:"+opacity+")"; // IE/Win
		obj.style.KHTMLOpacity = opacity/100; // Safari<1.2, Konqueror
		obj.style.MozOpacity = opacity/100; // Older Mozilla and Firefox
		obj.style.opacity = opacity/100; // Safari 1.2, newer Firefox and Mozilla, CSS3
	},

	setToolbarOpacity: function(n, opacity, opacity2) {
		for (var j = 0; j < this.config[n].Toolbar.length;j++) {
			if(this.config[n].Toolbar[j] && this.config[n].Toolbar[j].length > 0) {
				var toolbar = this.config[n].Toolbar[j];
				for (var i = 0; i < toolbar.length;i++) {
					var id = toolbar[i];
					if (toolbar[i]) {
						if(typeof (this.config[n].DropDowns[id]) != "undefined") {
							buttonObj = this.config[n].DropDowns[id];
							if(buttonObj) {
								this.setOpacity($('img_'+buttonObj['id']+'_'+n), opacity);
								this.setOpacity($('elm_'+buttonObj['id']+'_'+n), opacity2);
							}
						}
						else {
							buttonObj = this.ToolbarList[toolbar[i]];
							if(buttonObj) {
								buttonID = buttonObj[0] + n;
								if (toolbar[i] != "seperator" && buttonID != "HTMLMode" && buttonID != "textMode") {
									this.setOpacity($(buttonID), opacity);
								}
							}
						}
			  		}
			  	}
			}
		}
	},

	formatSource: function(n) {
		if (this.viewTextMode[n] == true && (WYSIWYG_Core.isFF || WYSIWYG_Core.isOpera)) {
			var body = this.getEditorWindow(n).document.body;
			var hl = new DlHighlight({ lang : "xml" });
			var html = body.ownerDocument.createRange();
			html.selectNodeContents(body);
			html = hl.doItNow(html.toString());
			body.innerHTML = html;
		}
	},

	/**
	 * Shows the HTML source code generated by the WYSIWYG editor
	 *
	 * @param {String} n The editor identifier (textarea ID)
	 */
	viewSource: function(n) {

		// document
		var doc = this.getEditorWindow(n).document;

		// Disable table highlighting
		WYSIWYG_Table.disableHighlighting(n);

		// View Source for IE
		if (WYSIWYG_Core.isMSIE) {
			var iHTML = doc.body.innerHTML;
			// strip off the absolute urls
			iHTML = this.stripURLPath(n, iHTML);
			// replace all decimal color strings with hex decimal color strings
			iHTML = WYSIWYG_Core.replaceRGBWithHexColor(iHTML);
			WYSIWYG_Beautifier.Init();
			iHTML = WYSIWYG_Beautifier.Format(iHTML);
			doc.body.innerText = iHTML;
		}
	  	// View Source for Mozilla/Netscape
	  	else {
	  		// replace all decimal color strings with hex decimal color strings
			var html = WYSIWYG_Core.replaceRGBWithHexColor(doc.body.innerHTML);
			WYSIWYG_Beautifier.Init();
			html = WYSIWYG_Beautifier.Format(html);
			html = document.createTextNode(html);
			doc.body.innerHTML = "";
			doc.body.appendChild(html);
	  	}

		// Hide the HTML Mode button and show the Text Mode button
		// Validate if Elements are present
		if($('HTMLMode' + n)) {
			$('HTMLMode' + n).style.display = 'none';
		}
		if($('textMode' + n)) {
			$('textMode' + n).style.display = 'block';
		}
		this.setToolbarOpacity(n, 40, 70);

		// set the font values for displaying HTML source
		doc.body.style.fontSize = "10pt";
		doc.body.style.fontFamily = "Courier New, monospace";
		doc.body.style.whiteSpace = 'pre';

	  	this.viewTextMode[n] = true;

		// Update Status Bar
		this.updateStatusBar(n);
	},

	/**
	 * Shows the Design of the code generated by the WYSIWYG editor.
	 *
	 * @param {String} n The editor identifier (textarea ID)
	 */
	viewText: function(n) {

		// get document
		var doc = this.getEditorWindow(n).document;

		// View Text for IE
		if (WYSIWYG_Core.isMSIE) {
			var iText = doc.body.innerText;
			// strip off the absolute urls
			iText = this.stripURLPath(n, iText);
			// replace all decimal color strings with hex decimal color strings
			iText = WYSIWYG_Core.replaceRGBWithHexColor(iText);
			doc.body.innerHTML = iText;
		}
		// View Text for Mozilla/Netscape
	  	else {
			var html = doc.body.ownerDocument.createRange();
			html.selectNodeContents(doc.body);
			// replace all decimal color strings with hex decimal color strings
			html = WYSIWYG_Core.replaceRGBWithHexColor(html.toString());
			doc.body.innerHTML = html;
		}

		// Enable table highlighting
		WYSIWYG_Table.refreshHighlighting(n);

		// Hide the Text Mode button and show the HTML Mode button
		// Validate if Elements are present
		if($('textMode' + n)) {
			$('textMode' + n).style.display = 'none';
		}
		if($('HTMLMode' + n)) {
			$('HTMLMode' + n).style.display = 'block';
		}
		this.setToolbarOpacity(n, 100, 90);

		// reset the font values (changed)
		WYSIWYG_Core.setAttribute(doc.body, "style", this.config[n].DefaultStyle);

		this.viewTextMode[n] = false;

		// Update Status Bar
		this.updateStatusBar(n);
	},

	/* ---------------------------------------------------------------------- *\
	  Function	: stripURLPath()
	  Description : Strips off the defined image and the anchor urls of the given content.
	  				It also can strip the document URL automatically if you define auto.
	  Usage	   : WYSIWYG.stripURLPath(content)
	  Arguments   : content  - Content on which the stripping applies
	\* ---------------------------------------------------------------------- */
	stripURLPath: function(n, content, exact) {

		// parameter exact is optional
		if(typeof exact == "undefined") {
			exact = true;
		}

		var stripImgageUrl = null;
		var stripAnchorUrl = null;

		// add url to strip of anchors to array
		if(this.config[n].AnchorPathToStrip == "auto") {
			stripAnchorUrl = WYSIWYG_Core.getDocumentUrl(document);
		}
		else if(this.config[n].AnchorPathToStrip == "absolute") {
			stripAnchorUrl = false;
		}
		else if(this.config[n].AnchorPathToStrip != "") {
			stripAnchorUrl = this.config[n].AnchorPathToStrip;
		}

		// add strip url of images to array
		if(this.config[n].ImagePathToStrip == "auto") {
			stripImgageUrl = WYSIWYG_Core.getDocumentUrl(document);
		}
		else if(this.config[n].ImagePathToStrip == "absolute") {
			stripImgageUrl = false;
		}
		else if(this.config[n].ImagePathToStrip != "") {
			stripImgageUrl = this.config[n].ImagePathToStrip;
		}

		var url;
		var regex;
		var result;
		// strip url of image path
		if(stripImgageUrl) {
			// escape reserved characters to be a valid regex
			url = WYSIWYG_Core.stringToRegex(WYSIWYG_Core.getDocumentPathOfUrl(stripImgageUrl));

			// exact replacing of url. regex: src="<url>"
			if(exact) {
				regex = new RegExp('(src=")(' + url + ')([^"]*)', 'gi');
				content = content.replace(regex, "$1$3");
			}
			// not exect replacing of url. regex: <url>
			else {
				regex = new RegExp('(' + url + ')(.+)', 'gi');
				content = content.replace(regex, "$2");
			}

			// strip absolute urls without a heading slash ("images/print.gif")
			result = WYSIWYG_Core.getDocumentPathOfUrl(stripImgageUrl).match(/.+[\/]{2,3}[^\/]*/,"");
			if(result) {
				url = WYSIWYG_Core.stringToRegex(result[0]);

				// exact replacing of url. regex: src="<url>"
				if(exact) {
					regex = new RegExp('(src="' + url + ')([^"]*)', 'gi');
					content = content.replace(regex, "$1$3");
				}
				// not exect replacing of url. regex: <url>
				else {
					regex = new RegExp('(' + url + ')(.+)', 'gi');
					content = content.replace(regex, "$2");
				}
			}
		}

		// strip url of anchor path
		if(stripAnchorUrl) {
			// escape reserved characters to be a valid regex
			url = WYSIWYG_Core.stringToRegex(WYSIWYG_Core.getDocumentPathOfUrl(stripAnchorUrl));

			// strip absolute urls with a heading slash ("/product/index.html")
			// exact replacing of url. regex: src="<url>"
			if(exact) {
				regex = new RegExp('(href="' + url + ')([^"]*)', 'gi');
				content = content.replace(regex, "$1$3");
			}
			// not exect replacing of url. regex: <url>
			else {
				regex = new RegExp('(' + url + ')(.+)', 'gi');
				content = content.replace(regex, "$2");
			}

			// strip absolute urls without a heading slash ("product/index.html")
			result = WYSIWYG_Core.getDocumentPathOfUrl(stripAnchorUrl).match(/.+[\/]{2,3}[^\/]*/,"");
			if(result) {
				url = WYSIWYG_Core.stringToRegex(result[0]);
				// exact replacing of url. regex: src="<url>"
				if(exact) {
					regex = new RegExp('(href="' + url + ')([^"]*)', 'gi');
					content = content.replace(regex, "$1$3");
				}
				// not exect replacing of url. regex: <url>
				else {
					regex = new RegExp('(' + url + ')(.+)', 'gi');
					content = content.replace(regex, "$2");
				}

			}

			// stip off anchor links with #name
			url = WYSIWYG_Core.stringToRegex(stripAnchorUrl);
			// exact replacing of url. regex: src="<url>"
			if(exact) {
				regex = new RegExp('(href="' + url + ')(#[^"]*)', 'gi');
				content = content.replace(regex, "$1$3");
			}
			// not exect replacing of url. regex: <url>
			else {
				regex = new RegExp('(' + url + ')(.+)', 'gi');
				content = content.replace(regex, "$2");
			}


			// stip off anchor links with #name (only for local system)
			url = WYSIWYG_Core.getDocumentUrl(document);
			var pos = url.lastIndexOf("/");
			if(pos != -1) {
				url = url.substring(pos + 1, url.length);
				url = WYSIWYG_Core.stringToRegex(url);
				// exact replacing of url. regex: src="<url>"
				if(exact) {
					regex = new RegExp('(href="' + url + ')(#[^"]*)', 'gi');
					content = content.replace(regex, "$1$3");
				}
				// not exect replacing of url. regex: <url>
				else {
					regex = new RegExp('(' + url + ')(.+)', 'gi');
					content = content.replace(regex, "$2");
				}
			}
		}

		return content;
	},

	/* ---------------------------------------------------------------------- *\
	  Function	: updateTextArea()
	  Description : Updates the text area value with the HTML source of the WYSIWYG
	  Arguments   : n   - The editor identifier (the textarea's ID)
	\* ---------------------------------------------------------------------- */
	updateTextArea: function(n) {
		// on update switch editor back to html mode
		if(this.viewTextMode[n]) { this.viewText(n); }
		// Strip table highlighting
		WYSIWYG_Table.disableHighlighting(n);
		// get inner HTML
		var content = this.getEditorWindow(n).document.body.innerHTML;
		// strip off defined URLs on IE
		content = this.stripURLPath(n, content);
		// replace all decimal color strings with hex color strings
		content = WYSIWYG_Core.replaceRGBWithHexColor(content);
		// remove line breaks before content will be updated
		if(this.config[n].ReplaceLineBreaks) { content = content.replace(/(\r\n)|(\n)/ig, " "); }
		// set content back in textarea
		$(n).value = content;
	},

	/* ---------------------------------------------------------------------- *\
	  Function	: hideToolbars()
	  Description : Hide all toolbars
	  Usage	   : WYSIWYG.hideToolbars(n)
	  Arguments   : n - The editor identifier (the textarea's ID)
	\* ---------------------------------------------------------------------- */
	hideToolbars: function(n) {
		for(var i=0;i<this.config[n].Toolbar.length;i++) {
			var toolbar = $("toolbar" + i + "_" + n);
			if(toolbar) { toolbar.style.display = "none"; }
		}
	},

	/* ---------------------------------------------------------------------- *\
	  Function	: showToolbars()
	  Description : Display all toolbars
	  Usage	   : WYSIWYG.showToolbars(n)
	  Arguments   : n - The editor identifier (the textarea's ID)
	\* ---------------------------------------------------------------------- */
	showToolbars: function(n) {
		for(var i=0;i<this.config[n].Toolbar.length;i++) {
			var toolbar = $("toolbar" + i + "_" + n);
			if(toolbar) { toolbar.style.display = ""; }
		}
	},

	/* ---------------------------------------------------------------------- *\
	  Function	: hideStatusBar()
	  Description : Hide the status bar
	  Usage	   : WYSIWYG.hideStatusBar(n)
	  Arguments   : n - The editor identifier (the textarea's ID)
	\* ---------------------------------------------------------------------- */
	hideStatusBar: function(n) {
		var statusbar = $('wysiwyg_statusbar_' + n);
		if(statusbar) {
			statusbar.style.display = "none";
		}
	},

	/* ---------------------------------------------------------------------- *\
	  Function	: showStatusBar()
	  Description : Display the status bar
	  Usage	   : WYSIWYG.showStatusBar(n)
	  Arguments   : n - The editor identifier (the textarea's ID)
	\* ---------------------------------------------------------------------- */
	showStatusBar: function(n) {
		var statusbar = $('wysiwyg_statusbar_' + n);
		if(statusbar) {
			statusbar.style.display = "";
		}
	},

	/**
	 * Finds the node with the given tag name in the given range
	 *
	 * @param {String} tagName Parent tag to find
	 * @param {Range} range Current range
	 */
	findParent: function(parentTagName, range){
		parentTagName = parentTagName.toUpperCase();
		var rangeWorking;
		var elmWorking = null;
		try {
			if(!WYSIWYG_Core.isMSIE) {
				var node = range.startContainer;
				var pos = range.startOffset;
				if(node.nodeType != 3) { node = node.childNodes[pos]; }
				return WYSIWYG_Core.findParentNode(parentTagName, node);
			}
			else {
				elmWorking = (range.length > 0) ? range.item(0): range.parentElement();
				elmWorking = WYSIWYG_Core.findParentNode(parentTagName, elmWorking);
				if(elmWorking != null) return elmWorking;

				rangeWorking = range.duplicate();
				rangeWorking.collapse(true);
				rangeWorking.moveEnd("character", 1);
				if (rangeWorking.text.length>0) {
					while (rangeWorking.compareEndPoints("EndToEnd", range) < 0){
			  			rangeWorking.move("Character");
			  			if (null != this.findParentTag(parentTagName, rangeWorking)){
			   				return this.findParentTag(parentTagName, rangeWorking);
			  			}
			 		}
			 	}
			 	return null;
			}
		}
		catch(e) {
			return null;
		}
	},

	/**
	 * Get the acutally tag of the given range
	 *
	 * @param {Range} range Current range
	 */
	getTag: function(range) {
		try {
			if(!WYSIWYG_Core.isMSIE) {
				var node = range.startContainer;
				var pos = range.startOffset;
				if(node.nodeType != 3) { node = node.childNodes[pos]; }

				if(node.nodeName && node.nodeName.search(/#/) != -1) {
					return node.parentNode;
				}
				return node;
			}
			else {
				if(range.length > 0) {
					return range.item(0);
				}
				else if(range.parentElement()) {
					return range.parentElement();
				}
			}
			return null;
		}
		catch(e) {
			return null;
		}
	},

	/**
	 * Get the parent node of the given node
	 *
	 * @param {DOMElement} element - Element which parent will be returned
	 */
	getParent: function(element) {
		if(element.parentNode) {
			return element.parentNode;
		}
		return null;
	},

	/* ---------------------------------------------------------------------- *\
	  Function	: getTextRange()
	  Description : Get the text range object of the given element
	  Usage	   : WYSIWYG.getTextRange(element)
	  Arguments   : element - An element of which you get the text range object
	\* ---------------------------------------------------------------------- */
	getTextRange: function(element){
		var range = element.parentTextEdit.createTextRange();
		range.moveToElementText(element);
		return range;
	},

	/* ---------------------------------------------------------------------- *\
	  Function	: invertIELineBreakCapability()
	  Description : Inverts the line break capability of IE (Thx to richyrich)
	  				Normal: ENTER = <p> , SHIFT + ENTER = <br>
	  				Inverted: ENTER = <br>, SHIFT + ENTER = <p>
	  Usage	   : WYSIWYG.invertIELineBreakCapability(n)
	  Arguments   : n   - The editor identifier (the textarea's ID)
	\* ---------------------------------------------------------------------- */
	invertIELineBreakCapability: function(n) {

		var editor = this.getEditorWindow(n);
		var sel;
		// validate if the press key is the carriage return key
		if (editor.event.keyCode==13) {
			if (!editor.event.shiftKey) {
				sel = this.getRange(this.getSelection(n), n);
				sel.pasteHTML("<br>");
				editor.event.cancelBubble = true;
				editor.event.returnValue = false;
				sel.select();
				sel.moveEnd("character", 1);
				sel.moveStart("character", 1);
				sel.collapse(false);
				return false;
			}
			else {
				sel = this.getRange(this.getSelection(n), n);
				sel.pasteHTML("<p>");
				editor.event.cancelBubble = true;
				editor.event.returnValue = false;
				sel.select();
				sel.moveEnd("character", 1);
				sel.moveStart("character", 1);
				sel.collapse(false);
				return false;
			}
		}
	},

	/* ---------------------------------------------------------------------- *\
	  Function	: selectNode()
	  Description : Select a node within the current editor
	  Usage	   : WYSIWYG.selectNode(n, level)
	  Arguments   : n   - The editor identifier (the textarea's ID)
	  				level - identifies the level of the element which will be selected
	\* ---------------------------------------------------------------------- */
	selectNode: function(n, level) {

		var sel = this.getSelection(n);
		var range = this.getRange(sel, n);
		var parentnode = this.getTag(range);
		var i = 0;

		for (var node=parentnode; (node && (node.nodeType == 1)); node=node.parentNode) {
			if (i == level) {
				this.nodeSelection(n, node);
			}
			i++;
		}

		this.updateStatusBar(n);
	},

	/* ---------------------------------------------------------------------- *\
	  Function	: nodeSelection()
	  Description : Do the node selection
	  Usage	   : WYSIWYG.nodeSelection(n, node)
	  Arguments   : n   - The editor identifier (the textarea's ID)
	  				node - The node which will be selected
	\* ---------------------------------------------------------------------- */
	nodeSelection: function(n, node) {

		var doc = this.getEditorWindow(n).document;
		var sel = this.getSelection(n);
		var range = this.getRange(sel, n);

		if(!WYSIWYG_Core.isMSIE) {
			if (node.nodeName == "BODY") {
				range.selectNodeContents(node);
			} else {
				range.selectNode(node);
			}

			/*
			if (endNode) {
				try {
					range.setStart(node, startOffset);
					range.setEnd(endNode, endOffset);
				} catch(e) {
				}
			}
			*/

			if (sel) { sel.removeAllRanges(); }
			if (sel) { sel.addRange(range);	 }
		}
		else {
			// MSIE may not select everything when BODY is selected -
			// start may be set to first text node instead of first non-text node -
			// no known workaround
			if ((node.nodeName == "TABLE") || (node.nodeName == "IMG") || (node.nodeName == "INPUT") || (node.nodeName == "SELECT") || (node.nodeName == "TEXTAREA")) {
				try {
					range = doc.body.createControlRange();
					range.addElement(node);
					range.select();
				}
				catch(e) { }
			}
			else {
				range = doc.body.createTextRange();
				if (range) {
					range.collapse();
					if (range.moveToElementText) {
						try {
							range.moveToElementText(node);
							range.select();
						} catch(e) {
							try {
								range = doc.body.createTextRange();
								range.moveToElementText(node);
								range.select();
							}
							catch(e) {}
						}
					} else {
						try {
							range = doc.body.createTextRange();
							range.moveToElementText(node);
							range.select();
						}
						catch(e) {}
					}
				}
			}
		}
	}
}

/********************************************************************
 * openWYSIWYG core functions Copyright (c) 2006 openWebWare.com
 * Contact us at devs@openwebware.com
 * This copyright notice MUST stay intact for use.
 *
 * $Id: wysiwyg.js,v 1.22 2007/09/08 21:45:57 xhaggi Exp $
 ********************************************************************/
var WYSIWYG_Core = {

	/**
	 * Holds true if browser is MSIE
	 */
	isMSIE: (navigator.appName == "Microsoft Internet Explorer"),

	/**
	 * Holds true if browser is Opera
	 */
	isOpera: (navigator.appName == "Opera"),

	/**
	 * Holds true if browser is Firefox (Mozilla)
	 */
	isFF: (!document.all && document.getElementById && navigator.appName != "Opera"),

	/**
	 * Trims whitespaces of the given string
	 *
	 * @param str String
	 * @return Trimmed string
	 */
	trim: function(str) {
		return str.replace(/^\s*|\s*$/g,"");
	},

	/**
	 * Determine if the given parameter is defined
	 *
	 * @param p Parameter
	 * @return true/false dependents on definition of the parameter
	 */
	defined: function(p) {
		return typeof p == "undefined" ? false : true;
	},

	/**
	 * Determine if the browser version is compatible
	 *
	 * @return true/false depending on compatiblity of the browser
	 */
	isBrowserCompatible: function() {
		// Validate browser and compatiblity
		if (!document.getElementById || !document.designMode || (!document.selection && typeof document.createRange === "undefined")) {
			//no designMode (Safari lies)
	   		return false;
		}
		return true;
	},

	/**
	 * Set the style attribute of the given element.
	 * Private method to solve the IE bug while setting the style attribute.
	 *
	 * @param {DOMElement} node The element on which the style attribute will affect
	 * @param {String} style Stylesheet which will be set
	 */
	_setStyleAttribute: function(node, style) {
		if(style == null) return;
		var styles = style.split(";");
		var pos;
		for(var i=0;i<styles.length;i++) {
			var attributes = styles[i].split(":");
			if(attributes.length == 2) {
				try {
					var attr = WYSIWYG_Core.trim(attributes[0]);
					while((pos = attr.search(/-/)) != -1) {
						var strBefore = attr.substring(0, pos);
						var strToUpperCase = attr.substring(pos + 1, pos + 2);
						var strAfter = attr.substring(pos + 2, attr.length);
						attr = strBefore + strToUpperCase.toUpperCase() + strAfter;
					}
					var value = WYSIWYG_Core.trim(attributes[1]).toLowerCase();
					node.style[attr] = value;
				}
				catch (e) {
					alert(e);
				}
			}
		}
	},

	/**
	 * Fix's the issue while getting the attribute style on IE
	 * It's return an object but we need the style string
	 *
	 * @private
	 * @param {DOMElement} node Node element
	 * @return {String} Stylesheet
	 */
	_getStyleAttribute: function(node) {
		if(this.isMSIE) {
			return node.style['cssText'].toLowerCase();
		}
		else {
			return node.getAttribute("style");
		}
	},

	/**
	 * Set an attribute's value on the given node element.
	 *
	 * @param {DOMElement} node Node element
	 * @param {String} attr Attribute which is set
	 * @param {String} value Value of the attribute
	 */
	setAttribute: function(node, attr, value) {
		if(value == null || node == null || attr == null) return;
		if(attr.toLowerCase() == "style") {
			this._setStyleAttribute(node, value);
		}
		else {
			node.setAttribute(attr, value);
		}
	},

	/**
	 * Removes an attribute on the given node
	 *
	 * @param {DOMElement} node Node element
	 * @param {String} attr Attribute which will be removed
	 */
	removeAttribute: function(node, attr) {
		node.removeAttribute(attr, false);
	},

	/**
	 * Get the vale of the attribute on the given node
	 *
	 * @param {DOMElement} node Node element
	 * @param {String} attr Attribute which value will be returned
	 */
	getAttribute: function(node, attr) {
		if(node == null || attr == null) return;
		if(attr.toLowerCase() == "style") {
			return this._getStyleAttribute(node);
		}
		else {
			return node.getAttribute(attr);
		}
	},

	/**
	 * Get the path out of an given url
	 *
	 * @param {String} url The url with is used to get the path
	 */
	getDocumentPathOfUrl: function(url) {
		var path = null;

		// if local file system, convert local url into web url
		url = url.replace(/file:\/\//gi, "file:///");
		url = url.replace(/\\/gi, "\/");
		var pos = url.lastIndexOf("/");
		if(pos != -1) {
			path = url.substring(0, pos + 1);
		}
		return path;
	},

	/**
	 * Get the documents url, convert local urls to web urls
	 *
	 * @param {DOMElement} doc Document which is used to get the url
	 */
	getDocumentUrl: function(doc) {
		// if local file system, convert local url into web url
		var url = doc.URL;
		url = url.replace(/file:\/\//gi, "file:///");
		url = url.replace(/\\/gi, "\/");
		return url;
	},

	/**
	 * Find a parent node with the given name, of the given start node
	 *
	 * @param {String} tagName - Tag name of the node to find
	 * @param {DOMElement} node - Node element
	 */
	findParentNode: function(tagName, node) {
		while (node.tagName != "HTML") {
	  		if (node.tagName == tagName){
	  			return node;
	  		}
	  		node = node.parentNode;
	 	}
	 	return null;
	},

	/**
	 * Cancel the given event.
	 *
	 * @param e Event which will be canceled
	 */
	cancelEvent: function(e) {
		if (!e) return false;
		if (window.event) {
			window.event.returnValue = false;
			window.event.cancelBubble = true;
		} else if (e && e.stopPropagation && e.preventDefault) {
			e.preventDefault();
			e.stopPropagation();
		}
		return false;
	},

	/**
	 * Converts a RGB color string to hex color string.
	 *
	 * @param color RGB color string
	 * @param Hex color string
	 */
	toHexColor: function(color) {
		color = color.replace(/^rgb/g,'');
		color = color.replace(/\(/g,'');
		color = color.replace(/\)/g,'');
		color = color.replace(/ /g,'');
		color = color.split(',');
		var r = parseFloat(color[0]).toString(16).toUpperCase();
		var g = parseFloat(color[1]).toString(16).toUpperCase();
		var b = parseFloat(color[2]).toString(16).toUpperCase();
		if (r.length<2) { r='0'+r; }
		if (g.length<2) { g='0'+g; }
		if (b.length<2) { b='0'+b; }
		return r + g + b;
	},

	/**
	 * Converts a decimal color to hex color string.
	 *
	 * @param Decimal color
	 * @param Hex color string
	 */
	_dec_to_rgb: function(value) {
		var hex_string = "";
		for (var hexpair = 0; hexpair < 3; hexpair++) {
			var myByte = value & 0xFF;			// get low byte
			value >>= 8;						  // drop low byte
			var nybble2 = myByte & 0x0F;		  // get low nybble (4 bits)
			var nybble1 = (myByte >> 4) & 0x0F;   // get high nybble
			hex_string += nybble1.toString(16);   // convert nybble to hex
			hex_string += nybble2.toString(16);   // convert nybble to hex
		}
		return hex_string.toUpperCase();
	},

	/**
	 * Replace RGB color strings with hex color strings within a string.
	 *
	 * @param {String} str RGB String
	 * @param {String} Hex color string
	 */
	replaceRGBWithHexColor: function(str) {
		if(str == null) return "";
		// find all decimal color strings
		var matcher = str.match(/rgb\([0-9 ]+,[0-9 ]+,[0-9 ]+\)/gi);
		if(matcher) {
			for(var j=0; j<matcher.length;j++) {
				var regex = new RegExp(WYSIWYG_Core.stringToRegex(matcher[j]), "gi");
				// replace the decimal color strings with hex color strings
				str = str.replace(regex, "#" + this.toHexColor(matcher[j]));
			}
		}
		return str;
	},

	/**
	 * Execute the given command on the given editor
	 *
	 * @param n The editor's identifier
	 * @param cmd Command which is execute
	 */
	execCommand: function(n, cmd, value) {
		if(typeof(value) == "undefined") value = null;

		// firefox BackColor problem fixed
		if(cmd == 'BackColor' && WYSIWYG_Core.isFF) cmd = 'HiliteColor';

		// firefox cut, paste and copy
		if(WYSIWYG_Core.isFF && (cmd == "Cut" || cmd == "Paste" || cmd == "Copy")) {
			try {
				WYSIWYG.getEditorWindow(n).document.execCommand(cmd, false, value);
			}
			catch(e) {
				alert("Copy, Cut and Paste is not available in this browser.");
			}
		}

		else {
			WYSIWYG.getEditorWindow(n).document.execCommand(cmd, false, value);
		}
	},

	/**
	 * Parse a given string to a valid regular expression
	 *
	 * @param {String} string String to be parsed
	 * @return {RegEx} Valid regular expression
	 */
	stringToRegex: function(string) {

		string = string.replace(/\//gi, "\\/");
		string = string.replace(/\(/gi, "\\(");
		string = string.replace(/\)/gi, "\\)");
		string = string.replace(/\[/gi, "\\[");
		string = string.replace(/\]/gi, "\\]");
		string = string.replace(/\+/gi, "\\+");
		string = string.replace(/\$/gi, "\\$");
		string = string.replace(/\*/gi, "\\*");
		string = string.replace(/\?/gi, "\\?");
		string = string.replace(/\^/gi, "\\^");
		string = string.replace(/\\b/gi, "\\\\b");
		string = string.replace(/\\B/gi, "\\\\B");
		string = string.replace(/\\d/gi, "\\\\d");
		string = string.replace(/\\B/gi, "\\\\B");
		string = string.replace(/\\D/gi, "\\\\D");
		string = string.replace(/\\f/gi, "\\\\f");
		string = string.replace(/\\n/gi, "\\\\n");
		string = string.replace(/\\r/gi, "\\\\r");
		string = string.replace(/\\t/gi, "\\\\t");
		string = string.replace(/\\v/gi, "\\\\v");
		string = string.replace(/\\s/gi, "\\\\s");
		string = string.replace(/\\S/gi, "\\\\S");
		string = string.replace(/\\w/gi, "\\\\w");
		string = string.replace(/\\W/gi, "\\\\W");

		return string;
	},

	/**
	 * Add an event listener
	 *
	 * @param obj Object on which the event will be attached
	 * @param ev Kind of event
	 * @param fu Function which is execute on the event
	 */
	addEvent: function(obj, ev, fu) {
		if (obj.attachEvent)
			obj.attachEvent("on" + ev, fu);
		else
			obj.addEventListener(ev, fu, false);
	},

	/**
	 * Remove an event listener
	 *
	 * @param obj Object on which the event will be attached
	 * @param ev Kind of event
	 * @param fu Function which is execute on the event
	 */
	removeEvent:  function(obj, ev, fu) {
		if (obj.attachEvent)
			obj.detachEvent("on" + ev, fu);
		else
			obj.removeEventListener(ev, fu, false);
	},

	/**
	 * Get the screen position of the given element.
	 *
	 * @param {HTMLObject} elm1 Element which position will be calculate
	 * @param {HTMLObject} elm2 Element which is the last one before calculation stops
	 * @param {Object} Left and top position of the given element
	 */
	getElementPosition: function(elm1, elm2) {
		var top = 0, left = 0;
		while (elm1 && elm1 != elm2) {
			left += elm1.offsetLeft;
			top += elm1.offsetTop;
			elm1 = elm1.offsetParent;
		}
		return {left : left, top : top};
	},

	/**
	 * Get the window size
	 * @private
	 */
	windowSize: function() {
		var e = window, a = 'inner', w = 'Width', d = document;
		if (! (a + w in e)) {
			a = 'client';
			e = (d.documentElement[a+w]) ? d.documentElement : d.body;
		}
		return {width: e[a + w], height: e[a + 'Height']};
	}

}

/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *  - GNU General Public License Version 2 or later (the "GPL")
 *	http://www.gnu.org/licenses/gpl.html
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *	http://www.gnu.org/licenses/lgpl.html
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *	http://www.mozilla.org/MPL/MPL-1.1.html
 * == END LICENSE ==
 *
 * Format the HTML.
 */
var WYSIWYG_Beautifier = {

	Init: function() {
		var oRegex = this.Regex = new Object();

		// Regex for line breaks.
		oRegex.BlocksOpener = /\<(P|DIV|H1|H2|H3|H4|H5|H6|ADDRESS|PRE|OL|UL|LI|TITLE|META|LINK|BASE|SCRIPT|LINK|TD|TH|AREA|OPTION)[^\>]*\>/gi ;
		oRegex.BlocksCloser = /\<\/(P|DIV|H1|H2|H3|H4|H5|H6|ADDRESS|PRE|OL|UL|LI|TITLE|META|LINK|BASE|SCRIPT|LINK|TD|TH|AREA|OPTION)[^\>]*\>/gi ;

		oRegex.NewLineTags	= /\<(BR|HR)[^\>]*\>/gi ;

		oRegex.MainTags = /\<\/?(HTML|HEAD|BODY|FORM|TABLE|TBODY|THEAD|TR)[^\>]*\>/gi ;

		oRegex.LineSplitter = /\s*\n+\s*/g ;

		// Regex for indentation.
		oRegex.IncreaseIndent = /^\<(HTML|HEAD|BODY|FORM|TABLE|TBODY|THEAD|TR|UL|OL)[ \/\>]/i ;
		oRegex.DecreaseIndent = /^\<\/(HTML|HEAD|BODY|FORM|TABLE|TBODY|THEAD|TR|UL|OL)[ \>]/i ;
		oRegex.FormatIndentatorRemove = new RegExp( '^' + '  ' ) ;

		oRegex.ProtectedTags = /(<PRE[^>]*>)([\s\S]*?)(<\/PRE>)/gi ;
	},

	_ProtectData: function( outer, opener, data, closer ) {
		return opener + '___VISCACHA_PD___' + this.ProtectedData.AddItem( data ) + closer;
	},

	Format: function( html ) {
		if ( !this.Regex ) this.Init();

		// Protected content that remain untouched during the
		// process go in the following array.
		this.ProtectedData = new Array() ;

		var sFormatted = html.replace( this.Regex.ProtectedTags, this._ProtectData ) ;

		// Line breaks.
		sFormatted		= sFormatted.replace( this.Regex.BlocksOpener, '\n$&' ) ;
		sFormatted		= sFormatted.replace( this.Regex.BlocksCloser, '$&\n' ) ;
		sFormatted		= sFormatted.replace( this.Regex.NewLineTags, '$&\n' ) ;
		sFormatted		= sFormatted.replace( this.Regex.MainTags, '\n$&\n' ) ;

		// Indentation.
		var sIndentation = '' ;

		var asLines = sFormatted.split( this.Regex.LineSplitter ) ;
		sFormatted = '' ;

		for ( var i = 0 ; i < asLines.length ; i++ ) {
			var sLine = asLines[i] ;
			if ( sLine.length == 0 ) continue ;
			if ( this.Regex.DecreaseIndent.test( sLine ) ) sIndentation = sIndentation.replace( this.Regex.FormatIndentatorRemove, '' ) ;
			sFormatted += sIndentation + sLine + '\n' ;
			if ( this.Regex.IncreaseIndent.test( sLine ) ) sIndentation += '  ' ;
		}

		// Now we put back the protected data.
		for ( var j = 0 ; j < this.ProtectedData.length ; j++ ) {
			var oRegex = new RegExp( '___VISCACHA_PD___' + j ) ;
			sFormatted = sFormatted.replace( oRegex, this.ProtectedData[j].replace( /\$/g, '$$$$' ) ) ;
		}

		return sFormatted;
	}
}

/**
 * Table object
 */
var WYSIWYG_Table = {

	/**
	 *
	 */
	create: function(n, table, cols, rows, td_style) {
		// get editor
		var doc = WYSIWYG.getEditorWindow(n).document;

		// get selection and range ( +'' forces the object to a string object for Safari )
		// IE's selection and range objects do not conform to the W3C
		var sel = WYSIWYG.getSelection(n);
		var range = WYSIWYG.getRange(sel, n);
		var td;

		// get element from selection
		if(WYSIWYG_Core.isMSIE) {
			if(sel.type == "Control" && range.length == 1) {
				range = WYSIWYG.getTextRange(range(0));
				range.select();
			}
		}

		// add rows and cols
		for(var i=0;i<rows;i++) {
			var tr = doc.createElement("tr");
			for(var j=0;j<cols;j++){
				td = doc.createElement("td");
				WYSIWYG_Core.setAttribute(td, "style", td_style);
				td.innerHTML = "&nbsp;";
				tr.appendChild(td);
			}
			table.appendChild(tr);
		}

		// Check if IE or Mozilla (other)
		if (WYSIWYG_Core.isMSIE) {
			range.pasteHTML(table.outerHTML);
		}
		else {
			WYSIWYG.insertNodeAtSelection(table, n);
		}

		// refresh table highlighting
		this.refreshHighlighting(n);
	},

	/**
	 * Enables the table highlighting
	 *
	 * @param {String} n The editor identifier (the textarea's ID)
	 */
	refreshHighlighting: function(n) {
		var doc = WYSIWYG.getEditorWindow(n).document;
		var tables = doc.getElementsByTagName("table");
		var tds,j,i;
		for(i=0; i<tables.length; i++) {
			this._enableHighlighting(tables[i], tables[i]);
			tds = tables[i].getElementsByTagName("td");
			for(j=0; j<tds.length; j++) {
				this._enableHighlighting(tds[j], tables[i]);
			}
			tds = tables[i].getElementsByTagName("th");
			for(j=0; j<tds.length; j++) {
				this._enableHighlighting(tds[j], tables[i]);
			}
		}
	},

	/**
	 * Enables the table highlighting
	 *
	 * @param {String} n The editor identifier (the textarea's ID)
	 */
	disableHighlighting: function(n) {
		var doc = WYSIWYG.getEditorWindow(n).document;
		var tables = doc.getElementsByTagName("table");
		var tds,j,i;
		for(i=0; i<tables.length; i++) {
			this._disableHighlighting(tables[i]);
			tds = tables[i].getElementsByTagName("td");
			for(j=0; j<tds.length; j++) {
				this._disableHighlighting(tds[j]);
			}
			tds = tables[i].getElementsByTagName("th");
			for(j=0; j<tds.length; j++) {
				this._disableHighlighting(tds[j]);
			}
		}

	},

	_hasTableBorder: function(style, border) {
		var hasBorder = false;
		var hasStyle = false;
		if (typeof style == 'string') {
			var style_borders = style.match(/(border(-(top|left|right|bottom))?:[^;]+|border-((top|left|right|bottom)-)?width:[^;]+)/ig);
			if (style_borders) {
				hasStyle = true;
				for (var i = 0; i < style_borders.length; i++) {
					widths = style_borders[i].match(/(\d+(px|pc|pt)|\d+(.\d+)?(ex|em|cm|in|mm|%))/ig);
					if (widths) {
						for (var j = 0; j < widths.length; j++) {
							number = widths[j].replace(/(px|pc|pt|ex|em|cm|in|mm|%)/ig, '');
							number = parseFloat(number);
							if (number > 0) {
								hasBorder = true;
								break;
							}
						}
					}
				}
			}
		}
		if (hasStyle == false && border > 0) {
			hasBorder = true;
		}
		return hasBorder;
	},

	/**
	 * @private
	 */
	_enableHighlighting: function(node, table) {
		var style = WYSIWYG_Core.getAttribute(node, "style");
		var border = WYSIWYG_Core.getAttribute(table, "border");
		if(!style) {
			style = "";
		}
		WYSIWYG_Core.removeAttribute(node, "prevstyle");
		if (this._hasTableBorder(style, border) == false && node != table) {
			WYSIWYG_Core.setAttribute(node, "prevstyle", style);
			WYSIWYG_Core.setAttribute(node, "style", "border:1px dashed #AAAAAA;");
		}
	},

	/**
	 * @private
	 */
	_disableHighlighting: function(node) {
		var style = WYSIWYG_Core.getAttribute(node, "prevstyle");
		// if no prevstyle is defined, the table is not in highlighting mode
		if(style) {
			WYSIWYG_Core.removeAttribute(node, "prevstyle");
			WYSIWYG_Core.removeAttribute(node, "style");
			WYSIWYG_Core.setAttribute(node, "style", style);
		}
	}
}

/**
 * Get an element by it's identifier
 *
 * @param id Element identifier
 */
function $(id) {
	return FetchElement(id);
}

/**
* Emulates insertAdjacentHTML(), insertAdjacentText() and
* insertAdjacentElement() three functions so they work with Netscape 6/Mozilla/Safari
* by Thor Larholm me@jscript.dk
*
* Modified: 6/11/2008 By CJBoCo
*  - Instead of testing for browser type, we test if the object is undefined
*/
if (typeof HTMLElement !== "undefined") {

	if (typeof HTMLElement.insertAdjacentHTML === "undefined") {
		HTMLElement.prototype.insertAdjacentElement = function(where, parsedNode) {
			switch (where) {
			case 'beforeBegin':
				this.parentNode.insertBefore(parsedNode, this);
				break;
			case 'afterBegin':
				this.insertBefore(parsedNode, this.firstChild);
				break;
			case 'beforeEnd':
				this.appendChild(parsedNode);
				break;
			case 'afterEnd':
				if (this.nextSibling) {
					this.parentNode.insertBefore(parsedNode, this.nextSibling);
				} else {
					this.parentNode.appendChild(parsedNode);
				}
				break;
			}
		};
	}

	if (typeof HTMLElement.insertAdjacentHTML === "undefined") {
		HTMLElement.prototype.insertAdjacentHTML = function(where, htmlStr) {
			var r = this.ownerDocument.createRange();
			r.setStartBefore(this);
			var parsedHTML = r.createContextualFragment(htmlStr);
			this.insertAdjacentElement(where, parsedHTML);
		};
	}

	if (typeof HTMLElement.insertAdjacentText === "undefined") {
		HTMLElement.prototype.insertAdjacentText = function(where, txtStr) {
			var parsedText = document.createTextNode(txtStr);
			this.insertAdjacentElement(where, parsedText);
		};
	}
}

// Config
var full = new WYSIWYG.Settings();
full.addToolbarElement("font", 3, 1);
full.addToolbarElement("fontsize", 3, 2);
full.addToolbarElement("headings", 3, 3);