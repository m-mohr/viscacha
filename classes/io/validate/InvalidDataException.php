<?php

namespace Viscacha\IO\Validate;

class InvalidDataException extends \Exception {
	
	public $field;
	public $rule;
	public $arguments;
	
	public function __construct($field, $rule = null, array $arguments = array(), $message = "") {
		parent::__construct($message, 0, null);
		$this->field = $field;
		$this->rule = $rule;
		$this->arguments = $arguments;
		if (empty($message)) {
			$this->localizeMessage();
		}
	}
	
	protected function localizeMessage() {
		// ToDo: Make these phrases available in both frontend and backend/admin.
		if (!empty($this->rule)) {
			global $lang;
			$phraseName = "rule_{$this->rule}";
			if ($lang->exists($phraseName)) {
				$lang->massAssign($this->arguments);
				$this->message = $lang->phrase($phraseName);
				return;
			}
			
			$phraseNameAlt = "rule_{$this->rule}_{$this->field}";
			if ($lang->exists($phraseNameAlt)) {
				$lang->massAssign($this->arguments);
				$this->message = $lang->phrase($phraseNameAlt);
				return;
			}
		}
		
		// ToDo: Change back to phrases
		$this->message = "{$this->field}: {$this->rule}";//$lang->phrase('query_string_error');
	}
	
}
