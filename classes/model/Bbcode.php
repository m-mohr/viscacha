<?php

namespace Viscacha\Model;

/**
 * Class Bbcode
 */
class Bbcode extends BaseModel {

	public function define() {
		$this->table = 'bbcode';
		$this->columns = [
			'id',
			'tag',
			'replacement',
			'example',
			'explanation',
			'twoparams',
			'title',
			'buttonimage'
		];
		$this->validationRules = [
			'tag' => '§required|maxLength:120|uniqueBbcode',
			'replacement' => 'required',
			'example' => 'required',
			'explanation' => '',
			'twoparams' => 'in:0,1',
			'title' => 'maxLength:255',
			'buttonimage' => '!nullable|maxLength:255|url'
		];
		$this->filterRules = [
			'tag' => 'str',
			'replacement' => 'str',
			'example' => 'str',
			'explanation' => 'str',
			'twoparams' => 'str|preset:0',
			'title' => 'str',
			'buttonimage' => 'str|nullable'
		];
	}
	
	public function defineCustomValidators() {
		global $lang;
		$this->getValidator()->addProcessor('uniqueBbcode', array($this, 'validateUniqueness'));
	}
	
	public function validateUniqueness($data, \Viscacha\IO\Validate\RuleProcessor $context = null) {
		$result = self::select()->where('tag', $data)->where('twoparams', $context->getData('twoparams'))->limit(1)->fetch();
		if ($result !== false) {
			global $lang;
			// ToDo: Make this phrase globally available
			$lang->assign('bbcodetag', $data);
			throw new \Exception($lang->phrase('admin_bbc_bbcode_already_exists')); 
		}
		return true;
	}

}
