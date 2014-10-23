function ieRand() {
	var IE = document.all? 1:0;
	if (IE) {
		return '&rndcache='+Math.floor(Math.random()*1000000);
	}
	else {
		return '';
	}
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

function check_all(elem) {
    var all = document.getElementsByName(elem);
    for(var i=0; i < all.length; i++) {
        if (all[i].checked == true) {
            all[i].checked = false;
        }
        else {
            all[i].checked = true;
        }
    }
}

function disable (txt) {
	if (txt.id == 'dis1') {
		input = FetchElement("dis2");
	}
	else {
		input = FetchElement("dis1");
	}
	
	if (txt.value != '') {
		input.disabled="disabled";
	}
	else {
		input.disabled="";
	}
    
	return;

}
function locate(url) {
	if (url != '') {
		location.href=url;
	}
}

function deleteit(rid){
	var res = confirm("Do you really want to delete the data?");
	if(res) locate('admin.php?action=query&job=delete&id='+rid);
}
function useit(rq){
	var revisedMessage;
	var currentMessage = document.getElementsByName("temp2")[0].value;
	revisedMessage = currentMessage+rq;
	document.getElementsByName("temp2")[0].value=revisedMessage;
	document.getElementsByName("temp2")[0].focus();
	return;
}

function docs() {
    window.open("admin.php?action=cms&job=nav_docslist","","width=480,height=480,resizable=yes,scrollbars=yes");   
}
function insert_doc(url,title) {
	opener.document.getElementsByName("url")[0].value = url;
	if (opener.document.getElementsByName("title")[0].value.length < 2) {
		opener.document.getElementsByName("title")[0].value = title;
	}
    top.close();
}

//
// Klappmenüs
//

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

var box_img_plus = 'admin/html/images/plus.gif';
var box_img_minus = 'admin/html/images/minus.gif';
var boxes = new Array();
function init(size) {
	for(var i=0; i < document.images.length; i++) {
	    name = document.images[i].name;
		if (name == 'c') {
			switchimg = document.images[i];
			id = switchimg.id.replace("img_","");
			boxes[i] = id;
			part = FetchElement("part_"+id);
			part.style.display = 'none';
			HandCursor(switchimg);
			switcher(switchimg);
		}
	}
}

function switcher(switchimg) {
	switchimg.onclick = function() {
		id = this.id.replace("img_","");
		part = FetchElement("part_"+id);
		disp = part.style.display;
		if(disp == 'none') {
			switchimg.src = box_img_minus;
			part.style.display = '';
		}
		else {
			switchimg.src = box_img_plus;
			part.style.display = 'none';
		}
	}
}

function All(job) {
	for(var i =0; i < document.images.length; i++) {
	    name = document.images[i].name;
		if (name == 'collapse') {
			switchimg = document.images[i];
			id = switchimg.id.replace("img_","");
			part = FetchElement("part_"+id);
			if(job == 1) {
				switchimg.src = box_img_plus;
				part.style.display = 'none';
			}
			else {
				switchimg.src = box_img_minus;
				part.style.display = 'block';
			}
			switcher(switchimg);
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

// Sucht nach Nutzernamen
function ajax_searchmember(field, ins) {
	inline = FetchElement(ins);
	if (field.value.length > 2) {
		var myConn = new ajax();
		if (!myConn) {alert('No connection');}
		var fnWhenDone = function (oXML) {
		    suggest = oXML.responseText;
		    if (suggest.length > 3) {
		        names = oXML.responseText.split(",");
				for (var i=0;i<names.length;i++) {
					names[i] = '<a href="javascript:ajax_smIns(\''+names[i]+'\', \''+field.id+'\', \''+ins+'\');">'+names[i]+'</a>';
				}
		    	inline.innerHTML = names.join(', ');
		    }
		    else {
		    	inline.innerHTML = 'None';
		    }
		};
		myConn.connect("ajax.php", "GET", "action=searchmember&name="+field.value+sidx+ieRand(), fnWhenDone);
	}
	else {
		inline.innerHTML = 'None (Name is too short)';
	}
}
// Sucht nach Nutzernamen - Einfügen d. Nutzernamens
function ajax_smIns(name, form, sugg) {
	inline = FetchElement(form);
	inline.value = name;
	inline2 = FetchElement(sugg);
	inline2.innerHTML = 'Name successfully inserted...';
}

function ajax_noki(img, params) {
	var myConn = new ajax();
	if (!myConn) {alert('Could not initalize AJAX. Make sure you are using an AJAX compatible browser.');}
	var fnWhenDone = function (oXML) {
	    if (oXML.responseText == '1' || oXML.responseText == '0') {
	    	img.src = noki(oXML.responseText);
	    }
	    else {
	    	alert(oXML.responseText);	
	    }
	};
	myConn.connect("admin.php", "GET", params+ieRand(), fnWhenDone);
}

function noki(int) {
	if (int == '1') {
		return 'admin/html/images/yes.gif';
	}
	else {
		return 'admin/html/images/no.gif';
	}
}

/*
Bases on Jeroen's Chmod Calculator
By Jeroen Vermeulen of Alphamega Hosting <jeroen@alphamegahosting.com>
*/
function octalchange() {
	var val = FetchElement('chmod').value;
	var ownerbin = parseInt(val.charAt(0)).toString(2);
	while (ownerbin.length<3) { ownerbin="0"+ownerbin; };
	var groupbin = parseInt(val.charAt(1)).toString(2);
	while (groupbin.length<3) { groupbin="0"+groupbin; };
	var otherbin = parseInt(val.charAt(2)).toString(2);
	while (otherbin.length<3) { otherbin="0"+otherbin; };
	FetchElement('owner4').checked = parseInt(ownerbin.charAt(0)); 
	FetchElement('owner2').checked = parseInt(ownerbin.charAt(1));
	FetchElement('owner1').checked = parseInt(ownerbin.charAt(2));
	FetchElement('group4').checked = parseInt(groupbin.charAt(0)); 
	FetchElement('group2').checked = parseInt(groupbin.charAt(1));
	FetchElement('group1').checked = parseInt(groupbin.charAt(2));
	FetchElement('other4').checked = parseInt(otherbin.charAt(0)); 
	FetchElement('other2').checked = parseInt(otherbin.charAt(1));
	FetchElement('other1').checked = parseInt(otherbin.charAt(2));
	calc_chmod(1);
};

function calc_chmod(nototals) {
  var users = new Array("owner", "group", "other");
  var totals = new Array("","","");

	for (var i=0; i<users.length; i++) {
	    var user=users[i];
		var field4 = user + "4";
		var field2 = user + "2";
		var field1 = user + "1";
		var number = 0;
	
		if (FetchElement(field4).checked == true) { number += 4; }
		if (FetchElement(field2).checked == true) { number += 2; }
		if (FetchElement(field1).checked == true) { number += 1; }

		totals[i] = totals[i]+number;
	
  };
	if (!nototals) {
	    FetchElement('chmod').value = totals[0] + totals[1] + totals[2];
	}
}

// Editor

function refreshElement(textarea, parentWindow) {
	if (parentWindow == 1) {
		return OpenerFetchElement(textarea);
	}
	else {
		return FetchElement(textarea);
	}
}
function InsertTags(field, aTag, eTag, parentWindow, param2) {
	var input = refreshElement(field, parentWindow);
	input.focus();

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
