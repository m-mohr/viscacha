<?php
/***************************************************************************

FeedCreator class v1.7.2
originally (c) Kai Blankenhorn
www.bitfolge.de
kaib@bitfolge.de

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define("FEEDCREATOR_VERSION", "Viscacha ".$config['version']." - FeedCreator 1.7.x");



/**
 * A FeedItem is a part of a FeedCreator feed.
 *
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 * @since 1.3
 */
class FeedItem extends HtmlDescribable {
	/**
	 * Mandatory attributes of an item.
	 */
	var $title, $description, $link;
	
	/**
	 * Optional attributes of an item.
	 */
	var $author, $authorEmail, $image, $category, $comments, $guid, $source, $creator;
	
	/**
	 * Publishing date of an item. May be in one of the following formats:
	 *
	 *	RFC 822:
	 *	"Mon, 20 Jan 03 18:05:41 +0400"
	 *	"20 Jan 03 18:05:41 +0000"
	 *
	 *	ISO 8601:
	 *	"2003-01-20T18:05:41+04:00"
	 *
	 *	Unix:
	 *	1043082341
	 */
	var $date;
	
	/**
	 * Any additional elements to include as an assiciated array. All $key => $value pairs
	 * will be included unencoded in the feed item in the form
	 *     <$key>$value</$key>
	 * Again: No encoding will be used! This means you can invalidate or enhance the feed
	 * if $value contains markup. This may be abused to embed tags not implemented by
	 * the FeedCreator class used.
	 */
	var $additionalElements = Array();

	// Added by Joseph LeBlanc, contact@jlleblanc.com
	var $enclosures = Array();
	function addEnclosure($url, $length = 0, $type)
	{
		$this->enclosures[] = array("url" => $url, "length" => $length, "type" => $type);
	}
	// end add, Joseph LeBlanc

	// on hold
	// var $source;
}



/**
 * An FeedImage may be added to a FeedCreator feed.
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 * @since 1.3
 */
class FeedImage extends HtmlDescribable {
	/**
	 * Mandatory attributes of an image.
	 */
	var $title, $url, $link;
	
	/**
	 * Optional attributes of an image.
	 */
	var $width, $height, $description;
}



/**
 * An HtmlDescribable is an item within a feed that can have a description that may
 * include HTML markup.
 */
class HtmlDescribable {
	/**
	 * Indicates whether the description field should be rendered in HTML.
	 */
	var $descriptionHtmlSyndicated;
	
	/**
	 * Indicates whether and to how many characters a description should be truncated.
	 */
	var $descriptionTruncSize;
	
	/**
	 * Returns a formatted description field, depending on descriptionHtmlSyndicated and
	 * $descriptionTruncSize properties
	 * @return    string    the formatted description  
	 */
	function getDescription() {
		$descriptionField = new FeedHtmlField($this->description);
		$descriptionField->syndicateHtml = $this->descriptionHtmlSyndicated;
		$descriptionField->truncSize = $this->descriptionTruncSize;
		return $descriptionField->output();
	}

}


/**
 * An FeedHtmlField describes and generates
 * a feed, item or image html field (probably a description). Output is 
 * generated based on $truncSize, $syndicateHtml properties.
 * @author Pascal Van Hecke <feedcreator.class.php@vanhecke.info>
 * @version 1.6
 */
class FeedHtmlField {
	/**
	 * Mandatory attributes of a FeedHtmlField.
	 */
	var $rawFieldContent;
	
	/**
	 * Optional attributes of a FeedHtmlField.
	 * 
	 */
	var $truncSize, $syndicateHtml;
	
