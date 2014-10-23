<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

class PDF extends TCPDF {

// Bookmarks
var $outlines=array();
var $OutlineRoot;

function PDF($orientation='P',$unit='mm',$format='A4') {
	$this->TCPDF($orientation,$unit,$format);
	$this->setImageScale(4);
	$this->SetFixedFont('VeraMo');
}

function Header() {
	global $pdftitle, $config;
	$content = $this->unhtmlentities($config['fname'].': '.$pdftitle);
	$this->SetTextColor(0);
	$this->SetFont('Vera','B',11);
	$this->Cell(0,0,$content,0,0,'C');
	$this->Ln(10);
	$this->SetTextColor($this->prevTextColor[0], $this->prevTextColor[1], $this->prevTextColor[2]);
}

//Page footer
function Footer() {
	global $config, $lang;
	$this->SetY(-20);
	$this->SetTextColor(0);
	$this->SetFont('Vera','I',8);
	$lang->assign('PageNr', $this->PageNo());
	$this->Cell(0,8,$this->unhtmlentities($lang->phrase('pdf_footer')),0,0,'C');
	$this->Ln(2);
	$this->SetFont('Vera','I',7);
	$this->Cell(0,12,$config['furl']."/showtopic.php?id=".$_GET['id']."&page=".$_GET['page'],0,0,'C');
	$this->SetTextColor($this->prevTextColor[0], $this->prevTextColor[1], $this->prevTextColor[2]);
}

function PrintVote ($head, $body, $foot) {
	$this->SetFont('Vera','B',9);
	$this->writeHTMLCell(0,4,0,0,$head,0,1,'L');
	$this->SetFont('Vera','I',7);
	$this->writeHTMLCell(0,4,0,0,$foot,0,1,'L');
	$this->Ln(2);
	$this->SetFont('Vera','',8);
	$this->WriteHTML($body);
	$this->Ln(2);
	$this->SetDrawColor(0);
	$Width = $this->w - $this->lMargin-$this->rMargin;
	$this->Ln(5);
	$x = $this->GetX();
	$y = $this->GetY();
	$this->SetLineWidth(0.2);
	$this->Line($x,$y,$x+$Width,$y);
	$this->Ln(4);
}

function PrintTopic ($title,$postinfo,$comment) {
	$this->SetFont('Vera','B',9);
	$this->writeHTMLCell(0,4,0,0,$title,0,1,'L');
	$this->SetFont('Vera','I',7);
	$this->writeHTMLCell(0,4,0,0,$postinfo,0,1,'L');
	$this->Ln(2);
	$this->SetFont('Vera','',8);
	$this->WriteHTML($comment);
	$this->SetDrawColor(0);
	$Width = $this->w - $this->lMargin-$this->rMargin;
	$this->Ln(5);
	$x = $this->GetX();
	$y = $this->GetY();
	$this->SetLineWidth(0.2);
	$this->Line($x,$y,$x+$Width,$y);
	$this->Ln(4);
}

function px2mm($px, $dpi=96){
    return $px*25.4/$dpi;
}

// Bookmarks
function Bookmark($txt,$level=0,$y=0) {
	if($y==-1)
		$y=$this->GetY();
	$this->outlines[]=array('t'=>$this->unhtmlentities($txt),'l'=>$level,'y'=>$y,'p'=>$this->PageNo());
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
