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

class Parser {

	protected $bits = array(); //exploded String like 0 1 * * *
	protected $now = array(); //Array of cron-style entries for time()
	protected $lastRan;   //Timestamp of last ran time.
	protected $taken;
	protected $year;
	protected $month;
	protected $day;
	protected $hour;
	protected $minute;
	protected $minutes_arr = array(); //minutes array based on cron string
	protected $hours_arr = array();  //hours array based on cron string
	protected $months_arr = array(); //months array based on cron string

	public function getLastRan() {
		return explode(",", strftime("%M,%H,%d,%m,%w,%Y", $this->lastRan)); //Get the values for now in a format we can use
	}

	public function getLastRanUnix() {
		return $this->lastRan;
	}

	/**
	 * Assumes that value is not *, and creates an array of valid numbers that
	 * the string represents.  Returns an array.
	 */
	public function expandRanges($str) {
		if (strstr($str, ",")) {
			$arParts = explode(',', $str);
			foreach ($arParts AS $part) {
				if (strstr($part, '-')) {
					$arRange = explode('-', $part);
					for ($i = $arRange[0]; $i <= $arRange[1]; $i++) {
						$ret[] = $i;
					}
				} else {
					$ret[] = $part;
				}
			}
		} elseif (strstr($str, '-')) {
			$arRange = explode('-', $str);
			for ($i = $arRange[0]; $i <= $arRange[1]; $i++) {
				$ret[] = $i;
			}
		} else {
			$ret[] = $str;
		}
		$ret = array_unique($ret);
		sort($ret);
		return $ret;
	}

	public function daysInMonth($month, $year) {
		return date('t', mktime(0, 0, 0, $month, 1, $year));
	}

	/**
	 *  Calculate the last due time before this moment
	 */
	public function calcLastRan($string) {

		$tstart = microtime(true);
		$this->lastRan = 0;
		$this->year = NULL;
		$this->month = NULL;
		$this->day = NULL;
		$this->hour = NULL;
		$this->minute = NULL;
		$this->hours_arr = array();
		$this->minutes_arr = array();
		$this->months_arr = array();

		$string = preg_replace('/[\s]{2,}/u', ' ', $string);

		if (preg_match('/[^-,* \\d]/u', $string) !== 0) {
			return false;
		}

		$this->bits = @explode(" ", $string);

		if (count($this->bits) != 5) {
			return false;
		}

		//put the current time into an array
		$t = strftime("%M,%H,%d,%m,%w,%Y", time());
		$this->now = explode(",", $t);

		$this->year = $this->now[5];

		$arMonths = $this->getMonthsArray();

		do {
			$this->month = array_pop($arMonths);
		} while ($this->month > $this->now[3]);

		if ($this->month === NULL) {
			$this->year = $this->year - 1;
			$arMonths = $this->getMonthsArray();
			$this->prevMonth($arMonths);
		} elseif ($this->month == $this->now[3]) {
			$arDays = $this->getDaysArray($this->month, $this->year);

			do {
				$this->day = array_pop($arDays);
			} while ($this->day > $this->now[2]);

			if ($this->day === NULL) {
				$this->prevMonth($arMonths);
			} elseif ($this->day == $this->now[2]) {
				$arHours = $this->getHoursArray();

				do {
					$this->hour = array_pop($arHours);
				} while ($this->hour > $this->now[1]);

				if ($this->hour === NULL) {
					$this->prevDay($arDays, $arMonths);
				} elseif ($this->hour < $this->now[1]) {
					$this->minute = $this->getLastMinute();
				} else {
					$arMinutes = $this->getMinutesArray();
					do {
						$this->minute = array_pop($arMinutes);
					} while ($this->minute > $this->now[0]);

					if ($this->minute === NULL) {
						$this->prevHour($arHours, $arDays, $arMonths);
					}
				}
			} else {
				$this->hour = $this->getLastHour();
				$this->minute = $this->getLastMinute();
			}
		} else {
			$this->day = $this->getLastDay($this->month, $this->year);
			if ($this->day === NULL) {
				//No scheduled date within this month. So we will try the previous month in the month array
				$this->prevMonth($arMonths);
			} else {
				$this->hour = $this->getLastHour();
				$this->minute = $this->getLastMinute();
			}
		}

		$tend = microtime(true);
		$this->taken = $tend - $tstart;

		//if the last due is beyond 1970
		if ($this->minute === NULL) {
			return false;
		} else {
			$this->lastRan = mktime($this->hour, $this->minute, 0, $this->month, $this->day, $this->year);
			return true;
		}
	}

	//get the due time before current month
	protected function prevMonth($arMonths) {
		$this->month = array_pop($arMonths);
		if ($this->month === NULL) {
			$this->year = $this->year - 1;
			if ($this->year <= 1970) {
				
			} else {
				$arMonths = $this->getMonthsArray();
				$this->prevMonth($arMonths);
			}
		} else {
			$this->day = $this->getLastDay($this->month, $this->year);

			if ($this->day === NULL) {
				//no available date schedule in this month
				$this->prevMonth($arMonths);
			} else {
				$this->hour = $this->getLastHour();
				$this->minute = $this->getLastMinute();
			}
		}
	}

