<?php
/**
  * KlipFoodCreator is a FeedCreator that implements the KlipFolio Food specification,
  * as in http://www.serence.com
  *
  * @author Matthias Mohr
  */
class KLIPFOOD extends FeedCreator {

	function createFeed() {
		$now = new FeedDate();
		$this->descriptionTruncSize = 500;

		$feed = '<?xml version="1.0" encoding="'.$this->encoding.'"?>'."\n";
		$feed .= $this->_createGeneratorComment();
		$feed .= '<klipfood>'."\n";
		for ($i=0;$i<count($this->items);$i++) {
			$feed .= '	<item>'."\n";
			$feed .= '		<title>'.$this->htmlspecialchars(FeedCreator::iTrunc($this->items[$i]->title,100)).'</title>'."\n";
			$feed .= '		<link>'.$this->htmlspecialchars($this->items[$i]->link).'</link>'."\n";
			$feed .= '		<note>'.$this->items[$i]->getDescription($this->encoding).'</note>'."\n";
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