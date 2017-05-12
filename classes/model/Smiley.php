<?php

namespace Viscacha\Model;

/**
 * Class Smiley
 */
class Smiley extends BaseModel {

	protected function define() {
		$this->table = 'smileys';
		$this->columns = [
			'id',
			'search',
			'replace',
			'desc',
			'show'
		];
		$this->validationRules = [
			'id' => '',
			'search' => '§required|§maxLength:120|unique',
			'replace' => 'required',
			'desc' => '',
			'show' => 'in:0,1'
		];
		$this->filterRules = [
			'id' => 'id',
			'search' => 'str',
			'replace' => 'str',
			'desc' => 'str',
			'show' => 'str|preset:0'
		];
	}

}
