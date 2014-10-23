// RTE REVAMPED VERSION: 2006/01/13
// This code is public domain. Redistribution and use of this code, with or without modification, is permitted.
// Visit http://fieldspar.com for the latest release.
// Visit the support forums at http://www.kevinroth.com/forums/index.php?c=2

// Fehler:
// IE: Editor-Fenster-Style wird nicht geändert
// Gecko: Höhe beim wechseln zwischen Modus bleibt nicht gleich


// Constants
var minWidth = 640;					// minumum width
var wrapWidth = 1245; 			//width at which all icons will appear on one bar
var maxchar = 64000;		// maximum number of characters per save
var lang = "en"; 						//xhtml language
var encoding = "utf-8";			//xhtml encoding, english only use "iso-8859-1"
var zeroBorder = "#c0c0c0"; //guideline color - see showGuidelines()
var btnText = "submit";			//Button value for non-designMode() & fullsceen rte
var resize_fullsrcreen = true;
// (resize_fullsrcreen) limited in that: 1)won't auto wrap icons. 2)won't
// shrink to less than (wrapWidth)px if screen was initized over (wrapWidth)px;

var keep_absolute = true; // !!!Disabled - see line 456 for details!!!!!
// By default IE will try to convery all hyperlinks to absolute paths. By
// setting this value to "false" it will retain the relative path.

// Pointers
var InsertChar;
var InsertTable;
var InsertLink;
var InsertImg;
var dlgReplace;
var dlgPasteText;
var dlgPasteWord;

// MOD: Added variable
var originalHTMLDesign;

//Init Variables & Attributes
var ua = navigator.userAgent.toLowerCase();
var isIE = ((ua.indexOf("msie") != -1) && (ua.indexOf("opera") == -1) && (ua.indexOf("webtv") == -1))? true:false;
var	isGecko = (ua.indexOf("gecko") != -1)? true:false;
var	isSafari = (ua.indexOf("safari") != -1)? true:false;
var	isKonqueror = (ua.indexOf("konqueror") != -1)? true:false;
var rng;
var currentRTE;
var allRTEs = "";
var obj_width;
var obj_height;
var imagesPath;
var includesPath;
var cssFile;
var generateXHTML = true;
var isRichText = false;
//check to see if designMode mode is available
//Safari/Konqueror think they are designMode capable even though they are not
if(document.getElementById && document.designMode && !isSafari && !isKonqueror) isRichText = true;
//for testing standard textarea, uncomment the following line
//isRichText = false;

function initRTE(imgPath, incPath, css, genXHTML){
	// CM 05/04/05 check args for compatibility with old RTE implementations
	if (arguments.length == 3) {
		genXHTML = generateXHTML;
	}
	//set paths vars
	imagesPath = imgPath;
	includesPath = incPath;
	cssFile = css;
	generateXHTML = genXHTML;
	if(isRichText) document.writeln('<style type="text/css">@import "' + includesPath + 'rte.css";</style>');
	if(isIE){
		document.onmouseover = ie_btnfx;
		document.onmouseout  = ie_btnfx;
		document.onmousedown = ie_btnfx;
		document.onmouseup   = ie_btnfx;
	}
	else{
		minWidth = minWidth-48;
		wrapWidth = wrapWidth-102;
  	}
}

