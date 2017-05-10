<?php

namespace Viscacha\Model;

class InvalidMassDataException extends \Exception {
	
	public $errors;
	
	public function __construct($errors) {
		global $lang;
		parent::__construct($lang->phrase('query_string_error'), 0, null);
		$this->errors = $errors;
	}
	
	public function getErrorMessages($field = null) {
		if ($field !== null) {
			return isset($this->errors[$field]) ? $this->errors[$field] : false;
		}
		else {
			// Flatten array to remove field grouping
			return call_user_func_array('array_merge', $this->errors);
		}
	}
	
}
