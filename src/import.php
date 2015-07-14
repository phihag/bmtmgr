<?php
namespace bmtmgr\import;

function find_player($season_id, $name) {
	return \bmtmgr\Player::find_by_name($season_id, $name);
}

function find_club($name) {
	return \bmtmgr\User::find_by_input($name);
}

class Disciplines {
	private $disciplines_by_name;
	private $tournament;
	private $autocreate;

	public function __construct(&$tournament, $autocreate) {
		$this->tournament = $tournament;
		$this->autocreate = $autocreate;
		$disciplines = $tournament->get_disciplines();
		$this->disciplines_by_name = \bmtmgr\utils\array_index($disciplines, function ($d) {
			return \strtolower($d->name);
		});
	}

	public function get($dname) {
		if (! \array_key_exists(\strtolower($dname), $this->disciplines_by_name)) {
			if (!$this->autocreate) {
				throw new \Exception('Could not find discipline ' . $dname . '.');
				continue;
			}

			$d = \bmtmgr\Discipline::create($this->tournament, $dname, 'AA');
			$d->save();
			$disciplines_by_name[\strtolower($dname)] = $d;
		} else {
			$d = $this->disciplines_by_name[\strtolower($dname)];
		}
		return $d;
	}
}

// Returns an array of unmatched line information
function import_text($tournament, $text, $autocreate) {
	$disciplines = new Disciplines($tournament, $autocreate);

	$disciplines_text = \preg_split('/(?:\n\s*)+\n/s', $text);
	$new_entries = [];
	$unmatched_lines = [];
	foreach ($disciplines_text as $dtext) {
		$lines = \explode("\n", $dtext);

		if (\preg_match('/^(?P<name>[A-Za-z0-9\ ]*[A-Za-z0-9])\s*(?:(?:–|-)\s*Noch\s+(?P<space>[0-9]+)\s+.*)?$/',
			$lines[0], $matches)) {
			$dname = $matches['name'];
			$discipline = $disciplines->get($dname);
			$lines = \array_slice($lines, 1);
		} else {
			$discipline = null;
		}

		foreach ($lines as $line) {
			$d = $discipline;

			// Filter out reason string
			if (\preg_match('/^(?P<line>.*?)\s+\[.*?\]$/', $line, $matches)) {
				$line = $matches['line'];
			}

			if (\preg_match('/^\[(?P<dname>.+)\]\s*(?P<line>.+)$/', $line, $matches)) {
				$dname = $matches['dname'];
				$d = $disciplines->get($dname);
				$line = $matches['line'];
			} else {
				if ($d === null) {
					\array_push($unmatched_lines, [
						'line' => $line,
					]);
					continue;
				}
			}

			if (\preg_match('/^(?P<player>[^\/]+?)\s*\(\s*(?P<player_club>[^)]+)\s*\)\s*$/', $line, $matches)) {
				$player_name = \trim($matches['player']);
				$player_club_name = \trim($matches['player_club']);

				$player = find_player($tournament->season_id, $player_name);
				$player_club = find_club($player_club_name);
				$partner = null;
				$partner_club = null;
			} elseif (\preg_match('/^(?P<player>[^\/]+?)\s*\/\s*(?P<partner>[^\/]+?)\s*\(\s*(?P<player_club>[^)\/]+)(?:\s*\/\s*(?P<partner_club>[^)\/]+))?\s*\)\s*$/x', $line, $matches)) {
				$player_name = \trim($matches['player']);
				$player_club_name = \trim($matches['player_club']);
				$partner_name = \trim($matches['partner']);

				$player = find_player($tournament->season_id, $player_name);
				$player_club = find_club($player_club_name);
				$partner = find_player($tournament->season_id, $partner_name);
				if (array_key_exists('partner_club', $matches)) {
					$partner_club_name = \trim($matches['partner_club']);
					$partner_club = find_club($partner_club_name);
				} else {
					$partner_club_name = $player_club_name;
					$partner_club = $player_club;
				}

				if (($partner === null) || ($partner_club === null)) {
					\array_push($unmatched_lines, [
						'line' => $line,
						'discipline' => $d,
						'reason' => (
							($partner === null) ?
							'Partner "' . $partner_name . '" nicht gefunden' :
							'Partner-Verein "' . $partner_club_name . '" nicht gefunden'),
					]);
					continue;
				}
			} else {
				\array_push($unmatched_lines, [
					'line' => $line,
					'discipline' => $d,
					'reason' => 'Ungültiges Format',
				]);
				continue;
			}

			if (($player === null) || ($player_club === null)) {
				\array_push($unmatched_lines, [
					'line' => $line,
					'discipline' => $d,
					'reason' => (
						($player === null) ?
						'Spieler "' . $player_name . '" nicht gefunden' :
						'Verein "' . $player_club_name . '" nicht gefunden'),
				]);
				continue;
			}

			if ($d->dtype == 'AA') {
				$d->dtype = \bmtmgr\Discipline::guess_dtype($player, $partner);
				$d->save();
			}
			$d->check_entry($player, $partner);
			$entry = \bmtmgr\Entry::create(
				$d, $player, $player_club, $partner, $partner_club, null, null);
			\array_push($new_entries, $entry);
		}
	}

	return [$new_entries, $unmatched_lines];
}