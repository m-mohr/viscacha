<?php

namespace Viscacha\Model;

/**
 * Class Reply
 */
class Reply extends BaseModel {

	protected $table = 'replies';
	protected $columns = [
		'id',
		'topic',
		'topic_id',
		'name',
		'comment',
		'dosmileys',
		'ip',
		'date',
		'edit',
		'report',
		'tstart'
	];

}
