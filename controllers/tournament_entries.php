<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';
require_once dirname(__DIR__) . '/src/btp_export.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['id']);
$tournament = Tournament::by_id($_GET['id']);
$season = $tournament->get_season();
$disciplines = $tournament->get_disciplines();
$any_empty_disciplines = false;
foreach ($disciplines as $d) {
	$d->name_html_id = utils\html_id($d->name);
	$d->entries = $d->get_entry_rows();
	$d->entries_present = \count($d->entries) > 0;
	$d->entry_count = \count($d->entries);
	$d->_is_new = 'modified';
	if (! $d->entries_present) {
		$any_empty_disciplines = true;
	}
}

function _count_players($disciplines) {
	$players = [];
	foreach ($disciplines as $d) {
		foreach ($d->entries as $e) {
			foreach ($e['players'] as $player) {
				\array_push($players, $player->id);
			}
		}
	}
	$unique_players = \array_unique($players);
	return \count($unique_players);
}

function _count_clubs($disciplines) {
	$res = [];
	foreach ($disciplines as $d) {
		foreach ($d->entries as $e) {
			foreach ($e['players'] as $player) {
				\array_push($res, $player->entry_club->id);
			}
		}
	}
	$unique_res = \array_unique($res);
	return \count($unique_res);
}

// calculate stats
$stats = [
	'entry_count' => 0,
	'certificate_count' => 0,
	'gold_medals' => 0,
	'silver_medals' => 0,
	'bronze_medals' => 0,
	'player_count' => _count_players($disciplines),
	'club_count' => _count_clubs($disciplines),
];
foreach ($disciplines as $discipline) {
	$entry_sizes = [];
	foreach ($discipline->entries as $entry) {
		$player_count = \count($entry['players']);

		$stats['entry_count']++;
		$stats['certificate_count'] += $player_count;
		$entry_sizes[] = $player_count;
	}

	if (\count($entry_sizes) === 0) {
		continue;
	}
	\sort($entry_sizes, SORT_NUMERIC);
	$max_size = $entry_sizes[\count($entry_sizes) - 1];
	if (\count($entry_sizes) >= 3) {
		$stats['bronze_medals'] += $max_size;
	}
	if (\count($entry_sizes) >= 2) {
		$stats['silver_medals'] += $max_size;
	}
	if (\count($entry_sizes) >= 1) {
		$stats['gold_medals'] += $max_size;
	}
}
$stats['medal_count'] = $stats['gold_medals'] + $stats['silver_medals'] + $stats['bronze_medals'];


$emails = [];
foreach ($disciplines as &$d) {
	foreach ($d->entries as &$e) {
		if ($e['email']) {
			\array_push($emails, $e['email']);
		}
	}
}
foreach ($tournament->get_all_players() as $player) {
	if ($player->email) {
		\array_push($emails, $player->email);
	}
}
$emails = \array_unique($emails);
\sort($emails);

foreach ($disciplines as &$d) {
	foreach ($d->entries as &$e) {
		$e['entry_rowspan'] = $discipline->is_team() ? (\count($e['players']) + 1) : 1;
	}
}


$data = [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . $season->id . '/'],
		['name' => $tournament->name, 'path' => 't/' . $tournament->id . '/'],
		['name' => $tournament->name, 'path' => 't/' . $tournament->id . '/entries'],
	],
	'season' => $season,
	'tournament' => $tournament,
	'disciplines' => $disciplines,
	'now_date' => \date('Y-m-d'),
	'any_empty_disciplines' => $any_empty_disciplines,
	'stats' => $stats,
	'emails' => $emails,
	'tournament_name_urlencoded' => \rawurlencode($tournament->name),
];

$format = isset($_GET['format']) ? $_GET['format'] : 'html';
switch ($format) {
case 'standalone-html':
	echo get_rendered('tournament_entries_standalone', $data);
	break;
case 'text':
	echo get_rendered('tournament_entries_standalone_text', $data);
	break;
case 'html':
	render('tournament_entries', $data);
	break;
case 'btp':
	\bmtmgr\btp_export\render($data);
	break;
default:
	throw new \Exception('Invalid format code');
}

