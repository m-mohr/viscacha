var boxes = new Array();
function initImg(size) {
	for(var i =0; i < document.images.length; i++) {
		if (document.images[i].alt == 'switch') {
			switchimg = document.images[i];
			id = switchimg.id.replace("img_","");
			boxes[i] = id;
			part = FetchElement("part_"+id);
			if(document.cookie && part.style.display != 'none') {
				hide = GetCookie(id);
				if(hide != '') {
					switchimg.src = box_img_plus;
					part.style.display = 'none';
				}
			}
			HandCursor(switchimg);
			Switch(switchimg);
		}
		else if (document.images[i].name = 'resize') {
			ResizeImg(document.images[i],size);
		}
	}
}
function Switch(switchimg) {
	switchimg.onclick = function() {
		id = this.id.replace("img_","");
		part = FetchElement("part_"+id);
		disp = part.style.display;
		if(disp == 'none') {
			switchimg.src = box_img_minus;
			part.style.display = '';
			KillCookie(id);
		}
		else {
			switchimg.src = box_img_plus;
			part.style.display = 'none';
			SetCookie(id);
		}
	}
}
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
// === Not compatible with opera? ===
var MenuTimeout = 500;
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