function writeRichText(rte, html, css, width, height, buttons, readOnly, fullscreen) {
	if(isRichText){
		currentRTE = rte;
		if(allRTEs.length > 0) allRTEs += ";";
		allRTEs += rte;
		// CM 06/04/05 stops single quotes from messing everything up
		html=replaceIt(html,'\'','&apos;');
		// CM 05/04/05 a bit of juggling for compatibility with old RTE implementations
		if (arguments.length == 6) {
			fullscreen = false;
			readOnly = buttons;
			buttons = height;
			height = width;
			width = css;
			css = "";
		}
		var iconWrapWidth = wrapWidth;
		if(readOnly) buttons = false;
		if(fullscreen) {
			readOnly = false; // fullscreen is not readOnly and must show buttons
			buttons = true;
			// resize rte on resize if the option resize_fullsrcreen = true.
			if(resize_fullsrcreen) window.onresize = resizeRTE;
			document.body.style.margin = "0px";
			document.body.style.overflow = "hidden";
	  		//adjust maximum table widths
			findSize("");
			width = obj_width;
	  		if(width < iconWrapWidth) {
				height = (obj_height - 83);
	  		}
	  		else{
		  		height = (obj_height - 55);
			}
			if (width < minWidth){
		  		document.body.style.overflow = "auto";
		  		if(isIE){
					height = obj_height-22;
		  		}
		  		else{
					height = obj_height-24;
				}
		 		width = minWidth;
			}
			var tablewidth = width;
		}
		else{
			fullscreen = false;
			iconWrapWidth = iconWrapWidth-25;
			//adjust minimum table widths
			if (buttons && (width < minWidth)) width = minWidth;
			if(isIE){
				var tablewidth = width;
			}
			else{
				var tablewidth = width + 4;
		  	}
	  	}
		var rte_css = "";
		if(css.length > 0) {
			rte_css = css;
		}
		else{
	  		rte_css = cssFile;
		}
		document.writeln('<span class="rteDiv">');
		if(buttons) {
			document.writeln('<table class="rteBk" cellpadding="0" cellspacing="0" id="Buttons1_'+rte+'" width="' + tablewidth + '">');
			document.writeln('<tbody><tr>');
				insertBar();
			document.writeln('<td><select id="formatblock_'+rte+'" onchange="selectFont(\''+rte+'\', this.id);" style="font-size:14px;width:105px;height:20px;margin:1px;">');
			document.writeln(lblFormat);
			document.writeln('</select></td><td>');
			document.writeln('<select id="fontname_'+rte+'" onchange="selectFont(\''+rte+'\', this.id)" style="font-size:14px;width:125px;height:20px;margin:1px;">');
			document.writeln(lblFont);
			document.writeln('</select></td><td>');
			document.writeln('<select unselectable="on" id="fontsize_'+rte+'" onchange="selectFont(\''+rte+'\', this.id);" style="font-size:14px;width:75px;height:20px;margin:1px;">');
			document.writeln(lblSize);
			document.writeln('</select>');
				insertSep();
			if(isIE){
				insertImg(lblCut,"cut.gif","rteCommand('"+rte+"','cut')");
				insertImg(lblCopy,"copy.gif","rteCommand('"+rte+"','copy')");
				insertImg(lblPaste,"paste.gif","rteCommand('"+rte+"','paste')");
			}
			insertImg(lblPasteText,"pastetext.gif","dlgLaunch('"+rte+"','text')");
			insertImg(lblPasteWord,"pasteword.gif","dlgLaunch('"+rte+"','word')");
				insertSep();
			insertImg(lblUndo,"undo.gif","rteCommand('"+rte+"','undo')");
			insertImg(lblRedo,"redo.gif","rteCommand('"+rte+"','redo')");
				insertSep();
			insertImg(lblSelectAll,"selectall.gif","toggleSelection('"+rte+"')");
			insertImg(lblUnformat,"unformat.gif","rteCommand('"+rte+"','removeformat')");
				insertSep();
			insertImg(lblSearch,"replace.gif","dlgLaunch('"+rte+"','replace')");
			insertImg(lblWordCount,"word_count.gif","countWords('"+rte+"')");
			if(isIE)insertImg(lblSpellCheck,"spellcheck.gif","checkspell()");
	   		document.writeln('</td>');
			if(tablewidth < iconWrapWidth){
				document.writeln('<td width="100%"></td></tr></tbody></table>');
				document.writeln('<table class="rteBk" cellpadding="0" cellspacing="0" id="Buttons2_'+rte+'" width="' + tablewidth + '">');
				document.writeln('<tbody><tr>');
			}
				insertBar();
			insertImg(lblBold,"bold.gif","rteCommand('"+rte+"','bold')");
			insertImg(lblItalic,"italic.gif","rteCommand('"+rte+"','italic')");
			insertImg(lblUnderline,"underline.gif","rteCommand('"+rte+"','underline')");
				insertSep();
			insertImg(lblStrikeThrough,"strikethrough.gif","rteCommand('"+rte+"','strikethrough')");
			insertImg(lblSuperscript,"superscript.gif","rteCommand('"+rte+"','superscript')");
			insertImg(lblSubscript,"subscript.gif","rteCommand('"+rte+"','subscript')");
	  			insertSep();
			insertImg(lblAlgnLeft,"left_just.gif","rteCommand('"+rte+"','justifyleft')");
			insertImg(lblAlgnCenter,"centre.gif","rteCommand('"+rte+"','justifcenter')");
			insertImg(lblAlgnRight,"right_just.gif","rteCommand('"+rte+"','justifyright')");
			insertImg(lblJustifyFull,"justifyfull.gif","rteCommand('"+rte+"','justifyfull')");
				insertSep();
			insertImg(lblOL,"numbered_list.gif","rteCommand('"+rte+"','insertorderedlist')");
			insertImg(lblUL,"list.gif","rteCommand('"+rte+"','insertunorderedlist')");
			insertImg(lblOutdent,"outdent.gif","rteCommand('"+rte+"','outdent')");
			insertImg(lblIndent,"indent.gif","rteCommand('"+rte+"','indent')");
				insertSep();
			insertImg(lblTextColor,"textcolor.gif","dlgColorPalette('"+rte+"','forecolor')","forecolor_"+rte);
			insertImg(lblBgColor,"bgcolor.gif","dlgColorPalette('"+rte+"','hilitecolor')","hilitecolor_"+rte);
				insertSep();
			insertImg(lblHR,"hr.gif","rteCommand('"+rte+"','inserthorizontalrule')");
				insertSep();
			insertImg(lblInsertChar,"special_char.gif","dlgLaunch('"+rte+"','char')");
			insertImg(lblInsertLink,"hyperlink.gif","dlgLaunch('"+rte+"','link')");
			insertImg(lblAddImage,"image.gif","dlgLaunch('"+rte+"','image')");
			insertImg(lblInsertTable,"insert_table.gif","dlgLaunch('"+rte+"','table')");
			document.writeln('<td width="100%"></td></tr></tbody></table>');
		}
		document.writeln('<iframe id="'+rte+'" frameborder="0" style="border: 1px solid #d2d2d2; width: ' + (tablewidth - 2) + 'px; height: ' + height + 'px;" src="' + includesPath + 'blank.htm" onfocus="dlgCleanUp();"></iframe>');
		if(!readOnly){
		  	document.writeln('<table id="vs'+rte+'" name="vs'+rte+'" class="rteBk" cellpadding=0 cellspacing=0 border=0 width="' + tablewidth + '"><tr>');
			document.writeln('<td onclick="toggleHTMLSrc(\''+rte+'\', ' + buttons + ');" nowrap="nowrap"><img class="rteBar" src="'+imagesPath+'bar.gif" alt="" align=absmiddle><span id="imgSrc'+rte+'"><img src="'+imagesPath+'code.gif" alt="" title="" style="margin:1px;" align=absmiddle></span><span id="txtSrc'+rte+'" style="font-family:sans-serif;font-size:12px;color:#555555;CURSOR: default;">'+lblModeHTML+'</span></td>');
			document.writeln('<td width="100%" nowrap>&nbsp;</td></tr></table>');
		}
		document.writeln('<iframe width="142" height="98" id="cp'+rte+'" src="' + includesPath + 'palette.htm" scrolling="no" frameborder=0 style="margin:0;border:0;visibility:hidden;position:absolute;border:1px solid #cdcdcd;top:-1000px;left:-1000px"></iframe>');
		document.writeln('<input type="hidden" id="hdn'+rte+'" name="'+rte+'" value="" style="position: absolute;left:-1000px;top:-1000px;">');
		if(!fullscreen) document.writeln('<input type="hidden" id="size'+rte+'" name="size'+rte+'" value="'+height+'" style="position: absolute;left:-1000px;top:-1000px;">');
		document.writeln('</span>');
		document.getElementById('hdn'+rte).value = html;
		enableDesignMode(rte, html, rte_css, readOnly);
	}
}

