<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

/*
 * CalitrixWiki (c) Copyright 2004 by Johannes Klose
 * E-Mail: exe@calitrix.de
 * Project page: http://developer.berlios.de/projects/calitrixwiki
 *
 * CalitrixWiki is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * CalitrixWiki is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with CalitrixWiki; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 **/


	/**
	 * Computes the two textes and returns an array with the changes needed
	 * to trade back to the old text.
	 *
	 * @author Johannes Klose <exe@calitrix.de>
	 * @param  string $text1 The old text
	 * @param  string $text2 The new text
	 * @return array         Differences between $text1 and $text2
	 **/
	function getDiff($text1, $text2)
	{
		$lines1 = explode("\n", $text1);
		$lines2 = explode("\n", $text2);

		$obj   = new Text_Diff($lines2, $lines1);
		$diff  = $obj->getDiff();
		$ndiff = array();
		$lines = 0;

		/**
		 * Take the array with the differences and strip
		 * informations (unchanged lines, old values on changed lines)
		 * we do not need to store in the database to get from the
		 * new page version to the old one.
		 **/
		foreach($diff as $op)
		{
			if(strtolower(get_class($op)) == 'text_diff_op_copy') {
				$lines += count($op->orig);
				continue;
			}
			elseif(strtolower(get_class($op)) == 'text_diff_op_change') {
				if(count($op->orig) == count($op->final)) {
					foreach($op->final as $key => $val)
					{
						if(isset($op->orig[$key])) {
							$ndiff[$lines + $key] = array('~', $val);
						}
						else {
							$ndiff[$lines + $key] = array('+', $val);
						}
					}
				}
				elseif(count($op->orig) > count($op->final)) {
					foreach($op->orig as $key => $val)
					{
						if(isset($op->final[$key])) {
							$ndiff[$lines + $key] = array('~', $op->final[$key]);
						}
						else {
							$ndiff[$lines + $key] = array('-');
						}
					}
				}
				else {
					foreach($op->final as $key => $val)
					{
						if(isset($op->orig[$key])) {
							$ndiff[$lines + $key] = array('~', $op->final[$key]);
						}
						else {
							$ndiff[$lines + $key] = array('+', $op->final[$key]);
						}
					}
				}
			}
			elseif(strtolower(get_class($op)) == 'text_diff_op_add') {
				foreach($op->final as $key => $val)
				{
					$ndiff[$lines + $key] = array('+', $val);
				}
			}
			elseif(strtolower(get_class($op)) == 'text_diff_op_delete') {
				foreach($op->orig as $key => $val)
				{
					$ndiff[$lines + $key] = array('-');
				}
			}

			$lines += count($op->orig) > count($op->final) ? count($op->orig) : count($op->final);
		}

		return $ndiff;
	}

	/**
	 * Creates the array which contains two compared page versions
	 *
	 * @author Johannes Klose <exe@calitrix.de>
	 * @param  string $origText  Original page text
	 * @param  string $finalText Final page text
	 * @param  array  $versions  All page versions
	 * @return array         Page differences
	 **/
	function makeDiff($origText, $finalText) {

		$finalText = preg_replace("/(\r\n|\r|\n)+/", "\n", $finalText);
		$origText = preg_replace("/(\r\n|\r|\n)/", "\n", $origText);

		$diff = getDiff($finalText, $origText);

		$origLines  = explode("\n", $origText);
		$finalLines = explode("\n", $finalText);
		$lineCount  = count($origLines) > count($finalLines) ? count($origLines) : count($finalLines);
		$origTextT  = array();
		$finalTextT = array();
		$ol         = 0;
		$fl         = 0;

		for($i = 0; $i <= $lineCount; $i++)
		{
			if(isset($diff[$i])) {
				$opType = $diff[$i][0];
				$opVal  = isset($diff[$i][1]) ? $diff[$i][1] : '';

				if($opType == '~') {
					$algo = levenshtein($origLines[$ol], $opVal);
					$length = ( strlen($origLines[$ol])+strlen($opVal) ) / 2 * 0.5;
					if ($algo < $length) {
						$origTextT[]  = array('type' => 'edit', 'line' => htmlentities($origLines[$ol]));
						$finalTextT[] = array('type' => 'edit', 'line' => htmlentities($opVal));
						$ol++;
						$fl++;
					}
					else {
						$origTextT[]  = array('type' => 'subs', 'line' => htmlentities($origLines[$ol]));
						$finalTextT[] = array('type' => 'add', 'line' => htmlentities($opVal));
						$ol++;
						$fl++;
					}
				}
				elseif($opType == '+') {
					$origTextT[]  = array('type' => 'none', 'line' => '&nbsp;');
					$finalTextT[] = array('type' => 'add', 'line' => htmlentities($opVal));
					$fl++;
				}
				else {
					$origTextT[]  = array('type' => 'subs', 'line' => htmlentities($origLines[$ol]));
					$finalTextT[] = array('type' => 'none', 'line' => '&nbsp;');
					$ol++;
				}
			}
			else {
				if(isset($origLines[$ol])) {
					$origTextT[] = array('type' => 'none', 'line' => htmlentities($origLines[$ol]));
					$ol++;
				}

				if(isset($finalLines[$fl])) {
					$finalTextT[] = array('type' => 'none', 'line' => htmlentities($finalLines[$fl]));
					$fl++;
				}
			}
		}

		return array('orig' => $origTextT, 'final' => $finalTextT);
	}

