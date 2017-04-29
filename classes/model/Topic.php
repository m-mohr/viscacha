<?php

namespace Viscacha\Model;

/**
 * Class Topic
 */
class Topic extends BaseModel {

	protected $table = 'topics';
	protected $columns = [
		'board',
		'topic',
		'prefix',
		'posts',
		'name',
		'date',
		'status',
		'last',
		'sticky',
		'last_name',
		'vquestion'
	];

}
