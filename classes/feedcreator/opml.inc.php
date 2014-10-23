<?php
/**
 * OPMLCreator is a FeedCreator that implements OPML 1.0.
 *
 * @see http://opml.scripting.com/spec
 * @author Dirk Clemens, Kai Blankenhorn
 * @since 1.5
 */
class OPML extends FeedCreator {

	function OPML() {
	}

	function createFeed() {
		$feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
		$feed.= $this->_createGeneratorComment();
		$feed.= $this->_createStylesheetReferences();
		$feed.= "<opml xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" version=\"1.0\">\n";
		$feed.= "	<head>\n";
		$feed.= "		<title>".$this->htmlspecialchars($this->title)."</title>\n";
		if ($this->pubDate!="") {
			$date = new FeedDate($this->pubDate);
			$feed.= "		 <dateCreated>".$date->rfc822()."</dateCreated>\n";
		}
		if ($this->lastBuildDate!="") {
			$date = new FeedDate($this->lastBuildDate);
			$feed.= "		 <dateModified>".$date->rfc822()."</dateModified>\n";
		}
		if ($this->editor!="") {
			$feed.= " 		<ownerName>".$this->htmlspecialchars($this->editor)."</ownerName>\n";
		}
		if ($this->editorEmail!="") {
			$feed.= "		 <ownerEmail>".$this->htmlspecialchars($this->editorEmail)."</ownerEmail>\n";
		}
		$feed.= "	</head>\n";
		$feed.= "	<body>\n";
		for ($i=0;$i<count($this->items);$i++) {
			$feed.= "	<outline type=\"link\" ";
			$title = $this->htmlspecialchars(strtr($this->items[$i]->title,"\n\r","  "));
			$feed.= " title=\"".$title."\"";
			$feed.= " text=\"".$title."\"";
			$feed.= " url=\"".$this->htmlspecialchars($this->items[$i]->link)."\"";
			if ($this->items[$i]->date!="") {
				$itemDate = new FeedDate($this->items[$i]->date);
				$feed.= " created=\"".$this->htmlspecialchars($itemDate->rfc822())."\"";
			}
			$feed.= "/>\n";
		}
		$feed.= "	</body>\n";
		$feed.= "</opml>\n";
		return $feed;
	}
}

?>
