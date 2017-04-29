<?php

namespace Viscacha\Model;

/**
 * Class Smiley
 */
class Smiley extends BaseModel {

	protected $table = 'smileys';
	protected $columns = [
		'id',
		'search',
		'replace',
		'desc',
		'show'
	];

}
