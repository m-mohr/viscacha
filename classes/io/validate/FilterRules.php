<?php

namespace Viscacha\IO\Validate;

class FilterRules implements Rules {
	
	public function str($data, $default = '') {
		$data = trim($data);
		return !empty($data) ? trim($data) : $default;
	}
	
	public function strNull($data) {
		$data = $this->str($data);
		return \Str::length($data) > 0 ? $data : null;
	}
	
	public function integer($data) {
		return intval($data);
	}
	
	public function positiveInteger($data, $default = 1) {
		$data = $this->integer($data);
		return $data > 0 ? $data : $default;
	}
	
}
