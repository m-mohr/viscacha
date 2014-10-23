<?php

DEFINE('V_TEMP_DIR', './temp/pdfimages/');

include('classes/class.imageconverter.php');

class PDF extends FPDF {

var $B;
var $I;
var $U;
var $HREF;
var $COLOR;
var $ALIGN;
var $TT;
var $CODE;
var $IMG;
var $H1;
var $H2;
var $H3;
var $SIZE;
var $SUB;
var $SUP;

// Bookmarks
var $outlines=array();
var $OutlineRoot;

function PDF($orientation='P',$unit='mm',$format='A4') {
	$this->FPDF($orientation,$unit,$format);
	$this->B=0;
	$this->I=0;
	$this->U=0;
	$this->HREF='';
	$this->COLOR='';
	$this->CODE=0;
	$this->TT='';
	$this->ALIGN='';
	$this->H1='';
	$this->H2='';
	$this->H3='';
	$this->SIZE='';
	$this->IMG='';
	$this->SUB=FALSE;
	$this->SUP=FALSE;
}


function Header() {
	global $pdftitle;
	$this->SetTextColor(0);
	$this->SetFont('Helvetica','B',11);
	$this->Cell(0,0,$pdftitle,0,0,'C');
	$this->Ln(10);
	$this->SetHEXColor($this->COLOR);
}

//Page footer
function Footer() {
	global $config, $lang;
	$this->SetY(-20);
	$this->SetTextColor(0);
	$this->SetFont('Helvetica','I',8);
	$lang->assign('PageNr', $this->PageNo());
	$this->Cell(0,8,$lang->phrase('pdf_footer'),0,0,'C');
	$this->Ln(2);
	$this->SetFont('Helvetica','I',7);
	$this->Cell(0,12,$config['furl']."/showtopic.php?id=".$_GET['id']."&page=".$_GET['page'],0,0,'C');
	$this->SetHEXColor($this->COLOR);
}

function PrintVote ($head, $body, $foot) {
	$this->SetFont('Helvetica','B',9);
	$this->Cell(0,4,$head,0,1,'L');
	$this->SetFont('Helvetica','I',7);
	$this->Cell(0,4,$foot,0,1,'L');
	$this->Ln(2);
	$this->SetFont('Helvetica','',8);
	$this->WriteHTML($body);
	$this->Ln(2);
	$this->SetDrawColor(0);
	$this->Line($this->GetX(), $this->GetY(), $this->GetX()+180, $this->GetY());
	$this->Ln(4);
}

function PrintTopic ($title,$postinfo,$comment) {
	$this->SetFont('Helvetica','B',9);
	$this->Cell(0,4,$title,0,1,'L');
	$this->SetFont('Helvetica','I',7);
	$this->Cell(0,4,$postinfo,0,1,'L');
	$this->Ln(2);
	$this->SetFont('Helvetica','',8);
	$this->WriteHTML($comment);
	$this->Ln(6);
	$this->SetDrawColor(0);
	$this->Line($this->GetX(), $this->GetY(), $this->GetX()+180, $this->GetY());
	$this->Ln(4);
}

function px2mm($px, $dpi=96){
    return $px*25.4/$dpi;
}

function WriteHTML($html) {
	$html=str_replace("\n",' ',$html);
	$html=str_replace("<br />",'<br>',$html);
	$a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	foreach($a as $i=>$e)
	{
		if($i%2==0)
		{
			$e = html_entity_decode($e);
			if($this->HREF)
				$this->PutLink($this->HREF,$e);
			elseif($this->COLOR)
				$this->PutColor($this->COLOR,$e);
			elseif($this->ALIGN) {
				$align = strtoupper(substr($this->ALIGN,0,1));
				$this->Cell(0,5,$e,0,1,$align);
			}
			elseif($this->SUP == TRUE) {
				$this->SetFontSize(6);
				$l = $this->GetStringWidth($e);
				$this->Text($this->GetX()+1,$this->GetY()+2,$e);
				$this->SetFontSize(8);
				$this->SetX($this->GetX()+$l+1);
			}
			elseif($this->SUB == TRUE) {
				$this->SetFontSize(6);
				$l = $this->GetStringWidth($e);
				$this->Text($this->GetX()+1,$this->GetY()+5,$e);
				$this->SetFontSize(8);
				$this->SetX($this->GetX()+$l+1);
			}
			else
				$this->Write(5,$e);
		}
		else
		{
			//Tag
			if($e{0}=='/')
				$this->CloseTag(strtoupper(substr($e,1)));
			else
			{
				//Extract attributes
				$a2=explode(' ',$e);
				$tag=strtoupper(array_shift($a2));
				$attr=array();
				foreach($a2 as $v)
					if(ereg('^([^=]*)=["\']?([^"\']*)["\']?$',$v,$a3))
						$attr[strtoupper($a3[1])]=$a3[2];
				$this->OpenTag($tag,$attr);
			}
		}
	}
}

function OpenTag($tag,$attr) {
	if($tag=='B' or $tag=='I' or $tag=='U')
		$this->SetStyle($tag,true);
	if($tag=='A')
		$this->HREF=$attr['HREF'];
	if($tag=='FONT') {
		if (isset($attr['COLOR'])) {
			$this->COLOR=$attr['COLOR'];
		}
		if (isset($attr['SIZE'])) {
			$this->SIZE=$attr['SIZE'];
			$this->SetFontSize($this->SIZE);
		}
	}
	if($tag=='BR')
		$this->Ln(5);
	if($tag=='P') {
		if (isset($attr['ALIGN'])) {
			$this->ALIGN=$attr['ALIGN'];
		}
	}
    if($tag=='IMG') {
		if(isset($attr['SRC'])) {
			$this->IMG = $attr['SRC'];
			$this->insertImage();
		}
    }
	if($tag=='HR') {
		if(!empty($attr['WIDTH'])) {
			$Width = $attr['WIDTH'];
		}
		else {
			$Width = $this->w - $this->lMargin-$this->rMargin;
		}
		$this->Ln(5);
		$x = $this->GetX();
		$y = $this->GetY();
		$this->SetLineWidth(0.2);
		$this->SetDrawColor(170,170,170);
		$this->Line($x,$y,$x+$Width,$y);
	}
	if($tag=='TT') {
		$this->SetFont('Courier');
	}
	if($tag=='H1') {
		$this->Ln(10);
		$this->SetFont('Times', 'B', 11);
	}
	if($tag=='H2') {
		$this->Ln(8);
		$this->SetFont('Times', 'B', 10);
	}
	if($tag=='H3') {
		$this->Ln(6);
		$this->SetFont('Times', 'B', 9);
	}
	if($tag=='CODE') {
		$this->SetTextColor(136,0,0);
		$this->SetFont('Courier');
	}
	if($tag=='SUP') {
		$this->SUP = TRUE;
	}
	if($tag=='SUB') {
		$this->SUB = TRUE;
	}
}

function CloseTag($tag) {
	if($tag=='B' or $tag=='I' or $tag=='U')
		$this->SetStyle($tag,false);
	if($tag=='A')
		$this->HREF='';
	if($tag=='FONT') {
		if ($this->COLOR) {
			$this->COLOR='';
		}
		if ($this->SIZE) {
			$this->SetFontSize(8);
			
		}
	}
	if($tag=='TT') {
		$this->SetFont('Helvetica');
	}
	if($tag=='CODE') {
		$this->SetTextColor(0);
		$this->SetFont('Helvetica');
	}
	if($tag=='H1' || $tag=='H2' || $tag=='H2') {
		$this->Ln(5);
		$this->SetFont('Helvetica','',8);
	}
    if($tag=='IMG') {
		$this->IMG = '';
    }
    if($tag=='P')
    	$this->ALIGN='';
	if($tag=='SUP') {
		$this->SUP = FALSE;
	}
	if($tag=='SUB') {
		$this->SUB = FALSE;
	}
}

function insertImage() {
	$type = get_extension($this->IMG);
	if ($type == 'gif') {
		$img = new ImageConverter($this->IMG,"png");
		$url = $img->getURL();
	}
	elseif ($type == 'bmp') {
		$img = new ImageConverter($this->IMG,"jpg");
		$url = $img->getURL();
	}
	else {
		$url = $this->IMG;
	}
	$nfo = @getimagesize($url);
	if ($nfo[0] > 0 && $nfo[1] > 0) {
		$x = $this->GetX();
		$y = $this->GetY();
		$sizex = $this->px2mm($nfo[0]);
		$sizey = $this->px2mm($nfo[1]);
		if ($sizey > $this->maximgheight) {
			$this->maximgheight = $sizey;
		}
		$pixel = $this->px2mm(1,72);
		$this->Image($url, $x+$pixel, $y, $sizex, $sizey, '', $this->IMG);
		$this->SetXY($x+$pixel+$sizex, $y);
	}
}

function SetStyle($tag,$enable) {
	$this->$tag+=($enable ? 1 : -1);
	$style='';
	foreach(array('B','I','U') as $s)
		if($this->$s>0)
			$style.=$s;
	$this->SetFont('',$style);
}

function PutLink($URL,$txt) {
	$this->SetTextColor(0,0,200);
	$this->SetStyle('U',true);
	$this->Write(5,$txt,$URL);
	$this->SetStyle('U',false);
	$this->SetTextColor(0);
}

function hex2dec($hex) {
	$color = str_replace('#', '', $hex);
	$ret = array(
	'r' => hexdec(substr($color, 0, 2)),
	'g' => hexdec(substr($color, 2, 2)),
	'b' => hexdec(substr($color, 4, 2))
	);
	return $ret;
}

function SetHEXColor ($color) {
	$c = $this->hex2dec($color);
	$this->SetTextColor($c['r'],$c['g'],$c['b']);
}

function PutColor($color,$txt) {
	$this->SetHEXColor($color);
	$this->Write(5,$txt);
	$this->SetTextColor(0);
}

// Bookmarks
function Bookmark($txt,$level=0,$y=0) {
	if($y==-1)
		$y=$this->GetY();
	$this->outlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->PageNo());
}

