///////////////////////// Variables /////////////////////////
var ColM = new Array("300","330","360","390","3C0","3F0","6F0","6C0","690","660","630","600","F00","F30","F60","F90","FC0","FF0","303","333","363","393","3C3","3F3","6F3","6C3","693","663","633","603","F03","F33","F63","F93","FC3","FF3","306","336","366","396","3C6","3F6","6F6","6C6","696","666","636","606","F06","F36","F66","F96","FC6","FF6","309","339","369","399","3C9","3F9","6F9","6C9","699","669","639","609","F09","F39","F69","F99","FC9","FF9","30C","33C","36C","39C","3CC","3FC","6FC","6CC","69C","66C","63C","60C","F0C","F3C","F6C","F9C","FCC","FFC","30F","33F","36F","39F","3CF","3FF","6FF","6CF","69F","66F","63F","60F","F0F","F3F","F6F","F9F","FCF","FFF","00F","03F","06F","09F","0CF","0FF","9FF","9CF","99F","96F","93F","90F","C0F","C3F","C6F","C9F","CCF","CFF","00C","03C","06C","09C","0CC","0FC","9FC","9CC","99C","96C","93C","90C","C0C","C3C","C6C","C9C","CCC","CFC","009","039","069","099","0C9","0F9","9F9","9C9","999","969","939","909","C09","C39","C69","C99","CC9","CF9","006","036","066","096","0C6","0F6","9F6","9C6","996","966","936","906","C06","C36","C66","C96","CC6","CF6","003","033","063","093","0C3","0F3","9F3","9C3","993","963","933","903","C03","C33","C63","C93","CC3","CF3","000","030","060","090","0C0","0F0","9F0","9C0","990","960","930","900","C00","C30","C60","C90","CC0","CF0","000","222","444","666","888","AAA", "CCC", "EEE", "FFF");
var boxes = new Array();
var MenuTimeout = 500;
var active = 0;
var MenuCountHide = 0;
var LightBoxCallback = null;

