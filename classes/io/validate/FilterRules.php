<?php

namespace Viscacha\IO\Validate;

class FilterRules implements Rules {
	
	public function str($data, RuleProcessor $context = null) {
		return trim($data);
	}
	
	public function integer($data, RuleProcessor $context = null) {
		return intval($data);
	}
	
	public function positiveInteger($data, RuleProcessor $context = null) {
		$data = $this->integer($data);
		return $data > 0 ? $data : 1;
	}
	
	public function pageNumber($data, $maxPage, RuleProcessor $context = null) {
		$data = $this->positiveInteger($data);
		return $data <= $maxPage ? $data : $maxPage;
	}
	
	public function preset($data, $defaultValue, RuleProcessor $context = null) {
		return !empty($data) ? $data : $defaultValue;
	}
	
	public function nullable($data, RuleProcessor $context = null) {
		return !empty($data) ? $data : null;
	}
		
	
}