function insertBar(){
	document.writeln('<td><img class="rteBar" src="'+imagesPath+'bar.gif" alt=""></td>');
}
function insertSep(){
	document.writeln('<td><img class="rteSep" src="'+imagesPath+'blackdot.gif" alt=""></td>');
}
function insertImg(name, image, command, id){
	var td = "<td>";
	if(id!=null) td = "<td id='"+id+"'>";
	document.writeln(td+'<img class="rteImg" src="'+imagesPath+image+'" alt="'+name+'" title="'+name+'" onClick="'+command+'"></td>');
}

function enableDesignMode(rte, html, css, readOnly) {
	var frameHtml = "<html id=\"" + rte + "\"><head>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\n";
	if(css.length > 0){
		frameHtml += "<link media=\"all\" type=\"text/css\" href=\"" + css + "\" rel=\"stylesheet\">\n";
	}
	frameHtml += "</head><body>\n"+html+"\n</body></html>";
  	var oRTE = returnRTE(rte).document;
	if(document.all){
		oRTE.open();
		oRTE.write(frameHtml);
		oRTE.close();
	 	if(!readOnly){
			oRTE.designMode = "On";
		}
	}
	else{
		originalHTMLDesign = oRTE.body.style;
		try{
			// Commented out the following line to confront a bug when loading multiple RTEs on one page in a MOZ browser
			// Fix provided by "Kings". Safari may have problems with this snytax - unable to test because I don't own a MAC.(Tim Bell)
			//
			// if(!readOnly) document.getElementById(rte).contentDocument.designMode = "on";
			if(!readOnly) {
				addLoadEvent(function() { document.getElementById(rte).contentDocument.designMode = "on"; });
	  		}
   			try{
				oRTE.open();
				oRTE.write(frameHtml);
				oRTE.close();
				if(isGecko && !readOnly){
				  //attach a keyboard handler for gecko browsers to make keyboard shortcuts work
				  oRTE.addEventListener("keypress", geckoKeyPress, true);
				  oRTE.addEventListener("focus", function (){dlgCleanUp()}, false);
				}
		  	}catch(e){
				alert(lblErrorPreload);
			}
		}catch(e){
			//gecko may take some time to enable design mode.
			//Keep looping until able to set.
			if(isGecko){
				setTimeout("enableDesignMode('"+rte+"', '"+html+"', '"+css+"', "+readOnly+");", 200);
			}
			else{
				return false;
			}
		}
	}
	setTimeout('showGuidelines("'+rte+'")',300);
}

function addLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	}
	else {
		window.onload = function() {
			oldonload();
			func();
	  	}
   	}
}

function returnRTE(rte) {
	var rtn;
	if(document.all){
		rtn = frames[rte];
	}
	else{
		rtn = document.getElementById(rte).contentWindow;
	}
	return rtn;
}

function updateRTE(rte) {
	if(isRichText) {
		dlgCleanUp(); // 	Closes Pop-ups
		stripGuidelines(rte); // Removes Table Guidelines
  	}
	parseRTE(rte);
}

function parseRTE(rte) {
	if (!isRichText) {
		 return false;
	}
	//check for readOnly mode
	var readOnly = false;
	var oRTE = returnRTE(rte);
	if(document.all){
		if (oRTE.document.designMode != "On") readOnly = true;
    }
    else{
		if (oRTE.document.designMode != "on") readOnly = true;
	}
	if(isRichText && !readOnly){
		//if viewing source, switch back to design view
		if(document.getElementById("txtSrc"+rte).innerHTML == lblModeRichText){
			 if(document.getElementById("Buttons1_"+rte)){
				 toggleHTMLSrc(rte, true);
			 }
			 else{
		        toggleHTMLSrc(rte, false);
			 }
  		    stripGuidelines(rte);
		}
		setHiddenVal(rte);
	}
}

function setHiddenVal(rte){
	//set hidden form field value for current rte
	var oHdnField = document.getElementById('hdn'+rte);
	//convert html output to xhtml (thanks Timothy Bell and Vyacheslav Smolin!)
	if(oHdnField.value == null) oHdnField.value = "";
	var sRTE = returnRTE(rte).document.body;
	if(generateXHTML){
	  	try{
			oHdnField.value = get_xhtml(sRTE, lang, encoding);
	  	}catch(e){
	  		oHdnField.value = sRTE.innerHTML;
		}
	}
	else{
	  oHdnField.value = sRTE.innerHTML;
	}
	// fix to replace special characters added here:
  	oHdnField.value = replaceSpecialChars(oHdnField.value);
  	//if there is no content (other than formatting) set value to nothing
	if(stripHTML(oHdnField.value.replace("&nbsp;", " ")) == "" && oHdnField.value.toLowerCase().search("<hr") == -1 && oHdnField.value.toLowerCase().search("<img") == -1) oHdnField.value = "";
}

function updateRTEs(){
	var vRTEs = allRTEs.split(";");
	for(var i=0; i<vRTEs.length; i++){
		updateRTE(vRTEs[i]);
	}
}

function rteCommand(rte, command, option){
	dlgCleanUp();
  	//function to perform command
	var oRTE = returnRTE(rte);
	try{
		oRTE.focus();
	  	oRTE.document.execCommand(command, false, option);
		oRTE.focus();
	}catch(e){
//		alert(e);
//		setTimeout("rteCommand('" + rte + "', '" + command + "', '" + option + "');", 10);
	}
}

