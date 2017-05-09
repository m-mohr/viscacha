<?php

namespace Viscacha\IO\Validate;

class FilterPreProcessor extends RuleProcessor {
	
	public function __construct() {
		parent::__construct(new FilterRules());
	}
	
	/**
	 * Pre-processes data.
	 * 
	 * @see Validator::parseRules()
	 * @param array $allRules
	 * @param array $data
	 * @return type
	 * @throws \InvalidArgumentException
	 */
	public function process(array $allRules, array $data) {
		$this->errors = array();
		$filteredData = array();
		foreach ($allRules as $field => $lineWithRules) {
			if (!isset($data[$field])) {
				$data[$field] = null;
			}
			$rules = $this->parseRules($lineWithRules);
			foreach($rules as $meta) {
				if (!isset($this->rules[$meta->name])) {
					throw new \InvalidArgumentException('Non-existing rule name specified.');
				}
				$filteredData[$field] = $this->callRule($this->rules[$meta->name], $data[$field], $meta->arguments);
			}
		}
		return $filteredData;
	}

}
