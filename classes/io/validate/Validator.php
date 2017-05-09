<?php

namespace Viscacha\IO\Validate;

class Validator extends RuleProcessor {

	protected $errors;
	
	public function __construct() {
		parent::__construct(new ValidatorRules());
	}
	
	public function getGroupedErrors() {
		return $this->errors;
	}
	
	public function getErrors($field = null) {
		if ($field !== null) {
			return isset($this->errors[$field]) ? $this->errors[$field] : false;
		}
		else {
			// Flatten array to remove field grouping
			return call_user_func_array('array_merge', $this->errors);
		}
	}
	
	/**
	 * Validate data.
	 * 
	 * @see Validator::parseRules()
	 * @param array $allRules
	 * @param array $data
	 * @return type
	 * @throws \InvalidArgumentException
	 */
	public function validate(array $allRules, array $data) {
		$this->errors = array();
		foreach ($allRules as $field => $lineWithRules) {
			if (!isset($data[$field])) {
				$data[$field] = null;
			}
			$rules = $this->parseRules($lineWithRules);
			foreach($rules as $meta) {
				if (!isset($this->rules[$meta->name])) {
					throw new \InvalidArgumentException('Non-existing rule name specified.');
				}
				if (!$this->callRule($this->rules[$meta->name], $data[$field], $meta->arguments)) {
					$this->errors[$field][] = $e->getMessage();
					if ($meta->stopOnError) {
						break;
					}
				}
				else if ($meta->stopOnSuccess) {
					break;
				}
			}
		}
		return empty($this->errors);
	}
	
}