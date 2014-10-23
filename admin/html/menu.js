var box_img_plus = 'admin/html/images/plus.gif';
var box_img_minus = 'admin/html/images/minus.gif';
var boxes = new Array();

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
function init() {
	for(var i=0; i < document.images.length; i++) {
	    name = document.images[i].alt;
		if (name == 'collapse') {
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
				else {
					switchimg.src = box_img_minus;
				}
			}
			HandCursor(switchimg);
			Switch(switchimg);
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
function Expand_all() {
	for (var i=1; i<=20; i++) {
		img = FetchElement('img_admin_menu'+i);
	    if (img) {
		    SetCookie('admin_menu'+i);
	    }
	}
	location.href = 'admin.php?action=frames';
}
function Collapse_all() {
	for (var i=0; i<=20; i++) {
	    img = FetchElement('img_admin_menu'+i);
		if (img) {
		    KillCookie('admin_menu'+i);
		    Switch(img);
		}
	}	
}
function All(job) {
	for(var i =0; i < document.images.length; i++) {
	    name = document.images[i].alt;
		if (name == 'collapse') {
			switchimg = document.images[i];
			id = switchimg.id.replace("img_","");
			part = FetchElement("part_"+id);
			if(job == 1) {
				switchimg.src = box_img_plus;
				part.style.display = 'none';
				SetCookie(id);
			}
			else {
				switchimg.src = box_img_minus;
				part.style.display = 'block';
				KillCookie(id);
			}
			Switch(switchimg);
		}
	}
}
function HandCursor(element) {
	try {
		element.style.cursor = "pointer";
	}
	catch(e) {
		element.style.cursor = "hand";
	}
}
