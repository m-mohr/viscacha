<?php

namespace Viscacha\Model;

/**
 * Class Userfield
 */
class Userfield extends BaseModel {

	public function define() {
		$this->table = 'userfields';
		$this->primaryKey = 'ufid';
		$this->columns = ['ufid'];
		$this->foreignKeys = [
			'ufid' => User::class
		];
	}

	public function user() {
		return $this->belongsTo('ufid');
	}

}
