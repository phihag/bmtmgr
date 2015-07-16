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

function _count_entries($disciplines, $callback, $count_players) {
	return \array_reduce($disciplines, function($carry, $d) use($callback, $count_players) {
		return $carry + $callback(\count($d->entries)) * ($count_players ? $d->entry_player_count() : 1);
	}, 0);
}

function _count_players($disciplines) {
	$players = [];
	foreach ($disciplines as $d) {
		foreach ($d->entries as $e) {
			if ($e['player']) {
				\array_push($players, $e['player']->id);
			}
			if ($e['partner']) {
				\array_push($players, $e['partner']->id);
			}
		}
	}
	$unique_players = \array_unique($players);
	return \count($unique_players);
}

function _count_clubs($disciplines) {
	$players = [];
	foreach ($disciplines as $d) {
		foreach ($d->entries as $e) {
			if ($e['player']) {
				\array_push($players, $e['player_club']->id);
			}
			if ($e['partner']) {
				\array_push($players, $e['partner_club']->id);
			}
		}
	}
	$unique_players = \array_unique($players);
	return \count($unique_players);
}


$stats = [
	'entry_count' => _count_entries($disciplines, function($entry_count) {
		return $entry_count;
	}, false),
	'certificate_count' => _count_entries($disciplines, function($entry_count) {
		return $entry_count;
	}, true),
	'gold_medals' => _count_entries($disciplines, function($entry_count) {
		return ($entry_count >= 1) ? 1 : 0;
	}, true),
	'silver_medals' => _count_entries($disciplines, function($entry_count) {
		return ($entry_count >= 2) ? 1 : 0;
	}, true),
	'bronze_medals' => _count_entries($disciplines, function($entry_count) {
		return ($entry_count >= 3) ? 1 : 0;
	}, true),
	'player_count' => _count_players($disciplines),
	'club_count' => _count_clubs($disciplines),
];
$stats['medal_count'] = $stats['gold_medals'] + $stats['silver_medals'] + $stats['bronze_medals'];


$emails = [];
foreach ($disciplines as $d) {
	foreach ($d->entries as $e) {
		if ($e['email']) {
			\array_push($emails, $e['email']);
		}
	}
}
$emails = \array_unique($emails);
\sort($emails);

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

