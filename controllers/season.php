<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';


$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['id']);
$season = Season::by_id($_GET['id']);
$player_count = $season->count_players();

render('season', [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . $season->id . '/'],
	],
	'season' => $season,
	'player_count' => $player_count,
]);