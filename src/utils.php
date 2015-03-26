<?php

csrf_token();
csrf_token();

function csrf_token() {
	if (isset($_COOKIE['csrf_token']) && strlen($_COOKIE['csrf_token']) >= 8) {
		return $_COOKIE['csrf_token'];
	}

	static $token = null;
	static $first_run = true;
	if ($first_run) {
		$token = gen_token();
		setcookie('csrf_token', $token, time() + 10 * 360 * 24 * 60 * 60);
	}
	$first_run = false;
	return $token;
}

function csrf_protect() {
	$title = false;
	if (!isset($_COOKIE['csrf_token'])) {
		$title = 'Sicherheitstoken nicht erstellt';
	} elseif (((!isset($_POST['csrf_token'])) || strlen($_COOKIE['csrf_token']) < 8)) {
		$title = 'Sicherheitstoken fehlte';
	} else if ($_COOKIE['csrf_token'] != $_POST['csrf_token']) {
		$title = 'Falsches Sicherheitstoken';
	}

	if ($title !== false) {
		header('HTTP/1.1 400 Bad Request');
		render('error', array(
			'title' => $title,
			'msg'=> 'Entschuldigung, bei dieser Anfrage ist etwas schief gelaufen: ' . $title .' . Bitte versuchen Sie die vorherige Seite neu zu laden'
		));
		exit();
	}
}

function gen_token() {
	$bs = openssl_random_pseudo_bytes(64, $crypto_strong);
	if (! $crypto_strong) {
		throw new Exception('Cannot generate crypto token');
	}
	return hash('sha512', $bs);
}