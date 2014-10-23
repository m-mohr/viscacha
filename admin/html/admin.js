///////////////////////// Variables /////////////////////////
var box_img_plus = 'admin/html/images/plus.gif';
var box_img_minus = 'admin/html/images/minus.gif';

///////////////////////// General / Misc. /////////////////////////
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
		location.href = url;
	}
}
function hideLanguageBoxes() {
	for(var i=1;i<256;i++) {
		box = FetchElement('language_'+i);
		check = FetchElement('use_'+i);
		if (box && check) {
			if (check.checked != true && check.checked != 'checked') {
				box.style.display = 'none';
			}
		}
	}
}
function useit(rq){
	var revisedMessage;
	var currentMessage = document.getElementsByName("temp2")[0].value;
	revisedMessage = currentMessage+rq;
	document.getElementsByName("temp2")[0].value=revisedMessage;
	document.getElementsByName("temp2")[0].focus();
	return;
}
function insert_doc(url,title) {
	opener.document.getElementsByName("url")[0].value = url;
	if (opener.document.getElementsByName("title")[0].value.length < 2) {
		opener.document.getElementsByName("title")[0].value = title;
	}
    top.close();
}

///////////////////////// PopUps / Confirm /////////////////////////
function openHookPosition(hook) {
	var url = 'admin.php?action=packages&job=plugins_hook_pos&hook=';
	if (hook == null) {
		var hook = FetchElement('hook').value;
	}
	window.open(url+hook+'#key', "sourcecode", "width=640,height=480,resizable=yes,scrollbars=yes,location=yes");
	return false;
}
function docs() {
    window.open("admin.php?action=cms&job=nav_docslist","","width=480,height=480,resizable=yes,scrollbars=yes");
}
function coms() {
    window.open("admin.php?action=cms&job=nav_comslist","","width=480,height=480,resizable=yes,scrollbars=yes");
}
function changeLanguageUsage(lid) {
	box = FetchElement('language_'+lid);
	if (box.style.display == 'none') {
		box.style.display = '';
		return true;
	}
	else {
		var test = confirm(lng['confirmNotUsed']);
		if (test) {
			box.style.display = 'none';
			return true;
		}
		else {
			return false;
		}
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
function initTranslateDetails() {
	for(var i=0; i < document.images.length; i++) {
	    name = document.images[i].name;
		if (name == 'c') {
			switchimg = document.images[i];
			id = switchimg.id.replace("img_","");
			boxes[i] = id;
			part = FetchElement("part_"+id);
			part.style.display = 'none';
			HandCursor(switchimg);
			Switch(switchimg, true);
		}
	}
}

///////////////////////// AdminCP /////////////////////////
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

///////////////////////// AJAX /////////////////////////
function ajax_backupinfo(file, id) {
	inline = FetchElement(id);
	inline.innerHTML = lng['ajax4'];
	var myConn = new ajax();
	if (!myConn) {alert(lng['ajax0']);}
	var fnWhenDone = function (oXML) {
    	inline.innerHTML = oXML.responseText;
	};
	myConn.connect("admin.php", "GET", "action=db&job=restore_info&file="+file+sidx, fnWhenDone);
}

function ajax_searchmember(field, ins, key) {
	if (typeof key == 'number') { // undefined on blur
		// Not on special chars
		if (key < 48 || (key > 91 && key < 123)) {
			return;
		}
	}
	inline = FetchElement(ins);
	if (field.value.length > 2) {
		var myConn = new ajax();
		if (!myConn) {alert(lng['ajax0']);}
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
		    	inline.innerHTML = lng['ajax3'];
		    }
		};
		myConn.connect("ajax.php", "GET", "action=searchmember&name="+field.value+sidx+ieRand(), fnWhenDone);
	}
	else {
		inline.innerHTML = lng['ajax2'];
	}
}
function ajax_smIns(name, form, sugg) {
	inline = FetchElement(form);
	inline.value = name;
	inline2 = FetchElement(sugg);
	inline2.innerHTML = lng['ajax1'];
}
function ajax_noki(img, params) {
	var myConn = new ajax();
	if (!myConn) {alert(lng['ajax0']);}
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
function noki(integer) {
	if (integer == '1') {
		return 'admin/html/images/yes.gif';
	}
	else {
		return 'admin/html/images/no.gif';
	}
}

///////////////////////// CHMOD /////////////////////////
// Bases on Jeroen's Chmod Calculator
// By Jeroen Vermeulen of Alphamega Hosting <jeroen@alphamegahosting.com>
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