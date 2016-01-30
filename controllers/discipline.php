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
$entries = $discipline->get_entry_rows();

foreach ($entries as &$e) {
	$e['entry_rowspan'] = $discipline->is_team() ? (\count($e['players']) + 1) : 1;
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