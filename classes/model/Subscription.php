<?php

namespace Viscacha\Model;

/**
 * Class Subscription
 */
class Subscription extends BaseModel {
	
	protected $table = 'abos';
	protected $columns = [
		'id',
		'mid',
		'tid',
		'type'
	];
	protected $foreignKeys = array(
		'mid' => User::class,
		'tid' => Topic::class
	);

}