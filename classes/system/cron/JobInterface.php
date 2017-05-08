<?php

namespace Viscacha\System\Cron;

interface JobInterface {
	
	/**
	 * Executes a job.
	 * 
	 * @param int $lastRunTime UNIX timestamp of last run tim of this job
	 * @return boolean
	 */
	public function run($lastRunTime);
	
}
