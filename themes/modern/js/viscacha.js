var cookieprefix = 'vc';
var mq_cookiename = 'mquote';
var sidebar_cookiename = 'sidebar';

// Sidebar
function initMenu() {
	// ToDo: Store sidebar state in Cookie
	$(".sidebar-toggle").css('display', 'inline-block');
	$('#sidebar ul').hide();
	$('#sidebar ul').children('.current').parent().show();
	$('#sidebar li a').click(
		function () {
			var checkElement = $(this).next();
			if (checkElement.is('ul')) {
				if (checkElement.is(':visible')) {
					checkElement.slideUp('normal');
				}
				else {
					$('#sidebar ul:visible').each(function() {
						if ($(this).has(checkElement).length === 0) {
							$(this).slideUp('normal');
						}
					});
					checkElement.slideDown('normal');
				}
				return false;
			}
		}
	);
	$(".sidebar-toggle").click(function (e) {
		e.preventDefault();
		$("#wrapper").toggleClass("sidebar-toggled");
		$('#sidebar ul').hide();
	});
}
$(document).ready(function () {
	initMenu();
});

// Upload Popup
function adduploads(elem) { // ToDo: Non-JS alternative
	window.open(elem.href, "adduploads", "width=550,height=450,resizable=yes,scrollbars=yes,location=no,status=yes");
}

// Cookies
function SetCookie(name, value) {
	name = cookieprefix + '_' + name;
	var a = new Date();
	a = new Date(a.getTime() + 1000*60*60*24*365);
	document.cookie = name + '=' + escape(value)+ '; expires='+a.toGMTString()+';';
}
function GetCookie(name) {
	if(document.cookie == '') {
		return false;
	}

	name = cookieprefix + '_' + name;
	var c = document.cookie;
	var firstPos = c.indexOf(name);
	if(firstPos != -1) {
		firstPos += name.length + 1;
		var lastPos = c.indexOf(';', firstPos);
		if(lastPos == -1) {
			lastPos = c.length;
		}
		return unescape(c.substring(firstPos, lastPos));
	}
	else {
		return false;
	}
}
function KillCookie(name) {
	name = cookieprefix + '_' + name;
	document.cookie = name + '=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
}

// Multiquote
function mq_init() {
	var c = GetCookie(mq_cookiename);
	if(c) {
		var values = c.split(',');
		for(var i = 0; i < values.length; i++) {
			mq_toggle_button(values[i], true);
		}
	}
}
function mq_toggle_button(id, add) {
	if (add) {
		$('#mq_'+id).addClass('active');
// ToDo: Add language resources
//		$('#mq_'+id+'_link').text(lng['js_quote_multi_2']);
	}
	else {
		$('#mq_'+id).removeClass('active');
//		$('#mq_'+id+'_link').text(lng['js_quote_multi']);
	}
}

function multiquote(id) {
	var c = GetCookie(mq_cookiename);
	var values = new Array();
	var newval = new Array();
	var add = true;
	if(c) {
		values = c.split(',');
		for(var i = 0; i < values.length; i++) {
			if(values[i] == id) {
				 add = false;
			}
			else {
				newval[newval.length] = values[i];
			}
		}
	}
	mq_toggle_button(id, add);
	if(add) {
		newval[newval.length] = id;
	}

	SetCookie(mq_cookiename, newval.join(','));
}
$(document).ready(function () {
	mq_init();
});