<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');
utils\require_get_params(['season_id']);
$season = Season::by_id($_GET['season_id']);
$player_rows = $season->get_player_rows_with_club_names('ORDER BY player.name ASC');


render('season_player_list', [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . urlencode($season->id) . '/'],
		['name' => 'Alle Spieler/innen', 'path' => 'season/' . urlencode($season->id) . '/player/'],
	],
	'season' => $season,
	'players' => $player_rows,
	'player_count' => \count($player_rows),
]);