	/**
	 * Creates a new instance of FeedHtmlField.
	 * @param  $string: if given, sets the rawFieldContent property
	 */
	function FeedHtmlField($parFieldContent) {
		if ($parFieldContent) {
			$this->rawFieldContent = $parFieldContent;
		}
	}
		
		
	/**
	 * Creates the right output, depending on $truncSize, $syndicateHtml properties.
	 * @return string    the formatted field
	 */
	function output() {
		// when field available and syndicated in html we assume 
		// - valid html in $rawFieldContent and we enclose in CDATA tags
		// - no truncation (truncating risks producing invalid html)
		if (!$this->rawFieldContent) {
			$result = "";
		}	elseif ($this->syndicateHtml) {
			$result = "<![CDATA[".$this->rawFieldContent."]]>";
		} else {
			if ($this->truncSize and is_int($this->truncSize)) {
				$result = FeedCreator::iTrunc(htmlspecialchars($this->rawFieldContent),$this->truncSize);
			} else {
				$result = htmlspecialchars($this->rawFieldContent);
			}
		}
		return $result;
	}

}



/**
 * UniversalFeedCreator lets you choose during runtime which
 * format to build.
 * For general usage of a feed class, see the FeedCreator class
 * below or the example above.
 *
 * @since 1.3
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 */
class UniversalFeedCreator extends FeedCreator {
	var $_feed;
	var $dir;
	
	function _setFormat($format) {
		$format = strtoupper($format);
		$data = file('data/feedcreator.inc.php');
		$data = array_map('trim', $data);
		foreach ($data as $feed) {
			$feed = explode("|", $feed);
			$f[$feed[0]] = array(
				'class' => $feed[0],
				'file' => $feed[1],
				'name' => $feed[2],
				'active' => $feed[3],
				'header' => $feed[4]
			);
		}
		if (!isset($f[$format])) {
			$t = current($f);
			$format = $t['class'];
		}
		$format = $f[$format];
		if (!class_exists($format['class'])) {
			include('classes/feedcreator/'.$format['file']);
		}
		eval('$this->_feed = new '.$format['class'].'();');
        
		$vars = get_object_vars($this);
		foreach ($vars as $key => $value) {
			// do not copy "_feed" itself
			if (!in_array($key, array("_feed", "contentType", "encoding"))) {
				$this->_feed->{$key} = $this->{$key};
			}
		}
	}
	
	/**
	 * Creates a syndication feed based on the items previously added.
	 *
	 * @see        FeedCreator::addItem()
	 * @param    string    format    format the feed should comply to. 
	 * @return    string    the contents of the feed.
	 */
	function createFeed($format = '') {
		$this->_setFormat($format);
		return $this->_feed->createFeed();
	}
	
	
	function setDir($dir) {
		$this->dir = $dir;
	}
	
	
	/**
	 * Saves this feed as a file on the local disk. After the file is saved, an HTTP redirect
	 * header may be sent to redirect the use to the newly created file.
	 * @since 1.4
	 * 
	 * @param	string	format	format the feed should comply to.
	 * @param	string	filename	optional	the filename where a recent version of the feed is saved. If not specified, the filename is $_SERVER["SCRIPT_NAME"] with the extension changed to .xml (see _generateFilename()).
	 * @param	boolean	displayContents	optional	send the content of the file or not. If true, the file will be sent in the body of the response.
	 */
	function saveFeed($format = '', $filename="", $displayContents=true) {
		$this->_setFormat($format);
		if (!empty($filename)) {
			$filename = $this->dir.$filename;
		}
		$this->_feed->saveFeed($filename, $displayContents);
	}


   /**
    * Turns on caching and checks if there is a recent version of this feed in the cache.
    * If there is, an HTTP redirect header is sent.
    * To effectively use caching, you should create the FeedCreator object and call this method
    * before anything else, especially before you do the time consuming task to build the feed
    * (web fetching, for example).
    *
    * @param   string   format   format the feed should comply to. 
    * @param filename   string   optional the filename where a recent version of the feed is saved. If not specified, the filename is $_SERVER["SCRIPT_NAME"] with the extension changed to .xml (see _generateFilename()).
    * @param timeout int      optional the timeout in seconds before a cached version is refreshed (defaults to 3600 = 1 hour)
    */
   function useCached($format = '', $filename="", $header = true, $timeout=3600) {
        $this->_setFormat($format);
		if (!empty($filename)) {
			$filename = $this->dir.$filename;
		}
        $this->_feed->useCached($filename, $header, $timeout);
   }

}


