<?php

namespace Viscacha\Model;

/**
 * Class Prefix
 */
class Prefix extends BaseModel {

	public function define() {
		$this->table = 'prefix';
		$this->columns = [
			'id',
			'bid',
			'value',
			'standard'
		];
		$this->foreignKeys = [
			'bid' => Forum::class
		];
	}

	public function getFormatted() {
		return '[' . $this->getValue() . ']';
	}

	public function topics() {
		return $this->hasMany(Topic::class, 'prefix');
	}

	public function forum() {
		return $this->belongsTo('bid');
	}

}
