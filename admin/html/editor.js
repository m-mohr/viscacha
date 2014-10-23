var textfield = 'template';

function openSpellChecker(val) {
	valdata = FetchElement(val);
	var speller = new spellChecker(valdata);
	speller.openChecker();
}

function OpenerFetchElement(id) {
	if (window.opener.document.getElementById) {
		return window.opener.document.getElementById(id);
	}
	else if (window.opener.document.all) {
		return window.opener.document.all[id];
	}
	else if (window.opener.document.layers) {
		return window.opener.document.layers[id];
	}
}

function setTextfield(name) {
	textfield = name;
}

function refreshElement(parentWindow) {
	if (parentWindow == 1) {
		return OpenerFetchElement(textfield);
	}
	else {
		return FetchElement(textfield);
	}
}

function setFocus(field) {
	if (field.hasFocus != true) {
		field.focus();
	}
}

function empty(val) {
	if(typeof val == 'undefined' || val == '' || val == null) {
		return true;
	}
	else if (val.length > 0) {
		return false;
	}
	else {
		return true;
	}
}

function getSelection() {
	var input = refreshElement();
	var selection = '';
	if(typeof document.selection != 'undefined') {
		var selection = document.selection.createRange().text;
	}
	else if(typeof input.selectionStart != 'undefined') {
		var selection = input.value.substring(input.selectionStart, input.selectionEnd);
	}
	return selection;
}

function InsertTags(aTag, eTag, parentWindow, param2) {
	var input = refreshElement(parentWindow);
	setFocus(input);

	if (parentWindow == 1) {
		var docsel = window.opener.document;
	}
	else {
		var docsel = document;
	}
	if(typeof docsel.selection != 'undefined') {
        var range = docsel.selection.createRange();
        var insText = range.text;
        if (param2 == 1) {
        	insText = '';
        }
        range.text = aTag + insText + eTag;
        range = docsel.selection.createRange();
        if (insText.length == 0) {
          range.move('character', -eTag.length);
        } else {
          range.moveStart('character', aTag.length + insText.length + eTag.length);      
        }
        range.select();
    }
    else if(typeof input.selectionStart != 'undefined') {
        var start = input.selectionStart;
        var end = input.selectionEnd;
        var insText = input.value.substring(start, end);
        if (param2 == 1) {
        	insText = '';
        }
        input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
        var pos;
        if (insText.length == 0) {
          pos = start + aTag.length;
        } else {
          pos = start + aTag.length + insText.length + eTag.length;
        }
        input.selectionStart = pos;
        input.selectionEnd = pos;
    }
    else {
        input.value = input.value + aTag + '' + eTag;
    }
}

function InsertTagsCode(front, end) {
	InsertTags(front, end, 1);
	top.close();
}
function InsertTagsMenu(front, end, menu) {
	InsertTags(front, end);
	if (menu.length > 0) {
		HideMenu(menu);
	}
}

