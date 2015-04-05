<?php
namespace bmtmgr;

class Player extends \bmtmgr\Model {
	public $id;
	public $season_id;
	public $club_id;
	public $textid;
	public $name;
	public $gender;
	public $birth_year;
	public $nationality;

	public function __construct($row, $_is_new=true) {
		$this->id = $row['id'];
		$this->season_id = $row['season_id'];
		$this->club_id = $row['club_id'];
		$this->textid = $row['textid'];
		$this->name = $row['name'];
		$this->gender = $row['gender'];
		$this->birth_year = $row['birth_year'];
		$this->nationality = $row['nationality'];
		$this->_is_new = $_is_new;
	}

	protected static function from_row($row) {
		return new static($row, false);
	}

	public static function exists($season, $textid) {
		return static::fetch_optional('WHERE season_id=? AND textid=?', [$season->id, $textid]) !== null;
	}
}
