///////////////////////// Variables /////////////////////////
var boxes = new Array();
var MenuTimeout = 500;
var active = 0;
var MenuCountHide = 0;

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
		var owidth = img.width;
		var oheight = img.height;
		img.width = maxwidth;
		img.height = Math.round(oheight/(owidth/maxwidth));
		img.title = lng['imgtitle'];

		HandCursor(img);
		img.onclick = function() {
			var width = screen.width-30;
			if (width > owidth) {
				width = owidth+40;
			}
			var height = screen.height-80;
			if (height > oheight) {
				height = oheight+70;
			}
			window.open(img.src,"","scrollbars=yes,status=yes,toolbar=no,location=yes,directories=no,resizable=yes,menubar=no,width="+width+",height="+height)
		}
	}
}
function openImageWindow(img, imgwidth, imgheight) {
    var width = screen.width-30;
    if (width > imgwidth) {
        width = imgwidth+40;
    }
    var height = screen.height-80;
    if (height > imgheight) {
        height = imgheight+70;
    }
    window.open(img.href,"","scrollbars=yes,status=yes,toolbar=no,location=yes,directories=no,resizable=yes,menubar=no,width="+width+",height="+height);
    return false;
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
		if (!document.all || window.opera) {
			elementmenu.style.overflow = 'auto';
		}
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
	if (!document.all || window.opera) {
		elementhide.style.overflow = 'hidden';
	}
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