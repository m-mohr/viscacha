<?php

namespace Viscacha\Model;

/**
 * Class Textparser
 */
class Textparser extends BaseModel {

	protected $table = 'textparser';
	protected $columns = [
		'id',
		'search',
		'replace'
	];

}
