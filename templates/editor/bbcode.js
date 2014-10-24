function buttonOver(button) {
	 button.className = 'editor_toolbar_button_on';
}
function buttonOut(button) {
	 button.className = 'editor_toolbar_button';
}
function buttonOverSmiley(button) {
	 button.className = 'editor_toolbar_smiley_on';
}
function buttonOutSmiley(button) {
	 button.className = 'editor_toolbar_smiley';
}

function resize_textarea(ta, type) {
	var textarea = FetchElement(ta);
	var newrows = textarea.rows + type * 3;
	if (newrows >= 6) {
		textarea.rows = newrows;
	}
}
function textarea_length(ta, max) {
	var textarea = FetchElement(ta);
	var value = textarea.value;
	if (navigator.appVersion.indexOf("Win") != -1) {
		var replace = "  ";
	}
	else {
		var replace = " ";
	}
	value = value.replace(new RegExp("[\\r\\n]{1}", "g"), replace);
	var av = max-value.length;
	if (av >= 0) {
		var key = 'js_ta_left';
	}
	else {
		var key = 'js_ta_too_much';
		av = av * -1;
	}
	alert(lng['js_ta_used']+av+lng[key]+"\n"+lng['js_ta_max']+max);
}

function empty(val) {
	if(typeof val == 'undefined' || val == '' || val == null) {
		return true;
	}
	else if (val.length > 0) {
		return false;
	}
	else {
		return true;
	}
}

function getSelection(id) {
	var elem = FetchElement(id);
	var selection = '';
	if(typeof document.selection != 'undefined') {
		selection = document.selection.createRange().duplicate().text;
	}
	else if(typeof elem.selectionStart != 'undefined') {
		selection = elem.value.substring(elem.selectionStart, elem.selectionEnd);
	}
	if (empty(selection)) {
		return '';
	}
	else {
		return selection;
	}
}
function InsertTags(id, aTag, eTag, param2) {
	var input = FetchElement(id);
	if(document.all && !window.opera && document.selection) { // IE
		var range = document.selection.createRange();
		var insText = range.duplicate().text;
		if (param2 > 0) {
			insText = '';
		}

		if (insText.length == 0) {
			input.focus();
		}
		document.selection.createRange().duplicate().text = aTag + insText + eTag;

		range = document.selection.createRange();
		if (insText.length == 0 && param2 != 2) {
			range.moveEnd('character', -eTag.length);
		}
		else {
			range.moveStart('character', aTag.length + insText.length + eTag.length);
		}
		range.select();
	}
	else if (input.selectionEnd) {
		var start_selection = input.selectionStart;
		var end_selection = input.selectionEnd;
		var new_endsel = end_selection + aTag.length + eTag.length;
		var scroll_top = input.scrollTop;
		var scroll_left = input.scrollLeft;

		var start = input.value.substring(0, start_selection);
		var insText = input.value.substring(start_selection, end_selection);
		var end = input.value.substring(end_selection, input.textLength);
		if (param2 > 0) {
			insText = '';
			new_endsel = new_endsel - (end_selection - start_selection);
		}

		input.value = start + aTag + insText + eTag + end;
		input.focus();
		if (insText.length == 0 && !param2) {
			input.selectionStart = new_endsel - eTag.length;
			input.selectionEnd = new_endsel - eTag.length;
		}
		else {
			input.selectionStart = new_endsel;
			input.selectionEnd = new_endsel;
		}
		input.scrollTop = scroll_top;
		input.scrollLeft = scroll_left;
	}
	else {
		input.value = input.value + aTag + '' + eTag;
	}
}
function InsertTagsList(id, type) {
	var Elements = new Array();
	var a = 1;
	if (empty(type) == false) {
		type = '='+type;
	}
	else {
		type = '';
	}
	var txt = window.prompt(lng['js_listpompt1']+a+lng['js_listpompt2'], getSelection(id));
	while (empty(txt) == false){
		a++;
		if (empty(txt) == false) {
			Elements[a] = "[*]"+txt;
		}
		txt = window.prompt(lng['js_listpompt1']+a+lng['js_listpompt2'], '');
	}
	if (Elements.length > 0){
		InsertTags(id, "[list"+type+"]"+Elements.join("\n"), '[/list]', 2);
	}
	else {
		InsertTags(id, "[list"+type+"]", "[/list]");
	}
}
function InsertTagsURL(id, front, end) {
	var selection = getSelection(id);
	var search = selection.search( /^(http:|ftp:|www\.)/i );
	if (search != -1) {
		var input1 = window.prompt(lng['bbcodes_url_prompt1'], selection); // Addr.
		var input2 = window.prompt(lng['bbcodes_url_prompt2'], ''); // Text
	}
	else {
		var input1 = window.prompt(lng['bbcodes_url_prompt1'], '');
		var input2 = window.prompt(lng['bbcodes_url_prompt2'], selection);
	}
	if (empty(input2)) {
		input2 = input1;
	}
	search = input1.search( /^www./i );
	if (search != -1) {
		input1 = 'http://' + input1;
	}
	InsertTagsParams(id, front, end, input1, input2, empty(selection));
}
function InsertTagsParams(id, front, end, input1, input2, noSelectionUsed) {
	if (!empty(input1) && !empty(input2)) {
		front = front.replace(/\{param1\}/i, input1);
		front = front.replace(/\{param2\}/i, input2);
		end = end.replace(/\{param1\}/i, input1);
		end = end.replace(/\{param2\}/i, input2);
		if (noSelectionUsed == true) {
			InsertTags(id, front, end, 1); // 1=No selection used
		}
		else {
			InsertTags(id, front, end, 2); // 2=Selection used
		}
	}
}
function InsertTagsMenu(id, front, end, menu) {
	InsertTags(id, front, end);
	if (menu.length > 0) {
		HideMenu(menu);
	}
}
function InsertTable(id) {
	var elem = FetchElement(id);
	var cols = FetchElement('table_cols_'+id).value;
	var rows = FetchElement('table_rows_'+id).value;
	var head = FetchElement('table_head_'+id).checked;
	var selection = getSelection(id);
	if (cols < 2) {
		cols = 2;
	}
	if (rows < 2) {
		rows = 2;
	}
	var str_open = "[table";
	if (head) {
		str_open += "=head";
	}
	str_open += "]\r\n"+selection;
	var str_close = '';
	for(var i = 1; rows >= i; i++) {
		for(var j = 1; cols > j; j++) {
			str_close += "|";
		}
		str_close += "\r\n";
	}
	str_close += "[/table]";
	InsertTags(id, str_open, str_close, 3);
	HideMenu('bbtable_'+id);
}