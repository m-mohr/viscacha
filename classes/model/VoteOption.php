<?php

namespace Viscacha\Model;

/**
 * Class Vote
 */
class VoteOption extends BaseModel {

	public function define() {
		$this->table = 'vote';
		$this->columns = [
			'id',
			'tid',
			'answer'
		];
		$this->foreignKeys = [
			'tid' => Topic::class
		];
	}

	public function topic() {
		return $this->belongsTo('tid');
	}

	public function votes() {
		return $this->hasMany(Votes::class, 'aid');
	}

}
