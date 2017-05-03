<?php

namespace Viscacha\Model;

/**
 * Class DocumentsContent
 */
class DocumentTranslation extends BaseModel {

	public function define() {
		$this->table = 'documents_content';
		$this->columns = [
			'id',
			'did',
			'lid',
			'title',
			'content',
			'active'
		];
		$this->foreignKeys = [
			'did' => Document::class,
			'lid' => Language::class
		];
	}
	
	public function document() {
		return $this->belongsTo('did');
	}

	public function language() {
		return $this->belongsTo('lid');
	}

}
