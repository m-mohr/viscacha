<?php

namespace Viscacha\IO\Validate;

class ValidatorRules implements Rules {
	
	public function required($data, RuleProcessor $context = null) {
		return !empty($data);
	}
	
	public function nullable($data, RuleProcessor $context = null) {
		return ($data === null);
	}
	
	public function email($email, RuleProcessor $context = null) {
		return is_email($email);
	}
	
	public function url($data, RuleProcessor $context = null) {
		return is_url($data);
	}
	
	public function min($data, $minimum, RuleProcessor $context = null) {
		return $data >= $minimum;
	}
	
	public function max($data, $maximum, RuleProcessor $context = null) {
		return $data <= $maximum;
	}
	
	public function between($data, $minimum, $maximum, RuleProcessor $context = null) {
		return $this->min($data, $minimum) && $this->max($data, $maximum);
	}
	
	public function length($data, $minLength, $maxLength, RuleProcessor $context = null) {
		return $this->minLength($data, $minLength) && $this->maxLength($data, $maxLength);
	}
	
	public function minLength($data, $minLength, RuleProcessor $context = null) {
		return \Str::length($data) >= $minLength;
	}
	
	public function maxLength($data, $maxLength, RuleProcessor $context = null) {
		return \Str::length($data) <= $maxLength;
	}
	
	public function integer($data, RuleProcessor $context = null) {
		return (!is_int($data) ? ctype_digit($data) : true);
	}
	
	public function equals($data, $compareWith, RuleProcessor $context = null) {
		return ($data == $compareWith);
	}
	
	public function in() {
		$args = func_get_args();
		// First parameter: $data
		$data = array_shift($args);
		// Last paremeter: $context
		if (end($args) instanceof RuleProcessor) {
			$content = array_pop($args);
		}
		if (empty($args)) {
			throw new \InvalidArgumentException('At least one additional argument needs to be specified for ValidatorRules::in().');
		}
		return in_array($data, $args);
	}
	
	public function id($data, RuleProcessor $context = null) {
		return is_id($data);
	}
	
	public function regexp($data, $pattern, RuleProcessor $context = null) {
		return (preg_match($pattern, $data) == 1);
	}
	
}