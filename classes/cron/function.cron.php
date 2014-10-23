<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

define("PC_MINUTE",		1);
define("PC_HOUR",		2);
define("PC_DOM",		3);
define("PC_MONTH",		4);
define("PC_DOW",		5);
define("PC_CMD",		7);
define("PC_COMMENT",	8);
define("PC_ARGS",		19);
define("PC_CRONLINE", 	20);

define("CRON_PATH", 'classes/cron/jobs/');

function logMessage($msg) {
	global $writeDir, $useLog, $resultsSummary;
	if ($msg[strlen($msg)-1]!="\n") {
		$msg.="\n";
	}
	$resultsSummary.= $msg;
	if ($useLog) {
		$logfile = $writeDir."cron.log";
		$file = fopen($logfile,"a");
		fputs($file,date("r")."  ".$msg);
		fclose($file);
	}
}

function lTrimZeros($number) {
	while ($number[0] == '0') {
		$number = substr($number,1);
	}
	return $number;
}

function multisort(&$array, $sortby, $order='asc') {
   foreach($array as $val) {
       $sortarray[] = $val[$sortby];
   }
   $c = $array;
   $const = $order == 'asc' ? SORT_ASC : SORT_DESC;
   $s = array_multisort($sortarray, $const, $c, $const);
   $array = $c;
   return $s;
}

function parseElement($element, &$targetArray, $numberOfElements) {
	$subelements = explode(",",$element);
	for ($i=0;$i<$numberOfElements;$i++) {
		$targetArray[$i] = $subelements[0]=="*";
	}

	for ($i=0;$i<count($subelements);$i++) {
		if (preg_match("~^(\\*|([0-9]{1,2})(-([0-9]{1,2}))?)(/([0-9]{1,2}))?$~",$subelements[$i],$matches)) {
			if ($matches[1]=="*") {
				$matches[2] = 0;		// from
				$matches[4] = $numberOfElements;		//to
			}
			elseif (empty($matches[4])) {
				$matches[4] = $matches[2];
			}
			if (!isset($matches[5]) || (isset($matches[5]) && $matches[5]{0}!="/")) {
				$matches[6] = 1;
			}
			for ($j=lTrimZeros($matches[2]);$j<=lTrimZeros($matches[4]);$j+=lTrimZeros($matches[6])) {
				$targetArray[$j] = TRUE;
			}
		}
	}
}

function incDate(&$dateArr, $amount, $unit) {

	if ($unit == "mday") {
		$dateArr["hours"] = 0;
		$dateArr["minutes"] = 0;
		$dateArr["seconds"] = 0;
		$dateArr["mday"] += $amount;
		$dateArr["wday"] += $amount % 7;
		if ($dateArr["wday"] > 6) {
			$dateArr["wday"] -= 7;
		}

        // Start: Bug (13. month) fixed by MaMo-Net
        if ($dateArr["mday"] == date("t")) {
            $dateArr["mon"]++;
            if ($dateArr["mon"] > 12) {
            	$dateArr["mon"] = 1;
            }
            $dateArr["mday"] = 1;
        }
        // End: Bug (13. month) fixed by MaMo-Net

	}
	elseif ($unit == "hour") {
		if ($dateArr["hours"] == 23) {
			incDate($dateArr, 1, "mday");
		}
		else {
			$dateArr["minutes"] = 0;
			$dateArr["seconds"] = 0;
			$dateArr["hours"]++;
		}
	}
	elseif ($unit=="minute") {
		if ($dateArr["minutes"] == 59) {
			incDate($dateArr, 1, "hour");
		}
		else {
			$dateArr["seconds"] = 0;
			$dateArr["minutes"]++;
		}
	}
}

function getLastScheduledRunTime($job) {
	$cron_string = $job[PC_MINUTE].' '.$job[PC_HOUR].' '.$job[PC_DOM].' '.$job[PC_MONTH].' '.$job[PC_DOW];
	$cronPars = new CronParser();
	$cronPars->calcLastRan($cron_string);
	return $cronPars->getLastRanUnix();
}

function getJobFileName($job) {
	global $writeDir;
	$jobArgHash = ( count($job[PC_ARGS]) > 1 ? '_'.md5(implode('', $job[PC_ARGS])) : '' );
	$jobfile = $writeDir.urlencode($job[PC_CMD]).$jobArgHash.".job";
	return $jobfile;
}

function getLastActualRunTime($job) {
	$jobfile = getJobFileName($job);
	if (file_exists($jobfile)) {
    	return filemtime($jobfile);
	}
	return 0;
}

function getJobTempData($job) {
	$jobfile = getJobFileName($job);
	if (file_exists($jobfile)) {
		$data = file_get_contents($jobfile);
	}
	else {
		$data = '';
	}
	return $data;
}