	//get the due time before current day
	protected function prevDay($arDays, $arMonths) {
		$this->day = array_pop($arDays);
		if ($this->day === NULL) {
			$this->prevMonth($arMonths);
		} else {
			$this->hour = $this->getLastHour();
			$this->minute = $this->getLastMinute();
		}
	}

	//get the due time before current hour
	protected function prevHour($arHours, $arDays, $arMonths) {
		$this->hour = array_pop($arHours);
		if ($this->hour === NULL) {
			$this->prevDay($arDays, $arMonths);
		} else {
			$this->minute = $this->getLastMinute();
		}
	}

	protected function getLastDay($month, $year) {
		//put the available days for that month into an array
		$days = $this->getDaysArray($month, $year);
		$day = array_pop($days);

		return $day;
	}

	protected function getLastHour() {
		$hours = $this->getHoursArray();
		$hour = array_pop($hours);

		return $hour;
	}

	protected function getLastMinute() {
		$minutes = $this->getMinutesArray();
		$minute = array_pop($minutes);

		return $minute;
	}

	//remove the out of range array elements. $arr should be sorted already and does not contain duplicates
	protected function sanitize($arr, $low, $high) {
		$count = count($arr);
		for ($i = 0; $i <= ($count - 1); $i++) {
			if ($arr[$i] < $low) {
				unset($arr[$i]);
			} else {
				break;
			}
		}

		for ($i = ($count - 1); $i >= 0; $i--) {
			if ($arr[$i] > $high) {
				unset($arr[$i]);
			} else {
				break;
			}
		}

		//re-assign keys
		sort($arr);
		return $arr;
	}

	//given a month/year, list all the days within that month fell into the week days list.
	protected function getDaysArray($month, $year = 0) {
		if ($year == 0) {
			$year = $this->year;
		}

		$days = array();

		//return everyday of the month if both bit[2] and bit[4] are '*'
		if ($this->bits[2] == '*' AND $this->bits[4] == '*') {
			$days = $this->getDays($month, $year);
		} else {
			//create an array for the weekdays
			if ($this->bits[4] == '*') {
				for ($i = 0; $i <= 6; $i++) {
					$arWeekdays[] = $i;
				}
			} else {
				$arWeekdays = $this->expandRanges($this->bits[4]);
				$arWeekdays = $this->sanitize($arWeekdays, 0, 7);

				//map 7 to 0, both represents Sunday. Array is sorted already!
				if (in_array(7, $arWeekdays)) {
					if (in_array(0, $arWeekdays)) {
						array_pop($arWeekdays);
					} else {
						$tmp[] = 0;
						array_pop($arWeekdays);
						$arWeekdays = array_merge($tmp, $arWeekdays);
					}
				}
			}

			if ($this->bits[2] == '*') {
				$daysmonth = $this->getDays($month, $year);
			} else {
				$daysmonth = $this->expandRanges($this->bits[2]);
				// so that we do not end up with 31 of Feb
				$daysinmonth = $this->daysInMonth($month, $year);
				$daysmonth = $this->sanitize($daysmonth, 1, $daysinmonth);
			}

			//Now match these days with weekdays
			foreach ($daysmonth AS $day) {
				$wkday = date('w', mktime(0, 0, 0, $month, $day, $year));
				if (in_array($wkday, $arWeekdays)) {
					$days[] = $day;
				}
			}
		}
		return $days;
	}

	//given a month/year, return an array containing all the days in that month
	public function getDays($month, $year) {
		$daysinmonth = $this->daysInMonth($month, $year);
		$days = array();
		for ($i = 1; $i <= $daysinmonth; $i++) {
			$days[] = $i;
		}
		return $days;
	}

	protected function getHoursArray() {
		if (empty($this->hours_arr)) {
			$hours = array();

			if ($this->bits[1] == '*') {
				for ($i = 0; $i <= 23; $i++) {
					$hours[] = $i;
				}
			} else {
				$hours = $this->expandRanges($this->bits[1]);
				$hours = $this->sanitize($hours, 0, 23);
			}

			$this->hours_arr = $hours;
		}
		return $this->hours_arr;
	}

	protected function getMinutesArray() {
		if (empty($this->minutes_arr)) {
			$minutes = array();

			if ($this->bits[0] == '*') {
				for ($i = 0; $i <= 60; $i++) {
					$minutes[] = $i;
				}
			} else {
				$minutes = $this->expandRanges($this->bits[0]);
				$minutes = $this->sanitize($minutes, 0, 59);
			}
			$this->minutes_arr = $minutes;
		}
		return $this->minutes_arr;
	}

	protected function getMonthsArray() {
		if (empty($this->months_arr)) {
			$months = array();
			if ($this->bits[3] == '*') {
				for ($i = 1; $i <= 12; $i++) {
					$months[] = $i;
				}
			} else {
				$months = $this->expandRanges($this->bits[3]);
				$months = $this->sanitize($months, 1, 12);
			}
			$this->months_arr = $months;
		}
		return $this->months_arr;
	}

}
