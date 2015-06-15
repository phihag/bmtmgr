<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['id']);
$discipline = Discipline::by_id($_GET['id']);
$tournament = $discipline->get_tournament();
$season = $tournament->get_season();
$disciplines = $tournament->get_disciplines();

// Calculate conflicts
$discipline_by_id = [];
foreach ($disciplines as $d) {
	$discipline_by_id[$d->id] = $d;
}
$all_entries = $tournament->get_entries();
$disciplines_by_player_id = [];
foreach ($all_entries as $e) {
	if ($e->discipline_id == $discipline->id) {
		continue;
	}
	if (! \array_key_exists($e->player_id, $disciplines_by_player_id)) {
		$disciplines_by_player_id[$e->player_id] = [];
	}
	\array_push(
		$disciplines_by_player_id[$e->player_id],
		$discipline_by_id[$e->discipline_id]);
	if ($e->partner_id) {
		if (! \array_key_exists($e->partner_id, $disciplines_by_player_id)) {
			$disciplines_by_player_id[$e->partner_id] = [];
		}
		\array_push(
			$disciplines_by_player_id[$e->partner_id],
			$discipline_by_id[$e->discipline_id]);
	}
}
$entries = $discipline->get_entry_rows();
foreach ($entries as &$e) {
	$conflicts = [];
	if (\array_key_exists($e['player']->id, $disciplines_by_player_id)) {
		$conflicts = \array_merge(
			$conflicts, $disciplines_by_player_id[$e['player']->id]);
	}
	if ($e['partner']) {
		if (\array_key_exists($e['partner']->id, $disciplines_by_player_id)) {
			$conflicts = \array_merge(
				$conflicts, $disciplines_by_player_id[$e['partner']->id]);
		}
	}
	$conflicts = utils\array_kunique($conflicts, function($d) {
		return $d->id;
	});
	\usort($conflicts, function($d1, $d2) {
		return \strcmp($d1->name, $d2->name);
	});
	$e['conflicts'] = $conflicts;
}

render('discipline', [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . $season->id . '/'],
		['name' => $tournament->name, 'path' => 't/' . $tournament->id . '/'],
		['name' => $discipline->name, 'path' => 'd/' . $discipline->id . '/'],
	],
	'season' => $season,
	'tournament' => $tournament,
	'disciplines' => $disciplines,
	'discipline' => $discipline,
	'entries' => $entries,
]);