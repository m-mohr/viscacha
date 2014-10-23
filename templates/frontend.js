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
		else if (document.images[i].name == 'resize') {
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
function ajax_searchmember(name, key) {
	if (typeof key == 'number') { // undefined on blur
		// Not on special chars
		if (key < 48 || (key > 91 && key < 123)) {
			return;
		}
	}
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
function ajax_search(words, key) {
	if (typeof key == 'number') { // undefined on blur
		// Space (32), DEL (46), Backspace (8), "," (188)
		if (key != 32 && key != 8 && key != 46 && key != 188) {
			return;
		}
	}
	inline = FetchElement('searchsuggest');
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
	else {
		inline.innerHTML = '';
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


// pixpls: a tiny "lightbox" work-alike
// pesco 2010, isc license
// -/\- khjk.org
(function () {
	// config vars
	var fadtime = 400; // Fadingtime
	var fadstep = 4;
	var fps = Math.round(fadtime / 100 * fadstep);
	var cfg = function (d,v) {
		return eval("typeof "+v) === "undefined" ? d : eval(v);
	}
	var mrg = cfg(50, "pp_margin");     // margin to leave around zoom window
	var pad = cfg(10, "pp_padding");    // margin inside of zoom window
	var brd = cfg(1,  "pp_border");     // border width around zoom window
	var th  = cfg(32, "pp_footheight"); // height of text area below img
	var tm  = cfg(5,  "pp_footmargin"); // top margin of text area

	// image list
	var pics = [];

	// active elements
	var bak;        // shaded background div
	var div;        // our zoom window
	var img;        // the pic inside
	var jmg = null; // the next pic (currently loading)
	var num;        // image number
	var prv;        // prev button
	var cls;        // close button
	var nxt;        // next button
	var grpactive = -1;
	var imgactive = -1;
	var loading = false;

	var zclose = function () {
		div.style.visibility = "hidden";
		bak.style.visibility = "hidden";
		setgroup(-1);
	};

	var setgroup = function (grp) {
		grpactive = grp;
		imgactive = -1;
	};
	
	var startLoading = function() {
		var anim = function() {
			if (loading) {
				if ( bak.innerHTML.length >= 3 ) 
					bak.innerHTML = "";
				else 
					bak.innerHTML += ".";
				window.setTimeout(anim, 200);
			}
		};
		loading = true;
		anim();
	};
	
	var stopLoading = function() {
		loading = false;
		bak.innerHTML = '';
	};

	var zopen = function (i) {
		startLoading();
		imgactive = 0;
		var imgCnt = 1;
		var a = i;
		if (typeof i === 'number') {
			imgCnt = pics[grpactive].length;
			imgactive = i;
			a = pics[grpactive][imgactive];
		}


		// image metadata
		num.data = (imgactive+1) + "/" + imgCnt;

		// nav buttons
		cls.onclick = function () {
			zclose();
			return false;
		};
		bak.onclick = function () {
			zclose();
			return false;
		}
		if (imgactive > 0) {
			prv.onclick = function () {
				fadeout(imgactive-1);
				return false;
			};
			prv.style.opacity = 1;
		} else {
			prv.onclick = function () {
				return false;
			};
			prv.style.opacity = 0.3;
		}
		if (imgactive < imgCnt-1) {
			nxt.onclick = function () {
				fadeout(imgactive+1);
				return false;
			};
			nxt.style.opacity = 1;
		} else {
			nxt.onclick = function () {
				return false;
			};
			nxt.style.opacity = 0.3;
		}

		img.style.visibility = "hidden";
		bak.style.visibility = "visible";
		div.style.visibility = "visible";

		fadeinout('PPwin', 0);

		// start loading new image
		if (jmg !== null) {
			jmg.src = null;
			jmg.onload = null;
		}
		jmg = document.createElement("img");
		fromImg = (typeof a.href === 'undefined');
		if (!fromImg) {
			jmg.src = a.href;
		}
		else {
			jmg.src = a.src;
		}
		
		var cb = function () {
			stopLoading();
			zshape(jmg);          // resize window
			div.replaceChild(jmg, img);
			img = jmg;
			jmg = null;
			fadein();
		};

		if (!fromImg) {
			// switch to image when loaded
			jmg.onload = cb;
		}
		else {
			// Image is loaded, show it now
			cb();
		}
	};
	
	var fadeout = function(imgno) {
		var tmp = 0;
		for(i = 100; i >= 0; i = i-fadstep) {
			setTimeout("fadeinout('PPwin'," + i + ")", (tmp * fps));
			tmp++;
		}
		setTimeout(function() {zopen(imgno);}, fadtime);
	}

	var fadein = function() {
		var tmp = 0;
		for(i = 0; i <= 100; i = i+fadstep) {
			setTimeout("fadeinout('PPwin'," + i + ")", (tmp * fps));
			tmp++;
		}
	}

	var zshape = function (im) {
		var winw,winh;  // browser window dimensions
		var divw,divh;  // zoom window dimensions
		var oriw,orih;  // zoom window dimensions
		var maxw,maxh;  // maximum image dimensions (wrt margins etc.)
		var imgw,imgh;  // scaled image dimensions
		var r;          // image aspect ratio (w/h)
		var x,y;        // zoom window position

		winw = window.innerWidth;
		winh = window.innerHeight;
		oriw = im.width;
		orih = im.height;
		r = oriw / orih;
		maxw = winw - mrg*2 - pad*2 - brd*2;
		maxh = winh - mrg*2 - pad*2 - brd*2 - th - tm;
		imgw = Math.min(oriw, maxw, maxh*r);
		imgh = Math.min(orih, maxh, maxw/r);
		divw = imgw + pad*2 + brd*2;
		divh = imgh + pad*2 + brd*2 + th + tm;
		x = (winw - divw) / 2;
		y = (winh - divh) / 2;

		div.style.top = y + "px";
		div.style.left = x + "px";
		im.width = imgw;
		im.height = imgh;

		return (imgw / oriw);
	};

	var zinit = function () {
		var body = document.getElementsByTagName("body")[0];
		var i;

		// insert <div> into body
		bak.style.visibility = "hidden";
		div.style.visibility = "hidden";
		body.appendChild(bak);
		body.appendChild(div);

		// get access to <div>'s active subelements
		img = FetchElement("PPimg");
		num = FetchElement("PPnum").firstChild;
		prv = FetchElement("PPprv");
		cls = FetchElement("PPcls");
		nxt = FetchElement("PPnxt");

		img.width=400;
		img.height=300;
		zshape(img);

		// hook up thumbnail click handlers
		for (i=0; i<document.links.length; i++) (function () {
			var a = document.links[i];

			var regex = /lightbox\[(\d+)\]/;
			var matches = a.rel.match(regex);
			if (null != matches) {
				var num = parseInt(matches[1]);
				if (typeof pics[num] === 'undefined') {
					pics[num] = [];
				}
				var myj = pics[num].length;
				pics[num][myj] = a;
				a.onclick = function () {
					setgroup(num);
					zopen(myj);
					return false;
				};
			}
		}());

		// key handling
		body.onkeyup = function (evt) {
			if (imgactive > -1) {
				switch (evt.keyCode) {
					case 37:
						if (imgactive>0) {
							fadeout(imgactive-1);
						}
						break;  
					case 39:
						if (imgactive<pics[grpactive].length-1) {
							fadeout(imgactive+1);
						}
						break;
					case 27:
						zclose();
						break;
				}
			}
			return false;
		};

		LightBoxCallback = zopen;
	};

	// create the <div>
	(function () {
		bak = document.createElement("div");
		bak.id = "PPbak";
		div = document.createElement("div");
		div.id = "PPwin";
		div.innerHTML = "\
            <img id='PPimg'/>\
                <div id='PPnav' class='PPfat'>\
					<span id='PPnum'> </span>\
                    <a id='PPcls' class='PPbtn' href=''>X</a>\
                    <a id='PPprv' class='PPbtn' href=''>&lt;</a>\
                    <a id='PPnxt' class='PPbtn' href=''>&gt;</a>\
                </div>";
	}());
	
	if (document.addEventListener)
		document.addEventListener("DOMContentLoaded", zinit, false);
}());

function fadeinout(id, pas) {
	var element = FetchElement(id).style;
	if(pas >= 0) {
		element.opacity = (pas / 100);
		element.MozOpacity = (pas / 100);
		element.KhtmlOpacity = (pas / 100);
		element.filter = "alpha(opacity=" + pas + ")"; 
	}
}
