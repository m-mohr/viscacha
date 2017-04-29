<?php

namespace Viscacha\Model;

/**
 * Class Package
 */
class Package extends BaseModel {

	protected $table = 'packages';
	protected $columns = [
		'id',
		'title',
		'active',
		'version',
		'internal',
		'core'
	];

}
