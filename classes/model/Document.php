<?php

namespace Viscacha\Model;

/**
 * Class Document
 */
class Document extends BaseModel {

	public function define() {
		$this->table = 'documents';
		$this->columns = [
			'id',
			'author',
			'date',
			'update',
			'parser',
			'template',
			'groups', // ToDo: Relation from a set of values
			'icomment'
		];
	}

	public function translations() {
		return $this->belongsTo('lid');
	}

}
