<?php
namespace bmtmgr;

class Tournament extends \bmtmgr\Model {
	public $id;
	public $season_id;
	public $name;
	public $description;
	public $start_timestamp;
	public $end_timestamp;
	public $visible;

	protected function __construct($row, $_is_new) {
		$this->id = $row['id'];
		$this->season_id = $row['season_id'];
		$this->name = $row['name'];
		$this->description = $row['description'];
		$this->start_timestamp = $row['start_timestamp'];
		$this->end_timestamp = $row['end_timestamp'];
		$this->visible = $row['visible'];

		$this->_is_new = $_is_new;
	}

	public static function create($season, $name) {
		return new static([
			'id' => null,
			'season_id' => $season->id,
			'name' => $name,
			'description' => null,
			'start_timestamp' => null,
			'end_timestamp' => null,
			'visible' => false,
		], true);
	}

	public function get_season() {
		return Season::by_id($this->season_id);
	}

	public function get_disciplines($add_sql='') {
		return Discipline::get_all('WHERE tournament_id=? ' . $add_sql, [$this->id]);
	}

}
