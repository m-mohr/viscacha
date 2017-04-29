<?php

namespace Viscacha\Model;

/**
 * Class Fgroup
 */
class Fgroup extends BaseModel {

	protected $table = 'fgroups';
	protected $primaryKey = 'fid';
	protected $columns = [
		'id',
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

}
