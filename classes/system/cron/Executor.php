<?php

/**
 * Cron Parser
 * 
 * @licence GPL
 * @author Nikol S <ns@eyo.com.au>
 * @author Matthias Mohr
 * @version 1.01 (12 Sep 2005)
 */

namespace Viscacha\System\Cron;

class Executor {

	const PC_MINUTE = 1;
	const PC_HOUR = 2;
	const PC_DOM = 3;
	const PC_MONTH = 4;
	const PC_DOW = 5;
	const PC_CMD = 7;
	const PC_COMMENT = 8;
	const PC_ARGS = 19;
	const PC_CRONLINE = 20;

	private $path;
	private $writeDir;
	private $useLog;
	private $sendLog;
	private $sendLogMailAddr;
	private $resultsSummary;

	public function __construct() {
		$this->path = 'classes/cron/jobs/';
		$this->writeDir = 'data/cron/';
		$this->resultsSummary = '';
		$this->useLog = false;
		$this->sendLog = false;
		$this->sendLogMailAddr = null;
	}
	
	public function setClassPath($path) {
		$this->path = $path;
	}
	
	public function setLogPath($path) {
		$this->writeDir = $path;
	}
	
	public function enableFileLogging($enable) {
		$this->useLog = $enable;
	}
	
	public function enableMailLogging($enable, $mail) {
		$this->sendLog = $enable;
		$this->sendLogMailAddr = $mail;
	}

	protected function logMessage($msg) {
		if ($msg[mb_strlen($msg) - 1] != "\n") {
			$msg .= "\n";
		}
		$this->resultsSummary .= $msg;
		if ($this->useLog) {
			$logfile = $this->writeDir . "cron.log";
			$file = fopen($logfile, "a");
			fputs($file, date("r") . "  " . $msg);
			fclose($file);
		}
	}

	protected function multisort(&$array, $sortby, $order = 'asc') {
		foreach ($array as $val) {
			$sortarray[] = $val[$sortby];
		}
		$c = $array;
		$const = $order == 'asc' ? SORT_ASC : SORT_DESC;
		$s = array_multisort($sortarray, $const, $c, $const);
		$array = $c;
		return $s;
	}

	protected function getLastScheduledRunTime($job) {
		$cron_string = $job[self::PC_MINUTE] . ' ' . $job[self::PC_HOUR] . ' ' . $job[self::PC_DOM] . ' ' . $job[self::PC_MONTH] . ' ' . $job[self::PC_DOW];
		$cronPars = new Parser();
		$cronPars->calcLastRan($cron_string);
		return $cronPars->getLastRanUnix();
	}

	protected function getJobFileName($job) {
		$jobArgHash = ( count($job[self::PC_ARGS]) > 1 ? '_' . md5(implode('', $job[self::PC_ARGS])) : '' );
		$jobfile = $this->writeDir . urlencode($job[self::PC_CMD]) . $jobArgHash . ".job";
		return $jobfile;
	}

	protected function getLastActualRunTime($job) {
		$jobfile = $this->getJobFileName($job);
		if (file_exists($jobfile)) {
			return filemtime($jobfile);
		}
		return 0;
	}

	protected function getJobTempData($job) {
		$jobfile = $this->getJobFileName($job);
		if (file_exists($jobfile)) {
			$data = file_get_contents($jobfile);
		} else {
			$data = '';
		}
		return $data;
	}

	protected function markLastRun($job, $lastRun, $data = '') {
		global $filesystem;
		$jobfile = $this->getJobFileName($job);
		$filesystem->file_put_contents($jobfile, $data);
		touch($jobfile);
	}

