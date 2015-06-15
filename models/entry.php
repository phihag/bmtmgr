<?php
namespace bmtmgr;

class Entry extends \bmtmgr\Model {
	public $id;
	public $discipline_id;
	public $player_id;
	public $player_club_id;
	public $partner_id;
	public $partner_club_id;
	public $email;
	public $created_time;
	public $updated_time;
	public $seeding;

	protected function __construct($row, $_is_new) {
		$this->id = $row['id'];
		$this->discipline_id = $row['discipline_id'];
		$this->player_id = $row['player_id'];
		$this->player_club_id = $row['player_club_id'];
		$this->partner_id = $row['partner_id'];
		$this->partner_club_id = $row['partner_club_id'];
		$this->email = $row['email'];
		$this->created_time = $row['created_time'];
		$this->updated_time = $row['updated_time'];
		$this->seeding = $row['seeding'];

		$this->_is_new = $_is_new;
	}

	public static function create($discipline, $player, $player_club, $partner, $partner_club, $email, $seeding) {
		// Call $discipline->check_entry to make sure everything is in order
		return new Entry([
			'id' => null,
			'discipline_id' => $discipline->id,
			'player_id' => $player->id,
			'player_club_id' => $player_club->id,
			'partner_id' => (($partner == null) ? null : $partner->id),
			'partner_club_id' => (($partner_club == null) ? null : $partner_club->id),
			'email' => $email,
			'created_time' => time(),
			'updated_time' => null,
			'seeding' => $seeding,
		], true);
	}

	public function get_discipline() {
		return Discipline::by_id($this->discipline_id);
	}

	public function get_player() {
		return Player::by_id($this->player_id);
	}

	public function get_partner() {
		if (! $this->partner_id) {
			return NULL;
		}
		return Player::by_id($this->partner_id);
	}

	public function get_player_club() {
		return User::by_id($this->player_club_id);
	}

	public function get_partner_club() {
		if (! $this->partner_club_id) {
			return NULL;
		}
		return User::by_id($this->partner_club_id);
	}

	public static function fetch_all_in_tournament($tournament_id) {
		return static::get_all(
			'WHERE entry.discipline_id = discipline.id
			       AND discipline.tournament_id=:tournament_id',
			[':tournament_id' => $tournament_id],
			['discipline']
		);
	}
}
