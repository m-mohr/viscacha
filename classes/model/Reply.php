<?php

namespace Viscacha\Model;

/**
 * Class Reply
 */
class Reply extends BaseModel {

	public function define() {
		$this->table = 'replies';
		$this->columns = [
			'id',
			'topic',
			'topic_id',
			'name',
			'comment',
			'dosmileys',
			'ip',
			'date',
			'edit',
			'report',
			'tstart'
		];
		$this->foreignKeys = [
			'topic_id' => Topic::class,
			'name' => User::class
		];
	}

	public function topic() {
		return $this->belongsTo('topic_id');
	}

	public function user() {
		return $this->belongsTo('name');
	}

	public function uploads() {
		return $this->hasMany(Upload::class, 'tid');
	}

}
