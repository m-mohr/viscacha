<?php

namespace Viscacha\Model;

/**
 * Class Forum
 */
class Forum extends BaseModel {

	protected $table = 'forums';
	protected $columns = [
		'name',
		'description',
		'topics',
		'replies',
		'parent',
		'position',
		'last_topic',
		'count_posts',
		'opt',
		'optvalue',
		'forumzahl',
		'topiczahl',
		'prefix',
		'invisible',
		'readonly',
		'reply_notification',
		'topic_notification',
		'active_topic',
		'message_active',
		'message_title',
		'message_text',
		'lid',
		'post_order'
	];

}