function _putbookmarks() {
	$nb=count($this->outlines);
	if($nb==0)
		return;
	$lru=array();
	$level=0;
	foreach($this->outlines as $i=>$o)
	{
		if($o['l']>0)
		{
			$parent=$lru[$o['l']-1];
			//Set parent and last pointers
			$this->outlines[$i]['parent']=$parent;
			$this->outlines[$parent]['last']=$i;
			if($o['l']>$level)
			{
				//Level increasing: set first pointer
				$this->outlines[$parent]['first']=$i;
			}
		}
		else
			$this->outlines[$i]['parent']=$nb;
		if($o['l']<=$level and $i>0)
		{
			//Set prev and next pointers
			$prev=$lru[$o['l']];
			$this->outlines[$prev]['next']=$i;
			$this->outlines[$i]['prev']=$prev;
		}
		$lru[$o['l']]=$i;
		$level=$o['l'];
	}
	//Outline items
	$n=$this->n+1;
	foreach($this->outlines as $i=>$o)
	{
		$this->_newobj();
		$this->_out('<</Title '.$this->_textstring($o['t']));
		$this->_out('/Parent '.($n+$o['parent']).' 0 R');
		if(isset($o['prev']))
			$this->_out('/Prev '.($n+$o['prev']).' 0 R');
		if(isset($o['next']))
			$this->_out('/Next '.($n+$o['next']).' 0 R');
		if(isset($o['first']))
			$this->_out('/First '.($n+$o['first']).' 0 R');
		if(isset($o['last']))
			$this->_out('/Last '.($n+$o['last']).' 0 R');
		$this->_out(sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]',1+2*$o['p'],($this->h-$o['y'])*$this->k));
		$this->_out('/Count 0>>');
		$this->_out('endobj');
	}
	//Outline root
	$this->_newobj();
	$this->OutlineRoot=$this->n;
	$this->_out('<</Type /Outlines /First '.$n.' 0 R');
	$this->_out('/Last '.($n+$lru[0]).' 0 R>>');
	$this->_out('endobj');
}

function _putresources() {
	parent::_putresources();
	$this->_putbookmarks();
}

function _putcatalog() {
	parent::_putcatalog();
	if(count($this->outlines)>0)
	{
		$this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
		$this->_out('/PageMode /UseOutlines');
	}
}

}
?>
