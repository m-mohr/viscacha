<?php
/**
+ * KlipFoodCreator is a FeedCreator that implements the KlipFolio Food specification,
+ * as in http://www.serence.com
+ *
+ * @since Viscacha 1.0
+ * @author Matthias Mohr
+ */
class KLIPFOOD extends FeedCreator {

	function createFeed() {
		global $config, $lang;
		$now = new FeedDate();
		$this->descriptionTruncSize = 500;

		$feed = $this->_createGeneratorComment();

		$feed .= '<?xml version="1.0"?>'."\n";
		$feed .= '<klipfood>'."\n";
		for ($i=0;$i<count($this->items);$i++) {
			$feed .= '	<item>'."\n";
			$feed .= '		<title>'.FeedCreator::iTrunc(FeedCreator::htmlspecialchars(strip_tags($this->items[$i]->title)),100).'</title>'."\n";
			$feed .= '		<link>'.FeedCreator::htmlspecialchars($this->items[$i]->link).'</link>'."\n";
			$feed .= '		<note>'.$this->items[$i]->getDescription().'</note>'."\n";
			$feed .= '	</item>'."\n";
		}
		$feed .= '</klipfood>'."\n";

		return $feed;
	}

	function _generateFilename() {
		return $this->dir.strtolower(get_class($this)).".food";
	}
}
?>