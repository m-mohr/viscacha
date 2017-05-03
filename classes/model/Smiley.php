<?php

namespace Viscacha\Model;

/**
 * Class Smiley
 */
class Smiley extends BaseModel {

	public function define() {
		$this->table = 'smileys';
		$this->columns = [
			'id',
			'search',
			'replace',
			'desc',
			'show'
		];
	}

}
