<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/sftp.php';

abstract class Publication extends Model {
	public $id;
	public $tournament_id;
	public $ptype;
	public $config;

	protected function __construct($row, $_is_new=true) {
		$this->id = $row['id'];
		$this->tournament_id = $row['tournament_id'];
		$this->ptype = $row['ptype'];
		$this->config = $row['config'];
		$this->_is_new = $_is_new;
	}

	public static function create($tournament, $ptype, $config) {
		return new static([
			'id' => null,
			'tournament_id' => $tournament->id,
			'ptype' => $ptype,
			'config' => $config,
		], true);
	}

	public function get_tournament() {
		return Tournament::by_id($this->tournament_id);
	}

	public static function fetch_all_in_tournament($tournament_id) {
		return static::get_all(
			'WHERE publication.tournament_id=:tournament_id',
			[':tournament_id' => $tournament_id]
		);
	}

	protected static function from_row($row, $_is_new=false) {
		switch ($row['ptype']) {
		case 'sftp':
			return new \bmtmgr\sftp\SFTPPublication($row, $_is_new);
		}
		return new static($row, $_is_new);
	}

	public function configuration_str() {
		return '(configured)';
	}

	abstract public function publish();
}
