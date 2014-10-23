<?php
/**
+ * KlipCreator is a FeedCreator that implements the KlipFolio specification,
+ * as in http://www.serence.com
+ *
+ * @since Viscacha 1.0
+ * @author Matthias Mohr
+ */
class KLIPFOLIO extends FeedCreator {
	
	function createFeed() {
		global $config, $lang;
		$now = new FeedDate();
		$this->descriptionTruncSize = 500;

		$feed = $this->_createGeneratorComment();
		
		$feed .= '<klip>'."\n";
		
		$feed .= '	<owner>'."\n";
		$feed .= '		<author>'.FeedCreator::iTrunc(htmlspecialchars($this->editor),100).'</author>'."\n";
		$feed .= '		<copyright>'.FeedCreator::iTrunc(htmlspecialchars($this->copyright),100).'</copyright>'."\n";
		$feed .= '		<email>'.$this->editorEmail.'</email>'."\n";
		$feed .= '		<web>'.$this->link.'</web>'."\n";
		$feed .= '	</owner>'."\n";
		
		$feed .= '	<identity>'."\n";
		$feed .= '		<title>'.FeedCreator::iTrunc(htmlspecialchars($this->title),100).'</title>'."\n";
		$feed .= '		<uniqueid>'.md5($config['cryptkey']).'</uniqueid>'."\n";
		$feed .= '		<version>1.0</version>'."\n";
		$feed .= '		<lastmodified>'.htmlspecialchars($now->v0001()).'</lastmodified>'."\n";
		$feed .= '		<description>'.$this->getDescription().'</description>'."\n";
		$feed .= '		<keywords>Viscacha '.htmlspecialchars($this->title).'</keywords>'."\n";
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
		$feed .= '		<refresh>'.htmlspecialchars($this->ttl).'</refresh>'."\n";
		if (empty($_SERVER['HTTP_REFERER'])) {
			$_SERVER['HTTP_REFERER'] = $this->link;
		}
		$feed .= '		<referer>'.$_SERVER['HTTP_REFERER'].'</referer>'."\n";
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
