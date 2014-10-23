/********************************************************************
 * openWYSIWYG popup functions Copyright (c) 2006 openWebWare.com
 * Contact us at devs@openwebware.com
 * This copyright notice MUST stay intact for use.
 *
 * $Id: wysiwyg-popup.js,v 1.2 2007/01/22 23:45:30 xhaggi Exp $
 ********************************************************************/
var WYSIWYG_Popup = {

	/**
	 * Return the value of an given URL parameter.
	 *
	 * @param param Parameter
	 * @return Value of the given parameter
	 */
	getParam: function(param) {
	  var query = window.location.search.substring(1);
	  var parms = query.split('&');
	  for (var i=0; i<parms.length; i++) {
	    var pos = parms[i].indexOf('=');
	    if (pos > 0) {
	       var key = parms[i].substring(0,pos).toLowerCase();
	       var val = parms[i].substring(pos+1);
	       if(key == param.toLowerCase())
	       	return val;
	    }
	  }
	  return null;
	}
}

// close the popup if the opener does not hold the WYSIWYG object
if(!window.opener) window.close();

// bind objects on local vars
var WYSIWYG = window.opener.WYSIWYG;
var WYSIWYG_Core = window.opener.WYSIWYG_Core;
var WYSIWYG_Table = window.opener.WYSIWYG_Table;


/**
 * Inverts a given hex color.
 */
function invert(color) {
	var t1 = '0123456789abcdef#'
	var t2 = 'fedcba9876543210#'
	color = color.replace( /./gi, function (s) { return t2.charAt(t1.indexOf(s)); });
	return color;
}

/**
 * Loads the color in the form field
 */
function loadColor() {
	document.getElementById('enterColor').value = "#" + WYSIWYG_Popup.getParam('color');
}

/* ---------------------------------------------------------------------- *\
  Function    : selectColor()
  Description : Selects the color and inserts it into the WYSIWYG
\* ---------------------------------------------------------------------- */
function selectColor(color) {
	// get params
	var n = WYSIWYG_Popup.getParam('wysiwyg');
	var cmd = WYSIWYG_Popup.getParam('command');
	var doc = WYSIWYG.getEditorWindow(n).document;
	// execute command
	WYSIWYG_Core.execCommand(n, cmd, color);
	// close window
    window.close();
}


/* ---------------------------------------------------------------------- *\
  Function    : previewColor()
  Description : Updates the preview pane as the user mouses over different colors
\* ---------------------------------------------------------------------- */
function previewColor(color) {
	document.getElementById('enterColor').value = color;
	document.getElementById('PreviewColor').style.backgroundColor = color;
	document.getElementById('PreviewColor').style.color = this.invert(color);
}

/* ---------------------------------------------------------------------- *\
  Function    : insertImage()
  Description : Inserts image into the WYSIWYG.
\* ---------------------------------------------------------------------- */
function insertImage() {
	var n = WYSIWYG_Popup.getParam('wysiwyg');

	// get values from form fields
	var src = document.getElementById('src').value;
	var alt = document.getElementById('alt').value;
	var width = document.getElementById('width').value
	var height = document.getElementById('height').value
	var border = document.getElementById('border').value
	var align = document.getElementById('align').value
	var vspace = document.getElementById('vspace').value
	var hspace = document.getElementById('hspace').value
	var bordercolor = document.getElementById('bordercolor').value

	// insert image
	WYSIWYG.insertImage(src, width, height, align, border, alt, hspace, vspace, bordercolor, n);
  	window.close();
}

