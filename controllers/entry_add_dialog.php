<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['discipline_id']);
$discipline = Discipline::by_id($_GET['discipline_id']);
$tournament = $discipline->get_tournament();
$season = $tournament->get_season();

render('entry_add', [
	'add_scripts' => [['filename' => 'discipline.js']],
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . $season->id . '/'],
		['name' => $tournament->name, 'path' => 't/' . $tournament->id . '/'],
		['name' => $discipline->name, 'path' => 'd/' . $discipline->id . '/'],
		['name' => 'Neue Meldung', 'path' => 'd/' . $discipline->id . '/entry_add_dialog'],
	],
	'season' => $season,
	'tournament' => $tournament,
	'discipline' => $discipline,
]);