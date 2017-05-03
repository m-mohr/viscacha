<?php

namespace Viscacha\Model;

/**
 * Class SettingsGroup
 */
class SettingsGroup extends BaseModel {

	public function define() {
		$this->table = 'settings_groups';
		$this->columns = [
			'id',
			'title',
			'name',
			'description'
		];
	}

	public function settings() {
		return $this->hasMany(Setting::class, 'sgroup');
	}

}