/**
 * FeedCreator is the abstract base implementation for concrete
 * implementations that implement a specific format of syndication.
 *
 * @abstract
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 * @since 1.4
 */
class FeedCreator extends HtmlDescribable {

	/**
	 * Mandatory attributes of a feed.
	 */
	var $title, $description, $link;
	
	
	/**
	 * Optional attributes of a feed.
	 */
	var $syndicationURL, $image, $language, $copyright, $pubDate, $lastBuildDate, $editor, $editorEmail, $webmaster, $category, $docs, $ttl, $rating, $skipHours, $skipDays;

	/**
	* The url of the external xsl stylesheet used to format the naked rss feed.
	* Ignored in the output when empty.
	*/
	var $xslStyleSheet = "";
	var $cssStyleSheet = "";
	
	
	/**
	 * @access private
	 */
	var $items = Array();
 	
	
	/**
	 * This feed's MIME content type.
	 * @since 1.4
	 * @access private
	 */
	var $contentType = "application/xml";
	
	
	/**
	 * This feed's character encoding.
	 * @since 1.6.1
	 **/
	var $encoding = "ISO-8859-1";
	
	
	/**
	 * Any additional elements to include as an assiciated array. All $key => $value pairs
	 * will be included unencoded in the feed in the form
	 *     <$key>$value</$key>
	 * Again: No encoding will be used! This means you can invalidate or enhance the feed
	 * if $value contains markup. This may be abused to embed tags not implemented by
	 * the FeedCreator class used.
	 */
	var $additionalElements = Array();
   
    
	/**
	 * Adds an FeedItem to the feed.
	 *
	 * @param object FeedItem $item The FeedItem to add to the feed.
	 * @access public
	 */
	function addItem($item) {
		$this->items[] = $item;
	}
	
	
	/**
	 * Truncates a string to a certain length at the most sensible point.
	 * First, if there's a '.' character near the end of the string, the string is truncated after this character.
	 * If there is no '.', the string is truncated after the last ' ' character.
	 * If the string is truncated, " ..." is appended.
	 * If the string is already shorter than $length, it is returned unchanged.
	 * 
	 * @static
	 * @param string    string A string to be truncated.
	 * @param int        length the maximum length the string should be truncated to
	 * @return string    the truncated string
	 */
	function iTrunc($string, $length) {
		if (strlen($string)<=$length) {
			return $string;
		}
		
		$pos = strrpos($string,".");
		if ($pos>=$length-4) {
			$string = substr($string,0,$length-4);
			$pos = strrpos($string,".");
		}
		if ($pos>=$length*0.4) {
			return substr($string,0,$pos+1)." ...";
		}
		
		$pos = strrpos($string," ");
		if ($pos>=$length-4) {
			$string = substr($string,0,$length-4);
			$pos = strrpos($string," ");
		}
		if ($pos>=$length*0.4) {
			return substr($string,0,$pos)." ...";
		}
		
		return substr($string,0,$length-4)." ...";
			
	}
	
	
	/**
	 * Creates a comment indicating the generator of this feed.
	 * The format of this comment seems to be recognized by
	 * Syndic8.com.
	 */
	function _createGeneratorComment() {
		return "<!-- generator=\"".FEEDCREATOR_VERSION."\" -->\n";
	}
	
	
	/**
	 * Creates a string containing all additional elements specified in
	 * $additionalElements.
	 * @param	elements	array	an associative array containing key => value pairs
	 * @param indentString	string	a string that will be inserted before every generated line
	 * @return    string    the XML tags corresponding to $additionalElements
	 */
	function _createAdditionalElements($elements, $indentString="") {
		$ae = "";
		if (is_array($elements)) {
			foreach($elements AS $key => $value) {
				$ae.= $indentString."<$key>$value</$key>\n";
			}
		}
		return $ae;
	}
	
