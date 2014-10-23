<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

if (!class_exists('ftp')) {
	if (is_dir("classes/ftp/")) {
		$path = 'classes';
	}
	else {
		$path = realpath(dirname(__FILE__));
	}
	require_once("{$path}/ftp/class.ftp.php");
	$pemftp_class = pemftp_class_module();
	require_once("{$path}/ftp/class.ftp_{$pemftp_class}.php");
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
		$this->installed_path = DIRECTORY_SEPARATOR;
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
			$this->ftp->Passive(false);

			if (!$this->ftp->chdir($this->installed_path)) {
				$this->ftp->chdir(DIRECTORY_SEPARATOR);
				trigger_error('Could not change working directory for FTP connection.', E_WARNING);
			}

			$this->connected = true;
			return true;
		}
		else {
			return false;
		}
	}

	function unlink($file) {
		if (!file_exists($file)) {
			return false;
		}
		if (@unlink($file) == false) {
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
		if (is_array($data)) {
			$data = implode("\n", $data);
		}
		if (file_put_contents($file, $data) == false) {
			if ($this->init()) {
				$fp = tmpfile();
				if (!is_resource($fp)) {
					return false;
				}
				fwrite($fp, $data);
				fseek($fp, 0);
				if ($this->ftp->put($fp, $file) == false) {
					return false;
				}
				if (is_resource($fp)) {
					fclose($fp);
				}
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return true;
		}
	}

	function copy($src, $dest) {
		if (!file_exists($src)) {
			return false;
		}
		$dir = dirname($dest);
		while(!file_exists($dir)) {
			$this->mkdir($dir, 0777);
			$dir = dirname($dir);
		}
		return copy($src, $dest);
	}

	function mkdir($file, $chmod = 0755) {
		if (file_exists($file)) {
			$this->ftp->chmod($file, $chmod);
		}
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
		if (!file_exists($old)) {
			return false;
		}
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
		if (!file_exists($file)) {
			return false;
		}
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
		if (!file_exists($file)) {
			return false;
		}
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
