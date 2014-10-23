<?php
class cache_version_check extends CacheItem {

	function cache_version_check ($filename, $cachedir = "cache/") {
		$this->CacheItem($filename, $cachedir);
		$this->max_age = 60*60*24;
	}

	function load () {
		global $config;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			if (viscacha_function_exists('xml_parser_create')) {
				$rssnews = get_remote('http://version.viscacha.org/news/rss/?version='.base64_encode($config['version']));
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

			$comp = get_remote('http://version.viscacha.org/compare/?version='.base64_encode($config['version']));
			if ($comp < 1 && $comp > 3) {
				$comp = 0;
			}

			$current_version = get_remote('http://version.viscacha.org/version');
			if ($current_version == REMOTE_CLIENT_ERROR || $current_version == REMOTE_INVALID_URL) {
				$current_version = null;
			}

			$this->data = array(
				'comp' => $comp,
				'version' => $current_version,
				'news' => $news
			);
			$this->export();
		}
	}
}
?>