function writeRow() {
	var cells = 17;
	var str = "";
	var ar = new Array("#330000","#333300","#336600","#339900","#33CC00","#33FF00","#66FF00","#66CC00","#669900","#666600","#663300","#660000","#FF0000","#FF3300","#FF6600","#FF9900","#FFCC00","#FFFF00","#330033","#333333","#336633","#339933","#33CC33","#33FF33","#66FF33","#66CC33","#669933","#666633","#663333","#660033","#FF0033","#FF3333","#FF6633","#FF9933","#FFCC33","#FFFF33","#330066","#333366","#336666","#339966","#33CC66","#33FF66","#66FF66","#66CC66","#669966","#666666","#663366","#660066","#FF0066","#FF3366","#FF6666","#FF9966","#FFCC66","#FFFF66","#330099","#333399","#336699","#339999","#33CC99","#33FF99","#66FF99","#66CC99","#669999","#666699","#663399","#660099","#FF0099","#FF3399","#FF6699","#FF9999","#FFCC99","#FFFF99","#3300CC","#3333CC","#3366CC","#3399CC","#33CCCC","#33FFCC","#66FFCC","#66CCCC","#6699CC","#6666CC","#6633CC","#6600CC","#FF00CC","#FF33CC","#FF66CC","#FF99CC","#FFCCCC","#FFFFCC","#3300FF","#3333FF","#3366FF","#3399FF","#33CCFF","#33FFFF","#66FFFF","#66CCFF","#6699FF","#6666FF","#6633FF","#6600FF","#FF00FF","#FF33FF","#FF66FF","#FF99FF","#FFCCFF","#FFFFFF","#0000FF","#0033FF","#0066FF","#0099FF","#00CCFF","#00FFFF","#99FFFF","#99CCFF","#9999FF","#9966FF","#9933FF","#9900FF","#CC00FF","#CC33FF","#CC66FF","#CC99FF","#CCCCFF","#CCFFFF","#0000CC","#0033CC","#0066CC","#0099CC","#00CCCC","#00FFCC","#99FFCC","#99CCCC","#9999CC","#9966CC","#9933CC","#9900CC","#CC00CC","#CC33CC","#CC66CC","#CC99CC","#CCCCCC","#CCFFCC","#000099","#003399","#006699","#009999","#00CC99","#00FF99","#99FF99","#99CC99","#999999","#996699","#993399","#990099","#CC0099","#CC3399","#CC6699","#CC9999","#CCCC99","#CCFF99","#000066","#003366","#006666","#009966","#00CC66","#00FF66","#99FF66","#99CC66","#999966","#996666","#993366","#990066","#CC0066","#CC3366","#CC6666","#CC9966","#CCCC66","#CCFF66","#000033","#003333","#006633","#009933","#00CC33","#00FF33","#99FF33","#99CC33","#999933","#996633","#993333","#990033","#CC0033","#CC3333","#CC6633","#CC9933","#CCCC33","#CCFF33","#000000","#003300","#006600","#009900","#00CC00","#00FF00","#99FF00","#99CC00","#999900","#996600","#993300","#990000","#CC0000","#CC3300","#CC6600","#CC9900","#CCCC00","#CCFF00","#000000","#333333","#666666","#999999","#cccccc","#ffffff");
	str += '<div class="bbcolor">';
	for (var i=0; i<ar.length; ) {
		for (var j=0; j<=cells && i<ar.length; j++) {
			str += "<span style='background-color: " + ar[i] + ";' onClick='bbcolor(\""+ar[i]+"\")'>&nbsp;</span>";
			i++;
		}
		str += '<br />';
	}
	str += '</div>';
	return str;
}

function bbcolor(bgc) {
	InsertTagsMenu("[color="+bgc+"]", '[/color]', 'bbcolor');
	return;
}

function popup_code() {
	window.open("admin.php?action=cms&job=doc_code","","width=300,height=600,resizable=yes,scrollbars=yes,status=yes");   
}

function list(type) {
	Elements = new Array();
	var a = 1;
	if (empty(type) == false) {
		type = '='+type;
	}
	else {
		type = '';
	}
	var txt = 'While';
	while (empty(txt) == false){
		var txt = window.prompt(lng['js_listpompt1']+a+lng['js_listpompt2'],'');
		if (empty(txt) == false) {
			Elements[a] = "[*]"+txt;
			a++;
		}
	}
	if (Elements.length > 0){
		var Code = "[list"+type+"]"+Elements.join("\n")+"[/list]";
		InsertTags(Code,'');
	}
	else {
		InsertTags("[list"+type+"]", "[/list]");
	}
}

function InsertTagsParams(front, end, txt, txt2) {
	var input1 = '';
	var input2 = '';
	var selection = getSelection();
	if (empty(selection) == false) {
		if (empty(txt2) == false) {
			input2 = selection;
		}
		else {
			input1 = selection;
		}
	}
	if (empty(input1) == true) {
		input1 = window.prompt(txt, '');
	}
	if (empty(txt2) == false && empty(input2) == true) {
		input2 = window.prompt(txt2, '');
	}
	var PosF = front.indexOf('{param2}', 0);
	var PosE = end.indexOf('{param2}', 0);
	front = SuchenUndErsetzen(front, '{param1}', input1);
	front = SuchenUndErsetzen(front, '{param2}', input2);
	end = SuchenUndErsetzen(end, '{param1}', input1);
	end = SuchenUndErsetzen(end, '{param2}', input2);
	if (PosE >= 0 || PosF >= 0) {
		InsertTags(front, end, 0, 1);
	}
	else {
		InsertTags(front, end);
	}
}

