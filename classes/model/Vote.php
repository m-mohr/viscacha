<?php

namespace Viscacha\Model;

/**
 * Class Vote
 */
class Vote extends BaseModel {

	protected $table = 'votes';
	protected $columns = [
		'id',
		'mid',
		'aid'
	];

}
