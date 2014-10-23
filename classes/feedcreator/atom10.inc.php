<?php
/**
 * AtomCreator10 is a FeedCreator that implements the atom specification,
 * as in http://www.atomenabled.org/developers/syndication/atom-format-spec.php
 * Please note that just by using AtomCreator10 you won't automatically
 * produce valid atom files. For example, you have to specify either an editor
 * for the feed or an author for every single feed item.
 *
 * Some elements have not been implemented yet. These are (incomplete list):
 * author URL, item author's email and URL, item contents, alternate links,
 * other link content types than text/html. Some of them may be created with
 * AtomCreator10::additionalElements.
 *
 * @see FeedCreator#additionalElements
 * @since 1.7.2-mod (modified)
 * @author Mohammad Hafiz Ismail (mypapit@gmail.com)
 */
 class ATOM10 extends FeedCreator {

	function ATOM10() {
		$this->contentType = "application/atom+xml";
	}

	function createFeed() {
		$feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
		$feed.= $this->_createGeneratorComment();
		$feed.= $this->_createStylesheetReferences();
		$feed.= "<feed xmlns=\"http://www.w3.org/2005/Atom\"";
		if (!empty($this->language)) {
			$feed.= " xml:lang=\"".$this->language."\"";
		}
		$feed.= ">\n";
		$feed.= "    <title>".$this->htmlspecialchars($this->title)."</title>\n";
		$feed.= "    <subtitle>".$this->htmlspecialchars($this->description)."</subtitle>\n";
		$feed.= "    <link rel=\"alternate\" type=\"text/html\" href=\"".$this->htmlspecialchars($this->link)."\"/>\n";
		$feed.= "    <id>".$this->htmlspecialchars($this->link)."</id>\n";
		$now = new FeedDate();
		$feed.= "    <updated>".$this->htmlspecialchars($now->iso8601())."</updated>\n";
		if (!empty($this->editor)) {
			$feed.= "    <author>\n";
			$feed.= "        <name>".$this->htmlspecialchars($this->editor)."</name>\n";
			if (!empty($this->editorEmail)) {
				$feed.= "        <email>".$this->editorEmail."</email>\n";
			}
			$feed.= "    </author>\n";
		}
		$feed.= "    <generator>".FEEDCREATOR_VERSION."</generator>\n";
		$feed.= "<link rel=\"self\" type=\"application/atom+xml\" href=\"". $this->syndicationURL . "\" />\n";
		$feed.= $this->_createAdditionalElements($this->additionalElements, "    ");
		for ($i=0;$i<count($this->items);$i++) {
			$feed.= "    <entry>\n";
			$feed.= "        <title>".$this->htmlspecialchars($this->items[$i]->title)."</title>\n";
			$feed.= "        <link rel=\"alternate\" type=\"text/html\" href=\"".$this->htmlspecialchars($this->items[$i]->link)."\"/>\n";
			if (empty($this->items[$i]->date)) {
				$this->items[$i]->date = time();
			}
			$itemDate = new FeedDate($this->items[$i]->date);
			$feed.= "        <published>".$this->htmlspecialchars($itemDate->iso8601())."</published>\n";
			$feed.= "        <updated>".$this->htmlspecialchars($itemDate->iso8601())."</updated>\n";
			$feed.= "        <id>".$this->htmlspecialchars($this->items[$i]->link)."</id>\n";
			$feed.= $this->_createAdditionalElements($this->items[$i]->additionalElements, "        ");
			if (!empty($this->items[$i]->author)) {
				$feed.= "        <author>\n";
				$feed.= "            <name>".$this->htmlspecialchars($this->items[$i]->author)."</name>\n";
				$feed.= "        </author>\n";
			}
			if (!empty($this->items[$i]->description)) {
				$feed.= "        <summary type=\"html\">".$this->htmlspecialchars($this->items[$i]->description)."</summary>\n";
			}
			if (!empty($this->items[$i]->enclosure)) {
			$feed.="        <link rel=\"enclosure\" href=\"". $this->items[$i]->enclosure->url ."\" type=\"". $this->items[$i]->enclosure->type."\"  length=\"". $this->items[$i]->enclosure->length . "\" />\n";
			}
			$feed.= "    </entry>\n";
		}
		$feed.= "</feed>\n";
		return $feed;
	}
}
?>
