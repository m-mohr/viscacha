if (!empty($_POST['Extended']) && $my->vlogin) {
	$data = array(
		'comment' => $_POST['comment'],
		'dosmileys' => $_POST['dosmileys'],
		'id' => $_POST['id'],
		'digest' => 0
	);
	$fid = save_error_data($data);
	$slog->updatelogged();
	sendStatusCode(302, $config['furl']."/addreply.php?id={$_POST['id']}&fid=".$fid.SID2URL_JS_x);
}