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
			'tag' => 'required|maxLength:120',
			'replacement' => 'required',
			'example' => 'required',
			'explanation' => '',
			'twoparams' => 'in:0,1',
			'title' => 'maxLength:255',
			'buttonimage' => '!empty|url|maxLength:255'
		];
		$this->filterRules = [
			'tag' => 'str',
			'replacement' => 'str',
			'example' => 'str',
			'explanation' => 'str',
			'twoparams' => 'string:0',
			'title' => 'str',
			'buttonimage' => 'strNull'
		];
	}

}
