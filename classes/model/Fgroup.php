<?php

namespace Viscacha\Model;

/**
 * Class Fgroup
 */
class Fgroup extends BaseModel {

	public function define() {
		$this->table = 'fgroups';
		$this->primaryKey = 'fid';
		$this->columns = [
			'fid',
			'f_downloadfiles',
			'f_forum',
			'f_posttopics',
			'f_postreplies',
			'f_addvotes',
			'f_attachments',
			'f_edit',
			'f_voting',
			'gid',
			'bid'
		];
		$this->foreignKeys = [
			'gid' => Group::class,
			'bid' => Forum::class
		];
	}

	public function group() {
		return $this->belongsTo('gid');
	}

	public function forum() {
		return $this->belongsTo('bid');
	}

}
