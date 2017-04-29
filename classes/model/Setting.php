<?php

namespace Viscacha\Model;

/**
 * Class Setting
 */
class Setting extends BaseModel {

	protected $table = 'settings';
	protected $columns = [
		'id',
		'name',
		'title',
		'description',
		'type',
		'optionscode',
		'value',
		'sgroup'
	];

}
