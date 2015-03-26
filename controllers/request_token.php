<?php

require_once dirname(__DIR__) . '/src/common.php';
require_once dirname(__DIR__) . '/src/utils.php';
require_once dirname(__DIR__) . '/src/user.php';

csrf_protect();
if (!isset($_POST['user'])) {
	die('missing user POST parameter');
}

$u = user_find_by_input($_POST['user']);
if (!$u) {
	header('HTTP/1.1 404 Not Found');
	render('error', array(
		'title' => 'Benutzer nicht gefunden',
		'msg'=> 'Benutzer "' . $_POST['user'] . '" konnte nicht gefunden werden.')
	);
	exit();
} else {
	$s = $pdo->pepare('INSERT INTO login_email_token (token, user_id, request_time, expiry_time) VALUES(?, ?, ?, ?)');
}

