<?php

namespace Viscacha\Model;

/**
 * Class Flood
 */
class Flood extends BaseModel {

	protected $table = 'flood';
	protected $columns = [
		'id',
		'ip',
		'mid',
		'time',
		'type'
	];

}
