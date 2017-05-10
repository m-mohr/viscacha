<?php

namespace Viscacha\IO\Validate;

class Validator extends RuleProcessor {

	protected $errors;
	
	/**
	 * 
	 * @param array $rules
	 * @throws \InvalidArgumentException
	 */
	public function __construct(array $rules) {
		parent::__construct(new ValidatorRules(), $rules);
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
	public function process(array $data) {
		$this->errors = array();
		return parent::process($data);
	}

	protected function processRule($field, RuleMeta $meta) {
		try {
			$result = $this->callProcess($meta, $this->data[$field]);
			if (!$result) {
				throw new InvalidDataException($field, $meta->name, $meta->arguments);
			}
			else if ($result && $meta->stopOnSuccess) {
				return null;
			}
		} catch(\Exception $e) {
			$this->errors[$field][] = $e->getMessage();
			if ($meta->stopOnError) {
				return false;
			}
		}
		return true;
	}
	
	public function validate(array $data) {
		$this->process($data);
		return empty($this->errors);
	}
	
}