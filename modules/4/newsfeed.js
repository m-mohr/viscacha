// Newsfeeds-Navbox
var nf_index = new Array();
var nf_delay = new Array();
var nf_timeout = new Array();
var nf_hover = new Array();
var nf_content = new Array();

function registerNF(id, delay) {
    nf_index[id] = 0;
    nf_delay[id] = delay;
    nf_timeout[id] = 0;
    nf_hover[id] = 0;
    setTimeout("changeNF("+id+")",0);
    setTimeout("checkNF("+id+")",0);
}
function checkNF(id){

    if (nf_timeout[id] < nf_delay[id]) {
        if (nf_hover[id] == 0) {
            nf_timeout[id] = nf_timeout[id]+1;
        }
	    setTimeout("checkNF("+id+")",1000);
	}
	else if (nf_hover[id] == 0) {
	    nf_timeout[id] = 0;
	    setTimeout("changeNF("+id+")",1000);
	    setTimeout("checkNF("+id+")",1000);
	}
}
function changeNF(id){
	if (nf_index[id] >= nf_content[id].length) {
		nf_index[id] = 0;
	}
	
	fscroller = FetchElement("nf_"+id);
	fscroller.onmouseover = function() {
		nf_hover[id] = 1;
	}
	fscroller.onmouseout = function() {
	    nf_hover[id] = 0;
	}
	fscroller.innerHTML=nf_content[id][nf_index[id]];
	nf_index[id]++;
}