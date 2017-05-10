<?php

namespace Viscacha\IO\Validate;

interface Rules {}

class RuleProcessor {

	protected $processors;
	protected $rules;
	private $rulesCache;
	protected $data;
	
	/**
	 * 
	 * @param \Viscacha\IO\Validate\Rules $object
	 * @param array $rules
	 */
	public function __construct(Rules $object, array $rules) {
		$this->addProcessorsFromObject($object);
		$this->setRules($rules);
	}
	
	/**
	 * Processes the data according to the specified rules using the processors.
	 * 
	 * @see Validator::parseRules()
	 * @param array $allRules
	 * @param array $data
	 * @return type
	 * @throws \InvalidArgumentException
	 */
	public function process(array $data) {
		$this->setData($data);
		foreach ($this->getRules() as $field => $rules) {
			foreach($rules as $meta) {
				if ($this->processRule($field, $meta) !== true) {
					break;
				}
			}
		}
		return $this->data;
	}
	
	protected function setData(array $data) {
		$this->data = array_merge($this->data, $data);
	}
	
	public function getData($field = null) {
		if ($field !== null) {
			return $this->data[$field];
		}
		else {
			return $this->data;
		}
	}
	
	public function addProcessorsFromObject(Rules $object) {
		$methods = get_class_methods($object);
		foreach ($methods as $method) {
			$this->processors[$method] = array($object, $method);
		}
	}
	
	public function addProcessor($name, $callback) {
		$this->processors[$name] = $callback;
	}
	
	public function getProcessors() {
		return $this->processors;
	}

	protected function processRule($field, RuleMeta $meta) {
		try {
			$this->data[$field] = $this->callProcess($meta, $this->data[$field]);
			if ($meta->stopOnSuccess) {
				return null;
			}
		} catch(\Exception $e) {
			if ($meta->stopOnError) {
				return false;
			}
		}
		return true;
	}
	
	protected function callProcess(RuleMeta $meta, $data) {
		$args = $meta->arguments;
		// Add the data to the arguments
		array_unshift($args, $data);
		// Add the context to the arguments
		array_push($args, $this);

		return call_user_func_array($this->processors[$meta->name], $args);
	}
	
	/**
	 * 
	 * @param array $rules
	 */
	protected function setRules(array $rules) {
		$this->rules = $rules;
		$this->data = array_fill_keys(array_keys($this->rules), null);
	}
	
	/**
	 * 
	 * @return type
	 * @throws \InvalidArgumentException
	 */
	protected function getRules() {
		if (empty($this->rulesCache)) {
			foreach ($this->rules as $field => $rulesLine) {
				$this->rulesCache[$field] = $this->parseRules($rulesLine);
			}
		}
		return $this->rulesCache;
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
	 * @throws \InvalidArgumentException
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
			if (!isset($this->processors[$meta->name])) {
				throw new \InvalidArgumentException("Non-existing processor name specified: '{$meta->name}'");
			}
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