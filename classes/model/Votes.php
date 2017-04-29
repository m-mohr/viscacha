<?php

namespace Viscacha\Model;

/**
 * Class Vote
 */
class Vote extends BaseModel {

	protected $table = 'vote';
	protected $columns = [
		'tid',
		'answer'
	];

}
