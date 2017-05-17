<?php
namespace bmtmgr\user;

require_once __DIR__ . '/render.php';

function find_by_token($table, $token) {
	$now = time();
	return \bmtmgr\User::fetch_optional(
		'WHERE user.id = ' . $table . '.user_id AND ' . $table . '.token = ? AND ' . $table . '.expiry_time > ?',
		array($token, $now),
		array($table)
	);
}

function get_current() {
	$token = (
		isset($_GET['login_token']) ? $_GET['login_token'] : (
			isset($_COOKIE['login_token']) ? $_COOKIE['login_token'] : null));
	if (! $token) {
		return null;
	}

	return find_by_token('login_cookie_token', $token);
}

function check_current() {
	$user = get_current();
	if (! $user) {
		render_login_form();
		exit();
	}
	return $user;
}

function create_session($u) {
	// Create and set up a session 
	$s = $GLOBALS['db']->prepare('INSERT INTO login_cookie_token
		(token, user_id, request_time, expiry_time)
		VALUES(?, ?, ?, ?);');
	$token = \bmtmgr\utils\gen_token();
	$request_time = time();
	$session_length = \bmtmgr\config\get('session_token_timeout', 10 * 360 * 24 * 60 * 60);
	$expiry_time = $request_time + $session_length;
	$s->execute(array($token, $u->id, $request_time, $expiry_time));
	setcookie(
		'login_token', $token, $expiry_time,
		'/', false,
		\bmtmgr\config\get('force_https', false), true);
}

function delete_session() {
	$token = $_COOKIE['login_token'];
	$s = $GLOBALS['db']->prepare('UPDATE login_cookie_token
		SET expiry_time = 0
		WHERE token=?');
	$s->execute([$token]);
}

function render_login_form() {
	header('HTTP/1.1 403 Forbidden');
	\bmtmgr\render('login', array(
		'title' => 'Login'
	));
	exit();
}
