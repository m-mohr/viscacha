<?php

namespace Viscacha\Model;

/**
 * Class User
 */
class User extends BaseModel {

	public function __construct($primaryKey = null) {
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
			'groups',
			'opt_pmnotify',
			'opt_hidemail',
			'opt_newsletter',
			'opt_showsig',
			'theme',
			'language',
			'confirm'
		];
		$this->belongsTo = array(
			'mid' => User::class,
			'tid' => Topic::class
		);
		parent::__construct($primaryKey);
	}
	
	function pm() {
		return $this->hasMany(Pm::class, 'pm_to');
	}
	
	function sentPm() {
		return $this->hasMany(Pm::class, 'pm_from');
	}
	
	function userfields() {
		return $this->hasOne(Userfield::class, 'ufid');
	}
	
	function subscriptions() {
		return $this->hasMany(Subscription::class, 'tid');
	}
	
	function topics() {
		return $this->hasMany(Topic::class, 'name');
	}
	
	function replies() {
		return $this->hasMany(Reply::class, 'name');
	}

}
