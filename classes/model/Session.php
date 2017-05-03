<?php

namespace Viscacha\Model;

/**
 * Class Session
 */
class Session extends BaseModel {

	public function define() {
		$this->table = 'session';
		$this->columns = [
			'id',
			'mid',
			'active',
			'wiw_script',
			'wiw_action',
			'wiw_id',
			'ip',
			'user_agent',
			'lastvisit',
			'mark',
			'sid',
			'pwfaccess',
			'settings'
		];
		$this->foreignKeys = [
			'mid' => User::class
		];
	}

	public function user() {
		return $this->belongsTo('mid');
	}

}
