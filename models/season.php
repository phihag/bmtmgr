<?php
namespace bmtmgr;

class Season extends \bmtmgr\Model {
	public $id;
	public $name;
	public $visible;
	public $baseurl;

	protected function __construct($row, $_is_new=true) {
		$this->id = $row['id'];
		$this->name = $row['name'];
		$this->visible = (bool) $row['visible'];
		$this->baseurl = $row['baseurl'];
		$this->_is_new = $_is_new;
	}

	public static function create($name, $visible, $baseurl) {
		return new static([
			'id' => null,
			'name' => $name,
			'visible' => $visible,
			'baseurl' => $baseurl,
		], true);
	}

	public function count_players() {
		$s = $GLOBALS['db']->query('SELECT COUNT(textid) as count FROM player WHERE season_id=?');
		$s->execute([$this->id]);
		$row = $s->fetch();
		return $row['count'];
	}

	public function get_player_rows_with_club_names($add_sql='') {
		return Player::get_rows_with_club_names(
				'AND player.season_id=? ' . $add_sql, [$this->id]);
	}

	public function get_tournaments($add_sql='') {
		return Tournament::get_all('WHERE season_id=? ' . $add_sql, [$this->id]);
	}

	public function get_player_by_input($input) {
		return Player::get_by_input(
			$input,
			' AND player.season_id=:season_id ',
			[':season_id' => $this->id]);
	}

	public function get_club_by_input($input) {
		return Club::find_by_input($input);
	}
}
