<?php

namespace Viscacha\Model;

/**
 * Class Subscription
 */
class Subscription extends BaseModel {

	public function define() {
		$this->table = 'abos';
		$this->columns = [
			'id',
			'mid',
			'tid',
			'type'
		];
		$this->foreignKeys = [
			'mid' => User::class,
			'tid' => Topic::class
		];
	}

	public function user() {
		return $this->belongsTo('mid');
	}

	public function topic() {
		return $this->belongsTo('tid');
	}

	public function prefix() {
		return $this->topic()->prefix();
	}

	public function forum() {
		return $this->topic()->forum();
	}

	/**
	 * Scope a query to only include daily mail subscriptions.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeOfUser($query, $userId) {
		return $query->where('mid', $userId);
	}

	/**
	 * Scope a query to only include daily mail subscriptions.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeDailyMail($query) {
		return $query->where('type', 'd');
	}

	/**
	 * Scope a query to only include instantly sent mail subscriptions.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeInstantMail($query) {
		return $query->where('type', 's');
	}

	/**
	 * Scope a query to only include instant forum notifications.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeForumNotification($query) {
		return $query->where('type', 'f');
	}

}
