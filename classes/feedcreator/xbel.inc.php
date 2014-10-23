<?php
/**
+ * XBELCreator is a FeedCreator that implements the XBEL specification,
+ * as in http://pyxml.sourceforge.net/topics/xbel/docs/html/xbel.html
+ *
+ * @since Viscacha 1.0
+ * @author Kimmo Suominen <kim@tac.nyc.ny.us>
+ */
class XBEL extends FeedCreator {
	
	function createFeed() {
		$feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
		$feed.= "<!DOCTYPE xbel PUBLIC\n";
		$feed.= '    "+//IDN python.org//DTD XML Bookmark Exchange Language 1.0//EN//XML"' . "\n";
		$feed.= '    "http://www.python.org/topics/xml/dtds/xbel-1.0.dtd">' . "\n";
		$feed.= $this->_createGeneratorComment();
		$feed.= '<xbel version="1.0"';
		$now = new FeedDate();
		$feed.= ' added="'.htmlspecialchars($now->iso8601()).'"';
		if ($this->language!="") {
			$feed.= " xml:lang=\"".$this->language."\"";
		}
		$feed.= ">\n";
		if ($this->title != "") {
			$feed.= "  <folder>\n";
			$feed.= "    <title>".htmlspecialchars($this->title)."</title>\n";
			$feed.= "    <desc>".htmlspecialchars($this->description)."</desc>\n";
		}
		$feed.= $this->_createAdditionalElements($this->additionalElements, "    ");
		$ocat = '';
		for ($i=0;$i<count($this->items);$i++) {
			if ($this->items[$i]->category != $ocat) {
				if ($ocat != '') {
					$feed.= "    </folder>\n";
				}
				$ocat = $this->items[$i]->category;
				if ($ocat != '') {
					$feed.= "    <folder>\n";
					$feed.= "      <title>".htmlspecialchars(strip_tags($ocat))."</title>\n";
				}
			}
			if (preg_match('/^-+$/', $this->items[$i]->title)) {
				$feed.= "    <separator/>\n";
				continue;
			}
			$feed.= '      <bookmark';
			$feed.= ' href="'.htmlspecialchars($this->items[$i]->link).'"';
			if ($this->items[$i]->date == "") {
				$itemDate = $now;
			} else {
				$itemDate = new FeedDate($this->items[$i]->date);
			}
			$feed.= ' added="'.htmlspecialchars($itemDate->iso8601()).'"';
			$feed.= ">\n";
			$feed.= "        <title>".htmlspecialchars(strip_tags($this->items[$i]->title))."</title>\n";
			if ($this->items[$i]->description!="") {
				$feed.= "        <desc>".htmlspecialchars($this->items[$i]->description)."</desc>\n";
			}
			$feed.= $this->_createAdditionalElements($this->items[$i]->additionalElements, "        ");
			$feed.= "      </bookmark>\n";
		}
		if ($ocat != '') {
			$feed.= "    </folder>\n";
		}
		if ($this->title != "") {
			$feed.= "  </folder>\n";
		}
		$feed.= "</xbel>\n";
		return $feed;
	}
}
?>