///////////////////////// Global /////////////////////////
function FetchElement(id) {
	if (document.getElementById) {
		return document.getElementById(id);
	}
	else if (document.all) {
		return document.all[id];
	}
	else if (document.layers) {
		return document.layers[id];
	}
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

///////////////////////// General / Misc. /////////////////////////
function GetLeft(l) {
	if (l.offsetParent) return (l.offsetLeft + GetLeft(l.offsetParent));
	else return (l.offsetLeft);
}
function GetTop(l) {
	if (l.offsetParent) return (l.offsetTop + GetTop(l.offsetParent));
	else return (l.offsetTop);
}
function check_all(elem) {
	var all = document.getElementsByName(elem.value);
	for(var i=0; i < all.length; i++) {
		all[i].checked = elem.checked;
	}
}
function HandCursor(element) {
	try {
		element.style.cursor = "pointer"
	}
	catch(e) {
		element.style.cursor = "hand"
	}
}
function key(event) { // Returns the pressed key
	event = (window.event) ? window.event : event; // windows.event for IE
	return (event.which ? event.which : event.keyCode);
}

/**
 * Gives a readable foreground-color for the given background-color.
 */
function invert(color) {
	var h = color.match(/^#{0,1}(\w{1,2})(\w{1,2})(\w{1,2})$/);
	var rgb = color.match(/\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)/);
	if (h) {
		var rgb = new Array();
    	for (var i = 1; i < h.length; i++) {
			var s = h[i];
			s = s.length == 1 ? s + s : s;
			rgb.push(parseInt(s, 16));
		}
	}
	else if (rgb) {
		rgb.shift();
	}
	if (rgb) {
		c = (0.213 * rgb[0] + 0.715 * rgb[1] + 0.072 * rgb[2]);
		return (c < 127.5) ? '#FFF' : '#000';
	}
	else {
		return '';
	}
}

function generateColorPicker(param, url) {
	if (!url) {
		url = 'images/empty.gif';
	}
	var cells = 17;
	var str = "";
	for (var i=0; i<ColM.length; ) {
		for (var j=0; j<=cells && i<ColM.length; j++) {
			var hex = '#'+ColM[i].charAt(0)+ColM[i].charAt(0)+ColM[i].charAt(1)+ColM[i].charAt(1)+ColM[i].charAt(2)+ColM[i].charAt(2);
			str += '<img style="background-color: '+hex+'; border-color: '+hex+';" src="'+url+'" onmouseover="hoverColor(this, 1)" onmouseout="hoverColor(this)" onClick="'+param.replace(/<color>/i, hex)+'" />';
			i++;
		}
		str += '<br clear="all" />';
	}
	return str;
}

function hoverColor(elem, state) {
	if (state == 1) {
		elem.style.borderColor = invert(elem.style.backgroundColor);
	}
	else {
		elem.style.borderColor = elem.style.backgroundColor;
	}
}

///////////////////////// Collapse/Expand Boxes /////////////////////////
function Switch(switchimg, nocookie) {
	switchimg.onclick = function() {
		id = this.id.replace("img_","");
		part = FetchElement("part_"+id);
		if(part.style.display == 'none') {
			switchimg.src = box_img_minus;
			part.style.display = '';
			if (nocookie != true) {
				KillCookie(id);
			}
		}
		else {
			switchimg.src = box_img_plus;
			part.style.display = 'none';
			if (nocookie != true) {
				SetCookie(id);
			}
		}
	}
}

///////////////////////// Cookies /////////////////////////
function SetCookie(n) {
	var a = new Date();
	a = new Date(a.getTime() +1000*60*60*24*365);
	document.cookie = n+'=hidden; expires='+a.toGMTString()+';';
}
function GetCookie(n) {
	a = document.cookie;
	res = '';
	while(a != '') {
		cookiename = a.substring(0,a.search('='));
		altcookiename = a.substring(1,a.search('='));
		cookievalue = a.substring(a.search('=')+1,a.search(';'));
		if(cookievalue == '') {
			cookievalue = a.substring(a.search('=')+1,a.length);
		}
	 	if(n == cookiename || n == altcookiename) {
			res = cookievalue;
		}
		i = a.search(';')+1;
		if(i == 0) {
			i = a.length;
		}
		a = a.substring(i,a.length);
	}
	return(res)
}
function KillCookie(n) {
	document.cookie = n+'=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
}

///////////////////////// PopUps /////////////////////////
function showpost(Link) {
	window.open(Link.href, "showpost", "width=640,height=480,resizable=yes,scrollbars=yes,location=yes,status=yes");
}
function edithistory(Link) {
	window.open(Link.href, "edithistory", "width=640,height=380,resizable=yes,scrollbars=yes,location=no,status=no");
}
function adduploads(Link) {
	window.open(Link.href, "adduploads", "width=480,height=480,resizable=yes,scrollbars=yes,location=no,status=yes");
}
function filetypeinfo(ftype) {
	window.open(Link.href, "filetypeinfo", "width=400,height=250,resizable=no,scrollbars=yes,location=no,status=no");
}
function postrating(Link) {
	window.open(Link.href, "postrating", "width=400,height=120,resizable=yes,scrollbars=yes,location=no,status=no");
}
function ResizeImg(img, maxwidth) {
	if(img.width >= maxwidth && maxwidth != 0) {
		img.width = maxwidth;
		img.height = Math.round(img.height/(img.width/maxwidth));
		img.title = lng['imgtitle'];
		if (LightBoxCallback) {
			HandCursor(img);
			img.onclick = function() {LightBoxCallback(img);}
		}
	}
}

///////////////////////// AJAX /////////////////////////
function ieRand() {
	if (document.all && !window.opera) {
		return '&rndcache='+Math.floor(Math.random()*1000000);
	}
	else {
		return '';
	}
}

/*
XHConn - Simple XMLHTTP interface - bfults@gmail.com - 2005-04-08
Code licensed under Creative Commons Attribution-ShareAlike License
http://creativecommons.org/licenses/by-sa/2.0/
*/
function ajax() {
	var xmlhttp, bComplete = false;
	try {
		xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
	}
	catch (e) {
		try {
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		catch (e) {
			try {
				xmlhttp = new XMLHttpRequest();
			}
			catch (e) {
				xmlhttp = false;
			}
		}
	}
	if (!xmlhttp) {
		return null;
	}

  	this.connect = function(sURL, sMethod, sVars, fnDone) {
		if (!xmlhttp) {
			return false;
		}
		bComplete = false;
		sMethod = sMethod.toUpperCase();

		try {
	  		if (sMethod == "GET") {
				xmlhttp.open(sMethod, sURL+"?"+sVars, true);
				sVars = "";
		  	}
			else {
				xmlhttp.open(sMethod, sURL, true);
				xmlhttp.setRequestHeader("Method", "POST "+sURL+" HTTP/1.1");
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		  	}
		  	xmlhttp.onreadystatechange = function(){
				if (xmlhttp.readyState == 4 && !bComplete) {
			  		bComplete = true;
			  		fnDone(xmlhttp);
				}
		  	};
		  	xmlhttp.send(sVars);
		}
		catch(z) {
			return false;
		}
		return true;
	};

	return this;
}

///////////////////////// Functions for Tooltips/PopUp-Menus /////////////////////////
function TryHideMenu(menu,CountHide) {
	if (CountHide != MenuCountHide) {
		return;
	}
	Hide(menu);
}
function MenuEvent() {
	var elementevent = FetchElement("popup_"+active);
	elementevent.onmouseover = elemMouseOver;
	elementevent.onmouseout = elemMouseOut;
}
function elemMouseOver() {
	MenuCountHide++;
}
function elemMouseOut() {
	setTimeout("TryHideMenu('" + active + "', " + MenuCountHide + ")", MenuTimeout);
}
function ShowMenu(id) {
	if(active != 0) {
		if (id == active) {
			HideMenu(active);
    	}
		else {
			Hide(active);
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

		elementmenu.style.zIndex = 10;
		elementmenu.style.left = posx+'px';
		elementmenu.style.top = posy+'px';
		elementmenu.style.visibility = 'visible';
		active = id;
		MenuEvent();
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
	id = this.id.replace("menu_","");
	elemMouseOver();
	if (active != 0 && active != id) {
		HideMenu(active);
		ShowMenu(id);
	}
	else {
		this.onmouseout = elemMouseOut;
	}
}
function Hide() {
    if (active != 0) {
		HideMenu(active);
	}
}

///////////////////////// Tooltips /////////////////////////
function RegisterTooltip(id) {
	id = "tooltip_"+id
	var buttonregister = FetchElement("menu_"+id);
	if(buttonregister) {
		buttonregister.onmouseover = ShowTooltip;
		window.onresize = Hide;

		if (typeof buttonregister.title != 'undefined' && buttonregister.title.length > 0) {
			element = FetchElement("header_"+id);
			if (typeof element != 'undefined' && element != null) {
				element.innerHTML = buttonregister.title;
				element.className = 'tooltip_header';
			}
			buttonregister.title = '';
		}

		if (active != 0 && active != id) {
			HideMenu(active);
		}
		else {
			this.onmouseout = elemMouseOut;
		}
		ShowMenu(id);
	}
}
function ShowTooltip() {
	id = this.id.replace("menu_","");
	elemMouseOver();
	if (active != 0 && active != id) {
		HideMenu(active);
	}
	else {
		this.onmouseout = elemMouseOut;
	}
	ShowMenu(id);
}

///////////////////////// PopUp-Menus /////////////////////////
function RegisterMenu(id) {
	var buttonregister = FetchElement("menu_"+id);
	if(buttonregister) {
		HandCursor(buttonregister);
		buttonregister.unselectable = true;
		buttonregister.onclick = Click;
		buttonregister.onmouseover = Swap;
		window.onresize = Hide;
	}
	return false;
}