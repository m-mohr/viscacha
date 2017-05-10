<?php

namespace Viscacha\System\Cron\Jobs;

// ToDo: What is this for?
class DeleteThumbnails implements \Viscacha\System\Cron\JobInterface {

	public function run($lastRunTime) {
		global $filesystem;

		$dir = 'uploads/topics/thumbnails/';
		$d = dir($dir);
		while (false !== ($entry = $d->read())) {
			if ($entry != '.htaccess' && $entry != 'index.htm' && !is_dir($dir . $entry) && file_exists($dir . $entry) && fileatime($dir . $entry) < time() - 60 * 60 * 24 * 7) {
				$filesystem->unlink($dir . $entry);
			}
		}
		$d->close();
		return true;
	}

}