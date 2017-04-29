<?php

namespace Viscacha\Model;

/**
 * Class DocumentsContent
 */
class DocumentsContent extends BaseModel {

	protected $table = 'documents_content';
	protected $columns = [
		'id',
		'did',
		'lid',
		'title',
		'content',
		'active'
	];

}
