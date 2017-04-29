<?php

namespace Viscacha\Model;

/**
 * Class Moderator
 */
class Moderator extends BaseModel {

	protected $table = 'moderators';
	protected $columns = [
		'id',
		'mid',
		'bid',
		'p_delete',
		'p_mc'
	];

}
