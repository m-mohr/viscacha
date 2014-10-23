<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

/**
* Convertion of Roman Numeral
*
* @version "just for fun"
* @author Huda M Elmatsani <justhuda at netscape dot net>
* @category Numbers
* @licence "up2u"
*
* example
* $converter = new ConvertRoman("2727");
* echo $converter->result();
* result is : MMDCCXXVII
* $converter = new ConvertRoman("MMDCCXXVII");
* echo $converter->result(); is 2727
* overscore on roman numeral is represented by underscore prefix
*/

/*
*	Matthias Mohr, 02.06.2005:
*	Added support to convert column numbers in to a letters
*/

class ConvertRoman {

var $number;
var $numrom;
var $romovr;

function ConvertRoman($number, $letter = FALSE) {
	$this->number = $number;

      $this->numrom = array("I"=>1,"A"=>4,
                              "V"=>5,"B"=>9,
                              "X"=>10,"E"=>40,
                              "L"=>50,"F"=>90,
                              "C"=>100,"G"=>400,
                              "D"=>500,"H"=>900,
                              "M"=>1000,"J"=>4000,
                              "P"=>5000,"K"=>9000,
                              "Q"=>10000,"N"=>40000,
                              "R"=>50000,"W"=>90000,
                              "S"=>100000,"Y"=>400000,
                              "T"=>500000,"Z"=>900000,
                              "U"=>1000000);
        $this->romovr = array("/_V/"=>"/P/",
                              "/_X/"=>"/Q/",
                              "/_L/"=>"/R/",
                              "/_C/"=>"/S/",
                              "/_D/"=>"/T/",
                              "/_M/"=>"/U/",
                              "/IV/"=>"/A/","/IX/"=>"/B/","/XL/"=>"/E/","/XC/"=>"/F/",
                              "/CD/"=>"/G/","/CM/"=>"/H/","/M_V/"=>"/J/","/MQ/"=>"/K/",
                              "/QR/"=>"/N/","/QS/"=>"/W/","/ST/"=>"/Y/","/SU/"=>"/Z/");

	if ($letter == TRUE) {
		$this->result = $this->int2ascii();
	}
	else {
		if(is_numeric($number)) {
			$this->convert2rom();
		}
		else {
			$this->convert2num();
		}
	}
}

function convert2num() {
$this->result = $this->convert_num();
}

function result() {
return $this->result;
}

function int2ascii() {
	$a = $this->number;
	return ($a-->26?chr(($a/26+25)%26+ord('A')):'').chr($a%26+ord('A'));
}

function convert2rom() {
if($this->number > 0) {
$this->result = $this->convert_rom();
} else return $this->raiseerror(1);
}

function convert_num() {
$number = $this->number;

$numrom = $this->numrom;

$romovr = $this->romovr;

$number = preg_replace(array_keys($romovr),array_values($romovr), $number);
print $number;
$split_rom = preg_split('//', strrev($number), -1, PREG_SPLIT_NO_EMPTY);
for($i=0; $i < sizeof($split_rom); $i++){
$num = $numrom[$split_rom[$i]];
if( $i > 0 && ($num < $numrom[$split_rom[$i-1]]))
$num = -$num;
$arr_num += $num;
}
return str_replace("/","",$arr_num);

}


function convert_rom() {
$number = $this->number;
$numrom = array_reverse($this->numrom);
$arabic = array_values($numrom);
$roman  = array_keys($numrom);
$str_roman = '';
//algorithm from oguds
$i = 0;
while($number != 0) {
while ($number >= $arabic[$i]) {
$number-=  $arabic[$i];
$str_roman .=  $roman[$i];
}
$i++;
}

$romovr =$this->romovr;

$str_roman = str_replace("/","",preg_replace(array_values($romovr),array_keys($romovr), $str_roman));

return $str_roman;
}

function raiseerror($num){
if($num==1)
echo "unsupported number";
}
}
?>