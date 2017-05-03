<?php

namespace Viscacha\Model;

/**
 * Class Topic
 */
class Topic extends BaseModel {

	public function define() {
		$this->table = 'topics';
		$this->columns = [
			'id',
			'board',
			'topic',
			'prefix',
			'posts',
			'name',
			'date',
			'status',
			'last',
			'sticky',
			'last_name',
			'vquestion'
		];
		$this->foreignKeys = array(
			'board' => Forum::class,
			'prefix' => Prefix::class,
			'name' => User::class,
			'last_name' => User::class
		);
	}

	public function forum() {
		return $this->belongsTo('board');
	}

	public function prefix() {
		return $this->belongsTo('prefix');
	}

	public function author() {
		return $this->belongsTo('name');
	}

	public function lastUser() {
		return $this->belongsTo('last_name');
	}

	public function posts() {
		return $this->hasMany(Reply::class, 'topic_id');
	}

	public function subscriptions() {
		return $this->hasMany(Subsciption::class, 'tid');
	}

	public function uploads() { // ToDo: Remove with removal of uploads.topic_id column
		return $this->hasMany(Upload::class, 'topic_id');
	}

	public function voteOptions() {
		return $this->hasMany(VoteOption::class, 'tid');
	}

}