/* matchlen(): returns the length of matching
 * substrings at beginning of $a and $b
 */
function matchlen(&$a, &$b) {
	$c=0;
	$alen = strlen($a);
	$blen = strlen($b);
	$d = min($alen, $blen);
	while(@($a{$c} == $b{$c}) && ($c < $d)) {
  		$c++;
  	}
  	return $c;
}

/* Returns a table describing
 * the differences of $a and $b */
function calcdiffer($a, $b)
{
  $alen = strlen($a);
  $blen = strlen($b);
  $aptr = 0;
  $bptr = 0;

  $ops = array();

  while($aptr < $alen && $bptr < $blen)
  {
   $matchlen = matchlen(substr($a, $aptr), substr($b, $bptr));
   if($matchlen)
   {
     $ops[] = array('=', substr($a, $aptr, $matchlen));
     $aptr += $matchlen;
     $bptr += $matchlen;
     continue;
   }
   /* Difference found */

   $bestlen=0;
   $bestpos=array(0,0);
   for($atmp = $aptr; $atmp < $alen; $atmp++)
   {
     for($btmp = $bptr; $btmp < $blen; $btmp++)
     {
       $matchlen = matchlen(substr($a, $atmp), substr($b, $btmp));
       if($matchlen>$bestlen)
       {
         $bestlen=$matchlen;
         $bestpos=array($atmp,$btmp);
       }
       if($matchlen >= $blen-$btmp)break;
     }
   }
   if(!$bestlen)break;

   $adifflen = $bestpos[0] - $aptr;
   $bdifflen = $bestpos[1] - $bptr;

   if($adifflen)
   {
     $ops[] = array('-', substr($a, $aptr, $adifflen));
     $aptr += $adifflen;
   }
   if($bdifflen)
   {
     $ops[] = array('+', substr($b, $bptr, $bdifflen));
     $bptr += $bdifflen;
   }
   $ops[] = array('=', substr($a, $aptr, $bestlen));
   $aptr += $bestlen;
   $bptr += $bestlen;
  }
  if($aptr < $alen)
  {
   /* b has too much stuff */
   $ops[] = array('-', substr($a, $aptr));
  }
  if($bptr < $blen)
  {
   /* a has too little stuff */
   $ops[] = array('+', substr($b, $bptr));
  }
  return $ops;
}
?>