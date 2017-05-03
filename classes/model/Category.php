<?php

namespace Viscacha\Model;

/**
 * Class Category
 */
class Category extends BaseModel {

	public function define() {
		$this->table = 'categories';
		$this->columns = [
			'id',
			'name',
			'description',
			'parent',
			'position'
		];
		$this->foreignKeys = array(
			'parent' => Forum::class
		);
	}

	public function subforums() {
		return $this->hasMany(Forum::class, 'parent');
	}

	public function forum() {
		return $this->belongsTo('parent');
	}

}
