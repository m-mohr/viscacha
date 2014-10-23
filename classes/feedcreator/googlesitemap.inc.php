<?php
/** 
* GoogleSM is a FeedCreator that implements Google Sitemap 0.84. 
* 
* @see https://www.google.com/webmasters/sitemaps/docs/en/protocol.html 
*/ 
class GOOGLESITEMAP extends FeedCreator { 

function GOOGLESITEMAP() {
	$this->encoding = "utf-8";
}

/** 
* Builds the Google Sitemap feed's text. 
* The feed will contain all items previously added in the same order. 
* @return string the feed's complete text 
*/ 
function createFeed() { 
	$feed = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; 
	$feed.= "<urlset xmlns=\"http://www.google.com/schemas/sitemap/0.84\"\n"; 
	$feed.= "		 xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\n"; 
	$feed.= "		 xsi:schemaLocation=\"http://www.google.com/schemas/sitemap/0.84\n"; 
	$feed.= "		 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd\">\n"; 
  
	$ci = count($this->items);
	for ($i=0;$i<$ci;$i++) { 
		$feed.= "  <url>\n"; 
		$feed.= "	 <loc>".htmlspecialchars($this->items[$i]->link)."</loc>\n"; 
		if (!empty($this->items[$i]->date)) { 
	  		$itemDate = new FeedDate($this->items[$i]->date); 
	  		$feed.= "	 <lastmod>".htmlentities($itemDate->iso8601())."</lastmod>\n"; 
   		} 
		if (!empty($this->items[$i]->priority)) { 
		  $feed.= "	   <priority>".$this->items[$i]->priority."</priority>\n"; 
		} 
		if (!empty($this->items[$i]->changefreq)) { 
		  $feed.= "	   <changefreq>".$this->items[$i]->changefreq."</changefreq>\n"; 
		} 
		$feed.= "  </url>\n"; 
  	} 	
	$feed.= "</urlset>\n"; 
	return $feed; 
} 
} 
?>