	function _createStylesheetReferences() {
		$xml = "";
		if ($this->cssStyleSheet) $xml .= "<?xml-stylesheet href=\"".$this->cssStyleSheet."\" type=\"text/css\"?>\n";
		if ($this->xslStyleSheet) $xml .= "<?xml-stylesheet href=\"".$this->xslStyleSheet."\" type=\"text/xsl\"?>\n";
		return $xml;
	}
	
	
	/**
	 * Builds the feed's text.
	 * @abstract
	 * @return    string    the feed's complete text 
	 */
	function createFeed() {
	}
	
	/**
	 * Generate a filename for the feed cache file. The result will be $_SERVER["SCRIPT_NAME"] with the extension changed to .xml.
	 * For example:
	 * 
	 * echo $_SERVER["SCRIPT_NAME"]."\n";
	 * echo FeedCreator::_generateFilename();
	 * 
	 * would produce:
	 * 
	 * /rss/latestnews.php
	 * latestnews.xml
	 *
	 * @return string the feed cache filename
	 * @since 1.4
	 * @access private
	 */
	function _generateFilename() {
		return $this->dir.strtolower(get_class($this)).".xml";
	}
	
	
	/**
	 * @since 1.4
	 * @access private
	 */
	function _redirect($filename, $op = true) {
		if ($op) {
			$a = 'inline';
		}
		else {
			$a = 'attachment';
		}
		viscacha_header("Content-Type: ".$this->contentType."; charset=".$this->encoding);
		viscacha_header("Content-Disposition: ".$a."; filename=".basename($filename));
		readfile($filename, "r");
		die();
	}
    
	/**
	 * Turns on caching and checks if there is a recent version of this feed in the cache.
	 * If there is, an HTTP redirect header is sent.
	 * To effectively use caching, you should create the FeedCreator object and call this method
	 * before anything else, especially before you do the time consuming task to build the feed
	 * (web fetching, for example).
	 * @since 1.4
	 * @param filename	string	optional	the filename where a recent version of the feed is saved. If not specified, the filename is $_SERVER["SCRIPT_NAME"] with the extension changed to .xml (see _generateFilename()).
	 * @param timeout	int		optional	the timeout in seconds before a cached version is refreshed (defaults to 3600 = 1 hour)
	 */
	function useCached($filename="", $header = true, $timeout=3600) {
		$this->_timeout = $timeout;
		if ($filename=="") {
			$filename = $this->_generateFilename();
		}
		if (file_exists($filename) AND (time()-filemtime($filename) < $timeout)) {
			$this->_redirect($filename, $header);
		}
	}
	
	
	/**
	 * Saves this feed as a file on the local disk. After the file is saved, a redirect
	 * header may be sent to redirect the user to the newly created file.
	 * @since 1.4
	 * 
	 * @param filename	string	optional	the filename where a recent version of the feed is saved. If not specified, the filename is $_SERVER["SCRIPT_NAME"] with the extension changed to .xml (see _generateFilename()).
	 * @param redirect	boolean	optional	send an HTTP redirect header or not. If true, the user will be automatically redirected to the created file.
	 */
	function saveFeed($filename="", $displayContents=true) {
		if ($filename=="") {
			$filename = $this->_generateFilename();
		}
		$feedFile = fopen($filename, "w");
		if ($feedFile) {
			fputs($feedFile,$this->createFeed());
			fclose($feedFile);
			$this->_redirect($filename, $displayContents);
		} else {
			echo "<br /><b>Error creating feed file, please check write permissions.</b><br />";
		}
	}
	
}


/**
 * FeedDate is an internal class that stores a date for a feed or feed item.
 * Usually, you won't need to use this.
 */
class FeedDate {
	var $unix;
	
