<?php

namespace Viscacha\Model;

/**
 * Class Plugin
 */
class Plugin extends BaseModel {

	protected $table = 'plugins';
	protected $columns = [
		'id',
		'name',
		'module',
		'ordering',
		'active',
		'position',
		'required'
	];

}
