<?php

namespace Viscacha\System\Cron\Jobs;

class DeleteTemp implements \Viscacha\System\Cron\JobInterface {

	public function run($lastRunTime) {
		$this->unlinkDir('temp');
		return true;
	}

	protected function isSubdir($dir) {
		if (is_dir($dir) && !preg_match("~\.{1,2}$~u", $dir)) {
			return true;
		} else {
			return false;
		}
	}

	protected function unlinkDir($dir) {
		global $filesystem;
		$dir = $dir . "/";
		$d = dir($dir);
		while (false !== ($entry = $d->read())) {
			if ($this->isSubdir($dir . $entry)) {
				$this->unlinkDir($dir . $entry);
			}
			if ($entry != '.htaccess' && $entry != 'index.htm' && file_exists($dir . $entry) && is_file($dir . $entry) && filemtime($dir . $entry) < time() - 60 * 60) {
				$filesystem->unlink($dir . $entry);
			}
		}
		$d->close();
	}

}