function markLastRun($job, $lastRun, $data = '') {
	global $filesystem;
	$jobfile = getJobFileName($job);
	$filesystem->file_put_contents($jobfile, $data);
	touch($jobfile);
}

function runJob($job) {
	global $sendLogToEmail, $resultsSummary, $jobdir;
	$resultsSummary = "";

	$lastActual = $job["lastActual"];
	$lastScheduled = $job["lastScheduled"];

	if ($lastScheduled>$lastActual) {
		logMessage("Running\t".$job[PC_CRONLINE]);
		logMessage("  Last run:\t".date("r",$lastActual));
		logMessage("  Last scheduled:\t".date("r",$lastScheduled));
		$argv = $job[PC_ARGS];

		$jobData = getJobTempData($job);

		$benchmark = job_benchmark_start();
		ob_start();
	    include(CRON_PATH.$job[PC_CMD]);
	    $return = trim(ob_get_contents());
	    ob_end_clean();
	    $seconds = job_benchmark_end($benchmark);

	    if (!empty($return)) {
			logMessage("  Script returned:\t".makeOneLine($return));
	    }
		logMessage("  Execution time:\t$seconds seconds");

	    markLastRun($job, $lastScheduled, $jobData);

		logMessage("Completed\t".$job[PC_CRONLINE]);
		if ($sendLogToEmail!="") {
			mail($sendLogToEmail, "[cron] ".$job[PC_COMMENT], $resultsSummary);
		}
		return true;
	}
	else {
		return false;
	}
}

function parseCronFile($cronTabFile) {
	$file = file($cronTabFile);
	$job = Array();
	$jobs = Array();
	for ($i=0;$i<count($file);$i++) {
		if ($file[$i][0]!='#') {
			if (preg_match("~^([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-7,/*]+|(-|/|Sun|Mon|Tue|Wed|Thu|Fri|Sat)+)\\s+([^#]*)\\s*(#.*)?$~i",$file[$i],$job)) {
				$jobNumber = count($jobs);
				//DL+- DEBUG - AVOID PHP NOTICES WHEN NO COMMENT IS GIVEN
		        if(!array_key_exists(PC_COMMENT, $job)) {
		        	$job[PC_COMMENT] = '';
		        }
				$jobs[$jobNumber] = $job;
				if ($jobs[$jobNumber][PC_DOW][0]!='*' AND !is_numeric($jobs[$jobNumber][PC_DOW])) {
					$jobs[$jobNumber][PC_DOW] = str_replace(
						Array("Sun","Mon","Tue","Wed","Thu","Fri","Sat"),
						Array(0,1,2,3,4,5,6),
						$jobs[$jobNumber][PC_DOW]);
				}
				$jobs[$jobNumber][PC_CMD] = trim($job[PC_CMD]);
				$jobs[$jobNumber][PC_COMMENT] = trim(substr($job[PC_COMMENT],1));
				$jobs[$jobNumber][PC_ARGS] = Array();
				if (preg_match_all('~(("([^"]*)")|(\S+))\s*~i', $jobs[$jobNumber][PC_CMD], $jobArgs, PREG_PATTERN_ORDER)) {
					for($ii=0; $ii<count($jobArgs[1]); $ii++){
						$jobArg = ($jobArgs[3][$ii]==='' ? $jobArgs[1][$ii] : $jobArgs[3][$ii]);
						if($ii==0) {
							$jobs[$jobNumber][PC_CMD] = $jobArg;
						}
						$jobs[$jobNumber][PC_ARGS][$ii] = str_replace(Array('\r','\n'), Array("\r","\n"), $jobArg);
				 	}
				}
				$jobs[$jobNumber][PC_CRONLINE] = $file[$i];
			}

		    $jobs[$jobNumber]["lastActual"] = getLastActualRunTime($jobs[$jobNumber]);
			$jobs[$jobNumber]["lastScheduled"] = getLastScheduledRunTime($jobs[$jobNumber]);
		}
	}

	multisort($jobs, "lastScheduled");

	return $jobs;
}

function PixelImage() {
	header("Content-Type: image/gif");
	echo base64_decode("R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==");
}

function InitCron() {
	global $cronTab, $maxJobs;
	@ignore_user_abort(false);
	$save_mode = @ini_get('safe_mode');
	if (!$save_mode) {
		@set_time_limit(60);
	}
	$jobs = parseCronFile($cronTab);
	$jobsRun = 0;
	for ($i=0;$i<count($jobs);$i++) {
		if ($maxJobs==0 || $jobsRun<$maxJobs) {
			if (runJob($jobs[$i])) {
				$jobsRun++;
			}
		}
	}
}

function job_benchmark_start() {
	return benchmarktime();
}
function job_benchmark_end($start) {
	$duration = benchmarktime() - $start;
	$duration = round($duration, 5);
	return $duration;
}
?>
