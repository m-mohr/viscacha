<?php

namespace Viscacha\Model;

/**
 * Class Moderator
 */
class Moderator extends BaseModel {

	public function define() {
		$this->table = 'moderators';
		$this->columns = [
			'id',
			'mid',
			'bid',
			'p_delete',
			'p_mc'
		];
		$this->foreignKeys = [
			'mid' => User::class,
			'bid' => Forum::class
		];
	}
	
	public function user() {
		return $this->belongsTo('mid');
	}
	
	public function forum() {
		return $this->belongsTo('bid');
	}

}
