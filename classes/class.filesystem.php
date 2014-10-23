<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

if (!class_exists('ftp')) {
	$classpath = dirname(__FILE__);
	require_once("{$classpath}/ftp/class.ftp.php");
	$pemftp_class = pemftp_class_module();
	require_once("{$classpath}/ftp/class.ftp_{$pemftp_class}.php");
}

class filesystem {

	var $server;
	var $port;
	var $user;
	var $pw;
	var $ftp;
	var $connected;
	var $installed_path;
	var $root_path;

	function filesystem($server, $user, $pw, $port = 21) {
		$this->server = $server;
		$this->port = $port;
		$this->user = $user;
		$this->pw = $pw;
		$this->installed_path = DIRECTORY_SEPARATOR;
		$this->connected = false;
		$this->root_path = DIRECTORY_SEPARATOR;
	}

	function _ftpize_path($path) {
		$path = preg_replace('~^'.preg_quote($this->root_path).'~i', '', $path);
		return $path;
	}

	function set_wd($ftp_root, $web_root) {
		$this->installed_path = $ftp_root;
		$this->root_path = $web_root.DIRECTORY_SEPARATOR;
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
				$file = $this->_ftpize_path($file);
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
					$file = $this->_ftpize_path($file);
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
            		$file = $this->_ftpize_path($file);
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
				$file = $this->_ftpize_path($file);
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
				$old = $this->_ftpize_path($old);
				$new = $this->_ftpize_path($new);
				$ret = $this->ftp->rename($old, $new);
			}
			if ($ret == false) {
				$ret = $this->copyr($old, $new);
				if ($ret == true) {
					$this->rmdirr($old);
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
				$file = $this->_ftpize_path($file);
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
				$file = $this->_ftpize_path($file);
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

	/*	Delete a file, or a folder and its contents
	*	@author      Aidan Lister <aidan@php.net>
	*	@version     1.0.0
	*	@param       string   $dirname    The directory to delete
	*	@return      bool     Returns true on success, false on failure
	*/
	function rmdirr($dirname) {
		if (!file_exists($dirname)) {
			return false;
		}
		if (is_file($dirname)) {
			return $this->unlink($dirname);
		}
		$dir = dir($dirname);
		while (false !== $entry = $dir->read()) {
			if ($entry == '.' || $entry == '..') {
				continue;
			}
			if (is_dir("$dirname/$entry")) {
				$this->rmdirr("$dirname/$entry");
			}
			else {
				$this->unlink("$dirname/$entry");
			}
		}
		$dir->close();
		return $this->rmdir($dirname);
	}
	/**
	 * Copy a file, or recursively copy a folder and its contents
	 *
	 * @author      Aidan Lister <aidan@php.net>
	 * @version     1.0.1
	 * @link        http://aidanlister.com/repos/v/function.copyr.php
	 * @param       string   $source    Source path
	 * @param       string   $dest      Destination path
	 * @return      bool     Returns TRUE on success, FALSE on failure
	 */
	function copyr($source, $dest) {
	    if (is_file($source)) {
	        return $this->copy($source, $dest);
	    }
	    if (!is_dir($dest)) {
	        if (!$this->mkdir($dest, 0777)) {
	        	return false;
	        }
	    }
	    if (!is_dir($source)) {
	    	return false;
	    }
	    $dir = @dir($source);
	    if (!is_object($dir)) {
	    	return false;
	    }
	    $ret = true;
	    while (false !== $entry = $dir->read()) {
	        if ($entry == '.' || $entry == '..') {
	            continue;
	        }
	        if ($dest !== "{$source}/{$entry}") {
	            $ret2 = $this->copyr("{$source}/{$entry}", "{$dest}/{$entry}");
	            if ($ret2 == false) {
	            	$ret = false;
	            }
	        }
	    }
	    $dir->close();
	    return $ret;
	}

	function mover($source, $dest) {
	    if (!is_dir($dest)) {
	        $this->mkdir($dest, 0777);
	    }
		if ($this->rename($source, $dest)) {
			return true;
		}
		else {
			if ($this->copyr($source, $dest)) {
				$this->rmdirr($source);
				return true;
			}
			return false;
		}
	}

}
?>
