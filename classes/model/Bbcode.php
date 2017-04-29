<?php

namespace Viscacha\Model;

/**
 * Class Bbcode
 */
class Bbcode extends BaseModel {

	protected $table = 'bbcode';
	protected $columns = [
		'id',
		'bbcodetag',
		'bbcodereplacement',
		'bbcodeexample',
		'bbcodeexplanation',
		'twoparams',
		'title',
		'buttonimage'
	];

}
