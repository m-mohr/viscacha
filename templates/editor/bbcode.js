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

function writeRow(id) {
	var cells = 17;
	var str = "";
	var ar = new Array("#330000","#333300","#336600","#339900","#33CC00","#33FF00","#66FF00","#66CC00","#669900","#666600","#663300","#660000","#FF0000","#FF3300","#FF6600","#FF9900","#FFCC00","#FFFF00","#330033","#333333","#336633","#339933","#33CC33","#33FF33","#66FF33","#66CC33","#669933","#666633","#663333","#660033","#FF0033","#FF3333","#FF6633","#FF9933","#FFCC33","#FFFF33","#330066","#333366","#336666","#339966","#33CC66","#33FF66","#66FF66","#66CC66","#669966","#666666","#663366","#660066","#FF0066","#FF3366","#FF6666","#FF9966","#FFCC66","#FFFF66","#330099","#333399","#336699","#339999","#33CC99","#33FF99","#66FF99","#66CC99","#669999","#666699","#663399","#660099","#FF0099","#FF3399","#FF6699","#FF9999","#FFCC99","#FFFF99","#3300CC","#3333CC","#3366CC","#3399CC","#33CCCC","#33FFCC","#66FFCC","#66CCCC","#6699CC","#6666CC","#6633CC","#6600CC","#FF00CC","#FF33CC","#FF66CC","#FF99CC","#FFCCCC","#FFFFCC","#3300FF","#3333FF","#3366FF","#3399FF","#33CCFF","#33FFFF","#66FFFF","#66CCFF","#6699FF","#6666FF","#6633FF","#6600FF","#FF00FF","#FF33FF","#FF66FF","#FF99FF","#FFCCFF","#FFFFFF","#0000FF","#0033FF","#0066FF","#0099FF","#00CCFF","#00FFFF","#99FFFF","#99CCFF","#9999FF","#9966FF","#9933FF","#9900FF","#CC00FF","#CC33FF","#CC66FF","#CC99FF","#CCCCFF","#CCFFFF","#0000CC","#0033CC","#0066CC","#0099CC","#00CCCC","#00FFCC","#99FFCC","#99CCCC","#9999CC","#9966CC","#9933CC","#9900CC","#CC00CC","#CC33CC","#CC66CC","#CC99CC","#CCCCCC","#CCFFCC","#000099","#003399","#006699","#009999","#00CC99","#00FF99","#99FF99","#99CC99","#999999","#996699","#993399","#990099","#CC0099","#CC3399","#CC6699","#CC9999","#CCCC99","#CCFF99","#000066","#003366","#006666","#009966","#00CC66","#00FF66","#99FF66","#99CC66","#999966","#996666","#993366","#990066","#CC0066","#CC3366","#CC6666","#CC9966","#CCCC66","#CCFF66","#000033","#003333","#006633","#009933","#00CC33","#00FF33","#99FF33","#99CC33","#999933","#996633","#993333","#990033","#CC0033","#CC3333","#CC6633","#CC9933","#CCCC33","#CCFF33","#000000","#003300","#006600","#009900","#00CC00","#00FF00","#99FF00","#99CC00","#999900","#996600","#993300","#990000","#CC0000","#CC3300","#CC6600","#CC9900","#CCCC00","#CCFF00","#000000","#333333","#666666","#999999","#cccccc","#ffffff");
	for (var i=0; i<ar.length; ) {
		for (var j=0; j<=cells && i<ar.length; j++) {
			str += "<span style='background-color: " + ar[i] + ";' onClick='InsertTagsMenu(\""+id+"\", \"[color="+ar[i]+"]\", \"[/color]\", \"bbcolor_"+id+"\");'><img src='images/empty.gif' alt='' /></span>";
			i++;
		}
		if (window.clipboardData && document.compatMode && !window.XMLHttpRequest) {
			str += '<br style="line-height: 0px;" />';
		}
		else {
			str += '<br clear="all" />';
		}
	}
	return str;
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
function InsertTagsNote(id, front, end) {
	var selection = getSelection(id);
	var input2 = window.prompt(lng['bbcodes_note_prompt2'], selection); // Def.
	var input1 = window.prompt(lng['bbcodes_note_prompt1'], ''); // Abbr.
	InsertTagsParams(id, front, end, input1, input2, empty(selection));
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