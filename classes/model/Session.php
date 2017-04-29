<?php

namespace Viscacha\Model;

/**
 * Class Session
 */
class Session extends BaseModel {

	protected $table = 'session';
	protected $columns = [
		'id',
		'mid',
		'active',
		'wiw_script',
		'wiw_action',
		'wiw_id',
		'ip',
		'user_agent',
		'lastvisit',
		'mark',
		'sid',
		'pwfaccess',
		'settings'
	];

}
