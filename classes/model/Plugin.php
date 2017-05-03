<?php

namespace Viscacha\Model;

/**
 * Class Plugin
 */
class Plugin extends BaseModel {

	public function define() {
		$this->table = 'plugins';
		$this->columns = [
			'id',
			'name',
			'module',
			'ordering',
			'active',
			'position',
			'required'
		];
		$this->foreignKeys = [
			'module' => Package::class
		];
	}
	
	public function package() {
		return $this->belongsTo('module');
	}
	
	public function menus() {
		return $this->hasMany(Menu::class, 'module');
	}

}
