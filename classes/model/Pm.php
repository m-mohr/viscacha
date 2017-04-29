<?php

namespace Viscacha\Model;

/**
 * Class Pm
 */
class Pm extends BaseModel {

	protected $table = 'pm';
	protected $columns = [
		'id',
		'topic',
		'pm_from',
		'pm_to',
		'comment',
		'date',
		'status',
		'dir'
	];

}
