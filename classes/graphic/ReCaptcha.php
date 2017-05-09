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

namespace Viscacha\Graphic;

class ReCaptcha {

	var $api_supported_languages = array('ar', 'af', 'am', 'hy', 'az', 'eu', 'bn', 'bg', 'ca', 'zh-HK', 'zh-CN', 'zh-TW', 'hr', 'cs', 'da', 'nl', 'en-GB', 'en', 'et', 'fil', 'fi', 'fr', 'fr-CA', 'gl', 'ka', 'de', 'de-AT', 'de-CH', 'el', 'gu', 'iw', 'hi', 'hu', 'is', 'id', 'it', 'ja', 'kn', 'ko', 'lo', 'lv', 'lt', 'ms', 'ml', 'mr', 'mn', 'no', 'fa', 'pl', 'pt', 'pt-BR', 'pt-PT', 'ro', 'ru', 'sr', 'si', 'sk', 'sl', 'es', 'es-419', 'sw', 'sv', 'ta', 'te', 'th', 'tr', 'uk', 'ur', 'vi', 'zu');
	var $private_key;
	var $public_key;
	var $error;
	var $extra_params;

	function __construct() {
		global $config;

		$this->error = null;
		$this->private_key = $config['botgfxtest_recaptcha_private'];
		$this->public_key = $config['botgfxtest_recaptcha_public'];
		if (empty($this->private_key) || empty($this->public_key)) {
			trigger_error('You have to specify public and private keys for reCaptcha.', E_USER_WARNING);
		}
	}

	/**
	 * Calls an HTTP POST function to verify if the user's guess was correct
	 * @param string $challenge
	 * @param string $response
	 * @return ReCaptchaResponse
	 */
	function check() {
		global $gpc;
		$response = $gpc->get('g-recaptcha-response');

		//discard spam submissions
		if (empty($response)) {
			$this->error = 'incorrect-captcha-sol';
			return CAPTCHA_FAILURE;
		}

		$params = array(
			'secret' => $this->private_key,
			'remoteip' => getip(),
			'response' => $response
		);
		$body = $this->_recaptcha_http_post("www.google.com", "/recaptcha/api/siteverify", $params);
		if ($body == null) {
			return CAPTCHA_FAILURE;
		}
		$json = json_decode($body, true);
		
		if (isset($json['success']) && $json['success'] == true) {
			return CAPTCHA_OK;
		} else {
			if (isset($json['error-codes']) && is_array($json['error-codes'])) {
				$this->error = trim($json['error-codes']);
				
			}
			if ($this->error == 'incorrect-captcha-sol' || $this->error == 'missing-input-response') {
				return CAPTCHA_MISTAKE;
			} else {
				return CAPTCHA_FAILURE;
			}
		}
	}

	/**
	 * Gets the challenge HTML (javascript and non-javascript version).
	 * This is called from the browser, and the resulting reCAPTCHA HTML widget
	 * is embedded within the HTML form it was called from.

	 * @return string - The HTML to be embedded in the user's form.
	 * @todo Implement custom code (with own language engine)
	 */
	function generateCode($tabindex = 0) {
		global $tpl, $lang;

		$pubkey = $this->public_key;
		$language = '';
		if (in_array($lang->phrase('lang_code'), $this->api_supported_languages)) {
			$language = $lang->phrase('lang_code');
		}

		$tpl->assignVars(compact("pubkey", "tabindex", "language"));
		return $tpl->parse('main/recaptcha');
	}

	function makeImage() {
		trigger_error('Not supported.', E_USER_ERROR);
	}

	function getError() {
		return $this->error;
	}

	/**
	 * Submits an HTTP POST to a reCAPTCHA server
	 * @param string $host
	 * @param string $path
	 * @param array $data
	 * @param int port
	 * @return array response
	 */
	function _recaptcha_http_post($host, $path, $data, $port = 443) {
		$req = http_build_query($data, '', '&');

		$http_request = "POST $path HTTP/1.1\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$http_request .= "Content-Length: " . strlen($req) . "\r\n";
		$http_request .= "Connection: close\r\n\r\n";
		$http_request .= $req;
		$http_request .= "\r\n\r\n";

		if ($port == 443) {
			$host = "ssl://{$host}";
		}

		if (false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) )) {
			trigger_error('Could not open socket', E_USER_WARNING);
			return null;
		}
		fwrite($fs, $http_request);
		$response = '';
		while (!feof($fs)) {
			$response .= fgets($fs, 4096);
		}
		fclose($fs);
		
        if (0 !== \Str::indexOf($response, 'HTTP/1.1 200 OK')) {
            return null;
        }

        $parts = preg_split("#\n\s*\n#Uisu", $response);

		return $parts[1];
	}

}

?>