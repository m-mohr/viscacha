// Spellchecker öffnen
function openSpellChecker(val) {
	valdata = FetchElement(val);
	var speller = new spellChecker(valdata);
	speller.openChecker();
}

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

function HandCursor(element) {
	try {
		element.style.cursor = "pointer"
	}
	catch(e) {
		element.style.cursor = "hand"
	}
}

function submit_flood(Button) {
	Button.value=lng['js_submitted'];
}

// Einzelnen Post anzeigen
function showpost(pid) {
    window.open("popup.php?action=showpost&id="+pid+sidx,"","width=640,height=480,resizable=yes,scrollbars=yes,location=yes");
}
function edithistory(pid) {
    window.open("popup.php?action=edithistory&id="+pid+sidx,"","width=640,height=380,resizable=yes,scrollbars=yes,location=no");
}

// Öffne Upload-Verwaltung
function adduploads(Link) {
    window.open(Link.href,"","width=480,height=480,resizable=yes,scrollbars=yes,status=yes");  
}

// Abo hinzufügen
function addabo(formv,id) {
	if (formv == 'x') {
		return false;
	}
	else {
		location.href='editprofile.php?action=addabo&id='+id+'&temp='+formv+sidx;
	}
}

function confirmdelete(box) {
    if (box.checked == true) {
	    input=confirm(lng['js_confirm_pdelete']);
	    if (input==true) {
	        box.checked = true;
	    }
	    else {
	        box.checked = false;
	    }
	}
}

// Notiz löschen
function deletenotice(id) {
	input=confirm(lng['js_confirm_ndelete']);
	if (input==true) {
	    notices = document.getElementsByName("notice[]");
		notices[id].value = '';
		notices[id].style.display = 'none';
		Form = FetchElement('notice');
		Form.Submit();
		return;
	}
}

// Fenster mit Dateierklärungen öffnen
function filetypeinfo(ftype) {
	window.open("popup.php?action=filetypes&type="+ftype+sidx,"","width=400,height=250,resizable=no,scrollbars=yes");
}

// Bilder an Forum anpassen
function ResizeImg(img,maxwidth) {
	if(img.width >= maxwidth) {
		var owidth = img.width;
		var oheight = img.height;
		img.width = maxwidth;
		img.height = Math.round(oheight/(owidth/maxwidth));
		img.title = lng['imgtitle'];
		
		try {
		    img.style.cursor = "pointer";
	    }
	    catch(e) {
	    	img.style.cursor = "hand";
	    }
		
		img.onclick = function() {
			var width = screen.width-30;
			if (width > owidth) {
				width = owidth+30;
			}
			var height = screen.height-50;
			if (height > oheight) {
				height = oheight+30;
			}
			window.open(img.src,"","scrollbars=yes,status=no,toolbar=no,location=yes,directories=no,resizable=no,menubar=no,width="+width+",height="+height)
		}
	}
}

/* 
XHConn - Simple XMLHTTP interface - bfults@gmail.com - 2005-04-08
Code licensed under Creative Commons Attribution-ShareAlike License
http://creativecommons.org/licenses/by-sa/2.0/
*/

function ajax() {
  var xmlhttp, bComplete = false;
  try { xmlhttp = new ActiveXObject("Msxml2.XMLHTTP"); }
  catch (e) { try { xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); }
  catch (e) { try { xmlhttp = new XMLHttpRequest(); }
  catch (e) { xmlhttp = false; }}}
  if (!xmlhttp) return null;
  this.connect = function(sURL, sMethod, sVars, fnDone)
  {
    if (!xmlhttp) return false;
    bComplete = false;
    sMethod = sMethod.toUpperCase();

    try {
      if (sMethod == "GET")
      {
        xmlhttp.open(sMethod, sURL+"?"+sVars, true);
        sVars = "";
      }
      else
      {
        xmlhttp.open(sMethod, sURL, true);
        xmlhttp.setRequestHeader("Method", "POST "+sURL+" HTTP/1.1");
        xmlhttp.setRequestHeader("Content-Type",
          "application/x-www-form-urlencoded");
      }
      xmlhttp.onreadystatechange = function(){
        if (xmlhttp.readyState == 4 && !bComplete)
        {
          bComplete = true;
          fnDone(xmlhttp);
        }};
      xmlhttp.send(sVars);
    }
    catch(z) { return false; }
    return true;
  };
  return this;
}

function ieRand() {
	var IE = document.all? 1:0;
	if (IE) {
		return '&rndcache='+Math.floor(Math.random()*1000000);
	}
	else {
		return '';
	}
}

// 
// AJAX
//

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

// Setzt forum als gelesen
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
					names[i] = '<a href="javascript: ajax_smIns(\''+names[i]+'\');">'+names[i]+'</a>';
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

//
// Multiquote
//
var mq_cookiename = cookieprefix+'_vquote'; 
function mq_init() {
    var cookie = mqgetCookie();
    if(cookie) {
        var values = cookie.split(',');
        for(var i = 0; i < values.length; i++) {
            var id = 'mq_' + values[i];
			var itm = FetchElement(id);
            if(itm) {
                itm.src = mq_img_on;
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
    cookie = mqgetCookie();
    values = new Array();
    newval = new Array();
    add    = 1;
    
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
    }
    else {
        img.src = mq_img_off;
    }
    
    mqmakeCookie(newval.join(','));
    return;
}
