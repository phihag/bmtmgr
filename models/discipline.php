<?php
namespace bmtmgr;

class Discipline extends \bmtmgr\Model {
	public $id;
	public $tournament_id;
	public $name;
	public $dtype;
	public $ages;
	public $leagues;

	protected function __construct($row, $_is_new) {
		$this->id = $row['id'];
		$this->tournament_id = $row['tournament_id'];
		$this->name = $row['name'];
		$this->dtype = $row['dtype'];
		$this->ages = $row['ages'];
		$this->leagues = $row['leagues'];

		$this->_is_new = $_is_new;
	}

	public function player_gender() {
		return $this->male_player() ? 'm': 'f';
	}

	public function male_player() {
		return in_array($this->dtype, ['MS', 'MD', 'MX']);
	}

	public function partner_gender() {
		switch ($this->dtype) {
		case 'MS':
		case 'WS':
			return null;
		case 'MD':
			return 'm';
		case 'WD':
		case 'MX':
			return 'f';
		default:
			\assert(false);
		}
	}

	public function male_partner() {
		return in_array($this->dtype, ['MD']);
	}

	public function with_partner() {
		return in_array($this->dtype, ['MD', 'WD', 'MX']);
	}

	public function entry_player_count() {
		return $this->with_partner() ? 2 : 1;
	}

	public static function create($tournament, $name, $dtype) {
		return new static([
			'id' => null,
			'tournament_id' => $tournament->id,
			'name' => $name,
			'dtype' => $dtype,
			'ages' => null,
			'leagues' => null,
		], true);
	}

	public function get_tournament() {
		return Tournament::by_id($this->tournament_id);
	}

	public function get_entries($add_sql='', $creation_callback=null) {
		return Entry::get_all('WHERE entry.discipline_id=? ' . $add_sql, [$this->id], [], '', $creation_callback);
	}

	public function get_entry_rows() {
		$players = Player::get_all(
			' WHERE entry.discipline_id = ? AND (entry.player_id = player.id OR entry.partner_id = player.id)',
			[$this->id],
			['entry']
		);
		$player_dict = [];
		foreach ($players as $p) {
			$player_dict[$p->id] = $p;
		}

		$clubs = User::get_all(
			' WHERE entry.discipline_id = ? AND (entry.player_club_id = user.id OR entry.partner_club_id = user.id)',
			[$this->id],
			['entry']
		);
		$club_dict = [];
		foreach ($clubs as $c) {
			$club_dict[$c->id] = $c;
		}

		$res = static::get_entries(' ORDER by entry.id', function($row) use ($player_dict, $club_dict) {
			return [
				'id' => $row['id'],
				'player' => $player_dict[$row['player_id']],
				'player_club' => $club_dict[$row['player_club_id']],
				'player_club_is_special' => $row['player_club_id'] != $player_dict[$row['player_id']]->club_id,
				'partner' => ($row['partner_id'] ? $player_dict[$row['partner_id']] : null),
				'partner_club' => ($row['partner_club_id'] ? $club_dict[$row['partner_club_id']] : null),
				'partner_club_is_special' => (($row['partner_id'] && $row['partner_club_id']) ? ($row['partner_club_id'] != $player_dict[$row['partner_id']]->club_id) : null),
				'same_club' => ($row['partner_club_id'] ? $row['player_club_id'] == $row['partner_club_id'] : null),
				'email' => $row['email']
			];
		});
		for ($i = 0;$i < \count($res);$i++) {
			$res[$i]['numstr'] = \strval($i + 1);
		}
		return $res;
	}

	protected function find_conflicting($player_id) {
		$vals = [
			':dtype' => $this->dtype,
			':tournament_id' => $this->tournament_id,
			':player_id' => $player_id,
		];
		$all_conflicting = Discipline::get_all(
			'WHERE
			 entry.discipline_id = discipline.id AND
			 discipline.dtype = :dtype AND
			 discipline.tournament_id = :tournament_id AND
			 (entry.player_id = :player_id OR entry.partner_id = :player_id)
			 ',
			$vals,
			['entry']
		);
		if (\count($all_conflicting) > 0) {
			return $all_conflicting[0];
		}
		return null;
	}

	public function check_entry($player, $partner) {
		if ($player == null) {
			throw new utils\InvalidEntryException('Erster Spieler fehlt!');
		}
		if ($player->gender != $this->player_gender()) {
			if ($partner) {
				throw new utils\InvalidEntryException('Falsches Geschlecht des ersten Spielers');
			} else {
				throw new utils\InvalidEntryException('Falsches Geschlecht des Spielers');
			}
		}
		if ($partner != null) {
			if ((! $this->with_partner())) {
				throw new utils\InvalidEntryException('Partner in einer Einzeldiziplin angegeben');
			}
			if ($player->id == $partner->id) {
				throw new utils\InvalidEntryException('Ein Spieler kann nicht mit sich selber im Doppel antreten. Feld freilassen für eine Freimeldung.');
			}
			if ($partner->gender != $this->partner_gender()) {
				throw new utils\InvalidEntryException('Falsches Geschlecht des zweiten Spielers');
			}
		}

		$conflicting = static::find_conflicting($player->id);
		if ($conflicting) {
			throw new utils\InvalidEntryException(sprintf('%s ist in diesem Turnier schon in %s angemeldet!', $player->name, $conflicting->name));
		}
		$conflicting = static::find_conflicting($partner->id);
		if ($conflicting) {
			throw new utils\InvalidEntryException(sprintf('%s ist in diesem Turnier schon in %s angemeldet!', $partner->name, $conflicting->name));
		}
	}

	public static function suggest_player_rows_with_clubs_by_id($discipline_id, $term, $gender, $add_sql='') {
		$vals = [
			':gender' => $gender,
			':discipline_id' => $discipline_id,
			':term_search_textid' => '%' . $term . '%',
			':term_search' => '%' . $term . '%',
		];

		$reverse_term = '';
		$p = \strpos($term, ' ');
		if ($p !== false) {
			$reverse_term = \substr($term, $p + strlen(' ')). '%,%' . \substr($term, 0, $p);
			$vals[':reverse_term_search'] = '%' . $reverse_term . '%';
		}
		$term = \str_replace(' ', '%', \trim($term));

		$sql = ' AND player.gender = :gender
			  AND (
				player.name LIKE :term_search
				OR player.textid LIKE :term_search_textid
				' . ($reverse_term ? ' OR player.name LIKE :reverse_term_search' : '') . '
			  )
			  AND discipline.id = :discipline_id
			  AND discipline.tournament_id = tournament.id
			  AND tournament.season_id = player.season_id
			  AND player.id NOT IN (
			  		SELECT entry.player_id AS pid
				  	FROM entry, discipline AS d1, discipline as d2
				  	WHERE d1.id = :discipline_id
				  	AND ((d1.tournament_id = d2.tournament_id) AND (d1.dtype = d2.dtype))
				  	AND entry.discipline_id = d2.id
			  	UNION
					SELECT entry.partner_id AS pid
				  	FROM entry, discipline AS d1, discipline as d2
				  	WHERE d1.id = :discipline_id
				  	AND ((d1.tournament_id = d2.tournament_id) AND (d1.dtype = d2.dtype))
				  	AND entry.discipline_id = d2.id
				  	AND entry.partner_id IS NOT NULL
			  )
			  ' . $add_sql;
		return Player::get_rows_with_club_names(
			$sql, $vals,
			['discipline', 'tournament']);
	}
}
