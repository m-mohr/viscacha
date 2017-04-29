<?php

namespace Viscacha\Model;

/**
 * Class Upload
 */
class Upload extends BaseModel {

	protected $table = 'uploads';
	protected $columns = [
		'id',
		'tid',
		'topic_id',
		'mid',
		'file',
		'source'
	];

}
