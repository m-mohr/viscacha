<?php

namespace Viscacha\IO\Validate;

interface Rules {}

abstract class RuleProcessor {
	
	private $ruleObject;
	protected $rules;
	
	public function __construct(Rules $ruleObject) {
		$this->ruleObject = $ruleObject;

		$methods = get_class_methods($this->ruleObject);
		foreach ($methods as $method) {
			$this->rules[$method] = array($this->ruleObject, $method);
		}
	}
	
	public function addRule($name, $validationCallback) {
		$this->rules[$name] = $validationCallback;
	}

	protected function callRule($callable, $data, $args) {
		if (is_array($args)) {
			array_unshift($args, $data);
			return call_user_func_array($callable, $args);
		}
		else {
			return call_user_func($callable, $data);
		}
	}
	
	/**
	 * A string of rules separates each rule with a "|".
	 * Optional arguments are added after a ":". 
	 * Multiple arguments are separated by a ",".
	 * 
	 * If you want to avoid a "|" to be used for splitting the rules, escape it using a "\".
	 * If you want to skip execution after an error at the following rule, prepend the rule with a "§".
	 * If you want to skip execution after a rule succeded, prepend the rule with a "!". "nullable" usually needs this to be useful.
	 * If you don't want the parser to split multiple arguments at a ",", use "::" instead of ":" as separator.
	 * 
	 * Example: "!nullable|required|§max:1|regexp::/,\|;/u"
	 * 
	 * @param type $lineWithRules
	 * @return array
	 */
	protected function parseRules($lineWithRules) {
		if (empty($lineWithRules)) {
			return array();
		}
		// Split by | - but not when escaped by a \
		$rules = preg_split('#(?<!\\\)\|#', $lineWithRules);
		$parsedRules = array();
		foreach ($rules as $rule) {
			$meta = new RuleMeta();
			$args = explode(':', $rule, 2);
			if (\Str::startsWith($args[0], '§')) {
				$meta->stopOnError = true;
				$args[0] = \Str::substr($args[0], 1);
			}
			if (\Str::startsWith($args[0], '!')) {
				$meta->stopOnSuccess = true;
				$args[0] = \Str::substr($args[0], 1);
			}
			$meta->name = $args[0];
			if (isset($args[1])) {
				$args[1] = str_replace('\|', '|', $args[1]); // Remove escape char from escaped pipes
				if (\Str::startsWith($args[1], ':')) {
					$meta->arguments = \Str::substr($args[1], 1);
				}
				$meta->arguments = explode(',', $args[1]);
			}
			$parsedRules[] = $meta;
		}
		return $parsedRules;
	}
	
}

class RuleMeta {
	
	public $name;
	public $arguments = array();
	public $stopOnError = false;
	public $stopOnSuccess = false;
	
}