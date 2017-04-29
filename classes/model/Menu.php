<?php

namespace Viscacha\Model;

/**
 * Class Menu
 */
class Menu extends BaseModel {

	protected $table = 'menu';
	protected $columns = [
		'id',
		'name',
		'link',
		'param',
		'groups',
		'position',
		'ordering',
		'sub',
		'module',
		'active'
	];

}
