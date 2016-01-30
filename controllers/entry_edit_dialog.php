<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['entry_id']);
$entry = Entry::by_id($_GET['entry_id']);
$discipline = $entry->get_discipline();
$entry->_specs = $discipline->player_specs();
$tournament = $discipline->get_tournament();
$disciplines = $tournament->get_disciplines();
$season = $tournament->get_season();

// Mustache skips null values, so set emails to empty string
foreach ($entry->_players as &$p) {
	if (!$p->email) {
		$p->email = '';
	}
}

render('entry_edit', [
	'add_scripts' => [['filename' => 'discipline.js']],
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . $season->id . '/'],
		['name' => $tournament->name, 'path' => 't/' . $tournament->id . '/'],
		['name' => $discipline->name, 'path' => 'd/' . $discipline->id . '/'],
		['name' => 'Meldung bearbeiten', 'path' => 'entry/' . $entry->id . '/edit_dialog'],
	],
	'season' => $season,
	'tournament' => $tournament,
	'disciplines' => $disciplines,
	'discipline' => $discipline,
	'entry' => $entry,
]);
