<?php

namespace Viscacha\Model;

/**
 * Class Category
 */
class Category extends BaseModel {

	protected $table = 'categories';
	protected $columns = [
		'id',
		'name',
		'description',
		'parent',
		'position'
	];

}
