<?php

namespace Viscacha\Model;

/**
 * Class Menu
 */
class Menu extends BaseModel {

	public function define() {
		$this->table = 'menu';
		$this->columns = [
			'id',
			'name',
			'link',
			'param',
			'groups', // ToDo: Relation from a set of values
			'position',
			'ordering',
			'sub',
			'module',
			'active'
		];
		$this->primaryKey = [
			'sub' => Menu::class,
			'module' => Plugin::class
		];
	}
	
	public function parentMenu() {
		return $this->belongsTo('sub');
	}
	
	public function hasMany() {
		return $this->hasMany(Menu::class, 'sub');
	}

	public function plugin() {
		return $this->belongsTo('module');
	}

}
