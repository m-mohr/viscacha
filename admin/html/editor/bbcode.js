var ColM = new Array("300","330","360","390","3C0","3F0","6F0","6C0","690","660","630","600","F00","F30","F60","F90","FC0","FF0","303","333","363","393","3C3","3F3","6F3","6C3","693","663","633","603","F03","F33","F63","F93","FC3","FF3","306","336","366","396","3C6","3F6","6F6","6C6","696","666","636","606","F06","F36","F66","F96","FC6","FF6","309","339","369","399","3C9","3F9","6F9","6C9","699","669","639","609","F09","F39","F69","F99","FC9","FF9","30C","33C","36C","39C","3CC","3FC","6FC","6CC","69C","66C","63C","60C","F0C","F3C","F6C","F9C","FCC","FFC","30F","33F","36F","39F","3CF","3FF","6FF","6CF","69F","66F","63F","60F","F0F","F3F","F6F","F9F","FCF","FFF","00F","03F","06F","09F","0CF","0FF","9FF","9CF","99F","96F","93F","90F","C0F","C3F","C6F","C9F","CCF","CFF","00C","03C","06C","09C","0CC","0FC","9FC","9CC","99C","96C","93C","90C","C0C","C3C","C6C","C9C","CCC","CFC","009","039","069","099","0C9","0F9","9F9","9C9","999","969","939","909","C09","C39","C69","C99","CC9","CF9","006","036","066","096","0C6","0F6","9F6","9C6","996","966","936","906","C06","C36","C66","C96","CC6","CF6","003","033","063","093","0C3","0F3","9F3","9C3","993","963","933","903","C03","C33","C63","C93","CC3","CF3","000","030","060","090","0C0","0F0","9F0","9C0","990","960","930","900","C00","C30","C60","C90","CC0","CF0","000","222","444","666","888","AAA", "CCC", "EEE", "FFF");

function generateColorPicker(param, url) {
	if (!url) {
		url = 'assets/empty.gif';
	}
	var cells = 17;
	var str = "";
	for (var i=0; i<ColM.length; ) {
		for (var j=0; j<=cells && i<ColM.length; j++) {
			var hex = '#'+ColM[i].charAt(0)+ColM[i].charAt(0)+ColM[i].charAt(1)+ColM[i].charAt(1)+ColM[i].charAt(2)+ColM[i].charAt(2);
			str += '<img style="background-color: '+hex+'; border-color: '+hex+';" src="'+url+'" onmouseover="hoverColor(this, 1)" onmouseout="hoverColor(this)" onClick="'+param.replace(/<color>/i, hex)+'" />';
			i++;
		}
		str += '<br clear="all" />';
	}
	return str;
}

/**
 * Gives a readable foreground-color for the given background-color.
 */
function invert(color) {
	var h = color.match(/^#{0,1}(\w{1,2})(\w{1,2})(\w{1,2})$/);
	var rgb = color.match(/\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)/);
	if (h) {
		var rgb = new Array();
    	for (var i = 1; i < h.length; i++) {
			var s = h[i];
			s = s.length == 1 ? s + s : s;
			rgb.push(parseInt(s, 16));
		}
	}
	else if (rgb) {
		rgb.shift();
	}
	if (rgb) {
		c = (0.213 * rgb[0] + 0.715 * rgb[1] + 0.072 * rgb[2]);
		return (c < 127.5) ? '#FFF' : '#000';
	}
	else {
		return '';
	}
}

function hoverColor(elem, state) {
	if (state == 1) {
		elem.style.borderColor = invert(elem.style.backgroundColor);
	}
	else {
		elem.style.borderColor = elem.style.backgroundColor;
	}
}

function resize_textarea(ta, type) {
	var textarea = document.getElementById(ta);
	var newrows = textarea.rows + type * 3;
	if (newrows >= 6) {
		textarea.rows = newrows;
	}
}
function textarea_length(ta, max) {
	var textarea = document.getElementById(ta);
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
	var elem = document.getElementById(id);
	var selection = '';
	if(typeof elem.selectionStart != 'undefined') {
		selection = elem.value.substring(elem.selectionStart, elem.selectionEnd);
	}
	if (empty(selection)) {
		return '';
	}
	else {
		return selection;
	}
}
function InsertAttachment(id, attachment) {
	var input = OpenerFetchElement(id);
	InsertTags(input, '[attachment=' + attachment + ']', '[/attachment]');
}
function InsertTags(input, aTag, eTag, param2) {
	if (typeof input == 'string') {
		input = document.getElementById(input);
	}
	if (input.selectionEnd) {
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
function InsertTable(id) {
	var elem = document.getElementById(id);
	var cols = document.getElementById('table_cols_'+id).value;
	var rows = document.getElementById('table_rows_'+id).value;
	var head = document.getElementById('table_head_'+id).checked;
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
}