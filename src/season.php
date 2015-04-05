<?php
namespace bmtmgr\season;

class Season extends \bmtmgr\Model {
	public $id;
	public $name;
	public $visible;

	public function __construct($id, $name, $visible) {
		$this->id = $id;
		$this->name = $name;
		$this->visible = (bool) $visible;
	}

	protected static function from_row($row) {
		return new Season($row['id'], $row['name'], $row['visible']);
	}
}
