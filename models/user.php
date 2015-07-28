<?php
namespace bmtmgr;

class User extends \bmtmgr\Model {
	public $id;
	public $name;
	public $email;
	protected $permissions_json;
	private $_perms;

	public function __construct($id, $name, $email, $perms, $_is_new=true) {
		$this->id = $id;
		$this->name = $name;
		$this->email = $email;
		$this->_perms = $perms;
		$this->permissions_json = \json_encode($this->_perms);
		$this->_is_new = $_is_new;
	}

	protected static function from_row($row, $_is_new=false) {
		return new static($row['id'], $row['name'], $row['email'], \json_decode($row['permissions_json']), $_is_new);
	}

	public static function find_by_input($input) {
		if (preg_match('/^\s*\((.*?)\)/', $input, $matches)) {
			return static::fetch_optional('WHERE id = ?', array($matches[1]));
		}

		return static::fetch_optional('WHERE id = ? OR name = ? COLLATE NOCASE', array($input, $input));
	}

	public static function create($textid, $name, $email, $perms=[]) {
		return new static($textid, $name, $email, $perms);
	}

	public function can($perm) {
		return in_array($perm, $this->_perms);
	}

	public function require_perm($perm) {
		if (! $this->can($perm)) {
			\bmtmgr\utils\access_denied();
		}
	}

	public function get_display_id() {
		return sprintf('(%s) %s', $this->id, $this->name);
	}
}
