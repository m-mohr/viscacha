if (!empty($_POST['Extended']) && $my->vlogin) {
	$data = array(
		'topic' => $_POST['topic'],
		'comment' => $_POST['comment'],
		'dosmileys' => $_POST['dosmileys'],
		'dowords' => $_POST['dowords'],
		'id' => $_POST['id'],
		'digest' => 0,
		'guest' => 0,
		'human' => false,
		'name' => null,
		'email' => null
	);
	$fid = save_error_data($data);
	sendStatusCode(307, $config['furl']."/addreply.php?id={$_POST['id']}&fid=".$fid.SID2URL_JS_x);
	exit;
}