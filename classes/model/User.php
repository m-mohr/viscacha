<?php

namespace Viscacha\Model;

/**
 * Class User
 */
class User extends BaseModel {

	protected $table = 'user';
	protected $softDelete = 'deleted_at';
	protected $columns = [
		'id',
		'name',
		'pw',
		'mail',
		'regdate',
		'posts',
		'fullname',
		'hp',
		'signature',
		'about',
		'location',
		'gender',
		'birthday',
		'pic',
		'lastvisit',
		'timezone',
		'groups',
		'opt_pmnotify',
		'opt_hidemail',
		'opt_newsletter',
		'opt_showsig',
		'theme',
		'language',
		'confirm'
	];

}
