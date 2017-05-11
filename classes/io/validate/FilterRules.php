<?php

namespace Viscacha\IO\Validate;

class FilterRules implements Rules {
	
	/**
	 * Strings (trimmed).
	 * 
	 * @param mixed $data
	 * @param \Viscacha\IO\Validate\RuleProcessor $context
	 * @return string
	 */
	public function str($data, RuleProcessor $context = null) {
		return trim($data);
	}
	
	/**
	 * Integers (...,-3,-2,-1,0,1,2,3,...)
	 * 
	 * @param mixed $data
	 * @param \Viscacha\IO\Validate\RuleProcessor $context
	 * @return int
	 */
	public function int($data, RuleProcessor $context = null) {
		return intval($data);
	}
	
	/**
	 * Natural numbers (1,2,3,4,...), excluding 0.
	 * 
	 * @param mixed $data
	 * @param \Viscacha\IO\Validate\RuleProcessor $context
	 * @return int 
	 */
	public function natural($data, RuleProcessor $context = null) {
		$data = $this->int($data);
		return $data > 0 ? $data : 1;
	}
	
	/**
	 * Page number, similar to natural, but with an upper limit.
	 * 
	 * @param type $data
	 * @param type $maxPage
	 * @param \Viscacha\IO\Validate\RuleProcessor $context
	 * @return type
	 */
	public function pageNo($data, $maxPage, RuleProcessor $context = null) {
		$data = $this->natural($data);
		return $data <= $maxPage ? $data : $maxPage;
	}
	
	/**
	 * Setting a default value for empty values.
	 * 
	 * @see empty()
	 * @param mixed $data
	 * @param mixed $defaultValue
	 * @param \Viscacha\IO\Validate\RuleProcessor $context
	 * @return mixed
	 */
	public function preset($data, $defaultValue, RuleProcessor $context = null) {
		return !empty($data) ? $data : $defaultValue;
	}
	
	/**
	 * Sets null for empty values.
	 * 
	 * @param mixed $data
	 * @param \Viscacha\IO\Validate\RuleProcessor $context
	 * @return mixed
	 */
	public function nullable($data, RuleProcessor $context = null) {
		return !empty($data) ? $data : null;
	}
		
	
}
