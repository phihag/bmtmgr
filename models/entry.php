<?php
namespace bmtmgr;

class Entry extends \bmtmgr\Model {
	public $id;
	public $discipline_id;
	public $player_id;
	public $partner_id;
	public $created_time;
	public $updated_time;

	protected function __construct($row, $_is_new) {
		$this->id = $row['id'];
		$this->discipline_id = $row['discipline_id'];
		$this->player_id = $row['player_id'];
		$this->partner_id = $row['partner_id'];
		$this->created_time = $row['created_time'];
		$this->updated_time = $row['updated_time'];

		$this->_is_new = $_is_new;
	}

	public static function create($discipline, $player, $partner) {
		// Call $discipline->check_entry to make sure everything is in order
		return new Entry([
			'id' => null,
			'discipline_id' => $discipline->id,
			'player_id' => $player->id,
			'partner_id' => (($partner == null) ? null : $partner->id),
			'created_time' => time(),
			'updated_time' => null,
		], true);
	}
}
