<?php

namespace Viscacha\Model;

/**
 * Class Profilefield
 */
class Profilefield extends BaseModel {

	public function define() {
		$this->table = 'profilefields';
		$this->primaryKey = 'fid';
		$this->columns = [
			'fid',
			'name',
			'description',
			'disporder',
			'type',
			'length',
			'maxlength',
			'required',
			'editable',
			'viewable'
		];
	}

}
