<?php
namespace bmtmgr\season;

class Season extends \bmtmgr\Model {
	public $id;
	public $name;
	public $visible;

	public function __construct($id, $name, $visible) {
		$this->id = $id;
		$this->name = $name;
		$this->visible = \boolval($visible);
	}
}

function create($name, $visible=false) {
	$s = $GLOBALS['db']->prepare('INSERT INTO season (name, visible) VALUES (?, ?)');
	try {
		$s->execute([$name, $visible]);
	} catch (\PDOException $pe) {
		if ($pe->getCode() == '23000') {
			throw new \bmtmgr\utils\DuplicateEntryException();
		} else {
			throw $pe;
		}
	}
	$id = $GLOBALS['db']->lastInsertId();
	return new Season($id, $name, $visible);
}
