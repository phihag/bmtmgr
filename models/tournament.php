<?php
namespace bmtmgr;

class Tournament extends \bmtmgr\Model {
	public $id;
	public $season_id;
	public $name;
	public $description;
	public $start_time;
	public $end_time;
	public $visible;

	protected function __construct($row, $_is_new) {
		$this->id = $row['id'];
		$this->season_id = $row['season_id'];
		$this->name = $row['name'];
		$this->description = $row['description'];
		$this->start_time = $row['start_time'];
		$this->end_time = $row['end_time'];
		$this->visible = $row['visible'];

		$this->_is_new = $_is_new;
	}

	public static function create($season, $name) {
		return new static([
			'id' => null,
			'season_id' => $season->id,
			'name' => $name,
			'description' => null,
			'start_time' => null,
			'end_time' => null,
			'visible' => false,
		], true);
	}

	public function get_season() {
		return Season::by_id($this->season_id);
	}

	public function get_disciplines($add_sql='') {
		return Discipline::get_all('WHERE tournament_id=? ' . $add_sql, [$this->id]);
	}

	public function get_disciplines_with_counts() {
		$sql = 'SELECT ' . Discipline::all_fields_str() . ', COUNT(entry.id) AS entry_count
			FROM discipline LEFT JOIN entry ON (discipline.id = entry.discipline_id)
			WHERE discipline.tournament_id = :tournament_id
			GROUP BY discipline.id
		';

		$rows = static::_fetch_all_rows($sql, [':tournament_id' => $this->id]);
		return $rows;
	}

	public function get_entries() {
		return Entry::fetch_all_in_tournament($this->id);
	}
}
