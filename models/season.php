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

	public function get_players_with_clubs($add_sql='') {
		return Player::get_all(
			'WHERE player.season_id=? AND user.id=player.club_id ' . $add_sql, [$this->id],
			['user'],
			'user.name AS club_name',
			function($row) {
				$p = Player::from_row($row);
				$p->club = new User($row['club_id'], $row['club_name'], null, null, 'dontsave');
				return $p;
			});
	}
}
