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

	public static function fetch($where, $input, $add_tables=array()) {
		$s = $GLOBALS['db']->prepare('SELECT
				user.id AS id,
				user.name AS name,
				user.email AS email,
				user.permissions_json AS permissions_json
				FROM user ' . implode(',', $add_tables) . '
				WHERE ' . $where);
		$s->execute($input);
		$rows = $s->fetchAll();
		if (count($rows) != 1) {
			return null;
		}
		$row = $rows[0];
		return new User($row['id'], $row['name'], $row['email'], json_decode($row['permissions_json']));
	}
}

function current_user() {
	if (!isset($_COOKIE['login'])) {
		return null;
	}

	return User::fetch(
		'user.id = login_user_token.user_id AND login_user_token = ?',
		$_COOKIE['login'],
		array('login_user_token'));
}

function check_current() {
	$user = current_user();
	if (! $user) {
		render_login_form();
		exit();
	}
	return $user;
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
