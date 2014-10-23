<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

global $db, $config, $scache;

if ($config['updatepostcounter'] == 0) {

	$jobData = intval($jobData);

	$cat_bid_obj = $scache->load('cat_bid');
	$boards = $cat_bid_obj->get();
	$id = array()
	foreach ($boards as $board) {
		if ($board['count_posts'] == 0) {
			$id[] = $board['id'];
		}
	}

	$result = $db->query("
		SELECT name
		FROM {$db->pre}replies
		WHERE guest = '0' AND date > '{$jobData}'". iif(count($id) > 0, " AND board NOT IN (".implode(',', $id).")") ."
		GROUP BY name
	");

	while ($row = $db->fetch_assoc($result)) {
		UpdateMemberStats($row['name']);
	}
}

$jobData = time();

?>