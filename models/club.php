<?php
namespace bmtmgr;

class Club extends \bmtmgr\Model {
	public $id;
	public $textid;
	public $name;

	public function __construct($row, $_is_new=true) {
		$this->id = $row['id'];
		$this->textid = $row['textid'];
		$this->name = $row['name'];
		$this->_is_new = $_is_new;
	}

	public static function find_by_input($input) {
		if (preg_match('/^\s*\((.*?)\)/', $input, $matches)) {
			return static::fetch_optional('WHERE textid = ?', array($matches[1]));
		}

		return static::fetch_optional('WHERE textid = ? OR name = ? COLLATE NOCASE', array($input, $input));
	}

	public static function create($textid, $name) {
		return new static([
			'id' => null,
			'textid' => $textid,
			'name' => $name,
		]);
	}

	public function get_display_id() {
		return sprintf('(%s) %s', $this->id, $this->name);
	}
}
