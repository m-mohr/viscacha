<?php

namespace Viscacha\System\Cron\Jobs;

class DigestDaily implements \Viscacha\System\Cron\JobInterface {

	public function run($lastRunTime) {
		global $db, $lang;

		$lastdate = mktime(0, 0); // midnight today
		$lastdate -= 24 * 60 * 60; // yesterday midnight

		$result = $db->execute("
		SELECT t.id, t.board, t.topic, u.mail, u.name, u.language, l.name AS last_name
		FROM {$db->pre}abos AS a
			LEFT JOIN {$db->pre}user AS u ON u.id = a.mid
			LEFT JOIN {$db->pre}topics AS t ON t.id = a.tid
			LEFT JOIN {$db->pre}user AS l ON l.id = t.last_name
		WHERE a.type = 'd' AND t.last > '{$lastdate}' AND t.last_name != u.id
		");

		$lang_dir = $lang->getdir(true);

		while ($row = $result->fetch()) {
			$lang->setdir($row['language']);
			$lang->assign('row', $row);
			$data = $lang->get_mail('digest_d');
			$to = array('0' => array('name' => $row['name'], 'mail' => $row['mail']));
			$from = array();
			xmail($to, $from, $data['title'], $data['comment']);
		}

		$lang->setdir($lang_dir);
		return true;
	}

}
