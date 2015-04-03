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

/**
* Return all seasons, including invisible ones
*/
function get_all() {
	$s = $GLOBALS['db']->prepare('SELECT id, name, visible FROM season ORDER BY name DESC');
	$s->execute();
	return \array_map(function($row) {
		return new Season($row['id'], $row['name'], $row['visible']);
	}, $s->fetchAll());
}

function by_id($id) {
	$s = $GLOBALS['db']->prepare('SELECT id, name, visible FROM season WHERE id=?');
	$s->execute([$id]);
	$row = $s->fetch();
	return new Season($row['id'], $row['name'], $row['visible']);
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
