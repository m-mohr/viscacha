<?php

namespace Viscacha\System\Cron\Jobs;

class ExportBoardStats implements \Viscacha\System\Cron\JobInterface {

	public function run($lastRunTime) {
		global $db, $config;

		$result = $db->execute("SELECT COUNT(*) FROM {$db->pre}topics");
		$topics = $result->fetchOne();

		$result = $db->execute("SELECT COUNT(*) FROM {$db->pre}replies");
		$posts = $result->fetchOne();

		$result = $db->execute("SELECT COUNT(*) FROM {$db->pre}user WHERE deleted_at IS NULL AND confirm = '11'");
		$members = $result->fetchOne();

		include("language/{$config['langdir']}/settings.lng.php");
		$lngc = $lang['lang_code'];
		if (!empty($lang['country_code'])) {
			$lngc .= '_' . $lang['country_code'];
		}

		$data = array(
			'settings' => array(
				'url' => $config['furl'],
				'name' => $config['fname'],
				'description' => $config['fdesc'],
				'version' => $config['version'],
				'language' => $lngc
			),
			'statistics' => array(
				'topics' => intval($topics),
				'posts' => intval($posts),
				'members' => intval($members)
			)
		);

		if (!file_exists('feeds/')) {
			mkdir('feeds/');
		}
		$myini = new \INI();
		$myini->write('feeds/board_data.ini', $data);
		return true;
	}

}
