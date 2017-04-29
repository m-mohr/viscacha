<?php

namespace Viscacha\Model;

/**
 * Class Group
 */
class Group extends BaseModel {

	protected $table = 'groups';
	protected $columns = [
		'id',
		'admin',
		'gmod',
		'guest',
		'members',
		'profile',
		'pm',
		'wwo',
		'search',
		'team',
		'usepic',
		'useabout',
		'usesignature',
		'downloadfiles',
		'forum',
		'posttopics',
		'postreplies',
		'addvotes',
		'attachments',
		'edit',
		'voting',
		'flood',
		'title',
		'name',
		'core'
	];

}
