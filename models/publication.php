<?php
namespace bmtmgr;

class Publication extends Model {
	public $id;
	public $tournament_id;
	public $ptype;
	public $config;

	protected function __construct($row, $_is_new=true) {
		$this->id = $row['id'];
		$this->tournament_id = $row['tournament_id'];
		$this->ptype = $row['ptype'];
		$this->config = $row['config'];
		$this->_is_new = $_is_new;
	}

	public static function create($tournament, $ptype, $config) {
		return new static([
			'id' => null,
			'tournament_id' => $tournament->id,
			'ptype' => $ptype,
			'config' => $config,
		], true);
	}

	public function get_tournament() {
		return Tournament::by_id($this->tournament_id);
	}
}