/* ---------------------------------------------------------------------- *\
  Function    : loadImage()
  Description : load the settings of a selected image into the form fields
\* ---------------------------------------------------------------------- */
function loadImage() {
	var n = WYSIWYG_Popup.getParam('wysiwyg');

	// get selection and range
	var sel = WYSIWYG.getSelection(n);
	var range = WYSIWYG.getRange(sel);

	// the current tag of range
	var img = WYSIWYG.findParent("img", range);

	// if no image is defined then return
	if(img == null) return;

	// assign the values to the form elements
	for(var i = 0;i < img.attributes.length;i++) {
		var attr = img.attributes[i].name.toLowerCase();
		var value = img.attributes[i].value;
		//alert(attr + " = " + value);
		if(attr && value && value != "null") {
			switch(attr) {
				case "src":
					// strip off urls on IE
					if(WYSIWYG_Core.isMSIE) value = WYSIWYG.stripURLPath(n, value, false);
					document.getElementById('src').value = value;
				break;
				case "alt":
					document.getElementById('alt').value = value;
				break;
				case "align":
					selectItemByValue(document.getElementById('align'), value);
				break;
				case "border":
					document.getElementById('border').value = value;
				break;
				case "hspace":
					document.getElementById('hspace').value = value;
				break;
				case "vspace":
					document.getElementById('vspace').value = value;
				break;
				case "width":
					document.getElementById('width').value = value;
				break;
				case "height":
					document.getElementById('height').value = value;
				break;
			}
		}
	}

	// get width and height from style attribute in none IE browsers
	if(!WYSIWYG_Core.isMSIE && document.getElementById('width').value == "" && document.getElementById('height').value == "") {
		document.getElementById('width').value = img.style.width.replace(/px/i, "");
		document.getElementById('height').value = img.style.height.replace(/px/i, "");
	}

	if(!WYSIWYG_Core.isMSIE && document.getElementById('border').value == "") {
		document.getElementById('border').value = img.style.borderWidth.replace(/px/i, "");
	}
	document.getElementById('bordercolor').value = img.style.borderColor;
}

/* ---------------------------------------------------------------------- *\
  Function    : selectItem()
  Description : Select an item of an select box element by value.
\* ---------------------------------------------------------------------- */
function selectItemByValue(element, value) {
	if(element.options.length) {
		for(var i=0;i<element.options.length;i++) {
			if(element.options[i].value == value) {
				element.options[i].selected = true;
			}
		}
	}
}

/* ---------------------------------------------------------------------- *\
  Function    : createHR()
  Description : Creates a horizontal Ruler in WYSIWYG Editor
\* ---------------------------------------------------------------------- */
function createHR(n) {
	if (document.hr_form.widthgroup.value == 3) {
  		var varWidth = document.hr_form.width.value + '%';
  	} else {
    	var varWidth = document.hr_form.width.value;
  	}

	if (document.hr_form.widthgroup.value == 1) {
		var varAlign = '';
	} else if (document.hr_form.align.value == 1) {
		var varAlign = 'center';
	} else if (document.hr_form.align.value == 2) {
		var varAlign = 'left';
	} else if (document.hr_form.align.value == 3) {
		var varAlign = 'right';
	}

	WYSIWYG.insertHR(varWidth, document.hr_form.height.value, document.hr_form.shade.checked, varAlign, n);
	window.close();
}

/* ---------------------------------------------------------------------- *\
  Function    : insertHyperLink() (changed)
  Description : Insert the link into the iframe html area
\* ---------------------------------------------------------------------- */
function insertHyperLink(n) {
	// get values from form fields
	var href = document.getElementById('linkUrl').value;
	var target = document.getElementById('linkTarget').value;
	var name = document.getElementById('linkName').value;

  	// insert link
	WYSIWYG.insertLink(href, target, '', '', name, n);
	window.close();
}

