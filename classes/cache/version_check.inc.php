<?php
class cache_version_check extends CacheItem {
	function load () {
		global $config;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			if (viscacha_function_exists('xml_parser_create')) {
				$rssnews = get_remote('http://version.viscacha.org/news/rss');
				include('classes/magpie_rss/rss_fetch.inc.php');
				$rss = new MagpieRSS($rssnews);
				$news = '';
				foreach ($rss->items as $item) {
					$news .= '<li><a href="'.htmlspecialchars($item['link']).'" style="font-weight: bold;" target="_blank">'.htmlspecialchars($item['title']).'</a>';
					if (isset($item['description'])) {
						$news .= '<br /><span style="font-size: 0.9em;">'.htmlspecialchars($item['description']).'</span>';
					}
					$news .= '</li>';
				}
				if (!empty($news)) {
					$news = '<ul>'.$news.'</ul>';
				}
			}
			else {
				$news = get_remote('http://version.viscacha.org/news');
			}
			$this->data = array(
				'comp' => get_remote('http://version.viscacha.org/compare/?version='.base64_encode($config['version'])),
				'version' => get_remote('http://version.viscacha.org/version'),
				'news' => $news
			);
			$this->export();
		}
	}
}
?>