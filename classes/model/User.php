<?php

namespace Viscacha\Model;

/**
 * Class User
 */
class User extends BaseModel {

	public function define() {
		$this->table = 'user';
		$this->softDelete = 'deleted_at';
		$this->columns = [
			'id',
			'name',
			'pw',
			'mail',
			'regdate',
			'posts',
			'fullname',
			'hp',
			'signature',
			'about',
			'location',
			'gender',
			'birthday',
			'pic',
			'lastvisit',
			'timezone',
			'groups', // ToDo: Relation from a set of values
			'opt_pmnotify',
			'opt_hidemail',
			'opt_newsletter',
			'opt_showsig',
			'theme',
			'language',
			'confirm'
		];
		$this->foreignKeys = [
			'language' => Language::class
		];
	}
	
	public function language() {
		return $this->belongsTo('language');
	}

	public function session() {
		return $this->hasOne(Session::class, 'mid');
	}

	public function fields() {
		return $this->hasOne(Userfield::class, 'ufid');
	}

	public function pm() {
		return $this->hasMany(Pm::class, 'pm_to');
	}

	public function sentPm() {
		return $this->hasMany(Pm::class, 'pm_from');
	}

	public function subscriptions() {
		return $this->hasMany(Subscription::class, 'tid');
	}

	public function topics() {
		return $this->hasMany(Topic::class, 'name');
	}

	public function replies() {
		return $this->hasMany(Reply::class, 'name');
	}

	public function floods() {
		return $this->hasMany(Flood::class, 'mid');
	}

	public function moderator() {
		return $this->hasMany(Moderator::class, 'mid');
	}

	public function uploads() {
		return $this->hasMany(Upload::class, 'mid');
	}
	
	public function moderatingForums() {
		return $this->moderators()->forum();
	}

}
