<?php

namespace Viscacha\Model;

/**
 * Class Language
 */
class Language extends BaseModel {

	protected $table = 'language';
	protected $columns = [
		'id',
		'language',
		'detail',
		'publicuse'
	];

}
