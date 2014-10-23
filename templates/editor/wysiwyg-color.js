/********************************************************************
 * openWYSIWYG color chooser Copyright (c) 2006 openWebWare.com
 * Contact us at devs@openwebware.com
 * This copyright notice MUST stay intact for use.
 *
 * $Id: wysiwyg-color.js,v 1.1 2007/01/29 19:19:49 xhaggi Exp $
 ********************************************************************/
function WYSIWYG_Color() {

	// colors
	var COLORS = new Array("#330000","#333300","#336600","#339900","#33CC00","#33FF00","#66FF00","#66CC00","#669900","#666600","#663300","#660000","#FF0000","#FF3300","#FF6600","#FF9900","#FFCC00","#FFFF00","#330033","#333333","#336633","#339933","#33CC33","#33FF33","#66FF33","#66CC33","#669933","#666633","#663333","#660033","#FF0033","#FF3333","#FF6633","#FF9933","#FFCC33","#FFFF33","#330066","#333366","#336666","#339966","#33CC66","#33FF66","#66FF66","#66CC66","#669966","#666666","#663366","#660066","#FF0066","#FF3366","#FF6666","#FF9966","#FFCC66","#FFFF66","#330099","#333399","#336699","#339999","#33CC99","#33FF99","#66FF99","#66CC99","#669999","#666699","#663399","#660099","#FF0099","#FF3399","#FF6699","#FF9999","#FFCC99","#FFFF99","#3300CC","#3333CC","#3366CC","#3399CC","#33CCCC","#33FFCC","#66FFCC","#66CCCC","#6699CC","#6666CC","#6633CC","#6600CC","#FF00CC","#FF33CC","#FF66CC","#FF99CC","#FFCCCC","#FFFFCC","#3300FF","#3333FF","#3366FF","#3399FF","#33CCFF","#33FFFF","#66FFFF","#66CCFF","#6699FF","#6666FF","#6633FF","#6600FF","#FF00FF","#FF33FF","#FF66FF","#FF99FF","#FFCCFF","#FFFFFF","#0000FF","#0033FF","#0066FF","#0099FF","#00CCFF","#00FFFF","#99FFFF","#99CCFF","#9999FF","#9966FF","#9933FF","#9900FF","#CC00FF","#CC33FF","#CC66FF","#CC99FF","#CCCCFF","#CCFFFF","#0000CC","#0033CC","#0066CC","#0099CC","#00CCCC","#00FFCC","#99FFCC","#99CCCC","#9999CC","#9966CC","#9933CC","#9900CC","#CC00CC","#CC33CC","#CC66CC","#CC99CC","#CCCCCC","#CCFFCC","#000099","#003399","#006699","#009999","#00CC99","#00FF99","#99FF99","#99CC99","#999999","#996699","#993399","#990099","#CC0099","#CC3399","#CC6699","#CC9999","#CCCC99","#CCFF99","#000066","#003366","#006666","#009966","#00CC66","#00FF66","#99FF66","#99CC66","#999966","#996666","#993366","#990066","#CC0066","#CC3366","#CC6666","#CC9966","#CCCC66","#CCFF66","#000033","#003333","#006633","#009933","#00CC33","#00FF33","#99FF33","#99CC33","#999933","#996633","#993333","#990033","#CC0033","#CC3333","#CC6633","#CC9933","#CCCC33","#CCFF33","#000000","#003300","#006600","#009900","#00CC00","#00FF00","#99FF00","#99CC00","#999900","#996600","#993300","#990000","#CC0000","#CC3300","#CC6600","#CC9900","#CCCC00","#CCFF00","#000000","#333333","#666666","#999999","#cccccc","#ffffff");

	// div id of the color table
	var CHOOSER_DIV_ID = "colorpicker-div";

	/**
	 * Init the color picker
	 */
	this.init = function() {
		var div = document.createElement("DIV");
		div.id = CHOOSER_DIV_ID;
		div.style.position = "absolute";
		div.style.visibility = "hidden";
		document.body.appendChild(div);
	};

	/**
	 * Open the color chooser to choose a color.
	 *
	 * @param {String} element Element identifier
	 */
	this.choose = function(element) {
		var div = document.getElementById(CHOOSER_DIV_ID);
		if(div == null) {
			alert("Initialisation of color picker failed.");
			return;
		}

		// writes the content of the color picker
		write(element);

		// Display color picker
		var x = window.event.clientX + document.body.scrollLeft;
		var y = window.event.clientY + document.body.scrollTop;
		var winsize = windowSize();
		if(x + div.offsetWidth > winsize.width) x = winsize.width - div.offsetWidth - 5;
		if(y + div.offsetHeight > winsize.height) y = winsize.height - div.offsetHeight - 5;
		div.style.left = x + "px";
		div.style.top = y + "px";
		div.style.visibility = "visible";
	};

	/**
	 * Set the color in the given field
	 *
	 * @param {String} n Element identifier
	 * @param {String} color HexColor String
	 */
	this.select = function(n, color) {
		var div = document.getElementById(CHOOSER_DIV_ID);
		var elm = document.getElementById(n);
		elm.value = color;
		elm.style.color = invert(color);
		elm.style.backgroundColor = color;
		div.style.visibility = "hidden";
	}


	/**
	 * Write the color table
	 * @param {String} n Element identifier
	 * @private
	 */
	function write(n) {

		var div = document.getElementById(CHOOSER_DIV_ID);

		var output = "";
		output += '<table border="1" cellpadding="0" cellspacing="0" class="wysiwyg-color-picker-table"><tr>';
		for(var i = 0; i < COLORS.length;i++) {
			var color = COLORS[i];
			output += '<td class="selectColorBorder" ';
			output += 'onmouseover="this.className=\'selectColorOn\';" ';
			output += 'onmouseout="this.className=\'selectColorOff\';" ';
			output += 'onclick="WYSIWYG_ColorInst.select(\'' + n + '\', \'' + color + '\');"> ';
			output += '<div style="background-color:' + color + ';" class="wysiwyg-color-picker-div">&nbsp;</div> ';
			output += '</td>';

			if((i+1) % 18 == 0) {
				output += "</tr><tr>";
			}
		}

		output += '</tr></table>';

		// write to div element
		div.innerHTML = output;
	};

	/**
	 * Set the window.event on Mozilla Browser
	 * @private
	 */
	function _event_tracker(event) {
		if (!document.all && document.getElementById) {
			window.event = event;
		}
	}
	document.onmousedown = _event_tracker;

	/**
	 * Get the window size
	 * @private
	 */
	function windowSize() {
		if (window.innerWidth) {
	  		return {width: window.innerWidth, height: window.innerHeight};
	  	}
		else if (document.body && document.body.offsetWidth) {
	  		return {width: document.body.offsetWidth, height: document.body.offsetHeight};
	  	}
		else {
	  		return {width: 0, height: 0};
	  	}
	}
}

var WYSIWYG_ColorInst = new WYSIWYG_Color();