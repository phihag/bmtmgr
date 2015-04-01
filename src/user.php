<?php
namespace bmtmgr\user;

require_once __DIR__ . '/render.php';


class User {
	public $id;
	public $name;
	public $email;
	private $perms;

	public function __construct($id, $name, $email, $perms) {
		$this->id = $id;
		$this->name = $name;
		$this->email = $email;
		$this->perms = $perms;
	}

	public function can($perm) {
		return in_array($perm, $this->perms);
	}

	public function require_perm($perm) {
		if (! $this->can($perm)) {
			\bmtmgr\utils\access_denied();
		}
	}

	public static function fetch($where, $input, $add_tables=array()) {
		$from_tables = implode(',', $add_tables);
		if ($from_tables) {
			$from_tables .= ',';
		}
		$from_tables .= 'user';
		$sql = ('SELECT
			user.id AS id,
			user.name AS name,
			user.email AS email,
			user.permissions_json AS permissions_json
			FROM ' . $from_tables . '
			WHERE ' . $where);
		$s = $GLOBALS['db']->prepare($sql);
		$s->execute($input);
		$rows = $s->fetchAll();
		if (count($rows) != 1) {
			return null;
		}
		$row = $rows[0];
		return new User($row['id'], $row['name'], $row['email'], json_decode($row['permissions_json']));
	}
}

function find_by_token($table, $token) {
	$now = time();
	return User::fetch(
		'user.id = ' . $table . '.user_id AND ' . $table . '.token = ? AND ' . $table . '.expiry_time > ?',
		array($token, $now),
		array($table)
	);
}

function current_user() {
	if (!isset($_COOKIE['login_token'])) {
		return null;
	}

	return find_by_token('login_cookie_token', $_COOKIE['login_token']);
}

function check_current() {
	$user = current_user();
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

function find_by_input($input) {
	if (preg_match('/^\s*\((.*?)\)/', $input, $matches)) {
		return User::fetch('id = ?', array($matches[1]));
	}

	return User::fetch('id = ? OR name = ? OR email = ?', array($input, $input, $input));
}

function render_login_form() {
	\bmtmgr\render('login', array(
		'title' => 'Login'
	));
	exit();
}
