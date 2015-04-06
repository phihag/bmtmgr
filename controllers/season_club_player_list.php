<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['season_id', 'club_id']);
$season = Season::by_id($_GET['season_id']);
$club = User::by_id($_GET['club_id']);

$players = Player::get_in_club_season($_GET['club_id'], $_GET['season_id'], 'ORDER BY name ASC');

render('season_player_list', [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . urlencode($season->id) . '/'],
		['name' => $club->name, 'path' => 'season/' . urlencode($season->id) . '/club/' . $club->id . '/'],
	],
	'club' => $club,
	'season' => $season,
	'players' => $players,
	'player_count' => \count($players),
]);