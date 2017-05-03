<?php

namespace Viscacha\Model;

/**
 * Class Package
 */
class Package extends BaseModel {

	public function define() {
		$this->table = 'packages';
		$this->columns = [
			'id',
			'title',
			'active',
			'version',
			'internal',
			'core'
		];
	}
	
	public function plugins() {
		return $this->hasMany(Plugin::class, 'module');
	}

}
