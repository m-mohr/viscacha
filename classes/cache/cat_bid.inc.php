<?php
class cache_cat_bid extends CacheItem {

	function load() {
		global $db, $scache, $config, $bbcode;
		if ($this->exists() == true) {
		    $this->import();
		}
		else {
			$categories_obj = $scache->load('categories');
			$cat_cache = $categories_obj->get();
		    $result = $db->query("
			SELECT id, name, parent, position, description, topics, replies, opt, optvalue, forumzahl, topiczahl, prefix, invisible, readonly, auto_status, active_topic, count_posts, reply_notification, topic_notification, message_active, message_title, message_text, lid, post_order
			FROM {$db->pre}forums
			");
		    $this->data = array();
		    while ($row = $db->fetch_assoc($result)) {
		    	$row['bid'] = $cat_cache[$row['parent']]['parent'];

				$emails = preg_split('/[\r\n]+/', $row['topic_notification'], -1, PREG_SPLIT_NO_EMPTY);
				$row['topic_notification'] = array();
				foreach ($emails as $email) {
					if(check_mail($email, true)) {
						$row['topic_notification'][] = $email;
					}
				}
				$emails = preg_split('/[\r\n]+/', $row['reply_notification'], -1, PREG_SPLIT_NO_EMPTY);
				$row['reply_notification'] = array();
				foreach ($emails as $email) {
					if(check_mail($email, true)) {
						$row['reply_notification'][] = $email;
					}
				}

				BBProfile($bbcode);
				$bbcode->setReplace($config['wordstatus']);
				$row['message_text'] = $bbcode->parse($row['message_text']);

				if ($row['post_order'] == -1) {
					$row['post_order'] = $config['post_order'];
				}

		        $this->data[$row['id']] = $row;
		    }
		    $this->export();
		}
	}

}
?>