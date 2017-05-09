<?php

namespace Viscacha\IO\Validate;

class InvalidDataException extends Exception {
	
	public $field;
	public $rule;
	public $args;
	
	public function __construct($field, $rule, $args = null, $message = "") {
		parent::__construct($message, 0, null);
		$this->field = $field;
		$this->rule = $rule;
		$this->args = $args;
	}
	
}
