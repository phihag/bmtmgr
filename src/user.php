<?php
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
}

function current_user($config, $db) {
	if (!isset($_COOKIE['login'])) {
		return null;
	}
	$s = $db->prepare('SELECT
			user.id AS id,
			user.name AS name,
			user.email AS email,
			user.permissions_json AS permissions_json
			FROM user, login_user_token
			WHERE user.id = login_user_token.user_id AND login_user_token = ?');
	$s->query(array($token));
	$rows = $s->fetchAll();
	if (count($rows) != 1) {
		return null;
	}
	$row = $rows[0];
	return new User($row['id'], $row['name'], $row['mail'], json_decode($row['permissions']));
}

function check_current_user($config, $db) {
	$user = current_user($config, $db);
	if (! $user) {
		render_login_form();
		exit();
	}
	return $user;
}

function user_find_by_input($input) {

}

function render_login_form() {
	render('login', array(
		'title' => 'Login'
	));
	exit();
}
