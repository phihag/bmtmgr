<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['tournament_id']);
$tournament = Tournament::by_id($_GET['tournament_id']);
$season = $tournament->get_season();

render('discipline_create', [
	'add_scripts' => [['filename' => 'discipline.js']],
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . $season->id . '/'],
		['name' => $tournament->name, 'path' => 't/' . $tournament->id . '/'],
		['name' => 'Neue Disziplin', 'path' => 't/' . $tournament->id . '/dialog_discipline_create'],
	],
	'season' => $season,
	'tournament' => $tournament,
]);