<?php

namespace Viscacha\Model;

/**
 * Class Vote
 */
class Votes extends BaseModel {

	public function define() {
		$this->table = 'votes';
		$this->columns = [
			'id',
			'mid',
			'aid'
		];
		$this->foreignKeys = [
			'mid' => User::class,
			'aid' => VoteOption::class
		];
	}
	
	public function user() {
		return $this->belongsTo('mid');
	}

	public function voteOptions() {
		return $this->belongsTo('aid');
	}

}
