<?php
namespace bmtmgr\utils;

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
		\bmtmgr\render('error', array(
			'title' => $title,
			'msg'=> 'Entschuldigung, bei dieser Anfrage ist etwas schief gelaufen: ' . $title .' . Bitte versuchen Sie die vorherige Seite neu zu laden'
		));
		exit();
	}
}

function endswith($haystack, $needle) {
	return substr($haystack, -strlen($needle)) === $needle;
}

function gen_token() {
	$bs = openssl_random_pseudo_bytes(64, $crypto_strong);
	if (! $crypto_strong) {
		throw new Exception('Cannot generate crypto token');
	}
	return substr(hash('sha512', $bs), 0, 16);
}

function root_path() {
	$res = \bmtmgr\config\get('root_path', null);
	if ($res !== null) {
		return $base_url;
	}
	if (($p = strpos($_SERVER['PHP_SELF'], '/bmtmgr/')) !== false) {
		return substr($_SERVER['PHP_SELF'], 0, $p + strlen('/bmtmgr/'));
	}
	return '';
}

function absolute_url() {
	$res = \bmtmgr\config\get('absolute_url_prefix', null);
	if ($res) {
		return $res;
	}

	$domain = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
	$port = $_SERVER['SERVER_PORT'];
	if (($_SERVER['HTTPS'] == 'on' && $port != 443) || ($_SERVER['HTTPS'] == '' && $port != 80)) {
		$domain .= ':' . $port;
	}
	$root_path = root_path();
	if (! endswith($root_path, '/')) {
		$root_path .= '/';
	}
	return $domain . $root_path;
}
