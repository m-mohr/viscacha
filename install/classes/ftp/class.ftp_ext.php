<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

class ftp extends ftp_base {

	function __construct($verb = false, $le = false) {
		parent::__construct(false, $verb, $le);
	}

	function _settimeout($sock) {
		if(!ftp_set_option($sock, FTP_TIMEOUT_SEC, $this->_timeout)) {
			$this->PushError('_settimeout','ftp set send timeout');
			$this->_quit();
			return false;
		}
		return true;
	}

	function connect($server = NULL) {
		if(!empty($server)) {
			if(!$this->SetServer($server)) return false;
		}
		if($this->_ready) return true;
		$this->SendMsg('Local OS : '.$this->OS_FullName[$this->OS_local]);
		if(!($this->_ftp_control_sock = $this->_connect($this->_host, $this->_port))) {
			$this->SendMSG("Error : Cannot connect to remote host \"".$this->_fullhost." :".$this->_port."\"");
			return false;
		}
		$this->SendMSG("Connected to remote host \"".$this->_fullhost.":".$this->_port."\". Waiting for greeting.");
		$this->_lastaction = time();
		$this->_ready = true;
		$syst = $this->systype();
		if(!$syst) $this->SendMSG("Can't detect remote OS");
		else {
			if(preg_match("/win|dos|novell/i", $syst[0])) $this->OS_remote = FTP_OS_Windows;
			elseif(preg_match("/os/i", $syst[0])) $this->OS_remote = FTP_OS_Mac;
			elseif(preg_match("/(li|u)nix/i", $syst[0])) $this->OS_remote = FTP_OS_Unix;
			else $this->OS_remote = FTP_OS_Mac;
			$this->SendMSG("Remote OS: ".$this->OS_FullName[$this->OS_remote]);
		}
		if(!$this->features()) $this->SendMSG("Can't get features list. All supported - disabled");
		else $this->SendMSG("Supported features: ".implode(", ", array_keys($this->_features)));
		return true;
	}

	function _connect($host, $port) {
		$this->SendMSG("Creating ftp connection");
		$sock = ftp_connect(convert_host_to_idna($host), $port, $this->_timeout);
		if (!$sock) {
			$this->PushError('_connect','ftp connect failed');
			return false;
		}
		$this->_connected = true;
		return $sock;
	}

	function get($remotefile, $localfile=NULL, $rest=0) {
		if(!$this->_ready) {
			$this->PushError('get', 'Connect first');
			return false;
		}

		if(is_null($localfile)) $localfile = $remotefile;
		if (@file_exists($localfile)) $this->SendMSG("Warning: local file will be overwritten");

		$pi = pathinfo($remotefile);
		if($this->_type == FTP_ASCII || ($this->_type == FTP_AUTOASCII && in_array(strtoupper($pi["extension"]), $this->AutoAsciiExt))) $mode = FTP_ASCII;
		else $mode = FTP_BINARY;

		if(!$this->_can_restore) $rest = 0;
		$status = ftp_get($this->_ftp_control_sock, $localfile, $remotefile, $mode, $rest);
		if ($status == true) {
			return @file_get_contents($localfile);
		}
		else {
			return false;
		}
	}

	function put($localfile, $remotefile = NULL, $rest=0) {
		if(!$this->_ready) {
			$this->PushError('put', 'Connect first');
			return false;
		}

		if (!@file_exists($localfile) && !is_resource($localfile)) {
			$this->PushError("put","can't open local file", "No such file or directory \"{$localfile}\"");
			return false;
		}
		if (is_resource($localfile)) {
			$fp = $localfile;
			$localfile = $remotefile;
			if (!is_string($remotefile)) {
				$this->PushError("put","second paramater is not a string", "String needed, when first parameter is resource.");
			}
		}
		else {
			if (is_null($remotefile)) {
				$remotefile = $localfile;
			}
			$fp = @fopen($localfile, "r");
			if (!$fp) {
				$this->PushError("put","can't open local file", "Cannot read file \"{$localfile}\"");
				return false;
			}
		}

		$pi = pathinfo($localfile);
		if (empty($pi["extension"])) {
			$pi["extension"] = '';
		}
		if($this->_type == FTP_ASCII || ($this->_type == FTP_AUTOASCII && in_array(strtoupper($pi["extension"]), $this->AutoAsciiExt))) $mode = FTP_ASCII;
		else $mode = FTP_BINARY;

		if(!$this->_can_restore) $rest = 0;
		if($rest > 0) fseek($fp, $rest);

		$status = ftp_fput($this->_ftp_control_sock, $remotefile, $fp, $mode, $rest);
		fclose($fp);

		return $status;
	}

	function _list($arg="", $cmd="LIST", $fnction="_list") {
		if(!$this->_ready) {
			$this->PushError('_list', 'Connect first');
			return false;
		}

		if ($cmd == 'NLST') {
			$contents = ftp_nlist($this->_ftp_control_sock, $arg);
		}
		else {
			$contents = ftp_rawlist($this->_ftp_control_sock, $arg);
		}

		return $contents;
	}

	function _readmsg($fnction="_readmsg"){
		if(!$this->_connected) {
			$this->PushError($fnction, 'Connect first');
			return false;
		}
		if (!is_array($this->_ftp_data_sock)) {
			$this->PushError($fnction, 'No data retrieved');
			return false;
		}
		$result = true;
		$this->_message = implode(CRLF, $this->_ftp_data_sock).CRLF;
		$this->_code = 0;
		if(!preg_match("/^([0-9]{3})(-(.*[".CRLF."]{1,2})+\\1)? [^".CRLF."]+[".CRLF."]{1,2}$/m", $this->_message, $regs)) {
			$this->PushError($fnction, 'Invalid response from FTP');
			return false;
		}
		if($this->LocalEcho) echo "GET < ".rtrim($this->_message, CRLF).CRLF;
		$this->_code = (int)$regs[1];
		return $result;
	}

	function _exec($cmd, $fnction="_exec") {
		if(!$this->_ready) {
			$this->PushError($fnction,'Connect first');
			return false;
		}
		if($this->LocalEcho) echo "PUT > ",$cmd,CRLF;
		$this->_ftp_data_sock = ftp_raw($this->_ftp_control_sock, $cmd);
		$this->_lastaction = time();
		if(!$this->_readmsg($fnction)) return false;
		return true;
	}

	function _quit($force = FALSE) {
		if($this->_connected || $force) {
			ftp_close($this->_ftp_control_sock);
			$this->_connected=false;
			$this->SendMSG("FTP closed");
		}
	}
}
?>