/* ---------------------------------------------------------------------- *\
  Function    : loadLink() (new)
  Description : Load the link attributes to the form
\* ---------------------------------------------------------------------- */
function loadLink() {
	// get params
	var n = WYSIWYG_Popup.getParam('wysiwyg');

	// get selection and range
	var sel = WYSIWYG.getSelection(n);
	var range = WYSIWYG.getRange(sel);
	var lin = null;
	if(WYSIWYG_Core.isMSIE) {
		if(sel.type == "Control" && range.length == 1) {
			range = WYSIWYG.getTextRange(range(0));
			range.select();
		}
		if (sel.type == 'Text' || sel.type == 'None') {
			sel = WYSIWYG.getSelection(n);
			range = WYSIWYG.getRange(sel);
			// find a as parent element
			lin = WYSIWYG.findParent("a", range);
		}
	}
	else {
		// find a as parent element
		lin = WYSIWYG.findParent("a", range);
	}

	// if no link as parent found exit here
	if(lin == null) return;

	// set form elements with attribute values
	for(var i=0; i<lin.attributes.length; i++) {
		var attr = lin.attributes[i].name.toLowerCase();
		var value = lin.attributes[i].value;
		if(attr && value && value != "null") {
			switch (attr) {
				case "href":
					// strip off urls on IE
					if(WYSIWYG_Core.isMSIE) value = WYSIWYG.stripURLPath(n, value, false);
					document.getElementById('linkUrl').value = value;
				break;
				case "target":
					document.getElementById('linkTarget').value = value;
					selectItemByValue(document.getElementById('linkTargetChooser'), value);
				break;
				case "name":
					document.getElementById('linkName').value = value;
				break;
			}
		}
	}
}

/* ---------------------------------------------------------------------- *\
  Function    : updateTarget() (new)
  Description : Updates the target text field
  Arguments   : value - Value to be set
\* ---------------------------------------------------------------------- */
function updateTarget(value) {
	document.getElementById('linkTarget').value = value;
}

/* ---------------------------------------------------------------------- *\
  Function    : selectItem()
  Description : Select an item of an select box element by value.
\* ---------------------------------------------------------------------- */
function selectItemByValue(element, value) {
	if(element.options.length) {
		for(var i=0;i<element.options.length;i++) {
			if(element.options[i].value == value) {
				element.options[i].selected = true;
				return;
			}
		}
		element.options[(element.options.length-1)].selected = true;
	}
}

/* ---------------------------------------------------------------------- *\
  Function    : buildTable()
  Description : Builds a table and inserts it into the WYSIWYG.
\* ---------------------------------------------------------------------- */
function buildTable(n) {
	// Get all information
	var collapse = document.getElementById("bordercollapse").checked ? "collapse" : "separate";
	var width = document.getElementById("width").value;
	var bgc = document.getElementById("backgroundcolor").value;
	var align = document.getElementById("alignment").value;
	var bWidth = document.getElementById('borderwidth').value;
	var bStyle = document.getElementById('borderstyle').value;
	var bColor = document.getElementById('bordercolor').value;
	var padding = document.getElementById('padding').value;
	// Construct table style
	var style = 'border-collapse:' + collapse + ';';
	if (width > 0) {
		style += "width:" + width + document.getElementById("widthType").value + ";";
	}
	if(bgc != "none" && bgc != '') {
		style += "background-color:" + bgc + ";";
	}
	// Construct td style
	var td_style = '';
	if (bColor != "none" && bColor != "" && bWidth > 0 && bStyle != 'none' && bStyle != '') {
		td_style += 'border: ' + bWidth + 'px ' +  bStyle + ' ' +  bColor + ';';
	}
	if (padding != '') {
		td_style += 'padding: ' + padding + 'px;';
	}
	// Construct table
	var WYSIWYG_Table = window.opener.WYSIWYG_Table;
	var doc = WYSIWYG.getEditorWindow(n).document;
	var table = doc.createElement("TABLE");
	// set cols and rows
	WYSIWYG_Core.setAttribute(table, "style", style);
	if(align != "") {
		WYSIWYG_Core.setAttribute(table, "align", align);
	}

	// Inserts the table code into the WYSIWYG editor
	WYSIWYG_Table.create(n, table, document.getElementById("cols").value, document.getElementById("rows").value, td_style);
	window.close();
}