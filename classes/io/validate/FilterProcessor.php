<?php

namespace Viscacha\IO\Validate;

class FilterProcessor extends RuleProcessor {
	
	/**
	 * 
	 * @param array $rules
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $rules) {
		parent::__construct(new FilterRules(), $rules);
	}

}
