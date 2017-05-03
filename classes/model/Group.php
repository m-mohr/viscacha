<?php

namespace Viscacha\Model;

/**
 * Class Group
 */
class Group extends BaseModel {

	public function define() {
		$this->table = 'groups';
		$this->columns = [
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

	public function forumPermissions() {
		return $this->hasMany(Fgroup::class, 'gid');
	}

}
