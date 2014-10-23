<?php

if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "function.jabber.php") die('Error: Hacking Attempt');   // MOD: Viscacha compliance

require("class.jabber.php"); 

class Viscacha_Jabber {
	var $j;
	
	// Constructor
	function Viscacha_Jabber () {
		global $config;
	
		$this->j = new Jabber();

		// Server and Port
		if (strpos($config['jabber_server'], ':') !== FALSE) {
		    list($this->j->server, $this->j->port) = explode(':', $config['jabber_server']);
		}
		else {
		    $this->j->port = 5222;
		    $this->j->server = $config['jabber_server'];
		}
		
		// Usernama and Password
		$this->j->username = $config['jabber_user'];
		$this->j->password = $config['jabber_pass'];
		
		// General technical Settings
		$this->j->resource = 'Viscacha';
		$this->j->charset = 'UTF-8';
		
		$this->j->log_filename = 'temp/jabber.inc.php';
		
		if ($config['benchmarkresult']) {
			$this->logging(true);
		}
		else {
			$this->logging(false);
		}
	
	}
	
	// connect to jabber
	function connect () {
		if (!$this->j->Connect()) {
			return 'Cannot connect to Jabber';
		}
		if (!$this->j->SendAuth()) {
			return 'Cannot auth to Jabber';
		}
		$this->j->SendPresence("online");
		return TRUE;
	}
	
	// disconnect from jabber
	function disconnect () {

		$this->j->Disconnect();

	}
	
	// FALSE = logging off, TRUE = logging on
	function logging ($status = FALSE) {
		$this->j->enable_logging = $status;
	}


	// to = array: array(to, subject, body) with all messages to be sent
	// to = string: single receipient's JID
	// subject = single subject
	// body = single message body. pass these as text/plain, the'll be htmlspecialchar'ed.
	function send_message($to, $body = '') {
		if (is_array($to)) {
			foreach ($to as $msg) {
				if ($this->j->charset == 'UTF-8') {
					$msg['body'] = @utf8_encode($msg['body']);
				}
	            $this->j->Subscribe($msg['to']);
				if (!$this->j->SendMessage($msg['to'], null, null, array('body' => $msg['body']))) {
					return 'Cannot send message';
				}
			}
		}
		else {
			if ($this->j->charset == 'UTF-8') {
				$body = @utf8_encode($body);
			}
			if (!$this->j->SendMessage($to, 'normal', null, array('body' => $body))) {
				return 'Cannot send message';
			}
		}
	
		return TRUE;
	}
		
}
?>
