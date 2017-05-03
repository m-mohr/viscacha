<?php

namespace Viscacha\Model;

/**
 * Class Language
 */
class Language extends BaseModel {

	public function define() {
		$this->table = 'language';
		$this->columns = [
			'id',
			'language',
			'detail',
			'publicuse'
		];
	}

	public function documentTranslations() {
		return $this->hasMany(DocumentTranslation::class, 'lid');
	}

	public function users() {
		return $this->hasMany(User::class, 'language');
	}

}