	protected function runJob($job) {
		$lastActual = $job["lastActual"];
		$lastScheduled = $job["lastScheduled"];

		if ($lastScheduled > $lastActual) {
			$this->logMessage("Running\t" . $job[self::PC_CRONLINE]);
			$this->logMessage("  Last run:\t" . date("r", $lastActual));
			$this->logMessage("  Last scheduled:\t" . date("r", $lastScheduled));
			$argv = $job[self::PC_ARGS];

			$jobData = $this->getJobTempData($job);
			$jobPath = $this->path . $job[self::PC_CMD];

			$startTime = microtime(true);
			ob_start();

			$success = false;
			if (file_exists($jobPath)) {
				include($jobPath);
				$name = Sys::getClassNameFromFile($jobPath);
				if ($name !== null) {
					$jobObject = new $name();
					$success = $jobObject->run($jobData);
				}
			}

			$return = trim(ob_get_contents());
			ob_end_clean();
			$seconds = round((microtime(true) - $startTime), 5);

			if (!$success) {
				$this->logMessage("  Script failed");
			}
			if (!empty($return)) {
				$this->logMessage("  Script returned:\t" . str_replace(array("\r\n","\n","\r","\t","\0"), ' ', $return));
			}
			$this->logMessage("  Execution time:\t{$seconds} seconds");

			$this->markLastRun($job, $lastScheduled, $jobData);

			$this->logMessage("Completed\t" . $job[self::PC_CRONLINE]);
			if ($this->sendLog) {
				mail($this->sendLogMailAddr, "[cron] " . $job[self::PC_COMMENT], $this->resultsSummary);
			}
			$this->resultsSummary = "";
			return true;
		} else {
			return false;
		}
	}

	protected function parseCronFile($cronTabFile) {
		$file = file($cronTabFile);
		$job = array();
		$jobs = array();
		for ($i = 0; $i < count($file); $i++) {
			if ($file[$i][0] != '#') {
				if (preg_match("~^([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-7,/*]+|(-|/|Sun|Mon|Tue|Wed|Thu|Fri|Sat)+)\\s+([^#]*)\\s*(#.*)?$~i", $file[$i], $job)) {
					$jobNumber = count($jobs);
					if (!array_key_exists(self::PC_COMMENT, $job)) {
						$job[self::PC_COMMENT] = '';
					}
					$jobs[$jobNumber] = $job;
					if ($jobs[$jobNumber][self::PC_DOW][0] != '*' AND ! is_numeric($jobs[$jobNumber][self::PC_DOW])) {
						$jobs[$jobNumber][self::PC_DOW] = str_replace(
							array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"),
							array(0, 1, 2, 3, 4, 5, 6),
							$jobs[$jobNumber][self::PC_DOW]
						);
					}
					$jobs[$jobNumber][self::PC_CMD] = trim($job[self::PC_CMD]);
					$jobs[$jobNumber][self::PC_COMMENT] = trim(mb_substr($job[self::PC_COMMENT], 1));
					$jobs[$jobNumber][self::PC_ARGS] = array();
					if (preg_match_all('~(("([^"]*)")|(\S+))\s*~iu', $jobs[$jobNumber][self::PC_CMD], $jobArgs, PREG_PATTERN_ORDER)) {
						for ($ii = 0; $ii < count($jobArgs[1]); $ii++) {
							$jobArg = ($jobArgs[3][$ii] === '' ? $jobArgs[1][$ii] : $jobArgs[3][$ii]);
							if ($ii == 0) {
								$jobs[$jobNumber][self::PC_CMD] = $jobArg;
							}
							$jobs[$jobNumber][self::PC_ARGS][$ii] = str_replace(array('\r', '\n'), array("\r", "\n"), $jobArg);
						}
					}
					$jobs[$jobNumber][self::PC_CRONLINE] = $file[$i];
				}

				$jobs[$jobNumber]["lastActual"] = $this->getLastActualRunTime($jobs[$jobNumber]);
				$jobs[$jobNumber]["lastScheduled"] = $this->getLastScheduledRunTime($jobs[$jobNumber]);
			}
		}

		$this->multisort($jobs, "lastScheduled");

		return $jobs;
	}

	public function sendPixelImage() {
		header("Content-Type: image/gif");
		echo base64_decode("R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==");
	}

	public function execute($cronTabFile, $maxJobs = 0) {
		$jobs = $this->parseCronFile($cronTabFile);
		$jobsRun = 0;
		for ($i = 0; $i < count($jobs); $i++) {
			if ($maxJobs == 0 || $jobsRun < $maxJobs) {
				if ($this->runJob($jobs[$i])) {
					$jobsRun++;
				}
			}
		}
		return $jobsRun;
	}

}
