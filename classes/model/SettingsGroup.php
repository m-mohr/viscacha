<?php

namespace Viscacha\Model;

/**
 * Class SettingsGroup
 */
class SettingsGroup extends BaseModel {

	protected $table = 'settings_groups';
	protected $columns = [
		'id',
		'title',
		'name',
		'description'
	];

}
