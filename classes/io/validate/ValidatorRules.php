<?php

namespace Viscacha\IO\Validate;

class ValidatorRules implements Rules {
	
	public function required($data) {
		return !empty($data);
	}
	
	public function nullable($data) {
		return ($data === null);
	}
	
	public function email($email) {
		return is_email($email);
	}
	
	public function url($data) {
		return is_url($data);
	}
	
	public function min($data, $minimum) {
		return $data >= $minimum;
	}
	
	public function max($data, $maximum) {
		return $data <= $maximum;
	}
	
	public function between($data, $minimum, $maximum) {
		return $this->min($data, $minimum) && $this->max($data, $maximum);
	}
	
	public function length($data, $minLength, $maxLength) {
		return $this->minLength($data, $minLength) && $this->maxLength($data, $maxLength);
	}
	
	public function minLength($data, $minLength) {
		return \Str::length($data) >= $minLength;
	}
	
	public function maxLength($data, $maxLength) {
		return \Str::length($data) <= $maxLength;
	}
	
	public function integer($data) {
		return (!is_int($data) ? ctype_digit($data) : true);
	}
	
	public function equals($data, $compareWith) {
		return ($data == $compareWith);
	}
	
	public function in() {
		$args = func_get_args();
		if (count($args) < 2) {
			throw new \InvalidArgumentException('At least two arguments need to be specified.');
		}
		$data = array_shift($args);
		return in_array($data, $args);
	}
	
	public function id($data) {
		return is_id($data);
	}
	
	public function regexp($data, $pattern) {
		return (preg_match($pattern, $data) == 1);
	}
	
}