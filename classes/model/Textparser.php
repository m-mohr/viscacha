<?php

namespace Viscacha\Model;

/**
 * Class Textparser
 */
class Textparser extends BaseModel {

	public function define() {
		$this->table = 'textparser';
		$this->columns = [
			'id',
			'search',
			'replace'
		];
	}

}
