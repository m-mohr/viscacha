<?php

namespace Viscacha\Model;

/**
 * Class Forum
 */
class Forum extends BaseModel {

	public function define() {
		$this->table = 'forums';
		$this->columns = [
			'id',
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
			'post_order'
		];
		$this->foreignKeys = array(
			'parent' => Category::class,
			'last_topic' => Topic::class
		);
	}

	public function category() {
		return $this->belongsTo('parent');
	}

	public function lastTopic() {
		return $this->belongsTo('last_topic');
	}

	public function topics() {
		return $this->hasMany(Topic::class, 'board');
	}

	public function moderators() {
		return $this->hasMany(Moderator::class, 'bid');
	}

	public function prefixes() {
		return $this->hasMany(Prefix::class, 'bid');
	}

	public function subcategories() {
		return $this->hasMany(Category::class, 'parent');
	}

	public function permissions() {
		return $this->hasMany(Fgroup::class, 'bid');
	}

	public function getTopicsPerPage() {
		global $config;
		if ($this->forumzahl > 0) {
			return $this->forumzahl;
		}
		return $config['forumzahl'];
	}

	public function getPostsPerPage() {
		global $config;
		if ($this->topiczahl > 0) {
			return $this->topiczahl;
		}
		return $config['topiczahl'];
	}

	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
/*	protected static function boot() {
		parent::boot();

		static::addGlobalScope('invisible', function (\Illuminate\Database\Eloquent\Builder $builder) {
			global $my;
			if (!isset($my->p['admin']) || $my->p['admin'] != 1) {
				$builder->where('invisible', '!=', 2);
			}
		});
	} */

}
