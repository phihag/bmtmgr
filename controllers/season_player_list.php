<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');
utils\require_get_params(['season_id']);
$season = Season::by_id($_GET['season_id']);
$players = $season->get_players_with_clubs('ORDER BY player.name ASC');

render('season_player_list', [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . urlencode($season->id) . '/'],
		['name' => 'Alle Spieler/innen', 'path' => 'season/' . urlencode($season->id) . '/players'],
	],
	'season' => $season,
	'players' => $players,
	'player_count' => \count($players),
]);