<?php

namespace Viscacha\IO\Validate;

class FilterRules implements Rules {
	
	/**
	 * Strings (trimmed).
	 * 
	 * @param mixed $data
	 * @param \Viscacha\IO\Validate\RuleContext $context
	 * @return string
	 */
	public function str($data, RuleContext $context = null) {
		return trim($data);
	}
	
	/**
	 * Integers (...,-3,-2,-1,0,1,2,3,...)
	 * 
	 * @param mixed $data
	 * @param \Viscacha\IO\Validate\RuleContext $context
	 * @return int
	 */
	public function int($data, RuleContext $context = null) {
		return intval($data);
	}
	
	/**
	 * Natural numbers (1,2,3,4,...), excluding 0.
	 * 
	 * @param mixed $data
	 * @param \Viscacha\IO\Validate\RuleContext $context
	 * @return int 
	 */
	public function natural($data, RuleContext $context = null) {
		$data = $this->int($data);
		return $data > 0 ? $data : 1;
	}
	
	/**
	 * Checks for potential IDs (0,1,2,3,...) with 0 being no ID given.
	 * 
	 * @param mixed $data
	 * @param \Viscacha\IO\Validate\RuleContext $context
	 * @return int 
	 */
	public function id($data, RuleContext $context = null) {
		$data = $this->int($data);
		return $data >= 0 ? $data : 0;
	}
	
	/**
	 * Page number, similar to natural, but with an upper limit.
	 * 
	 * @param type $data
	 * @param type $maxPage
	 * @param \Viscacha\IO\Validate\RuleContext $context
	 * @return type
	 */
	public function pageNo($data, $maxPage, RuleContext $context = null) {
		$data = $this->natural($data);
		return $data <= $maxPage ? $data : $maxPage;
	}
	
	/**
	 * Setting a default value for empty values.
	 * 
	 * @see empty()
	 * @param mixed $data
	 * @param mixed $defaultValue
	 * @param \Viscacha\IO\Validate\RuleContext $context
	 * @return mixed
	 */
	public function preset($data, $defaultValue, RuleContext $context = null) {
		return !empty($data) ? $data : $defaultValue;
	}
	
	/**
	 * Sets null for empty values.
	 * 
	 * @param mixed $data
	 * @param \Viscacha\IO\Validate\RuleContext $context
	 * @return mixed
	 */
	public function nullable($data, RuleContext $context = null) {
		return !empty($data) ? $data : null;
	}
		
	
}
