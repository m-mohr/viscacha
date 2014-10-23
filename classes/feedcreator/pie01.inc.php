<?php
/**
 * PIECreator01 is a FeedCreator that implements the emerging PIE specification,
 * as in http://intertwingly.net/wiki/pie/Syntax.
 *
 * @deprecated
 * @since 1.3
 * @author Scott Reynen <scott@randomchaos.com> and Kai Blankenhorn <kaib@bitfolge.de>
 */
class PIE01 extends FeedCreator {

	function PIE01() {
	//	$this->encoding = "utf-8";
	}

	function createFeed() {
		$feed = "<?xml version=\"1.0\" encoding=\"{$this->encoding}\"?>\n";
		$feed.= $this->_createStylesheetReferences();
		$feed.= "<feed version=\"0.1\" xmlns=\"http://www.tbray.org/ongoing/pie/0.1/pie.rnc\">\n";
		$feed.= "    <title>".$this->htmlspecialchars(FeedCreator::iTrunc($this->title,100))."</title>\n";
		$this->truncSize = 500;
		$feed.= "    <subtitle>".$this->getDescription()."</subtitle>\n";
		$feed.= "    <link>".$this->link."</link>\n";
		for ($i=0;$i<count($this->items);$i++) {
			$feed.= "    <entry>\n";
			$feed.= "        <title>".$this->htmlspecialchars(FeedCreator::iTrunc($this->items[$i]->title,100))."</title>\n";
			$feed.= "        <link>".$this->htmlspecialchars($this->items[$i]->link)."</link>\n";
			$itemDate = new FeedDate($this->items[$i]->date);
			$feed.= "        <created>".$this->htmlspecialchars($itemDate->iso8601())."</created>\n";
			$feed.= "        <issued>".$this->htmlspecialchars($itemDate->iso8601())."</issued>\n";
			$feed.= "        <modified>".$this->htmlspecialchars($itemDate->iso8601())."</modified>\n";
			$feed.= "        <id>".$this->htmlspecialchars($this->items[$i]->guid)."</id>\n";
			if ($this->items[$i]->author!="") {
				$feed.= "        <author>\n";
				$feed.= "            <name>".$this->htmlspecialchars($this->items[$i]->author)."</name>\n";
				if ($this->items[$i]->authorEmail!="") {
					$feed.= "            <email>".$this->items[$i]->authorEmail."</email>\n";
				}
				$feed.="        </author>\n";
			}
			$feed.= "        <content type=\"text/html\" xml:lang=\"en-us\">\n";
			$feed.= "            <div xmlns=\"http://www.w3.org/1999/xhtml\">".$this->items[$i]->getDescription()."</div>\n";
			$feed.= "        </content>\n";
			$feed.= "    </entry>\n";
		}
		$feed.= "</feed>\n";
		return $feed;
	}
}

?>
