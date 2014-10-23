<?php
/**
 * RSSCreator091 is a FeedCreator that implements RSS 0.91 Spec, revision 3.
 *
 * @see http://my.netscape.com/publish/formats/rss-spec-0.91.html
 * @since 1.3
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 */
class RSS091 extends FeedCreator {

	/**
	 * Stores this RSS feed's version number.
	 * @access private
	 */
	var $RSSVersion;

	function RSS091() {
		$this->_setRSSVersion("0.91");
		$this->contentType = "application/rss+xml";
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
	 * @return    string    the feed's complete text
	 */
	function createFeed() {
		$feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
		$feed.= $this->_createGeneratorComment();
		$feed.= $this->_createStylesheetReferences();
		$feed.= "<rss version=\"".$this->RSSVersion."\">\n";
		$feed.= "    <channel>\n";
		$feed.= "        <title>".FeedCreator::iTrunc(FeedCreator::FeedCreator::htmlspecialchars($this->title),100)."</title>\n";
		$this->descriptionTruncSize = 500;
		$feed.= "        <description>".$this->getDescription()."</description>\n";
		$feed.= "        <link>".$this->link."</link>\n";
		$now = new FeedDate();
		$feed.= "        <lastBuildDate>".FeedCreator::htmlspecialchars($now->rfc822())."</lastBuildDate>\n";
		$feed.= "        <generator>".FEEDCREATOR_VERSION."</generator>\n";

		if ($this->image!=null) {
			$feed.= "        <image>\n";
			$feed.= "            <url>".$this->image->url."</url>\n";
			$feed.= "            <title>".FeedCreator::iTrunc(FeedCreator::FeedCreator::htmlspecialchars($this->image->title),100)."</title>\n";
			$feed.= "            <link>".$this->image->link."</link>\n";
			if ($this->image->width!="") {
				$feed.= "            <width>".$this->image->width."</width>\n";
			}
			if ($this->image->height!="") {
				$feed.= "            <height>".$this->image->height."</height>\n";
			}
			if ($this->image->description!="") {
				$feed.= "            <description>".$this->image->getDescription()."</description>\n";
			}
			$feed.= "        </image>\n";
		}
		if ($this->language!="") {
			$feed.= "        <language>".$this->language."</language>\n";
		}
		if ($this->copyright!="") {
			$feed.= "        <copyright>".FeedCreator::iTrunc(FeedCreator::htmlspecialchars($this->copyright),100)."</copyright>\n";
		}
		if (!empty($this->editorEmail) && !empty($this->editor)) {
			$feed.= "        <managingEditor>".FeedCreator::htmlspecialchars($this->editorEmail)." (".FeedCreator::iTrunc(FeedCreator::htmlspecialchars($this->editor),100).")</managingEditor>\n";
		}
		if ($this->webmaster!="") {
			$feed.= "        <webMaster>".FeedCreator::iTrunc(FeedCreator::htmlspecialchars($this->webmaster),100)."</webMaster>\n";
		}
		if ($this->pubDate!="") {
			$pubDate = new FeedDate($this->pubDate);
			$feed.= "        <pubDate>".FeedCreator::htmlspecialchars($pubDate->rfc822())."</pubDate>\n";
		}
		if ($this->category!="") {
			$feed.= "        <category>".FeedCreator::htmlspecialchars($this->category)."</category>\n";
		}
		if ($this->docs!="") {
			$feed.= "        <docs>".FeedCreator::iTrunc(FeedCreator::htmlspecialchars($this->docs),500)."</docs>\n";
		}
		if ($this->ttl!="") {
			$feed.= "        <ttl>".FeedCreator::htmlspecialchars($this->ttl)."</ttl>\n";
		}
		if ($this->rating!="") {
			$feed.= "        <rating>".FeedCreator::iTrunc(FeedCreator::htmlspecialchars($this->rating),500)."</rating>\n";
		}
		if ($this->skipHours!="") {
			$feed.= "        <skipHours>".FeedCreator::htmlspecialchars($this->skipHours)."</skipHours>\n";
		}
		if ($this->skipDays!="") {
			$feed.= "        <skipDays>".FeedCreator::htmlspecialchars($this->skipDays)."</skipDays>\n";
		}
		$feed.= $this->_createAdditionalElements($this->additionalElements, "    ");

		for ($i=0;$i<count($this->items);$i++) {
			$feed.= "        <item>\n";

			if (count($this->items[$i]->enclosures)) {
				foreach($this->items[$i]->enclosures as $enc)
				{
					$feed.= "            <enclosure url=\"" . $enc['url'] . "\" length=\"" . $enc['length'] . "\" type=\"" . $enc['type'] . "\" />\n";
				}
			}

			$feed.= "            <title>".FeedCreator::iTrunc(FeedCreator::htmlspecialchars(strip_tags($this->items[$i]->title)),100)."</title>\n";
			$feed.= "            <link>".FeedCreator::htmlspecialchars($this->items[$i]->link)."</link>\n";
			$feed.= "            <description>".$this->items[$i]->getDescription()."</description>\n";

			if (!empty($this->items[$i]->authorEmail) && !empty($this->items[$i]->author)) {
				$feed.= "            <author>".FeedCreator::htmlspecialchars($this->items[$i]->author)." &lt;".$this->items[$i]->authorEmail."&gt;</author>\n";
			}
			if ($this->items[$i]->category!="") {
				$feed.= "            <category>".FeedCreator::htmlspecialchars($this->items[$i]->category)."</category>\n";
			}
			if ($this->items[$i]->comments!="") {
				$feed.= "            <comments>".FeedCreator::htmlspecialchars($this->items[$i]->comments)."</comments>\n";
			}
			if ($this->items[$i]->date!="") {
			$itemDate = new FeedDate($this->items[$i]->date);
				$feed.= "            <pubDate>".FeedCreator::htmlspecialchars($itemDate->rfc822())."</pubDate>\n";
			}
			if ($this->items[$i]->guid!="") {
				$feed.= "            <guid>".FeedCreator::htmlspecialchars($this->items[$i]->guid)."</guid>\n";
			}
			$feed.= $this->_createAdditionalElements($this->items[$i]->additionalElements, "        ");
			$feed.= "        </item>\n";
		}
		$feed.= "    </channel>\n";
		$feed.= "</rss>\n";
		return $feed;
	}
}
?>