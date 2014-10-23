///////////////////////// Variables /////////////////////////
var mq_cookiename = cookieprefix+'_vquote';

///////////////////////// General / Misc. /////////////////////////
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
function ReloadCountdown(iv) {
	if (iv == -1) {
		window.location.reload();
	}
	else {
		countdown = FetchElement('countdown');
		countdown.firstChild.nodeValue = iv;
		iv = iv - 1;
		setTimeout("ReloadCountdown("+iv+")", 1000);
	}
}
function deletenotice(id) {
	input = confirm(lng['js_confirm_ndelete']);
	if (input == true) {
		notices = document.getElementsByName("notice[]");
		notices[id].value = '';
		noticeArea = FetchElement("notice_"+id);
		noticeArea.style.display = 'none';
		Form = FetchElement('notice');
		Form.submit();
		return;
	}
}
function confirmdelete(box) {
	if (box.checked == true) {
		input = confirm(lng['js_confirm_pdelete']);
		if (input == true) {
			box.checked = true;
		}
		else {
			box.checked = false;
		}
	}
}
function jumptopage(url) {
	var page = prompt(lng['js_page_jumpto'], '');
	if (page !== null && !isNaN(page) && page > 0) {
		document.location.href = url.replace(/&amp;/g, '&') + 'page=' + page + sidx;
	}
}

///////////////////////// AJAX /////////////////////////

// Schliesst oder oeffnet einen Beitrag
function ajax_openclosethread(id, img, isnew) {
	var myConn = new ajax();
	if (!myConn) {alert(lng['ajax0']);}
	var fnWhenDone = function (oXML) {
		if (oXML.responseText == '1' || oXML.responseText == '2') {
			lngval = 'ajax'+oXML.responseText;
			alert(lng[lngval]);
		}
		else if (oXML.responseText == '3' || oXML.responseText == '4') {
			lngval = 'ajax'+oXML.responseText+'_'+isnew;
			img.src = lng[lngval];
		}
	};
	myConn.connect("ajax.php", "GET", "action=openclosethread&id="+id+sidx+ieRand(), fnWhenDone);
}
// Setzt Forum als gelesen
function ajax_markforumread(id, img, small) {
	var myConn = new ajax();
	if (!myConn) {alert(lng['ajax0']);}
	var fnWhenDone = function (oXML) {
		if (oXML.responseText == '1') {
			if (small == 1) {
				img.src = lng['ajax_markforumread_small'];
			}
			else {
				img.src = lng['ajax_markforumread'];
			}
		}
		else {
			// ToDo: Error (0=No Permission)
		}
	};
	myConn.connect("ajax.php", "GET", "action=markforumread&id="+id+sidx+ieRand(), fnWhenDone);
}
// Checkt ob der Nutzername schon existiert
function ajax_doubleudata(name) {
	inline = FetchElement('udata_name');
	if (name.length > 3) {
		var myConn = new ajax();
		if (!myConn) {alert(lng['ajax0']);}
		var fnWhenDone = function (oXML) {
			if (oXML.responseText == '1') {
				lngval = 'ajax'+oXML.responseText;
				alert(lng[lngval]);
			}
			else {
				lngval = 'ajax'+oXML.responseText;
				inline.innerHTML = lng[lngval];
			}
		};
		myConn.connect("ajax.php", "GET", "action=doubleudata&name="+name+sidx+ieRand(), fnWhenDone);
	}
	else {
		inline.innerHTML = '';
	}
}
// Sucht nach Nutzernamen (PN)
function ajax_searchmember(name) {
	inline = FetchElement('membersuggest');
	if (name.length > 2) {
		var myConn = new ajax();
		if (!myConn) {alert(lng['ajax0']);}
		var fnWhenDone = function (oXML) {
			suggest = oXML.responseText;
			if (suggest.length > 3) {
				names = oXML.responseText.split(",");
				for (var i=0;i<names.length;i++) {
					names[i] = '<a tabindex="1'+i+'" href="javascript:ajax_smIns(\''+names[i]+'\');">'+names[i]+'</a>';
				}
				inline.innerHTML = lng['ajax7']+names.join(', ');
			}
			else {
				inline.innerHTML = '';
			}
		};
		myConn.connect("ajax.php", "GET", "action=searchmember&name="+name+sidx+ieRand(), fnWhenDone);
	}
	else {
		inline.innerHTML = '';
	}
}
// Sucht nach Nutzernamen (PN) - Einfügen d. Nutzernamens
function ajax_smIns(name) {
	inline = FetchElement('membersuggest_val');
	inline.value = name;
	inline2 = FetchElement('membersuggest');
	inline2.innerHTML = '';
}
// Sucht nach ignorierten Wörtern
function ajax_search(words) {
	inline = FetchElement('searchsuggest');
	inline.innerHTML = '';
	if (words.length > 2) {
		var myConn = new ajax();
		if (!myConn) {alert(lng['ajax0']);}
		var fnWhenDone = function (oXML) {
			x = oXML.responseText;
			if (x == '1') {
				inline.innerHTML = '';
			}
			else {
				ignore = x.split(",");
				if (ignore.length > 0) {
					inline.innerHTML = lng['ajax9']+ignore.join(', ');
				}
				else {
					inline.innerHTML = '';
				}
			}
		};
		myConn.connect("ajax.php", "GET", "action=search&search="+escape(words)+sidx+ieRand(), fnWhenDone);
	}
}
// Namen richtig setzen beim PM schreiben
function edit_pmto() {
	FetchElement('membersuggest_val').name = 'name';
	FetchElement('membersuggest_val2').name = 'name2';
	FetchElement('membersuggest_val').disabled = '';
	FetchElement('edit_pmto').style.display = 'none';
}

///////////////////////// MultiQuote /////////////////////////
function mq_init() {
	var cookie = mqgetCookie();
	if(cookie) {
		var values = cookie.split(',');
		for(var i = 0; i < values.length; i++) {
			var itm = FetchElement('mq_'+values[i]);
			var itml = FetchElement('mq_'+values[i]+'_link');
			if(itm) {
				itm.src = mq_img_on;
			}
			if(itml) {
				itml.innerHTML = lng['js_quote_multi_2'];
			}
		}
	}
}
function mqmakeCookie(value) {
	var cookie = mq_cookiename + '=' + escape(value) + '; ';
	document.cookie = cookie;
}
function mqgetCookie() {
	if(document.cookie == '') {
		return false;
	}

	var name = mq_cookiename;
	var firstPos;
	var lastPos;
	var cookie = document.cookie;
	firstPos = cookie.indexOf(name);
	if(firstPos != -1) {
		firstPos += name.length + 1;
		lastPos = cookie.indexOf(';', firstPos);
		if(lastPos == -1) {
			lastPos = cookie.length;
		}
		return unescape(cookie.substring(firstPos, lastPos));
	}
	else {
		return false;
	}
}
function multiquote(id) {
	img = FetchElement('mq_'+id);
	link = FetchElement('mq_'+id+'_link');
	cookie = mqgetCookie();
	values = new Array();
	newval = new Array();
	add	   = 1;

	if(cookie) {
		values = cookie.split(',');
		for(var i = 0; i < values.length; i++) {
			if(values[i] == id) {
				 add = 0;
			}
			else {
				newval[newval.length] = values[i];
			}
		}
	}
	if(add) {
		newval[newval.length] = id;
		img.src = mq_img_on;
		link.innerHTML = lng['js_quote_multi_2'];
	}
	else {
		img.src = mq_img_off;
		link.innerHTML = lng['js_quote_multi'];
	}

	mqmakeCookie(newval.join(','));
}