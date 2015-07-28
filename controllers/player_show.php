<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['player_id']);
$player = Player::by_id($_GET['player_id']);
$season = $player->get_season();
$club = $player->get_club();

render('player_show', [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . \urlencode($season->id) . '/'],
		['name' => $player->natural_name(), 'path' => '/player/' . \urlencode($player->id) . '/'],
	],
	'season' => $season,
	'player' => $player,
	'player_club' => $club,
]);