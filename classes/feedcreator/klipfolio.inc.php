<?php
/**
  * KlipCreator is a FeedCreator that implements the KlipFolio specification,
  * as in http://www.serence.com
  *
  * @author Matthias Mohr
  */
class KLIPFOLIO extends FeedCreator {

	function createFeed() {
		global $config;
		$now = new FeedDate();
		$this->descriptionTruncSize = 500;

		$feed = '<?xml version="1.0" encoding="'.$this->encoding.'"?>'."\n";
		$feed .= $this->_createGeneratorComment();
		$feed .= '<klip>'."\n";

		$feed .= '	<owner>'."\n";
		$feed .= '		<author>'.$this->htmlspecialchars(FeedCreator::iTrunc($this->editor,100)).'</author>'."\n";
		$feed .= '		<copyright>'.$this->htmlspecialchars(FeedCreator::iTrunc($this->copyright,100)).'</copyright>'."\n";
		if (!empty($this->editorEmail)) {
			$feed .= '		<email>'.$this->htmlspecialchars($this->editorEmail).'</email>'."\n";
		}
		$feed .= '		<web>'.$this->link.'</web>'."\n";
		$feed .= '	</owner>'."\n";

		$feed .= '	<identity>'."\n";
		$feed .= '		<title>'.$this->htmlspecialchars(FeedCreator::iTrunc($this->title,100)).'</title>'."\n";
		$feed .= '		<uniqueid>'.md5($config['cryptkey']).'</uniqueid>'."\n";
		$feed .= '		<version>1.0</version>'."\n";
		$feed .= '		<lastmodified>'.$this->htmlspecialchars($now->v0001()).'</lastmodified>'."\n";
		$feed .= '		<description>'.$this->getDescription($this->encoding).'</description>'."\n";
		$feed .= '		<keywords>Viscacha '.$this->htmlspecialchars($this->title).'</keywords>'."\n";
		$feed .= '	</identity>'."\n";

		$feed .= '	<locations>'."\n";
		$feed .= '		<defaultlink>'.$this->link.'</defaultlink>'."\n";
		$feed .= '		<contentsource>'.$config['furl'].'/external.php?action=KLIPFOOD</contentsource>'."\n";
		$feed .= '		<icon>'.$config['furl'].'/'.$config['syndication_klipfolio_icon'].'</icon>'."\n";
		$feed .= '		<banner>'.$config['furl'].'/'.$config['syndication_klipfolio_banner'].'</banner>'."\n";
		$feed .= '		<help></help>'."\n";
		$feed .= '		<kliplocation>'.$config['furl'].'/external.php?action=KLIPFOLIO</kliplocation>'."\n";
		$feed .= '	</locations>'."\n";

		$feed .= '	<setup>'."\n";
		$feed .= '		<refresh>'.$this->htmlspecialchars($this->ttl).'</refresh>'."\n";
		if (!check_hp($_SERVER['HTTP_REFERER'])) {
			$_SERVER['HTTP_REFERER'] = $this->link;
		}
		$feed .= '		<referer>'.$this->htmlspecialchars($_SERVER['HTTP_REFERER']).'</referer>'."\n";
		$feed .= '		<country>'.$this->language.'</country>'."\n";
		$feed .= '		<language>'.$this->language.'</language>'."\n";
		$feed .= '	</setup>'."\n";

		$feed .= '	<messages>'."\n";
		$feed .= '		<loading>Getting data...</loading>'."\n";
		$feed .= '		<nodata>No items to display.</nodata>'."\n";
		$feed .= '	</messages>'."\n";

		$feed .= "</klip>\n";
		return $feed;
	}

	function _generateFilename() {
		return $this->dir.strtolower(get_class($this)).".klip";
	}
}
?>
