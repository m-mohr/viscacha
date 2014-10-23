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
		$this->chmod($file, 0666);

		if (@file_put_contents($file, $data) === false) {
			$ret = false;
			$fp = @tmpfile();
			if (is_resource($fp) == true) {
				if ($this->init()) {
					fwrite($fp, $data);
					fseek($fp, 0);
					$this->chmod(dirname($file), 0777);
					$ret = $this->ftp->put($fp, $file);
				}
				else {
					fclose($fp);
				}
			}
			if ($ret === false) {
				trigger_error("filesystem::file_put_contents({$file}): failed to open stream: Permission denied", E_USER_WARNING);
			}
			return $ret;
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
		$ret = @copy($src, $dest);
		if ($ret == false) {
            $fp = @fopen($file, "r");
            if (is_resource($fp)) {
            	if ($this->init()) {
					$this->chmod(dirname($file), 0777);
            		$ret = $this->ftp->put($fp, str_replace(' ', '_', $dest));
            	}
            	fclose($fp);
            }
		}
		if ($ret == false) {
			trigger_error("filesystem::copy({$src}, {$dest}): failed to open stream: Permission denied", E_USER_WARNING);
		}
		return $ret;
	}

	function mkdir($file, $chmod = 0755) {
		if (file_exists($file)) {
			if ($this->init()) {
				$this->ftp->chmod($file, $chmod);
			}
		}
		$base = dirname($file);
		if (!@is_dir($base)) {
			if (!$this->mkdir($base, $chmod)) {
				return false;
			}
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
			$this->chmod($file, $chmod);
			return true;
		}
	}

	function rename($old, $new) {
		if (!file_exists($old)) {
			return false;
		}
		if (!@rename($old, $new)) {
			$ret = false;
			if ($this->init()) {
				$ret = $this->ftp->rename($old, $new);
			}
			if ($ret == false) {
				$ret = copyr($old, $new);
				if ($ret == true) {
					rmdirr($old);
				}
				return $ret;
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
