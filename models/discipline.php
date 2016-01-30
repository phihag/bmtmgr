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

	public function player_specs() {
		$g1 = $this->player_gender();
		$spec1 = self::gender2spec($g1, ['required' => ! $this->is_mixed(), 'name' => 'player0']);
		if ($this->player_count() === 1) {
			return [$spec1];
		}

		$spec2 = self::gender2spec($this->partner_gender(), ['name' => 'player1', 'required' => false]);
		if ($this->player_count() === 2) {
			return [$spec1, $spec2];
		} else {
			$res = [$spec1, $spec2];
			$pcount = $this->player_count() - 2;
			for ($i = 2;$i < $pcount;$i++) {
				$spec2['name'] = 'player' . $i;
				\array_push($res, $spec2);
			}
			return $res;
		}
	}

	protected static function gender2spec($g, $add=null) {
		switch ($g) {
		case 'a':
			$res = [
				'spec_any' => true,
			];
			break;
		case 'm':
			$res = [
				'spec_male' => true,
			];
			break;
		case 'f':
			$res = [
				'spec_female' => true,
			];
			break;
		default:
			\assert(false);
		}

		$res['gender'] = $g;
		if ($add) {
			$res = \array_merge($res, $add);
		}
		return $res;
	}

	public function is_mixed() {
		return $this->dtype === 'MX';
	}

	public function player_count() {
		switch ($this->dtype) {
		case 'MS':
		case 'WS':
		case 'AS':
			return 1;
		case 'MD':
		case 'WD':
		case 'AD':
		case 'MX':
			return 2;
		case 't5':
			return 10;
		default:
			\assert(false);
		}
	}

	public function player_gender() {
		switch ($this->dtype) {
		case 'MX':
		case 'MD':
		case 'MS':
			return 'm';
		case 'WD':
		case 'WS':
			return 'f';
		case 't5':
		case 'AD':
		case 'AS':
			return 'a';
		default:
			\assert(false);
		}
	}

	public function partner_gender() {
		switch ($this->dtype) {
		case 'MD':
			return 'm';
		case 'MX':
		case 'WD':
			return 'f';
		case 't5':
		case 'AD':
			return 'a';
		case 'AS':
		case 'WS':
		case 'MS':
			return null;
		default:
			\assert(false);
		}
	}

	public function is_team() {
		return $this->dtype === 't5';
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
		$db_rows = self::sql('
			SELECT
				entry.id AS id,
				entry.created_time AS created_time,
				entry.email AS email,
				entry.seeding AS seeding,
				entry.entry_name AS entry_name,
				' . Player::all_fields_str(true) . ',
				' . Club::all_fields_str(true) . '
			FROM entry
			INNER JOIN entry_player ON entry.id = entry_player.entry_id
			INNER JOIN player ON player.id = entry_player.player_id
			INNER JOIN club ON entry_player.club_id = club.id
			WHERE entry.discipline_id = :discipline_id
			ORDER BY entry.position, entry.id, entry_player.position
		', [
			':discipline_id' => $this->id,
		]);

		$cur_id = null;
		$cur = null;
		$res = [];
		foreach ($db_rows as $dbr) {
			$club_id = null;
			if ($dbr['id'] !== $cur_id) {
				$cur_id = $dbr['id'];
				$club_id = $dbr['club_id'];
				$res[] = [
					'id' => $dbr['id'],
					'players' => [],
					'created_time_str' => \date('Y-m-d H:i', \intval($dbr['created_time'])),
					'email' => $dbr['email'],
					'seeding' => $dbr['seeding'],
					'same_club' => true,
					'entry_name' => $dbr['entry_name'],
				];
			}
			$p = Player::from_prefixed_row($dbr, 'player_');
			$club = Club::from_prefixed_row($dbr, 'club_');
			$p->entry_club = $club;
			$p->entry_club_is_special = $p->club_id !== $club->id;
			if ($club->id !== $club_id) {
				$res[\count($res) - 1]['same_club'] = false;
			}
			$res[\count($res) - 1]['players'][] = $p;
		}

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
			 (entry.player_id = :player_id OR entry.TODO_partner_id = :player_id)
			 ',
			$vals,
			['entry']
		);
		if (\count($all_conflicting) > 0) {
			return $all_conflicting[0];
		}
		return null;
	}

	public function check_entry($players) {
		if (\count($players) === 0) {
			throw new utils\InvalidEntryException('Kein Spieler angegeben!');
		}
		if ($this->is_team()) {
			return;
		}

		$specs = $this->player_specs();
		if (\count($players) > \count($specs)) {
			throw new utils\InvalidEntryException('Zu viele Spieler - erwarte maximal ' . $this->player_count());
		}

		for ($i = 0;$i < \count($players);$i++) {
			// TODO: match spec
		}
	}

	public static function suggest_player_rows_with_clubs_by_id($discipline_id, $term, $gender, $add_sql='') {
		$vals = [
			':discipline_id' => $discipline_id,
			':term_search_textid' => '%' . $term . '%',
			':term_search' => '%' . $term . '%',
		];
		if ($gender !== 'a') {
			$vals[':gender'] = $gender;
		}

		$reverse_term = '';
		$p = \strpos($term, ' ');
		if ($p !== false) {
			$reverse_term = \substr($term, $p + strlen(' ')). '%,%' . \substr($term, 0, $p);
			$vals[':reverse_term_search'] = '%' . $reverse_term . '%';
		}
		$term = \str_replace(' ', '%', \trim($term));

		$sql = ' 
			' . (($gender === 'a') ? '' : 'AND player.gender = :gender') . '
			  AND (
				player.name LIKE :term_search
				OR player.textid LIKE :term_search_textid
				' . ($reverse_term ? ' OR player.name LIKE :reverse_term_search' : '') . '
			  )
			  AND discipline.id = :discipline_id
			  AND discipline.tournament_id = tournament.id
			  AND tournament.season_id = player.season_id
			  AND player.id NOT IN (
			  		SELECT entry_player.player_id
				  	FROM entry_player, entry
				  	WHERE entry.discipline_id = :discipline_id AND entry_player.entry_id = entry.id
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

	public function pseudo_entry() {
		return Entry::pseudo_entry($this->player_specs());
	}
}
