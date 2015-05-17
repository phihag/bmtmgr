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
		\bmtmgr\render('error', [
			'title' => $title,
			'msg'=> 'Entschuldigung, bei dieser Anfrage ist etwas schief gelaufen: ' . $title . '. Bitte versuchen Sie die vorherige Seite neu zu laden.'
		]);
		exit();
	}
}

function require_params($keys, $ar, $name) {
	$missing = [];
	foreach ($keys as $k) {
		if (! \array_key_exists($k, $ar)) {
			\array_push($missing, $k);
		}
	}
	if (\count($missing) > 0) {
		\header('HTTP/1.1 400 Bad Request');
		\bmtmgr\render('error', [
			'title' => 'Fehler: ' . $name . '-Parameter fehlt',
			'msg' => 'Entschuldigung, wir haben die ' . $name . '-Parameter ' . \implode(', ', $missing) . ' im vorherigem Formular vergessen.'
		]);
		exit();
	}
}

function require_post_params($keys) {
	require_params($keys, $_POST, 'POST');
}

function require_get_params($keys) {
	require_params($keys, $_GET, 'GET');
}

function startswith($haystack, $needle) {
	return \substr($haystack, 0, \strlen($needle)) === $needle;
}

function endswith($haystack, $needle) {
	return \substr($haystack, -\strlen($needle)) === $needle;
}

function url_basename($url) {
	if (!preg_match('/.*?\/([^\/]+)(?:\/?$|[?#])/', $url, $matches)) {
		throw new \Exception('Invalid URL ' . $url);
	}
	return $matches[1];
}

function strip_ext($name) {
	$basename = basename($name);
	return preg_replace('/\.[^.]+$/', '', $name);
}


function gen_token() {
	$bs = openssl_random_pseudo_bytes(64, $crypto_strong);
	if (! $crypto_strong) {
		throw new \Exception('Cannot generate crypto token');
	}
	return \substr(\hash('sha512', $bs), 0, 24);
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
	$https = \array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'on';
	if (($https && $port != 443) || (!$https && $port != 80)) {
		$domain .= ':' . $port;
	}
	$root_path = root_path();
	if (! endswith($root_path, '/')) {
		$root_path .= '/';
	}
	return $domain . $root_path;
}

function access_denied() {
	header('HTTP/1.1 403 Forbidden');
	\bmtmgr\render('error', [
		'title' => 'Zugriff verweigert.',
		'msg' => 'Entschuldigung, der vorherige Link war fehlerhaft. Sie haben leider keinen Zugriff auf diese Seite.'
	]);
	exit();
}

class DuplicateEntryException extends \Exception {
	// Name or ID is already in use
}

class InvalidEntryException extends \Exception {
	// Entry not allowed
}

function array_filter_keys($ar, $callback) {
	$keys = \array_filter(\array_keys($ar), $callback);
	return \array_intersect_key($ar, \array_flip($keys));
}

function sanitize_filename($input) {
	return \preg_replace('/[^äÄöÖüÜßa-zA-Z 0-9_.-]/', '', $input);
}

function html_id($name) {
	return \preg_replace('/\s/', '_', sanitize_filename($name));
}