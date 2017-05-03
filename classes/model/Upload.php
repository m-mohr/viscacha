<?php

namespace Viscacha\Model;

/**
 * Class Upload
 */
class Upload extends BaseModel {

	function define() {
		$this->table = 'uploads';
		$this->columns = [
			'id',
			'tid',
			'topic_id', // ToDo: Check whether this column is really neccessary.
			'mid',
			'file',
			'source'
		];
		$this->foreignKeys = [
			'tid' => Reply::class,
			'topic_id' => Topic::class,
			'user' => User::class
		];
	}

	public function topic() { // ToDo: Remove with removal of topic_id column
		return $this->belongsTo('topic_id');
	}
	
	public function post() {
		return $this->belongsTo('tid');
	}

	public function user() {
		return $this->belongsTo('mid');
	}
	
}