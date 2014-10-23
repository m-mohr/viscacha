<?php
/**
 * RSSCreator20 is a FeedCreator that implements RSS 2.0.
 *
 * @since 1.3
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 */
class RSS20 extends FeedCreator {

	/**
	 * Stores this RSS feed's version number.
	 * @access private
	 */
	var $RSSVersion;

	function RSS20() {
		$this->_setRSSVersion("2.0");
		$this->contentType = "application/rss+xml";
		$this->descriptionTruncSize = 500;
	}

	/**
	 * Sets this RSS feed's version number.
	 * @access private
	 */
	function _setRSSVersion($version) {
		$this->RSSVersion = $version;
	}

	/**
	 * Builds the RSS feed's text. The feed will be compliant to RDF Site Summary (RSS) 1.0.
	 * The feed will contain all items previously added in the same order.
	 * @return	string	the feed's complete text
	 */
	function createFeed() {
		$feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
		$feed.= $this->_createGeneratorComment();
		$feed.= $this->_createStylesheetReferences();
		$feed.= "<rss version=\"{$this->RSSVersion}\">\n";
		$feed.= "	<channel>\n";
		$feed.= "		<title>".$this->htmlspecialchars(FeedCreator::iTrunc($this->title,100))."</title>\n";

		$feed.= "		<description>".$this->getDescription($this->encoding)."</description>\n";
		$feed.= "		<link>{$this->link}</link>\n";
		$now = new FeedDate();
		$feed.= "		<lastBuildDate>".$this->htmlspecialchars($now->rfc822())."</lastBuildDate>\n";
		$feed.= "		<generator>".FEEDCREATOR_VERSION."</generator>\n";

		if ($this->image!=null) {
			$feed.= "		<image>\n";
			$feed.= "			<url>{$this->image->url}</url>\n";
			$feed.= "			<title>".$this->htmlspecialchars(FeedCreator::iTrunc($this->image->title,100))."</title>\n";
			$feed.= "			<link>{$this->image->link}</link>\n";
			if (!empty($this->image->width)) {
				$feed.= "			<width>{$this->image->width}</width>\n";
			}
			if (!empty($this->image->height)) {
				$feed.= "			<height>{$this->image->height}</height>\n";
			}
			if (!empty($this->image->description)) {
				$feed.= "			<description>".$this->image->getDescription($this->encoding)."</description>\n";
			}
			$feed.= "		</image>\n";
		}
		if ($this->language!="") {
			$feed.= "		<language>{$this->language}</language>\n";
		}
		if ($this->copyright!="") {
			$feed.= "		<copyright>".$this->htmlspecialchars(FeedCreator::iTrunc($this->copyright,100))."</copyright>\n";
		}
		if (!empty($this->editor) && !empty($this->editorEmail)) {
			$feed.= "		<managingEditor>".$this->htmlspecialchars($this->editorEmail)." (".$this->htmlspecialchars(FeedCreator::iTrunc($this->editor,100)).")</managingEditor>\n";
		}
		if ($this->webmaster!="") {
			$feed.= "		<webMaster>".$this->htmlspecialchars(FeedCreator::iTrunc($this->webmaster,100))."</webMaster>\n";
		}
		if ($this->pubDate!="") {
			$pubDate = new FeedDate($this->pubDate);
			$feed.= "		<pubDate>".$this->htmlspecialchars($pubDate->rfc822())."</pubDate>\n";
		}
		if ($this->category!="") {
			$feed.= "		<category>".$this->htmlspecialchars($this->category)."</category>\n";
		}
		if ($this->docs!="") {
			$feed.= "		<docs>".$this->htmlspecialchars(FeedCreator::iTrunc($this->docs,500))."</docs>\n";
		}
		if ($this->ttl!="") {
			$feed.= "		<ttl>".$this->htmlspecialchars($this->ttl)."</ttl>\n";
		}
		if ($this->rating!="") {
			$feed.= "		<rating>".$this->htmlspecialchars(FeedCreator::iTrunc($this->rating,500))."</rating>\n";
		}
		if ($this->skipHours!="") {
			$feed.= "		<skipHours>".$this->htmlspecialchars($this->skipHours)."</skipHours>\n";
		}
		if ($this->skipDays!="") {
			$feed.= "		<skipDays>".$this->htmlspecialchars($this->skipDays)."</skipDays>\n";
		}
		$feed.= $this->_createAdditionalElements($this->additionalElements, "	");

		for ($i=0;$i<count($this->items);$i++) {
			$feed.= "		<item>\n";
			$feed.= "			<title>".$this->htmlspecialchars(FeedCreator::iTrunc($this->items[$i]->title,100))."</title>\n";
			$feed.= "			<link>".$this->htmlspecialchars($this->items[$i]->link)."</link>\n";
			$feed.= "			<description>".$this->items[$i]->getDescription($this->encoding)."</description>\n";

			if (!empty($this->items[$i]->author) && !empty($this->items[$i]->authorEmail)) {
				$feed.= "			<author>".$this->htmlspecialchars($this->items[$i]->author)." &lt;".$this->htmlspecialchars($this->items[$i]->authorEmail)."&gt;</author>\n";
			}
			if ($this->items[$i]->category!="") {
				$feed.= "			<category>".$this->htmlspecialchars($this->items[$i]->category)."</category>\n";
			}
			if ($this->items[$i]->comments!="") {
				$feed.= "			<comments>".$this->htmlspecialchars($this->items[$i]->comments)."</comments>\n";
			}
			if ($this->items[$i]->date!="") {
			$itemDate = new FeedDate($this->items[$i]->date);
				$feed.= "			<pubDate>".$this->htmlspecialchars($itemDate->rfc822())."</pubDate>\n";
			}
			if ($this->items[$i]->guid!="") {
				$feed.= "			<guid>".$this->htmlspecialchars($this->items[$i]->guid)."</guid>\n";
			}
			$feed.= $this->_createAdditionalElements($this->items[$i]->additionalElements, "		");
			$feed.= "		</item>\n";
		}
		$feed.= "	</channel>\n";
		$feed.= "</rss>\n";
		return $feed;
	}
}
?>