<?php
namespace bmtmgr;

class Discipline extends \bmtmgr\Model {
	public $id;
	public $tournament_id;
	public $name;
	public $dtype;
	public $ages;
	public $leagues;
	public $note;
	public $capacity;

	protected function __construct($row, $_is_new) {
		$this->id = $row['id'];
		$this->tournament_id = $row['tournament_id'];
		$this->name = $row['name'];
		$this->dtype = $row['dtype'];
		$this->ages = $row['ages'];
		$this->leagues = $row['leagues'];
		$this->note = $row['note'];
		$this->capacity = $row['capacity'];

		$this->_is_new = $_is_new;
	}

	public function player_gender() {
		if ($this->allow_any()) {
			return 'a';
		}
		return $this->male_player() ? 'm': 'f';
	}

	public function allow_any() {
		return \in_array($this->dtype, ['AS', 'AD', 'AA']);
	}

	public function male_player() {
		return \in_array($this->dtype, ['MS', 'MD', 'MX']);
	}

	public function is_mixed() {
		return $this->dtype == 'MX';
	}

	public function partner_gender() {
		switch ($this->dtype) {
		case 'MS':
		case 'WS':
		case 'AS': // Gender doesn't matter
			return null;
		case 'MD':
			return 'm';
		case 'WD':
		case 'MX':
			return 'f';
		case 'AD':
			return 'a';
		case 'AA': // No restrictions on players
			return 'a';
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
			'capacity' => null,
			'note' => null,
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
				'player' => ($row['player_id'] ? $player_dict[$row['player_id']] : null),
				'player_club' => ($row['player_club_id'] ? $club_dict[$row['player_club_id']] : null),
				'player_club_is_special' => ($row['player_club_id'] ? ($row['player_club_id'] != $player_dict[$row['player_id']]->club_id) : null),
				'partner' => ($row['partner_id'] ? $player_dict[$row['partner_id']] : null),
				'partner_club' => ($row['partner_club_id'] ? $club_dict[$row['partner_club_id']] : null),
				'partner_club_is_special' => (($row['partner_id'] && $row['partner_club_id']) ? ($row['partner_club_id'] != $player_dict[$row['partner_id']]->club_id) : null),
				'same_club' => ($row['partner_club_id'] ? $row['player_club_id'] == $row['partner_club_id'] : null),
				'email' => $row['email'],
				'seeding' => $row['seeding'],
				'created_time_str' => \date('Y-m-d H:i', \intval($row['created_time'])),
			];
		});
		$space_size = ((($this->capacity === NULL) || (\count($res) <= $this->capacity)) ?
			(\strlen(\sprintf('%d', \count($res)))) :
			(\strlen(\sprintf('%d+%d', $this->capacity, \count($res) - $this->capacity)))
		);
		for ($i = 0;$i < \count($res);$i++) {
			$res[$i]['on_waiting_list'] = ($this->capacity !== NULL) && ($i >= $this->capacity);
			$res[$i]['numstr'] = ($res[$i]['on_waiting_list'] ?
				\sprintf('%d+%d', $this->capacity, $i - $this->capacity + 1)
				: \strval($i + 1)
			);
			$res[$i]['numstr_spaced'] = \str_pad($res[$i]['numstr'], $space_size, ' ', \STR_PAD_LEFT);
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
		if (($player == null) && ($partner == null)) {
			throw new utils\InvalidEntryException('Kein Spieler angegeben!');
		}
		if ($player != null) {
			if ($player->gender != $this->player_gender()) {
				if (! $this->allow_any()) {
					if ($partner) {
						throw new utils\InvalidEntryException('Falsches Geschlecht des ersten Spielers');
					} else {
						throw new utils\InvalidEntryException('Falsches Geschlecht des Spielers');
					}
				}
			}
		}
		if ($partner != null) {
			if ((! $this->with_partner())) {
				throw new utils\InvalidEntryException('Partner in einer Einzeldiziplin angegeben');
			}
			if ($player != null) {
				if ($player->id == $partner->id) {
					throw new utils\InvalidEntryException('Ein Spieler kann nicht mit sich selber im Doppel antreten. Feld freilassen für eine Freimeldung.');
				}
			}
			if (! $this->allow_any()) {
				if ($partner->gender != $this->partner_gender()) {
					throw new utils\InvalidEntryException('Falsches Geschlecht des zweiten Spielers');
				}
			}
		}

		if ($player != null) {
			$conflicting = static::find_conflicting($player->id);
			if ($conflicting) {
				throw new utils\InvalidEntryException(sprintf('%s ist in diesem Turnier schon in %s angemeldet!', $player->name, $conflicting->name));
			}
		}
		if ($partner != null) {
			$conflicting = static::find_conflicting($partner->id);
			if ($conflicting) {
				throw new utils\InvalidEntryException(sprintf('%s ist in diesem Turnier schon in %s angemeldet!', $partner->name, $conflicting->name));
			}
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
				  	AND entry.player_id IS NOT NULL
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

	public static function guess_dtype($player, $partner) {
		if ($partner === null) {
			return $player->gender == 'm' ? 'MS' : 'WS';
		}
		if (($player->gender == 'm') && ($partner->gender == 'f')) {
			return 'MX';
		}
		if (($player->gender == 'f') && ($partner->gender == 'm')) {
			return 'MX';
		}
		if (($player->gender == 'm') && ($partner->gender == 'm')) {
			return 'MD';
		}
		if (($player->gender == 'f') && ($partner->gender == 'f')) {
			return 'WD';
		}
		return 'AD';
	}
}
