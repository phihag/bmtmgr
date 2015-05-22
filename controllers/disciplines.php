<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['tournament_id']);
$tournament = Tournament::by_id($_GET['tournament_id']);
$season = $tournament->get_season();
$disciplines = $tournament->get_disciplines_with_counts();

render('disciplines', [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . $season->id . '/'],
		['name' => $tournament->name, 'path' => 't/' . $tournament->id . '/'],
		['name' => 'Disziplinen', 'path' => 't/' . $tournament->id . '/disciplines'],
	],
	'season' => $season,
	'tournament' => $tournament,
	'disciplines' => $disciplines,
]);