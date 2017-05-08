<?php

/*
  Viscacha - An advanced bulletin board solution to manage your content easily
  Copyright (C) 2004-2017, Lutana
  http://www.viscacha.org

  Authors: Matthias Mohr et al.
  Publisher: The Viscacha Project, http://www.viscacha.org
  Start Date: May 22, 2004

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

namespace Viscacha\Net\HTTP;

class Response {

	protected $sid;

	public function __construct() {
		ob_start();
		ob_implicit_flush(0);
	}

	protected function addSid($content) {
		if (!empty($this->sid)) {
			$own_url = Sys::isHttps() ? 'https://' : 'http://';
			$own_url = preg_quote($own_url . $_SERVER['HTTP_HOST'], '~');
			$content = preg_replace_callback('~<a([^>]+?)href=("|\')(' . $own_url . '(:\d*)?/?([a-zA-Z0-9\-\.:;_\?\,/\\\+&%\$#\=\~\[\]]*)?|([a-zA-Z0-9\-\._/\~]*)?[\w-]+?\.\w+?(\?[a-zA-Z0-9\-\.:;_\?\,/\\\+&%\$#\=\~\[\]]*)?)("|\')~iu', array(&$this, 'buildurl'), $content);
		}
		return $content;
	}

	protected function buildUrl($matches) {
		list(, $prehref,, $url) = $matches;
		if (mb_substr($url, -1) == '?') {
			$url = mb_substr($url, 0, mb_strlen($url) - 1);
		}
		$info = parse_url($url);
		if (isset($info['query'])) {
			$info['query'] = '?' . $info['query'];
			if (!preg_match('~(\?|&amp;|&)s=[A-Za-z0-9]*?~iu', $info['query'])) {
				$url .= '&amp;s=' . $this->sid;
			}
			// Auskommentieren, wenn alte (oder leere) SIDs nicht ersetzt werden sollen
			else {
				$url = preg_replace('~(\?|&amp;|&)s=([A-Za-z0-9]*)~iu', '\1s=' . $this->sid, $url);
			}
		} else {
			$url .= '?s=' . $this->sid;
		}
		return '<a' . $prehref . 'href="' . $url . '"';
	}

	public function send() {
		global $breadcrumb, $config, $plugins;
		$this->sid = SID2URL;
		$Contents = ob_get_contents();
		ob_end_clean();
		$Contents = $this->addSid($Contents);

		($code = $plugins->load('docout_parse')) ? eval($code) : null;

		print $Contents;
	}

}