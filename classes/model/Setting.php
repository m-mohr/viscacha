<?php

namespace Viscacha\Model;

/**
 * Class Setting
 */
class Setting extends BaseModel {

	public function define() {
		$this->table = 'settings';
		$this->columns = [
			'id',
			'name',
			'title',
			'description',
			'type',
			'optionscode',
			'value',
			'sgroup'
		];
		$this->foreignKeys = [
			'sgroup' => SettingsGroup::class
		];
	}
	
	public function group() {
		return $this->belongsTo('sgroup');
	}

}
