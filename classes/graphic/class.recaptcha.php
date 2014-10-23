<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2009  The Viscacha Project

	Author: Matthias Mohr (et al.)
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

if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

class ReCaptcha {

	var $api_server = 'http://api.recaptcha.net';
	var $api_secure_server = 'https://api-secure.recaptcha.net';
	var $api_verify_server = 'api-verify.recaptcha.net';
	var $api_supported_languages = array('de', 'en', 'fr', 'nl', 'pt', 'ru', 'es', 'tr');
	var $private_key;
	var $public_key;
	var $use_ssl;
	var $error;
	var $extra_params;

	function ReCaptcha() {
		global $config;

		$this->use_ssl = false;
		$this->error = null;
		$this->extra_params = array();

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
	function check () {
		global $gpc;
		$challenge = $gpc->get('recaptcha_challenge_field');
		$response = $gpc->get('recaptcha_response_field');

		//discard spam submissions
		if (empty($challenge) || empty($response)) {
			$this->error = 'incorrect-captcha-sol';
			return CAPTCHA_FAILURE;
		}

		$params =	array (
						'privatekey' => $this->private_key,
						'remoteip' => getip(),
						'challenge' => $challenge,
						'response' => $response
					) + $this->extra_params;

		$response = $this->_recaptcha_http_post ($this->api_verify_server, "/verify", $params);

		$answers = explode ("\n", $response [1]);

		if (trim($answers [0]) == 'true') {
			return CAPTCHA_OK;
		}
		else {
			$this->error = trim($answers[1]);
			if ($this->error == 'incorrect-captcha-sol') {
				return CAPTCHA_MISTAKE;
			}
			else {
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

		if ($this->use_ssl) {
			$server = $this->api_secure_server;
		}
		else {
			$server = $this->api_server;
		}

		$errorpart = "";
		if ($this->error !== null) {
		   $errorpart = "&amp;error=" . $this->error;
		}

		$pubkey = $this->public_key;

		if (in_array($lang->phrase('lang_code'), $this->api_supported_languages)) {
			$language = $lang->phrase('lang_code');
		}
		else {
			$language = 'en';
		}

		$tpl->globalvars(compact("server", "errorpart", "pubkey", "tabindex", "language"));
		return $tpl->parse('main/recaptcha');
	}

	function makeImage() {
		trigger_error('Image creating not supported by reCaptcha.', E_USER_ERROR);
	}

	function setExtraParams($params) {
		$this->extra_params = $params;
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
	function _recaptcha_http_post($host, $path, $data, $port = 80) {
		$req = $this->_recaptcha_qsencode ($data);

		$http_request  = "POST $path HTTP/1.0\r\n";
		$http_request .= "Host: $host\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
		$http_request .= "Content-Length: " . strlen($req) . "\r\n";
		$http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
		$http_request .= "\r\n";
		$http_request .= $req;

		$response = '';
		if(false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
			trigger_error('Could not open socket', E_USER_WARNING);
		}

		fwrite($fs, $http_request);
		while ( !feof($fs) ) {
			$response .= fgets($fs, 1160); // One TCP-IP packet
		}
		fclose($fs);
		$response = explode("\r\n\r\n", $response, 2);

		return $response;
	}

	/**
	 * Encodes the given data into a query string format
	 * @param $data - array of string elements to be encoded
	 * @return string - encoded request
	 */
	function _recaptcha_qsencode ($data) {
		$req = "";
		foreach ($data as $key => $value) {
			$req .= $key . '=' . urlencode( stripslashes($value) ) . '&';
		}
		// Cut the last '&'
		$req=substr($req,0,strlen($req)-1);
		return $req;
	}
}
?>