function SuchenUndErsetzen(QuellText, SuchText, ErsatzText) {   
// (c) Ralf Pfeifer, http://www.arstechnica.de/computer/JavaScript/JS11_01.html
	if ((QuellText == null) || (SuchText == null))			 { return null; }
	if ((QuellText.length == 0) || (SuchText.length == 0))   { return QuellText; }
	if ((ErsatzText == null) || (ErsatzText.length == 0))	 { ErsatzText = ""; }
	var LaengeSuchText = SuchText.length;
	var LaengeErsatzText = ErsatzText.length;
	var Pos = QuellText.indexOf(SuchText, 0);
	while (Pos >= 0) {
		QuellText = QuellText.substring(0, Pos) + ErsatzText + QuellText.substring(Pos + LaengeSuchText);
		Pos = QuellText.indexOf(SuchText, Pos + LaengeErsatzText);
	}
	return QuellText;
}


var MenuTimeout = 1000;
var active = 0;
var MenuCountHide = 0;
function GetLeft(l) {
	if (l.offsetParent) return (l.offsetLeft + GetLeft(l.offsetParent));
	else return (l.offsetLeft);
}
function GetTop(l) {
	if (l.offsetParent) return (l.offsetTop + GetTop(l.offsetParent));
	else return (l.offsetTop);
}
function TryHideMenu(menu,CountHide) {
	if (CountHide != MenuCountHide) {
		return;
	}
	HideMenu(menu);
}
function MenuEvent(active) {
	var elementevent = FetchElement("popup_"+active);
	elementevent.onmouseover = function() {
        	MenuCountHide++;
	}
	elementevent.onmouseout = function() {
		setTimeout("TryHideMenu('" + active + "', " + MenuCountHide + ")", MenuTimeout);
	}
}
function ShowMenu(id) {
	if(active != 0) {
		if (id == active) {
			HideMenu(active);
    	}
		else {
			HideMenu(active);
			ShowMenu(id);
		}
	}
	else {
		var elementbutton = FetchElement("menu_"+id);
		var buttonleft = GetLeft(elementbutton);
		var buttontop = GetTop(elementbutton);
		var buttonwidth = elementbutton.offsetWidth;
		var buttonheight = elementbutton.offsetHeight;
		var elementmenu = FetchElement("popup_"+id);
		var menuwidth = elementmenu.offsetWidth;
		if((buttonleft+menuwidth) >= document.body.clientWidth) {
			var posx = buttonleft + buttonwidth - menuwidth;
		}
		else {
			var posx = buttonleft;
		}
		var posy = buttontop + buttonheight;
	
		elementmenu.style.zIndex = 1;
		elementmenu.style.left = posx+'px';
		elementmenu.style.top = posy+'px';
		elementmenu.style.visibility = 'visible';
		active = id;
		MenuEvent(active);
	}
}
function HideMenu(menu) {
	var elementhide = FetchElement("popup_"+menu);
	elementhide.style.zIndex = -1;
	elementhide.style.left = '-1000px';
	elementhide.style.top = '-1000px';
	elementhide.style.visibility = 'hidden';
	active = 0;
}
function Click() {
	id = this.id.replace("menu_","");
	ShowMenu(id);
	return false;
}
function Swap() {
	id = this.id.replace("menu_","")
	if (active != 0 && active != id) {
		HideMenu(active);
		ShowMenu(id);
	}
}
function Hide() {
    if (active != 0) {
		HideMenu(active);
	}
}
function RegisterMenu(id) {
	var buttonregister = FetchElement("menu_"+id);
	if(buttonregister) {
		HandCursor(buttonregister);
		buttonregister.unselectable = true;
		buttonregister.onclick = Click;
		buttonregister.onmouseover = Swap;
		window.onresize = Hide;
	}
}
function Link() {
	return false;
}