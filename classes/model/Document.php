<?php

namespace Viscacha\Model;

/**
 * Class Document
 */
class Document extends BaseModel {

	protected $table = 'documents';
	protected $columns = [
		'id',
		'author',
		'date',
		'update',
		'parser',
		'template',
		'groups',
		'icomment'
	];

}
