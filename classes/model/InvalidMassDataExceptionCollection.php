<?php

namespace Viscacha\Model;

class InvalidMassDataExceptionCollection extends \Exception {
	
	public $exceptions;
	
	public function __construct() {
		global $lang;
		parent::__construct($lang->phrase('query_string_error'), 0, null);
	}
	
	public function add($id, InvalidMassDataException $e) {
		$this->exceptions[$id] = $e;
	}
	
	public function get($id) {
		return isset($this->exceptions[$id]) ? $this->exceptions[$id] : null;
	}
	
	public function toArray() {
		return $this->exceptions;
	}
	
	public function count() {
		return count($this->exceptions);
	}
	
}