<?php
// Only needed for installations migrated from Viscacha 0.8
if (empty($_GET['id'])) {
	exit;
}
$id = intval($_GET['id']);
$page = (empty($_GET['page']) || $_GET['page'] < 2) ? 1 : intval($_GET['page']);

header("Status: 301 Moved Permanently");
header("Location: showtopic.php?id={$id}&page={$page}");
