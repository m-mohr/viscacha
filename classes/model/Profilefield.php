<?php

namespace Viscacha\Model;

/**
 * Class Profilefield
 */
class Profilefield extends BaseModel {

	protected $table = 'profilefields';
	protected $primaryKey = 'fid';
	protected $columns = [
		'fid',
		'name',
		'description',
		'disporder',
		'type',
		'length',
		'maxlength',
		'required',
		'editable',
		'viewable'
	];

}
