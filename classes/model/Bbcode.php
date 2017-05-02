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
	}

}
