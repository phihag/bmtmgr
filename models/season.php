<?php
namespace bmtmgr;

class Season extends \bmtmgr\Model {
	public $id;
	public $name;
	public $visible;

	public function __construct($id, $name, $visible, $_is_new=true) {
		$this->id = $id;
		$this->name = $name;
		$this->visible = (bool) $visible;
		$this->_is_new = $_is_new;
	}

	protected static function from_row($row) {
		return new Season($row['id'], $row['name'], $row['visible'], false);
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
}
