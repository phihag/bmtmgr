<?php
namespace bmtmgr;

class Entry extends \bmtmgr\Model {
	public $id;
	public $discipline_id;
	public $entry_name;
	public $email;
	public $created_time;
	public $updated_time;
	public $seeding;
	public $memo;

	// From the intermediate entry_player table
	public $_players;
	public $_clubs;

	// Specifications for this entry, used to check it. Not mapped to the database
	public $_specs;

	protected function __construct($row, $_is_new) {
		$this->id = $row['id'];
		$this->discipline_id = $row['discipline_id'];
		$this->entry_name = $row['entry_name'];
		$this->email = $row['email'];
		$this->created_time = $row['created_time'];
		$this->updated_time = $row['updated_time'];
		$this->seeding = $row['seeding'];
		$this->memo = $row['memo'];
		$this->_players = array_key_exists('_players', $row) ? $row['_players'] : null;
		$this->_clubs = array_key_exists('_clubs', $row) ? $row['_clubs'] : null;

		$this->_is_new = $_is_new;
	}

	public static function create($discipline, $entry_name, $players, $clubs, $email, $seeding, $memo) {
		// Call $discipline->check_entry to make sure everything is in order
		$e = new Entry([
			'id' => null,
			'discipline_id' => $discipline->id,
			'entry_name' => $entry_name,
			'email' => $email,
			'created_time' => time(),
			'updated_time' => null,
			'seeding' => $seeding,
			'memo' => $memo,
			'_players' => $players,
			'_clubs' => $clubs,
		], true);
		return $e;
	}

	public function save() {
		if (($this->_players === null) || ($this->_clubs === null)) {
			throw new \Exception('Cannot save: No clubs or no players specified.');
		}

		if ($this->id) {
			static::sql(
				'DELETE FROM entry_player WHERE entry_id = :entry_id',
				[':entry_id' => $this->id]);
		}

		parent::save();

		$s = static::prepare('INSERT INTO entry_player
			(entry_id, player_id, club_id, position)
			VALUES (:entry_id, :player_id, :club_id, :position);');
		for ($i = 0;$i < \count($this->_players);$i++) {
			$player = $this->_players[$i];
			$club = $this->_clubs[$i];
			$s->execute([
				':entry_id' => $this->id,
				':player_id' => $player->id,
				':club_id' => ($club ? $club->id : $player->club_id),
				':position' => $i,
			]);
		}
	}

	public function get_discipline() {
		return Discipline::by_id($this->discipline_id);
	}

	public static function get_all($add_sql='', $add_params=[], $add_tables=[], $add_fields='', $creation_callback=null) {
		$res = parent::get_all($add_sql, $add_params, $add_tables, $add_fields, $creation_callback);
		foreach ($res as &$entry) {
			$entry->_players = Player::get_all(
				'WHERE entry_player.entry_id = :entry_id AND entry_player.player_id = player.id ORDER by entry_player.position',
				[':entry_id' => $entry->id],
				['entry_player']
			);
			$entry->_clubs = Club::get_all(
				'WHERE entry_player.entry_id = :entry_id AND entry_player.club_id = club.id ORDER by entry_player.position',
				[':entry_id' => $entry->id],
				['entry_player']
			);
		}
		return $res;
	}

	/**
	* Create a pseudo entry just for the specs.
	*/
	public static function pseudo_entry($specs) {
		$e = new Entry([
			'id' => null,
			'discipline_id' => null,
			'entry_name' => null,
			'email' => null,
			'created_time' => null,
			'updated_time' => null,
			'seeding' => null,
			'memo' => null,
			'_players' => [],
			'_clubs' => [],
		], 'pseudo');
		$e->_specs = $specs;
		return $e;
	}

	public function combined_list() {
		\assert($this->_specs);
		\assert(\is_array($this->_players));
		\assert(\is_array($this->_clubs));
		$res = [];
		for ($i = 0;$i < \count($this->_specs);$i++) {
			$tmp = [
				'spec' => $this->_specs[$i],
				'key' => 'player' . $i,
			];
			if ($i < \count($this->_players)) {
				$tmp['player'] = $this->_players[$i];
			}
			if ($i < \count($this->_clubs)) {
				$tmp['club'] = $this->_clubs[$i];
			}
			$res[] = $tmp;
		}
		return $res;
	}
}