// MOD: Added function
function setHTMLDesign(oRTE) {
	if (!document.all) {
	oRTE.body.style.fontFamily = 'monospace';
	oRTE.body.style.backgroundColor = 'white';
	oRTE.body.style.color = 'black';
	oRTE.body.style.fontSize = '9pt';
	oRTE.body.style.margin = '3px';
	oRTE.body.style.textAlign = 'left';
	oRTE.body.style.whiteSpace = 'pre';
	}
}
// MOD: Added function
function unsetHTMLDesign(oRTE) {
	if (!document.all) {
	oRTE.body.style.fontFamily = originalHTMLDesign.fontFamily;
	oRTE.body.style.backgroundColor = originalHTMLDesign.backgroundColor;
	oRTE.body.style.color = originalHTMLDesign.color;
	oRTE.body.style.fontSize = originalHTMLDesign.fontSize;
	oRTE.body.style.margin = originalHTMLDesign.margin;
	oRTE.body.style.textAlign = originalHTMLDesign.textAlign;
	oRTE.body.style.whiteSpace = originalHTMLDesign.whiteSpace;
	}
}

function toggleHTMLSrc(rte, buttons){
	dlgCleanUp();
	//contributed by Bob Hutzel (thanks Bob!)
	var cRTE = document.getElementById(rte);
	var hRTE = document.getElementById('hdn'+rte);
	var sRTE = document.getElementById("size"+rte);
	var tRTE = document.getElementById("txtSrc"+rte);
	var iRTE = document.getElementById("imgSrc"+rte);
	var oRTE = returnRTE(rte).document;
	if(sRTE){
		obj_height = parseInt(sRTE.value);
	}
	else{
		findSize(rte);
	}
	if(tRTE.innerHTML == lblModeHTML){
		//we are checking the box
		tRTE.innerHTML = lblModeRichText;
		// MOD: Added line
		setHTMLDesign(oRTE);
		stripGuidelines(rte);
		if(buttons){
			showHideElement("Buttons1_" + rte, "hide", true);
			if(document.getElementById("Buttons2_"+rte)){
				showHideElement("Buttons2_" + rte, "hide", true);
				cRTE.style.height = obj_height+56;
			}
			else{
				cRTE.style.height = obj_height+28;
			}
		}
		setHiddenVal(rte);
  	    if(document.all){
   		    oRTE.body.innerText = hRTE.value;
  	    }
  	    else{
   		    var htmlSrc = oRTE.createTextNode(hRTE.value);
			oRTE.body.innerHTML = "";
			oRTE.body.appendChild(htmlSrc);
		}
		iRTE.innerHTML = '<img src="'+imagesPath+'design.gif" alt="Switch Mode" style="margin:1px;" align="absmiddle">';
	}
	else{
		//we are unchecking the box
		obj_height = parseInt(cRTE.style.height);
		tRTE.innerHTML = lblModeHTML;
		// MOD: Added line
		unsetHTMLDesign(oRTE);
		if(buttons){
			showHideElement("Buttons1_" + rte, "show", true);
		    if(document.getElementById("Buttons2_"+rte)){
		        showHideElement("Buttons2_" + rte, "show", true);
				cRTE.style.height = obj_height-56;
		    }
		    else{
				cRTE.style.height = obj_height-28;
	        }
		}
		if(document.all) {
			//fix for IE
			var output = escape(oRTE.body.innerText);
			output = output.replace("%3CP%3E%0D%0A%3CHR%3E", "%3CHR%3E");
			output = output.replace("%3CHR%3E%0D%0A%3C/P%3E", "%3CHR%3E");
			oRTE.body.innerHTML = unescape(output);
			// Disabled due to flaw in the regular expressions, this fix
	        // does not work with the revamped's enhanced insert link dialog window.
	        //
	        // Prevent links from changing to absolute paths
	        if(!keep_absolute){
		        var tagfix = unescape(output).match(/<a[^>]*href=(['"])([^\1>]*)\1[^>]*>/ig);
		        var coll = oRTE.body.all.tags('A');
		        for(i=0; i<coll.length; i++){
		            // the 2 alerts below show when we hinder the links from becoming absolute
		            //alert(tagfix[i]);
		            coll[i].href = tagfix[i].replace(/.*href=(['"])([^\1]*)\1.*/i,"$2");
		            //alert(RegExp.$1 + " " + RegExp.$2 + " " + RegExp.$3);
		        }
				var imgfix = unescape(output).match(/<img[^>]*src=['"][^'"]*['"][^>]*>/ig);
				var coll2 = oRTE.body.all.tags('IMG');
				for(i=0; i<coll2.length; i++){
		  			coll2[i].src = imgfix[i].replace(/.*src=['"]([^'"]*)['"].*/i,"$1");
			}
	  	}
	  	//end path fix
		}
		else{
   			var htmlSrc = oRTE.body.ownerDocument.createRange();
			htmlSrc.selectNodeContents(oRTE.body);
			oRTE.body.innerHTML = htmlSrc.toString();
		}
		oRTE.body.innerHTML = replaceSpecialChars(oRTE.body.innerHTML);
		showGuidelines(rte);
		// (IE Only)This prevents an undo operation from displaying a pervious HTML mode
		// This resets the undo/redo buffer.
		if(document.all){
			parseRTE(rte);
	 	}
	 	iRTE.innerHTML = '<img src="'+imagesPath+'code.gif" alt="Switch Mode" style="margin:1px;" align="absmiddle">';
	}
}

function toggleSelection(rte) {
	var rng = setRange(rte);
	var oRTE = returnRTE(rte).document;
	var length1;
 	var length2;
	if(document.all){
		length1 = rng.text.length;
		var output = escape(oRTE.body.innerText);
		output = output.replace("%3CP%3E%0D%0A%3CHR%3E", "%3CHR%3E");
		output = output.replace("%3CHR%3E%0D%0A%3C/P%3E", "%3CHR%3E");
		length2 = unescape(output).length;
	}
	else{
		length1 = rng.toString().length;
		var htmlSrc = oRTE.body.ownerDocument.createRange();
		htmlSrc.selectNodeContents(oRTE.body);
	  	length2 = htmlSrc.toString().length;
	}
	if(length1 < length2){
	  	rteCommand(rte,'selectall','');
	}
	else {
		if(!document.all){
			oRTE.designMode = "off";
			oRTE.designMode = "on";
		}
		else{
			rteCommand(rte,'unselect','');
		}
	}
}

function dlgColorPalette(rte, command) {
	//function to display or hide color palettes
	setRange(rte);
	//get dialog position
	var oDialog = document.getElementById('cp' + rte);
	var buttonElement = document.getElementById(command+"_"+rte);
	var iLeftPos = getOffsetLeft(buttonElement);
	var iTopPos = getOffsetTop(buttonElement)+22;
	oDialog.style.left = iLeftPos + "px";
	oDialog.style.top = iTopPos + "px";
	if((command == parent.command)&&(rte == currentRTE)){
		//if current command dialog is currently open, close it
		if(oDialog.style.visibility == "hidden"){
			showHideElement(oDialog, 'show', false);
		}
		else{
			showHideElement(oDialog, 'hide', false);
		}
	}
	else{
		//if opening a new dialog, close all others
		var vRTEs = allRTEs.split(";");
		for(var i = 0; i<vRTEs.length; i++){
			showHideElement('cp' + vRTEs[i], 'hide', false);
		}
		showHideElement(oDialog, 'show', false);
	}
	//save current values
	currentRTE = rte;
	parent.command = command;
}

function dlgLaunch(rte, command) {
	var selectedText = '';
  	//save current values
	parent.command = command;
	currentRTE = rte;
	switch(command){
  		case "char":
			InsertChar = popUpWin(includesPath+'insert_char.htm', 'InsertChar', 50, 50, 'status=yes,');
		break;
  		case "table":
			InsertTable = popUpWin(includesPath + 'insert_table.htm', 'InsertTable', 50, 50, 'status=yes,');
		break;
	  	case "image":
			setRange(rte);
			parseRTE(rte);
			InsertImg = popUpWin(includesPath + 'insert_img.htm','AddImage', 50, 50, 'status=yes,');
		break;
	  	case "link":
			selectedText = getText(rte);
			InsertLink = popUpWin(includesPath + 'insert_link.htm', 'InsertLink', 50, 50, 'status=yes,');
			setFormText("0", selectedText);
		break;
	  	case "replace":
			selectedText = getText(rte);
			dlgReplace = popUpWin(includesPath + 'replace.htm', 'dlgReplace', 50, 50, 'status=yes,');
			setFormText("1", selectedText);
		break;
	  	case "text":
			dlgPasteText = popUpWin(includesPath + 'paste_text.htm', 'dlgPasteText', 50, 50, 'status=yes,');
		break;
	  	case "word":
			dlgPasteWord = popUpWin(includesPath + 'paste_word.htm', 'dlgPasteWord', 50, 50, 'status=yes,');
		break;
	}
}

function getText(rte) {
	//get currently highlighted text and set link text value
	setRange(rte);
	var rtn = '';
	if (isIE) {
	  	rtn = stripHTML(rng.htmlText);
	}
	else {
	  	rtn = stripHTML(rng.toString());
	}
	parseRTE(rte);
	if(document.all){
		rtn = rtn.replace("'","\\\\\\'");
  	}
  	else{
		rtn = rtn.replace("'","\\'");
  	}
  	return rtn;
}

function setFormText(popup, content){
	//set link text value in dialog windows
	if(content != "undefined") {
		try{
			switch(popup){
				case "0": InsertLink.document.getElementById("linkText").value = content; break;
				case "1": dlgReplace.document.getElementById("searchText").value = content; break;
		  	}
	  	}catch(e){
		  	//may take some time to create dialog window.
		  	//Keep looping until able to set.
		  	setTimeout("setFormText('"+popup+"','" + content + "');", 10);
		}
	}
}

function dlgCleanUp(){
	var vRTEs = allRTEs.split(";");
	for(var i = 0; i < vRTEs.length; i++){
		showHideElement('cp' + vRTEs[i], 'hide', false);
	}
	if(InsertChar!=null) InsertChar.close();InsertChar=null;
	if(InsertTable!=null) InsertTable.close();InsertTable=null;
	if(InsertLink!=null) InsertLink.close();InsertLink=null;
	if(InsertImg!=null) InsertImg.close();InsertImg=null;
	if(dlgReplace!=null) dlgReplace.close();dlgReplace=null;
	if(dlgPasteText!=null) dlgPasteText.close();dlgPasteText=null;
	if(dlgPasteWord!=null) dlgPasteWord.close();dlgPasteWord=null;
}

function popUpWin (url, win, width, height, options) {
  	dlgCleanUp();
	var leftPos = (screen.availWidth - width) / 2;
	var topPos = (screen.availHeight - height) / 2;
	options += 'width=' + width + ',height=' + height + ',left=' + leftPos + ',top=' + topPos;
	return window.open(url, win, options);
}

function setColor(color) {
	//function to set color
	var rte = currentRTE;
	var parentCommand = parent.command;
	if(document.all){
		if(parentCommand == "hilitecolor") parentCommand = "backcolor";
		//retrieve selected range
		rng.select();
	}
	rteCommand(rte, parentCommand, color);
	showHideElement('cp'+rte, "hide", false);
}

function addImage(rte) {
  	dlgCleanUp();
	//function to add image
	imagePath = prompt('Enter Image URL:', 'http://');
	if((imagePath != null)&&(imagePath != "")){
		rteCommand(rte, 'InsertImage', imagePath);
	}
}

function rtePrint(rte) {
  	dlgCleanUp();
	if(isIE){
		document.getElementById(rte).contentWindow.document.execCommand('Print');
  	}
  	else{
 		document.getElementById(rte).contentWindow.print();
	}
}

// Ernst de Moor: Fix the amount of digging parents up, in case the RTE editor itself is displayed in a div.
// KJR 11/12/2004 Changed to position palette based on parent div, so palette will always appear in proper location regardless of nested divs
function getOffsetTop(elm){
	var mOffsetTop = elm.offsetTop;
	var mOffsetParent = elm.offsetParent;
	var parents_up = 2; //the positioning div is 2 elements up the tree
	while(parents_up > 0){
		mOffsetTop += mOffsetParent.offsetTop;
		mOffsetParent = mOffsetParent.offsetParent;
		parents_up--;
	}
	return mOffsetTop;
}

// Ernst de Moor: Fix the amount of digging parents up, in case the RTE editor itself is displayed in a div.
// KJR 11/12/2004 Changed to position palette based on parent div, so palette will always appear in proper location regardless of nested divs
function getOffsetLeft(elm){
	var mOffsetLeft = elm.offsetLeft;
	var mOffsetParent = elm.offsetParent;
	var parents_up = 2;
	while(parents_up > 0){
		mOffsetLeft += mOffsetParent.offsetLeft;
		mOffsetParent = mOffsetParent.offsetParent;
		parents_up--;
	}
	return mOffsetLeft;
}

function selectFont(rte, selectname){
	//function to handle font changes
	var idx = document.getElementById(selectname).selectedIndex;
	// First one is always a label
	if(idx != 0){
		var selected = document.getElementById(selectname).options[idx].value;
		var cmd = selectname.replace('_'+rte, '');
		rteCommand(rte, cmd, selected);
		document.getElementById(selectname).selectedIndex = 0;
	}
}

function insertHTML(html){
	//function to add HTML -- thanks dannyuk1982
	var rte = currentRTE;
	var oRTE = returnRTE(rte);
	oRTE.focus();
	if(document.all){
		var oRng = oRTE.document.selection.createRange();
		oRng.pasteHTML(html);
		oRng.collapse(false);
		oRng.select();
	}
	else{
		oRTE.document.execCommand('insertHTML', false, html);
	}
}

function replaceHTML(tmpContent, searchFor, replaceWith) {
	var runCount = 0;
	var intBefore = 0;
	var intAfter = 0;
	var tmpOutput = "";
	while(tmpContent.toUpperCase().indexOf(searchFor.toUpperCase()) > -1) {
		runCount = runCount+1;
		// Get all content before the match
		intBefore = tmpContent.toUpperCase().indexOf(searchFor.toUpperCase());
		tmpBefore = tmpContent.substring(0, intBefore);
		tmpOutput = tmpOutput + tmpBefore;
		// Get the string to replace
		tmpOutput = tmpOutput + replaceWith;
		// Get the rest of the content after the match until
		// the next match or the end of the content
		intAfter = tmpContent.length - searchFor.length + 1;
		tmpContent = tmpContent.substring(intBefore + searchFor.length);
  	}
  	return runCount+"|^|"+tmpOutput+tmpContent;
}

function replaceSpecialChars(html){
	var specials = new Array("&cent;","&euro;","&pound;","&curren;","&yen;","&copy;","&reg;","&trade;","&divide;","&times;","&plusmn;","&frac14;","&frac12;","&frac34;","&deg;","&sup1;","&sup2;","&sup3;","&micro;","&laquo;","&raquo;","&lsquo;","&rsquo;","&lsaquo;","&rsaquo;","&sbquo;","&bdquo;","&ldquo;","&rdquo;","&iexcl;","&brvbar;","&sect;","&not;","&macr;","&para;","&middot;","&cedil;","&iquest;","&fnof;","&mdash;","&ndash;","&bull;","&hellip;","&permil;","&ordf;","&ordm;","&szlig;","&dagger;","&Dagger;","&eth;","&ETH;","&oslash;","&Oslash;","&thorn;","&THORN;","&oelig;","&OElig;","&scaron;","&Scaron;","&acute;","&circ;","&tilde;","&uml;","&agrave;","&aacute;","&acirc;","&atilde;","&auml;","&aring;","&aelig;","&Agrave;","&Aacute;","&Acirc;","&Atilde;","&Auml;","&Aring;","&AElig;","&ccedil;","&Ccedil;","&egrave;","&eacute;","&ecirc;","&euml;","&Egrave;","&Eacute;","&Ecirc;","&Euml;","&igrave;","&iacute;","&icirc;","&iuml;","&Igrave;","&Iacute;","&Icirc;","&Iuml;","&ntilde;","&Ntilde;","&ograve;","&oacute;","&ocirc;","&otilde;","&ouml;","&Ograve;","&Oacute;","&Ocirc;","&Otilde;","&Ouml;","&ugrave;","&uacute;","&ucirc;","&uuml;","&Ugrave;","&Uacute;","&Ucirc;","&Uuml;","&yacute;","&yuml;","&Yacute;","&Yuml;");
	var unicodes = new Array("\u00a2","\u20ac","\u00a3","\u00a4","\u00a5","\u00a9","\u00ae","\u2122","\u00f7","\u00d7","\u00b1","\u00bc","\u00bd","\u00be","\u00b0","\u00b9","\u00b2","\u00b3","\u00b5","\u00ab","\u00bb","\u2018","\u2019","\u2039","\u203a","\u201a","\u201e","\u201c","\u201d","\u00a1","\u00a6","\u00a7","\u00ac","\u00af","\u00b6","\u00b7","\u00b8","\u00bf","\u0192","\u2014","\u2013","\u2022","\u2026","\u2030","\u00aa","\u00ba","\u00df","\u2020","\u2021","\u00f0","\u00d0","\u00f8","\u00d8","\u00fe","\u00de","\u0153","\u0152","\u0161","\u0160","\u00b4","\u02c6","\u02dc","\u00a8","\u00e0","\u00e1","\u00e2","\u00e3","\u00e4","\u00e5","\u00e6","\u00c0","\u00c1","\u00c2","\u00c3","\u00c4","\u00c5","\u00c6","\u00e7","\u00c7","\u00e8","\u00e9","\u00ea","\u00eb","\u00c8","\u00c9","\u00ca","\u00cb","\u00ec","\u00ed","\u00ee","\u00ef","\u00cc","\u00cd","\u00ce","\u00cf","\u00f1","\u00d1","\u00f2","\u00f3","\u00f4","\u00f5","\u00f6","\u00d2","\u00d3","\u00d4","\u00d5","\u00d6","\u00f9","\u00fa","\u00fb","\u00fc","\u00d9","\u00da","\u00db","\u00dc","\u00fd","\u00ff","\u00dd","\u0178");
	for(var i=0; i<specials.length; i++){
		html = replaceIt(html,unicodes[i],specials[i]);
	}
	return html;
}

function SearchAndReplace(searchFor, replaceWith, matchCase, wholeWord) {
	var cfrmMsg = lblSearchConfirm.replace("SF",searchFor).replace("RW",replaceWith);
	var rte = currentRTE;
	stripGuidelines(rte);
	var oRTE = returnRTE(rte);
	var tmpContent = oRTE.document.body.innerHTML.replace("'", "\'").replace('"', '\"');
	var strRegex;
	if (matchCase && wholeWord) {
		strRegex = "/(?!<[^>]*)(\\b(" + searchFor + ")\\b)(?![^<]*>)/g";
	}
	else if (matchCase) {
		strRegex = "/(?!<[^>]*)(" + searchFor + ")(?![^<]*>)/g";
	}
	else if (wholeWord) {
		strRegex = "/(?!<[^>]*)(\\b(" + searchFor + ")\\b)(?![^<]*>)/gi";
	}
	else {
		strRegex = "/(?!<[^>]*)(" + searchFor + ")(?![^<]*>)/gi";
   	}
    var cmpRegex=eval(strRegex);
    var runCount = 0;
    var tmpNext = tmpContent;
    var intFound = tmpNext.search(cmpRegex);
    while(intFound > -1) {
		runCount = runCount+1;
		tmpNext = tmpNext.substr(intFound + searchFor.length);
		intFound = tmpNext.search(cmpRegex);
    }
    if (runCount > 0) {
		cfrmMsg = cfrmMsg.replace("[RUNCOUNT]",runCount);
	  	if(confirm(cfrmMsg)) {
			tmpContent=tmpContent.replace(cmpRegex,replaceWith);
		 	oRTE.document.body.innerHTML = tmpContent.replace("\'", "'").replace('\"', '"');
	  	}
	  	else {
	   		alert(lblSearchAbort);
		}
	  	showGuidelines(rte);
   	}
   	else {
		showGuidelines(rte);
		alert("["+searchFor+"] "+lblSearchNotFound);
	}
}

function showHideElement(element, showHide, rePosition){
	//function to show or hide elements
	//element variable can be string or object
	if(document.getElementById(element)){
		element = document.getElementById(element);
	}
	if(showHide == "show"){
		element.style.visibility = "visible";
	  	if(rePosition){
			element.style.position = "relative";
			element.style.left = "auto";
			element.style.top = "auto";
		}
	}
	else if(showHide == "hide"){
		element.style.visibility = "hidden";
		if(rePosition){
			element.style.position = "absolute";
			element.style.left = "-1000px";
			element.style.top = "-1000px";
	  	}
  	}
}

function setRange(rte){
	//function to store range of current selection
	var oRTE = returnRTE(rte);
	if(document.all){
		var selection = oRTE.document.selection;
		if(selection != null) rng = selection.createRange();
	}
	else{
		var selection = oRTE.getSelection();
		rng = selection.getRangeAt(selection.rangeCount - 1).cloneRange();
	}
	return rng;
}

function stripHTML(strU) {
	//strip all html
	var strN = strU.replace(/(<([^>]+)>)/ig,"");
	//replace carriage returns and line feeds
	strN = strN.replace(/\r\n/g," ");
	strN = strN.replace(/\n/g," ");
	strN = strN.replace(/\r/g," ");
	strN = trim(strN);
	return strN;
}

function trim(inputString) {
	if (typeof inputString != "string") return inputString;
	var retValue = inputString;
  	var ch = retValue.substring(0, 1);
  	while(ch == " "){
		retValue = retValue.substring(1, retValue.length);
		ch = retValue.substring(0, 1);
  	}
  	ch = retValue.substring(retValue.length - 1, retValue.length);
  	while(ch == " "){
		retValue = retValue.substring(0, retValue.length - 1);
		ch = retValue.substring(retValue.length - 1, retValue.length);
  	}
	// Note that there are two spaces in the string - look for multiple spaces within the string
  	while (retValue.indexOf("  ") != -1) {
		// Again, there are two spaces in each of the strings
		retValue = retValue.substring(0, retValue.indexOf("  ")) + retValue.substring(retValue.indexOf("  ") + 1, retValue.length);
  	}
  	return retValue; // Return the trimmed string back to the user
}

function showGuidelines(rte) {
	if(rte.length == 0) rte = currentRTE;
	var oRTE = returnRTE(rte);
	var tables = oRTE.document.getElementsByTagName("table");
  	for(var i=0; i<tables.length; i++){
		if(tables[i].getAttribute("border") == "0"){
	  		var trs = tables[i].getElementsByTagName("tr");
	  		for(var j=0; j<trs.length; j++){
				var tds = trs[j].getElementsByTagName("td");
				for(var k=0; k<tds.length; k++){
  					if(j == 0 && k == 0){
			   	  		tds[k].style.border = "dashed 1px "+zeroBorder;
					}
					else if(j == 0 && k != 0){
						tds[k].style.borderBottom = "dashed 1px "+zeroBorder;
						tds[k].style.borderTop = "dashed 1px "+zeroBorder;
						tds[k].style.borderRight = "dashed 1px "+zeroBorder;
					}
					else if(j != 0 && k == 0) {
				  		tds[k].style.borderBottom = "dashed 1px "+zeroBorder;
					  	tds[k].style.borderLeft = "dashed 1px "+zeroBorder;
						tds[k].style.borderRight = "dashed 1px "+zeroBorder;
		  			}
		  			else if(j != 0 && k != 0) {
						tds[k].style.borderBottom = "dashed 1px "+zeroBorder;
					  	tds[k].style.borderRight = "dashed 1px "+zeroBorder;
			  		}
				}
	  		}
		}
  	}
}

function stripGuidelines(rte) {
	var oRTE = returnRTE(rte);
	var tbls = oRTE.document.getElementsByTagName("table");
  	for(var j=0; j<tbls.length; j++) {
		if(tbls[j].getAttribute("border") == "0") {
	  		var tds = tbls[j].getElementsByTagName("td");
	  		for(var k=0; k<tds.length; k++) {
				tds[k].removeAttribute("style");
	  		}
		}
  	}
}

function findSize(obj) {
	if(obj.length > 0 && document.all) {
		obj = frames[obj];
  	}
  	else if(obj.length > 0 && !document.all) {
		obj = document.getElementById(obj).contentWindow;
  	}
  	else {
		obj = this;
	}
	if ( typeof( obj.window.innerWidth ) == 'number' ) {
		//Non-IE
		obj_width = obj.window.innerWidth;
		obj_height = obj.window.innerHeight;
  	}
  	else if( obj.document.documentElement && ( obj.document.documentElement.clientWidth || obj.document.documentElement.clientHeight ) ) {
		//IE 6+ in 'standards compliant mode'
		obj_width = document.documentElement.clientWidth;
		obj_height = document.documentElement.clientHeight;
  	}
  	else if( obj.document.body && ( obj.document.body.clientWidth || obj.document.body.clientHeight ) ) {
		//IE 4 compatible
		obj_width = obj.document.body.clientWidth;
		obj_height = obj.document.body.clientHeight;
  	}
}

function resizeRTE() {
  	document.body.style.overflow = "hidden";
	var rte = currentRTE;
  	var oRTE = document.getElementById(rte);
  	var oBut1 = document.getElementById('Buttons1_'+rte);
  	var oBut2;
  	var oVS = document.getElementById('vs'+rte);
  	findSize("");
  	width = obj_width;
  	if (width < minWidth){
		document.body.style.overflow = "auto";
		width = minWidth;
	}
  	var height = obj_height - 83;
	if (document.getElementById("txtSrc"+rte).innerHTML == lblModeRichText) {
		height = obj_height-28;
		if (!document.getElementById('Buttons2_'+rte) && width < wrapWidth) {
			document.body.style.overflow = "auto";
			width = wrapWidth;
		}
		if (document.getElementById('Buttons2_'+rte)) document.getElementById('Buttons2_'+rte).style.width = width;
  	}
  	else {
		if (document.getElementById('Buttons2_'+rte)) {
			document.getElementById('Buttons2_'+rte).style.width = width;
		}
		else {
			height = obj_height - 55;
			if(width < wrapWidth){
	 			document.body.style.overflow = "auto";
				width = wrapWidth;
			}
		}
  	}
  	if (document.body.style.overflow == "auto" && isIE) height = height-18;
  	if (document.body.style.overflow == "auto" && !isIE) height = height-24;
  	oBut1.style.width = width;
  	oVS.style.width = width;
	oRTE.style.width = width-2;
	oRTE.style.height = height;
  	if(!document.all) oRTE.contentDocument.designMode = "on";
}

function replaceIt(string,text,by) {
  	// CM 19/10/04 custom replace function
  	var strLength = string.length, txtLength = text.length;
  	if ((strLength == 0) || (txtLength == 0)) return string;
  	var i = string.indexOf(text);
  	if ((!i) && (text != string.substring(0,txtLength))) return string;
  	if (i == -1) return string;
  	var newstr = string.substring(0,i) + by;
  	if (i+txtLength < strLength) {
		newstr += replaceIt(string.substring(i+txtLength,strLength),text,by);
	}
  	return newstr;
}

function countWords(rte){
 	parseRTE(rte);
  	var words = document.getElementById("hdn"+rte).value;
  	var str = stripHTML(words);
  	var chars = trim(words);
  	chars = chars.length;
  	chars = maxchar - chars;
  	str = str+" a ";  // word added to avoid error
  	str = str.replace(/&nbsp;/gi,' ').replace(/([\n\r\t])/g,' ').replace(/(  +)/g,' ').replace(/&(.*);/g,' ').replace(/^\s*|\s*$/g,'');
  	var count = 0;
  	for (x=0;x<str.length;x++) {
		if (str.charAt(x)==" " && str.charAt(x-1)!=" ") count++;
  	}
  	if (str.charAt(str.length-1) != " ") count++;
	count = count - 1; // extra word removed
	var alarm = "";
  	if(chars<0) alarm = "\n\n"+lblCountCharWarn;
  	alert(lblCountTotal+": "+count+ "\n\n"+lblCountChar+": "+chars+alarm);
}

//********************
//Gecko-Only Functions
//********************
function geckoKeyPress(evt) {
	//function to add bold, italic, and underline shortcut commands to gecko RTEs
	//contributed by Anti Veeranna (thanks Anti!)
	var rte = evt.target.id;
	if (evt.ctrlKey) {
		var key = String.fromCharCode(evt.charCode).toLowerCase();
		var cmd = '';
		switch (key) {
			case 'b': cmd = "bold"; break;
			case 'i': cmd = "italic"; break;
			case 'u': cmd = "underline"; break;
		};
		if (cmd) {
			rteCommand(rte, cmd, null);
			// stop the event bubble
			evt.preventDefault();
			evt.stopPropagation();
		}
 	}
}

//*****************
//IE-Only Functions
//*****************
function checkspell() {
	dlgCleanUp();
	//function to perform spell check
	try {
		var tmpis = new ActiveXObject("ieSpell.ieSpellExtension");
		tmpis.CheckAllLinkedDocuments(document);
	}
	catch(exception) {
		if(exception.number==-2146827859) {
			if (confirm("ieSpell not detected.  Click Ok to go to download page."))
				window.open("http://www.iespell.com/download.php","DownLoad");
		}
		else {
			alert("Error Loading ieSpell: Exception " + exception.number);
		}
	}
}

function ie_btnfx(e) {
	var el = window.event.srcElement;
	className = el.className;
	switch(window.event.type){
  		case "mousedown": if(className == 'rteImg'||className=='rteImgUp')el.className='rteImgDn'; break;
  		case "mouseout": if(className=='rteImgUp'||className=='rteImgDn')el.className='rteImg'; break;
  		default: if (className=='rteImg'||className=='rteImgDn')el.className='rteImgUp';
  	}
}