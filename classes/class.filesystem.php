<?php
if (!class_exists('ftp')) {
	require_once(realpath(dirname(__FILE__))."/ftp/class.ftp.php");
	require_once(realpath(dirname(__FILE__))."/ftp/class.ftp_".pemftp_class_module().".php");
}

class filesystem {

	var $server;
	var $port;
	var $user;
	var $pw;
	var $ftp;
	var $connected;
	var $installed_path;

	function filesystem($server, $user, $pw, $port = 21) {
		$this->server = $server;
		$this->port = $port;
		$this->user = $user;
		$this->pw = $pw;
		$this->installed_path = '/';
		$this->connected = false;
	}
	
	function set_wd($path) {
		$this->installed_path = $path;
	}
	
	function init() {
		if ($this->connected) {
			return true;
		}
		elseif (!empty($this->server)) {
			$this->ftp = new ftp(false, false);
			if(!$this->ftp->SetServer($this->server, $this->port)) {
				$this->ftp->quit();
				return false;
			}
			if (!$this->ftp->connect()) {
				return false;
			}
			if (!$this->ftp->login($this->user, $this->pw)) {
				$this->ftp->quit();
				return false;
			}
			$this->ftp->SetType(FTP_AUTOASCII);
			$this->ftp->Passive(FALSE);
			$this->ftp->chdir($this->installed_path);
			
			$this->connected = true;
			return true;
		}
		else {
			return false;
		}
	}
	
	function unlink($file) {
		if (!@unlink($file)) {
			if ($this->init()) {
				return $this->ftp->delete($file);
			}
			else {
				return false;
			}
		}
		else {
			return true;
		}
	}
	
	function file_put_contents($file, $data) {
		return @file_put_contents($file, $data);
	}
	
	function copy($src, $dest) {
		return @copy($src, $dest);
	}
	
	function mkdir($file, $chmod = 0755) {
		if (!@mkdir($file, $chmod)) {
			if ($this->init()) {
				$success = $this->ftp->mkdir($file);
				$this->ftp->chmod($file, $chmod);
				return $success;
			}
			else {
				return false;
			}
		}
		else {
			return true;
		}
	}
	
	function rename($old, $new) {
		if (!@rename($old, $new)) {
			if ($this->init()) {
				return $this->ftp->rename($old, $new);
			}
			else {
				return false;
			}
		}
		else {
			return true;
		}
	}
	
	function chmod($file, $chmod) {
		if (!@chmod($file, $chmod)) {
			if ($this->init()) {
				return $this->ftp->chmod($file, $chmod);
			}
			else {
				return false;
			}
		}
		else {
			return true;
		}
	}
	
	function rmdir($file) {
		if (!@rmdir($file)) {
			if ($this->init()) {
				return $this->ftp->rmdir($file);
			}
			else {
				return false;
			}
		}
		else {
			return true;
		}
	}

}
?>