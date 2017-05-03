<?php

namespace Viscacha\Model;

/**
 * Class Flood
 */
class Flood extends BaseModel {

	public function define() {
		$this->table = 'flood';
		$this->columns = [
			'id',
			'ip',
			'mid',
			'time',
			'type'
		];
		$this->foreignKeys = [
			'mid' => User::class
		];
	}
	
	public function user() {
		return $this->belongsTo('mid');
	}

}
