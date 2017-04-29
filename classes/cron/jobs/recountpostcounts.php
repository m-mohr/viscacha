<?php
global $db, $config, $scache;

if ($config['updatepostcounter'] == 0) {

	$jobData = intval($jobData);

	$cat_bid_obj = $scache->load('cat_bid');
	$boards = $cat_bid_obj->get();
	$id = array();
	foreach ($boards as $board) {
		if ($board['count_posts'] == 0) {
			$id[] = $board['id'];
		}
	}

	$result = $db->execute("
		SELECT r.name
		FROM {$db->pre}replies AS r
			LEFT JOIN {$db->pre}topics AS t ON r.topic_id = t.id
		WHERE r.date > '{$jobData}'". iif(count($id) > 0, " AND t.board NOT IN (".implode(',', $id).")") ."
		GROUP BY r.name
	");

	while ($row = $result->fetch()) {
		UpdateMemberStats($row['name']);
	}
}

$jobData = time();

?>