	/**
	 * Creates a new instance of FeedDate representing a given date.
	 * Accepts RFC 822, ISO 8601 date formats as well as unix time stamps.
	 * @param mixed $dateString optional the date this FeedDate will represent. If not specified, the current date and time is used.
	 */
	function FeedDate($dateString="") {
		if ($dateString=="") $dateString = date("r");
		
		// MOD: changed is_integer to is_numeric
		if (is_numeric($dateString)) {
			$this->unix = $dateString;
			return;
		}
		if (preg_match("~(?:(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun),\\s+)?(\\d{1,2})\\s+([a-zA-Z]{3})\\s+(\\d{4})\\s+(\\d{2}):(\\d{2}):(\\d{2})\\s+(.*)~",$dateString,$matches)) {
			$months = Array("Jan"=>1,"Feb"=>2,"Mar"=>3,"Apr"=>4,"May"=>5,"Jun"=>6,"Jul"=>7,"Aug"=>8,"Sep"=>9,"Oct"=>10,"Nov"=>11,"Dec"=>12);
			$this->unix = mktime($matches[4],$matches[5],$matches[6],$months[$matches[2]],$matches[1],$matches[3]);
			if (substr($matches[7],0,1)=='+' OR substr($matches[7],0,1)=='-') {
				$tzOffset = (substr($matches[7],0,3) * 60 + substr($matches[7],-2)) * 60;
			} else {
				if (strlen($matches[7])==1) {
					$oneHour = 3600;
					$ord = ord($matches[7]);
					if ($ord < ord("M")) {
						$tzOffset = (ord("A") - $ord - 1) * $oneHour;
					} elseif ($ord >= ord("M") AND $matches[7]!="Z") {
						$tzOffset = ($ord - ord("M")) * $oneHour;
					} elseif ($matches[7]=="Z") {
						$tzOffset = 0;
					}
				}
				switch ($matches[7]) {
					case "UT":
					case "GMT":	$tzOffset = 0;
				}
			}
			$this->unix += $tzOffset;
			return;
		}
		if (preg_match("~(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(.*)~",$dateString,$matches)) {
			$this->unix = mktime($matches[4],$matches[5],$matches[6],$matches[2],$matches[3],$matches[1]);
			if (substr($matches[7],0,1)=='+' OR substr($matches[7],0,1)=='-') {
				$tzOffset = (substr($matches[7],0,3) * 60 + substr($matches[7],-2)) * 60;
			} else {
				if ($matches[7]=="Z") {
					$tzOffset = 0;
				}
			}
			$this->unix += $tzOffset;
			return;
		}
		$this->unix = 0;
	}

	/**
	 * Gets the date stored in this FeedDate as an RFC 822 date.
	 *
	 * @return a date in RFC 822 format
	 */
	function rfc822() {
		$date = date("D, d M Y H:i:s O", $this->unix);
		return $date;
	}
	
	/**
	 * Gets the date stored in this FeedDate as an ISO 8601 date.
	 *
	 * @return a date in ISO 8601 format
	 */
	function iso8601() {
	  	//$int_date: current date in UNIX timestamp
	   	$date_mod = date('Y-m-d\TH:i:s', $this->unix);
	   	$pre_timezone = date('O', $this->unix);
	   	$time_zone = substr($pre_timezone, 0, 3).":".substr($pre_timezone, 3, 2);
	   	$date_mod .= $time_zone;
	   	return $date_mod;
	}
	
	/**
	 * Gets the date stored in this FeedDate as an KlipFolio date.
	 *
	 * @return a date in KlipFolio format
	 */
	function v0001() {
		$date = gmdate("Y.m.d:Hi",$this->unix);
		return $date;
	}

	/**
	 * Gets the date stored in this FeedDate as an MBOX date.
	 *
	 * @return a date in MBOX format
	 */
	function v0002() {
		$date = gmdate("D M d H:i:s Y",$this->unix);
		return $date;
	}


	
	/**
	 * Gets the date stored in this FeedDate as unix time stamp.
	 *
	 * @return a date as a unix time stamp
	 */
	function unix() {
		return $this->unix;
	}
}
?>
