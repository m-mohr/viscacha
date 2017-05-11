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
			'tag' => '§required|maxLength:120|unique2',
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
	
	public function validateUnique2($data, \Viscacha\IO\Validate\RuleContext $context) {
		$query = self::select()->where('tag', $data)->where('twoparams', $context->data['twoparams'])->limit(1);
		if ($this->id > 0) {
			$query->where('id', '!=', $this->id);
		}
		$result = $query->fetch();
		if ($result !== false) {
			global $lang;
			// ToDo: Make this phrase globally available
			$lang->assign('bbcodetag', $data);
			throw new \Viscacha\IO\Validate\InvalidDataException($context->field, 'unique2', array(), $lang->phrase('admin_bbc_bbcode_already_exists'));
		}
		return true;
	}

}
