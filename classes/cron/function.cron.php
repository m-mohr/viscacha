<?php

define("PC_MINUTE",	1);
define("PC_HOUR",	2);
define("PC_DOM",	3);
define("PC_MONTH",	4);
define("PC_DOW",	5);
define("PC_CMD",	7);
define("PC_COMMENT",8);
define("PC_ARGS",	19);
define("PC_CRONLINE", 20);

define("CRON_PATH", 'classes/cron/jobs/');

function logMessage($msg) {
	GLOBAL $writeDir, $useLog, $debug, $resultsSummary;
	if ($msg[strlen($msg)-1]!="\n") {
		$msg.="\n";
	}
	if ($debug) echo $msg;
	$resultsSummary.= $msg;
	if ($useLog) {
		$logfile = $writeDir."cron.log";
		$file = fopen($logfile,"a");
		fputs($file,date("r",time())."  ".$msg);
		fclose($file);
	}
}

function lTrimZeros($number) {
	GLOBAL $debug;
	while ($number[0]=='0') {
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
	GLOBAL $debug;
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
	GLOBAL $debug;
	
	if ($debug) echo sprintf("Increasing from %02d.%02d. %02d:%02d by %d %6s ",$dateArr['mday'],$dateArr['mon'],$dateArr['hours'],$dateArr['minutes'],$amount,$unit);
	if ($unit=="mday") {
		$dateArr["hours"] = 0;
		$dateArr["minutes"] = 0;
		$dateArr["seconds"] = 0;
		$dateArr["mday"] += $amount;
		$dateArr["wday"] += $amount % 7;
		if ($dateArr["wday"]>6) {
			$dateArr["wday"]-=7;
		}

        // Start: Bug (13. month) fixed by MaMo-Net
        if ($dateArr["mday"]==date("t")) { 
            $dateArr["mon"]++; 
            if ($dateArr["mon"] > 12) {
            	$dateArr["mon"] = 1;
            }
            $dateArr["mday"] = 1; 
        }
        // End: Bug (13. month) fixed by MaMo-Net
		
	} elseif ($unit=="hour") {
		if ($dateArr["hours"]==23) {
			incDate($dateArr, 1, "mday");
		} else {
			$dateArr["minutes"] = 0;
			$dateArr["seconds"] = 0;
			$dateArr["hours"]++;
		}
	} elseif ($unit=="minute") {
		if ($dateArr["minutes"]==59) {
			incDate($dateArr, 1, "hour");
		} else {
			$dateArr["seconds"] = 0;
			$dateArr["minutes"]++;
		}
	}
	if ($debug) echo sprintf("to %02d.%02d. %02d:%02d\n",$dateArr['mday'],$dateArr['mon'],$dateArr['hours'],$dateArr['minutes']);
}

function getLastScheduledRunTime($job) {
  GLOBAL $debug;

//DL+ DEBUG - USE PHP CRON CLASS
//COMPLETELY REPLACED IT BY THE PHP CRON CLASS BY MICK SEAR (see www.phpclasses.org)
//---------
  /*
  $extjob = Array();
  parseElement($job[PC_MINUTE], $extjob[PC_MINUTE], 60);
  parseElement($job[PC_HOUR], $extjob[PC_HOUR], 24);
  parseElement($job[PC_DOM], $extjob[PC_DOM], 31);
  parseElement($job[PC_MONTH], $extjob[PC_MONTH], 12);
  parseElement($job[PC_DOW], $extjob[PC_DOW], 7);
  
//DL+- ADD ARGUMENTS TO JOB CALL
//$dateArr = getdate(getLastActualRunTime($job[PC_CMD]));
  $dateArr = getdate(getLastActualRunTime($job));
  $minutesAhead = 0;
  while (
    $minutesAhead<525600 AND 
    (!$extjob[PC_MINUTE][$dateArr["minutes"]] OR 
    !$extjob[PC_HOUR][$dateArr["hours"]] OR 
    (!$extjob[PC_DOM][$dateArr["mday"]] OR !$extjob[PC_DOW][$dateArr["wday"]]) OR
    !$extjob[PC_MONTH][$dateArr["mon"]])
  ) {
    if (!$extjob[PC_DOM][$dateArr["mday"]] OR !$extjob[PC_DOW][$dateArr["wday"]]) {
      incDate($dateArr,1,"mday");
      $minutesAhead+=1440;
      continue;
    }
    if (!$extjob[PC_HOUR][$dateArr["hours"]]) {
      incDate($dateArr,1,"hour");
      $minutesAhead+=60;
      continue;
    }
    if (!$extjob[PC_MINUTE][$dateArr["minutes"]]) {
      incDate($dateArr,1,"minute");
      $minutesAhead++;
      continue;
    }
  }
  
  return mktime($dateArr["hours"],$dateArr["minutes"],0,$dateArr["mon"],$dateArr["mday"],$dateArr["year"]);
  */

  $cron_string = $job[PC_MINUTE].' '.$job[PC_HOUR].' '.$job[PC_DOM].' '.$job[PC_MONTH].' '.$job[PC_DOW];
  
  $cronPars = new CronParser();
  
  $cronPars->calcLastRan($cron_string);

  if ($debug) {
  	print_r($cronPars->getLastRan());
  }

  return $cronPars->getLastRanUnix();

//DL- DEBUG - USE PHP CRON CLASS
}

//DL+ ADD ARGUMENTS TO JOB CALL
/*
function getJobFileName($jobname) {
  GLOBAL $writeDir;
  GLOBAL $debug;
  $jobfile = $writeDir.urlencode($jobname).".job";
  return $jobfile;
}
*/
function getJobFileName($job) {
  GLOBAL $writeDir;
  GLOBAL $debug;
  $jobArgHash = ( count($job[PC_ARGS])>1 ? '_'.md5(implode('', $job[PC_ARGS])) : '' );
  $jobfile = $writeDir.urlencode($job[PC_CMD]).$jobArgHash.".job";
  return $jobfile;
}
//DL- ADD ARGUMENTS TO JOB CALL

//DL+ ADD ARGUMENTS TO JOB CALL
/*
function getLastActualRunTime($jobname) {
  GLOBAL $debug;
  $jobfile = getJobFileName($jobname);
  if (file_exists($jobfile)) {
    return filemtime($jobfile);
  }
  return 0;
}
*/
function getLastActualRunTime($job) {
  GLOBAL $debug;
  $jobfile = getJobFileName($job);
  if (file_exists($jobfile)) {
    return filemtime($jobfile);
  }
  return 0;
}
//DL- ADD ARGUMENTS TO JOB CALL

//DL+ ADD ARGUMENTS TO JOB CALL
/*
function markLastRun($jobname, $lastRun) {
  $jobfile = getJobFileName($jobname);
  touch($jobfile);
}
*/
function markLastRun($job, $lastRun) {
  $jobfile = getJobFileName($job);
  touch($jobfile);
}
//DL- ADD ARGUMENTS TO JOB CALL

function runJob($job) {
	GLOBAL $debug, $sendLogToEmail, $resultsSummary, $jobdir, $config, $db;
	$resultsSummary = "";
	
	$lastActual = $job["lastActual"];
	$lastScheduled = $job["lastScheduled"];
	
//DL+- DEBUG - CORRECT COMPARISON
//	if ($lastScheduled<time()) {
	if ($lastScheduled>$lastActual) {
		logMessage("Running\t".$job[PC_CRONLINE]);
		logMessage("  Last run:\t".date("r",$lastActual));
		logMessage("  Last scheduled:\t".date("r",$lastScheduled));
		//DL+ ADD ARGUMENTS TO JOB CALL
		    $argv = $job[PC_ARGS];
		//DL- ADD ARGUMENTS TO JOB CALL
	
		$benchmark = job_benchmark_start();
	    if ($debug) {
	     	include(CRON_PATH.$job[PC_CMD]);
	    }
	    else {
	      	$e = @error_reporting(0);
	      	include(CRON_PATH.$job[PC_CMD]);
	      	@error_reporting($e);
	    }
		$seconds = job_benchmark_end($benchmark);
		logMessage("  Execution time:\t$seconds seconds");
		
	//DL+- ADD ARGUMENTS TO JOB CALL
	//  markLastRun($job[PC_CMD], $lastScheduled);
	    markLastRun($job, $lastScheduled);
	    
		logMessage("Completed\t".$job[PC_CRONLINE]);
		if ($sendLogToEmail!="") {
			mail($sendLogToEmail, "[cron] ".$job[PC_COMMENT], $resultsSummary);
		}
		return true;
	}
	else {
		if ($debug) {
			logMessage("Skipping\t".$job[PC_CRONLINE]);
			logMessage("  Last run:\t".date("r",$lastActual));
			logMessage("  Last scheduled:\t".date("r",$lastScheduled));
			logMessage("Completed\t".$job[PC_CRONLINE]);
		}
		return false;
	}
}

function parseCronFile($cronTabFile) {
	GLOBAL $debug;
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
				//DL+ ADD ARGUMENTS TO JOB CALL
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
				//DL- ADD ARGUMENTS TO JOB CALL
				$jobs[$jobNumber][PC_CRONLINE] = $file[$i];
			}
			//DL+- DEBUG - LINE OBSOLETE
			//    $jobfile = getJobFileName($jobs[$jobNumber][PC_CMD]);
			
		//DL+- ADD ARGUMENTS TO JOB CALL (line now obsolete)
		//  $jobs[$jobNumber]["lastActual"] = getLastActualRunTime($jobs[$jobNumber][PC_CMD]);
		    $jobs[$jobNumber]["lastActual"] = getLastActualRunTime($jobs[$jobNumber]);
			$jobs[$jobNumber]["lastScheduled"] = getLastScheduledRunTime($jobs[$jobNumber]);
		}
	}
	
	multisort($jobs, "lastScheduled");
	
	if ($debug) var_dump($jobs);
	return $jobs;
}

function PixelImage() {
	header("Content-Type: image/gif");
	echo base64_decode("R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==");
}

function InitCron() {
	global $cronTab, $maxJobs;
	@ignore_user_abort(FALSE);
	@set_time_limit(60);
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
	$zeitmessung1=benchmarktime();
	return $zeitmessung1;
}
function job_benchmark_end($zeitmessung1) {
	$zeitmessung2=benchmarktime();
	$zeitmessung=$zeitmessung2-$zeitmessung1; 
	$zeitmessung=substr($zeitmessung,0,6);
	return $zeitmessung;
}
?>
