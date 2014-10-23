require_once('classes/magpie_rss/rss_fetch.inc.php');
$feed = $config['module_'.$pluginid]['feed'];
$title = $config['module_'.$pluginid]['title'];

if (isset($scache)) {
	$grabrss = $scache->load('grabrss');
	$grabrss_cache = $grabrss->get();
}
if (isset($grabrss_cache[$feed])) {
	$thisfeed = &$grabrss_cache[$feed];
}
if (isset($thisfeed)) {
	$rss = fetch_rss($thisfeed['file']);
	if ($rss) {
		$id = &$thisfeed['id'];
		
		if ($thisfeed['entries'] > 0 && $thisfeed['entries'] < 15) {
			$items = array_slice($rss->items, 0, $thisfeed['entries']);
		}
		else {
			$items = &$rss->items;
		}
		if (count($items) > 1) {
			$slide = 1;
			$item = array('link' => '','title' => '');
		}
		else {
			$slide = 0;
			if (count($items) == 0) {
				$item = array('link' => 'javascript:return;','title' => '---');
			}
			else {
				if (isset($items[0]['description'])) {
					$item = array('link' => htmlspecialchars($items[0]['link']),'title' => '<b>'.htmlentities($items[0]['title']).'</b><br \>'.htmlentities($items[0]['description']));
				}
				else {
					$item = array('link' => htmlspecialchars($items[0]['link']),'title' => htmlentities($items[0]['title']));
				}
			}
		}
		foreach ($items as $key => $crow) {
			$crow['title'] = htmlentities($crow['title'], ENT_QUOTES);
			if (isset($crow['description'])) {
				$crow['description'] = htmlentities($crow['description'], ENT_QUOTES);
			}
			$items[$key] = $crow;
		}
		$a = 0;
		$tpl->globalvars(compact("a", "items", "feed", "slide", "title", "pluginid", "item"));
		echo $tpl->parse("modules/{$pluginid}/grabrss");
		if (!defined('newsbox_js_loaded')) {
			define('newsbox_js_loaded', true);
		}
	}
	else {
		echo "<!-- Could not load feed! ID: {$thisfeed['id']}, URL: {$thisfeed['file']} -->";
	}
}
