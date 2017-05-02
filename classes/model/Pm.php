<?php

namespace Viscacha\Model;

/**
 * Class Pm
 */
class Pm extends BaseModel {

	public function __construct($primaryKey = null) {
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
		parent::__construct($primaryKey);
	}
	
	public function scopeOnlyNew($query) {
		$query->where('status', 0);
		return $this;
	}
	
	public function author() {
		return $this->belongsTo(User::class, 'pm_from');
	}
	
	public function recipient() {
		return $this->belongsTo(User::class, 'pm_to');
	}

}
