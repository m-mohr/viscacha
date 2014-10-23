<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

class breadcrumb {

    var $content = array();
    var $cache = array();

    function breadcrumb() {
    }

    function Add($title, $url = NULL) {
		global $config;
		$title = html_entity_decode($title, ENT_QUOTES, $config['asia_charset']);
		$title = str_replace ("'", '&#039;', $title );
		$title = str_replace ('"', '&quot;', $title );
		$title = str_replace ('<', '&lt;', $title );
		$title = str_replace ( '>', '&gt;', $title );
    	$this->content[] = array(
    	    'title' => $title,
    	    'url' => $url
    	);
    }

    function AddUrl($url) {
		$last = array_pop($this->content);
    	$this->content[] = array(
    	    'title' => $last['title'],
    	    'url' => $url
    	);
    }

    function ResetUrl() {
		$last = array_pop($this->content);
    	$this->content[] = array(
    	    'title' => $last['title'],
    	    'url' => NULL
    	);
    }

    function OutputHTML($seperator = ' > ') {
        $cache = array();
        foreach ($this->content as $key => $row) {
            if (!empty($row['url'])) {
                $cache[$key] = '<a href="'.$row['url'].'">'.$row['title'].'</a>';
            }
            else {
                $cache[$key] = $row['title'];
            }
        }
        return implode($seperator, $cache);
    }

    function OutputPLAIN($seperator = ' > ') {
        $cache = array();
        foreach ($this->content as $key => $row) {
            $cache[$key] = strip_tags($row['title']);
        }
        return implode($seperator, $cache);
    }

    function OutputPRINT($seperator = ' > ') {
    	global $config;
        $cache = array();
        foreach ($this->content as $key => $row) {
        	if (!empty($row['url'])) {
            	$cache[$key] = "{$row['title']} (<a href=\"{$config['furl']}/{$row['url']}\">{$config['furl']}/{$row['url']}</a>)";
            }
            else {
            	$cache[$key] = $row['title'];
            }
        }
        return implode($seperator, $cache);
    }

    function getArray() {
        return $content;
    }
}
?>
