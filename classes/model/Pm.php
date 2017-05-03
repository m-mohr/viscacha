<?php

namespace Viscacha\Model;

/**
 * Class Pm
 */
class Pm extends BaseModel {

	public function define() {
		$this->table = 'pm';
		$this->columns = [
			'id',
			'topic',
			'pm_from',
			'pm_to',
			'comment',
			'date',
			'status',
			'dir'
		];
		$this->foreignKeys = [
			'pm_from' => User::class,
			'pm_to' => User::class
		];
	}

	public function scopeOnlyNew($query) {
		$query->where('status', 0);
		return $this;
	}

	public function author() {
		return $this->belongsTo('pm_from');
	}

	public function recipient() {
		return $this->belongsTo('pm_to');
	}

}
