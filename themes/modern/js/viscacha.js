$(document).ready(function () {
	// Remove js only UI elements and remove the nonjs-only elements
	$('.js-only').removeClass('js-only');
	$('body').removeClass('no-js');
	$('.nonjs-only').hide();
	// Remove js only UI elements
	$('.pagination-separator').removeClass('disabled');
	// Initialize MultiQuote
	MultiQuote.init();
	// Initalize Menu
	Sidebar.init();
	// Remove loading screen
	$('body').addClass('loaded');
});

// Sidebar
var Sidebar = {
	cookiename: 'sidebar',
	init: function() {
		// ToDo: Store sidebar state in Cookie
		$('#sidebar ul').hide();
		// Show current element or expand first element by default
		var current = $('#sidebar ul').children('.current');
		if (current.length > 0) {
			current.parent().show();
		}
		else {
			$('#sidebar ul').first().show();
		}
		$('#sidebar li a').click(function () {
			var checkElement = $(this).next();
			if (checkElement.is('ul')) {
				if (checkElement.is(':visible')) {
					checkElement.slideUp('normal');
				} else {
					$('#sidebar ul:visible').each(function () {
						if ($(this).has(checkElement).length === 0) {
							$(this).slideUp('normal');
						}
					});
					checkElement.slideDown('normal');
				}
				return false;
			}
		});
		$(".sidebar-toggle").click(function (e) {
			e.preventDefault();
			$("body").toggleClass("sidebar-toggled");
//			$('#sidebar ul').hide();
		});
	}
};

// Cookies
var Cookies = {
	prefix: 'vc',
	set: function (name, value) {
		var a = new Date((new Date()).getTime() + 1000 * 60 * 60 * 24 * 365);
		document.cookie = this._Name(name) + '=' + escape(value) + '; expires=' + a.toGMTString() + ';';
	},
	get: function (name) {
		if (document.cookie != '') {
			name = this._Name(name);
			var firstPos = document.cookie.indexOf(name);
			if (firstPos !== -1) {
				firstPos += name.length + 1;
				var lastPos = document.cookie.indexOf(';', firstPos);
				if (lastPos === -1) {
					lastPos = document.cookie.length;
				}
				return unescape(document.cookie.substring(firstPos, lastPos));
			}
		}
		return false;
	},
	kill: function (name) {
		document.cookie = this._Name(name) + '=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
	},
	_Name: function (name) {
		return this.prefix + '_' + name;
	}
};

// Multiquote
var MultiQuote = {
	cookieName: 'mquote',
	init: function () {
		var c = Cookies.get(this.cookieName);
		if (c) {
			var values = c.split(',');
			for (var i = 0; i < values.length; i++) {
				this._toggleBtn(values[i], true);
			}
		}
		this.showFlashNotice();
	},
	toggle: function (id) {
		var c = Cookies.get(this.cookieName);
		var values = new Array();
		var newval = new Array();
		var add = true;
		if (c) {
			values = c.split(',');
			for (var i = 0; i < values.length; i++) {
				if (values[i] == id) {
					add = false;
				} else if (values[i].length > 0) {
					newval[newval.length] = values[i];
				}
			}
		}
		this._toggleBtn(id, add);
		if (add) {
			newval[newval.length] = id;
		}

		Cookies.set(this.cookieName, newval.join(','));
		this.showFlashNotice();
	},
	_toggleBtn: function (id, add) {
		if (add) {
			$('#mq_' + id).addClass('active');
			$('#mq_'+id+'_link').text('Gemerktes Mehrfachzitat entfernen'); // ToDo: I18N
		} else {
			$('#mq_' + id).removeClass('active');
			$('#mq_'+id+'_link').text('Beitrag für Mehrfachzitat merken'); // ToDo: I18N
		}
	},
	showFlashNotice: function() {
		var c = Cookies.get(this.cookieName);
		var notice = $('#mq_flashnotice');
		if (c && c.length > 0) {
			var num = c.split(',').length;
			var newNotice = $('<div class="alert alert-info alert-dismissible" id="mq_flashnotice"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><a class="btn btn-default" href="addreply.php?id=...">Jetzt gemerkte Beiträge zitieren <span class="badge">' + num + ' </span></a></div>'); // ToDo: I18N and add ID to link
			if (notice.length > 0) {
				notice.replaceWith(newNotice);
			}
			else {
				$('#flash-notifications').append(newNotice);
			}
		}
		else {
			notice.remove();
		}
	}
};

// Helpers

function checkAll(elem) {
	var all = document.getElementsByName(elem.value);
	for (var i = 0; i < all.length; i++) {
		all[i].checked = elem.checked;
	}
}

// Upload Popup
function adduploads() {
	window.open(null, "adduploads", "width=550,height=500,resizable=yes,scrollbars=yes,location=no,status=yes");
}

// Jump to page
function jumptopage(url) {
	var page = prompt(lng['js_page_jumpto'], '');
	if (page !== null && !isNaN(page) && page > 0) {
		document.location.href = url.replace(/&amp;/g, '&') + 'page=' + page + sidx;
	}
}

// Topic management
function topicSelectedForMod(element, modElementId) {
	var modElement = $('#' + modElementId);
	if (modElement.parent('#flash-notifications').length === 0) {
		$('#flash-notifications').append(modElement.detach());
	}
	var allCheckboxes = document.getElementsByName(element.name);
	for (var i = 0; i < allCheckboxes.length; i++) {
		var checkbox = allCheckboxes[i];
		if (checkbox.checked) {
			$('#' + modElementId).show();
			return;
		}
	}

	$('#' + modElementId).hide();
}

// AJAX
var sidx = '';
function markforumread(id, element) {
	$.get("ajax.php?action=markforumread&id=" + id + sidx, function () {
		$(element).removeClass("clickable").removeClass("icon-new").addClass("icon-old").attr("title", "");
	});
}
function marktopicread(id, element) {
	$.get("ajax.php?action=marktopicread&id=" + id + sidx, function () {
		$(element).removeClass("clickable").removeClass("icon-new").addClass("icon-old").attr("title", "");
	});
}