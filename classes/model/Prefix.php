<?php

namespace Viscacha\Model;

/**
 * Class Prefix
 */
class Prefix extends BaseModel {

	protected $table = 'prefix';
	protected $columns = [
		'bid',
		'value',
		'standard'
	];
}
